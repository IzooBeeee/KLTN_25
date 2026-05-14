<template>
  <div class="bg-slate-50 min-h-screen font-sans selection:bg-blue-100 selection:text-blue-900 overflow-x-hidden">

    <!-- 1. HERO SECTION -->
    <section class="relative h-[85vh] min-h-[600px]  flex items-center justify-center overflow-hidden">
      <!-- Background Image -->
      <div class="absolute inset-0 z-0">
        <img
          src="https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?ixlib=rb-4.0.3&auto=format&fit=crop&w=2500&q=80"
          class="w-full h-full object-cover animate-hero-scale" alt="Bất động sản cao cấp" />
        <!-- Elegant dark overlay -->
        <div class="absolute inset-0 bg-gradient-to-b from-slate-900/80 via-slate-900/60 to-slate-900/90"></div>
      </div>

      <!-- Hero Content -->
      <div class="relative z-10 container mx-auto px-6 lg:px-12 text-center reveal-item">
        <span
          class="inline-block px-4 py-1.5 bg-white/10 backdrop-blur-sm text-white text-xs font-bold uppercase tracking-widest rounded-full mb-6 border border-white/20">
          Nền tảng giao dịch BĐS uy tín
        </span>
        <h1 class="text-4xl md:text-6xl lg:text-7xl font-bold text-white leading-tight mb-6 tracking-tight">
          Tìm Kiếm Tổ Ấm <br class="hidden md:block" />
          <span class="text-blue-400">Hoàn Hảo Của Bạn</span>
        </h1>
        <p class="text-lg md:text-xl text-slate-300 max-w-2xl mx-auto font-light leading-relaxed mb-12">
          Khám phá hàng ngàn bất động sản xác thực với thông tin minh bạch, định giá chính xác và hỗ trợ chuyên nghiệp
          24/7.
        </p>
      </div>
    </section>

    <!-- 2. SEARCH BAR -->
    <section class="relative z-20 -mt-20 px-6 reveal-item delay-100">
      <div class="container mx-auto max-w-5xl">
        <div class="bg-white rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.08)] border border-slate-100 overflow-hidden">
          <div class="flex flex-col md:flex-row items-stretch divide-y md:divide-y-0 md:divide-x divide-slate-100">

            <!-- Vị trí -->
            <div
              class="w-full flex-1 px-6 py-4 flex flex-col justify-center group hover:bg-slate-50/50 transition-colors">
              <label
                class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2 group-hover:text-blue-500 transition-colors">Khu
                vực</label>
              <div class="flex items-center">
                <span
                  class="material-symbols-outlined text-slate-400 mr-3 text-xl group-focus-within:text-blue-500">location_on</span>
                <input v-model="search.location" placeholder="Nhập quận, thành phố..."
                  class="w-full bg-transparent border-0 border-transparent shadow-none focus:ring-0 text-slate-700 text-base font-medium focus:outline-none placeholder:text-slate-300 placeholder:font-normal p-0" />
              </div>
            </div>

            <!-- Loại hình -->
            <div
              class="w-full flex-1 px-6 py-4 flex flex-col justify-center group hover:bg-slate-50/50 transition-colors">
              <label
                class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2 group-hover:text-blue-500 transition-colors">Loại
                hình</label>
              <div class="flex items-center relative">
                <span class="material-symbols-outlined text-slate-400 mr-3 text-xl">real_estate_agent</span>
                <select v-model="search.type"
                  class="w-full bg-transparent border-0 border-transparent shadow-none focus:ring-0 p-0 text-slate-700 text-base font-medium appearance-none focus:outline-none cursor-pointer">
                  <option value="">Tất cả loại hình</option>
                  <option v-for="loai in propertyTypes" :key="loai.id" :value="loai.id">{{ loai.ten_loai }}</option>
                </select>
                <span
                  class="material-symbols-outlined absolute right-0 text-slate-400 pointer-events-none text-xl">expand_more</span>
              </div>
            </div>

            <!-- Mức giá -->
            <div
              class="w-full flex-1 px-6 py-4 flex flex-col justify-center group hover:bg-slate-50/50 transition-colors">
              <label
                class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2 group-hover:text-blue-500 transition-colors">Mức
                giá</label>
              <div class="flex items-center relative">
                <span class="material-symbols-outlined text-slate-400 mr-3 text-xl">payments</span>
                <select v-model="search.price"
                  class="w-full bg-transparent border-0 border-transparent shadow-none focus:ring-0 p-0 text-slate-700 text-base font-medium appearance-none focus:outline-none cursor-pointer">
                  <option value="">Mọi mức giá</option>
                  <option value="duoi-10">Dưới 10 tỷ</option>
                  <option value="10-30">10 - 30 tỷ</option>
                  <option value="tren-30">Trên 30 tỷ</option>
                </select>
                <span
                  class="material-symbols-outlined absolute right-0 text-slate-400 pointer-events-none text-xl">expand_more</span>
              </div>
            </div>

            <!-- Submit -->
            <div class="w-full md:w-auto p-2 flex items-stretch">
              <button @click="handleSearch" style="background-color: #191d41; color: white; border-radius: 0.75rem;"
                class="w-full md:w-auto px-10 hover:opacity-90 font-bold flex items-center justify-center gap-2 transition-all shadow-md active:scale-[0.98]">
                <span class="material-symbols-outlined text-xl">search</span>
                <span>Tìm kiếm</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- 3. PROPERTIES SECTION -->
    <section id="properties-section" class="py-24 px-6">
      <div class="container mx-auto max-w-7xl">
        <div class="flex flex-col md:flex-row justify-between items-end mb-12 gap-6 reveal-item">
          <div>
            <h2 class="text-3xl md:text-4xl font-bold text-slate-900 mb-3">
              {{ isSearchMode ? 'Kết quả Tìm kiếm' : 'Dự án Nổi bật' }}
            </h2>
            <p class="text-slate-500 text-base">
              {{ isSearchMode ? `Tìm thấy ${totalResults}
              bất động sản phù hợp với yêu cầu của bạn.` : 'Những bất động sản được đánh giá cao nhất trong tuần.' }}
            </p>
          </div>
          <router-link to="/khach-hang/danh-sach-bat-dong-san"
            class="text-blue-600 font-semibold hover:text-blue-700 flex items-center gap-1 group transition-colors">
            Xem tất cả dự án
            <span
              class="material-symbols-outlined text-xl group-hover:translate-x-1 transition-transform">arrow_right_alt</span>
          </router-link>
        </div>

        <!-- Loading Skeleton -->
        <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div v-for="i in 3" :key="`loading-${i}`"
            class="animate-pulse bg-white border border-slate-100 rounded-2xl overflow-hidden shadow-sm">
            <div class="aspect-[4/3] bg-slate-200"></div>
            <div class="p-6">
              <div class="h-4 bg-slate-200 rounded w-1/4 mb-4"></div>
              <div class="h-6 bg-slate-200 rounded w-3/4 mb-6"></div>
              <div class="h-5 bg-slate-200 rounded w-1/2"></div>
            </div>
          </div>
        </div>

        <!-- Properties Grid -->
        <div v-else-if="properties.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div v-for="(item, index) in properties" :key="item.id"
            class="property-showcase-card reveal-item group bg-white border border-slate-100/70 rounded-[28px] overflow-hidden cursor-pointer"
            :style="`transition-delay: ${index * 100}ms`" @click.prevent="viewProperty(item.id)">
            <!-- Image Area -->
            <div class="property-showcase-media relative aspect-[4/3] overflow-hidden bg-slate-100">
              <img :src="item.image" class="property-showcase-img w-full h-full object-cover" :alt="item.name"
                @error="handleImageError" loading="lazy" />
              <div class="property-showcase-overlay absolute inset-0"></div>

              <!-- Badges -->
              <div class="property-badge-row absolute top-4 left-4 flex gap-3">
                <span
                  class="property-badge property-badge--type px-3 py-1 backdrop-blur text-slate-800 text-[11px] font-bold uppercase tracking-wider">
                  {{ item.loai }}
                </span>
                <span v-if="item.isExclusive"
                  class="property-badge property-badge--hot px-3 py-1 text-white text-[11px] font-bold uppercase tracking-wider">
                  Hot
                </span>
              </div>

              <!-- Heart Button -->
              <button @click.stop="toggleFavorite(item.id, $event)"
                class="favorite-float-btn group/btn absolute top-4 right-4 w-11 h-11 bg-white/95 backdrop-blur-md rounded-full flex items-center justify-center active:scale-95">
                <span class="material-symbols-outlined text-[20px] transition-colors duration-300"
                  :style="{ fontVariationSettings: item.isFavorite ? `'FILL' 1` : `'FILL' 0` }"
                  :class="item.isFavorite ? 'text-rose-500' : 'text-slate-400 group-hover/btn:text-rose-400'">
                  favorite
                </span>
              </button>

              <!-- Image Footer overlay (Price) -->
              <div class="absolute bottom-4 left-4 right-4 flex justify-between items-end">
                <span class="text-white font-bold text-2xl tracking-tight drop-shadow-md">
                  {{ formatPriceDisplay(item.gia) }}
                </span>
              </div>
            </div>

            <!-- Content Area -->
            <div class="property-showcase-body p-6">
              <div class="property-location-line flex items-center gap-1.5 text-slate-400 mb-2">
                <span class="material-symbols-outlined text-[16px]">location_on</span>
                <span class="text-sm font-medium">{{ item.location }}</span>
              </div>
              <h3
                class="property-showcase-title text-lg font-bold text-slate-800 line-clamp-2 leading-snug min-h-[56px] mb-2">
                {{ item.name }}
              </h3>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else-if="!loading && properties.length === 0" class="py-24 text-center reveal-item">
          <div class="w-20 h-20 mx-auto bg-slate-50 rounded-full flex items-center justify-center mb-6 text-slate-300">
            <span class="material-symbols-outlined text-4xl">search_off</span>
          </div>
          <h3 class="text-xl font-bold text-slate-700 mb-2">Không tìm thấy bất động sản</h3>
          <p class="text-slate-500">Xin vui lòng thay đổi bộ lọc tìm kiếm của bạn.</p>
        </div>
      </div>
    </section>

    <!-- 4. FEATURES SECTION -->
    <section class="py-24 px-6 bg-white border-y border-slate-100">
      <div class="container mx-auto max-w-7xl">
        <div class="text-center max-w-2xl mx-auto mb-16 reveal-item">
          <h2 class="text-3xl md:text-4xl font-bold text-slate-900 mb-4">Tại sao chọn ArchiEstate?</h2>
          <p class="text-slate-500 text-lg">Chúng tôi cung cấp dịch vụ chuyên nghiệp, minh bạch và an toàn nhất trên thị
            trường bất động sản.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
          <div class="reveal-item text-center group" style="transition-delay: 100ms">
            <div
              class="w-16 h-16 mx-auto bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-blue-600 group-hover:text-white transition-colors duration-300">
              <span class="material-symbols-outlined text-3xl">shield_locked</span>
            </div>
            <h3 class="text-lg font-bold text-slate-900 mb-3">Pháp lý an toàn</h3>
            <p class="text-slate-500 text-sm leading-relaxed px-4">100% bất động sản được kiểm tra sổ đỏ và tình trạng
              quy hoạch minh bạch.</p>
          </div>

          <div class="reveal-item text-center group" style="transition-delay: 200ms">
            <div
              class="w-16 h-16 mx-auto bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-300">
              <span class="material-symbols-outlined text-3xl">verified_user</span>
            </div>
            <h3 class="text-lg font-bold text-slate-900 mb-3">Định giá chính xác</h3>
            <p class="text-slate-500 text-sm leading-relaxed px-4">Sử dụng dữ liệu thị trường thực tế để đưa ra mức giá
              tư vấn tốt nhất.</p>
          </div>

          <div class="reveal-item text-center group" style="transition-delay: 300ms">
            <div
              class="w-16 h-16 mx-auto bg-cyan-50 text-cyan-600 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-cyan-600 group-hover:text-white transition-colors duration-300">
              <span class="material-symbols-outlined text-3xl">support_agent</span>
            </div>
            <h3 class="text-lg font-bold text-slate-900 mb-3">Hỗ trợ tận tâm</h3>
            <p class="text-slate-500 text-sm leading-relaxed px-4">Đội ngũ chuyên viên túc trực 24/7 để giải đáp mọi quy
              trình mua bán.</p>
          </div>

          <div class="reveal-item text-center group" style="transition-delay: 400ms">
            <div
              class="w-16 h-16 mx-auto bg-teal-50 text-teal-600 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-teal-600 group-hover:text-white transition-colors duration-300">
              <span class="material-symbols-outlined text-3xl">handshake</span>
            </div>
            <h3 class="text-lg font-bold text-slate-900 mb-3">Giao dịch nhanh gọn</h3>
            <p class="text-slate-500 text-sm leading-relaxed px-4">Thủ tục giấy tờ được tối ưu hóa, tiết kiệm tối đa
              thời gian của khách hàng.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- 5. RECENTLY VIEWED SECTION -->
    <section v-if="recentlyViewed.length > 0" class="py-16 px-6 bg-slate-50">
      <div class="container mx-auto max-w-7xl">
        <div class="flex justify-between items-end mb-8 reveal-item">
          <div>
            <div class="flex items-center gap-2 mb-1">
              <span class="material-symbols-outlined text-blue-500 text-xl">history</span>
              <span class="text-xs font-bold text-blue-500 uppercase tracking-widest">Lịch sử duyệt</span>
            </div>
            <h2 class="text-2xl md:text-3xl font-bold text-slate-900">Bạn đã xem gần đây</h2>
          </div>
          <button @click="clearRecent"
            class="text-sm text-slate-400 hover:text-rose-500 transition-colors flex items-center gap-1">
            <span class="material-symbols-outlined text-base">delete_sweep</span>
            Xóa lịch sử
          </button>
        </div>

        <div class="flex gap-5 overflow-x-auto pb-3 no-scrollbar snap-x snap-mandatory">
          <div v-for="item in recentlyViewed" :key="item.id"
            class="snap-start flex-shrink-0 w-64 bg-white rounded-2xl overflow-hidden border border-slate-100 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-300 cursor-pointer group"
            @click="viewProperty(item.id)">
            <!-- Image -->
            <div class="relative h-40 bg-slate-100 overflow-hidden">
              <img :src="item.image || defaultImage"
                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                :alt="item.name" @error="handleImageError" loading="lazy" />
              <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent"></div>
              <span
                class="absolute top-3 left-3 px-2 py-0.5 bg-white/90 backdrop-blur text-[10px] font-bold text-slate-700 uppercase rounded-md">
                {{ item.loai }}
              </span>
              <span class="absolute bottom-3 left-3 text-white font-bold text-base drop-shadow">
                {{ formatPriceDisplay(item.gia) }}
              </span>
            </div>

            <!-- Info -->
            <div class="p-4">
              <p class="text-sm font-semibold text-slate-800 line-clamp-2 leading-snug mb-2 min-h-[40px]">{{ item.name
              }}</p>
              <div class="flex items-center gap-1 text-slate-400">
                <span class="material-symbols-outlined text-[13px]">location_on</span>
                <span class="text-xs truncate">{{ item.location }}</span>
              </div>
              <div class="mt-2 flex items-center gap-1 text-[10px] text-slate-300">
                <span class="material-symbols-outlined text-[11px]">schedule</span>
                <span>{{ formatViewedAt(item.viewed_at) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- 6. CTA SECTION -->
    <section class="py-24 px-6">
      <div class="container mx-auto max-w-6xl reveal-item">
        <div
          class="relative bg-[#0a0e27] rounded-3xl p-12 lg:p-20 overflow-hidden shadow-2xl shadow-[#4682B4]/20 text-center">
          <!-- Geometric background pattern -->
          <div class="absolute inset-0 opacity-10">
            <div
              class="absolute top-0 right-0 w-96 h-96 bg-white rounded-full mix-blend-overlay blur-3xl translate-x-1/2 -translate-y-1/2">
            </div>
            <div
              class="absolute bottom-0 left-0 w-96 h-96 bg-white rounded-full mix-blend-overlay blur-3xl -translate-x-1/2 translate-y-1/2">
            </div>
          </div>

          <div class="relative z-10 max-w-3xl mx-auto">
            <h2 class="text-4xl md:text-5xl font-bold text-white mb-6 leading-tight">Trở Thành Đối Tác Chuyên Nghiệp
            </h2>
            <p class="text-blue-100 text-lg md:text-xl mb-10 font-medium">
              Đăng ký để tiếp cận hàng triệu khách hàng tiềm năng và sử dụng công cụ quản lý bất động sản hàng đầu.
            </p>
            <router-link to="/khach-hang/nang-cap-moi-gioi"
              class="bg-white text-blue-600 hover:bg-slate-50 px-12 py-5 rounded-xl font-bold text-lg transition-colors shadow-lg active:scale-95 inline-block cursor-pointer relative z-20">
              Đăng Ký Ngay
            </router-link>
          </div>
        </div>
      </div>
    </section>

    <!-- 6. TOAST NOTIFICATION -->
    <transition name="fade-toast">
      <div v-if="toast.visible" class="home-toast-shell fixed top-6 right-6 z-[10000] pointer-events-auto">
        <div class="home-toast flex items-center gap-3 bg-slate-900/95 backdrop-blur text-white shadow-2xl">
          <span class="home-toast__icon material-symbols-outlined"
            :class="toast.type === 'favorite-add' ? 'text-rose-400 font-variation-settings:\'FILL\' 1' : 'text-blue-300'">
            {{ toast.icon || 'info' }}
          </span>
          <span class="home-toast__message font-semibold">{{ toast.message }}</span>
          <button @click="hideToast" class="home-toast__close" aria-label="Đóng thông báo">
            <span class="material-symbols-outlined text-[18px]">close</span>
          </button>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
import api from "@/axios/config";
import { getToken } from "@/js/auth";
import { getRecentlyViewed, clearRecentlyViewed } from "@/js/recentlyViewed";

export default {
  name: "HomePage",

  data() {
    return {
      loading: false,
      propertyTypes: [],
      search: { location: "", type: "", price: "" },
      properties: [],
      favoriteIds: [],
      isSearchMode: false,
      totalResults: 0,
      defaultImage: "https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800",
      searchTimer: null,
      toast: {
        visible: false,
        message: "",
        type: "warning",
        icon: null,
        timer: null,
      },
      observer: null,
      recentlyViewed: [],
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
    this.recentlyViewed = getRecentlyViewed();
    this.loadProperties().then(() => {
      if (getToken("khach-hang")) {
        this.syncFavoriteList();
      }
      this.$nextTick(() => this.initScrollAnimations());
    });
    this.loadPropertyTypes();
    window.addEventListener("favorite-updated", this.handleFavoriteUpdated);
  },

  beforeUnmount() {
    if (this.toast.timer) clearTimeout(this.toast.timer);
    if (this.searchTimer) clearTimeout(this.searchTimer);
    if (this.observer) this.observer.disconnect();
    window.removeEventListener("favorite-updated", this.handleFavoriteUpdated);
  },

  methods: {
    async syncFavoriteList() {
      try {
        if (!getToken("khach-hang")) return;
        const res = await api.get("/khach-hang/bds/yeu-thich/data");
        const favorites = res.data?.data || [];
        this.favoriteIds = favorites.map(item => item.bat_dong_san_id || item.bds_id || item.batDongSan?.id).filter(id => id);

        this.properties = this.properties.map(p => ({
          ...p,
          isFavorite: this.favoriteIds.map(Number).includes(Number(p.id))
        }));
      } catch (err) {
        console.error("Sync favorite error:", err);
      }
    },

    handleFavoriteUpdated() {
      if (getToken("khach-hang")) {
        this.syncFavoriteList();
      }
    },
    // Scroll Animation Logic
    initScrollAnimations() {
      const options = {
        root: null,
        rootMargin: '0px',
        threshold: 0.15
      };

      this.observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.classList.add('revealed');
            this.observer.unobserve(entry.target); // Trigger only once
          }
        });
      }, options);

      const elements = document.querySelectorAll('.reveal-item');
      elements.forEach(el => this.observer.observe(el));
    },

    showToast(msg, type = "warning", duration = 3000) {
      if (this.toast.timer) clearTimeout(this.toast.timer);
      this.toast = {
        visible: true,
        message: msg,
        type,
        icon: null,
        timer: setTimeout(() => this.hideToast(), duration),
      };
    },

    showFavoriteToast(action, propertyName) {
      const shortName = propertyName.length > 25 ? propertyName.substring(0, 22) + "..." : propertyName;
      if (this.toast.timer) clearTimeout(this.toast.timer);
      this.toast = {
        visible: true,
        message: action === "add" ? `Đã lưu "${shortName}"` : `Đã bỏ lưu "${shortName}"`,
        type: action === "add" ? "favorite-add" : "favorite-remove",
        icon: action === "add" ? "favorite" : "heart_broken",
        timer: setTimeout(() => this.hideToast(), 2500),
      };
    },

    hideToast() {
      this.toast.visible = false;
      if (this.toast.timer) {
        clearTimeout(this.toast.timer);
        this.toast.timer = null;
      }
    },

    handleImageError(e) {
      if (e.target.src !== this.defaultImage) {
        e.target.src = this.defaultImage;
      }
    },

    async loadPropertyTypes() {
      try {
        const res = await api.get("/client/loai-bat-dong-san");
        this.propertyTypes = Array.isArray(res.data) ? res.data : (res.data?.data || []);
      } catch (error) {
        console.error("Lỗi tải loại BĐS:", error);
      }
    },

    getImageUrl(url) {
      if (!url) return this.defaultImage;
      if (url.startsWith("http")) return url;
      const base = import.meta.env.VITE_API_URL?.replace('/api', '') || 'http://localhost:8000';
      return `${base}/storage/${url}`;
    },

    async loadProperties() {
      this.loading = true;
      try {
        const res = await api.get("/client/bat-dong-san");
        let raw = Array.isArray(res.data) ? res.data : (res.data?.data?.data || res.data?.data || []);
        this.properties = this.mapProperties(raw);
      } catch (error) {
        console.error("Lỗi tải BĐS:", error);
      } finally {
        this.loading = false;
      }
    },

    mapProperties(rawData) {
      return rawData.map((item) => {
        let imageUrl = this.defaultImage;
        const isValidUrl = (u) => typeof u === "string" && u.trim() !== "" && u !== "null" && u !== "undefined";

        if (isValidUrl(item.anh_dai_dien_url)) {
          imageUrl = this.getImageUrl(item.anh_dai_dien_url.trim());
        } else if (Array.isArray(item.hinh_anh) && item.hinh_anh.length > 0) {
          const img = item.hinh_anh.find((i) => i && isValidUrl(i.url));
          if (img) imageUrl = this.getImageUrl(img.url.trim());
        } else if (item.anh_dai_dien && isValidUrl(item.anh_dai_dien.url)) {
          imageUrl = this.getImageUrl(item.anh_dai_dien.url.trim());
        }

        let location = "Đang cập nhật";
        if (item.dia_chi) {
          const quan = item.dia_chi.quan?.ten || item.dia_chi.ten_quan;
          const tinh = item.dia_chi.tinh?.ten || item.dia_chi.ten_tinh;
          location = (quan && tinh) ? `${quan}, ${tinh}` : (tinh || "Việt Nam");
        }

        return {
          id: item.id,
          name: item.tieu_de || "Bất động sản",
          location,
          loai: item.loai?.ten_loai || "BĐS",
          gia: item.gia_display || item.gia,
          image: imageUrl,
          isFavorite: item.is_favorite || false,
          isExclusive: item.is_noi_bat || false // Random logic for mockup exclusive badge
        };
      });
    },

    async handleSearch() {
      this.loading = true;
      this.isSearchMode = true;
      try {
        let gia_min = null, gia_max = null;
        if (this.search.price === "duoi-10") gia_max = 10000000000;
        else if (this.search.price === "10-30") { gia_min = 10000000000; gia_max = 30000000000; }
        else if (this.search.price === "tren-30") gia_min = 30000000000;

        const payload = { tieu_de: this.search.location || "", loai_id: this.search.type || "", gia_min, gia_max };
        const res = await api.post("/client/tim-kiem", payload);
        const raw = res.data?.status ? (res.data.data?.data || []) : [];
        this.properties = this.mapProperties(raw);
        this.totalResults = res.data?.data?.total || this.properties.length;

        // Re-init animations for new items
        this.$nextTick(() => {
          this.initScrollAnimations();
          const section = document.getElementById("properties-section");
          if (section) {
            section.scrollIntoView({ behavior: "smooth", block: "start" });
          }
        });
      } catch (error) {
        console.error("Lỗi tìm kiếm:", error);
        this.properties = [];
        this.totalResults = 0;
      } finally {
        this.loading = false;
      }
    },

    viewProperty(id) {
      if (!getToken("khach-hang")) {
        this.showToast("Vui lòng đăng nhập để xem chi tiết");
        setTimeout(() => this.$router.push("/khach-hang/dang-nhap"), 800);
        return;
      }
      this.$router.push(`/khach-hang/chi-tiet-bat-dong-san/${id}`);
    },

    async toggleFavorite(id, ev) {
      ev.stopPropagation();
      if (!getToken("khach-hang")) {
        this.showToast("Vui lòng đăng nhập để lưu tin");
        setTimeout(() => this.$router.push("/khach-hang/dang-nhap"), 800);
        return;
      }

      const property = this.properties.find((p) => p.id === id);
      const wasFavorite = property?.isFavorite || false;
      const action = wasFavorite ? "remove" : "add";

      // Optimistic update
      if (property) property.isFavorite = !wasFavorite;

      try {
        await api.post("/khach-hang/bds/yeu-thich", { bds_id: id });
        await this.syncFavoriteList();
        this.showFavoriteToast(action, property?.name || "Bất động sản");
        window.dispatchEvent(new Event("favorite-updated"));
      } catch (err) {
        // Rollback
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

    clearRecent() {
      clearRecentlyViewed();
      this.recentlyViewed = [];
    },

    formatViewedAt(isoStr) {
      if (!isoStr) return '';
      const d = new Date(isoStr);
      const now = new Date();
      const diffMs = now - d;
      const diffMin = Math.floor(diffMs / 60000);
      if (diffMin < 1) return 'Vừa xem';
      if (diffMin < 60) return `${diffMin} phút trước`;
      const diffH = Math.floor(diffMin / 60);
      if (diffH < 24) return `${diffH} giờ trước`;
      const diffD = Math.floor(diffH / 24);
      return `${diffD} ngày trước`;
    },
  },
};
</script>

<style scoped>
/* Hide scrollbar for recently viewed row */
.no-scrollbar {
  scrollbar-width: none;
  -ms-overflow-style: none;
}

.no-scrollbar::-webkit-scrollbar {
  display: none;
}

/* 1. Scroll Reveal Animations */
.reveal-item {
  opacity: 0;
  transform: translateY(30px);
  transition: opacity 0.8s cubic-bezier(0.16, 1, 0.3, 1), transform 0.8s cubic-bezier(0.16, 1, 0.3, 1);
}

.reveal-item.revealed {
  opacity: 1;
  transform: translateY(0);
}

.property-showcase-card {
  position: relative;
  isolation: isolate;
  background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
  box-shadow: 0 18px 50px rgba(15, 23, 42, 0.07);
  transition:
    transform 0.72s cubic-bezier(0.16, 1, 0.3, 1),
    box-shadow 0.72s cubic-bezier(0.16, 1, 0.3, 1),
    border-color 0.72s cubic-bezier(0.16, 1, 0.3, 1);
  will-change: transform;
}

.property-showcase-card::after {
  content: "";
  position: absolute;
  inset: 0;
  z-index: -1;
  border-radius: inherit;
  background: linear-gradient(135deg, rgba(37, 99, 235, 0.12), rgba(20, 184, 166, 0.08));
  opacity: 0;
  transition: opacity 0.72s cubic-bezier(0.16, 1, 0.3, 1);
}

.property-showcase-card:hover {
  transform: translate3d(0, -10px, 0);
  border-color: rgba(147, 197, 253, 0.72);
  box-shadow: 0 28px 70px rgba(15, 23, 42, 0.13);
}

.property-showcase-card:hover::after {
  opacity: 1;
}

.property-showcase-media::before {
  content: "";
  position: absolute;
  inset: 0;
  z-index: 2;
  background: linear-gradient(120deg, transparent 20%, rgba(255, 255, 255, 0.2) 45%, transparent 70%);
  transform: translateX(-120%);
  transition: transform 0.95s cubic-bezier(0.16, 1, 0.3, 1);
  pointer-events: none;
}

.property-showcase-card:hover .property-showcase-media::before {
  transform: translateX(120%);
}

.property-showcase-img {
  transition:
    transform 1.05s cubic-bezier(0.16, 1, 0.3, 1),
    filter 0.8s ease;
}

.property-showcase-card:hover .property-showcase-img {
  transform: scale(1.08);
  filter: saturate(1.1) contrast(1.04);
}

.property-showcase-overlay {
  background: linear-gradient(180deg, rgba(2, 6, 23, 0.02) 0%, rgba(2, 6, 23, 0.08) 36%, rgba(2, 6, 23, 0.72) 100%);
  opacity: 0.84;
  transition: opacity 0.72s ease;
}

.property-showcase-card:hover .property-showcase-overlay {
  opacity: 0.96;
}

.property-badge-row {
  max-width: calc(100% - 76px);
  flex-wrap: wrap;
}

.property-badge {
  border-radius: 999px;
  border: 1px solid rgba(255, 255, 255, 0.72);
  box-shadow: 0 10px 26px rgba(15, 23, 42, 0.12);
}

.property-badge--type {
  background: rgba(255, 255, 255, 0.92);
}

.property-badge--hot {
  background: linear-gradient(135deg, #ff4d7d 0%, #e11d48 100%);
  box-shadow: 0 12px 28px rgba(225, 29, 72, 0.28);
}

.favorite-float-btn {
  border: 1px solid rgba(255, 255, 255, 0.72);
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.13);
  transition:
    transform 0.42s cubic-bezier(0.16, 1, 0.3, 1),
    box-shadow 0.42s cubic-bezier(0.16, 1, 0.3, 1),
    background-color 0.42s ease;
}

.favorite-float-btn:hover {
  transform: translateY(-2px) scale(1.08);
  background: #ffffff;
  box-shadow: 0 18px 38px rgba(15, 23, 42, 0.18);
}

.property-showcase-body {
  background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
}

.property-location-line {
  transition: color 0.4s ease;
}

.property-showcase-title {
  transition: color 0.42s ease;
}

.property-showcase-card:hover .property-location-line,
.property-showcase-card:hover .property-showcase-title {
  color: #1d4ed8;
}

.home-toast-shell {
  max-width: calc(100vw - 2rem);
}

.home-toast {
  width: min(340px, calc(100vw - 2rem));
  min-height: 64px;
  padding: 0.9rem 1rem;
  border-radius: 22px;
  border: 1px solid rgba(255, 255, 255, 0.12);
  box-shadow: 0 22px 56px rgba(15, 23, 42, 0.22);
}

.home-toast__icon {
  width: 38px;
  height: 38px;
  flex: 0 0 auto;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.12);
  font-size: 20px;
}

.home-toast__message {
  flex: 1;
  min-width: 0;
  font-size: 0.9rem;
  line-height: 1.35;
}

.home-toast__close {
  width: 34px;
  height: 34px;
  flex: 0 0 auto;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 999px;
  color: rgba(255, 255, 255, 0.68);
}

.home-toast__close:hover {
  color: #ffffff;
  background: rgba(255, 255, 255, 0.12);
}

/* 2. Hero subtle zoom */
.animate-hero-scale {
  animation: heroScale 25s ease-in-out infinite alternate;
}

@keyframes heroScale {
  0% {
    transform: scale(1);
  }

  100% {
    transform: scale(1.1);
  }
}

/* 3. Toast Fade Up */
.fade-toast-enter-active,
.fade-toast-leave-active {
  transition:
    opacity 0.32s cubic-bezier(0.16, 1, 0.3, 1),
    transform 0.32s cubic-bezier(0.16, 1, 0.3, 1);
}

.fade-toast-enter-from,
.fade-toast-leave-to {
  opacity: 0;
  transform: translateX(22px) scale(0.96);
}

/* ===== DARK MODE FIX FOR HOME PAGE ===== */

:global(html.dark) .bg-slate-50 {
  background-color: #0f172a !important;
}

:global(html.dark) #properties-section {
  background: #0f172a !important;
}

:global(html.dark) .property-showcase-card {
  background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%) !important;
  border-color: #334155 !important;
  color: #e2e8f0 !important;
  box-shadow: 0 18px 50px rgba(0, 0, 0, 0.38) !important;
}

:global(html.dark) .property-showcase-body {
  background: linear-gradient(180deg, #1e293b 0%, #172033 100%) !important;
}

:global(html.dark) .property-showcase-title {
  color: #f8fafc !important;
}

:global(html.dark) .property-location-line {
  color: #94a3b8 !important;
}

:global(html.dark) .property-location-line .material-symbols-outlined {
  color: #94a3b8 !important;
}

:global(html.dark) .property-showcase-card:hover .property-showcase-title {
  color: #60a5fa !important;
}

:global(html.dark) .property-showcase-card:hover .property-location-line {
  color: #bfdbfe !important;
}

:global(html.dark) .property-showcase-media {
  background-color: #0f172a !important;
}

:global(html.dark) .favorite-float-btn {
  background: rgba(15, 23, 42, 0.9) !important;
  border-color: rgba(148, 163, 184, 0.35) !important;
}

:global(html.dark) .favorite-float-btn:hover {
  background: #1e293b !important;
}

:global(html.dark) .property-badge--type {
  background: rgba(15, 23, 42, 0.88) !important;
  color: #f8fafc !important;
  border-color: rgba(148, 163, 184, 0.35) !important;
}

/* ===== DARK MODE FIX FOR FEATURES SECTION ===== */

:global(html.dark) section.bg-white {
  background-color: #0f172a !important;
  border-color: #334155 !important;
}

:global(html.dark) .text-slate-900 {
  color: #f8fafc !important;
}

:global(html.dark) .text-slate-800,
:global(html.dark) .text-slate-700 {
  color: #e2e8f0 !important;
}

:global(html.dark) .text-slate-500,
:global(html.dark) .text-slate-400,
:global(html.dark) .text-slate-300 {
  color: #94a3b8 !important;
}

:global(html.dark) .bg-slate-100,
:global(html.dark) .bg-slate-50 {
  background-color: #1e293b !important;
}

:global(html.dark) .border-slate-100,
:global(html.dark) .border-slate-100\/70 {
  border-color: #334155 !important;
}

/* ===== DARK MODE FIX FOR SEARCH BAR ===== */

:global(html.dark) section .bg-white.rounded-2xl {
  background: #1e293b !important;
  border-color: #334155 !important;
}

:global(html.dark) input,
:global(html.dark) select {
  color: #e2e8f0 !important;
}

:global(html.dark) input::placeholder {
  color: #64748b !important;
}

:global(html.dark) option {
  background: #1e293b !important;
  color: #e2e8f0 !important;
}

:global(html.dark) .divide-slate-100> :not([hidden])~ :not([hidden]) {
  border-color: #334155 !important;
}
</style>
