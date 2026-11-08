# 15-17. Bug, launch checklist & DoD

> Tài liệu tham chiếu · Cập nhật: 2026-11-08 · [Mục lục](../README.md) · [Backlog công việc](../tasks/README.md)

## 15. Phân loại bug & điều kiện thoát

| Mức | Định nghĩa | Ví dụ |
|---|---|---|
| **P0** | Site down, mất dữ liệu, lộ bảo mật | 500 toàn site, DB corrupt, XSS |
| **P1** | Luồng chính hỏng, a11y critical, vượt budget > 20% | Không đọc được bài, search chết, TOC sai |
| **P2** | Lỗi nhỏ có workaround | Spacing lệch, thiếu meta phụ |
| **P3** | Polish | Microcopy, hover chưa mượt |

**Điều kiện thoát release:**

- 0 P0, 0 P1.
- P2 còn lại có ticket + workaround được chấp nhận.
- Toàn bộ gate CI xanh.
- Checklist launch hoàn tất.

---

# 16. Launch checklist

## Trước launch

- [ ] Freeze code, tag release.
- [ ] Backup production + snapshot DB.
- [ ] Staging sign-off (design, content, a11y, perf).
- [ ] DNS/SSL/Cloudflare cấu hình đúng.
- [ ] Xoá `noindex`, kiểm tra `robots.txt`.
- [ ] Search Console verify + submit sitemap.
- [ ] Analytics (Umami) nhận pageview.
- [ ] Uptime monitor + alert email.
- [ ] 2FA, security headers, file edit off, XML-RPC off.
- [ ] Backup schedule + restore test.
- [ ] RSS/OG/schema validate.
- [ ] 404 + redirects.
- [ ] Privacy/Contact/About đầy đủ.
- [ ] Favicon/site icon.
- [ ] Test mobile thật + Safari.

## Sau launch (24h)

- [ ] Không 5xx, không 404 bất thường.
- [ ] CWV/real user check.
- [ ] Search Console không lỗi index.
- [ ] Form/email hoạt động (nếu có).
- [ ] Rollback path đã xác nhận.

---

# 17. Definition of Done cho mỗi release

- CI xanh toàn bộ.
- Không P0/P1.
- Lighthouse + axe đạt ngưỡng.
- Visual regression không diff ngoài dự kiến.
- Backup đã chạy và restore test gần nhất còn hiệu lực.
- Tài liệu (`PROJECT-PLAN`, `TEST-PLAN`, changelog) cập nhật.
- Người chịu trách nhiệm ký checklist launch.
