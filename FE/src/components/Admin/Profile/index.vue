<template>
  <div class="admin-profile-page">
    <div class="page-header">
      <h1>Hồ sơ admin</h1>
    </div>

    <div class="profile-content">
      <!-- Left Column - Profile Card -->
      <div class="profile-sidebar">
        <div class="profile-card">
          <div class="profile-avatar-wrapper">
            <img
              :src="profile.avatar || defaultAvatar"
              alt="Avatar"
              class="profile-avatar"
            />
          </div>

          <h2 class="profile-name">{{ profile.ten }}</h2>
          <p class="profile-role">{{ profile.mo_ta || "Chief Curator" }}</p>

          <div class="profile-info-list">
            <div class="info-item">
              <span class="label">MÃ ADMIN</span>
              <span class="value">AD-{{ profile.id }}</span>
            </div>
            <div class="info-item">
              <span class="label">NGÀY GIA NHẬP</span>
              <span class="value">{{ formatDate(profile.created_at) }}</span>
            </div>
            <div class="info-item">
              <span class="label">TRẠNG THÁI</span>
              <span class="value status-active">Đang hoạt động</span>
            </div>
          </div>
        </div>

        <!-- 2FA Security Card -->
        <div class="security-card">
          <div class="security-header">
            <div class="security-icon">
              <i class="bi bi-shield-lock"></i>
            </div>
            <h3>Bảo mật 2FA</h3>
          </div>
          <p class="security-desc">
            Tăng cường bảo mật cho tài khoản của bạn bằng cách yêu cầu mã xác
            minh mỗi khi đăng nhập.
          </p>
          <div class="toggle-section">
            <div class="toggle-info">
              <strong>Xác thực hai yếu tố</strong>
              <span class="toggle-status">Chưa hoạt động chức năng này</span>
            </div>
            <label class="toggle-switch">
              <input type="checkbox" v-model="twoFactorEnabled" />
              <span class="toggle-slider"></span>
            </label>
          </div>
        </div>

        <button @click="confirmLogoutAll" class="btn btn-danger">
          <i class="bi bi-shield-lock"></i>
          Đăng xuất tất cả thiết bị
        </button>
      </div>

      <!-- Right Column - Forms -->
      <div class="profile-main">
        <!-- Personal Info Card -->
        <div class="info-card">
          <div class="card-header">
            <div class="card-title">
              <span class="title-bar"></span>
              <h3>Thông tin cá nhân</h3>
            </div>
            <button class="btn-save" @click="saveProfile" :disabled="loading">
              {{ loading ? "Đang lưu..." : "Lưu thay đổi" }}
            </button>
          </div>

          <div class="card-body">
            <!-- Họ và tên -->
            <div class="form-row">
              <div class="form-group">
                <label>HỌ VÀ TÊN</label>
                <input
                  type="text"
                  v-model="profile.ten"
                  class="form-control"
                  :class="{ 'is-invalid': errors.ten }"
                  placeholder="Nhập họ và tên"
                />
                <!-- Hiển thị lỗi -->
                <div
                  v-if="errors.ten"
                  class="text-danger mt-1"
                  style="font-size: 12px"
                >
                  {{ Array.isArray(errors.ten) ? errors.ten[0] : errors.ten }}
                </div>
              </div>

              <!-- Email -->
              <div class="form-group">
                <label>EMAIL</label>
                <input
                  type="email"
                  v-model="profile.email"
                  class="form-control"
                  :class="{ 'is-invalid': errors.email }"
                  placeholder="email@example.com"
                />
                <div v-if="errors.email" class="invalid-feedback">
                  {{ errors.email[0] }}
                </div>
              </div>
            </div>

            <!-- Số điện thoại -->
            <div class="form-row">
              <div class="form-group">
                <label>SỐ ĐIỆN THOẠI</label>
                <input
                  type="tel"
                  v-model="profile.so_dien_thoai"
                  class="form-control"
                  :class="{ 'is-invalid': errors.so_dien_thoai }"
                  placeholder="+84 xxx xxx xxx"
                />
                <div v-if="errors.so_dien_thoai" class="invalid-feedback">
                  {{ errors.so_dien_thoai[0] }}
                </div>
              </div>

              <!-- Mô tả -->
              <div class="form-group">
                <label>MÔ TẢ NGẮN VỀ BẠN</label>
                <input
                  type="text"
                  v-model="profile.mo_ta"
                  class="form-control"
                  :class="{ 'is-invalid': errors.mo_ta }"
                  placeholder="Mô tả về chức vụ của bạn"
                />
                <div v-if="errors.mo_ta" class="invalid-feedback">
                  {{ errors.mo_ta[0] }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Change Password Card -->
        <div class="info-card">
          <div class="card-header">
            <div class="card-title">
              <span class="title-bar"></span>
              <h3>Thay đổi mật khẩu</h3>
            </div>
          </div>

          <div class="card-body">
            <div class="form-row">
              <div class="form-group">
                <label>MẬT KHẨU HIỆN TẠI</label>
                <div class="password-input">
                  <input
                    :type="showCurrentPassword ? 'text' : 'password'"
                    v-model="passwordForm.mat_khau_cu"
                    class="form-control"
                    placeholder="••••••••"
                  />
                  <button
                    type="button"
                    class="password-toggle"
                    @click="showCurrentPassword = !showCurrentPassword"
                  >
                    <i
                      :class="
                        showCurrentPassword ? 'bi bi-eye-slash' : 'bi bi-eye'
                      "
                    ></i>
                  </button>
                </div>
              </div>
              <div class="form-group">
                <label>MẬT KHẨU MỚI</label>
                <input
                  type="password"
                  v-model="passwordForm.mat_khau_moi"
                  class="form-control"
                  placeholder="Mật khẩu mới"
                />
              </div>
            </div>

            <div class="form-group">
              <label>XÁC NHẬN MẬT KHẨU</label>
              <input
                type="password"
                v-model="passwordForm.xac_nhan_mat_khau_moi"
                class="form-control"
                placeholder="Nhập lại mật khẩu"
              />
            </div>

            <div class="password-hint">
              <em
                >Lưu ý: Mật khẩu nên bao gồm ít nhất 8 ký tự, bao gồm cả chữ
                hoa, chữ thường, số và ký tự đặc biệt.</em
              >
            </div>

            <button class="btn-update-password" @click="changePassword">
              Cập nhật mật khẩu
            </button>
          </div>
        </div>

        <!-- Login Alert -->
        <div class="alert-card">
          <div class="alert-icon">
            <i class="bi bi-exclamation-triangle"></i>
          </div>
          <div class="alert-content">
            <h4>Cảnh báo đăng nhập</h4>
            <p>
              Lần cuối đăng nhập từ một thiết bị lạ tại TP.HCM vào lúc 14:22
              ngày hôm qua.
            </p>
          </div>
          <button class="btn-check">KIỂM TRA NGAY</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Security Alert Modal -->
  <div v-if="showLogoutAllModal" class="modal fade show d-block">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title">
            <i class="bi bi-exclamation-triangle"></i>
            Xác nhận đăng xuất tất cả
          </h5>
          <button type="button" class="btn-close" @click="closeModal"></button>
        </div>
        <div class="modal-body">
          <p><strong>Hành động này sẽ:</strong></p>
          <ul>
            <li>Đăng xuất bạn khỏi <b>TẤT CẢ</b> thiết bị đang đăng nhập</li>
            <!-- <li>Hủy tất cả token hiện tại</li> -->
            <li>Bạn sẽ cần đăng nhập lại trên mọi thiết bị</li>
          </ul>
          <p class="text-danger fw-bold">Bạn có chắc chắn muốn tiếp tục?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="closeModal">
            Hủy
          </button>
          <button
            type="button"
            class="btn text-center btn-danger"
            @click="logoutAll"
          >
            <i class="bi bi-shield-lock"></i>
            Đăng xuất tất cả
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, getCurrentInstance } from "vue";
import api from "@/axios/config";
import Swal from "sweetalert2";
import { clearAuth } from "@/js/auth";
const showLogoutAllModal = ref(false);
import { useRouter } from "vue-router";
const router = useRouter();

const profile = reactive({
  id: "",
  created_at: "",
  ten: "",
  email: "",
  so_dien_thoai: "",
  mo_ta: "",
});

const errors = ref({}); // Lưu lỗi validation
const loading = ref(false);

const passwordForm = reactive({
  mat_khau_cu: "",
  mat_khau_moi: "",
  xac_nhan_mat_khau_moi: "",
});

const logoutCurrent = async () => {
  try {
    await api.get("/admin/dang-xuat");
    clearAuth("admin");
    router.push("/admin/dang-nhap");
  } catch (error) {
    console.error("Logout error:", error);
    clearAuth("admin");
    router.push("/admin/dang-nhap");
  }
};

// Logout tất cả thiết bị
const logoutAll = async () => {
  try {
    const response = await api.post("/admin/dang-xuat-tat-ca");

    if (response.data.status === "success") {
      // ✅ XÓA TOKEN ADMIN (không ảnh hưởng role khác)
      clearAuth("admin");

      Swal.fire({
        icon: "success",
        title: "Thành công",
        text: "Đã đăng xuất từ tất cả thiết bị!",
        timer: 2000,
        showConfirmButton: false,
      });

      // ✅ REDIRECT VỀ LOGIN SAU 2 GIÂY
      setTimeout(() => {
        router.push("/admin/dang-nhap");
      }, 2000);
    }
  } catch (error) {
    console.error("Logout error:", error);

    // ✅ VẪN XÓA TOKEN NẾU CÓ LỖI
    clearAuth("admin");

    Swal.fire({
      icon: "error",
      title: "Lỗi",
      text: "Có lỗi xảy ra khi đăng xuất",
    });

    setTimeout(() => {
      router.push("/admin/dang-nhap");
    }, 2000);
  } finally {
    closeModal();
  }
};

const closeModal = () => {
  showLogoutAllModal.value = false;
};

// Confirm logout all
const confirmLogoutAll = () => {
  showLogoutAllModal.value = true;
};

const twoFactorEnabled = ref(false);
const showCurrentPassword = ref(false);
const defaultAvatar =
  "https://ui-avatars.com/api/?name=Alex+Nguyen&background=1d2a89&color=fff&size=200";

const formatDate = (date) => {
  if (!date) return "-";
  const [y, m, d] = date.split(" ")[0].split("-");
  return `${parseInt(d)} th ${parseInt(m)}, ${y}`;
};

const changePassword = async () => {
  // Validate
  if (passwordForm.mat_khau_moi !== passwordForm.xac_nhan_mat_khau_moi) {
    Swal.fire({
      icon: "error",
      title: "Lỗi!",
      text: "Mật khẩu xác nhận không khớp!",
      confirmButtonColor: "#dc3545",
    });
    return;
  }

  try {
    await api.post("/admin/doi-mat-khau", passwordForm);

    Swal.fire({
      icon: "success",
      title: "Thành công!",
      text: "Đổi mật khẩu thành công! Các thiết bị khác đã được đăng xuất.",
      timer: 2000,
      showConfirmButton: false,
    });

    passwordForm.mat_khau_cu = "";
    passwordForm.mat_khau_moi = "";
    passwordForm.xac_nhan_mat_khau_moi = "";
  } catch (error) {
    console.error("Error:", error);

    Swal.fire({
      icon: "error",
      title: "Lỗi!",
      text: error.response?.data?.message || "Mật khẩu hiện tại không đúng!",
      confirmButtonColor: "#dc3545",
    });
  }
};
// 1. Hàm lấy profile từ API
const fetchProfile = async () => {
  try {
    const response = await api.get("/admin/profile");

    if (response.data.status) {
      // Đổ dữ liệu vào form
      profile.id = response.data.data.id || "";
      profile.created_at = response.data.data.created_at || "";
      profile.ten = response.data.data.ten || "";
      profile.email = response.data.data.email || "";
      profile.so_dien_thoai = response.data.data.so_dien_thoai || "";
      profile.mo_ta = response.data.data.mo_ta || "";
    }
  } catch (error) {
    console.error("Lỗi lấy profile:", error);
    Swal.fire("Lỗi!", "Không thể tải thông tin profile!", "error");
  }
};

// 2. Hàm lưu profile
const saveProfile = async () => {
  errors.value = {};
  loading.value = true;

  try {
    await api.post("/admin/update-profile", {
      id: profile.id,
      created_at: profile.created_at,
      ten: profile.ten,
      email: profile.email,
      so_dien_thoai: profile.so_dien_thoai,
      mo_ta: profile.mo_ta,
    });

    Swal.fire("Thành công!", "Cập nhật profile thành công!", "success");

    // Reload lại data sau khi lưu thành công
    await fetchProfile();
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors;
    } else {
      Swal.fire(
        "Lỗi!",
        error.response?.data?.message || "Cập nhật thất bại!",
        "error"
      );
    }
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchProfile();
});
</script>

<style scoped>
/* Thêm vào file style.css của bạn */
/* Thêm vào style.css */
.is-invalid {
  border-color: #dc3545 !important;
}

.text-danger {
  color: #dc3545 !important;
  font-size: 0.875rem;
  margin-top: 0.25rem;
}

/* Input lỗi - viền đỏ */
.form-control.is-invalid {
  border-color: #dc3545;
  padding-right: calc(1.5em + 0.75rem);
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right calc(0.375em + 0.1875rem) center;
  background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}

.form-control.is-invalid:focus {
  border-color: #dc3545;
  box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

/* Thông báo lỗi */
.invalid-feedback {
  display: block;
  width: 100%;
  margin-top: 0.25rem;
  font-size: 0.875em;
  color: #dc3545;
}

/* Input thành công - viền xanh (optional) */
.form-control.is-valid {
  border-color: #28a745;
  padding-right: calc(1.5em + 0.75rem);
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%2328a745' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right calc(0.375em + 0.1875rem) center;
  background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}

.form-control.is-valid:focus {
  border-color: #28a745;
  box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
}

/* Valid feedback */
.valid-feedback {
  display: block;
  width: 100%;
  margin-top: 0.25rem;
  font-size: 0.875em;
  color: #28a745;
}
.profile-actions {
  display: flex;
  gap: 12px;
  margin-top: 20px;
}
.btn {
  padding: 10px 20px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  gap: 8px;
}

.btn-warning {
  background: #ffc107;
  color: #000;
}

.btn-danger {
  background: #dc3545;
  color: white;
}

.btn:hover {
  opacity: 0.9;
  transform: translateY(-1px);
}

.modal-backdrop {
  background: rgba(0, 0, 0, 0.5);
}
.admin-profile-page {
  padding: 24px;
  background: #f7f6f3;
  min-height: 100vh;
}

.page-header h1 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #0a0a4d;
  margin-bottom: 24px;
}

.profile-content {
  display: grid;
  grid-template-columns: 360px 1fr;
  gap: 24px;
}

/* Profile Sidebar */
.profile-sidebar {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.profile-card {
  background: white;
  border-radius: 16px;
  padding: 32px;
  text-align: center;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
}

.profile-avatar-wrapper {
  position: relative;
  width: 140px;
  height: 140px;
  margin: 0 auto 20px;
}

.profile-avatar {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  object-fit: cover;
  border: 4px solid #f8f9fa;
}

.edit-avatar-btn {
  position: absolute;
  bottom: 0;
  right: 0;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: #0a0a4d;
  color: white;
  border: 3px solid white;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
}

.profile-name {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1a1a1a;
  margin: 0 0 4px;
}

.profile-role {
  font-size: 0.9rem;
  color: #0d7a5f;
  margin: 0 0 24px;
  font-weight: 500;
}

.profile-info-list {
  text-align: left;
  border-top: 1px solid #f0f0f0;
  padding-top: 20px;
}

.info-item {
  display: flex;
  justify-content: space-between;
  margin-bottom: 12px;
}

.info-item .label {
  font-size: 0.75rem;
  color: #8d93a6;
  text-transform: uppercase;
  font-weight: 600;
}

.info-item .value {
  font-size: 0.9rem;
  color: #1a1a1a;
  font-weight: 600;
}

.status-active {
  background: #7fffd4;
  color: #0d7a5f;
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 0.75rem;
}

/* Security Card */
.security-card {
  background: white;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
}

.security-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 12px;
}

.security-icon {
  width: 40px;
  height: 40px;
  background: #fef3a2;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
}

.security-header h3 {
  font-size: 1rem;
  font-weight: 700;
  color: #1a1a1a;
  margin: 0;
}

.security-desc {
  font-size: 0.85rem;
  color: #6c757d;
  line-height: 1.6;
  margin-bottom: 20px;
}

.toggle-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px;
  background: #f8f9fa;
  border-radius: 12px;
}

.toggle-info strong {
  display: block;
  font-size: 0.9rem;
  color: #1a1a1a;
}

.toggle-status {
  font-size: 0.8rem;
  color: #0d7a5f;
}

.toggle-switch {
  position: relative;
  display: inline-block;
  width: 50px;
  height: 26px;
}

.toggle-switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.toggle-slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  transition: 0.4s;
  border-radius: 26px;
}

.toggle-slider:before {
  position: absolute;
  content: "";
  height: 20px;
  width: 20px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  transition: 0.4s;
  border-radius: 50%;
}

input:checked + .toggle-slider {
  background-color: #0a0a4d;
}

input:checked + .toggle-slider:before {
  transform: translateX(24px);
}

/* Main Content */
.profile-main {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.info-card {
  background: white;
  border-radius: 16px;
  padding: 28px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 28px;
}

.card-title {
  display: flex;
  align-items: center;
  gap: 12px;
}

.title-bar {
  width: 4px;
  height: 24px;
  background: #0a0a4d;
  border-radius: 2px;
}

.card-title h3 {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1a1a1a;
  margin: 0;
}

.btn-save {
  background: #0a0a4d;
  color: white;
  border: none;
  padding: 10px 24px;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-save:hover {
  background: #1a1a6d;
  transform: translateY(-1px);
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
  margin-bottom: 20px;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  font-size: 0.75rem;
  color: #6c757d;
  text-transform: uppercase;
  font-weight: 600;
  margin-bottom: 8px;
}

.form-control {
  width: 100%;
  padding: 12px 16px;
  border: 1px solid #e9ecef;
  border-radius: 8px;
  font-size: 0.95rem;
  background: #f8f9fa;
  transition: all 0.2s;
}

.form-control:focus {
  outline: none;
  border-color: #0a0a4d;
  background: white;
  box-shadow: 0 0 0 3px rgba(10, 10, 77, 0.1);
}

textarea.form-control {
  resize: vertical;
  min-height: 100px;
}

.password-input {
  position: relative;
}

.password-toggle {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  cursor: pointer;
  color: #6c757d;
  font-size: 18px;
}

.password-hint {
  font-size: 0.8rem;
  color: #6c757d;
  margin: 16px 0;
  font-style: italic;
}

.btn-update-password {
  background: #e9ecef;
  color: #495057;
  border: none;
  padding: 12px 28px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-update-password:hover {
  background: #dee2e6;
}

/* Alert Card */
.alert-card {
  background: linear-gradient(135deg, #fff9b3 0%, #fff599 100%);
  border-radius: 16px;
  padding: 24px;
  display: flex;
  align-items: center;
  gap: 20px;
  border-left: 4px solid #8b7508;
}

.alert-icon {
  width: 48px;
  height: 48px;
  background: rgba(0, 0, 0, 0.1);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: #8b7508;
}

.alert-content h4 {
  font-size: 0.95rem;
  font-weight: 700;
  color: #1a1a1a;
  margin: 0 0 4px;
}

.alert-content p {
  font-size: 0.85rem;
  color: #6c757d;
  margin: 0;
}

.btn-check {
  background: #1a1a1a;
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.85rem;
  cursor: pointer;
  margin-left: auto;
  transition: all 0.2s;
}

.btn-check:hover {
  background: #0a0a4d;
  transform: translateY(-1px);
}

/* Responsive */
@media (max-width: 1024px) {
  .profile-content {
    grid-template-columns: 1fr;
  }

  .form-row {
    grid-template-columns: 1fr;
  }

  .alert-card {
    flex-direction: column;
    text-align: center;
  }

  .btn-check {
    margin-left: 0;
  }
}
</style>