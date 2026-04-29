<template>
  <div class="property-management p-4">
    <!-- Header Section -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="card border-0 shadow-sm custom-header-card">
          <div
            class="card-body d-flex flex-column flex-md-row justify-content-between align-items-md-center py-3 gap-3"
          >
            <div>
              <h4 class="mb-0 fw-bold text-primary">
                <i class="bi bi-buildings-fill me-2"></i>Quản lý bất động sản
              </h4>
              <small class="text-muted"
                >Giám sát và phê duyệt danh mục tin đăng toàn hệ thống với độ
                chính xác cao.</small
              >
            </div>

            <div class="d-flex flex-wrap gap-2">
              <button
                @click="showAdvancedFilter = !showAdvancedFilter"
                class="btn btn-outline-secondary rounded-pill px-4 shadow-sm fw-bold d-flex align-items-center"
              >
                <i class="bi bi-funnel me-2"></i> Lọc nâng cao
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4 g-3">
      <div class="col-md-3">
        <div class="card border-0 shadow-sm">
          <div
            class="card-body d-flex justify-content-between align-items-center"
          >
            <div>
              <small class="text-muted fw-bold">TỔNG TIN ĐĂNG</small>
              <div class="d-flex align-items-baseline gap-2">
                <h3 class="fw-bold text-primary mb-0">
                  {{ statistics.total.toLocaleString() }}
                </h3>
                <small class="text-success fw-bold"
                  ><i class="bi bi-arrow-up-short"></i>12%</small
                >
              </div>
            </div>
            <i class="bi bi-building fs-1 text-primary opacity-25"></i>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-warning bg-opacity-10">
          <div
            class="card-body d-flex justify-content-between align-items-center"
          >
            <div>
              <small class="text-warning fw-bold">ĐANG CHỜ DUYỆT</small>
              <h3 class="fw-bold text-warning mb-0">
                {{ statistics.pending.toLocaleString() }}
              </h3>
              <small class="text-danger fw-bold">Cần xử lý</small>
            </div>
            <i class="bi bi-hourglass-split fs-1 text-warning opacity-50"></i>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-success bg-opacity-10">
          <div
            class="card-body d-flex justify-content-between align-items-center"
          >
            <div>
              <small class="text-success fw-bold">ĐÃ DUYỆT THÁNG NÀY</small>
              <h3 class="fw-bold text-success mb-0">
                {{ statistics.approved.toLocaleString() }}
              </h3>
              <small class="text-muted fw-medium">89% tỷ lệ</small>
            </div>
            <i class="bi bi-check-circle fs-1 text-success opacity-50"></i>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-primary text-white">
          <div
            class="card-body d-flex justify-content-between align-items-center"
          >
            <div>
              <small class="opacity-75 fw-bold">BĐS NỔI BẬT</small>
              <h3 class="fw-bold mb-0 text-white">
                {{ statistics.featured.toLocaleString() }}
              </h3>
              <small class="opacity-75 fw-medium">Gói VIP</small>
            </div>
            <i class="bi bi-star-fill fs-1 opacity-25"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- Status Quick-Filter Tabs -->
    <div class="d-flex flex-wrap gap-2 mb-4">
      <button
        v-for="tab in statusTabs"
        :key="tab.value"
        @click="setStatusTab(tab.value)"
        class="btn rounded-pill fw-semibold px-4 py-2 border"
        :class="activeStatusTab === tab.value ? tab.activeClass : 'btn-light text-secondary'"
      >
        {{ tab.label }}
        <span v-if="tab.value === '1' && statistics.pending > 0" class="badge bg-warning text-dark ms-1 rounded-pill">{{ statistics.pending }}</span>
      </button>
    </div>

    <!-- Advanced Filter Panel -->
    <div
      v-if="showAdvancedFilter"
      class="card border-0 shadow-sm mb-4 bg-white rounded-4"
    >
      <div class="card-body p-4">
        <div class="row g-3">
          <div class="col-md-3">
            <label
              class="form-label small fw-bold text-muted text-uppercase tracking-widest"
              >Loại bất động sản</label
            >
            <select
              v-model="filters.loai"
              class="form-select custom-input text-muted fw-medium"
            >
              <option value="">Tất cả</option>
              <option value="1">Căn hộ</option>
              <option value="2">Nhà phố</option>
              <option value="3">Đất nền</option>
              <option value="4">Văn phòng</option>
            </select>
          </div>

          <div class="col-md-3">
            <label
              class="form-label small fw-bold text-muted text-uppercase tracking-widest"
              >Trạng thái</label
            >
            <select
              v-model="filters.trang_thai"
              class="form-select custom-input text-muted fw-medium"
            >
              <option value="">Tất cả</option>
              <option value="1">Chờ duyệt</option>
              <option value="2">Đã duyệt</option>
              <option value="3">Từ chối</option>
              <option value="4">Đã bán</option>
            </select>
          </div>

          <div class="col-md-3">
            <label
              class="form-label small fw-bold text-muted text-uppercase tracking-widest"
              >Khoảng giá</label
            >
            <select
              v-model="filters.gia"
              class="form-select custom-input text-muted fw-medium"
            >
              <option value="">Tất cả</option>
              <option value="0-1000000000">Dưới 1 tỷ</option>
              <option value="1000000000-3000000000">1 - 3 tỷ</option>
              <option value="3000000000-5000000000">3 - 5 tỷ</option>
              <option value="5000000000-999999999999">Trên 5 tỷ</option>
            </select>
          </div>

          <div class="col-md-3">
            <label
              class="form-label small fw-bold text-muted text-uppercase tracking-widest"
              >Thời gian</label
            >
            <select
              v-model="filters.thoi_gian"
              class="form-select custom-input text-muted fw-medium"
            >
              <option value="">Tất cả</option>
              <option value="today">Hôm nay</option>
              <option value="week">Tuần này</option>
              <option value="month">Tháng này</option>
            </select>
          </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
          <button
            @click="resetFilters"
            class="btn btn-light rounded-pill px-4 fw-medium text-muted"
          >
            Đặt lại
          </button>
          <button
            @click="applyFilters"
            class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm"
          >
            Áp dụng bộ lọc
          </button>
        </div>
      </div>
    </div>

    <!-- Active Filters Indicator -->
    <div
      v-if="hasActiveFilters"
      class="alert alert-info border-0 shadow-sm rounded-3 py-2 px-3 mb-4 d-flex align-items-center gap-2"
    >
      <i class="bi bi-funnel-fill"></i>
      <small class="fw-medium">
        Đang lọc:
        <span
          v-if="filters.loai"
          class="badge bg-primary-soft text-primary me-1"
          >Loại: {{ getLoaiName(filters.loai) }}</span
        >
        <span
          v-if="filters.trang_thai"
          class="badge bg-success-soft text-success me-1"
          >Trạng thái: {{ getTrangThaiName(filters.trang_thai) }}</span
        >
        <span v-if="filters.gia" class="badge bg-warning-soft text-warning me-1"
          >Giá: {{ getGiaName(filters.gia) }}</span
        >
        <button
          @click="resetFilters"
          class="btn btn-link text-decoration-none p-0 ms-2 small"
        >
          ✕ Đặt lại
        </button>
      </small>
    </div>

    <!-- Main Data Table -->
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-body p-0">
        <div class="table-responsive custom-scrollbar">
          <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
              <tr>
                <th class="ps-4">Bất động sản</th>
                <th>Loại & Vị trí</th>
                <th>Giá niêm yết</th>
                <th>Môi giới đăng</th>
                <th class="text-center">Trạng thái</th>
                <th class="text-end pe-4">Hành động</th>
              </tr>
            </thead>

            <tbody>
              <tr v-if="properties.length === 0">
                <td colspan="6" class="text-center py-5 text-muted">
                  <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                  Chưa có dữ liệu bất động sản
                </td>
              </tr>

              <tr
                v-for="bds in properties"
                :key="bds.id"
                class="transition-all"
              >
                <td class="ps-4 py-3">
                  <div class="d-flex align-items-center gap-3">
                    <div class="position-relative">
                      <img
                        :src="getPrimaryImageUrl(bds)"
                        class="rounded-3 object-cover shadow-sm border border-2 border-white"
                        style="width: 60px; height: 60px"
                        :alt="bds.tieu_de"
                      />
                      <span
                        v-if="bds.is_noi_bat || bds.is_featured"
                        class="position-absolute top-0 start-0 translate-middle p-1 bg-warning rounded-circle d-flex align-items-center justify-content-center text-white"
                        style="width: 24px; height: 24px"
                      >
                        <i class="bi bi-star-fill" style="font-size: 12px"></i>
                      </span>
                    </div>
                    <div>
                      <p
                        class="fw-bold text-dark mb-0 text-truncate"
                        style="max-width: 250px"
                        :title="bds.tieu_de"
                      >
                        {{ bds.tieu_de }}
                      </p>
                      <span
                        class="badge bg-light text-secondary border px-2 py-1 mt-1 small"
                      >
                        ID:
                        {{
                          bds.ma_bds || "RE-" + String(bds.id).padStart(4, "0")
                        }}
                      </span>
                    </div>
                  </div>
                </td>

                <td>
                  <p class="fw-bold text-dark small mb-0">
                    {{ bds.loai?.ten_loai || "—" }}
                  </p>
                  <small
                    class="text-muted d-block mt-1 text-truncate"
                    style="max-width: 200px"
                  >
                    <i class="bi bi-geo-alt me-1"></i
                    >{{ bds.dia_chi?.dia_chi_chi_tiet || bds.dia_chi || "—" }}
                  </small>
                </td>

                <td>
                  <p class="fw-bold text-primary mb-0">
                    {{ formatPrice(bds.gia) }}
                  </p>
                  <small class="text-muted d-block mt-1">{{
                    formatPricePerSqm(bds.gia, bds.dien_tich)
                  }}</small>
                </td>

                <td>
                  <div class="d-flex align-items-center gap-2">
                    <div
                      class="avatar-sm bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm"
                      style="width: 32px; height: 32px; font-size: 0.8rem"
                    >
                      {{ getInitials(bds.moi_gioi?.ten) }}
                    </div>
                    <span class="small fw-medium text-dark">
                      {{ bds.moi_gioi?.ten || "Không có" }}
                    </span>
                  </div>
                </td>

                <td class="text-center">
                  <span
                    v-if="bds.trang_thai_id == 1"
                    class="badge-warning badge px-3 py-2 rounded-pill small fw-bold border-0"
                  >
                    Chờ duyệt
                  </span>
                  <span
                    v-else-if="bds.trang_thai_id == 2"
                    class="badge-active badge px-3 py-2 rounded-pill small fw-bold border-0"
                  >
                    Đã duyệt
                  </span>
                  <span
                    v-else-if="bds.trang_thai_id == 3"
                    class="badge-inactive badge px-3 py-2 rounded-pill small fw-bold border-0"
                  >
                    Từ chối
                  </span>
                  <span
                    v-else-if="bds.trang_thai_id == 4"
                    class="badge-secondary badge px-3 py-2 rounded-pill small fw-bold border-0"
                  >
                    Đã bán
                  </span>
                  <span
                    v-else-if="bds.trang_thai_id == 5"
                    class="badge-secondary badge px-3 py-2 rounded-pill small fw-bold border-0"
                  >
                    Cho thuê
                  </span>
                  <span
                    v-else-if="bds.trang_thai_id == 6"
                    class="badge-secondary badge px-3 py-2 rounded-pill small fw-bold border-0"
                  >
                    Hết hạn
                  </span>
                  <span
                    v-else
                    class="badge bg-dark px-3 py-2 rounded-pill small"
                  >
                    Không xác định
                  </span>
                </td>

                <td class="text-end pe-4 text-nowrap">
                  <div class="d-flex justify-content-end gap-1">
                    <button
                      @click="viewProperty(bds.id)"
                      class="btn btn-icon btn-light-secondary me-1"
                      data-bs-toggle="modal"
                      data-bs-target="#propertyModal"
                      title="Xem chi tiết"
                    >
                      <i class="bi bi-eye"></i>
                    </button>
                    <button
                      @click="id_can_xoa = bds.id"
                      class="btn btn-icon btn-light-danger"
                      title="Xóa"
                      data-bs-toggle="modal"
                      data-bs-target="#deleteModal"
                    >
                      <i class="bi bi-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pagination -->
      <div
        class="card-footer bg-white border-0 d-flex justify-content-between align-items-center py-3 px-4"
      >
        <small class="text-muted fw-medium">
          Hiển thị
          <span class="fw-bold text-dark">{{ pagination.from }}</span> -
          <span class="fw-bold text-dark">{{ pagination.to }}</span> của
          <span class="fw-bold text-dark">{{ pagination.total }}</span> kết quả
        </small>

        <nav aria-label="Page navigation" v-if="pagination.last_page > 1">
          <ul class="pagination pagination-sm mb-0">
            <li
              class="page-item"
              :class="{ disabled: pagination.current_page === 1 }"
            >
              <button
                class="page-link shadow-none"
                @click="changePage(pagination.current_page - 1)"
              >
                &laquo;
              </button>
            </li>
            <li
              v-for="page in visiblePages"
              :key="page"
              class="page-item"
              :class="{ active: page === pagination.current_page }"
            >
              <button class="page-link shadow-none" @click="changePage(page)">
                {{ page }}
              </button>
            </li>
            <li
              class="page-item"
              :class="{
                disabled: pagination.current_page === pagination.last_page,
              }"
            >
              <button
                class="page-link shadow-none"
                @click="changePage(pagination.current_page + 1)"
              >
                &raquo;
              </button>
            </li>
          </ul>
        </nav>
      </div>
    </div>

    <!-- Urgent Approvals Alert -->
    <div
      v-if="urgentApprovals > 0"
      class="alert alert-warning border-0 shadow-sm rounded-4 p-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-0"
      role="alert"
      style="background: linear-gradient(145deg, #fff3cd, #fff8e6)"
    >
      <div class="d-flex align-items-center gap-3 mb-3 mb-md-0">
        <div
          class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm"
          style="width: 48px; height: 48px"
        >
          <i class="bi bi-exclamation-triangle-fill fs-4"></i>
        </div>
        <div>
          <h6 class="alert-heading fw-bold mb-1 text-dark">
            Yêu cầu phê duyệt gấp
          </h6>
          <p class="mb-0 small text-muted">
            Có <strong class="text-danger">{{ urgentApprovals }}</strong> tin
            đăng của đối tác hạng VIP đang chờ xử lý quá 24h.
          </p>
        </div>
      </div>
      <button
        @click="handleUrgentApprovals"
        class="btn btn-warning fw-bold rounded-pill px-4 shadow-sm text-dark"
      >
        Xử lý ngay
      </button>
    </div>

    <!-- ========== MODALS ========== -->

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div
          class="modal-content border-0 shadow-lg"
          style="border-radius: 20px"
        >
          <div class="modal-header border-0 pb-0 pt-4 px-4">
            <h5 class="modal-title fw-bold text-danger">
              <i class="bi bi-x-circle-fill me-2"></i>Từ chối tin đăng
            </h5>
            <button
              type="button"
              class="btn-close shadow-none"
              data-bs-dismiss="modal"
              aria-label="Close"
            ></button>
          </div>
          <div class="modal-body px-4 pb-0">
            <p class="text-muted small mb-2">Tin đăng: <strong>{{ rejectTarget?.tieu_de || '—' }}</strong></p>
            <label class="form-label fw-semibold small">Lý do từ chối <span class="text-danger">*</span></label>
            <textarea
              v-model="rejectReason"
              class="form-control rounded-3 shadow-none"
              rows="3"
              placeholder="Nhập lý do từ chối để thông báo cho môi giới..."
            ></textarea>
            <small class="text-muted">Lý do này sẽ được gửi đến môi giới qua thông báo.</small>
          </div>
          <div class="modal-footer border-0 pt-3 pb-4 px-4">
            <button
              type="button"
              class="btn btn-light rounded-pill px-4 fw-medium"
              data-bs-dismiss="modal"
            >
              Hủy
            </button>
            <button
              @click="rejectProperty"
              type="button"
              :disabled="!rejectReason.trim()"
              class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm"
            >
              Xác nhận từ chối
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
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
              Bạn có chắc chắn muốn xóa tin đăng
              <span class="text-dark fw-bold d-block mt-1"
                >"{{ deleteTarget?.tieu_de }}"</span
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
                @click="xoaBDS()"
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

  <!-- Property Detail Modal -->
  <div
    class="modal fade"
    id="propertyModal"
    data-bs-backdrop="static"
    data-bs-keyboard="false"
    tabindex="-1"
  >
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title fw-bold text-primary">
            {{ selectedProperty?.tieu_de }}
          </h5>
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
          ></button>
        </div>

        <div class="modal-body">
          <div class="row text-center">
            <div class="col-lg-7 text-start">
              <p class="mo-ta">
                <strong>Mô tả:</strong><br />
                {{ selectedProperty?.mo_ta }}
              </p>
              <p>
                <strong>Giá:</strong> {{ formatPrice(selectedProperty?.gia) }}
              </p>
              <p>
                <strong>Diện tích:</strong> {{ selectedProperty?.dien_tich }} m²
              </p>
              <p>
                <strong>Phòng ngủ:</strong> {{ selectedProperty?.so_phong_ngu }}
              </p>
              <p>
                <strong>Phòng tắm:</strong> {{ selectedProperty?.so_phong_tam }}
              </p>
              <p>
                <strong>Loại:</strong> {{ selectedProperty?.loai?.ten_loai }}
              </p>
              <p>
                <strong>Môi giới:</strong> {{ selectedProperty?.moi_gioi?.ten }}
              </p>
              <p>
                <strong>Địa chỉ:</strong>
                {{ selectedProperty?.dia_chi?.dia_chi_chi_tiet }},
                {{ selectedProperty?.dia_chi?.quan?.ten }},
                {{ selectedProperty?.dia_chi?.tinh?.ten }}
              </p>
              <p>
                <strong>Nổi bật:</strong>
                <span v-if="selectedProperty?.is_noi_bat">Có</span>
                <span v-else>Không</span>
              </p>
              <p>
                <strong>Hạn bài đăng:</strong><br />
                <span
                  v-html="formatExpiresAt(selectedProperty?.expires_at)"
                ></span>
                <span
                  v-if="selectedProperty?.expires_at"
                  class="badge ms-2"
                  :class="getExpiresBadgeClass(selectedProperty.expires_at)"
                >
                  {{
                    isExpired(selectedProperty.expires_at)
                      ? "Hết hạn"
                      : "Active"
                  }}
                </span>
              </p>
            </div>
            <div class="col-lg-5">
              <div class="text-start mb-2">
                <strong>Ảnh đại diện:</strong>
              </div>
              <img
                v-if="getPrimaryImageUrl(selectedProperty)"
                :src="getPrimaryImageUrl(selectedProperty)"
                class="img-thumbnail rounded mb-3"
                style="
                  width: 100%;
                  height: 200px;
                  object-fit: cover;
                  cursor: pointer;
                "
                @click="openImage(getPrimaryImageUrl(selectedProperty))"
              />
              <div class="d-flex flex-wrap gap-2">
                <div
                  v-for="(img, index) in limitedImages"
                  :key="index"
                  class="position-relative"
                  @click="
                    index === 5 && remainingCount > 0
                      ? openImageAt(5)
                      : openImageAt(index)
                  "
                >
                  <img
                    :src="getImageUrl(img.url)"
                    class="img-thumbnail"
                    style="
                      width: 148px;
                      height: 80px;
                      object-fit: cover;
                      cursor: pointer;
                    "
                  />
                  <div
                    v-if="index === 5 && remainingCount > 0"
                    class="overlay-more"
                  >
                    +{{ remainingCount }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <template v-if="selectedProperty?.trang_thai_id == 1">
            <button
              @click="approveProperty(selectedProperty.id)"
              class="btn btn-success"
              data-bs-dismiss="modal"
            >
              Duyệt
            </button>
            <button
              @click="openRejectModal(selectedProperty)"
              class="btn btn-danger"
              data-bs-dismiss="modal"
            >
              Từ chối
            </button>
          </template>
          <button
            type="button"
            class="btn btn-secondary"
            data-bs-dismiss="modal"
          >
            Đóng
          </button>
        </div>
      </div>
    </div>
  </div>

  <div
    v-if="showImagePreview"
    class="image-preview-overlay d-flex justify-content-between"
    @click="showImagePreview = false"
  >
    <button class="btn btn-outline-secondary btn-lg" @click.stop="prevImage">
      ‹
    </button>
    <img :src="previewImage" class="preview-img" />
    <button class="btn btn-outline-secondary btn-lg" @click.stop="nextImage">
      ›
    </button>
  </div>
</template>

<script>
import api from "@/axios/config";
import { createToaster } from "@meforma/vue-toaster";
import * as bootstrap from "bootstrap";
import { subscribeUser, subscribeAdmin, leaveAllChannels } from '@/js/services/echo';
const toaster = createToaster({ position: "top-right" });


export default {
  name: "AdminPropertyManagement",

  data() {
    return {
      properties: [],
      id_can_xoa: "",
      searchQuery: "",
      showAdvancedFilter: false,
      urgentApprovals: 0,
      statistics: {
        total: 0,
        pending: 0,
        approved: 0,
        featured: 0,
      },
      pagination: {
        current_page: 1,
        last_page: 1,
        from: 1,
        to: 10,
        total: 0,
      },
      filters: {
        loai: "",
        trang_thai: "",
        gia: "",
        thoi_gian: "",
      },
      rejectTarget: null,
      rejectReason: "",
      deleteTarget: null,
      activeStatusTab: "",
      statusTabs: [
        { value: "", label: "Tất cả", activeClass: "btn-primary text-white" },
        { value: "1", label: "Chờ duyệt", activeClass: "btn-warning text-dark" },
        { value: "2", label: "Đã duyệt", activeClass: "btn-success text-white" },
        { value: "3", label: "Từ chối", activeClass: "btn-danger text-white" },
      ],
      selectedProperty: null,
      showModal: false,
      loadingDetail: false,
      previewImage: null,
      showImagePreview: false,
      currentIndex: 0,
    };
  },

  computed: {
    hasActiveFilters() {
      return (
        this.filters.loai ||
        this.filters.trang_thai ||
        this.filters.gia ||
        this.filters.thoi_gian
      );
    },
    visiblePages() {
      const pages = [];
      const current = this.pagination.current_page;
      const total = this.pagination.last_page;
      const start = Math.max(1, current - 2);
      const end = Math.min(total, current + 2);
      for (let i = start; i <= end; i++) {
        pages.push(i);
      }
      return pages;
    },
    galleryImages() {
      const images = this.selectedProperty?.hinhAnh || this.selectedProperty?.hinh_anh || [];
      if (!images.length) return [];
      const main = this.selectedProperty.anh_dai_dien_url;
      return images.filter((img) => {
        const url = this.getImageUrl(img.url);
        return url !== main;
      });
    },
    limitedImages() {
      return this.galleryImages.slice(0, 6);
    },
    remainingCount() {
      return Math.max(0, this.galleryImages.length - 6);
    },
    formattedMoTa() {
      if (!this.selectedProperty?.mo_ta) return "";
      return this.selectedProperty.mo_ta
        .split(". ")
        .map((s) => `<div>${s.trim()}.</div>`)
        .join("");
    },
  },

  mounted() {
    this.loadProperties();
    this.listenPropertyExpired();
  },

  beforeUnmount() {
    leaveAllChannels();
  },

  methods: {
    formatExpiresAt(expiresAt) {
      if (!expiresAt) return "Không có";
      const now = new Date();
      const expires = new Date(expiresAt);
      if (expires < now) {
        return `<span class="text-danger fw-bold"><i class="bi bi-x-circle me-1"></i>Đã hết hạn</span>`;
      }
      const diffTime = expires - now;
      const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
      if (diffDays === 0) {
        return `<span class="text-warning fw-bold"><i class="bi bi-exclamation-circle me-1"></i>Hôm nay hết hạn</span>`;
      } else if (diffDays === 1) {
        return `<span class="text-warning fw-bold"><i class="bi bi-clock me-1"></i>Còn 1 ngày</span>`;
      } else if (diffDays <= 3) {
        return `<span class="text-warning fw-bold"><i class="bi bi-clock me-1"></i>Còn ${diffDays} ngày</span>`;
      } else if (diffDays <= 7) {
        return `<span class="text-primary"><i class="bi bi-calendar-check me-1"></i>Còn ${diffDays} ngày</span>`;
      } else {
        const formatted = expires.toLocaleDateString("vi-VN", {
          day: "2-digit",
          month: "2-digit",
          year: "numeric",
        });
        return `<span class="text-muted"><i class="bi bi-calendar me-1"></i>Đến ${formatted}</span>`;
      }
    },

    isExpired(expiresAt) {
      if (!expiresAt) return false;
      return new Date(expiresAt) < new Date();
    },

    getExpiresBadgeClass(expiresAt) {
      if (!expiresAt) return "bg-secondary";
      if (this.isExpired(expiresAt)) return "bg-danger";
      const diffDays = Math.ceil(
        (new Date(expiresAt) - new Date()) / (1000 * 60 * 60 * 24)
      );
      if (diffDays <= 3) return "bg-warning text-dark";
      return "bg-success";
    },

    listenPropertyExpired() {
      const userId = this.currentUser?.id;
      
      // Subscribe generic admin channel via service
      const adminChan = subscribeAdmin(this.currentUser?.id, () => {});
      if (adminChan) {
        adminChan.listen("PropertyExpired", (e) => {
          this.handlePropertyExpired(e);
        });
      }

      // Subscribe user channel via service
      if (userId) {
        const userChan = subscribeUser(userId, () => {});
        if (userChan) {
          userChan.listen("PropertyExpired", (e) => {
            this.handlePropertyExpired(e);
          });
        }
      }
    },

    handlePropertyExpired(e) {
      const index = this.properties.findIndex((p) => p.id === e.property_id);
      if (index !== -1) {
        this.properties[index].trang_thai_id = e.trang_thai_id;
        this.properties[index].is_duyet = e.is_duyet;
        if (this.$toast) {
          this.$toast.info(
            `📦 Tin "${this.properties[index].tieu_de}" đã hết hạn`
          );
        }
      }
    },

    prevImage() {
      if (this.currentIndex > 0) {
        this.currentIndex--;
        this.previewImage = this.getImageUrl(
          this.galleryImages[this.currentIndex].url
        );
      }
    },

    openImageAt(index) {
      this.currentIndex = index;
      this.previewImage = this.getImageUrl(this.galleryImages[index].url);
      this.showImagePreview = true;
    },

    nextImage() {
      if (this.currentIndex < this.galleryImages.length - 1) {
        this.currentIndex++;
        this.previewImage = this.getImageUrl(
          this.galleryImages[this.currentIndex].url
        );
      }
    },

    async viewProperty(id) {
      try {
        const res = await api.get(`/admin/bds/${id}`);
        if (res.data.status) {
          this.selectedProperty = res.data.data;
        }
      } catch (error) {
        console.error(error);
      }
    },

    openImage(url) {
      this.previewImage = url;
      this.showImagePreview = true;
    },

    async loadProperties() {
      try {
        const apiParams = this.buildApiParams();
        const response = await api.get(`/admin/bds/data`, { params: { page: this.pagination.current_page, ...apiParams } });
        if (response.data.status) {
          const data = response.data.data.data || response.data.data;
          this.properties = data;
          this.pagination = {
            current_page: response.data.data.current_page || 1,
            last_page: response.data.data.last_page || 1,
            from: response.data.data.from || 1,
            to: response.data.data.to || data.length,
            total: response.data.data.total || data.length,
          };
          this.updateStatistics();
          this.urgentApprovals = response.data.data.urgent_count || 0;
        }
      } catch (error) {
        console.error("Lỗi tải danh sách BĐS:", error);
        toaster.error("Không thể tải danh sách bất động sản");
      }
    },

    setStatusTab(value) {
      this.activeStatusTab = value;
      this.filters.trang_thai = value;
      this.pagination.current_page = 1;
      this.loadProperties();
    },

    buildApiParams() {
      const params = {};
      if (this.filters.loai) {
        params.loai_id = Number(this.filters.loai);
      }
      if (this.filters.trang_thai) {
        params.trang_thai = Number(this.filters.trang_thai);
      }
      if (this.filters.gia) {
        const [min, max] = this.filters.gia.split("-").map(Number);
        if (!isNaN(min)) params.min_price = min;
        if (!isNaN(max) && max > 0 && max < 999999999999) {
          params.max_price = max;
        }
      }
      if (this.filters.thoi_gian) {
        params.thoi_gian = this.filters.thoi_gian;
      }
      console.log("📡 API Params:", params);
      return params;
    },

    updateStatistics() {
      this.statistics.total = this.pagination.total;
      this.statistics.pending = this.properties.filter(
        (p) => p.trang_thai_id == 1
      ).length;
      this.statistics.approved = this.properties.filter(
        (p) => p.trang_thai_id == 2
      ).length;
      this.statistics.featured = this.properties.filter(
        (p) => p.is_noi_bat || p.is_featured
      ).length;
    },

    applyFilters() {
      this.pagination.current_page = 1;
      this.loadProperties();
      this.showAdvancedFilter = false;
    },

    resetFilters() {
      this.filters = { loai: "", trang_thai: "", gia: "", thoi_gian: "" };
      this.searchQuery = "";
      this.pagination.current_page = 1;
      this.loadProperties();
    },

    changePage(page) {
      if (page < 1 || page > this.pagination.last_page) return;
      this.pagination.current_page = page;
      this.loadProperties();
      window.scrollTo({ top: 0, behavior: "smooth" });
    },

    handleUrgentApprovals() {
      this.setStatusTab("1");
    },

    async approveProperty(id) {
      if (!confirm("Bạn có chắc chắn muốn duyệt tin đăng này?")) return;
      try {
        const response = await api.post(`/admin/bds/duyet`, { id: id, is_duyet: 1 });
        if (response.data.status) {
          toaster.success("Đã duyệt tin đăng thành công!");
          this.loadProperties();
        }
      } catch (error) {
        console.error("Lỗi duyệt tin:", error.response?.data);
        toaster.error(
          error.response?.data?.message || "Đã xảy ra lỗi khi duyệt tin"
        );
      }
    },

    openRejectModal(bds) {
      this.rejectTarget = bds;
      this.rejectReason = "";

      this.$nextTick(() => {
        const modalElement = document.getElementById("rejectModal");
        if (modalElement) {
          const modal = new bootstrap.Modal(modalElement);
          modal.show();
        }
      });
    },

    async rejectProperty() {
      console.log("🔄 rejectProperty called");
      console.log("🔄 rejectTarget:", this.rejectTarget);

      if (!this.rejectTarget) {
        toaster.error("Không tìm thấy tin đăng!");
        return;
      }

      const bdsId =
        typeof this.rejectTarget === "object"
          ? this.rejectTarget.id || this.rejectTarget.bds_id
          : this.rejectTarget;

      console.log("🔄 bdsId:", bdsId);

      if (!bdsId) {
        toaster.error("Không tìm thấy ID bất động sản!");
        return;
      }

      try {
        const response = await api.post(`/admin/bds/duyet`, { id: bdsId, is_duyet: 2, ly_do: this.rejectReason });

        console.log("📥 Response:", response.data);

        if (response.data.status) {
          toaster.success("Đã từ chối tin đăng");
          this.loadProperties();
          this.rejectTarget = null;

          // ✅ Đóng modal reject
          const modalEl = document.getElementById("rejectModal");
          if (modalEl) {
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
          }
        } else {
          toaster.error(response.data.message || "Từ chối thất bại");
        }
      } catch (error) {
        console.error("❌ Lỗi từ chối:", error.response?.data);
        const message = error.response?.data?.message || "Lỗi khi từ chối";
        toaster.error(message);
      }
    },

    xoaBDS() {
      api
        .delete(`/admin/bds/delete/${this.id_can_xoa}`)
        .then((res) => {
          if (res.data.success || res.status === 200 || res.status === 201) {
            this.$toast.success(
              `<span class="text-nowrap"><b>${
                res.data.message || "Xóa thành công!"
              }</b></span>`
            );
            this.loadProperties();
          } else {
            this.$toast.error(
              `<span class="text-nowrap">Thất Bại <b>${
                res.data.message || "Có lỗi xảy ra"
              }</b></span>`
            );
          }
        })
        .catch((err) => {
          console.error("Lỗi xóa BĐS:", err);
          this.$toast.error("Đã xảy ra lỗi khi xóa tin đăng.");
        });
    },

    getImageUrl(url) {
      if (!url) return "https://via.placeholder.com/100x100?text=No+Image";
      if (url.startsWith("http")) return url;
      const base = import.meta.env.VITE_API_URL?.replace('/api','') || 'http://localhost:8000';
      return `${base}/storage/${url}`;
    },

    getPrimaryImageUrl(bds) {
      if (!bds) return "https://via.placeholder.com/100x100?text=No+Image";
      const firstImage = bds.hinhAnh?.[0]?.url || bds.hinh_anh?.[0]?.url;
      return bds.anh_dai_dien_url || this.getImageUrl(firstImage);
    },

    getGalleryImageUrl(img) {
      if (!img) return "https://via.placeholder.com/100x100?text=No+Image";
      return this.getImageUrl(img.url);
    },


    formatPrice(gia) {
      if (!gia && gia !== 0) return "Liên hệ";
      const price = Number(gia);
      if (price >= 1000000000) {
        return (price / 1000000000).toFixed(1).replace(/\.0$/, "") + " Tỷ VNĐ";
      }
      return (price / 1000000).toFixed(0) + " Triệu VNĐ";
    },

    formatPricePerSqm(gia, dienTich) {
      if (!gia || !dienTich) return "";
      const price = Number(gia);
      const area = Number(dienTich);
      if (!price || !area) return "";
      const pricePerSqm = price / area;
      if (pricePerSqm >= 1000000) {
        return "~" + (pricePerSqm / 1000000).toFixed(1) + " Triệu/m²";
      }
      return "~" + Math.round(pricePerSqm) + " Nghìn/m²";
    },

    getInitials(ten) {
      if (!ten) return "—";
      const parts = ten.split(" ");
      if (parts.length >= 2) {
        return (parts[parts.length - 1][0] + parts[0][0]).toUpperCase();
      }
      return parts[0]?.[0]?.toUpperCase() || "—";
    },

    getLoaiName(value) {
      const map = { 1: "Căn hộ", 2: "Nhà phố", 3: "Đất nền", 4: "Văn phòng" };
      return map[value] || "—";
    },

    getTrangThaiName(value) {
      const map = { 1: "Chờ duyệt", 2: "Đã duyệt", 3: "Từ chối", 4: "Đã bán" };
      return map[value] || "—";
    },

    getGiaName(value) {
      const map = {
        "0-1000000000": "Dưới 1 tỷ",
        "1000000000-3000000000": "1 - 3 tỷ",
        "3000000000-5000000000": "3 - 5 tỷ",
        "5000000000-999999999999": "Trên 5 tỷ",
      };
      return map[value] || "—";
    },
  },
};
</script>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap");
@import url("https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css");

.property-management {
  font-family: "Inter", sans-serif;
  background-color: #f8f9fa;
  min-height: 100vh;
}

.card {
  border-radius: 16px;
  transition: transform 0.2s;
}

.custom-header-card {
  background: white;
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
  padding: 15px;
  border-bottom: 1px solid #f1f1f1;
}

.badge-active {
  background-color: #e6fcf5;
  color: #087f5b;
}

.badge-inactive {
  background-color: #fff5f5;
  color: #dc3545;
}

.badge-warning {
  background-color: #fff3cd;
  color: #856404;
}

.badge-secondary {
  background-color: #e9ecef;
  color: #495057;
}

.bg-primary-soft {
  background-color: #e7f1ff;
}

.bg-success-soft {
  background-color: #d1e7dd;
}

.bg-warning-soft {
  background-color: #fff3cd;
}

.btn-icon {
  width: 34px;
  height: 34px;
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

.btn-light-secondary {
  background: #e9ecef;
  color: #6c757d;
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

.btn-light-secondary:hover {
  background: #6c757d;
  color: white;
  transform: translateY(-1px);
}

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

.pagination .page-link {
  border-radius: 8px;
  margin: 0 2px;
  border: none;
  color: #6c757d;
  font-weight: 500;
}

.pagination .page-item.active .page-link {
  background-color: #0d6efd;
  color: white;
}

.pagination .page-link:hover:not(.active) {
  background-color: #f1f1f1;
}

.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #dee2e6;
  border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}

.transition-all {
  transition: all 0.2s ease-in-out;
}

.cursor-pointer {
  cursor: pointer;
}

.object-cover {
  object-fit: cover;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 999;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.image-box img {
  width: 100%;
  border-radius: 10px;
  margin: 10px 0;
}

.gallery {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.thumb {
  width: 100px;
  height: 80px;
  object-fit: cover;
  border-radius: 6px;
  cursor: pointer;
}

.image-preview-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.8);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

.preview-img {
  max-width: 90%;
  max-height: 90%;
  border-radius: 10px;
}

.mo-ta {
  white-space: pre-line;
}
</style>