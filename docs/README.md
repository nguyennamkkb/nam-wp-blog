# Latent — Tài liệu dự án

> **Phiên bản:** 2.0 · **Cập nhật:** 2026-11-08
> **Trạng thái:** Kế hoạch đã chia nhỏ · Implementation **chưa bắt đầu**
> **Môi trường local:** DDEV · WordPress 7.1.4 · PHP 8.4 · MariaDB 11.8
> **Định hướng:** custom block theme · Editorial Light + dark mode · tiếng Việt · không comments/members

---

## 1. Bộ tài liệu gồm 3 tầng

| Tầng | Thư mục | Trả lời câu hỏi | Khi nào dùng |
|---|---|---|---|
| **Plan** | `docs/plan/` | Vì sao làm, làm cái gì, kiến trúc nào | Đọc một lần đầu, tra cứu khi thiết kế |
| **Tasks** | `docs/tasks/` | Làm theo thứ tự nào, xong khi nào | Mỗi ngày, mỗi phiên làm việc |
| **Testing** | `docs/testing/` | Chứng minh chất lượng bằng cách nào | Mỗi task, mỗi PR, mỗi release |

**Quy tắc:** Plan không thay đổi tùy tiện. Task là đơn vị thực thi. Test là bằng chứng để đóng task.

---

## 2. Cấu trúc thư mục

```text
docs/
├── README.md                     ← bạn đang ở đây
├── plan/
│   ├── 00-summary.md             Tóm tắt điều hành
│   ├── 01-vision.md              Tầm nhìn & định vị
│   ├── 02-goals.md               Mục tiêu & thước đo
│   ├── 03-content-strategy.md    Chiến lược nội dung
│   ├── 04-architecture.md        Kiến trúc kỹ thuật + ADR
│   ├── 05-design-system.md       Design tokens & components
│   ├── 06-build-process.md       Quy trình build 5 phase
│   ├── 07-risks.md               Rủi ro & giảm thiểu
│   ├── 08-operations.md          Vận hành & đo lường
│   └── 09-decisions.md           Quyết định cần chốt
├── tasks/
│   ├── README.md                 Backlog + thứ tự + trạng thái
│   ├── phase-0-foundation.md     5 task nền móng
│   ├── phase-1-design-shell.md   8 task design system + khung
│   ├── phase-2-features.md       8 task tính năng cốt lõi
│   ├── phase-3-hardening.md      7 task SEO/perf/a11y/security
│   └── phase-4-launch.md         5 task staging + launch
└── testing/
    ├── 00-strategy.md            Mục tiêu & chiến lược test
    ├── 01-environments.md        Môi trường, fixture, ma trận
    ├── 02-functional.md          E2E-01 → E2E-15
    ├── 03-accessibility.md       A11Y
    ├── 04-performance.md         PERF
    ├── 05-seo.md                 SEO
    ├── 06-security.md            SEC
    ├── 07-visual-content.md      Visual + content validation
    ├── 08-browser-ci.md          Cross-browser + CI + lịch test
    └── 09-release.md             Bug, launch checklist, DoD
```

---

## 3. Cách thực thi

1. Mở `tasks/README.md`, xác định **task kế tiếp** theo cột `Depends`.
2. Mở file phase tương ứng, đọc mục tiêu + acceptance criteria.
3. Làm đúng checklist, không mở rộng scope.
4. Chạy lệnh test ghi trong task. Nếu fail → sửa, không đánh dấu DONE.
5. Cập nhật trạng thái trong `tasks/README.md` và file task.
6. Commit theo Conventional Commits, ví dụ:
   - `chore: bootstrap repository`
   - `feat(theme): add design tokens`
   - `test(e2e): cover toc and search`
7. Chỉ chuyển task khi **Definition of Done** đạt.

---

## 4. Quy ước trạng thái

| Trạng thái | Nghĩa |
|---|---|
| `TODO` | Chưa bắt đầu, đã đủ điều kiện |
| `READY` | Task kế tiếp, được phép bắt đầu |
| `DOING` | Đang làm, chỉ một task DOING tại một thời điểm |
| `REVIEW` | Đã xong code, đang chạy test/soát |
| `DONE` | Acceptance + test pass, đã commit |
| `BLOCKED` | Có blocker, ghi rõ lý do và người cần gỡ |

**Quy tắc WIP = 1:** mỗi lúc chỉ có một task `DOING`. Nếu bị chặn, đánh `BLOCKED` và chuyển sang task không phụ thuộc.

---

## 5. Definition of Ready / Done

### Ready

- Mục tiêu rõ, phạm vi không mơ hồ.
- Dependency đã `DONE`.
- File/đường dẫn cần chạm đã biết.
- Acceptance criteria kiểm tra được.
- Test command chạy được.

### Done

- Code pass lint/static analysis.
- Acceptance criteria pass.
- Test tương ứng pass.
- Không có P0/P1 mới.
- Tài liệu/trạng thái đã cập nhật.
- Đã commit với message rõ ràng.

---

## 6. Trạng thái hiện tại

| Hạng mục | Trạng thái |
|---|---|
| Kế hoạch & chia nhỏ task | ✅ Xong |
| Phase 0 — Foundation | ✅ Xong |
| Phase 1 — Design system & shell | 🟡 Đang làm — Wave 1: T1.1/T1.2/T1.4 ✅, T1.3 running |
| Phase 2 — Features | ⬜ Chưa bắt đầu |
| Phase 3 — Hardening | ⬜ Chưa bắt đầu |
| Phase 4 — Launch | ⬜ Chưa bắt đầu |

**Đã chốt:** tên **Latent** · ngôn ngữ **tiếng Việt** · timeline **10 ngày làm việc**.

**Đang chạy:** T1.3 (Worker B) — T1.1/T1.2 (`9ffe06b`) và T1.4 (`f3bb3b2`) đã xong
**Sau Wave 1:** [T1.5 — Home/front-page](tasks/phase-1-design-shell.md#t15--homefront-page-template)
