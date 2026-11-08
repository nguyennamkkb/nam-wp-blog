# 7. Performance

> Tài liệu tham chiếu · Cập nhật: 2026-11-08 · [Mục lục](../README.md) · [Backlog công việc](../tasks/README.md)


## PERF-01 — Lighthouse CI

Chạy trên staging, mobile preset:

| Category | Ngưỡng |
|---|---|
| Performance | ≥ 95 |
| Accessibility | 100 |
| Best Practices | 100 |
| SEO | 100 |

## PERF-02 — WebPageTest

- 4G, mobile, 3 lần lấy median.
- LCP < 1.5s, INP < 200ms, CLS < 0.05.
- Không render-blocking third-party above fold.

## PERF-03 — Budget check

- HTML < 40KB, CSS < 50KB, JS < 40KB, fonts < 250KB.
- Requests < 30.
- Ảnh: AVIF/WebP, kích thước đúng, lazy-load dưới fold.

## PERF-04 — Runtime

- Query Monitor (local): query count hợp lý, không N+1 ở query loop.
- Không plugin gọi HTTP API trên frontend.
- Object cache hit rate (staging) > 90% nếu bật Redis.

## PERF-05 — Field data

- Search Console CWV hàng tháng sau launch.
- Nếu p75 vượt budget → mở ticket P1.

---

