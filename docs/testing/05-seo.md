# 8. SEO

> Tài liệu tham chiếu · Cập nhật: 2026-11-08 · [Mục lục](../README.md) · [Backlog công việc](../tasks/README.md)


## SEO-01 — On-page

- Title ≤ 60 ký tự (ưu tiên), meta 140–160.
- Canonical đúng, không trỏ staging.
- Slug tiếng Anh, không dấu, không stop word thừa.
- Một H1, hierarchy đúng.

## SEO-02 — Structured data

- `Person` (author), `WebSite` + `SearchAction`, `BlogPosting`, `BreadcrumbList`.
- JSON-LD parse được, required fields đủ.
- Không schema trùng lặp (Rank Math + theme).

## SEO-03 — Sitemap & robots

- Sitemap không chứa staging/noindex/trash.
- `robots.txt` không chặn CSS/JS/ảnh.
- Staging `noindex` + basic auth.

## SEO-04 — RSS

- Feed hợp lệ, đủ metadata, không lỗi encoding tiếng Việt.

## SEO-05 — Broken links

- Crawl toàn site (lychee/linkinator), 0 internal 404.
- External link kiểm tra định kỳ (không chặn release nếu external chết).

## SEO-06 — Open Graph

- Preview đúng trên Facebook/LinkedIn/X.
- Ảnh 1200×630, < 300KB, có alt.

## SEO-07 — Indexing

- Search Console verify, submit sitemap.
- Kiểm tra `site:` sau 7 ngày.
- Không index search results/archive phân trang nếu không cần.

---

