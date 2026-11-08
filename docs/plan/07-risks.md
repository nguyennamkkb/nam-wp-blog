# 7. Rủi ro & giảm thiểu

> Tài liệu tham chiếu · Cập nhật: 2026-11-08 · [Mục lục](../README.md) · [Backlog công việc](../tasks/README.md)


| Rủi ro | Xác suất | Tác động | Giảm thiểu |
|---|---|---|---|
| Scope creep (thêm feature) | Cao | Trễ launch | Khóa MVP, mọi ý mới vào backlog Phase 2 |
| Block editor phức tạp | Trung bình | Trễ | Timebox; fallback shortcode cho block lỗi |
| Font tiếng Việt lỗi dấu | Trung bình | Xấu | Subset vietnamese + test chuỗi dấu |
| Dark mode không nhất quán | Trung bình | Trải nghiệm | Chỉ dùng token, visual regression |
| Plugin update phá theme | Trung bình | Downtime | Staging, pin version, test trước update |
| Search REST chậm | Thấp | UX | Debounce, giới hạn 8 kết quả, cache |
| Burnout nội dung | Cao | Blog chết | Buffer 2 bài, series, cadence tối thiểu |
| Traffic phụ thuộc Google/AI | Cao | Không ổn định | RSS, cộng đồng, newsletter Phase 2 |
| Mất dữ liệu | Thấp | Nghiêm trọng | Backup offsite + restore drill |
| Bảo mật | Thấp | Nghiêm trọng | 2FA, hardening, WAF, update staging |

---

