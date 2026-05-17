<template>
  <div class="property-type-management p-4">
    <div class="row mb-4">
      <div class="col-12">
        <div class="card border-0 shadow-sm custom-header-card">
          <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-md-center py-3 gap-3">
            <div class="p-2">
              <h4 class="mb-0 fw-bold text-primary">
                <i class="bi bi-tags-fill me-2"></i>Loại bất động sản
              </h4>
              <small class="text-muted">Quản lý các danh mục phân loại bất động sản trên hệ thống.</small>
            </div>

            <div class="d-flex justify-content-center align-items-center gap-2">
              <div class="input-group" style="max-width: 200px">
                <select v-model="statusFilter" @change="applyFilter" class="form-select custom-input text-muted rounded-pill">
                  <option value="">Tất cả trạng thái</option>
                  <option value="active">Đang hoạt động</option>
                  <option value="inactive">Đã khóa</option>
                </select>
              </div>

              <div class="input-group" style="max-width: 250px">
                <input v-model="search" @keyup.enter="applyFilter" type="text" class="form-control custom-input rounded-start-pill" placeholder="Tìm tên loại BĐS..." />
                <button @click="applyFilter" class="btn btn-outline-secondary shadow-none rounded-end-circle" type="button">
                  <i class="bi bi-search"></i>
                </button>
              </div>

              <button @click="openAdd" class="btn btn-primary text-nowrap rounded-pill btn-lg shadow-sm fw-bold d-flex align-items-center btn-hover-elevate">
                <i class="bi bi-plus-circle me-2"></i> Thêm loại BĐS
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="card border-0 shadow-sm mb-4 glass-card">
      <div class="card-list p-3">
        <!-- Loading State -->
        <div v-if="isLoading" class="text-center py-5">
          <div class="spinner-grow text-primary" role="status"></div>
          <p class="mt-2 text-muted fw-bold tracking-widest">ĐANG TẢI DỮ LIỆU...</p>
        </div>

        <!-- Empty State -->
        <div v-else-if="loaiBDSList.length === 0" class="text-center py-5 text-muted empty-state-animated">
          <i class="bi bi-inbox fs-1 d-block mb-3 text-primary opacity-50"></i>
          <h5 class="fw-bold">Không tìm thấy dữ liệu</h5>
          <p class="small">Hãy thử điều chỉnh lại bộ lọc hoặc thêm mới.</p>
        </div>

        <!-- Card Items -->
        <div v-else class="d-flex flex-column gap-3">
          <div v-for="(item, index) in loaiBDSList" :key="item.id" 
               class="card-item d-flex align-items-center justify-content-between p-3 bg-white border rounded-4 shadow-sm hover-shadow-lg transition-all"
               :style="{ animationDelay: `${index * 0.05}s` }">
            
            <div class="d-flex align-items-center gap-4 flex-grow-1">
              <div class="icon-box bg-gradient-primary text-white rounded-3 shadow-sm d-flex align-items-center justify-content-center">
                <i class="bi bi-tag-fill fs-5"></i>
              </div>
              <div>
                <h6 class="fw-bold text-dark mb-1 fs-5">{{ item.ten_loai }}</h6>
                <div class="d-flex gap-3 align-items-center">
                  <small class="text-muted"><i class="bi bi-hash"></i>{{ String(item.id).padStart(3, "0") }}</small>
                  <small v-if="item.mo_ta" class="text-muted border-start ps-3 text-truncate" style="max-width: 300px;">
                    {{ item.mo_ta }}
                  </small>
                </div>
              </div>
            </div>

            <div class="text-center px-4">
              <div class="form-check form-switch custom-switch">
                <input class="form-check-input shadow-sm" type="checkbox" role="switch"
                       :checked="item.is_active == 1" 
                       @change="changeStatus(item, $event.target.checked)" />
                <label class="form-check-label small fw-bold mt-1" :class="item.is_active == 1 ? 'text-success' : 'text-danger'">
                  {{ item.is_active == 1 ? "Hoạt động" : "Đã khóa" }}
                </label>
              </div>
            </div>

            <div class="d-flex gap-2">
              <button @click="openEdit(item)" class="btn-icon btn-light-primary shadow-sm" title="Sửa">
                <i class="bi bi-pencil-fill"></i>
              </button>
              <button @click="openDelete(item)" class="btn-icon btn-light-danger shadow-sm" title="Xóa">
                <i class="bi bi-trash3-fill"></i>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination Footer -->
      <div class="card-footer bg-white border-top-0 py-4 px-4 rounded-bottom-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
          <small class="text-muted fw-bold">
            Hiển thị {{ pagination.from || 0 }} - {{ pagination.to || 0 }} của tổng số {{ pagination.total || 0 }} danh mục
          </small>

          <div class="d-flex align-items-center gap-2" v-if="pagination.last_page > 1">
            <button @click="goToPage(pagination.current_page - 1)" :disabled="pagination.current_page === 1" class="btn btn-sm btn-outline-primary rounded-circle pagination-btn">
              <i class="bi bi-chevron-left"></i>
            </button>
            <button v-for="page in visiblePages" :key="page" @click="goToPage(page)" class="btn btn-sm rounded-circle pagination-btn fw-bold" :class="page === pagination.current_page ? 'btn-primary shadow' : 'btn-light text-muted'">
              {{ page }}
            </button>
            <button @click="goToPage(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page" class="btn btn-sm btn-outline-primary rounded-circle pagination-btn">
              <i class="bi bi-chevron-right"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Form (Add/Edit) -->
    <div v-if="showAdd || showEdit" class="modal-backdrop fade show" style="z-index: 1040"></div>
    <div v-if="showAdd || showEdit" class="modal fade show d-block" tabindex="-1" style="z-index: 1050">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 glass-modal">
          <div class="modal-header border-0 pt-4 px-4 pb-2">
            <h5 class="fw-bold text-primary mb-0 d-flex align-items-center gap-2">
              <div class="icon-box-sm bg-primary text-white rounded-circle"><i class="bi bi-bookmark-star"></i></div>
              {{ showAdd ? "Thêm Mới Danh Mục" : "Cập Nhật Danh Mục" }}
            </h5>
            <button @click="closeModals" type="button" class="btn-close shadow-none bg-light rounded-circle p-2"></button>
          </div>
          <div class="modal-body px-4 py-3">
            <div class="mb-4 form-floating-custom">
              <label class="form-label small fw-bold text-muted tracking-widest mb-2">TÊN LOẠI BĐS <span class="text-danger">*</span></label>
              <input v-model="formData.ten_loai" type="text" placeholder="VD: Căn hộ chung cư cao cấp" class="form-control custom-input fw-bold bg-light border-0" />
            </div>
            <div class="mb-4">
              <label class="form-label small fw-bold text-muted tracking-widest mb-2">MÔ TẢ CHI TIẾT</label>
              <textarea v-model="formData.mo_ta" rows="3" placeholder="Nhập mô tả về loại bất động sản này..." class="form-control custom-input bg-light border-0"></textarea>
            </div>
            <div class="mb-2 p-3 bg-light rounded-3 d-flex align-items-center justify-content-between">
              <div>
                <h6 class="fw-bold mb-1">Trạng thái hoạt động</h6>
                <small class="text-muted">Cho phép khách hàng nhìn thấy loại BĐS này</small>
              </div>
              <div class="form-check form-switch custom-switch fs-4 mb-0">
                <input v-model="formData.is_active" class="form-check-input cursor-pointer" type="checkbox" role="switch" />
              </div>
            </div>
          </div>
          <div class="modal-footer border-0 pb-4 px-4 pt-0 justify-content-end">
            <button @click="closeModals" class="btn btn-light rounded-pill px-4 fw-bold">Hủy bỏ</button>
            <button @click="saveData" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm d-flex align-items-center gap-2">
              <i class="bi bi-check2-circle"></i> {{ showAdd ? "Lưu thông tin" : "Lưu thay đổi" }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Delete -->
    <div v-if="showDelete" class="modal fade show d-block" tabindex="-1" style="z-index: 1050">
      <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg rounded-4 text-center p-4">
          <div class="mb-3">
            <div class="avatar-lg bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 70px; height: 70px">
              <i class="bi bi-exclamation-triangle-fill fs-1"></i>
            </div>
          </div>
          <h4 class="fw-bold mb-2">Xóa danh mục?</h4>
          <p class="text-muted mb-4 small px-2">
            Hành động này sẽ xóa danh mục <strong class="text-danger">"{{ deleteData?.ten_loai }}"</strong> vĩnh viễn. Không thể hoàn tác!
          </p>
          <div class="d-flex flex-column gap-2">
            <button @click="confirmDelete" class="btn btn-danger rounded-pill fw-bold w-100 py-2 shadow-sm">Có, xóa ngay</button>
            <button @click="closeModals" class="btn btn-light rounded-pill fw-bold w-100 py-2">Không, hủy bỏ</button>
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

      pagination: {
        current_page: 1,
        last_page: 1,
        total: 0,
        from: 0,
        to: 0,
      },

      formData: {
        id: null,
        ten_loai: "",
        mo_ta: "",
        is_active: true,
      },
      deleteData: null,
    };
  },

  computed: {
    visiblePages() {
      const pages = [];
      const maxVisible = 5;
      let start = Math.max(1, this.pagination.current_page - Math.floor(maxVisible / 2));
      let end = Math.min(this.pagination.last_page, start + maxVisible - 1);

      if (end - start < maxVisible - 1) {
        start = Math.max(1, end - maxVisible + 1);
      }

      for (let i = start; i <= end; i++) {
        pages.push(i);
      }
      return pages;
    },
  },

  mounted() {
    this.loadData(1);
  },

  methods: {
    applyFilter() {
      this.loadData(1);
    },

    goToPage(page) {
      if (page >= 1 && page <= this.pagination.last_page) {
        this.loadData(page);
      }
    },

    async loadData(page = 1) {
      this.isLoading = true;
      try {
        const params = {
          page: page,
          search: this.search,
          status: this.statusFilter
        };
        const res = await api.get('/admin/loai-bds/data', { params });
        if (res.data?.status) {
          const paginationData = res.data.data;
          this.loaiBDSList = paginationData.data || [];
          this.pagination = {
            current_page: paginationData.current_page,
            last_page: paginationData.last_page,
            total: paginationData.total,
            from: paginationData.from,
            to: paginationData.to,
          };
        }
      } catch (err) {
        console.error("Lỗi load danh sách:", err);
      } finally {
        this.isLoading = false;
      }
    },

    openAdd() {
      this.formData = { id: null, ten_loai: "", mo_ta: "", is_active: true };
      this.showAdd = true;
      document.body.classList.add("modal-open");
    },

    openEdit(item) {
      this.formData = {
        id: item.id,
        ten_loai: item.ten_loai,
        mo_ta: item.mo_ta || "",
        is_active: item.is_active == 1,
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

    async saveData() {
      if (!this.formData.ten_loai.trim()) {
        this.$toast.error("Vui lòng nhập tên loại BĐS!");
        return;
      }

      const payload = {
        ten_loai: this.formData.ten_loai,
        mo_ta: this.formData.mo_ta,
        is_active: this.formData.is_active ? 1 : 0,
      };

      try {
        if (this.showAdd) {
          await api.post('/admin/loai-bds/create', payload);
        } else {
          await api.put(`/admin/loai-bds/update/${this.formData.id}`, payload);
        }
        this.loadData(this.pagination.current_page);
        this.$toast.success(this.showAdd ? "Thêm mới thành công!" : "Cập nhật thành công!");
        this.closeModals();
      } catch (err) {
        this.$toast.error(err.response?.data?.message || "Có lỗi xảy ra khi lưu dữ liệu.");
      }
    },

    async confirmDelete() {
      if (!this.deleteData?.id) return;
      try {
        await api.delete(`/admin/loai-bds/delete/${this.deleteData.id}`);
        this.$toast.success("Xóa danh mục thành công!");
        this.loadData(this.pagination.current_page);
        this.closeModals();
      } catch (err) {
        this.$toast.error(err.response?.data?.message || "Không thể xóa danh mục này.");
      }
    },

    async changeStatus(item, isChecked) {
      const originalStatus = item.is_active;
      try {
        item.is_active = isChecked ? 1 : 0; 
        const res = await api.post('/admin/loai-bds/change-status', { 
          id: item.id, 
          is_active: isChecked ? 1 : 0 
        });
        if (!res.data?.status) throw new Error("Cập nhật thất bại");
        this.$toast.success("Cập nhật trạng thái thành công!");
      } catch (err) {
        item.is_active = originalStatus; 
        this.$toast.error("Cập nhật trạng thái thất bại");
      }
    },
  },
};
</script>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap");

.property-type-management {
  font-family: "Plus Jakarta Sans", sans-serif;
  background-color: #f4f7fe;
  min-height: 100vh;
}

.bg-gradient-primary {
  background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
}

.tracking-widest {
  letter-spacing: 1.5px;
}

.card {
  border-radius: 20px;
}

.custom-header-card {
  background: white;
}

.glass-card {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
}

.glass-modal {
  background: rgba(255, 255, 255, 0.98);
  backdrop-filter: blur(10px);
}

.icon-box {
  width: 48px;
  height: 48px;
  flex-shrink: 0;
}

.icon-box-sm {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.custom-input {
  border-radius: 12px;
  padding: 12px 16px;
  border: 1px solid #e2e8f0;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.custom-input:focus {
  border-color: #0d6efd;
  box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
}

.btn-hover-elevate {
  transition: all 0.3s ease;
}
.btn-hover-elevate:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 15px rgba(13, 110, 253, 0.3) !important;
}

.card-item {
  animation: slideInUp 0.4s cubic-bezier(0.4, 0, 0.2, 1) forwards;
  opacity: 0;
  transform: translateY(20px);
}

@keyframes slideInUp {
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.transition-all {
  transition: all 0.3s ease;
}

.hover-shadow-lg:hover {
  box-shadow: 0 10px 25px rgba(0,0,0,0.05) !important;
  transform: translateY(-2px);
}

.custom-switch .form-check-input {
  width: 3em;
  height: 1.5em;
  border-radius: 2em;
  transition: background-color 0.3s, transform 0.3s;
}

.custom-switch .form-check-input:checked {
  background-color: #10b981;
  border-color: #10b981;
}

.btn-icon {
  width: 40px;
  height: 40px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
  transition: all 0.3s ease;
  border: none;
}

.btn-light-primary {
  background: #eef2ff;
  color: #4f46e5;
}

.btn-light-danger {
  background: #fef2f2;
  color: #ef4444;
}

.btn-icon:hover {
  transform: translateY(-2px) scale(1.05);
}

.btn-light-primary:hover {
  background: #4f46e5;
  color: white;
}

.btn-light-danger:hover {
  background: #ef4444;
  color: white;
}

.pagination-btn {
  width: 36px;
  height: 36px;
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.empty-state-animated i {
  animation: float 3s ease-in-out infinite;
}

@keyframes float {
  0% { transform: translateY(0px); }
  50% { transform: translateY(-10px); }
  100% { transform: translateY(0px); }
}
</style>