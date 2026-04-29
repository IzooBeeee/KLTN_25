<!-- src/components/MoiGioi/PaymentResultModal.vue -->
<template>
  <div v-if="modelValue" class="payment-result-overlay" @click.self="$emit('update:modelValue', false)">
    <div class="payment-result-card" :class="type">
      
      <!-- ✅ SUCCESS -->
      <div v-if="type === 'success'" class="result-content">
        <div class="icon-wrapper success">
          <svg viewBox="0 0 52 52" class="checkmark">
            <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
            <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
          </svg>
        </div>
        <h3 class="title text-success">Thanh toán thành công!</h3>
        <p class="description">Gói <strong>{{ planName }}</strong> đã được kích hoạt.</p>
        <p class="order-code">Mã đơn: <strong>{{ orderCode }}</strong></p>
        <button class="btn btn-success" @click="handleClose">Đóng</button>
      </div>

      <!-- ❌ FAILED -->
      <div v-else-if="type === 'error'" class="result-content">
        <div class="icon-wrapper error">
          <i class="bx bx-x-circle"></i>
        </div>
        <h3 class="title text-danger">Thanh toán thất bại</h3>
        <p class="description">{{ message || 'Có lỗi xảy ra. Vui lòng thử lại.' }}</p>
        <p class="order-code">Mã đơn: <strong>{{ orderCode }}</strong></p>
        <div class="btn-group">
          <button class="btn btn-outline" @click="handleClose">Đóng</button>
          <button class="btn btn-outline-primary" @click="handleRetry">Thử lại</button>
        </div>
      </div>

      <!-- ⏹️ CANCEL -->
      <div v-else-if="type === 'cancel'" class="result-content">
        <div class="icon-wrapper cancel">
          <i class="bx bx-minus-circle"></i>
        </div>
        <h3 class="title">Đã hủy thanh toán</h3>
        <p class="description">Bạn có thể thử lại bất cứ lúc nào.</p>
        <p class="order-code">Mã đơn: <strong>{{ orderCode }}</strong></p>
        <button class="btn btn-outline-primary" @click="handleClose">Quay lại</button>
      </div>

      <!-- ⏳ LOADING -->
      <div v-else-if="type === 'loading'" class="result-content">
        <div class="spinner"></div>
        <p class="description">Đang xác nhận thanh toán...</p>
        <p class="order-code">Mã đơn: <strong>{{ orderCode }}</strong></p>
      </div>

    </div>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
  modelValue: Boolean,
  type: {
    type: String,
    default: 'success', // 'success' | 'error' | 'cancel' | 'loading'
  },
  planName: String,
  orderCode: String,
  message: String,
});

const emit = defineEmits(['update:modelValue', 'close', 'retry']);

const handleClose = () => {
  emit('update:modelValue', false);
  emit('close');
};

const handleRetry = () => {
  emit('update:modelValue', false);
  emit('retry');
};
</script>

<style scoped>
.payment-result-overlay {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
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
  animation: fill 0.4s ease-in-out 0.4s forwards, scale 0.3s ease-in-out 0.9s both;
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

@keyframes stroke { 100% { stroke-dashoffset: 0; } }
@keyframes scale { 0%, 100% { transform: none; } 50% { transform: scale3d(1.1, 1.1, 1); } }
@keyframes fill { 100% { box-shadow: inset 0 0 0 30px #d1fae5; } }

.title {
  font-size: 1.4rem;
  font-weight: 700;
  margin: 0;
}
.text-success { color: #10b981; }
.text-danger { color: #dc3545; }

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

.btn {
  padding: 0.75rem 2rem;
  border: none;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-success {
  background: #10b981;
  color: white;
}
.btn-success:hover {
  background: #059669;
  transform: translateY(-2px);
}
.btn-outline {
  background: white;
  color: #6b7280;
  border: 2px solid #e5e7eb;
}
.btn-outline-primary {
  background: white;
  color: #001f7c;
  border: 2px solid #001f7c;
}
.btn-outline:hover {
  background: #f9fafb;
}
.btn-outline-primary:hover {
  background: #f0f4ff;
}

.btn-group {
  display: flex;
  gap: 0.75rem;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #e5e7eb;
  border-top-color: #10b981;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes slideUp { from { transform: translateY(30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

@media (max-width: 480px) {
  .payment-result-card {
    padding: 2rem 1.5rem;
  }
  .title {
    font-size: 1.2rem;
  }
}
</style>