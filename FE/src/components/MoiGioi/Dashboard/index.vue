<template>
  <div class="mg-dashboard">
    <!-- Hero Section -->
    <div class="mg-hero">
      <div class="mg-hero__orbs">
        <span class="orb orb-1"></span>
        <span class="orb orb-2"></span>
      </div>
      <div class="mg-hero__content">
        <div class="mg-hero__badge">
          <i class="fa-solid fa-chart-line"></i> Dashboard Môi Giới
        </div>
        <h1 class="mg-hero__title">Xin chào, <span class="accent">{{ brokerName }}</span></h1>
        <p class="mg-hero__sub">Theo dõi hiệu suất, quản lý gói tin và xem thống kê chi tiết</p>
      </div>
    </div>

    <!-- Stats Grid -->
    <div class="mg-stats-grid">
      <div class="mg-stat-card primary">
        <div class="mg-stat__icon"><i class="fa-solid fa-house-chimney"></i></div>
        <div class="mg-stat__info">
          <div class="mg-stat__value">{{ stats.active_bds }}</div>
          <div class="mg-stat__label">BĐS đang hoạt động</div>
        </div>
        <div class="mg-stat__trend up">
          <i class="fa-solid fa-arrow-trend-up"></i>
        </div>
      </div>

      <div class="mg-stat-card">
        <div class="mg-stat__icon" style="background: linear-gradient(135deg,#f59e0b,#fbbf24);color:#fff">
          <i class="fa-solid fa-clock"></i>
        </div>
        <div class="mg-stat__info">
          <div class="mg-stat__value">{{ stats.pending_bds }}</div>
          <div class="mg-stat__label">Chờ duyệt</div>
        </div>
      </div>

      <div class="mg-stat-card">
        <div class="mg-stat__icon" style="background: linear-gradient(135deg,#ef4444,#f87171);color:#fff">
          <i class="fa-solid fa-circle-exclamation"></i>
        </div>
        <div class="mg-stat__info">
          <div class="mg-stat__value">{{ stats.expired_bds }}</div>
          <div class="mg-stat__label">Đã hết hạn</div>
        </div>
      </div>

      <div class="mg-stat-card">
        <div class="mg-stat__icon" style="background: linear-gradient(135deg,#10b981,#34d399);color:#fff">
          <i class="fa-solid fa-coins"></i>
        </div>
        <div class="mg-stat__info">
          <div class="mg-stat__value">{{ stats.credits_remaining }}</div>
          <div class="mg-stat__label">Tín chỉ còn</div>
        </div>
      </div>

      <div class="mg-stat-card">
        <div class="mg-stat__icon" style="background: linear-gradient(135deg,#8b5cf6,#a78bfa);color:#fff">
          <i class="fa-solid fa-heart"></i>
        </div>
        <div class="mg-stat__info">
          <div class="mg-stat__value">{{ stats.total_favorites }}</div>
          <div class="mg-stat__label">Lượt yêu thích</div>
        </div>
      </div>

      <div class="mg-stat-card">
        <div class="mg-stat__icon" style="background: linear-gradient(135deg,#3b82f6,#60a5fa);color:#fff">
          <i class="fa-solid fa-users"></i>
        </div>
        <div class="mg-stat__info">
          <div class="mg-stat__value">{{ stats.total_contacts }}</div>
          <div class="mg-stat__label">Khách liên hệ</div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="mg-main">
      <!-- Left Column -->
      <div class="mg-col-left">
        <!-- Chart -->
        <div class="mg-card">
          <div class="mg-card__head">
            <h3 class="mg-card__title"><i class="fa-solid fa-chart-column" style="color:#3b82f6"></i> Bài đăng 6 tháng qua</h3>
          </div>
          <apexchart type="bar" height="280" :options="chartOptions" :series="chartSeries" />
        </div>

        <!-- Recent BDS -->
        <div class="mg-card">
          <div class="mg-card__head">
            <h3 class="mg-card__title"><i class="fa-solid fa-list" style="color:#8b5cf6"></i> BĐS gần đây</h3>
            <router-link to="/moi-gioi/bds" class="mg-card__link">Xem tất cả →</router-link>
          </div>
          <div class="mg-bds-list">
            <div v-for="bds in recentBds" :key="bds.id" class="mg-bds-item">
              <img :src="bds.anh_dai_dien_url || defaultImage" class="mg-bds__img" />
              <div class="mg-bds__info">
                <div class="mg-bds__title">{{ bds.tieu_de }}</div>
                <div class="mg-bds__meta">
                  <span class="mg-bds__price">{{ formatPrice(bds.gia) }}</span>
                  <span class="mg-bds__type">{{ bds.loai }}</span>
                </div>
                <div class="mg-bds__status" :class="{ approved: bds.is_duyet, pending: !bds.is_duyet }">
                  {{ bds.is_duyet ? 'Đã duyệt' : 'Chờ duyệt' }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column -->
      <div class="mg-col-right">
        <!-- Package Card -->
        <div class="mg-card mg-card--plan" :class="{ 'expiring': plan.days_until_expiry !== null && plan.days_until_expiry <= 7 }">
          <div class="mg-card__head">
            <h3 class="mg-card__title"><i class="fa-solid fa-crown" style="color:#f59e0b"></i> Gói hiện tại</h3>
          </div>

          <div v-if="plan.current" class="mg-plan">
            <div class="mg-plan__name">{{ plan.current.ten_goi }}</div>
            <div class="mg-plan__price">{{ formatPrice(plan.current.gia) }}<span>/{{ plan.current.so_ngay }} ngày</span></div>
            <div class="mg-plan__features">
              <div class="mg-plan__feat"><i class="fa-solid fa-check"></i> {{ plan.current.so_luong_tin }} tin đăng</div>
              <div class="mg-plan__feat"><i class="fa-solid fa-check"></i> {{ stats.credits_remaining }} tin còn lại</div>
            </div>
            <div class="mg-plan__expiry" :class="{ warning: plan.days_until_expiry !== null && plan.days_until_expiry <= 7 }">
              <i class="fa-regular fa-calendar"></i>
              Hết hạn: {{ plan.expiry_date }}
              <span v-if="plan.days_until_expiry !== null" class="days-left">
                ({{ plan.days_until_expiry > 0 ? `còn ${plan.days_until_expiry} ngày` : 'đã hết hạn' }})
              </span>
            </div>
            <router-link to="/moi-gioi/goi-tin" class="mg-plan__btn">
              <i class="fa-solid fa-arrow-up-right-from-square"></i> Gia hạn / Nâng cấp
            </router-link>
          </div>

          <div v-else class="mg-plan--empty">
            <i class="fa-regular fa-circle-xmark" style="font-size:2rem;color:#94a3b8"></i>
            <p>Chưa có gói tin nào</p>
            <router-link to="/moi-gioi/goi-tin" class="mg-plan__btn">
              Mua gói tin →
            </router-link>
          </div>
        </div>

        <!-- Spending Card -->
        <div class="mg-card">
          <div class="mg-card__head">
            <h3 class="mg-card__title"><i class="fa-solid fa-wallet" style="color:#10b981"></i> Chi tiêu</h3>
          </div>
          <div class="mg-spending">
            <div class="mg-spending__amount">{{ formatPrice(stats.total_spent) }}</div>
            <div class="mg-spending__label">Tổng chi tiêu</div>
          </div>
          <router-link to="/moi-gioi/giao-dich/lich-su" class="mg-card__link" style="display:block;margin-top:1rem">
            Xem lịch sử giao dịch →
          </router-link>
        </div>

        <!-- Quick Actions -->
        <div class="mg-card">
          <div class="mg-card__head">
            <h3 class="mg-card__title"><i class="fa-solid fa-bolt" style="color:#f59e0b"></i> Thao tác nhanh</h3>
          </div>
          <div class="mg-quick-actions">
            <router-link to="/moi-gioi/bds/create" class="mg-quick-btn">
              <i class="fa-solid fa-plus"></i> Đăng tin mới
            </router-link>
            <router-link to="/moi-gioi/bds" class="mg-quick-btn secondary">
              <i class="fa-solid fa-list"></i> Quản lý BĐS
            </router-link>
            <router-link to="/moi-gioi/chat" class="mg-quick-btn secondary">
              <i class="fa-solid fa-comments"></i> Tin nhắn
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import api from "@/axios/config";
import VueApexCharts from "vue3-apexcharts";

const apexchart = VueApexCharts;

// State
const loading = ref(true);
const brokerName = ref("Môi Giới");
const stats = ref({
  active_bds: 0,
  pending_bds: 0,
  expired_bds: 0,
  total_bds: 0,
  credits_remaining: 0,
  total_favorites: 0,
  total_contacts: 0,
  total_spent: 0,
});
const plan = ref({
  current: null,
  expiry_date: null,
  days_until_expiry: null,
});
const recentBds = ref([]);
const chartLabels = ref([]);
const chartData = ref([]);

const defaultImage = "https://placehold.co/120x90?text=BDS";

// Chart options
const chartOptions = computed(() => ({
  chart: {
    type: "bar",
    toolbar: { show: false },
    fontFamily: "Inter, sans-serif",
  },
  plotOptions: {
    bar: {
      borderRadius: 8,
      columnWidth: "55%",
      distributed: true,
    },
  },
  colors: ["#3b82f6", "#60a5fa", "#93c5fd", "#bfdbfe", "#dbeafe", "#eff6ff"],
  dataLabels: { enabled: false },
  xaxis: {
    categories: chartLabels.value,
    labels: { style: { colors: "#64748b", fontSize: "11px" } },
    axisBorder: { show: false },
    axisTicks: { show: false },
  },
  yaxis: {
    labels: { style: { colors: "#64748b", fontSize: "11px" } },
  },
  grid: {
    borderColor: "#f1f5f9",
    strokeDashArray: 4,
  },
  tooltip: {
    y: { formatter: (val) => `${val} tin` },
  },
}));

const chartSeries = computed(() => [{
  name: "Bài đăng",
  data: chartData.value,
}]);

// Methods
const formatPrice = (price) => {
  if (!price) return "0 ₫";
  return new Intl.NumberFormat("vi-VN", { style: "currency", currency: "VND", notation: "compact" }).format(price);
};

const loadDashboard = async () => {
  try {
    loading.value = true;
    const res = await api.get("/moi-gioi/thong-ke/dashboard");
    if (res.data?.status) {
      const data = res.data.data;
      stats.value = data.stats;
      plan.value = data.plan;
      recentBds.value = data.recent_bds;
      chartLabels.value = data.chart.labels;
      chartData.value = data.chart.data;
    }

    // Get broker name from profile
    const profileRes = await api.get("/moi-gioi/profile");
    if (profileRes.data?.status) {
      brokerName.value = profileRes.data.data?.ten || "Môi Giới";
    }
  } catch (e) {
    console.error("Lỗi tải dashboard:", e);
  } finally {
    loading.value = false;
  }
};

onMounted(loadDashboard);
</script>

<style scoped>
.mg-dashboard {
  min-height: 100vh;
  background: #f8fafc;
  font-family: "Inter", "Be Vietnam Pro", sans-serif;
}

/* Hero */
.mg-hero {
  position: relative;
  overflow: hidden;
  background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 55%, #2563eb 100%);
  padding: 2.5rem 2rem;
  color: #fff;
}
.mg-hero__orbs { position: absolute; inset: 0; pointer-events: none; }
.orb { position: absolute; border-radius: 50%; filter: blur(60px); opacity: .35; }
.orb-1 { width: 300px; height: 300px; background: linear-gradient(135deg,#8b5cf6,#3b82f6); top: -80px; right: -60px; }
.orb-2 { width: 200px; height: 200px; background: linear-gradient(135deg,#10b981,#3b82f6); bottom: -60px; left: 15%; }
.mg-hero__content { position: relative; z-index: 10; max-width: 700px; }
.mg-hero__badge {
  display: inline-flex; align-items: center; gap: .5rem;
  padding: .35rem .9rem; background: rgba(255,255,255,.1); backdrop-filter: blur(8px);
  border: 1px solid rgba(255,255,255,.2); border-radius: 50px;
  font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em;
  color: rgba(255,255,255,.9); margin-bottom: .75rem;
}
.mg-hero__title { font-size: 2rem; font-weight: 900; margin: 0 0 .5rem; }
.mg-hero__title .accent { background: linear-gradient(135deg,#60a5fa,#a78bfa); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
.mg-hero__sub { font-size: .9rem; color: rgba(255,255,255,.7); margin: 0; }

/* Stats Grid */
.mg-stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 1rem;
  padding: 1.5rem 2rem;
  margin-top: -1.5rem;
}
.mg-stat-card {
  display: flex; align-items: center; gap: 1rem;
  background: #fff; border-radius: 16px; padding: 1.25rem;
  box-shadow: 0 1px 3px rgba(0,0,0,.08); border: 1px solid #e2e8f0;
  transition: transform .2s, box-shadow .2s;
}
.mg-stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,.1); }
.mg-stat-card.primary { border-color: #3b82f6; }
.mg-stat__icon {
  width: 48px; height: 48px; border-radius: 12px;
  background: linear-gradient(135deg,#3b82f6,#2563eb);
  color: #fff; display: flex; align-items: center; justify-content: center;
  font-size: 1.2rem; flex-shrink: 0;
}
.mg-stat__info { flex: 1; }
.mg-stat__value { font-size: 1.5rem; font-weight: 900; color: #0f172a; line-height: 1; }
.mg-stat__label { font-size: .75rem; color: #64748b; margin-top: .25rem; }
.mg-stat__trend { font-size: 1.1rem; }
.mg-stat__trend.up { color: #10b981; }
.mg-stat__trend.down { color: #ef4444; }

/* Main Layout */
.mg-main {
  display: grid; grid-template-columns: 1.5fr 1fr; gap: 1.5rem;
  padding: 0 2rem 2rem;
}

/* Cards */
.mg-card {
  background: #fff; border-radius: 18px; padding: 1.25rem;
  box-shadow: 0 1px 3px rgba(0,0,0,.06); border: 1px solid #e2e8f0;
  margin-bottom: 1.25rem;
}
.mg-card:last-child { margin-bottom: 0; }
.mg-card__head {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 1rem;
}
.mg-card__title { font-size: .95rem; font-weight: 700; color: #0f172a; margin: 0; display: flex; align-items: center; gap: .5rem; }
.mg-card__link { font-size: .8rem; color: #3b82f6; font-weight: 600; text-decoration: none; }
.mg-card__link:hover { text-decoration: underline; }

/* Plan Card */
.mg-card--plan { border-color: #f59e0b; position: relative; overflow: hidden; }
.mg-card--plan.expiring { border-color: #ef4444; background: linear-gradient(135deg,#fef2f2,#fff); }
.mg-card--plan.expiring::before {
  content: "Sắp hết hạn"; position: absolute; top: 10px; right: -30px;
  background: #ef4444; color: #fff; font-size: .65rem; font-weight: 700;
  padding: 3px 35px; transform: rotate(45deg);
}
.mg-plan__name { font-size: 1.25rem; font-weight: 800; color: #0f172a; margin-bottom: .25rem; }
.mg-plan__price { font-size: 1.5rem; font-weight: 900; color: #f59e0b; margin-bottom: 1rem; }
.mg-plan__price span { font-size: .85rem; font-weight: 500; color: #64748b; margin-left: .25rem; }
.mg-plan__features { margin-bottom: 1rem; }
.mg-plan__feat { display: flex; align-items: center; gap: .5rem; font-size: .85rem; color: #475569; margin-bottom: .4rem; }
.mg-plan__feat i { color: #10b981; font-size: .75rem; }
.mg-plan__expiry { font-size: .8rem; color: #64748b; display: flex; align-items: center; gap: .4rem; margin-bottom: 1rem; }
.mg-plan__expiry.warning { color: #ef4444; font-weight: 600; }
.mg-plan__expiry .days-left { font-weight: 700; }
.mg-plan__btn {
  display: inline-flex; align-items: center; gap: .5rem;
  padding: .65rem 1.2rem; background: linear-gradient(135deg,#f59e0b,#fbbf24);
  color: #fff; font-weight: 700; font-size: .85rem; border-radius: 10px;
  text-decoration: none; transition: transform .2s, box-shadow .2s;
}
.mg-plan__btn:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(245,158,11,.35); }
.mg-plan--empty { text-align: center; padding: 1.5rem; }
.mg-plan--empty p { color: #64748b; margin: .75rem 0 1rem; }

/* BDS List */
.mg-bds-list { display: flex; flex-direction: column; gap: .75rem; }
.mg-bds-item {
  display: flex; gap: .75rem; padding: .75rem;
  background: #f8fafc; border-radius: 12px; border: 1px solid #f1f5f9;
  transition: background .2s;
}
.mg-bds-item:hover { background: #f1f5f9; }
.mg-bds__img { width: 80px; height: 60px; object-fit: cover; border-radius: 8px; flex-shrink: 0; }
.mg-bds__info { flex: 1; min-width: 0; }
.mg-bds__title { font-size: .85rem; font-weight: 700; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: .2rem; }
.mg-bds__meta { display: flex; gap: .5rem; font-size: .75rem; margin-bottom: .35rem; }
.mg-bds__price { font-weight: 700; color: #ef4444; }
.mg-bds__type { color: #64748b; }
.mg-bds__status { display: inline-block; font-size: .65rem; font-weight: 700; padding: .15rem .5rem; border-radius: 4px; }
.mg-bds__status.approved { background: #dcfce7; color: #166534; }
.mg-bds__status.pending { background: #fef3c7; color: #92400e; }

/* Spending */
.mg-spending { text-align: center; padding: .5rem; }
.mg-spending__amount { font-size: 1.75rem; font-weight: 900; color: #0f172a; }
.mg-spending__label { font-size: .8rem; color: #64748b; margin-top: .25rem; }

/* Quick Actions */
.mg-quick-actions { display: flex; flex-direction: column; gap: .6rem; }
.mg-quick-btn {
  display: flex; align-items: center; gap: .6rem;
  padding: .85rem 1rem; background: linear-gradient(135deg,#3b82f6,#2563eb);
  color: #fff; font-weight: 600; font-size: .85rem; border-radius: 10px;
  text-decoration: none; transition: all .2s;
}
.mg-quick-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(59,130,246,.3); }
.mg-quick-btn.secondary {
  background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;
}
.mg-quick-btn.secondary:hover { background: #e2e8f0; box-shadow: none; }

/* Responsive */
@media (max-width: 1024px) {
  .mg-main { grid-template-columns: 1fr; }
  .mg-stats-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 640px) {
  .mg-hero__title { font-size: 1.5rem; }
  .mg-stats-grid { grid-template-columns: 1fr; }
  .mg-main { padding: 0 1rem 1.5rem; }
}
</style>
