<template>
  <div class="min-h-screen bg-[#f0f4f8] font-['Inter']">

    <!-- Hero -->
    <div class="relative bg-gradient-to-br from-[#0a0e27] via-[#1e3a8a] to-[#1d4ed8] overflow-hidden">
      <div class="absolute inset-0 opacity-10" style="background-image:url('https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=1600&q=60');background-size:cover"></div>
      <div class="relative z-10 container mx-auto max-w-4xl px-6 py-20 text-center text-white">
        <span class="inline-flex items-center gap-2 px-4 py-1.5 bg-white/10 border border-white/20 rounded-full text-xs font-bold tracking-widest uppercase mb-6">
          ✨ Nâng cấp tài khoản
        </span>
        <h1 class="font-['Be_Vietnam_Pro'] text-4xl lg:text-5xl font-black mb-4 leading-tight">
          Trở thành <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-300 to-yellow-200">Môi Giới</span><br/>Chuyên Nghiệp
        </h1>
        <p class="text-blue-200 text-lg max-w-2xl mx-auto leading-relaxed">
          Mua gói tin để đăng bất động sản, tiếp cận hàng nghìn khách hàng tiềm năng trên nền tảng của chúng tôi.
        </p>
      </div>
    </div>

    <div class="container mx-auto max-w-6xl px-6 -mt-8 pb-16">

      <!-- Benefits -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-14">
        <div v-for="b in benefits" :key="b.title"
          class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-md transition-all text-center group border border-gray-100">
          <div class="text-4xl mb-4 group-hover:scale-110 transition-transform">{{ b.icon }}</div>
          <h3 class="font-bold text-gray-800 mb-2">{{ b.title }}</h3>
          <p class="text-gray-400 text-sm leading-relaxed">{{ b.desc }}</p>
        </div>
      </div>

      <!-- Section title -->
      <div class="text-center mb-10">
        <h2 class="font-['Be_Vietnam_Pro'] text-3xl font-black text-[#0a0e27] mb-2">Chọn gói phù hợp</h2>
        <p class="text-gray-400">Bắt đầu ngay hôm nay với gói tin phù hợp nhất</p>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div v-for="i in 3" :key="i" class="bg-white rounded-2xl p-6 shadow-sm animate-pulse">
          <div class="h-5 bg-gray-200 rounded w-2/3 mb-4"></div>
          <div class="h-10 bg-gray-200 rounded w-3/4 mb-6"></div>
          <div class="space-y-3">
            <div class="h-3 bg-gray-200 rounded"></div>
            <div class="h-3 bg-gray-200 rounded"></div>
            <div class="h-3 bg-gray-200 rounded w-4/5"></div>
          </div>
        </div>
      </div>

      <!-- Empty -->
      <div v-else-if="packages.length === 0" class="text-center py-16">
        <div class="text-5xl mb-4">📭</div>
        <p class="text-gray-400">Chưa có gói tin nào.</p>
      </div>

      <!-- Pricing Cards -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 justify-items-center">
        <div v-for="pkg in packages" :key="pkg.id" class="w-full max-w-sm relative group">
          <!-- Popular glow -->
          <div v-if="pkg.uu_tien_hien_thi" class="absolute -inset-0.5 bg-gradient-to-r from-amber-400 to-orange-400 rounded-2xl blur opacity-50 group-hover:opacity-75 transition"></div>

          <div class="relative bg-white rounded-2xl overflow-hidden shadow-sm flex flex-col h-full"
            :class="pkg.uu_tien_hien_thi ? 'border-2 border-amber-400' : 'border border-gray-100'">

            <!-- Popular badge -->
            <div v-if="pkg.uu_tien_hien_thi" class="bg-gradient-to-r from-amber-400 to-orange-400 text-white text-xs font-black text-center py-2 tracking-widest uppercase">
              🔥 Phổ biến nhất
            </div>

            <div class="p-6 flex flex-col flex-1">
              <h3 class="font-bold text-gray-800 text-lg mb-1">{{ pkg.ten_goi }}</h3>
              <p class="text-gray-400 text-xs mb-4">{{ pkg.mo_ta || 'Gói tin đăng bất động sản' }}</p>

              <div class="mb-6">
                <span class="text-4xl font-black text-[#0a0e27]">{{ formatCurrencyCompact(pkg.gia) }}</span>
                <div class="text-xs text-gray-400 mt-1">{{ formatCurrency(pkg.gia) }}</div>
              </div>

              <ul class="space-y-3 mb-6 flex-1">
                <li class="flex items-center gap-2.5 text-sm text-gray-600">
                  <span class="w-5 h-5 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                  </span>
                  <strong>{{ pkg.so_luong_tin }} tin đăng</strong>
                </li>
                <li class="flex items-center gap-2.5 text-sm text-gray-600">
                  <span class="w-5 h-5 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                  </span>
                  Hiệu lực <strong>{{ pkg.so_ngay }} ngày</strong>
                </li>
                <li class="flex items-center gap-2.5 text-sm text-gray-600">
                  <span class="w-5 h-5 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                  </span>
                  Hiển thị thông tin liên hệ
                </li>
                <li class="flex items-center gap-2.5 text-sm text-gray-600">
                  <span class="w-5 h-5 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                  </span>
                  Hỗ trợ ưu tiên 24/7
                </li>
              </ul>

              <button @click="selectPackage(pkg)"
                class="w-full py-3 rounded-xl font-bold text-sm transition-all"
                :class="pkg.uu_tien_hien_thi
                  ? 'bg-gradient-to-r from-amber-400 to-orange-400 text-white shadow-lg shadow-amber-200 hover:-translate-y-0.5 hover:shadow-amber-300'
                  : 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-lg shadow-blue-200 hover:-translate-y-0.5 hover:shadow-blue-300'">
                Chọn gói này
              </button>
            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- Confirm Modal -->
    <Teleport to="body">
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" @click.self="showModal = false">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
          <!-- Modal Header -->
          <div class="bg-gradient-to-r from-blue-600 to-blue-700 p-6 text-white">
            <h3 class="text-xl font-black mb-1">Xác nhận mua gói</h3>
            <p class="text-blue-200 text-sm">{{ selectedPkg?.ten_goi }}</p>
          </div>

          <!-- Modal Body -->
          <div class="p-6">
            <div class="bg-gray-50 rounded-2xl p-4 mb-5 space-y-3">
              <div class="flex justify-between text-sm">
                <span class="text-gray-500">Tên gói</span>
                <span class="font-semibold text-gray-800">{{ selectedPkg?.ten_goi }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-gray-500">Số tin đăng</span>
                <span class="font-semibold text-gray-800">{{ selectedPkg?.so_luong_tin }} tin</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-gray-500">Hiệu lực</span>
                <span class="font-semibold text-gray-800">{{ selectedPkg?.so_ngay }} ngày</span>
              </div>
              <div class="flex justify-between text-sm pt-3 border-t border-gray-200">
                <span class="text-gray-500 font-semibold">Tổng tiền</span>
                <span class="text-xl font-black text-blue-600">{{ formatCurrency(selectedPkg?.gia) }}</span>
              </div>
            </div>

            <p class="flex items-start gap-2 text-xs text-gray-400 mb-6 bg-blue-50 p-3 rounded-xl">
              <svg class="w-4 h-4 text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              Sau khi mua gói, tài khoản sẽ được nâng cấp lên môi giới và nhận ngay quyền đăng tin bất động sản.
            </p>

            <div class="flex gap-3">
              <button @click="showModal = false"
                class="flex-1 py-3 border-2 border-gray-200 text-gray-500 font-bold rounded-xl hover:bg-gray-50 transition text-sm">
                Hủy
              </button>
              <button @click="confirmBuy" :disabled="buying"
                class="flex-1 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-bold rounded-xl shadow-lg shadow-blue-200 hover:-translate-y-0.5 transition-all disabled:opacity-60 text-sm flex items-center justify-center gap-2">
                <span v-if="buying" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                {{ buying ? 'Đang xử lý...' : 'Xác nhận thanh toán' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import api from "@/axios/config";
import { useRouter } from "vue-router";

const router = useRouter();
const loading = ref(false);
const packages = ref([]);
const selectedId = ref(null);
const selectedPkg = ref(null);
const showModal = ref(false);
const buying = ref(false);

const benefits = [
  { icon: "🏡", title: "Đăng tin bất động sản", desc: "Đăng nhiều tin BĐS theo gói bạn chọn, tiếp cận khách hàng rộng rãi trên toàn quốc." },
  { icon: "📊", title: "Thống kê chi tiết", desc: "Theo dõi lượt xem, yêu thích và tương tác với tin đăng của bạn theo thời gian thực." },
  { icon: "💬", title: "Hỗ trợ ưu tiên", desc: "Được hỗ trợ ưu tiên từ đội ngũ chuyên nghiệp khi gặp vấn đề hoặc cần tư vấn." },
];

const fetchPackages = async () => {
  loading.value = true;
  try {
    const res = await api.get("/goi-tin/data");
    if (res.data?.data) {
      const data = res.data.data;
      packages.value = Array.isArray(data) ? data : data.data || [];
    }
  } catch (e) {
    console.error("Lỗi tải gói tin:", e);
  } finally {
    loading.value = false;
  }
};

const selectPackage = (pkg) => {
  selectedId.value = pkg.id;
  selectedPkg.value = pkg;
  showModal.value = true;
};

const confirmBuy = async () => {
  if (!selectedPkg.value) return;
  buying.value = true;
  try {
    const res = await api.post("/khach-hang/mua-goi", { goi_tin_id: selectedPkg.value.id });
    showModal.value = false;
    if (res.data?.status) {
      alert("Mua gói thành công! Tài khoản của bạn đã được nâng cấp lên môi giới.");
      localStorage.setItem("user_type", "moi-gioi");
      router.push("/moi-gioi/trang-chu");
    }
  } catch (e) {
    console.error("Lỗi mua gói:", e);
    alert(e.response?.data?.message || "Có lỗi xảy ra, vui lòng thử lại.");
  } finally {
    buying.value = false;
  }
};

const formatCurrency = (val) =>
  new Intl.NumberFormat("vi-VN", { style: "currency", currency: "VND" }).format(val || 0);

const formatCurrencyCompact = (val) =>
  new Intl.NumberFormat("vi-VN", { style: "currency", currency: "VND", notation: "compact" }).format(val || 0);

onMounted(() => fetchPackages());
</script>
