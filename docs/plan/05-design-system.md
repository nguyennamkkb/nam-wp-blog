# 5. Design system

> Tài liệu tham chiếu · Cập nhật: 2026-11-08 · [Mục lục](../README.md) · [Backlog công việc](../tasks/README.md)


## 5.1 Brand

- **Tên tạm:** Latent.
- **Tagline:** Ghi chú thực chiến về AI, LLM và hệ thống.
- **Tính cách:** điềm tĩnh, sắc sảo, đáng tin, không phô trương.
- **Logo:** wordmark chữ “Latent” + dấu chấm accent; không icon phức tạp.

## 5.2 Tokens

**Màu — light**

| Token | Giá trị | Dùng cho |
|---|---|---|
| `base` | `#FBFAF7` | Nền chính |
| `contrast` | `#15171C` | Chữ chính |
| `accent` | `#2F5BFF` | Link, CTA, focus |
| `accent-2` | `#C9F24D` | Highlight, marker |
| `muted` | `#6E6A61` | Meta, caption |
| `surface` | `#F2EFE7` | Card, callout |
| `border` | `#E3DFD4` | Đường kẻ |
| `code-bg` | `#101318` | Code block |

**Màu — dark**

| Token | Giá trị |
|---|---|
| `base` | `#0C0F14` |
| `contrast` | `#F2F0E8` |
| `accent` | `#8FA8FF` |
| `accent-2` | `#D6FF62` |
| `muted` | `#9B978D` |
| `surface` | `#151A22` |
| `border` | `#272D37` |
| `code-bg` | `#05070A` |

**Typography**

| Vai trò | Font | Ghi chú |
|---|---|---|
| Display/Heading | Newsreader (variable, opsz) | Serif editorial, self-host |
| Body/UI | Inter (variable) | Self-host, subset vietnamese |
| Code | JetBrains Mono (variable) | Self-host |

Thang chữ: 0.875 / 1 / 1.125 / 1.25 / 1.5 / 1.875 / 2.5 / 3.5 / 4.5 rem, dùng `clamp()` cho display.

**Spacing:** 4px base; 8 / 12 / 16 / 24 / 32 / 48 / 64 / 96 / 128.

**Radius:** 2 / 6 / 12 / 999.

**Motion:** 120ms (micro), 200ms (UI), 320ms (page); easing `cubic-bezier(.2,.8,.2,1)`; tôn trọng `prefers-reduced-motion`.

**Layout:** content 720px; wide 1200px; TOC 240px; breakpoints 640 / 768 / 1024 / 1280.

## 5.3 Components

- Header sticky + blur, wordmark, nav, search trigger, theme toggle.
- Hero editorial: eyebrow mono, display title, deck, featured post.
- Post card: featured / standard / compact.
- Series card: số thứ tự, tên, số phần, tiến độ.
- TOC sticky + scroll spy.
- Code block: dark, copy button, language label.
- Callout: note / warning / tip.
- Author box.
- Pagination.
- Search dialog.
- 404.
- Footer.
- Skip link.

## 5.4 Responsive

- Mobile-first; 1 cột < 768px.
- 768–1023: 2 cột cho card, TOC thu gọn.
- ≥ 1024: layout 12 cột, content + aside.
- Không horizontal scroll ở 320px.
- Ảnh dùng `srcset`, `sizes`, aspect-ratio cố định.

## 5.5 Accessibility (WCAG 2.2 AA)

- Landmark đầy đủ, skip link, heading hierarchy đúng.
- Focus ring 2px accent, offset 2px; không bao giờ `outline: none` không thay thế.
- Contrast ≥ 4.5:1 cho text thường, ≥ 3:1 cho large text/UI.
- Target size ≥ 24×24px.
- Dialog: focus trap, Esc, trả focus.
- `aria-live` cho search results và copy feedback.
- Tôn trọng reduced motion; không autoplay video.
- Alt text bắt buộc cho ảnh nội dung; ảnh decorative dùng alt rỗng.

## 5.6 Performance budgets

| Metric | Budget |
|---|---|
| LCP (p75 mobile) | < 1.5s |
| INP (p75) | < 200ms |
| CLS (p75) | < 0.05 |
| HTML | < 40KB |
| CSS | < 50KB |
| JS | < 40KB |
| Fonts | < 250KB tổng |
| Requests | < 30 |
| Lighthouse mobile | ≥ 95 perf, 100 a11y/SEO/BP |

---

