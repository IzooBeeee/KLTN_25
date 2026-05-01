<template>
  <div class="db-wrap">

    <!-- ══════════ HERO BANNER ══════════ -->
    <section class="db-hero">
      <div class="db-hero__orbs">
        <span class="orb orb-1"></span>
        <span class="orb orb-2"></span>
        <span class="orb orb-3"></span>
      </div>
      <div class="db-hero__grid"></div>

      <div class="db-hero__inner">
        <!-- Left -->
        <div class="db-hero__left">
          <div class="db-badge">
            <i class="fa-solid fa-chart-line"></i>
            Real-time Analytics
          </div>
          <h1 class="db-hero__title">
            Dashboard Tổng Quan
            <span class="db-hero__accent">Bất Động Sản</span>
          </h1>
          <p class="db-hero__sub">
            Theo dõi hiệu suất kinh doanh, giao dịch và phân tích thị trường
            <span style="color:#60a5fa;font-weight:600">thời gian thực</span>
          </p>

          <!-- 3 hero KPIs -->
          <div class="db-hero__kpis">
            <div class="hkpi">
              <div class="hkpi__icon" style="background:linear-gradient(135deg,#10b981,#34d399)">
                <i class="fa-solid fa-sack-dollar"></i>
              </div>
              <div>
                <div class="hkpi__label">Doanh thu tháng</div>
                <div class="hkpi__val">{{ formatCurrencyCompact(stats.doanh_thu_thang) }}</div>
              </div>
              <div class="hkpi__badge" :class="stats.doanh_thu_change >= 0 ? 'positive' : 'negative'">
                <i class="fa-solid" :class="stats.doanh_thu_change >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down'"></i>
                {{ Math.abs(stats.doanh_thu_change) }}%
              </div>
            </div>
            <div class="hkpi">
              <div class="hkpi__icon" style="background:linear-gradient(135deg,#3b82f6,#60a5fa)">
                <i class="fa-solid fa-handshake"></i>
              </div>
              <div>
                <div class="hkpi__label">Giao dịch hôm nay</div>
                <div class="hkpi__val">{{ stats.giao_dich_hom_nay }}</div>
              </div>
              <div class="hkpi__badge" :class="stats.giao_dich_hom_nay >= stats.giao_dich_hom_qua ? 'positive' : 'negative'">
                <i class="fa-solid fa-plus"></i>
                vs hôm qua: {{ stats.giao_dich_hom_qua }}
              </div>
            </div>
            <div class="hkpi">
              <div class="hkpi__icon" style="background:linear-gradient(135deg,#f59e0b,#fbbf24)">
                <i class="fa-solid fa-clock-rotate-left"></i>
              </div>
              <div>
                <div class="hkpi__label">BĐS chờ duyệt</div>
                <div class="hkpi__val">{{ stats.cho_duyet }}</div>
              </div>
              <router-link to="/admin/quan-ly-bds" class="hkpi__action">Duyệt ngay →</router-link>
            </div>
          </div>
        </div>

        <!-- Right: clock + controls -->
        <div class="db-hero__right">
          <div class="db-clock-card">
            <div class="db-clock">
              <i class="fa-regular fa-clock" style="color:#60a5fa"></i>
              {{ currentTime }}
            </div>
            <div class="db-date">{{ currentDate }}</div>
            <div class="db-controls">
              <button class="btn-autoref" :class="{active:autoRefresh}" @click="toggleAutoRefresh" title="Auto refresh">
                <i class="fa-solid fa-rotate" :class="{'fa-spin':autoRefresh}"></i>
              </button>
              <button class="btn-refresh" @click="loadAll" :disabled="loading">
                <i class="fa-solid fa-rotate-right" :class="{'fa-spin':loading}"></i>
                Làm mới
              </button>
              <div class="db-period">
                <label>Khoảng</label>
                <select v-model="timePeriod" @change="fetchChartData" class="db-select">
                  <option value="7days">7 ngày</option>
                  <option value="30days">30 ngày</option>
                  <option value="3months">3 tháng</option>
                  <option value="6months">6 tháng</option>
                  <option value="1year">1 năm</option>
                </select>
              </div>
            </div>
          </div>
          <div class="db-live-badge">
            <span class="pulse-dot"></span>
            <div>
              <div style="font-size:.7rem;color:rgba(255,255,255,.55)">Cập nhật lần cuối</div>
              <div style="font-size:.82rem;font-weight:700;color:#10b981">{{ lastUpdateTime }}</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ══════════ 6 KPI CARDS ══════════ -->
    <div class="db-kpi-grid">
      <div v-for="card in kpiCards" :key="card.key" class="kpi-card" :style="`--accent:${card.color}`">
        <template v-if="!loading">
          <div class="kpi-card__icon" :style="`background:${card.gradient}`">
            <i :class="card.icon"></i>
          </div>
          <div class="kpi-card__body">
            <div class="kpi-card__label">{{ card.label }}</div>
            <div class="kpi-card__value">{{ card.format ? card.format(stats[card.key]) : formatNumber(stats[card.key]) }}</div>
            <div class="kpi-card__desc">{{ card.desc }}</div>
          </div>
          <router-link v-if="card.link" :to="card.link" class="kpi-card__link">
            <i class="fa-solid fa-arrow-right"></i>
          </router-link>
        </template>
        <div v-else class="kpi-card__skeleton">
          <div class="sk-circle"></div>
          <div class="sk-lines"><div class="sk-line sk-s"></div><div class="sk-line sk-m"></div><div class="sk-line sk-xs"></div></div>
        </div>
      </div>
    </div>

    <!-- ══════════ CHARTS ROW ══════════ -->
    <div class="db-charts-row">
      <div class="db-card db-card--lg">
        <div class="db-card__head">
          <h3 class="db-card__title"><i class="fa-solid fa-chart-area"></i> Doanh Thu &amp; Giao Dịch</h3>
          <button class="btn-toggle-chart" @click="toggleChartType">
            <i :class="isAreaChart ? 'fa-solid fa-chart-bar' : 'fa-solid fa-chart-area'"></i>
          </button>
        </div>
        <apexchart v-if="chartLoaded" :type="isAreaChart ? 'area' : 'bar'" height="320"
          :options="revenueChartOptions" :series="revenueChartSeries" />
        <div v-else class="chart-skeleton"><div class="sk-rect"></div></div>
      </div>

      <div class="db-card db-card--sm">
        <div class="db-card__head">
          <h3 class="db-card__title"><i class="fa-solid fa-chart-pie"></i> Phân Loại BĐS</h3>
        </div>
        <apexchart v-if="propertyChartLoaded" type="donut" height="320"
          :options="propertyChartOptions" :series="propertyChartSeries" />
        <div v-else class="chart-skeleton" style="display:flex;justify-content:center;align-items:center">
          <div class="sk-donut"></div>
        </div>
      </div>
    </div>

    <!-- ══════════ BOTTOM 3-COL ══════════ -->
    <div class="db-bottom-row">

      <!-- TOP MÔI GIỚI -->
      <div class="db-card">
        <div class="db-card__head">
          <h3 class="db-card__title"><i class="fa-solid fa-trophy" style="color:#f59e0b"></i> Top Môi Giới</h3>
          <router-link to="/admin/moi-gioi" class="db-view-all">Xem tất cả</router-link>
        </div>
        <div class="top-broker-list">
          <div v-if="overviewLoading" class="db-loading"><i class="fa-solid fa-circle-notch fa-spin"></i></div>
          <div v-else-if="topMoiGioi.length === 0" class="db-empty"><i class="fa-solid fa-inbox"></i><p>Chưa có dữ liệu</p></div>
          <div v-else v-for="(mg, i) in topMoiGioi" :key="mg.id" class="top-broker-item">
            <div class="top-broker-rank" :class="`rank-${i+1}`">{{ i + 1 }}</div>
            <div class="top-broker-avatar">{{ getInitials(mg.ten) }}</div>
            <div class="top-broker-info">
              <div class="top-broker-name">{{ mg.ten }}</div>
              <div class="top-broker-email">{{ mg.email }}</div>
            </div>
            <div class="top-broker-stat">
              <div class="top-broker-count">{{ mg.so_bds }}</div>
              <div class="top-broker-slabel">tin đăng</div>
            </div>
          </div>
        </div>
      </div>

      <!-- RECENT FAVORITES -->
      <div class="db-card">
        <div class="db-card__head">
          <h3 class="db-card__title"><i class="fa-solid fa-heart" style="color:#f43f5e"></i> Yêu Thích Gần Đây</h3>
        </div>
        <div class="activity-list">
          <div v-if="favoritesLoading" class="db-loading"><i class="fa-solid fa-circle-notch fa-spin"></i></div>
          <div v-else-if="favorites.length === 0" class="db-empty"><i class="fa-solid fa-inbox"></i><p>Chưa có</p></div>
          <div v-else v-for="(f, i) in favorites" :key="i" class="activity-item">
            <div class="act-avatar act-purple">{{ getInitials(f.khach_hang) }}</div>
            <div class="act-body">
              <div class="act-name">{{ f.khach_hang }}</div>
              <div class="act-sub">{{ f.bat_dong_san }}</div>
              <div class="act-meta"><i class="fa-regular fa-calendar"></i> {{ f.date }} · <span class="act-price">{{ f.gia_formatted }}</span></div>
            </div>
          </div>
        </div>
      </div>

      <!-- RECENT TRANSACTIONS -->
      <div class="db-card">
        <div class="db-card__head">
          <h3 class="db-card__title"><i class="fa-solid fa-cart-shopping" style="color:#3b82f6"></i> Giao Dịch Gần Đây</h3>
          <router-link to="/admin/giao-dich" class="db-view-all">Xem tất cả</router-link>
        </div>
        <div class="activity-list">
          <div v-if="transactionsLoading" class="db-loading"><i class="fa-solid fa-circle-notch fa-spin"></i></div>
          <div v-else-if="transactions.length === 0" class="db-empty"><i class="fa-solid fa-inbox"></i><p>Chưa có</p></div>
          <div v-else v-for="(t, i) in transactions" :key="i" class="activity-item">
            <div class="act-avatar act-blue">{{ getInitials(t.moi_gioi) }}</div>
            <div class="act-body">
              <div class="act-name">{{ t.moi_gioi }}</div>
              <div class="act-sub">{{ t.goi_tin }}</div>
              <div class="act-meta"><i class="fa-regular fa-calendar"></i> {{ t.date }} · <span class="act-price">{{ t.so_tien_formatted }}</span></div>
            </div>
            <div class="act-status" :class="t.trang_thai === 'success' ? 'status-ok' : 'status-pending'">
              <i class="fa-solid" :class="t.trang_thai === 'success' ? 'fa-check' : 'fa-clock'"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ══════════ BĐS CHỜ DUYỆT ══════════ -->
    <div class="db-card db-card--full" v-if="bdsChoDuyet.length > 0">
      <div class="db-card__head">
        <h3 class="db-card__title">
          <i class="fa-solid fa-hourglass-half" style="color:#f59e0b"></i>
          BĐS Chờ Duyệt
          <span class="pending-badge">{{ stats.cho_duyet }}</span>
        </h3>
        <router-link to="/admin/bat-dong-san" class="db-view-all">Xem &amp; duyệt tất cả</router-link>
      </div>
      <div class="pending-table-wrap">
        <table class="pending-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Tiêu đề</th>
              <th>Loại</th>
              <th>Môi giới</th>
              <th>Tỉnh/TP</th>
              <th>Giá</th>
              <th>Ngày đăng</th>
              <th>Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(b, i) in bdsChoDuyet" :key="b.id">
              <td class="td-idx">{{ i + 1 }}</td>
              <td class="td-title">{{ b.tieu_de }}</td>
              <td><span class="loai-tag">{{ b.loai }}</span></td>
              <td class="td-mg">{{ b.moi_gioi }}</td>
              <td>{{ b.tinh }}</td>
              <td class="td-price">{{ formatPriceCompact(b.gia) }}</td>
              <td>{{ b.ngay }}</td>
              <td>
                <router-link to="/admin/bat-dong-san" class="btn-duyet">
                  <i class="fa-solid fa-check"></i> Xem &amp; duyệt
                </router-link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { useRouter } from "vue-router";
import api from "@/axios/config";

const router = useRouter();
const currentTime = ref("");
const currentDate = ref("");
const lastUpdateTime = ref("Vừa xong");
const autoRefresh = ref(false);

let timerInterval = null;
let autoRefreshInterval = null;

// ── State ──
const loading = ref(false);
const overviewLoading = ref(false);
const favoritesLoading = ref(false);
const transactionsLoading = ref(false);

const stats = ref({
  khach_hang: 0,
  moi_gioi: 0,
  bat_dong_san: 0,
  giao_dich: 0,
  cho_duyet: 0,
  doanh_thu_thang: 0,
  doanh_thu_change: 0,
  giao_dich_hom_nay: 0,
  giao_dich_hom_qua: 0,
});
const topMoiGioi = ref([]);
const bdsChoDuyet = ref([]);

// ── KPI card definitions ──
const kpiCards = computed(() => [
  {
    key: "khach_hang",
    label: "Khách Hàng",
    desc: "Tổng số khách hàng",
    icon: "fa-solid fa-users",
    gradient: "linear-gradient(135deg,#667eea,#764ba2)",
    color: "#667eea",
    link: "/admin/khach-hang",
  },
  {
    key: "moi_gioi",
    label: "Môi Giới",
    desc: "Tổng số môi giới",
    icon: "fa-solid fa-user-tie",
    gradient: "linear-gradient(135deg,#f093fb,#f5576c)",
    color: "#f5576c",
    link: "/admin/moi-gioi",
  },
  {
    key: "bat_dong_san",
    label: "BĐS Đã Duyệt",
    desc: "Tin đang hiển thị",
    icon: "fa-solid fa-building",
    gradient: "linear-gradient(135deg,#4facfe,#00f2fe)",
    color: "#4facfe",
    link: "/admin/bat-dong-san",
  },
  {
    key: "giao_dich",
    label: "Giao Dịch",
    desc: "Giao dịch thành công",
    icon: "fa-solid fa-chart-line",
    gradient: "linear-gradient(135deg,#43e97b,#38f9d7)",
    color: "#43e97b",
    link: "/admin/giao-dich",
  },
  {
    key: "cho_duyet",
    label: "Chờ Duyệt",
    desc: "BĐS cần xét duyệt",
    icon: "fa-solid fa-hourglass-half",
    gradient: "linear-gradient(135deg,#f59e0b,#fbbf24)",
    color: "#f59e0b",
    link: "/admin/bat-dong-san",
  },
  {
    key: "doanh_thu_thang",
    label: "Doanh Thu Tháng",
    desc: "Tổng thu tháng này",
    icon: "fa-solid fa-sack-dollar",
    gradient: "linear-gradient(135deg,#0ea5e9,#6366f1)",
    color: "#0ea5e9",
    format: (v) => formatCurrencyCompact(v),
  },
]);

const timePeriod = ref("30days");
const isAreaChart = ref(true);
const chartLoaded = ref(false);
const propertyChartLoaded = ref(false);

// ── Chart configs ──
const revenueChartSeries = ref([]);
const revenueChartOptions = ref({
  chart: { type: "area", height: 320, toolbar: { show: false }, animations: { enabled: true }, fontFamily: "inherit" },
  stroke: { curve: "smooth", width: 3 },
  fill: { type: "gradient", gradient: { shadeIntensity: 1, opacityFrom: 0.55, opacityTo: 0.05, stops: [0, 95, 100] } },
  dataLabels: { enabled: false },
  xaxis: { categories: [], axisBorder: { show: false }, axisTicks: { show: false }, labels: { style: { fontSize: "11px" } } },
  yaxis: [
    { title: { text: "Doanh thu (VNĐ)", style: { fontSize: "11px" } }, labels: { formatter: (v) => new Intl.NumberFormat("vi-VN", { notation: "compact" }).format(v) } },
    { opposite: true, title: { text: "Số giao dịch", style: { fontSize: "11px" } } },
  ],
  tooltip: { theme: "light", shared: true, intersect: false },
  legend: { position: "top", horizontalAlign: "right", fontSize: "12px" },
  colors: ["#3b82f6", "#10b981"],
  grid: { borderColor: "#f1f5f9", strokeDashArray: 4 },
});

const propertyChartSeries = ref([]);
const propertyChartOptions = ref({
  chart: { type: "donut", height: 320, toolbar: { show: false }, fontFamily: "inherit" },
  labels: [],
  colors: ["#3b82f6", "#10b981", "#f59e0b", "#ef4444", "#8b5cf6"],
  plotOptions: {
    pie: {
      donut: {
        size: "65%",
        labels: {
          show: true,
          name: { fontSize: "13px", fontWeight: "600" },
          value: { fontSize: "18px", fontWeight: "700" },
          total: { show: true, label: "Tổng BĐS", formatter: () => stats.value.bat_dong_san },
        },
      },
    },
  },
  dataLabels: { enabled: false },
  legend: { position: "bottom", fontSize: "12px" },
});

const favorites = ref([]);
const transactions = ref([]);

// ── API ──
const fetchOverview = async () => {
  overviewLoading.value = true;
  try {
    const res = await api.get("/admin/dashboard/overview");
    if (res.data.status) {
      stats.value = { ...stats.value, ...res.data.stats };
      topMoiGioi.value = res.data.top_moi_gioi || [];
      bdsChoDuyet.value = res.data.bds_cho_duyet || [];
    }
  } catch (e) {
    console.error("fetchOverview:", e);
  } finally {
    overviewLoading.value = false;
  }
};

const fetchChartData = async () => {
  try {
    const res = await api.post("/admin/dashboard/revenue-chart", { period: timePeriod.value });
    if (res.data.status && res.data.data) {
      const data = res.data.data;
      revenueChartSeries.value = [
        { name: "Doanh thu", data: data.map((d) => d.revenue) },
        { name: "Giao dịch", data: data.map((d) => d.transaction_count) },
      ];
      revenueChartOptions.value = {
        ...revenueChartOptions.value,
        xaxis: { ...revenueChartOptions.value.xaxis, categories: data.map((d) => d.date_display) },
      };
    }
    chartLoaded.value = true;
    fetchPropertyChartData();
  } catch (e) {
    chartLoaded.value = true;
  }
};

const fetchPropertyChartData = async () => {
  try {
    const res = await api.get("/admin/dashboard/property-status-chart");
    if (res.data.status && res.data.data) {
      propertyChartSeries.value = res.data.data.series;
      propertyChartOptions.value = { ...propertyChartOptions.value, labels: res.data.data.labels };
    }
  } finally {
    propertyChartLoaded.value = true;
  }
};

const fetchFavorites = async () => {
  favoritesLoading.value = true;
  try {
    const res = await api.get("/admin/dashboard/recent-favorites", { params: { limit: 5 } });
    if (res.data.status) favorites.value = res.data.data;
  } finally {
    favoritesLoading.value = false;
  }
};

const fetchTransactions = async () => {
  transactionsLoading.value = true;
  try {
    const res = await api.get("/admin/dashboard/recent-package-purchases", { params: { limit: 5 } });
    if (res.data.status) transactions.value = res.data.data;
  } finally {
    transactionsLoading.value = false;
  }
};

const loadAll = async () => {
  loading.value = true;
  await Promise.all([fetchOverview(), fetchChartData(), fetchFavorites(), fetchTransactions()]);
  loading.value = false;
  lastUpdateTime.value = new Date().toLocaleTimeString("vi-VN", { hour: "2-digit", minute: "2-digit" });
};

// ── Helpers ──
const toggleChartType = () => {
  isAreaChart.value = !isAreaChart.value;
  revenueChartOptions.value = {
    ...revenueChartOptions.value,
    chart: { ...revenueChartOptions.value.chart, type: isAreaChart.value ? "area" : "bar" },
    stroke: { ...revenueChartOptions.value.stroke, width: isAreaChart.value ? 3 : 0 },
  };
};

const toggleAutoRefresh = () => {
  autoRefresh.value = !autoRefresh.value;
  if (autoRefresh.value) {
    loadAll();
    autoRefreshInterval = setInterval(loadAll, 30000);
  } else {
    clearInterval(autoRefreshInterval);
  }
};

const updateTime = () => {
  const now = new Date();
  currentTime.value = now.toLocaleTimeString("vi-VN", { hour: "2-digit", minute: "2-digit", second: "2-digit" });
  currentDate.value = now.toLocaleDateString("vi-VN", { weekday: "long", year: "numeric", month: "long", day: "numeric" });
};

const formatNumber = (n) => new Intl.NumberFormat("vi-VN").format(n || 0);
const formatCurrencyCompact = (v) => {
  if (!v) return "0 ₫";
  if (v >= 1e9) return (v / 1e9).toFixed(1) + " Tỷ";
  if (v >= 1e6) return (v / 1e6).toFixed(0) + " Tr";
  return new Intl.NumberFormat("vi-VN").format(v) + " ₫";
};
const formatPriceCompact = (v) => {
  if (!v) return "Liên hệ";
  if (v >= 1e9) return (v / 1e9).toFixed(1) + " Tỷ";
  if (v >= 1e6) return (v / 1e6).toFixed(0) + " Tr";
  return formatNumber(v) + " ₫";
};
const getInitials = (name) => {
  if (!name) return "??";
  return name.split(" ").map((n) => n[0]).join("").toUpperCase().slice(0, 2);
};

onMounted(() => {
  loadAll();
  updateTime();
  timerInterval = setInterval(updateTime, 1000);
});
onUnmounted(() => {
  clearInterval(timerInterval);
  clearInterval(autoRefreshInterval);
});
</script>


<style scoped>
/* ══════════════════════════════════════
   WRAPPER
══════════════════════════════════════ */
.db-wrap {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  padding: 1.5rem;
  background: #f1f5f9;
  min-height: 100vh;
}

/* ══════════════════════════════════════
   HERO
══════════════════════════════════════ */
.db-hero {
  position: relative;
  border-radius: 24px;
  overflow: hidden;
  background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 55%, #1d4ed8 100%);
  box-shadow: 0 24px 64px -12px rgba(0,0,0,.55);
}

.db-hero__orbs { position: absolute; inset: 0; overflow: hidden; z-index: 1; pointer-events: none; }
.orb {
  position: absolute;
  border-radius: 50%;
  filter: blur(80px);
  opacity: .5;
  animation: float 20s infinite ease-in-out;
}
.orb-1 { width: 420px; height: 420px; background: linear-gradient(135deg,#3b82f6,#8b5cf6); top: -120px; right: -120px; animation-delay: 0s; }
.orb-2 { width: 320px; height: 320px; background: linear-gradient(135deg,#10b981,#3b82f6); bottom: -60px; right: 22%; animation-delay: -5s; }
.orb-3 { width: 260px; height: 260px; background: linear-gradient(135deg,#f59e0b,#ef4444); top: 35%; right: 8%; animation-delay: -10s; }

@keyframes float {
  0%, 100% { transform: translate(0,0) scale(1); }
  33% { transform: translate(30px,-30px) scale(1.1); }
  66% { transform: translate(-20px,20px) scale(.9); }
}

.db-hero__grid {
  position: absolute; inset: 0; z-index: 1; pointer-events: none;
  background-image: linear-gradient(rgba(255,255,255,.03) 1px,transparent 1px), linear-gradient(90deg,rgba(255,255,255,.03) 1px,transparent 1px);
  background-size: 50px 50px;
}

.db-hero__inner {
  position: relative; z-index: 10;
  display: grid; grid-template-columns: 1fr auto;
  gap: 3rem; padding: 2.5rem;
  align-items: flex-start;
}

/* Left */
.db-hero__left { display: flex; flex-direction: column; gap: 1.25rem; }

.db-badge {
  display: inline-flex; align-items: center; gap: .5rem;
  padding: .45rem 1rem;
  background: rgba(255,255,255,.1); backdrop-filter: blur(10px);
  border: 1px solid rgba(255,255,255,.2); border-radius: 50px;
  font-size: .72rem; font-weight: 700; text-transform: uppercase;
  letter-spacing: .1em; color: rgba(255,255,255,.9); width: fit-content;
}
.db-badge i { color: #10b981; }

.db-hero__title { font-size: 2.6rem; font-weight: 900; color: #fff; margin: 0; line-height: 1.2; }
.db-hero__accent {
  display: block;
  background: linear-gradient(135deg,#60a5fa,#a78bfa);
  -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
}
.db-hero__sub { font-size: .95rem; color: rgba(255,255,255,.7); margin: 0; line-height: 1.6; max-width: 580px; }

/* Hero KPIs */
.db-hero__kpis { display: flex; flex-wrap: wrap; gap: 1rem; margin-top: .5rem; }
.hkpi {
  display: flex; align-items: center; gap: .9rem;
  padding: .9rem 1.2rem;
  background: rgba(255,255,255,.08); backdrop-filter: blur(10px);
  border: 1px solid rgba(255,255,255,.15); border-radius: 16px;
  transition: all .3s;
}
.hkpi:hover { background: rgba(255,255,255,.13); transform: translateY(-2px); }
.hkpi__icon {
  width: 44px; height: 44px; border-radius: 12px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.2rem; color: #fff;
}
.hkpi__label { font-size: .7rem; color: rgba(255,255,255,.6); font-weight: 500; text-transform: uppercase; letter-spacing: .05em; }
.hkpi__val { font-size: 1.35rem; font-weight: 800; color: #fff; }
.hkpi__badge {
  display: flex; align-items: center; gap: .25rem;
  font-size: .72rem; font-weight: 700;
  padding: .25rem .6rem; border-radius: 8px; margin-left: auto; white-space: nowrap;
}
.hkpi__badge.positive { background: rgba(52,211,153,.15); color: #34d399; }
.hkpi__badge.negative { background: rgba(248,113,113,.15); color: #f87171; }
.hkpi__action {
  font-size: .72rem; font-weight: 700; color: #fbbf24;
  text-decoration: none; margin-left: auto; white-space: nowrap;
  padding: .25rem .6rem; border: 1px solid rgba(251,191,36,.3); border-radius: 8px;
  transition: all .2s;
}
.hkpi__action:hover { background: rgba(251,191,36,.15); }

/* Right */
.db-hero__right { display: flex; flex-direction: column; gap: 1rem; align-items: flex-end; }

.db-clock-card {
  background: rgba(255,255,255,.08); backdrop-filter: blur(20px);
  border: 1px solid rgba(255,255,255,.15); border-radius: 20px; padding: 1.4rem;
  min-width: 280px;
}
.db-clock {
  display: flex; align-items: center; gap: .5rem;
  font-size: 1.9rem; font-weight: 800; color: #fff; margin-bottom: .25rem;
}
.db-date { font-size: .82rem; color: rgba(255,255,255,.6); margin-bottom: 1.25rem;
  padding-bottom: 1.25rem; border-bottom: 1px solid rgba(255,255,255,.1); text-transform: capitalize; }
.db-controls { display: flex; gap: .75rem; align-items: flex-end; flex-wrap: wrap; }

.btn-autoref {
  width: 44px; height: 44px; border-radius: 10px;
  border: 1px solid rgba(255,255,255,.2); background: rgba(255,255,255,.05);
  color: rgba(255,255,255,.75); cursor: pointer; font-size: 1.1rem; transition: all .3s;
}
.btn-autoref:hover, .btn-autoref.active { background: rgba(59,130,246,.3); border-color: #3b82f6; color: #60a5fa; }

.btn-refresh {
  display: flex; align-items: center; gap: .4rem;
  padding: .65rem 1.2rem; background: linear-gradient(135deg,#3b82f6,#2563eb);
  border: none; border-radius: 10px; color: #fff; font-weight: 700;
  cursor: pointer; transition: all .3s; box-shadow: 0 4px 15px rgba(59,130,246,.4);
  font-size: .85rem;
}
.btn-refresh:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(59,130,246,.6); }
.btn-refresh:disabled { opacity: .6; cursor: not-allowed; }

.db-period { display: flex; flex-direction: column; gap: .35rem; }
.db-period label { font-size: .68rem; color: rgba(255,255,255,.6); font-weight: 600; text-transform: uppercase; letter-spacing: .05em; }
.db-select {
  padding: .6rem .9rem; background: rgba(255,255,255,.1);
  border: 1px solid rgba(255,255,255,.2); border-radius: 10px;
  color: #fff; font-weight: 600; cursor: pointer; outline: none;
  transition: all .3s; font-size: .83rem; min-width: 160px;
}
.db-select option { background: #1e1b4b; color: #fff; }
.db-select:focus { border-color: #60a5fa; }

.db-live-badge {
  display: flex; align-items: center; gap: .75rem;
  padding: .65rem 1.1rem;
  background: rgba(255,255,255,.08); backdrop-filter: blur(10px);
  border: 1px solid rgba(255,255,255,.15); border-radius: 50px;
}
.pulse-dot {
  width: 10px; height: 10px; background: #10b981; border-radius: 50%;
  position: relative; animation: pulse-dot 2s infinite; flex-shrink: 0;
}
.pulse-dot::before {
  content: ""; position: absolute; inset: -4px; border-radius: 50%;
  border: 2px solid #10b981; animation: pulse-ring 2s infinite;
}
@keyframes pulse-dot { 0%,100%{opacity:1} 50%{opacity:.5} }
@keyframes pulse-ring { 0%{transform:scale(.8);opacity:1} 100%{transform:scale(2);opacity:0} }

/* ══════════════════════════════════════
   6-KPI GRID
══════════════════════════════════════ */
.db-kpi-grid {
  display: grid;
  grid-template-columns: repeat(6, 1fr);
  gap: 1rem;
}

.kpi-card {
  background: #fff; border-radius: 18px;
  padding: 1.35rem 1.25rem;
  display: flex; align-items: center; gap: 1rem;
  position: relative; overflow: hidden;
  box-shadow: 0 1px 4px rgba(0,0,0,.06);
  transition: all .3s;
  border: 1px solid #e2e8f0;
}
.kpi-card::before {
  content: "";
  position: absolute; left: 0; top: 0; bottom: 0; width: 4px;
  background: var(--accent, #3b82f6); border-radius: 4px 0 0 4px;
}
.kpi-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,.1); }

.kpi-card__icon {
  width: 52px; height: 52px; border-radius: 14px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.35rem; color: #fff;
  box-shadow: 0 4px 14px rgba(0,0,0,.2);
}
.kpi-card__body { flex: 1; min-width: 0; }
.kpi-card__label { font-size: .72rem; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: .06em; }
.kpi-card__value { font-size: 1.55rem; font-weight: 800; color: #0f172a; line-height: 1.2; }
.kpi-card__desc { font-size: .72rem; color: #94a3b8; margin-top: .15rem; }
.kpi-card__link {
  width: 32px; height: 32px; border-radius: 8px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  background: #f1f5f9; color: #64748b; text-decoration: none;
  transition: all .2s;
}
.kpi-card__link:hover { background: var(--accent); color: #fff; }

.kpi-card__skeleton { display: flex; align-items: center; gap: 1rem; width: 100%; }
.sk-circle { width: 52px; height: 52px; border-radius: 14px; flex-shrink: 0; }
.sk-lines { flex: 1; display: flex; flex-direction: column; gap: .5rem; }
.sk-line { border-radius: 6px; height: 14px; }
.sk-s { width: 50%; }
.sk-m { width: 75%; height: 22px; }
.sk-xs { width: 60%; }
.sk-circle, .sk-line { background: linear-gradient(90deg,#e2e8f0 25%,#f1f5f9 50%,#e2e8f0 75%); background-size: 200% 100%; animation: sk-anim 1.5s infinite; }
@keyframes sk-anim { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

/* ══════════════════════════════════════
   CHARTS ROW
══════════════════════════════════════ */
.db-charts-row {
  display: grid; grid-template-columns: 2fr 1fr; gap: 1.25rem;
}

/* ══════════════════════════════════════
   CARDS (shared)
══════════════════════════════════════ */
.db-card {
  background: #fff; border-radius: 18px; padding: 1.5rem;
  box-shadow: 0 1px 4px rgba(0,0,0,.06); border: 1px solid #e2e8f0;
}
.db-card--full { width: 100%; }
.db-card__head {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 1.25rem; gap: .5rem;
}
.db-card__title {
  display: flex; align-items: center; gap: .55rem;
  font-size: 1rem; font-weight: 700; color: #0f172a; margin: 0;
}
.btn-toggle-chart {
  width: 36px; height: 36px; border-radius: 10px;
  border: 1px solid #e2e8f0; background: #f8fafc;
  color: #64748b; cursor: pointer; font-size: 1rem; transition: all .2s;
}
.btn-toggle-chart:hover { background: #3b82f6; color: #fff; border-color: #3b82f6; }

.chart-skeleton { height: 320px; display: flex; flex-direction: column; justify-content: flex-end; gap: .5rem; padding: .5rem; }
.sk-rect { width: 100%; height: 250px; border-radius: 8px; background: linear-gradient(90deg,#e2e8f0 25%,#f1f5f9 50%,#e2e8f0 75%); background-size: 200% 100%; animation: sk-anim 1.5s infinite; }
.sk-donut { width: 220px; height: 220px; border-radius: 50%; background: linear-gradient(90deg,#e2e8f0 25%,#f1f5f9 50%,#e2e8f0 75%); background-size: 200% 100%; animation: sk-anim 1.5s infinite; }

.db-view-all {
  font-size: .78rem; font-weight: 700; color: #3b82f6; text-decoration: none;
  padding: .3rem .75rem; border: 1px solid #dbeafe; border-radius: 8px;
  transition: all .2s; white-space: nowrap;
}
.db-view-all:hover { background: #eff6ff; }

/* ══════════════════════════════════════
   BOTTOM 3-COL
══════════════════════════════════════ */
.db-bottom-row {
  display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.25rem;
}

/* Top Brokers */
.top-broker-list { display: flex; flex-direction: column; gap: .6rem; }
.top-broker-item {
  display: flex; align-items: center; gap: .85rem;
  padding: .7rem .85rem; border-radius: 12px; border: 1px solid #f1f5f9;
  transition: all .2s;
}
.top-broker-item:hover { background: #f8fafc; }
.top-broker-rank {
  width: 28px; height: 28px; border-radius: 8px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  font-size: .78rem; font-weight: 800; color: #fff;
  background: #cbd5e1;
}
.rank-1 { background: linear-gradient(135deg,#f59e0b,#fbbf24); }
.rank-2 { background: linear-gradient(135deg,#94a3b8,#cbd5e1); }
.rank-3 { background: linear-gradient(135deg,#b45309,#d97706); }

.top-broker-avatar {
  width: 38px; height: 38px; border-radius: 50%; background: linear-gradient(135deg,#667eea,#764ba2);
  display: flex; align-items: center; justify-content: center;
  font-size: .8rem; font-weight: 700; color: #fff; flex-shrink: 0;
}
.top-broker-info { flex: 1; min-width: 0; }
.top-broker-name { font-size: .88rem; font-weight: 600; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.top-broker-email { font-size: .72rem; color: #94a3b8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.top-broker-stat { text-align: right; }
.top-broker-count { font-size: 1.1rem; font-weight: 800; color: #1e40af; }
.top-broker-slabel { font-size: .65rem; color: #94a3b8; font-weight: 500; }

/* Activity lists */
.activity-list { display: flex; flex-direction: column; gap: .5rem; }
.activity-item {
  display: flex; align-items: center; gap: .85rem;
  padding: .7rem .85rem; border-radius: 12px; border: 1px solid #f1f5f9;
  transition: background .2s;
}
.activity-item:hover { background: #f8fafc; }

.act-avatar {
  width: 38px; height: 38px; border-radius: 50%; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  font-size: .8rem; font-weight: 700; color: #fff;
}
.act-purple { background: linear-gradient(135deg,#667eea,#764ba2); }
.act-blue { background: linear-gradient(135deg,#4facfe,#00f2fe); }

.act-body { flex: 1; min-width: 0; }
.act-name { font-size: .88rem; font-weight: 600; color: #0f172a; }
.act-sub { font-size: .76rem; color: #475569; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.act-meta { font-size: .7rem; color: #94a3b8; margin-top: .2rem; display: flex; gap: .4rem; align-items: center; flex-wrap: wrap; }
.act-price { font-weight: 700; color: #10b981; }

.act-status {
  width: 30px; height: 30px; border-radius: 8px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center; font-size: .82rem;
}
.status-ok { background: #dcfce7; color: #16a34a; }
.status-pending { background: #fef9c3; color: #b45309; }

/* Utils */
.db-loading { display: flex; align-items: center; gap: .5rem; color: #94a3b8; font-size: .9rem; padding: 1.5rem; justify-content: center; }
.db-empty { display: flex; flex-direction: column; align-items: center; gap: .5rem; color: #cbd5e1; padding: 2rem; font-size: .85rem; }
.db-empty i { font-size: 2rem; }

/* ══════════════════════════════════════
   PENDING BDS TABLE
══════════════════════════════════════ */
.pending-badge {
  display: inline-flex; align-items: center; justify-content: center;
  min-width: 22px; height: 22px; padding: 0 6px;
  background: #fef3c7; color: #b45309;
  border-radius: 50px; font-size: .72rem; font-weight: 700;
}
.pending-table-wrap { overflow-x: auto; border-radius: 12px; }
.pending-table {
  width: 100%; border-collapse: collapse; font-size: .85rem;
}
.pending-table thead th {
  background: #f8fafc; padding: .75rem 1rem; text-align: left;
  font-size: .72rem; font-weight: 700; color: #64748b;
  text-transform: uppercase; letter-spacing: .06em; white-space: nowrap;
  border-bottom: 1px solid #e2e8f0;
}
.pending-table tbody tr {
  border-bottom: 1px solid #f1f5f9; transition: background .15s;
}
.pending-table tbody tr:hover { background: #f8fafc; }
.pending-table td { padding: .8rem 1rem; color: #334155; vertical-align: middle; }
.td-idx { font-weight: 700; color: #94a3b8; width: 40px; }
.td-title { font-weight: 600; color: #0f172a; max-width: 240px; }
.td-mg { color: #475569; }
.td-price { font-weight: 700; color: #0ea5e9; white-space: nowrap; }

.loai-tag {
  display: inline-block; padding: .2rem .6rem;
  background: #eff6ff; color: #2563eb; border-radius: 6px;
  font-size: .72rem; font-weight: 600;
}
.btn-duyet {
  display: inline-flex; align-items: center; gap: .35rem;
  padding: .35rem .85rem; background: linear-gradient(135deg,#10b981,#34d399);
  color: #fff; border-radius: 8px; font-size: .78rem; font-weight: 700;
  text-decoration: none; transition: all .2s; white-space: nowrap;
  box-shadow: 0 2px 8px rgba(16,185,129,.25);
}
.btn-duyet:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(16,185,129,.4); }

/* ══════════════════════════════════════
   RESPONSIVE
══════════════════════════════════════ */
@media (max-width: 1400px) {
  .db-kpi-grid { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 1100px) {
  .db-hero__inner { grid-template-columns: 1fr; }
  .db-hero__right { align-items: flex-start; }
  .db-clock-card { min-width: unset; width: 100%; }
  .db-charts-row { grid-template-columns: 1fr; }
  .db-bottom-row { grid-template-columns: 1fr; }
}
@media (max-width: 700px) {
  .db-kpi-grid { grid-template-columns: repeat(2, 1fr); }
  .db-hero__title { font-size: 1.8rem; }
  .db-hero__kpis { flex-direction: column; }
}
</style>
