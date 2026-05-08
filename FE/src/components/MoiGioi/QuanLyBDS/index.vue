<template>
  <div class="container-fluid py-4 dashboard-bg min-vh-100" style="
      background-color: #f4f6f9;
      font-family: &quot;Inter&quot;, sans-serif;
    ">
    <header class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
      <div>
        <h1 class="h3 fw-bolder text-dark mb-0">Bất Động Sản Của Tôi</h1>
        <p class="text-muted mb-0 small">
          Quản lý và theo dõi danh sách bất động sản
        </p>
      </div>
      <button @click="$router.push('/moi-gioi/dang-tin')"
        class="btn btn-primary px-4 py-2 fw-semibold d-flex align-items-center gap-2 shadow-sm rounded-pill">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" width="20" height="20" fill="none" viewBox="0 0 24 24"
          stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Đăng Tin Mới
      </button>
    </header>

    <div class="row g-3 mb-4">
      <div class="col-6 col-md-3">
        <div class="card h-100 border-0 shadow-sm rounded-4 border-bottom border-primary"
          style="border-width: 0 0 4px 0 !important">
          <div class="card-body p-4">
            <p class="text-uppercase text-muted fw-bold mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px">
              Tổng Bất Động Sản
            </p>
            <h2 class="text-primary fw-black mb-0 display-6 fw-bold">
              {{ stats?.total || 0 }}
            </h2>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card h-100 border-0 shadow-sm rounded-4 border-bottom border-success"
          style="border-width: 0 0 4px 0 !important">
          <div class="card-body p-4">
            <p class="text-uppercase text-muted fw-bold mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px">
              Đã Duyệt
            </p>
            <h2 class="text-success fw-black mb-0 display-6 fw-bold">
              {{ stats?.approved || 0 }}
            </h2>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card h-100 border-0 shadow-sm rounded-4 border-bottom border-warning"
          style="border-width: 0 0 4px 0 !important">
          <div class="card-body p-4">
            <p class="text-uppercase text-muted fw-bold mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px">
              Chờ Duyệt
            </p>
            <h2 class="text-warning fw-black mb-0 display-6 fw-bold">
              {{ stats?.pending || 0 }}
            </h2>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card h-100 border-0 shadow-sm rounded-4 border-bottom border-secondary"
          style="border-width: 0 0 4px 0 !important">
          <div class="card-body p-4">
            <p class="text-uppercase text-muted fw-bold mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px">
              Từ Chối / Đã Bán
            </p>
            <h2 class="text-secondary fw-black mb-0 display-6 fw-bold">
              {{ stats?.rejected || stats?.sold || 0 }}
            </h2>
          </div>
        </div>
      </div>
    </div>

    <ul class="nav nav-pills mb-4 gap-2 flex-nowrap overflow-auto hide-scrollbar pb-2">
      <li class="nav-item">
        <button @click="activeTab = 'all'" class="nav-link rounded-pill px-4 fw-semibold border transition-all" :class="activeTab === 'all' ? 'active shadow-sm' : 'bg-white text-dark'
          ">
          Tất cả
        </button>
      </li>
      <li class="nav-item">
        <button @click="activeTab = 'draft'" class="nav-link rounded-pill px-4 fw-semibold border transition-all"
          :class="activeTab === 'draft' ? 'active shadow-sm' : 'bg-white text-dark'
            ">
          Nháp
        </button>
      </li>
      <li class="nav-item">
        <button @click="activeTab = 'pending'" class="nav-link rounded-pill px-4 fw-semibold border transition-all"
          :class="activeTab === 'pending' ? 'active shadow-sm' : 'bg-white text-dark'
            ">
          Chờ duyệt
        </button>
      </li>
      <li class="nav-item">
        <button @click="activeTab = 'approved'" class="nav-link rounded-pill px-4 fw-semibold border transition-all"
          :class="activeTab === 'approved' ? 'active shadow-sm' : 'bg-white text-dark'
            ">
          Đã đăng
        </button>
      </li>
      <li class="nav-item">
        <button @click="activeTab = 'rejected'" class="nav-link rounded-pill px-4 fw-semibold border transition-all"
          :class="activeTab === 'rejected' ? 'active shadow-sm' : 'bg-white text-dark'
            ">
          Bị từ chối
        </button>
      </li>
    </ul>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
      <div class="card-body p-3 d-flex flex-wrap gap-3 align-items-center">
        <div class="input-group flex-grow-1" style="max-width: 400px">
          <span class="input-group-text bg-light border-0 text-muted rounded-start-4">🔍</span>
          <input v-model="searchKeyword" type="text" placeholder="Tìm theo tên, địa chỉ hoặc giá..."
            class="form-control bg-light border-0 rounded-end-4 py-2 px-3 shadow-none" />
        </div>
        <button v-if="searchKeyword || activeTab !== 'all'" @click="resetFilters"
          class="btn btn-light border text-muted px-4 rounded-pill fw-medium transition-all hover-bg-gray">
          ✕ Làm mới
        </button>
      </div>
    </div>

    <div v-if="errorMessage" class="alert alert-danger rounded-4 border-0 shadow-sm text-center fw-medium mb-4">
      {{ errorMessage }}
    </div>
    <div v-if="successMessage" class="alert alert-success rounded-4 border-0 shadow-sm text-center fw-medium mb-4">
      {{ successMessage }}
    </div>

    <div v-if="loading" class="text-center py-5 text-muted">
      <div class="spinner-border text-primary mb-3" role="status"></div>
      <p>Đang tải dữ liệu...</p>
    </div>

    <div v-else>
      <div v-if="
        (searchKeyword || activeTab !== 'all') &&
        filteredProperties?.length > 0
      " class="mb-3 text-primary fw-semibold small">
        ✅ Tìm thấy {{ filteredProperties?.length || 0 }} kết quả phù hợp
      </div>

      <div v-for="item in filteredProperties" :key="item.id"
        class="card border-0 shadow-sm mb-3 rounded-4 property-card transition-all">
        <div class="row g-0 p-3 align-items-center">
          <div class="col-12 col-md-3 position-relative mb-3 mb-md-0">
            <div style="height: 150px; border-radius: 12px; overflow: hidden">
              <img :src="item.image" :alt="item.title" class="w-100 h-100" style="object-fit: cover" />
            </div>
            <span v-if="item.premium"
              class="badge bg-info text-dark position-absolute top-0 start-0 m-2 rounded-2 shadow-sm">PREMIUM</span>
            <div v-if="item.status === 'Đã bán'"
              class="position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center"
              style="background: rgba(0, 0, 0, 0.5); border-radius: 12px">
              <span class="badge border border-white bg-dark fs-6">ĐÃ BÁN</span>
            </div>
          </div>

          <div class="col-12 col-md-4 px-md-4 mb-3 mb-md-0">
            <h5 class="fw-bold text-dark mb-2 text-truncate" :title="item.title">
              {{ item.title }}
            </h5>
            <p class="text-muted small mb-3 text-truncate" :title="item.dia_chi_id">
              <span class="me-1">📍</span> {{ item.dia_chi_id }}
            </p>
            <p class="text-muted small mb-3">
              <span class="me-1">📅</span> Đăng ngày: {{ item.ngay_dang }}
            </p>
            <div class="d-flex gap-3 text-muted small fw-medium">
              <span v-if="item.dien_tich">📐 {{ item.dien_tich }} m²</span>
              <span v-if="item.so_phong_ngu">🛏️ {{ item.so_phong_ngu }} PN</span>
              <span v-if="item.so_phong_tam">🚿 {{ item.so_phong_tam }} WC</span>
            </div>
          </div>

          <div class="col-6 col-md-3 border-md-start px-md-4">
            <p class="text-uppercase text-muted fw-bold mb-1" style="font-size: 0.7rem">
              Mức Giá
            </p>
            <h5 class="text-primary fw-bold mb-2">{{ item.price }}</h5>
            <span class="badge rounded-pill px-3 py-2 fw-medium" :class="statusClasses(item.status)">
              <span class="me-1">•</span>{{ item.status }}
            </span>
            <div v-if="item.status === 'Bị từ chối'" class="mt-2">
              <RouterLink to="/moi-gioi/thong-bao" class="text-danger small text-decoration-none">
                <i class="bi bi-bell-fill me-1"></i>Xem lý do từ chối
              </RouterLink>
            </div>
          </div>

          <div class="col-6 col-md-2 d-flex justify-content-end align-items-center gap-2 mt-3 mt-md-0">
            <button v-if="item.status === 'Nháp'" @click="handlePublish(item)"
              class="btn btn-success rounded-3 btn-sm fw-semibold shadow-sm px-3 py-2" title="Đăng bài">
              Đăng
            </button>
            <button v-if="item.status === 'Đã đăng'" @click="handleMarkSold(item)"
              class="btn btn-warning rounded-3 btn-sm fw-semibold shadow-sm px-3 py-2 d-flex align-items-center gap-1"
              title="Đánh dấu đã bán">
              <i class="bi bi-check2-circle"></i>
              Đã bán
            </button>
            <button @click="handleEdit(item)"
              class="btn btn-light border text-primary rounded-3 btn-sm d-flex align-items-center justify-content-center"
              style="width: 40px; height: 40px" title="Cập nhật">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
            </button>
            <button @click="handleDelete(item)"
              class="btn btn-light border text-danger rounded-3 btn-sm d-flex align-items-center justify-content-center"
              style="width: 40px; height: 40px" title="Xóa">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <div v-if="!loading && filteredProperties?.length === 0" class="text-center py-5 bg-white rounded-4 shadow-sm">
        <p v-if="searchKeyword || activeTab !== 'all'" class="text-muted fs-5 mb-0">
          🔍 Không tìm thấy bất động sản phù hợp.
        </p>
        <p v-else class="text-muted fs-5 mb-0">
          📭 Chưa có bất động sản nào trong hệ thống.
        </p>
      </div>

      <div v-if="pagination.last_page > 1"
        class="d-flex justify-content-center align-items-center gap-2 mt-4 flex-wrap">
        <button @click="loadBatDongSan(pagination.current_page - 1)" :disabled="pagination.current_page === 1"
          class="btn btn-light border rounded-pill px-4 fw-medium">
          &larr; Trước
        </button>
        <button v-for="page in pagination.last_page" :key="page" @click="loadBatDongSan(page)"
          class="btn rounded-circle fw-bold" style="
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
          " :class="page === pagination.current_page
            ? 'btn-primary shadow-sm'
            : 'btn-light border'
            ">
          {{ page }}
        </button>
        <button @click="loadBatDongSan(pagination.current_page + 1)"
          :disabled="pagination.current_page === pagination.last_page"
          class="btn btn-light border rounded-pill px-4 fw-medium">
          Sau &rarr;
        </button>
      </div>
    </div>
  </div>

  <div v-if="showEditModal" class="modal fade show d-block" tabindex="-1"
    style="background: rgba(10, 10, 51, 0.6); backdrop-filter: blur(4px)">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
      <div class="modal-content border-0 shadow-lg" style="border-radius: 24px">
        <div class="modal-header border-bottom px-4 py-3">
          <div>
            <h5 class="modal-title fw-bold text-dark mb-0">
              Chỉnh sửa thông tin
            </h5>
            <small class="text-muted">Cập nhật chi tiết cho bất động sản của bạn</small>
          </div>
          <button type="button" class="btn-close" @click="cancelEdit"></button>
        </div>

        
        <div class="modal-body px-4 py-4">
          <div v-if="isLoadingDetail" class="text-center py-5 text-muted">
            <div class="spinner-border text-primary mb-3" role="status"></div>
            <p>Đang tải chi tiết...</p>
          </div>
          <div v-else class="row g-4">
            <!-- Thông tin cơ bản -->
            <div class="col-12"><h6 class="fw-bold text-primary border-bottom pb-2">1. Thông tin cơ bản</h6></div>
            <div class="col-12">
              <label class="form-label text-muted fw-bold small text-uppercase">📌 Tiêu Đề</label>
              <input v-model="editingProperty.title" type="text" class="form-control form-control-lg bg-light border-0 rounded-3 fs-6" />
            </div>
            <div class="col-md-6">
              <label class="form-label text-muted fw-bold small text-uppercase">Loại Bất Động Sản</label>
              <select v-model="editingProperty.loai_id" class="form-select form-control-lg bg-light border-0 rounded-3 fs-6">
                <option value="">Chọn loại</option>
                <option v-for="loai in loaiBDSList" :key="loai.id" :value="loai.id">{{ loai.ten_loai }}</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label text-muted fw-bold small text-uppercase">💰 Giá (VNĐ)</label>
              <input v-model="editingProperty.gia" type="number" class="form-control form-control-lg bg-light border-0 rounded-3 fs-6 fw-bold text-primary" />
            </div>
            <div class="col-md-4">
              <label class="form-label text-muted fw-bold small text-uppercase">📐 Diện Tích (m²)</label>
              <input v-model="editingProperty.dien_tich" type="number" class="form-control form-control-lg bg-light border-0 rounded-3 fs-6" />
            </div>
            <div class="col-md-4">
              <label class="form-label text-muted fw-bold small text-uppercase">🛏️ Phòng Ngủ</label>
              <input v-model.number="editingProperty.so_phong_ngu" type="number" class="form-control form-control-lg bg-light border-0 rounded-3 fs-6" />
            </div>
            <div class="col-md-4">
              <label class="form-label text-muted fw-bold small text-uppercase">🚿 Phòng Tắm</label>
              <input v-model.number="editingProperty.so_phong_tam" type="number" class="form-control form-control-lg bg-light border-0 rounded-3 fs-6" />
            </div>

            <!-- Vị trí -->
            <div class="col-12 mt-4"><h6 class="fw-bold text-primary border-bottom pb-2">2. Vị trí</h6></div>
            <div class="col-md-4">
              <label class="form-label text-muted fw-bold small text-uppercase">Tỉnh / Thành</label>
              <select v-model="editingProperty.tinh_id" @change="onTinhChange" class="form-select form-control-lg bg-light border-0 rounded-3 fs-6">
                <option value="">Chọn tỉnh</option>
                <option v-for="tinh in tinhThanhList" :key="tinh.id" :value="tinh.id">{{ tinh.ten }}</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label text-muted fw-bold small text-uppercase">Quận / Huyện</label>
              <select v-model="editingProperty.quan_id" @change="onQuanChange" class="form-select form-control-lg bg-light border-0 rounded-3 fs-6">
                <option value="">Chọn quận</option>
                <option v-for="quan in quanHuyenList" :key="quan.id" :value="quan.id">{{ quan.ten }}</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label text-muted fw-bold small text-uppercase">Phường / Xã</label>
              <select v-model="editingProperty.phuong_id" class="form-select form-control-lg bg-light border-0 rounded-3 fs-6">
                <option value="">Chọn phường</option>
                <option v-for="phuong in phuongList" :key="phuong.id" :value="phuong.id">{{ phuong.ten }}</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label text-muted fw-bold small text-uppercase">Địa chỉ chi tiết</label>
              <input v-model="editingProperty.dia_chi_chi_tiet" type="text" class="form-control form-control-lg bg-light border-0 rounded-3 fs-6" />
            </div>

            <!-- Hình ảnh -->
            <div class="col-12 mt-4"><h6 class="fw-bold text-primary border-bottom pb-2">3. Hình ảnh</h6></div>
            <div class="col-12">
              <div class="d-flex flex-wrap gap-2 mb-3">
                <div v-for="(img, idx) in imagePreviewUrls" :key="idx" class="position-relative" style="width: 100px; height: 100px; border-radius: 8px; overflow: hidden; border: 1px solid #ddd;">
                  <img :src="img" class="w-100 h-100" style="object-fit: cover" />
                  <button @click="removeImage(idx)" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 p-0" style="width: 20px; height: 20px; line-height: 1;">&times;</button>
                </div>
                <div class="position-relative" style="width: 100px; height: 100px; border-radius: 8px; border: 2px dashed #0d6efd; display: flex; align-items: center; justify-content: center; cursor: pointer;" @click="$refs.fileInput.click()">
                  <span class="fs-3 text-primary">+</span>
                </div>
                <input ref="fileInput" type="file" multiple accept="image/*" class="d-none" @change="handleFileSelect" />
              </div>
            </div>

            <!-- Mô tả -->
            <div class="col-12 mt-4"><h6 class="fw-bold text-primary border-bottom pb-2">4. Mô tả chi tiết</h6></div>
            <div class="col-12">
              <textarea v-model="editingProperty.mo_ta" rows="6" class="form-control bg-light border-0 rounded-3 fs-6"></textarea>
            </div>
          </div>
        </div>

        <div class="modal-footer border-top bg-light px-4 py-3" style="
            border-bottom-left-radius: 24px;
            border-bottom-right-radius: 24px;
          ">
          <button type="button" class="btn btn-light border px-4 py-2 rounded-3 fw-semibold" @click="cancelEdit">
            Hủy bỏ
          </button>
          <button type="button" class="btn btn-primary px-4 py-2 rounded-3 fw-semibold shadow-sm" @click="submitEdit"
            :disabled="isSubmitting">
            <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-2" role="status"
              aria-hidden="true"></span>
            Lưu Thay Đổi
          </button>
        </div>
      </div>
    </div>
  </div>

  <div v-if="showDeleteModal" class="modal fade show d-block" tabindex="-1"
    style="background: rgba(0, 0, 0, 0.6); backdrop-filter: blur(4px)">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg text-center p-4" style="border-radius: 24px">
        <div class="mb-3">
          <div class="d-inline-flex align-items-center justify-content-center bg-danger bg-opacity-10 rounded-circle"
            style="width: 80px; height: 80px">
            <span style="font-size: 40px">⚠️</span>
          </div>
        </div>
        <h4 class="fw-bold text-dark mb-2">Xác Nhận Xóa</h4>
        <p class="text-muted mb-1">Bạn sắp xóa bài đăng:</p>
        <p class="fw-bold text-dark fs-5 mb-4 px-3">
          "{{ deleteProperty?.title }}"
        </p>

        <div class="alert alert-danger border-0 rounded-3 mb-4 small fw-medium">
          Hành động này không thể hoàn tác. Bài đăng sẽ bị xóa vĩnh viễn.
        </div>

        <div class="d-flex gap-3">
          <button @click="cancelDelete" :disabled="isDeleting"
            class="btn btn-light border w-50 py-2 rounded-3 fw-semibold">
            Hủy
          </button>
          <button @click="confirmDelete" :disabled="isDeleting"
            class="btn btn-danger w-50 py-2 rounded-3 fw-semibold shadow-sm">
            <span v-if="isDeleting">⏳ Đang xử lý...</span>
            <span v-else>Xóa Vĩnh Viễn</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from "vue";
import { useRouter, RouterLink } from "vue-router";
import api from "@/axios/config.js";
import Swal from "sweetalert2";

// ROUTER
const router = useRouter();

// DATA
const properties = ref([]);
const loading = ref(false);
const searchKeyword = ref("");
const activeTab = ref("all");
const stats = ref({
  total: 0,
  approved: 0,
  pending: 0,
  rejected: 0,
  sold: 0,
});
const errorMessage = ref("");
const successMessage = ref("");
const isDeleting = ref(false);
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 5,
  total: 0,
});
// MODAL STATES
const showDeleteModal = ref(false);
const showEditModal = ref(false);
const deleteProperty = ref(null);
const editingProperty = ref(null);
const isSubmitting = ref(false);

const isLoadingDetail = ref(false);
const loaiBDSList = ref([]);
const tinhThanhList = ref([]);
const quanHuyenList = ref([]);
const phuongList = ref([]);
const oldImages = ref([]);
const newImages = ref([]);
const deletedImages = ref([]);
const imagePreviewUrls = ref([]);


// CHECK AUTH
const token =
  localStorage.getItem("moi_gioi_auth_token");

// LOAD DATA
const loadBatDongSan = async (page = 1) => {
  loading.value = true;
  errorMessage.value = "";

  try {
    const params = { page, per_page: 5 };
    if (activeTab.value === "draft") params.status = "draft";
    else if (activeTab.value !== "all") params.status = "published";

    const res = await api.get("/moi-gioi/bds/data", { params });

    if (res.data.status) {
      const result = res.data.data;
      const items = result.data || [];

      properties.value = items.map((item) => {
        let displayStatus = "";
        if (item.status === "draft") {
          displayStatus = "Nháp";
        } else {
          if (item.trang_thai_id == 1) displayStatus = "Chờ duyệt";
          else if (item.trang_thai_id == 2) displayStatus = "Đã đăng";
          else if (item.trang_thai_id == 3) displayStatus = "Đã bán";
          else if (item.trang_thai_id == 6) displayStatus = "Bị từ chối";
          else displayStatus = "Khác";
        }

        return {
          id: item.id,
          title: item.tieu_de,
          dia_chi_id: formatDiaChi(item),
          price: Number(item.gia).toLocaleString("vi-VN") + " VNĐ",
          status: displayStatus,
          premium: item.is_noi_bat == 1,
          image: getImageUrl(item),
          dien_tich: item.dien_tich,
          so_phong_ngu: item.so_phong_ngu,
          so_phong_tam: item.so_phong_tam,
          ngay_dang: formatNgayDang(item.created_at),
          raw: item,
        };
      });

      pagination.value = {
        current_page: result.current_page,
        last_page: result.last_page,
        per_page: result.per_page,
        total: result.total,
      };

      if (res.data.stats) {
        stats.value = res.data.stats;
      }
    }
  } catch (error) {
    errorMessage.value = "Lỗi tải dữ liệu";
  } finally {
    loading.value = false;
  }
};


// Helper: Format địa chỉ
const formatNgayDang = (dateString) => {
  if (!dateString) return "—";
  const date = new Date(dateString);
  const day = String(date.getDate()).padStart(2, "0");
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const year = date.getFullYear();
  const hours = String(date.getHours()).padStart(2, "0");
  const minutes = String(date.getMinutes()).padStart(2, "0");
  return `${day}/${month}/${year} ${hours}:${minutes}`;
};

const formatDiaChi = (item) => {
  if (!item.dia_chi) return `Địa chỉ ID: ${item.dia_chi_id}`;
  const quan =
    item.dia_chi?.quan?.ten || item.dia_chi?.quan?.ten_quan || "Quận/Huyện";
  const tinh =
    item.dia_chi?.tinh?.ten || item.dia_chi?.tinh?.ten_tinh || "Tỉnh/TP";
  const diaChi = item.dia_chi?.dia_chi_chi_tiet || "";
  return diaChi ? `${diaChi}, ${quan}, ${tinh}` : `${quan}, ${tinh}`;
};

// Helper: Lấy ảnh đại diện
const getImageUrl = (item) => {
  if (!item) return "/no-image.png";

  // Lấy URL thô từ object hoặc string
  let url = "";
  if (typeof item === 'string') {
    url = item;
  } else {
    url = item.anh_dai_dien_url || (item.hinh_anh?.[0]?.url) || (item.anh_dai_dien?.url) || "";
  }

  if (!url) return "/no-image.png";
  if (url.startsWith("http")) return url;

  const base = import.meta.env.VITE_API_URL?.replace('/api', '') || 'http://localhost:8000';
  const cleanUrl = url.startsWith('/') ? url.substring(1) : url;
  const finalUrl = cleanUrl.startsWith('storage/') ? cleanUrl : `storage/${cleanUrl}`;

  return `${base}/${finalUrl}`;
};

// FILTER & SEARCH
const filteredProperties = computed(() => {
  return properties.value.filter((item) => {
    if (activeTab.value === "draft" && item.status !== "Nháp") return false;
    if (activeTab.value === "pending" && item.status !== "Chờ duyệt")
      return false;
    if (activeTab.value === "approved" && item.status !== "Đã đăng")
      return false;
    if (activeTab.value === "rejected" && item.status !== "Bị từ chối")
      return false;

    if (searchKeyword.value.trim()) {
      const keyword = searchKeyword.value.toLowerCase();
      return (
        item.title.toLowerCase().includes(keyword) ||
        item.dia_chi_id.toLowerCase().includes(keyword) ||
        item.price.includes(searchKeyword.value)
      );
    }
    return true;
  });
});

// ACTIONS

const handleEdit = async (property) => {
  editingProperty.value = {
    id: property.id,
    title: property.title,
    dia_chi_id: "",
    gia: parseInt(property.raw?.gia) || 0,
    dien_tich: parseInt(property.raw?.dien_tich) || 0,
    so_phong_ngu: parseInt(property.raw?.so_phong_ngu) || 0,
    so_phong_tam: parseInt(property.raw?.so_phong_tam) || 0,
    mo_ta: property.raw?.mo_ta || "",
    loai_id: property.raw?.loai_id ? String(property.raw.loai_id) : "",
    tinh_id: "",
    quan_id: "",
    phuong_id: "",
    dia_chi_chi_tiet: "",
    latitude: null,
    longitude: null,
  };
  
  showEditModal.value = true;
  isLoadingDetail.value = true;
  
  // Reset images
  oldImages.value = [];
  newImages.value = [];
  deletedImages.value = [];
  imagePreviewUrls.value = [];

  try {
    const res = await api.get(`/client/bat-dong-san/${property.id}`);
    if (res.data?.status) {
      const detail = res.data.data;
      editingProperty.value.mo_ta = detail.mo_ta || "";
      
      if (detail.dia_chi) {
        const t_id = detail.dia_chi.tinh_id ? String(detail.dia_chi.tinh_id) : "";
        const q_id = detail.dia_chi.quan_id ? String(detail.dia_chi.quan_id) : "";
        const p_id = detail.dia_chi.phuong_xa_id ? String(detail.dia_chi.phuong_xa_id) : "";

        // 1. Gán Tỉnh và load Quận
        editingProperty.value.tinh_id = t_id;
        if (t_id) {
          await onTinhChange(false);
          
          // 2. Sau khi có danh sách Quận, gán Quận và load Phường
          if (q_id) {
            editingProperty.value.quan_id = q_id;
            await onQuanChange(false);
            
            // 3. Sau khi có danh sách Phường, gán Phường
            if (p_id) {
              editingProperty.value.phuong_id = p_id;
            }
          }
        }
        
        editingProperty.value.dia_chi_chi_tiet = detail.dia_chi.dia_chi_chi_tiet || "";
        editingProperty.value.latitude = detail.dia_chi.latitude;
        editingProperty.value.longitude = detail.dia_chi.longitude;
      }

      if (detail.hinh_anh) {
        oldImages.value = detail.hinh_anh;
        imagePreviewUrls.value = detail.hinh_anh.map(img => getImageUrl(img.url));
      }
    }
  } catch (e) {
    console.error("Lỗi lấy chi tiết BDS", e);
  } finally {
    isLoadingDetail.value = false;
  }
};



const onTinhChange = async (shouldReset = true) => {
  quanHuyenList.value = [];
  if (shouldReset) {
    editingProperty.value.quan_id = "";
    editingProperty.value.phuong_id = "";
  }
  if (!editingProperty.value.tinh_id) return;
  try {
    const res = await api.get(`/quan-huyen-by-tinh`, { params: { tinh_id: editingProperty.value.tinh_id } });
    if (res.data?.status) quanHuyenList.value = res.data.data.map(x => ({...x, id: String(x.id)}));
  } catch (error) {}
};

const onQuanChange = async (shouldReset = true) => {
  phuongList.value = [];
  if (shouldReset) {
    editingProperty.value.phuong_id = "";
  }
  if (!editingProperty.value.quan_id) return;
  try {
    const res = await api.get(`/phuong-xa-by-quan-huyen`, { 
      params: { quan_huyen_id: editingProperty.value.quan_id } 
    });
    if (res.data?.status === 'success' || res.data?.status === true) {
      phuongList.value = res.data.data.map(x => ({...x, id: String(x.id)}));
    }
  } catch (error) {
    console.error("Lỗi lấy danh sách phường xã:", error);
  }
};

const handleFileSelect = (event) => {
  const files = Array.from(event.target.files);
  files.forEach((file) => {
    newImages.value.push(file);
    const reader = new FileReader();
    reader.onload = (e) => imagePreviewUrls.value.push(e.target.result);
    reader.readAsDataURL(file);
  });
};

const removeImage = (index) => {
  if (index < oldImages.value.length) {
    deletedImages.value.push(oldImages.value[index].id);
    oldImages.value.splice(index, 1);
  } else {
    const newIdx = index - oldImages.value.length;
    newImages.value.splice(newIdx, 1);
  }
  imagePreviewUrls.value.splice(index, 1);
};


const submitEdit = async () => {
  if (!editingProperty.value.title.trim()) {
    alert("⚠️ Vui lòng nhập tiêu đề");
    return;
  }
  isSubmitting.value = true;
  errorMessage.value = "";

  try {
    const formData = new FormData();
    formData.append("id", editingProperty.value.id);
    formData.append("tieu_de", editingProperty.value.title);
    formData.append("gia", editingProperty.value.gia || 0);
    formData.append("dien_tich", editingProperty.value.dien_tich || 0);
    formData.append("so_phong_ngu", editingProperty.value.so_phong_ngu || 0);
    formData.append("so_phong_tam", editingProperty.value.so_phong_tam || 0);
    formData.append("mo_ta", editingProperty.value.mo_ta || "");
    if (editingProperty.value.loai_id) formData.append("loai_id", editingProperty.value.loai_id);
    if (editingProperty.value.tinh_id) formData.append("tinh_id", editingProperty.value.tinh_id);
    if (editingProperty.value.quan_id) formData.append("quan_id", editingProperty.value.quan_id);
    if (editingProperty.value.phuong_id) formData.append("phuong_id", editingProperty.value.phuong_id);
    if (editingProperty.value.dia_chi_chi_tiet) formData.append("dia_chi_chi_tiet", editingProperty.value.dia_chi_chi_tiet);
    if (editingProperty.value.latitude) formData.append("latitude", editingProperty.value.latitude);
    if (editingProperty.value.longitude) formData.append("longitude", editingProperty.value.longitude);

    deletedImages.value.forEach((id, idx) => {
      formData.append(`deleted_images[${idx}]`, id);
    });

    newImages.value.forEach((file) => {
      formData.append("hinh_anh[]", file);
    });

    const res = await api.post("/moi-gioi/bds/update", formData, {
      headers: { "Content-Type": "multipart/form-data" },
    });

    if (res.data.status) {
      successMessage.value = "✅ Cập nhật thành công! Đang chờ duyệt lại...";
      showEditModal.value = false;
      setTimeout(() => {
        loadBatDongSan();
        successMessage.value = "";
      }, 2000);
    } else {
      errorMessage.value = `❌ ${res.data.message || "Cập nhật thất bại"}`;
    }
  } catch (error) {
    const message = error.response?.data?.message || error.message || "Lỗi không xác định";
    errorMessage.value = `❌ Cập nhật thất bại: ${message}`;
  } finally {
    isSubmitting.value = false;
  }
};


const cancelEdit = () => {
  showEditModal.value = false;
  editingProperty.value = null;
};

const handleDelete = (property) => {
  deleteProperty.value = property;
  showDeleteModal.value = true;
};

const confirmDelete = async () => {
  isDeleting.value = true;
  errorMessage.value = "";
  successMessage.value = "";

  try {
    const res = await api.delete("/moi-gioi/bds/delete", {
      data: { id: deleteProperty.value.id },
    });

    if (res.data.status) {
      successMessage.value = "✅ Xóa bài đăng thành công!";
      showDeleteModal.value = false;
      deleteProperty.value = null;
      await loadBatDongSan();
      setTimeout(() => {
        successMessage.value = "";
      }, 1500);
    } else {
      errorMessage.value = res.data.message || "Xóa thất bại";
    }
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message || error.message || "Lỗi xóa bài đăng";
  } finally {
    isDeleting.value = false;
  }
};

const cancelDelete = () => {
  showDeleteModal.value = false;
  deleteProperty.value = null;
};

const handlePublish = async (item) => {
  const { isConfirmed } = await Swal.fire({
    title: "Xác nhận đăng bài?",
    text: "Bạn có chắc chắn muốn đăng bài viết này lên hệ thống?",
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Đồng ý",
    cancelButtonText: "Hủy",
  });
  if (!isConfirmed) return;

  try {
    const res = await api.post(`/moi-gioi/bds/${item.id}/publish`);
    if (res.data.status) {
      Swal.fire({
        toast: true,
        position: "top-end",
        icon: "success",
        title: "Đã đăng bài thành công!",
        showConfirmButton: false,
        timer: 2000,
      });
      loadBatDongSan(pagination.value.current_page);
    } else {
      Swal.fire("Lỗi", res.data.message || "Đăng bài thất bại", "error");
    }
  } catch (error) {
    Swal.fire("Lỗi", error.response?.data?.message || "Đã xảy ra lỗi", "error");
  }
};

const handleMarkSold = async (item) => {
  const { isConfirmed } = await Swal.fire({
    title: "Xác nhận đã bán?",
    text: "Bạn có chắc chắn muốn đánh dấu bất động sản này là ĐÃ BÁN? Tin đăng sẽ ngừng hiển thị công khai.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Xác nhận",
    cancelButtonColor: "#f59e0b",
    cancelButtonText: "Hủy",
  });
  if (!isConfirmed) return;

  try {
    const res = await api.post("/moi-gioi/bds/change-status", {
      id: item.id,
      trang_thai_id: 3 // ID của 'Đã bán'
    });

    if (res.data.status) {
      Swal.fire({
        toast: true,
        position: "top-end",
        icon: "success",
        title: "Cập nhật thành công!",
        showConfirmButton: false,
        timer: 2000,
      });
      loadBatDongSan(pagination.value.current_page);
    } else {
      Swal.fire("Lỗi", res.data.message || "Cập nhật thất bại", "error");
    }
  } catch (error) {
    Swal.fire("Lỗi", error.response?.data?.message || "Đã xảy ra lỗi", "error");
  }
};

// UI STATUS CLASSES (Chuyển đổi sang Bootstrap colors)
const statusClasses = (status) => {
  switch (status) {
    case "Đã đăng":
      return "bg-success bg-opacity-10 text-success border border-success";
    case "Chờ duyệt":
      return "bg-warning bg-opacity-10 text-warning border border-warning";
    case "Nháp":
      return "bg-secondary bg-opacity-10 text-secondary border border-secondary";
    case "Bị từ chối":
      return "bg-danger bg-opacity-10 text-danger border border-danger";
    case "Đã bán":
      return "bg-dark bg-opacity-10 text-dark border border-dark";
    default:
      return "bg-light text-secondary border";
  }
};

const resetFilters = () => {
  searchKeyword.value = "";
  activeTab.value = "all";
};

watch(activeTab, () => {
  pagination.value.current_page = 1;
  loadBatDongSan();
});


const loadInitialData = async () => {
  try {
    const [loaiRes, tinhRes] = await Promise.all([
      api.get("/client/loai-bat-dong-san"),
      api.get("/tinh-thanh")
    ]);
    if (loaiRes.data?.status) loaiBDSList.value = loaiRes.data.data.map(x => ({...x, id: String(x.id)}));
    if (tinhRes.data?.status) tinhThanhList.value = tinhRes.data.data.map(x => ({...x, id: String(x.id)}));
  } catch (e) {
    console.error("Lỗi load data:", e);
  }
};

onMounted(() => {
  loadInitialData();
  loadBatDongSan();
});
</script>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap");

.property-card {
  transition:
    transform 0.2s ease,
    box-shadow 0.2s ease;
  border: 1px solid transparent !important;
}

.property-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important;
  border-color: #e2e8f0 !important;
}

.transition-all {
  transition: all 0.2s ease-in-out;
}

.hover-bg-gray:hover {
  background-color: #e9ecef !important;
}

.hide-scrollbar::-webkit-scrollbar {
  display: none;
}

.hide-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

.nav-pills .nav-link {
  color: #6c757d;
}

.nav-pills .nav-link.active {
  background-color: #0d6efd;
  color: #fff !important;
}

.form-control:focus {
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
}
</style>
