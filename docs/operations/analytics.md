# Analytics — Umami (privacy-first)

> Tài liệu vận hành · Cập nhật: 2026-11-08 · [Mục lục](../README.md)

Latent dùng **Umami** cho pageview và một bộ event hành vi tối giản. Không có
Google Analytics, không cookie tracking của bên thứ ba và không cần cookie
banner. Theme chỉ in thẻ script khi cả hai option đã được cấu hình; mặc định
site không gửi dữ liệu nào.

## 1. Cấu hình

Hai option cần thiết:

| Option | Giá trị | Ví dụ |
|---|---|---|
| `latent_umami_src` | URL **HTTPS tuyệt đối** tới `script.js` | `https://cloud.umami.is/script.js` |
| `latent_umami_website_id` | Website ID của site trong Umami | `00000000-0000-0000-0000-000000000000` |

Đặt bằng WP-CLI (DDEV):

```bash
ddev wp option update latent_umami_src 'https://cloud.umami.is/script.js'
ddev wp option update latent_umami_website_id '00000000-0000-0000-0000-000000000000'
```

Sau khi đổi option, xoá cache trang (nếu có page cache/CDN) rồi kiểm tra:

```bash
# Thẻ script phải xuất hiện đúng một lần trong <head>
curl -s https://wp-blog.ddev.site/ | grep -o '<script[^>]*umami[^>]*>' | head -2

# Không cấu hình: không có gì được in
curl -s https://wp-blog.ddev.site/ | grep -c 'umami' || true
```

Quy tắc kiểm tra:

- Thiếu một trong hai option → không in script, không nạp `analytics.js`.
- `latent_umami_src` phải là URL tuyệt đối bắt đầu bằng `https://`; giá trị
  `http://`, URL tương đối hoặc chuỗi rỗng bị từ chối.
- Website ID phải là UUID hoặc slug 8–128 ký tự (`A–Z`, `a–z`, `0–9`, `.`, `_`,
  `:`, `-`).
- Script được in một lần trong `wp_head`, có `async defer` và
  `data-website-id`; URL/attribute đều được escape qua API WordPress.

## 2. Umami Cloud hoặc self-host

### Umami Cloud

1. Tạo tài khoản tại <https://cloud.umami.is> và thêm website với domain thật.
2. Copy **Website ID** và dùng `https://cloud.umami.is/script.js` làm
   `latent_umami_src`.
3. Không cần cấu hình DNS hay server riêng.

### Self-host

1. Chạy Umami bằng Docker Compose với PostgreSQL (xem tài liệu chính thức của
   Umami).
2. Trỏ một subdomain, ví dụ `analytics.example.com`, qua reverse proxy có TLS.
3. Tạo website trong dashboard, rồi đặt:
   - `latent_umami_src` = `https://analytics.example.com/script.js`
   - `latent_umami_website_id` = website ID tương ứng
4. Nếu site production chặn IP/domain nội bộ, cho phép domain của site gọi
   Umami trong CSP (`connect-src`/`script-src`).

Không hard-code ID trong code: mọi cấu hình đi qua option để staging và
production dùng site Umami khác nhau.

## 3. Event đang gửi

`assets/js/analytics.js` là cầu nối mỏng giữa theme và `window.umami`. Mọi
lời gọi đều no-op nếu tracker chưa tải hoặc bị chặn, nên không có lỗi console
và không ảnh hưởng giao diện.

| Event | Gắn vào | Props gửi kèm |
|---|---|---|
| `theme_toggle` | sự kiện `latent:themechange` (do `theme.js` phát) | `theme`: `light` hoặc `dark` |
| `search_open` | click `.latent-search-open` (nút mở palette, ⌘K) | không |
| `search_result_click` | click `.latent-search__result` (kết quả trong palette) | không |
| `code_copy` | click `.latent-code__copy` (nút copy code block) | không |
| `outbound_click` | click link `http(s)` ra khỏi domain hiện tại | `host`: hostname đích |
| `scroll_75` | cuộn tới 75% chiều cao trang, tối đa một lần/trang | không |

Ghi chú:

- Click được uỷ quyền ở cấp `document`, nên kết quả search render động vẫn
  được tính mà không cần sửa `search.js`.
- `scroll_75` chỉ bắn một lần cho mỗi lượt xem, kể cả khi người đọc cuộn lên
  xuống nhiều lần.
- **Không** gửi event `search_query`: từ khoá tìm kiếm không bao giờ rời khỏi
  trình duyệt.

## 4. Bảo vệ privacy

- Không gửi từ khoá tìm kiếm, nội dung bài, email, user ID, tên hay bất kỳ
  thuộc tính nào của người đọc.
- `outbound_click` chỉ gửi hostname; path/query (có thể chứa dữ liệu nhạy cảm)
  bị bỏ.
- Không cookie tracking của bên thứ ba; Umami dùng session ẩn danh phía server.
- Script tải bằng `async defer`, không chặn render.
- Khi tracker bị chặn (adblock/CSP), toàn bộ event trở thành no-op.
- Không thu thập dữ liệu ở trang admin hay preview.

## 5. Tắt analytics

Xoá cả hai option:

```bash
ddev wp option delete latent_umami_src
ddev wp option delete latent_umami_website_id
```

Theme sẽ không in thẻ script và cũng dequeue `assets/js/analytics.js`. Xoá cache
sau khi đổi để HTML cũ không còn thẻ script.

Tạm thời chỉ tắt tracking mà vẫn giữ cấu hình: chặn domain Umami ở proxy/CDN
hoặc CSP; trang vẫn hoạt động bình thường.

## 6. Kiểm tra & xử lý sự cố

| Hiện tượng | Kiểm tra |
|---|---|
| Không thấy pageview | `ddev wp option get latent_umami_src`; xem Network tab có request `script.js` 200 |
| Có thẻ script nhưng không có event | Mở console, chạy `window.umami` phải là object; kiểm tra CSP/adblock |
| Script không in dù đã cấu hình | URL phải là `https://` tuyệt đối, ID đúng định dạng; xem cache |
| Event search/code không lên | Kiểm tra selector còn khớp markup (`.latent-search-open`, `.latent-search__result`, `.latent-code__copy`) |
| Dữ liệu staging lẫn production | Mỗi môi trường phải dùng website ID riêng |

Kiểm tra nhanh bằng Umami **Realtime**: mở site, thực hiện search → mở kết quả,
copy một code block, đổi theme, cuộn hết bài; cả sáu event phải xuất hiện với
đúng tên.
