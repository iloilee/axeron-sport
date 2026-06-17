import os
import pymysql
import requests
from io import BytesIO
from PIL import Image
from app import extract_image_vector, load_image_features, save_image_features

def main():
    print("Connecting to database...")
    conn = pymysql.connect(
        host='127.0.0.1',
        user='root',
        password='',
        database='sports_shop',
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
