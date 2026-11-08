# Phase 4 — Launch (Ngày 10)

> **Mục tiêu phase:** staging giống production, full regression, nội dung launch, DNS/CDN, go-live an toàn.
> **Thời lượng:** ~16h · **Cổng ra:** launch checklist 100%; production ổn định 24h; có retro.
> **Trạng thái phase:** `DOING`

---

## T4.1 — Staging & deploy pipeline

**Trạng thái:** `DOING` · **Ước lượng:** 4h · **Phụ thuộc:** T3.5, T3.7
**Đã làm:** staging `staging.simpleverse.io.vn` + WordPress + theme/plugin + nội dung; deploy qua cPanel Git.
**Còn lại:** SSL tin cậy, backup/rollback staging, basic auth/noindex nếu cần.

**Mục tiêu:** deploy lặp lại được, có staging bảo vệ, có rollback.

**Việc cần làm:**

- [ ] Provision staging cùng PHP 8.4/DB với production; bật basic auth + `noindex`.
- [ ] Tạo GitHub Actions deploy workflow: build zip theme/plugin → rsync/SSH → WP-CLI.
- [ ] Quy trình deploy: backup → maintenance mode → deploy code → `wp cache flush` → `wp rewrite flush` → smoke → tắt maintenance.
- [ ] Lưu artifact 3 release gần nhất; viết lệnh rollback.
- [ ] Secrets trong GitHub Secrets, không commit.
- [ ] Deploy thử từ `main` lên staging.
- [ ] Commit: `ci: add staging deploy and rollback workflow`

**Nghiệm thu:**

- [ ] Push `main` → staging tự deploy.
- [ ] Smoke staging pass.
- [ ] Rollback về artifact trước thành công.
- [ ] Không có secret trong repo/log.
- [ ] Staging không index được.

**Test:**

```bash
git push origin main
# theo dõi Actions
curl -s -o /dev/null -w '%{http_code}\n' https://staging.example.com/
curl -sI https://staging.example.com/ | grep -i 'x-robots-tag'
```

---

## T4.2 — Full regression & reports

**Trạng thái:** `TODO` · **Ước lượng:** 3h · **Phụ thuộc:** T4.1, T3.7

**Mục tiêu:** chứng minh release đạt mọi gate bằng báo cáo có thể lưu trữ.

**Việc cần làm:**

- [ ] Chạy full E2E trên Chrome/Firefox/WebKit.
- [ ] Chạy axe 8 template × light/dark.
- [ ] Chạy Lighthouse CI + WebPageTest trên staging.
- [ ] Chạy security scan + checksum + backup restore test.
- [ ] Kiểm tra visual regression.
- [ ] Tạo `docs/reports/release-YYYYMMDD.md`: kết quả, ảnh chụp, lỗi còn lại, quyết định go/no-go.
- [ ] Commit: `docs: add release regression report`

**Nghiệm thu:**

- [ ] 0 P0/P1.
- [ ] Lighthouse đạt budget.
- [ ] axe 0 serious/critical.
- [ ] Security scan không high/critical.
- [ ] Backup restore thành công.
- [ ] Báo cáo có chữ ký/ngày.

**Test:**

```bash
npm run test:e2e
npm run test:a11y
npx lhci autorun
```

---

## T4.3 — Content launch set

**Trạng thái:** `TODO` · **Ước lượng:** 4h · **Phụ thuộc:** T2.8

**Mục tiêu:** có nội dung thật đủ để launch, không phải demo rỗng.

**Việc cần làm:**

- [ ] 4 bài launch: 1 deep dive, 2 tutorial, 1 note.
- [ ] 3 series, mỗi series ≥ 3 phần (có thể một phần là draft? Không — launch cần đủ).
- [ ] 8 page hoàn chỉnh: Home, Blog, About, Now, Uses, Contact, Privacy, Series.
- [ ] Author bio + avatar + social links.
- [ ] Featured image/OG cho từng bài.
- [ ] Internal links 3–5/bài; category/series/tags đúng.
- [ ] Editorial checklist: TL;DR, meta, excerpt, alt text, code chạy.
- [ ] Commit: `content: add launch set`

**Nghiệm thu:**

- [ ] 4 bài publish, không draft.
- [ ] 3 series hiển thị đúng.
- [ ] Pages không còn placeholder.
- [ ] Không link 404, không ảnh thiếu alt.
- [ ] Meta/OG đầy đủ.
- [ ] Đọc thử 1 bài trên mobile không lỗi.

**Test:**

```bash
ddev wp post list --post_type=post --post_status=publish --fields=ID,post_title
ddev wp term list series --fields=name,slug,count
npx lychee --no-progress 'https://staging.example.com/'
```

---

## T4.4 — DNS/CDN/Search Console/uptime

**Trạng thái:** `TODO` · **Ước lượng:** 3h · **Phụ thuộc:** T4.2

**Mục tiêu:** hạ tầng tên miền, CDN, SSL, analytics, monitoring sẵn sàng.

**Việc cần làm:**

- [ ] Trỏ domain, cấu hình Cloudflare (SSL Full Strict, HTTP/3, Brotli).
- [ ] Redirect 301 HTTP→HTTPS, `www`→apex (hoặc ngược lại, chốt một hướng).
- [ ] HSTS sau khi HTTPS ổn định.
- [ ] Search Console verify + submit sitemap.
- [ ] Umami nhận pageview; uptime monitor + alert email.
- [ ] Email giao dịch (nếu contact form) + SPF/DKIM/DMARC.
- [ ] Privacy/Contact/Legal đầy đủ.
- [ ] Commit: `chore: configure production infrastructure`

**Nghiệm thu:**

- [ ] Domain + SSL hợp lệ, không mixed content.
- [ ] `www`/HTTP redirect đúng.
- [ ] Search Console không lỗi.
- [ ] Uptime alert hoạt động.
- [ ] Analytics nhận dữ liệu.
- [ ] Email không vào spam (nếu có).

**Test:**

```bash
curl -sI http://example.com | grep -i location
curl -sI https://example.com | grep -Ei 'strict-transport|server'
dig +short example.com
```

---

## T4.5 — Go-live + 24h watch + retro

**Trạng thái:** `TODO` · **Ước lượng:** 2h · **Phụ thuộc:** T4.3, T4.4

**Mục tiêu:** đưa production lên an toàn, theo dõi 24h, ghi lại bài học.

**Việc cần làm:**

- [ ] Freeze code + tag `v1.0.0`.
- [ ] Backup production + snapshot DB trước deploy.
- [ ] Deploy production theo quy trình T4.1.
- [ ] Smoke test 10 URL + REST + 404 + feed/sitemap.
- [ ] Theo dõi 24h: 5xx, 404, CWV, uptime, analytics.
- [ ] Sửa lỗi phát sinh, ghi log.
- [ ] Retro: điều gì tốt, điều gì chậm, cải tiến cho Phase 2.
- [ ] Cập nhật `docs/` + tag release.
- [ ] Commit: `chore: launch v1.0.0`

**Nghiệm thu:**

- [ ] Production 200 OK, không 5xx.
- [ ] Search Console nhận sitemap.
- [ ] Không có P0/P1 trong 24h.
- [ ] Retro + release note đã lưu.
- [ ] Backup production đã chạy.

**Test:**

```bash
for u in / /blog/ /series/ /about/ /feed/ /sitemap_index.xml /robots.txt /llms.txt; do
  printf '%-22s ' "$u"; curl -s -o /dev/null -w '%{http_code}\n' "https://example.com$u"
done
curl -s -o /dev/null -w '%{http_code}\n' https://example.com/khong-ton-tai
```
