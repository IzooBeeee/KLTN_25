<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100 p-6">
    <div
      class="flex w-full max-w-6xl bg-white rounded-[40px] shadow-2xl overflow-hidden min-h-[650px]"
    >
      <!-- LEFT -->
      <div
        class="hidden md:flex md:w-1/2 relative bg-indigo-950 text-white p-12 flex-col justify-between"
      >
        <div class="absolute inset-0 z-0">
          <img
            src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1000&q=80"
            class="w-full h-full object-cover opacity-30"
          />
          <div
            class="absolute inset-0 bg-gradient-to-br from-indigo-900/80 to-indigo-950"
          ></div>
        </div>

        <div class="relative z-10">
          <div
            class="mb-6 inline-block px-4 py-1 bg-white/10 border border-white/20 rounded-full text-[10px] tracking-widest font-semibold uppercase"
          >
            Nền tảng bất động sản cao cấp
          </div>

          <h2 class="text-5xl font-bold leading-tight mb-6 tracking-tighter">
            Bắt đầu <span class="text-indigo-300 italic">hành trình</span><br />
            cùng chúng tôi
          </h2>

          <p
            class="text-indigo-200 text-sm max-w-md leading-relaxed font-light"
          >
            Tạo tài khoản để khám phá các bất động sản và cơ hội đầu tư tốt
            nhất.
          </p>
        </div>

        <div class="relative z-10">
          <div class="flex -space-x-2 mb-3">
            <div
              v-for="i in 3"
              :key="i"
              class="w-8 h-8 rounded-full border-2 border-indigo-900 bg-gray-400"
            ></div>
          </div>
          <p
            class="text-[11px] text-indigo-300 uppercase tracking-widest font-bold"
          >
            Hơn 5.000 khách hàng đã tham gia
          </p>
        </div>
      </div>

      <!-- RIGHT -->
      <div
        class="w-full md:w-1/2 p-8 md:p-16 flex flex-col justify-center bg-white"
      >
        <!-- 🔥 QUAN TRỌNG: GIỐNG LOGIN -->
        <div class="w-full max-w-[400px] mx-auto">
          <!-- HEADER -->
          <div class="mb-10 text-left">
            <h5
              class="text-indigo-900 font-black text-[10px] uppercase tracking-[0.2em] mb-4"
            >
              Architectural Curator
            </h5>
            <h3 class="text-4xl font-black text-gray-900 mb-2 tracking-tighter">
              Tạo tài khoản
            </h3>
            <p class="text-gray-400 text-sm font-medium">
              Điền thông tin để bắt đầu
            </p>
          </div>

          <!-- FORM -->
          <form @submit.prevent="handleRegister" class="space-y-5">
            <!-- NAME (FULL) -->
            <div class="space-y-2">
              <label
                class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1"
              >
                Họ và tên
              </label>
              <div class="relative">
                <span class="absolute left-5 top-1/2 -translate-y-1/2">👤</span>
                <input
                  v-model="ten"
                  type="text"
                  class="w-full pl-14 pr-6 py-4 bg-gray-50 !rounded-full focus:ring-2 focus:ring-indigo-600 text-sm border-none shadow-sm"
                  placeholder="Nguyễn Văn A"
                  required
                />
              </div>
            </div>

            <!-- 🔥 ROW 1 -->
            <div class="gap-4">
              <!-- EMAIL -->
              <div class="space-y-2">
                <label
                  class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1"
                >
                  Email
                </label>
                <div class="relative">
                  <span class="absolute left-5 top-1/2 -translate-y-1/2"
                    >📧</span
                  >
                  <input
                    v-model="email"
                    type="email"
                    class="w-full pl-12 pr-4 py-4 bg-gray-50 !rounded-full focus:ring-2 focus:ring-indigo-600 text-sm border-none shadow-sm"
                    placeholder="email@gmail.com"
                    required
                  />
                </div>
              </div>

              <!-- PHONE -->
              <div class="space-y-2">
                <label
                  class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1"
                >
                  Số điện thoại
                </label>
                <div class="relative">
                  <span class="absolute left-5 top-1/2 -translate-y-1/2"
                    >📱</span
                  >
                  <input
                    v-model="so_dien_thoai"
                    type="text"
                    class="w-full pl-12 pr-4 py-4 bg-gray-50 !rounded-full focus:ring-2 focus:ring-indigo-600 text-sm border-none shadow-sm"
                    placeholder="090xxxxxxx"
                    required
                  />
                </div>
              </div>
            </div>

            <!-- 🔥 ROW 2 -->
            <div class="grid grid-cols-2 gap-4">
              <!-- PASSWORD -->
              <div class="space-y-2">
                <label
                  class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1"
                >
                  Mật khẩu
                </label>
                <div class="relative">
                  <span class="absolute left-5 top-1/2 -translate-y-1/2"
                    >🔒</span
                  >
                  <input
                    v-model="password"
                    type="password"
                    class="w-full pl-12 pr-4 py-4 bg-gray-50 !rounded-full focus:ring-2 focus:ring-indigo-600 text-sm border-none shadow-sm"
                    placeholder="••••••••"
                    required
                  />
                </div>
              </div>

              <!-- CONFIRM -->
              <div class="space-y-2">
                <label
                  class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1"
                >
                  Nhập lại
                </label>
                <div class="relative">
                  <span class="absolute left-5 top-1/2 -translate-y-1/2"
                    >🔒</span
                  >
                  <input
                    v-model="password_confirmation"
                    type="password"
                    class="w-full pl-12 pr-4 py-4 bg-gray-50 !rounded-full focus:ring-2 focus:ring-indigo-600 text-sm border-none shadow-sm"
                    placeholder="••••••••"
                    required
                  />
                </div>
              </div>
            </div>

            <!-- BUTTON -->
            <button
              type="submit "
              class="mt-2 w-full !bg-indigo-950 hover:!bg-black !text-white font-bold py-4 !rounded-full shadow-xl shadow-indigo-100 transition-all hover:-translate-y-[1px] active:scale-[0.98] uppercase tracking-[0.15em] text-xs"
            >
              Đăng ký
            </button>
          </form>

          <!-- FOOT -->
          <p
            class="text-center text-[11px] text-gray-400 mt-10 font-medium uppercase tracking-wider mt-5"
          >
            Đã có tài khoản?
            <router-link
              to="/khach-hang/dang-nhap"
              class="text-indigo-900 font-black hover:underline ml-1"
            >
              Đăng nhập
            </router-link>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import api from "@/axios/config";
import { createToaster } from "@meforma/vue-toaster";
import { useRouter } from "vue-router";

const ten = ref("");
const email = ref("");
const so_dien_thoai = ref("");
const password = ref("");
const password_confirmation = ref("");

const router = useRouter();
const toaster = createToaster({ position: "top-right" });

const handleRegister = async () => {
  // ✅ Validate frontend (tuỳ chọn)
  if (password.value !== password_confirmation.value) {
    toaster.error("Mật khẩu xác nhận không khớp");
    return;
  }

  try {
    const response = await api.post("/khach-hang/dang-ky", {
      ten: ten.value,
      email: email.value,
      so_dien_thoai: so_dien_thoai.value,
      password: password.value,
      password_confirmation: password_confirmation.value,
    });

    console.log("✅ Register response:", response.data);
    toaster.success("Đăng ký thành công!");
    router.push("/khach-hang/dang-nhap");
  } catch (err) {
    // ✅ Hiển thị lỗi chi tiết từ Laravel
    const errors = err.response?.data?.errors;
    const message = errors
      ? Object.values(errors).flat().join("<br>") // Hiển thị nhiều lỗi
      : err.response?.data?.message || "Đăng ký thất bại";

    toaster.error(message);
    console.error("Register error:", err.response?.data); // Debug
  }
};
</script>