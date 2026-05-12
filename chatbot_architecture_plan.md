# Báo Cáo Phân Tích & Kiến Trúc AI Chatbot BĐS — Giai Đoạn 1

---

## 1. Luồng Chatbot Hiện Tại (TRƯỚC KHI TÍCH HỢP AI)

### 1.1 FE Component — `ChatBot/index.vue`

```
User mở trang → ChatBot widget hiện góc phải màn hình
  └─ Click button robot → isOpen = true → window hiện ra
       └─ Gửi tin nhắn → sendMessage()
            ├─ Thêm user bubble vào messages[]
            ├─ isTyping = true (fake delay 1500ms)
            ├─ findResponse(text) ← keyword matching trong JS
            │    ├─ Quét từng key trong responses object
            │    ├─ Nếu text.includes(key) → trả chuỗi HTML hardcode
            │    └─ Fallback: responses.default
            └─ Thêm bot bubble vào messages[]
```

**State quản lý:**
```javascript
const messages = ref([...])      // Lịch sử chat
const isOpen = ref(false)        // Toggle window
const isTyping = ref(false)      // Typing indicator
const inputMessage = ref('')     // Input field
const showQuickQuestions = ref(true)  // 6 nút quick questions
```

**Keyword mapping hiện tại (responses object):**
```
'đăng tin'  → Hướng dẫn đăng tin
'gói tin'   → Báo giá gói tin (hardcode)
'tìm'       → Hướng dẫn tìm BĐS
'liên hệ'   → Thông tin liên hệ
'đánh giá'  → Giải thích hệ thống đánh giá
'bảo mật'   → Chính sách bảo mật
'giá'       → Link đến /dinh-gia-ai
'vay'       → Link đến /tinh-vay
'default'   → Redirect đến hotline
```

**Vấn đề hiện tại:**
- ❌ Không gọi bất kỳ API nào — hoàn toàn offline
- ❌ Responses hardcode, sai thực tế (giá gói tin khác thực tế trong DB)
- ❌ Không nhận diện ngôn ngữ tự nhiên (phải đúng từ khóa mới phản hồi)
- ❌ Không có context về user (đã đăng nhập hay chưa, role gì)
- ❌ Không có memory giữa các lần chat

---

### 1.2 Backend — `TrainChatController.php`

```php
// Hiện tại (26 dòng):
public function chat(ChatBotRequest $request)
{
    $user = Auth::guard('sanctum')->user();
    if ($user) {
        $message = $request->input('message');
        $response = "Chat về BDS: {$message}. Cần config OpenAI key.";
        return response()->json(['status' => true, 'data' => ['reply' => $response]]);
    } else {
        return response()->json(['status' => false, 'message' => "Có lỗi xảy ra"]);
    }
}
```

**Vấn đề backend:**
- ❌ Không có middleware → route `POST /api/chatbot` PUBLIC, ai cũng gọi được
- ❌ `$user` check nhưng ChatBotRequest không require auth → khi không có token thì `$user = null` → trả lỗi
- ❌ Response chỉ có `data.reply` (string) — không có cấu trúc phong phú
- ❌ Không proxy sang Python service nào cả
- ❌ Không lưu lịch sử chat

---

### 1.3 Route Hiện Tại

```php
// api.php dòng 350-351:
Route::post('/chatbot', [TrainChatController::class, 'chat']); // chưa làm
// → KHÔNG có middleware → public route
// → FE hiện tại KHÔNG gọi route này
```

---

## 2. Kiến Trúc AI Chatbot Đề Xuất

### 2.1 Sơ Đồ Luồng Tổng Quan

```
┌─────────────────────────────────────────────────────────┐
│                    VUE 3 FRONTEND                        │
│                                                          │
│  ChatBot/index.vue                                       │
│  ├─ User gửi message                                     │
│  ├─ Đọc token từ localStorage (khach-hang / moi-gioi)   │
│  ├─ POST /api/chatbot                                    │
│  │   body: { message, session_id, role }                 │
│  └─ Nhận response → render bubble                        │
└──────────────────────────┬──────────────────────────────┘
                           │ HTTP (Bearer Token)
                           ▼
┌─────────────────────────────────────────────────────────┐
│                 LARAVEL BACKEND (:8000)                  │
│                                                          │
│  Route: POST /api/chatbot                                │
│  Controller: TrainChatController@chat                    │
│  ├─ Validate request (message required)                  │
│  ├─ Auth: cho phép cả guest lẫn logged-in                │
│  ├─ Xác định role (khach_hang / moi_gioi / guest)        │
│  ├─ Lấy thông tin user (nếu đăng nhập)                   │
│  ├─ Proxy HTTP POST → Python Service                     │
│  │   body: { message, session_id, role, user_context }   │
│  ├─ Nhận response từ Python                              │
│  └─ Forward về FE (chuẩn hóa format)                    │
└──────────────────────────┬──────────────────────────────┘
                           │ HTTP Internal (localhost)
                           ▼
┌─────────────────────────────────────────────────────────┐
│              PYTHON FLASK SERVICE (:5002)                │
│                   Chatbot/app_bds.py                     │
│                                                          │
│  POST /chat                                              │
│  ├─ Nhận message + session_id + role + user_context      │
│  ├─ Stage 1: Gemini phân tích intent + entities          │
│  │   → { intent, loai_bds, khu_vuc, khoang_gia, ... }   │
│  ├─ Query MySQL BĐS DB trực tiếp                         │
│  ├─ Xử lý theo intent:                                   │
│  │   ├─ 'tim_bds'      → searchBDS()                     │
│  │   ├─ 'xem_chi_tiet' → getBDSDetail()                  │
│  │   ├─ 'goi_y'        → recommendBDS()                  │
│  │   ├─ 'dinh_gia'     → call /api/ai/dinh-gia           │
│  │   ├─ 'lich_hen'     → hướng dẫn đặt lịch             │
│  │   ├─ 'goi_tin'      → tư vấn gói tin                  │
│  │   └─ 'chung'        → general Q&A                     │
│  ├─ Stage 2: Gemini format response thành text đẹp       │
│  └─ Return JSON                                          │
└─────────────────────────────────────────────────────────┘
```

### 2.2 Session Handling

```
Session ID = role + "_" + localStorage_unique_key
Ví dụ:
  - Khách hàng chưa đăng nhập: "guest_<uuid>"
  - Khách hàng đã đăng nhập:   "kh_<khach_hang_id>"
  - Môi giới đã đăng nhập:     "mg_<moi_gioi_id>"

→ Python service dùng session_id làm key lưu conversation history trong memory
→ Không cần DB tables mới cho session (tránh migration)
```

---

## 3. JSON API Contract

### 3.1 FE → Laravel (Request)

```json
POST /api/chatbot
Content-Type: application/json
Authorization: Bearer <token> (optional - nếu đã đăng nhập)

{
  "message": "Tôi muốn tìm căn hộ ở Đà Nẵng khoảng 2 tỷ",
  "session_id": "kh_42",
  "role": "khach-hang"
}
```

**Validation rules (ChatBotRequest hiện tại - GIỮ NGUYÊN, chỉ thêm):**
```
message:    required|string|max:1000  ← GIỮ NGUYÊN
session_id: required|string|max:100  ← THÊM
role:       nullable|string|in:khach-hang,moi-gioi,guest ← THÊM
```

### 3.2 Laravel → Python (Internal Request)

```json
POST http://localhost:5002/chat
Content-Type: application/json

{
  "message": "Tôi muốn tìm căn hộ ở Đà Nẵng khoảng 2 tỷ",
  "session_id": "kh_42",
  "role": "khach-hang",
  "user_context": {
    "user_id": 42,
    "ten": "Nguyễn Văn A",
    "is_logged_in": true,
    "yeu_thich_ids": [5, 12, 38]
  }
}
```

### 3.3 Python → Laravel (Response)

```json
{
  "success": true,
  "response": "Tôi tìm thấy **3 căn hộ** phù hợp ở Đà Nẵng...",
  "intent": "tim_bds",
  "context": "search",
  "suggestions": [
    {
      "id": 15,
      "tieu_de": "Căn hộ 2PN The Sun Tower",
      "gia": 1850000000,
      "dien_tich": 65.5,
      "anh_dai_dien_url": "http://...",
      "dia_chi": "Quận Sơn Trà, Đà Nẵng",
      "url": "/khach-hang/chi-tiet-bat-dong-san/15"
    }
  ],
  "quick_replies": [
    "Xem thêm căn hộ",
    "Tìm nhà phố thay thế",
    "Đặt lịch xem nhà"
  ],
  "processing_time": 1.23
}
```

### 3.4 Laravel → FE (Final Response)

```json
{
  "status": true,
  "data": {
    "reply": "Tôi tìm thấy **3 căn hộ** phù hợp ở Đà Nẵng...",
    "intent": "tim_bds",
    "suggestions": [...],
    "quick_replies": ["Xem thêm căn hộ", "Tìm nhà phố thay thế"],
    "is_markdown": true
  }
}
```

> **Lý do giữ `status` + `data` wrapper**: Đây là convention của toàn bộ hệ thống hiện tại. Không thay đổi.

---

## 4. Phân Tích Điểm Thay Đổi Tối Thiểu

### 4.1 Thứ tự ưu tiên thay đổi

```
① Tạo Python Flask service mới (app_bds.py)  ← File hoàn toàn mới
② Cập nhật TrainChatController               ← Sửa tối thiểu (~50 dòng)
③ Cập nhật ChatBotRequest                    ← Thêm 2 fields validation
④ Cập nhật ChatBot/index.vue                 ← Thay findResponse() bằng API call
⑤ Thêm CHATBOT_URL vào .env BE              ← 1 dòng config
```

### 4.2 File KHÔNG ĐƯỢC ĐỘNG ĐẾN

```
✅ api.php              → Route đã đúng, không cần sửa
✅ app.py (phim)        → File riêng biệt, không liên quan
✅ AIDinhGiaController  → Đang chạy tốt
✅ ChatController       → Chat người-người riêng biệt
✅ Tất cả migrations    → Không cần migration mới ở giai đoạn 1
✅ auth.js              → Không cần sửa
✅ axios/config.js      → Không cần sửa
✅ router/index.js      → Không cần sửa
```

---

## 5. Danh Sách File Cần Tạo Mới

| # | File | Mục đích |
|---|---|---|
| 1 | `Chatbot/app_bds.py` | Flask service AI BĐS (port 5002) |
| 2 | `Chatbot/.env.bds` | Config riêng cho BĐS service |
| 3 | `Chatbot/requirements_bds.txt` | Dependencies Python (tách riêng) |

---

## 6. Danh Sách File Cần Sửa Tối Thiểu

| # | File | Thay đổi | Mức độ |
|---|---|---|---|
| 1 | `BE/app/Http/Controllers/TrainChatController.php` | Thêm HTTP proxy call tới Python | 🟡 Trung bình |
| 2 | `BE/app/Http/Requests/ChatBotRequest.php` | Thêm `session_id`, `role` vào rules | 🟢 Nhỏ |
| 3 | `BE/.env` | Thêm `CHATBOT_BDS_URL=http://localhost:5002` | 🟢 Nhỏ |
| 4 | `FE/src/components/KhachHang/ChatBot/index.vue` | Thay `findResponse()` bằng `callAPI()` | 🟡 Trung bình |

---

## 7. Chi Tiết Từng Thay Đổi

### 7.1 `TrainChatController.php` — Logic mới

```
Hiện tại:
  → Trả hardcode string

Sau khi sửa:
  1. Validate request (ChatBotRequest)
  2. Guard check (cho phép cả guest)
  3. Xác định role từ token
  4. Build user_context (nếu đăng nhập: lấy id, ten, yeu_thichs)
  5. Gọi HTTP POST → CHATBOT_BDS_URL/chat (timeout 30s)
  6. Nếu Python lỗi → fallback response thân thiện
  7. Return chuẩn format { status, data }
```

### 7.2 `ChatBot/index.vue` — Logic mới

```javascript
// XÓA: findResponse() function (keyword matching)
// THÊM: async callChatbotAPI(text) function

async function callChatbotAPI(text) {
  const role = getRoleFromPath()       // từ auth.js
  const token = getToken(role)         // từ auth.js
  const session_id = buildSessionId() // kh_<id> hoặc guest_<uuid>

  const res = await api.post('/chatbot', {
    message: text,
    session_id: session_id,
    role: role
  })
  return res.data.data
}

// sendMessage() gọi callChatbotAPI() thay vì findResponse()
// Render suggestions[] thành mini-card BĐS trong bubble
// Render quick_replies[] thành clickable buttons
```

### 7.3 `app_bds.py` — Cấu trúc Flask service

```python
# Port 5002 (khác port 5001 của chatbot phim)
# Database: be_bds_kltn_t6 (đọc trực tiếp)
# AI: Gemini 2.0 Flash (cùng API key)

# Classes:
class BDSDataFetcher     # Query MySQL be_bds_kltn_t6
class BDSChatbot         # Xử lý logic chat
  ├─ analyze_intent()    # Gemini phân tích
  ├─ search_bds()        # Tìm BĐS theo criteria
  ├─ recommend_bds()     # Gợi ý dựa trên session history
  ├─ format_response()   # Gemini tạo text đẹp
  └─ process_message()   # Main pipeline

# Routes:
GET  /              → health check
POST /chat          → main chatbot endpoint
GET  /loai-bds      → danh sách loại BĐS từ DB
GET  /tinh-thanh    → danh sách tỉnh thành
```

---

## 8. Error Handling & Timeout

### 8.1 Khi Python service down

```
Laravel TrainChatController:
  try {
    $response = Http::timeout(30)->post(...)
  } catch (ConnectionException $e) {
    return response()->json([
      'status' => true,
      'data' => [
        'reply' => 'Xin lỗi, trợ lý AI đang bảo trì. Bạn có thể gọi hotline 1900xxxx hoặc chat trực tiếp với môi giới.',
        'suggestions' => [],
        'quick_replies' => ['Liên hệ môi giới', 'Xem danh sách BĐS']
      ]
    ]);
  }
```

### 8.2 Khi Gemini API lỗi

```
Python app_bds.py:
  try:
    intent = analyze_with_gemini(message)
  except Exception:
    intent = fallback_keyword_analysis(message)  # Simple keyword check
    # Vẫn có thể trả về kết quả search từ DB
```

### 8.3 Timeout FE

```javascript
// ChatBot/index.vue
const CHATBOT_TIMEOUT = 30000 // 30 giây

try {
  const res = await Promise.race([
    callChatbotAPI(text),
    new Promise((_, reject) => setTimeout(() => reject(new Error('timeout')), CHATBOT_TIMEOUT))
  ])
  // render response
} catch (err) {
  // Hiện thông báo lỗi trong bubble
  messages.value.push({
    type: 'bot',
    text: '⚠️ Không thể kết nối AI. Vui lòng thử lại.',
    time: getCurrentTime()
  })
}
```

---

## 9. Role Handling

### 9.1 Kịch bản theo role

| Role | Session ID | User Context | Chatbot Behavior |
|---|---|---|---|
| Guest (chưa đăng nhập) | `guest_<uuid>` | `{ is_logged_in: false }` | Tư vấn chung, hướng dẫn đăng ký |
| KhachHang đã đăng nhập | `kh_<id>` | `{ user_id, ten, yeu_thichs }` | Cá nhân hóa theo yêu thích, gợi ý phù hợp |
| MoiGioi đã đăng nhập | `mg_<id>` | `{ user_id, ten, goi_tin, so_tin_con_lai }` | Tư vấn gói tin, hỗ trợ đăng tin |

### 9.2 Build session_id trong FE

```javascript
function buildSessionId() {
  const role = getRoleFromPath()
  const token = getToken(role)

  if (token) {
    // Đã đăng nhập - dùng prefix + một phần token để unique
    const prefix = role === 'khach-hang' ? 'kh' : 'mg'
    const userInfo = getUserInfo(role)
    return userInfo?.id ? `${prefix}_${userInfo.id}` : `${prefix}_${token.slice(-8)}`
  } else {
    // Guest - tạo/lấy uuid từ localStorage
    let guestId = localStorage.getItem('chatbot_guest_id')
    if (!guestId) {
      guestId = 'guest_' + Math.random().toString(36).slice(2, 11)
      localStorage.setItem('chatbot_guest_id', guestId)
    }
    return guestId
  }
}
```

---

## 10. Rủi Ro Tích Hợp

| Rủi Ro | Mức | Giải Pháp |
|---|---|---|
| Python service chậm (Gemini API latency) | 🔴 Cao | Timeout 30s + fallback response. Typing indicator đã sẵn có |
| CORS giữa FE và Laravel | 🟢 Thấp | Không thay đổi — FE chỉ gọi Laravel, không gọi Python trực tiếp |
| Conflict port với chatbot phim (5001) | 🟢 Thấp | Dùng port 5002 cho BĐS service |
| `ChatBotRequest` break existing | 🟢 Thấp | Thêm `nullable` cho fields mới — không break backward compat |
| `TrainChatController` đổi response format | 🟡 Trung | FE hiện tại không gọi API này → không có user bị ảnh hưởng |
| Gemini API key chung với chatbot phim | 🟢 Thấp | Cùng key dùng được — đọc từ `.env.bds` riêng |
| Memory leak Python (conversation history) | 🟡 Trung | Giới hạn history 20 messages/session, dùng dict với max size |
| Database connection pool | 🟡 Trung | Dùng `pool_size=3` (nhỏ hơn chatbot phim 10) |

---

## 11. Kế Hoạch Triển Khai An Toàn

### Bước 1 — Tạo Python Service (KHÔNG ảnh hưởng hệ thống cũ)

```
① Tạo Chatbot/app_bds.py
② Tạo Chatbot/.env.bds
③ Chạy: python app_bds.py (port 5002)
④ Test: curl -X POST http://localhost:5002/chat -d '{"message":"test","session_id":"kh_1","role":"khach-hang"}'
```

### Bước 2 — Update Laravel (thay đổi nhỏ, có fallback)

```
① Thêm CHATBOT_BDS_URL vào .env
② Sửa TrainChatController (thêm proxy logic + fallback)
③ Sửa ChatBotRequest (thêm nullable fields)
④ Test: curl -X POST /api/chatbot -d '{"message":"test","session_id":"kh_1","role":"khach-hang"}'
```

### Bước 3 — Update FE (thay findResponse bằng API call)

```
① Thêm buildSessionId(), callChatbotAPI() vào ChatBot/index.vue
② Thay sendMessage() gọi API thay vì findResponse()
③ Thêm render cho suggestions[] và quick_replies[]
④ Test end-to-end trên browser
```

### Bước 4 — Verification

```
✅ Python service: GET http://localhost:5002/ → health OK
✅ Laravel proxy: POST /api/chatbot → nhận reply từ Gemini
✅ FE: Nhập "tìm căn hộ Đà Nẵng" → hiện kết quả BĐS thật từ DB
✅ Fallback: tắt Python service → FE nhận thông báo lỗi thân thiện
✅ Các chức năng khác không bị ảnh hưởng (định giá, chat người-người, lịch hẹn)
```

---

## 12. Tóm Tắt Quyết Định Kiến Trúc

| Quyết Định | Lý Do |
|---|---|
| Python Flask riêng (không nhúng vào Laravel) | Giữ nguyên kiến trúc chatbot phim đã hoạt động |
| Port 5002 (không phải 5001) | Tránh conflict với chatbot phim đang chạy |
| Laravel làm proxy (FE không gọi thẳng Python) | Bảo mật, auth check, format chuẩn hóa, CORS không cần cấu hình thêm |
| Session ID tạo ở FE (không phải server) | Phù hợp với kiến trúc SPA hiện tại, không cần server session |
| Query DB trực tiếp (không dùng CSV) | Data BĐS thay đổi liên tục, cần realtime accuracy |
| Giữ `{ status, data }` response format | Convention nhất quán toàn hệ thống |
| Fallback khi AI lỗi | Chatbot phải hoạt động dù Python/Gemini gặp sự cố |
| `nullable` cho fields mới trong Request | Không break backward compat với code cũ |
