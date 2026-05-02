<template>
  <div class="luxury-detail-page bg-[#fcfdfe] min-h-screen">
    <!-- Premium Loading State -->
    <div v-if="loading" class="flex flex-col items-center justify-center min-h-screen bg-white">
      <div class="relative w-24 h-24">
        <div class="absolute inset-0 border-4 border-blue-50/50 rounded-full"></div>
        <div class="absolute inset-0 border-4 border-blue-600 rounded-full border-t-transparent animate-spin"></div>
        <div class="absolute inset-0 flex items-center justify-center">
          <span class="material-symbols-outlined text-blue-600 animate-pulse text-3xl">home</span>
        </div>
      </div>
      <p class="mt-6 text-[#0a0e27] font-['Be_Vietnam_Pro'] font-bold tracking-widest uppercase text-xs animate-pulse">IzooBee Luxury</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="flex items-center justify-center min-h-screen p-6">
      <div class="glass-card max-w-md w-full p-10 text-center rounded-[32px] border border-gray-100 shadow-2xl">
        <div class="w-20 h-20 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-6">
          <span class="material-symbols-outlined text-4xl text-red-500">search_off</span>
        </div>
        <h2 class="text-2xl font-bold text-[#0a0e27] mb-4">Tuyệt phẩm này đã được sở hữu</h2>
        <p class="text-gray-500 mb-8 leading-relaxed">{{ error }}</p>
        <router-link to="/khach-hang/danh-sach-bat-dong-san" 
          class="inline-flex items-center gap-2 px-8 py-4 bg-[#0a0e27] text-white rounded-2xl font-bold hover:-translate-y-1 transition-all shadow-xl shadow-blue-900/20">
          Khám phá bộ sưu tập khác
        </router-link>
      </div>
    </div>

    <!-- Main Content -->
    <template v-else-if="property">
      <!-- 👑 LUXURY HERO GALLERY (BENTO GRID) -->
      <section class="container mx-auto max-w-[1400px] px-4 pt-6 pb-10">
        <!-- Breadcrumb Float -->
        <div class="flex items-center gap-2 mb-6 px-2 overflow-x-auto no-scrollbar whitespace-nowrap">
          <router-link to="/" class="text-xs font-bold text-gray-400 hover:text-blue-600 uppercase tracking-widest transition-colors">Trang chủ</router-link>
          <span class="text-gray-300">/</span>
          <router-link to="/khach-hang/danh-sach-bat-dong-san" class="text-xs font-bold text-gray-400 hover:text-blue-600 uppercase tracking-widest transition-colors">Bất động sản</router-link>
          <span class="text-gray-300">/</span>
          <span class="text-xs font-bold text-blue-600 uppercase tracking-widest">{{ property.loai?.ten_loai || 'Chi tiết' }}</span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 lg:grid-rows-2 gap-3 h-[400px] md:h-[600px] lg:h-[750px]">
          <!-- Big Featured Image -->
          <div class="lg:col-span-2 lg:row-span-2 relative rounded-[24px] md:rounded-[40px] overflow-hidden group cursor-zoom-in" @click="openGallery(0)">
            <img :src="mainImage" class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110" alt="Main View">
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            
            <!-- Favorite Floating -->
            <button @click.stop="toggleFavorite(property.id, $event)" 
              class="absolute top-6 right-6 w-12 h-12 rounded-full backdrop-blur-md flex items-center justify-center transition-all duration-300 hover:scale-110 active:scale-95 z-20 shadow-lg"
              :class="isPropertyFavorite ? 'bg-gradient-to-br from-pink-500 to-rose-500 shadow-rose-500/30' : 'bg-white/95 text-gray-800 hover:bg-white'">
              <span class="material-symbols-outlined text-2xl transition-all duration-300" 
                :style="{ fontVariationSettings: isPropertyFavorite ? `'FILL' 1` : `'FILL' 0` }"
                :class="isPropertyFavorite ? 'text-white' : 'text-gray-400 hover:text-rose-500'">
                favorite
              </span>
            </button>

            <!-- Price & Title Badge -->
            <div class="absolute bottom-10 left-10 right-10 z-10 pointer-events-none transform translate-y-4 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-500 hidden md:block">
              <h1 class="text-white text-3xl font-black font-['Be_Vietnam_Pro'] leading-tight mb-2 drop-shadow-lg">{{ property.tieu_de }}</h1>
              <p class="text-white/80 flex items-center gap-2"><span class="material-symbols-outlined text-sm">location_on</span> {{ property?.dia_chi?.quan?.ten }}, {{ property?.dia_chi?.tinh?.ten }}</p>
            </div>
          </div>

          <!-- Secondary Images -->
          <div v-for="(img, i) in displayImages" :key="i" 
            class="relative rounded-[24px] md:rounded-[40px] overflow-hidden group cursor-pointer hidden md:block" 
            @click="openGallery(i + 1)">
            <img :src="getImageUrl(img.url)" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" alt="Detail View">
            <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity"></div>
          </div>

          <!-- The "View All" Card -->
          <div class="relative rounded-[24px] md:rounded-[40px] overflow-hidden group cursor-pointer bg-blue-600 hidden lg:flex items-center justify-center text-white" 
            @click="openGallery(0)">
            <img v-if="images[3]" :src="getImageUrl(images[3].url)" class="absolute inset-0 w-full h-full object-cover opacity-40 transition-transform duration-700 group-hover:scale-110" alt="More View">
            <div class="relative z-10 text-center">
              <p class="text-4xl font-black mb-1">+{{ images.length }}</p>
              <p class="text-[10px] font-bold uppercase tracking-widest opacity-80">Khám phá album</p>
            </div>
          </div>
        </div>
      </section>

      <!-- 💎 MAIN CONTENT GRID -->
      <section class="container mx-auto max-w-[1400px] px-4 pb-24">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
          
          <!-- LEFT: Details & Features (8 cols) -->
          <div class="lg:col-span-8 space-y-12">
            
            <!-- Header Mobile Info -->
            <div class="lg:hidden mb-8">
              <h1 class="text-3xl font-black text-[#0a0e27] mb-4 leading-tight">{{ property.tieu_de }}</h1>
              <p class="text-3xl font-bold text-blue-600 mb-6">{{ formatPriceFull(property.gia) }}</p>
            </div>

            <!-- 🧊 Luxury Stats Bar -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
              <div class="glass-stat-card p-6 rounded-[32px] border border-gray-50 flex flex-col items-center text-center hover:bg-white hover:shadow-xl transition-all group">
                <div class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mb-4 group-hover:bg-blue-600 group-hover:text-white transition-colors duration-500">
                  <span class="material-symbols-outlined text-3xl">square_foot</span>
                </div>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1">Diện tích</p>
                <p class="text-xl font-black text-[#0a0e27]">{{ property.dien_tich }} m²</p>
              </div>
              <div class="glass-stat-card p-6 rounded-[32px] border border-gray-50 flex flex-col items-center text-center hover:bg-white hover:shadow-xl transition-all group">
                <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-4 group-hover:bg-emerald-600 group-hover:text-white transition-colors duration-500">
                  <span class="material-symbols-outlined text-3xl">bed</span>
                </div>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1">Phòng ngủ</p>
                <p class="text-xl font-black text-[#0a0e27]">{{ property.so_phong_ngu || '—' }}</p>
              </div>
              <div class="glass-stat-card p-6 rounded-[32px] border border-gray-50 flex flex-col items-center text-center hover:bg-white hover:shadow-xl transition-all group">
                <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mb-4 group-hover:bg-amber-600 group-hover:text-white transition-colors duration-500">
                  <span class="material-symbols-outlined text-3xl">bathtub</span>
                </div>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1">Phòng tắm</p>
                <p class="text-xl font-black text-[#0a0e27]">{{ property.so_phong_tam || '—' }}</p>
              </div>
              <div class="glass-stat-card p-6 rounded-[32px] border border-gray-50 flex flex-col items-center text-center hover:bg-white hover:shadow-xl transition-all group">
                <div class="w-14 h-14 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center mb-4 group-hover:bg-rose-600 group-hover:text-white transition-colors duration-500">
                  <span class="material-symbols-outlined text-3xl">compass_calibration</span>
                </div>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1">Trạng thái</p>
                <p class="text-xl font-black" :class="property.trang_thai_id === 3 ? 'text-amber-600' : 'text-[#0a0e27]'">
                  {{ property.trang_thai?.ten_trang_thai || 'Đang bán' }}
                </p>
              </div>
            </div>

            <!-- 📝 Description & Amenities -->
            <div class="space-y-12 bg-white rounded-[48px] p-8 md:p-12 border border-gray-50 shadow-sm">
              <div class="description-section">
                <div class="flex items-center gap-4 mb-8">
                  <div class="w-10 h-10 rounded-full bg-[#0a0e27] text-white flex items-center justify-center">
                    <span class="material-symbols-outlined text-xl">description</span>
                  </div>
                  <h2 class="text-2xl font-black font-['Be_Vietnam_Pro'] text-[#0a0e27]">Đặc điểm bất động sản</h2>
                </div>
                <div class="prose prose-blue max-w-none text-gray-600 leading-[1.8] text-[16px] mb-10" v-html="property.mo_ta"></div>

                <!-- 📊 Detailed Specifications Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-12 pt-10 border-t border-gray-100">
                  <div class="flex justify-between items-center py-2 border-b border-gray-50">
                    <span class="text-gray-400 font-medium text-sm">Mã tin</span>
                    <span class="text-[#0a0e27] font-bold text-sm">#BDS-{{ property.id }}</span>
                  </div>
                  <div class="flex justify-between items-center py-2 border-b border-gray-50">
                    <span class="text-gray-400 font-medium text-sm">Loại tin đăng</span>
                    <span class="text-[#0a0e27] font-bold text-sm">Cần bán / Cho thuê</span>
                  </div>
                  <div class="flex justify-between items-center py-2 border-b border-gray-50">
                    <span class="text-gray-400 font-medium text-sm">Ngày đăng</span>
                    <span class="text-[#0a0e27] font-bold text-sm">{{ formatDate(property.created_at) }}</span>
                  </div>
                  <div class="flex justify-between items-center py-2 border-b border-gray-50">
                    <span class="text-gray-400 font-medium text-sm">Loại BĐS</span>
                    <span class="text-[#0a0e27] font-bold text-sm">{{ property.loai?.ten_loai || 'Chung cư' }}</span>
                  </div>
                  <div class="flex justify-between items-center py-2 border-b border-gray-50">
                    <span class="text-gray-400 font-medium text-sm">Pháp lý</span>
                    <span class="text-emerald-600 font-bold text-sm">Sổ hồng / Sổ đỏ</span>
                  </div>
                  <div class="flex justify-between items-center py-2 border-b border-gray-50">
                    <span class="text-gray-400 font-medium text-sm">Nội thất</span>
                    <span class="text-[#0a0e27] font-bold text-sm">Bàn giao cơ bản</span>
                  </div>
                  <div class="flex justify-between items-center py-2 border-b border-gray-50">
                    <span class="text-gray-400 font-medium text-sm">Khu vực</span>
                    <span class="text-[#0a0e27] font-bold text-sm">{{ property.dia_chi?.quan?.ten }}, {{ property.dia_chi?.tinh?.ten }}</span>
                  </div>
                  <div class="flex justify-between items-center py-2 border-b border-gray-50">
                    <span class="text-gray-400 font-medium text-sm">Địa chỉ</span>
                    <span class="text-[#0a0e27] font-bold text-sm text-right max-w-[200px] line-clamp-1">{{ property.dia_chi?.dia_chi_chi_tiet }}</span>
                  </div>
                </div>
              </div>

              <div v-if="featuresList.length > 0" class="amenities-section pt-10 border-t border-gray-100">
                <h3 class="text-xl font-black font-['Be_Vietnam_Pro'] text-[#0a0e27] mb-8">Tiện ích đỉnh cao</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
                  <div v-for="(feat, i) in featuresList" :key="i" class="flex items-center gap-4 group">
                    <div class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition-all">
                      <span class="material-symbols-outlined text-[14px]">done</span>
                    </div>
                    <span class="text-gray-700 font-medium text-[15px]">{{ feat }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- 📍 Map Luxury Wrapper -->
            <div class="relative rounded-[48px] overflow-hidden shadow-2xl border-8 border-white group h-[500px]">
              <div id="propertyMap" class="w-full h-full grayscale-[0.2] brightness-[0.95]"></div>
              <div class="absolute inset-0 pointer-events-none shadow-[inset_0_0_100px_rgba(0,0,0,0.1)]"></div>
              <div class="absolute top-8 left-8 glass-card py-3 px-6 rounded-2xl flex items-center gap-3 z-[1000]">
                <span class="w-3 h-3 bg-red-500 rounded-full animate-ping"></span>
                <p class="text-sm font-bold text-[#0a0e27]">Vị trí chính xác dự án</p>
              </div>
            </div>
          </div>

          <!-- RIGHT: Agent & Actions (4 cols) -->
          <div class="lg:col-span-4 space-y-8">
            
            <!-- Price Sticky Card (Desktop Only) -->
            <div class="hidden lg:block bg-[#0a0e27] rounded-[40px] p-10 text-white shadow-2xl shadow-blue-900/40 relative overflow-hidden group">
              <div class="absolute top-0 right-0 w-32 h-32 bg-blue-600/20 rounded-full -translate-y-1/2 translate-x-1/2 blur-3xl"></div>
              <p class="text-[11px] font-bold uppercase tracking-[0.2em] opacity-60 mb-3">Giá trị tài sản</p>
              <h2 class="text-4xl font-black font-['Be_Vietnam_Pro'] mb-2">{{ formatPriceFull(property.gia) }}</h2>
              <p class="text-blue-400 font-bold mb-8">~ {{ (property.gia / (property.dien_tich || 1)).toLocaleString() }} đ/m²</p>
              <div class="flex gap-2">
                <span class="px-3 py-1.5 rounded-full bg-white/10 text-[10px] font-bold uppercase tracking-widest border border-white/5">Sẵn sàng bàn giao</span>
                <span class="px-3 py-1.5 rounded-full bg-white/10 text-[10px] font-bold uppercase tracking-widest border border-white/5">Pháp lý minh bạch</span>
              </div>
            </div>

            <!-- Professional Agent Card -->
            <div class="bg-white rounded-[40px] p-8 md:p-10 border border-gray-100 shadow-xl relative sticky top-10">
              <div class="text-center mb-10">
                <div class="relative w-28 h-28 mx-auto mb-6 p-1.5 rounded-full bg-gradient-to-tr from-blue-600 via-indigo-400 to-emerald-400">
                  <div class="w-full h-full rounded-full bg-white flex items-center justify-center overflow-hidden">
                    <img v-if="broker?.avatar" :src="getImageUrl(broker.avatar)" class="w-full h-full object-cover" alt="Agent">
                    <span v-else class="text-3xl font-black text-blue-600 uppercase">{{ getInitials(brokerName) }}</span>
                  </div>
                  <div class="absolute bottom-1 right-1 w-7 h-7 bg-emerald-500 border-4 border-white rounded-full"></div>
                </div>
                <h3 class="text-2xl font-black font-['Be_Vietnam_Pro'] text-[#0a0e27] mb-1">{{ brokerName }}</h3>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Chuyên gia tư vấn cao cấp</p>
              </div>

              <div class="space-y-4">
                <a :href="'tel:' + (broker?.so_dien_thoai || '')" 
                  class="flex items-center justify-center gap-3 w-full py-5 bg-blue-600 hover:bg-blue-700 text-white rounded-[20px] font-black transition-all shadow-lg shadow-blue-600/30 hover:-translate-y-1 active:scale-95 group">
                  <span class="material-symbols-outlined group-hover:rotate-12 transition-transform">call</span>
                  {{ broker?.so_dien_thoai || 'Gọi ngay' }}
                </a>
                
                <button @click="startChat" 
                  class="flex items-center justify-center gap-3 w-full py-5 bg-white border-2 border-gray-100 hover:border-blue-600 text-[#0a0e27] hover:text-blue-600 rounded-[20px] font-black transition-all hover:-translate-y-1 active:scale-95 group">
                  <span class="material-symbols-outlined group-hover:translate-x-1 transition-transform">forum</span>
                  Nhắn tin tư vấn
                </button>

                <button @click="openBookingModal"
                  class="flex items-center justify-center gap-3 w-full py-5 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white rounded-[20px] font-black transition-all shadow-lg shadow-emerald-500/30 hover:-translate-y-1 active:scale-95 group">
                  <span class="material-symbols-outlined group-hover:rotate-12 transition-transform">calendar_month</span>
                  Đặt lịch xem nhà
                </button>

                <div class="pt-6 border-t border-gray-50 flex items-center justify-around">
                  <div class="text-center">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Đánh giá</p>
                    <div class="flex gap-0.5 text-amber-400">
                      <span v-for="s in 5" :key="s" class="material-symbols-outlined text-[16px] fill-current">star</span>
                    </div>
                  </div>
                  <div class="w-px h-8 bg-gray-100"></div>
                  <div class="text-center">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Phản hồi</p>
                    <p class="font-bold text-[#0a0e27]">~10 phút</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- 🌟 RELATED COLLECTION -->
      <section class="bg-[#f8fafc] py-24 border-t border-gray-50">
        <div class="container mx-auto max-w-[1400px] px-4">
          <div class="flex flex-col md:flex-row justify-between items-end mb-16 gap-6">
            <div class="max-w-xl">
              <p class="text-blue-600 font-bold uppercase tracking-[0.3em] text-[10px] mb-4">Có thể bạn quan tâm</p>
              <h2 class="text-4xl font-black font-['Be_Vietnam_Pro'] text-[#0a0e27] leading-tight">Tuyệt phẩm tương tự trong khu vực</h2>
            </div>
            <router-link to="/khach-hang/danh-sach-bat-dong-san" class="px-8 py-4 bg-white border border-gray-100 rounded-2xl font-bold text-sm hover:shadow-lg transition-all flex items-center gap-2">
              Xem tất cả bộ sưu tập <span class="material-symbols-outlined">east</span>
            </router-link>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
            <div v-for="bds in similarProperties" :key="bds.id" @click="viewProperty(bds.id)"
              class="luxury-card group cursor-pointer bg-white rounded-[40px] overflow-hidden border border-gray-50 shadow-sm hover:shadow-2xl transition-all duration-700 hover:-translate-y-4">
              <div class="relative h-[320px] overflow-hidden">
                <img :src="bds.image" class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110" :alt="bds.tieu_de">
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="absolute top-6 left-6 px-4 py-2 bg-white/90 backdrop-blur-md rounded-full text-[10px] font-black uppercase tracking-widest text-[#0a0e27]">{{ bds.loai?.ten_loai || 'BĐS' }}</div>
                <div class="absolute bottom-6 left-6 right-6 text-white transform translate-y-4 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-500">
                  <p class="font-bold flex items-center gap-2 text-sm mb-1"><span class="material-symbols-outlined text-sm">location_on</span> {{ bds.location }}</p>
                </div>
              </div>
              <div class="p-8">
                <h3 class="text-xl font-black font-['Be_Vietnam_Pro'] text-[#0a0e27] mb-6 line-clamp-2 leading-relaxed group-hover:text-blue-600 transition-colors">{{ bds.tieu_de }}</h3>
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Giá chỉ từ</p>
                    <p class="text-2xl font-black text-[#0a0e27]">{{ formatPriceFull(bds.gia) }}</p>
                  </div>
                  <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition-all duration-500">
                    <span class="material-symbols-outlined">arrow_outward</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </template>

    <!-- 🍞 LUXURY TOAST -->
    <transition name="luxury-toast">
      <div v-if="toast.visible" class="fixed bottom-10 right-10 z-[10000]">
        <div class="glass-toast flex items-center gap-4 px-6 py-5 rounded-[24px] shadow-2xl border border-white/20 min-w-[320px]"
          :class="getToastClass(toast.type)">
          <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center backdrop-blur-md">
            <span class="material-symbols-outlined text-white text-xl">{{ toast.icon || getToastIcon(toast.type) }}</span>
          </div>
          <p class="text-white font-bold text-sm leading-tight flex-1">{{ toast.message }}</p>
          <button @click="hideToast" class="text-white/60 hover:text-white transition-colors">
            <span class="material-symbols-outlined text-xl">close</span>
          </button>
        </div>
      </div>
    </transition>

    <!-- 🖼 FULLSCREEN GALLERY -->
    <transition name="gallery-fade">
      <div v-if="showGallery" class="fixed inset-0 z-[10001] bg-black/98 flex flex-col items-center justify-center backdrop-blur-2xl" @click.self="closeGallery">
        <div class="absolute top-8 right-8 flex gap-4">
          <button @click="closeGallery" class="w-12 h-12 rounded-full bg-white/10 text-white flex items-center justify-center hover:bg-white/20 transition-all active:scale-95">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>
        
        <div class="relative w-full max-w-6xl px-6 flex items-center justify-center">
          <button @click="prevImage" class="absolute left-0 w-16 h-16 rounded-full bg-white/5 text-white flex items-center justify-center hover:bg-white/10 transition-all z-20">
            <span class="material-symbols-outlined text-4xl">chevron_left</span>
          </button>
          
          <img :src="getImageUrl(images[currentImageIndex]?.url)" class="max-w-full max-h-[80vh] object-contain shadow-2xl rounded-lg" :alt="'Album ' + (currentImageIndex + 1)">
          
          <button @click="nextImage" class="absolute right-0 w-16 h-16 rounded-full bg-white/5 text-white flex items-center justify-center hover:bg-white/10 transition-all z-20">
            <span class="material-symbols-outlined text-4xl">chevron_right</span>
          </button>
        </div>

        <div class="mt-10 flex flex-col items-center gap-4">
          <p class="text-white/60 font-black tracking-[0.4em] uppercase text-[10px]">Ảnh {{ currentImageIndex + 1 }} / {{ images.length }}</p>
          <div class="flex gap-2 max-w-2xl overflow-x-auto py-4 px-10 no-scrollbar">
            <div v-for="(img, idx) in images" :key="idx" 
              @click="currentImageIndex = idx"
              class="w-16 h-16 rounded-xl overflow-hidden cursor-pointer transition-all duration-300 flex-shrink-0"
              :class="currentImageIndex === idx ? 'ring-4 ring-blue-600 scale-110 opacity-100' : 'opacity-40 hover:opacity-100'">
              <img :src="getImageUrl(img.url)" class="w-full h-full object-cover">
            </div>
          </div>
        </div>
      </div>
    </transition>

    <!-- 📅 Booking Modal -->
    <DatLichModal
      v-if="showBookingModal"
      :propertyId="property?.id"
      :propertyTitle="property?.tieu_de"
      @close="showBookingModal = false"
      @success="onBookingSuccess"
    />
  </div>
</template>

<script>
import api from '@/axios/config';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import { saveToRecentlyViewed } from '@/js/recentlyViewed';
import DatLichModal from '@/components/KhachHang/LichHen/DatLichModal.vue';
import { createToaster } from '@meforma/vue-toaster';

const toaster = createToaster({ position: 'top-right', duration: 4000 });

export default {
  name: 'PropertyDetailLuxury',
  components: { DatLichModal },
  data() {
    return {
      loading: true,
      error: null,
      property: null,
      similarProperties: [],
      showGallery: false,
      currentImageIndex: 0,
      favoriteIds: [],
      toast: { visible: false, message: '', type: 'warning', icon: null, timer: null },
      map: null,
      showBookingModal: false
    };
  },
  watch: {
    '$route.params.id': {
      handler(newId, oldId) {
        if (newId && this.$route.name === 'KhachHangChiTietBatDongSan' && newId !== oldId) {
          this.loadPropertyDetail(newId);
          window.scrollTo({ top: 0, behavior: 'smooth' });
        }
      }
    }
  },
  computed: {
    images() { return this.property?.hinhAnh || this.property?.hinh_anh || []; },
    mainImage() { 
      if (this.property?.anh_dai_dien_url) return this.getImageUrl(this.property.anh_dai_dien_url);
      return this.images.length > 0 ? this.getImageUrl(this.images[0].url) : 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&q=80&w=1200'; 
    },
    displayImages() { return this.images.slice(1, 3); },
    featuresList() {
      const f = this.property?.tien_ich || this.property?.tien_nghi || '';
      return f ? f.split(',').map(x => x.trim()).filter(x => x) : [];
    },
    isPropertyFavorite() { return this.favoriteIds.map(Number).includes(Number(this.property?.id)); },
    broker() { return this.property?.moi_gioi || null; },
    brokerId() { return this.property?.moi_gioi?.id || null; },
    brokerName() { return this.property?.moi_gioi?.ten || 'Chuyên viên IzooBee'; }
  },
  async mounted() {
    const id = this.$route.params.id;
    if (id) {
      await this.loadPropertyDetail(id);
      if (this.isAuthenticated()) await this.syncFavoriteList();
    }
    window.addEventListener('favorite-updated', this.handleFavoriteUpdated);
  },
  beforeUnmount() {
    document.body.style.overflow = '';
    if (this.toast.timer) clearTimeout(this.toast.timer);
    window.removeEventListener('favorite-updated', this.handleFavoriteUpdated);
  },
  methods: {
    getToastClass(type) {
      const c = {
        success: 'bg-emerald-600',
        error: 'bg-rose-600',
        warning: 'bg-amber-600',
        'favorite-add': 'bg-pink-600',
        'favorite-remove': 'bg-slate-700'
      };
      return c[type] || c.warning;
    },
    getToastIcon(type) {
      const i = { success: 'verified', error: 'error', warning: 'warning', 'favorite-add': 'favorite', 'favorite-remove': 'heart_broken' };
      return i[type] || i.warning;
    },
    showToast(msg, type = 'warning', duration = 3000) {
      if (this.toast.timer) clearTimeout(this.toast.timer);
      this.toast = { visible: true, message: msg, type, icon: null, timer: setTimeout(() => this.hideToast(), duration) };
    },
    hideToast() { this.toast.visible = false; },
    isAuthenticated() { return !!(localStorage.getItem('khach_hang_auth_token')); },
    requireAuth() {
      if (!this.isAuthenticated()) {
        this.showToast('Vui lòng đăng nhập để tiếp tục trải nghiệm', 'warning');
        setTimeout(() => this.$router.push('/khach-hang/dang-nhap'), 1500);
        return false;
      }
      return true;
    },
    async toggleFavorite(bdsId, ev) {
      ev?.stopPropagation();
      if (!this.requireAuth()) return;
      const wasFavorite = this.isPropertyFavorite;
      try {
        await api.post('/khach-hang/bds/yeu-thich', { bds_id: bdsId });
        await this.syncFavoriteList();
        this.showToast(wasFavorite ? 'Đã xóa khỏi danh sách yêu thích' : 'Đã thêm vào danh sách yêu thích', wasFavorite ? 'favorite-remove' : 'favorite-add');
        window.dispatchEvent(new Event('favorite-updated'));
      } catch (err) { this.showToast('Không thể cập nhật yêu thích', 'error'); }
    },
    async syncFavoriteList() {
      try {
        const res = await api.get('/khach-hang/bds/yeu-thich/data');
        this.favoriteIds = (res.data?.data || []).map(item => item.bat_dong_san_id || item.bds_id || item.id);
      } catch (err) { this.favoriteIds = []; }
    },
    handleFavoriteUpdated() { if (this.isAuthenticated()) this.syncFavoriteList(); },
    async loadPropertyDetail(id) {
      this.loading = true;
      try {
        const res = await api.get(`/client/bat-dong-san/${id}`);
        if (res.data.status) {
          this.property = res.data.data;
          this.loadSimilarProperties(id);
          setTimeout(() => { this.initMap(); }, 500);

          // Lưu vào danh sách đã xem gần đây
          const p = res.data.data;
          const loc = p.dia_chi?.quan?.ten
            ? `${p.dia_chi.quan.ten}, ${p.dia_chi.tinh?.ten || ''}`
            : 'Đang cập nhật';
          saveToRecentlyViewed({
            id: p.id,
            name: p.tieu_de || 'Bất động sản',
            image: p.anh_dai_dien_url || (p.hinh_anh?.[0]?.url) || null,
            gia: p.gia,
            loai: p.loai?.ten_loai || 'BĐS',
            location: loc,
          });
        } else { this.error = 'Bất động sản không tồn tại'; }
      } catch (err) { this.error = 'Lỗi kết nối máy chủ'; } finally { this.loading = false; }
    },
    async loadSimilarProperties(currentId) {
      try {
        const res = await api.get('/client/bat-dong-san');
        if (res.data.status) {
          const raw = res.data.data?.data || res.data.data || [];
          this.similarProperties = raw.filter(item => item.id !== currentId).slice(0, 3).map(it => {
            let img = it.anh_dai_dien_url || (it.hinh_anh?.[0]?.url) || 'https://via.placeholder.com/800x600';
            let loc = it.dia_chi?.quan?.ten ? `${it.dia_chi.quan.ten}, ${it.dia_chi.tinh.ten}` : 'Đang cập nhật';
            return { ...it, image: this.getImageUrl(img), location: loc };
          });
        }
      } catch {}
    },
    getImageUrl(url) {
      if (!url) return 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&q=80&w=1200';
      if (url.startsWith('http')) return url;
      
      const base = import.meta.env.VITE_API_URL?.replace('/api','') || 'http://localhost:8000';
      const cleanUrl = url.startsWith('/') ? url.substring(1) : url;
      const finalUrl = cleanUrl.startsWith('storage/') ? cleanUrl : `storage/${cleanUrl}`;
      
      return `${base}/${finalUrl}`;
    },
    formatPriceFull(gia) { if (!gia && gia !== 0) return 'Liên hệ'; return new Intl.NumberFormat('vi-VN').format(gia) + ' VNĐ'; },
    getInitials(name) { return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2); },
    formatDate(dateStr) {
      if (!dateStr) return '—';
      const date = new Date(dateStr);
      return date.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' });
    },
    viewProperty(id) { this.$router.push(`/khach-hang/chi-tiet-bat-dong-san/${id}`); window.scrollTo({ top: 0, behavior: 'smooth' }); },
    openBookingModal() { this.showBookingModal = true; },
    onBookingSuccess() {
      this.showBookingModal = false;
      // Toast already shown in DatLichModal
    },
    initMap() {
      const lat = this.property?.dia_chi?.latitude || this.property?.dia_chi?.lat;
      const lng = this.property?.dia_chi?.longitude || this.property?.dia_chi?.lng;
      
      console.log('📍 Initializing Map with:', { lat, lng });
      
      if (!lat || !lng) {
        console.warn('Không có tọa độ bản đồ cho BĐS này');
        return;
      }

      if (this.map) {
        this.map.remove();
      }

      this.map = L.map('propertyMap', {
        zoomControl: false,
        scrollWheelZoom: false
      }).setView([lat, lng], 15);

      L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap contributors'
      }).addTo(this.map);

      // Custom Icon
      const customIcon = L.divIcon({
        className: 'custom-marker',
        html: `<div class="marker-pin"></div><div class="marker-pulse"></div>`,
        iconSize: [30, 30],
        iconAnchor: [15, 15]
      });

      L.marker([lat, lng], { icon: customIcon }).addTo(this.map);

      // Quan trọng: Refresh lại size bản đồ
      setTimeout(() => {
        if (this.map) this.map.invalidateSize();
      }, 100);
    },

    openGallery(index) { this.currentImageIndex = index; this.showGallery = true; document.body.style.overflow = 'hidden'; },
    closeGallery() { this.showGallery = false; document.body.style.overflow = ''; },
    nextImage() { this.currentImageIndex = (this.currentImageIndex + 1) % this.images.length; },
    prevImage() { this.currentImageIndex = (this.currentImageIndex - 1 + this.images.length) % this.images.length; },
    async startChat() {
      if (!this.requireAuth()) return;
      try {
        const res = await api.post("/khach-hang/chat/start", { moi_gioi_id: this.brokerId, bat_dong_san_id: this.property.id });
        window.dispatchEvent(new CustomEvent("open-chat", {
          detail: { conversationId: res.data.data.id, brokerId: this.brokerId, brokerName: this.brokerName, propertyName: this.property.tieu_de, propertyId: this.property.id }
        }));
      } catch (err) { this.showToast('Lỗi kết nối chat', 'error'); }
    }
  }
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@100;300;400;500;700;900&display=swap');

.luxury-detail-page {
  font-family: 'Be Vietnam Pro', sans-serif;
  letter-spacing: -0.02em;
}

.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

.glass-card {
  background: rgba(255, 255, 255, 0.85);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
}

.glass-stat-card {
  background: rgba(255, 255, 255, 0.6);
  backdrop-filter: blur(10px);
}

.luxury-card {
  transition: all 0.7s cubic-bezier(0.16, 1, 0.3, 1);
}

.glass-toast {
  background: rgba(15, 23, 42, 0.9);
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

/* Animations */
.luxury-toast-enter-active { animation: toastIn 0.5s cubic-bezier(0.16, 1, 0.3, 1); }
.luxury-toast-leave-active { animation: toastOut 0.3s ease-in; }

@keyframes toastIn {
  from { opacity: 0; transform: translateY(40px) scale(0.9); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}
@keyframes toastOut {
  to { opacity: 0; transform: scale(0.9); }
}

.gallery-fade-enter-active, .gallery-fade-leave-active { transition: opacity 0.5s; }
.gallery-fade-enter-from, .gallery-fade-leave-to { opacity: 0; }

.prose :deep(p) { margin-bottom: 1.5rem; }
.prose :deep(strong) { color: #0a0e27; font-weight: 800; }

/* Custom Map Marker */
:deep(.custom-marker) {
  display: flex;
  align-items: center;
  justify-content: center;
}

:deep(.marker-pin) {
  width: 14px;
  height: 14px;
  background: #2563eb;
  border: 3px solid white;
  border-radius: 50%;
  box-shadow: 0 0 10px rgba(37, 99, 235, 0.5);
  z-index: 2;
}

:deep(.marker-pulse) {
  position: absolute;
  width: 40px;
  height: 40px;
  background: rgba(37, 99, 235, 0.2);
  border-radius: 50%;
  animation: marker-pulse 2s infinite;
  z-index: 1;
}

@keyframes marker-pulse {
  0% { transform: scale(0.5); opacity: 1; }
  100% { transform: scale(2.5); opacity: 0; }
}

@media (max-width: 1024px) {
  .luxury-hero-gallery { height: auto; }
}

.animate-heart-pulse { animation: heartPulse 0.6s ease-in-out; }
.bg-gradient-to-r.from-pink-500 { box-shadow: 0 8px 32px rgba(236, 72, 153, 0.4); }
@media(prefers-reduced-motion:reduce){ 
  *,*::before,*::after{ animation-duration:0.01ms!important; transition-duration:0.01ms!important; } 
  .animate-heart-pulse { animation: none !important; } 
}
button:disabled { opacity: 0.5; cursor: not-allowed; }
button:disabled:hover { background: transparent; border-color: #e5e7eb; color: #9ca3af; }
</style>