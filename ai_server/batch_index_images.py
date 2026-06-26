import os
import pymysql
import requests
import pickle
import torch
import torch.nn as nn
from torchvision import models, transforms
from io import BytesIO
from PIL import Image

print("Loading model ResNet50 for Visual Search...")
weights = models.ResNet50_Weights.DEFAULT
resnet = models.resnet50(weights=weights)
resnet_model = nn.Sequential(*list(resnet.children())[:-1])
resnet_model.eval()

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
    img_t = preprocess_image(image.convert('RGB'))
    batch_t = torch.unsqueeze(img_t, 0)
    with torch.no_grad():
        features = resnet_model(batch_t)
    return features.squeeze().numpy()

def main():
    from dotenv import load_dotenv, find_dotenv
    load_dotenv(find_dotenv())
    
    print("Connecting to database...")
    conn = pymysql.connect(
        host=os.environ.get('DB_HOST', '127.0.0.1'),
        user=os.environ.get('DB_USER', 'root'),
        password=os.environ.get('DB_PASS', ''),
        database=os.environ.get('DB_NAME', 'sports_shop'),
        cursorclass=pymysql.cursors.DictCursor
    )
    
    features = load_image_features()
    
    try:
        with conn.cursor() as cursor:
            # Lấy tất cả ảnh chính của các sản phẩm
            cursor.execute("""
                SELECT p.product_id, pi.image_url 
                FROM products p
                JOIN product_images pi ON p.product_id = pi.product_id
                WHERE pi.is_primary = 1
            """)
            rows = cursor.fetchall()
            
            print(f"Found {len(rows)} products to index.")
            
            success_count = 0
            for row in rows:
                product_id = str(row['product_id'])
                image_url = row['image_url']
                
                print(f"Processing Product ID {product_id}... ", end="")
                
                try:
                    if image_url.startswith('http'):
                        response = requests.get(image_url)
                        img = Image.open(BytesIO(response.content))
                    else:
                        base_dir = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
                        img_path = os.path.join(base_dir, image_url.lstrip('/'))
                        img = Image.open(img_path)
                        
                    vector = extract_image_vector(img)
                    features[product_id] = vector
                    success_count += 1
                    print("OK")
                except Exception as e:
                    print(f"FAILED: {str(e)}")
                    
            save_image_features(features)
            print(f"\nDone! Indexed {success_count}/{len(rows)} products successfully.")
            
    finally:
        conn.close()

if __name__ == '__main__':
    main()
