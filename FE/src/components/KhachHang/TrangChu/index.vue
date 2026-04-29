<template>
  <div
    class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen font-['Inter']"
  >
    <!-- Hero Section -->
    <section
      class="relative h-[600px] lg:h-[700px] flex items-center overflow-hidden"
    >
      <div class="absolute inset-0">
        <img
          src="https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?ixlib=rb-4.0.3&auto=format&fit=crop&w=2000&q=80"
          class="w-full h-full object-cover"
          alt="Luxury Real Estate"
        />
        <div
          class="absolute inset-0 bg-gradient-to-r from-slate-900/95 via-slate-900/80 to-transparent"
        ></div>
      </div>

      <div class="relative z-10 container mx-auto px-6 lg:px-12">
        <div class="max-w-3xl">
          <div
            class="inline-block px-4 py-2 bg-blue-600/20 backdrop-blur-sm border border-blue-400/30 rounded-full mb-6"
          >
            <span class="text-blue-300 text-sm font-semibold tracking-wide"
              >BẤT ĐỘNG SẢN CAO CẤP</span
            >
          </div>
          <h1
            class="font-['Be_Vietnam_Pro'] text-5xl lg:text-7xl font-bold text-white leading-tight mb-6"
          >
            Kiến tạo không gian<br />
            <span
              class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-cyan-300"
              >sống thượng lưu</span
            >
          </h1>
          <p class="text-lg text-gray-300 mb-10 max-w-xl leading-relaxed">
            Khám phá những bất động sản đẳng cấp nhất, được tuyển chọn kỹ lưỡng
            cho phong cách sống đặc quyền.
          </p>

          <!-- Quick Stats -->
          <div class="flex gap-8">
            <div
              v-for="(stat, index) in stats"
              :key="`stat-${stat.id}`"
              class="contents"
            >
              <div>
                <div class="text-3xl font-bold text-white uppercase">
                  {{ stat.current }}{{ stat.suffix }}
                </div>
                <div class="text-sm text-gray-400">{{ stat.label }}</div>
              </div>
              <div
                v-if="index < stats.length - 1"
                class="w-px bg-gray-600"
              ></div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Search Section -->
    <section class="relative -mt-20 z-20 px-6">
      <div class="container mx-auto max-w-5xl">
        <div class="bg-white rounded-3xl shadow-2xl p-8 border border-gray-100">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="relative">
              <label
                class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2"
                >Vị trí</label
              >
              <div class="relative">
                <span
                  class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"
                  >location_on</span
                >
                <input
                  v-model="search.location"
                  placeholder="Quận, thành phố..."
                  class="w-full bg-gray-50 border border-gray-200 rounded-xl py-3.5 pl-12 pr-4 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all"
                />
              </div>
            </div>

            <div class="relative">
              <label
                class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2"
                >Loại hình</label
              >
              <div class="relative">
                <span
                  class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"
                  >category</span
                >
                <select
                  v-model="search.type"
                  class="w-full bg-gray-50 border border-gray-200 rounded-xl py-3.5 pl-12 pr-4 text-sm appearance-none focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all cursor-pointer"
                >
                  <option value="">Tất cả</option>
                  <option
                    v-for="loai in propertyTypes"
                    :key="loai.id"
                    :value="loai.id"
                  >
                    {{ loai.ten_loai }}
                  </option>
                </select>
              </div>
            </div>

            <div class="relative">
              <label
                class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2"
                >Khoảng giá</label
              >
              <div class="relative">
                <span
                  class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"
                  >payments</span
                >
                <select
                  v-model="search.price"
                  class="w-full bg-gray-50 border border-gray-200 rounded-xl py-3.5 pl-12 pr-4 text-sm appearance-none focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all cursor-pointer"
                >
                  <option value="">Mức giá</option>
                  <option value="duoi-10">Dưới 10 tỷ</option>
                  <option value="10-30">10 - 30 tỷ</option>
                  <option value="tren-30">Trên 30 tỷ</option>
                </select>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Properties Section -->
    <section class="py-5 px-6">
      <div class="container mx-auto max-w-7xl">
        <div class="flex justify-between items-end mb-4">
          <div>
            <h2
              class="font-['Be_Vietnam_Pro'] text-4xl font-bold text-slate-800 mt-4"
            >
              Bất động sản nổi bật
            </h2>
            <p class="text-gray-500">Những cơ hội đầu tư sinh lời cao nhất</p>
          </div>
          <router-link
            to="/khach-hang/danh-sach-bat-dong-san"
            class="hidden md:flex items-center gap-2 text-blue-600 font-semibold hover:text-blue-700 transition-colors"
          >
            Xem tất cả
            <span class="material-symbols-outlined">arrow_forward</span>
          </router-link>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div v-for="i in 3" :key="`loading-${i}`" class="animate-pulse">
            <div class="bg-white rounded-2xl overflow-hidden h-[450px]">
              <div class="h-64 bg-gray-200"></div>
              <div class="p-6 space-y-4">
                <div class="h-6 bg-gray-200 rounded w-3/4"></div>
                <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                <div class="h-8 bg-gray-200 rounded w-1/3 mt-4"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Properties Grid -->
        <div
          v-else-if="properties.length > 0"
          class="grid md:grid-cols-2 lg:grid-cols-3 gap-10"
        >
          <div
            v-for="item in properties"
            :key="item.id"
            class="group block bg-white rounded-3xl overflow-hidden shadow-[0_10px_40px_rgba(0,0,0,0.04)] hover:shadow-[0_20px_60px_rgba(37,99,235,0.1)] transition-all duration-500 cursor-pointer border border-gray-100/50 hover:-translate-y-3"
            @click.prevent="viewProperty(item.id)"
          >
            <div class="relative h-80 overflow-hidden">
              <img
                :src="item.image"
                class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110"
                :alt="item.name || 'Bất động sản'"
                @error="handleImageError"
                loading="lazy"
              />
              <div
                class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-transparent to-transparent opacity-60 group-hover:opacity-90 transition-opacity duration-500 pointer-events-none"
              ></div>

              <!-- Badge Exclusive -->
              <div class="absolute top-5 left-5 pointer-events-none">
                <span
                  class="px-4 py-1.5 bg-white/90 backdrop-blur-md text-slate-900 text-[10px] font-black uppercase tracking-[0.15em] rounded-full shadow-sm"
                >
                  Exclusive
                </span>
              </div>

              <!-- ❤️ Icon Trái Tim - Nút Favorite -->
              <button
                @click.stop="toggleFavorite(item.id, $event)"
                class="absolute top-5 right-5 w-11 h-11 rounded-full bg-white/95 backdrop-blur-md shadow-lg hover:shadow-xl flex items-center justify-center transition-all duration-300 hover:scale-110 active:scale-95 group/btn"
                :class="
                  item.isFavorite
                    ? 'bg-gradient-to-br from-pink-500 to-rose-500'
                    : ''
                "
                :aria-label="
                  item.isFavorite ? 'Bỏ yêu thích' : 'Thêm vào yêu thích'
                "
              >
                <span
                  class="material-symbols-outlined text-xl transition-all duration-300"
                  :class="
                    item.isFavorite
                      ? 'text-white fill-current'
                      : 'text-gray-400 group-hover/btn:text-pink-500'
                  "
                >
                  favorite
                </span>
              </button>
            </div>

            <div class="p-8">
              <div class="flex items-center gap-2 mb-3">
                <span
                  class="text-blue-600 font-bold text-[11px] uppercase tracking-widest"
                  >{{ item.loai }}</span
                >
                <span class="w-1 h-1 bg-gray-300 rounded-full"></span>
                <span
                  class="text-gray-400 text-[11px] font-medium uppercase tracking-widest"
                >
                  {{ (item.location || "Việt Nam").split(",")[0] }}
                </span>
              </div>

              <h3
                class="font-['Be_Vietnam_Pro'] text-2xl font-bold text-slate-800 mb-6 leading-tight group-hover:text-blue-600 transition-colors line-clamp-2 h-[67px]"
              >
                {{ item.name }}
              </h3>

              <div class="flex items-baseline justify-between">
                <div>
                  <p
                    class="text-[10px] text-gray-400 uppercase tracking-[0.2em] font-bold mb-1"
                  >
                    Giá đặc quyền
                  </p>
                  <p class="text-2xl font-black text-slate-900">
                    {{ formatPriceDisplay(item.gia) }}
                  </p>
                </div>

                <div
                  class="flex items-center gap-2 text-blue-600 font-semibold text-sm group/btn"
                >
                  <span>Chi tiết</span>
                  <span
                    class="material-symbols-outlined transition-transform group-hover/btn:translate-x-1"
                    >arrow_right_alt</span
                  >
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div
          v-else-if="!loading && properties.length === 0"
          class="text-center py-20"
        >
          <div
            class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4"
          >
            <span class="material-symbols-outlined text-gray-400 text-5xl"
              >search_off</span
            >
          </div>
          <h3 class="text-xl font-bold text-gray-700 mb-2">
            Không tìm thấy bất động sản
          </h3>
          <p class="text-gray-500">
            Hãy thử điều chỉnh tiêu chí tìm kiếm của bạn
          </p>
        </div>
      </div>
    </section>

    <!-- Why Choose Us Section -->
    <section class="py-5  border-gray-100">
      <div class="container mx-auto max-w-7xl px-6">
        <div class="text-center max-w-2xl mx-auto">
          <h2
            class="font-['Be_Vietnam_Pro'] text-[32px] md:text-[38px] font-bold text-[#0a0e27] mb-3"
          >
            Tại sao chọn Architectural Curator?
          </h2>
          <p class="text-gray-500 text-[16px] leading-relaxed mb-4 text-center">
            Chúng tôi không chỉ là một mô hình bất động sản bình thường, mà là
            hệ sinh thái toàn diện dành cho giới thượng lưu.
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
          <div
            class="bg-white p-8 rounded-[24px] shadow-[0_4px_20px_rgba(0,0,0,0.03)] hover:shadow-[0_10px_40px_rgba(10,14,39,0.08)] hover:-translate-y-2 transition-all duration-500 group border border-gray-100"
          >
            <div
              class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-blue-600 transition-colors duration-500"
            >
              <span
                class="material-symbols-outlined text-[32px] text-blue-600 group-hover:text-white transition-colors duration-500"
                >verified</span
              >
            </div>
            <h3
              class="font-['Be_Vietnam_Pro'] text-[20px] font-bold text-[#0a0e27] mb-3"
            >
              Pháp lý chuẩn mực
            </h3>
            <p class="text-gray-500 text-[14px] leading-relaxed">
              100% dự án được kiểm định pháp lý bởi đội ngũ luật sư chuyên
              nghiệp, đảm bảo an toàn tuyệt đối.
            </p>
          </div>

          <div
            class="bg-white p-8 rounded-[24px] shadow-[0_4px_20px_rgba(0,0,0,0.03)] hover:shadow-[0_10px_40px_rgba(10,14,39,0.08)] hover:-translate-y-2 transition-all duration-500 group border border-gray-100"
          >
            <div
              class="w-16 h-16 bg-purple-50 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-purple-600 transition-colors duration-500"
            >
              <span
                class="material-symbols-outlined text-[32px] text-purple-600 group-hover:text-white transition-colors duration-500"
                >diamond</span
              >
            </div>
            <h3
              class="font-['Be_Vietnam_Pro'] text-[20px] font-bold text-[#0a0e27] mb-3"
            >
              Sản phẩm độc quyền
            </h3>
            <p class="text-gray-500 text-[14px] leading-relaxed">
              Tuyển chọn kỹ lưỡng những BĐS hạng sang tại các vị trí kim cương
              của thành phố.
            </p>
          </div>

          <div
            class="bg-white p-8 rounded-[24px] shadow-[0_4px_20px_rgba(0,0,0,0.03)] hover:shadow-[0_10px_40px_rgba(10,14,39,0.08)] hover:-translate-y-2 transition-all duration-500 group border border-gray-100"
          >
            <div
              class="w-16 h-16 bg-cyan-50 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-cyan-600 transition-colors duration-500"
            >
              <span
                class="material-symbols-outlined text-[32px] text-cyan-600 group-hover:text-white transition-colors duration-500"
                >support_agent</span
              >
            </div>
            <h3
              class="font-['Be_Vietnam_Pro'] text-[20px] font-bold text-[#0a0e27] mb-3"
            >
              Tư vấn tận tâm
            </h3>
            <p class="text-gray-500 text-[14px] leading-relaxed">
              Đội ngũ chuyên viên am hiểu thị trường, hỗ trợ 24/7 với thái độ
              phục vụ đẳng cấp 5 sao.
            </p>
          </div>

          <div
            class="bg-white p-8 rounded-[24px] shadow-[0_4px_20px_rgba(0,0,0,0.03)] hover:shadow-[0_10px_40px_rgba(10,14,39,0.08)] hover:-translate-y-2 transition-all duration-500 group border border-gray-100"
          >
            <div
              class="w-16 h-16 bg-orange-50 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-orange-600 transition-colors duration-500"
            >
              <span
                class="material-symbols-outlined text-[32px] text-orange-600 group-hover:text-white transition-colors duration-500"
                >chart_data</span
              >
            </div>
            <h3
              class="font-['Be_Vietnam_Pro'] text-[20px] font-bold text-[#0a0e27] mb-3"
            >
              Tiềm năng sinh lời
            </h3>
            <p class="text-gray-500 text-[14px] leading-relaxed">
              Phân tích dữ liệu thị trường chuyên sâu, tối ưu hóa lợi nhuận đầu
              tư cho khách hàng.
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5">
      <div class="container mx-auto max-w-7xl px-6">
        <div
          class="relative overflow-hidden rounded-[32px] bg-gradient-to-br from-[#0a0e27] via-[#0d1542] to-[#0a0e27] px-8 md:px-16 py-12 md:py-16"
        >
          <div
            class="absolute top-0 right-0 w-96 h-96 bg-blue-600/20 rounded-full blur-3xl pointer-events-none"
          ></div>
          <div
            class="absolute bottom-0 left-0 w-80 h-80 bg-cyan-500/10 rounded-full blur-3xl pointer-events-none"
          ></div>

          <div class="relative z-10 grid lg:grid-cols-2 gap-10 items-center">
            <div>
              <h2
                class="font-['Be_Vietnam_Pro'] text-[32px] md:text-[40px] font-bold text-white leading-tight mb-5"
              >
                Trở thành đối tác<br />
                <span class="text-blue-400">môi giới chuyên nghiệp</span>
              </h2>

              <p
                class="text-[#94a3b8] text-[15px] md:text-[16px] leading-relaxed max-w-xl mb-8"
              >
                Gia nhập đội ngũ ArchiEstate để tiếp cận nguồn khách hàng cao
                cấp và hệ thống quản lý bất động sản hiện đại nhất.
              </p>

              <button
                style="border-radius: 20px"
                class="bg-white hover:bg-gray-100 text-[#0a0e27] px-8 py-4 rounded-full font-['Be_Vietnam_Pro'] font-bold text-[14px] tracking-wide shadow-lg hover:shadow-xl hover:-translate-y-0.5 active:scale-[0.98] transition-all duration-300 flex items-center gap-2.5 group"
              >
                <span>Đăng ký ngay</span>
                <span
                  class="material-symbols-outlined text-[18px] group-hover:translate-x-1 transition-transform duration-300"
                >
                  arrow_forward
                </span>
              </button>
            </div>

            <div class="flex lg:justify-end">
              <div
                class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-[24px] px-8 py-7 max-w-sm w-full"
              >
                <div class="flex items-start gap-4 mb-6">
                  <div
                    class="w-12 h-12 rounded-xl bg-blue-500/20 flex items-center justify-center flex-shrink-0"
                  >
                    <span
                      class="material-symbols-outlined text-blue-400 text-[24px]"
                      >trending_up</span
                    >
                  </div>
                  <div>
                    <div class="text-white font-bold text-[18px] mb-0.5">
                      Tăng trưởng 45%
                    </div>
                    <div class="text-[#64748b] text-[13px] font-medium">
                      Doanh thu trung bình đối tác
                    </div>
                  </div>
                </div>

                <div class="flex items-start gap-4">
                  <div
                    class="w-12 h-12 rounded-xl bg-cyan-400/20 flex items-center justify-center flex-shrink-0"
                  >
                    <span
                      class="material-symbols-outlined text-cyan-400 text-[24px]"
                      >groups</span
                    >
                  </div>
                  <div>
                    <div class="text-white font-bold text-[18px] mb-0.5">
                      1,200+ Môi giới
                    </div>
                    <div class="text-[#64748b] text-[13px] font-medium">
                      Đang hoạt động trên toàn quốc
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- 🍞 Enhanced Vue Toast Notification -->
    <transition name="toast-slide">
      <div
        v-if="toast.visible"
        class="fixed top-5 right-5 z-[9999] pointer-events-auto"
      >
        <div
          class="flex items-center gap-3 px-5 py-3.5 text-white rounded-2xl shadow-2xl backdrop-blur-md border border-white/20 min-w-[280px] max-w-sm"
          :class="getToastClass(toast.type)"
        >
          <!-- Icon với animation pulse khi add favorite -->
          <span
            class="material-symbols-outlined text-xl flex-shrink-0"
            :class="{ 'animate-heart-pulse': toast.type === 'favorite-add' }"
          >
            {{ toast.icon || getToastIcon(toast.type) }}
          </span>

          <!-- Message -->
          <span class="font-medium text-sm flex-1 leading-tight">
            {{ toast.message }}
          </span>

          <!-- Close button -->
          <button
            @click="hideToast"
            class="ml-2 hover:opacity-80 transition-opacity p-1 rounded-full hover:bg-white/20"
            aria-label="Đóng thông báo"
          >
            <span class="material-symbols-outlined text-lg">close</span>
          </button>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
import api from "@/axios/config";

export default {
  name: "HomePage",

  data() {
    return {
      loading: false,
      propertyTypes: [],
      search: { location: "", type: "", price: "" },
      properties: [],
      stats: [
        { id: 1, label: "Dự án", value: 5000, suffix: "+", current: 0 },
        { id: 2, label: "Khách hàng", value: 10, suffix: "K+", current: 0 },
        { id: 3, label: "Năm kinh nghiệm", value: 15, suffix: "+", current: 0 },
      ],
      defaultImage:
        "https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800",
      searchTimer: null,

      // ✅ Vue Reactive Toast System
      toast: {
        visible: false,
        message: "",
        type: "warning",
        icon: null,
        timer: null,
      },
    };
  },

  watch: {
    search: {
      handler() {
        if (this.searchTimer) clearTimeout(this.searchTimer);
        this.searchTimer = setTimeout(() => this.handleSearch(), 600);
      },
      deep: true,
    },
  },

  mounted() {
    this.loadProperties();
    this.loadPropertyTypes();
    this.animateStats();
  },

  beforeUnmount() {
    // ✅ Cleanup toast timer khi unmount
    if (this.toast.timer) {
      clearTimeout(this.toast.timer);
      this.toast.timer = null;
    }
    if (this.searchTimer) clearTimeout(this.searchTimer);
  },

  methods: {
    // 🍞 Toast helpers - Enhanced với favorite-add/remove
    getToastClass(type) {
      const classes = {
        success: "bg-gradient-to-r from-emerald-500 to-teal-500",
        error: "bg-gradient-to-r from-red-500 to-rose-500",
        warning: "bg-gradient-to-r from-amber-500 to-orange-500",
        "favorite-add":
          "bg-gradient-to-r from-pink-500 via-rose-500 to-red-500 shadow-pink-500/30",
        "favorite-remove":
          "bg-gradient-to-r from-gray-500 via-slate-500 to-gray-600 shadow-gray-500/30",
      };
      return classes[type] || classes.warning;
    },

    getToastIcon(type) {
      const icons = {
        success: "check_circle",
        error: "error",
        warning: "warning",
        "favorite-add": "favorite",
        "favorite-remove": "heart_broken",
      };
      return icons[type] || icons.warning;
    },

    // ✅ Toast cơ bản cho các thông báo chung
    showToast(msg, type = "warning", duration = 2500) {
      if (this.toast.timer) clearTimeout(this.toast.timer);
      this.toast = {
        visible: true,
        message: msg,
        type,
        icon: null,
        timer: setTimeout(() => this.hideToast(), duration),
      };
    },

    // ❤️ Toast chuyên biệt cho favorite - Phân biệt THÊM vs XÓA
    showFavoriteToast(action, propertyName) {
      const shortName =
        propertyName.length > 30
          ? propertyName.substring(0, 27) + "..."
          : propertyName;

      if (this.toast.timer) clearTimeout(this.toast.timer);

      if (action === "add") {
        this.toast = {
          visible: true,
          message: `❤️ Đã lưu "${shortName}" vào yêu thích`,
          type: "favorite-add",
          icon: "favorite",
          timer: setTimeout(() => this.hideToast(), 3000),
        };
      } else {
        this.toast = {
          visible: true,
          message: `💔 Đã xóa "${shortName}" khỏi yêu thích`,
          type: "favorite-remove",
          icon: "heart_broken",
          timer: setTimeout(() => this.hideToast(), 2500),
        };
      }
    },

    hideToast() {
      this.toast.visible = false;
      if (this.toast.timer) {
        clearTimeout(this.toast.timer);
        this.toast.timer = null;
      }
    },

    async loadPropertyTypes() {
      try {
        const res = await api.get("/client/loai-bat-dong-san");

        if (Array.isArray(res.data)) {
          this.propertyTypes = res.data;
        } else if (res.data?.data) {
          this.propertyTypes = res.data.data;
        } else {
          this.propertyTypes = [];
        }
      } catch (error) {
        console.error("Lỗi loại BĐS:", error);
        this.propertyTypes = [];
      }
    },

    getImageUrl(url) {
      if (!url) return this.defaultImage;
      if (url.startsWith("http")) return url;
      const base = import.meta.env.VITE_API_URL?.replace('/api','') || 'http://localhost:8000';
      return `${base}/storage/${url}`;
    },

    async loadProperties() {
      this.loading = true;
      try {
        const res = await api.get("/client/bat-dong-san");
        let raw = [];
        if (Array.isArray(res.data)) {
          raw = res.data;
        } else if (res.data?.data?.data) {
          raw = res.data.data.data;
        } else if (res.data?.data) {
          raw = res.data.data;
        }
        this.properties = this.mapProperties(raw);
      } catch (error) {
        console.error("Load lỗi:", error);
        this.properties = [];
      } finally {
        this.loading = false;
      }
    },

    mapProperties(rawData) {
      return rawData.map((item) => {
        let imageUrl = this.defaultImage;

        const isValidUrl = (u) => {
          return (
            typeof u === "string" &&
            u.trim() !== "" &&
            u !== "null" &&
            u !== "undefined"
          );
        };

        // 🔥 ƯU TIÊN hinh_anh trước (FIX LẶP)
        if (Array.isArray(item.hinhAnh)) {
          const img = item.hinhAnh.find((i) => i && isValidUrl(i.url));
          if (img) {
            imageUrl = this.getImageUrl(img.url.trim());
          }
        }

        // 🔥 fallback mới dùng anh_dai_dien
        else if (item.anh_dai_dien && isValidUrl(item.anh_dai_dien.url)) {
          imageUrl = this.getImageUrl(item.anh_dai_dien.url.trim());
        }

        let location = "Đang cập nhật";
        if (item.dia_chi) {
          if (item.dia_chi.ten_quan && item.dia_chi.ten_tinh) {
            location = `${item.dia_chi.ten_quan}, ${item.dia_chi.ten_tinh}`;
          } else if (item.dia_chi.ten_tinh) {
            location = item.dia_chi.ten_tinh;
          }
        }

        return {
          id: item.id,
          name: item.tieu_de || "Bất động sản",
          location,
          loai: item.loai?.ten_loai || "BĐS",
          gia: item.gia_display || item.gia,
          image: imageUrl,
          isFavorite: item.is_favorite || false,
        };
      });
    },

    async handleSearch() {
      this.loading = true;
      try {
        let gia_min = null,
          gia_max = null;
        switch (this.search.price) {
          case "duoi-10":
            gia_max = 10000000000;
            break;
          case "10-30":
            gia_min = 10000000000;
            gia_max = 30000000000;
            break;
          case "tren-30":
            gia_min = 30000000000;
            break;
        }
        const payload = {
          tieu_de: this.search.location || "",
          loai_id: this.search.type || "",
          gia_min,
          gia_max,
        };
        const res = await api.post("/client/tim-kiem", payload);
        if (res.data?.status) {
          const raw = res.data.data?.data || [];
          this.properties = this.mapProperties(raw);
        } else {
          this.properties = [];
        }
      } catch (error) {
        console.error("Search lỗi:", error);
        this.properties = [];
      } finally {
        this.loading = false;
      }
    },

    viewProperty(id) {
      const token = localStorage.getItem("auth_token");
      const userType = localStorage.getItem("user_type");
      if (!token || userType !== "khach-hang") {
        this.showToast("Vui lòng đăng nhập để xem chi tiết", "warning");
        setTimeout(() => this.$router.push("/khach-hang/dang-nhap"), 800);
        return;
      }
      this.$router.push(`/khach-hang/chi-tiet-bat-dong-san/${id}`);
    },

    // ❤️ Toggle Favorite - ✅ VỚI TOAST ĐẸP & MƯỢT
    async toggleFavorite(id, ev) {
      ev.stopPropagation();

      const token = localStorage.getItem("auth_token");
      if (!token) {
        this.showToast("Vui lòng đăng nhập để lưu tin", "warning");
        setTimeout(() => this.$router.push("/khach-hang/dang-nhap"), 800);
        return;
      }

      // ✅ Tìm property để lấy tên và trạng thái cũ
      const property = this.properties.find((p) => p.id === id);
      const wasFavorite = property?.isFavorite || false;
      const action = wasFavorite ? "remove" : "add";
      const propertyName = property?.name || "Bất động sản";

      // ✅ Optimistic UI: Cập nhật ngay để cảm giác nhanh
      if (property) property.isFavorite = !wasFavorite;

      try {
        // ✅ GỌI API HIỆN TẠI
        await api.post("/khach-hang/bds/yeu-thich", { bds_id: id });

        const res = await api.get("/khach-hang/bds/yeu-thich/data");

        // Lấy danh sách id đã thích
        const favoriteIds = res.data.data.map(
          (item) => item.bat_dong_san_id || item.bds_id || item.batDongSan?.id
        );

        // Update lại toàn bộ properties
        this.properties = this.properties.map((p) => ({
          ...p,
          isFavorite: favoriteIds.includes(p.id),
        }));

        // ✅ Hiển thị toast CHUYÊN BIỆT cho THÊM/XÓA
        this.showFavoriteToast(action, propertyName);

        // Sync header
        window.dispatchEvent(new Event("favorite-updated"));
      } catch (err) {
        console.error("API lỗi:", err.response?.data || err);

        // ✅ Rollback UI nếu API fail
        if (property) property.isFavorite = wasFavorite;

        this.showToast("Có lỗi xảy ra, vui lòng thử lại", "error");
      }
    },

    formatPriceDisplay(gia) {
      if (!gia) return "Liên hệ";
      if (gia >= 1_000_000_000) return Math.floor(gia / 1_000_000_000) + " Tỷ";
      if (gia >= 1_000_000) return Math.floor(gia / 1_000_000) + " Triệu";
      return "Liên hệ";
    },

    animateStats() {
      this.stats.forEach((stat) => {
        let current = 0;
        const step = stat.value / 60;
        const interval = setInterval(() => {
          current += step;
          if (current >= stat.value) {
            stat.current = stat.value;
            clearInterval(interval);
          } else {
            stat.current = Math.floor(current);
          }
        }, 30);
      });
    },
  },
};
</script>

<style scoped>
.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  line-clamp: 1;
  overflow: hidden;
  word-break: break-all;
}

@keyframes float {
  0%,
  100% {
    transform: translateY(0px);
  }
  50% {
    transform: translateY(-20px);
  }
}
.bg-blue-500\/10,
.bg-cyan-500\/10 {
  animation: float 6s ease-in-out infinite;
}

/* 🍞 Enhanced Toast Animation - Mượt với cubic-bezier */
.toast-slide-enter-active {
  transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.toast-slide-leave-active {
  transition: all 0.2s ease-in;
}
.toast-slide-enter-from {
  opacity: 0;
  transform: translateX(120%) scale(0.9);
}
.toast-slide-leave-to {
  opacity: 0;
  transform: translateX(120%) scale(0.95);
}

/* 💖 Heart pulse animation cho icon khi add favorite */
@keyframes heartPulse {
  0%,
  100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.25);
  }
}
.animate-heart-pulse {
  animation: heartPulse 0.6s ease-in-out;
}

/* 🌟 Glow effect cho toast favorite-add */
.bg-gradient-to-r.from-pink-500 {
  box-shadow: 0 8px 32px rgba(236, 72, 153, 0.4);
}

/* Reduce motion cho người dùng nhạy cảm */
@media (prefers-reduced-motion: reduce) {
  *,
  *::before,
  *::after {
    animation-duration: 0.01ms !important;
    transition-duration: 0.01ms !important;
  }
  .animate-heart-pulse {
    animation: none !important;
  }
}
</style>