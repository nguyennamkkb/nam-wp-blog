# Phase 1 — Design system & shell (Ngày 2–4)

> **Mục tiêu phase:** bộ token thiết kế, font, CSS nền, header/footer, 5 template chính, dark mode.
> **Thời lượng:** ~25h · **Cổng ra:** tất cả template render với fixture; visual QA 3 breakpoints; dark mode không flash.
> **Trạng thái phase:** `TODO`

---

## T1.1 — theme.json tokens

**Trạng thái:** `TODO` · **Ước lượng:** 3h · **Phụ thuộc:** T0.3

**Mục tiêu:** toàn bộ màu, typography, spacing, layout, radius được khai báo một lần trong `theme.json`; không hardcode trong template.

**Việc cần làm:**

- [ ] `theme.json` version 3, `$schema` trỏ schema trunk.
- [ ] `settings.appearanceTools: true`.
- [ ] Color palette: `base`, `contrast`, `accent`, `accent-2`, `muted`, `surface`, `border`, `code-bg`, `code-text`.
- [ ] Font families: `newsreader`, `inter`, `jetbrains-mono` (fontFace thêm ở T1.2).
- [ ] Font sizes: `small`, `body`, `lead`, `h3`, `h2`, `h1`, `display` (dùng `fluid`).
- [ ] Spacing scale: 4px base; 8/12/16/24/32/48/64/96/128.
- [ ] Layout: content 720px, wide 1200px.
- [ ] Border radius presets: 2/6/12/999.
- [ ] Styles: body background/text, headings, link, button, code, quote, caption, separator.
- [ ] Custom tokens cho TOC width, header height, transition.
- [ ] Commit: `feat(theme): add design tokens`

**Nghiệm thu:**

- [ ] Site Editor → Styles hiển thị đúng palette/font size/spacing.
- [ ] Frontend CSS có `--wp--preset--color--accent` v.v.
- [ ] Không có lỗi schema/JSON.
- [ ] Reset user global styles nếu có để theme.json là nguồn chính.

**Test:**

```bash
ddev wp theme list --fields=name,status
curl -s https://wp-blog.ddev.site/ | grep -o -- '--wp--preset--color--accent' | head -1
# Mở Site Editor, kiểm tra palette + typography
```

---

## T1.2 — Self-host fonts

**Trạng thái:** `TODO` · **Ước lượng:** 2h · **Phụ thuộc:** T1.1

**Mục tiêu:** Newsreader, Inter, JetBrains Mono tải từ chính domain; hỗ trợ đầy đủ dấu tiếng Việt; không request Google Fonts.

**Việc cần làm:**

- [ ] Tải variable woff2 cho 3 font, subset `latin` + `vietnamese`.
- [ ] Lưu vào `assets/fonts/`.
- [ ] Khai báo `fontFace` trong `theme.json` với `fontWeight`, `fontStyle`, `fontDisplay: swap`, `unicodeRange` (nếu tách subset).
- [ ] Preload 2 file quan trọng (Inter latin, Newsreader latin) trong `inc/assets.php`.
- [ ] Kiểm tra dung lượng tổng < 250KB.
- [ ] Commit: `feat(theme): self-host variable fonts`

**Cách lấy font an toàn:**

1. Dùng Google Fonts CSS2 API với UA Chrome hiện đại, tải file woff2 cho subset `latin` + `vietnamese`.
2. Hoặc dùng package Fontsource (`@fontsource-variable/inter`, `newsreader`, `jetbrains-mono`) và copy file woff2 tương ứng.
3. Không commit file `.ttf`/`.otf` nếu không cần.

**Nghiệm thu:**

- [ ] Network tab không có request `fonts.googleapis.com` / `fonts.gstatic.com`.
- [ ] Chuỗi test `Tiếng Việt: ắằẳẵặ ĩũơưđ` hiển thị đúng ở heading + body + code.
- [ ] `font-display: swap`; không FOIT.
- [ ] Tổng font < 250KB.
- [ ] Preload chỉ 1–2 file, không preload tất cả.

**Test:**

```bash
curl -s https://wp-blog.ddev.site/ | grep -i 'fonts.googleapis' && echo FAIL || echo OK
find wp-content/themes/latent/assets/fonts -name '*.woff2' -printf '%s %p\n' | sort -n
```

---

## T1.3 — Global CSS & base elements

**Trạng thái:** `TODO` · **Ước lượng:** 3h · **Phụ thuộc:** T1.1, T1.2

**Mục tiêu:** nền CSS sạch cho toàn site: reset nhẹ, typography, link, focus, skip link, form, table, code, print.

**Việc cần làm:**

- [ ] Tạo `assets/css/theme.css`, enqueue trong `inc/assets.php` với version filemtime.
- [ ] Reset nhẹ: `box-sizing`, margin, media, `text-wrap`.
- [ ] Body: font, line-height 1.65, background/text token.
- [ ] Heading: font serif, line-height 1.1, balance.
- [ ] Link: underline offset, hover color accent.
- [ ] `:focus-visible`: ring 2px accent, offset 2px.
- [ ] Skip link: ẩn đến khi focus, nhảy tới `#main`.
- [ ] Ảnh: max-width 100%, height auto, border-radius.
- [ ] Table, blockquote, code, pre, form cơ bản.
- [ ] `@media print`: ẩn header/footer/search, hiện URL link.
- [ ] `prefers-reduced-motion`: tắt animation/smooth scroll.
- [ ] Commit: `feat(theme): add global styles and base elements`

**Nghiệm thu:**

- [ ] Tab đầu tiên hiện skip link, Enter nhảy tới main.
- [ ] Focus ring thấy rõ trên nền sáng/tối.
- [ ] Không horizontal scroll ở 320px.
- [ ] Không hardcode mã màu ngoài token.
- [ ] Print preview gọn, không header/footer.

**Test:**

```bash
# Lighthouse a11y sơ bộ
npx lighthouse https://wp-blog.ddev.site/ --only-categories=accessibility --chrome-flags='--headless'
# Kiểm tra CSS không có hex ngoài theme.json (spot-check)
grep -nE '#[0-9a-fA-F]{3,8}' wp-content/themes/latent/assets/css/theme.css | head
```

---

## T1.4 — Header/footer parts

**Trạng thái:** `TODO` · **Ước lượng:** 3h · **Phụ thuộc:** T1.3

**Mục tiêu:** header sticky, wordmark, nav, search trigger, theme toggle, mobile menu; footer đầy đủ.

**Việc cần làm:**

- [ ] `parts/header.html`: group sticky, site title/wordmark, navigation block, search button, theme toggle button.
- [ ] Navigation links: Blog `/blog/`, Series `/series/`, About `/about/`.
- [ ] Search button class `latent-search-open`, icon kính lúp, `aria-label`, hint `⌘K` (ẩn trên mobile).
- [ ] Theme toggle class `latent-theme-toggle`, `aria-pressed`, icon sun/moon.
- [ ] Mobile: nav overlay hoặc collapsible; đóng bằng Esc, focus đúng.
- [ ] `parts/footer.html`: brand, nav phụ, RSS, GitHub/LinkedIn, copyright, Privacy.
- [ ] Search dialog tạm chưa gắn (sẽ thêm ở T2.5); nút có thể `disabled` tạm thời.
- [ ] Commit: `feat(theme): add header and footer parts`

**Nghiệm thu:**

- [ ] Header sticky không che anchor heading (scroll-margin-top).
- [ ] Keyboard: skip link → logo → nav → search → toggle.
- [ ] Mobile menu mở/đóng được, không bẫy focus.
- [ ] Theme toggle có `aria-pressed` đúng.
- [ ] Footer links không 404 (trừ social placeholder).
- [ ] Không layout shift khi font tải.

**Test:**

```bash
# Playwright: tab order, mobile menu
npx playwright test tests/e2e/header.spec.ts
```

---

## T1.5 — Home/front-page template

**Trạng thái:** `TODO` · **Ước lượng:** 4h · **Phụ thuộc:** T1.4

**Mục tiêu:** trang chủ editorial: hero, featured post, latest grid, series strip, about CTA.

**Việc cần làm:**

- [ ] `templates/front-page.html`: hero 2 cột.
- [ ] Hero: eyebrow mono, H1 static, deck, CTA “Đọc bài mới nhất”.
- [ ] Featured query loop: 1 post mới nhất, card lớn.
- [ ] Latest query loop: 6 post, offset 1 (nếu Query Loop hỗ trợ), card chuẩn.
- [ ] Series strip: placeholder heading + text (thay bằng block ở T2.4).
- [ ] About CTA: link `/about/`.
- [ ] Đảm bảo chỉ một H1 trên trang.
- [ ] Commit: `feat(theme): build editorial home template`

**Nghiệm thu:**

- [ ] Home có hero + featured + 6 latest + CTA.
- [ ] Query loop đúng post, không lặp featured nếu offset hoạt động.
- [ ] Responsive: 1 cột mobile, 2–3 cột desktop.
- [ ] Chỉ một H1; heading hierarchy đúng.
- [ ] Không PHP notice.

**Test:**

```bash
curl -s https://wp-blog.ddev.site/ | grep -o '<h1[^>]*>' | wc -l
# Visual: screenshot 390/768/1440
```

---

## T1.6 — Single template

**Trạng thái:** `TODO` · **Ước lượng:** 4h · **Phụ thuộc:** T1.4

**Mục tiêu:** trang bài viết chuyên nghiệp: title, meta, featured image, content, author box, không comments.

**Việc cần làm:**

- [ ] `templates/single.html`: article wrapper, max 720px.
- [ ] `core/post-title` level 1.
- [ ] Meta: category, ngày, author (reading time thêm ở T2.2).
- [ ] `core/post-featured-image` với `aspect-ratio`, `sizes`.
- [ ] `core/post-content`.
- [ ] Author box: avatar, tên, bio, link author archive.
- [ ] Related posts placeholder (thêm ở T2.7).
- [ ] Không render comments.
- [ ] Commit: `feat(theme): build single post template`

**Nghiệm thu:**

- [ ] Bài viết render đúng title/meta/image/content/author.
- [ ] Content width 720px; ảnh không tràn.
- [ ] Không comment form.
- [ ] Heading trong content bắt đầu từ H2 (không H1 thứ hai).
- [ ] Không PHP notice.

**Test:**

```bash
ddev wp post list --post_type=post --fields=ID,post_title,post_name
curl -s https://wp-blog.ddev.site/<slug>/ | grep -c '<h1'
```

---

## T1.7 — Archive/page/search/404 templates

**Trạng thái:** `TODO` · **Ước lượng:** 4h · **Phụ thuộc:** T1.4

**Mục tiêu:** đủ template cho blog index, archive, page, search, 404, fallback.

**Việc cần làm:**

- [ ] `templates/home.html`: tiêu đề Blog + query loop inherit + pagination.
- [ ] `templates/archive.html`: `core/query-title`, `core/term-description`, query loop inherit, pagination.
- [ ] `templates/page.html`: title + content, width hẹp.
- [ ] `templates/search.html`: `core/search` + query title + query loop inherit + empty state.
- [ ] `templates/404.html`: heading, message, search, link về Blog/Home.
- [ ] `templates/index.html`: fallback query loop.
- [ ] Commit: `feat(theme): add archive page search and 404 templates`

**Nghiệm thu:**

- [ ] `/blog/`, `/category/llm-systems/`, `/about/`, `/search/?q=rag` trả 200.
- [ ] URL không tồn tại trả 404 (không phải 200).
- [ ] Search không có kết quả → empty state.
- [ ] Pagination hoạt động khi > 10 bài.
- [ ] Không PHP notice.

**Test:**

```bash
for u in / /blog/ /category/llm-systems/ /about/ '/search/?q=rag' /khong-ton-tai; do
  printf '%-30s ' "$u"
  curl -s -o /dev/null -w '%{http_code}\n' "https://wp-blog.ddev.site$u"
done
```

---

## T1.8 — Dark mode

**Trạng thái:** `TODO` · **Ước lượng:** 2h · **Phụ thuộc:** T1.3, T1.4

**Mục tiêu:** dark mode theo hệ thống, toggle thủ công, lưu lựa chọn, không flash sai màu.

**Việc cần làm:**

- [ ] CSS override token trong `[data-theme="dark"]` và fallback `@media (prefers-color-scheme: dark)` khi không có JS.
- [ ] Set `color-scheme` tương ứng.
- [ ] Inline script trong `wp_head` (trước CSS) đọc `localStorage` + `matchMedia`, set `data-theme` lên `<html>`.
- [ ] `assets/js/theme.js`: toggle, cập nhật `aria-pressed`/icon, lưu `latent-theme`, dispatch event.
- [ ] Enqueue JS với `defer`.
- [ ] Kiểm tra tất cả template ở cả 2 chế độ.
- [ ] Commit: `feat(theme): add dark mode without flash`

**Gợi ý inline script:**

```js
(function () {
  try {
    var t = localStorage.getItem('latent-theme');
    if (t !== 'light' && t !== 'dark') {
      t = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }
    document.documentElement.dataset.theme = t;
  } catch (e) {}
})();
```

**Nghiệm thu:**

- [ ] Lần đầu theo system preference.
- [ ] Toggle đổi ngay, reload vẫn giữ.
- [ ] Không flash trắng/đen khi reload (kiểm tra screenshot sớm hoặc `data-theme` trong HTML).
- [ ] Contrast AA ở cả 2 chế độ.
- [ ] Không còn mảng màu sáng/tối sót ở header/footer/code/search.

**Test:**

```bash
# Playwright: colorScheme + localStorage + screenshot
npx playwright test tests/e2e/theme.spec.ts
```
