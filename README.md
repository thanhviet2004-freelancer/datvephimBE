# CineGo backend — Laravel API

Backend độc lập dùng PHP 8.3+, Laravel 13 và SQLite. Không chứa giao diện khách hàng. Frontend ở repository datvephimFE.

## Chạy trên máy hiện tại

```powershell
php artisan serve --host=127.0.0.1 --port=8000
```

Cơ sở dữ liệu và `.env` cũ đã được giữ lại khi tách. API mẫu: http://127.0.0.1:8000/api/v1/movies

## Cài trên máy khác

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
New-Item -ItemType File database/database.sqlite -ErrorAction SilentlyContinue
php artisan migrate --seed
php artisan serve --host=127.0.0.1 --port=8000
```

Nếu Composer chưa có trong PATH, máy hiện tại có thể dùng `php composer.phar install`. Không chạy migrate:fresh trên dữ liệu cần giữ. Chạy `php artisan db:seed` để bổ sung lịch chiếu mẫu khi hết lịch.

## API v1

Tất cả phản hồi thành công chứa trường `data`. Gửi `Accept: application/json`; các POST dùng `Content-Type: application/json`.

| Phương thức | Đường dẫn | Chức năng |
| --- | --- | --- |
| GET | /api/v1/movies?q= | Tìm/danh sách phim |
| GET | /api/v1/movies/{id} | Phim và các suất chiếu sắp tới |
| GET | /api/v1/showtimes/{id} | Suất chiếu, phim và mảng occupied |
| POST | /api/v1/showtimes/{id}/bookings | Đặt ghế; trả 201 |
| POST | /api/v1/bookings/lookup | Tra cứu bằng code và email |

Ví dụ body đặt vé:

```json
{"customer_name":"Khách Demo","email":"demo@example.com","phone":"0901234567","seats":["C4","C5"]}
```

Ví dụ tra cứu:

```json
{"code":"UUID-nhan-duoc-khi-dat-ve","email":"demo@example.com"}
```

Validation trả 422 với `errors`, không tìm thấy/hết giờ trả 404, giới hạn tần suất trả 429. Backend tính giá từ database và dùng transaction + unique constraint chống trùng ghế. Không có API công khai đọc vé chỉ bằng mã; cần cả email để tra cứu. BookingResource không trả email/số điện thoại ra frontend.

## CORS và triển khai

`.env`: `FRONTEND_URLS=http://127.0.0.1:8080,http://localhost:8080`. Khi đổi tên miền frontend, cập nhật danh sách này rồi chạy `php artisan config:clear`. CORS không thay thế xác thực; API đặt vé demo cho khách vãng lai, có rate limit. Khi triển khai thật cần HTTPS, `APP_DEBUG=false`, xác thực/quản trị và vòng đời thanh toán/giữ ghế. Chưa gửi email, chưa thanh toán online, chưa hủy hoặc tự hết hạn ghế.

## Kiểm thử

```powershell
php artisan test --compact
```

Kiểm thử dùng SQLite trong bộ nhớ, không sửa dữ liệu thật.

## GitHub

Remote origin đã cấu hình tới https://github.com/thanhviet2004-freelancer/datvephimBE.git trên máy hiện tại. Chạy trong thư mục datvephimBE:

```powershell
git add .
git commit -m "Tach backend Laravel API dat ve phim"
git push -u origin main
```

`.env`, database SQLite, vendor và composer.phar đã được bỏ qua. Nếu repo GitHub đã có commit và push bị từ chối, cần kiểm tra/ghép lịch sử; không dùng force push.
