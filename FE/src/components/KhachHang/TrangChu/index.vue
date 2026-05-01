<template>
  <div class="bg-slate-50 min-h-screen font-sans selection:bg-blue-100 selection:text-blue-900 overflow-x-hidden">
    
    <!-- 1. HERO SECTION -->
    <section class="relative h-[85vh] min-h-[600px] flex items-center justify-center overflow-hidden">
      <!-- Background Image -->
      <div class="absolute inset-0 z-0">
        <img
          src="https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?ixlib=rb-4.0.3&auto=format&fit=crop&w=2500&q=80"
          class="w-full h-full object-cover animate-hero-scale"
          alt="Bất động sản cao cấp"
        />
        <!-- Elegant dark overlay -->
        <div class="absolute inset-0 bg-gradient-to-b from-slate-900/80 via-slate-900/60 to-slate-900/90"></div>
      </div>

      <!-- Hero Content -->
      <div class="relative z-10 container mx-auto px-6 lg:px-12 text-center reveal-item">
        <span class="inline-block px-4 py-1.5 bg-white/10 backdrop-blur-sm text-white text-xs font-bold uppercase tracking-widest rounded-full mb-6 border border-white/20">
          Nền tảng giao dịch BĐS uy tín
        </span>
        <h1 class="text-4xl md:text-6xl lg:text-7xl font-bold text-white leading-tight mb-6 tracking-tight">
          Tìm Kiếm Tổ Ấm <br class="hidden md:block"/>
          <span class="text-blue-400">Hoàn Hảo Của Bạn</span>
        </h1>
        <p class="text-lg md:text-xl text-slate-300 max-w-2xl mx-auto font-light leading-relaxed mb-12">
          Khám phá hàng ngàn bất động sản xác thực với thông tin minh bạch, định giá chính xác và hỗ trợ chuyên nghiệp 24/7.
        </p>
      </div>
    </section>

    <!-- 2. SEARCH BAR -->
    <section class="relative z-20 -mt-20 px-6 reveal-item delay-100">
      <div class="container mx-auto max-w-5xl">
        <div class="bg-white rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.08)] border border-slate-100 overflow-hidden">
          <div class="flex flex-col md:flex-row items-stretch divide-y md:divide-y-0 md:divide-x divide-slate-100">
            
            <!-- Vị trí -->
            <div class="w-full flex-1 px-6 py-4 flex flex-col justify-center group hover:bg-slate-50/50 transition-colors">
              <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2 group-hover:text-blue-500 transition-colors">Khu vực</label>
              <div class="flex items-center">
                <span class="material-symbols-outlined text-slate-400 mr-3 text-xl group-focus-within:text-blue-500">location_on</span>
                <input
                  v-model="search.location"
                  placeholder="Nhập quận, thành phố..."
                  class="w-full bg-transparent border-0 border-transparent shadow-none focus:ring-0 text-slate-700 text-base font-medium focus:outline-none placeholder:text-slate-300 placeholder:font-normal p-0"
                />
              </div>
            </div>

            <!-- Loại hình -->
            <div class="w-full flex-1 px-6 py-4 flex flex-col justify-center group hover:bg-slate-50/50 transition-colors">
              <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2 group-hover:text-blue-500 transition-colors">Loại hình</label>
              <div class="flex items-center relative">
                <span class="material-symbols-outlined text-slate-400 mr-3 text-xl">real_estate_agent</span>
                <select v-model="search.type" class="w-full bg-transparent border-0 border-transparent shadow-none focus:ring-0 p-0 text-slate-700 text-base font-medium appearance-none focus:outline-none cursor-pointer">
                  <option value="">Tất cả loại hình</option>
                  <option v-for="loai in propertyTypes" :key="loai.id" :value="loai.id">{{ loai.ten_loai }}</option>
                </select>
                <span class="material-symbols-outlined absolute right-0 text-slate-400 pointer-events-none text-xl">expand_more</span>
              </div>
            </div>

            <!-- Mức giá -->
            <div class="w-full flex-1 px-6 py-4 flex flex-col justify-center group hover:bg-slate-50/50 transition-colors">
              <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2 group-hover:text-blue-500 transition-colors">Mức giá</label>
              <div class="flex items-center relative">
                <span class="material-symbols-outlined text-slate-400 mr-3 text-xl">payments</span>
                <select v-model="search.price" class="w-full bg-transparent border-0 border-transparent shadow-none focus:ring-0 p-0 text-slate-700 text-base font-medium appearance-none focus:outline-none cursor-pointer">
                  <option value="">Mọi mức giá</option>
                  <option value="duoi-10">Dưới 10 tỷ</option>
                  <option value="10-30">10 - 30 tỷ</option>
                  <option value="tren-30">Trên 30 tỷ</option>
                </select>
                <span class="material-symbols-outlined absolute right-0 text-slate-400 pointer-events-none text-xl">expand_more</span>
              </div>
            </div>

            <!-- Submit -->
            <div class="w-full md:w-auto p-2 flex items-stretch">
              <button @click="handleSearch" style="background-color: #2563eb; color: white; border-radius: 0.75rem;" class="w-full md:w-auto px-10 hover:opacity-90 font-bold flex items-center justify-center gap-2 transition-all shadow-md active:scale-[0.98]">
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
              {{ isSearchMode ? `Tìm thấy ${totalResults} bất động sản phù hợp với yêu cầu của bạn.` : 'Những bất động sản được đánh giá cao nhất trong tuần.' }}
            </p>
          </div>
          <router-link
            to="/khach-hang/danh-sach-bat-dong-san"
            class="text-blue-600 font-semibold hover:text-blue-700 flex items-center gap-1 group transition-colors"
          >
            Xem tất cả dự án
            <span class="material-symbols-outlined text-xl group-hover:translate-x-1 transition-transform">arrow_right_alt</span>
          </router-link>
        </div>

        <!-- Loading Skeleton -->
        <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div v-for="i in 3" :key="`loading-${i}`" class="animate-pulse bg-white border border-slate-100 rounded-2xl overflow-hidden shadow-sm">
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
          <div
            v-for="(item, index) in properties"
            :key="item.id"
            class="reveal-item group bg-white border border-slate-100/60 rounded-2xl overflow-hidden hover:shadow-[0_20px_40px_rgb(0,0,0,0.06)] hover:-translate-y-1.5 transition-all duration-400 cursor-pointer"
            :style="`transition-delay: ${index * 100}ms`"
            @click.prevent="viewProperty(item.id)"
          >
            <!-- Image Area -->
            <div class="relative aspect-[4/3] overflow-hidden bg-slate-100">
              <img
                :src="item.image"
                class="w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-105"
                :alt="item.name"
                @error="handleImageError"
                loading="lazy"
              />
              <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 via-transparent to-transparent opacity-60 group-hover:opacity-80 transition-opacity"></div>

              <!-- Badges -->
              <div class="absolute top-4 left-4 flex gap-2">
                <span class="px-3 py-1 bg-white/95 backdrop-blur text-slate-800 text-[11px] font-bold uppercase tracking-wider rounded-md shadow-sm">
                  {{ item.loai }}
                </span>
                <span v-if="item.isExclusive" class="px-3 py-1 bg-rose-500 text-white text-[11px] font-bold uppercase tracking-wider rounded-md shadow-sm">
                  Hot
                </span>
              </div>

              <!-- Heart Button -->
              <button
                @click.stop="toggleFavorite(item.id, $event)"
                class="absolute top-4 right-4 w-10 h-10 bg-white/95 backdrop-blur-md rounded-full shadow-sm flex items-center justify-center hover:scale-110 active:scale-95 transition-all duration-300"
              >
                <span
                  class="material-symbols-outlined text-[20px] transition-colors duration-300"
                  :style="{ fontVariationSettings: item.isFavorite ? `'FILL' 1` : `'FILL' 0` }"
                  :class="item.isFavorite ? 'text-rose-500' : 'text-slate-400 group-hover/btn:text-rose-400'"
                >
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
            <div class="p-6">
              <div class="flex items-center gap-1.5 text-slate-400 mb-2">
                <span class="material-symbols-outlined text-[16px]">location_on</span>
                <span class="text-sm font-medium">{{ item.location }}</span>
              </div>
              <h3 class="text-lg font-bold text-slate-800 line-clamp-2 leading-snug group-hover:text-blue-600 transition-colors min-h-[56px] mb-2">
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
          <p class="text-slate-500 text-lg">Chúng tôi cung cấp dịch vụ chuyên nghiệp, minh bạch và an toàn nhất trên thị trường bất động sản.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
          <div class="reveal-item text-center group" style="transition-delay: 100ms">
            <div class="w-16 h-16 mx-auto bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-blue-600 group-hover:text-white transition-colors duration-300">
              <span class="material-symbols-outlined text-3xl">shield_locked</span>
            </div>
            <h3 class="text-lg font-bold text-slate-900 mb-3">Pháp lý an toàn</h3>
            <p class="text-slate-500 text-sm leading-relaxed px-4">100% bất động sản được kiểm tra sổ đỏ và tình trạng quy hoạch minh bạch.</p>
          </div>

          <div class="reveal-item text-center group" style="transition-delay: 200ms">
            <div class="w-16 h-16 mx-auto bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-300">
              <span class="material-symbols-outlined text-3xl">verified_user</span>
            </div>
            <h3 class="text-lg font-bold text-slate-900 mb-3">Định giá chính xác</h3>
            <p class="text-slate-500 text-sm leading-relaxed px-4">Sử dụng dữ liệu thị trường thực tế để đưa ra mức giá tư vấn tốt nhất.</p>
          </div>

          <div class="reveal-item text-center group" style="transition-delay: 300ms">
            <div class="w-16 h-16 mx-auto bg-cyan-50 text-cyan-600 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-cyan-600 group-hover:text-white transition-colors duration-300">
              <span class="material-symbols-outlined text-3xl">support_agent</span>
            </div>
            <h3 class="text-lg font-bold text-slate-900 mb-3">Hỗ trợ tận tâm</h3>
            <p class="text-slate-500 text-sm leading-relaxed px-4">Đội ngũ chuyên viên túc trực 24/7 để giải đáp mọi quy trình mua bán.</p>
          </div>

          <div class="reveal-item text-center group" style="transition-delay: 400ms">
            <div class="w-16 h-16 mx-auto bg-teal-50 text-teal-600 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-teal-600 group-hover:text-white transition-colors duration-300">
              <span class="material-symbols-outlined text-3xl">handshake</span>
            </div>
            <h3 class="text-lg font-bold text-slate-900 mb-3">Giao dịch nhanh gọn</h3>
            <p class="text-slate-500 text-sm leading-relaxed px-4">Thủ tục giấy tờ được tối ưu hóa, tiết kiệm tối đa thời gian của khách hàng.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- 5. CTA SECTION -->
    <section class="py-24 px-6">
      <div class="container mx-auto max-w-6xl reveal-item">
        <div class="relative bg-blue-600 rounded-3xl p-12 lg:p-20 overflow-hidden shadow-2xl shadow-blue-600/20 text-center">
          <!-- Geometric background pattern -->
          <div class="absolute inset-0 opacity-10">
            <div class="absolute top-0 right-0 w-96 h-96 bg-white rounded-full mix-blend-overlay blur-3xl translate-x-1/2 -translate-y-1/2"></div>
            <div class="absolute bottom-0 left-0 w-96 h-96 bg-white rounded-full mix-blend-overlay blur-3xl -translate-x-1/2 translate-y-1/2"></div>
          </div>
          
          <div class="relative z-10 max-w-3xl mx-auto">
            <h2 class="text-4xl md:text-5xl font-bold text-white mb-6 leading-tight">Trở Thành Đối Tác Chuyên Nghiệp</h2>
            <p class="text-blue-100 text-lg md:text-xl mb-10 font-medium">
              Đăng ký để tiếp cận hàng triệu khách hàng tiềm năng và sử dụng công cụ quản lý bất động sản hàng đầu.
            </p>
            <router-link to="/khach-hang/nang-cap-moi-gioi" class="bg-white text-blue-600 hover:bg-slate-50 px-12 py-5 rounded-xl font-bold text-lg transition-colors shadow-lg active:scale-95 inline-block cursor-pointer relative z-20">
              Đăng Ký Ngay
            </router-link>
          </div>
        </div>
      </div>
    </section>

    <!-- 6. TOAST NOTIFICATION -->
    <transition name="fade-toast">
      <div v-if="toast.visible" class="fixed bottom-8 left-1/2 -translate-x-1/2 z-[9999]">
        <div class="flex items-center gap-3 px-6 py-4 bg-slate-900/95 backdrop-blur text-white rounded-xl shadow-2xl">
          <span class="material-symbols-outlined text-xl" :class="toast.type === 'favorite-add' ? 'text-rose-500 font-variation-settings:\'FILL\' 1' : 'text-blue-400'">
            {{ toast.icon || 'info' }}
          </span>
          <span class="font-medium">{{ toast.message }}</span>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
import api from "@/axios/config";
import { getToken } from "@/js/auth";

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
      const base = import.meta.env.VITE_API_URL?.replace('/api','') || 'http://localhost:8000';
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
    }
  },
};
</script>

<style scoped>
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

/* 2. Hero subtle zoom */
.animate-hero-scale {
  animation: heroScale 25s ease-in-out infinite alternate;
}
@keyframes heroScale {
  0% { transform: scale(1); }
  100% { transform: scale(1.1); }
}

/* 3. Toast Fade Up */
.fade-toast-enter-active,
.fade-toast-leave-active {
  transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}
.fade-toast-enter-from,
.fade-toast-leave-to {
  opacity: 0;
  transform: translate(-50%, 20px);
}
</style>