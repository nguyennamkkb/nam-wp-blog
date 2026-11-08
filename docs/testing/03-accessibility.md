# 6. Accessibility

> Tài liệu tham chiếu · Cập nhật: 2026-11-08 · [Mục lục](../README.md) · [Backlog công việc](../tasks/README.md)


## A11Y-01 — Automated axe

- Chạy `@axe-core/playwright` trên 8 template × light/dark × mobile/desktop.
- Ngưỡng: **0 serious, 0 critical**.
- Minor/moderate phải có ticket hoặc fix.

## A11Y-02 — Keyboard

- Tab qua toàn trang: skip link → header → nav → search → content → footer.
- Không bẫy focus ngoài dialog.
- Search dialog: Esc đóng, focus trả về trigger.
- TOC, pagination, theme toggle hoạt động bằng Enter/Space.
- Focus ring hiển thị rõ trên mọi nền.

## A11Y-03 — Cấu trúc

- Một `<h1>`/trang.
- Heading không nhảy cấp.
- Landmark: header/nav/main/footer đầy đủ.
- Link có text mô tả, không “click here”.

## A11Y-04 — Contrast

- Text thường ≥ 4.5:1; large text/UI ≥ 3:1.
- Kiểm tra tự động + spot-check bằng công cụ.

## A11Y-05 — Screen reader (manual)

- VoiceOver (macOS) hoặc NVDA (Windows) trước launch.
- Đọc đúng thứ tự, TOC, search results, copy feedback.
- Ảnh có alt phù hợp; decorative alt rỗng.

## A11Y-06 — Target size

- Mọi control ≥ 24×24px (WCAG 2.2).
- Mobile menu/search/theme toggle dễ bấm.

---

