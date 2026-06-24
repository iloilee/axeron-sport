# Axeron Sport Website

**Axeron Sport** là một hệ thống website thương mại điện tử chuyên cung cấp và bán lẻ dụng cụ, trang phục, và phụ kiện thể thao cao cấp. Hệ thống được thiết kế với giao diện hiện đại, áp dụng những công nghệ tiên tiến nhất bao gồm cả Trí tuệ Nhân tạo (AI) để nâng cao trải nghiệm mua sắm của khách hàng, đồng thời đi kèm một hệ thống quản trị (Admin Dashboard) mạnh mẽ.

---

## 🚀 Tính năng nổi bật

### 1. Dành cho Khách hàng (Storefront)
- **Giao diện & UX/UI Hiện đại:** Sử dụng Tailwind CSS với các hiệu ứng chuyển cảnh mượt mà, Mega Menu đa cấp trực quan, và thiết kế đáp ứng (Responsive) 100% trên thiết bị di động.
- **Tích hợp Trí tuệ Nhân tạo (AI Search):**
  - **Tìm kiếm bằng hình ảnh (Visual Search):** Cho phép người dùng tải ảnh lên để tìm kiếm các sản phẩm tương tự.
  - **Tìm kiếm ngữ nghĩa (Semantic Search):** Hệ thống phân tích ý nghĩa từ khóa thay vì chỉ tìm theo cụm từ chính xác (được xử lý qua Python/Flask API).
- **Trải nghiệm mua sắm cá nhân hóa:**
  - Danh mục sản phẩm (Product Catalog) với bộ lọc đa dạng (Khoảng giá, màu sắc, kích cỡ, nhãn hiệu).
  - Quản lý sản phẩm yêu thích (Wishlist).
  - **Sản phẩm đã xem (Recently Viewed):** Ghi nhớ và gợi ý lại các sản phẩm khách hàng đã tương tác.
- **Quản lý Đơn hàng & Đánh giá:** 
  - Khách hàng có thể tra cứu đơn hàng (Order Tracking), xem lịch sử mua hàng chi tiết.
  - Hệ thống khuyến khích đánh giá (Review) thông minh nhắc nhở người dùng đánh giá từng sản phẩm cụ thể trong đơn hàng.

### 2. Dành cho Quản trị viên (Admin Dashboard)
- **Trung tâm Điều khiển (Dashboard):** 
  - Biểu đồ trực quan (Chart.js) theo dõi doanh thu và trạng thái đơn hàng.
  - Thống kê thời gian thực (Real-time Activity Log).
  - Tự động cảnh báo hàng sắp hết và hàng tồn kho quá hạn (Dead Stock).
- **Phân tích Dữ liệu (Analytics):** Thống kê chi tiết tỷ lệ chuyển đổi, giá trị trung bình/đơn, xếp hạng khách hàng VIP và phân tích hiệu suất bán hàng.
- **Quản lý Đơn hàng & Sản phẩm:** Thêm, sửa, xóa sản phẩm, hình ảnh, biến thể (variants). Xử lý trạng thái đơn hàng.
- **Quản lý Cài đặt:** Đồng bộ hóa Logo, Favicon, Tagline và các thiết lập chung ngay lập tức trên toàn hệ thống.

---

## 🛠 Công nghệ sử dụng

- **Backend (Core):** PHP (Raw / Custom Architecture)
- **AI / Microservice:** Python, Flask (Xử lý Semantic Search và Image Search thông qua cổng 5000).
- **Cơ sở dữ liệu:** MySQL (`sports_shop.sql`)
- **Frontend & UI:** 
  - HTML5, Vanilla JavaScript.
  - **Tailwind CSS** (Được cấu hình để compile qua Node.js/PostCSS).
  - **Google Material Symbols** cho hệ thống Icon.
  - [Chart.js](https://www.chartjs.org/) (Dựng biểu đồ thống kê).
- **Công cụ hỗ trợ:** Node.js, npm (Dùng cho lệnh `npm run build:css`).

---

## 📁 Cấu trúc thư mục (Tóm tắt)

```text
axeron-sport-website-master/
├── admin/                  # Khu vực dành riêng cho Quản trị viên
├── assets/                 # CSS (input.css, output.css), JS, Images tĩnh
├── config/                 # Các file cấu hình hệ thống (Database, Session)
├── includes/               # Các component dùng chung (Header, Footer, Head)
├── shop/                   # Giao diện cửa hàng dành cho Khách mua
│   ├── product-catalog.php # Trang danh sách sản phẩm (có AI Search)
│   ├── product-detail.php  # Chi tiết sản phẩm
│   ├── order-history.php   # Lịch sử đơn hàng có phân trang
│   ├── my-reviews.php      # Đánh giá sản phẩm của tôi
│   └── ...                 
├── uploads/                # Nơi lưu trữ hình ảnh tải lên của sản phẩm, logo
├── package.json            # File cấu hình Node.js cho Tailwind/PostCSS
├── tailwind.config.js      # Cấu hình UI theme, màu sắc của Tailwind
├── sports_shop.sql         # File cấu trúc Cơ sở dữ liệu MySQL
└── README.md               # Tài liệu dự án
```

---

## ⚙️ Hướng dẫn cài đặt & Khởi chạy

### 1. Yêu cầu hệ thống
- XAMPP / WAMP (Apache & MySQL).
- PHP 8.0 trở lên.
- Node.js (Để compile CSS nếu bạn muốn tùy chỉnh giao diện).
- Python 3.x (Bắt buộc nếu muốn chạy các tính năng AI Search).

### 2. Cài đặt Web (PHP/MySQL)
1. **Clone/Copy dự án** vào thư mục `htdocs` (nếu dùng XAMPP).
2. **Import Cơ sở dữ liệu:** Mở phpMyAdmin, tạo một database tên là `axeron_sport`, sau đó import file `sports_shop.sql` vào database này.
3. **Cấu hình DB:** Mở thư mục `config/database.php` (hoặc `.env` nếu có) và đảm bảo thông tin kết nối đúng với MySQL của bạn.
4. Truy cập trang web qua: `http://localhost/axeron-sport-website-master`

### 3. Cài đặt giao diện (Tailwind CSS)
Nếu bạn thay đổi cấu trúc HTML hoặc thêm class mới, hãy chạy lệnh sau trong thư mục gốc của dự án để build lại CSS:
```bash
npm install
npm run build:css
```

### 4. Khởi chạy AI Microservice (Tùy chọn)
Để tính năng **Tìm kiếm bằng hình ảnh** và **Tìm kiếm ngữ nghĩa** hoạt động, bạn cần khởi chạy Server Python:
1. Mở terminal, điều hướng đến thư mục chứa mã nguồn Python AI (thường nằm ở một thư mục backend riêng hoặc trong dự án).
2. Chạy server Flask (mặc định lắng nghe ở `http://127.0.0.1:5000`).
*(Hệ thống PHP đã được lập trình sẵn để tự động fallback về tìm kiếm truyền thống nếu Server AI này không được bật).*

---

*Phát triển và thiết kế với ❤️ dành riêng cho Axeron Sports.*
