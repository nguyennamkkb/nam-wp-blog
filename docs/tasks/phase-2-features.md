# Phase 2 — Features (Ngày 5–7)

> **Mục tiêu phase:** series, TOC, reading time, search command palette, code block, author/related, polish.
> **Thời lượng:** ~27h · **Cổng ra:** E2E-03/04/05/07/08/10 pass; keyboard pass; không PHP notice.
> **Trạng thái phase:** `TODO`

---

## T2.1 — Series taxonomy & archive

**Trạng thái:** `DONE` · **Ước lượng:** 3h · **Phụ thuộc:** T0.4, T1.7
**Hoàn thành:** 2026-11-08 · commit `e27e37f`

**Mục tiêu:** series hoạt động như một chuyên mục nhiều phần: archive, thứ tự, điều hướng, trang tổng hợp.

**Việc cần làm:**

- [ ] Hoàn thiện labels/rewrite/admin column cho taxonomy `series` (đã register ở T0.4).
- [ ] `pre_get_posts` cho `is_tax('series')`: order `ASC`, orderby `date` (phần 1 → phần N).
- [ ] `templates/taxonomy-series.html`: term title, description, số phần, query loop inherit, pagination.
- [ ] Seed 3 series + gán post mẫu.
- [ ] Trang `Series` (page slug `series`) sẽ dùng block list ở T2.4.
- [ ] Commit: `feat(core): add series archive and ordering`

**Nghiệm thu:**

- [ ] `/series/{slug}/` trả 200, đúng term.
- [ ] Bài trong series sắp xếp tăng dần theo ngày.
- [ ] Term description hiển thị nếu có.
- [ ] REST `/wp-json/wp/v2/series` trả term + count.
- [ ] Không PHP notice.

**Test:**

```bash
ddev wp term list series --fields=term_id,name,slug,count
curl -s -o /dev/null -w '%{http_code}\n' https://wp-blog.ddev.site/series/<slug>/
ddev wp post list --post_type=post --tax_query[0][taxonomy]=series --tax_query[0][terms]=<slug> --order=ASC --orderby=date --fields=ID,post_title,post_date
```

---

## T2.2 — Block scaffolding + reading time

**Trạng thái:** `DONE` · **Ước lượng:** 3h · **Phụ thuộc:** T1.1
**Hoàn thành:** 2026-11-08 · commit `8d94e1b`

**Mục tiêu:** hạ tầng custom block không cần build React; block đầu tiên `latent/reading-time` chạy ở editor + frontend.

**Việc cần làm:**

- [ ] `inc/blocks.php`: register các block trong `blocks/*/block.json` bằng `register_block_type()`.
- [ ] Tạo `blocks/reading-time/block.json`, `index.js`, `index.asset.php`, `render.php`.
- [ ] `index.js` dùng `wp.blocks.registerBlockType`, `wp.element.createElement`, `wp.serverSideRender`; không JSX, không build.
- [ ] `render.php`: đếm từ bằng `preg_split('/\s+/u', ...)`, tốc độ 200 wpm, tối thiểu 1 phút; output `<span class="latent-reading-time">X phút đọc</span>`.
- [ ] `usesContext: ["postId"]`; fallback `get_the_ID()`.
- [ ] Enqueue editor script qua `block.json` + `index.asset.php`.
- [ ] Commit: `feat(blocks): add reading time block scaffold`

**Mẫu `block.json`:**

```json
{
  "$schema": "https://schemas.wp.org/trunk/block.json",
  "apiVersion": 3,
  "name": "latent/reading-time",
  "title": "Reading time",
  "category": "theme",
  "icon": "clock",
  "description": "Estimated reading time for the current post.",
  "textdomain": "latent",
  "usesContext": ["postId"],
  "supports": { "html": false, "multiple": false },
  "editorScript": "file:./index.js",
  "render": "file:./render.php"
}
```

**Nghiệm thu:**

- [ ] Block xuất hiện trong inserter.
- [ ] Editor hiển thị preview (ServerSideRender), không “unsupported”.
- [ ] Frontend render đúng số phút.
- [ ] Không console error, không build step.
- [ ] Bài 0 từ → “1 phút đọc”; bài dài → số phút hợp lý.

**Test:**

```bash
ddev wp eval 'echo json_encode(array_keys(WP_Block_Type_Registry::get_instance()->get_all_registered()));' | grep -o 'latent/reading-time'
curl -s https://wp-blog.ddev.site/<slug>/ | grep -o 'latent-reading-time[^<]*'
```

---

## T2.3 — TOC + heading IDs + scroll spy

**Trạng thái:** `DOING` · **Ước lượng:** 5h · **Phụ thuộc:** T2.2, T1.6

**Mục tiêu:** mục lục tự động, link đúng, scroll spy, hỗ trợ tiếng Việt và heading trùng.

**Việc cần làm:**

- [ ] `inc/toc.php`: helper slugify heading ổn định (`sanitize_title` + suffix `-2`, `-3`).
- [ ] `render_block_data` filter: gán `anchor` cho `core/heading` level 2/3 nếu chưa có; dùng cùng thứ tự với TOC.
- [ ] `blocks/toc/`: block.json, index.js, index.asset.php, render.php.
- [ ] `render.php`: parse `post_content` bằng `parse_blocks()`, duyệt đệ quy, lấy heading H2/H3, render `<nav aria-label="Mục lục">` + `<ol>`.
- [ ] Chỉ render khi ≥ 2 heading.
- [ ] `assets/js/toc.js`: IntersectionObserver, `aria-current="location"`, smooth scroll (trừ reduced motion), mobile toggle.
- [ ] Thêm TOC vào `single.html` (desktop aside, mobile top).
- [ ] Commit: `feat(blocks): add accessible table of contents`

**Nghiệm thu:**

- [ ] Số link TOC = số H2/H3 trong bài.
- [ ] Click link → đúng ID, heading vào viewport.
- [ ] Heading tiếng Việt có dấu → slug/ID đúng.
- [ ] Heading trùng → suffix, không đè ID.
- [ ] Heading trong code block không bị đưa vào TOC.
- [ ] Scroll spy đổi `aria-current` đúng.
- [ ] Mobile toggle hoạt động, keyboard được.
- [ ] Không PHP notice.

**Test:**

```bash
ddev wp eval 'echo count(parse_blocks(get_post(<ID>)->post_content));'
# Playwright E2E-03
npx playwright test tests/e2e/toc.spec.ts
```

---

## T2.4 — Series nav/list blocks

**Trạng thái:** `DOING` · **Ước lượng:** 3h · **Phụ thuộc:** T2.1, T2.2

**Mục tiêu:** người đọc thấy mình đang ở phần nào của series và khám phá series khác.

**Việc cần làm:**

- [ ] `blocks/series-nav/`: render term của post hiện tại, danh sách phần theo thứ tự ASC, phần hiện tại `aria-current="page"`, link archive.
- [ ] `blocks/series-list/`: render tất cả series có bài, số phần, link archive, bài mới nhất.
- [ ] Ẩn series-nav nếu post không thuộc series; với series 1 phần, hiển thị gọn hoặc ẩn theo thiết kế.
- [ ] Thêm `series-nav` vào `single.html`; thêm `series-list` vào page `Series`.
- [ ] Commit: `feat(blocks): add series navigation and list`

**Nghiệm thu:**

- [ ] Bài thuộc series hiển thị đúng tên + danh sách phần.
- [ ] Phần hiện tại có `aria-current`.
- [ ] Click archive đúng term.
- [ ] Trang `/series/` liệt kê đủ series + count.
- [ ] Bài không thuộc series không hiển thị block rỗng.
- [ ] Không PHP notice.

**Test:**

```bash
curl -s https://wp-blog.ddev.site/<post-slug>/ | grep -o 'aria-current="page"' | head
curl -s https://wp-blog.ddev.site/series/ | grep -o 'latent-series-list' | head
```

---

## T2.5 — Search command palette + fallback

**Trạng thái:** `DOING` · **Ước lượng:** 5h · **Phụ thuộc:** T1.4, T1.7

**Mục tiêu:** tìm kiếm nhanh bằng `Ctrl/Cmd + K`, không plugin, an toàn XSS, có fallback khi JS tắt.

**Việc cần làm:**

- [ ] `parts/search-dialog.html`: `<dialog>` với input, results list, status `aria-live`, nút đóng, hint phím.
- [ ] `inc/assets.php`: enqueue `search.js` với `defer`.
- [ ] `assets/js/search.js`: mở/đóng, shortcut, debounce 250ms, min 2 ký tự, abort request cũ.
- [ ] Gọi `/wp-json/wp/v2/search?search=...&per_page=8&_fields=id,title,url,subtype`.
- [ ] Render bằng `textContent` (không `innerHTML` với dữ liệu API).
- [ ] Keyboard: ↑/↓, Enter, Esc; focus trả về trigger; `aria-activedescendant` hoặc focus management rõ ràng.
- [ ] Trạng thái: loading, empty, error, kết quả.
- [ ] `templates/search.html`: fallback search page hoạt động không JS.
- [ ] Commit: `feat(theme): add command palette search`

**Nghiệm thu:**

- [ ] Mở bằng nút header và `Ctrl/Cmd+K`.
- [ ] < 2 ký tự không gọi API.
- [ ] Kết quả đúng title/URL; Enter mở.
- [ ] Esc đóng, focus về trigger.
- [ ] Query `<script>alert(1)</script>` không thực thi.
- [ ] API lỗi/mất mạng có thông báo.
- [ ] JS off: `/search/?q=rag` vẫn trả kết quả.
- [ ] Không console error.

**Test:**

```bash
npx playwright test tests/e2e/search.spec.ts
curl -s -o /dev/null -w '%{http_code}\n' 'https://wp-blog.ddev.site/wp-json/wp/v2/search?search=rag&per_page=8'
```

---

## T2.6 — Code block + copy + callouts

**Trạng thái:** `DOING` · **Ước lượng:** 3h · **Phụ thuộc:** T1.3

**Mục tiêu:** code block dễ đọc, copy được, không tràn mobile; callout note/warning/tip.

**Việc cần làm:**

- [ ] CSS cho `.wp-block-code`, `pre`, `code`, scroll ngang trong block, màu code token.
- [ ] `assets/js/code-copy.js`: thêm nút Copy cho mỗi `pre`, copy bằng Clipboard API + fallback, `aria-live` feedback.
- [ ] Label ngôn ngữ nếu có class `language-*`.
- [ ] `register_block_style` cho `core/group`: `note`, `warning`, `tip`; CSS tương ứng.
- [ ] Đảm bảo code block không làm vỡ layout ở 320px.
- [ ] Commit: `feat(theme): style code blocks and callouts`

**Nghiệm thu:**

- [ ] Nút Copy xuất hiện, copy đúng nội dung `<`, `>`, `&`, emoji.
- [ ] Feedback “Đã copy” + `aria-live`.
- [ ] Code dài scroll trong block, không tràn trang.
- [ ] Callout 3 loại hiển thị đúng, contrast AA.
- [ ] Không ảnh hưởng code trong editor.

**Test:**

```bash
npx playwright test tests/e2e/code.spec.ts
```

---

## T2.7 — Author box, related posts, post meta

**Trạng thái:** `TODO` · **Ước lượng:** 3h · **Phụ thuộc:** T2.2, T2.3

**Mục tiêu:** tăng tin cậy và giữ chân người đọc: author rõ ràng, bài liên quan, meta đầy đủ.

**Việc cần làm:**

- [ ] Author box cuối bài: avatar, tên, bio, link author archive; fallback nếu bio trống.
- [ ] `blocks/related-posts/`: query 3 post cùng category/tag, exclude current, order date DESC; fallback 3 bài mới nhất nếu không có.
- [ ] Post meta: category, ngày, reading time, author; link đúng.
- [ ] Thêm vào `single.html`.
- [ ] Commit: `feat(blocks): add author box and related posts`

**Nghiệm thu:**

- [ ] Author box hiển thị đúng, link archive 200.
- [ ] Related 3 bài, không chứa bài hiện tại, không trùng.
- [ ] Nếu không có related → fallback hợp lý hoặc ẩn gọn.
- [ ] Reading time + date + category đúng.
- [ ] Không PHP notice.

**Test:**

```bash
curl -s https://wp-blog.ddev.site/<slug>/ | grep -o 'latent-related-posts' | head
ddev wp eval '$p=get_post(<ID>); echo $p->post_title;'
```

---

## T2.8 — 404/search/empty states polish

**Trạng thái:** `TODO` · **Ước lượng:** 2h · **Phụ thuộc:** T2.5, T2.6

**Mục tiêu:** mọi trạng thái “không có nội dung” đều rõ ràng, có đường quay lại; pagination và focus states hoàn chỉnh.

**Việc cần làm:**

- [ ] Polish 404: thông điệp, search, link Blog/Home.
- [ ] Polish search empty/error/loading.
- [ ] Pagination: current state, prev/next, keyboard.
- [ ] Focus states cho card/link/button.
- [ ] Print stylesheet cho bài viết.
- [ ] Commit: `fix(theme): polish empty states and pagination`

**Nghiệm thu:**

- [ ] 404 trả HTTP 404 + CTA rõ.
- [ ] Search không kết quả có empty state.
- [ ] Pagination trang 2/prev/next đúng.
- [ ] Focus ring đầy đủ.
- [ ] Print preview sạch.
- [ ] Không regression visual.

**Test:**

```bash
curl -s -o /dev/null -w '%{http_code}\n' https://wp-blog.ddev.site/khong-ton-tai
npx playwright test tests/e2e/empty-states.spec.ts
```
