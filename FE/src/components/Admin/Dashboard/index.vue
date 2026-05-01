<template>
  <div class="dashboard-container">
    <!-- Header -->

    <!-- THAY BẰNG CODE NÀY -->
    <section class="dashboard-hero">
      <!-- Animated Background -->
      <div class="hero-bg">
        <div class="gradient-orb orb-1"></div>
        <div class="gradient-orb orb-2"></div>
        <div class="gradient-orb orb-3"></div>
        <div class="grid-pattern"></div>
      </div>

      <div class="hero-content">
        <!-- Left Side -->
        <div class="hero-left">
          <div class="hero-badge">
            <i class="fa-solid fa-chart-line"></i>
            <span>Real-time Analytics</span>
          </div>

          <h1 class="hero-title">
            Dashboard Tổng Quan
            <span class="title-highlight">Bất Động Sản</span>
          </h1>

          <p class="hero-subtitle">
            Theo dõi hiệu suất kinh doanh, trạng thái giao dịch và phân tích thị
            trường
            <span class="highlight-text">thời gian thực</span>
          </p>

          <!-- Quick Stats -->
          <!-- <div class="quick-stats">
            <div class="quick-stat-item">
              <div class="stat-icon revenue">
                <i class="fa-solid fa-sack-dollar"></i>
              </div>
              <div class="stat-info">
                <span class="stat-label">Doanh thu tháng</span>
                <span class="stat-value">{{
                  formatCurrency(quickStats.revenue)
                }}</span>
              </div>
              <div class="stat-change positive">
                <i class="fa-solid fa-arrow-trend-up"></i>
                +12.5%
              </div>
            </div>

            <div class="quick-stat-item">
              <div class="stat-icon deals">
                <i class="fa-solid fa-handshake"></i>
              </div>
              <div class="stat-info">
                <span class="stat-label">Giao dịch hôm nay</span>
                <span class="stat-value">{{ quickStats.todayDeals }}</span>
              </div>
              <div class="stat-change positive">
                <i class="fa-solid fa-plus"></i>
                +3
              </div>
            </div>

            <div class="quick-stat-item">
              <div class="stat-icon properties">
                <i class="fa-solid fa-building"></i>
              </div>
              <div class="stat-info">
                <span class="stat-label">BĐS đang hoạt động</span>
                <span class="stat-value">{{
                  quickStats.activeProperties
                }}</span>
              </div>
              <div class="stat-change neutral">
                <i class="fa-solid fa-minus"></i>
                0%
              </div>
            </div>
          </div> -->
        </div>

        <!-- Right Side -->
        <div class="hero-right">
          <div class="control-panel">
            <div class="time-display">
              <div class="current-time">
                <i class="fa-regular fa-clock"></i>
                <span>{{ currentTime }}</span>
              </div>
              <div class="current-date">{{ currentDate }}</div>
            </div>

            <div class="control-actions">
              <button class="btn-auto-refresh" :class="{ active: autoRefresh }" @click="toggleAutoRefresh">
                <i class="fa-solid fa-rotate" :class="{ 'fa-spin': autoRefresh }"></i>
              </button>

              <button class="btn-refresh" @click="refreshAllData" :disabled="loading">
                <i class="fa-solid fa-rotate-right" :class="{ 'fa-spin': loading }"></i>
                <span>Làm mới</span>
              </button>

              <div class="period-selector">
                <label>Khoảng thời gian</label>
                <select v-model="timePeriod" @change="fetchChartData" class="period-select">
                  <option value="7days">7 ngày qua</option>
                  <option value="30days">30 ngày qua</option>
                  <option value="3months">3 tháng qua</option>
                  <option value="6months">6 tháng qua</option>
                  <option value="1year">1 năm qua</option>
                </select>
              </div>
            </div>
          </div>

          <div class="update-badge">
            <div class="pulse-dot"></div>
            <div class="update-info">
              <span class="update-label">Cập nhật lần cuối</span>
              <span class="update-time">{{ lastUpdateTime }}</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- TẦNG 1: 4 THẺ THỐNG KÊ -->
    <div class="layer-1 stats-grid">
      <div class="stat-card stat-card-1">
        <div class="stat-icon-wrapper" v-if="!loading">
          <div class="stat-icon" style="
              background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            ">
            <i class="fa-solid fa-users"></i>
          </div>
        </div>
        <div class="stat-content" v-if="!loading">
          <div class="stat-label">Khách Hàng</div>
          <div class="stat-value">{{ formatNumber(stats.khach_hang) }}</div>
          <div class="stat-desc">Tổng số khách hàng</div>
        </div>
        <div class="skeleton-wrapper" v-else>
          <div class="skeleton-avatar"></div>
          <div class="skeleton-text-group">
            <div class="skeleton-text skeleton-title"></div>
            <div class="skeleton-text skeleton-value"></div>
            <div class="skeleton-text skeleton-desc"></div>
          </div>
        </div>
      </div>

      <div class="stat-card stat-card-2">
        <div class="stat-icon-wrapper" v-if="!loading">
          <div class="stat-icon" style="
              background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            ">
            <i class="fa-solid fa-user-tie"></i>
          </div>
        </div>
        <div class="stat-content" v-if="!loading">
          <div class="stat-label">Môi Giới</div>
          <div class="stat-value">{{ formatNumber(stats.moi_gioi) }}</div>
          <div class="stat-desc">Tổng số môi giới</div>
        </div>
        <div class="skeleton-wrapper" v-else>
          <div class="skeleton-avatar"></div>
          <div class="skeleton-text-group">
            <div class="skeleton-text skeleton-title"></div>
            <div class="skeleton-text skeleton-value"></div>
            <div class="skeleton-text skeleton-desc"></div>
          </div>
        </div>
      </div>

      <div class="stat-card stat-card-3">
        <div class="stat-icon-wrapper" v-if="!loading">
          <div class="stat-icon" style="
              background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            ">
            <i class="fa-solid fa-building"></i>
          </div>
        </div>
        <div class="stat-content" v-if="!loading">
          <div class="stat-label">Bất Động Sản</div>
          <div class="stat-value">{{ formatNumber(stats.bat_dong_san) }}</div>
          <div class="stat-desc">BĐS đã duyệt</div>
        </div>
        <div class="skeleton-wrapper" v-else>
          <div class="skeleton-avatar"></div>
          <div class="skeleton-text-group">
            <div class="skeleton-text skeleton-title"></div>
            <div class="skeleton-text skeleton-value"></div>
            <div class="skeleton-text skeleton-desc"></div>
          </div>
        </div>
      </div>

      <div class="stat-card stat-card-4">
        <div class="stat-icon-wrapper" v-if="!loading">
          <div class="stat-icon" style="
              background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            ">
            <i class="fa-solid fa-chart-line"></i>
          </div>
        </div>
        <div class="stat-content" v-if="!loading">
          <div class="stat-label">Giao Dịch</div>
          <div class="stat-value">{{ formatNumber(stats.giao_dich) }}</div>
          <div class="stat-desc">Giao dịch thành công</div>
        </div>
        <div class="skeleton-wrapper" v-else>
          <div class="skeleton-avatar"></div>
          <div class="skeleton-text-group">
            <div class="skeleton-text skeleton-title"></div>
            <div class="skeleton-text skeleton-value"></div>
            <div class="skeleton-text skeleton-desc"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- TẦNG 2: 2 BIỂU ĐỒ -->
    <div class="layer-2 charts-grid">
      <!-- Biểu đồ bên trái: Doanh thu & Giao dịch -->
      <div class="chart-card chart-large">
        <div class="chart-header">
          <h3 class="chart-title">
            <i class="fa-solid fa-chart-area"></i>
            Doanh Thu & Giao Dịch
          </h3>
          <button class="btn-chart-toggle" @click="toggleChartType">
            <i :class="isAreaChart ? 'fa-solid fa-chart-area' : 'fa-solid fa-chart-bar'
              "></i>
          </button>
        </div>
        <div class="chart-body">
          <apexchart v-if="chartLoaded" :type="isAreaChart ? 'area' : 'bar'" height="350" :options="revenueChartOptions"
            :series="revenueChartSeries"></apexchart>
          <div v-else class="chart-loading skeleton-chart">
            <div class="skeleton-text" style="width: 30%; height: 20px; margin-bottom: 20px;"></div>
            <div class="skeleton-rect" style="width: 100%; height: 250px;"></div>
          </div>
        </div>
      </div>

      <!-- Biểu đồ bên phải: Thống kê BĐS theo loại -->
      <div class="chart-card chart-large">
        <div class="chart-header">
          <h3 class="chart-title">
            <i class="fa-solid fa-chart-pie"></i>
            Phân Loại Bất Động Sản
          </h3>
        </div>
        <div class="chart-body">
          <apexchart v-if="propertyChartLoaded" type="donut" height="350" :options="propertyChartOptions"
            :series="propertyChartSeries"></apexchart>
          <div v-else class="chart-loading skeleton-chart" style="display:flex; justify-content:center; align-items:center;">
            <div class="skeleton-circle" style="width: 250px; height: 250px; border-radius: 50%;"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- TẦNG 3: CHI TIẾT -->
    <div class="layer-3 details-grid">
      <!-- Bên trái: Khách hàng yêu thích BĐS gần đây -->
      <div class="detail-card">
        <div class="card-header">
          <h3 class="card-title">
            <i class="fa-solid fa-heart"></i>
            Khách Hàng Yêu Thích BĐS Gần Đây
          </h3>
          <button class="btn-view-all" @click="viewAllFavorites">
            Xem tất cả <i class="fa-solid fa-arrow-right"></i>
          </button>
        </div>
        <div class="card-body">
          <div class="favorites-list">
            <div v-if="favoritesLoading" class="loading-state">
              <i class="fa-solid fa-circle-notch fa-spin"></i>
              Đang tải...
            </div>
            <div v-else-if="favorites.length === 0" class="empty-state">
              <i class="fa-solid fa-inbox"></i>
              <p>Chưa có dữ liệu</p>
            </div>
            <div v-else class="favorite-item" v-for="(item, index) in favorites" :key="index">
              <div class="favorite-avatar">
                {{ getInitials(item.khach_hang) }}
              </div>
              <div class="favorite-info">
                <div class="favorite-customer">{{ item.khach_hang }}</div>
                <div class="favorite-property">{{ item.bat_dong_san }}</div>
                <div class="favorite-meta">
                  <span><i class="fa-regular fa-calendar"></i>
                    {{ item.date }}</span>
                  <span class="favorite-price">{{ item.gia_formatted }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Bên phải: Giao dịch mua gói tin gần đây -->
      <div class="detail-card">
        <div class="card-header">
          <h3 class="card-title">
            <i class="fa-solid fa-cart-shopping"></i>
            Giao Dịch Mua Gói Tin Gần Đây
          </h3>
          <button class="btn-view-all" @click="viewAllTransactions">
            Xem tất cả <i class="fa-solid fa-arrow-right"></i>
          </button>
        </div>
        <div class="card-body">
          <div class="transactions-list">
            <div v-if="transactionsLoading" class="loading-state">
              <i class="fa-solid fa-circle-notch fa-spin"></i>
              Đang tải...
            </div>
            <div v-else-if="transactions.length === 0" class="empty-state">
              <i class="fa-solid fa-inbox"></i>
              <p>Chưa có giao dịch</p>
            </div>
            <div v-else class="transaction-item" v-for="(item, index) in transactions" :key="index">
              <div class="transaction-avatar">
                {{ getInitials(item.moi_gioi) }}
              </div>
              <div class="transaction-info">
                <div class="transaction-agent">{{ item.moi_gioi }}</div>
                <div class="transaction-package">{{ item.goi_tin }}</div>
                <div class="transaction-meta">
                  <span><i class="fa-regular fa-calendar"></i>
                    {{ item.date }}</span>
                  <span class="transaction-amount">{{
                    item.so_tien_formatted
                  }}</span>
                </div>
              </div>
              <div class="transaction-status" :class="item.trang_thai">
                <i class="fa-solid" :class="getStatusIcon(item.trang_thai)"></i>
                {{ item.trang_thai_label }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import { useRouter } from "vue-router";
import api from "@/axios/config";

const router = useRouter();
const currentTime = ref("");
const currentDate = ref("");
const lastUpdateTime = ref("Vừa xong");
const autoRefresh = ref(false);
const quickStats = ref({
  revenue: 2450000000,
  todayDeals: 12,
  activeProperties: 156,
});

let timerInterval = null;
let autoRefreshInterval = null;
// State
const loading = ref(false);
const stats = ref({
  khach_hang: 0,
  moi_gioi: 0,
  bat_dong_san: 0,
  giao_dich: 0,
});

const timePeriod = ref("30days");
const isAreaChart = ref(true);
const chartLoaded = ref(false);
const propertyChartLoaded = ref(false);

const revenueChartSeries = ref([]);
const revenueChartOptions = ref({
  chart: {
    type: "area",
    height: 350,
    toolbar: { show: false },
    animations: { enabled: true },
  },
  stroke: {
    curve: "smooth",
    width: 3,
  },
  fill: {
    type: "gradient",
    gradient: {
      shadeIntensity: 1,
      opacityFrom: 0.7,
      opacityTo: 0.3,
      stops: [0, 90, 100],
    },
  },
  dataLabels: { enabled: false },
  xaxis: {
    categories: [],
    axisBorder: { show: false },
    axisTicks: { show: false },
  },
  yaxis: [
    {
      title: { text: "Doanh thu (VNĐ)", style: { fontSize: "12px" } },
      labels: {
        formatter: (val) =>
          new Intl.NumberFormat("vi-VN", { notation: "compact" }).format(val),
      },
    },
    {
      opposite: true,
      title: { text: "Số giao dịch", style: { fontSize: "12px" } },
    },
  ],
  tooltip: {
    theme: "light",
    shared: true,
    intersect: false,
  },
  legend: {
    position: "top",
    horizontalAlign: "right",
  },
  colors: ["#3b82f6", "#10b981"],
  grid: {
    borderColor: "#e5e7eb",
    strokeDashArray: 4,
  },
});

const propertyChartSeries = ref([]);
const propertyChartOptions = ref({
  chart: {
    type: "donut",
    height: 350,
    toolbar: { show: false },
  },
  labels: [],
  colors: ["#3b82f6", "#10b981", "#f59e0b", "#ef4444", "#8b5cf6"],
  plotOptions: {
    pie: {
      donut: {
        size: "65%",
        labels: {
          show: true,
          name: { fontSize: "14px", fontWeight: "600" },
          value: { fontSize: "18px", fontWeight: "700" },
          total: {
            show: true,
            label: "Tổng",
            formatter: () => stats.value.bat_dong_san,
          },
        },
      },
    },
  },
  dataLabels: { enabled: false },
  legend: {
    position: "bottom",
    fontSize: "12px",
  },
});

const favorites = ref([]);
const transactions = ref([]);
const favoritesLoading = ref(false);
const transactionsLoading = ref(false);

// API Methods
const fetchStats = async () => {
  try {
    const res = await api.get("/admin/dashboard/stats");
    if (res.data.status) {
      stats.value = res.data.data;
    }
  } catch (error) {
    console.error("Error fetching stats:", error);
  }
};

const fetchChartData = async () => {
  try {
    const res = await api.post("/admin/dashboard/revenue-chart", { period: timePeriod.value });

    if (res.data.status && res.data.data) {
      const data = res.data.data;
      const labels = data.map((item) => item.date_display);
      const revenue = data.map((item) => item.revenue);
      const transactions = data.map((item) => item.transaction_count);

      revenueChartSeries.value = [
        { name: "Doanh thu", data: revenue },
        { name: "Giao dịch", data: transactions },
      ];

      // Update chart options
      revenueChartOptions.value = {
        ...revenueChartOptions.value,
        xaxis: {
          categories: labels,
          axisBorder: { show: false },
          axisTicks: { show: false },
        },
      };

      chartLoaded.value = true;

      // Fetch property chart
      fetchPropertyChartData();
    }
  } catch (error) {
    console.error("Error fetching chart:", error);
    chartLoaded.value = true; // Still show chart even if empty
  }
};

const fetchPropertyChartData = async () => {
  try {
    const res = await api.get("/admin/dashboard/property-status-chart");
    if (res.data.status && res.data.data) {
      propertyChartSeries.value = res.data.data.series;
      propertyChartOptions.value = {
        ...propertyChartOptions.value,
        labels: res.data.data.labels,
      };
    }
    propertyChartLoaded.value = true;
  } catch (error) {
    console.error("Error fetching property chart:", error);
    propertyChartLoaded.value = true;
  }
};

const fetchFavorites = async () => {
  favoritesLoading.value = true;
  try {
    const res = await api.get("/admin/dashboard/recent-favorites", { params: { limit: 5 } });
    if (res.data.status) {
      favorites.value = res.data.data;
    }
  } catch (error) {
    console.error("Error fetching favorites:", error);
  } finally {
    favoritesLoading.value = false;
  }
};

const fetchTransactions = async () => {
  transactionsLoading.value = true;
  try {
    const res = await api.get("/admin/dashboard/recent-package-purchases", { params: { limit: 5 } });
    if (res.data.status) {
      transactions.value = res.data.data;
    }
  } catch (error) {
    console.error("Error fetching transactions:", error);
  } finally {
    transactionsLoading.value = false;
  }
};

const refreshAllData = () => {
  loading.value = true;
  Promise.all([fetchStats(), fetchChartData(), fetchFavorites(), fetchTransactions()]).finally(() => {
    loading.value = false;
    lastUpdateTime.value = new Date().toLocaleTimeString("vi-VN", { hour: "2-digit", minute: "2-digit" });
  });
};

const toggleChartType = () => {
  isAreaChart.value = !isAreaChart.value;
  revenueChartOptions.value = {
    ...revenueChartOptions.value,
    chart: { ...revenueChartOptions.value.chart, type: isAreaChart.value ? "area" : "bar" },
    stroke: { ...revenueChartOptions.value.stroke, width: isAreaChart.value ? 3 : 0 },
  };
};

const formatNumber = (num) => new Intl.NumberFormat("vi-VN").format(num || 0);
const formatCurrency = (amount) =>
  new Intl.NumberFormat("vi-VN", { style: "currency", currency: "VND", notation: "compact" }).format(amount);

const getInitials = (name) => {
  if (!name) return "??";
  return name.split(" ").map((n) => n[0]).join("").toUpperCase().slice(0, 2);
};

const getStatusIcon = (status) => (status === "success" ? "fa-check-circle" : "fa-clock");
const viewAllFavorites = () => {
  // router.push("/admin/bat-dong-san"); // or appropriate route
};
const viewAllTransactions = () => {
  router.push("/admin/giao-dich");
};

const updateTime = () => {
  const now = new Date();
  currentTime.value = now.toLocaleTimeString("vi-VN", { hour: "2-digit", minute: "2-digit", second: "2-digit" });
  currentDate.value = now.toLocaleDateString("vi-VN", { weekday: "long", year: "numeric", month: "long", day: "numeric" });
};

const toggleAutoRefresh = () => {
  autoRefresh.value = !autoRefresh.value;
  if (autoRefresh.value) {
    refreshAllData(); // Initial refresh when enabled
    autoRefreshInterval = setInterval(refreshAllData, 30000);
  } else {
    if (autoRefreshInterval) clearInterval(autoRefreshInterval);
  }
};

onMounted(() => {
  refreshAllData();
  updateTime();
  timerInterval = setInterval(updateTime, 1000);
});

onUnmounted(() => {
  if (timerInterval) clearInterval(timerInterval);
  if (autoRefreshInterval) clearInterval(autoRefreshInterval);
});
</script>


<style scoped>
/* Dashboard Hero Styles */
.dashboard-hero {
  position: relative;
  margin-bottom: 2rem;
  border-radius: 24px;
  overflow: hidden;
  background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #1d4ed8 100%);
  box-shadow: 0 20px 60px -15px rgba(0, 0, 0, 0.5);
}

.hero-bg {
  position: absolute;
  inset: 0;
  overflow: hidden;
  z-index: 1;
}

.gradient-orb {
  position: absolute;
  border-radius: 50%;
  filter: blur(80px);
  opacity: 0.5;
  animation: float 20s infinite ease-in-out;
}

.orb-1 {
  width: 400px;
  height: 400px;
  background: linear-gradient(135deg, #3b82f6, #8b5cf6);
  top: -100px;
  right: -100px;
  animation-delay: 0s;
}

.orb-2 {
  width: 300px;
  height: 300px;
  background: linear-gradient(135deg, #10b981, #3b82f6);
  bottom: -50px;
  right: 20%;
  animation-delay: -5s;
}

.orb-3 {
  width: 250px;
  height: 250px;
  background: linear-gradient(135deg, #f59e0b, #ef4444);
  top: 40%;
  right: 10%;
  animation-delay: -10s;
}

@keyframes float {

  0%,
  100% {
    transform: translate(0, 0) scale(1);
  }

  33% {
    transform: translate(30px, -30px) scale(1.1);
  }

  66% {
    transform: translate(-20px, 20px) scale(0.9);
  }
}

.grid-pattern {
  position: absolute;
  inset: 0;
  background-image: linear-gradient(rgba(255, 255, 255, 0.03) 1px,
      transparent 1px),
    linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
  background-size: 50px 50px;
  opacity: 0.5;
}

.hero-content {
  position: relative;
  z-index: 10;
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 3rem;
  padding: 2.5rem;
}

.hero-left {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.hero-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 50px;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  color: rgba(255, 255, 255, 0.9);
  width: fit-content;
  animation: slideInLeft 0.6s ease-out;
}

.hero-badge i {
  color: #10b981;
}

.hero-title {
  font-size: 3rem;
  font-weight: 900;
  color: #ffffff;
  margin: 0;
  line-height: 1.2;
  animation: slideInLeft 0.6s ease-out 0.1s both;
}

.title-highlight {
  display: block;
  background: linear-gradient(135deg, #60a5fa, #a78bfa);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.hero-subtitle {
  font-size: 1rem;
  color: rgba(255, 255, 255, 0.75);
  margin: 0;
  line-height: 1.6;
  max-width: 600px;
  animation: slideInLeft 0.6s ease-out 0.2s both;
}

.highlight-text {
  color: #60a5fa;
  font-weight: 600;
}

.quick-stats {
  display: flex;
  gap: 1.5rem;
  margin-top: 1rem;
  animation: slideInLeft 0.6s ease-out 0.3s both;
}

.quick-stat-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem 1.5rem;
  background: rgba(255, 255, 255, 0.08);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 16px;
  transition: all 0.3s ease;
}

.quick-stat-item:hover {
  background: rgba(255, 255, 255, 0.12);
  transform: translateY(-2px);
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: white;
}

.stat-icon.revenue {
  background: linear-gradient(135deg, #10b981, #34d399);
}

.stat-icon.deals {
  background: linear-gradient(135deg, #3b82f6, #60a5fa);
}

.stat-icon.properties {
  background: linear-gradient(135deg, #f59e0b, #fbbf24);
}

.stat-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.stat-label {
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.65);
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 800;
  color: #ffffff;
}

.stat-change {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  font-size: 0.875rem;
  font-weight: 700;
  padding: 0.25rem 0.5rem;
  border-radius: 8px;
}

.stat-change.positive {
  color: #34d399;
  background: rgba(52, 211, 153, 0.15);
}

.stat-change.negative {
  color: #f87171;
  background: rgba(248, 113, 113, 0.15);
}

.stat-change.neutral {
  color: rgba(255, 255, 255, 0.65);
}

.hero-right {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  align-items: flex-end;
}

.control-panel {
  background: rgba(255, 255, 255, 0.08);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 20px;
  padding: 1.5rem;
  animation: slideInRight 0.6s ease-out 0.2s both;
}

.time-display {
  text-align: right;
  margin-bottom: 1.5rem;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.current-time {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 0.5rem;
  font-size: 2rem;
  font-weight: 800;
  color: #ffffff;
  margin-bottom: 0.25rem;
}

.current-time i {
  color: #60a5fa;
}

.current-date {
  font-size: 0.875rem;
  color: rgba(255, 255, 255, 0.65);
  text-transform: capitalize;
}

.control-actions {
  display: flex;
  gap: 1rem;
  align-items: flex-end;
}

.btn-auto-refresh {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.2);
  background: rgba(255, 255, 255, 0.05);
  color: rgba(255, 255, 255, 0.75);
  cursor: pointer;
  transition: all 0.3s;
  font-size: 1.25rem;
}

.btn-auto-refresh:hover,
.btn-auto-refresh.active {
  background: rgba(59, 130, 246, 0.3);
  border-color: #3b82f6;
  color: #60a5fa;
}

.btn-refresh {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: linear-gradient(135deg, #3b82f6, #2563eb);
  border: none;
  border-radius: 12px;
  color: white;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s;
  box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
}

.btn-refresh:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(59, 130, 246, 0.6);
}

.btn-refresh:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.period-selector {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.period-selector label {
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.65);
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.period-select {
  padding: 0.75rem 1rem;
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 12px;
  color: white;
  font-weight: 600;
  cursor: pointer;
  outline: none;
  transition: all 0.3s;
  min-width: 180px;
}

.period-select:hover {
  background: rgba(255, 255, 255, 0.15);
  border-color: rgba(255, 255, 255, 0.3);
}

.period-select:focus {
  border-color: #60a5fa;
  box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.2);
}

.period-select option {
  background: #1e1b4b;
  color: white;
}

.update-badge {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1.25rem;
  background: rgba(255, 255, 255, 0.08);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 50px;
  animation: slideInRight 0.6s ease-out 0.3s both;
}

.pulse-dot {
  width: 10px;
  height: 10px;
  background: #10b981;
  border-radius: 50%;
  position: relative;
  animation: pulse 2s infinite;
}

.pulse-dot::before {
  content: "";
  position: absolute;
  inset: -4px;
  border-radius: 50%;
  border: 2px solid #10b981;
  animation: pulse-ring 2s infinite;
}

@keyframes pulse {

  0%,
  100% {
    opacity: 1;
  }

  50% {
    opacity: 0.5;
  }
}

@keyframes pulse-ring {
  0% {
    transform: scale(0.8);
    opacity: 1;
  }

  100% {
    transform: scale(2);
    opacity: 0;
  }
}

.update-info {
  display: flex;
  flex-direction: column;
  gap: 0.125rem;
}

.update-label {
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.6);
}

.update-time {
  font-size: 0.875rem;
  font-weight: 700;
  color: #10b981;
}

@keyframes slideInLeft {
  from {
    opacity: 0;
    transform: translateX(-30px);
  }

  to {
    opacity: 1;
    transform: translateX(0);
  }
}

@keyframes slideInRight {
  from {
    opacity: 0;
    transform: translateX(30px);
  }

  to {
    opacity: 1;
    transform: translateX(0);
  }
}

@media (max-width: 1024px) {
  .hero-content {
    grid-template-columns: 1fr;
    gap: 2rem;
  }

  .hero-right {
    align-items: flex-start;
  }

  .control-panel {
    width: 100%;
  }

  .time-display {
    text-align: left;
  }

  .current-time {
    justify-content: flex-start;
  }

  .control-actions {
    flex-wrap: wrap;
  }

  .quick-stats {
    flex-wrap: wrap;
  }
}

@media (max-width: 768px) {
  .hero-content {
    padding: 1.5rem;
  }

  .hero-title {
    font-size: 2rem;
  }

  .quick-stat-item {
    flex: 1;
    min-width: 200px;
  }

  .control-actions {
    flex-direction: column;
    width: 100%;
  }

  .period-selector {
    width: 100%;
  }

  .period-select {
    width: 100%;
  }
}

/* css tầng 1 2 3 */
.dashboard-container {
  padding: 2rem;
  background: #f8fafc;
  min-height: 100vh;
}

/* Header */
.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.dashboard-title {
  font-size: 2rem;
  font-weight: 800;
  color: #1e293b;
  margin: 0;
  letter-spacing: -0.5px;
}

.dashboard-subtitle {
  color: #64748b;
  margin: 0.25rem 0 0 0;
  font-size: 0.95rem;
}

.header-actions {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.btn-refresh {
  padding: 0.625rem 1.25rem;
  background: white;
  border: 2px solid #e2e8f0;
  border-radius: 0.75rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #475569;
}

.btn-refresh:hover:not(:disabled) {
  border-color: #3b82f6;
  color: #3b82f6;
  transform: translateY(-2px);
}

.btn-refresh:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.period-select {
  padding: 0.625rem 1rem;
  background: white;
  border: 2px solid #e2e8f0;
  border-radius: 0.75rem;
  font-weight: 600;
  cursor: pointer;
  outline: none;
  color: #475569;
  transition: all 0.3s;
}

.period-select:hover {
  border-color: #3b82f6;
}

/* Layer 1: Stats Grid */
.layer-1 {
  margin-bottom: 2rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
}

.stat-card {
  background: white;
  border-radius: 1rem;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1.25rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
  border: 1px solid #f1f5f9;
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
}

.stat-icon-wrapper {
  flex-shrink: 0;
}

.stat-icon {
  width: 4rem;
  height: 4rem;
  border-radius: 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.75rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.stat-content {
  flex: 1;
}

.stat-label {
  font-size: 0.875rem;
  color: #64748b;
  font-weight: 600;
  margin-bottom: 0.5rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.stat-value {
  font-size: 2.25rem;
  font-weight: 800;
  color: #1e293b;
  line-height: 1;
  margin-bottom: 0.25rem;
}

.stat-desc {
  font-size: 0.875rem;
  color: #94a3b8;
}

/* Layer 2: Charts Grid */
.layer-2 {
  margin-bottom: 2rem;
}

.charts-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.5rem;
}

.chart-card {
  background: white;
  border-radius: 1rem;
  padding: 1.5rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
  border: 1px solid #f1f5f9;
}

.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.chart-title {
  font-size: 1.125rem;
  font-weight: 700;
  color: #1e293b;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin: 0;
}

.btn-chart-toggle {
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 0.75rem;
  background: #f1f5f9;
  border: none;
  cursor: pointer;
  transition: all 0.3s;
  color: #64748b;
}

.btn-chart-toggle:hover {
  background: #e2e8f0;
  color: #3b82f6;
}

.chart-body {
  position: relative;
}

.chart-loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 350px;
  color: #64748b;
  gap: 1rem;
}

.chart-loading i {
  font-size: 2rem;
  color: #3b82f6;
}

/* Layer 3: Details Grid */
.layer-3 {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.5rem;
}

.detail-card {
  background: white;
  border-radius: 1rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
  border: 1px solid #f1f5f9;
  overflow: hidden;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid #f1f5f9;
}

.card-title {
  font-size: 1.125rem;
  font-weight: 700;
  color: #1e293b;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin: 0;
}

.btn-view-all {
  background: none;
  border: none;
  color: #3b82f6;
  font-weight: 600;
  cursor: pointer;
  font-size: 0.875rem;
  display: flex;
  align-items: center;
  gap: 0.375rem;
  transition: all 0.3s;
}

.btn-view-all:hover {
  color: #2563eb;
  transform: translateX(4px);
}

.card-body {
  padding: 1.5rem;
}

/* Favorites List */
.favorites-list,
.transactions-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.favorite-item,
.transaction-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: #f8fafc;
  border-radius: 0.75rem;
  transition: all 0.3s;
  border: 1px solid transparent;
}

.favorite-item:hover,
.transaction-item:hover {
  background: #f1f5f9;
  border-color: #e2e8f0;
  transform: translateX(4px);
}

.favorite-avatar,
.transaction-avatar {
  width: 3rem;
  height: 3rem;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 700;
  font-size: 0.875rem;
  flex-shrink: 0;
}

.transaction-avatar {
  background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
}

.favorite-info,
.transaction-info {
  flex: 1;
  min-width: 0;
}

.favorite-customer,
.transaction-agent {
  font-weight: 600;
  color: #1e293b;
  margin-bottom: 0.25rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.favorite-property,
.transaction-package {
  font-size: 0.875rem;
  color: #64748b;
  margin-bottom: 0.5rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.favorite-meta,
.transaction-meta {
  display: flex;
  gap: 1rem;
  font-size: 0.75rem;
  color: #94a3b8;
  align-items: center;
}

.favorite-price,
.transaction-amount {
  font-weight: 700;
  color: #3b82f6;
}

.transaction-status {
  padding: 0.375rem 0.75rem;
  border-radius: 0.5rem;
  font-size: 0.75rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.375rem;
  flex-shrink: 0;
}

.transaction-status.success {
  background: #d1fae5;
  color: #059669;
}

.transaction-status.pending {
  background: #fef3c7;
  color: #d97706;
}

/* Loading & Empty States */
.loading-state,
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem;
  color: #94a3b8;
  gap: 1rem;
}

.loading-state i,
.empty-state i {
  font-size: 2.5rem;
}

.empty-state p {
  margin: 0;
  font-size: 0.95rem;
}

/* Responsive */
@media (max-width: 1024px) {

  .charts-grid,
  .layer-3 {
    grid-template-columns: 1fr;
  }

  .dashboard-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }

  .header-actions {
    width: 100%;
    flex-direction: column;
  }

  .btn-refresh,
  .period-select {
    width: 100%;
    justify-content: center;
  }
}

@media (max-width: 768px) {
  .dashboard-container {
    padding: 1rem;
  }

  .dashboard-title {
    font-size: 1.5rem;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }

  .stat-value {
    font-size: 1.75rem;
  }
}
.skeleton-wrapper {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  width: 100%;
}
.skeleton-avatar {
  width: 60px;
  height: 60px;
  border-radius: 16px;
  background: linear-gradient(90deg, #e2e8f0 25%, #cbd5e1 50%, #e2e8f0 75%);
  background-size: 200% 100%;
  animation: skeleton-loading 1.5s infinite;
}
.skeleton-text-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  flex: 1;
}
.skeleton-text {
  background: linear-gradient(90deg, #e2e8f0 25%, #cbd5e1 50%, #e2e8f0 75%);
  background-size: 200% 100%;
  animation: skeleton-loading 1.5s infinite;
  border-radius: 4px;
}
.skeleton-title {
  width: 50%;
  height: 14px;
}
.skeleton-value {
  width: 70%;
  height: 28px;
}
.skeleton-desc {
  width: 40%;
  height: 12px;
}
.skeleton-rect {
  background: linear-gradient(90deg, #e2e8f0 25%, #cbd5e1 50%, #e2e8f0 75%);
  background-size: 200% 100%;
  animation: skeleton-loading 1.5s infinite;
  border-radius: 8px;
}
.skeleton-circle {
  background: linear-gradient(90deg, #e2e8f0 25%, #cbd5e1 50%, #e2e8f0 75%);
  background-size: 200% 100%;
  animation: skeleton-loading 1.5s infinite;
}
.skeleton-chart {
  padding: 1rem;
  height: 350px;
}
@keyframes skeleton-loading {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}
</style>
