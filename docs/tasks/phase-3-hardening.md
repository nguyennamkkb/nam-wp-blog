# Phase 3 — Hardening (Ngày 8–9)

> **Mục tiêu phase:** đạt chuẩn production về SEO, performance, accessibility, security, analytics và test tự động.
> **Thời lượng:** ~26h · **Cổng ra:** Lighthouse/axe/security gates pass; bộ E2E + visual chạy trong CI.
> **Trạng thái phase:** `TODO`

---

## T3.1 — SEO & schema

**Trạng thái:** `TODO` · **Ước lượng:** 4h · **Phụ thuộc:** T2.8

**Mục tiêu:** metadata, canonical, schema và cấu trúc heading đúng; không trùng schema giữa theme và plugin.

**Việc cần làm:**

- [ ] Cài + activate Rank Math (một plugin SEO duy nhất).
- [ ] Cấu hình title template: `%title% — Latent`; trang chủ dùng tagline.
- [ ] Bật schema: `Person` (author), `WebSite` + `SearchAction`, `BlogPosting`, `BreadcrumbList`.
- [ ] Bật breadcrumb (nếu dùng) và style nhẹ.
- [ ] Đảm bảo mỗi trang một H1, heading hierarchy đúng.
- [ ] Canonical đúng; không canonical về staging.
- [ ] Meta description cho post/page; fallback excerpt.
- [ ] Noindex cho search results/author archive nếu không cần index.
- [ ] Commit: `feat(seo): configure metadata and schema`

**Nghiệm thu:**

- [ ] Mỗi template có title/meta/canonical.
- [ ] JSON-LD parse được, required fields đủ.
- [ ] Không có 2 schema `BlogPosting` trùng.
- [ ] Breadcrumb đúng thứ tự.
- [ ] Lighthouse SEO = 100 trên 5 template chính.

**Test:**

```bash
curl -s https://wp-blog.ddev.site/<slug>/ | grep -o '<script type="application/ld+json">.*</script>' | head
npx lighthouse https://wp-blog.ddev.site/ --only-categories=seo --chrome-flags='--headless'
```

---

## T3.2 — OG/RSS/sitemap/robots/llms.txt

**Trạng thái:** `TODO` · **Ước lượng:** 3h · **Phụ thuộc:** T3.1

**Mục tiêu:** chia sẻ mạng xã hội đẹp, feed/sitemap hợp lệ, robots đúng, có `llms.txt` cho AI search.

**Việc cần làm:**

- [ ] Đăng ký image size `latent-og` 1200×630; dùng featured image làm OG.
- [ ] Fallback OG image khi bài không có featured image (site cover mặc định).
- [ ] OG/Twitter meta đầy đủ; kiểm tra preview.
- [ ] Cấu hình RSS: title, description, 10 item gần nhất; kiểm tra encoding tiếng Việt.
- [ ] Sitemap XML (Rank Math) gồm post/page/series; loại trừ staging/noindex.
- [ ] `robots.txt` trỏ sitemap; không chặn CSS/JS/ảnh.
- [ ] Route `llms.txt` trong `latent-core`: liệt kê bài pillar + series + about.
- [ ] Commit: `feat(seo): add og images rss sitemap and llms.txt`

**Nghiệm thu:**

- [ ] `/feed/` 200, XML hợp lệ, tiếng Việt đúng.
- [ ] `/sitemap_index.xml` 200, có post/page/series.
- [ ] `/robots.txt` 200, trỏ đúng sitemap.
- [ ] `/llms.txt` 200, nội dung đúng.
- [ ] OG image 1200×630, < 300KB.
- [ ] Facebook/LinkedIn/X preview đúng (manual).

**Test:**

```bash
for u in /feed/ /sitemap_index.xml /robots.txt /llms.txt; do
  printf '%-20s ' "$u"; curl -s -o /dev/null -w '%{http_code}\n' "https://wp-blog.ddev.site$u"
done
curl -s https://wp-blog.ddev.site/feed/ | head -20
```

---

## T3.3 — Performance

**Trạng thái:** `TODO` · **Ước lượng:** 5h · **Phụ thuộc:** T3.2, T2.8

**Mục tiêu:** đạt budget CWV trên mobile, không có tài nguyên thừa.

**Việc cần làm:**

- [ ] Audit baseline: Lighthouse + Query Monitor + network waterfall.
- [ ] Preload đúng 1–2 font; `font-display: swap`; subset latin/vietnamese.
- [ ] Đăng ký image sizes cho card/cover; dùng `srcset`/`sizes`; ưu tiên AVIF/WebP.
- [ ] Ảnh above-fold: `fetchpriority="high"`, không lazy; ảnh dưới fold: `loading="lazy"`.
- [ ] JS: `defer`, chỉ load search/code-copy khi cần; không thư viện ngoài.
- [ ] CSS: giữ theme.css gọn; không import font/CSS từ CDN.
- [ ] Kiểm tra query count; tránh N+1 trong query loop/related.
- [ ] Bật cache production (LiteSpeed/Redis) — ghi rõ trong deploy docs.
- [ ] Commit: `perf: optimize fonts images and assets`

**Nghiệm thu:**

- [ ] LCP < 1.5s, INP < 200ms, CLS < 0.05 (Lighthouse mobile, staging).
- [ ] HTML < 40KB, CSS < 50KB, JS < 40KB, font < 250KB.
- [ ] Requests < 30.
- [ ] Không request Google Fonts/CDN ngoài.
- [ ] Không layout shift khi ảnh/font tải.

**Test:**

```bash
npx lhci autorun --collect.url=https://staging.example.com/
npx lighthouse https://wp-blog.ddev.site/ --preset=desktop --chrome-flags='--headless'
```

---

## T3.4 — Accessibility

**Trạng thái:** `TODO` · **Ước lượng:** 4h · **Phụ thuộc:** T2.8

**Mục tiêu:** WCAG 2.2 AA cho toàn bộ template, keyboard và screen reader.

**Việc cần làm:**

- [ ] Chạy axe trên 8 template × light/dark × mobile/desktop; ghi lại lỗi.
- [ ] Sửa serious/critical: contrast, label, landmark, heading order, focus.
- [ ] Keyboard: skip link, nav, search dialog, TOC, pagination, theme toggle.
- [ ] Dialog focus trap + trả focus; `aria-live` cho search/copy.
- [ ] `prefers-reduced-motion`: tắt smooth scroll/animation.
- [ ] Target size ≥ 24px; focus ring rõ.
- [ ] Manual screen reader (VoiceOver/NVDA) trước launch.
- [ ] Commit: `fix(a11y): resolve axe and keyboard issues`

**Nghiệm thu:**

- [ ] axe 0 serious, 0 critical.
- [ ] Keyboard pass 100% luồng chính.
- [ ] Contrast AA.
- [ ] Không bẫy focus.
- [ ] Screen reader đọc đúng thứ tự và trạng thái.

**Test:**

```bash
npx playwright test tests/a11y/
npx axe https://wp-blog.ddev.site/ --exit
```

---

## T3.5 — Security & backup

**Trạng thái:** `TODO` · **Ước lượng:** 3h · **Phụ thuộc:** T0.5

**Mục tiêu:** giảm bề mặt tấn công, có backup và quy trình restore đã kiểm chứng.

**Việc cần làm:**

- [ ] Cài Solid Security (hoặc bộ tương đương) + bật 2FA cho admin.
- [ ] Giới hạn login attempts; strong password; least privilege.
- [ ] `DISALLOW_FILE_EDIT`; tắt XML-RPC nếu không dùng.
- [ ] Security headers: HSTS, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy`; CSP report-only.
- [ ] Backup: UpdraftPlus/hosting, daily DB + weekly files, offsite.
- [ ] Restore drill: restore DB + files vào staging, ghi thời gian.
- [ ] Quét: `wp core verify-checksums`, `wp plugin verify-checksums --all`, WPScan.
- [ ] Commit: `chore(security): harden site and configure backups`

**Nghiệm thu:**

- [ ] 2FA bắt buộc với admin.
- [ ] Login sai bị giới hạn.
- [ ] File editor disabled; XML-RPC off.
- [ ] Security headers đạt (securityheaders.com ≥ A).
- [ ] Backup chạy đúng lịch; restore test thành công.
- [ ] Không có plugin lỗi thời/critical vulnerability.

**Test:**

```bash
ddev wp core verify-checksums
ddev wp plugin verify-checksums --all
curl -sI https://staging.example.com/ | grep -Ei 'strict-transport|x-content-type|referrer-policy|permissions-policy'
```

---

## T3.6 — Analytics

**Trạng thái:** `TODO` · **Ước lượng:** 2h · **Phụ thuộc:** T3.1

**Mục tiêu:** đo WER và hành vi đọc mà không xâm phạm privacy, không cookie banner.

**Việc cần làm:**

- [ ] Tạo site Umami (cloud/self-host), thêm script qua theme.
- [ ] Event: `search_open`, `search_query`, `search_result_click`, `code_copy`, `theme_toggle`, `outbound_click`, `scroll_75`, `series_nav_click`.
- [ ] Không gửi dữ liệu cá nhân/nội dung search nhạy cảm.
- [ ] Dashboard tuần: WER, top pages, search terms, CWV, 404.
- [ ] Commit: `feat(analytics): add privacy-first tracking`

**Nghiệm thu:**

- [ ] Pageview xuất hiện trong Umami.
- [ ] Event search/code/theme hiển thị đúng.
- [ ] Không cookie tracking third-party.
- [ ] Script không chặn render, không lỗi console.

**Test:**

```bash
# Mở site, thực hiện search/copy/toggle, kiểm tra Umami realtime
```

---

## T3.7 — Fixtures + full E2E/visual

**Trạng thái:** `TODO` · **Ước lượng:** 5h · **Phụ thuộc:** T2.8, T3.3, T3.4

**Mục tiêu:** bộ dữ liệu test chuẩn + toàn bộ test tự động chạy trong CI.

**Việc cần làm:**

- [ ] `tests/setup-fixtures.sh`: seed 12 post, 3 series, 5 page, media, case đặc biệt (mục 3.2 của test plan).
- [ ] Script idempotent, chạy được trên DB trống.
- [ ] `playwright.config.ts`: Chromium/Firefox/WebKit, mobile viewport, baseURL.
- [ ] E2E: home, single, TOC, series, search, dark mode, code, pagination, 404, RSS/sitemap.
- [ ] axe test cho 8 template.
- [ ] Visual baseline 8 template × light/dark × 390/1440.
- [ ] GitHub Actions job `e2e` + `visual` + `axe`.
- [ ] Commit: `test(e2e): add fixtures and full browser suite`

**Nghiệm thu:**

- [ ] `npm run test:e2e` pass local.
- [ ] `npm run test:a11y` pass.
- [ ] Visual diff 0 ngoài baseline.
- [ ] CI E2E xanh.
- [ ] Fixture chạy lại không nhân đôi dữ liệu.

**Test:**

```bash
bash tests/setup-fixtures.sh
npm run test:e2e
npm run test:a11y
npm run test:visual
```
