<template>
  <div class="broker-management p-4">
    <div class="row mb-4">
      <div class="col-12">
        <div class="card border-0 shadow-sm custom-header-card">
          <div
            class="card-body d-flex flex-column flex-md-row justify-content-between align-items-md-center py-3 gap-3"
          >
            <div class="p-2">
              <h4 class="mb-0 fw-bold text-primary">
                <i class="bi bi-person-vcard me-2"></i>Quản lý môi giới
              </h4>
              <small class="text-muted"
                >Theo dõi hiệu suất, phê duyệt danh tính và quản lý quyền lợi
                đội ngũ.</small
              >
            </div>

            <div class="d-flex justify-content-center align-items-center gap-2">
              <div class="input-group" style="max-width: 180px">
                <select
                  v-model="statusFilter"
                  class="form-select custom-input text-muted rounded-pill"
                >
                  <option value="">Tất cả trạng thái</option>
                  <option :value="true">Đã xác minh</option>
                  <option :value="false">Chờ xác minh</option>
                </select>
              </div>

              <div class="input-group" style="max-width: 180px">
                <select
                  v-model="packageFilter"
                  class="form-select custom-input text-muted rounded-pill"
                >
                  <option value="">Tất cả các gói</option>
                  <option value="VIP">Gói VIP</option>
                  <option value="Cơ bản">Gói Cơ bản</option>
                </select>
              </div>

              <div class="input-group" style="max-width: 250px">
                <input
                  v-model="search"
                  type="text"
                  class="form-control custom-input rounded-start-pill"
                  placeholder="Tìm tên, email, SĐT..."
                />
                <button
                  class="btn btn-outline-secondary shadow-none rounded-end-circle"
                  type="button"
                >
                  <i class="bi bi-search"></i>
                </button>
              </div>

              <div class="position-relative">
                <button
                  @click="showExportMenu = !showExportMenu"
                  class="btn btn-success btn-lg text-nowrap px-4 shadow-sm fw-bold rounded-pill"
                >
                  <i class="bi bi-download me-1"></i> Export
                  <i
                    class="bi bi-chevron-down ms-1"
                    style="font-size: 12px"
                  ></i>
                </button>
                <div
                  v-if="showExportMenu"
                  class="dropdown-menu show position-absolute end-0 mt-2 shadow-sm border-0 rounded-3 p-2 z-3"
                  style="min-width: 200px"
                >
                  <a
                    href="#"
                    @click.prevent="exportData('csv')"
                    class="dropdown-item rounded py-2 d-flex align-items-center"
                  >
                    <i class="bi bi-filetype-csv text-success fs-5 me-2"></i>
                    Xuất file CSV
                  </a>
                  <a
                    href="#"
                    @click.prevent="exportData('excel')"
                    class="dropdown-item rounded py-2 d-flex align-items-center"
                  >
                    <i
                      class="bi bi-file-earmark-excel text-primary fs-5 me-2"
                    ></i>
                    Xuất file Excel
                  </a>
                  <a
                    href="#"
                    @click.prevent="exportData('pdf')"
                    class="dropdown-item rounded py-2 d-flex align-items-center"
                  >
                    <i class="bi bi-filetype-pdf text-danger fs-5 me-2"></i>
                    Xuất file PDF
                  </a>
                </div>
                <div
                  v-if="showExportMenu"
                  @click="showExportMenu = false"
                  class="position-fixed top-0 start-0 w-100 h-100 z-2"
                ></div>
              </div>
              <!-- <button
                @click="openAdd"
                class="btn btn-primary btn-lg text-nowrap rounded-pill px-4 shadow-sm fw-bold"
              >
                <i class="bi bi-plus-lg me-1"></i> Thêm môi giới
              </button> -->
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row mb-4 g-3">
      <!-- TỔNG MÔI GIỚI -->
      <div class="col-md-3">
        <div class="card border-0 shadow-sm">
          <div
            class="card-body d-flex justify-content-between align-items-center"
          >
            <div>
              <small class="text-muted fw-bold">TỔNG MÔI GIỚI</small>
              <h3 class="fw-bold text-primary mb-0">
                {{ moiGioiList.length.toLocaleString() }}
              </h3>
            </div>
            <i class="bi bi-people fs-1 text-primary opacity-25"></i>
          </div>
        </div>
      </div>

      <!-- ĐÃ XÁC MINH -->
      <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-primary text-white">
          <div
            class="card-body d-flex justify-content-between align-items-center"
          >
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

      <!-- CHỜ XÁC MINH-->
      <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-danger bg-opacity-10">
          <div
            class="card-body d-flex justify-content-between align-items-center"
          >
            <div>
              <small class="text-danger fw-bold">BỊ KHÓA</small>
              <div class="d-flex align-items-end">
                <h3 class="fw-bold text-danger mb-0">
                  {{ soBiKhoa.toLocaleString() }}
                </h3>
              </div>
            </div>
            <i class="bi bi-person-x fs-1 text-danger opacity-50"></i>
          </div>
        </div>
      </div>

      <!-- MÔI GIỚI VIP-->
      <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-primary bg-opacity-10">
          <div
            class="card-body d-flex justify-content-between align-items-center"
          >
            <div>
              <small class="text-primary fw-bold">MÔI GIỚI VIP</small>
              <div class="d-flex align-items-end">
                <h3 class="fw-bold text-primary mb-0">
                  {{ soGoiVIP.toLocaleString() }}
                </h3>
                <small class="text-muted ms-3 fw-bold"
                  >Đang sử dụng gói cao cấp</small
                >
              </div>
            </div>
            <i class="bi bi-star-fill fs-1 text-primary opacity-50"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- DANH SÁCH MÔI GIỚI-->
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-body p-0">
        <div class="table-responsive custom-scrollbar">
          <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
              <tr>
                <th>Môi giới</th>
                <th>Liên hệ</th>
                <th class="text-center">Gói dịch vụ</th>
                <th class="text-center">Số tin đăng</th>
                <th class="text-center">Trạng thái</th>
                <th class="text-end pe-4">Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="isLoading">
                <td colspan="6" class="text-center py-5">
                  <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                  </div>
                  <p class="mt-2 text-muted">Đang tải dữ liệu môi giới...</p>
                </td>
              </tr>

              <tr v-if="!isLoading && moiGioiFilter.length === 0">
                <td colspan="6" class="text-center py-5 text-muted">
                  <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                  Không có dữ liệu môi giới
                </td>
              </tr>

              <tr
                v-for="(v, k) in paginatedList"
                :key="v.id"
                class="transition-all"
              >
                <td>
                  <div class="d-flex align-items-center">
                    <img
                      :src="v.avatar || defaultAvatar(v.ten)"
                      class="avatar-sm me-3 rounded-circle object-cover shadow-sm border border-2 border-white"
                      alt="Avatar"
                    />
                    <div>
                      <div class="fw-bold text-dark">{{ v.ten }}</div>
                      <div
                        class="text-muted small mt-1 d-flex align-items-center gap-2"
                      >
                        <span
                          class="badge bg-light text-dark border px-2 py-1 mt-1 small"
                          >ID: BRO-{{ String(k + 1).padStart(2, "0") }}</span
                        >
                      </div>
                    </div>
                  </div>
                </td>
                <td>
                  <div class="fw-bold text-dark small">{{ v.email }}</div>
                  <small class="text-muted d-block mt-1">
                    <i class="bi bi-telephone me-1"></i>{{ v.so_dien_thoai }}
                  </small>
                </td>
                <td class="text-center">
                  <span
                    :class="{
                      'bg-warning-subtle text-warning border-warning':
                        v.goi_tin?.ten_goi?.includes('VIP') ||
                        v.goi_tin?.ten_goi?.includes('Kim'),
                      'bg-primary-subtle text-primary border-primary':
                        v.goi_tin?.ten_goi?.includes('Vàng') ||
                        v.goi_tin?.ten_goi?.includes('Bạc'),
                      'bg-secondary-subtle text-secondary border-secondary':
                        !v.goi_tin,
                    }"
                    class="badge border px-3 py-2 rounded-pill small fw-bold mb-1"
                  >
                    {{ v.ten_goi_hien_thi }}
                  </span>
                </td>
                <td class="text-center">
                  <span
                    class="badge bg-light text-primary border border-primary border-opacity-25 px-3 py-2 rounded-pill"
                  >
                    {{ v.so_tin || 0 }}
                  </span>
                </td>
                <td class="text-center">
                  <!--  Fix: Truyền đúng boolean cho changeStatus -->
                  <button
                    @click="
                      changeStatus(
                        v,
                        !(v.is_active === true || v.is_active === 1)
                      )
                    "
                    :class="
                      v.is_active === true || v.is_active === 1
                        ? 'badge-active'
                        : 'badge-inactive'
                    "
                    class="badge px-3 py-2 rounded-pill small fw-bold border-0 cursor-pointer"
                  >
                    {{
                      v.is_active === true || v.is_active === 1
                        ? "Đang hoạt động"
                        : "Bị khóa"
                    }}
                  </button>
                </td>
                <td class="text-end pe-4 text-nowrap">
                  <button
                    @click="openEdit(v)"
                    class="btn btn-icon btn-light-primary me-2"
                    title="Cập nhật"
                  >
                    <i class="bi bi-pencil-square"></i>
                  </button>
                  <button
                    @click="openDelete(v)"
                    class="btn btn-icon btn-light-danger"
                    title="Xóa"
                  >
                    <i class="bi bi-trash"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
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
      class="alert alert-warning border-0 shadow-sm d-flex align-items-center rounded-3 p-3 mb-0"
      role="alert"
    >
      <i class="bi bi-shield-exclamation fs-3 text-warning me-3"></i>
      <div>
        <h6 class="alert-heading fw-bold mb-1 text-dark">
          Lưu ý bảo mật nền tảng
        </h6>
        <p class="mb-0 small text-muted">
          Việc cấp quyền môi giới và thao tác với dữ liệu người dùng sẽ được ghi
          log vào hệ thống. Đảm bảo tuân thủ quy trình kiểm duyệt.
        </p>
      </div>
    </div>

    <div
      v-if="showAdd || showEdit || showDelete"
      class="modal-backdrop fade show"
      style="z-index: 1040"
    ></div>

    <div
      v-if="showAdd"
      class="modal fade show d-block"
      tabindex="-1"
      style="z-index: 1050"
    >
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
          <div class="modal-header border-0 pt-4 px-4 pb-0">
            <h5 class="fw-bold text-primary mb-0">
              <i class="bi bi-person-plus-fill me-2"></i>Thêm môi giới mới
            </h5>
            <button
              @click="closeModals"
              type="button"
              class="btn-close shadow-none"
            ></button>
          </div>
          <div class="modal-body px-4 py-4">
            <div class="row g-3">
              <div class="col-12">
                <label
                  class="form-label small fw-bold text-muted text-uppercase tracking-widest"
                  >Họ và tên</label
                >
                <input
                  v-model="moi_gioi.ten"
                  type="text"
                  placeholder="Nhập tên môi giới..."
                  class="form-control custom-input fw-bold"
                />
              </div>
              <div class="col-md-6">
                <label
                  class="form-label small fw-bold text-muted text-uppercase tracking-widest"
                  >Email</label
                >
                <input
                  v-model="moi_gioi.email"
                  type="email"
                  placeholder="example@email.com"
                  class="form-control custom-input fw-bold"
                />
              </div>
              <div class="col-md-6">
                <label
                  class="form-label small fw-bold text-muted text-uppercase tracking-widest"
                  >Số điện thoại</label
                >
                <input
                  v-model="moi_gioi.so_dien_thoai"
                  type="text"
                  placeholder="09xx xxx xxx"
                  class="form-control custom-input fw-bold"
                />
              </div>
              <div class="col-12">
                <label
                  class="form-label small fw-bold text-muted text-uppercase tracking-widest"
                  >Mật khẩu</label
                >
                <input
                  v-model="moi_gioi.password"
                  type="password"
                  placeholder="••••••••"
                  class="form-control custom-input fw-bold"
                />
              </div>
            </div>
          </div>
          <div class="modal-footer border-0 pb-4 px-4 pt-0">
            <button
              @click="closeModals"
              class="btn btn-light rounded-pill px-4"
            >
              Hủy thao tác
            </button>
            <button
              @click="taoMoiGioi"
              class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm"
            >
              Lưu thông tin
            </button>
          </div>
        </div>
      </div>
    </div>

    <div
      v-if="showEdit"
      class="modal fade show d-block"
      tabindex="-1"
      style="z-index: 1050"
    >
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
          <div class="modal-header border-0 pt-4 px-4 pb-0">
            <h5 class="fw-bold text-primary mb-0">
              <i class="bi bi-pencil-square me-2"></i>Cập nhật thông tin
            </h5>
            <button
              @click="closeModals"
              type="button"
              class="btn-close shadow-none"
            ></button>
          </div>
          <div class="modal-body px-4 py-4">
            <div class="row g-3">
              <div class="col-12">
                <label
                  class="form-label small fw-bold text-muted text-uppercase tracking-widest"
                  >Họ và tên</label
                >
                <input
                  v-model="edit_moi_gioi.ten"
                  type="text"
                  class="form-control custom-input fw-bold"
                />
              </div>
              <div class="col-md-6">
                <label
                  class="form-label small fw-bold text-muted text-uppercase tracking-widest"
                  >Email</label
                >
                <input
                  v-model="edit_moi_gioi.email"
                  type="email"
                  class="form-control custom-input fw-bold"
                />
              </div>
              <div class="col-md-6">
                <label
                  class="form-label small fw-bold text-muted text-uppercase tracking-widest"
                  >Số điện thoại</label
                >
                <input
                  v-model="edit_moi_gioi.so_dien_thoai"
                  type="text"
                  class="form-control custom-input fw-bold"
                />
              </div>
            </div>
          </div>
          <div class="modal-footer border-0 pb-4 px-4 pt-0">
            <button
              @click="closeModals"
              class="btn btn-light rounded-pill px-4"
            >
              Hủy
            </button>
            <button
              @click="capNhatMoiGioi"
              class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm"
            >
              Lưu thay đổi
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
              <i class="bi bi-exclamation-triangle fs-1"></i>
            </div>
          </div>
          <h5 class="fw-bold mb-3">Xác nhận xóa!</h5>
          <p class="text-muted mb-4 small">
            Bạn có chắc chắn muốn xóa môi giới <br />
            <span class="text-danger fw-bold fs-6">{{
              delete_moi_gioi?.ten
            }}</span>
            không?<br />
            Hành động này không thể hoàn tác.
          </p>
          <div class="d-flex justify-content-center gap-2">
            <button
              @click="closeModals"
              class="btn btn-light rounded-pill px-4"
            >
              Hủy
            </button>
            <button
              @click="xoaMoiGioiConfirm"
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
import { createToaster } from "@meforma/vue-toaster";
const toaster = createToaster({ position: "top-right" });

export default {
  data() {
    return {
      moiGioiList: [],
      isLoading: false,

      // Search & Filters
      search: "",
      statusFilter: "",
      packageFilter: "",

      // Modals visibility
      showAdd: false,
      showEdit: false,
      showDelete: false,
      showExportMenu: false,

      moi_gioi: {
        ten: "",
        email: "",
        so_dien_thoai: "",
        password: "",
        avatar: "",
        mo_ta: "",
        zalo_link: "",
        is_active: true,
        so_tin_con_lai: 0,
        ngay_het_han_goi: null,
      },

      edit_moi_gioi: {},
      delete_moi_gioi: {},

      currentPage: 1,
      itemsPerPage: 5,

    };
  },

  computed: {
    //  Danh sách đã phân trang (dùng moiGioiFilter để tôn trọng filter/search)
    paginatedList() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      const end = start + this.itemsPerPage;
      return this.moiGioiFilter.slice(start, end); //  Đổi từ danhSachGoiTin → moiGioiFilter
    },

    //  Tổng số trang
    totalPages() {
      return Math.ceil(this.moiGioiFilter.length / this.itemsPerPage) || 1; //  Đổi biến
    },

    //  Các số trang hiển thị
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

    //  Info phân trang
    paginationInfo() {
      const total = this.moiGioiFilter.length; //  Đổi biến
      if (total === 0) return "Hiển thị 0 môi giới";
      const start = (this.currentPage - 1) * this.itemsPerPage + 1;
      const end = Math.min(this.currentPage * this.itemsPerPage, total);
      return `Hiển thị ${start} - ${end} của ${total} môi giới`;
    },

    //  Filter logic (giữ nguyên, chỉ sửa logic trùng)
    moiGioiFilter() {
      let data = [...this.moiGioiList];

      // Filter text search
      if (this.search) {
        const keyword = this.search.toLowerCase();
        data = data.filter(
          (v) =>
            (v.ten && v.ten.toLowerCase().includes(keyword)) ||
            (v.email && v.email.toLowerCase().includes(keyword)) ||
            (v.so_dien_thoai && v.so_dien_thoai.includes(keyword))
        );
      }

      //  Filter status - chỉ giữ 1 logic duy nhất (vì select dùng boolean true/false)
      if (this.statusFilter === true) {
        data = data.filter((v) => v.is_active === true || v.is_active === 1);
      } else if (this.statusFilter === false) {
        data = data.filter((v) => v.is_active === false || v.is_active === 0);
      }

      // Filter package
      if (this.packageFilter) {
        data = data.filter((v) => v.goi === this.packageFilter);
      }

      return data;
    },

    //  Stats cards
    soDangHoatDong() {
      return this.moiGioiList.filter(
        (v) => v.is_active === true || v.is_active === 1
      ).length;
    },
    soBiKhoa() {
      return this.moiGioiList.filter(
        (v) => v.is_active === false || v.is_active === 0
      ).length;
    },

    //  Fix: Đếm VIP case-insensitive
    soGoiVIP() {
      return this.moiGioiList.filter((v) => {
        const tenGoi = (v.ten_goi_hien_thi || "").toLowerCase();
        return (
          tenGoi.includes("vip") ||
          tenGoi.includes("kim cương") ||
          tenGoi.includes("premium")
        );
      }).length;
    },
  },

  mounted() {
    this.loadMoiGioi();
  },

  methods: {
    //  Chuyển trang
    goToPage(page) {
      if (page < 1 || page > this.totalPages || page === this.currentPage)
        return;
      this.currentPage = page;

      //  Scroll lên đầu table (dùng class có thật trong component này)
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

    //  Reset page về 1 (dùng khi có search/filter sau này)
    resetPagination() {
      this.currentPage = 1;
    },

    defaultAvatar(name) {
      if (!name)
        return `https://ui-avatars.com/api/?name=?&background=F4F7FE&color=11047A`;
      return `https://ui-avatars.com/api/?name=${name}&background=F4F7FE&color=11047A`;
    },

    openAdd() {
      this.showAdd = true;
      document.body.classList.add("modal-open");
    },

    openEdit(data) {
      this.edit_moi_gioi = { ...data };
      this.showEdit = true;
      document.body.classList.add("modal-open");
    },

    openDelete(data) {
      this.delete_moi_gioi = { ...data };
      this.showDelete = true;
      document.body.classList.add("modal-open");
    },

    closeModals() {
      this.showAdd = false;
      this.showEdit = false;
      this.showDelete = false;
      this.moi_gioi = {
        ten: "",
        email: "",
        so_dien_thoai: "",
        password: "",
        avatar: "",
        mo_ta: "",
        zalo_link: "",
      };
      this.edit_moi_gioi = {};
      this.delete_moi_gioi = {};
      document.body.classList.remove("modal-open");
    },
    getToken() {
      return (
        localStorage.getItem("token") || localStorage.getItem("auth_token")
      );
    },

    async loadMoiGioi() {
      this.isLoading = true;
      try {
        const res = await api.get("/admin/moi-gioi/data");

        if (res.data?.status) {
          this.moiGioiList = res.data.data.map((v) => ({
            ...v,
            ten_goi_hien_thi: v.goi_tin_id
              ? `Gói #${v.goi_tin_id}`
              : "Chưa mua",
            so_tin: v.so_tin_con_lai || 0,
            is_active: v.is_active,
            goi: v.ngay_het_han_goi ? "VIP" : "Cơ bản",
          }));
        } else {
          this.moiGioiList = [];
        }
      } catch (err) {
        console.error("Lỗi load:", err);
      } finally {
        this.isLoading = false;
      }
    },

    async taoMoiGioi() {
      try {
        await api.post("/admin/moi-gioi/create", this.moi_gioi);
        this.loadMoiGioi();
        this.closeModals();
      } catch (err) {
        console.error("Lỗi thêm:", err);
        alert("Lỗi thêm môi giới!");
      }
    },

    async capNhatMoiGioi() {
      try {
        await api.post("/admin/moi-gioi/update", { ...this.edit_moi_gioi });
        alert("Cập nhật thành công");
        this.loadMoiGioi();
        this.closeModals();
      } catch (err) {
        console.error("Lỗi update:", err);
        alert("Cập nhật thất bại!");
      }
    },

    // Hàm gọi API khi đã bấm xác nhận trên Modal Xóa
    async xoaMoiGioiConfirm() {
      if (!this.delete_moi_gioi?.id) return;
      try {
        await api.delete("/admin/moi-gioi/delete", { data: { id: this.delete_moi_gioi.id } });
        this.loadMoiGioi();
        this.closeModals();
      } catch (err) {
        console.error("Lỗi xóa:", err);
        alert("Xóa thất bại!");
      }
    },

    async changeStatus(customer, newStatus) {
      try {
        const token = this.getToken();
        if (!token) return;

        //  Convert boolean → 1/0 cho database
        const payloadStatus = newStatus ? 1 : 0;

        const res = await api.post(
          '/admin/moi-gioi/change-status',
          { id: customer.id, is_active: payloadStatus }
        );

        if (res.data?.status) {
          //  Cập nhật UI ngay (dùng cùng format với DB)
          customer.is_active = payloadStatus;
          toaster.success(newStatus ? "✓ Đã kích hoạt" : "✓ Đã khóa");
        }
      } catch (err) {
        console.error("❌ Change status error:", err);
        toaster.error(err.response?.data?.message || "Cập nhật thất bại");
      }
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
      const exportUrl = `${apiBase}/admin/moi-gioi/export?${params}`;
      console.log("📤 Export URL:", exportUrl); // Debug

      window.open(exportUrl, "_blank");
      this.showExportMenu = false;
      this.showToast(`Đang tải file ${format.toUpperCase()}...`, "success");
    },
  },
  watch: {
    search() {
      this.resetPagination();
    },
    statusFilter() {
      this.resetPagination();
    },
    packageFilter() {
      this.resetPagination();
    },
    // Auto update pagination info khi filtered list thay đổi
    moiGioiFilter() {
      if (this.currentPage > this.totalPages) {
        this.currentPage = 1;
      }
    },
  },
};
</script>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap");
@import url("https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css");

.broker-management {
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
  color: #e67700;
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
</style>