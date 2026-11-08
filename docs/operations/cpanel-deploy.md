# Deploy lên cPanel — staging

> Tài liệu vận hành · Cập nhật: 2026-11-08 · [Mục lục](../README.md)

Staging hiện tại:

| Thành phần | Giá trị |
|---|---|
| Domain | `staging.simpleverse.io.vn` |
| Docroot | `/home/bggxwvvk/public_html/staging.simpleverse.io.vn` |
| IP hosting | `210.211.125.205` |
| WordPress | 7.1.4, cài bằng Softaculous |
| Admin | `/wp-admin/` |

## 1. Cấu hình DNS (Tenten)

Tại `https://domain.tenten.vn/` → bản ghi DNS của `simpleverse.io.vn`:

| Type | Name | Value | TTL |
|---|---|---|---|
| A | `staging` | `210.211.125.205` | 3600 |

Sau khi DNS lan truyền, bật SSL:

- cPanel → **SSL/TLS Status** → chọn `staging.simpleverse.io.vn` → **Run AutoSSL**.
- Hoặc **Lets Encrypt™ SSL** → issue cho subdomain.

## 2. Deploy code bằng cPanel Git Version Control

Repo GitHub: `https://github.com/nguyennamkkb/nam-wp-blog.git`

Trong cPanel:

1. **Files → Git™ Version Control → Create**
   - Clone URL: `https://github.com/nguyennamkkb/nam-wp-blog.git`
   - Repository Path: `/home/bggxwvvk/repos/nam-wp-blog`
   - Repository Name: `nam-wp-blog`
   - Branch: `main`
2. Mở repo → **Manage** → **Pull or Deploy**
3. Bấm **Update from Remote** để lấy commit mới.
4. Bấm **Deploy HEAD Commit** — cPanel chạy `.cpanel.yml`, copy:
   - `wp-content/themes/latent` → docroot staging
   - `wp-content/plugins/latent-core` → docroot staging
5. Trong WP Admin → **Appearance → Themes** → activate **Latent**.
6. **Plugins** → activate **Latent Core**.

Mỗi lần code thay đổi:

```bash
git push origin main
```

Sau đó trong cPanel Git: **Update from Remote → Deploy HEAD Commit**.

## 3. Cấu hình WordPress staging

Trong WP Admin:

- **Settings → General**: Site Title `Latent`, Tagline `Ghi chú thực chiến về AI, LLM và hệ thống`.
- **Settings → Permalinks**: `/%postname%/`.
- **Settings → General**: Timezone `Ho Chi Minh`.
- **Settings → Discussion**: bỏ tick “Allow comments”.
- **Settings → Reading**: chọn static front page `Home`, posts page `Blog` (sau khi import nội dung).

## 4. Nội dung (tuỳ chọn)

Staging đang là WordPress mới. Có 2 cách:

1. **Viết mới** vài bài trên staging để test giao diện.
2. **Import từ local**:
   - Local: Tools → Export → WXR.
   - Staging: Tools → Import → WordPress → upload file.
   - Media/featured image cần upload lại nếu import không kéo được file.

Không import DB local trực tiếp nếu table prefix khác (`wp_` vs `wp75_`) và URL khác; cần search-replace cẩn thận.

## 5. Kiểm tra sau deploy

```bash
curl -I https://staging.simpleverse.io.vn/
curl -s https://staging.simpleverse.io.vn/ | grep -o 'latent-theme\|latent-header' | head
```

- Trang chủ 200, không lỗi PHP.
- Theme `Latent` active.
- Plugin `Latent Core` active.
- `/wp-admin/` đăng nhập được.
- SSL hợp lệ (ổ khóa xanh).

## 6. Lên production sau khi duyệt staging

Production dự kiến: `simpleverse.io.vn` (hoặc `blog.simpleverse.io.vn`).

Cần chốt:

- Docroot production là `public_html` hay thư mục riêng?
- DNS `@`/`www` hiện trỏ `74.81.54.185`, không phải IP cPanel `210.211.125.205`.
  Nếu muốn blog ở root, phải đổi A record `@` sang IP cPanel (hoặc dùng subdomain `blog`).
- Cập nhật `DEPLOYPATH` trong `.cpanel.yml` hoặc tạo branch `production` với path riêng.
