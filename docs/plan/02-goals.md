# 2. Mục tiêu & thước đo

> Tài liệu tham chiếu · Cập nhật: 2026-11-08 · [Mục lục](../README.md) · [Backlog công việc](../tasks/README.md)


## 2.1 Cây mục tiêu

```
Tầm nhìn 24 tháng
└── Mục tiêu 12 tháng: trở thành reference cho AI engineering tiếng Việt
    └── North Star: Weekly Engaged Readers (WER)
        └── KR 90 ngày
            ├── Nội dung: 12 bài, 3 series
            ├── Chất lượng: chuẩn kỹ thuật + biên tập
            ├── Phân phối: SEO, RSS, cộng đồng
            └── Vận hành: ổn định, an toàn, backup
                └── Việc hằng tuần: publish → distribute → measure → improve
```

## 2.2 North Star metric

**Weekly Engaged Readers (WER)** — số độc giả có ít nhất một trong hai hành vi trong tuần:

- đọc ≥ 2 bài, hoặc
- có thời gian đọc ≥ 5 phút trên một bài deep dive.

Đo bằng Umami event + pageview; không dùng GA4.

## 2.3 KR 90 ngày

| # | Key Result | Baseline | Target | Cách đo | Tần suất |
|---|---|---|---|---|---|
| KR1 | Bài publish | 0 | 12 (4 deep dive, 6 tutorial, 2 note) | WordPress | Tuần |
| KR2 | Series hoàn chỉnh | 0 | 3 series, mỗi series ≥ 3 phần | Taxonomy `series` | Tháng |
| KR3 | Chất lượng kỹ thuật | — | Lighthouse mobile ≥ 95 perf, 100 a11y/SEO/BP trên 5 template chính | Lighthouse CI | Mỗi release |
| KR4 | Core Web Vitals | — | LCP p75 < 1.5s, INP < 200ms, CLS < 0.05 | CrUX/Search Console | Tháng |
| KR5 | Accessibility | — | 0 lỗi axe serious/critical; keyboard pass 100% luồng chính | axe + manual | Mỗi release |
| KR6 | Độc giả | 0 | 100 WER, 25% returning | Umami | Tuần |
| KR7 | Phân phối | 0 | 12 bài chia sẻ LinkedIn/Facebook, 4 cross-post có canonical | Checklist | Tuần |
| KR8 | Vận hành | — | 0 sự cố critical; backup restore test 1 lần | Log + checklist | Tháng |

> Target độc giả là **stretch**. 90 ngày đầu ưu tiên chất lượng và nhịp publish hơn con số traffic.

## 2.4 KR 12 tháng (stretch)

- 50 bài publish, 5 series flagship.
- 1.000 WER, 10.000 monthly readers.
- 3 cơ hội inbound (speaking, consulting, collaboration).
- 100% bài pillar được cập nhật ít nhất 1 lần.

## 2.5 Nhịp review

| Nhịp | Nội dung | Thời lượng |
|---|---|---|
| Thứ Hai | Chốt bài tuần, xem số liệu tuần trước | 30 phút |
| Thứ Sáu | Publish, distribute, retro 15 phút | 60 phút |
| Cuối tháng | Content audit, CWV, security, backup | 90 phút |
| Cuối quý | Chiến lược, prune/update content, roadmap | 3 giờ |

---

