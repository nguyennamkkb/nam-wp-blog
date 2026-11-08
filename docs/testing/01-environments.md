# 3-4. Môi trường, dữ liệu & ma trận bao phủ

> Tài liệu tham chiếu · Cập nhật: 2026-11-08 · [Mục lục](../README.md) · [Backlog công việc](../tasks/README.md)


## 3.1 Môi trường

| Môi trường | Dùng cho | Ghi chú |
|---|---|---|
| Local DDEV | Phát triển, E2E nhanh | `https://wp-blog.ddev.site`, PHP 8.4, MariaDB 11.8 |
| CI (wp-env) | Unit, E2E, visual | Pin WP 7.1.4 + PHP 8.4; DB MySQL tương đương |
| Staging | Lighthouse, UAT, cross-browser | Production-like, basic auth, `noindex` |
| Production | Smoke test sau deploy | Chỉ đọc, không tạo dữ liệu rác |

## 3.2 Fixture content

Bộ dữ liệu chuẩn trong `tests/fixtures/`:

- **12 posts**: 4 deep dive, 6 tutorial, 2 note.
- **3 series**, mỗi series 3–5 phần.
- **5 categories** theo content pillars.
- **5 pages**: About, Now, Uses, Contact, Privacy.
- **Media**: cover 1200×630 (PNG/AVIF), avatar tác giả.
- **Case đặc biệt**:
  - bài 15 headings (TOC dài),
  - heading trùng tên (slug chống trùng),
  - heading tiếng Việt có dấu,
  - bài không có featured image,
  - title rất dài (80 ký tự),
  - bài không có excerpt,
  - code block chứa `<script>`, `&`, emoji,
  - series chỉ 1 phần,
  - series 10 phần,
  - 0 kết quả search,
  - 100+ posts để test pagination.

Seed script: `tests/setup-fixtures.sh`, chạy qua `ddev wp` hoặc `wp-env run cli`. Script phải:

- idempotent (chạy lại không nhân đôi dữ liệu),
- tạo đúng slug/ngày cố định,
- không chứa secret,
- chạy được trên DB trống.

---

# 4. Ma trận bao phủ

## 4.1 Template × trạng thái

| Template | Light | Dark | Mobile 390 | Desktop 1440 | JS off |
|---|---|---|---|---|---|
| `front-page` | ✅ | ✅ | ✅ | ✅ | ✅ |
| `home` (blog) | ✅ | ✅ | ✅ | ✅ | ✅ |
| `single` | ✅ | ✅ | ✅ | ✅ | ✅ |
| `page` | ✅ | ✅ | ✅ | ✅ | ✅ |
| `archive` | ✅ | ✅ | ✅ | ✅ | ✅ |
| `taxonomy-series` | ✅ | ✅ | ✅ | ✅ | ✅ |
| `search` | ✅ | ✅ | ✅ | ✅ | ✅ |
| `404` | ✅ | ✅ | ✅ | ✅ | ✅ |

## 4.2 Tính năng × browser

| Tính năng | Chrome | Firefox | WebKit/Safari | Mobile |
|---|---|---|---|---|
| Navigation / menu | ✅ | ✅ | ✅ | ✅ |
| Search dialog + keyboard | ✅ | ✅ | ✅ | ✅ |
| TOC + scroll spy | ✅ | ✅ | ✅ | ✅ |
| Series nav/list | ✅ | ✅ | ✅ | ✅ |
| Dark toggle + persist | ✅ | ✅ | ✅ | ✅ |
| Code copy | ✅ | ✅ | ✅ | ✅ |
| Pagination | ✅ | ✅ | ✅ | ✅ |
| Sticky header/TOC | ✅ | ✅ | ✅ | ✅ |

---

