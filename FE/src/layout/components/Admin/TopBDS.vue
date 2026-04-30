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
          <div v-for="(n, i) in adminNotifs" :key="i" class="admin-notif-item" :class="{ unread: !n.read }">
            <div class="notif-icon">
              <span class="material-symbols-outlined">info</span>
            </div>
            <div class="notif-content">
              <div class="notif-title-row">
                <span class="notif-title">{{ n.tieu_de }}</span>
                <span class="notif-time">{{ formatTime(n.thoi_gian) }}</span>
              </div>
              <div class="notif-body">{{ n.noi_dung }}</div>
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
import { ref, onMounted, onUnmounted } from "vue";
import { useRouter } from "vue-router";
import { getCurrentInstance } from "vue";
import { subscribeAdmin, leaveAllChannels } from '@/js/services/echo';
import { clearAuth } from "@/js/auth";

const router = useRouter();
const { appContext } = getCurrentInstance();
const toast = appContext.config.globalProperties.$toast;

const showProfileDropdown = ref(false);
const adminName = ref("Admin");
const adminId   = ref(null);
const unreadCount   = ref(0);
const adminNotifs   = ref([]);
const showNotifPanel = ref(false);

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

const markAllRead = () => {
  adminNotifs.value.forEach(n => n.read = true);
  unreadCount.value = 0;
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
const checkLogin = () => {
  const token = localStorage.getItem("admin_auth_token");
  const savedUserInfo = localStorage.getItem("admin_user_info");
  if (savedUserInfo) {
    try {
      const ui = JSON.parse(savedUserInfo);
      if (ui.ten || ui.name) adminName.value = ui.ten || ui.name;
      adminId.value = ui.id;
    } catch (_) {}
  } else {
    adminName.value = "Admin";
    adminId.value = null;
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

  // Load admin info from token or admin_user_info
onMounted(() => {
  checkLogin();

  // Add listeners
  document.addEventListener("click", handleClickOutside);
  window.addEventListener("storage", onStorageChange);
  window.addEventListener("admin-auth-changed", onAuthChanged);

  // ✅ Subscribe Echo admin channel
  if (adminId.value) {
    subscribeAdmin(adminId.value, (data) => {
      console.log('SUBSCRIBED ADMIN RECEIVED NOTIFICATION:', adminId.value, data);
      adminNotifs.value.unshift({
        tieu_de: data.tieu_de || 'Thông báo mới',
        noi_dung: data.noi_dung || '',
        thoi_gian: new Date().toISOString(),
        read: false,
      });
      unreadCount.value += 1;
    });
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