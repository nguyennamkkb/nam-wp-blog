# Latent

Blog cá nhân chuyên sâu về **AI engineering**: LLM systems, RAG/agents, production AI, benchmark và kinh nghiệm thực chiến.

> Trạng thái: Phase 0 — Foundation. Theme và plugin đang được dựng theo kế hoạch trong [`docs/`](docs/README.md).

## Stack

| Thành phần | Phiên bản / lựa chọn |
|---|---|
| CMS | WordPress 7.1.4 |
| Local | DDEV, PHP 8.4, MariaDB 11.8, nginx-fpm |
| Theme | `latent` — custom block theme |
| Plugin | `latent-core` — series taxonomy, tắt comments |
| Ngôn ngữ | Tiếng Việt (timezone `Asia/Ho_Chi_Minh`) |
| Định hướng | Editorial Light + dark mode, không page builder, không comments/members |

## Yêu cầu

- Docker + DDEV
- Node.js 24+ và Composer (cho lint/test, sẽ thêm ở T0.5)

## Chạy local

```bash
cd /home/namnl/Documents/dev/wp-blog
ddev start
ddev launch
```

- Site: https://wp-blog.ddev.site
- Admin: https://wp-blog.ddev.site/wp-admin
- Tài khoản local: `admin` / `admin` (chỉ dùng cho môi trường local)

Lệnh thường dùng:

```bash
ddev describe
ddev wp plugin list
ddev wp theme list
ddev stop
```

## Cấu trúc

```text
.
├── docs/                     Kế hoạch, backlog, test plan
├── wp-content/
│   ├── themes/latent/        Custom block theme (Phase 1)
│   └── plugins/latent-core/  Site logic (Phase 0)
├── .ddev/                    Cấu hình DDEV
├── .gitignore
└── README.md
```

Chỉ **theme/plugin tự phát triển** được track trong Git. WordPress core, uploads, config và dependency được ignore.

## Tài liệu

- [`docs/README.md`](docs/README.md) — mục lục và cách thực thi
- [`docs/plan/`](docs/plan/) — tầm nhìn, mục tiêu, kiến trúc, design system
- [`docs/tasks/README.md`](docs/tasks/README.md) — 33 task, thứ tự và trạng thái
- [`docs/testing/`](docs/testing/) — test plan, E2E, a11y, performance, SEO, security

## Trạng thái

| Phase | Trạng thái |
|---|---|
| 0 — Foundation | ✅ Xong |
| 1 — Design system & shell | Đang làm — Wave 1: T1.1+T1.2 ✅, T1.3/T1.4 running |
| 2 — Features | Chưa bắt đầu |
| 3 — Hardening | Chưa bắt đầu |
| 4 — Launch | Chưa bắt đầu |

Đang chạy: T1.3 (Worker B) · T1.4 (Worker C). T1.1 + T1.2 đã xong (`9ffe06b`).
Sau Wave 1: [`T1.5 — Home/front-page`](docs/tasks/phase-1-design-shell.md#t15--homefront-page-template).
