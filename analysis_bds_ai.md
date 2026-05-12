# Báo Cáo Phân Tích Hệ Thống BĐS — Chuẩn Bị Tích Hợp AI

## 1. Toàn Bộ Bảng Database Hiện Có (38 migrations)

### 1.1 Nhóm Địa Chỉ
| Bảng | Các cột quan trọng |
|---|---|
| `tinh_thanhs` | id, ten |
| `quan_huyens` | id, ten, tinh_id (FK) |
| `phuong_xas` | id, ten, quan_id (FK) |
| `dia_chis` | id, tinh_id, quan_id, phuong_xa_id, dia_chi_chi_tiet, **latitude**, **longitude** |

### 1.2 Nhóm Người Dùng
| Bảng | Các cột quan trọng |
|---|---|
| `admins` | id, ten, email, password, is_super, is_active |
| `khach_hangs` | id, ten, email, so_dien_thoai, password, is_active, trang_thai |
| `moi_giois` | id, ten, email, so_dien_thoai, password, avatar, mo_ta, zalo_link, is_active, goi_tin_id (FK), so_tin_con_lai, ngay_het_han_goi |
| `chuc_vus` | id, (phân cấp chức vụ admin) |
| `phan_quyens` | id, chuc_vu_id, chuc_nang_id |
| `chuc_nangs` | id, (danh sách chức năng hệ thống) |
| `sessions` | id (string), user_id, ip_address, user_agent, payload, last_activity |

### 1.3 Nhóm Bất Động Sản
| Bảng | Các cột quan trọng |
|---|---|
| `loai_bat_dong_sans` | id, ten_loai, mo_ta, is_active |
| `trang_thai_bat_dong_sans` | id, ten_trang_thai (VD: Đang bán, Đã bán, Cho thuê) |
| `bat_dong_sans` | id, tieu_de, mo_ta, **gia** (decimal 15,0), **dien_tich** (float), loai_id, trang_thai_id, moi_gioi_id, dia_chi_id, so_phong_ngu, so_phong_tam, is_duyet, is_noi_bat, **status** (draft/published), expires_at |
| `hinh_anh_bat_dong_sans` | id, bds_id, url, is_anh_dai_dien, thu_tu |

### 1.4 Nhóm Giao Dịch & Gói Tin
| Bảng | Các cột quan trọng |
|---|---|
| `goi_tins` | id, ten_goi, mo_ta, gia, so_ngay, so_luong_tin, gan_nhan_vip, uu_tien_hien_thi, trang_thai |
| `giao_dichs` | id, moi_gioi_id, goi_tin_id, so_tien, phuong_thuc, trang_thai, paid_at, ma_giao_dich, ma_sepay_txn_ref |
| `lich_su_goi_tins` | id, moi_gioi_id, goi_tin_id, giao_dich_id, ngay_bat_dau, ngay_ket_thuc, trang_thai |
| `unmatched_payments` | id, (thanh toán chưa match được) |

### 1.5 Nhóm Tương Tác
| Bảng | Các cột quan trọng |
|---|---|
| `yeu_thichs` | id, moi_gioi_id, khach_hang_id, bds_id, noi_dung, is_read |
| `thong_baos` | id, moi_gioi_id, khach_hang_id, bat_dong_san_id, tieu_de, noi_dung, trang_thai |
| `lich_hen_xem_nha` | id, bat_dong_san_id, khach_hang_id, moi_gioi_id, ngay_hen, gio_hen, ghi_chu, trang_thai (cho_xac_nhan/da_xac_nhan/hoan_thanh/huy), ly_do_huy |
| `conversations` | id, khach_hang_id, moi_gioi_id, bat_dong_san_id, last_message_id |
| `messages` | id, conversation_id, sender_id, sender_type (khach_hang/moi_gioi), content, type, is_read |

### 1.6 Nhóm AI (đã có skeleton)
| Bảng | Các cột quan trọng |
|---|---|
| `a_i_dinh_gias` | id, moi_gioi_id, dia_chi, gia_ao, gia_thap, gia_cao, trang_thai (pending/done) |
| `lich_su_dinh_gias` | id, a_i_dinh_gia_id, ket_qua |

---

## 2. Quan Hệ Giữa Các Bảng (ERD tóm tắt)

```
tinh_thanhs ─── quan_huyens ─── phuong_xas
                                     │
                                  dia_chis ◄── bat_dong_sans ──► loai_bat_dong_sans
                                                    │                   
                                                    ├──► trang_thai_bat_dong_sans
                                                    ├──► moi_giois ──► goi_tins
                                                    │         │
                                                    │         └──► giao_dichs
                                                    │         └──► lich_su_goi_tins
                                                    ├──► hinh_anh_bat_dong_sans
                                                    ├──► yeu_thichs ◄── khach_hangs
                                                    ├──► thong_baos
                                                    ├──► lich_hen_xem_nha
                                                    └──► conversations ──► messages

admins ──► chuc_vus ──► phan_quyens ──► chuc_nangs
a_i_dinh_gias ──► lich_su_dinh_gias
```

---

## 3. Phân Tích Module Đã Có

### 3.1 Authentication
- **3 guard riêng biệt**: Admin, MoiGioi, KhachHang đều dùng Laravel Sanctum
- Token lưu trong `localStorage` với key riêng: `admin_auth_token`, `moi_gioi_auth_token`, `khach_hang_auth_token`
- Middleware: `AdminMiddleware`, `MoiGioiMiddleware`, `KhachHangMiddleware`
- Hỗ trợ quên mật khẩu qua email (hash_reset)

### 3.2 Real-time
- **Laravel Reverb** (WebSocket) đang chạy
- Events: `MessageSent`, `BatDongSanMoiDang`, `BatDongSanDuocDuyet`, `BatDongSanBiTuChoi`, `BatDongSanDuocYeuThich`, `ThanhToanThanhCong`, `PropertyExpired`, `AdminNotificationEvent`
- Channels: `private-user.{id}` cho từng user

### 3.3 Thanh Toán
- Tích hợp **SePay** (webhook + return URL)
- `SePayService` đã có trong `app/Services/`
- Luồng: `createPayment` → SePay → webhook → kích hoạt gói tin

### 3.4 Bản Đồ (GIS)
- `MapController`: `getBatDongSanMap`, `getNearbyProperties`
- `dia_chis` có `latitude`, `longitude`
- Frontend: LeafletJS + OpenStreetMap

### 3.5 AI Định Giá (ĐÃ CÓ — Logic đơn giản)
- `AIDinhGiaController::predictPrice()` — **ĐANG HOẠT ĐỘNG**
- Thuật toán: Tính đơn giá TB từ BĐS cùng loại + cùng khu vực → nhân diện tích → dao động ±8-15%
- Input: `loai_id`, `tinh_id`, `quan_id`, `dien_tich`
- Output: `gia_du_doan_min/max`, `gia_trung_binh`, `don_gia`, `so_mau_tham_khao`, `danh_sach_tham_khao`
- FE: `/khach-hang/dinh-gia-ai` — component hoàn chỉnh với ApexCharts gauge

### 3.6 AI Chatbot (CHƯA LÀM — Chỉ là stub)
- `TrainChatController::chat()` — chỉ trả về placeholder string
- Route: `POST /api/chatbot` — không có middleware, không phân biệt role
- FE ChatBot component tại `/components/KhachHang/ChatBot/index.vue`:
  - **Hoạt động hoàn toàn offline** — chỉ dùng `if/else keyword matching` trong JS
  - Có UI đẹp sẵn (toggle button, typing indicator, quick questions)
  - **CHƯA kết nối API nào**

### 3.7 Chat Thực (KhachHang ↔ MoiGioi)
- `ChatController` — đã hoàn chỉnh, có WebSocket realtime
- Các bảng `conversations`, `messages` đầy đủ
- Routes cho cả KhachHang và MoiGioi

### 3.8 Lịch Hẹn Xem Nhà
- `LichHenXemNhaController` — đã đầy đủ CRUD
- Workflow: KhachHang đặt → MoiGioi xác nhận/hủy → hoàn thành
- Trang thái: `cho_xac_nhan`, `da_xac_nhan`, `hoan_thanh`, `huy`

---

## 4. Phân Tích API Quan Trọng cho AI

### 4.1 API Public (không cần auth) — nguồn data cho AI
```
GET  /api/client/bat-dong-san          → Danh sách BĐS public (paginate 6)
GET  /api/client/bat-dong-san/{id}     → Chi tiết BĐS
POST /api/client/tim-kiem              → Tìm kiếm cơ bản
POST /api/client/tim-kiem-nang-cao     → Tìm kiếm nâng cao (loai, gia, dien_tich, so_phong_ngu, sort)
GET  /api/client/loai-bat-dong-san     → Danh sách loại BĐS
GET  /api/tinh-thanh                   → Danh sách tỉnh thành
GET  /api/quan-huyen?tinh_id=X         → Danh sách quận huyện
GET  /api/loai-bds                     → Danh sách loại BĐS (endpoint khác)
GET  /api/goi-tin/data                 → Danh sách gói tin public
```

### 4.2 API AI hiện có
```
POST /api/ai/dinh-gia          → Định giá (không cần auth!)
POST /api/chatbot              → Chatbot (stub, không có middleware)
```

### 4.3 API có thể tái dụng để xây AI gợi ý
```
GET  /api/client/map/bat-dong-san      → BĐS kèm tọa độ map
GET  /api/client/map/nearby            → BĐS lân cận (theo lat/lng)
POST /api/client/tim-kiem-nang-cao     → Filter chuẩn, dùng làm AI filter
```

---

## 5. Mapping Chatbot Phim → BĐS

| Chatbot Phim | Real Estate AI | Ghi chú |
|---|---|---|
| `vietnamese_movies.csv` | `bat_dong_sans` table (DB trực tiếp) | Không cần CSV — query DB |
| `phims` | `bat_dong_sans` | id, tieu_de, mo_ta, gia, dien_tich |
| `genres` | `loai_bat_dong_sans` | Căn hộ, Nhà phố, Biệt thự, Đất nền |
| `film_type` (Lẻ/Bộ/HH) | `trang_thai_bat_dong_sans` | Đang bán / Cho thuê / Đã bán |
| `director` | `moi_giois` | Người đăng tin |
| `country` | `tinh_thanhs` / `quan_huyens` | Khu vực địa lý |
| `plot` | `mo_ta` của BĐS | Mô tả tài sản |
| `rating` | `gia/dien_tich` (đơn giá) | Không có rating người dùng |
| `combined_features` | tieu_de + mo_ta + loai + dia_chi | TF-IDF vector |
| `TF-IDF similarity` | Cosine similarity theo đặc trưng BĐS | Áp dụng được |
| `user_genre_preferences` | Không có bảng tương đương | **PHẢI TẠO MỚI** |
| `user_movie_interactions` | `yeu_thichs` (like BĐS) | Có sẵn nhưng cấu trúc khác |
| `search_history` | Không có | **PHẢI TẠO MỚI** |
| `sessions` | `sessions` Laravel (khác cấu trúc) | Không tương đương |
| `user_preference_summary` | Không có | **PHẢI TẠO MỚI** |
| `VIP context` | `goi_tin` context | Tư vấn gói tin cho MoiGioi |
| `movie chatbot` | Real estate assistant | Trả lời câu hỏi BĐS |
| `getPopularMovies()` | BĐS mới nhất + is_noi_bat | Có sẵn |
| `getSimilarMovies()` | BĐS tương tự (cùng khu vực/loại/giá) | Cần viết mới |

---

## 6. Hệ Thống Hiện Tại Có Gì

### ✅ ĐÃ SẴN SÀNG (không cần động vào)
1. **Toàn bộ CRUD BĐS** — đăng tin, duyệt, quản lý
2. **Authentication 3 role** — Admin, MoiGioi, KhachHang
3. **Chat realtime** KhachHang ↔ MoiGioi (Reverb WebSocket)
4. **Lịch hẹn xem nhà** — full workflow
5. **Yêu thích BĐS** — `yeu_thichs` table
6. **Thống kê** — dashboard Admin và MoiGioi
7. **Thanh toán SePay** — webhook, gói tin
8. **Bản đồ GIS** — LeafletJS + tọa độ
9. **AI Định giá** — logic đơn giản ĐANG HOẠT ĐỘNG tốt
10. **ChatBot FE UI** — component đẹp sẵn, chỉ cần kết nối API
11. **Tìm kiếm nâng cao** — filter đa tiêu chí
12. **Thông báo realtime** — Laravel Notifications + Reverb

### ❌ THIẾU CHO HỆ THỐNG AI
1. **AI Chatbot backend thực sự** — `TrainChatController` chỉ là stub rỗng
2. **Bảng lưu sở thích BĐS của user** — không có `user_bds_preferences`
3. **Bảng lưu lịch sử tìm kiếm** — không có `tim_kiem_lich_sus`
4. **Bảng gợi ý BĐS** — không có `goi_y_bat_dong_sans`
5. **Logic AI gợi ý BĐS** — không có recommendation engine
6. **Kết nối chatbot FE với API** — chatbot đang chạy offline hoàn toàn
7. **Context aware chatbot** — chatbot phim có 2 context (movie/vip), BĐS cần nhiều hơn
8. **CSV/vector data cho similarity** — cần export BĐS ra format phù hợp

---

## 7. Phần Có Thể Tái Sử Dụng Từ Chatbot Phim

### ✅ TÁI SỬ DỤNG ĐƯỢC (cần điều chỉnh)
| Thành phần | Điều chỉnh cần làm |
|---|---|
| `AIMovieChatbot` class structure | Đổi thành `AIBDSChatbot`, thay CSV bằng DB query |
| `_enhance_input_with_gemini()` | Giữ nguyên ý tưởng, đổi entities sang: loai_id, tinh_id, khoang_gia, dien_tich |
| `_enhance_output_with_gemini()` | Giữ nguyên, chỉ đổi context prompt |
| `process_message()` pipeline | Giữ: input→analysis→handle→output |
| Context routing (movie/vip → bds/goi_tin) | Đổi context: `search`, `recommend`, `pricing`, `appointment`, `package` |
| `_format_recommendations_to_markdown()` | Tái sử dụng, đổi fields sang BĐS |
| Flask server structure | Tái sử dụng gần như nguyên vẹn |
| CORS config | Giữ nguyên |
| `_extract_json_from_text()` | Giữ nguyên |
| Quick questions UI (FE) | Đổi nội dung sang câu hỏi BĐS |
| Typing indicator, UI bubble | Giữ nguyên |

### ❌ KHÔNG TÁI SỬ DỤNG ĐƯỢC
| Thành phần | Lý do |
|---|---|
| `InitDataToCSV` class | BĐS dùng DB Laravel trực tiếp, không cần CSV pipeline |
| `MovieRecommender` TF-IDF | BĐS có cấu trúc dữ liệu đặc trưng hơn (giá, vị trí địa lý, diện tích) → cần content-based khác |
| `UserPreferenceManager` MySQL tables | Schema khác hoàn toàn, tables chưa tồn tại trong BĐS DB |
| `VIETNAMESE_GENRES` list | Thay bằng `loai_bat_dong_sans` từ DB |
| `FILM_TYPES` list | Thay bằng `trang_thai_bat_dong_sans` |
| Movie similarity matrix (cosine) | Có thể dùng ý tưởng nhưng cần điều chỉnh features |
| `vip_system_prompt` (WOPAI) | Viết mới cho gói tin MoiGioi |
| `faq.csv` | Viết mới FAQ cho BĐS |

---

## 8. Phần Phải Viết Mới Hoàn Toàn

### 8.1 Python/Flask (Chatbot Service)
1. **`BDSRecommender` class** — Gợi ý BĐS dựa trên:
   - Vị trí địa lý (tinh, quan, phuong)
   - Khoảng giá
   - Loại BĐS
   - Diện tích
   - Số phòng ngủ/tắm
2. **`AIChatbotBDS` class** — với các context:
   - `search` — tìm BĐS theo tiêu chí
   - `recommend` — gợi ý BĐS phù hợp
   - `pricing` — hỏi về giá thị trường (kết nối AI định giá)
   - `appointment` — hướng dẫn đặt lịch xem nhà
   - `package` — tư vấn gói tin cho MoiGioi
   - `general` — câu hỏi chung về BĐS
3. **System prompts** mới hoàn toàn cho domain BĐS Đà Nẵng
4. **Database connector** — kết nối trực tiếp MySQL BĐS DB thay vì CSV
5. **API endpoint `/bds-chat`** — thay `/chat` của phim

### 8.2 Laravel BE (không sửa code hiện tại)
1. **Migration mới**: `ai_chatbot_sessions` — lưu lịch sử chat của user với chatbot AI (khác với `conversations` giữa người với người)
2. **Migration mới**: `ai_tim_kiem_lich_sus` — lưu lịch sử tìm kiếm để học sở thích
3. **`ChatBotController`** — proxy request từ FE → Python service
4. Cập nhật `TrainChatController` hoặc tạo `AIBDSChatController` mới

### 8.3 Vue FE (không sửa code hiện tại)
1. Kết nối ChatBot component với API thực (thay `findResponse()` keyword matching)
2. Thêm quick questions phù hợp với BĐS
3. Hiển thị kết quả gợi ý BĐS trong bubble chat (card mini)

---

## 9. Rủi Ro Nếu Port Trực Tiếp Logic Phim Sang BĐS

| Rủi Ro | Mức Độ | Chi Tiết |
|---|---|---|
| **Dùng CSV thay vì DB** | 🔴 Cao | Data BĐS thay đổi liên tục (thêm/xóa/hết hạn), dùng CSV sẽ stale ngay. PHẢI query DB realtime |
| **Copy UserPreferenceManager** | 🔴 Cao | Schema tables hoàn toàn khác, sẽ conflict với DB BĐS hiện tại |
| **TF-IDF thuần túy** | 🟡 Trung bình | BĐS cần filter địa lý chính xác (lat/lng) + giá, TF-IDF text không phù hợp cho numeric data |
| **Port VIP prompt sang gói tin** | 🟡 Trung bình | Logic gói tin MoiGioi phức tạp hơn VIP WOPAI nhiều (so_tin_con_lai, expires_at, SePay payment) |
| **Session management** | 🟡 Trung bình | BĐS có 3 role khác nhau, chatbot phim không có role, cần xử lý context per-role |
| **Hardcode localhost:5173** | 🟢 Thấp | Đã biết vấn đề từ phim, tránh ngay từ đầu |
| **Duplicate except blocks** | 🟢 Thấp | Bug code phim đã phát hiện, không lặp lại |
| **Connection pool naming** | 🟢 Thấp | Đổi `preference_pool` thành tên khác để tránh conflict |

---

## 10. Kế Hoạch Triển Khai Đề Xuất (chỉ phân tích, chưa làm)

### Giai đoạn 1 — Chatbot AI (ưu tiên cao nhất, route đã có)
- File: `Chatbot/app_bds.py` (file mới, không đụng `app.py` phim)
- Kết nối: BE Laravel → Python Flask qua HTTP proxy
- Route BE đã sẵn: `POST /api/chatbot`
- Cần thêm: Session ID theo role (khach_hang/moi_gioi)

### Giai đoạn 2 — AI Gợi Ý BĐS
- Gợi ý dựa trên: lịch sử xem, yêu thích, tìm kiếm
- Kết nối `yeu_thichs` hiện có làm nguồn preference data

### Giai đoạn 3 — Nâng Cấp AI Định Giá
- Hiện tại: statistical averaging đơn giản — đã hoạt động tốt
- Nâng cấp: Thêm phân tích xu hướng giá theo thời gian
- Cần thêm migration: `lich_su_gia_bds` (snapshot giá theo tháng)

---

## 11. Tóm Tắt Nhanh

```
✅ DB đủ dữ liệu để làm AI (bat_dong_sans có gia, dien_tich, loai, dia_chi)
✅ AI Định giá đang hoạt động tốt — KHÔNG CẦN SỬA
✅ ChatBot FE UI đã sẵn sàng — CHỈ CẦN nối API
✅ Routes /api/chatbot và /api/ai/dinh-gia đã có
✅ WebSocket Reverb đang chạy — có thể stream chatbot response
✅ yeu_thichs table = nguồn preference data cho gợi ý

❌ TrainChatController chỉ là stub rỗng
❌ Chatbot FE đang chạy hoàn toàn offline (keyword matching JS)
❌ Không có bảng lưu sở thích/lịch sử tìm kiếm cho AI
❌ Không có AI recommendation engine

⚠️  KHÔNG copy UserPreferenceManager từ phim
⚠️  KHÔNG dùng CSV — phải query DB trực tiếp
⚠️  KHÔNG sửa bất kỳ code nào đang chạy ổn
⚠️  File Python mới phải riêng biệt với app.py phim
```
