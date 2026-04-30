<template>
  <div>
    <nav class="header">
      <div class="container">
        <!-- Logo -->
        <router-link to="/" class="logo">
          <span class="logo-icon">
            <img src="../../../assets/images/logo/logo-2.png" alt="Logo" class="logo-img" />
          </span>
          <div class="logo-text">
            <span class="brand">SmartEstate</span>
            <span class="sub">2026</span>
          </div>
        </router-link>

        <!-- ✅ NAVIGATION MENU - MỚI THÊM -->
        <nav class="nav-menu">
          <router-link to="/" class="nav-link" exact>
            <span class="nav-label">Trang chủ</span>
          </router-link>

          <router-link to="/khach-hang/danh-sach-bat-dong-san" class="nav-link">
            <span class="nav-label">Bất động sản</span>
          </router-link>

          <router-link to="/khach-hang/lien-he" class="nav-link">
            <span class="nav-label">Liên hệ</span>
          </router-link>

          <router-link to="/khach-hang/ban-do" class="nav-link">
            <span class="nav-label">Bản đồ</span>
          </router-link>
        </nav>

        <!-- Actions -->
        <div class="actions">
          <!-- Nút Đăng tin -->
          <button @click="handlePostListing" class="btn-post">
            <span class="label">Đăng tin</span>
          </button>

          <!-- ✅ Nút Tin đã lưu -->
          <div class="saved-trigger-wrapper" v-if="isLoggedIn">
            <button @click.stop="toggleNotifPanel" class="btn-saved" aria-label="Tin đã lưu">
              <span class="material-symbols-outlined icon">favorite</span>
            </button>

            <!-- 🔔 NOTIFICATION PANEL -->
            <transition name="notif-slide">
              <div v-if="showNotifPanel" class="notif-panel" @click.stop>
                <div class="notif-header">
                  <h3 class="notif-title flex items-center gap-2">
                    <span class="material-symbols-outlined text-rose-500">favorite</span>
                    Tin đã lưu
                  </h3>
                  <span class="notif-count">{{ notifications.length }} tin</span>
                </div>

                <div class="notif-list">
                  <div v-for="item in notifications" :key="item.id" class="notif-item-wrapper"
                    :class="{ deleting: item.isDeleting }">
                    <div class="notif-item-swipeable" :style="{
                      transform: `translateX(${item.swipeOffset || 0}px)`,
                    }">
                      <div class="notif-delete-btn" @click.stop="deleteSavedItem(item)">
                        <span class="material-symbols-outlined">delete</span>
                        <span>Xóa</span>
                      </div>

                      <div class="notif-item" @click="handleNotifClick(item)" @mousedown="handleMouseDown($event, item)"
                        @mousemove="handleMouseMove($event, item)" @mouseup="handleMouseUp(item)"
                        @mouseleave="handleMouseUp(item)" :class="{ dragging: draggedItem === item }">
                        <div class="notif-image-wrapper">
                          <img :src="item.avatar" :alt="item.text" @error="handleImageError"
                            class="notif-image" />
                        </div>
                        <div class="notif-content">
                          <p class="notif-text">{{ item.text }}</p>
                          <span class="notif-time">{{ item.time }}</span>
                        </div>
                        <span class="material-symbols-outlined notif-arrow">arrow_forward</span>
                      </div>
                    </div>
                  </div>

                  <div v-if="notifications.length === 0 && !loadingNotifs" class="notif-empty">
                    <div class="notif-empty-icon">📭</div>
                    <p class="notif-empty-title">Chưa có tin nào được lưu</p>
                    <p class="notif-empty-hint">
                      Bấm <span class="material-symbols-outlined text-xs align-middle">favorite</span> để lưu tin bạn quan tâm
                    </p>
                  </div>

                  <div v-if="loadingNotifs" class="notif-empty">
                    <div class="notif-loading-spinner"></div>
                    <p>Đang tải...</p>
                  </div>
                </div>

                <div class="notif-footer">
                  <button class="notif-btn-primary" @click="goToSavedPage">
                    Xem tất cả tin đã lưu
                  </button>
                </div>
              </div>
            </transition>
          </div>

          <!-- 🔥 CHAT ICON - MỚI -->
          <div class="chat-trigger-wrapper" v-if="isLoggedIn">
            <button @click.stop="toggleChatPanel" class="btn-chat" aria-label="Tin nhắn">
              <span class="chat-icon">💬</span>
              <span v-if="unreadCount > 0" class="chat-badge">
                {{ unreadCount > 9 ? "9+" : unreadCount }}
              </span>
            </button>

            <!-- CHAT DROPDOWN PANEL -->
            <transition name="dropdown-fade">
              <div v-if="showChatPanel" class="chat-panel" @click.stop>
                <div class="chat-header">
                  <span class="chat-title">💬 Đoạn chat</span>
                  <button @click.stop="toggleChatPanel" class="chat-close" aria-label="Đóng">
                    <span class="material-symbols-outlined">close</span>
                  </button>
                </div>

                <div class="chat-list">
                  <!-- Loading -->
                  <div v-if="loadingChat" class="chat-loading">
                    <div class="chat-loading-spinner"></div>
                    <span>Đang tải tin nhắn...</span>
                  </div>

                  <!-- Conversations -->
                  <template v-else>
                    <div v-for="c in conversations" :key="c.id" class="chat-item" @click="openChat(c)">
                      <div class="chat-avatar">
                        <span class="broker-initial">{{
                          getBrokerInitial(c)
                        }}</span>
                      </div>
                      <div class="chat-info">
                        <p class="bds-title">
                          {{ c.bat_dong_san?.tieu_de || "Bất động sản" }}
                        </p>
                        <p class="broker-name">
                          {{ c.moi_gioi?.ten || "Môi giới" }}
                        </p>
                        <p v-if="c.last_message" class="last-message" :class="{ unread: !c.last_message.is_read }">
                          {{ truncateMessage(c.last_message.noi_dung) }}
                        </p>
                      </div>
                      <span v-if="hasUnread(c)" class="unread-dot"></span>
                      <span class="chat-time" v-if="c.last_message">
                        {{ formatChatTime(c.last_message.created_at) }}
                      </span>
                    </div>

                    <!-- Empty State -->
                    <div v-if="conversations.length === 0" class="chat-empty">
                      <span class="chat-empty-icon">💬</span>
                      <p>Chưa có cuộc trò chuyện nào</p>
                    </div>
                  </template>
                </div>

                <div class="chat-footer">
                  <router-link to="/khach-hang/tin-nhan" class="chat-view-all" @click="showChatPanel = false">
                    Xem tất cả tin nhắn
                  </router-link>
                </div>
              </div>
            </transition>
          </div>

          <!-- CHƯA LOGIN -->
          <template v-else>
            <button @click="goToLogin" class="btn-login">Đăng nhập</button>
          </template>

          <!-- ✅ ĐÃ LOGIN - User Menu (XANH DA TRỜI) -->
          <div v-if="isLoggedIn" class="user-wrapper" @click="toggleMenu">
            <div class="avatar-container">
              <div class="avatar">{{ userInitial }}</div>
              <span class="status-dot online"></span>
            </div>
            <span class="user-name">{{ userShortName }}</span>
            <span class="arrow" :class="{ open: showMenu }">▼</span>

            <!-- Dropdown Menu -->
            <transition name="dropdown-fade">
              <div v-show="showMenu" class="dropdown-menu-new" @click.stop>
                <!-- Header: User Info - XANH DA TRỜI GRADIENT -->
                <div class="dropdown-header-new">
                  <div class="avatar-large-new">{{ userInitial }}</div>
                  <div class="user-info-new">
                    <p class="user-display-name">{{ userName }}</p>
                    <span class="user-role-badge">{{ userTypeLabel }}</span>
                  </div>
                </div>

                <!-- Menu Items -->
                <div class="dropdown-items-new">
                  <!-- Hồ sơ cá nhân -->
                  <router-link to="/khach-hang/ho-so-ca-nhan" class="dropdown-item-new" @click="showMenu = false">
                    <span class="item-icon-new">�</span>
                    <span class="item-label-new">Hồ sơ cá nhân</span>
                    <span class="item-arrow-new">→</span>
                  </router-link>

                  <!-- Yêu thích -->
                  <router-link to="/khach-hang/yeu-thich" class="dropdown-item-new" @click="showMenu = false">
                    <span class="item-icon-new material-symbols-outlined text-rose-500">favorite</span>
                    <span class="item-label-new">BĐS yêu thích</span>
                    <span class="item-arrow-new">→</span>
                  </router-link>

                  <!-- Chat với môi giới -->
                  <div class="dropdown-item-new chat-broker-item" @click="handleChatWithBroker">
                    <span class="item-icon-new">💬</span>
                    <span class="item-label-new">Chat với môi giới</span>
                    <span class="item-badge-new">Mới</span>
                  </div>

                  <!-- Nâng cấp -->
                  <router-link to="/khach-hang/nang-cap-moi-gioi" class="dropdown-item-new" @click="showMenu = false">
                    <span class="item-icon-new">�</span>
                    <span class="item-label-new">Trở thành Môi Giới</span>
                    <span class="item-arrow-new">→</span>
                  </router-link>
                </div>

                <!-- Divider -->
                <div class="dropdown-divider-new"></div>

                <!-- Footer: Settings + Logout -->
                <div class="dropdown-footer-new">
                  <router-link to="/cai-dat" class="dropdown-item-new settings-item" @click="showMenu = false">
                    <span class="item-icon-new">⚙️</span>
                    <span class="item-label-new">Cài đặt tài khoản</span>
                  </router-link>

                  <button @click="handleLogout" class="logout-btn-new">
                    <span class="logout-icon-new">🚪</span>
                    <span>Đăng xuất</span>
                  </button>
                </div>
              </div>
            </transition>
          </div>
        </div>
      </div>
    </nav>

    <!-- Modal Broker -->
    <transition name="modal-smooth">
      <div v-if="showBrokerModal" class="modal-overlay" @click.self="closeBrokerModal">
        <div class="modal-box">
          <div class="modal-header">
            <span class="modal-icon">🔐</span>
            <h3 class="modal-title">Yêu cầu tài khoản Môi giới</h3>
          </div>
          <div class="modal-body">
            <p class="modal-text">
              Chức năng đăng tin chỉ dành cho tài khoản
              <strong>Môi giới bất động sản</strong>. <br /><br />
              Bạn có muốn đăng xuất và chuyển sang đăng nhập tài khoản môi giới?
            </p>
          </div>
          <div class="modal-footer">
            <button @click="closeBrokerModal" class="btn-modal cancel">
              Hủy
            </button>
            <button @click="switchToBrokerLogin" class="btn-modal confirm">
              Đăng xuất & Đăng nhập môi giới
            </button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
import api from "@/axios/config";
import { createToaster } from "@meforma/vue-toaster";

const toaster = createToaster({
  position: "top-right",
  duration: 2500,
  queue: true,
});

export default {
  name: "CustomerHeader",

  data() {
    return {
      // User Auth
      user: null,
      token: null,
      userType: null,

      // Menu States
      showMenu: false,
      showBrokerModal: false,
      menuTimer: null,

      // Notification Panel
      showNotifPanel: false,
      loadingNotifs: false,
      notifications: [],
      defaultImage:
        "https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=200&q=80",

      // Swipe Gesture
      dragStartX: 0,
      draggedItem: null,
      isDragging: false,

      // 🔥 Chat Feature
      showChatPanel: false,
      conversations: [],
      unreadCount: 0,
      loadingChat: false,
    };
  },

  watch: {
    // ✅ Re-check auth mỗi khi route thay đổi (sau khi login redirect)
    $route: {
      immediate: true,
      handler() {
        this.checkLogin();
      },
    },
  },

  computed: {
    isLoggedIn() {
      return !!(this.token && this.user);
    },

    userName() {
      if (!this.user) return "User";
      return (
        this.user.ten ||
        this.user.ho_ten ||
        this.user.name ||
        this.user.email ||
        "Khách hàng"
      );
    },

    userShortName() {
      const name = this.userName;
      const parts = name.trim().split(" ");
      return parts.length >= 2 ? parts[parts.length - 1] : parts[0] || "KH";
    },

    userInitial() {
      const name = this.userName;
      return name ? name.charAt(0).toUpperCase() : "?";
    },

    userTypeLabel() {
      const labels = {
        "moi-gioi": "Môi giới",
        "khach-hang": "Khách hàng",
        admin: "Admin",
      };
      return labels[this.userType] || "Người dùng";
    },
  },

  async mounted() {
    const isLogged = this.checkLogin();
    if (isLogged) {
      await this.loadSavedNotifications();
    }

    // Event Listeners
    document.addEventListener("click", this.handleClickOutside);
    // ✅ Chỉ react với storage event của key khách hàng (tránh cross-tab)
    window.addEventListener("storage", this._onStorageChange);
    window.addEventListener("favorite-updated", this.loadSavedNotifications);
    document.addEventListener("selectstart", this.preventSelect);

    // ✅ Lắng nghe sự kiện login/logout trong cùng tab
    window.addEventListener("khach-hang-auth-changed", this.onAuthChanged);

    // 🔥 Chat event listener
    window.addEventListener("new-chat-message", this.handleNewMessage);
  },

  beforeUnmount() {
    // Cleanup listeners
    document.removeEventListener("click", this.handleClickOutside);
    window.removeEventListener("storage", this._onStorageChange);
    window.removeEventListener("favorite-updated", this.loadSavedNotifications);
    document.removeEventListener("selectstart", this.preventSelect);
    window.removeEventListener("khach-hang-auth-changed", this.onAuthChanged);
    window.removeEventListener("new-chat-message", this.handleNewMessage);

    if (this.menuTimer) {
      clearTimeout(this.menuTimer);
      this.menuTimer = null;
    }
  },

  methods: {
    // ===== AUTH METHODS =====
    // ✅ Chỉ react với storage event của key khách hàng (tránh cross-tab interference)
    _onStorageChange(event) {
      if (event.key === 'khach_hang_auth_token' || event.key === 'khach_hang_user_info') {
        this.checkLogin();
      }
    },
    checkLogin() {
      // ✅ Đọc từ key riêng của khách hàng
      const token = localStorage.getItem("khach_hang_auth_token");
      const userStr = localStorage.getItem("khach_hang_user_info");

      if (token && userStr) {
        try {
          this.token = token;
          this.user = JSON.parse(userStr);
          this.userType = "khach-hang";
          return true;
        } catch (e) {
          console.error("Parse user error:", e);
          this.clearData();
        }
      } else if (token) {
        // Có token nhưng không có user info → vẫn coi là logged in
        this.token = token;
        this.userType = "khach-hang";
        this.user = this.user || { ten: "Khách hàng" };
        return true;
      } else {
        this.clearData();
      }
      return false;
    },

    // ✅ Handler khi có auth thay đổi trong cùng tab
    async onAuthChanged() {
      const isLogged = this.checkLogin();
      if (isLogged) {
        await this.loadSavedNotifications();
      }
    },

    clearData() {
      this.token = null;
      this.user = null;
      this.userType = null;
      this.showMenu = false;
      this.showNotifPanel = false;
      this.showChatPanel = false;
    },

    toggleMenu(e) {
      e?.stopPropagation();
      this.showNotifPanel = false;
      this.showChatPanel = false;
      if (this.menuTimer) clearTimeout(this.menuTimer);
      this.menuTimer = setTimeout(() => {
        this.showMenu = !this.showMenu;
      }, 100);
    },

    handleClickOutside(e) {
      if (!e.target.closest(".user-wrapper")) {
        if (this.menuTimer) clearTimeout(this.menuTimer);
        this.menuTimer = setTimeout(() => {
          this.showMenu = false;
        }, 150);
      }
      if (!e.target.closest(".saved-trigger-wrapper")) {
        this.showNotifPanel = false;
      }
      // 🔥 Close chat panel when clicking outside
      if (!e.target.closest(".chat-trigger-wrapper")) {
        this.showChatPanel = false;
      }
    },

    preventSelect(e) {
      if (this.isDragging) e.preventDefault();
    },

    goToLogin() {
      this.$router.push("/khach-hang/dang-nhap");
    },

    handlePostListing() {
      if (!this.isLoggedIn) {
        this.$router.push("/moi-gioi/dang-nhap");
        return;
      }
      if (this.userType === "moi-gioi") {
        this.$router.push("/moi-gioi/quan-ly-tin");
        return;
      }
      toaster.warning("Chức năng này chỉ dành cho tài khoản Môi giới", {
        duration: 3000,
      });
      this.showBrokerModal = true;
      this.showMenu = false;
    },

    closeBrokerModal() {
      this.showBrokerModal = false;
    },

    switchToBrokerLogin() {
      localStorage.removeItem("khach_hang_auth_token");
      localStorage.removeItem("user_info");
      localStorage.removeItem("user_type");
      this.clearData();
      toaster.info("Vui lòng đăng nhập tài khoản Môi giới", { duration: 2000 });
      setTimeout(() => {
        this.$router.push("/moi-gioi/dang-nhap");
      }, 300);
      this.closeBrokerModal();
    },

    handleLogout() {
      localStorage.removeItem("khach_hang_auth_token");
      localStorage.removeItem("user_info");
      localStorage.removeItem("user_type");
      this.clearData();
      toaster.success("Đăng xuất thành công!", {
        duration: 2000,
        variant: "success",
      });
      setTimeout(() => {
        this.$router.push("/");
      }, 400);
    },

    handleChatWithBroker() {
      this.showMenu = false;
      toaster.info("Đang kết nối với môi giới...", { duration: 2000 });
      // Option: this.$router.push('/khach-hang/chat-voi-moi-gioi');
    },

    // ===== NOTIFICATION METHODS =====
    async loadSavedNotifications() {
      const token = localStorage.getItem("khach_hang_auth_token");

      // ✅ CHỈ LOAD NẾU LÀ CUSTOMER (token khach hang)
      if (!token) {
        console.log("⏭️ No khach-hang token → skip load notifications");
        return;
      }

      this.loadingNotifs = true;
      try {
        const res = await api.get("/khach-hang/bds/yeu-thich/data");

        if (res.data?.status && Array.isArray(res.data.data)) {
          const isValidUrl = (u) => {
            return (
              typeof u === "string" &&
              u.trim() !== "" &&
              u !== "null" &&
              u !== "undefined"
            );
          };

          this.notifications = res.data.data.map((item) => {
            const bds = item.batDongSan || item.bat_dong_san || item.property || {};
            
            // Tìm ảnh đại diện từ nhiều nguồn khả thi
            let avatarUrl = "";
            
            // 1. Ưu tiên anh_dai_dien_url trực tiếp
            if (isValidUrl(bds.anh_dai_dien_url)) {
              avatarUrl = bds.anh_dai_dien_url;
            }
            // 2. Thử hinh_anh hoặc hinhAnh array
            else {
              const images = bds.hinh_anh || bds.hinhAnh || [];
              if (Array.isArray(images) && images.length > 0) {
                const img = images.find(i => i && isValidUrl(i.url));
                if (img) avatarUrl = img.url;
              }
            }
            
            // 3. Thử anh_dai_dien object
            if (!avatarUrl && bds.anh_dai_dien && isValidUrl(bds.anh_dai_dien.url)) {
              avatarUrl = bds.anh_dai_dien.url;
            }

            return {
              id: item.id || bds.id,
              text: bds.tieu_de || "Bất động sản",
              time: this.formatTime(item.created_at || item.createdAt),
              propertyId: bds.id || item.bat_dong_san_id,
              avatar: this.getImageUrl(avatarUrl), // Chuyển thành URL tuyệt đối ngay tại đây
              price: bds.gia,
              swipeOffset: 0,
              isDeleting: false,
            };
          });
        } else {
          this.notifications = [];
        }
      } catch (err) {
        console.error("Lỗi load saved notifications:", err);
        this.notifications = [];
        // ✅ Không tự redirect ở đây — để axios interceptor xử lý 401 tập trung
        if (err.response?.status === 401) {
          this.clearData();
        }
      } finally {
        this.loadingNotifs = false;
      }
    },

    formatTime(dateStr) {
      if (!dateStr) return "Vừa xong";
      const now = new Date();
      const created = new Date(dateStr);
      const diff = Math.floor((now - created) / 1000);

      if (diff < 60) return "Vừa xong";
      if (diff < 3600) return `${Math.floor(diff / 60)} phút trước`;
      if (diff < 86400) return `${Math.floor(diff / 3600)} giờ trước`;
      return created.toLocaleDateString("vi-VN");
    },

    getImageUrl(url) {
      if (!url) return this.defaultImage;
      if (typeof url !== "string") return this.defaultImage;
      if (url.startsWith("http")) return url;
      
      const base = import.meta.env.VITE_API_URL?.replace('/api','') || 'http://localhost:8000';
      const cleanUrl = url.startsWith('/') ? url.substring(1) : url;
      const finalUrl = cleanUrl.startsWith('storage/') ? cleanUrl : `storage/${cleanUrl}`;
      
      return `${base}/${finalUrl}`;
    },

    handleImageError(e) {
      e.target.src = this.defaultImage;
    },

    toggleNotifPanel() {
      this.showNotifPanel = !this.showNotifPanel;
      this.showMenu = false;
      this.showChatPanel = false;
      if (this.showNotifPanel && this.isLoggedIn) {
        this.loadSavedNotifications();
      }
    },

    handleNotifClick(item) {
      if (item.swipeOffset < -80) return;
      this.showNotifPanel = false;
      this.$router.push(`/khach-hang/chi-tiet-bat-dong-san/${item.propertyId}`);
    },

    handleMouseDown(e, item) {
      if (e.button !== 0) return;
      this.isDragging = true;
      this.draggedItem = item;
      this.dragStartX = e.clientX;
      item.swipeOffset = 0;
      e.currentTarget.style.cursor = "grabbing";
    },

    handleMouseMove(e, item) {
      if (!this.isDragging || this.draggedItem !== item) return;
      const diff = e.clientX - this.dragStartX;
      if (diff < 0) item.swipeOffset = Math.max(diff, -120);
    },

    handleMouseUp(e, item) {
      if (!this.isDragging || this.draggedItem !== item) return;
      e.currentTarget.style.cursor = "pointer";
      item.swipeOffset = item.swipeOffset < -80 ? -120 : 0;
      this.isDragging = false;
      this.draggedItem = null;
    },

    async deleteSavedItem(item) {
      const token = localStorage.getItem("khach_hang_auth_token");
      if (!token) return;

      try {
        await api.post("/khach-hang/bds/yeu-thich", { bds_id: item.propertyId });

        const index = this.notifications.findIndex((n) => n.id === item.id);
        if (index === -1) return;

        this.notifications[index].isDeleting = true;
        this.notifications[index].swipeOffset = 0;

        setTimeout(() => {
          this.notifications.splice(index, 1);
          toaster.success("Đã xóa tin khỏi danh sách lưu", { duration: 2000 });
          window.dispatchEvent(new Event("favorite-updated"));
        }, 300);
      } catch (err) {
        console.error("Lỗi xóa tin đã lưu:", err.response?.data || err);
        toaster.error("Có lỗi xảy ra khi xóa", { duration: 2000 });
        item.swipeOffset = 0;
      }
    },

    goToSavedPage() {
      this.showNotifPanel = false;
      this.$router.push("/khach-hang/yeu-thich");
    },

    // ===== 🔥 CHAT METHODS =====
    async loadConversations() {
      const token = localStorage.getItem("khach_hang_auth_token");
      if (!token) return;

      this.loadingChat = true;
      try {
        const res = await api.get("/khach-hang/chat/conversations");

        this.conversations = res.data.data || [];

        // Calculate unread count
        this.unreadCount = this.conversations.filter((c) => {
          return c.last_message && !c.last_message.is_read;
        }).length;
      } catch (err) {
        console.error("Lỗi load conversations:", err);
        this.conversations = [];
        this.unreadCount = 0;
        // ✅ Không tự redirect ở đây — để axios interceptor xử lý 401 tập trung
        if (err.response?.status === 401) {
          this.clearData();
        }
      } finally {
        this.loadingChat = false;
      }
    },

    toggleChatPanel() {
      this.showChatPanel = !this.showChatPanel;
      this.showMenu = false;
      this.showNotifPanel = false;

      if (this.showChatPanel && this.isLoggedIn) {
        this.loadConversations();
      }
    },

    hasUnread(c) {
      return c.last_message && !c.last_message.is_read;
    },

    getBrokerInitial(c) {
      const name = c.moi_gioi?.ten || "M";
      return name.trim().charAt(0).toUpperCase();
    },

    truncateMessage(text, maxLength = 35) {
      if (!text) return "";
      const clean = text.replace(/<[^>]*>/g, "").trim();
      return clean.length > maxLength
        ? clean.substring(0, maxLength) + "..."
        : clean;
    },

    formatChatTime(dateStr) {
      if (!dateStr) return "";
      const now = new Date();
      const msgDate = new Date(dateStr);
      const diff = Math.floor((now - msgDate) / 1000);

      if (diff < 60) return "Vừa xong";
      if (diff < 3600) return `${Math.floor(diff / 60)}p`;
      if (diff < 86400) return `${Math.floor(diff / 3600)}g`;
      return msgDate.toLocaleDateString("vi-VN", {
        day: "2-digit",
        month: "2-digit",
      });
    },

    openChat(c) {
      this.showChatPanel = false;

      // Dispatch event to open chat modal/page
      window.dispatchEvent(
        new CustomEvent("open-chat", {
          detail: {
            conversationId: c.id,
            brokerId: c.moi_gioi?.id,
            brokerName: c.moi_gioi?.ten,
            propertyName: c.bat_dong_san?.tieu_de,
            propertyId: c.bat_dong_san?.id,
          },
        })
      );
    },

    // 🔥 Handle new message event (from WebSocket or other source)
    handleNewMessage(event) {
      // Refresh conversations when new message arrives
      if (this.showChatPanel) {
        this.loadConversations();
      } else {
        // Just increment unread count if panel is closed
        this.unreadCount++;
      }
    },
  },
};
</script>

<style scoped>
/* ===== NAVIGATION MENU ===== */
.nav-menu {
  display: flex;
  align-items: center;
  gap: 8px;
}

.nav-link {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 10px 16px;
  border-radius: 10px;
  text-decoration: none;
  color: #475569;
  font-weight: 500;
  font-size: 14px;
  transition: all 0.2s ease;
  position: relative;
  border-bottom: 2px solid transparent;
  /* Chuẩn bị cho gạch dưới */
}

.nav-link .nav-icon {
  font-size: 16px;
  opacity: 0.8;
  transition: opacity 0.2s;
}

.nav-link .nav-label {
  white-space: nowrap;
}

/* Hover state */
.nav-link:hover {
  background: rgba(59, 130, 246, 0.1);
  color: #3b82f6;
}

.nav-link:hover .nav-icon {
  opacity: 1;
}

/* Active state - CHỈ CÓ GẠCH DƯỚI */
.nav-link.router-link-active,
.nav-link.router-link-exact-active {
  color: #3b82f6;
  font-weight: 600;
  border-bottom: 2px solid #3b82f6;
  /* Gạch dưới màu xanh */
  background: transparent;
  /* Không nền */
  box-shadow: none;
  /* Không shadow */
}

.nav-link.router-link-active .nav-icon,
.nav-link.router-link-exact-active .nav-icon {
  opacity: 1;
}

/* Responsive */
@media (max-width: 1024px) {
  .nav-menu {
    gap: 4px;
    padding: 4px;
  }

  .nav-link {
    padding: 10px 12px;
  }

  .nav-link .nav-label {
    display: none;
  }

  .nav-link .nav-icon {
    font-size: 18px;
  }
}

@media (max-width: 768px) {
  .nav-menu {
    display: none;
  }
}

/* ===== HEADER BASE ===== */
.header {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  height: 72px;
  background: rgba(255, 255, 255, 0.98);
  backdrop-filter: blur(12px);
  border-bottom: 1px solid rgba(0, 0, 0, 0.06);
  z-index: 1000;
  overflow: visible !important;
}

.container {
  max-width: 1280px;
  margin: 0 auto;
  height: 100%;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 24px;
  overflow: visible !important;
}

.logo {
  display: flex;
  align-items: center;
  gap: 10px;
  text-decoration: none;
}

.logo-img {
  width: 40px;
  height: 40px;
  object-fit: cover;
  transition: transform 0.2s ease;
}

.logo-text {
  display: flex;
  flex-direction: column;
  line-height: 1.1;
}

.brand {
  font-weight: 800;
  color: #1e293b;
  font-size: 17px;
}

.sub {
  font-weight: 600;
  color: #3b82f6;
  font-size: 12px;
}

.actions {
  display: flex;
  align-items: center;
  gap: 12px;
  position: relative;
  overflow: visible !important;
}

/* ===== BUTTONS ===== */
.btn-post {
  display: flex;
  align-items: center;
  gap: 7px;
  padding: 10px 20px;
  border-radius: 999px;
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
  font-weight: 700;
  font-size: 13px;
  border: none;
  cursor: pointer;
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 4px 14px rgba(16, 185, 129, 0.3);
}

.btn-post:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(16, 185, 129, 0.45);
}

.btn-post:active {
  transform: translateY(0) scale(0.98);
  transition: transform 0.1s ease;
}

.btn-post .icon {
  font-size: 15px;
}

.btn-post .label {
  display: none;
}

@media (min-width: 640px) {
  .btn-post .label {
    display: inline;
  }
}

.saved-trigger-wrapper {
  position: relative;
}

.btn-saved {
  display: flex;
  align-items: center;
  gap: 7px;
  padding: 10px 20px;
  border-radius: 999px;
  background: white;
  color: #ef4444;
  font-weight: 600;
  font-size: 13px;
  border: 1px solid #fecaca;
  cursor: pointer;
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.btn-saved:hover {
  background: #fef2f2;
  border-color: #ef4444;
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(239, 68, 68, 0.25);
}

.btn-saved:active {
  transform: translateY(0) scale(0.98);
  transition: transform 0.1s ease;
}

.btn-saved .icon {
  font-size: 15px;
}

.btn-saved .label {
  display: none;
}

@media (min-width: 640px) {
  .btn-saved .label {
    display: inline;
  }
}

.btn-login {
  padding: 10px 22px;
  border-radius: 999px;
  background: #3b82f6;
  color: white;
  border: none;
  font-weight: 600;
  font-size: 13px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-login:hover {
  background: #2563eb;
  transform: translateY(-1px);
}

.btn-login:active {
  transform: scale(0.98);
  transition: transform 0.1s ease;
}

/* ===== USER WRAPPER - XANH DA TRỜI ===== */
.user-wrapper {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 6px 12px;
  border-radius: 999px;
  background: #eff6ff;
  border: 1px solid #bfdbfe;
  transition: all 0.2s ease;
  position: relative;
  cursor: pointer;
}

.user-wrapper:hover {
  background: #dbeafe;
  border-color: #93c5fd;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
}

.avatar-container {
  position: relative;
}

.avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: linear-gradient(135deg, #3b82f6, #2563eb);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 14px;
  box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
}

.status-dot {
  position: absolute;
  bottom: -2px;
  right: -2px;
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: #3b82f6;
  border: 2px solid white;
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
}

.user-name {
  font-weight: 600;
  color: #1e40af;
  font-size: 14px;
  max-width: 120px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.arrow {
  font-size: 10px;
  color: #2563eb;
  transition: transform 0.2s ease;
  margin-left: 2px;
}

.arrow.open {
  transform: rotate(180deg);
}

/* ===== DROPDOWN MENU - XANH DA TRỜI ===== */
@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-8px) scale(0.98);
  }

  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.dropdown-fade-enter-active,
.dropdown-fade-leave-active {
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.dropdown-fade-enter-from,
.dropdown-fade-leave-to {
  opacity: 0;
  transform: translateY(-8px) scale(0.98);
}

.dropdown-menu-new {
  position: absolute;
  top: calc(100% + 12px);
  right: 0;
  width: 300px;
  background: white;
  border-radius: 16px;
  box-shadow: 0 20px 60px rgba(59, 130, 246, 0.15);
  border: 1px solid #bfdbfe;
  z-index: 9999;
  overflow: hidden;
  animation: slideDown 0.2s ease-out;
}

.dropdown-header-new {
  padding: 20px;
  background: linear-gradient(135deg, #3b82f6, #2563eb);
  color: white;
  display: flex;
  align-items: center;
  gap: 12px;
}

.avatar-large-new {
  width: 52px;
  height: 52px;
  border-radius: 50%;
  background: white;
  color: #3b82f6;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 20px;
  flex-shrink: 0;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.user-info-new {
  flex: 1;
  min-width: 0;
}

.user-display-name {
  margin: 0;
  font-weight: 600;
  font-size: 16px;
  color: white;
}

.user-role-badge {
  display: inline-block;
  margin-top: 4px;
  font-size: 11px;
  background: rgba(255, 255, 255, 0.2);
  padding: 2px 10px;
  border-radius: 12px;
  color: white;
  font-weight: 500;
}

.dropdown-items-new {
  padding: 8px 0;
}

.dropdown-item-new {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 20px;
  color: #374151;
  text-decoration: none;
  font-size: 14px;
  transition: all 0.15s ease;
  position: relative;
}

.dropdown-item-new:hover {
  background: #eff6ff;
  padding-left: 24px;
  color: #1e40af;
}

.item-icon-new {
  font-size: 18px;
  width: 24px;
  text-align: center;
}

.item-label-new {
  flex: 1;
  font-weight: 500;
}

.item-arrow-new {
  color: #9ca3af;
  font-size: 14px;
  opacity: 0;
  transform: translateX(-4px);
  transition: all 0.15s ease;
}

.dropdown-item-new:hover .item-arrow-new {
  opacity: 1;
  transform: translateX(0);
}

/* Chat với môi giới - Special Style */
.chat-broker-item {
  background: linear-gradient(135deg,
      rgba(59, 130, 246, 0.08),
      rgba(37, 99, 235, 0.08));
  border-left: 3px solid #3b82f6;
  cursor: pointer;
}

.chat-broker-item:hover {
  background: linear-gradient(135deg,
      rgba(59, 130, 246, 0.15),
      rgba(37, 99, 235, 0.15));
  padding-left: 24px;
}

.item-badge-new {
  font-size: 9px;
  background: #3b82f6;
  color: white;
  padding: 2px 8px;
  border-radius: 8px;
  font-weight: 600;
}

.dropdown-divider-new {
  height: 1px;
  background: linear-gradient(to right, transparent, #e5e7eb, transparent);
  margin: 4px 20px;
}

.dropdown-footer-new {
  padding: 8px 20px 16px;
}

.settings-item {
  border-radius: 10px;
  transition: background 0.15s;
}

.settings-item:hover {
  background: #f9fafb;
}

.logout-btn-new {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 11px 16px;
  background: none;
  border: 1px solid #fee2e2;
  border-radius: 10px;
  color: #ef4444;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.2s ease;
  margin-top: 4px;
}

.logout-btn-new:hover {
  background: #fef2f2;
  border-color: #ef4444;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.15);
}

.logout-btn-new:active {
  transform: scale(0.99);
}

.logout-icon-new {
  font-size: 16px;
}

/* ===== NOTIFICATION PANEL ===== */
.notif-panel {
  position: absolute;
  top: calc(100% + 12px);
  right: 0;
  width: 420px;
  max-height: 85vh;
  background: #ffffff;
  border-radius: 16px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
  border: 1px solid #e5e7eb;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  z-index: 9999;
}

.notif-header {
  padding: 18px 20px;
  border-bottom: 1px solid #e5e7eb;
  background: #f9fafb;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-shrink: 0;
}

.notif-title {
  color: #111827;
  font-size: 18px;
  font-weight: 700;
  margin: 0;
}

.notif-count {
  color: #6b7280;
  font-size: 13px;
  font-weight: 500;
  background: #f3f4f6;
  padding: 4px 12px;
  border-radius: 20px;
}

.notif-list {
  flex: 1;
  overflow-y: auto;
  padding: 8px 0;
  background: #ffffff;
  max-height: calc(85vh - 130px);
}

.notif-list::-webkit-scrollbar {
  width: 6px;
}

.notif-list::-webkit-scrollbar-thumb {
  background: #d1d5db;
  border-radius: 3px;
}

.notif-list::-webkit-scrollbar-track {
  background: transparent;
}

.notif-item-wrapper {
  position: relative;
  overflow: hidden;
}

.notif-item-swipeable {
  position: relative;
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  will-change: transform;
}

.notif-delete-btn {
  position: absolute;
  right: 0;
  top: 0;
  bottom: 0;
  width: 120px;
  background: linear-gradient(135deg, #ef4444, #dc2626);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 4px;
  color: white;
  cursor: pointer;
  user-select: none;
  z-index: 0;
}

.notif-delete-btn:hover {
  background: linear-gradient(135deg, #dc2626, #b91c1c);
}

.notif-delete-btn .material-symbols-outlined {
  font-size: 24px;
}

.notif-delete-btn span:last-child {
  font-size: 12px;
  font-weight: 600;
}

.notif-item {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 14px 20px;
  cursor: pointer;
  transition: background 0.15s;
  background: white;
  border-bottom: 1px solid #f3f4f6;
  position: relative;
  z-index: 1;
  user-select: none;
}

.notif-item:last-child {
  border-bottom: none;
}

.notif-item:hover {
  background: #f9fafb;
}

.notif-item.dragging {
  cursor: grabbing;
}

.notif-image-wrapper {
  flex-shrink: 0;
  width: 64px;
  height: 64px;
  border-radius: 12px;
  overflow: hidden;
  border: 1px solid #e5e7eb;
}

.notif-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.notif-content {
  flex: 1;
  min-width: 0;
}

.notif-text {
  color: #1f2937;
  font-size: 14px;
  line-height: 1.4;
  margin: 0 0 4px;
  font-weight: 600;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.notif-time {
  color: #6b7280;
  font-size: 12px;
  display: block;
}

.notif-arrow {
  color: #9ca3af;
  font-size: 18px;
  flex-shrink: 0;
}

.notif-empty {
  text-align: center;
  padding: 60px 20px;
  color: #6b7280;
}

.notif-empty-icon {
  font-size: 56px;
  margin-bottom: 16px;
  opacity: 0.4;
  display: block;
}

.notif-empty-title {
  font-size: 15px;
  font-weight: 600;
  color: #374151;
  margin: 0 0 6px;
}

.notif-empty-hint {
  font-size: 13px;
  color: #9ca3af;
  margin: 0;
}

.notif-loading-spinner {
  width: 28px;
  height: 28px;
  border: 3px solid #e5e7eb;
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 16px;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.notif-footer {
  padding: 14px 20px;
  border-top: 1px solid #e5e7eb;
  background: #f9fafb;
  flex-shrink: 0;
}

.notif-btn-primary {
  background: #3b82f6;
  color: white;
  border: none;
  padding: 11px 16px;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  width: 100%;
  transition: all 0.2s;
}

.notif-btn-primary:hover {
  background: #2563eb;
  transform: translateY(-1px);
}

.notif-item-wrapper {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.notif-item-wrapper.deleting {
  opacity: 0;
  transform: translateX(-100%);
}

.notif-slide-enter-active,
.notif-slide-leave-active {
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.notif-slide-enter-from,
.notif-slide-leave-to {
  opacity: 0;
  transform: translateY(-10px) scale(0.98);
}

/* ===== 🔥 CHAT STYLES ===== */
.chat-trigger-wrapper {
  position: relative;
  margin-right: 4px;
}

.btn-chat {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 42px;
  height: 42px;
  border-radius: 50%;
  background: white;
  border: 1px solid #bfdbfe;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 18px;
  color: #3b82f6;
}

.btn-chat:hover {
  background: #eff6ff;
  border-color: #93c5fd;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
}

.btn-chat:active {
  transform: scale(0.98);
}

.chat-icon {
  transition: transform 0.2s ease;
}

.btn-chat:hover .chat-icon {
  transform: scale(1.1);
}

.chat-badge {
  position: absolute;
  top: -4px;
  right: -4px;
  min-width: 20px;
  height: 20px;
  background: linear-gradient(135deg, #ef4444, #dc2626);
  color: white;
  font-size: 10px;
  font-weight: 700;
  padding: 0 6px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid white;
  box-shadow: 0 2px 6px rgba(239, 68, 68, 0.3);
  animation: pulse 2s infinite;
}

@keyframes pulse {

  0%,
  100% {
    transform: scale(1);
  }

  50% {
    transform: scale(1.05);
  }
}

/* Chat Panel */
.chat-panel {
  position: absolute;
  top: calc(100% + 12px);
  right: 0;
  width: 340px;
  max-height: 520px;
  background: white;
  border-radius: 16px;
  box-shadow: 0 20px 60px rgba(59, 130, 246, 0.15);
  border: 1px solid #bfdbfe;
  overflow: hidden;
  z-index: 9999;
  display: flex;
  flex-direction: column;
}

.chat-header {
  padding: 14px 18px;
  background: linear-gradient(135deg, #3b82f6, #2563eb);
  color: white;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-shrink: 0;
}

.chat-title {
  font-weight: 600;
  font-size: 15px;
  display: flex;
  align-items: center;
  gap: 6px;
}

.chat-close {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  border-radius: 8px;
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: white;
  transition: background 0.2s;
}

.chat-close:hover {
  background: rgba(255, 255, 255, 0.3);
}

.chat-close .material-symbols-outlined {
  font-size: 18px;
}

.chat-list {
  flex: 1;
  overflow-y: auto;
  padding: 8px 0;
  background: #fafafa;
  max-height: 420px;
}

.chat-list::-webkit-scrollbar {
  width: 5px;
}

.chat-list::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}

.chat-list::-webkit-scrollbar-track {
  background: transparent;
}

.chat-item {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 12px 18px;
  cursor: pointer;
  transition: background 0.15s ease;
  position: relative;
  border-bottom: 1px solid #f1f5f9;
}

.chat-item:last-child {
  border-bottom: none;
}

.chat-item:hover {
  background: #eff6ff;
}

.chat-item:active {
  background: #dbeafe;
}

.chat-avatar {
  flex-shrink: 0;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, #60a5fa, #3b82f6);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 14px;
  box-shadow: 0 2px 8px rgba(59, 130, 246, 0.25);
}

.chat-info {
  flex: 1;
  min-width: 0;
  padding-right: 20px;
}

.bds-title {
  font-weight: 600;
  font-size: 14px;
  color: #1e293b;
  margin: 0 0 2px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.broker-name {
  font-size: 12px;
  color: #64748b;
  margin: 0 0 4px;
}

.last-message {
  font-size: 12px;
  color: #94a3b8;
  margin: 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.last-message.unread {
  color: #3b82f6;
  font-weight: 500;
}

.unread-dot {
  position: absolute;
  right: 18px;
  top: 20px;
  width: 8px;
  height: 8px;
  background: #3b82f6;
  border-radius: 50%;
  border: 2px solid white;
  box-shadow: 0 0 0 1px rgba(59, 130, 246, 0.3);
  animation: dotPulse 1.5s infinite;
}

@keyframes dotPulse {

  0%,
  100% {
    opacity: 1;
    transform: scale(1);
  }

  50% {
    opacity: 0.7;
    transform: scale(0.9);
  }
}

.chat-time {
  position: absolute;
  right: 18px;
  top: 14px;
  font-size: 10px;
  color: #94a3b8;
  font-weight: 500;
}

.chat-empty {
  text-align: center;
  padding: 40px 20px;
  color: #64748b;
}

.chat-empty-icon {
  font-size: 40px;
  opacity: 0.5;
  display: block;
  margin-bottom: 12px;
}

.chat-empty p {
  font-size: 13px;
  margin: 0;
}

.chat-footer {
  padding: 12px 18px 16px;
  border-top: 1px solid #e2e8f0;
  background: white;
  flex-shrink: 0;
}

.chat-view-all {
  display: block;
  width: 100%;
  padding: 10px;
  background: #3b82f6;
  color: white;
  text-align: center;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.2s ease;
}

.chat-view-all:hover {
  background: #2563eb;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.chat-view-all:active {
  transform: scale(0.99);
}

/* Chat Loading */
.chat-loading {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 30px;
  color: #64748b;
  font-size: 13px;
  gap: 10px;
}

.chat-loading-spinner {
  width: 20px;
  height: 20px;
  border: 2px solid #e2e8f0;
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

/* ===== MODAL ANIMATIONS ===== */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
  padding: 20px;
}

.modal-smooth-enter-active,
.modal-smooth-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.modal-smooth-enter-from,
.modal-smooth-leave-to {
  opacity: 0;
}

.modal-smooth-enter-from .modal-box,
.modal-smooth-leave-to .modal-box {
  transform: scale(0.97) translateY(8px);
  transition: transform 0.25s ease 0.05s, opacity 0.2s ease;
}

.modal-box {
  background: white;
  border-radius: 20px;
  width: 100%;
  max-width: 420px;
  box-shadow: 0 25px 80px rgba(0, 0, 0, 0.25);
  overflow: hidden;
}

.modal-header {
  padding: 20px 24px;
  background: linear-gradient(135deg, #3b82f6, #2563eb);
  display: flex;
  align-items: center;
  gap: 12px;
}

.modal-icon {
  font-size: 24px;
}

.modal-title {
  margin: 0;
  color: white;
  font-weight: 700;
  font-size: 16px;
}

.modal-body {
  padding: 24px;
}

.modal-text {
  margin: 0;
  color: #475569;
  font-size: 14px;
  line-height: 1.6;
}

.modal-text strong {
  color: #1e293b;
}

.modal-footer {
  padding: 16px 24px 24px;
  display: flex;
  gap: 12px;
  justify-content: flex-end;
}

.btn-modal {
  padding: 10px 20px;
  border-radius: 10px;
  font-weight: 600;
  font-size: 13px;
  cursor: pointer;
  transition: all 0.2s ease;
  border: none;
}

.btn-modal.cancel {
  background: #f1f5f9;
  color: #475569;
}

.btn-modal.cancel:hover {
  background: #e2e8f0;
  transform: translateY(-1px);
}

.btn-modal.confirm {
  background: linear-gradient(135deg, #3b82f6, #2563eb);
  color: white;
  box-shadow: 0 4px 14px rgba(59, 130, 246, 0.3);
}

.btn-modal.confirm:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(59, 130, 246, 0.45);
}

.btn-modal:active {
  transform: scale(0.98);
  transition: transform 0.1s ease;
}
</style>