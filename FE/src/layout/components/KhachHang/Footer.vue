<template>
  <footer class="bg-[#0a0e27] text-white pt-20 pb-8 relative overflow-hidden">
    <!-- Top Gradient Line -->
    <div
      class="absolute top-0 left-0 w-full h-px bg-gradient-to-r from-transparent via-blue-500/40 to-transparent"
    ></div>
    <!-- Background Glow -->
    <div
      class="absolute -top-40 -right-40 w-[500px] h-[500px] bg-blue-600/5 rounded-full blur-3xl pointer-events-none"
    ></div>

    <div class="container mx-auto max-w-7xl px-6 relative z-10">
      <!-- Main Grid -->
      <div
        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 lg:gap-12 mb-16"
      >
        <!-- Brand -->
        <div class="lg:col-span-1">
          <h3
            class="font-['Be_Vietnam_Pro'] text-2xl font-bold text-white mb-4 tracking-tight"
          >
            Architectural Curator
          </h3>
          <p class="text-gray-400 text-[14px] leading-relaxed mb-6 max-w-xs">
            Đơn vị hàng đầu trong tư vấn và phân phối bất động sản cao cấp, kiến
            tạo giá trị sống thượng lưu cho mọi gia đình Việt.
          </p>
          <div class="flex gap-3">
            <a
              href="https://www.facebook.com/vuhoang.mbp"
              class="w-10 h-10 bg-white/5 hover:bg-[#1877F2]/20 border border-white/10 hover:border-[#1877F2]/30 rounded-full flex items-center justify-center text-gray-400 hover:text-[#1877F2] transition-all duration-300 group"
            >
              <i
                class="fab fa-facebook-f text-[16px] group-hover:scale-110 transition-transform"
              ></i>
            </a>

            <a
              href="#"
              class="w-10 h-10 bg-white/5 hover:bg-[#E4405F]/20 border border-white/10 hover:border-[#E4405F]/30 rounded-full flex items-center justify-center text-gray-400 hover:text-[#E4405F] transition-all duration-300 group"
            >
              <i
                class="fab fa-instagram text-[16px] group-hover:scale-110 transition-transform"
              ></i>
            </a>

            <a
              href="#"
              class="w-10 h-10 bg-white/5 hover:bg-blue-400/20 border border-white/10 hover:border-blue-400/30 rounded-full flex items-center justify-center text-gray-400 hover:text-blue-400 transition-all duration-300 group"
            >
              <i
                class="far fa-envelope text-[16px] group-hover:scale-110 transition-transform"
              ></i>
            </a>
          </div>
        </div>

        <!-- Services -->
        <div>
          <h4
            class="font-['Be_Vietnam_Pro'] font-semibold text-xs text-gray-300 uppercase tracking-[0.15em] mb-6"
          >
            Dịch vụ
          </h4>
          <ul class="space-y-3">
            <li v-for="(item, i) in services" :key="i">
              <a
                href="#"
                class="text-sm text-gray-400 hover:text-white hover:translate-x-1 transition-all duration-300 flex items-center gap-2.5 group"
              >
                <span
                  class="bg-gray-600 rounded-full group-hover:bg-blue-400 transition-colors"
                ></span>
                {{ item }}
              </a>
            </li>
          </ul>
        </div>

        <!-- Support -->
        <div>
          <h4
            class="font-['Be_Vietnam_Pro'] font-semibold text-xs text-gray-300 uppercase tracking-[0.15em] mb-6"
          >
            Hỗ trợ
          </h4>
          <ul class="space-y-3">
            <li v-for="(item, i) in support" :key="i">
              <a
                href="#"
                class="text-sm text-gray-400 hover:text-white hover:translate-x-1 transition-all duration-300 flex items-center gap-2.5 group"
              >
                <span
                  class="bg-gray-600 rounded-full group-hover:bg-blue-400 transition-colors"
                ></span>
                {{ item }}
              </a>
            </li>
          </ul>
        </div>

        <!-- Contact -->
        <div>
          <h4
            class="font-['Be_Vietnam_Pro'] font-semibold text-xs text-gray-300 uppercase tracking-[0.15em] mb-6"
          >
            Liên hệ
          </h4>
          <div class="space-y-4">
            <div
              class="flex items-start gap-3 text-gray-400 hover:text-white transition-colors cursor-pointer group"
            >
              <span
                class="material-symbols-outlined text-[20px] mt-0.5 text-blue-400/60 group-hover:text-blue-400"
                >location_on</span
              >
              <span class="text-sm leading-relaxed"
                >3 Quang Trung, Hải Châu, Đà Nẵng 550000, Việt Nam</span
              >
            </div>
            <div
              class="flex items-center gap-3 text-gray-400 hover:text-white transition-colors cursor-pointer group"
            >
              <span
                class="material-symbols-outlined text-[20px] text-blue-400/60 group-hover:text-blue-400"
                >call</span
              >
              <span class="text-sm font-medium tracking-wide">1800 0000</span>
            </div>
            <div
              class="flex items-center gap-3 text-gray-400 hover:text-white transition-colors cursor-pointer group"
            >
              <span
                class="material-symbols-outlined text-[20px] text-blue-400/60 group-hover:text-blue-400"
                >mail</span
              >
              <span class="text-sm">nhom25@architecturalcurator.vn</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Divider -->
      <div class="h-px bg-white/10 mb-8"></div>

      <!-- Bottom Bar -->
      <div
        class="flex flex-col md:flex-row justify-between items-center gap-4 text-xs text-gray-500"
      >
        <p>© {{ new Date().getFullYear() }} Architectural Curator. Nhóm 25.</p>
        <div
          class="flex items-center gap-6 flex-wrap justify-center md:justify-end"
        >
          <a href="#" class="hover:text-gray-300 transition-colors"
            >Chính sách bảo mật</a
          >
          <a href="#" class="hover:text-gray-300 transition-colors"
            >Điều khoản sử dụng</a
          >
          <div class="flex items-center gap-2 pl-6 border-l border-white/10">
            <span class="material-symbols-outlined text-[16px]">language</span>
            <span>Tiếng Việt</span>
          </div>
        </div>
      </div>
    </div>
  </footer>
</template>

<script>
export default {
  name: "KhachHangFooter",
  data() {
    return {
      services: [
        "Mua bán bất động sản",
        "Cho thuê cao cấp",
        "Tư vấn đầu tư",
        "Định giá & Pháp lý",
      ],
      support: [
        "Trung tâm hỗ trợ",
        "Chính sách bảo mật",
        "Điều khoản dịch vụ",
        "Câu hỏi thường gặp",
      ],
    };
  },
};
</script>