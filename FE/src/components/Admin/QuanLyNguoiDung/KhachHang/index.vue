<template>
  <div class="customer-management p-4">
    <div class="row mb-4">
      <div class="col-12">
        <div class="card border-0 shadow-sm custom-header-card">
          <div
            class="card-body d-flex flex-column flex-md-row justify-content-between align-items-md-center py-3 gap-3">
            <div class="p-2">
              <h4 class="mb-0 fw-bold text-primary">
                <i class="bi bi-people-fill me-2"></i>Quản lý khách hàng
              </h4>
              <small class="text-muted">Duy trì và quản lý cơ sở dữ liệu khách hàng tiềm năng.</small>
            </div>

            <div class="d-flex justify-content-center align-items-center gap-2">
              <div class="input-group" style="max-width: 200px">
                <select v-model="statusFilter" @change="applyFilter"
                  class="form-select custom-input text-muted rounded-pill">
                  <option value="">Tất cả trạng thái</option>
                  <option value="active">Đang hoạt động</option>
                  <option value="inactive">Bị khóa</option>
                </select>
              </div>

              <div class="input-group" style="max-width: 250px">
                <input v-model="search" @input="onSearchInput" type="text"
                  class="form-control custom-input rounded-start-pill" placeholder="Tìm tên, email..." />
                <button class="btn btn-outline-secondary shadow-none rounded-end-circle" type="button">
                  <i class="bi bi-search"></i>
                </button>
              </div>

              <!-- Export Dropdown -->
              <div class="position-relative">
                <button @click="showExportMenu = !showExportMenu"
                  class="btn btn-success btn-lg text-nowrap px-4 shadow-sm fw-bold rounded-pill">
                  <i class="bi bi-download me-1"></i> Export
                  <i class="bi bi-chevron-down ms-1" style="font-size: 12px"></i>
                </button>
                <div v-if="showExportMenu"
                  class="dropdown-menu show position-absolute end-0 mt-2 shadow-sm border-0 rounded-3 p-2 z-3"
                  style="min-width: 200px">
                  <a href="#" @click.prevent="exportData('csv')"
                    class="dropdown-item rounded py-2 d-flex align-items-center">
                    <i class="bi bi-filetype-csv text-success fs-5 me-2"></i>
                    Xuất file CSV
                  </a>
                  <a href="#" @click.prevent="exportData('excel')"
                    class="dropdown-item rounded py-2 d-flex align-items-center">
                    <i class="bi bi-file-earmark-excel text-primary fs-5 me-2"></i>
                    Xuất file Excel
                  </a>
                  <a href="#" @click.prevent="exportData('pdf')"
                    class="dropdown-item rounded py-2 d-flex align-items-center">
                    <i class="bi bi-filetype-pdf text-danger fs-5 me-2"></i>
                    Xuất file PDF
                  </a>
                </div>
                <div v-if="showExportMenu" @click="showExportMenu = false"
                  class="position-fixed top-0 start-0 w-100 h-100 z-2"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row mb-4 g-3">
      <!-- Tổng khách hàng-->
      <div class="col-md-3">
        <div class="card border-0 shadow-sm">
          <div class="card-body d-flex justify-content-between align-items-center">
            <div>
              <small class="text-muted fw-bold">TỔNG KHÁCH HÀNG</small>
              <h3 class="fw-bold text-primary mb-0">
                {{ khachHangList.length.toLocaleString() }}
              </h3>
            </div>
            <i class="bi bi-people fs-1 text-primary opacity-25"></i>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <!-- Đang hoạt động -->
        <div class="card border-0 shadow-sm bg-primary text-white">
          <div class="card-body d-flex justify-content-between align-items-center">
            <div>
              <small class="opacity-75 fw-bold">ĐANG HOẠT ĐỘNG</small>
              <h3 class="fw-bold mb-0">
                {{ soDangHoatDong.toLocaleString() }}
              </h3>
            </div>
            <i class="bi bi-person-check fs-1 opacity-25"></i>
          </div>
        </div>
      </div>

      <!-- Khách hàng mới-->
      <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-warning bg-opacity-10">
          <div class="card-body d-flex justify-content-between align-items-center">
            <div>
              <small class="text-warning fw-bold">KHÁCH HÀNG MỚI</small>
              <h3 class="fw-bold text-warning mb-0">
                {{ khachHangMoi.toLocaleString() }}
              </h3>
              <!-- <small class="text-muted">Trong 7 ngày qua</small> -->
            </div>
            <i class="bi bi-person-plus fs-1 text-warning opacity-50"></i>
          </div>
        </div>
      </div>

      <!-- Khách hàng bị khóa-->
      <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-danger bg-opacity-10">
          <div class="card-body d-flex justify-content-between align-items-center">
            <div>
              <small class="text-danger fw-bold">BỊ KHÓA</small>
              <h3 class="fw-bold text-danger mb-0">
                {{ soBiKhoa.toLocaleString() }}
              </h3>
            </div>
            <i class="bi bi-person-x fs-1 text-danger opacity-50"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="card border-0 shadow-sm">
      <div class="card-body p-0">
        <div class="table-responsive custom-scrollbar">
          <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
              <tr>
                <th>Khách hàng</th>
                <th class="">Liên hệ</th>
                <th class="">Ngày tham gia</th>
                <th class="">BĐS Đã lưu</th>
                <th class="ps-5">Trạng thái</th>
                <th class="text-end pe-5">Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="isLoading">
                <td colspan="7" class="text-center py-5">
                  <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                  </div>
                  <p class="mt-2 text-muted">Đang xử lý dữ liệu...</p>
                </td>
              </tr>

              <!-- ✅ Fix: Dùng paginatedList thay vì khachHangFilter -->
              <tr v-for="v in paginatedList" :key="v.id" class="transition-all">
                <td>
                  <div class="d-flex align-items-center">
                    <div
                      class="avatar-sm me-3 border border-2 border-white bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center shadow-sm">
                      {{ getInitials(v.ten) }}
                    </div>
                    <div>
                      <div class="fw-bold text-dark">{{ v.ten }}</div>
                      <span class="badge bg-light text-dark border px-2 py-1 mt-1 small">
                        ID: KH-{{ String(v.id).padStart(2, "0") }}
                      </span>
                    </div>
                  </div>
                </td>
                <td>
                  <div class="fw-bold text-dark small">{{ v.email }}</div>
                  <small class="text-muted d-block mt-1">
                    <i class="bi bi-telephone me-1"></i>{{ v.so_dien_thoai }}
                  </small>
                </td>
                <td class="text-muted fw-medium small">
                  {{ formatDate(v.created_at) || "-" }}
                </td>
                <td>
                  <span
                    class="badge bg-light text-primary border border-primary border-opacity-25 px-3 py-2 rounded-pill">
                    {{ v.bds_da_luu || 0 }}
                  </span>
                </td>
                <td>
                  <button style="width: 150px" @click="changeStatus(v, !v.is_active)"
                    :class="v.is_active ? 'badge-active' : 'badge-inactive'"
                    class="badge px-3 py-2 rounded-pill small fw-bold border-0">
                    {{ v.is_active ? "Đang hoạt động" : "Bị khóa" }}
                  </button>
                </td>
                <td class="text-end pe-5 text-nowrap">
                  <button @click="openEdit(v)" class="btn btn-icon btn-light-primary me-2" title="Cập nhật">
                    <i class="bi bi-pencil-square"></i>
                  </button>
                  <button @click="openDelete(v)" class="btn btn-icon btn-light-danger" title="Xóa khách hàng">
                    <i class="bi bi-trash"></i>
                  </button>
                </td>
              </tr>

              <tr v-if="!isLoading && paginatedList.length === 0">
                <td colspan="7" class="text-center py-5 text-muted">
                  <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                  Không tìm thấy kết quả phù hợp
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ✅ Pagination Footer -->
      <div class="card-footer bg-white border-0 py-3 px-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
          <!-- Info -->
          <small class="text-muted fw-medium">
            {{ paginationInfo }}
          </small>

          <!-- Pagination Buttons -->
          <div class="d-flex align-items-center gap-1" v-if="totalPages > 1">
            <!-- Previous -->
            <button @click="prevPage" :disabled="currentPage === 1" class="btn btn-sm btn-light border"
              :class="{ disabled: currentPage === 1 }">
              &laquo;
            </button>

            <!-- Page Numbers -->
            <button v-for="page in visiblePages" :key="page" @click="goToPage(page)" class="btn btn-sm btn-primary"
              :class="page === currentPage
                  ? 'btn-primary'
                  : 'btn-light border text-muted'
                ">
              {{ page }}
            </button>

            <!-- Next -->
            <button @click="nextPage" :disabled="currentPage === totalPages" class="btn btn-sm btn-light border"
              :class="{ disabled: currentPage === totalPages }">
              &raquo;
            </button>
          </div>
        </div>
      </div>
    </div>

    <div v-if="showEdit || showDelete" class="modal-backdrop fade show" style="z-index: 1040"></div>

    <div v-if="showEdit" class="modal fade show d-block" tabindex="-1" style="z-index: 1050">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
          <div class="modal-header border-0 pt-4 px-4 pb-0">
            <h5 class="fw-bold text-primary mb-0">
              <i class="bi bi-person-lines-fill me-2"></i>Cập nhật thông tin
            </h5>
            <button @click="closeModals" type="button" class="btn-close shadow-none"></button>
          </div>
          <div class="modal-body px-4 py-4">
            <div class="mb-3">
              <label class="form-label small fw-bold text-muted text-uppercase tracking-widest">Họ và tên</label>
              <input v-model="edit_khach_hang.ten" type="text" class="form-control custom-input fw-bold" />
            </div>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label small fw-bold text-muted text-uppercase tracking-widest">Email</label>
                <input v-model="edit_khach_hang.email" type="email" class="form-control custom-input fw-bold" />
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-bold text-muted text-uppercase tracking-widest">Số điện thoại</label>
                <input v-model="edit_khach_hang.so_dien_thoai" type="text" class="form-control custom-input fw-bold" />
              </div>
            </div>
          </div>
          <div class="modal-footer border-0 pb-4 px-4 pt-0">
            <button @click="closeModals" class="btn btn-light rounded-pill px-4">
              Hủy bỏ
            </button>
            <button @click="capNhatKhachHang" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
              Lưu thay đổi
            </button>
          </div>
        </div>
      </div>
    </div>

    <div v-if="showDelete" class="modal fade show d-block" tabindex="-1" style="z-index: 1050">
      <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg rounded-4 text-center p-4">
          <div class="mb-3">
            <div
              class="avatar-sm bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center mx-auto"
              style="width: 60px; height: 60px">
              <i class="bi bi-exclamation-triangle fs-1"></i>
            </div>
          </div>
          <h5 class="fw-bold mb-3">Xác nhận xóa!</h5>
          <p class="text-muted mb-4 small">
            Bạn có chắc chắn muốn xóa khách hàng <br />
            <span class="text-danger fw-bold fs-6">{{
              delete_khach_hang?.ten
            }}</span>
            không?<br />
            Hành động này không thể hoàn tác.
          </p>
          <div class="d-flex justify-content-center gap-2">
            <button @click="closeModals" class="btn btn-light rounded-pill px-4">
              Hủy
            </button>
            <button @click="xoaKhachHang" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm">
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
import { createToaster } from "@meforma/vue-toaster";
const toaster = createToaster({ position: "top-right" });

export default {
  data() {
    return {
      khachHangList: [],
      isLoading: false,

      showEdit: false,
      showDelete: false,
      showExportMenu: false,

      search: "",
      statusFilter: "",
      searchTimeout: null,

      // 🔹 Phân trang
      currentPage: 1,
      itemsPerPage: 5,

      edit_khach_hang: {},
      delete_khach_hang: {},

    };
  },

  mounted() {
    this.loadKhachHang();
  },

  beforeDestroy() {
    if (this.searchTimeout) clearTimeout(this.searchTimeout);
  },

  computed: {
    // ✅ Danh sách đã phân trang
    paginatedList() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      const end = start + this.itemsPerPage;
      return this.khachHangFilter.slice(start, end);
    },

    // ✅ Tổng số trang
    totalPages() {
      return Math.ceil(this.khachHangFilter.length / this.itemsPerPage) || 1;
    },

    // ✅ Các số trang hiển thị
    visiblePages() {
      const pages = [];
      const maxVisible = 5;
      let start = Math.max(1, this.currentPage - Math.floor(maxVisible / 2));
      let end = Math.min(this.totalPages, start + maxVisible - 1);

      if (end - start < maxVisible - 1) {
        start = Math.max(1, end - maxVisible + 1);
      }

      for (let i = start; i <= end; i++) pages.push(i);
      return pages;
    },

    // ✅ Info phân trang
    paginationInfo() {
      const total = this.khachHangFilter.length;
      if (total === 0) return "Hiển thị 0 khách hàng";
      const start = (this.currentPage - 1) * this.itemsPerPage + 1;
      const end = Math.min(this.currentPage * this.itemsPerPage, total);
      return `Hiển thị ${start} - ${end} của ${total} khách hàng`;
    },

    // ✅ Filter logic (giữ nguyên)
    khachHangFilter() {
      let data = [...this.khachHangList];
      if (this.search) {
        const keyword = this.search.toLowerCase();
        data = data.filter((v) => {
          return (
            (v.ten && v.ten.toLowerCase().includes(keyword)) ||
            (v.email && v.email.toLowerCase().includes(keyword)) ||
            (v.so_dien_thoai && v.so_dien_thoai.includes(keyword))
          );
        });
      }
      if (this.statusFilter === "active") {
        data = data.filter((v) => v.is_active !== false && v.is_active !== 0);
      }
      if (this.statusFilter === "inactive") {
        data = data.filter((v) => v.is_active === false || v.is_active === 0);
      }
      return data;
    },

    soDangHoatDong() {
      return this.khachHangList.filter(
        (v) => v.is_active !== false && v.is_active !== 0
      ).length;
    },
    soBiKhoa() {
      return this.khachHangList.filter(
        (v) => v.is_active === false || v.is_active === 0
      ).length;
    },
    khachHangMoi() {
      const now = new Date();
      return this.khachHangList.filter((v) => {
        if (!v.created_at) return false;
        const created = new Date(v.created_at);
        const diff = (now - created) / (1000 * 60 * 60 * 24);
        return diff <= 7;
      }).length;
    },
  },

  watch: {
    // ✅ Reset page khi filter/search thay đổi
    search() {
      this.resetPagination();
    },
    statusFilter() {
      this.resetPagination();
    },
    // Auto về trang 1 nếu currentPage > totalPages sau khi filter
    khachHangFilter() {
      if (this.currentPage > this.totalPages) {
        this.currentPage = 1;
      }
    },
  },

  methods: {
    // Helper Functions
    formatDate(date) {
      if (!date) return "-";
      return new Date(date).toLocaleDateString("vi-VN", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
      });
    },
    getInitials(name) {
      if (!name) return "?";
      return name
        .split(" ")
        .map((part) => part[0])
        .slice(0, 2)
        .join("")
        .toUpperCase();
    },
    getToken() {
      return (
        localStorage.getItem("token") || localStorage.getItem("auth_token")
      );
    },
    closeModals() {
      this.showEdit = false;
      this.showDelete = false;
      this.showExportMenu = false;
      this.edit_khach_hang = {};
      this.delete_khach_hang = {};
      document.body.classList.remove("modal-open");
    },
    showToast(message, type = "info") {
      if (this.$toast) {
        this.$toast[type]?.(message);
      } else {
        alert(message);
      }
    },
    handleError(err, fallbackMsg) {
      const msg = err.response?.data?.message || err.message || fallbackMsg;
      this.showToast(msg, "error");
      if (err.response?.status === 401) {
        localStorage.removeItem("token");
        window.location.href = "/dang-nhap";
      }
    },

    // 🔹 Phân trang methods
    goToPage(page) {
      if (page < 1 || page > this.totalPages || page === this.currentPage)
        return;
      this.currentPage = page;
      this.$nextTick(() => {
        document
          .querySelector(".table-responsive")
          ?.scrollIntoView({ behavior: "smooth" });
      });
    },
    prevPage() {
      this.goToPage(this.currentPage - 1);
    },
    nextPage() {
      this.goToPage(this.currentPage + 1);
    },
    resetPagination() {
      this.currentPage = 1;
    },

    // API Functions
    async loadKhachHang() {
      this.isLoading = true;
      try {
        const token = this.getToken();
        if (!token) return;
        const res = await api.get('/admin/khach-hang/data');
        if (res.data?.status) {
          this.khachHangList = res.data.data?.data || res.data.data || [];
        } else {
          this.khachHangList = [];
        }
      } catch (err) {
        this.handleError(err, "Không thể tải danh sách khách hàng");
      } finally {
        this.isLoading = false;
      }
    },

    onSearchInput() {
      if (this.searchTimeout) clearTimeout(this.searchTimeout);
      this.searchTimeout = setTimeout(() => {
        this.timKiemKhachHang();
      }, 300);
    },

    async timKiemKhachHang() {
      try {
        const token = this.getToken();
        if (!token || !this.search.trim()) return;
        const res = await api.post('/admin/khach-hang/search', { keyword: this.search });
        if (res.data?.status) {
          this.khachHangList = res.data.data || [];
        }
      } catch (err) {
        this.handleError(err, "Tìm kiếm thất bại");
      }
    },

    applyFilter() {
      this.loadKhachHang();
    },

    exportData(format) {
      const token = this.getToken();
      if (!token) {
        alert("Vui lòng đăng nhập lại");
        return;
      }

      const params = new URLSearchParams({
        format,
        search: this.search || "",
        status: this.statusFilter || "",
      });

      // URL phải khớp với route backend: /api/admin/khach-hang/export
      const apiBase = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';
      const exportUrl = `${apiBase}/admin/khach-hang/export?${params}`;
      console.log("📤 Export URL:", exportUrl); // Debug

      window.open(exportUrl, "_blank");
      this.showExportMenu = false;
      this.showToast(`Đang tải file ${format.toUpperCase()}...`, "success");
    },

    async changeStatus(customer, newStatus) {
      try {
        const token = this.getToken();
        if (!token) return;
        const res = await api.post('/admin/khach-hang/change-status', { id: customer.id, is_active: newStatus ? 1 : 0 });
        if (res.data?.status) {
          customer.is_active = newStatus;
          this.showToast(newStatus ? "Đã kích hoạt" : "Đã khóa", "success");
        }
      } catch (err) {
        this.handleError(err, "Cập nhật trạng thái thất bại");
      }
    },

    openEdit(data) {
      this.edit_khach_hang = { ...data };
      this.showEdit = true;
      document.body.classList.add("modal-open");
    },

    openDelete(data) {
      this.delete_khach_hang = { ...data };
      this.showDelete = true;
      document.body.classList.add("modal-open");
    },

    async capNhatKhachHang() {
      try {
        const token = this.getToken();
        if (!token || !this.edit_khach_hang?.id) return;
        const res = await api.put(`/admin/khach-hang/${this.edit_khach_hang.id}`, {
          ten: this.edit_khach_hang.ten,
          email: this.edit_khach_hang.email,
          so_dien_thoai: this.edit_khach_hang.so_dien_thoai,
        });
        if (res.data?.status) {
          const idx = this.khachHangList.findIndex(
            (c) => c.id === this.edit_khach_hang.id
          );
          if (idx !== -1) {
            this.khachHangList[idx] = {
              ...this.khachHangList[idx],
              ...this.edit_khach_hang,
            };
          }
          this.closeModals();
          this.showToast("Cập nhật thành công!", "success");
        }
      } catch (err) {
        this.handleError(err, "Cập nhật thất bại");
      }
    },

    async xoaKhachHang() {
      if (!this.delete_khach_hang?.id) return;
      try {
        const token = this.getToken();
        if (!token) return;

        // ✅ Gửi id qua query string: /delete?id=123
        const res = await api.delete(`/admin/khach-hang/delete?id=${this.delete_khach_hang.id}`);

        if (res.data?.status) {
          this.khachHangList = this.khachHangList.filter(
            (v) => v.id !== this.delete_khach_hang.id
          );
          this.closeModals();
          this.showToast("Xóa thành công!", "success");
        }
      } catch (err) {
        this.handleError(err, "Xóa thất bại");
      }
    }
  },
};
</script>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap");
@import url("https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css");

.customer-management {
  font-family: "Inter", sans-serif;
  background-color: #f8f9fa;
  min-height: 100vh;
}

/* Card & Header */
.card {
  border-radius: 16px;
}

.custom-header-card {
  background: white;
}

/* Table Design */
.table th:nth-child(1),
.table td:nth-child(1) {
  width: 22%;
}

.table th:nth-child(2),
.table td:nth-child(2) {
  width: 22%;
}

.table th:nth-child(3),
.table td:nth-child(3) {
  width: 14%;
}

.table th:nth-child(4),
.table td:nth-child(4) {
  width: 12%;
}

.table th:nth-child(5),
.table td:nth-child(5) {
  width: 15%;
}

.table th:nth-child(6),
.table td:nth-child(6) {
  width: 15%;
}

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
  padding: 18px 15px;
  border-bottom: 1px solid #f1f1f1;
}

/* Avatar */
.avatar-sm {
  width: 40px;
  height: 40px;
  font-size: 0.9rem;
}

/* Status Badges */
.badge-active {
  background-color: #e6fcf5;
  color: #087f5b;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
}

.badge-inactive {
  background-color: #fff5f5;
  color: #c92a2a;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
}

/* Buttons */
.btn-icon {
  width: 36px;
  height: 36px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 10px;
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

/* Inputs */
.custom-input {
  border-radius: 12px;
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

/* Scrollbar */
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #dee2e6;
  border-radius: 10px;
}

/* ✅ Pagination Styles */
.pagination .page-item .page-link {
  border: 1px solid #e9ecef;
  background: white;
  color: #6c757d;
  font-weight: 500;
  transition: all 0.2s;
  min-width: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.pagination .page-item .page-link:hover:not(.active):not(:disabled) {
  background: #f8f9fa;
  border-color: #dee2e6;
  color: #0d6efd;
}

.pagination .page-item.active .page-link {
  background: #0d6efd;
  border-color: #0d6efd;
  color: white;
}

.pagination .page-item.disabled .page-link {
  opacity: 0.5;
  cursor: not-allowed;
}

.pagination .page-link i {
  font-size: 0.875rem;
}

.pagination-sm {
  gap: 4px;
}

.btn-sm.rounded-circle {
  width: 36px;
  height: 36px;
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.875rem;
}
</style>