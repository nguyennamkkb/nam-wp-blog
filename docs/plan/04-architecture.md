# 4. Kiến trúc kỹ thuật

> Tài liệu tham chiếu · Cập nhật: 2026-11-08 · [Mục lục](../README.md) · [Backlog công việc](../tasks/README.md)


## 4.1 Stack & quyết định

| Lớp | Lựa chọn | Lý do |
|---|---|---|
| CMS | WordPress block theme | Ít vận hành, SEO tốt, editor quen, không lock-in |
| Theme | `latent` (custom) | Kiểm soát design system, không phụ thuộc theme ngoài |
| Site plugin | `latent-core` | Tách logic nội dung khỏi theme (series, tắt comments) |
| Page builder | Không | Tránh bloat, giữ HTML sạch, CWV tốt |
| CSS | `theme.json` + CSS thuần | Token hóa, không build step |
| JS | ES modules thuần | Nhẹ, không framework runtime |
| Search | Core REST API + command palette | Không plugin, UX tốt, fallback search page |
| SEO | Rank Math | Schema, sitemap, OG, redirect; một plugin duy nhất |
| Analytics | Umami | Privacy-first, không cookie banner |
| Font | Self-host, subset latin + vietnamese | Performance, không phụ thuộc Google Fonts |

**ADR tóm tắt**

- **ADR-001:** Block theme thay vì headless. Headless chỉ đáng khi cần app/multi-channel; blog thuần không cần.
- **ADR-002:** Custom dynamic blocks render server-side, editor preview bằng `ServerSideRender`, không build React.
- **ADR-003:** `series` là taxonomy, không phải CPT, để query và archive tự nhiên.
- **ADR-004:** Không comments/members; giảm bề mặt tấn công và chi phí vận hành.
- **ADR-005:** Search dùng core REST, debounce, tối thiểu 2 ký tự; không thêm search engine ở MVP.
- **ADR-006:** SEO bằng Rank Math để giảm code tự viết; theme vẫn giữ HTML semantic.

## 4.2 Cấu trúc theme & plugin

```text
wp-content/
├── themes/latent/
│   ├── style.css
│   ├── theme.json
│   ├── functions.php
│   ├── inc/
│   │   ├── setup.php
│   │   ├── assets.php
│   │   ├── blocks.php
│   │   ├── toc.php
│   │   ├── reading-time.php
│   │   ├── series.php
│   │   └── search.php
│   ├── blocks/
│   │   ├── reading-time/{block.json,index.js,render.php}
│   │   ├── toc/{block.json,index.js,render.php}
│   │   ├── series-nav/{block.json,index.js,render.php}
│   │   └── series-list/{block.json,index.js,render.php}
│   ├── templates/
│   │   ├── index.html
│   │   ├── front-page.html
│   │   ├── home.html
│   │   ├── single.html
│   │   ├── page.html
│   │   ├── archive.html
│   │   ├── taxonomy-series.html
│   │   ├── search.html
│   │   └── 404.html
│   ├── parts/
│   │   ├── header.html
│   │   ├── footer.html
│   │   └── search-dialog.html
│   ├── patterns/
│   │   ├── hero.php
│   │   ├── post-grid.php
│   │   └── series-list.php
│   ├── assets/
│   │   ├── fonts/
│   │   ├── css/theme.css
│   │   └── js/{theme.js,search.js,toc.js,code-copy.js}
│   └── languages/
└── plugins/latent-core/
    ├── latent-core.php
    └── inc/{taxonomy-series.php,comments.php,cleanup.php}
```

## 4.3 Data model

- `post` — bài viết.
- `category` — pillar (5 category cố định).
- `post_tag` — chủ đề tự do.
- `series` — taxonomy mới, non-hierarchical, public, `show_in_rest`, rewrite `series`.
- `page` — About, Now, Uses, Contact, Privacy, Series.
- Không CPT ở MVP; “notes” dùng post + category `notes` để giữ đơn giản.

## 4.4 Chiến lược block

- Dùng core blocks tối đa: Group, Columns, Query Loop, Post Title/Excerpt/Featured Image, Navigation, Search, Post Content.
- Custom dynamic blocks (4): `latent/reading-time`, `latent/toc`, `latent/series-nav`, `latent/series-list`.
- Mỗi block có `block.json`, `index.js` đăng ký client-side, `render.php` render server-side, editor preview qua `ServerSideRender`.
- Không build step: JS dùng global `wp.*` và `wp.element.createElement`.
- Mọi block phải pass block validation; không để “invalid block” trong template.

## 4.5 Search

- `<dialog>` command palette, mở bằng nút header hoặc `Ctrl/Cmd + K`.
- Gọi `/wp-json/wp/v2/search?search=...&per_page=8&_fields=id,title,url,subtype`.
- Debounce 250ms, tối thiểu 2 ký tự, `aria-live` cho kết quả.
- Keyboard: ↑/↓ chọn, Enter mở, Esc đóng; focus trả về nút mở.
- Fallback: trang `/search/` dùng template `search.html` (hoạt động khi JS tắt).
- Không index nội dung nhạy cảm; chỉ post/page public.

## 4.6 Dark mode

- Mặc định theo `prefers-color-scheme`.
- Toggle light/dark, lưu `localStorage`.
- Inline script trong `wp_head` để set `data-theme` trước paint, tránh flash.
- Tất cả màu đi qua token; không hardcode màu trong template/block.
- Dark mode phải pass contrast AA và visual regression.

## 4.7 TOC

- Sinh từ heading H2/H3 của post, render server-side.
- Heading IDs sinh bằng slugify ổn định, hỗ trợ tiếng Việt, chống trùng.
- `the_content` filter gắn ID; TOC dùng cùng thuật toán để link khớp.
- Desktop: sticky cột phải; mobile: collapsible ở đầu bài.
- Scroll spy bằng IntersectionObserver, tôn trọng `prefers-reduced-motion`.

## 4.8 Series

- Taxonomy `series`; archive `/series/{slug}/`.
- `latent/series-nav` hiển thị tên series, danh sách phần, phần hiện tại.
- `latent/series-list` hiển thị tất cả series + số phần + bài mới nhất.
- Trang “Series” dùng pattern + block list.

## 4.9 Môi trường

| Môi trường | Mục đích | Cấu hình |
|---|---|---|
| Local | Phát triển | DDEV, WP 7.1.4, PHP 8.4, MariaDB 11.8, nginx-fpm |
| Staging | Kiểm thử trước release | Cùng PHP/DB với production, basic auth, `noindex` |
| Production | Người đọc | LiteSpeed/Apache + Redis, PHP 8.4, CDN Cloudflare |

**Nguyên tắc parity:** PHP version, DB engine/version, WP version, theme/plugin code giống nhau. Khác biệt web server phải được test riêng.

## 4.10 Git & CI/CD

- Repo track: `wp-content/themes/latent`, `wp-content/plugins/latent-core`, `.ddev`, `composer.json`, `package.json`, `tests`, `docs`, config mẫu.
- Không track: WP core, uploads, `wp-config.php`, `vendor`, `node_modules`, backup, secrets.
- Branch: `main` luôn deployable; `feat/*`, `fix/*`, `content/*`.
- Commit: Conventional Commits.
- PR: template + checklist + CI bắt buộc.
- Deploy: tag `v*` → build artifact → staging → smoke test → production (manual approval).
- Rollback: giữ 3 artifact gần nhất; rollback code + DB snapshot nếu cần.

## 4.11 Observability

- Uptime: Better Stack/UptimeRobot.
- Analytics: Umami (pageview + custom events).
- Search Console: index, query, CWV.
- Error log: PHP error log + hosting log; Phase 2 có thể thêm Sentry.
- Dashboard tuần: WER, top pages, search terms, CWV, 5xx, 404.

## 4.12 Backup & rollback

- DB: daily, giữ 30 ngày.
- Files: weekly + trước mỗi deploy.
- Offsite: S3/Backblaze hoặc hosting snapshot.
- Restore drill: mỗi quý, đo thời gian restore.
- Trước deploy: snapshot + export DB.

---

