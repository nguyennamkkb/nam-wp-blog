# 9. Security

> Tài liệu tham chiếu · Cập nhật: 2026-11-08 · [Mục lục](../README.md) · [Backlog công việc](../tasks/README.md)


## SEC-01 — Hardening

- 2FA cho admin.
- `DISALLOW_FILE_EDIT`, XML-RPC off nếu không dùng.
- Giới hạn login, strong password.
- Không dùng plugin nulled; chỉ plugin từ wp.org/uy tín.

## SEC-02 — Headers

- HSTS, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy`.
- CSP report-only trước, enforce sau khi sạch.
- HTTPS toàn site, redirect 301 từ HTTP.

## SEC-03 — REST & input

- Search chỉ trả post/page public.
- Dynamic blocks escape/sanitize đúng.
- Không lộ nonce/secret trong HTML/JS.
- XSS test ở search, title, excerpt.

## SEC-04 — Dependencies

- `composer audit`, `npm audit` (high/critical = fail).
- WPScan định kỳ; `wp core verify-checksums`, `wp plugin verify-checksums --all`.

## SEC-05 — Backup/restore

- Backup DB daily + files weekly, offsite.
- Restore drill mỗi quý; ghi thời gian và kết quả.
- Trước deploy: snapshot + export DB.

## SEC-06 — Staging

- Basic auth, `noindex`, không public dữ liệu thật.
- Không dùng chung credential production.

---

