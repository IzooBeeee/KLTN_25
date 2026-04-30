<template>
  <div class="property-posting-wrapper">
    <!-- Progress Steps -->
    <div class="progress-header">
      <div class="progress-track">
        <div v-for="(step, index) in steps" :key="index" class="progress-item" :class="{
          active: currentStep === index + 1,
          completed: currentStep > index + 1,
        }">
          <div class="step-indicator">
            <span class="step-number">{{ index + 1 }}</span>
            <i v-if="currentStep > index + 1" class="fas fa-check check-icon"></i>
          </div>
          <span class="step-name">{{ step }}</span>
        </div>
      </div>
    </div>

    <div class="posting-grid">
      <!-- Sidebar Trái: Thống kê & Gói tin -->
      <aside class="sidebar-left">
        <div class="sidebar-section">
          <h3 class="sidebar-title">Gói tin của bạn</h3>
          <div class="credit-card">
            <div class="credit-label">Số tin còn lại</div>
            <div class="credit-value">{{ soTinConLai }}</div>
            <div class="progress-mini">
              <div class="progress-bar-mini" :style="{ width: (soTinConLai / 10 * 100) + '%' }"></div>
            </div>
            <p class="credit-hint">Nâng cấp để nhận thêm nhiều ưu đãi và lượt đăng tin VIP.</p>
          </div>
        </div>

        <div class="sidebar-section">
          <h3 class="sidebar-title">Hướng dẫn đăng tin</h3>
          <div class="guide-list">
            <div class="guide-item active">
              <i class="fas fa-check-circle"></i>
              <span>Cung cấp đủ thông tin</span>
            </div>
            <div class="guide-item" :class="{ active: form.images.length > 0 }">
              <i class="fas fa-image"></i>
              <span>Hình ảnh sắc nét</span>
            </div>
            <div class="guide-item" :class="{ active: form.latitude }">
              <i class="fas fa-map-marker-alt"></i>
              <span>Vị trí chính xác</span>
            </div>
          </div>
        </div>

        <div class="sidebar-section upgrade-box">
          <div class="upgrade-icon">
            <i class="fas fa-crown"></i>
          </div>
          <h4>Mua thêm gói tin</h4>
          <p>Tăng khả năng tiếp cận khách hàng tiềm năng lên gấp 5 lần.</p>
          <button class="btn-upgrade" @click="$router.push('/moi-gioi/goi-tin')">Nâng cấp ngay</button>
        </div>
      </aside>

      <!-- Main Content -->
      <main class="main-form-area">
        <!-- Step 1: Basic Information -->
        <div v-if="currentStep === 1" class="form-step fade-in">
          <div class="section-header">
            <h2 class="section-title">Thông tin chi tiết tài sản</h2>
            <p class="section-desc">
              Cung cấp thông tin cơ bản về bất động sản của bạn
            </p>
          </div>

          <div class="form-container">
            <!-- Title -->
            <div class="form-row">
              <div class="form-group full-width">
                <label class="form-label required">
                  Tiêu đề tin đăng
                  <span class="label-hint">Viết hoa chữ cái đầu, không viết hoa toàn bộ</span>
                </label>
                <input v-model="form.tieu_de" type="text" class="form-input"
                  placeholder="Ví dụ: Căn hộ Penhouse Indochina Riverside 3PN view sông Hàn" />
                <div class="char-count">{{ form.tieu_de.length }}/150</div>
              </div>
            </div>

            <div class="form-row">
              <!-- Property Type -->
              <div class="form-group">
                <label class="form-label required">Loại hình bất động sản</label>
                <div class="select-wrapper">
                  <select v-model="form.loai_id" class="form-select">
                    <option value="">Chọn loại BĐS</option>
                    <option v-for="loai in loaiBDSList" :key="loai.id" :value="loai.id">
                      {{ loai.ten }}
                    </option>
                  </select>
                  <i class="fas fa-chevron-down select-icon"></i>
                </div>
              </div>

              <!-- Project Name -->
              <div class="form-group">
                <label class="form-label">Tên dự án (nếu có)</label>
                <input v-model="form.ten_du_an" type="text" class="form-input" placeholder="Indochina Estate" />
              </div>
            </div>

            <div class="form-row">
              <!-- Price -->
              <div class="form-group">
                <label class="form-label required">Giá bán/thuê</label>
                <div class="input-group">
                  <input v-model="form.gia" type="number" class="form-input" placeholder="0" />
                  <span class="input-addon">VNĐ</span>
                </div>
                <small class="form-hint">Để trống nếu thương lượng</small>
              </div>

              <!-- Area -->
              <div class="form-group">
                <label class="form-label required">Diện tích</label>
                <div class="input-group">
                  <input v-model="form.dien_tich" type="number" step="0.01" class="form-input" placeholder="0" />
                  <span class="input-addon">m²</span>
                </div>
              </div>
            </div>

            <div class="form-row">
              <!-- Bedrooms -->
              <div class="form-group">
                <label class="form-label">Số phòng ngủ</label>
                <div class="button-group">
                  <button v-for="n in [1, 2, 3]" :key="n" type="button" class="btn-chip"
                    :class="{ active: form.so_phong_ngu === n }" @click="form.so_phong_ngu = n">
                    {{ n }}
                  </button>
                  <button type="button" class="btn-chip" :class="{ active: form.so_phong_ngu === 4 }"
                    @click="form.so_phong_ngu = 4">
                    4+
                  </button>
                </div>
              </div>

              <!-- Bathrooms -->
              <div class="form-group">
                <label class="form-label">Số phòng tắm</label>
                <div class="button-group">
                  <button v-for="n in [1, 2]" :key="n" type="button" class="btn-chip"
                    :class="{ active: form.so_phong_tam === n }" @click="form.so_phong_tam = n">
                    {{ n }}
                  </button>
                  <button type="button" class="btn-chip" :class="{ active: form.so_phong_tam === 3 }"
                    @click="form.so_phong_tam = 3">
                    3+
                  </button>
                </div>
              </div>
            </div>

            <!-- Description -->
            <div class="form-row">
              <div class="form-group full-width">
                <label class="form-label">Mô tả chi tiết</label>
                <textarea v-model="form.mo_ta" class="form-textarea" rows="6"
                  placeholder="Mô tả chi tiết về vị trí, tiện ích, pháp lý, hướng nhà..."></textarea>
                <div class="textarea-toolbar">
                  <button type="button" class="toolbar-btn" title="In đậm">
                    <i class="fas fa-bold"></i>
                  </button>
                  <button type="button" class="toolbar-btn" title="In nghiêng">
                    <i class="fas fa-italic"></i>
                  </button>
                  <button type="button" class="toolbar-btn" title="Gạch chân">
                    <i class="fas fa-underline"></i>
                  </button>
                  <button type="button" class="toolbar-btn" title="Danh sách">
                    <i class="fas fa-list-ul"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Quick Upload Preview -->
          <!-- <div class="quick-upload-section"> -->
          <!-- <div
              class="upload-card"
              @click="triggerFileInput"
              @dragover.prevent
              @drop="handleDrop"
            >
              <div class="upload-content">
                <div class="upload-icon-wrapper">
                  <i class="fas fa-cloud-upload-alt"></i>
                </div>
                <h4>Tải ảnh lên ngay</h4>
                <p>Kéo thả ảnh vào đây hoặc click để chọn file</p>
                <span class="upload-hint">Hỗ trợ: JPG, PNG (Tối đa 10MB)</span>
              </div>
              <input
                ref="fileInput"
                type="file"
                multiple
                accept="image/*"
                class="hidden-input"
                @change="handleFileSelect"
              />
            </div> -->

          <!-- Preview Gallery -->
          <!-- <div v-if="form.images.length > 0" class="image-preview-grid">
              <div
                v-for="(img, idx) in form.images"
                :key="idx"
                class="preview-item"
              >
                <img :src="getImageUrl(img)" :alt="'Preview ' + (idx + 1)" />
                <button class="remove-img" @click="removeImage(idx)">
                  <i class="fas fa-times"></i>
                </button>
                <span v-if="idx === 0" class="main-badge">Ảnh bìa</span>
              </div>
            </div> -->
          <!-- </div> -->
        </div>

        <!-- Step 2: Images & Video -->
        <div v-else-if="currentStep === 2" class="form-step fade-in">
          <div class="quick-upload-section">
            <div class="upload-card" @click="triggerFileInput" @dragover.prevent @drop="handleDrop">
              <div class="upload-content">
                <div class="upload-icon-wrapper">
                  <i class="fas fa-cloud-upload-alt"></i>
                </div>
                <h4>Tải ảnh lên ngay</h4>
                <p>Kéo thả ảnh vào đây hoặc click để chọn file</p>
                <span class="upload-hint">Hỗ trợ: JPG, PNG (Tối đa 10MB)</span>
              </div>
              <input ref="fileInput" type="file" multiple accept="image/*" class="hidden-input"
                @change="handleFileSelect" />
            </div>

            <!-- Preview Gallery -->
            <div v-if="imagePreviewUrls.length > 0" class="image-preview-grid">
              <div v-for="(url, idx) in imagePreviewUrls" :key="idx" class="preview-item">
                <img :src="url" :alt="'Preview ' + (idx + 1)" />
                <button class="remove-img" @click="removeImage(idx)">
                  <i class="fas fa-times"></i>
                </button>
                <span v-if="idx === 0" class="main-badge">Ảnh bìa</span>
              </div>
            </div>
          </div>
          <!-- <div class="section-header">
            <h2 class="section-title">Hình ảnh & Video</h2>
            <p class="section-desc">
              Thêm hình ảnh chất lượng cao để thu hút người xem
            </p>
          </div>
          <div class="empty-state">
            <i class="fas fa-images"></i>
            <h3>Quản lý media</h3>
            <p>Chức năng đang được phát triển</p>
          </div> -->
        </div>

        <!-- Step 3: Location & Map -->
        <div v-else-if="currentStep === 3" class="form-step fade-in">
          <div class="section-header">
            <h2 class="section-title">Vị trí & Bản đồ</h2>
            <p class="section-desc">
              Chọn vị trí chính xác của bất động sản trên bản đồ
            </p>
          </div>

          <div class="form-container">
            <!-- Province -->
            <div class="form-row">
              <div class="form-group">
                <label class="form-label required">Tỉnh / Thành phố</label>
                <div class="select-wrapper">
                  <select v-model="form.tinh_id" @change="onTinhChange" class="form-select">
                    <option value="">-- Chọn tỉnh/thành --</option>
                    <option v-for="tinh in tinhThanhList" :key="tinh.id" :value="tinh.id">
                      {{ tinh.ten }}
                    </option>
                  </select>
                  <i class="fas fa-chevron-down select-icon"></i>
                </div>
              </div>

              <!-- District -->
              <div class="form-group">
                <label class="form-label required">Quận / Huyện</label>
                <div class="select-wrapper">
                  <select v-model="form.quan_id" class="form-select" @change="onQuanChange">
                    <option value="">Chọn quận huyện</option>
                    <option v-for="quan in quanHuyenList" :key="quan.id" :value="quan.id">
                      {{ quan.ten }}
                    </option>
                  </select>
                  <i class="fas fa-chevron-down select-icon"></i>
                </div>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label class="form-label required">Phường / Xã</label>
                <div class="select-wrapper">
                  <select v-model="form.phuong_id" class="form-select">
                    <option value="">Chọn phường xã</option>
                    <option v-for="phuong in phuongList" :key="phuong.id" :value="phuong.id">
                      {{ phuong.ten }}
                    </option>
                  </select>
                  <i class="fas fa-chevron-down select-icon"></i>
                </div>
              </div>
            </div>

            <!-- Address Detail -->
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Số nhà / Ngõ ngách</label>
                <input v-model="soNha" type="text" class="form-input" placeholder="Ví dụ: 12/34A"
                  @input="updateDiaChiChiTiet" />
              </div>
              <div class="form-group">
                <label class="form-label">Địa chỉ tự động từ bản đồ</label>
                <input v-model="form.dia_chi_chi_tiet" type="text" class="form-input bg-gray-100" readonly
                  style="background-color: #f3f4f6; cursor: not-allowed"
                  placeholder="Địa chỉ sẽ tự động điền khi chọn trên bản đồ" />
              </div>
            </div>

            <!-- Map Container -->
            <div class="form-group full-width">
              <label class="form-label required">🗺️ Chọn vị trí trên bản đồ</label>
              <div class="map-wrapper">
                <div v-if="!mapLoaded" class="map-loading">
                  <i class="fas fa-spinner fa-spin"></i>
                  <span>Đang tải bản đồ...</span>
                </div>
                <div id="leaflet-map" class="leaflet-map-container"></div>
              </div>
              <small class="form-hint">💡 Click vào bản đồ để chọn vị trí bất động sản</small>

              <!-- Selected Coordinates Display -->
              <div v-if="form.latitude && form.longitude" class="coords-display mt-2">
                <i class="fas fa-check-circle text-success"></i>
                <strong>Đã chọn:</strong>
                {{ form.latitude.toFixed(6) }}, {{ form.longitude.toFixed(6) }}
              </div>
            </div>
          </div>
        </div>

        <!-- Step 4: Package & Confirmation -->
        <div v-else-if="currentStep === 4" class="form-step fade-in">
          <div class="section-header">
            <h2 class="section-title">Xác nhận & Gói tin đăng</h2>
            <p class="section-desc">Kiểm tra lại quyền lợi đăng tin của bạn trước khi hoàn tất</p>
          </div>

          <div class="package-confirmation-card">
            <div class="package-info-main">
              <div class="package-header">
                <div class="package-icon">
                  <i class="fas fa-gem"></i>
                </div>
                <div class="package-titles">
                  <h3>Gói tin hiện tại</h3>
                  <p class="package-name">VIP Broker Package</p>
                </div>
                <div class="status-badge" :class="hetTin ? 'expired' : 'active'">
                  {{ hetTin ? 'Hết lượt' : 'Đang hoạt động' }}
                </div>
              </div>

              <div class="package-stats-grid">
                <div class="p-stat">
                  <span class="p-label">Số tin còn lại</span>
                  <span class="p-value" :class="{ 'text-danger': hetTin }">{{ soTinConLai }} tin</span>
                </div>
                <div class="p-stat">
                  <span class="p-label">Ngày hết hạn</span>
                  <span class="p-value">{{ formatDate(form.ngay_het_han_goi) || '30/12/2026' }}</span>
                </div>
                <div class="p-stat">
                  <span class="p-label">Chi phí đăng tin</span>
                  <span class="p-value text-success">Miễn phí (Trừ 1 tin)</span>
                </div>
              </div>
            </div>

            <div v-if="hetTin" class="alert-box danger">
              <i class="fas fa-exclamation-triangle"></i>
              <p>Bạn đã hết số lượng tin đăng. Vui lòng mua thêm gói để tiếp tục.</p>
              <button class="btn btn-primary btn-sm" @click="$router.push('/moi-gioi/goi-tin')">Mua gói ngay</button>
            </div>
            <div v-else class="alert-box success">
              <i class="fas fa-info-circle"></i>
              <p>Tin đăng của bạn sẽ được chuyển đến Admin để duyệt sau khi bấm "Đăng tin ngay".</p>
            </div>
          </div>

          <!-- Review Summary -->
          <div class="review-summary mt-8 d-flex gap-4">
            <div class="summary-image" style="width: 150px; height: 100px; border-radius: 12px; overflow: hidden; flex-shrink: 0;">
                <img :src="imagePreviewUrls[0] || 'https://via.placeholder.com/400x300?text=No+Image'" class="w-100 h-100 object-cover" />
            </div>
            <div class="flex-grow-1">
                <h4 class="summary-title">Tóm tắt tin đăng</h4>
                <div class="summary-list">
                  <div class="summary-item">
                    <span>Tiêu đề:</span>
                    <strong>{{ form.tieu_de }}</strong>
                  </div>
                  <div class="summary-item">
                    <span>Giá:</span>
                    <strong>{{ formatPrice(form.gia) }}</strong>
                  </div>
                  <div class="summary-item">
                    <span>Vị trí:</span>
                    <strong>{{ form.dia_chi_chi_tiet }}</strong>
                  </div>
                </div>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="form-actions">
          <button v-if="currentStep > 1" class="btn btn-secondary" @click="prevStep">
            <i class="fas fa-arrow-left"></i>
            Quay lại
          </button>

          <button v-if="currentStep < 4" class="btn btn-primary btn-lg" @click="nextStep">
            Tiếp tục
            <i class="fas fa-arrow-right"></i>
          </button>

          <button v-else class="btn btn-success btn-lg" @click="submitForm" :disabled="hetTin">
            <i class="fas fa-paper-plane"></i>
            {{ hetTin ? 'Hết lượt đăng' : 'Đăng tin ngay' }}
          </button>

          <button class="btn btn-outline" @click="saveDraft(true)">
            <i class="fas fa-save"></i>
            Lưu nháp
          </button>
        </div>

        <!-- Auto Save Indicator -->
        <div class="auto-save-indicator" :class="{ saving: isSaving }">
          <i class="fas" :class="isSaving ? 'fa-spinner fa-spin' : 'fa-check-circle'"></i>
          <span>{{
            isSaving ? "Đang lưu..." : `Tự động lưu: ${lastSaveTime}`
          }}</span>
        </div>
      </main>

      <!-- Right Sidebar -->
      <aside class="sidebar-right">
        <!-- Tips Card -->
        <div class="info-card tips-card">
          <div class="card-header warning">
            <i class="fas fa-lightbulb"></i>
            <h3>Mẹo chuyên nghiệp</h3>
          </div>
          <div class="card-body">
            <ul class="tips-list">
              <li>
                <i class="fas fa-check-circle"></i><span>Sử dụng ít nhất 6 hình ảnh chất lượng cao</span>
              </li>
              <li>
                <i class="fas fa-check-circle"></i><span>Cung cấp đầy đủ thông tin pháp lý</span>
              </li>
              <li>
                <i class="fas fa-check-circle"></i><span>Mô tả chi tiết tiện ích xung quanh</span>
              </li>
              <li>
                <i class="fas fa-check-circle"></i><span>Đặt giá cạnh tranh so với thị trường</span>
              </li>
            </ul>
          </div>
        </div>

        <!-- Stats Card -->
        <div class="info-card stats-card">
          <div class="stat-item">
            <div class="stat-value">85%</div>
            <div class="stat-label">Tin có ảnh thu hút hơn</div>
          </div>
          <div class="stat-item">
            <div class="stat-value">3x</div>
            <div class="stat-label">Nhiều lượt xem hơn</div>
          </div>
        </div>
      </aside>
    </div>
  </div>
</template>

<script setup>
import {
  ref,
  reactive,
  onMounted,
  onUnmounted,
  computed,
  watch,
  nextTick,
} from "vue";
import Swal from "sweetalert2";
import L from "leaflet";
import * as turf from "@turf/turf"; // ✅ Import Turf.js
import "leaflet/dist/leaflet.css";

// ✅ KHẮC PHỤC TRIỆT ĐỂ LỖI MARKER BỊ LỆCH BẰNG CUSTOM ICON TƯỜNG MINH
import iconUrl from "leaflet/dist/images/marker-icon.png";
import iconRetinaUrl from "leaflet/dist/images/marker-icon-2x.png";
import shadowUrl from "leaflet/dist/images/marker-shadow.png";

const customLeafletIcon = L.icon({
  iconUrl: iconUrl,
  iconRetinaUrl: iconRetinaUrl,
  shadowUrl: shadowUrl,
  iconSize: [25, 41],
  iconAnchor: [12, 41], // Điểm neo ghim chính xác vào toạ độ
  popupAnchor: [1, -34],
  tooltipAnchor: [16, -28],
  shadowSize: [41, 41],
});

import api from "@/axios/config"; // ✅ Import api

// ===== STATE =====
const currentStep = ref(1);
const lastSaveTime = ref("Vừa xong");
const isSaving = ref(false);
const fileInput = ref(null);
const mapLoaded = ref(false);
const mapInstance = ref(null);
const markerInstance = ref(null);
const boundaryLayer = ref(null);
let mapResizeObserver = null; // ✅ Thêm ResizeObserver để theo dõi thay đổi kích thước map

const draftId = ref(null); // ✅ Lưu ID bản nháp

const soNha = ref("");
const addressFromMap = ref("");
const geojsonCache = ref({});
const currentWardFeature = ref(null);

// ✅ THÊM: Số tin còn lại
const soTinConLai = ref(0);
const dangTaiDuLieu = ref(false);

const steps = [
  "Thông tin cơ bản",
  "Hình ảnh & Video",
  "Vị trí & Tiện ích",
  "Chọn gói tin",
];

const form = reactive({
  tieu_de: "",
  mo_ta: "",
  gia: 0,
  dien_tich: 0,
  loai_id: "",
  trang_thai_id: 1,
  moi_gioi_id: null,
  dia_chi_id: null,
  latitude: null,
  longitude: null,
  dia_chi_chi_tiet: "",
  so_phong_ngu: 1,
  so_phong_tam: 1,
  ten_du_an: "",
  tinh_id: "",
  quan_id: "",
  phuong_id: "",
  is_duyet: false,
  is_noi_bat: false,
  images: [],
});

const loaiBDSList = ref([]);
const tinhThanhList = ref([]);
const quanHuyenList = ref([]);
const phuongList = ref([]);

// ===== COMPUTED =====
const isFormValid = computed(() => {
  if (currentStep.value === 1) {
    return form.tieu_de && form.loai_id && form.gia && form.dien_tich;
  }
  if (currentStep.value === 3) {
    return form.latitude && form.longitude;
  }
  return true;
});

// ✅ COMPUTED: Kiểm tra còn tin không
const hetTin = computed(() => soTinConLai.value <= 0);

// ===== FILE HANDLING =====
const triggerFileInput = () => fileInput.value?.click();

const handleFileSelect = (event) => {
  const files = Array.from(event.target.files);
  handleFiles(files);
};

const handleDrop = (event) => {
  const files = Array.from(event.dataTransfer.files);
  handleFiles(files);
};

// ✅ Lưu base64 preview song song để persist qua localStorage
const imagePreviewUrls = ref([]); // base64 strings

const handleFiles = (files) => {
  const validFiles = files.filter((file) => {
    const isValidType = file.type.startsWith("image/");
    const isValidSize = file.size <= 10 * 1024 * 1024;
    return isValidType && isValidSize;
  });

  if (validFiles.length < files.length) {
    Swal.fire({
      icon: "warning",
      title: "File không hợp lệ",
      text: "Chỉ chấp nhận ảnh JPG, PNG dưới 10MB",
      timer: 3000,
    });
  }

  // Đọc base64 cho mỗi ảnh mới
  validFiles.forEach((file) => {
    const reader = new FileReader();
    reader.onload = (e) => {
      imagePreviewUrls.value.push(e.target.result);
      saveImagePreviewsToLocal();
    };
    reader.readAsDataURL(file);
  });

  form.images = [...form.images, ...validFiles];
};

const removeImage = (index) => {
  form.images.splice(index, 1);
  imagePreviewUrls.value.splice(index, 1);
  saveImagePreviewsToLocal();
};

const formatPrice = (gia) => {
  if (!gia && gia !== 0) return 'Liên hệ';
  if (gia >= 1000000000) return (gia / 1000000000).toFixed(2) + ' tỷ';
  if (gia >= 1000000) return (gia / 1000000).toFixed(0) + ' triệu';
  return new Intl.NumberFormat('vi-VN').format(gia) + ' VNĐ';
};

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  return date.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' });
};

const getImageUrl = (url) => {
  if (!url) return "https://via.placeholder.com/400x300?text=No+Image";
  if (url.startsWith("http") || url.startsWith("data:")) return url;
  
  const base = import.meta.env.VITE_API_URL?.replace('/api','') || 'http://localhost:8000';
  const cleanUrl = url.startsWith('/') ? url.substring(1) : url;
  const finalUrl = cleanUrl.startsWith('storage/') ? cleanUrl : `storage/${cleanUrl}`;
  
  return `${base}/${finalUrl}`;
};

// Trả về URL preview: ưu tiên File object, fallback về base64
const getImagePreviewUrl = (file) => {
  if (!file) return "";
  if (file instanceof File || file instanceof Blob) {
    return URL.createObjectURL(file);
  }
  return getImageUrl(file);
};

const saveImagePreviewsToLocal = () => {
  try {
    // Chỉ lưu tối đa 10 ảnh base64 (tránh vượt localStorage quota)
    const toSave = imagePreviewUrls.value.slice(0, 10);
    localStorage.setItem('dang_tin_images', JSON.stringify(toSave));
  } catch (e) {
    console.warn('Không thể lưu ảnh vào localStorage:', e);
  }
};

const restoreImagesFromLocal = () => {
  try {
    const saved = localStorage.getItem('dang_tin_images');
    if (saved) {
      const base64List = JSON.parse(saved);
      imagePreviewUrls.value = base64List;
      // Chuyển base64 → Blob để có thể upload
      const blobs = base64List.map((b64, i) => {
        const arr = b64.split(',');
        const mime = arr[0].match(/:(.*?);/)[1];
        const bstr = atob(arr[1]);
        let n = bstr.length;
        const u8arr = new Uint8Array(n);
        while (n--) u8arr[n] = bstr.charCodeAt(n);
        return new File([new Blob([u8arr], { type: mime })], `restored_${i}.jpg`, { type: mime });
      });
      form.images = blobs;
    }
  } catch (e) {
    console.warn('Không thể khôi phục ảnh từ localStorage:', e);
  }
};

// ===== LOCATION DROPDOWNS =====
const onTinhChange = async () => {
  quanHuyenList.value = [];
  form.quan_id = "";
  if (!form.tinh_id) return;
  try {
    const res = await api.get(`/moi-gioi/quan-huyen`, { params: { tinh_id: form.tinh_id } });
    if (res.data?.status) {
      quanHuyenList.value = res.data.data.map((item) => ({
        id: item.id,
        ten: item.ten,
      }));
    }
  } catch (error) {
    console.error("Load districts error:", error);
  }
};

const onQuanChange = () => {
  const quan = quanHuyenList.value.find((q) => q.id === form.quan_id);
  if (quan) {
    showDistrictBoundary(quan.ten);
  }
};

// ✅ Flag để tránh watcher reset form khi đang restore từ localStorage
const isRestoringForm = ref(false);

watch(
  () => form.tinh_id,
  async (newTinhId) => {
    if (!newTinhId) return;
    // Chỉ reset quan_id nếu không đang restore
    if (!isRestoringForm.value) {
      quanHuyenList.value = [];
      form.quan_id = "";
    }
    try {
      const res = await api.get(`/quan-huyen-by-tinh`, { params: { tinh_id: newTinhId } });
      if (res.data?.status) {
        quanHuyenList.value = res.data.data.map((item) => ({
          id: item.id,
          ten: item.ten,
        }));
      }
    } catch (error) {
      console.error("Load districts error:", error);
    }
    debouncedForwardGeocode();
  },
);

// Load phường xã khi chọn quận huyện
watch(
  () => form.quan_id,
  async (newQuanId) => {
    // Chỉ reset phuong_id nếu không đang restore
    if (!isRestoringForm.value) {
      form.phuong_id = "";
      phuongList.value = [];
    }

    if (!newQuanId) return;

    try {
      const res = await api.get(`/phuong-xa-by-quan-huyen`, { params: { quan_huyen_id: newQuanId } });
      if (res.data?.status && res.data?.data) {
        phuongList.value = res.data.data.map((item) => ({
          id: item.id,
          ten: item.ten,
        }));
      }
    } catch (error) {
      console.error("❌ Lỗi load phường xã:", error);
    }
    debouncedForwardGeocode();
  },
);

// Watch khi chọn phường xã để show boundary
watch(
  () => form.phuong_id,
  (newPhuongId) => {
    currentWardFeature.value = null; // Reset current ward
    if (!newPhuongId) {
      if (boundaryLayer.value && mapInstance.value) {
        mapInstance.value.removeLayer(boundaryLayer.value);
        boundaryLayer.value = null;
      }
      return;
    }
    const phuong = phuongList.value.find((p) => p.id === newPhuongId);
    if (phuong) {
      showWardBoundary(phuong.ten);
    }
    debouncedForwardGeocode();
  },
);

const showDistrictBoundary = async (districtName) => {
  if (!mapInstance.value) return;

  const normalize = (str) => {
    return str
      ? str
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .toLowerCase()
        .trim()
      : "";
  };

  try {
    const response = await fetch("/geojson/danang.json");
    if (!response.ok) throw new Error("Network response was not ok");

    const data = await response.json();
    console.log("Loaded GeoJSON data:", data);

    const normalizedTargetName = normalize(districtName);

    const feature = data.features.find((f) => {
      const name = f.properties.name || "";
      return normalize(name) === normalizedTargetName;
    });

    console.log(
      `Matching result for "${districtName}":`,
      feature ? "Found" : "Not Found",
    );

    if (boundaryLayer.value) {
      mapInstance.value.removeLayer(boundaryLayer.value);
      boundaryLayer.value = null;
    }

    if (feature) {
      boundaryLayer.value = L.geoJSON(feature, {
        style: {
          color: "#3388ff",
          weight: 2,
          opacity: 0.8,
          fillOpacity: 0.2,
        },
      }).addTo(mapInstance.value);

      mapInstance.value.fitBounds(boundaryLayer.value.getBounds());
    } else {
      console.warn(`Không tìm thấy boundary cho quận: ${districtName}`);
    }
  } catch (error) {
    console.error("Error loading or parsing boundary GeoJSON:", error);
  }
};

const showWardBoundary = async (wardName) => {
  if (!mapInstance.value) return;

  const normalizeMapString = (str) => {
    if (!str) return "";
    let s = str
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, "")
      .replace(/đ/g, "d")
      .toLowerCase()
      .replace(/[^a-z0-9]/g, ""); // Strip ALL spaces, hyphens, punctuation
    s = s.replace(/^(phuong|xa)/, "");
    return s;
  };

  try {
    // Tìm tên quận đang chọn để tải file subarea tương ứng
    const quan = quanHuyenList.value.find((q) => q.id === form.quan_id);
    if (!quan) return;

    let distSlug = quan.ten
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, "")
      .replace(/đ/g, "d")
      .toLowerCase()
      .replace(/[^a-z0-9\s-]/g, "")
      .replace(/\s+/g, "-");

    // Custom slug fixes for Da Nang
    if (distSlug === "cam-le") distSlug = "cam-le";
    else if (distSlug === "hai-chau") distSlug = "hai-chau";
    else if (distSlug === "hoa-vang") distSlug = "hoa-vang";
    else if (distSlug === "lien-chieu") distSlug = "lien-chieu";
    else if (distSlug === "ngu-hanh-son") distSlug = "ngu-hanh-son";
    else if (distSlug === "son-tra") distSlug = "son-tra";
    else if (distSlug === "thanh-khe") distSlug = "thanh-khe";

    // ✅ Dùng cache để không load lại dữ liệu nhiều lần
    let data;
    if (geojsonCache.value[distSlug]) {
      data = geojsonCache.value[distSlug];
    } else {
      const response = await fetch(`/geojson/subareas/${distSlug}.min.json`);
      if (!response.ok) throw new Error("Network response was not ok");
      data = await response.json();
      geojsonCache.value[distSlug] = data;
    }

    const subAreas = data.features || (Array.isArray(data) ? data : [data]);

    const targetName = normalizeMapString(wardName);

    const feature = subAreas.find((f) => {
      const fName = normalizeMapString(
        f.properties.slug || f.properties.name || f.name,
      );
      return fName === targetName;
    });

    // Lưu lại feature đang chọn để check toạ độ
    currentWardFeature.value = feature || null;

    console.log(
      `Matching result for ward "${wardName}":`,
      feature ? "Found" : "Not Found",
    );

    if (boundaryLayer.value) {
      mapInstance.value.removeLayer(boundaryLayer.value);
      boundaryLayer.value = null;
    }

    if (feature) {
      boundaryLayer.value = L.geoJSON(feature, {
        style: {
          color: "#3388ff", // ✅ Đổi thành màu xanh dương nhẹ
          weight: 2,
          opacity: 0.8,
          fillOpacity: 0.2,
        },
        onEachFeature: (f, layer) => {
          // ✅ Bonus: Highlight khi hover
          layer.on({
            mouseover: (e) => {
              e.target.setStyle({ fillOpacity: 0.4 });
            },
            mouseout: (e) => {
              e.target.setStyle({ fillOpacity: 0.2 });
            },
          });
        },
      }).addTo(mapInstance.value);

      mapInstance.value.fitBounds(boundaryLayer.value.getBounds());
    } else {
      console.warn(`Không tìm thấy boundary cho phường: ${wardName}`);
    }
  } catch (error) {
    console.error("Error loading ward boundary:", error);
  }
};

// ===== MAP FUNCTIONS =====
const validatePointInPolygon = (lat, lng) => {
  if (!currentWardFeature.value) {
    Swal.fire({
      icon: "warning",
      title: "Chưa chọn Phường/Xã",
      text: "Vui lòng chọn Phường/Xã trước khi ghim vị trí trên bản đồ.",
    });
    return false;
  }

  const pt = turf.point([lng, lat]);
  const isInside = turf.booleanPointInPolygon(pt, currentWardFeature.value);

  if (!isInside) {
    Swal.fire({
      icon: "error",
      title: "Sai vị trí",
      text: "Vị trí bạn chọn không nằm trong ranh giới Phường/Xã đã chọn. Vui lòng chọn lại đúng vị trí.",
    });
    return false;
  }
  return true;
};

const initMap = async () => {
  await nextTick();
  if (mapInstance.value) return;

  const mapContainer = document.getElementById("leaflet-map");
  if (!mapContainer) return;

  const defaultLat = 16.0471;
  const defaultLng = 108.2068;

  mapInstance.value = L.map("leaflet-map").setView(
    [defaultLat, defaultLng],
    12, // ✅ Map zoom default 12 theo yêu cầu
  );

  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    attribution: "© OpenStreetMap contributors",
    maxZoom: 19,
  }).addTo(mapInstance.value);

  // ✅ XỬ LÝ TRIỆT ĐỂ VẤN ĐỀ RESIZE/MODAL/V-IF BẰNG RESIZEOBSERVER
  // Bất cứ khi nào container bị đổi kích thước, Leaflet sẽ tính toán lại projection
  mapResizeObserver = new ResizeObserver(() => {
    if (mapInstance.value) {
      mapInstance.value.invalidateSize();
    }
  });
  mapResizeObserver.observe(mapContainer);

  mapInstance.value.on("click", async (e) => {
    // ✅ Kiểm tra Turf.js trước khi đặt marker
    const { lat, lng } = e.latlng;
    if (!validatePointInPolygon(lat, lng)) return;

    await placeMarker(e.latlng);
    debouncedReverseGeocode(lat, lng);
  });

  if (form.latitude && form.longitude) {
    await placeMarker({ lat: form.latitude, lng: form.longitude });
    mapInstance.value.setView([form.latitude, form.longitude], 15);
  }

  mapLoaded.value = true;
};

const placeMarker = async (latlng) => {
  if (markerInstance.value) {
    // ✅ Tái sử dụng marker, chỉ dời vị trí (Không xoá đi tạo lại)
    markerInstance.value.setLatLng([latlng.lat, latlng.lng]);
    markerInstance.value.openPopup();
    form.latitude = latlng.lat;
    form.longitude = latlng.lng;
    return;
  }

  // ✅ Tạo marker lần đầu và bật tính năng kéo thả (draggable)
  markerInstance.value = L.marker([latlng.lat, latlng.lng], {
    draggable: true,
    icon: customLeafletIcon,
  })
    .addTo(mapInstance.value)
    .bindPopup("📍 Vị trí bất động sản")
    .openPopup();

  form.latitude = latlng.lat;
  form.longitude = latlng.lng;

  // ✅ Lắng nghe sự kiện kéo thả marker
  markerInstance.value.on("dragend", (e) => {
    const pos = e.target.getLatLng();
    // Validate lại tọa độ sau khi drag
    if (!validatePointInPolygon(pos.lat, pos.lng)) {
      // Revert lại vị trí cũ nếu không hợp lệ
      e.target.setLatLng([form.latitude, form.longitude]);
      return;
    }
    form.latitude = pos.lat;
    form.longitude = pos.lng;
    debouncedReverseGeocode(pos.lat, pos.lng);
  });
};

const reverseGeocode = async (lat, lng) => {
  try {
    const response = await fetch(
      `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1&accept-language=vi`,
      { headers: { "User-Agent": "BDS-App/1.0" } },
    );
    const data = await response.json();
    if (data.address) {
      const addr = data.address;
      // Trích xuất Đường, Phường, Quận
      const street = addr.road || addr.pedestrian || addr.suburb || "";
      const ward = addr.suburb || addr.quarter || addr.village || "";
      const district = addr.city_district || addr.county || addr.district || "";

      let parts = [];
      if (street) parts.push(street);
      if (ward) parts.push(ward);
      if (district) parts.push(district);

      addressFromMap.value = parts.join(", ");
      updateDiaChiChiTiet();
    }
  } catch (error) {
    console.warn("Reverse geocoding failed:", error);
  }
};

let geocodeTimer = null;
const debouncedReverseGeocode = (lat, lng) => {
  if (geocodeTimer) clearTimeout(geocodeTimer);
  geocodeTimer = setTimeout(() => {
    reverseGeocode(lat, lng);
  }, 500);
};

const updateDiaChiChiTiet = () => {
  // Khi người dùng gõ số nhà, ta kết hợp với chuỗi địa chỉ từ map hoặc chuỗi từ dropdown
  if (addressFromMap.value) {
    form.dia_chi_chi_tiet = soNha.value
      ? `${soNha.value}, ${addressFromMap.value}`
      : addressFromMap.value;
  } else {
    form.dia_chi_chi_tiet = getFullAddressString();
  }
  debouncedForwardGeocode();
};

const getFullAddressString = () => {
  const tinh = tinhThanhList.value.find(t => t.id === form.tinh_id)?.ten || "";
  const quan = quanHuyenList.value.find(q => q.id === form.quan_id)?.ten || "";
  const phuong = phuongList.value.find(p => p.id === form.phuong_id)?.ten || "";
  
  let parts = [];
  if (soNha.value) parts.push(soNha.value);
  if (phuong) parts.push(phuong);
  if (quan) parts.push(quan);
  if (tinh) parts.push(tinh);
  
  return parts.join(", ");
};

const forwardGeocode = async () => {
  const addressQuery = getFullAddressString();
  // Chỉ tìm kiếm khi đã chọn ít nhất Tỉnh và Quận
  if (!form.tinh_id || !form.quan_id || !mapInstance.value) return;

  try {
    const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(addressQuery)}`);
    const data = await response.json();
    if (data && data.length > 0) {
      const lat = parseFloat(data[0].lat);
      const lon = parseFloat(data[0].lon);
      
      form.latitude = lat;
      form.longitude = lon;
      
      if (!markerInstance.value) {
        markerInstance.value = L.marker([lat, lon], {
          icon: customLeafletIcon,
          draggable: true,
        }).addTo(mapInstance.value);
        
        markerInstance.value.on("dragend", (e) => {
          const pos = e.target.getLatLng();
          form.latitude = pos.lat;
          form.longitude = pos.lng;
          debouncedReverseGeocode(pos.lat, pos.lng);
        });
      } else {
        markerInstance.value.setLatLng([lat, lon]);
      }
      mapInstance.value.setView([lat, lon], 16);
      
      if (!addressFromMap.value) {
         form.dia_chi_chi_tiet = addressQuery;
      }
    }
  } catch (error) {
    console.warn("Forward geocoding failed:", error);
  }
};

let forwardGeocodeTimer = null;
const debouncedForwardGeocode = () => {
  if (forwardGeocodeTimer) clearTimeout(forwardGeocodeTimer);
  forwardGeocodeTimer = setTimeout(() => {
    forwardGeocode();
  }, 1000);
};

// Watch step changes to init/destroy map
watch(
  () => currentStep.value,
  (newStep, oldStep) => {
    // ✅ Destroy map khi rời khỏi step 3 để cho phép init lại khi quay lại
    if (oldStep === 3 && newStep !== 3) {
      if (mapResizeObserver) {
        mapResizeObserver.disconnect();
        mapResizeObserver = null;
      }
      if (mapInstance.value) {
        mapInstance.value.remove();
        mapInstance.value = null;
        markerInstance.value = null;
        mapLoaded.value = false;
      }
    }
    if (newStep === 3) {
      nextTick(() => initMap());
    }
  },
);

// ===== NAVIGATION =====
const nextStep = () => {
  if (validateStep()) {
    currentStep.value++;
    window.scrollTo({ top: 0, behavior: "smooth" });
  }
};

const prevStep = () => {
  if (currentStep.value > 1) {
    currentStep.value--;
    window.scrollTo({ top: 0, behavior: "smooth" });
  }
};

const validateStep = () => {
  if (currentStep.value === 1) {
    if (!form.tieu_de || !form.loai_id || !form.gia || !form.dien_tich) {
      Swal.fire({
        icon: "warning",
        title: "Thiếu thông tin",
        text: "Vui lòng điền đầy đủ các trường bắt buộc (*)",
        confirmButtonText: "Đã hiểu",
      });
      return false;
    }
    if (form.tieu_de.length < 20) {
      Swal.fire({
        icon: "warning",
        title: "Tiêu đề quá ngắn",
        text: "Tiêu đề nên có ít nhất 20 ký tự để thu hút người xem",
        confirmButtonText: "Tiếp tục",
      });
    }
  }
  if (currentStep.value === 3) {
    if (!form.latitude || !form.longitude) {
      Swal.fire({
        icon: "warning",
        title: "Chưa chọn vị trí",
        text: "Vui lòng click vào bản đồ để chọn vị trí bất động sản",
        confirmButtonText: "Đã hiểu",
      });
      return false;
    }
  }
  return true;
};

// ✅ LOAD SỐ TIN CÒN LẠI
const loadSoTinConLai = async () => {
  dangTaiDuLieu.value = true;
  try {
    const res = await api.get("/moi-gioi/profile");
    if (res.data?.status && res.data?.data) {
      soTinConLai.value = res.data.data.so_tin_con_lai || 0;
    }
  } catch (error) {
    console.error("Error loading remaining posts:", error);
  } finally {
    dangTaiDuLieu.value = false;
  }
};

// ===== SAVE & SUBMIT =====
const saveDraft = async (isManual = false) => {
  // Chỉ block auto-save khi không có dữ liệu gì
  // Manual save (bấm nút) luôn cho phép gọi API
  if (!isManual && !form.tieu_de && form.images.length === 0) {
    console.log("Auto-save: Chưa có dữ liệu để lưu nháp");
    return;
  }

  isSaving.value = true;
  const formData = new FormData();

  // ✅ Thêm status = draft
  formData.append("status", "draft");

  // ✅ Nếu đã có draftId, gửi kèm để update
  if (draftId.value) {
    formData.append("id", draftId.value);
  }

  Object.keys(form).forEach((key) => {
    if (key === "images") return;

    let value = form[key];
    if (value === null || value === "" || value === undefined) return;

    if (key === "is_noi_bat" || key === "is_duyet") {
      value = value ? 1 : 0;
    }

    if (
      [
        "gia",
        "dien_tich",
        "so_phong_ngu",
        "so_phong_tam",
        "loai_id",
        "tinh_id",
        "quan_id",
        "trang_thai_id",
      ].includes(key)
    ) {
      value = parseFloat(value);
    }

    formData.append(key, value);
  });

  form.images.forEach((image, index) => {
    formData.append(`hinh_anh[${index}]`, image);
  });

  // ✅ KHÔNG append thêm trang_thai_id=0 ở đây (đã xử lý trong forEach phía trên)

  try {
    let res;
    if (draftId.value) {
      try {
        // ✅ Thử update trước, nếu BĐS không tồn tại (404/422) thì fallback về create
        res = await api.post("/moi-gioi/bds/update", formData, {
          headers: { "Content-Type": "multipart/form-data" },
        });
      } catch (updateErr) {
        const status = updateErr.response?.status;
        if (status === 404 || status === 422 || status === 400) {
          console.warn('⚠️ BĐS nháp không còn tồn tại, tạo mới...');
          // Xóa id cũ khỏi formData và tạo mới
          draftId.value = null;
          localStorage.removeItem('dang_tin_draft_id');
          formData.delete('id');
          res = await api.post("/moi-gioi/bds/create", formData, {
            headers: { "Content-Type": "multipart/form-data" },
          });
        } else {
          throw updateErr;
        }
      }
    } else {
      res = await api.post("/moi-gioi/bds/create", formData, {
        headers: { "Content-Type": "multipart/form-data" },
      });
    }

    if (res.data?.status && res.data?.data?.id) {
      draftId.value = res.data.data.id;
      // ✅ Lưu draftId vào localStorage để khôi phục khi quay lại
      localStorage.setItem('dang_tin_draft_id', draftId.value);
    }

    // ✅ Lưu form text vào localStorage
    const formSnapshot = {
      tieu_de: form.tieu_de,
      mo_ta: form.mo_ta,
      gia: form.gia,
      dien_tich: form.dien_tich,
      loai_id: form.loai_id,
      so_phong_ngu: form.so_phong_ngu,
      so_phong_tam: form.so_phong_tam,
      ten_du_an: form.ten_du_an,
      tinh_id: form.tinh_id,
      quan_id: form.quan_id,
      phuong_id: form.phuong_id,
      dia_chi_chi_tiet: form.dia_chi_chi_tiet,
      latitude: form.latitude,
      longitude: form.longitude,
      currentStep: currentStep.value,
    };
    localStorage.setItem('dang_tin_form', JSON.stringify(formSnapshot));

    lastSaveTime.value = "Vừa xong";

    if (isManual) {
      Swal.fire({
        icon: "success",
        title: "Đã lưu nháp",
        text: "Bạn có muốn xem danh sách tin nháp không?",
        showCancelButton: true,
        confirmButtonText: "Đến danh sách",
        cancelButtonText: "Ở lại",
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = "/moi-gioi/quan-ly-bat-dong-san";
        }
      });
    } else {
      Swal.fire({
        toast: true,
        position: "top-end",
        icon: "success",
        title: "Đã lưu nháp tự động",
        showConfirmButton: false,
        timer: 1500,
      });
    }
  } catch (error) {
    console.error("❌ Lỗi lưu nháp:", error);
    console.error("❌ Response data:", error.response?.data);
    console.error("❌ Status code:", error.response?.status);

    if (isManual) {
      const errMsg = error.response?.data?.message
        || (error.response?.data?.errors ? JSON.stringify(error.response.data.errors) : null)
        || error.message
        || "Lỗi không xác định";
      Swal.fire({
        icon: "error",
        title: "Lưu nháp thất bại",
        text: errMsg,
      });
    }
  } finally {
    isSaving.value = false;
  }
};

const submitForm = async () => {
  if (hetTin.value) {
    Swal.fire({
      icon: "error",
      title: "Hết số tin đăng!",
      text: "Bạn đã hết số tin trong gói. Vui lòng mua thêm gói tin để tiếp tục.",
      confirmButtonText: "Mua gói tin",
      confirmButtonColor: "#001f7c",
      showCancelButton: true,
      cancelButtonText: "Để sau",
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = "/moi-gioi/goi-tin";
      }
    });
    return;
  }

  if (!validateStep()) return;

  const { value: confirm } = await Swal.fire({
    title: "Xác nhận đăng tin?",
    text: "Tin đăng sẽ trừ 1 lượt đăng và cần Admin duyệt để hiển thị.",
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Đồng ý đăng",
    cancelButtonText: "Hủy",
    confirmButtonColor: "#001f7c",
    cancelButtonColor: "#6c757d",
  });

  if (!confirm) return;

  try {
    const formData = new FormData();
    formData.append("status", "published");

    if (draftId.value) {
      formData.append("id", draftId.value);
    }

    Object.keys(form).forEach((key) => {
      if (key === "images") return;
      let value = form[key];

      if (key === "is_noi_bat" || key === "is_duyet") {
        value = value ? 1 : 0;
      }

      if (["gia", "dien_tich", "so_phong_ngu", "so_phong_tam", "loai_id", "tinh_id", "quan_id", "phuong_id", "trang_thai_id"].includes(key)) {
        if (value === null || value === "" || value === undefined) return;
        value = parseFloat(value);
      }

      if (["latitude", "longitude"].includes(key) && value) {
        value = parseFloat(value).toFixed(6);
      }

      if (value !== null && value !== undefined && value !== "") {
        formData.append(key, value);
      }
    });

    form.images.forEach((image, index) => {
      formData.append(`hinh_anh[${index}]`, image);
    });

    if (draftId.value) {
      await api.post("/moi-gioi/bds/update", formData, { headers: { "Content-Type": "multipart/form-data" } });
      await api.post(`/moi-gioi/bds/${draftId.value}/publish`);
    } else {
      await api.post("/moi-gioi/bds/create", formData, { headers: { "Content-Type": "multipart/form-data" } });
    }

    soTinConLai.value--;

    await Swal.fire({
      icon: "success",
      title: "Đã gửi tin đăng!",
      text: "Tin của bạn đang chờ Admin duyệt. Bạn còn " + soTinConLai.value + " lượt đăng.",
      timer: 3000,
      showConfirmButton: true,
    });

    clearDraftLocal();
    window.location.href = "/moi-gioi/quan-ly-bat-dong-san";
  } catch (error) {
    console.error("❌ Submit error:", error.response?.data);
    Swal.fire({
      icon: "error",
      title: "Lỗi",
      text: error.response?.data?.message || "Có lỗi xảy ra. Vui lòng thử lại.",
    });
  }
};

// ===== LOAD DATA =====
const loadData = async () => {
  try {
    const [loaiRes, tinhRes] = await Promise.all([
      api.get("/moi-gioi/loai-bat-dong-san/data"),
      api.get("/moi-gioi/tinh-thanh"),
    ]);
    if (loaiRes.data?.status)
      loaiBDSList.value = loaiRes.data.data.map((item) => ({
        id: item.id,
        ten: item.ten_loai,
      }));
    if (tinhRes.data?.status)
      tinhThanhList.value = tinhRes.data.data.map((item) => ({
        id: item.id,
        ten: item.ten,
      }));
  } catch (error) {
    console.error("Error loading dropdowns:", error);
  }
};

// ===== HELPERS: localStorage =====
const DRAFT_ID_KEY = 'dang_tin_draft_id';
const DRAFT_FORM_KEY = 'dang_tin_form';

const restoreFormFromLocal = async () => {
  try {
    isRestoringForm.value = true;

    const savedId = localStorage.getItem(DRAFT_ID_KEY);
    if (savedId) draftId.value = parseInt(savedId);

    const savedForm = localStorage.getItem(DRAFT_FORM_KEY);
    if (savedForm) {
      const parsed = JSON.parse(savedForm);

      // ✅ Bước 1: Set tinh_id trước và đợi quận load
      if (parsed.tinh_id) {
        form.tinh_id = parsed.tinh_id;
        // Đợi watcher tinh_id load xong danh sách quận
        await new Promise(resolve => setTimeout(resolve, 500));
      }

      // ✅ Bước 2: Set quan_id và đợi phường load
      if (parsed.quan_id) {
        form.quan_id = parsed.quan_id;
        await new Promise(resolve => setTimeout(resolve, 500));
      }

      // ✅ Bước 3: Restore toàn bộ form (không reset quan/phuong)
      Object.keys(parsed).forEach((key) => {
        if (key === 'currentStep') {
          currentStep.value = parsed[key] || 1;
        } else if (key === 'tinh_id' || key === 'quan_id') {
          // Đã set ở trên
        } else if (key in form) {
          form[key] = parsed[key];
        }
      });

      lastSaveTime.value = "Khôi phục từ lần lưu trước";
    }

    // ✅ Bước 4: Khôi phục ảnh từ localStorage
    restoreImagesFromLocal();

  } catch (e) {
    console.warn('Không thể khôi phục dữ liệu nháp:', e);
  } finally {
    isRestoringForm.value = false;
  }
};

const clearDraftLocal = () => {
  localStorage.removeItem(DRAFT_ID_KEY);
  localStorage.removeItem(DRAFT_FORM_KEY);
  localStorage.removeItem('dang_tin_images');
};

// ===== LIFECYCLE =====
let autoSaveInterval;
onMounted(async () => {
  await Promise.all([loadData(), loadSoTinConLai()]);

  // ✅ Khôi phục form từ localStorage nếu có
  restoreFormFromLocal();

  if (currentStep.value === 3) initMap();

  autoSaveInterval = setInterval(() => {
    if (form.tieu_de || form.mo_ta) saveDraft(false);
  }, 60000);
});

onUnmounted(() => {
  clearInterval(autoSaveInterval);
  if (mapResizeObserver) {
    mapResizeObserver.disconnect();
    mapResizeObserver = null;
  }
  if (mapInstance.value) {
    mapInstance.value.remove();
    mapInstance.value = null;
  }
});
</script>
<style scoped>
/* Fix dropdown bị che */
.form-select,
select.form-select {
  position: relative !important;
  z-index: 1;
}

/* Đảm bảo parent containers không bị overflow hidden */
.form-container,
.form-group,
.main-form-area {
  overflow: visible !important;
}

/* Tăng z-index cho dropdown menu */
.form-select:focus {
  z-index: 1001;
}

/* Fix cho các phần tử cha */
.property-posting-wrapper,
.posting-grid,
.main-form-area {
  overflow: visible;
}

.form-select option {
  color: #000000 !important;
  padding: 8px 12px !important;
}

/* Nếu có modal hoặc card cha */
.card,
.modal-content {
  overflow: visible !important;
}

/* ===== LEAFLET MAP ===== */
@import "leaflet/dist/leaflet.css";

/* ✅ Fix lỗi CSS làm vỡ / lệch marker Leaflet do thuộc tính CSS global (Tailwind / Bootstrap) */
.leaflet-container img.leaflet-marker-icon,
.leaflet-container img.leaflet-marker-shadow {
  max-width: none !important;
  max-height: none !important;
}

.map-wrapper {
  position: relative;
  border-radius: 12px;
  overflow: hidden;
  border: 2px solid #e5e7eb;
  background: #f9fafb;
}

.map-loading {
  height: 500px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  color: #6b7280;
}

.map-loading i {
  font-size: 2rem;
}

.leaflet-map-container {
  width: 100%;
  height: 550px;
  min-height: 400px;
  z-index: 1;
  position: relative;
}

.coords-display {
  padding: 0.75rem 1rem;
  background: #f0fdf4;
  border-radius: 8px;
  font-size: 0.9rem;
  color: #166534;
  border: 1px solid #bbf7d0;
}

.coords-display i {
  margin-right: 0.5rem;
}

/* ===== BASE STYLES ===== */
.property-posting-wrapper {
  max-width: 100%;
  background: #f8f9fa;
  /* min-height: 100vh; */
}

.progress-header {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  margin-bottom: 2rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.progress-track {
  display: flex;
  justify-content: space-between;
  max-width: 800px;
  margin: 0 auto;
  position: relative;
}

.progress-track::before {
  content: "";
  position: absolute;
  top: 20px;
  left: 0;
  right: 0;
  height: 2px;
  background: #e5e7eb;
  z-index: 0;
}

.progress-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
  position: relative;
  z-index: 1;
  flex: 1;
}

.step-indicator {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: #e5e7eb;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  color: #6b7280;
  transition: all 0.3s;
}

.progress-item.active .step-indicator {
  background: #001f7c;
  color: white;
  box-shadow: 0 0 0 4px rgba(0, 31, 124, 0.2);
}

.progress-item.completed .step-indicator {
  background: #10b981;
  color: white;
}

.step-name {
  font-size: 0.875rem;
  font-weight: 500;
  color: #6b7280;
}

.progress-item.active .step-name {
  color: #001f7c;
  font-weight: 600;
}

.posting-grid {
  display: grid;
  grid-template-columns: 250px 1fr 350px;
  gap: 1rem;
  align-items: start;
}

.sidebar-left {
  position: s;
  top: 1rem;
}

.sidebar-section {
  background: white;
  border-radius: 12px;
  padding: 1rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  margin-bottom: 1rem;
}

.sidebar-title {
  font-size: 1rem;
  font-weight: 700;
  color: #111827;
  margin-bottom: 1rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid #f3f4f6;
}

.sidebar-nav {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.875rem 1rem;
  border-radius: 8px;
  color: #4b5563;
  text-decoration: none;
  font-weight: 500;
  transition: all 0.2s;
  cursor: pointer;
}

.nav-item:hover {
  background: #f9fafb;
  color: #001f7c;
}

.nav-item.active {
  background: #001f7c;
  color: white;
}

.nav-item i {
  width: 20px;
  font-size: 1.125rem;
}

.upgrade-box {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  text-align: center;
  padding: 2rem 1.5rem;
}

.upgrade-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.upgrade-box h4 {
  font-size: 1.125rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
}

.upgrade-box p {
  font-size: 0.875rem;
  opacity: 0.9;
  margin-bottom: 1.25rem;
}

.btn-upgrade {
  width: 100%;
  padding: 0.875rem;
  background: white;
  color: #667eea;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: transform 0.2s;
}

.btn-upgrade:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.main-form-area {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.section-header {
  margin-bottom: 2rem;
}

.section-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #111827;
  margin-bottom: 0.5rem;
}

.section-desc {
  color: #6b7280;
  font-size: 0.95rem;
}

.form-container {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group.full-width {
  grid-column: 1 / -1;
}

.form-label {
  font-weight: 600;
  color: #374151;
  font-size: 0.9rem;
}

.label-hint {
  font-weight: 400;
  font-size: 0.75rem;
  color: #6b7280;
}

.required::after {
  content: "*";
  color: #ef4444;
  margin-left: 0.25rem;
}

.form-input,
.form-select,
.form-textarea {
  width: 100%;
  padding: 0.875rem 1rem;
  border: 2px solid #d1d5db;
  border-radius: 8px;
  font-size: 0.95rem;
  background: white;
  color: #111827;
  transition: all 0.2s;
  box-sizing: border-box;
}

.form-input:focus,
.form-select:focus,
.form-textarea:focus {
  outline: none;
  border-color: #001f7c;
  box-shadow: 0 0 0 3px rgba(0, 31, 124, 0.1);
}

.form-input::placeholder {
  color: #9ca3af;
}

.form-textarea {
  resize: vertical;
  min-height: 120px;
}

.char-count {
  text-align: right;
  font-size: 0.75rem;
  color: #6b7280;
}

.select-wrapper {
  position: relative;
}

.select-icon {
  position: absolute;
  right: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: #9ca3af;
  pointer-events: none;
}

.input-group {
  display: flex;
  gap: 0;
}

.input-group .form-input {
  flex: 1;
  border-top-right-radius: 0;
  border-bottom-right-radius: 0;
  border-right: none;
}

.input-addon {
  padding: 0.875rem 1rem;
  background: #f3f4f6;
  border: 2px solid #d1d5db;
  border-left: none;
  border-radius: 0 8px 8px 0;
  font-weight: 600;
  color: #374151;
  display: flex;
  align-items: center;
}

.form-hint {
  font-size: 0.8rem;
  color: #6b7280;
}

.button-group {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.btn-chip {
  padding: 0.75rem 1.5rem;
  border: 2px solid #d1d5db;
  background: white;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  color: #374151;
}

.btn-chip:hover {
  border-color: #001f7c;
  color: #001f7c;
}

.btn-chip.active {
  background: #001f7c;
  color: white;
  border-color: #001f7c;
}

.textarea-toolbar {
  display: flex;
  gap: 0.5rem;
  padding-top: 0.5rem;
  border-top: 1px solid #e5e7eb;
}

.toolbar-btn {
  padding: 0.5rem 0.75rem;
  background: #f3f4f6;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  color: #6b7280;
  transition: all 0.2s;
}

.toolbar-btn:hover {
  background: #e5e7eb;
  color: #001f7c;
}

.quick-upload-section {
  margin-top: 2rem;
  padding-top: 2rem;
  border-top: 2px solid #f3f4f6;
}

.upload-card {
  border: 2px dashed #d1d5db;
  border-radius: 12px;
  padding: 3rem 2rem;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s;
  background: #f9fafb;
}

.upload-card:hover {
  border-color: #001f7c;
  background: rgba(0, 31, 124, 0.05);
}

.upload-icon-wrapper {
  width: 80px;
  height: 80px;
  background: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1.5rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.upload-icon-wrapper i {
  font-size: 2.5rem;
  color: #001f7c;
}

.upload-content h4 {
  font-size: 1.25rem;
  font-weight: 700;
  color: #111827;
  margin-bottom: 0.5rem;
}

.upload-content p {
  color: #6b7280;
  margin-bottom: 0.75rem;
}

.upload-hint {
  font-size: 0.875rem;
  color: #9ca3af;
  display: inline-block;
  padding: 0.25rem 0.75rem;
  background: #f3f4f6;
  border-radius: 20px;
}

.hidden-input {
  display: none;
}

.image-preview-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  gap: 1rem;
  margin-top: 1.5rem;
}

.preview-item {
  position: relative;
  aspect-ratio: 1;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.preview-item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.remove-img {
  position: absolute;
  top: 0.5rem;
  right: 0.5rem;
  width: 32px;
  height: 32px;
  background: rgba(239, 68, 68, 0.9);
  color: white;
  border: none;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.2s;
}

.preview-item:hover .remove-img {
  opacity: 1;
}

.main-badge {
  position: absolute;
  bottom: 0.5rem;
  left: 0.5rem;
  background: #f59e0b;
  color: white;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
}

.empty-state {
  text-align: center;
  padding: 4rem 2rem;
  background: #f9fafb;
  border-radius: 12px;
}

.empty-state i {
  font-size: 4rem;
  color: #d1d5db;
  margin-bottom: 1rem;
}

.empty-state h3 {
  font-size: 1.25rem;
  color: #374151;
  margin-bottom: 0.5rem;
}

.empty-state p {
  color: #9ca3af;
}

.form-actions {
  display: flex;
  gap: 1rem;
  margin-top: 2.5rem;
  padding-top: 2rem;
  border-top: 2px solid #f3f4f6;
  justify-content: flex-end;
}

.btn {
  padding: 0.875rem 2rem;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.2s;
  font-size: 0.95rem;
}

.btn-lg {
  padding: 1rem 2.5rem;
  font-size: 1rem;
}

.btn-primary {
  background: #001f7c;
  color: white;
}

.btn-primary:hover {
  background: #1e40af;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 31, 124, 0.3);
}

.btn-secondary {
  background: #6b7280;
  color: white;
}

.btn-secondary:hover {
  background: #4b5563;
}

.btn-success {
  background: #10b981;
  color: white;
}

.btn-success:hover {
  background: #059669;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.btn-outline {
  background: white;
  color: #001f7c;
  border: 2px solid #001f7c;
}

.btn-outline:hover {
  background: #f9fafb;
}

.auto-save-indicator {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-top: 1.5rem;
  padding: 0.75rem 1rem;
  background: #f9fafb;
  border-radius: 8px;
  font-size: 0.875rem;
  color: #6b7280;
}

/* ===== PACKAGE CONFIRMATION ===== */
.package-confirmation-card {
  background: white;
  border-radius: 16px;
  border: 1px solid #e5e7eb;
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
}

.package-info-main {
  padding: 2rem;
}

.package-header {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  margin-bottom: 2rem;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid #f3f4f6;
}

.package-icon {
  width: 60px;
  height: 60px;
  background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.5rem;
}

.package-titles h3 {
  font-size: 1.25rem;
  font-weight: 700;
  color: #111827;
  margin: 0;
}

.package-name {
  color: #6366f1;
  font-weight: 600;
  font-size: 0.9rem;
  margin: 0;
}

.status-badge {
  margin-left: auto;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
}

.status-badge.active {
  background: #ecfdf5;
  color: #059669;
}

.status-badge.expired {
  background: #fef2f2;
  color: #dc2626;
}

.package-stats-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.5rem;
}

.p-stat {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.p-label {
  font-size: 0.85rem;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.p-value {
  font-size: 1.1rem;
  font-weight: 700;
  color: #111827;
}

.alert-box {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.25rem 2rem;
  border-top: 1px solid #f3f4f6;
}

.alert-box.success {
  background: #f0fdf4;
  color: #166534;
}

.alert-box.danger {
  background: #fef2f2;
  color: #991b1b;
}

.alert-box p {
  margin: 0;
  font-size: 0.95rem;
  font-weight: 500;
}

.review-summary {
  background: #f8fafc;
  border-radius: 12px;
  padding: 1.5rem;
  border: 1px dashed #cbd5e1;
}

.summary-title {
  font-size: 1rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 1rem;
}

.summary-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.summary-item {
  display: flex;
  justify-content: space-between;
  font-size: 0.9rem;
}

.summary-item span {
  color: #64748b;
}

.summary-item strong {
  color: #1e293b;
}

.mt-8 { margin-top: 2rem; }
.text-danger { color: #ef4444; }
.text-success { color: #10b981; }

/* ===== SIDEBAR ENHANCEMENTS ===== */
.credit-card {
  background: #f8fafc;
  border-radius: 12px;
  padding: 1.25rem;
  border: 1px solid #e2e8f0;
}

.credit-label {
  font-size: 0.8rem;
  color: #64748b;
  font-weight: 600;
  text-transform: uppercase;
}

.credit-value {
  font-size: 2rem;
  font-weight: 800;
  color: #001f7c;
  margin: 0.5rem 0;
}

.progress-mini {
  height: 6px;
  background: #e2e8f0;
  border-radius: 10px;
  margin-bottom: 0.75rem;
  overflow: hidden;
}

.progress-bar-mini {
  height: 100%;
  background: #001f7c;
  border-radius: 10px;
  transition: width 0.5s ease;
}

.credit-hint {
  font-size: 0.75rem;
  color: #94a3b8;
  margin: 0;
  line-height: 1.4;
}

.guide-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.guide-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 0.85rem;
  color: #94a3b8;
  transition: color 0.3s;
}

.guide-item i {
  font-size: 1rem;
}

.guide-item.active {
  color: #059669;
}

.guide-item.active i {
  color: #10b981;
}

.upgrade-box {
  background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
  color: white;
}

.upgrade-box h4 {
  color: white;
  margin-bottom: 0.5rem;
}

.upgrade-box p {
  color: rgba(255, 255, 255, 0.8);
  font-size: 0.8rem;
}

.btn-upgrade {
  background: white;
  color: #1e40af;
  border: none;
  padding: 0.6rem;
  border-radius: 6px;
  font-weight: 700;
  width: 100%;
  cursor: pointer;
  transition: transform 0.2s;
}

.btn-upgrade:hover {
  transform: scale(1.02);
}

.auto-save-indicator.saving {
  color: #001f7c;
}

.sidebar-right {
  position: sticky;
  top: 1rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.info-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  overflow: hidden;
}

.card-header {
  padding: 1.25rem 1.5rem;
  background: #f9fafb;
  border-bottom: 2px solid #f3f4f6;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.card-header i {
  font-size: 1.25rem;
  color: #001f7c;
}

.card-header.warning i {
  color: #f59e0b;
}

.card-header h3 {
  font-size: 1rem;
  font-weight: 700;
  color: #111827;
}

.card-body {
  padding: 1.5rem;
}

.tips-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.tips-list li {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  font-size: 0.9rem;
  color: #374151;
}

.tips-list i {
  color: #10b981;
  margin-top: 0.125rem;
  flex-shrink: 0;
}

.stats-card {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.stat-item {
  text-align: center;
  padding: 1.25rem;
}

.stat-value {
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 0.25rem;
}

.stat-label {
  font-size: 0.875rem;
  opacity: 0.9;
}

.fade-in {
  animation: fadeIn 0.4s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@media (max-width: 1200px) {
  .posting-grid {
    grid-template-columns: 1fr;
  }

  .sidebar-left,
  .sidebar-right {
    position: static;
  }

  .sidebar-left {
    display: none;
  }
}

@media (max-width: 768px) {
  .property-posting-wrapper {
    padding: 1rem;
  }

  .progress-track {
    flex-direction: column;
    gap: 1rem;
  }

  .progress-track::before {
    display: none;
  }

  .form-row {
    grid-template-columns: 1fr;
  }

  .form-actions {
    flex-direction: column;
  }

  .btn {
    width: 100%;
    justify-content: center;
  }

  .leaflet-map-container {
    height: 300px;
  }
}
</style>
