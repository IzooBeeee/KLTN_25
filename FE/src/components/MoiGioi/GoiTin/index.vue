<template>
  <div class="pricing-wrapper">
    <!-- ✅ PAYMENT RESULT MODAL (Tái sử dụng cho success/error/cancel) -->
    <div v-if="paymentResult" class="payment-result-overlay" @click.self="paymentResult = null">
      <div class="payment-result-card" :class="paymentResult">
        <!-- ✅ SUCCESS -->
        <div v-if="paymentResult === 'success'" class="result-content">
          <div class="icon-wrapper success">
            <svg viewBox="0 0 52 52" class="checkmark">
              <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none" />
              <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8" />
            </svg>
          </div>
          <h3 class="title text-success">Thanh toán thành công!</h3>
          <p class="description">
            Gói <strong>{{ selectedPlan?.name }}</strong> đã được kích hoạt.
          </p>
          <p class="order-code">
            Mã đơn: <strong>{{ orderCode }}</strong>
          </p>
          <button class="btn btn-success" @click="handleModalClose">
            Đóng
          </button>
        </div>

        <!-- ❌ FAILED -->
        <div v-else-if="paymentResult === 'error'" class="result-content">
          <div class="icon-wrapper error">
            <i class="bx bx-x-circle"></i>
          </div>
          <h3 class="title text-danger">Thanh toán thất bại</h3>
          <p class="description">
            {{ paymentErrorMessage || "Có lỗi xảy ra. Vui lòng thử lại." }}
          </p>
          <p class="order-code">
            Mã đơn: <strong>{{ orderCode }}</strong>
          </p>
          <div class="btn-group">
            <button class="btn btn-outline" @click="paymentResult = null">
              Đóng
            </button>
            <button class="btn btn-outline-primary" @click="handleRetry">
              Thử lại
            </button>
          </div>
        </div>

        <!-- ⏹️ CANCEL -->
        <div v-else-if="paymentResult === 'cancel'" class="result-content">
          <div class="icon-wrapper cancel">
            <i class="bx bx-minus-circle"></i>
          </div>
          <h3 class="title">Đã hủy thanh toán</h3>
          <p class="description">Bạn có thể thử lại bất cứ lúc nào.</p>
          <p class="order-code">
            Mã đơn: <strong>{{ orderCode }}</strong>
          </p>
          <button class="btn btn-outline-primary" @click="paymentResult = null">
            Quay lại
          </button>
        </div>

        <!-- ⏳ LOADING -->
        <div v-else-if="paymentResult === 'loading'" class="result-content">
          <div class="spinner"></div>
          <p class="description">Đang xác nhận thanh toán...</p>
          <p class="order-code">
            Mã đơn: <strong>{{ orderCode }}</strong>
          </p>
        </div>
      </div>
    </div>

    <!-- Header -->
    <div class="text-center mb-5">
      <h2 class="fw-bold mb-2 text-dark">Nâng cấp gói của bạn</h2>
      <p class="text-muted">Chọn gói phù hợp để tối ưu hiệu quả đăng tin</p>

      <!-- Billing Toggle -->
      <div class="billing-toggle">
        <span :class="{ active: cycle === 'monthly' }" @click="cycle = 'monthly'">
          Thanh toán hàng tháng
        </span>
        <div class="toggle-switch" :class="{ yearly: cycle === 'yearly' }" @click="toggleCycle">
          <div class="toggle-knob"></div>
        </div>
        <span :class="{ active: cycle === 'yearly' }" @click="cycle = 'yearly'">
          Thanh toán hàng năm
          <span class="badge-discount">Tiết kiệm 20%</span>
        </span>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-emerald" role="status"></div>
      <p class="mt-2 text-muted">Đang tải gói tin...</p>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="alert alert-danger text-center shadow-sm border-0">
      {{ error }}
      <button class="btn btn-sm btn-outline-danger ms-2" @click="loadPlans">
        Thử lại
      </button>
    </div>

    <!-- Pricing Grid -->
    <!-- Pricing Grid -->
    <div v-else class="pricing-grid">
      <div v-for="plan in sortedPlans" :key="plan.id" class="pricing-card" :class="{
        popular: plan.isPopular,
        'border-emerald': plan.isPopular,
        'current-plan': plan.id === currentPlanId,
      }">
        <!-- Badge gói đang dùng -->
        <!-- <div v-if="plan.id === currentPlanId" class="current-plan-badge-top">
          <i class="bx bx-check-circle"></i>
          <span>Gói đang dùng</span>
        </div> -->

        <!-- Popular badge -->
        <div v-if="plan.isPopular" class="popular-badge">
          <i class="bx bx-star me-1"></i> PHỔ BIẾN NHẤT
        </div>

        <div class="card-header">
          <h3 class="plan-name" :class="{ 'text-emerald': plan.isPopular }">
            {{ plan.name }}
          </h3>
          <p class="plan-desc">{{ plan.description }}</p>
        </div>

        <div class="card-price" :class="{ 'text-emerald': plan.isPopular }">
          <!-- ✅ Nếu là gói đang dùng → gạch ngang giá + hiện 0 -->
          <template v-if="plan.id === currentPlanId">
            <span class="amount original-price">
              {{ formatPrice(getPrice(plan)) }}
            </span>
            <span class="amount current-price">0</span>
          </template>

          <!-- ✅ Bình thường → hiển thị giá gốc -->
          <template v-else>
            <span class="amount">{{ formatPrice(getPrice(plan)) }}</span>
          </template>

          <span class="period">/{{ cycle === "monthly" ? "tháng" : "năm" }}</span>
        </div>

        <div class="post-allowance">
          <i class="bx bx-file me-2"></i>
          <strong>{{ plan.postLimit }}</strong> tin đăng /
          {{ cycle === "monthly" ? "tháng" : "năm" }}
          <small v-if="plan.postLimit < 999" class="d-block text-muted">
            {{ getRemainingText(plan) }}
          </small>
        </div>

        <ul class="feature-list">
          <li v-for="(feat, i) in plan.features" :key="i">
            <i :class="getFeatureIcon(feat)" class="me-2"></i>
            <span v-html="feat"></span>
          </li>
        </ul>

        <!-- ✅ MỚI: Nút với logic check -->
        <button class="btn w-100 mt-auto" :class="getButtonClass(plan)" @click="selectPlan(plan)"
          :disabled="!canPurchase(plan)">
          {{ getButtonText(plan) }}
        </button>
      </div>
    </div>

    <!-- FAQ Section -->
    <div class="faq-section mt-5 pt-4 border-top">
      <h5 class="mb-4 text-center fw-bold text-dark">❓ Câu hỏi thường gặp</h5>
      <div class="faq-list">
        <div v-for="(faq, index) in faqs" :key="index" class="faq-item" :class="{ active: activeIndex === index }"
          @click="toggleFaq(index)">
          <div class="faq-header">
            <span class="faq-question-text">{{ faq.q }}</span>
            <i class="faq-icon" :class="activeIndex === index ? 'bx bx-up-arrow' : 'bx bx-down-arrow'
              "></i>
          </div>
          <div class="faq-body" :style="{ maxHeight: activeIndex === index ? '500px' : '0' }">
            <div class="faq-content">{{ faq.a }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Payment Confirmation Modal (Xác nhận trước khi thanh toán) -->
  <div v-if="showModal && !paymentResult" class="payment-modal">
    <div class="modal-content">
      <h3>Thanh toán gói</h3>
      <p>
        <strong>{{ selectedPlan?.name }}</strong>
      </p>
      <p>
        Giá: {{ formatPrice(getPrice(selectedPlan)) }}đ /
        {{ cycle === "monthly" ? "tháng" : "năm" }}
      </p>
      <ul class="modal-features">
        <li v-for="(f, i) in selectedPlan?.features" :key="i" v-html="formatFeatureWithIcon(f)"></li>
      </ul>
      <div class="modal-actions">
        <button class="btn btn-cancel" @click="cancelPayment" :disabled="paymentLoading">
          Hủy
        </button>
        <button class="btn btn-emerald" @click="confirmPayment" :disabled="paymentLoading">
          <span v-if="paymentLoading" class="spinner-border spinner-border-sm me-2"></span>
          {{ paymentLoading ? "Đang xử lý..." : "Thanh toán ngay" }}
        </button>
      </div>
    </div>
  </div>

  <div v-if="showPaymentModal" class="modal-overlay">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Thanh toán gói {{ selectedPlan?.name }}</h3>
        <div class="countdown-badge" :class="{ urgent: timeRemaining <= 60 }">
          <i class="bx bx-time"></i> {{ formatTime(timeRemaining) }}
        </div>
      </div>

      <div class="qr-container">
        <img v-if="paymentData.qr_image_url" :src="paymentData.qr_image_url" alt="QR Code" class="qr-image" />
      </div>

      <div class="payment-info-box">
        <div class="info-row">
          <span class="label">💰 Số tiền:</span>
          <span class="value">{{ formatPrice(paymentData.amount) }}đ</span>
        </div>
        <div class="info-row">
          <span class="label">📝 Nội dung CK:</span>
          <span class="value code">{{ paymentData.order_code }}</span>
        </div>
        <div class="info-row warning">
          <i class="bx bx-error-circle"></i>
          <span>Vui lòng chuyển ĐÚNG nội dung để được kích hoạt tự động</span>
        </div>
      </div>

      <div class="action-buttons">
        <button @click="markAsPaid" class="btn btn-success" :disabled="!canMarkAsPaid">
          <i class="bx bx-check"></i> Tôi đã thanh toán
        </button>
        <button @click="reportWrongContent" class="btn btn-link">
          Chuyển khoản sai nội dung?
        </button>
        <button @click="closeModal" class="btn btn-text">Đóng</button>
      </div>

      <div v-if="isExpired" class="expired-overlay">
        <i class="bx bx-time-five"></i>
        <h4>Mã QR đã hết hạn</h4>
        <p>Vui lòng tạo mã mới để tiếp tục thanh toán</p>
        <button @click="regenerateQR" class="btn btn-primary">
          Tạo mã mới
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch, nextTick, onUnmounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import api from "@/axios/config";
import QRCode from "qrcode";
const router = useRouter();
const route = useRoute();

// ===== STATE =====
const cycle = ref("monthly");
const plans = ref([]);
const loading = ref(true);
const error = ref(null);
const currentPlanId = ref(null);
const userPostUsage = ref({ used: 0, limit: 0 });
const selectedId = ref(null);
const selectedPlan = ref(null);
const showModal = ref(false);
const activeIndex = ref(null);
const qrCanvas = ref(null);
const paymentData = ref({});
let pollingInterval = null;
const showPaymentModal = ref(false);
const isPaymentCompleted = ref(false);
// ✅ Payment state
const paymentResult = ref(null); // 'success' | 'error' | 'cancel' | 'loading' | null
const paymentLoading = ref(false);
const paymentErrorMessage = ref("");
const orderCode = ref(null);
const timeRemaining = ref(300); // 5 phút = 300 giây
const isExpired = ref(false);
let countdownInterval = null;

const extraCreditOptions = [
  { count: 5, price: 50000 },
  { count: 10, price: 90000 },
  { count: 20, price: 160000 },
];

// ===== FEATURE ICONS =====
const getFeatureIcon = (featureText) => {
  const text = featureText.toLowerCase();
  if (
    text.includes("tin đăng") ||
    text.includes("bài đăng") ||
    text.includes("tin")
  )
    return "bx bx-file text-emerald";
  if (
    text.includes("hiệu lực") ||
    text.includes("ngày") ||
    text.includes("tháng")
  )
    return "bx bx-calendar text-emerald";
  if (
    text.includes("hiển thị") ||
    text.includes("ưu tiên") ||
    text.includes("vip")
  )
    return "bx bx-star text-emerald";
  if (text.includes("hỗ trợ") || text.includes("support"))
    return "bx bx-support text-emerald";
  if (
    text.includes("email") ||
    text.includes("zalo") ||
    text.includes("liên hệ")
  )
    return "bx bx-chat text-emerald";
  if (text.includes("ảnh") || text.includes("hình") || text.includes("media"))
    return "bx bx-image text-emerald";
  if (text.includes("thống kê") || text.includes("báo cáo"))
    return "bx bx-bar-chart text-emerald";
  if (text.includes("api") || text.includes("integration"))
    return "bx bx-code text-emerald";
  if (
    text.includes("tài khoản") ||
    text.includes("user") ||
    text.includes("quản lý")
  )
    return "bx bx-user text-emerald";
  if (text.includes("nhãn") || text.includes("badge"))
    return "bx bx-badge text-emerald";
  if (text.includes("tìm kiếm") || text.includes("search"))
    return "bx bx-search text-emerald";
  return "bx bx-check-circle text-emerald";
};

const formatFeatureWithIcon = (featureText) => {
  const icon = getFeatureIcon(featureText);
  return `<i class="${icon} me-2"></i><span>${featureText}</span>`;
};

// ===== FAQ =====
const faqs = [
  {
    q: "Tin đăng có thời hạn bao lâu?",
    a: "Mỗi tin đăng có hiệu lực 15 ngày kể từ khi đăng. Sau đó tin sẽ tự động ẩn và bạn có thể đăng lại.",
  },
  {
    q: "Nếu dùng hết số tin trong gói thì sao?",
    a: "Bạn có thể mua thêm credit tin đăng bất cứ lúc nào, hoặc nâng cấp lên gói cao hơn. Credit mua thêm không có thời hạn.",
  },
  {
    q: "Có được hoàn tiền nếu hủy gói?",
    a: "Với gói tháng: không hoàn tiền cho phần đã sử dụng. Với gói năm: hoàn proportional cho số tháng còn lại.",
  },
  {
    q: "Credit mua thêm có hết hạn không?",
    a: "Credit mua thêm KHÔNG có thời hạn, bạn có thể dùng dần khi cần.",
  },
];
const toggleFaq = (index) => {
  activeIndex.value = activeIndex.value === index ? null : index;
};

// ===== COMPUTED =====
const sortedPlans = computed(() =>
  [...plans.value].sort((a, b) => {
    if (a.isPopular && !b.isPopular) return -1;
    if (!a.isPopular && b.isPopular) return 1;
    return a.postLimit - b.postLimit;
  })
);

const canBuyExtra = computed(
  () => userPostUsage.value.used >= userPostUsage.value.limit * 0.8
);

// ✅ COMPUTED: Kiểm tra có thể thanh toán gói này không
const canPurchase = (plan) => {
  // Nếu đang dùng gói này rồi → KHÔNG CHO MUA
  if (plan.id === currentPlanId.value) return false;
  // Nếu đang trong quá trình thanh toán → KHÔNG CHO MUA
  if (paymentLoading.value && selectedId.value === plan.id) return false;
  return true;
};

const getButtonClass = (plan) => {
  if (!canPurchase(plan)) return "btn-secondary";
  if (plan.isPopular) return "btn-emerald";
  return "btn-outline-emerald";
};

const getButtonText = (plan) => {
  if (plan.id === currentPlanId.value) return "Đang sử dụng";
  if (paymentLoading.value && selectedId.value === plan.id)
    return "Đang xử lý...";
  return plan.btnText || "Chọn gói";
};

// Hàm gọi API và vẽ QR
const openPaymentModal = async (plan) => {
  try {
    const res = await api.post("/moi-gioi/payment/create", {
      goi_tin_id: plan.id,
    });

    if (res.data.status) {
      paymentData.value = res.data.data;
      showPaymentModal.value = true;

      // ✅ KHÔNG CẦN vẽ QR nữa (vì là ảnh từ URL)
      // Chỉ cần start polling
      startCountdown();
      startPolling();
    }
  } catch (err) {
    console.error("Error:", err);
    alert("Không thể tạo thanh toán");
  }
};

// Hàm vẽ QR
const drawQRCode = (text) => {
  QRCode.toCanvas(
    qrCanvas.value,
    text,
    {
      width: 250,
      margin: 2,
      color: {
        dark: "#000000",
        light: "#ffffff",
      },
    },
    function (error) {
      if (error) console.error(error);
    }
  );
};

const startPolling = () => {
  if (!paymentData.value.order_code) return;

  pollingInterval = setInterval(async () => {
    await checkPaymentStatus();
  }, 3000);
};

// Stop polling
const stopPolling = () => {
  if (pollingInterval) {
    clearInterval(pollingInterval);
    pollingInterval = null;
  }
};

// Check payment status
const checkPaymentStatus = async () => {
  // ✅ CHẶN: Nếu đã hoàn tất rồi thì không làm gì cả
  if (isPaymentCompleted.value) return;

  if (!paymentData.value.order_code) return;

  try {
    const token = localStorage.getItem("auth_token");
    const { data } = await api.get(`/moi-gioi/giao-dich/${paymentData.value.order_code}/status`);

    // ✅ CHẶN LẦN 2: Trong lúc chờ API, nếu user đã đóng modal thì bỏ qua
    if (isPaymentCompleted.value) return;

    if (data?.data?.trang_thai === "success") {
      isPaymentCompleted.value = true; // Đánh dấu hoàn tất
      stopPolling(); // Dừng polling

      paymentResult.value = "success";
      orderCode.value = paymentData.value.order_code;
      showPaymentModal.value = false;

      // Reload lại gói (nhưng không để hiện loading trắng trang)
      await loadPlans(true);
    }
  } catch (err) {
    console.error("Check status error:", err);
  }
};

const canMarkAsPaid = computed(() => {
  return timeRemaining.value > 0 && !isExpired.value;
});

const formatTime = (seconds) => {
  const mins = Math.floor(seconds / 60);
  const secs = seconds % 60;
  return `${mins.toString().padStart(2, "0")}:${secs
    .toString()
    .padStart(2, "0")}`;
};

const startCountdown = () => {
  // Dừng interval cũ nếu có (tránh chạy chồng khi mở lại modal)
  if (countdownInterval) clearInterval(countdownInterval);

  timeRemaining.value = 300; // Reset về 5 phút

  countdownInterval = setInterval(() => {
    if (timeRemaining.value > 0) {
      timeRemaining.value--;
    } else {
      clearInterval(countdownInterval);
      // Hết giờ → đóng modal + thông báo
      showPaymentModal.value = false;
      Swal.fire({
        icon: "warning",
        title: "Mã QR đã hết hạn",
        text: "Vui lòng tạo mã mới để tiếp tục thanh toán.",
        confirmButtonText: "Tạo mã mới",
      }).then(() => openPaymentModal(selectedPlan.value));
    }
  }, 1000);
};

const stopCountdown = () => {
  if (countdownInterval) {
    clearInterval(countdownInterval);
    countdownInterval = null;
  }
};

const markAsPaid = async () => {
  if (!canMarkAsPaid.value) return;

  // Gọi API check status
  await checkPaymentStatus();
};

const reportWrongContent = () => {
  // Mở modal hướng dẫn hoặc redirect đến trang hỗ trợ
  Swal.fire({
    icon: "info",
    title: "Chuyển khoản sai nội dung?",
    html: `
      <p>Vui lòng liên hệ hỗ trợ với thông tin:</p>
      <ul style="text-align: left; margin-top: 10px;">
        <li>Mã giao dịch: <strong>${paymentData.value.order_code}</strong></li>
        <li>Số tiền: <strong>${formatPrice(
      paymentData.value.amount
    )}đ</strong></li>
        <li>Thời gian: <strong>${new Date().toLocaleString(
      "vi-VN"
    )}</strong></li>
      </ul>
    `,
    confirmButtonText: "Đã hiểu",
    showCancelButton: true,
    cancelButtonText: "Liên hệ Zalo",
    confirmButtonColor: "#10b981",
  }).then((result) => {
    if (result.isDismissed && result.dismiss === "cancel") {
      // Open Zalo support
      window.open("https://zalo.me/your-support-number", "_blank");
    }
  });
};

const regenerateQR = async () => {
  // Gọi lại API để tạo QR mới
  await openPaymentModal(selectedPlan.value);
};

const closeModal = () => {
  if (countdownInterval) clearInterval(countdownInterval);
  showPaymentModal.value = false;
};

// ===== METHODS =====
const toggleCycle = () => {
  cycle.value = cycle.value === "monthly" ? "yearly" : "monthly";
};
const getPrice = (plan) =>
  cycle.value === "monthly" ? plan.monthlyPrice : plan.yearlyPrice;
const formatPrice = (val) => new Intl.NumberFormat("vi-VN").format(val);

const getRemainingText = (plan) => {
  if (plan.id !== currentPlanId.value) return "";
  const remaining = plan.postLimit - userPostUsage.value.used;
  return `Còn ${remaining} tin chưa dùng trong kỳ này`;
};

// ✅ Handle modal close
const handleModalClose = () => {
  stopCountdown(); // ✅ DỪNG ĐẾM NGƯỢC
  stopPolling();
  showPaymentModal.value = false;
  // ✅ 1. Chặn ngay lập tức không cho API cũ mở lại modal
  isPaymentCompleted.value = true;

  // ✅ 2. Dừng mọi polling
  stopPolling();

  // ✅ 3. Đóng modal và xóa trạng thái
  paymentResult.value = null;
  paymentErrorMessage.value = "";
  orderCode.value = null;
  paymentData.value = {};

  // ✅ 4. Load lại dữ liệu ngầm (silent = true) để bảng giá không bị biến mất
  if (paymentResult.value === "success") {
    loadPlans(true);
  }

  // 5. Xóa param trên URL
  router.replace({ query: {} });
};

// ✅ Handle retry
const handleRetry = () => {
  paymentResult.value = null;
  if (selectedId.value) {
    const plan = plans.value.find((p) => p.id === selectedId.value);
    if (plan) selectPlan(plan);
  }
};

// ✅ Load plans
// ✅ Thêm tham số silent
const loadPlans = async (silent = false) => {
  if (!silent) loading.value = true; // Chỉ hiện loading nếu không phải silent
  error.value = null;

  try {
    const token = localStorage.getItem("auth_token");
    const { data } = await api.get(`/moi-gioi/goi-tin/data`);

    if (data?.status && data?.data) {
      plans.value = data.data;
      if (data.current_plan) {
        currentPlanId.value = data.current_plan.id;
        userPostUsage.value = {
          used: data.current_plan.used_posts,
          limit: data.current_plan.postLimit,
        };
      }
    } else {
      throw new Error(data?.message || "Lỗi tải dữ liệu");
    }
  } catch (err) {
    console.error("Load plans error:", err);
    error.value = "Không thể tải bảng giá. Vui lòng thử lại sau.";
  } finally {
    if (!silent) loading.value = false; // Chỉ tắt loading nếu không phải silent
  }
};
// ✅ Check order status
const checkOrderStatus = async (code) => {
  if (!code) return null;
  try {
    const token = localStorage.getItem("auth_token");
    const { data } = await api.get(`/moi-gioi/giao-dich/${code}/status`);
    return data?.data?.trang_thai;
  } catch (err) {
    console.error("Check order error:", err);
    return null;
  }
};

// ✅ Polling check order status
const pollOrderStatus = async (code, maxAttempts = 15) => {
  let attempts = 0;
  return new Promise((resolve) => {
    const interval = setInterval(async () => {
      attempts++;
      const status = await checkOrderStatus(code);
      if (status === "success") {
        clearInterval(interval);
        paymentResult.value = "success";
        paymentLoading.value = false;
        await loadPlans();
        resolve();
      } else if (status === "failed" || status === "cancelled") {
        clearInterval(interval);
        paymentResult.value = status === "failed" ? "error" : "cancel";
        paymentLoading.value = false;
        resolve();
      } else if (attempts >= maxAttempts) {
        clearInterval(interval);
        paymentLoading.value = false;
        resolve();
      }
    }, 2000);
  });
};

// ✅ Handle payment redirect from Sepay
const handlePaymentRedirect = async () => {
  const status = route.query.status;
  const code = route.query.order_code;
  if (!status && !code) return;

  orderCode.value = code;

  if (status === "success") {
    paymentResult.value = "loading";
    await pollOrderStatus(code);
    if (paymentResult.value === "loading") {
      paymentResult.value = "error";
      paymentErrorMessage.value =
        "Thanh toán đang được xử lý. Vui lòng kiểm tra lại sau.";
    }
  } else if (status === "error") {
    paymentResult.value = "error";
    paymentErrorMessage.value = route.query.message || "Thanh toán thất bại";
  } else if (status === "cancel") {
    paymentResult.value = "cancel";
  }
};

// ✅ Select plan & show confirmation modal
const selectPlan = (plan) => {
  // Nếu đang dùng gói này → Alert và return
  if (plan.id === currentPlanId.value) {
    Swal.fire({
      icon: "info",
      title: "Bạn đang sử dụng gói này",
      text: "Gói hiện tại của bạn vẫn còn hiệu lực. Vui lòng đợi hết hạn hoặc nâng cấp lên gói cao hơn.",
      confirmButtonText: "Đóng",
    });
    return;
  }

  selectedPlan.value = plan;
  selectedId.value = plan.id;
  showModal.value = true;
};

const cancelPayment = () => {
  showModal.value = false;
  selectedPlan.value = null;
  selectedId.value = null;
};

// ✅ Buy extra credits
const buyExtraCredits = async (extra) => {
  if (
    !confirm(
      `Mua thêm ${extra.count} tin đăng với giá ${formatPrice(extra.price)}₫?`
    )
  )
    return;
  try {
    const token = localStorage.getItem("auth_token");
    const { data } = await api.post(
      `/moi-gioi/credit/mua`,
      { count: extra.count, price: extra.price },
    );
    if (data?.status) {
      alert(`✅ Đã mua thành công ${extra.count} tin đăng!`);
      loadPlans();
    }
  } catch (err) {
    console.error("Buy credits error:", err);
    alert("❌ Lỗi khi mua credit. Vui lòng thử lại.");
  }
};

// ✅ Confirm payment & redirect to Sepay
// ✅ Confirm payment & show QR modal
const confirmPayment = async () => {
  const token = localStorage.getItem("auth_token");
  if (!selectedId.value) {
    alert("Vui lòng chọn gói");
    return;
  }

  try {
    paymentLoading.value = true;

    const res = await api.post(
      "/moi-gioi/payment/create",
      {
        goi_tin_id: selectedId.value,
      },
      {
        headers: { Authorization: `Bearer ${token}` },
      }
    );

    if (res.data?.status && res.data?.data?.qr_image_url) {
      // ✅ HIỂN THỊ MODAL QR
      paymentData.value = res.data.data;
      showModal.value = false;
      showPaymentModal.value = true;

      // ✅ Start polling (KHÔNG CẦN vẽ QR)
      startPolling();
    } else {
      paymentResult.value = "error";
      paymentErrorMessage.value = "Không tạo được thanh toán";
    }
  } catch (err) {
    console.error("Payment error:", err);
    paymentResult.value = "error";
    paymentErrorMessage.value = err.response?.data?.message || "Lỗi kết nối";
  } finally {
    paymentLoading.value = false;
  }
};

// ✅ WebSocket listener
const setupWebSocket = () => {
  const user = JSON.parse(localStorage.getItem("user"));
  if (!user?.id || !window.Echo) return;

  window.Echo.channel(`payment.${user.id}`).listen(
    "ThanhToanThanhCong",
    (data) => {
      console.log("🔔 WebSocket payment success:", data);
      if (orderCode.value && paymentResult.value === "loading") {
        paymentResult.value = "success";
        paymentLoading.value = false;
        loadPlans();
      }
    }
  );
};

// ===== LIFECYCLE =====
onMounted(async () => {
  await loadPlans();
  setupWebSocket();
  await handlePaymentRedirect();
});

// Thêm vào phần script
watch(
  showPaymentModal,
  (newVal) => {
    if (newVal) {
      startCountdown();
    } else {
      stopCountdown();
    }
  },
  { immediate: true }
);

watch(
  () => route.query,
  async (newQuery) => {
    if (newQuery.status || newQuery.order_code) await handlePaymentRedirect();
  },
  { deep: true }
);

onUnmounted(() => {
  if (countdownInterval) clearInterval(countdownInterval);
});
</script>

<style scoped>
.btn-secondary {
  background: #e2e8f0;
  color: #94a3b8;
  border: 2px solid #cbd5e1;
  cursor: not-allowed;
  opacity: 0.6;
}

.pricing-card.current-plan {
  border: 3px solid #10b981;
  background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%);
  position: relative;
  overflow: hidden;
}

.current-plan-badge-top {
  position: absolute;
  top: 12px;
  left: -35px;
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
  padding: 0.5rem 3rem;
  transform: rotate(-45deg);
  font-size: 0.75rem;
  font-weight: 700;
  box-shadow: 0 2px 8px rgba(16, 185, 129, 0.4);
  display: flex;
  align-items: center;
  gap: 0.5rem;
  z-index: 10;
}

.current-plan-badge-top i {
  font-size: 1rem;
}

/* ===== BASE STYLES ===== */
.pricing-wrapper {
  --emerald-50: #ecfdf5;
  --emerald-100: #d1fae5;
  --emerald-500: #10b981;
  --emerald-600: #059669;
  --emerald-700: #047857;
  border-radius: 20px;
  max-width: 1200px;
  margin: 0 auto;
  font-family: "Inter", system-ui, sans-serif;
}

.text-emerald {
  color: var(--emerald-500) !important;
}

.border-emerald {
  border-color: var(--emerald-500) !important;
}

.text-success {
  color: #10b981 !important;
}

.text-danger {
  color: #dc3545 !important;
}

/* ===== TOGGLE ===== */
.billing-toggle {
  display: inline-flex;
  align-items: center;
  gap: 12px;
  background: #f8f9fa;
  padding: 6px;
  border-radius: 50px;
  font-weight: 500;
  color: #6c757d;
  font-size: 0.9rem;
}

.billing-toggle span {
  cursor: pointer;
  padding: 8px 16px;
  border-radius: 40px;
  transition: 0.2s;
}

.billing-toggle span.active {
  background: white;
  color: var(--emerald-600);
  font-weight: 600;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.toggle-switch {
  width: 44px;
  height: 24px;
  background: #dee2e6;
  border-radius: 50px;
  position: relative;
  cursor: pointer;
  transition: 0.3s;
}

.toggle-switch.yearly {
  background: var(--emerald-500);
}

.toggle-knob {
  width: 18px;
  height: 18px;
  background: white;
  border-radius: 50%;
  position: absolute;
  top: 3px;
  left: 3px;
  transition: 0.3s;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
}

.toggle-switch.yearly .toggle-knob {
  transform: translateX(20px);
}

.badge-discount {
  background: #fef08a;
  color: #854d0e;
  font-size: 0.7rem;
  padding: 2px 8px;
  border-radius: 10px;
  margin-left: 6px;
  font-weight: 600;
}

/* ===== GRID & CARDS ===== */
.pricing-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.5rem;
  align-items: start;
}

.pricing-card {
  background: linear-gradient(145deg, #ffffff, #f8fafc);
  border-radius: 20px;
  padding: 2rem 1.5rem;
  border: 1px solid #e2e8f0;
  position: relative;
  display: flex;
  flex-direction: column;
  min-height: 520px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08),
    inset 0 1px 0 rgba(255, 255, 255, 0.6);
  transition: all 0.35s ease;
  overflow: visible;
}

.pricing-card:hover {
  transform: translateY(-8px) scale(1.02);
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.12),
    0 0 0 1px rgba(16, 185, 129, 0.15);
}

.pricing-card.popular {
  border: 2px solid #10b981;
  transform: scale(1.06);
  background: linear-gradient(180deg, #ecfdf5, #ffffff);
  box-shadow: 0 20px 50px rgba(16, 185, 129, 0.25);
}

.popular-badge {
  position: absolute;
  top: 40px;
  right: -40px;
  /* ✅ Giảm từ -35px xuống -30px để vừa khít hơn */
  background: linear-gradient(135deg, #f59e0b, #d97706);
  color: white;
  font-size: 0.7rem;
  font-weight: 700;
  padding: 6px 45px;
  /* ✅ Tăng padding để badge dài hơn */
  transform: rotate(45deg);
  letter-spacing: 0.5px;
  z-index: 10;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

.card-header {
  text-align: center;
  margin-bottom: 1.5rem;
}

.plan-name {
  font-weight: 800;
  font-size: 1.4rem;
  margin: 0 0 0.5rem;
}

.plan-desc {
  color: #64748b;
  font-size: 0.9rem;
  margin: 0;
  min-height: 40px;
}

.card-price {
  text-align: center;
  margin-bottom: 1rem;
}

/* Giá gốc bị gạch ngang */
.original-price {
  text-decoration: line-through;
  color: #9ca3af !important;
  /* Màu xám nhạt */
  font-size: 1.6rem;
  margin-right: 0.5rem;
  opacity: 0.7;
}

/* Giá hiện tại (0đ) */
.current-price {
  color: #10b981 !important;
  /* Màu xanh emerald */
  font-size: 2.4rem;
  font-weight: 800;
  background: linear-gradient(45deg, #10b981, #059669);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

/* Responsive cho mobile */
@media (max-width: 768px) {
  .original-price {
    font-size: 1.2rem;
  }

  .current-price {
    font-size: 1.8rem;
  }
}

.amount {
  font-size: 2.4rem;
  font-weight: 800;
  background: linear-gradient(45deg, #10b981, #059669);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.period {
  color: #64748b;
  font-size: 0.9rem;
  font-weight: 500;
}

.post-allowance {
  background: var(--emerald-50);
  border-radius: 12px;
  padding: 12px 16px;
  margin-bottom: 1.5rem;
  text-align: center;
  font-size: 0.95rem;
  color: #065f46;
}

.post-allowance small {
  font-size: 0.8rem;
  margin-top: 4px;
}

.feature-list {
  list-style: none;
  padding: 0;
  margin: 0 0 1.5rem;
  flex-grow: 1;
}

.feature-list li {
  transition: 0.2s;
  margin-bottom: 12px;
  font-size: 0.9rem;
  display: flex;
  align-items: flex-start;
  color: #334155;
  line-height: 1.4;
}

.feature-list li:hover {
  transform: translateX(5px);
  color: #10b981;
}

.feature-list li i {
  margin-top: 3px;
  flex-shrink: 0;
}

.current-plan-badge {
  background: var(--emerald-100);
  color: var(--emerald-700);
  padding: 8px 12px;
  border-radius: 8px;
  font-size: 0.85rem;
  font-weight: 500;
  text-align: center;
  margin-bottom: 12px;
}

/* ===== BUTTONS ===== */
.btn {
  padding: 0.875rem 2rem;
  border: none;
  border-radius: 12px;
  font-weight: 600;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.2s;
  font-size: 0.95rem;
  justify-content: center;
}

.btn-emerald {
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
}

.btn-emerald:hover {
  background: #047857;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.btn-outline-emerald {
  background: transparent;
  color: var(--emerald-600);
  border: 2px solid var(--emerald-500);
}

.btn-outline-emerald:hover:not(:disabled) {
  background: var(--emerald-50);
  color: var(--emerald-700);
}

.btn-secondary {
  background: #e2e8f0;
  color: #64748b;
  border: none;
  cursor: not-allowed;
}

.btn-success {
  background: #10b981;
  color: white;
  padding: 0.75rem 2rem;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-success:hover {
  background: #059669;
  transform: translateY(-2px);
}

.btn-outline {
  background: white;
  color: #6b7280;
  border: 2px solid #e5e7eb;
  padding: 0.75rem 1.5rem;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-outline:hover {
  background: #f9fafb;
}

.btn-outline-primary {
  background: white;
  color: #001f7c;
  border: 2px solid #001f7c;
  padding: 0.75rem 1.5rem;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-outline-primary:hover {
  background: #f0f4ff;
}

.btn-group {
  display: flex;
  gap: 0.75rem;
}

.extra-credits {
  border-top: 1px dashed #e2e8f0;
  padding-top: 1rem;
}

.extra-credits .btn-outline-emerald {
  padding: 8px 4px;
  font-size: 0.8rem;
}

.extra-credits .btn-outline-emerald small {
  font-size: 0.75rem;
  opacity: 0.9;
}

/* ===== FAQ ===== */
.faq-section {
  max-width: 800px;
  margin: 0 auto;
}

.faq-list {
  max-width: 800px;
  margin: 0 auto;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.faq-item {
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  overflow: hidden;
  background: #ffffff;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
}

.faq-item:hover {
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15);
  border-color: #10b981;
}

.faq-item.active {
  border-color: #10b981;
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
}

.faq-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  background-color: #f8fafc;
  transition: background 0.3s;
}

.faq-item.active .faq-header {
  background-color: #ecfdf5;
}

.faq-question-text {
  font-weight: 600;
  font-size: 1rem;
  color: #1a202c !important;
}

.faq-icon {
  font-size: 1.2rem;
  color: #10b981;
  transition: transform 0.3s ease;
}

.faq-body {
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.3s ease-out;
}

.faq-content {
  padding: 1rem 1.5rem;
  color: #4a5568 !important;
  line-height: 1.6;
  background-color: #ffffff;
  border-top: 1px solid #f1f5f9;
}

/* ===== VIP BADGE ===== */
.vip-badge {
  position: absolute;
  top: 12px;
  left: 12px;
  background: linear-gradient(45deg, gold, orange);
  color: #000;
  font-size: 11px;
  padding: 4px 10px;
  border-radius: 8px;
  font-weight: bold;
  z-index: 10;
  box-shadow: 0 0 10px rgba(255, 200, 0, 0.6);
  animation: vipPulse 1.5s infinite;
}

@keyframes vipPulse {

  0%,
  100% {
    box-shadow: 0 0 5px rgba(255, 200, 0, 0.5);
  }

  50% {
    box-shadow: 0 0 20px rgba(255, 200, 0, 1);
  }
}

/* ===== PAYMENT MODAL (Xác nhận) ===== */
.payment-modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 999;
}

.modal-content {
  background: white;
  padding: 25px;
  border-radius: 15px;
  width: 350px;
  text-align: center;
}

.modal-actions {
  display: flex;
  gap: 10px;
  margin-top: 20px;
}

.modal-features {
  list-style: none;
  padding: 0;
  margin: 1rem 0;
  text-align: left;
  background: #f8fafc;
  border-radius: 12px;
  padding: 1rem;
}

.modal-features li {
  padding: 8px 0;
  color: #374151;
  font-size: 0.9rem;
  display: flex;
  align-items: flex-start;
  border-bottom: 1px solid #e2e8f0;
}

.modal-features li:last-child {
  border-bottom: none;
}

.modal-features li i {
  margin-top: 2px;
  flex-shrink: 0;
}

.modal-features li span {
  flex-grow: 1;
}

/* ===== PAYMENT RESULT MODAL ===== */
.payment-result-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  animation: fadeIn 0.3s ease;
}

.payment-result-card {
  background: white;
  border-radius: 20px;
  padding: 2.5rem 2rem;
  text-align: center;
  max-width: 400px;
  width: 90%;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
  animation: slideUp 0.4s ease;
}

.payment-result-card.success {
  border: 3px solid #10b981;
}

.payment-result-card.error {
  border: 3px solid #dc3545;
}

.payment-result-card.cancel {
  border: 3px solid #6c757d;
}

.result-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
}

.icon-wrapper {
  width: 70px;
  height: 70px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2.5rem;
}

.icon-wrapper.success {
  background: #d1fae5;
  color: #10b981;
}

.icon-wrapper.error {
  background: #fee2e2;
  color: #dc3545;
}

.icon-wrapper.cancel {
  background: #e5e7eb;
  color: #6c757d;
}

/* Checkmark animation */
.checkmark {
  width: 52px;
  height: 52px;
  border-radius: 50%;
  display: block;
  stroke-width: 2;
  stroke: #10b981;
  stroke-miterlimit: 10;
  box-shadow: inset 0 0 0 #10b981;
  animation: fill 0.4s ease-in-out 0.4s forwards,
    scale 0.3s ease-in-out 0.9s both;
}

.checkmark-circle {
  stroke-width: 2;
  stroke-dasharray: 166;
  stroke-dashoffset: 166;
  stroke: #10b981;
  animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
}

.checkmark-check {
  stroke-width: 3;
  stroke-dasharray: 48;
  stroke-dashoffset: 48;
  animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
}

@keyframes stroke {
  100% {
    stroke-dashoffset: 0;
  }
}

@keyframes scale {

  0%,
  100% {
    transform: none;
  }

  50% {
    transform: scale3d(1.1, 1.1, 1);
  }
}

@keyframes fill {
  100% {
    box-shadow: inset 0 0 0 30px #d1fae5;
  }
}

.title {
  font-size: 1.4rem;
  font-weight: 700;
  margin: 0;
}

.description {
  color: #6b7280;
  font-size: 1rem;
  margin: 0;
  line-height: 1.5;
}

.order-code {
  background: #f3f4f6;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-size: 0.9rem;
  color: #374151;
  margin: 0;
}

.order-code strong {
  font-family: monospace;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #e5e7eb;
  border-top-color: #10b981;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }

  to {
    opacity: 1;
  }
}

@keyframes slideUp {
  from {
    transform: translateY(30px);
    opacity: 0;
  }

  to {
    transform: translateY(0);
    opacity: 1;
  }
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
  .pricing-card.popular {
    transform: none;
  }

  .pricing-card.popular:hover {
    transform: translateY(-4px);
  }

  .amount {
    font-size: 1.8rem;
  }

  .popular-badge {
    right: -25px;
    padding: 4px 25px;
    font-size: 0.65rem;
  }
}

@media (max-width: 480px) {
  .payment-result-card {
    padding: 2rem 1.5rem;
  }

  .title {
    font-size: 1.2rem;
  }
}

/*  */
/* ===== QR PAYMENT MODAL ===== */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  animation: fadeIn 0.3s ease;
}

.modal-overlay .modal-content {
  background: white;
  padding: 2rem;
  border-radius: 16px;
  max-width: 450px;
  width: 90%;
  text-align: center;
  position: relative;
  animation: slideUp 0.4s ease;
}

.qr-container {
  margin: 1.5rem 0;
  display: flex;
  justify-content: center;
}

.qr-container canvas {
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 1rem;
  background: white;
}

.payment-details {
  background: #f9fafb;
  padding: 1rem;
  border-radius: 8px;
  margin: 1rem 0;
  text-align: left;
}

.payment-details p {
  margin: 0.5rem 0;
  font-size: 0.9rem;
}

.payment-details .text-warning {
  color: #d97706;
  font-weight: 600;
  font-size: 0.85rem;
}

.btn-check {
  width: 100%;
  padding: 0.875rem;
  background: #10b981;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-check:hover {
  background: #059669;
  transform: translateY(-2px);
}

.qr-container {
  margin: 1.5rem 0;
  display: flex;
  justify-content: center;
  align-items: center;
}

.qr-image {
  max-width: 280px;
  width: 100%;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  padding: 1rem;
  background: white;
}

.qr-loading {
  text-align: center;
  padding: 2rem;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #e5e7eb;
  border-top-color: #10b981;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.modal-header h3 {
  margin: 0;
  font-size: 1.25rem;
  color: #1f2937;
}

.countdown-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: #f3f4f6;
  color: #6b7280;
  border-radius: 99px;
  font-weight: 600;
  font-size: 0.875rem;
  transition: all 0.3s;
}

.countdown-badge.urgent {
  background: #fee2e2;
  color: #dc2626;
  animation: pulse 1s infinite;
}

@keyframes pulse {

  0%,
  100% {
    opacity: 1;
  }

  50% {
    opacity: 0.7;
  }
}

.payment-info-box {
  background: #f9fafb;
  border-radius: 12px;
  padding: 1.5rem;
  margin: 1.5rem 0;
}

.info-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #e5e7eb;
}

.info-row:last-child {
  margin-bottom: 0;
  padding-bottom: 0;
  border-bottom: none;
}

.info-row .label {
  font-weight: 600;
  color: #6b7280;
  font-size: 0.875rem;
}

.info-row .value {
  font-weight: 700;
  color: #1f2937;
  font-size: 1rem;
}

.info-row .value.code {
  font-family: monospace;
  background: #fef3c7;
  padding: 0.25rem 0.75rem;
  border-radius: 6px;
  color: #92400e;
}

.info-row.warning {
  background: #fef3c7;
  padding: 1rem;
  border-radius: 8px;
  margin-top: 1rem;
}

.info-row.warning i {
  color: #f59e0b;
  font-size: 1.25rem;
  margin-right: 0.75rem;
}

.info-row.warning span {
  color: #92400e;
  font-size: 0.875rem;
  line-height: 1.5;
}

.action-buttons {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-top: 1.5rem;
}

.btn-success {
  background: #10b981;
  color: white;
}

.btn-success:hover:not(:disabled) {
  background: #059669;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.btn-success:disabled {
  background: #d1d5db;
  cursor: not-allowed;
}

.btn-link {
  background: transparent;
  color: #3b82f6;
  text-decoration: underline;
  font-size: 0.875rem;
}

.btn-link:hover {
  color: #2563eb;
}

.btn-text {
  background: #f3f4f6;
  color: #6b7280;
}

.btn-text:hover {
  background: #e5e7eb;
}

.expired-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.95);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 2rem;
  border-radius: 16px;
}

.expired-overlay i {
  font-size: 4rem;
  color: #ef4444;
  margin-bottom: 1rem;
}

.expired-overlay h4 {
  color: #1f2937;
  margin-bottom: 0.5rem;
}

.expired-overlay p {
  color: #6b7280;
  margin-bottom: 1.5rem;
}

/* Nút đang sử dụng - làm mờ đi */
.btn-secondary {
  background: #e2e8f0;
  color: #94a3b8;
  border: 2px solid #cbd5e1;
  cursor: not-allowed;
  opacity: 0.6;
  /* ✅ Làm mờ */
  pointer-events: none;
  /* ✅ Không cho click */
  position: relative;
}

.btn-secondary::after {
  content: "Đang sử dụng";
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: #10b981;
  color: white;
  padding: 0.25rem 1rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 700;
  opacity: 0;
  transition: opacity 0.2s;
}

.btn-secondary:hover::after {
  opacity: 1;
}

/* Card có gói đang dùng */
.pricing-card.current-plan {
  border: 3px solid #10b981;
  background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%);
  position: relative;
  overflow: hidden;
}

/* Ribbon góc trên */
.current-plan-ribbon {
  position: absolute;
  top: 12px;
  left: -35px;
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
  padding: 0.5rem 3rem;
  transform: rotate(-45deg);
  font-size: 0.75rem;
  font-weight: 700;
  box-shadow: 0 2px 8px rgba(16, 185, 129, 0.4);
  display: flex;
  align-items: center;
  gap: 0.5rem;
  z-index: 10;
}

.current-plan-ribbon i {
  font-size: 1rem;
}

/* Nút disabled - mờ đi */
.pricing-card.current-plan .btn {
  background: #e2e8f0;
  color: #94a3b8;
  border: 2px solid #cbd5e1;
  cursor: not-allowed;
  opacity: 0.6;
  pointer-events: none;
  position: relative;
  overflow: hidden;
}

.pricing-card.current-plan .btn::before {
  content: "";
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg,
      transparent,
      rgba(255, 255, 255, 0.4),
      transparent);
  animation: shimmer 2s infinite;
}

@keyframes shimmer {
  0% {
    left: -100%;
  }

  100% {
    left: 100%;
  }
}

.btn-cancel {
  background: #f3f4f6;
  color: #6b7280;
  border: 2px solid #e5e7eb;
  cursor: pointer;
  opacity: 1;
}

.btn-cancel:hover:not(:disabled) {
  background: #e5e7eb;
}

.btn-cancel:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>