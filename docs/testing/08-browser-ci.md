# 12-14. Cross-browser, CI & lịch test

> Tài liệu tham chiếu · Cập nhật: 2026-11-08 · [Mục lục](../README.md) · [Backlog công việc](../tasks/README.md)

## 12. Cross-browser & thiết bị

| Nền tảng | Cách test | Mức độ |
|---|---|---|
| Chrome desktop | Playwright + manual | Bắt buộc |
| Firefox desktop | Playwright | Bắt buộc |
| Safari (WebKit) | Playwright | Bắt buộc |
| Chrome Android | Device emulation + real nếu có | Bắt buộc |
| Safari iOS | Real device/ BrowserStack | Nên có trước launch |
| Màn hình 320px | Playwright | Bắt buộc |
| Dark mode hệ thống | Playwright colorScheme | Bắt buộc |

**Fallback cần test:** `backdrop-filter`, `:has()`, `view-transition`, `dialog` — phải degrade gracefully.

---

# 13. CI pipeline

```text
commit
  └── lint: PHPCS + PHPStan + ESLint + Stylelint + theme.json schema
  └── unit: PHPUnit
PR
  └── build: zip theme/plugin + checksum
  └── e2e: wp-env + fixtures + Playwright (smoke)
  └── a11y: axe trên 8 template
  └── visual: screenshot diff (theme changes)
  └── security: composer/npm audit + checksums
main
  └── deploy staging
  └── lighthouse staging
  └── smoke staging
tag v*
  └── manual approval
  └── backup production
  └── deploy production
  └── smoke production
  └── rollback nếu smoke fail
```

## 13.1 Gate bắt buộc

- Lint/unit/e2e/axe pass.
- Không P0/P1.
- Lighthouse đạt ngưỡng.
- Security audit không high/critical.
- Smoke staging pass.

## 13.2 Smoke test production

- `curl -I` các URL: `/`, `/blog/`, một bài, `/series/`, `/search/?q=rag`, `/feed/`, `/sitemap_index.xml`, URL 404 (phải 404).
- REST search trả JSON hợp lệ.
- Không có `noindex` ngoài ý muốn.
- Không lỗi 5xx trong 15 phút đầu.

---

# 14. Lịch kiểm thử

| Tần suất | Việc |
|---|---|
| Mỗi commit | Lint, unit |
| Mỗi PR | E2E smoke, axe, visual diff |
| Nightly | Full E2E cross-browser, broken links, full axe |
| Mỗi release | Full regression, Lighthouse, security, backup snapshot |
| Hằng tháng | CWV field data, security scan, content audit |
| Hằng quý | Restore drill, manual screen reader, cross-device |

---

