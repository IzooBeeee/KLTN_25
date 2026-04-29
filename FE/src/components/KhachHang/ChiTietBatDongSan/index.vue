<template>
  <div class="bg-[#f8fafc] min-h-screen font-['Inter']">
    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center min-h-screen">
      <div class="text-center">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-blue-600 border-t-transparent"></div>
        <p class="mt-4 text-gray-500 font-medium">Đang tải thông tin bất động sản...</p>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="flex items-center justify-center min-h-screen">
      <div class="text-center">
        <span class="material-symbols-outlined text-6xl text-gray-300 mb-4">home_work</span>
        <h2 class="text-2xl font-bold text-gray-700 mb-2">Không tìm thấy bất động sản</h2>
        <p class="text-gray-500 mb-6">{{ error }}</p>
        <router-link to="/" class="text-blue-600 font-semibold hover:underline">← Quay lại trang chủ</router-link>
      </div>
    </div>

    <!-- Content -->
    <template v-else-if="property">
      <!-- Breadcrumb -->
      <div class="bg-white border-b border-gray-100">
        <div class="container mx-auto max-w-7xl px-6 py-4">
          <nav class="flex items-center gap-2 text-sm text-gray-500">
            <router-link to="/" class="hover:text-blue-600 transition-colors">Trang chủ</router-link>
            <span class="material-symbols-outlined text-[14px]">chevron_right</span>
            <router-link to="/properties" class="hover:text-blue-600 transition-colors">Bất động sản</router-link>
            <span class="material-symbols-outlined text-[14px]">chevron_right</span>
            <span class="text-[#0a0e27] font-semibold">{{ property.ten_tinh || property.location || 'Thành phố' }}</span>
            <span class="material-symbols-outlined text-[14px]">chevron_right</span>
            <span class="text-[#0a0e27] font-semibold truncate max-w-[200px]">{{ property.tieu_de }}</span>
          </nav>
        </div>
      </div>

      <!-- Header Section -->
      <div class="bg-white border-b border-gray-100">
        <div class="container mx-auto max-w-7xl px-6 py-8">
          <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
            <div>
              <h1 class="font-['Be_Vietnam_Pro'] text-[32px] md:text-[36px] font-bold text-[#0a0e27] leading-tight mb-3">
                {{ property.tieu_de }}
              </h1>
              <p class="flex items-center gap-2 text-gray-500">
                <span class="material-symbols-outlined text-[18px] text-blue-600">location_on</span>
                <span>{{ property?.dia_chi?.dia_chi_chi_tiet}}, {{ property?.dia_chi?.quan?.ten}}, {{ property?.dia_chi?.tinh?.ten }}</span>
              </p>
            </div>
            <div class="text-left lg:text-right">
              <p class="text-xs text-gray-400 uppercase tracking-widest font-semibold mb-1">Giá niêm yết</p>
              <p class="font-['Be_Vietnam_Pro'] text-[32px] font-black text-[#0a0e27]">
                {{ formatPriceFull(property.gia) }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Image Gallery + Property Stats + Agent Form -->
      <div class="container mx-auto max-w-7xl px-6 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Left Column: Main Image + Stats -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Main Image with ❤️ Favorite Button -->
            <div class="relative group cursor-pointer rounded-2xl overflow-hidden h-[700px]" @click="openGallery(0)">
              <img
                :src="mainImage"
                class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                :alt="property.tieu_de"
              />
              <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors"></div>
              
              <!-- ❤️ Nút Yêu Thích - Góc trên bên TRÁI ảnh chính -->
              <button
                @click.stop="toggleFavorite(property.id, $event)"
                class="absolute top-4 left-4 w-11 h-11 rounded-full bg-white/95 backdrop-blur-md shadow-lg hover:shadow-xl flex items-center justify-center transition-all duration-300 hover:scale-110 active:scale-95 group/btn z-10"
                :class="isPropertyFavorite ? 'bg-gradient-to-br from-pink-500 to-rose-500' : ''"
                :aria-label="isPropertyFavorite ? 'Bỏ yêu thích' : 'Thêm vào yêu thích'"
                title="Yêu thích"
              >
                <span 
                  class="material-symbols-outlined text-xl transition-all duration-300"
                  :class="isPropertyFavorite ? 'text-white fill-current' : 'text-gray-400 group-hover/btn:text-pink-500'"
                >
                  favorite
                </span>
              </button>
              
              <!-- Image Count Badge - Góc dưới bên trái -->
              <div class="absolute bottom-4 left-4 bg-black/60 backdrop-blur-md text-white px-4 py-2 rounded-lg text-sm z-10">
                {{ images.length }} ảnh
              </div>
            </div>

            <!-- Property Info Stats - 4 Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
              <div class="bg-white rounded-2xl p-5 text-center border border-gray-100 hover:shadow-lg transition-shadow">
                <span class="material-symbols-outlined text-blue-600 text-[28px] mb-2 block mx-auto">square_foot</span>
                <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Diện tích</p>
                <p class="font-['Be_Vietnam_Pro'] text-xl font-bold text-[#0a0e27]">{{ property.dien_tich || '—' }} m²</p>
              </div>
              <div class="bg-white rounded-2xl p-5 text-center border border-gray-100 hover:shadow-lg transition-shadow">
                <span class="material-symbols-outlined text-blue-600 text-[28px] mb-2 block mx-auto">bed</span>
                <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Phòng ngủ</p>
                <p class="font-['Be_Vietnam_Pro'] text-xl font-bold text-[#0a0e27]">{{ property.so_phong_ngu || '—' }}</p>
              </div>
              <div class="bg-white rounded-2xl p-5 text-center border border-gray-100 hover:shadow-lg transition-shadow">
                <span class="material-symbols-outlined text-blue-600 text-[28px] mb-2 block mx-auto">bathtub</span>
                <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Phòng tắm</p>
                <p class="font-['Be_Vietnam_Pro'] text-xl font-bold text-[#0a0e27]">{{ property.so_phong_tam || '—' }}</p>
              </div>
              <div class="bg-white rounded-2xl p-5 text-center border border-gray-100 hover:shadow-lg transition-shadow">
                <span class="material-symbols-outlined text-blue-600 text-[28px] mb-2 block mx-auto">garage</span>
                <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Chỗ đậu xe</p>
                <p class="font-['Be_Vietnam_Pro'] text-xl font-bold text-[#0a0e27]">{{ property.so_cho_dau_xe || '—' }}</p>
              </div>
            </div>
          </div>

          <!-- Right Column: Small Images + Agent Form -->
          <div class="flex flex-col gap-4">
            <!-- Small Images Grid -->
            <div class="grid grid-cols-2 gap-4 h-[240px]">
              <div
                v-for="(img, i) in displayImages"
                :key="i"
                class="relative group cursor-pointer rounded-2xl overflow-hidden h-full"
                @click="openGallery(i + 1)"
              >
                <img
                  :src="getImageUrl(img.url)"
                  class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                  :alt="'Ảnh ' + (i + 2)"
                />
                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors"></div>
                <!-- Counter overlay for last image if more images exist -->
                <div 
                  v-if="i === displayImages.length - 1 && remainingImages > 0"
                  class="absolute inset-0 bg-black/60 flex items-center justify-center rounded-2xl"
                >
                  <span class="text-white text-2xl font-bold">+{{ remainingImages }}</span>
                </div>
              </div>
            </div>

            <!-- Agent Contact Form (Sticky) -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-5 sticky top-6">
              <!-- Agent Info -->
              <div class="flex items-center gap-3 mb-4 pb-4 border-b border-gray-100">
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-600 to-blue-800 flex items-center justify-center text-white font-bold text-lg flex-shrink-0">
                  {{ property?.moi_gioi?.ten?.charAt(0) || 'A' }}
                </div>
                <div class="flex-1 min-w-0">
                  <h3 class="font-['Be_Vietnam_Pro'] font-bold text-[#0a0e27] text-sm truncate">{{ property?.moi_gioi?.ten }}</h3>
                  <p class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold">Chuyên viên tư vấn</p>
                  <div class="flex items-center gap-0.5 mt-0.5">
                    <span v-for="s in 5" :key="s" class="material-symbols-outlined text-[12px]" :class="s <= 5 ? 'text-yellow-400' : 'text-gray-300'">star</span>
                  </div>
                </div>
              </div>

              <!-- Contact Form -->
              <!-- <form @submit.prevent="submitForm" class="space-y-3">
                <div>
                  <input
                    v-model="form.ho_ten"
                    type="text"
                    placeholder="Họ và tên *"
                    class="w-full bg-gray-50 border border-gray-200 rounded-lg py-2.5 px-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all"
                  />
                </div>
                <div>
                  <input
                    v-model="form.so_dien_thoai"
                    type="tel"
                    placeholder="Số điện thoại *"
                    class="w-full bg-gray-50 border border-gray-200 rounded-lg py-2.5 px-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all"
                  />
                </div>
                <div>
                  <textarea
                    v-model="form.tin_nhan"
                    rows="2"
                    placeholder="Tin nhắn (tùy chọn)"
                    class="w-full bg-gray-50 border border-gray-200 rounded-lg py-2.5 px-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all resize-none"
                  ></textarea>
                </div>
                <button
                  type="submit"
                  class="w-full bg-[#0a0e27] hover:bg-[#0d1542] text-white font-bold py-2.5 rounded-lg shadow-lg shadow-blue-900/20 transition-all hover:shadow-xl hover:-translate-y-0.5 active:scale-[0.98] flex items-center justify-center gap-2 text-sm"
                >
                  <span class="material-symbols-outlined text-[16px]">calendar_month</span>
                  Đăng ký tư vấn
                </button>
              </form> -->

              <!-- Quick Contact Buttons -->
              <div class="grid grid-cols-2 gap-2 mt-3">
                <!-- Gọi ngay -->
                <a :href="'tel:' + (broker?.so_dien_thoai || '')" class="flex items-center justify-center gap-1.5 py-2 rounded-lg border border-gray-200 hover:border-blue-500 hover:bg-blue-50 transition-all text-gray-600 hover:text-blue-600 text-xs font-semibold">
                  <span class="material-symbols-outlined text-[16px]">call</span>
                  Gọi ngay
                </a>
                
                <!-- 🔥 Chat với môi giới -->
                <button
                  @click="startChat"
                  class="flex items-center justify-center gap-1.5 py-2 rounded-lg border border-gray-200 hover:border-blue-500 hover:bg-blue-50 transition-all text-gray-600 hover:text-blue-600 text-xs font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
                  :disabled="!brokerId"
                  :title="brokerId ? 'Chat với môi giới' : 'Chưa có môi giới'"
                >
                  <span class="material-symbols-outlined text-[16px]">chat</span>
                  Chat
                </button>
              </div>

              <!-- Zalo Button -->
              <a
                v-if="broker?.zalo_link"
                :href="broker.zalo_link"
                target="_blank"
                class="mt-2 w-full flex items-center justify-center gap-2 py-2 rounded-lg bg-[#0068FF] hover:bg-[#0055D6] text-white font-bold transition-all text-sm"
              >
                <img src="https://upload.wikimedia.org/wikipedia/commons/9/91/Icon_of_Zalo.svg" class="w-4 h-4" alt="Zalo" />
                Chat Zalo
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="container mx-auto max-w-7xl px-6 pb-16">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
          
          <!-- Left Content (2 columns) -->
          <div class="lg:col-span-2 space-y-10">
            
            <!-- Description -->
            <div>
              <h2 class="font-['Be_Vietnam_Pro'] text-[24px] font-bold text-[#0a0e27] mb-6 flex items-center gap-2">
                <span class="w-1 h-7 bg-blue-600 rounded-full"></span>
                Mô tả chi tiết
              </h2>
              <div class="text-gray-600 leading-relaxed space-y-4 text-[15px]" v-html="property.mo_ta || '<p>Chưa có mô tả chi tiết.</p>'">
              </div>
            </div>

            <!-- Features/Amenities - Only 4 items -->
            <div v-if="featuresList.length > 0">
              <h2 class="font-['Be_Vietnam_Pro'] text-[24px] font-bold text-[#0a0e27] mb-6 flex items-center gap-2">
                <span class="w-1 h-7 bg-blue-600 rounded-full"></span>
                Tiện ích & Tính năng
              </h2>
              <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <div v-for="(feature, i) in featuresList.slice(0, 4)" :key="i" class="bg-white rounded-xl p-4 border border-gray-100 text-center hover:shadow-md transition-shadow">
                  <span class="material-symbols-outlined text-green-500 text-[24px] mb-2 block mx-auto">check_circle</span>
                  <span class="text-[13px] text-gray-700 font-medium">{{ feature }}</span>
                </div>
              </div>
              <div v-if="featuresList.length > 4" class="mt-3 text-center">
                <button @click="showAllFeatures = !showAllFeatures" class="text-blue-600 font-semibold text-sm hover:underline">
                  {{ showAllFeatures ? 'Thu gọn' : 'Xem thêm ' + (featuresList.length - 4) + ' tiện ích' }}
                </button>
              </div>
              <!-- Additional features (hidden by default) -->
              <div v-if="showAllFeatures && featuresList.length > 4" class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-3">
                <div v-for="(feature, i) in featuresList.slice(4)" :key="i" class="bg-white rounded-xl p-4 border border-gray-100 text-center hover:shadow-md transition-shadow">
                  <span class="material-symbols-outlined text-green-500 text-[24px] mb-2 block mx-auto">check_circle</span>
                  <span class="text-[13px] text-gray-700 font-medium">{{ feature }}</span>
                </div>
              </div>
            </div>

            <!-- Location Map -->
            <div>
              <h2 class="font-['Be_Vietnam_Pro'] text-[24px] font-bold text-[#0a0e27] mb-6 flex items-center gap-2">
                <span class="w-1 h-7 bg-blue-600 rounded-full"></span>
                Vị trí dự án
              </h2>
              <div class="bg-gray-100 rounded-2xl overflow-hidden h-[350px] relative">
                <iframe
                  v-if="property.vi_do || property.google_map_url"
                  :src="property.google_map_url || property.vi_do"
                  class="w-full h-full border-0"
                  allowfullscreen
                  loading="lazy"
                ></iframe>
                <div v-else class="flex items-center justify-center h-full text-gray-400">
                  <div class="text-center">
                    <span class="material-symbols-outlined text-5xl mb-2">map</span>
                    <p>Bản đồ đang được cập nhật</p>
                  </div>
                </div>
                <!-- Map Label -->
                <div class="absolute bottom-4 left-4 bg-white rounded-lg px-4 py-2 shadow-lg">
                  <p class="font-semibold text-[#0a0e27] text-sm">{{ property.tieu_de }}</p>
                  <p class="text-xs text-gray-500">{{ property.location }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Sidebar - Empty (for spacing) -->
          <div class="lg:col-span-1">
            <!-- This column is now empty as the form is in the gallery section -->
          </div>
        </div>
      </div>

      <!-- Similar Properties -->
      <section class="bg-gray-50 py-16 border-t border-gray-100">
        <div class="container mx-auto max-w-7xl px-6">
          <div class="flex justify-between items-center mb-10">
            <div>
              <h2 class="font-['Be_Vietnam_Pro'] text-[28px] font-bold text-[#0a0e27]">Bất động sản tương tự</h2>
              <p class="text-gray-500 text-sm mt-1">Những lựa chọn phù hợp khác cho bạn</p>
            </div>
            <router-link to="/properties" class="hidden md:flex items-center gap-1 text-blue-600 font-semibold hover:underline text-sm">
              Xem tất cả <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
            </router-link>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div
              v-for="bds in similarProperties"
              :key="bds.id"
              @click="viewProperty(bds.id)"
              class="group bg-white rounded-2xl overflow-hidden shadow-[0_4px_20px_rgba(0,0,0,0.04)] hover:shadow-[0_12px_40px_rgba(10,14,39,0.1)] transition-all duration-500 cursor-pointer border border-gray-100 hover:-translate-y-2"
            >
              <div class="relative h-[240px] overflow-hidden">
                <img
                  :src="getImageUrl(bds.hinh_anh?.[0]?.url)"
                  class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                  :alt="bds.tieu_de"
                />
                <div class="absolute top-4 left-4 px-3 py-1 bg-white/90 backdrop-blur-md text-[#0a0e27] text-[10px] font-bold uppercase tracking-wider rounded-full">
                  {{ bds.loai?.ten_loai || 'BĐS' }}
                </div>
              </div>
              <div class="p-6">
                <h3 class="font-['Be_Vietnam_Pro'] font-bold text-[18px] text-[#0a0e27] mb-3 line-clamp-2 group-hover:text-blue-600 transition-colors">
                  {{ bds.tieu_de }}
                </h3>
                <p class="text-gray-500 text-sm flex items-center gap-1 mb-4">
                  <span class="material-symbols-outlined text-[16px]">location_on</span>
                  {{ bds.dia_chi?.ten_quan || bds.location || '—' }}
                </p>
                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                  <p class="font-['Be_Vietnam_Pro'] text-xl font-black text-[#0a0e27]">
                    {{ formatPriceFull(bds.gia) }}
                  </p>
                  <span class="text-blue-600 font-semibold text-sm flex items-center gap-1 group/btn">
                    Chi tiết
                    <span class="material-symbols-outlined text-[16px] group-hover/btn:translate-x-1 transition-transform">arrow_forward</span>
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </template>

    <!-- 🍞 Enhanced Toast Notification -->
    <transition name="toast-slide">
      <div 
        v-if="toast.visible" 
        class="fixed top-20 right-5 z-[9999] pointer-events-auto"
        style="will-change: transform, opacity;"
      >
        <div 
          class="flex items-center gap-3 px-5 py-3.5 text-white rounded-2xl shadow-2xl backdrop-blur-md border border-white/20 min-w-[280px] max-w-sm"
          :class="getToastClass(toast.type)"
        >
          <span 
            class="material-symbols-outlined text-xl flex-shrink-0"
            :class="{ 'animate-heart-pulse': toast.type === 'favorite-add' }"
          >
            {{ toast.icon || getToastIcon(toast.type) }}
          </span>
          <span class="font-medium text-sm flex-1 leading-tight">
            {{ toast.message }}
          </span>
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

    <!-- Image Gallery Modal -->
    <div v-if="showGallery" class="fixed inset-0 z-[9999] bg-black/90 flex items-center justify-center" @click.self="closeGallery">
      <button @click="closeGallery" class="absolute top-6 right-6 text-white hover:text-gray-300 z-10">
        <span class="material-symbols-outlined text-[40px]">close</span>
      </button>
      <button @click="prevImage" class="absolute left-4 md:left-8 text-white hover:text-gray-300">
        <span class="material-symbols-outlined text-[48px]">chevron_left</span>
      </button>
      <img
        :src="getImageUrl(images[currentImageIndex]?.url)"
        class="max-w-[90vw] max-h-[85vh] object-contain rounded-lg"
        :alt="'Ảnh ' + (currentImageIndex + 1)"
      />
      <button @click="nextImage" class="absolute right-4 md:right-8 text-white hover:text-gray-300">
        <span class="material-symbols-outlined text-[48px]">chevron_right</span>
      </button>
      <div class="absolute bottom-6 left-1/2 -translate-x-1/2 text-white text-sm bg-black/50 px-4 py-2 rounded-full">
        {{ currentImageIndex + 1 }} / {{ images.length }}
      </div>
    </div>
  </div>
</template>

<script>
import api from '@/axios/config';

export default {
  name: 'PropertyDetail',
  data() {
    return {
      loading: true,
      error: null,
      property: null,
      similarProperties: [],
      form: { ho_ten: '', so_dien_thoai: '', tin_nhan: '' },
      showGallery: false,
      currentImageIndex: 0,
      showAllFeatures: false,
      favoriteIds: [],
      toast: { visible: false, message: '', type: 'warning', icon: null, timer: null }
    };
  },
  computed: {
    images() { return this.property?.hinhAnh || this.property?.hinh_anh || []; },
    mainImage() { return this.images.length > 0 ? this.getImageUrl(this.images[0].url) : 'https://via.placeholder.com/1200x800?text=No+Image'; },
    displayImages() { return this.images.slice(1, 3); },
    remainingImages() { const r = this.images.length - 3; return r > 0 ? r : 0; },
    featuresList() {
      const f = this.property?.tien_ich || this.property?.tien_nghi || '';
      return f ? f.split(',').map(x => x.trim()).filter(x => x) : [];
    },
    isPropertyFavorite() { return this.favoriteIds.includes(this.property?.id); },
    
    // 🔥 BROKER COMPUTED - CHUẨN FINAL: Đọc trực tiếp từ property.moiGioi
    broker() {
      return this.property?.moi_gioi || null;
    },
    brokerId() {
      return this.property?.moi_gioi?.id || null;
    },
    brokerName() {
      return this.property?.moi_gioi?.ten || 'Đang cập nhật';
    }
  },
  async mounted() {
    const id = this.$route.params.id;
    if (id) {
      await this.loadPropertyDetail(id);
      if (this.isAuthenticated()) await this.syncFavoriteList();
    } else {
      this.error = 'Không tìm thấy ID bất động sản';
      this.loading = false;
    }
    window.addEventListener('favorite-updated', this.handleFavoriteUpdated);
    
    // 🧪 DEBUG: Log data để kiểm tra moiGioi có trong response không
    console.log('🔥 DATA:', this.property);
  },
  beforeUnmount() {
    document.body.style.overflow = '';
    if (this.toast.timer) clearTimeout(this.toast.timer);
    window.removeEventListener('favorite-updated', this.handleFavoriteUpdated);
  },
  methods: {
    getToastClass(type) {
      const c = {
        success: 'bg-gradient-to-r from-emerald-500 to-teal-500',
        error: 'bg-gradient-to-r from-red-500 to-rose-500',
        warning: 'bg-gradient-to-r from-amber-500 to-orange-500',
        'favorite-add': 'bg-gradient-to-r from-pink-500 via-rose-500 to-red-500',
        'favorite-remove': 'bg-gradient-to-r from-gray-500 via-slate-500 to-gray-600'
      };
      return c[type] || c.warning;
    },

    getToastIcon(type) {
      const i = { success: 'check_circle', error: 'error', warning: 'warning', 'favorite-add': 'favorite', 'favorite-remove': 'heart_broken' };
      return i[type] || i.warning;
    },
    showToast(msg, type = 'warning', duration = 2500) {
      if (this.toast.timer) clearTimeout(this.toast.timer);
      this.toast = { visible: true, message: msg, type, icon: null, timer: setTimeout(() => this.hideToast(), duration) };
    },
    showFavoriteToast(action, propertyName) {
      const shortName = propertyName.length > 30 ? propertyName.substring(0, 27) + '...' : propertyName;
      if (action === 'add') {
        this.toast = { visible: true, message: `❤️ Đã lưu "${shortName}" vào yêu thích`, type: 'favorite-add', icon: 'favorite', timer: setTimeout(() => this.hideToast(), 3000) };
      } else {
        this.toast = { visible: true, message: `💔 Đã xóa "${shortName}" khỏi yêu thích`, type: 'favorite-remove', icon: 'heart_broken', timer: setTimeout(() => this.hideToast(), 2500) };
      }
    },
    hideToast() { this.toast.visible = false; if (this.toast.timer) { clearTimeout(this.toast.timer); this.toast.timer = null; } },
    isAuthenticated() { return !!(localStorage.getItem('auth_token') && localStorage.getItem('user_type') === 'khach-hang'); },
    requireAuth(redirectUrl = null) {
      if (!this.isAuthenticated()) {
        this.showToast('Vui lòng đăng nhập để tiếp tục', 'warning');
        setTimeout(() => this.$router.push({ path: '/khach-hang/dang-nhap', query: { redirect: redirectUrl || this.$route.fullPath } }), 800);
        return false;
      }
      return true;
    },
    async toggleFavorite(bdsId, ev) {
      ev?.stopPropagation();
      if (!this.requireAuth()) return;
      const token = localStorage.getItem('auth_token');
      const wasFavorite = this.isPropertyFavorite;
      const action = wasFavorite ? 'remove' : 'add';
      const propertyName = this.property?.tieu_de || 'Bất động sản';
      try {
        await api.post('/khach-hang/bds/yeu-thich', { bds_id: bdsId });
        await this.syncFavoriteList();
        this.showFavoriteToast(action, propertyName);
        window.dispatchEvent(new Event('favorite-updated'));
      } catch (err) {
        console.error('Favorite API error:', err.response?.data || err);
        this.showToast('Có lỗi xảy ra, vui lòng thử lại', 'error');
      }
    },
    async syncFavoriteList() {
      try {
        const token = localStorage.getItem('auth_token');
        if (!token) { this.favoriteIds = []; return; }
        const res = await api.get('/khach-hang/bds/yeu-thich/data');
        const favorites = res.data?.data || [];
        this.favoriteIds = favorites.map(item => item.bat_dong_san_id || item.bds_id || item.batDongSan?.id || item.id).filter(id => id);
      } catch (err) { console.error('Sync favorite error:', err); this.favoriteIds = []; }
    },
    async handleFavoriteUpdated() { if (this.isAuthenticated()) await this.syncFavoriteList(); },
    
    // 🔥 LOAD PROPERTY - KHÔNG FETCH BROKER THỪA (BE ĐÃ TRẢ SẴN)
    async loadPropertyDetail(id) {
      this.loading = true;
      this.error = null;
      try {
        const res = await api.get(`/client/bat-dong-san/${id}`);
        if (res.data.status && res.data.data) {
          this.property = res.data.data;
          // ✅ BE đã with('moiGioi') → KHÔNG CẦN fetch thêm
          this.loadSimilarProperties(id);
        } else {
          this.error = res.data.message || 'Không tìm thấy bất động sản';
        }
      } catch (err) {
        console.error('Lỗi load chi tiết:', err);
        this.error = 'Có lỗi xảy ra khi tải thông tin. Vui lòng thử lại.';
      } finally {
        this.loading = false;
      }
    },
    
    async loadSimilarProperties(currentId) {
      try {
        const res = await api.get('/client/bat-dong-san');
        if (res.data.status) {
          this.similarProperties = res.data.data.data.filter(item => item.id !== currentId).slice(0, 3);
        }
      } catch (err) { console.error('Lỗi load BĐS tương tự:', err); }
    },
    
    getImageUrl(url) {
      if (!url) return 'https://via.placeholder.com/800x600?text=No+Image';
      if (url.startsWith('http')) return url;
      const base = import.meta.env.VITE_API_URL?.replace('/api','') || 'http://localhost:8000';
      return `${base}/storage/${url}`;
    },
    formatPriceFull(gia) { if (!gia && gia !== 0) return 'Liên hệ'; return new Intl.NumberFormat('vi-VN').format(gia) + ' VNĐ'; },
    getInitials(name) { if (!name || name === 'Đang cập nhật') return 'AC'; return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2); },
    viewProperty(id) { this.$router.push(`/chi-tiet-bds/${id}`); window.scrollTo(0, 0); },
    openGallery(index) { this.currentImageIndex = index; this.showGallery = true; document.body.style.overflow = 'hidden'; },
    closeGallery() { this.showGallery = false; document.body.style.overflow = ''; },
    nextImage() { this.currentImageIndex = (this.currentImageIndex + 1) % this.images.length; },
    prevImage() { this.currentImageIndex = (this.currentImageIndex - 1 + this.images.length) % this.images.length; },
    
    async submitForm() {
      if (!this.form.ho_ten || !this.form.so_dien_thoai) { this.showToast('Vui lòng nhập họ tên và số điện thoại', 'warning'); return; }
      try {
        console.log('Form submitted:', { ...this.form, bds_id: this.property?.id });
        this.showToast('Đăng ký tư vấn thành công! Chúng tôi sẽ liên hệ bạn sớm.', 'success');
        this.form = { ho_ten: '', so_dien_thoai: '', tin_nhan: '' };
      } catch (err) { console.error('Lỗi submit:', err); this.showToast('Có lỗi xảy ra. Vui lòng thử lại.', 'error'); }
    },

    // 🔥 CHAT VỚI MÔI GIỚI - CHUẨN FINAL
    async startChat() {
      if (!this.requireAuth()) return;
      if (!this.brokerId) { this.showToast("Chưa có môi giới", "warning"); return; }
      const token = localStorage.getItem("auth_token");
      try {
        const res = await api.post("/khach-hang/chat/start", { moi_gioi_id: this.brokerId, bat_dong_san_id: this.property.id });
        const conversation = res.data.data;
        window.dispatchEvent(new CustomEvent("open-chat", {
          detail: {
            conversationId: conversation.id,
            brokerId: this.brokerId,
            brokerName: this.brokerName,
            propertyName: this.property.tieu_de,
            propertyId: this.property.id
          }
        }));
        this.showToast(`Đang kết nối với ${this.brokerName}...`, 'success', 2000);
      } catch (err) {
        console.error('Chat start error:', err.response?.data || err);
        if (err.response?.status === 401) this.showToast('Phiên đăng nhập hết hạn', 'error');
        else if (err.response?.status === 404) this.showToast('Không tìm thấy môi giới', 'error');
        else this.showToast('Không thể mở chat, vui lòng thử lại', 'error');
      }
    }
  }
};
</script>

<style scoped>
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.toast-slide-enter-active { transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }
.toast-slide-leave-active { transition: all 0.2s ease-in; }
.toast-slide-enter-from { opacity: 0; transform: translateX(120%) scale(0.9); }
.toast-slide-leave-to { opacity: 0; transform: translateX(120%) scale(0.95); }
@keyframes heartPulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.25); } }
.animate-heart-pulse { animation: heartPulse 0.6s ease-in-out; }
.bg-gradient-to-r.from-pink-500 { box-shadow: 0 8px 32px rgba(236, 72, 153, 0.4); }
@media(prefers-reduced-motion:reduce){ *,*::before,*::after{ animation-duration:0.01ms!important; transition-duration:0.01ms!important; } .animate-heart-pulse { animation: none !important; } }
button:disabled { opacity: 0.5; cursor: not-allowed; }
button:disabled:hover { background: transparent; border-color: #e5e7eb; color: #9ca3af; }
</style>