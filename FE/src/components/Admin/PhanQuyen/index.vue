<template>
  <div class="permission-manager p-4">
    <div class="row mb-4">
      <div class="col-12">
        <div class="card border-0 shadow-sm custom-header-card">
          <div
            class="card-body d-flex justify-content-between align-items-center py-3"
          >
            <h4 class="mb-0 fw-bold text-primary">
              <i class="bi bi-shield-lock-fill me-2"></i>Quản Lý Phân Quyền
            </h4>
            <button
              class="btn btn-primary rounded-pill px-4 shadow-sm"
              data-bs-toggle="modal"
              data-bs-target="#createModal"
            >
              <i class="bi bi-plus-lg me-1"></i> Thêm chức vụ mới
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header bg-white border-0 py-3">
            <h6 class="mb-0 fw-bold">
              <i class="bi bi-people me-2"></i>Danh Sách Chức Vụ
            </h6>
          </div>
          <div class="card-body p-0">
            <div
              class="table-responsive custom-scrollbar"
              style="max-height: 600px"
            >
              <table class="table table-hover align-middle mb-0">
                <thead class="bg-light sticky-top">
                  <tr>
                    <th class="ps-3">Tên chức vụ</th>
                    <th class="text-center">Tình Trạng</th>
                    <th class="text-end pe-3">Hành động</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="(v, i) in chucVuList"
                    :key="i"
                    :class="{ 'table-active-primary': id_chuc_vu === v.id }"
                    class="transition-all cursor-pointer"
                  >
                    <td class="ps-3 fw-medium" @click="selectChucVu(v)">
                      {{ v.ten_chuc_vu }}
                    </td>
                    <td class="text-center">
                      <span
                        :class="`badge ${
                          v.tinh_trang === 1
                            ? 'bg-success w-100'
                            : 'bg-danger w-100'
                        }`"
                      >
                        {{
                          v.tinh_trang === 1 ? "Đang làm việc" : "Đã nghỉ làm"
                        }}
                      </span>
                    </td>
                    <td class="text-end pe-3 text-nowrap">
                      <button
                        @click="selectChucVu(v)"
                        class="btn btn-icon btn-light-info me-1"
                        title="Phân quyền"
                      >
                        <i class="bi bi-gear-fill"></i>
                      </button>
                      <button
                        @click="Object.assign(edit, v)"
                        class="btn btn-icon btn-light-primary me-1"
                        data-bs-toggle="modal"
                        data-bs-target="#staticBackdrop"
                      >
                        <i class="bi bi-pencil-square"></i>
                      </button>
                      <button
                        @click="Object.assign(this.delete, v)"
                        class="btn btn-icon btn-light-danger"
                        data-bs-toggle="modal"
                        data-bs-target="#staticBackdropXoa"
                      >
                        <i class="bi bi-trash3"></i>
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header bg-white border-0 py-3">
            <h6 class="mb-0 fw-bold">
              <i class="bi bi-list-check me-2"></i>Kho Chức Năng
            </h6>
            <div class="mt-2">
              <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-0"
                  ><i class="bi bi-search"></i
                ></span>
                <input
                  type="text"
                  v-model="searchChucNang"
                  class="form-control bg-light border-0"
                  placeholder="Tìm tên chức năng..."
                />
              </div>
            </div>
          </div>
          <div class="card-body p-0">
            <div
              class="table-responsive custom-scrollbar"
              style="max-height: 545px"
            >
              <table class="table table-hover align-middle mb-0">
                <tbody>
                  <tr v-for="(v, k) in filteredChucNang" :key="k">
                    <td class="ps-3">{{ v.ten_chuc_nang }}</td>
                    <td class="text-end pe-3">
                      <button
                        @click="capQuyen(v)"
                        class="btn btn-sm btn-outline-primary rounded-pill px-3 transition-all"
                      >
                        Cấp <i class="bi bi-arrow-right-short"></i>
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div
          class="card border-0 shadow-sm h-100 border-start border-4 border-primary"
        >
          <div class="card-header bg-white border-0 py-3">
            <h6 class="mb-0 fw-bold text-dark">
              Quyền của:
              <span
                v-if="quyen_dang_chon.id"
                class="badge bg-primary-soft text-primary ms-2 px-3"
              >
                {{ quyen_dang_chon.ten_chuc_vu }}
              </span>
              <span v-else class="text-muted small ms-2 fw-normal"
                >Chưa chọn</span
              >
            </h6>
          </div>
          <div class="card-body p-0">
            <div
              class="table-responsive custom-scrollbar"
              style="max-height: 600px"
            >
              <table class="table align-middle mb-0">
                <thead v-if="locMang().length > 0" class="bg-light sticky-top">
                  <tr>
                    <th class="ps-3">Chức năng</th>
                    <th class="text-end pe-3">Gỡ bỏ</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="(v, k) in locMang()"
                    :key="k"
                    class="animate__animated animate__fadeIn"
                  >
                    <td class="ps-3">
                      <div class="d-flex align-items-center">
                        <div class="dot bg-success me-2"></div>
                        {{ v.ten_chuc_nang }}
                      </div>
                    </td>
                    <td class="text-end pe-3">
                      <button
                        @click="xoaQuyen(v)"
                        class="btn btn-link text-danger p-0 text-decoration-none"
                      >
                        <i class="bi bi-x-circle-fill fs-5"></i>
                      </button>
                    </td>
                  </tr>
                  <tr v-if="locMang().length === 0">
                    <td colspan="2" class="text-center py-5">
                      <img
                        src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png"
                        style="width: 80px; opacity: 0.5"
                        class="mb-3"
                      />
                      <p class="text-muted mb-0">
                        {{
                          quyen_dang_chon.id
                            ? "Chức vụ này chưa có quyền nào"
                            : "Vui lòng chọn chức vụ bên trái"
                        }}
                      </p>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div
          class="modal-content border-0 shadow-lg"
          style="border-radius: 20px"
        >
          <div class="modal-header border-0 pb-0 pt-4 px-4">
            <h5 class="modal-title fw-bold text-primary">
              <i class="bi bi-plus-circle-fill me-2"></i>Tạo Chức Vụ Mới
            </h5>
            <button
              type="button"
              class="btn-close shadow-none"
              data-bs-dismiss="modal"
              aria-label="Close"
            ></button>
          </div>
          <div class="modal-body py-4 px-4">
            <div class="mb-3">
              <label class="form-label fw-bold small text-uppercase text-muted"
                >Tên chức vụ</label
              >
              <input
                v-model="chuc_vu.ten_chuc_vu"
                type="text"
                class="form-control form-control-lg border-2 shadow-none bg-light"
                placeholder="Ví dụ: Quản lý cửa hàng"
                style="font-size: 1rem; border-radius: 12px"
              />
            </div>

            <div>
              <label class="form-label fw-bold small text-uppercase text-muted"
                >Tình trạng</label
              >
              <select
                v-model.number="chuc_vu.tinh_trang"
                class="form-select form-select-lg border-2 shadow-none bg-light"
                style="font-size: 1rem; border-radius: 12px"
              >
                <option :value="1">🟢 Đang làm việc</option>
                <option :value="0">🔴 Đã nghỉ làm</option>
              </select>
            </div>
          </div>
          <div class="modal-footer border-0 pt-0 pb-4 px-4">
            <button
              type="button"
              class="btn btn-light rounded-pill px-4 fw-medium"
              data-bs-dismiss="modal"
            >
              Hủy bỏ
            </button>
            <button
              @click="themChucVu()"
              type="button"
              class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm"
            >
              Xác nhận tạo
            </button>
          </div>
        </div>
      </div>
    </div>

    <div
      class="modal fade"
      id="staticBackdrop"
      data-bs-backdrop="static"
      data-bs-keyboard="false"
      tabindex="-1"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-dialog-centered">
        <div
          class="modal-content border-0 shadow-lg"
          style="border-radius: 20px"
        >
          <div class="modal-header border-0 pb-0 pt-4 px-4">
            <h5 class="modal-title fw-bold text-info">
              <i class="bi bi-pencil-square me-2"></i>Chỉnh Sửa Chức Vụ
            </h5>
            <button
              type="button"
              class="btn-close shadow-none"
              data-bs-dismiss="modal"
              aria-label="Close"
            ></button>
          </div>
          <div class="modal-body py-4 px-4">
            <div class="mb-3">
              <label class="form-label fw-bold small text-uppercase text-muted"
                >Tên chức vụ</label
              >
              <input
                v-model="edit.ten_chuc_vu"
                type="text"
                class="form-control form-control-lg border-2 shadow-none bg-light"
                style="font-size: 1rem; border-radius: 12px"
              />
            </div>

            <div>
              <label class="form-label fw-bold small text-uppercase text-muted"
                >Tình trạng</label
              >
              <select
                v-model.number="edit.tinh_trang"
                class="form-select form-select-lg border-2 shadow-none bg-light"
                style="font-size: 1rem; border-radius: 12px"
              >
                <option :value="1">🟢 Đang làm việc</option>
                <option :value="0">🔴 Đã nghỉ làm</option>
              </select>
            </div>
          </div>
          <div class="modal-footer border-0 pt-0 pb-4 px-4">
            <button
              type="button"
              class="btn btn-light rounded-pill px-4 fw-medium"
              data-bs-dismiss="modal"
            >
              Hủy
            </button>
            <button
              @click="capNhatChucVu()"
              type="button"
              class="btn btn-info text-white rounded-pill px-4 fw-bold shadow-sm"
              data-bs-dismiss="modal"
            >
              Lưu thay đổi
            </button>
          </div>
        </div>
      </div>
    </div>

    <div
      class="modal fade"
      id="staticBackdropXoa"
      tabindex="-1"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-dialog-centered modal-sm">
        <div
          class="modal-content border-0 shadow-lg"
          style="border-radius: 20px"
        >
          <div class="modal-body text-center py-5 px-4">
            <div class="mb-4">
              <i
                class="bi bi-exclamation-triangle-fill text-danger"
                style="font-size: 4rem"
              ></i>
            </div>
            <h5 class="fw-bold mb-3">Xác nhận xóa?</h5>
            <p class="text-muted mb-4">
              Bạn có chắc chắn muốn xóa chức vụ
              <span class="text-dark fw-bold d-block mt-1"
                >"{{ this.delete.ten_chuc_vu }}"</span
              >
            </p>
            <div class="d-flex gap-2 justify-content-center">
              <button
                type="button"
                class="btn btn-light rounded-pill px-4"
                data-bs-dismiss="modal"
              >
                Hủy
              </button>
              <button
                @click="xoaChucVu()"
                type="button"
                class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm"
                data-bs-dismiss="modal"
              >
                Đồng ý xóa
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>


<style scoped>
/* Google Font & Background */
@import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap");
@import url("https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css");

.permission-manager {
  font-family: "Inter", sans-serif;
  background-color: #f8f9fa;
  min-height: 100vh;
}

/* Card Styling */
.card {
  border-radius: 16px;
  transition: transform 0.2s;
}

.bg-primary-soft {
  background-color: #e7f1ff;
}

/* Table Enhancements */
.table thead th {
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-weight: 700;
  color: #6c757d;
  border: none;
  padding: 12px 15px;
}

.table tbody td {
  padding: 14px 15px;
  border-bottom: 1px solid #f1f1f1;
}

.table-active-primary {
  background-color: #f0f7ff !important;
  border-left: 4px solid #0d6efd;
}

/* Custom Buttons */
.btn-icon {
  width: 32px;
  height: 32px;
  padding: 0;
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
  background: #ffe5e5;
  color: #dc3545;
  border: none;
}
.btn-light-info {
  background: #e0f7fa;
  color: #0dcaf0;
  border: none;
}

.btn-light-primary:hover {
  background: #0d6efd;
  color: white;
}
.btn-light-danger:hover {
  background: #dc3545;
  color: white;
}

/* Custom Scrollbar */
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #dee2e6;
  border-radius: 10px;
}

/* Misc */
.dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
}
.transition-all {
  transition: all 0.2s ease-in-out;
}
.cursor-pointer {
  cursor: pointer;
}
</style>
<script>
import api from "@/axios/config";
import { createToaster } from "@meforma/vue-toaster";
import * as bootstrap from "bootstrap";
const toaster = createToaster({ position: "top-right" });

export default {
  data() {
    return {
      listChucNang: [],
      listPhanQuyen: [],
      chucVuList: [],
      chuc_vu: {
        ten_chuc_vu: "",
        tinh_trang: 1,
      },
      edit: {},
      delete: {},
      quyen_dang_chon: {},
      list_chi_tiet: [],
      id_chuc_vu: null,
      searchChucNang: "",
    };
  },
  mounted() {
    // this.layDuLieuPhanQuyen();
    this.layDuLieuChucNang();
    this.loadDuLieu();
    // this.loadData();
  },
  computed: {
    // Logic lọc chức năng theo từ khóa search
    filteredChucNang() {
      if (!this.searchChucNang) return this.listChucNang;
      return this.listChucNang.filter((item) =>
        item.ten_chuc_nang
          .toLowerCase()
          .includes(this.searchChucNang.toLowerCase())
      );
    },
  },
  methods: {

    
    selectChucVu(v) {
      this.quyen_dang_chon = v;
      this.id_chuc_vu = v.id;
      this.loadData();
    },
    //thêm chức vụ
    themChucVu() {
      if (!this.chuc_vu.ten_chuc_vu?.trim()) {
        this.$toast.error("Vui lòng nhập tên chức vụ!");
        return;
      }

      // ✅ Debug: xem giá trị trước khi gửi
      console.log("Data gửi đi:", {
        ten_chuc_vu: this.chuc_vu.ten_chuc_vu.trim(),
        tinh_trang: this.chuc_vu.tinh_trang,
        type: typeof this.chuc_vu.tinh_trang,
      });

      const payload = {
        ten_chuc_vu: this.chuc_vu.ten_chuc_vu.trim(),
        tinh_trang: Number(this.chuc_vu.tinh_trang), // ✅ Convert sang number
      };

      api
        .post("/admin/chuc-vu/create", payload)
        .then((response) => {
          if (response.data.status || response.data.success) {
            this.$toast.success(
              `<div style="text-align:left"><strong>✅ Thành công!</strong><p style="margin:4px 0 0 0">${response.data.message}</p></div>`
            );
            this.loadDuLieu();
            this.chuc_vu = {
              ten_chuc_vu: "",
              tinh_trang: 1,
            };
            const modalEl = document.getElementById("createModal");
            if (modalEl) {
              const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
              modal.hide();
            }
          } else {
            this.$toast.error(
              `<div style="text-align:left"><strong>❌ Thất bại!</strong><p style="margin:4px 0 0 0">${
                response.data.message || "Có lỗi xảy ra"
              }</p></div>`
            );
          }
        })
        .catch((error) => {
          console.error("Lỗi khi thêm Chức Vụ:", error);
          if (error.response?.data?.errors) {
            const errors = Object.values(error.response.data.errors).flat();
            this.$toast.error(errors.join("<br>"));
          } else {
            this.$toast.error(
              error.response?.data?.message || "Đã xảy ra lỗi khi thêm Chức Vụ."
            );
          }
        });
    },

    // Cập nhật chức vụ
    capNhatChucVu() {
      if (!this.edit.ten_chuc_vu?.trim()) {
        this.$toast.error("Vui lòng nhập tên chức vụ!");
        return;
      }

      console.log("Update data:", {
        id: this.edit.id,
        ten_chuc_vu: this.edit.ten_chuc_vu.trim(),
        tinh_trang: this.edit.tinh_trang,
        type: typeof this.edit.tinh_trang,
      });

      const payload = {
        id: this.edit.id,
        ten_chuc_vu: this.edit.ten_chuc_vu.trim(),
        tinh_trang: Number(this.edit.tinh_trang), // Convert sang number
      };

      api
        .post(`/admin/chuc-vu/update`, payload)
        .then((response) => {
          if (response.data.status || response.data.success) {
            this.$toast.success(`Cập nhật thành công!${response.data.message}`);
            this.loadDuLieu();
            this.edit = {};
          } else {
            this.$toast.error(
              `Cập nhật thất bại! ${response.data.message || "Có lỗi xảy ra"}`
            );
          }
        })
        .catch((error) => {
          console.error("Lỗi khi cập nhật Chức Vụ:", error);
          if (error.response?.data?.errors) {
            const errors = Object.values(error.response.data.errors).flat();
            this.$toast.error(errors.join("<br>"));
          } else {
            this.$toast.error(
              error.response?.data?.message ||
                "Đã xảy ra lỗi khi cập nhật Chức Vụ."
            );
          }
        });
    },

    // Xóa chức vụ
   xoaChucVu() {
  // Dùng this.delete vì đã gán qua Object.assign(this.delete, v) ở template
  const id = this.delete?.id;
  const ten = this.delete?.ten_chuc_vu;

  if (!id) {
    this.$toast.error("Không tìm thấy thông tin chức vụ cần xóa!");
    return;
  }

  api
    .delete(`/admin/chuc-vu/delete`, {
      data: { id: id }, // Gửi id qua request body cho DELETE
    })
    .then((res) => {
      if (res.data.status || res.data.success) {
        this.$toast.success(`Xóa thành công! ${res.data.message}`);

        this.loadDuLieu(); // 🔥 giữ nguyên

        // ================== 🔥 THÊM ĐOẠN NÀY ==================
        this.quyen_dang_chon = {};
        this.id_chuc_vu = null;
        this.list_chi_tiet = [];

        // 🔥 nếu còn dữ liệu thì auto chọn lại
        setTimeout(() => {
          if (this.chucVuList.length > 0) {
            this.selectChucVu(this.chucVuList[0]);
          }
        }, 300);
        // =====================================================

        this.delete = {}; // Reset
      } else {
        this.$toast.error(
          `Xóa thất bại! ${res.data.message || "Có lỗi xảy ra"}`
        );
      }
    })
    .catch((error) => {
      console.error("Lỗi khi xóa Chức Vụ:", error);
      this.$toast.error(
        error.response?.data?.message || "Đã xảy ra lỗi khi xóa Chức Vụ."
      );
    });
},

    // Load data chức vụ để hiển thị ở cột trái
    loadDuLieu() {
      api
        .get("/admin/chuc-vu/data")
        .then((res) => {
          this.chucVuList = res.data.data;
        })
        .catch((err) => {
          console.log("Error:", err);
        });
    },

    // Load data chức năng để hiển thị ở cột giữa
    layDuLieuChucNang() {
      api
        .get("/admin/chuc-nang/data")
        .then((res) => {
          if (res.data.status == false) {
            this.$toast.error(res.data.message);
          }
          this.listChucNang = res.data.data;
        });
    },
    // Load data phân quyền cho chức vụ đã chọn ở cốt phải
    loadData() {
      if (!this.id_chuc_vu) return;
      api
        .get("/admin/phan-quyen/data/" + this.id_chuc_vu)
        .then((res) => {
          if (res.data.status == false) {
            this.$toast.error(res.data.message);
          }
          this.list_chi_tiet = res.data.data; // Gán data vào list_chi_tiet
        })
        .catch((err) => {
          console.error("Lỗi load data phân quyền:", err);
        });
    },

    // lấy data phân quyền cho chức vụ đã chọn (dùng khi click vào phân quyền để load data luôn)
    layDuLieuPhanQuyen() {
      api
        .get("/admin/phan-quyen/data/" + this.id_chuc_vu)
        .then((res) => {
          if (res.data.status == false) {
            this.$toast.error(res.data.message);
          }
          this.listPhanQuyen = res.data.data;
        });
    },

    // Lọc mảng theo chức vụ đang chọn
    locMang() {
      if (!this.quyen_dang_chon.id) {
        return [];
      }
      return this.list_chi_tiet.filter(
        (value) => value.id_chuc_vu == this.quyen_dang_chon.id
      );
    },

    // Cấp quyền cho chức năng
    capQuyen(chuc_nang) {
      if (!this.quyen_dang_chon.id) {
        this.$toast.warning("Vui lòng chọn chức vụ cần phân quyền!");
        return;
      }

      //  Kiểm tra xem chức năng này đã được cấp chưa
      const daTonTai = this.list_chi_tiet.some(
        (item) => item.id_chuc_nang == chuc_nang.id
      );

      if (daTonTai) {
        this.$toast.error(
          `<span class="text-nowrap">Chức năng <b>${chuc_nang.ten_chuc_nang}</b> đã tồn tại!</span>`
        );
        return;
      }

      var payload = {
        id_chuc_vu: this.quyen_dang_chon.id,
        id_chuc_nang: chuc_nang.id,
      };

      api
        .post("/admin/phan-quyen/chuc-vu/create", payload)
        .then((res) => {
          if (res.data.status || res.data.success) {
            this.$toast.success(
              `<span class="text-nowrap">Đã cấp quyền "<b>${chuc_nang.ten_chuc_nang}</b>" cho <b>${this.quyen_dang_chon.ten_chuc_vu}</b></span> `
            );
            this.loadData(); // Reload lại danh sách
          } else {
            // Kiểm tra lỗi trùng lặp từ backend
            if (res.data.message && res.data.message.includes("tồn tại")) {
              toaster.warning(`Chức năng đã tồn tại!${res.data.message}`);
            } else {
              toaster.error(res.data.message || "Có lỗi xảy ra!");
            }
          }
        })
        .catch((error) => {
          // Xử lý lỗi validation từ backend
          if (error.response && error.response.data) {
            const errorMsg = error.response.data.message || "";

            // Kiểm tra các loại lỗi khác nhau
            if (
              errorMsg.includes("tồn tại") ||
              errorMsg.includes("duplicate")
            ) {
              toaster.warning(
                `Chức năng đã tồn tại!<small class="d-block mt-1">${errorMsg}</small>`
              );
            } else if (error.response.data.errors) {
              const errors = Object.values(error.response.data.errors).flat();
              toaster.error(errors.join("<br>"));
            } else {
              toaster.error(errorMsg || "Đã xảy ra lỗi khi cấp quyền!");
            }
          } else {
            toaster.error("Không thể kết nối đến server!");
          }
        });
    },

    // Xóa quyền
    xoaQuyen(payload) {
      api
        .delete("/admin/phan-quyen/chuc-vu/delete", { data: { id_chuc_vu: payload.id_chuc_vu, id_chuc_nang: payload.id_chuc_nang } })
        .then((res) => {
          if (res.data.status || res.data.success) {
            toaster.success(res.data.message);
            this.loadData(); //  Reload lại danh sách
          } else {
            toaster.error(res.data.message);
          }
        });
    },
  },
};
</script>

