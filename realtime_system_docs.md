# Hệ Thống Real-time & Notification — KLTN_25

Tài liệu này mô tả chính xác cách hệ thống **Real-time & Notifications** được triển khai trong project `KLTN_25`, dựa trên **Laravel Reverb** (WebSocket Server) + **Laravel Echo** (Frontend).

---

## 1. Tổng quan Kiến trúc

Hệ thống có **2 luồng thông báo** song song:

| Luồng | Mô tả | Ai nhận |
|---|---|---|
| **Luồng 1 – Broadcast Notification** | Gửi real-time qua WebSocket, hiện toast ngay lập tức | Admin, Môi giới |
| **Luồng 2 – Database ThongBao** | Lưu vào bảng `thong_baos` (custom), đọc lại qua API | Khách hàng |

> ⚠️ **Lưu ý thiết kế:** Project dùng song song 2 hệ thống.
> - `notifications` (bảng chuẩn Laravel) → dùng cho Admin & Môi giới (có broadcast).
> - `thong_baos` (bảng custom) → dùng cho Khách hàng (không broadcast, poll qua API).

### Luồng sự kiện hoàn chỉnh

```
Môi giới Đăng tin
    → event(BatDongSanMoiDang)
        → [Listener] TaoThongBaoBDSMoi
            → ThongBao::create() cho từng KhachHang  (DB cũ)
            → Admin::notify(NewPostPendingApprovalNotification)  (DB + Broadcast → Admin)

Admin Duyệt tin
    → event(BatDongSanDuocDuyet)
        → [Listener] GuiThongBaoDuyet
            → ThongBao::create() cho MoiGioi  (DB cũ)
            → MoiGioi::notify(PostApprovedNotification)  (DB + Broadcast → MoiGioi)

Admin Từ chối tin
    → event(BatDongSanBiTuChoi)
        → [Listener] GuiThongBaoTuChoi
            → ThongBao::create() cho MoiGioi  (DB cũ)
            → MoiGioi::notify(PostRejectedNotification)  (DB + Broadcast → MoiGioi)

Khách hàng Yêu thích BĐS
    → event(BatDongSanDuocYeuThich)
        → [Listener] TaoThongBaoKhiYeuThich
            → ThongBao::updateOrCreate() cho MoiGioi  (DB cũ)
            → MoiGioi::notify(CustomerInterestedNotification)  (DB + Broadcast → MoiGioi)
```

---

## 2. Cấu hình Backend (Laravel)

### 2.1. Biến môi trường (`.env`)

```env
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=my-app-id
REVERB_APP_KEY=my-app-key
REVERB_APP_SECRET=my-app-secret
REVERB_HOST="127.0.0.1"
REVERB_PORT=8080
REVERB_SCHEME=http
```

Chạy Reverb server: `php artisan reverb:start`

### 2.2. Phân quyền Channel (`routes/channels.php`)

Project dùng **Private Channels** — mỗi user chỉ subscribe được channel của chính mình.

```php
// routes/channels.php

// ── Admin channel: private-admin.{id} ──────────────────────────────────
Broadcast::channel('admin.{id}', function ($user, $id) {
    return $user instanceof \App\Models\Admin && (int) $user->id === (int) $id;
});

// ── MoiGioi channel: private-user.{id} ────────────────────────────────
Broadcast::channel('user.{id}', function ($user, $id) {
    return $user instanceof \App\Models\MoiGioi && (int) $user->id === (int) $id;
});
```

### 2.3. Khai báo channel nhận Notification trên Model

Mỗi Model phải khai báo `receivesBroadcastNotificationsOn()` để Laravel biết đẩy về channel nào.

```php
// app/Models/Admin.php
public function receivesBroadcastNotificationsOn(): string
{
    return 'admin.' . $this->id;  // → private-admin.{id}
}

// app/Models/MoiGioi.php
public function receivesBroadcastNotificationsOn(): string
{
    return 'user.' . $this->id;  // → private-user.{id}
}
```

### 2.4. Events (Nội bộ — Không tự Broadcast)

Các Events trong project là **nội bộ** (không `implements ShouldBroadcast`). Chúng chỉ dùng để kích hoạt Listeners.

```php
// app/Events/BatDongSanDuocDuyet.php
class BatDongSanDuocDuyet
{
    use Dispatchable, SerializesModels;

    public function __construct(public BatDongSan $batDongSan) {}
}

// Tương tự cho: BatDongSanBiTuChoi, BatDongSanMoiDang, BatDongSanDuocYeuThich
```

**Cách gọi trong Controller:**
```php
event(new BatDongSanDuocDuyet($bds));
```

### 2.5. Notifications (Lưu DB + Broadcast Real-time)

Các class Notification trong project đều `implements ShouldBroadcastNow` và dùng cả 2 channel: `database` và `broadcast`.

```php
// app/Notifications/PostApprovedNotification.php
class PostApprovedNotification extends Notification implements ShouldBroadcastNow
{
    public function __construct(public BatDongSan $batDongSan) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];  // Vừa lưu DB, vừa broadcast real-time
    }

    public function toDatabase(object $notifiable): array
    {
        return $this->payload();
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->payload());
    }

    private function payload(): array
    {
        return [
            'type'     => 'post_approved',
            'loai'     => 'approved',          // FE dùng để render icon/màu
            'tieu_de'  => 'Bài đăng đã được duyệt ✅',
            'noi_dung' => "Bài đăng \"{$this->batDongSan->tieu_de}\" đã được admin duyệt.",
            'bds_id'   => $this->batDongSan->id,
            'link'     => '/moi-gioi/quan-ly-tin',
        ];
    }
}
```

**Danh sách Notifications hiện có:**

| Class | Người nhận | Khi nào |
|---|---|---|
| `NewPostPendingApprovalNotification` | Admin | Môi giới publish bài |
| `PostApprovedNotification` | Môi giới | Admin duyệt bài |
| `PostRejectedNotification` | Môi giới | Admin từ chối bài |
| `CustomerInterestedNotification` | Môi giới | Khách hàng yêu thích bài |

### 2.6. Listeners và EventServiceProvider

```php
// app/Providers/EventServiceProvider.php
protected $listen = [
    BatDongSanMoiDang::class    => [TaoThongBaoBDSMoi::class],
    BatDongSanDuocDuyet::class  => [GuiThongBaoDuyet::class],
    BatDongSanBiTuChoi::class   => [GuiThongBaoTuChoi::class],
    BatDongSanDuocYeuThich::class => [TaoThongBaoKhiYeuThich::class],
];
```

---

## 3. Cấu hình Frontend (Vue 3)

### 3.1. Khởi tạo Echo (`src/main.js`)

Echo được khởi tạo **một lần duy nhất** ở `main.js` và gán vào `window.Echo` để toàn app dùng chung. Token được lấy từ `localStorage` theo **role** của URL hiện tại.

```javascript
// src/main.js
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// ✅ Lấy token đúng theo role (admin / moi-gioi / khach-hang)
const _currentPath = window.location.pathname;
const _tokenKey = _currentPath.startsWith('/admin')
  ? 'admin_auth_token'
  : _currentPath.startsWith('/moi-gioi')
    ? 'moi_gioi_auth_token'
    : 'khach_hang_auth_token';
const _initToken = localStorage.getItem(_tokenKey) || '';

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST || '127.0.0.1',
    wsPort: Number(import.meta.env.VITE_REVERB_PORT) || 8080,
    forceTLS: false,
    disableStats: true,
    enabledTransports: ['ws', 'wss'],
    authEndpoint: `${import.meta.env.VITE_API_URL}/broadcasting/auth`,
    auth: {
        headers: {
            Authorization: _initToken ? `Bearer ${_initToken}` : '',
        }
    },
});
```

### 3.2. Echo Service (`src/js/services/echo.js`)

File trung tâm để quản lý việc subscribe/unsubscribe channel. **Tất cả components phải dùng thông qua đây** thay vì gọi `window.Echo` trực tiếp.

```javascript
// src/js/services/echo.js

let _userChannel = null;
let _adminChannel = null;
let _subscribedUserId = null;
let _subscribedAdminId = null;

// ── Cập nhật token sau khi đăng nhập ──────────────────────────────────
export const updateEchoToken = (token) => {
    if (!window.Echo?.connector?.options?.auth?.headers) return;
    window.Echo.connector.options.auth.headers.Authorization = token
        ? `Bearer ${token}`
        : '';
};

// ── Subscribe channel cho MÔI GIỚI ────────────────────────────────────
export const subscribeUser = (userId, onNotify) => {
    if (!window.Echo || !userId) return null;
    if (_subscribedUserId === userId && _userChannel) return _userChannel;

    // Tự động unsubscribe user cũ trước khi subscribe user mới
    if (_subscribedUserId) safeLeave(`user.${_subscribedUserId}`);

    _subscribedUserId = userId;
    _userChannel = window.Echo.private(`user.${userId}`);

    if (onNotify) {
        _userChannel.notification((notification) => {
            onNotify(notification);
        });
    }
    return _userChannel;
};

// ── Subscribe channel cho ADMIN ────────────────────────────────────────
export const subscribeAdmin = (adminId, onNotify) => {
    if (!window.Echo || !adminId) return null;
    if (_subscribedAdminId === adminId && _adminChannel) return _adminChannel;

    if (_subscribedAdminId) safeLeave(`admin.${_subscribedAdminId}`);

    _subscribedAdminId = adminId;
    _adminChannel = window.Echo.private(`admin.${adminId}`);

    if (onNotify) {
        _adminChannel.notification((notification) => {
            onNotify(notification);
        });
    }
    return _adminChannel;
};

// ── Cleanup khi đăng xuất ──────────────────────────────────────────────
export const leaveAllChannels = () => {
    if (_subscribedUserId) safeLeave(`user.${_subscribedUserId}`);
    if (_subscribedAdminId) safeLeave(`admin.${_subscribedAdminId}`);
    _userChannel = _adminChannel = null;
    _subscribedUserId = _subscribedAdminId = null;
};
```

### 3.3. Cách dùng trong Components

**Môi giới — Header.vue** (subscribe khi đăng nhập, unsubscribe khi logout):
```javascript
import { subscribeUser, leaveAllChannels, updateEchoToken } from '@/js/services/echo';

// Sau khi đăng nhập thành công:
updateEchoToken(token);
subscribeUser(userId, (notification) => {
    // notification.loai: 'approved' | 'tu_choi' | 'khach_moi' | 'dang_tin'
    // notification.tieu_de, notification.noi_dung, notification.bds_id
    showToast(notification.tieu_de);
    unreadCount.value++;
});

// Khi đăng xuất:
leaveAllChannels();
```

**Admin — TopBDS.vue** (subscribe khi vào trang admin):
```javascript
import { subscribeAdmin, leaveAllChannels } from '@/js/services/echo';

subscribeAdmin(adminId, (notification) => {
    // notification.loai: 'pending'
    // notification.tieu_de, notification.noi_dung, notification.bds_id
    showAdminToast(notification.tieu_de);
    pendingCount.value++;
});
```

**Môi giới — ThongBao Page** (tải lịch sử + lắng nghe real-time mới):
```javascript
import { subscribeUser } from '@/js/services/echo';

// 1. Tải thông báo cũ qua API HTTP
const res = await api.get('/moi-gioi/thong-bao');
list.value = res.data.data;

// 2. Lắng nghe thông báo mới real-time
subscribeUser(user.id, (data) => {
    list.value.unshift({
        id: data.id || Date.now(),
        tieu_de: data.tieu_de,
        noi_dung: data.noi_dung,
        loai: data.loai,
        is_read: false,
        created_at: new Date().toISOString(),
    });
});
```

---

## 4. API Endpoints Thông báo

### Môi giới
| Method | Endpoint | Mô tả |
|---|---|---|
| GET | `/moi-gioi/thong-bao` | Lấy danh sách thông báo (bảng `thong_baos`) |
| POST | `/moi-gioi/thong-bao/{id}/da-doc` | Đánh dấu đã đọc |
| POST | `/moi-gioi/thong-bao/doc-tat-ca` | Đánh dấu tất cả đã đọc |
| DELETE | `/moi-gioi/thong-bao/{id}` | Xóa một thông báo |

### Admin
| Method | Endpoint | Mô tả |
|---|---|---|
| GET | `/admin/notifications` | Lấy notifications (bảng chuẩn Laravel) |
| POST | `/admin/notifications/read-all` | Đánh dấu tất cả đã đọc |

---

## 5. Cấu trúc Payload Notification

Tất cả notification trong project đều trả payload theo chuẩn sau:

```json
{
    "type": "post_approved",
    "loai": "approved",
    "tieu_de": "Bài đăng đã được duyệt ✅",
    "noi_dung": "Bài đăng \"...\" đã được admin duyệt.",
    "bds_id": 119,
    "link": "/moi-gioi/quan-ly-tin"
}
```

**Giá trị `loai` và ý nghĩa ở Frontend:**

| `loai` | Icon màu | Dùng ở |
|---|---|---|
| `approved` | Xanh lá (✅) | Môi giới — bài được duyệt |
| `tu_choi` | Đỏ (❌) | Môi giới — bài bị từ chối |
| `khach_moi` / `yeu_thich` | Đỏ (❤️) | Môi giới — khách quan tâm |
| `dang_tin` | Xanh dương (🏠) | Môi giới — bài mới đăng |
| `pending` | Vàng (⏳) | Admin — có bài chờ duyệt |

---

## 6. Best Practices & Lưu ý

### ✅ Đúng cách

1. **Luôn gọi `updateEchoToken(token)` ngay sau khi đăng nhập thành công**, trước khi `subscribeUser/subscribeAdmin`. Nếu không, request xác thực Private Channel sẽ bị 401.

2. **Dùng `leaveAllChannels()` khi đăng xuất** để giải phóng WebSocket connection, tránh memory leak và nhận thông báo nhầm người.

3. **Tải thông báo cũ bằng API HTTP** ở lần đầu load trang. WebSocket chỉ nhận thông báo **từ thời điểm kết nối trở đi**.

4. **Sử dụng `ShouldBroadcastNow`** (thay vì `ShouldBroadcast`) trong các Notification để broadcast ngay lập tức, không cần queue worker.

### ❌ Không làm

5. **Không gọi `window.Echo.private()` trực tiếp trong component**. Luôn dùng qua `echo.js` service để tránh subscribe trùng lặp.

6. **Không push payload nặng qua WebSocket**. Chỉ push `id`, `loai`, `tieu_de`, `noi_dung`, `bds_id`. Tuyệt đối không push toàn bộ object BĐS kèm ảnh và relations.

7. **Không dùng `echo.leave()` trong component nếu các component khác đang dùng cùng channel**. Dùng `leaveAllChannels()` chỉ khi logout.

---

## 7. Chạy Hệ thống

```bash
# Backend
cd BE
php artisan serve        # Laravel API server
php artisan reverb:start # WebSocket server (cổng 8080)

# Frontend
cd FE
npm run dev              # Vite dev server (cổng 5173)
```

**Biến `.env` Frontend cần thiết (`FE/.env`):**
```env
VITE_API_URL=http://localhost:8000/api
VITE_REVERB_APP_KEY=my-app-key
VITE_REVERB_HOST=127.0.0.1
VITE_REVERB_PORT=8080
VITE_REVERB_SCHEME=http
```
