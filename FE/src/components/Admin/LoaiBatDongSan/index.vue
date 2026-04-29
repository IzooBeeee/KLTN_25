<template>
  <div class="property-type-management p-4">
    <div class="row mb-4">
      <div class="col-12">
        <div class="card border-0 shadow-sm custom-header-card">
          <div
            class="card-body d-flex flex-column flex-md-row justify-content-between align-items-md-center py-3 gap-3"
          >
            <div class="p-2">
              <h4 class="mb-0 fw-bold text-primary">
                <i class="bi bi-tags-fill me-2"></i>Loại bất động sản
              </h4>
              <small class="text-muted"
                >Quản lý các danh mục phân loại bất động sản trên hệ
                thống.</small
              >
            </div>

            <div class="d-flex justify-content-center align-items-center gap-2">
              <div class="input-group" style="max-width: 200px">
                <select
                  v-model="statusFilter"
                  class="form-select custom-input text-muted rounded-pill"
                >
                  <option value="">Tất cả trạng thái</option>
                  <option value="active">Đang hoạt động</option>
                  <option value="inactive">Đã khóa</option>
                </select>
              </div>

              <div class="input-group" style="max-width: 250px">
                <input
                  v-model="search"
                  type="text"
                  class="form-control custom-input rounded-start-pill"
                  placeholder="Tìm tên loại BĐS..."
                />
                <button
                  class="btn btn-outline-secondary shadow-none rounded-end-circle"
                  type="button"
                >
                  <i class="bi bi-search"></i>
                </button>
              </div>

              <button
                @click="openAdd"
                class="btn btn-primary text-nowrap rounded-pill btn-lg shadow-sm fw-bold d-flex align-items-center"
              >
                <i class="bi bi-plus-circle me-2"></i> Thêm loại BĐS
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row mb-4 g-3">
      <div class="col-md-4">
        <div class="card border-0 shadow-sm">
          <div
            class="card-body d-flex justify-content-between align-items-center"
          >
            <div>
              <small class="text-muted fw-bold">TỔNG SỐ DANH MỤC</small>
              <h3 class="fw-bold text-primary mb-0">
                {{ loaiBDSList.length }}
              </h3>
            </div>
            <i class="bi bi-ui-radios-grid fs-1 text-primary opacity-25"></i>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-success text-white">
          <div
            class="card-body d-flex justify-content-between align-items-center"
          >
            <div>
              <small class="opacity-75 fw-bold">ĐANG HOẠT ĐỘNG</small>
              <h3 class="fw-bold mb-0">{{ soDangHoatDong }}</h3>
            </div>
            <i class="bi bi-check-circle fs-1 opacity-25"></i>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-danger bg-opacity-10">
          <div
            class="card-body d-flex justify-content-between align-items-center"
          >
            <div>
              <small class="text-danger fw-bold">ĐÃ KHÓA</small>
              <h3 class="fw-bold text-danger mb-0">{{ soBiKhoa }}</h3>
            </div>
            <i class="bi bi-lock fs-1 text-danger opacity-50"></i>
          </div>
        </div>
      </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
      <!-- Thay thế toàn bộ <div class="card-body p-0">...</div> chứa table bằng: -->
      <div class="card-list p-3">
        <!-- Loading State -->
        <div v-if="isLoading" class="text-center py-5">
          <div class="spinner-border text-primary" role="status"></div>
          <p class="mt-2 text-muted">Đang tải dữ liệu...</p>
        </div>

        <!-- Empty State -->
        <div
          v-else-if="filteredList.length === 0"
          class="text-center py-5 text-muted"
        >
          <i class="bi bi-inbox fs-1 d-block mb-2"></i>
          Không có dữ liệu danh mục
        </div>

        <!-- Card Items -->
        <div v-else class="d-flex flex-column gap-2">
          <div
            v-for="item in paginatedList"
            :key="item.id"
            class="card-item d-flex align-items-center justify-content-between p-3 bg-white border rounded-3 shadow-sm hover-shadow"
          >
            <!-- Left: Icon + Info -->
            <div class="d-flex align-items-center gap-3 flex-grow-1">
              <div
                class="avatar-sm bg-primary bg-opacity-10 text-primary rounded-3 d-flex align-items-center justify-content-center"
              >
                <i class="bi bi-tag-fill"></i>
              </div>
              <div>
                <h6 class="fw-bold text-dark mb-0">
                  {{ item.ten_loai || item.name }}
                </h6>
                <small class="text-muted"
                  >ID: #{{ String(item.id).padStart(3, "0") }}</small
                >
              </div>
            </div>

            <!-- Center: Status -->
            <div class="text-center px-3">
              <button
                @click="changeStatus(item, !item.is_active)"
                :class="
                  item.is_active !== false && item.is_active !== 0
                    ? 'badge-active'
                    : 'badge-inactive'
                "
                class="badge w-100 px-3 py-2 rounded-pill small fw-bold border-0 cursor-pointer"
              >
                {{
                  item.is_active !== false && item.is_active !== 0
                    ? "Đang hoạt động"
                    : "Đã khóa"
                }}
              </button>
            </div>

            <!-- Right: Actions -->
            <div class="d-flex gap-1">
              <button
                @click="openEdit(item)"
                class="btn-icon-sm btn-light-primary"
                title="Sửa"
              >
                <i class="bi bi-pencil"></i>
              </button>
              <button
                @click="openDelete(item)"
                class="btn-icon-sm btn-light-danger"
                title="Xóa"
              >
                <i class="bi bi-trash"></i>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination Footer -->
      <div class="card-footer bg-white border-0 py-3 px-4">
        <div
          class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3"
        >
          <!-- Info -->
          <small class="text-muted fw-medium">
            {{ paginationInfo }}
          </small>

          <!-- Pagination Buttons -->
          <div class="d-flex align-items-center gap-1" v-if="totalPages > 1">
            <!-- Previous -->
            <button
              @click="prevPage"
              :disabled="currentPage === 1"
              class="btn btn-sm btn-light border"
              :class="{ disabled: currentPage === 1 }"
            >
              &laquo;
            </button>

            <!-- Page Numbers -->
            <button
              v-for="page in visiblePages"
              :key="page"
              @click="goToPage(page)"
              class="btn btn-sm btn-primary"
              :class="
                page === currentPage
                  ? 'btn-primary'
                  : 'btn-light border text-muted'
              "
            >
              {{ page }}
            </button>

            <!-- Next -->
            <button
              @click="nextPage"
              :disabled="currentPage === totalPages"
              class="btn btn-sm btn-light border"
              :class="{ disabled: currentPage === totalPages }"
            >
              &raquo;
            </button>
          </div>
        </div>
      </div>
    </div>

    <div
      v-if="showAdd || showEdit || showDelete"
      class="modal-backdrop fade show"
      style="z-index: 1040"
    ></div>

    <div
      v-if="showAdd || showEdit"
      class="modal fade show d-block"
      tabindex="-1"
      style="z-index: 1050"
    >
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
          <div class="modal-header border-0 pt-4 px-4 pb-0">
            <h5 class="fw-bold text-primary mb-0">
              <i class="bi bi-bookmark-star-fill me-2"></i
              >{{
                showAdd
                  ? "Thêm Loại Bất Động Sản"
                  : "Cập nhật Loại Bất Động Sản"
              }}
            </h5>
            <button
              @click="closeModals"
              type="button"
              class="btn-close shadow-none"
            ></button>
          </div>
          <div class="modal-body px-4 py-4">
            <div class="mb-3">
              <label
                class="form-label small fw-bold text-muted text-uppercase tracking-widest"
                >Tên loại BĐS <span class="text-danger">*</span></label
              >
              <input
                v-model="formData.ten_loai"
                type="text"
                placeholder="VD: Căn hộ chung cư"
                class="form-control custom-input fw-bold"
              />
            </div>
            <div class="mb-3">
              <label
                class="form-label small fw-bold text-muted text-uppercase tracking-widest"
                >Mô tả</label
              >
              <textarea
                v-model="formData.mo_ta"
                rows="3"
                placeholder="Nhập mô tả ngắn gọn..."
                class="form-control custom-input fw-medium"
              ></textarea>
            </div>
            <div class="mb-2">
              <label
                class="form-label small fw-bold text-muted text-uppercase tracking-widest"
                >Trạng thái</label
              >
              <div class="form-check form-switch fs-5">
                <input
                  v-model="formData.is_active"
                  class="form-check-input"
                  type="checkbox"
                  role="switch"
                />
                <label
                  class="form-check-label fs-6 fw-medium text-dark ms-2 mt-1"
                  >{{ formData.is_active ? "Kích hoạt" : "Tạm khóa" }}</label
                >
              </div>
            </div>
          </div>
          <div class="modal-footer border-0 pb-4 px-4 pt-0">
            <button
              @click="closeModals"
              class="btn btn-light rounded-pill px-4"
            >
              Hủy bỏ
            </button>
            <button
              @click="saveData"
              class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm"
            >
              {{ showAdd ? "Lưu thông tin" : "Lưu thay đổi" }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <div
      v-if="showDelete"
      class="modal fade show d-block"
      tabindex="-1"
      style="z-index: 1050"
    >
      <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg rounded-4 text-center p-4">
          <div class="mb-3">
            <div
              class="avatar-sm bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center mx-auto"
              style="width: 60px; height: 60px"
            >
              <i class="bi bi-trash3-fill fs-1"></i>
            </div>
          </div>
          <h5 class="fw-bold mb-3">Xác nhận xóa!</h5>
          <p class="text-muted mb-4 small">
            Xóa danh mục
            <strong class="text-danger"
              >"{{ deleteData?.ten_loai || deleteData?.name }}"</strong
            >?<br />
            Cảnh báo: Hành động này có thể ảnh hưởng đến các BĐS đang thuộc danh
            mục này.
          </p>
          <div class="d-flex justify-content-center gap-2">
            <button
              @click="closeModals"
              class="btn btn-light rounded-pill px-4"
            >
              Hủy
            </button>
            <button
              @click="confirmDelete"
              class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm"
            >
              Xóa ngay
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from "@/axios/config";

export default {
  data() {
    return {
      loaiBDSList: [],
      isLoading: false,

      search: "",
      statusFilter: "",

      showAdd: false,
      showEdit: false,
      showDelete: false,

      currentPage: 1,
      itemsPerPage: 5,

      // Model chung cho thêm & sửa
      formData: {
        id: null,
        ten_loai: "",
        mo_ta: "",
        is_active: true,
      },
      deleteData: null,

      // Cập nhật Base URL API của bạn
    };
  },

  computed: {
    paginatedList() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      const end = start + this.itemsPerPage;
      return this.filteredList.slice(start, end);
    },
    totalPages() {
      return Math.ceil(this.filteredList.length / this.itemsPerPage);
    },
    visiblePages() {
      const pages = [];
      const maxVisible = 5; // Số nút page hiển thị tối đa
      let start = Math.max(1, this.currentPage - Math.floor(maxVisible / 2));
      let end = Math.min(this.totalPages, start + maxVisible - 1);

      if (end - start < maxVisible - 1) {
        start = Math.max(1, end - maxVisible + 1);
      }

      for (let i = start; i <= end; i++) {
        pages.push(i);
      }
      return pages;
    },

    // Info phân trang cho footer
    paginationInfo() {
      const start =
        this.filteredList.length === 0
          ? 0
          : (this.currentPage - 1) * this.itemsPerPage + 1;
      const end = Math.min(
        this.currentPage * this.itemsPerPage,
        this.filteredList.length
      );
      return `Hiển thị ${start}-${end} của ${this.filteredList.length} danh mục`;
    },
    
    filteredList() {
      let data = [...this.loaiBDSList];

      // Lọc theo tên
      if (this.search) {
        const kw = this.search.toLowerCase();
        data = data.filter((v) =>
          (v.ten_loai || v.name || "").toLowerCase().includes(kw)
        );
      }

      // Lọc theo trạng thái
      if (this.statusFilter === "active") {
        data = data.filter((v) => v.is_active !== false && v.is_active !== 0);
      } else if (this.statusFilter === "inactive") {
        data = data.filter((v) => v.is_active === false || v.is_active === 0);
      }

      return data;
    },
    soDangHoatDong() {
      return this.loaiBDSList.filter(
        (v) => v.is_active !== false && v.is_active !== 0
      ).length;
    },
    soBiKhoa() {
      return this.loaiBDSList.filter(
        (v) => v.is_active === false || v.is_active === 0
      ).length;
    },
  },

  mounted() {
    this.loadData();
  },

  methods: {
    getToken() {
      return (
        localStorage.getItem("token") || localStorage.getItem("auth_token")
      );
    },

    getHeaders() {
      const token = this.getToken();
      return {
        headers: { Authorization: `Bearer ${token}` },
      };
    },

    // 1. GET DATA
    async loadData() {
      this.isLoading = true;
      try {
        const res = await api.get('/admin/loai-bds/data');
        if (res.data?.status) {
          // Xử lý linh hoạt cấu trúc trả về của API
          this.loaiBDSList = res.data.data?.data || res.data.data || [];
        }
      } catch (err) {
        console.error("Lỗi load danh sách:", err);
      } finally {
        this.isLoading = false;
      }
    },

    // Xử lý giao diện Modals
    openAdd() {
      this.formData = { id: null, ten_loai: "", mo_ta: "", is_active: true };
      this.showAdd = true;
      document.body.classList.add("modal-open");
    },

    openEdit(item) {
      // Map đúng tên trường trả về từ database
      this.formData = {
        id: item.id,
        ten_loai: item.ten_loai || item.name || "",
        mo_ta: item.mo_ta || item.description || "",
        is_active: item.is_active !== false && item.is_active !== 0,
      };
      this.showEdit = true;
      document.body.classList.add("modal-open");
    },

    openDelete(item) {
      this.deleteData = item;
      this.showDelete = true;
      document.body.classList.add("modal-open");
    },

    closeModals() {
      this.showAdd = false;
      this.showEdit = false;
      this.showDelete = false;
      this.formData = { id: null, ten_loai: "", mo_ta: "", is_active: true };
      this.deleteData = null;
      document.body.classList.remove("modal-open");
    },

    // 2. TẠO & CẬP NHẬT (Lưu thông tin)
    async saveData() {
      if (!this.formData.ten_loai.trim()) {
        alert("Vui lòng nhập tên loại BĐS!");
        return;
      }

      // Format data (Chuyển boolean sang 1/0 nếu DB yêu cầu)
      const payload = {
        ten_loai: this.formData.ten_loai,
        mo_ta: this.formData.mo_ta,
        is_active: this.formData.is_active ? 1 : 0,
      };

      try {
        if (this.showAdd) {
          // POST /create
          await api.post('/admin/loai-bds/create', payload);
          alert("Thêm thành công!");
        } else {
          // PUT /update/{id}
          await api.put(`/admin/loai-bds/update/${this.formData.id}`, payload);
          alert("Cập nhật thành công!");
        }
        this.loadData();
        this.closeModals();
      } catch (err) {
        console.error("Lỗi lưu dữ liệu:", err);
        alert(err.response?.data?.message || "Có lỗi xảy ra khi lưu dữ liệu.");
      }
    },

    // 3. XÓA (DELETE /delete/{id})
    async confirmDelete() {
      if (!this.deleteData?.id) return;
      try {
        await api.delete(`/admin/loai-bds/delete/${this.deleteData.id}`);
        alert("Xóa thành công!");
        this.loadData();
        this.closeModals();
      } catch (err) {
        console.error("Lỗi xóa dữ liệu:", err);
        alert(err.response?.data?.message || "Không thể xóa danh mục này.");
      }
    },

    //4. Thay đổi trạng thái
    async changeStatus(customer, newStatus) {
      try {
        const token = this.getToken();
        if (!token) return;
        const res = await api.post('/admin/loai-bds/change-status', { id: customer.id, is_active: newStatus ? 1 : 0 });
        if (res.data?.status) {
          customer.is_active = newStatus;
          this.showToast(newStatus ? "Đã kích hoạt" : "Đã khóa", "success");
        }
      } catch (err) {
        this.handleError(err, "Cập nhật trạng thái thất bại");
      }
    },

    // Chuyển trang
    goToPage(page) {
      if (page < 1 || page > this.totalPages || page === this.currentPage)
        return;
      this.currentPage = page;
      // Scroll lên đầu danh sách khi đổi trang
      document
        .querySelector(".card-list")
        ?.scrollIntoView({ behavior: "smooth" });
    },

    // Trang trước / sau
    prevPage() {
      this.goToPage(this.currentPage - 1);
    },
    nextPage() {
      this.goToPage(this.currentPage + 1);
    },

    // Reset về trang 1 khi filter/search thay đổi
    resetPagination() {
      this.currentPage = 1;
    },
  },
};
</script>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap");
@import url("https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css");

.property-type-management {
  font-family: "Inter", sans-serif;
  background-color: #f8f9fa;
  min-height: 100vh;
}

/* Custom UI Core */
.card {
  border-radius: 16px;
}
.custom-header-card {
  background: white;
}
.avatar-sm {
  width: 40px;
  height: 40px;
  font-size: 1.2rem;
}

/* Input Fields */
.custom-input {
  border-radius: 10px;
  padding: 10px 15px;
  border: 1px solid #dee2e6;
  background-color: #fcfcfc;
  transition: all 0.2s;
}
.custom-input:focus {
  border-color: #0d6efd;
  background-color: white;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

/* Switch Toggle (Thay đổi style form-switch của bootstrap) */
.form-switch .form-check-input {
  width: 2.5em;
  height: 1.25em;
  cursor: pointer;
}
.form-switch .form-check-input:checked {
  background-color: #0d6efd;
  border-color: #0d6efd;
}

/* Buttons Icon */
.btn-icon {
  width: 36px;
  height: 36px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  transition: all 0.2s;
}
.btn-light-primary {
  background: #e7f1ff;
  color: #0d6efd;
  border: none;
}
.btn-light-danger {
  background: #fff5f5;
  color: #dc3545;
  border: none;
}
.btn-light-primary:hover {
  background: #0d6efd;
  color: white;
  transform: translateY(-1px);
}
.btn-light-danger:hover {
  background: #dc3545;
  color: white;
  transform: translateY(-1px);
}

/* Table Styling */
.table thead th {
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.8px;
  font-weight: 700;
  color: #6c757d;
  background-color: #fcfcfc;
  padding: 15px;
  border: none;
}
.table tbody td {
  padding: 15px;
  border-bottom: 1px solid #f1f1f1;
}

/* Status Badges */
.badge-active {
  background-color: #e6fcf5;
  color: #087f5b;
}
.badge-inactive {
  background-color: #fff5f5;
  color: #dc3545;
}

/* Custom Scrollbar */
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #dee2e6;
  border-radius: 10px;
}

/* Pagination Button Styles */
.btn-sm.rounded-circle {
  width: 32px;
  height: 32px;
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.875rem;
  transition: all 0.2s;
}

.btn-sm.rounded-circle:hover:not(.disabled) {
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.btn-sm.rounded-circle.disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Reset pagination khi filter thay đổi */
/* Thêm watch để auto reset */
</style>