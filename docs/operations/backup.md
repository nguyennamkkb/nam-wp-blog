# Backup & restore

> Tài liệu vận hành · Cập nhật: 2026-11-08 · [Mục lục](../README.md) · [Backlog công việc](../tasks/README.md)

Quy trình backup và restore cho site Latent. Script `scripts/backup.sh` tạo bản
backup DB + source theme/plugin; phần uploads và hạ tầng do hosting đảm nhiệm
(xem mục 3).

## 1. Phạm vi backup

### Được backup

| Thành phần | Nguồn | Ghi chú |
|---|---|---|
| Toàn bộ database | `ddev wp db export` | Tất cả bảng WordPress, kể cả options, users, posts, series |
| Theme | `wp-content/themes/latent` | Source code, templates, parts, `theme.json`, CSS/JS |
| Site plugin | `wp-content/plugins/latent-core` | Source code taxonomy series, tắt comments |

### Không được backup bởi script

| Thành phần | Lý do / nơi backup |
|---|---|
| `wp-content/uploads/` (media gốc) | Dung lượng lớn; backup ở host (snapshot/UpdraftPlus) và offsite |
| WordPress core | Cài lại từ phiên bản đã pin (`wp core download --version=...`) |
| `vendor/`, `node_modules/` | Sinh lại từ `composer install` / `npm ci` |
| `wp-config.php`, `wp-config-ddev.php`, `.env` | Secrets — lưu trong password manager, không đưa vào archive |
| Cấu hình DDEV (`.ddev/`) | Sinh lại bằng `ddev config` / version trong repo |
| Database server, mail, object cache | Hosting chịu trách nhiệm |

> **Quan trọng:** một bản backup chỉ có DB + theme/plugin **không đủ** để dựng
> lại site có ảnh. Production phải bật backup cấp host cho `uploads/` hằng ngày
> hoặc dùng UpdraftPlus/BBQ theo lịch trong `docs/plan/08-operations.md`.

## 2. Chạy backup

Yêu cầu: Docker + DDEV, project đang chạy (`ddev start`).

```bash
cd /home/namnl/Documents/dev/wp-blog
bash scripts/backup.sh
```

Script sẽ:

1. Resolve project root từ đường dẫn script (chạy được từ bất kỳ thư mục nào).
2. Tạo `backups/` nếu chưa có, dùng timestamp UTC `YYYYMMDDTHHMMSSZ`.
3. Export DB ra file tạm, kiểm tra khác rỗng, rồi gzip thành
   `backups/db-$STAMP.sql.gz`.
4. Nén theme + plugin thành `backups/files-$STAMP.tar.gz`.
5. Kiểm tra `gzip -t` cả hai file và `tar -tzf` cho archive files.
6. In đường dẫn, số byte và dung lượng; thoát khác 0 ngay khi bất kỳ bước nào lỗi.

Ví dụ output:

```text
==> Latent backup — 20261108T073000Z (UTC)
==> Project root: /home/namnl/Documents/dev/wp-blog
==> Exporting database
...
    /home/namnl/Documents/dev/wp-blog/backups/db-20261108T073000Z.sql.gz (48213 bytes, 48K)
    /home/namnl/Documents/dev/wp-blog/backups/files-20261108T073000Z.tar.gz (612044 bytes, 598K)
    Total: 646K
```

Kiểm tra nhanh sau khi chạy:

```bash
ls -lh backups/ | tail -4
gzip -t backups/db-*.sql.gz
gzip -t backups/files-*.tar.gz
```

`backups/` và các file `*.sql.gz`, `*.tar.gz` đã nằm trong `.gitignore`; không
commit backup lên git.

## 3. Backup production

Production dùng thêm một trong các lựa chọn sau, chạy ngoài repo:

- **UpdraftPlus** (hoặc tương đương): DB hằng ngày, files hằng tuần, đẩy lên
  offsite (S3/Backblaze/Google Drive) và mã hoá.
- **Host snapshot**: snapshot toàn VM/volume hằng ngày, giữ tối thiểu 7 bản.
- Trước mỗi lần deploy: snapshot + `wp db export` thủ công (xem
  `docs/tasks/phase-4-launch.md`).

Nguyên tắc **3-2-1**: 3 bản copy, 2 loại phương tiện, 1 bản offsite. Backup DB
chứa email và hash mật khẩu người dùng — phải để nơi riêng tư, mã hoá, không
chia sẻ qua chat.

## 4. Quy trình restore

Restore vào **local/staging trước**, không restore thẳng production khi chưa
kiểm tra. Luôn tạo một backup an toàn của trạng thái hiện tại trước khi ghi đè.

### 4.1 Restore local (DDEV)

```bash
cd /home/namnl/Documents/dev/wp-blog
ddev start

# 0. Backup trạng thái hiện tại để có đường lùi
bash scripts/backup.sh

# 1. Xác định timestamp từ tên file
STAMP=20261108T073000Z
ls -lh "backups/db-$STAMP.sql.gz" "backups/files-$STAMP.tar.gz"

# 2. Restore code (theme + plugin) từ project root
tar -xzf "backups/files-$STAMP.tar.gz"

# 3. Restore database — giải nén vào trong project vì DDEV chỉ mount project root
gunzip -c "backups/db-$STAMP.sql.gz" > "backups/restore-$STAMP.sql"
ddev wp db import "backups/restore-$STAMP.sql"
rm -f "backups/restore-$STAMP.sql"

# 4. Xoá cache và flush rewrite rules
ddev wp cache flush
ddev wp rewrite flush

# 5. Kiểm tra
ddev wp db check
ddev wp option get siteurl
ddev wp post list --post_type=post --format=count
ddev wp core verify-checksums
ddev wp plugin verify-checksums --all
curl -sI https://wp-blog.ddev.site/ | head -5
```

Nếu cần dựng lại từ đầu (DB trống hoàn toàn):

```bash
ddev wp db reset --yes          # xoá sạch DB local
ddev wp core install --url=https://wp-blog.ddev.site \
  --title=Latent --admin_user=admin --admin_password=admin \
  --admin_email=admin@example.com --skip-email
# sau đó import DB như bước 3 ở trên
```

### 4.2 Restore production (WP-CLI trên host)

Chạy trong thư mục webroot production, dùng WP-CLI cài sẵn:

```bash
cd /var/www/latent/current

# 0. Bật maintenance + backup hiện trạng
wp maintenance-mode activate
wp db export "/backups/pre-restore-$(date -u +%Y%m%dT%H%M%SZ).sql"

# 1. Restore code
tar -xzf /backups/files-20261108T073000Z.tar.gz -C /var/www/latent/current

# 2. Restore DB
gunzip -c /backups/db-20261108T073000Z.sql.gz > /backups/restore.sql
wp db import /backups/restore.sql
rm -f /backups/restore.sql

# 3. Cache, rewrite, quyền file
wp cache flush
wp rewrite flush --hard
wp core verify-checksums
wp plugin verify-checksums --all

# 4. Tắt maintenance và smoke test
wp maintenance-mode deactivate
curl -sI https://example.com/ | head -5
```

Nếu site dùng object cache (Redis), flush cả object cache ở tầng hosting sau khi
import DB. Nếu URL khác môi trường nguồn, chạy search-replace có kiểm soát:

```bash
wp search-replace 'https://staging.example.com' 'https://example.com' \
  --all-tables --precise --skip-columns=guid --dry-run
# bỏ --dry-run sau khi xem kết quả
```

### 4.3 Rollback

Nếu restore lỗi, import lại file backup vừa tạo ở bước 0 và giải nén lại archive
code tương ứng. Không xoá backup cũ cho tới khi smoke test production xanh.

## 5. Restore drill checklist

Chạy mỗi quý (và trước launch). Ghi thời gian thực tế vào bảng dưới.

- [ ] Chọn một cặp `db-*.sql.gz` + `files-*.tar.gz` bất kỳ (không phải bản mới nhất).
- [ ] Dựng staging/local sạch, restore theo mục 4.
- [ ] `wp db check` trả `OK`.
- [ ] Trang chủ, 1 single post, 1 series archive, search, `/feed/`, sitemap trả 200.
- [ ] Ảnh featured image hiển thị (kiểm tra `uploads/` có backup host).
- [ ] Đăng nhập admin được; không có PHP warning trong `debug.log`.
- [ ] `wp core verify-checksums` và `wp plugin verify-checksums --all` sạch.
- [ ] Ghi lại thời gian restore, người thực hiện, lỗi gặp phải.

| Ngày | Người thực hiện | Bản backup | Thời gian restore | Kết quả | Ghi chú |
|---|---|---|---|---|---|
|  |  |  |  |  |  |

## 6. Retention đề xuất

| Loại | Giữ | Nơi lưu |
|---|---|---|
| Daily | 7 bản | Offsite + local |
| Weekly | 4 bản | Offsite |
| Monthly | 3 bản | Offsite, mã hoá |
| Trước deploy | 1 bản / lần | Offsite |

Dọn bản cũ bằng lịch của hosting/UpdraftPlus, không xoá thủ công bản monthly
gần nhất. Kiểm tra dung lượng `backups/` local định kỳ để tránh đầy ổ.

## 7. Hardening production không nằm trong repo

Các mục sau bắt buộc ở production nhưng không cấu hình được bằng theme
(xem docblock `wp-content/themes/latent/inc/security.php`):

- **2FA** cho mọi tài khoản admin (Solid Security hoặc tương đương).
- **Giới hạn login attempts** + strong password + least privilege.
- **`DISALLOW_FILE_EDIT`** (và thường cả `DISALLOW_FILE_MODS`) trong
  `wp-config.php` production.
- **HSTS** (`Strict-Transport-Security`) chỉ bật khi HTTPS vĩnh viễn và redirect
  301 từ HTTP đã hoạt động; không bật ở local DDEV.
- **CSP report-only** trước, enforce sau khi không còn violation.
- **WAF/CDN** trước origin, chặn `/xmlrpc.php`, bảo vệ `/wp-login.php`.
- **Quét định kỳ**: `wp core verify-checksums`, `wp plugin verify-checksums --all`,
  WPScan; `composer audit` và `npm audit --audit-level=high` trong CI.

Tham chiếu: `docs/testing/06-security.md`, `docs/plan/08-operations.md`.
