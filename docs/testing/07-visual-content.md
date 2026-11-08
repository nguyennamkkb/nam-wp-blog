# 10-11. Visual regression & nội dung

> Tài liệu tham chiếu · Cập nhật: 2026-11-08 · [Mục lục](../README.md) · [Backlog công việc](../tasks/README.md)

## 10. Visual regression

### VIS-01 — Baseline

- 8 template × light/dark × 390/1440 = 32 ảnh baseline.
- Component: header, post card, TOC, search dialog, code block, series card, footer.

### VIS-02 — Quy tắc

- Chạy trong Docker/Playwright để font rendering ổn định.
- Chỉ update baseline khi thay đổi có chủ đích, review diff.
- Diff > 0.1% → fail, trừ khi có label `visual-approved`.

### VIS-03 — Kiểm tra

- Không lệch layout, màu, font, spacing.
- Dark mode không sót mảng sáng/tối.
- Ảnh/placeholder đúng tỉ lệ.

---

# 11. Nội dung & block validation

## CNT-01 — Block editor

- Mở từng template trong Site Editor: không “invalid block”, không “unsupported”.
- Custom blocks hiển thị preview đúng.
- Lưu template không sinh diff rác.

## CNT-02 — Nội dung

- Không heading trống, không đoạn rỗng.
- Code block có language, không chứa secret.
- Ảnh có alt; ảnh decorative alt rỗng.
- Link nội bộ đúng, không link staging.
- Tiếng Việt hiển thị đúng dấu ở mọi font.

## CNT-03 — Editorial checklist

- TL;DR, mục tiêu bài, kết luận.
- Ít nhất 3 internal links.
- Meta description, excerpt, featured image.
- Series/category/tags đúng.
- “Cập nhật lần cuối” nếu là bài cũ.

---

