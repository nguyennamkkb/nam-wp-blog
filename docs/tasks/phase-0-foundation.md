# Phase 0 — Foundation (Ngày 1)

> **Mục tiêu phase:** repo sạch, site baseline đúng, theme/plugin skeleton, CI lint.
> **Thời lượng:** ~9h · **Cổng ra:** `ddev start` OK, theme active, `composer lint` + `npm run lint` xanh.
> **Trạng thái phase:** `DOING`

---

## T0.1 — Repo & .gitignore

**Trạng thái:** `DONE` · **Ước lượng:** 1h · **Phụ thuộc:** —
**Hoàn thành:** 2026-11-08 · commit `4468735`

**Mục tiêu:** có repo Git sạch, chỉ track code/config cần thiết; WP core, secret và file runtime không bị commit.

**Việc cần làm:**

- [ ] `git init -b main`
- [ ] Tạo `.gitignore` với các nhóm: WP core, config/secret, runtime, dependency, DDEV local, test artifacts, backup, OS.
- [ ] Tạo `README.md` ngắn: mô tả dự án, yêu cầu DDEV, lệnh chạy local.
- [ ] Kiểm tra không có file > 5MB bị track.
- [ ] Commit đầu: `chore: bootstrap repository`

**`.gitignore` tối thiểu:**

```gitignore
# WordPress core
/wp-admin/
/wp-includes/
/wp-*.php
!/wp-config-sample.php
/index.php
/license.txt
/readme.html
/xmlrpc.php

# Config & secrets
/wp-config.php
/.env
/.env.*

# Runtime
/wp-content/uploads/
/wp-content/upgrade/
/wp-content/cache/
/wp-content/debug.log

# Dependencies / build
/vendor/
/node_modules/
/coverage/

# DDEV local-only
/.ddev/config.local.yaml
/.ddev/.dbimageBuild/
/.ddev/.webimageBuild/
/.ddev/.router-compose.*.yaml

# Test artifacts
/test-results/
/playwright-report/
/blob-report/

# Backups
*.sql
*.sql.gz
*.tar.gz

# OS / editor
.DS_Store
Thumbs.db
*.swp
```

**File chạm:** `.gitignore`, `README.md`

**Nghiệm thu:**

- [ ] `git status --short` rỗng sau commit.
- [ ] `git check-ignore -v wp-config.php` trả về match.
- [ ] `git check-ignore -v wp-content/uploads/2026/11/x.jpg` trả về match.
- [ ] `git ls-files wp-content/themes docs` có file (không bị ignore oan).
- [ ] Không có secret trong `git log -p`.

**Test:**

```bash
git status --short
git check-ignore -v wp-config.php
git ls-files | wc -l
```

---

## T0.2 — Site baseline config

**Trạng thái:** `READY` · **Ước lượng:** 2h · **Phụ thuộc:** T0.1

**Mục tiêu:** WordPress local có ngôn ngữ, timezone, permalink, trang và category đúng chuẩn; comments/pings đóng.

**Việc cần làm:**

- [ ] Cài language pack tiếng Việt và activate.
- [ ] Set timezone `Asia/Ho_Chi_Minh`, date format `d/m/Y`, time format `H:i`.
- [ ] Set permalink `/%postname%/`, flush rewrite.
- [ ] Set site title `Latent`, tagline `Ghi chú thực chiến về AI, LLM và hệ thống`.
- [ ] Đóng comments/pings mặc định.
- [ ] Xoá post/page/comment mẫu.
- [ ] Tạo 8 page: Home, Blog, About, Now, Uses, Contact, Privacy, Series.
- [ ] Tạo 5 category: `llm-systems`, `production-ai`, `ai-tooling`, `experiments`, `craft`.
- [ ] Set static front page = Home, posts page = Blog.
- [ ] Tạo user tác giả (hoặc cập nhật display name/bio cho admin).
- [ ] Commit: `chore: configure site baseline`

**Lệnh tham khảo:**

```bash
cd /home/namnl/Documents/dev/wp-blog

ddev wp language core install vi --activate
ddev wp option update timezone_string 'Asia/Ho_Chi_Minh'
ddev wp option update date_format 'd/m/Y'
ddev wp option update time_format 'H:i'
ddev wp option update permalink_structure '/%postname%/'
ddev wp rewrite flush --hard

ddev wp option update blogname 'Latent'
ddev wp option update blogdescription 'Ghi chú thực chiến về AI, LLM và hệ thống'
ddev wp option update default_comment_status closed
ddev wp option update default_ping_status closed

ddev wp post delete $(ddev wp post list --post_type=post,page --name=hello-world,sample-page --field=ID --format=ids) --force || true
```

Tạo page (ví dụ):

```bash
make_page() {
  ddev wp post create --post_type=page --post_status=publish \
    --post_title="$1" --post_name="$2" --porcelain
}

HOME_ID=$(make_page "Home" "home")
BLOG_ID=$(make_page "Blog" "blog")
make_page "About" "about" >/dev/null
make_page "Now" "now" >/dev/null
make_page "Uses" "uses" >/dev/null
make_page "Contact" "contact" >/dev/null
make_page "Privacy" "privacy" >/dev/null
make_page "Series" "series" >/dev/null

ddev wp option update show_on_front page
ddev wp option update page_on_front "$HOME_ID"
ddev wp option update page_for_posts "$BLOG_ID"
```

Tạo category:

```bash
for pair in "LLM Systems:llm-systems" "Production AI:production-ai" "AI Tooling:ai-tooling" "Experiments:experiments" "Craft:craft"; do
  name="${pair%%:*}"; slug="${pair##*:}"
  ddev wp term create category "$name" --slug="$slug" || true
done
```

**File chạm:** database local (không commit), `README.md` nếu cần ghi chú.

**Nghiệm thu:**

- [ ] `timezone_string` = `Asia/Ho_Chi_Minh`.
- [ ] `permalink_structure` = `/%postname%/`.
- [ ] 8 page tồn tại, `show_on_front` = `page`, `page_on_front`/`page_for_posts` đúng.
- [ ] 5 category tồn tại, slug đúng.
- [ ] `default_comment_status` = `closed`, `default_ping_status` = `closed`.
- [ ] Trang chủ 200 OK, blog 200 OK.

**Test:**

```bash
ddev wp option get timezone_string
ddev wp option get permalink_structure
ddev wp option get show_on_front
ddev wp post list --post_type=page --fields=ID,post_title,post_name,post_status
ddev wp term list category --fields=name,slug
ddev wp option get default_comment_status
curl -s -o /dev/null -w '%{http_code}\n' https://wp-blog.ddev.site/
```

---

## T0.3 — Theme skeleton

**Trạng thái:** `TODO` · **Ước lượng:** 2h · **Phụ thuộc:** T0.2

**Mục tiêu:** theme block `latent` tối thiểu được WordPress nhận, activate và render không lỗi.

**Việc cần làm:**

- [ ] Tạo `wp-content/themes/latent/` với cấu trúc tối thiểu.
- [ ] `style.css` có theme header đầy đủ (Name: Latent, Text Domain: latent, Requires PHP 8.2+, GPL).
- [ ] `theme.json` version 3 tối thiểu: layout content 720px, wide 1200px, appearanceTools.
- [ ] `functions.php`: theme supports (`post-thumbnails`, `responsive-embeds`, `html5`, `wp-block-styles`, `editor-styles`), enqueue `assets/css/theme.css` nếu tồn tại.
- [ ] `templates/index.html`: header + main + footer template parts.
- [ ] `parts/header.html`, `parts/footer.html`: markup tối thiểu.
- [ ] `assets/css/theme.css`: file rỗng có comment header.
- [ ] Activate theme.
- [ ] Commit: `feat(theme): bootstrap block theme`

**Cấu trúc tối thiểu:**

```text
wp-content/themes/latent/
├── style.css
├── theme.json
├── functions.php
├── templates/index.html
├── parts/header.html
├── parts/footer.html
└── assets/css/theme.css
```

**Nghiệm thu:**

- [ ] `ddev wp theme list` có `latent` status `active`.
- [ ] `curl -I https://wp-blog.ddev.site/` trả 200.
- [ ] Không có PHP notice/warning trong `wp-content/debug.log` (bật `WP_DEBUG` local).
- [ ] Site Editor mở được, không báo theme lỗi.
- [ ] `theme.json` parse thành công (không fallback style).

**Test:**

```bash
ddev wp theme list --fields=name,status,version
ddev wp eval 'echo wp_get_theme()->get("Name");'
curl -s -o /dev/null -w '%{http_code}\n' https://wp-blog.ddev.site/
tail -50 wp-content/debug.log 2>/dev/null || true
```

---

## T0.4 — Plugin `latent-core` skeleton

**Trạng thái:** `TODO` · **Ước lượng:** 1.5h · **Phụ thuộc:** T0.2

**Mục tiêu:** tách logic nội dung khỏi theme: taxonomy `series`, tắt comments/pings.

**Việc cần làm:**

- [ ] Tạo `wp-content/plugins/latent-core/latent-core.php` với plugin header.
- [ ] `inc/taxonomy-series.php`: register taxonomy `series` cho `post`, public, `show_in_rest`, rewrite `series`, non-hierarchical.
- [ ] `inc/comments.php`: filter `comments_open` + `pings_open` trả false; ẩn comment form.
- [ ] Activation hook: flush rewrite.
- [ ] Activate plugin.
- [ ] Commit: `feat(core): add series taxonomy and disable comments`

**Gợi ý code:**

```php
// inc/taxonomy-series.php
add_action( 'init', function () {
    register_taxonomy( 'series', [ 'post' ], [
        'labels' => [
            'name'          => __( 'Series', 'latent' ),
            'singular_name' => __( 'Series', 'latent' ),
        ],
        'public'            => true,
        'show_in_rest'      => true,
        'hierarchical'      => false,
        'rewrite'           => [ 'slug' => 'series', 'with_front' => false ],
        'show_admin_column' => true,
    ] );
} );

// inc/comments.php
add_filter( 'comments_open', '__return_false', 20 );
add_filter( 'pings_open', '__return_false', 20 );
add_filter( 'comments_array', '__return_empty_array', 10, 2 );
```

**File chạm:** `wp-content/plugins/latent-core/*`

**Nghiệm thu:**

- [ ] `ddev wp plugin list` có `latent-core` status `active`.
- [ ] `ddev wp taxonomy list --fields=name,public,show_in_rest` có `series`.
- [ ] `GET /wp-json/wp/v2/series` trả 200 JSON.
- [ ] Bài viết không hiển thị comment form; `comments_open` false.
- [ ] Không PHP notice.

**Test:**

```bash
ddev wp plugin list --fields=name,status
ddev wp taxonomy list --fields=name,public,show_in_rest
curl -s -o /dev/null -w '%{http_code}\n' https://wp-blog.ddev.site/wp-json/wp/v2/series
ddev wp eval 'var_dump(comments_open(1));'
```

---

## T0.5 — CI lint baseline

**Trạng thái:** `TODO` · **Ước lượng:** 2.5h · **Phụ thuộc:** T0.1, T0.3, T0.4

**Mục tiêu:** mọi commit được kiểm tra tự động: PHP standards, static analysis, JS/CSS lint, theme.json schema.

**Việc cần làm:**

- [ ] `composer.json` với dev tools: PHPCS + WPCS, PHPStan + phpstan-wordpress, PHPUnit + WP test suite.
- [ ] `phpcs.xml.dist`: standard WordPress, paths theme/plugin, exclude vendor/node_modules.
- [ ] `phpstan.neon.dist`: level 5+, include WP extension, paths theme/plugin.
- [ ] `package.json`: ESLint + `@wordpress/eslint-plugin`, Stylelint + `@wordpress/stylelint-config`, scripts `lint:js`, `lint:css`.
- [ ] `.eslintrc.json`, `stylelint.config.js`.
- [ ] `.github/workflows/ci.yml`: checkout, PHP 8.4, Node 24, composer install, npm ci, lint jobs.
- [ ] Chạy local toàn bộ lint, sửa lỗi nếu có.
- [ ] Commit: `ci: add lint and static analysis pipeline`

**Lệnh local:**

```bash
composer install
composer lint
composer analyse
npm ci
npm run lint:js
npm run lint:css
```

**File chạm:** `composer.json`, `phpcs.xml.dist`, `phpstan.neon.dist`, `package.json`, `.eslintrc.json`, `stylelint.config.js`, `.github/workflows/ci.yml`

**Nghiệm thu:**

- [ ] `composer lint` exit 0.
- [ ] `composer analyse` exit 0 (hoặc baseline có ghi chú cho lỗi chấp nhận).
- [ ] `npm run lint:js` + `npm run lint:css` exit 0.
- [ ] CI chạy xanh trên push đầu tiên.
- [ ] Không có warning “no files found” do sai path.

**Test:**

```bash
composer lint && composer analyse
npm run lint:js && npm run lint:css
git push -u origin main
# kiểm tra GitHub Actions
```

**Ghi chú:** nếu version PHPUnit/WP test suite chưa tương thích PHP 8.4, chọn bộ version tương thích và ghi lại trong `docs/plan/04-architecture.md` (mục môi trường) — không bỏ qua static analysis.
