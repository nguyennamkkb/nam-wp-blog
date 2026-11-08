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

## Clone sang máy khác

Repo chỉ track code (theme/plugin/docs), không track WordPress core, uploads hay database.

```bash
git clone https://github.com/nguyennamkkb/nam-wp-blog.git
cd nam-wp-blog
ddev start

# Cài WordPress core (DDEV project name vẫn là wp-blog)
ddev wp core download --version=7.1.4

# Cách A: cài mới
ddev wp core install \
  --url=https://wp-blog.ddev.site \
  --title='Latent' \
  --admin_user=admin \
  --admin_password=admin \
  --admin_email=admin@example.com

# Cách B: chuyển database từ máy cũ
# (máy cũ) bash scripts/backup.sh
# copy backups/db-*.sql.gz sang máy mới rồi:
# ddev import-db --file=backups/db-....sql.gz
```

Sau đó kích hoạt theme/plugin:

```bash
ddev wp theme activate latent
ddev wp plugin activate latent-core
```

## CI workflow

Workflow GitHub Actions hiện được tạm đặt tại `ci/github-actions-ci.yml.disabled`
vì PAT dùng để push chưa có scope `workflow`. Nó không ảnh hưởng blog; chỉ là
pipeline lint/test tự động.

Để bật lại:

1. GitHub → Settings → Developer settings → Personal access tokens → thêm scope `workflow`.
2. Chạy:

```bash
mkdir -p .github/workflows
git mv ci/github-actions-ci.yml.disabled .github/workflows/ci.yml
git commit -m "ci: enable GitHub Actions workflow"
git push
```

Hoặc dùng SSH key thay PAT (SSH không bị giới hạn scope workflow).

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
| 1 — Design system & shell | ✅ Xong |
| 2 — Features | ✅ Xong |
| 3 — Hardening | ✅ Xong |
| 4 — Launch | Đang làm — staging live, SSL pending |

Staging: https://staging.simpleverse.io.vn — WordPress + theme/plugin + 8 bài demo.
SSL: Let's Encrypt HTTP-01 bị hosting reset; đang chờ BKHost/Cloudflare/DNS-01.
Sau Wave 3: T2.3 TOC · T2.4 series nav · T2.5 search · T2.6 code/callouts.
