<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100 p-6">
    <div
      class="flex w-full max-w-6xl bg-white rounded-[40px] shadow-2xl overflow-hidden min-h-[650px]"
    >
      <!-- Left Panel (Hình ảnh) -->
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
            Bảo mật tài khoản
          </div>
          <h2 class="text-5xl font-bold leading-tight mb-6 tracking-tighter">
            Xác thực <span class="text-indigo-300 italic">mã OTP</span><br />
            của bạn
          </h2>
          <p
            class="text-indigo-200 text-sm max-w-md leading-relaxed font-light"
          >
            Chúng tôi đã gửi mã 6 số đến email của bạn. Mã có hiệu lực trong 5
            phút.
          </p>
        </div>

        <div class="relative z-10">
          <div class="flex items-center gap-2 text-[11px] text-indigo-300">
            <span
              class="w-2 h-2 rounded-full bg-green-400 animate-pulse"
            ></span>
            <span>Mã được mã hóa end-to-end</span>
          </div>
        </div>
      </div>

      <!-- Right Panel (Form) -->
      <div
        class="w-full md:w-1/2 p-8 md:p-16 flex flex-col justify-center bg-white"
      >
        <div class="w-full max-w-[400px] mx-auto">
          <div class="mb-8 text-left">
            <h5
              class="text-indigo-900 font-black text-[10px] uppercase tracking-[0.2em] mb-4"
            >
              Architectural Curator
            </h5>
            <h3 class="text-4xl font-black text-gray-900 mb-2 tracking-tighter">
              Nhập mã xác nhận
            </h3>
            <p class="text-gray-500 text-sm">
              Gửi đến:
              <span class="font-semibold text-gray-700">{{ email }}</span>
            </p>
          </div>

          <!-- OTP Input -->
          <form @submit.prevent="xacThucMa" class="space-y-6">
            <div class="space-y-3">
              <label
                class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1"
              >
                Mã xác nhận (6 số)
              </label>

              <!-- 6 ô input riêng biệt -->
              <div class="flex gap-2 justify-center">
                <input
                  v-for="(digit, index) in 6"
                  :key="index"
                  ref="otpInputs"
                  v-model="otpArray[index]"
                  type="text"
                  maxlength="1"
                  @input="handleInput($event, index)"
                  @keydown.delete="handleBackspace($event, index)"
                  @keyup="handleKeyup($event, index)"
                  class="w-12 h-14 text-center text-xl font-bold border-2 border-gray-200 rounded-xl focus:border-indigo-600 focus:ring-2 focus:ring-indigo-200 transition-all outline-none"
                  :class="{ 'border-red-400 bg-red-50': error }"
                />
              </div>

              <!-- Hidden input để lấy full code -->
              <input type="hidden" v-model="otpCode" />

              <p
                v-if="error"
                class="text-red-500 text-xs text-center font-medium"
              >
                {{ error }}
              </p>
            </div>

            <!-- Timer Resend -->
            <div class="text-center">
              <span v-if="countdown > 0" class="text-gray-400 text-xs">
                Gửi lại mã sau:
                <span class="font-bold text-indigo-600">{{ countdown }}s</span>
              </span>
              <button
                v-else
                type="button"
                @click="guiLaiMa"
                :disabled="isLoading"
                class="text-indigo-600 text-xs font-black uppercase tracking-wider hover:underline disabled:opacity-50"
              >
                ↻ Gửi lại mã
              </button>
            </div>

            <!-- Button -->
            <div class="pt-4">
              <button
                type="submit"
                :disabled="isLoading || otpCode.length < 6"
                class="w-full !bg-indigo-950 hover:!bg-black !text-white font-bold py-4 !rounded-full shadow-xl shadow-indigo-100 transition-all disabled:opacity-70 disabled:cursor-not-allowed uppercase tracking-[0.15em] text-xs"
              >
                {{ isLoading ? "Đang xác thực..." : "Xác nhận & Tiếp tục" }}
              </button>
            </div>
          </form>

          <!-- Back Link -->
          <p
            class="text-center text-[11px] text-gray-400 mt-6 font-medium uppercase tracking-wider"
          >
            <router-link
              to="/khach-hang/quen-mat-khau"
              class="hover:text-indigo-600 transition-colors"
            >
              ← Quay lại nhập email
            </router-link>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from "@/axios/config";
import { createToaster } from "@meforma/vue-toaster";

const toaster = createToaster({ position: "top-right", duration: 3000 });

export default {
  name: "XacThucMa",
  data() {
    return {
      email: "",
      otpArray: ["", "", "", "", "", ""],
      isLoading: false,
      error: "",
      countdown: 0,
      timer: null,
    };
  },

  computed: {
    otpCode() {
      return this.otpArray.join("");
    },
  },

  created() {
    this.email = this.$route.query.email;
    if (!this.email) {
      toaster.error("Email không hợp lệ");
      this.$router.replace("/khach-hang/quen-mat-khau");
    }
  },

  mounted() {
    this.$nextTick(() => {
      if (this.$refs.otpInputs?.[0]) {
        this.$refs.otpInputs[0].focus();
      }
    });
    this.startCountdown();
  },

  beforeUnmount() {
    if (this.timer) clearInterval(this.timer);
  },

  methods: {
    startCountdown() {
      this.countdown = 30;
      if (this.timer) clearInterval(this.timer);
      this.timer = setInterval(() => {
        if (this.countdown > 0) {
          this.countdown--;
        } else {
          clearInterval(this.timer);
        }
      }, 1000);
    },

    handleInput(event, index) {
      const val = event.target.value.replace(/[^0-9]/g, "");
      this.otpArray[index] = val;

      // Auto focus next
      if (val && index < 5 && this.$refs.otpInputs[index + 1]) {
        this.$refs.otpInputs[index + 1].focus();
      }

      // Auto submit when full
      if (this.otpArray.every((d) => d !== "") && !this.isLoading) {
        this.xacThucMa();
      }

      this.error = "";
    },

    handleBackspace(event, index) {
      if (
        !this.otpArray[index] &&
        index > 0 &&
        this.$refs.otpInputs[index - 1]
      ) {
        this.$refs.otpInputs[index - 1].focus();
      }
    },

    handleKeyup(event, index) {
      // Allow arrow navigation
      if (
        event.key === "ArrowRight" &&
        index < 5 &&
        this.$refs.otpInputs[index + 1]
      ) {
        this.$refs.otpInputs[index + 1].focus();
      }
      if (
        event.key === "ArrowLeft" &&
        index > 0 &&
        this.$refs.otpInputs[index - 1]
      ) {
        this.$refs.otpInputs[index - 1].focus();
      }
    },

    async guiLaiMa() {
      if (this.countdown > 0) return;

      try {
        const res = await api.post("/khach-hang/quen-mat-khau/gui-ma", {
          email: this.email,
        });

        if (res.data.status) {
          toaster.success("Đã gửi mã mới!");
          // Reset OTP
          this.otpArray = ["", "", "", "", "", ""];
          this.startCountdown();
          if (this.$refs.otpInputs?.[0]) {
            this.$refs.otpInputs[0].focus();
          }
        } else {
          toaster.error(res.data.message);
        }
      } catch {
        toaster.error("Gửi lại mã thất bại");
      }
    },

    async xacThucMa() {
      if (this.otpCode.length < 6) {
        toaster.error("Vui lòng nhập đủ 6 số mã xác nhận");
        return;
      }

      this.isLoading = true;
      this.error = "";

      try {
        const res = await api.post("/khach-hang/quen-mat-khau/xac-thuc", {
          email: this.email,
          code: this.otpCode,
        });

        if (res.data.status) {
          toaster.success("Mã xác nhận hợp lệ!");

          // 👉 Chuyển sang trang đặt lại mật khẩu
          this.$router.push({
            path: "/khach-hang/dat-lai-mat-khau",
            query: {
              email: this.email,
              code: this.otpCode,
            },
          });
        } else {
          this.error = res.data.message;
          toaster.error(res.data.message);
          // Clear OTP sai
          this.otpArray = ["", "", "", "", "", ""];
          if (this.$refs.otpInputs?.[0]) {
            this.$refs.otpInputs[0].focus();
          }
        }
      } catch (err) {
        this.error = "Xác thực thất bại. Vui lòng thử lại";
        toaster.error("Xác thực thất bại");
      } finally {
        this.isLoading = false;
      }
    },
  },
};
</script>

<style scoped>
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
input[type="text"] {
  -moz-appearance: textfield;
}
</style>