# 1-2. Mục tiêu & chiến lược kiểm thử

> Tài liệu tham chiếu · Cập nhật: 2026-11-08 · [Mục lục](../README.md) · [Backlog công việc](../tasks/README.md)


1. Bảo vệ 5 luồng quan trọng nhất: **đọc bài, mục lục, series, tìm kiếm, dark mode**.
2. Đảm bảo chuẩn **WCAG 2.2 AA**, **Core Web Vitals**, **SEO**, **bảo mật**.
3. Phát hiện regression sớm bằng tự động hóa, giảm kiểm tra thủ công lặp lại.
4. Mỗi release có bằng chứng: CI xanh, báo cáo Lighthouse/axe, checklist đã ký.

**Không nằm trong phạm vi MVP:** comments, membership, checkout, đa ngôn ngữ, app mobile.

---

# 2. Chiến lược kiểm thử

## 2.1 Test pyramid

| Tầng | Tỷ trọng | Công cụ | Chạy khi |
|---|---|---|---|
| Static analysis | Nền tảng | PHPCS, PHPStan, ESLint, Stylelint, schema validator | Mỗi commit |
| Unit | ~30% | PHPUnit + WP test suite | Mỗi commit |
| Integration | ~20% | WP-CLI, REST API tests | Mỗi PR |
| E2E | ~30% | Playwright | Mỗi PR + nightly |
| Visual regression | ~10% | Playwright screenshots | Mỗi PR (theme changes) |
| Manual/exploratory | ~10% | Checklist + axe DevTools | Trước release |

## 2.2 Nguyên tắc

- **Shift-left:** lint/unit chạy trước, không đợi đến staging.
- **Risk-based:** ưu tiên test nơi lỗi gây mất nội dung/SEO/khả năng đọc.
- **Test hành vi, không test implementation:** assert trên DOM/URL/HTTP status, không phụ thuộc class nội bộ.
- **Dữ liệu xác định:** fixture cố định, seed idempotent, không phụ thuộc thời gian thực.
- **Mọi bug P0/P1 phải có test tái hiện** trước khi fix.

---

