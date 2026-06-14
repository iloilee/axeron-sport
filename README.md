# Axeron Sport Website

**Axeron Sport** là một hệ thống website thương mại điện tử chuyên cung cấp và bán lẻ dụng cụ, trang phục, và phụ kiện thể thao cao cấp. Hệ thống được thiết kế với giao diện hiện đại, tối ưu hóa trải nghiệm người dùng (UX/UI) và đi kèm một hệ thống quản trị (Admin Dashboard) mạnh mẽ giúp chủ cửa hàng dễ dàng vận hành và theo dõi hoạt động kinh doanh.

## 🚀 Tính năng nổi bật

### Dành cho Khách hàng (Storefront)
- **Giao diện hiện đại, thân thiện:** Thiết kế đáp ứng (Responsive) hiển thị mượt mà trên cả điện thoại, máy tính bảng và desktop.
- **Tìm kiếm và Lọc sản phẩm:** Tìm kiếm thông minh theo danh mục, thương hiệu, mức giá.
- **Giỏ hàng & Thanh toán:** Hệ thống giỏ hàng tiện lợi, tính toán phí vận chuyển, áp dụng mã giảm giá (Promo Code) tự động.
- **Đánh giá Sản phẩm:** Cho phép khách hàng để lại bình luận và đánh giá sao cho sản phẩm.
- **Chính sách Freeship:** Thanh thông báo (Top bar) hiển thị động mức tiền còn thiếu để đạt điều kiện miễn phí vận chuyển.

### Dành cho Quản trị viên (Admin Dashboard)
- **Trung tâm Điều khiển (Dashboard):** 
  - Biểu đồ trực quan (Chart.js) theo dõi doanh thu và trạng thái đơn hàng.
  - Thống kê thời gian thực (Real-time Activity Log).
  - Tự động cảnh báo hàng sắp hết và hàng tồn kho quá hạn (Dead Stock).
- **Phân tích Dữ liệu (Analytics):** Thống kê chi tiết tỷ lệ chuyển đổi, giá trị trung bình/đơn, xếp hạng khách hàng VIP và phân tích hiệu suất bán hàng.
- **Quản lý Đơn hàng (Order Management):** Cập nhật trạng thái đơn hàng, thanh toán, xuất file báo cáo Excel/PDF, và in phiếu giao hàng loạt.
- **Quản lý Sản phẩm & Tồn kho:** Thêm, sửa, xóa sản phẩm, hình ảnh và các biến thể (variants).
- **Quản lý Cài đặt:** Đồng bộ hóa Logo, Favicon, Tagline và các thiết lập chung ngay lập tức trên toàn hệ thống.

## 🛠 Công nghệ sử dụng

- **Backend:** PHP (Raw / Custom Framework)
- **Cơ sở dữ liệu:** MySQL (sử dụng Database Helper class)
- **Frontend & UI:** 
  - HTML5, Vanilla CSS
  - [Tailwind CSS](https://tailwindcss.com/) (Styling framework chính yếu)
  - [Chart.js](https://www.chartjs.org/) (Dựng biểu đồ thống kê)
  - Google Material Symbols
- **Export Data:** SheetJS (Xuất Excel), jsPDF (Xuất PDF)

## 📁 Cấu trúc thư mục

```text
axeron-sport-website-master/
├── admin/                  # Khu vực dành riêng cho Quản trị viên
│   ├── admin.php           # File điều hướng & layout chính của Admin
│   ├── admin-api.php       # API xử lý cho admin (AJAX)
│   ├── admin-analytics.php # Báo cáo và thống kê nâng cao
│   ├── admin-orders.php    # Quản lý đơn hàng
│   └── ...                 # Các modules khác của admin
├── shop/                   # Giao diện cửa hàng dành cho Khách mua
│   ├── cart.php            # Giỏ hàng & Xử lý Freeship
│   └── ...                 
├── uploads/                # Hình ảnh sản phẩm, banner, logo
├── .env                    # File cấu hình môi trường (DB, Constants)
└── README.md               # Tài liệu dự án
```

## ⚙️ Hướng dẫn cài đặt

1. **Clone repository về máy local:**
   Đảm bảo bạn đã cài đặt XAMPP/WAMP (hoặc môi trường PHP/MySQL tương đương).
   Clone source code vào thư mục `htdocs` (nếu dùng XAMPP).

2. **Cấu hình Cơ sở dữ liệu:**
   - Tạo một database mới trong MySQL (ví dụ: `axeron_sport`).
   - Import file CSDL (thường có đuôi `.sql` đi kèm dự án) vào database vừa tạo.

3. **Cập nhật file cấu hình:**
   - Mở file cấu hình Database của bạn (hoặc `.env` nếu có).
   - Điền thông tin kết nối Database tương ứng (`DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`).

4. **Chạy ứng dụng:**
   - Khởi động Apache và MySQL trên XAMPP.
   - Truy cập vào trang web thông qua trình duyệt: `http://localhost/axeron-sport-website-master`

---

*Phát triển và thiết kế với ❤️ dành riêng cho Axeron Sports.*
