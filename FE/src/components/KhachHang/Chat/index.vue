<template>
  <div class="broker-dashboard">
    <!-- Main Content -->
    <main class="main-content">

      <!-- Stats Cards -->
      <div class="stats-grid">
        <div class="stat-card primary">
          <div class="stat-header">
            <span>TỔNG TIN ĐĂNG</span>
            <span class="stat-icon">🏠</span>
          </div>
          <div class="stat-value">
            {{ stats.totalListings }}
            <span class="stat-change positive">+{{ stats.listingsGrowth }} tháng này</span>
          </div>
        </div>

        <div class="stat-card success">
          <div class="stat-header">
            <span>KHÁCH TIỀM NĂNG</span>
            <span class="stat-icon">👤</span>
          </div>
          <div class="stat-value">
            {{ stats.potentialClients }}
            <span class="stat-change positive">{{ stats.clientsGrowth }}% tăng</span>
          </div>
        </div>

        <div class="stat-card info">
          <div class="stat-header">
            <span>GIAO DỊCH THÀNH CÔNG</span>
            <span class="stat-icon">✅</span>
          </div>
          <div class="stat-value">
            {{ stats.successfulTransactions }}
            <span class="stat-goal">Mục tiêu: {{ stats.transactionTarget }}</span>
          </div>
        </div>

        <div class="stat-card highlight">
          <div class="stat-header">
            <span>HOA HỒNG THÁNG NÀY</span>
            <span class="stat-icon">💰</span>
          </div>
          <div class="stat-value large">
            {{ stats.monthlyCommission }}
            <span class="currency">triệu VNĐ</span>
          </div>
        </div>
      </div>

      <!-- Chart and Contacts Section -->
      <div class="content-grid">
        <!-- Performance Chart -->
        <div class="chart-section">
          <div class="section-header">
            <h2>Hiệu suất tin đăng theo tuần</h2>
            <div class="toggle-buttons">
              <button :class="{ active: chartPeriod === 'month' }" @click="chartPeriod = 'month'">
                THÁNG QUA
              </button>
              <button :class="{ active: chartPeriod === 'week' }" @click="chartPeriod = 'week'">
                TUẦN NÀY
              </button>
            </div>
          </div>
          <div class="chart-container">
            <div class="chart-bars">
              <div v-for="(day, index) in weeklyData" :key="index" class="chart-bar-wrapper">
                <div class="chart-bar" :style="{ height: day.value + '%' }" :class="{ 'is-today': day.isToday }"></div>
                <span class="chart-label">{{ day.label }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Contacts -->
        <div class="contacts-section">
          <div class="section-header">
            <div class="urgent-badge">!</div>
            <h2>LIÊN HỆ NGAY</h2>
          </div>
          <div class="contacts-list">
            <div v-for="client in urgentContacts" :key="client.id" class="contact-card">
              <div class="contact-avatar">
                <img :src="client.avatar" :alt="client.name" />
              </div>
              <div class="contact-info">
                <h3>{{ client.name }}</h3>
                <span class="contact-property">{{ client.property }}</span>
                <p class="contact-note">"{{ client.note }}"</p>
              </div>
              <button class="contact-action" @click="contactClient(client)">
                <span v-if="client.type === 'call'">📞</span>
                <span v-else-if="client.type === 'message'">💬</span>
                <span v-else>📅</span>
              </button>
            </div>
          </div>
          <button class="btn-view-all" @click="viewAllClients">
            XEM TẤT CẢ KHÁCH HÀNG
          </button>
        </div>
      </div>

      <!-- Expiring Listings Alert -->
      <div class="alert-banner" v-if="expiringListings.length > 0">
        <div class="alert-content">
          <h3>Gia hạn tin đăng sắp hết hạn</h3>
          <p>
            Có {{ expiringListings.length }} tin đăng tại dự án
            {{ expiringListings[0].project }} sẽ hết hiệu lực trong
            {{ expiringListings[0].hoursLeft }} giờ tới. Hãy gia hạn ngay để
            không bỏ lỡ khách hàng tiềm năng.
          </p>
        </div>
        <button class="btn-renew" @click="renewListings">GIA HẠN NGAY</button>
      </div>
    </main>

    <!-- Add Listing Modal -->
    <div class="modal" v-if="showAddListing" @click.self="showAddListing = false">
      <div class="modal-content">
        <h2>Thêm tin đăng mới</h2>
        <form @submit.prevent="addListing">
          <div class="form-group">
            <label>Tiêu đề</label>
            <input v-model="newListing.title" type="text" required />
          </div>
          <div class="form-group">
            <label>Loại bất động sản</label>
            <select v-model="newListing.typeId" required>
              <option value="">Chọn loại BĐS</option>
              <option v-for="type in propertyTypes" :key="type.id" :value="type.id">
                {{ type.name }}
              </option>
            </select>
          </div>
          <div class="form-group">
            <label>Tỉnh/Thành phố</label>
            <select v-model="newListing.provinceId" @change="fetchDistricts" required>
              <option value="">Chọn tỉnh/thành</option>
              <option v-for="province in provinces" :key="province.id" :value="province.id">
                {{ province.name }}
              </option>
            </select>
          </div>
          <div class="form-group">
            <label>Quận/Huyện</label>
            <select v-model="newListing.districtId" required>
              <option value="">Chọn quận/huyện</option>
              <option v-for="district in districts" :key="district.id" :value="district.id">
                {{ district.name }}
              </option>
            </select>
          </div>
          <div class="form-actions">
            <button type="button" class="btn-cancel" @click="showAddListing = false">
              Hủy
            </button>
            <button type="submit" class="btn-submit">Tạo tin đăng</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import api from "@/axios/config";

export default {
  data() {
    return {
      activeTab: "overview",
      showAddListing: false,
      chartPeriod: "week",
      broker: {
        name: "Minh Quân",
        avatar: "/images/avatar.jpg",
        role: "SENIOR BROKER",
      },
      stats: {
        totalListings: 24,
        listingsGrowth: 2,
        potentialClients: 118,
        clientsGrowth: 15,
        successfulTransactions: 12,
        transactionTarget: 15,
        monthlyCommission: 480.5,
      },
      newClientsCount: 5,
      weeklyData: [
        { label: "T2", value: 30, isToday: false },
        { label: "T3", value: 45, isToday: false },
        { label: "T4", value: 80, isToday: false },
        { label: "T5", value: 55, isToday: false },
        { label: "T6", value: 35, isToday: false },
        { label: "T7", value: 50, isToday: false },
        { label: "CN", value: 40, isToday: true },
      ],
      urgentContacts: [
        {
          id: 1,
          name: "Nguyễn Thu Thủy",
          avatar: "/images/client1.jpg",
          property: "KINH ĐÔ ÁNH SÁNG",
          note: "Cần tư vấn căn 2PN tầng 15",
          type: "call",
        },
        {
          id: 2,
          name: "Trần Anh Đức",
          avatar: "/images/client2.jpg",
          property: "VILLAS RIVERSIDE",
          note: "Gửi hợp đồng đặt cọc",
          type: "message",
        },
        {
          id: 3,
          name: "Lê Minh Châu",
          avatar: "/images/client3.jpg",
          property: "PENTHOUSE ZEN PARK",
          note: "Hẹn xem nhà chiều nay 16:00",
          type: "calendar",
        },
      ],
      expiringListings: [
        {
          id: 1,
          project: "Heritage West Lake",
          hoursLeft: 48,
          count: 3,
        },
      ],
      propertyTypes: [],
      provinces: [],
      districts: [],
      newListing: {
        title: "",
        typeId: "",
        provinceId: "",
        districtId: "",
      },
    };
  },

  mounted() {
    this.fetchDashboardData();
    this.fetchPropertyTypes();
    this.fetchProvinces();
  },

  methods: {
    async fetchDashboardData() {
      try {
        // Fetch listings data
        const listingsRes = await api.get("/moi-gioi/bds/data");
        this.stats.totalListings = listingsRes.data.total || 24;

        // Fetch notifications
        await this.fetchNotifications();
      } catch (error) {
        console.error("Error fetching dashboard data:", error);
      }
    },

    async fetchNotifications() {
      try {
        const res = await api.get("/moi-gioi/thong-bao");
        this.unreadNotifications = res.data.unreadCount || 0;
      } catch (error) {
        console.error("Error fetching notifications:", error);
      }
    },

    async fetchPropertyTypes() {
      try {
        const res = await api.get("/moi-gioi/loai-bat-dong-san/data");
        this.propertyTypes = res.data || [];
      } catch (error) {
        console.error("Error fetching property types:", error);
        // Mock data if API fails
        this.propertyTypes = [
          { id: 1, name: "Căn hộ" },
          { id: 2, name: "Nhà phố" },
          { id: 3, name: "Biệt thự" },
        ];
      }
    },

    async fetchProvinces() {
      try {
        const res = await api.get("/moi-gioi/tinh-thanh");
        this.provinces = res.data || [];
      } catch (error) {
        console.error("Error fetching provinces:", error);
        // Mock data
        this.provinces = [
          { id: 1, name: "Hà Nội" },
          { id: 2, name: "TP. Hồ Chí Minh" },
          { id: 3, name: "Đà Nẵng" },
        ];
      }
    },

    async fetchDistricts() {
      if (!this.newListing.provinceId) return;

      try {
        const res = await api.get("/moi-gioi/quan-huyen", {
          params: { tinh_id: this.newListing.provinceId },
        });
        this.districts = res.data || [];
      } catch (error) {
        console.error("Error fetching districts:", error);
        this.districts = [
          { id: 1, name: "Quận Ba Đình" },
          { id: 2, name: "Quận Hoàn Kiếm" },
        ];
      }
    },

    async addListing() {
      try {
        await api.post("/moi-gioi/bds/create", this.newListing);
        this.showAddListing = false;
        this.newListing = {
          title: "",
          typeId: "",
          provinceId: "",
          districtId: "",
        };
        this.fetchDashboardData();
        alert("Tạo tin đăng thành công!");
      } catch (error) {
        console.error("Error creating listing:", error);
        alert("Có lỗi xảy ra. Vui lòng thử lại.");
      }
    },

    async renewListings() {
      try {
        // Redirect to package purchase or renewal page
        const packagesRes = await api.get("/moi-gioi/goi-tin/data");
        // Show package selection or auto-renew
        alert("Chuyển đến trang gia hạn...");
      } catch (error) {
        console.error("Error renewing listings:", error);
      }
    },

    contactClient(client) {
      // Handle contact action based on type
      if (client.type === "call") {
        window.location.href = "tel:" + (client.phone || "+84123456789");
      } else if (client.type === "message") {
        // Open messaging interface
        alert("Mở chat với " + client.name);
      } else {
        // Open calendar
        alert("Mở lịch hẹn với " + client.name);
      }
    },

    viewAllClients() {
      // Navigate to leads page
      this.activeTab = "leads";
    },

    logout() {
      if (confirm("Bạn có chắc chắn muốn đăng xuất?")) {
        api
          .post("/moi-gioi/logout")
          .then(() => {
            window.location.href = "/dang-nhap";
          })
          .catch((error) => {
            console.error("Logout error:", error);
            window.location.href = "/dang-nhap";
          });
      }
    },

  },
};
</script>

<style scoped>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

.broker-dashboard {
  display: flex;
  min-height: 100vh;
  background: #f5f7fa;
  font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
}

.logo {
  display: flex;
  align-items: center;
  gap: 12px;
}

.logo-icon {
  width: 40px;
  height: 40px;
  background: #001f66;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
}

.logo h3 {
  color: #001f66;
  font-size: 18px;
  font-weight: 700;
}


.nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  border-radius: 8px;
  color: #4b5563;
  text-decoration: none;
  margin-bottom: 4px;
  transition: all 0.2s;
  cursor: pointer;
}

.nav-item:hover {
  background: #f3f4f6;
  color: #001f66;
}

.nav-item.active {
  background: #001f66;
  color: #fff;
}

.nav-icon {
  font-size: 18px;
  width: 24px;
  text-align: center;
}


.btn-add-listing {
  width: 100%;
  padding: 12px;
  background: #001f66;
  color: #fff;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  margin-bottom: 8px;
  transition: background 0.2s;
}

.btn-add-listing:hover {
  background: #002880;
}

/* Main Content */
.main-content {
  flex: 1;
  padding: 32px;
}


/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 24px;
  margin-bottom: 32px;
}

.stat-card {
  background: #fff;
  padding: 24px;
  border-radius: 16px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  transition: transform 0.2s;
}

.stat-card:hover {
  transform: translateY(-4px);
}

.stat-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.stat-header span:first-child {
  font-size: 13px;
  color: #6b7280;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.stat-icon {
  font-size: 24px;
}

.stat-value {
  font-size: 32px;
  font-weight: 700;
  color: #111827;
  display: flex;
  align-items: baseline;
  gap: 8px;
}

.stat-value.large {
  font-size: 36px;
}

.currency {
  font-size: 16px;
  color: #6b7280;
  font-weight: 400;
}

.stat-change {
  font-size: 13px;
  font-weight: 500;
}

.stat-change.positive {
  color: #10b981;
}

.stat-goal {
  font-size: 13px;
  color: #6b7280;
}

.stat-card.primary {
  border-left: 4px solid #001f66;
}

.stat-card.success {
  border-left: 4px solid #10b981;
}

.stat-card.info {
  border-left: 4px solid #3b82f6;
}

.stat-card.highlight {
  background: #001f66;
  color: #fff;
}

.stat-card.highlight .stat-header span:first-child,
.stat-card.highlight .stat-value {
  color: #fff;
}

.stat-card.highlight .currency {
  color: #93c5fd;
}

/* Content Grid */
.content-grid {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 24px;
  margin-bottom: 24px;
}

.chart-section,
.contacts-section {
  background: #fff;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.section-header h2 {
  font-size: 18px;
  color: #001f66;
  font-weight: 700;
}

.toggle-buttons {
  display: flex;
  gap: 8px;
}

.toggle-buttons button {
  padding: 8px 16px;
  border: none;
  background: #f3f4f6;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.toggle-buttons button.active {
  background: #001f66;
  color: #fff;
}

/* Chart */
.chart-container {
  height: 300px;
  display: flex;
  align-items: flex-end;
  justify-content: space-around;
  padding: 20px 0;
}

.chart-bars {
  display: flex;
  justify-content: space-around;
  align-items: flex-end;
  width: 100%;
  height: 100%;
  padding: 0 20px;
}

.chart-bar-wrapper {
  display: flex;
  flex-direction: column;
  align-items: center;
  flex: 1;
  max-width: 60px;
}

.chart-bar {
  width: 12px;
  background: #e5e7eb;
  border-radius: 6px;
  transition: all 0.3s;
  min-height: 20px;
}

.chart-bar.is-today {
  background: #5eead4;
  box-shadow: 0 0 12px rgba(94, 234, 212, 0.5);
}

.chart-bar:nth-child(3) {
  background: #001f66;
  box-shadow: 0 0 12px rgba(0, 31, 102, 0.4);
}

.chart-label {
  margin-top: 12px;
  font-size: 12px;
  color: #9ca3af;
  font-weight: 500;
}

/* Contacts */
.urgent-badge {
  width: 32px;
  height: 32px;
  background: #fde047;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  color: #854d0e;
  margin-right: 12px;
}

.contacts-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.contact-card {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 16px;
  background: #f9fafb;
  border-radius: 12px;
  transition: all 0.2s;
}

.contact-card:hover {
  background: #f3f4f6;
  transform: translateX(4px);
}

.contact-avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  overflow: hidden;
  flex-shrink: 0;
}

.contact-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.contact-info {
  flex: 1;
}

.contact-info h3 {
  font-size: 14px;
  font-weight: 600;
  color: #111827;
  margin-bottom: 4px;
}

.contact-property {
  display: block;
  font-size: 12px;
  color: #059669;
  font-weight: 500;
  margin-bottom: 4px;
}

.contact-note {
  font-size: 13px;
  color: #6b7280;
  font-style: italic;
}

.contact-action {
  width: 40px;
  height: 40px;
  border: none;
  background: #fff;
  border-radius: 50%;
  cursor: pointer;
  font-size: 18px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  transition: all 0.2s;
}

.contact-action:hover {
  transform: scale(1.1);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
}

.btn-view-all {
  width: 100%;
  margin-top: 16px;
  padding: 12px;
  border: 1px solid #001f66;
  background: transparent;
  color: #001f66;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-view-all:hover {
  background: #001f66;
  color: #fff;
}

/* Alert Banner */
.alert-banner {
  background: #fef08a;
  border-radius: 12px;
  padding: 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.alert-content h3 {
  color: #854d0e;
  font-size: 16px;
  font-weight: 700;
  margin-bottom: 8px;
}

.alert-content p {
  color: #713f12;
  font-size: 14px;
  line-height: 1.6;
}

.btn-renew {
  padding: 12px 24px;
  background: #1f2937;
  color: #fff;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  white-space: nowrap;
}

.btn-renew:hover {
  background: #111827;
  transform: translateY(-2px);
}

/* Modal */
.modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: #fff;
  padding: 32px;
  border-radius: 16px;
  width: 90%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-content h2 {
  color: #001f66;
  margin-bottom: 24px;
  font-size: 24px;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: #374151;
}

.form-group input,
.form-group select {
  width: 100%;
  padding: 12px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 14px;
  transition: border-color 0.2s;
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #001f66;
}

.form-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  margin-top: 24px;
}

.btn-cancel,
.btn-submit {
  padding: 12px 24px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-cancel {
  background: #f3f4f6;
  border: 1px solid #d1d5db;
  color: #374151;
}

.btn-cancel:hover {
  background: #e5e7eb;
}

.btn-submit {
  background: #001f66;
  border: none;
  color: #fff;
}

.btn-submit:hover {
  background: #002880;
}

/* Responsive */
@media (max-width: 1200px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .content-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .main-content {
    margin-left: 0;
    padding: 16px;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }
}
</style>