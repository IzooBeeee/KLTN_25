<template>
  <Teleport to="body">
    <div class="dl-overlay" @click.self="$emit('close')">
      <div class="dl-modal">
        <div class="dl-header">
          <div class="dl-header__icon">📅</div>
          <div>
            <h3 class="dl-header__title">Đặt lịch hẹn xem nhà</h3>
            <p class="dl-header__subtitle">{{ propertyTitle }}</p>
          </div>
          <button class="dl-close" @click="$emit('close')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="dl-body">
          <div class="dl-field">
            <label><i class="fa-regular fa-calendar"></i> Ngày hẹn</label>
            <input type="date" v-model="form.ngay_hen" :min="minDate" class="dl-input" />
            <span v-if="errors.ngay_hen" class="dl-error">{{ errors.ngay_hen }}</span>
          </div>
          <div class="dl-field">
            <label><i class="fa-regular fa-clock"></i> Giờ hẹn</label>
            <div class="dl-time-grid">
              <button v-for="time in timeSlots" :key="time" class="dl-time-btn"
                :class="{ active: form.gio_hen === time, disabled: isTimePast(time) }" :disabled="isTimePast(time)"
                @click="!isTimePast(time) && (form.gio_hen = time)">{{ time }}</button>
            </div>
            <span v-if="errors.gio_hen" class="dl-error">{{ errors.gio_hen }}</span>
          </div>
          <div class="dl-field">
            <label><i class="fa-regular fa-note-sticky"></i> Ghi chú (tùy chọn)</label>
            <textarea v-model="form.ghi_chu" placeholder="Ví dụ: Tôi muốn xem vào buổi chiều..." class="dl-textarea"
              rows="3"></textarea>
          </div>
        </div>
        <div class="dl-footer">
          <button class="dl-btn dl-btn--secondary" @click="$emit('close')">Hủy</button>
          <button class="dl-btn dl-btn--primary" @click="submit" :disabled="loading">
            <span v-if="loading" class="dl-spinner"></span>
            <span v-else><i class="fa-solid fa-check"></i> Xác nhận đặt lịch</span>
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, computed } from 'vue';
import api from '@/axios/config';
import { createToaster } from '@meforma/vue-toaster';

const toaster = createToaster({ position: 'top-right', duration: 3000 });
const props = defineProps({ propertyId: Number, propertyTitle: String });
const emit = defineEmits(['close', 'success']);

const loading = ref(false);
const errors = ref({});
const form = ref({ bat_dong_san_id: props.propertyId, ngay_hen: '', gio_hen: '', ghi_chu: '' });
const minDate = computed(() => new Date().toISOString().split('T')[0]);
const timeSlots = ['08:00', '08:30', '09:00', '09:30', '10:00', '10:30', '11:00', '11:30', '13:00', '13:30', '14:00', '14:30', '15:00', '15:30', '16:00', '16:30', '17:00', '17:30'];

// Kiểm tra giờ đã qua chưa (chỉ áp dụng khi chọn ngày hôm nay)
function isTimePast(time) {
  const today = new Date().toISOString().split('T')[0];
  if (form.value.ngay_hen !== today) return false;
  const now = new Date();
  const [h, m] = time.split(':').map(Number);
  return now.getHours() > h || (now.getHours() === h && now.getMinutes() >= m);
}

function validate() {
  errors.value = {};
  if (!form.value.ngay_hen) errors.value.ngay_hen = 'Vui lòng chọn ngày';
  if (!form.value.gio_hen) errors.value.gio_hen = 'Vui lòng chọn giờ';
  return Object.keys(errors.value).length === 0;
}

async function submit() {
  if (!validate()) return;
  loading.value = true;
  try {
    const res = await api.post('/khach-hang/lich-hen/dat', form.value);
    if (res.data?.status) {
      toaster.success(`✅ Đặt lịch thành công!\n📅 ${form.value.ngay_hen} lúc ${form.value.gio_hen}\n⏰ Môi giới sẽ liên hệ xác nhận`, { duration: 4000 });
      emit('success');
      emit('close');
    } else {
      toaster.error(res.data?.message || '❌ Có lỗi xảy ra khi đặt lịch');
    }
  } catch (e) {
    const msg = e.response?.status === 401
      ? '🔒 Bạn cần đăng nhập để đặt lịch'
      : (e.response?.data?.message || '❌ Không thể đặt lịch. Vui lòng thử lại sau');
    toaster.error(msg);
  } finally {
    loading.value = false;
  }
}
</script>

<style scoped>
.dl-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}

.dl-modal {
  background: #fff;
  border-radius: 20px;
  width: 100%;
  max-width: 480px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 25px 60px rgba(0, 0, 0, 0.2);
}

.dl-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.25rem;
  border-bottom: 1px solid #f1f5f9;
}

.dl-header__icon {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, #3b82f6, #8b5cf6);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
}

.dl-header__title {
  font-size: 1.1rem;
  font-weight: 800;
  color: #0f172a;
  margin: 0;
}

.dl-header__subtitle {
  font-size: 0.875rem;
  color: #64748b;
  margin: 0;
}

.dl-close {
  margin-left: auto;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  border: none;
  background: #f1f5f9;
  color: #64748b;
  cursor: pointer;
}

.dl-close:hover {
  background: #e2e8f0;
}

.dl-body {
  padding: 1.25rem;
}

.dl-field {
  margin-bottom: 1rem;
}

.dl-field label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.5rem;
}

.dl-input {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  font-size: 0.95rem;
}

.dl-input:focus {
  border-color: #3b82f6;
  outline: none;
}

.dl-time-grid {
  display: grid;
  grid-template-columns: repeat(6, 1fr);
  gap: 0.5rem;
}

.dl-time-btn {
  padding: 0.5rem;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: #fff;
  font-size: 0.8rem;
  cursor: pointer;
  transition: all 0.2s;
}

.dl-time-btn.disabled {
  background: #f1f5f9;
  color: #c0c9d4;
  border-color: #e9ecef;
  cursor: not-allowed;
  text-decoration: line-through;
  opacity: 0.6;
}

.dl-time-btn:hover {
  border-color: #3b82f6;
  color: #3b82f6;
}

.dl-time-btn.active {
  background: linear-gradient(135deg, #3b82f6, #8b5cf6);
  color: #fff;
  border-color: transparent;
}

.dl-textarea {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  font-size: 0.95rem;
  resize: vertical;
}

.dl-error {
  color: #ef4444;
  font-size: 0.8rem;
  margin-top: 0.25rem;
  display: block;
}

.dl-footer {
  display: flex;
  gap: 0.75rem;
  padding: 1rem 1.25rem;
  border-top: 1px solid #f1f5f9;
}

.dl-btn {
  flex: 1;
  padding: 0.875rem 1rem;
  border-radius: 12px;
  font-weight: 600;
  cursor: pointer;
  border: none;
}

.dl-btn--secondary {
  background: #f1f5f9;
  color: #475569;
}

.dl-btn--secondary:hover {
  background: #e2e8f0;
}

.dl-btn--primary {
  background: linear-gradient(135deg, #3b82f6, #8b5cf6);
  color: #fff;
}

.dl-btn--primary:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.35);
}

.dl-btn--primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.dl-spinner {
  display: inline-block;
  width: 20px;
  height: 20px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>
