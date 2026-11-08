# 5. Test cases chức năng (E2E)

> Tài liệu tham chiếu · Cập nhật: 2026-11-08 · [Mục lục](../README.md) · [Backlog công việc](../tasks/README.md)


## E2E-01 — Home render đầy đủ

- Mở `/`.
- Assert: hero, featured post, latest grid, series section, footer.
- Không console error, không request 4xx/5xx (trừ analytics bị chặn).
- Không horizontal scroll ở 320px.

## E2E-02 — Điều hướng bài viết

- Click card đầu tiên → đúng URL/slug, đúng title.
- Back → giữ vị trí scroll hợp lý.
- Breadcrumb (nếu có) đúng.

## E2E-03 — TOC

- Mở bài có 10+ headings.
- Assert: số link TOC = số H2/H3 hợp lệ.
- Click link → URL hash đúng, heading scroll vào viewport.
- Scroll spy đổi trạng thái active.
- Heading tiếng Việt có dấu → ID đúng, link không 404.
- Heading trùng → ID có suffix `-2`, không đè.
- Mobile: TOC mở/đóng được, keyboard được.

## E2E-04 — Series

- Bài thuộc series → hiển thị tên series, danh sách phần, phần hiện tại có `aria-current`.
- Click series archive → đúng term, đủ bài, đúng thứ tự.
- Series 1 phần không hiển thị nav thừa.
- Trang `/series/` liệt kê đủ 3 series + số phần.

## E2E-05 — Search command palette

- Mở bằng nút header và `Ctrl/Cmd + K`.
- Gõ < 2 ký tự → không gọi API.
- Gõ "rag" → có kết quả, đúng title/URL.
- ↑/↓ di chuyển, Enter mở, Esc đóng, focus trả về nút mở.
- Query không có kết quả → empty state rõ ràng.
- Query chứa `<script>alert(1)</script>` → không thực thi, không render HTML.
- Mất mạng/API lỗi → thông báo lỗi thân thiện.
- JS off → `/search/` vẫn hoạt động.

## E2E-06 — Dark mode

- Lần đầu: theo `prefers-color-scheme`.
- Toggle light/dark → đổi ngay, không reload.
- Reload → giữ lựa chọn.
- Không flash sai màu (kiểm tra `data-theme` được set trước paint).
- Tất cả template đổi đúng, không còn mảng trắng/đen sót.
- Contrast pass ở cả hai chế độ.

## E2E-07 — Code block

- Có label ngôn ngữ, nút Copy.
- Copy đúng nội dung, kể cả `<`, `>`, `&`, emoji.
- Feedback “Đã copy” hiển thị và được `aria-live` đọc.
- Code không tràn ngang trên mobile (scroll trong block).

## E2E-08 — Pagination

- Trang blog > 10 bài → có phân trang.
- Trang 2 đúng bài, prev/next đúng.
- Trang vượt số trang → HTTP 404 (không phải 200 rỗng).

## E2E-09 — Trang tĩnh

- About/Now/Uses/Contact/Privacy render đúng.
- Contact có email/social link hoạt động (mailto không test gửi).
- Không lộ thông tin nhạy cảm.

## E2E-10 — 404

- URL không tồn tại → HTTP 404.
- Có thông điệp + link về Home/Blog/Search.
- Không lỗi PHP/stack trace.

## E2E-11 — RSS / sitemap / robots

- `/feed/` → 200, XML hợp lệ, có 10 item gần nhất.
- `/sitemap_index.xml` → 200, XML hợp lệ, có post/page/series.
- `/robots.txt` → 200, trỏ sitemap đúng.
- `/llms.txt` → 200 (nếu bật).

## E2E-12 — Meta/OG

- Mỗi template có `<title>`, meta description, canonical.
- Bài viết có `og:title`, `og:description`, `og:image` (1200×630), `twitter:card`.
- Không có canonical sai/trùng.

## E2E-13 — Responsive

- 320 / 390 / 768 / 1024 / 1440.
- Không horizontal scroll.
- Header/menu không che nội dung.
- TOC/mobile collapsible hoạt động.
- Tap target ≥ 24px.

## E2E-14 — Reduced motion

- Bật `prefers-reduced-motion: reduce`.
- Không smooth scroll, không animation trang trí.
- Chức năng vẫn đầy đủ.

## E2E-15 — In ấn (nice-to-have)

- `@media print`: ẩn header/footer/search, hiện nội dung, link rõ ràng.

---

