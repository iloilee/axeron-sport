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
import pickle
import torch
import torch.nn as nn
from torchvision import models, transforms
from PIL import Image
from sklearn.metrics.pairwise import cosine_similarity
import requests
from io import BytesIO

# Tự động quét ngược lên các thư mục cha để tìm file .env
load_dotenv(find_dotenv())

app = Flask(__name__)

# Tắt log spam của Prophet (cmdstanpy) 
import logging
logging.getLogger('cmdstanpy').disabled = True
                                                                                                                                                                                                                                                                
# Tải sẵn mô hình vào RAM khi khởi động server
print("Loading model Qwen/Qwen3-Embedding-0.6B...")
model = SentenceTransformer('Qwen/Qwen3-Embedding-0.6B', trust_remote_code=True)
print("Loading model PhoBERT Sentiment...")
sentiment_analyzer = pipeline("sentiment-analysis", model="wonrax/phobert-base-vietnamese-sentiment")

print("Loading model ResNet50 for Visual Search...")
weights = models.ResNet50_Weights.DEFAULT
resnet = models.resnet50(weights=weights)
# Bỏ lớp Fully Connected cuối cùng để lấy Vector đặc trưng (2048 chiều)
resnet_model = nn.Sequential(*list(resnet.children())[:-1])
resnet_model.eval() # Chế độ suy luận

preprocess_image = transforms.Compose([
    transforms.Resize(256),
    transforms.CenterCrop(224),
    transforms.ToTensor(),
    transforms.Normalize(mean=[0.485, 0.456, 0.406], std=[0.229, 0.224, 0.225]),
])

FEATURE_FILE = 'image_features.pkl'

def load_image_features():
    if os.path.exists(FEATURE_FILE):
        with open(FEATURE_FILE, 'rb') as f:
            return pickle.load(f)
    return {}

def save_image_features(features):
    with open(FEATURE_FILE, 'wb') as f:
        pickle.dump(features, f)

def extract_image_vector(image):
    """Biến đổi file ảnh (PIL Image) thành 1 mảng numpy vector 2048 chiều"""
    img_t = preprocess_image(image.convert('RGB'))
    batch_t = torch.unsqueeze(img_t, 0)
    with torch.no_grad():
        features = resnet_model(batch_t)
    return features.squeeze().numpy()

print("Server AI Python ready!")

# Cache Vector Database
VECTOR_CACHE = {
    'ids': [],
    'embeddings': None
}

def load_vectors_from_db():
    print("Đang nạp Vector từ MariaDB vào RAM...")
    try:
        conn = pymysql.connect(
            host=os.environ.get('DB_HOST', '127.0.0.1'),
            user=os.environ.get('DB_USER', 'root'),
            password=os.environ.get('DB_PASS', ''),
            database=os.environ.get('DB_NAME', 'sports_shop'),
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
            print(f"Đã nạp thành công {len(ids)} vectors vào RAM.")
    except Exception as e:
        print(f"Lỗi nạp Vector: {str(e)}")
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
    
    # In log ra màn hình CMD theo đúng format
    if results:
        print(f"Keyword: \"{keyword}\" | Results: {len(results)} | Top 1: ID {results[0]['id']} (Score: {results[0]['score']:.4f})", flush=True)
    else:
        print(f"Keyword: \"{keyword}\" | Results: 0", flush=True)
    
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
        print(f"Text: \"{text}\" | Sentiment: {final_sentiment} | Confidence: {score:.4f}", flush=True)

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
        
        # In log ra màn hình CMD theo đúng format
        print(f"Action: Forecast | Data: {len(df)} days | Forecast: {len(future_data)} days", flush=True)
        
        return future_data.to_json(orient='records')

    except Exception as e:
        return jsonify({"error": str(e)}), 500

# ================= VISUAL SEARCH ROUTES =================

@app.route('/index_image', methods=['POST'])
def index_image():
    """Admin gọi API này khi thêm sản phẩm mới để AI học hình ảnh"""
    product_id = request.form.get('product_id')
    image_url = request.form.get('image_url')
    
    if not product_id:
        return jsonify({"error": "Thiếu product_id"}), 400

    try:
        # Nếu gửi file lên
        if 'file' in request.files:
            file = request.files['file']
            img = Image.open(file)
        # Hoặc nếu gửi URL (từ thư mục cục bộ hoặc cloudinary)
        elif image_url:
            if image_url.startswith('http'):
                response = requests.get(image_url)
                img = Image.open(BytesIO(response.content))
            else:
                # Local file path
                base_dir = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
                img_path = os.path.join(base_dir, image_url.lstrip('/'))
                img = Image.open(img_path)
        else:
            return jsonify({"error": "Thiếu file hoặc image_url"}), 400

        vector = extract_image_vector(img)
        features = load_image_features()
        features[product_id] = vector # Lưu vector vào dict
        save_image_features(features)
        
        print(f"Action: Index Image | Product ID: {product_id}", flush=True)
        return jsonify({"status": "success", "product_id": product_id})
        
    except Exception as e:
        print(f"Error indexing image: {str(e)}", flush=True)
        return jsonify({"error": str(e)}), 500

@app.route('/search_image', methods=['POST'])
def search_image():
    """User gọi API này bằng cách upload ảnh để tìm kiếm"""
    if 'file' not in request.files:
        return jsonify({"error": "Vui lòng upload ảnh"}), 400

    try:
        file = request.files['file']
        img = Image.open(file)
        
        query_vector = extract_image_vector(img)
        
        features = load_image_features()
        if not features:
            return jsonify([])

        product_ids = list(features.keys())
        database_vectors = list(features.values())
        
        # Tính độ tương đồng
        similarities = cosine_similarity([query_vector], database_vectors)[0]
        
        # Lấy Top 8 sản phẩm giống nhất
        top_indices = np.argsort(similarities)[::-1][:8]
        
        results = []
        for i in top_indices:
            # Lọc bỏ ảnh giống < 50%
            if similarities[i] > 0.5:
                results.append({
                    "product_id": product_ids[i],
                    "score": round(float(similarities[i]) * 100, 2)
                })
        
        print(f"Action: Visual Search | Results: {len(results)}", flush=True)
        return jsonify(results)
        
    except Exception as e:
        print(f"Error visual search: {str(e)}", flush=True)
        return jsonify({"error": str(e)}), 500

if __name__ == '__main__':
    # Load database into memory first time
    # (Đã gọi load_vectors_from_db ở trên nên không cần gọi lại, nhưng cứ để đây nếu muốn reload)
    
    # Run the server using Waitress (Production server)
    from waitress import serve
    print(" * Serving Flask app 'app' (Production Mode via Waitress)")
    print(" * Running on all addresses (0.0.0.0)")
    print(" * Running on http://127.0.0.1:5000")
    print("Press CTRL+C to quit")
    serve(app, host='0.0.0.0', port=5000)
