import os
import json
import pymysql
import numpy as np
import pandas as pd
from prophet import Prophet
from flask import Flask, request, jsonify
from sentence_transformers import SentenceTransformer
from transformers import pipeline
from dotenv import load_dotenv, find_dotenv

# Tự động quét ngược lên các thư mục cha để tìm file .env
load_dotenv(find_dotenv())

app = Flask(__name__)

# Tải sẵn mô hình vào RAM khi khởi động server
print("Đang tải mô hình Qwen/Qwen3-Embedding-0.6B vào RAM...")
model = SentenceTransformer('Qwen/Qwen3-Embedding-0.6B', trust_remote_code=True)
print("Đang tải mô hình PhoBERT Sentiment...")
sentiment_analyzer = pipeline("sentiment-analysis", model="wonrax/phobert-base-vietnamese-sentiment")
print("Server AI Python đã sẵn sàng lắng nghe request!")

# Cache Vector Database
VECTOR_CACHE = {
    'ids': [],
    'embeddings': None
}

def load_vectors_from_db():
    print("Đang nạp Vector từ MariaDB vào RAM...")
    try:
        conn = pymysql.connect(
            host='127.0.0.1',
            user='root',
            password='',
            database='sports_shop',
            cursorclass=pymysql.cursors.DictCursor
        )
        with conn.cursor() as cursor:
            cursor.execute("SELECT product_id, embedding_vector FROM product_embeddings")
            rows = cursor.fetchall()
            
            ids = []
            embeddings = []
            for row in rows:
                vec = json.loads(row['embedding_vector'])
                ids.append(row['product_id'])
                embeddings.append(vec)
                
            VECTOR_CACHE['ids'] = np.array(ids)
            # Normalize embeddings for faster cosine similarity
            emb_array = np.array(embeddings, dtype=np.float32)
            norms = np.linalg.norm(emb_array, axis=1, keepdims=True)
            # Avoid division by zero
            norms[norms == 0] = 1
            VECTOR_CACHE['embeddings'] = emb_array / norms
            print(f"✅ Đã nạp thành công {len(ids)} vectors vào RAM.")
    except Exception as e:
        print(f"❌ Lỗi nạp Vector: {str(e)}")
    finally:
        if 'conn' in locals() and conn.open:
            conn.close()

# Nạp database lần đầu khi khởi động
load_vectors_from_db()

@app.route('/api/reload', methods=['POST'])
def reload_vectors():
    """Gọi endpoint này khi có sản phẩm mới để AI nạp lại Vector"""
    load_vectors_from_db()
    return jsonify({"status": "success", "message": f"Reloaded {len(VECTOR_CACHE['ids'])} vectors."})

@app.route('/api/search', methods=['GET'])
def search_similar_products():
    keyword = request.args.get('keyword', '')
    if not keyword:
        return jsonify({'error': 'Vui lòng cung cấp từ khóa'}), 400
    
    if VECTOR_CACHE['embeddings'] is None or len(VECTOR_CACHE['embeddings']) == 0:
        return jsonify({'error': 'Database vectors not loaded'}), 500
        
    # 1. Biến từ khóa thành vector
    query_vector = model.encode(keyword).astype(np.float32)
    
    # 2. Chuẩn hóa vector truy vấn
    query_norm = np.linalg.norm(query_vector)
    if query_norm > 0:
        query_vector = query_vector / query_norm
        
    # 3. Tính Cosine Similarity bằng NumPy Matrix Multiplication (Siêu tốc độ)
    # Vì cả 2 đã chuẩn hóa, dot product chính là cosine similarity
    similarities = np.dot(VECTOR_CACHE['embeddings'], query_vector)
    
    # 4. Lọc ra các sản phẩm có độ giống > 0.3
    threshold = 0.3
    valid_indices = np.where(similarities > threshold)[0]
    
    # 5. Lấy ID và Score
    results = []
    for idx in valid_indices:
        results.append({
            "id": int(VECTOR_CACHE['ids'][idx]),
            "score": float(similarities[idx])
        })
        
    # 6. Sắp xếp giảm dần theo Score
    results = sorted(results, key=lambda x: x['score'], reverse=True)
    
    return jsonify({'results': results})

# Endpoint dự phòng vẫn giữ lại để tương thích
@app.route('/api/embed', methods=['GET'])
def get_embedding():
    keyword = request.args.get('keyword', '')
    if not keyword:
        return jsonify({'error': 'Vui lòng cung cấp từ khóa'}), 400
    vector = model.encode(keyword).tolist()
    return jsonify({'vector': vector})

@app.route('/analyze_sentiment', methods=['POST'])
def analyze():
    try:
        data = request.get_json()
        text = data.get('text', '')
        
        if not text:
            return jsonify({"error": "Không có nội dung bình luận"}), 400

        # Đưa đoạn text vào mô hình PhoBERT để dự đoán
        result = sentiment_analyzer(text)[0]
        label = result['label']  # POS, NEG, NEU
        score = result['score']  # Độ tin cậy (VD: 0.98)

        # Chuẩn hóa nhãn (Map label) về định dạng tiếng Anh
        sentiment_map = {
            "POS": "positive",
            "NEG": "negative",
            "NEU": "neutral"
        }
        
        final_sentiment = sentiment_map.get(label, "neutral")

        # In log ra màn hình CMD theo đúng format
        print(f"Nhập: \"{text}\"", flush=True)
        print(f"AI trả về: {{\"confidence\":{round(score, 4)},\"sentiment\":\"{final_sentiment}\"}}", flush=True)

        return jsonify({
            "sentiment": final_sentiment,
            "confidence": round(score, 4)
        })

    except Exception as e:
        return jsonify({"error": str(e)}), 500

@app.route('/forecast_revenue', methods=['POST'])
def forecast():
    try:
        data = request.get_json()
        if not data or len(data) < 10:
            return jsonify({"error": "Cần ít nhất 10 ngày dữ liệu để dự báo"}), 400

        df = pd.DataFrame(data)
        
        # Khởi tạo và huấn luyện mô hình Prophet
        model = Prophet(yearly_seasonality=False, weekly_seasonality=True, daily_seasonality=False)
        model.fit(df)
        
        # Tạo dataframe chứa 90 ngày tiếp theo
        future = model.make_future_dataframe(periods=90)
        forecast_result = model.predict(future)
        
        # Lọc lấy 90 ngày tương lai để trả về
        future_data = forecast_result[['ds', 'yhat', 'yhat_lower', 'yhat_upper']].tail(90)
        
        # Format lại ngày và làm tròn số tiền
        future_data['ds'] = future_data['ds'].dt.strftime('%Y-%m-%d')
        future_data['yhat'] = future_data['yhat'].round(0)
        future_data['yhat_lower'] = future_data['yhat_lower'].round(0)
        future_data['yhat_upper'] = future_data['yhat_upper'].round(0)
        
        return future_data.to_json(orient='records')

    except Exception as e:
        return jsonify({"error": str(e)}), 500

if __name__ == '__main__':
    # Chạy server ở port 5000
    app.run(port=5000, debug=False)
