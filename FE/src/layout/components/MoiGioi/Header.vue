<template>
  <div>
    <nav class="header" role="navigation" aria-label="Header navigation">
      <div class="container">
        <div class="actions">
          <template v-if="!isLoggedIn">
            <button @click="goToLogin" class="btn-login" aria-label="Đăng nhập">
              Đăng nhập
            </button>
            <button @click="goToRegister" class="btn-register" aria-label="Đăng ký môi giới">
              Đăng ký môi giới
            </button>
          </template>

          <div v-else class="user-box">

            <div v-if="isLoggedIn" class="quick-post-wrapper me-2">
              <button class="quick-post-btn" @click="handleQuickPost" :disabled="loadingPosts" title="Đăng tin mới"
                type="button">
                <i class="bi bi-plus-circle me-1"></i>
                <span class="post-label d-none d-md-inline">Đăng tin</span>
                <span v-if="!loadingPosts" class="post-count-badge ms-2" :class="postsBadgeClass">
                  {{ postsDisplay }}
                </span>
                <span v-else class="post-count-badge ms-2 badge-loading">...</span>
              </button>
            </div>
            <!-- 🔔 Notification Bell -->
            <div class="notification-wrapper" v-if="isLoggedIn">
              <button class="notification-btn" @click="toggleNotifications" title="Thông báo" aria-label="Thông báo"
                :aria-expanded="showNotifications">
                <svg class="bell-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <span v-if="unreadCount > 0" class="notification-badge">
                  {{ unreadCount > 9 ? "9+" : unreadCount }}
                </span>
              </button>

              <transition name="dropdown-slide">
                <div v-show="showNotifications" class="notification-dropdown" @click.stop>
                  <div class="dropdown-top">
                    <h3 class="dropdown-title">Thông báo</h3>
                    <button v-if="unreadCount > 0" @click="markAllAsRead" class="mark-all-link">
                      Đánh dấu tất cả là đã đọc
                    </button>
                  </div>

                  <div v-if="loadingNotifications" class="dropdown-loading">
                    <div class="spinner"></div>
                    <p>Đang tải...</p>
                  </div>

                  <div v-else-if="notifications.length === 0" class="dropdown-empty">
                    <p class="empty-text">Không có thông báo</p>
                  </div>

                  <div v-else class="dropdown-list">
                    <div v-for="item in notifications.slice(0, 6)" :key="item.id" class="notification-item-wrapper">
                      <div class="notification-item" :class="{ 'is-unread': !item.da_doc }"
                        @click="handleNotificationClick(item)">
                        <button class="delete-btn" @click.stop="deleteNotification(item.id)" title="Xóa thông báo"
                          aria-label="Xóa thông báo">
                          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                          </svg>
                        </button>
                        <div class="item-icon" :class="getIconClass(item.loai)" @click.stop="openCustomerModal(item)">
                          <span v-if="
                            item.loai === 'khach_moi' || item.loai === 'user'
                          ">👤</span>
                          <span v-else-if="
                            item.loai === 'approved' || item.loai === 'duyet'
                          ">✅</span>
                          <span v-else-if="
                            item.loai === 'binh_luan' ||
                            item.loai === 'comment'
                          ">💬</span>
                          <span v-else>🔔</span>
                        </div>
                        <div class="item-content">
                          <div class="item-header">
                            <p class="item-title">{{ item.tieu_de }}</p>
                            <span class="item-time">{{
                              formatTime(item.thoi_gian)
                            }}</span>
                          </div>
                          <p class="item-desc">{{ item.noi_dung }}</p>
                        </div>
                        <span v-if="!item.da_doc" class="unread-indicator"></span>
                      </div>
                    </div>
                  </div>

                  <div class="dropdown-footer">
                    <router-link to="/moi-gioi/thong-bao" class="view-all-link" @click="showNotifications = false">
                      Xem tất cả thông báo
                    </router-link>
                  </div>
                </div>
              </transition>
            </div>

            <!-- ✅ USER MENU - DESIGN MỚI -->
            <div class="avatar-wrapper" @click="toggleMenu" @keydown.enter="toggleMenu"
              @keydown.space.prevent="toggleMenu" role="button" tabindex="0" aria-haspopup="true"
              :aria-expanded="showMenu" aria-controls="broker-dropdown">
              <div class="avatar-container">
                <div class="avatar" aria-hidden="true">{{ userInitial }}</div>
                <span class="status-dot online"></span>
              </div>
              <span class="avatar-name">{{ userShortName }}</span>
              <span class="arrow" :class="{ rotate: showMenu }" aria-hidden="true">▼</span>
            </div>

            <transition name="dropdown-fade">
              <div v-show="showMenu" id="broker-dropdown" class="dropdown-menu-new" @click.stop role="menu"
                aria-label="Menu tài khoản">
                <!-- Header: User Info -->
                <div class="dropdown-header-new">
                  <div class="avatar-large-new">{{ userInitial }}</div>
                  <div class="user-info-new">
                    <p class="user-display-name">{{ userName }}</p>
                    <span class="user-code-badge">Mã MG: {{ userCode }}</span>
                  </div>
                </div>

                <!-- Stats -->
                <div class="dropdown-stats-new">
                  <div class="stat-item-new">
                    <span class="stat-value-new">{{ totalListings }}</span>
                    <span class="stat-label-new">Tin đăng</span>
                  </div>
                  <div class="stat-item-new">
                    <span class="stat-value-new">{{ totalCustomers }}</span>
                    <span class="stat-label-new">Khách hàng</span>
                  </div>
                </div>

                <!-- Menu Items -->
                <div class="dropdown-items-new">
                  <!-- 📊 Dashboard -->
                  <router-link to="/moi-gioi/dashboard" class="dropdown-item-new primary" role="menuitem"
                    @click="showMenu = false">
                    <span class="item-icon-new">📊</span>
                    <span class="item-label-new">Dashboard</span>
                    <span class="item-badge-new hot">MỚI</span>
                  </router-link>

                  <!-- Đăng tin mới -->
                  <router-link to="/moi-gioi/dang-tin" class="dropdown-item-new" role="menuitem"
                    @click="showMenu = false">
                    <span class="item-icon-new">✎</span>
                    <span class="item-label-new">Đăng tin mới</span>
                  </router-link>

                  <!-- Quản lý tin đăng -->
                  <router-link to="/moi-gioi/quan-ly-bat-dong-san" class="dropdown-item-new" role="menuitem"
                    @click="showMenu = false">
                    <span class="item-icon-new">📋</span>
                    <span class="item-label-new">Quản lý tin đăng</span>
                    <span class="item-arrow-new">→</span>
                  </router-link>

                  <!-- Danh sách khách hàng -->
                  <router-link to="/moi-gioi/quan-ly-khach-hang" class="dropdown-item-new" role="menuitem"
                    @click="showMenu = false">
                    <span class="item-icon-new">👥</span>
                    <span class="item-label-new">Danh sách khách hàng</span>
                    <span class="item-arrow-new">→</span>
                  </router-link>

                  <!-- 🔥 MỚI: Chat với khách hàng -->
                  <div class="dropdown-item-new chat-customer-item" @click="handleChatWithCustomer" role="menuitem">
                    <span class="item-icon-new">💬</span>
                    <span class="item-label-new">Chat với khách hàng</span>
                    <span class="item-badge-new new">Mới</span>
                  </div>

                  <!-- Thống kê -->
                  <router-link to="/moi-gioi/trang-chu" class="dropdown-item-new" role="menuitem"
                    @click="showMenu = false">
                    <span class="item-icon-new">📊</span>
                    <span class="item-label-new">Thống kê hiệu suất</span>
                    <span class="item-arrow-new">→</span>
                  </router-link>
                </div>

                <!-- Divider -->
                <div class="dropdown-divider-new"></div>

                <!-- Footer: Settings + Logout -->
                <div class="dropdown-footer-new">
                  <router-link to="/moi-gioi/ho-so-ca-nhan" class="dropdown-item-new settings-item" role="menuitem"
                    @click="showMenu = false">
                    <span class="item-icon-new">⚙️</span>
                    <span class="item-label-new">Cài đặt tài khoản</span>
                  </router-link>

                  <button @click="handleLogout" class="logout-btn-new" role="menuitem"
                    aria-label="Đăng xuất khỏi tài khoản">
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

    <!-- Modal Khách hàng (giữ nguyên) -->
    <transition name="fade">
      <div v-if="showCustomerModal" class="modal-overlay" @click="closeModal">
        <div class="modal-box" @click.stop>
          <div class="modal-header">
            <h3>Thông tin khách hàng</h3>
            <button class="close-btn" @click="closeModal" aria-label="Đóng">
              ✕
            </button>
          </div>
          <div class="modal-body">
            <table class="info-table">
              <tbody>
                <tr>
                  <th>Họ và tên:</th>
                  <td>{{ selectedCustomer?.ten || "Không có thông tin" }}</td>
                </tr>
                <tr>
                  <th>Email:</th>
                  <td>
                    <span class="copyable" title="Bấm để sao chép"
                      @click="copyToClipboard(selectedCustomer?.email, 'Email')">{{
                        selectedCustomer?.email || "Không có thông tin"
                      }}</span>
                  </td>
                </tr>
                <tr>
                  <th>Số điện thoại:</th>
                  <td>
                    <span class="copyable" title="Bấm để sao chép" @click="
                      copyToClipboard(
                        selectedCustomer?.so_dien_thoai,
                        'Số điện thoại'
                      )
                      ">{{
                        selectedCustomer?.so_dien_thoai || "Không có thông tin"
                      }}</span>
                  </td>
                </tr>
                <tr v-if="selectedCustomer?.ghi_chu">
                  <th>Ghi chú:</th>
                  <td>{{ selectedCustomer.ghi_chu }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" @click="closeModal">Đóng</button>
            <button v-if="selectedCustomer?.email" class="btn btn-primary" @click="contactCustomer">
              Liên hệ Email
            </button>
            <button v-if="selectedCustomer" class="btn btn-emerald" @click="startChatWithCustomer">
              <i class="bi bi-chat-dots me-1"></i> Nhắn tin
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!-- Toast (giữ nguyên) -->
    <transition name="toast-fade">
      <div v-if="showToast" class="custom-toast">
        <svg class="toast-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span class="toast-text">{{ toastMessage }}</span>
        <button class="toast-close" @click="showToast = false" aria-label="Đóng thông báo">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </transition>
  </div>
</template>

<script>
import api from '@/axios/config';
import { subscribeUser, leaveAllChannels, updateEchoToken } from '@/js/services/echo';
export default {
  name: "MoiGioiHeader",
  data() {
    return {
      user: null,
      token: null,
      userType: null,
      showMenu: false,
      showNotifications: false,
      notifications: [],
      unreadCount: 0,
      loadingNotifications: false,
      menuTimer: null,
      pollingInterval: null,
      isDragging: false,
      showCustomerModal: false,
      selectedCustomer: null,
      showToast: false,
      toastMessage: "",
      remainingPosts: null, // null = đang loading, -1 = không giới hạn
      loadingPosts: false,
      hasNewNotifications: false, // ✅ Theo dõi thông báo mới
      statsListings: 0,
      statsCustomers: 0,
      selectedBdsId: null,
    };
  },
  computed: {
    isLoggedIn() {
      return !!(this.token && this.user && this.userType === "moi-gioi");
    },
    userName() {
      if (!this.user) return "Môi giới";
      return (
        this.user.ten ||
        this.user.ho_ten ||
        this.user.name ||
        this.user.email ||
        "Broker"
      );
    },
    userShortName() {
      const name = this.userName;
      const parts = name.trim().split(" ");
      return parts.length >= 2 ? parts[parts.length - 1] : parts[0] || "MG";
    },
    userInitial() {
      const name = this.userName;
      return name ? name.charAt(0).toUpperCase() : "M";
    },
    userCode() {
      if (!this.user?.id) return '---';
      return this.user?.ma_moi_gioi || this.user?.code || `MG-${String(this.user.id).padStart(4, '0')}`;
    },
    totalListings() {
      return this.statsListings;
    },
    totalCustomers() {
      return this.statsCustomers;
    },
    // ✅ Hiển thị số tin: "5 tin", "Hết", hoặc "∞"
    postsDisplay() {
      if (this.remainingPosts === null) return "...";
      if (this.remainingPosts === -1) return "∞"; // Gói không giới hạn
      if (this.remainingPosts <= 0) return "Hết";
      return `${this.remainingPosts} tin`;
    },

    // ✅ Màu badge theo trạng thái
    postsBadgeClass() {
      if (this.remainingPosts === null) return "badge-loading";
      if (this.remainingPosts <= 0) return "badge-empty";
      if (this.remainingPosts <= 3) return "badge-warning";
      return "badge-success";
    },
  },
  mounted() {
    this.checkLogin();
    this.fetchNotifications();
    this.fetchRemainingPosts();
    this.fetchDropdownStats();
    this.startPolling();
    // ✅ subscribeEcho gọi sau checkLogin (user đã có sẵn) trong một tick sau
    this.$nextTick(() => {
      this.subscribeEcho();
    });
    document.addEventListener("click", this.handleClickOutside);
    // ✅ storage event: chỉ trigger khi localStorage thay đổi từ TAB KHÁC
    // (KHÔNG dùng focus vì nó chạy khi switch tab làm checkLogin sai)
    window.addEventListener("storage", this._onStorageChange);
    // ✅ Lắng nghe auth thay đổi trong cùng tab
    window.addEventListener("moi-gioi-auth-changed", this.checkLogin);
    window.addEventListener("moi-gioi-posts-updated", this.fetchRemainingPosts);
    this.$router.afterEach(() => {
      this.showMenu = false;
      this.showNotifications = false;
      this.checkLogin(); // ✅ Re-check sau mỗi navigate
    });
  },
  beforeUnmount() {
    document.removeEventListener("click", this.handleClickOutside);
    window.removeEventListener("storage", this._onStorageChange);
    window.removeEventListener("moi-gioi-auth-changed", this.checkLogin);
    window.removeEventListener("moi-gioi-posts-updated", this.fetchRemainingPosts);
    if (this.menuTimer) clearTimeout(this.menuTimer);
    if (this.pollingInterval) clearInterval(this.pollingInterval);
    // ✅ Leave Echo channel khi unmount
    this.leaveEcho();
  },
  methods: {
    // ========== ✅ ECHO REALTIME (dùng service) ==========
    subscribeEcho() {
      const userId = this.user?.id;
      console.log('[Header] subscribeEcho called, userId:', userId, '| Echo ready:', !!window.Echo);
      if (!userId) return;

      subscribeUser(userId, (data) => {
        // ✅ Nếu là tin nhắn của chính mình gửi (từ tab khác) thì bỏ qua
        if (data.loai === 'tin_nhan' && data.sender_type === 'moi_gioi') {
          return;
        }

        // data là payload từ Laravel Broadcast
        this.notifications.unshift({
          id: data.id ?? Date.now(),
          loai: data.loai || 'khach_moi',
          tieu_de: data.tieu_de || 'Thông báo mới',
          noi_dung: data.noi_dung || '',
          thoi_gian: new Date().toISOString(),
          da_doc: false,
          khach_hang: data.khach_hang || null,
          khach_hang_id: data.khach_hang_id || null,
          bat_dong_san_id: data.bds_id || null,
        });
        this.unreadCount += 1;
        this.hasNewNotifications = true;

        // ✅ Hiển thị toast với nội dung phù hợp theo loại thông báo
        const toastMsg = this._buildToastMessage(data);
        if (this.$toast) {
          if (data.loai === 'tin_nhan') {
            this.$toast.info(toastMsg, { 
              position: 'top-right', 
              duration: 6000,
              onClick: () => {
                // Điều hướng sang trang chat
                this.$router.push({
                  path: '/moi-gioi/quan-ly-khach-hang',
                  query: { active_chat_id: data.conversation_id }
                });
              }
            });
          } else if (data.loai === 'tu_choi') {
            this.$toast.error(toastMsg, { position: 'top-right', duration: 6000 });
          } else if (data.loai === 'approved') {
            this.$toast.success(toastMsg, { position: 'top-right', duration: 6000 });
          } else {
            // yeu_thich / khach_moi / pending
            this.$toast.info(toastMsg, { position: 'top-right', duration: 6000 });
          }
        }
        
        // Cập nhật lại stats ở dropdown nếu đang mở
        this.fetchDropdownStats();
        if (this.showNotifications) {
          this.fetchNotifications();
        }
      });
    },

    // ✅ Build nội dung toast rõ ràng theo loại thông báo
    _buildToastMessage(data) {
      if (data.loai === 'yeu_thich' || data.loai === 'khach_moi') {
        return data.noi_dung || `❤️ ${data.tieu_de}`;
      }
      if (data.loai === 'approved') {
        return data.noi_dung || `✅ ${data.tieu_de}`;
      }
      if (data.loai === 'tu_choi') {
        return data.noi_dung || `❌ ${data.tieu_de}`;
      }
      return data.tieu_de || 'Bạn có thông báo mới!';
    },

    leaveEcho() {
      leaveAllChannels();
    },

    // ========== MODAL ==========
    openCustomerModal(item) {
      this.selectedCustomer = item.khach_hang;
      this.selectedBdsId = item.bat_dong_san_id || item.bds_id; // Lưu lại ID BĐS nếu có
      this.showCustomerModal = true;
      this.showNotifications = false;
      this.showMenu = false;
    },
    closeModal() {
      this.showCustomerModal = false;
      setTimeout(() => {
        this.selectedCustomer = null;
      }, 300);
    },
    contactCustomer() {
      if (this.selectedCustomer?.email)
        window.location.href = `mailto:${this.selectedCustomer.email}`;
      this.closeModal();
    },
    async startChatWithCustomer() {
      if (!this.selectedCustomer) return;
      
      this.triggerToast("Đang khởi tạo cuộc trò chuyện...");
      
      try {
        const payload = {
          khach_hang_id: this.selectedCustomer.id,
          // Nếu có thông tin BĐS từ notification thì đính kèm vào room chat luôn
          bat_dong_san_id: this.selectedBdsId || null
        };
        
        const res = await api.post('/moi-gioi/chat/start', payload);
        
        if (res.data?.status) {
          this.closeModal();
          // Chuyển hướng sang trang quản lý khách hàng (trang chat) và truyền ID cuộc hội thoại
          this.$router.push({
            path: '/moi-gioi/quan-ly-khach-hang',
            query: { active_chat_id: res.data.data.id }
          });
        }
      } catch (error) {
        console.error("Lỗi khởi tạo chat:", error);
        this.triggerToast("Không thể bắt đầu chat. Vui lòng thử lại.");
      }
    },
    // ========== TOAST & COPY ==========
    triggerToast(message) {
      this.toastMessage = message;
      this.showToast = true;
      setTimeout(() => {
        this.showToast = false;
      }, 3000);
    },
    async copyToClipboard(text, label) {
      if (!text) return;
      try {
        await navigator.clipboard.writeText(text);
        this.triggerToast(`Đã sao chép ${label}: ${text}`);
      } catch (err) {
        console.error("Không thể sao chép:", err);
      }
    },
    // ========== AUTH ==========
    // ✅ Chỉ react với storage event của key môi giới (tránh cross-tab)
    _onStorageChange(event) {
      if (event.key === 'moi_gioi_auth_token' || event.key === 'moi_gioi_user_info') {
        this.checkLogin();
      }
    },
    checkLogin() {
      const token = localStorage.getItem("moi_gioi_auth_token");
      const userStr = localStorage.getItem("moi_gioi_user_info");
      if (token) {
        try {
          this.token = token;
          this.user = userStr ? JSON.parse(userStr) : (this.user || { ten: "Môi giới" });
          this.userType = "moi-gioi";
          
          // ✅ Cập nhật token cho Echo
          updateEchoToken(token);

          // ✅ Re-subscribe Echo nếu chưa subscribe (ví dụ: login từ tab khác)
          this.subscribeEcho();

          // ✅ Refresh dữ liệu mỗi khi check login (chuyển trang, storage change)
          this.fetchRemainingPosts();
          this.fetchNotifications();
          this.fetchDropdownStats();
        } catch (e) {
          console.error("Parse user error:", e);
          this.clearData();
        }
      } else {
        this.clearData();
      }
    },
    clearData() {
      this.token = null;
      this.user = null;
      this.userType = null;
      this.showMenu = false;
    },
    goToLogin() {
      this.$router.push("/moi-gioi/dang-nhap");
    },
    goToRegister() {
      this.$router.push("/moi-gioi/dang-ky");
    },
    handleLogout() {
      localStorage.removeItem("moi_gioi_auth_token");
      localStorage.removeItem("moi_gioi_user_info");
      this.clearData();
      this.triggerToast("Đăng xuất thành công!");
      setTimeout(() => {
        this.$router.push("/moi-gioi/dang-nhap");
      }, 600);
    },
    // ========== NOTIFICATIONS ==========
    async fetchNotifications() {
      this.loadingNotifications = true;
      const token = localStorage.getItem("moi_gioi_auth_token");
      if (!token) return;
      try {
        const res = await api.get("/moi-gioi/thong-bao");
        if (res.data?.status === true && res.data.data) {
          this.notifications = res.data.data.map((item) => ({
            id: item.id,
            loai: this.determineNotificationType(item),
            tieu_de: item.tieu_de || "Thông báo mới",
            noi_dung: item.noi_dung || "",
            thoi_gian: item.created_at,
            da_doc: item.trang_thai === 1,
            khach_hang: item.khach_hang || null,
            bat_dong_san: item.bat_dong_san || null,
            khach_hang_id: item.khach_hang_id || null,
            bat_dong_san_id: item.bat_dong_san_id || null,
          }));
          this.unreadCount = this.notifications.filter((n) => !n.da_doc).length;
        } else {
          this.notifications = [];
          this.unreadCount = 0;
        }
      } catch (error) {
        console.error("Lỗi tải thông báo:", error);
      } finally {
        this.loadingNotifications = false;
      }
    },
    determineNotificationType(item) {
      const tieuDe = (item.tieu_de || "").toLowerCase();
      if (tieuDe.includes("khách hàng") || tieuDe.includes("user"))
        return "khach_moi";
      if (tieuDe.includes("duyệt") || tieuDe.includes("phê duyệt"))
        return "approved";
      if (tieuDe.includes("bình luận")) return "binh_luan";
      return "khach_moi";
    },
    getIconClass(loai) {
      const classes = {
        user: "icon-blue",
        khach_moi: "icon-blue",
        order: "icon-pink",
        don_hang: "icon-pink",
        pdf: "icon-green",
        file: "icon-green",
        feedback: "icon-yellow",
        phan_hoi: "icon-yellow",
        approved: "icon-cyan",
        duyet: "icon-cyan",
        comment: "icon-red",
        binh_luan: "icon-red",
      };
      return classes[loai] || "icon-gray";
    },
    formatTime(isoString) {
      if (!isoString) return "";
      const date = new Date(isoString),
        diffSec = Math.floor((new Date() - date) / 1000);
      if (diffSec < 60) return `${diffSec} giây trước`;
      if (diffSec < 3600) return `${Math.floor(diffSec / 60)} phút trước`;
      if (diffSec < 86400) return `${Math.floor(diffSec / 3600)} giờ trước`;
      if (diffSec < 604800) return `${Math.floor(diffSec / 86400)} ngày trước`;
      return date.toLocaleDateString("vi-VN");
    },
    toggleNotifications(e) {
      e?.stopPropagation();
      this.showNotifications = !this.showNotifications;
      if (this.showNotifications) this.showMenu = false;
      if (this.showNotifications && this.notifications.length === 0)
        this.fetchNotifications();
    },
    handleNotificationClick(item) {
      if (this.isDragging) return;
      if (!item.da_doc) {
        item.da_doc = true;
        this.unreadCount = Math.max(0, this.unreadCount - 1);
        this.markAsRead(item.id);
      }
    },
    async markAsRead(id) {
      try {
        const token = localStorage.getItem("moi_gioi_auth_token");
        await api.post(`/moi-gioi/thong-bao/${id}/da-doc`);
      } catch (error) {
        console.error(error);
      }
    },
    async markAllAsRead() {
      try {
        const token = localStorage.getItem("moi_gioi_auth_token");
        const response = await api.post("/moi-gioi/thong-bao/doc-tat-ca");
        if (response.data) {
          this.notifications.forEach((n) => (n.da_doc = true));
          this.unreadCount = 0;
        }
      } catch (error) {
        console.error("Mark all as read error:", error);
      }
    },
    async deleteNotification(id) {
      const index = this.notifications.findIndex((n) => n.id === id);
      if (index === -1) return;
      const removed = this.notifications.splice(index, 1)[0];
      if (!removed.da_doc) this.unreadCount = Math.max(0, this.unreadCount - 1);
      try {
        const token = localStorage.getItem("moi_gioi_auth_token");
        await api.delete(`/moi-gioi/thong-bao/${id}`);
      } catch (e) {
        console.error("Delete error:", e);
        this.notifications.splice(index, 0, removed);
      }
    },
    // ========== MENU & UTILS ==========
    toggleMenu(e) {
      e?.stopPropagation();
      if (this.menuTimer) clearTimeout(this.menuTimer);
      this.menuTimer = setTimeout(() => {
        this.showMenu = !this.showMenu;
        if (this.showMenu) this.showNotifications = false;
      }, 100);
    },
    handleClickOutside(e) {
      if (!e.target.closest(".user-box") && !e.target.closest(".modal-box")) {
        if (this.menuTimer) clearTimeout(this.menuTimer);
        this.menuTimer = setTimeout(() => {
          this.showMenu = false;
          this.showNotifications = false;
        }, 150);
      }
    },


    // ✅ MỚI: Xử lý Chat với khách hàng
    handleChatWithCustomer() {
      this.showMenu = false;
      // Option 1: Router push (nếu có trang chat)
      // this.$router.push('/moi-gioi/chat-voi-khach-hang');
      // Option 2: Emit event để parent mở modal
      // this.$emit('open-chat-modal');
      // Option 3: Mở Zalo (thay số thật)
      // window.open('https://zalo.me/0123456789', '_blank');
      // Option 4: Chỉ toast + log (để dev test)
      this.triggerToast("Đang kết nối với khách hàng...", 2000);
      console.log("🔗 Mở chat với khách hàng cho broker:", this.user?.id);
    },
    // ✅ Method gọi API lấy số tin còn lại (giống Dashboard)
    async fetchRemainingPosts() {
      if (!this.isLoggedIn) return;

      this.loadingPosts = true;
      const token = localStorage.getItem("moi_gioi_auth_token");

      try {
        const res = await api.get("/moi-gioi/thong-ke/tin-con-lai");
        this.remainingPosts = res.data?.data ?? 0;
      } catch (error) {
        console.error("Lỗi tải số tin:", error);
      } finally {
        this.loadingPosts = false;
      }
    },

    // ✅ Xử lý click nút Đăng tin
    handleQuickPost() {
      // Nếu hết tin (và không phải gói vô hạn) → Hỏi mua gói
      if (this.remainingPosts <= 0 && this.remainingPosts !== -1) {
        if (typeof Swal !== 'undefined') {
          Swal.fire({
            icon: "warning",
            title: "Hết số tin đăng!",
            text: "Bạn đã dùng hết số tin trong gói. Vui lòng nâng cấp để tiếp tục.",
            confirmButtonText: "Mua thêm gói",
            confirmButtonColor: "#10b981",
            showCancelButton: true,
          }).then((result) => {
            if (result.isConfirmed) {
              this.$router.push("/moi-gioi/goi-tin");
            }
          });
        } else {
          // Fallback nếu không có Swal
          if (confirm("Bạn đã hết số tin đăng. Chuyển đến trang mua gói?")) {
            this.$router.push("/moi-gioi/goi-tin");
          }
        }
        return;
      }

      // ✅ Còn tin → Chuyển đến trang đăng tin
      this.$router.push("/moi-gioi/dang-tin");
    },

    async fetchDropdownStats() {
      if (!this.isLoggedIn) return;
      try {
        const [listingsRes, customersRes] = await Promise.all([
          api.get('/moi-gioi/thong-ke/tong-tin-da-dang'),
          api.get('/moi-gioi/thong-ke/tong-khach-hang'),
        ]);
        this.statsListings = listingsRes.data?.data ?? 0;
        this.statsCustomers = customersRes.data?.data ?? 0;
      } catch (e) {
        console.error('fetchDropdownStats error:', e);
      }
    },

    // ✅ Cập nhật polling để refresh số tin mỗi 30s
    startPolling() {
      this.pollingInterval = setInterval(() => {
        if (this.isLoggedIn) {
          this.fetchNotifications();
          this.fetchRemainingPosts(); // ✅ Refresh số tin cùng lúc
          this.fetchDropdownStats();
        }
      }, 30000);
    },
  },
};
</script>

<style scoped>
/* ===== 🔧 HEADER BASE - FIX OVERFLOW ===== */
.header {
  position: fixed;
  top: 0;
  left: 260px;
  right: 0;
  height: 70px;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(6, 95, 70, 0.1);
  z-index: 20;
  transition: left 0.3s ease;
  overflow: visible !important;
  /* 🔥 FIX dropdown */
}

.container {
  max-width: 100%;
  margin: auto;
  height: 100%;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 24px;
  overflow: visible !important;
}

.actions {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-left: auto;
  position: relative;
  overflow: visible !important;
}

/* Buttons */
.btn-login,
.btn-register {
  padding: 9px 18px;
  border-radius: 999px;
  font-weight: 600;
  font-size: 13px;
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.btn-login {
  border: 2px solid #10b981;
  color: #10b981;
  background: transparent;
}

.btn-login:hover {
  background: #10b981;
  color: white;
  transform: translateY(-1px);
}

.btn-register {
  background: linear-gradient(135deg, #065f46, #10b981);
  color: white;
  border: none;
}

.btn-register:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

/* ===== ✅ USER WRAPPER - DESIGN MỚI ===== */
.user-box {
  position: relative;
  display: flex;
  align-items: center;
  gap: 8px;
}

.avatar-wrapper {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  padding: 4px 8px;
  border-radius: 999px;
  transition: all 0.2s ease;
  outline: none;
}

.avatar-wrapper:hover {
  background: #f0fdf4;
}

.avatar-container {
  position: relative;
}

.avatar {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  background: linear-gradient(135deg, #065f46, #10b981);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 14px;
  flex-shrink: 0;
}

.status-dot {
  position: absolute;
  bottom: -2px;
  right: -2px;
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: #94a3b8;
  border: 2px solid white;
}

.status-dot.online {
  background: #10b981;
  box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
}

.avatar-name {
  font-size: 13px;
  font-weight: 600;
  color: #1f2937;
  max-width: 100px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.arrow {
  font-size: 8px;
  color: #6b7280;
  transition: transform 0.25s;
}

.arrow.rotate {
  transform: rotate(180deg);
}

/* ===== ✅ DROPDOWN MENU - DESIGN MỚI ===== */
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
  right: 0;
  top: calc(100% + 12px);
  width: 300px;
  background: white;
  border-radius: 16px;
  box-shadow: 0 20px 60px rgba(6, 95, 70, 0.15);
  border: 1px solid #e2e8f0;
  z-index: 9999;
  overflow: hidden;
  animation: slideDown 0.2s ease-out;
}

.dropdown-header-new {
  padding: 16px 20px;
  background: linear-gradient(135deg, #065f46, #10b981);
  color: white;
  display: flex;
  align-items: center;
  gap: 12px;
}

.avatar-large-new {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: white;
  color: #065f46;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 18px;
  flex-shrink: 0;
}

.user-info-new {
  flex: 1;
  min-width: 0;
}

.user-display-name {
  margin: 0;
  font-weight: 600;
  font-size: 15px;
  color: white;
}

.user-code-badge {
  display: inline-block;
  margin-top: 4px;
  font-size: 10px;
  background: rgba(255, 255, 255, 0.15);
  padding: 2px 8px;
  border-radius: 10px;
  color: #e2e8f0;
}

.dropdown-stats-new {
  display: flex;
  padding: 12px 20px;
  background: #f0fdf4;
  gap: 16px;
  border-bottom: 1px solid #e5e7eb;
}

.stat-item-new {
  flex: 1;
  text-align: center;
}

.stat-value-new {
  font-weight: 800;
  color: #065f46;
  font-size: 18px;
  display: block;
}

.stat-label-new {
  font-size: 9px;
  color: #6b7280;
  font-weight: 600;
  text-transform: uppercase;
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
  background: #f8fafc;
  padding-left: 24px;
  color: #1e293b;
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

/* Primary item - Đăng tin mới */
.dropdown-item-new.primary {
  background: linear-gradient(135deg, #10b981, #065f46);
  color: white;
}

.dropdown-item-new.primary:hover {
  background: linear-gradient(135deg, #059669, #047857);
  color: white;
  padding-left: 24px;
}

/* 🔥 Chat với khách hàng - Special Style */
.chat-customer-item {
  background: linear-gradient(135deg,
      rgba(59, 130, 246, 0.08),
      rgba(99, 102, 241, 0.08));
  border-left: 3px solid #3b82f6;
  cursor: pointer;
}

.chat-customer-item:hover {
  background: linear-gradient(135deg,
      rgba(59, 130, 246, 0.15),
      rgba(99, 102, 241, 0.15));
  padding-left: 24px;
}

.item-badge-new {
  font-size: 9px;
  padding: 2px 7px;
  border-radius: 8px;
  font-weight: 600;
}

.item-badge-new.hot {
  background: #ef4444;
  color: white;
}

.item-badge-new.new {
  background: #3b82f6;
  color: white;
}

.dropdown-divider-new {
  height: 1px;
  background: linear-gradient(to right, transparent, #e2e8f0, transparent);
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
  background: #f8fafc;
}

.logout-btn-new {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 16px;
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

/* ===== NOTIFICATIONS (giữ nguyên + fix overflow) ===== */
.notification-wrapper {
  position: relative;
  margin-right: 16px;
}

.notification-btn {
  background: none;
  border: none;
  cursor: pointer;
  padding: 8px;
  border-radius: 8px;
  transition: all 0.2s;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.notification-btn:hover {
  background: #f3f4f6;
}

.bell-icon {
  width: 22px;
  height: 22px;
  color: #000;
}

.notification-badge {
  position: absolute;
  top: 2px;
  right: 2px;
  background: #ef4444;
  color: white;
  font-size: 10px;
  font-weight: 700;
  min-width: 16px;
  height: 16px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 4px;
  border: 2px solid white;
  animation: pulse 2s infinite;
}

.notification-dropdown {
  position: absolute;
  right: 0;
  top: 48px;
  width: 400px;
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 12px 40px -12px rgba(0, 0, 0, 0.15);
  border: 1px solid rgba(229, 231, 235, 0.8);
  z-index: 1002;
  transform-origin: top right;
  overflow: hidden;
}

.dropdown-top {
  padding: 16px 20px;
  background: rgba(249, 250, 251, 0.8);
  border-bottom: 1px solid #f3f4f6;
  display: flex;
  justify-content: space-between;
}

.dropdown-title {
  margin: 0;
  font-size: 16px;
  font-weight: 700;
  color: #111827;
}

.mark-all-link {
  background: none;
  border: none;
  color: #6b7280;
  font-size: 13px;
  cursor: pointer;
}

.mark-all-link:hover {
  color: #065f46;
}

.dropdown-list {
  max-height: 420px;
  overflow-y: auto;
  padding: 6px 0;
}

.notification-item-wrapper {
  margin: 4px 8px;
  border: 1px solid rgba(143, 146, 153, 0.8);
  border-radius: 10px;
  background: #f1f5f9;
}

.notification-item {
  position: relative;
  background: white;
  border-radius: 10px;
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 12px 16px;
  cursor: pointer;
  transition: background 0.2s ease;
}

.notification-item:hover {
  background: #f8fafc;
}

.notification-item.is-unread {
  background: #e0f2fe;
}

.delete-btn {
  position: absolute;
  top: 8px;
  right: 8px;
  width: 24px;
  height: 24px;
  border: none;
  background: transparent;
  color: #9ca3af;
  cursor: pointer;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10;
}

.delete-btn:hover {
  background: #fee2e2;
  color: #dc2626;
}

.unread-indicator {
  position: absolute;
  top: 0;
  right: 0;
  width: 10px;
  height: 10px;
  background: #ef4444;
  border-radius: 50%;
  box-shadow: 0 0 0 2px #ef4444;
  animation: pulse-red 2s infinite;
  z-index: 3;
}

.item-icon {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 16px;
  flex-shrink: 0;
}

.icon-blue {
  background: #eff6ff;
  color: #3b82f6;
}

.icon-pink {
  background: #fdf2f8;
  color: #ec4899;
}

.icon-green {
  background: #ecfdf5;
  color: #10b981;
}

.icon-yellow {
  background: #fffbeb;
  color: #f59e0b;
}

.icon-cyan {
  background: #ecfeff;
  color: #06b6d4;
}

.icon-red {
  background: #fef2f2;
  color: #ef4444;
}

.icon-gray {
  background: #f3f4f6;
  color: #6b7280;
}

.item-content {
  flex: 1;
  padding-right: 30px;
}

.item-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 4px;
}

.item-title {
  font-size: 14px;
  font-weight: 600;
  margin: 0;
}

.item-time {
  font-size: 11px;
  color: #9ca3af;
  margin-left: 8px;
}

.item-desc {
  font-size: 12.5px;
  color: #6b7280;
  margin: 0;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.dropdown-footer {
  padding: 12px;
  border-top: 1px solid #f3f4f6;
  text-align: center;
}

.view-all-link {
  color: #065f46;
  font-size: 14px;
  font-weight: 600;
  text-decoration: none;
  padding: 6px 12px;
  border-radius: 8px;
  transition: background 0.2s;
}

.view-all-link:hover {
  background: #f0fdf4;
}

/* ===== MODAL & TOAST (giữ nguyên) ===== */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(2px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

.modal-box {
  background: white;
  width: 90%;
  max-width: 420px;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #eee;
  padding-bottom: 12px;
  margin-bottom: 16px;
}

.modal-header h3 {
  margin: 0;
  font-size: 18px;
  color: #111827;
}

.close-btn {
  background: none;
  border: none;
  font-size: 18px;
  cursor: pointer;
  color: #6b7280;
}

.close-btn:hover {
  color: #111827;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 20px;
  padding-top: 16px;
  border-top: 1px solid #eee;
}

.btn {
  padding: 10px 18px;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: opacity 0.2s;
}

.btn:hover {
  opacity: 0.85;
}

.btn-secondary {
  background: #f3f4f6;
  color: #374151;
}

.btn-primary {
  background: #065f46;
  color: white;
}

.info-table {
  width: 100%;
  border-collapse: collapse;
}

.info-table th,
.info-table td {
  padding: 12px 8px;
  text-align: left;
  border-bottom: 1px solid #f3f4f6;
}

.info-table th {
  width: 35%;
  color: #6b7280;
  font-weight: 600;
  font-size: 14px;
  white-space: nowrap;
}

.info-table td {
  color: #111827;
  font-size: 15px;
}

.info-table tr:last-child th,
.info-table tr:last-child td {
  border-bottom: none;
}

.copyable {
  cursor: pointer;
  color: #2563eb;
  font-weight: 500;
  transition: all 0.2s ease;
}

.copyable:hover {
  color: #1d4ed8;
  text-decoration: underline;
}

.custom-toast {
  position: fixed;
  top: 24px;
  left: 50%;
  transform: translateX(-50%);
  background-color: #043625;
  color: #00e676;
  padding: 12px 20px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  gap: 12px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
  z-index: 10000;
  min-width: 300px;
}

.toast-icon {
  width: 24px;
  height: 24px;
  flex-shrink: 0;
}

.toast-text {
  flex-grow: 1;
  font-size: 14px;
  font-weight: 500;
  line-height: 1.5;
}

.toast-close {
  background: none;
  border: none;
  color: #00e676;
  cursor: pointer;
  padding: 4px;
  display: flex;
  transition: opacity 0.2s;
}

.toast-close:hover {
  opacity: 0.7;
}

/* ===== ANIMATIONS ===== */
@keyframes pulse {

  0%,
  100% {
    opacity: 1;
    transform: scale(1);
  }

  50% {
    opacity: 0.8;
    transform: scale(1.1);
  }
}

@keyframes pulse-red {

  0%,
  100% {
    box-shadow: 0 0 0 2px #ef4444;
    transform: scale(1);
  }

  50% {
    box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.4);
    transform: scale(1.1);
  }
}

.spinner {
  width: 32px;
  height: 32px;
  border: 3px solid #f3f4f6;
  border-top-color: #10b981;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 0 auto 12px;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.dropdown-slide-enter-active,
.dropdown-slide-leave-active {
  transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.dropdown-slide-enter-from,
.dropdown-slide-leave-to {
  opacity: 0;
  transform: translateY(-12px) scale(0.96);
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.toast-fade-enter-active,
.toast-fade-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.toast-fade-enter-from,
.toast-fade-leave-to {
  opacity: 0;
  transform: translate(-50%, -20px);
}

/* ===== RESPONSIVE ===== */
@media (max-width: 1024px) {
  .header {
    left: 0;
  }
}

@media (max-width: 768px) {
  .avatar-name {
    display: none;
  }

  .dropdown-menu-new {
    width: 280px;
    right: -10px;
  }

  .notification-dropdown {
    width: 320px;
  }
}

@media (max-width: 480px) {
  .container {
    padding: 0 12px;
  }

  .dropdown-menu-new {
    width: 260px;
  }

  .notification-dropdown {
    width: 280px;
  }

  .user-code-badge {
    display: none;
  }
}

/* ===== QUICK POST BUTTON ===== */
.quick-post-wrapper {
  display: flex;
  align-items: center;
}

.quick-post-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
  border: none;
  border-radius: 999px;
  font-weight: 600;
  font-size: 13px;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 2px 8px rgba(16, 185, 129, 0.25);
  white-space: nowrap;
}

.quick-post-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
  background: linear-gradient(135deg, #059669, #047857);
  color: white;
}

.quick-post-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

/* Badge số tin */
.post-count-badge {
  padding: 2px 10px;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 700;
  background: rgba(255, 255, 255, 0.25);
  color: white;
  min-width: 45px;
  text-align: center;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

/* Màu badge theo trạng thái */
.badge-success {
  background: rgba(255, 255, 255, 0.25);
  color: white;
}

.badge-warning {
  background: #f59e0b;
  color: white;
  animation: pulse-warning 2s infinite;
}

.badge-empty {
  background: #ef4444;
  color: white;
  animation: pulse-danger 2s infinite;
}

.badge-loading {
  background: rgba(255, 255, 255, 0.2);
  color: rgba(255, 255, 255, 0.9);
}

/* Animations */
@keyframes pulse-warning {

  0%,
  100% {
    box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.4);
  }

  50% {
    box-shadow: 0 0 0 6px rgba(245, 158, 11, 0);
  }
}

@keyframes pulse-danger {

  0%,
  100% {
    box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4);
  }

  50% {
    box-shadow: 0 0 0 6px rgba(239, 68, 68, 0);
  }
}

/* Responsive */
@media (max-width: 768px) {
  .post-label {
    display: none;
  }

  .quick-post-btn {
    padding: 8px 12px;
  }

  .post-count-badge {
    margin-left: 0;
    min-width: 38px;
    font-size: 10px;
  }
}
</style>