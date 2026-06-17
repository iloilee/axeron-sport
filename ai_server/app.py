from flask import Flask, request, jsonify
from sentence_transformers import SentenceTransformer

app = Flask(__name__)

# Tải sẵn mô hình vào RAM khi khởi động server
print("Đang tải mô hình AI vào RAM... Xin chờ vài giây...")
model = SentenceTransformer('Qwen/Qwen3-Embedding-0.6B', trust_remote_code=True)
print("Server AI đã sẵn sàng lắng nghe request!")

@app.route('/api/embed', methods=['GET'])
def get_embedding():
    keyword = request.args.get('keyword', '')
    if not keyword:
        return jsonify({'error': 'Vui lòng cung cấp từ khóa'}), 400
    
    # Biến từ khóa thành vector
    vector = model.encode(keyword).tolist()
    return jsonify({'vector': vector})

if __name__ == '__main__':
    # Chạy server ở port 5000
    app.run(port=5000, debug=False)
