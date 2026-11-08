# Backlog thực thi — Latent

> **Cập nhật:** 2026-11-08 · **Tổng:** 33 task · **Ước lượng:** ~103 giờ (~13 ngày làm việc)
> **Quy tắc:** WIP = 1 · task ≤ 1 ngày · chỉ chuyển khi acceptance + test pass.

---

## 1. Task kế tiếp

| | |
|---|---|
| **Đang làm** | T1.3 (Worker B) |
| **Kế tiếp** | [T1.5 — Home/front-page](phase-1-design-shell.md#t15--homefront-page-template) sau Wave 1 |
| **Phase** | 0 ✅ · Phase 1 — Design system & shell (Wave 1) |
| **Đã chốt** | Tên: Latent · Ngôn ngữ: tiếng Việt · Timeline: 10 ngày làm việc |
| **Vừa xong** | T1.4 `f3bb3b2` · T1.1+T1.2 `9ffe06b` · Phase 0: T0.1 `4468735` · T0.2 (DB) · T0.3 `c0d301b` · T0.4 `3b93d4d` · T0.5 `20e84a6` |

---

## 2. Bảng trạng thái

### Phase 0 — Foundation (~9h)

| ID | Task | Phụ thuộc | Ước lượng | Trạng thái | Kiểm chứng |
|---|---|---|---|---|---|
| T0.1 | [Repo & .gitignore](phase-0-foundation.md#t01--repo--gitignore) | — | 1h | `DONE` | `git status` sạch — commit `4468735` |
| T0.2 | [Site baseline config](phase-0-foundation.md#t02--site-baseline-config) | T0.1 | 2h | `DONE` | options/pages/categories đúng (DB local) |
| T0.3 | [Theme skeleton](phase-0-foundation.md#t03--theme-skeleton) | T0.2 | 2h | `DONE` | theme active, 200 OK — `c0d301b` |
| T0.4 | [Plugin latent-core](phase-0-foundation.md#t04--plugin-latent-core-skeleton) | T0.2 | 1.5h | `DONE` | series taxonomy + comments off — `3b93d4d` |
| T0.5 | [CI lint baseline](phase-0-foundation.md#t05--ci-lint-baseline) | T0.1, T0.3, T0.4 | 2.5h | `DONE` | PHPCS/PHPStan/ESLint/Stylelint pass — `20e84a6` |

### Phase 1 — Design system & shell (~25h)

| ID | Task | Phụ thuộc | Ước lượng | Trạng thái | Kiểm chứng |
|---|---|---|---|---|---|
| T1.1 | [theme.json tokens](phase-1-design-shell.md#t11--themejson-tokens) | T0.3 | 3h | `DONE` | palette/typography/layout áp dụng — `9ffe06b` |
| T1.2 | [Self-host fonts](phase-1-design-shell.md#t12--self-host-fonts) | T1.1 | 2h | `DONE` | 194KB, self-host, không Google Fonts — `9ffe06b` |
| T1.3 | [Global CSS & base elements](phase-1-design-shell.md#t13--global-css--base-elements) | T1.1, T1.2 | 3h | `DOING` | focus/skip link/không tràn ngang |
| T1.4 | [Header/footer parts](phase-1-design-shell.md#t14--headerfooter-parts) | T1.3 | 3h | `DONE` | nav keyboard + mobile menu — `f3bb3b2` |
| T1.5 | [Home/front-page template](phase-1-design-shell.md#t15--homefront-page-template) | T1.4 | 4h | `TODO` | hero + featured + latest |
| T1.6 | [Single template](phase-1-design-shell.md#t16--single-template) | T1.4 | 4h | `TODO` | bài viết render đúng |
| T1.7 | [Archive/page/search/404](phase-1-design-shell.md#t17--archivepagesearch404-templates) | T1.4 | 4h | `TODO` | status code + query loop |
| T1.8 | [Dark mode](phase-1-design-shell.md#t18--dark-mode) | T1.3, T1.4 | 2h | `TODO` | không flash, persist |

### Phase 2 — Features (~27h)

| ID | Task | Phụ thuộc | Ước lượng | Trạng thái | Kiểm chứng |
|---|---|---|---|---|---|
| T2.1 | [Series taxonomy & archive](phase-2-features.md#t21--series-taxonomy--archive) | T0.4, T1.7 | 3h | `TODO` | `/series/{slug}/` 200 |
| T2.2 | [Block scaffolding + reading time](phase-2-features.md#t22--block-scaffolding--reading-time) | T1.1 | 3h | `TODO` | block preview + render |
| T2.3 | [TOC + heading IDs + scroll spy](phase-2-features.md#t23--toc--heading-ids--scroll-spy) | T2.2, T1.6 | 5h | `TODO` | E2E-03 pass |
| T2.4 | [Series nav/list blocks](phase-2-features.md#t24--series-navlist-blocks) | T2.1, T2.2 | 3h | `TODO` | E2E-04 pass |
| T2.5 | [Search command palette + fallback](phase-2-features.md#t25--search-command-palette--fallback) | T1.4, T1.7 | 5h | `TODO` | E2E-05 pass |
| T2.6 | [Code block + copy + callouts](phase-2-features.md#t26--code-block--copy--callouts) | T1.3 | 3h | `TODO` | E2E-07 pass |
| T2.7 | [Author box, related, post meta](phase-2-features.md#t27--author-box-related-posts-post-meta) | T2.2, T2.3 | 3h | `TODO` | related đúng, exclude current |
| T2.8 | [404/search/empty states polish](phase-2-features.md#t28--404searchempty-states-polish) | T2.5, T2.6 | 2h | `TODO` | E2E-08/10 pass |

### Phase 3 — Hardening (~26h)

| ID | Task | Phụ thuộc | Ước lượng | Trạng thái | Kiểm chứng |
|---|---|---|---|---|---|
| T3.1 | [SEO & schema](phase-3-hardening.md#t31--seo--schema) | T2.8 | 4h | `TODO` | SEO-01/02 pass |
| T3.2 | [OG/RSS/sitemap/robots/llms.txt](phase-3-hardening.md#t32--ogrsssitemaprobotsllmstxt) | T3.1 | 3h | `TODO` | E2E-11/12 pass |
| T3.3 | [Performance](phase-3-hardening.md#t33--performance) | T3.2, T2.8 | 5h | `TODO` | PERF budgets |
| T3.4 | [Accessibility](phase-3-hardening.md#t34--accessibility) | T2.8 | 4h | `TODO` | A11Y-01/02 pass |
| T3.5 | [Security & backup](phase-3-hardening.md#t35--security--backup) | T0.5 | 3h | `TODO` | SEC-01..05 pass |
| T3.6 | [Analytics](phase-3-hardening.md#t36--analytics) | T3.1 | 2h | `TODO` | event hiển thị trong Umami |
| T3.7 | [Fixtures + full E2E/visual](phase-3-hardening.md#t37--fixtures--full-e2evisual) | T2.8, T3.3, T3.4 | 5h | `TODO` | CI E2E xanh |

### Phase 4 — Launch (~16h)

| ID | Task | Phụ thuộc | Ước lượng | Trạng thái | Kiểm chứng |
|---|---|---|---|---|---|
| T4.1 | [Staging & deploy pipeline](phase-4-launch.md#t41--staging--deploy-pipeline) | T3.5, T3.7 | 4h | `TODO` | deploy + rollback |
| T4.2 | [Full regression & reports](phase-4-launch.md#t42--full-regression--reports) | T4.1, T3.7 | 3h | `TODO` | báo cáo release pass |
| T4.3 | [Content launch set](phase-4-launch.md#t43--content-launch-set) | T2.8 | 4h | `TODO` | 4 bài + 3 series + pages |
| T4.4 | [DNS/CDN/Search Console/uptime](phase-4-launch.md#t44--dnscdnsearch-consoleuptime) | T4.2 | 3h | `TODO` | launch checklist |
| T4.5 | [Go-live + 24h watch + retro](phase-4-launch.md#t45--go-live--24h-watch--retro) | T4.3, T4.4 | 2h | `TODO` | production ổn định 24h |

---

## 3. Thứ tự thực thi

```text
T0.1 → T0.2 → T0.3 ─┬→ T0.5
        └→ T0.4 ────┘
T0.3 → T1.1 → T1.2 → T1.3 → T1.4 → T1.5 / T1.6 / T1.7 → T1.8
T0.4 → T2.1
T1.1 → T2.2 → T2.3 / T2.4
T1.4 → T2.5
T1.3 → T2.6
T2.2 → T2.7
T2.5 + T2.6 → T2.8
T2.8 → T3.1 → T3.2 → T3.3
T2.8 → T3.4
T0.5 → T3.5
T3.1 → T3.6
T2.8 + T3.3 + T3.4 → T3.7
T3.5 + T3.7 → T4.1 → T4.2 → T4.4
T2.8 → T4.3
T4.3 + T4.4 → T4.5
```

**Đường găng:** `T0.1 → T0.2 → T0.3 → T1.1 → T1.3 → T1.4 → T1.6 → T2.3 → T2.8 → T3.3 → T3.7 → T4.1 → T4.2 → T4.4 → T4.5`

---

## 4. Cách cập nhật trạng thái

Khi bắt đầu một task:

1. Đổi `READY` → `DOING` trong file này và file phase.
2. Ghi ngày bắt đầu nếu cần.
3. Commit code + test cùng nhau.

Khi kết thúc:

1. Chạy toàn bộ acceptance + test command.
2. Đổi `DOING` → `REVIEW`, tự soát checklist.
3. Đổi `REVIEW` → `DONE`, ghi commit hash ngắn.
4. Chuyển task kế tiếp sang `READY`.

Nếu bị chặn:

- Đổi sang `BLOCKED`, ghi rõ: đang chờ gì, ai cần gỡ, task nào có thể làm thay thế.
- Không để task `DOING` quá 1 ngày mà không có commit.

---

## 5. Quy tắc tách task

Tách task mới khi:

- Ước lượng > 1 ngày.
- Có ≥ 2 deliverable độc lập có thể test riêng.
- Có dependency mới chưa được mô tả.
- Phát sinh quyết định kiến trúc cần ADR.

Task mới phải có: ID, mục tiêu, dependency, file chạm, acceptance, test command, ước lượng.

---

## 6. Test references

Khi task ghi `E2E-xx`, `A11Y-xx`, `PERF-xx`, `SEO-xx`, `SEC-xx`, mở tài liệu tương ứng:

| Mã | Tài liệu |
|---|---|
| `E2E-xx` | [../testing/02-functional.md](../testing/02-functional.md) |
| `A11Y-xx` | [../testing/03-accessibility.md](../testing/03-accessibility.md) |
| `PERF-xx` | [../testing/04-performance.md](../testing/04-performance.md) |
| `SEO-xx` | [../testing/05-seo.md](../testing/05-seo.md) |
| `SEC-xx` | [../testing/06-security.md](../testing/06-security.md) |
| `VIS-xx`, `CNT-xx` | [../testing/07-visual-content.md](../testing/07-visual-content.md) |
| CI, browser, lịch test | [../testing/08-browser-ci.md](../testing/08-browser-ci.md) |
| Bug, launch, DoD | [../testing/09-release.md](../testing/09-release.md) |

---

## 7. Mẫu task

```markdown
## TXX — Tên task

**Trạng thái:** TODO · **Ước lượng:** Xh · **Phụ thuộc:** TYY

**Mục tiêu:** một câu, kết quả quan sát được.

**Việc cần làm:**
- [ ] bước 1
- [ ] bước 2

**File chạm:** `path/...`

**Nghiệm thu:**
- [ ] điều kiện 1
- [ ] điều kiện 2

**Test:** `command`
```
