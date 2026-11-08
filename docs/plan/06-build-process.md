# 6. Quy trình xây dựng

> Tài liệu tham chiếu · Cập nhật: 2026-11-08 · [Mục lục](../README.md) · [Backlog công việc](../tasks/README.md)


## 6.1 Các phase

| Phase | Mục tiêu | Deliverables | Exit criteria | Thời lượng |
|---|---|---|---|---|
| **0. Foundation** | Nền móng sạch | Git, .gitignore, settings, theme/plugin skeleton, CI lint | `ddev start` OK, theme activate, CI xanh | 0.5 ngày |
| **1. Design system + shell** | Bộ khung thiết kế | theme.json, fonts, header/footer, home, single, archive, dark mode | Templates render với fixture, visual QA 3 breakpoints | 2 ngày |
| **2. Content features** | Tính năng cốt lõi | Series, TOC, reading time, search, code copy, related, author, 404 | E2E feature pass, keyboard pass | 2.5 ngày |
| **3. Hardening** | Đạt chuẩn production | SEO/schema/OG/RSS/llms.txt, perf, a11y, security, analytics | Lighthouse/axe/security checklist đạt | 2 ngày |
| **4. Launch** | Đưa lên production | Staging, smoke test, DNS/CDN, monitoring, backup | Launch checklist 100% | 1 ngày |
| **Buffer** | Sửa phát sinh | — | — | 2–3 ngày |

**Tổng:** 10 ngày làm việc tập trung, dự trù 2–3 tuần lịch.

## 6.2 Lịch 10 ngày

| Ngày | Việc chính | Đầu ra kiểm chứng được |
|---|---|---|
| 1 | Phase 0: Git, settings, skeleton, CI lint | Repo sạch, theme activate, lint pass |
| 2 | Tokens, fonts, CSS nền, header/footer | Trang trắng đúng typography/màu |
| 3 | Home + post card + hero | Home render với 6 bài mẫu |
| 4 | Single + page + archive + 404 | 4 template render đúng |
| 5 | Series taxonomy + series nav/list | 3 series mẫu, archive đúng |
| 6 | TOC + reading time + code block | TOC link đúng, scroll spy chạy |
| 7 | Search dialog + fallback + dark mode | Ctrl/Cmd+K, toggle, không flash |
| 8 | SEO, OG, RSS, sitemap, analytics | Validator pass |
| 9 | Test full + fix + performance | CI xanh, Lighthouse đạt |
| 10 | Staging + smoke + launch checklist | Production sẵn sàng |

## 6.3 Definition of Done

Một hạng mục chỉ được coi là xong khi:

- Code pass lint/static analysis, không có PHP notice/warning.
- Template/block render đúng ở light + dark, 3 breakpoints.
- Keyboard + axe không có lỗi serious/critical.
- Không làm vượt performance budget.
- Có test tương ứng (unit/E2E/visual) và test xanh.
- Tài liệu/checklist cập nhật.
- Không có TODO/P1 bug còn mở.

---

