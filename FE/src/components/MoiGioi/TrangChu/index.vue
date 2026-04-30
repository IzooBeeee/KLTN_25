<template>
  <div class="min-h-screen bg-[#f0f4f8] font-['Inter'] p-6">

    <!-- Page Header -->
    <div class="mb-6">
      <h1 class="text-2xl font-black text-gray-900">Tổng quan</h1>
      <p class="text-sm text-gray-400">Theo dõi hiệu suất và hoạt động của bạn</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-6">

      <!-- 1. Tổng tin đăng -->
      <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 relative overflow-hidden group hover:shadow-md transition-all">
        <div class="flex items-start justify-between mb-4">
          <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Tổng tin đăng</p>
            <div class="text-4xl font-black text-blue-600">{{ stats.totalPosted }}</div>
          </div>
          <div class="w-12 h-12 bg-blue-100 rounded-2xl flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
          </div>
        </div>
        <span class="inline-flex items-center gap-1 text-xs text-green-600 bg-green-50 px-2 py-0.5 rounded-full font-semibold">
          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
          +2 tháng này
        </span>
        <div class="absolute -bottom-3 -right-3 w-20 h-20 bg-blue-50 rounded-full opacity-50"></div>
      </div>

      <!-- 2. Tin còn lại -->
      <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 relative overflow-hidden group hover:shadow-md transition-all">
        <div class="flex items-start justify-between mb-4">
          <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Tin còn lại</p>
            <div class="text-4xl font-black text-green-600">{{ stats.remainingPosts }}</div>
          </div>
          <div class="w-12 h-12 bg-green-100 rounded-2xl flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
          </div>
        </div>
        <p class="text-xs text-gray-400">Sẵn sàng đăng tin</p>
        <div class="absolute -bottom-3 -right-3 w-20 h-20 bg-green-50 rounded-full opacity-50"></div>
      </div>

      <!-- 3. Lượt yêu thích -->
      <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 relative overflow-hidden group hover:shadow-md transition-all">
        <div class="flex items-start justify-between mb-4">
          <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Lượt yêu thích</p>
            <div class="text-4xl font-black text-rose-500">{{ stats.totalFavorites }}</div>
          </div>
          <div class="w-12 h-12 bg-rose-100 rounded-2xl flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-rose-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/></svg>
          </div>
        </div>
        <p class="text-xs text-gray-400">Từ khách hàng tiềm năng</p>
        <div class="absolute -bottom-3 -right-3 w-20 h-20 bg-rose-50 rounded-full opacity-50"></div>
      </div>

      <!-- 4. Tổng tiền gói -->
      <div class="bg-gradient-to-br from-[#0a0e27] to-[#1d4ed8] rounded-2xl p-5 shadow-sm relative overflow-hidden text-white">
        <div class="flex items-start justify-between mb-4">
          <div>
            <p class="text-xs font-bold text-blue-300 uppercase tracking-widest mb-1">Tổng tiền gói</p>
            <div class="text-3xl font-black">{{ Number(stats.totalPaid).toLocaleString("vi-VN") }}</div>
          </div>
          <div class="w-12 h-12 bg-white/15 rounded-2xl flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          </div>
        </div>
        <p class="text-xs text-blue-300">VNĐ đã đầu tư</p>
        <div class="absolute -bottom-4 -right-4 w-24 h-24 bg-white/5 rounded-full"></div>
        <div class="absolute -top-4 -left-4 w-16 h-16 bg-white/5 rounded-full"></div>
      </div>
    </div>

    <!-- Chart + Customer List -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

      <!-- Chart -->
      <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
        <div class="flex items-center justify-between mb-5">
          <h2 class="font-bold text-gray-800">Hiệu suất tin đăng</h2>
          <div class="flex items-center gap-1 bg-gray-100 p-1 rounded-xl">
            <button @click="changeChartPeriod('week')"
              class="px-3 py-1.5 text-xs font-bold rounded-lg transition-all"
              :class="chartPeriod === 'week' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-400 hover:text-gray-600'">
              Tuần này
            </button>
            <button @click="changeChartPeriod('month')"
              class="px-3 py-1.5 text-xs font-bold rounded-lg transition-all"
              :class="chartPeriod === 'month' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-400 hover:text-gray-600'">
              Tháng qua
            </button>
          </div>
        </div>
        <apexchart type="bar" height="280" :options="chartOptions" :series="chartSeries"></apexchart>
      </div>

      <!-- Customer List -->
      <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 flex flex-col">
        <div class="flex items-center gap-3 mb-5">
          <div class="w-9 h-9 bg-amber-100 rounded-xl flex items-center justify-center">
            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
          </div>
          <h2 class="font-bold text-gray-800">Khách hàng liên hệ</h2>
        </div>

        <!-- Loading -->
        <div v-if="loading.customers" class="flex-1 flex items-center justify-center py-8">
          <div class="w-8 h-8 border-3 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
        </div>

        <!-- Empty -->
        <div v-else-if="customers.length === 0" class="flex-1 flex flex-col items-center justify-center py-8 text-center">
          <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mb-3">
            <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          </div>
          <p class="text-sm text-gray-400">Chưa có khách hàng liên hệ</p>
        </div>

        <!-- List -->
        <div v-else class="space-y-3 flex-1 overflow-y-auto max-h-72 pr-1">
          <div v-for="(customer, index) in customers" :key="index"
            class="flex items-start gap-3 p-3 rounded-xl bg-gray-50 hover:bg-blue-50 transition-colors cursor-pointer group">
            <img
              :src="customer.khach_hang?.avatar || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(customer.khach_hang?.ten || 'KH') + '&background=dbeafe&color=3b82f6'"
              class="w-10 h-10 rounded-full object-cover flex-shrink-0 border-2 border-white shadow-sm"
              alt="Customer"
            />
            <div class="flex-1 min-w-0">
              <p class="text-sm font-semibold text-gray-800 truncate">{{ customer.khach_hang?.ten }}</p>
              <p class="text-xs text-blue-500 truncate">{{ customer.bat_dong_san?.tieu_de || 'Quan tâm BĐS' }}</p>
              <div class="flex items-center justify-between mt-1">
                <span class="text-xs text-gray-400">{{ formatRelativeTime(customer.last_contact) }}</span>
                <span class="text-xs text-gray-400">{{ customer.contact_count }} lần</span>
              </div>
            </div>
          </div>
        </div>

        <router-link to="/moi-gioi/khach-hang"
          class="mt-4 flex items-center justify-center gap-2 py-2.5 border-2 border-blue-200 text-blue-600 text-xs font-bold rounded-xl hover:bg-blue-50 transition">
          Xem tất cả khách hàng
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
        </router-link>
      </div>
    </div>

    <!-- Alert Banner -->
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 flex flex-col sm:flex-row items-start sm:items-center gap-4">
      <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0">
        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
      </div>
      <div class="flex-1">
        <h3 class="font-bold text-gray-800 text-sm mb-0.5">Tin đăng sắp hết hạn</h3>
        <p class="text-sm text-gray-500">Một số tin đăng sẽ hết hiệu lực sớm. Hãy gia hạn để không bỏ lỡ khách hàng tiềm năng.</p>
      </div>
      <router-link to="/moi-gioi/quan-ly-bds"
        class="flex-shrink-0 px-5 py-2.5 bg-amber-500 text-white text-sm font-bold rounded-xl hover:bg-amber-600 transition shadow-sm shadow-amber-200">
        Quản lý tin
      </router-link>
    </div>

  </div>
</template>
<script>
import api from "@/axios/config";
import VueApexCharts from "vue3-apexcharts";

export default {
  components: {
    apexchart: VueApexCharts,
  },
  data() {
    return {
      loading: {
        stats: true,
        chart: true,
        customers: true,
      },
      stats: {
        totalPosted: 0,
        remainingPosts: 0,
        totalFavorites: 0,
        totalPaid: 0,
        transactionCount: 0,
      },
      chartPeriod: "week",
      customers: [],
      userInfo: { ten: "Minh Quân", avatar: null },
      newContactsToday: 5,

      // ✅ Cấu hình ApexCharts đã fix
      chartOptions: {
        chart: {
          type: "bar",
          height: 300,
          toolbar: { show: false },
          fontFamily: "Inter, sans-serif",
        },
        plotOptions: {
          bar: {
            borderRadius: 8,
            columnWidth: "40%",
            distributed: false,
          },
        },
        colors: ["#1a237e"],
        dataLabels: { enabled: false },
        grid: {
          borderColor: "#f3f4f6",
          strokeDashArray: 4,
        },
        xaxis: {
          categories: [],
          labels: {
            style: { fontSize: "11px", colors: "#64748b" },
            rotate: -45,
          },
          axisBorder: { show: false },
          axisTicks: { show: false },
        },
        yaxis: {
          labels: {
            style: { fontSize: "11px", colors: "#64748b" },
            formatter: (val) => Math.round(val),
          },
          min: 0,
          tickAmount: 5,
          stepSize: 1,
        },
        tooltip: {
          theme: "light",
          y: {
            formatter: (val) => `${val} tin đăng`,
          },
        },
        legend: { show: false },
        states: {
          hover: { filter: { type: "darken", value: 0.85 } },
        },
      },

      chartSeries: [
        {
          name: "Số tin đăng",
          data: [],
        },
      ],
    };
  },

  mounted() {
    this.fetchUserProfile();
    this.fetchAllStats();
    this.fetchChartData();
    this.fetchCustomers();
  },

  methods: {
    fetchUserProfile() {
      const user = localStorage.getItem("moi_gioi_user");
      if (user) {
        this.userInfo = JSON.parse(user);
      }
    },

    async fetchAllStats() {
      this.loading.stats = true;
      try {
        const token = localStorage.getItem("moi_gioi_auth_token");
        const [postedRes, remainingRes, favoritesRes, paymentRes] =
          await Promise.all([
            api.get("/moi-gioi/thong-ke/tong-tin-da-dang"),
            api.get("/moi-gioi/thong-ke/tin-con-lai"),
            api.get("/moi-gioi/thong-ke/tong-yeu-thich"),
            api.get("/moi-gioi/thong-ke/tong-tien"),
          ]);

        this.stats.totalPosted = postedRes.data.data || 0;
        this.stats.remainingPosts = remainingRes.data.data || 0;
        this.stats.totalFavorites = favoritesRes.data.data || 0;
        this.stats.totalPaid = paymentRes.data.data || 0;
        this.stats.transactionCount =
          Math.floor(this.stats.totalPaid / 200000000) || 12;
      } catch (error) {
        console.error("Lỗi khi lấy thống kê:", error);
        this.showError("Không thể tải dữ liệu thống kê");
      } finally {
        this.loading.stats = false;
      }
    },

    async fetchChartData() {
      this.loading.chart = true;
      try {
        const range = this.chartPeriod === "week" ? 7 : 30;
        const response = await api.get(`/moi-gioi/thong-ke/bieu-do-bai-dang`, { params: { range } });
        this.updateChart(response.data.data);
      } catch (error) {
        console.error("Lỗi khi lấy dữ liệu biểu đồ:", error);
      } finally {
        this.loading.chart = false;
      }
    },

    // ✅ Method updateChart đã fix
    updateChart(chartData) {
      const labels = chartData.labels.map((date) => {
        const d = new Date(date);
        const days = ["CN", "T2", "T3", "T4", "T5", "T6", "T7"];
        const dayName = days[d.getDay()];
        const dayDate = d.getDate();
        const month = d.getMonth() + 1;
        return `${dayName} ${dayDate}/${month}`;
      });

      const colors = chartData.values.map((val, idx) => {
        const max = Math.max(...chartData.values);
        if (val === max && val > 0) return "#1a237e";
        if (idx === chartData.values.length - 1) return "#06b6d4";
        return "#e2e8f0";
      });

      this.chartOptions.xaxis.categories = labels;
      this.chartOptions.colors = colors;
      this.chartOptions.plotOptions.bar.distributed = false;

      this.chartSeries = [
        {
          name: "Số tin đăng",
          data: chartData.values,
        },
      ];
    },

    async fetchCustomers() {
      this.loading.customers = true;
      try {
        const response = await api.get("/moi-gioi/thong-ke/khach-hang-lien-he", { params: { limit: 10 } });
        this.customers = response.data.data || [];
      } catch (error) {
        console.error("Lỗi khi lấy danh sách khách hàng:", error);
        this.showError("Không thể tải danh sách khách hàng");
      } finally {
        this.loading.customers = false;
      }
    },

    changeChartPeriod(period) {
      this.chartPeriod = period;
      this.fetchChartData();
    },

    formatCurrency(value) {
      if (!value) return "0";
      return Number(value).toLocaleString("vi-VN");
    },

    formatRelativeTime(dateString) {
      if (!dateString) return "";
      const date = new Date(dateString);
      const now = new Date();
      const diffMs = now - date;
      const diffMins = Math.floor(diffMs / 60000);
      const diffHours = Math.floor(diffMs / 3600000);
      const diffDays = Math.floor(diffMs / 86400000);

      if (diffMins < 1) return "Vừa xong";
      if (diffMins < 60) return `${diffMins} phút trước`;
      if (diffHours < 24) return `${diffHours} giờ trước`;
      if (diffDays < 7) return `${diffDays} ngày trước`;
      return date.toLocaleDateString("vi-VN");
    },

    showError(message) {
      alert(message);
    },
  },
};
</script>