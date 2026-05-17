<template>
  <header class="admin-topbar">
    <div class="admin-topbar__actions">
      <!-- 🔔 Notification -->
      <button class="admin-topbar__icon" type="button" @click="toggleNotifPanel" :title="`${unreadCount} thông báo chưa đọc`">
        <span class="material-symbols-outlined">notifications</span>
        <span v-if="unreadCount > 0" class="admin-topbar__dot" :data-count="unreadCount"></span>
      </button>

      <!-- 🔔 Notification Panel -->
      <div v-show="showNotifPanel" class="admin-notif-panel" @click.stop>
        <div class="admin-notif-header">
          <strong>Thông báo ({{ unreadCount }} chưa đọc)</strong>
          <button @click="showNotifPanel = false" class="close-panel-btn">×</button>
        </div>
        
        <div v-if="adminNotifs.length === 0" class="admin-notif-empty">
          <span class="material-symbols-outlined">notifications_off</span>
          <p>Không có thông báo mới</p>
        </div>

        <div v-else class="admin-notif-list">
          <div v-for="(n, i) in adminNotifs" :key="i" class="admin-notif-item" :class="{ unread: !n.read }" @click="goToNotifLink(n)">
            <div class="notif-icon">
              <span class="material-symbols-outlined">info</span>
            </div>
            <div class="notif-content">
              <div class="notif-title-row">
                <span class="notif-title">{{ n.tieu_de }}</span>
                <span class="notif-time">{{ formatTime(n.thoi_gian) }}</span>
              </div>
              <div class="notif-body">{{ n.noi_dung }}</div>
              
              <!-- ⚡ Action Buttons for New Post Pending -->
              <div v-if="n.type === 'new_post_pending' && !n.handled" class="notif-actions" @click.stop>
                <button class="action-btn approve" @click="handleAction(n, 1)">
                  <span class="material-symbols-outlined">check_circle</span>
                  Duyệt
                </button>
                <button class="action-btn reject" @click="handleAction(n, 0)">
                  <span class="material-symbols-outlined">cancel</span>
                  Từ chối
                </button>
              </div>
              <div v-else-if="n.handled" class="notif-status-tag" :class="n.handledStatus">
                {{ n.handledStatus === 'approved' ? 'Đã duyệt' : 'Đã từ chối' }}
              </div>
            </div>
            <div v-if="!n.read" class="unread-dot"></div>
          </div>
        </div>
        
        <div class="admin-notif-footer">
          <button @click="markAllRead" class="view-all-btn">Đánh dấu tất cả là đã đọc</button>
        </div>
      </div>

      <!-- 👤 PROFILE DROPDOWN -->
      <div
        class="admin-topbar__profile-wrapper"
        :class="{ active: showProfileDropdown }"
      >
        <!-- Avatar Button -->
        <div class="admin-topbar__avatar-btn" @click="toggleProfileDropdown">
          <div class="admin-topbar__avatar">
            {{ getInitials(adminName) }}
          </div>
          <span
            class="material-symbols-outlined admin-dropdown-arrow"
            :class="{ rotated: showProfileDropdown }"
          >
            expand_more
          </span>
        </div>

        <!-- Dropdown Menu -->
        <div class="admin-profile-dropdown" v-if="showProfileDropdown">
          <div class="admin-profile-dropdown__header">
            <div class="admin-profile-dropdown__avatar">
              {{ getInitials(adminName) }}
            </div>
            <div class="admin-profile-dropdown__info">
              <strong>{{ adminName }}</strong>
              <p>Administrator</p>
            </div>
          </div>
          <div class="admin-profile-dropdown__divider"></div>
            <button class="admin-profile-dropdown__item" @click="logout">
              <span class="material-symbols-outlined">logout</span>
              Đăng xuất
            </button>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from "vue";
import { useRouter } from "vue-router";
import { getCurrentInstance } from "vue";
import { subscribeAdmin, leaveAllChannels } from '@/js/services/echo';
import { clearAuth } from "@/js/auth";
import Swal from "sweetalert2";
import api from "@/axios/config";

const router = useRouter();
const { appContext } = getCurrentInstance();
const toast = appContext.config.globalProperties.$toast;

const showProfileDropdown = ref(false);
const adminName = ref("Admin");
const adminId   = ref(null);
const unreadCount   = ref(0);
const adminNotifs   = ref([]);
const showNotifPanel = ref(false);

const fetchNotifications = async () => {
  try {
    const res = await api.get('/admin/notifications');
    if (res.data) {
      adminNotifs.value = res.data.map(n => ({
        id: n.id,
        type: n.data?.type,
        bds_id: n.data?.bds_id,
        tieu_de: n.data?.tieu_de || 'Thông báo',
        noi_dung: n.data?.noi_dung || '',
        thoi_gian: n.created_at,
        read: !!n.read_at,
        link: n.data?.link,
        handled: false,
        handledStatus: null
      }));
      unreadCount.value = adminNotifs.value.filter(n => !n.read).length;
    }
  } catch (error) {
    console.error('[AdminHeader] Failed to fetch notifications:', error);
  }
};

const toggleNotifPanel = () => {
  showNotifPanel.value = !showNotifPanel.value;
};

// Toggle dropdown
const toggleProfileDropdown = () => {
  showProfileDropdown.value = !showProfileDropdown.value;
};

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
  const wrapper = document.querySelector(".admin-topbar__profile-wrapper");
  const notifBtn = document.querySelector(".admin-topbar__icon");
  const notifPanel = document.querySelector(".admin-notif-panel");

  if (wrapper && !wrapper.contains(event.target)) {
    showProfileDropdown.value = false;
  }
  
  if (notifBtn && !notifBtn.contains(event.target) && notifPanel && !notifPanel.contains(event.target)) {
    showNotifPanel.value = false;
  }
};

// Get initials from name
const getInitials = (name) => {
  if (!name) return "AD";
  return name
    .split(" ")
    .map((part) => part[0])
    .slice(0, 2)
    .join("")
    .toUpperCase();
};

const formatTime = (isoString) => {
  if (!isoString) return "";
  const date = new Date(isoString);
  const diffSec = Math.floor((new Date() - date) / 1000);
  if (diffSec < 10) return "vừa xong";
  if (diffSec < 60) return `${diffSec} giây trước`;
  if (diffSec < 3600) return `${Math.floor(diffSec / 60)} phút trước`;
  if (diffSec < 86400) return `${Math.floor(diffSec / 3600)} giờ trước`;
  return date.toLocaleDateString("vi-VN");
};

const markAllRead = async () => {
  try {
    await api.post('/admin/notifications/mark-read');
    adminNotifs.value.forEach(n => n.read = true);
    unreadCount.value = 0;
    toast.success("Đã đánh dấu tất cả là đã đọc");
  } catch (error) {
    console.error("Mark read error:", error);
  }
};

const goToNotifLink = (n) => {
  console.log('[AdminHeader] Clicking notification:', n);
  
  // Mark as read if not already
  if (!n.read) {
    n.read = true;
    unreadCount.value = Math.max(0, unreadCount.value - 1);
    api.post(`/admin/notifications/mark-read`, { id: n.id }).catch(e => console.error(e));
  }
  
  if (n.link) {
    console.log('[AdminHeader] Navigating to:', n.link);
    showNotifPanel.value = false;
    
    // Nếu link là chi tiết (có ID) mà chưa có route chi tiết, 
    // ta hướng về trang danh sách để tránh bị redirect về home (404)
    if (n.link.startsWith('/admin/bat-dong-san/') && n.link !== '/admin/bat-dong-san') {
        router.push('/admin/bat-dong-san');
    } else {
        router.push(n.link);
    }
  } else {
    console.warn('[AdminHeader] Notification has no link');
  }
};

const handleAction = async (notif, isDuyet) => {
  if (!notif.bds_id) return;

  try {
    let ly_do = "";
    if (isDuyet === 0) {
      const { value: text } = await Swal.fire({
        title: 'Lý do từ chối',
        input: 'textarea',
        inputPlaceholder: 'Nhập lý do từ chối bài đăng...',
        showCancelButton: true,
        confirmButtonColor: '#ff4d4f',
        confirmButtonText: 'Từ chối bài',
        cancelButtonText: 'Hủy'
      });
      
      if (text === undefined) return; // User cancelled
      ly_do = text;
    }

    const res = await api.post('/admin/bds/duyet', {
      id: notif.bds_id,
      is_duyet: isDuyet,
      ly_do: ly_do
    });

    if (res.data?.status) {
      toast.success(isDuyet === 1 ? "Đã duyệt bài đăng thành công" : "Đã từ chối bài đăng");
      notif.handled = true;
      notif.handledStatus = isDuyet === 1 ? 'approved' : 'rejected';
      
      // Mark notification as read
      if (!notif.read) {
        notif.read = true;
        unreadCount.value = Math.max(0, unreadCount.value - 1);
      }
    } else {
      toast.error(res.data?.message || "Có lỗi xảy ra");
    }
  } catch (error) {
    console.error("Action error:", error);
    toast.error("Không thể thực hiện thao tác");
  }
};

// Logout
const logout = () => {
  showProfileDropdown.value = false;
  clearAuth("admin");

  toast.success("Đăng xuất thành công 👋");

  leaveAllChannels();
  setTimeout(() => {
    router.push("/admin/dang-nhap");
  }, 1000);
};

// ========== AUTH HANDLERS ==========
const checkLogin = async () => {
  const token = localStorage.getItem("admin_auth_token");
  const savedUserInfo = localStorage.getItem("admin_user_info");
  
  if (savedUserInfo && savedUserInfo !== "undefined" && savedUserInfo !== "null") {
    try {
      const ui = JSON.parse(savedUserInfo);
      if (ui.ten || ui.name) adminName.value = ui.ten || ui.name;
      adminId.value = ui.id;
    } catch (_) {
      fetchAdminProfile();
    }
  } else if (token) {
    // Nếu có token mà không có info -> Fetch từ server
    fetchAdminProfile();
  } else {
    adminName.value = "Admin";
    adminId.value = null;
  }
};

const fetchAdminProfile = async () => {
  try {
    const res = await api.get('/admin/profile');
    if (res.data?.status || res.data?.data) {
      const user = res.data.data;
      adminName.value = user.ten || user.name;
      adminId.value = user.id;
      // Cập nhật lại localStorage để lần sau không cần fetch
      localStorage.setItem("admin_user_info", JSON.stringify(user));
    }
  } catch (error) {
    console.error('[AdminHeader] Failed to fetch profile:', error);
  }
};

const initEcho = () => {
  const token = localStorage.getItem("admin_auth_token");
  console.log('[AdminHeader] Attempting initEcho...', { adminId: adminId.value, hasToken: !!token });
  if (adminId.value && token) {
    console.log('[AdminHeader] Initializing Echo for admin ID:', adminId.value);
    
    import('@/js/services/echo').then(({ updateEchoToken, subscribeAdmin }) => {
      updateEchoToken(token);
      
      const channel = subscribeAdmin(adminId.value, (data) => {
        console.log('[AdminHeader] Real-time notification received! Payload:', data);
        
        // Thêm vào danh sách
        adminNotifs.value.unshift({
          tieu_de: data.tieu_de || 'Thông báo mới',
          noi_dung: data.noi_dung || '',
          thoi_gian: new Date().toISOString(),
          read: false,
          link: data.link,
          type: data.type,
          bds_id: data.bds_id,
          handled: false,
          handledStatus: null
        });
        unreadCount.value += 1;
        
        // Hiển thị toast
        if (toast) {
          toast.info(`${data.tieu_de || 'Thông báo mới'}: ${data.noi_dung || ''}`, {
            position: "top-right",
            duration: 8000,
            onClick: () => {
              if (data.link) router.push(data.link);
            }
          });
        }
      });

      if (channel) {
        console.log('[AdminHeader] Echo subscription successful ✅');
      }
    }).catch(err => {
      console.error('[AdminHeader] Echo initialization failed:', err);
    });
  } else {
    console.warn('[AdminHeader] Cannot init Echo: Missing adminId or token');
  }
};

const onStorageChange = (event) => {
  if (event.key === "admin_auth_token" || event.key === "admin_user_info") {
    checkLogin();
  }
};

const onAuthChanged = () => {
  checkLogin();
};

watch(adminId, (newId) => {
  if (newId) {
    initEcho();
    fetchNotifications();
  }
});

onMounted(() => {
  checkLogin();
  
  // Add listeners
  document.addEventListener("click", handleClickOutside);
  window.addEventListener("storage", onStorageChange);
  window.addEventListener("admin-auth-changed", onAuthChanged);

  // Khởi tạo lần đầu
  if (adminId.value) {
    initEcho();
    fetchNotifications();
  }
});

onUnmounted(() => {
  document.removeEventListener("click", handleClickOutside);
  window.removeEventListener("storage", onStorageChange);
  window.removeEventListener("admin-auth-changed", onAuthChanged);
  leaveAllChannels();
});

</script>

<style scoped>
/* ✅ Admin Notification Panel */
.admin-notif-panel {
  position: absolute;
  top: calc(100% + 10px);
  right: 60px;
  width: 320px;
  max-height: 400px;
  overflow-y: auto;
  background: white;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0,0,0,0.15);
  border: 1px solid #e9ecef;
  z-index: 1100;
  animation: slideDown 0.2s ease-out;
}
.admin-notif-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 14px 16px;
  background: #f8f9fa;
  border-bottom: 1px solid #e9ecef;
  font-size: 0.9rem;
  color: #1a237e;
}
.close-panel-btn {
  background: none;
  border: none;
  font-size: 20px;
  color: #999;
  cursor: pointer;
  line-height: 1;
}
.close-panel-btn:hover { color: #333; }

.admin-notif-empty {
  padding: 40px 20px;
  text-align: center;
  color: #adb5bd;
}
.admin-notif-empty .material-symbols-outlined {
  font-size: 48px;
  margin-bottom: 10px;
  opacity: 0.5;
}
.admin-notif-empty p { margin: 0; font-size: 0.9rem; }

.admin-notif-list {
  max-height: 320px;
  overflow-y: auto;
}
.admin-notif-item {
  display: flex;
  gap: 12px;
  padding: 12px 16px;
  border-bottom: 1px solid #f1f3f5;
  cursor: pointer;
  transition: all 0.2s;
  position: relative;
}
.admin-notif-item:hover { background: #f8f9fa; }
.admin-notif-item.unread { background: #f0f4ff; }

.notif-icon {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  background: #eef2ff;
  color: #1a237e;
  display: flex;
  align-items: center;
  justify-content: center;
}
.notif-content { flex: 1; min-width: 0; }
.notif-title-row {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  margin-bottom: 2px;
}
.notif-title {
  font-weight: 600;
  font-size: 0.85rem;
  color: #212529;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.notif-time {
  font-size: 0.75rem;
  color: #868e96;
  white-space: nowrap;
}
.notif-body {
  font-size: 0.8rem;
  color: #495057;
  line-height: 1.4;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.unread-dot {
  width: 8px;
  height: 8px;
  background: #4dabf7;
  border-radius: 50%;
  position: absolute;
  top: 15px;
  right: 8px;
}

.admin-notif-footer {
  padding: 10px;
  text-align: center;
  background: #f8f9fa;
  border-top: 1px solid #e9ecef;
}
.view-all-btn {
  background: none;
  border: none;
  color: #1a237e;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  padding: 4px 8px;
  border-radius: 4px;
}
.view-all-btn:hover { background: #eef2ff; }

/* ⚡ Action Buttons in Notif */
.notif-actions {
  display: flex;
  gap: 8px;
  margin-top: 10px;
}
.action-btn {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 6px 12px;
  border-radius: 6px;
  border: none;
  font-size: 0.75rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}
.action-btn .material-symbols-outlined {
  font-size: 16px;
}
.action-btn.approve {
  background: #e6fffa;
  color: #0d9488;
}
.action-btn.approve:hover {
  background: #b2f5ea;
}
.action-btn.reject {
  background: #fff5f5;
  color: #e53e3e;
}
.action-btn.reject:hover {
  background: #fed7d7;
}

.notif-status-tag {
  display: inline-block;
  margin-top: 8px;
  padding: 2px 8px;
  border-radius: 4px;
  font-size: 0.7rem;
  font-weight: 600;
}
.notif-status-tag.approved {
  background: #0d9488;
  color: white;
}
.notif-status-tag.rejected {
  background: #e53e3e;
  color: white;
}

.admin-topbar {
  position: fixed;
  top: 0;
  left: 260px;
  right: 0;

  z-index: 20;
  display: flex;
  justify-content: flex-end;
  align-items: center;
  height: 74px;
  padding: 12px 28px;
  background: rgba(247, 246, 243, 0.86);
  border-bottom: 1px solid rgba(26, 35, 126, 0.05);
}

.admin-topbar__actions {
  display: flex;
  align-items: center;
  gap: 18px;
}

/* 🔔 Notification */
.admin-topbar__icon {
  position: relative;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  border: none;
  background: transparent;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.2s;
}

.admin-topbar__icon:hover {
  background: rgba(0, 0, 0, 0.05);
}

.admin-topbar__dot {
  position: absolute;
  top: 8px;
  right: 8px;
  width: 6px;
  height: 6px;
  background: #ff4d4f;
  border-radius: 50%;
  border: 2px solid rgba(247, 246, 243, 0.86);
}

/* 👤 Profile Wrapper */
.admin-topbar__profile-wrapper {
  position: relative;
}

.admin-topbar__avatar-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
  padding: 4px;
  border-radius: 8px;
  transition: background 0.2s;
}

.admin-topbar__avatar-btn:hover {
  background: rgba(0, 0, 0, 0.05);
}

.admin-topbar__avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, #1d2a89, #3342b6);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 0.9rem;
}

.admin-dropdown-arrow {
  font-size: 20px;
  color: #1a237e;
  transition: transform 0.3s;
}

.admin-dropdown-arrow.rotated {
  transform: rotate(180deg);
}

/* 📋 Dropdown Menu */
.admin-profile-dropdown {
  position: absolute;
  top: calc(100% + 10px);
  right: 0;
  width: 280px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
  border: 1px solid rgba(26, 35, 126, 0.1);
  overflow: hidden;
  animation: slideDown 0.2s ease-out;
  z-index: 1000;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.admin-profile-dropdown__header {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px;
  background: linear-gradient(135deg, #f8f9fa, #e9ecef);
}

.admin-profile-dropdown__avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: linear-gradient(135deg, #1d2a89, #3342b6);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 1.1rem;
}

.admin-profile-dropdown__info {
  flex: 1;
}

.admin-profile-dropdown__info strong {
  display: block;
  font-size: 0.95rem;
  font-weight: 700;
  color: #1a237e;
  margin-bottom: 2px;
}

.admin-profile-dropdown__info p {
  font-size: 0.75rem;
  color: #6c757d;
  margin: 0;
}

.admin-profile-dropdown__divider {
  height: 1px;
  background: #e9ecef;
  margin: 8px 16px;
}

.admin-profile-dropdown__menu {
  padding: 8px;
}

.admin-profile-dropdown__item {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  border: none;
  background: transparent;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.9rem;
  color: #495057;
  transition: all 0.2s;
  text-align: left;
}

.admin-profile-dropdown__item:hover {
  background: #f8f9fa;
  color: #1a237e;
}

.admin-profile-dropdown__item .material-symbols-outlined {
  font-size: 18px;
  color: #6c757d;
}

.admin-profile-dropdown__item:hover .material-symbols-outlined {
  color: #1a237e;
}

.admin-profile-dropdown__item:last-child {
  color: #ff4d4f;
}

.admin-profile-dropdown__item:last-child:hover {
  background: #fff5f5;
}

.admin-profile-dropdown__item:last-child .material-symbols-outlined {
  color: #ff4d4f;
}
</style>