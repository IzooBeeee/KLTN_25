<template>
  <div class="property-management p-4">
    <!-- Header Section -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="card border-0 shadow-sm glass-header-card">
          <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-md-center py-4 gap-3">
            <div class="d-flex align-items-center gap-3">
              <div class="header-icon-box bg-gradient-primary text-white rounded-4 shadow-sm d-flex align-items-center justify-content-center">
                <i class="bi bi-buildings-fill fs-3"></i>
              </div>
              <div>
                <h4 class="mb-1 fw-bold text-dark">Quản lý bất động sản</h4>
                <p class="text-muted mb-0 small fw-medium tracking-wide">Giám sát và phê duyệt danh mục tin đăng toàn hệ thống</p>
              </div>
            </div>

            <div class="d-flex flex-wrap gap-2">
              <button @click="showAdvancedFilter = !showAdvancedFilter" class="btn btn-outline-primary rounded-pill px-4 shadow-sm fw-bold d-flex align-items-center btn-hover-elevate transition-all">
                <i class="bi bi-funnel-fill me-2"></i> Lọc nâng cao
                <i class="bi ms-2" :class="showAdvancedFilter ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-5 g-4">
      <div class="col-md-3">
        <div class="card border-0 shadow-sm stat-card premium-stat-card-1">
          <div class="card-body p-4 position-relative overflow-hidden">
            <div class="stat-icon-bg"><i class="bi bi-building"></i></div>
            <p class="text-white opacity-75 fw-bold mb-1 tracking-widest small">TỔNG TIN ĐĂNG</p>
            <h2 class="fw-bold text-white mb-0 display-6">{{ statistics.total.toLocaleString() }}</h2>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card border-0 shadow-sm stat-card premium-stat-card-2">
          <div class="card-body p-4 position-relative overflow-hidden">
            <div class="stat-icon-bg"><i class="bi bi-hourglass-split"></i></div>
            <p class="text-dark opacity-75 fw-bold mb-1 tracking-widest small text-uppercase">Chờ Duyệt</p>
            <h2 class="fw-bold text-dark mb-0 display-6">{{ statistics.pending.toLocaleString() }}</h2>
            <div v-if="statistics.pending > 0" class="position-absolute top-0 end-0 mt-3 me-3">
              <span class="badge bg-danger rounded-pill pulse-animation">Cần xử lý!</span>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card border-0 shadow-sm stat-card premium-stat-card-3">
          <div class="card-body p-4 position-relative overflow-hidden">
            <div class="stat-icon-bg"><i class="bi bi-check-circle-fill"></i></div>
            <p class="text-white opacity-75 fw-bold mb-1 tracking-widest small text-uppercase">Đã Duyệt</p>
            <h2 class="fw-bold text-white mb-0 display-6">{{ statistics.approved.toLocaleString() }}</h2>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card border-0 shadow-sm stat-card premium-stat-card-4">
          <div class="card-body p-4 position-relative overflow-hidden">
            <div class="stat-icon-bg"><i class="bi bi-star-fill"></i></div>
            <p class="text-white opacity-75 fw-bold mb-1 tracking-widest small text-uppercase">BĐS Nổi Bật</p>
            <h2 class="fw-bold text-white mb-0 display-6">{{ statistics.featured.toLocaleString() }}</h2>
          </div>
        </div>
      </div>
    </div>

    <!-- Status Tabs -->
    <div class="d-flex flex-wrap gap-3 mb-4">
      <button v-for="tab in statusTabs" :key="tab.value" @click="setStatusTab(tab.value)"
        class="btn rounded-pill fw-bold px-4 py-2 transition-all tab-btn"
        :class="activeStatusTab === tab.value ? 'tab-btn-active shadow-sm' : 'tab-btn-inactive'">
        {{ tab.label }}
        <span v-if="tab.value === '1' && statistics.pending > 0" class="badge bg-danger ms-2 rounded-pill">{{ statistics.pending }}</span>
      </button>
    </div>

    <!-- Advanced Filter -->
    <transition name="fade-slide">
      <div v-if="showAdvancedFilter" class="card border-0 shadow-sm mb-4 glass-card rounded-4">
        <div class="card-body p-4">
          <div class="row g-4">
            <div class="col-md-3">
              <label class="form-label small fw-bold text-muted text-uppercase tracking-widest mb-2">Loại BĐS</label>
              <select v-model="filters.loai" class="form-select custom-input text-dark fw-medium">
                <option value="">Tất cả loại</option>
                <option value="1">Căn hộ</option>
                <option value="2">Nhà phố</option>
                <option value="3">Đất nền</option>
                <option value="4">Văn phòng</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label small fw-bold text-muted text-uppercase tracking-widest mb-2">Trạng thái</label>
              <select v-model="filters.trang_thai" class="form-select custom-input text-dark fw-medium">
                <option value="">Tất cả trạng thái</option>
                <option value="1">Chờ duyệt</option>
                <option value="2">Đã duyệt</option>
                <option value="6">Từ chối</option>
                <option value="4">Đã bán</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label small fw-bold text-muted text-uppercase tracking-widest mb-2">Khoảng giá</label>
              <select v-model="filters.gia" class="form-select custom-input text-dark fw-medium">
                <option value="">Tất cả mức giá</option>
                <option value="0-1000000000">Dưới 1 tỷ</option>
                <option value="1000000000-3000000000">1 - 3 tỷ</option>
                <option value="3000000000-5000000000">3 - 5 tỷ</option>
                <option value="5000000000-999999999999">Trên 5 tỷ</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label small fw-bold text-muted text-uppercase tracking-widest mb-2">Thời gian đăng</label>
              <select v-model="filters.thoi_gian" class="form-select custom-input text-dark fw-medium">
                <option value="">Tất cả thời gian</option>
                <option value="today">Hôm nay</option>
                <option value="week">Tuần này</option>
                <option value="month">Tháng này</option>
              </select>
            </div>
          </div>
          <div class="d-flex justify-content-end gap-3 mt-4 pt-3 border-top">
            <button @click="resetFilters" class="btn btn-light rounded-pill px-5 fw-bold text-muted">Xóa bộ lọc</button>
            <button @click="applyFilters" class="btn btn-primary rounded-pill px-5 fw-bold shadow d-flex align-items-center">
              <i class="bi bi-search me-2"></i> Áp dụng
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!-- Active Filters Tags -->
    <div v-if="hasActiveFilters" class="d-flex flex-wrap gap-2 mb-4 align-items-center">
      <span class="text-muted small fw-bold me-2">ĐANG LỌC THEO:</span>
      <span v-if="filters.loai" class="filter-tag bg-primary text-white">Loại: {{ getLoaiName(filters.loai) }}</span>
      <span v-if="filters.trang_thai" class="filter-tag bg-success text-white">Trạng thái: {{ getTrangThaiName(filters.trang_thai) }}</span>
      <span v-if="filters.gia" class="filter-tag bg-warning text-dark">Giá: {{ getGiaName(filters.gia) }}</span>
      <button @click="resetFilters" class="btn btn-sm btn-link text-danger text-decoration-none fw-bold ms-2 px-0">Xóa tất cả ✕</button>
    </div>

    <!-- Main Content List -->
    <div class="card border-0 shadow-sm mb-4 glass-card">
      <div class="card-body p-3 p-md-4">
        
        <div v-if="properties.length === 0" class="text-center py-5 my-5">
          <div class="empty-state-icon mx-auto mb-4 bg-light rounded-circle d-flex align-items-center justify-content-center" style="width:100px; height:100px;">
            <i class="bi bi-inbox fs-1 text-muted"></i>
          </div>
          <h4 class="fw-bold text-dark mb-2">Chưa có dữ liệu</h4>
          <p class="text-muted">Không tìm thấy bất động sản nào khớp với yêu cầu của bạn.</p>
        </div>

        <div v-else class="property-grid">
          <div v-for="(bds, index) in properties" :key="bds.id" class="property-card rounded-4 bg-white border transition-all" :style="{ animationDelay: `${index * 0.05}s` }">
            <div class="row g-0">
              <!-- Image Section -->
              <div class="col-md-3 position-relative">
                <img :src="getPrimaryImageUrl(bds)" class="img-fluid rounded-start-4 object-cover w-100 h-100" style="min-height: 180px;" :alt="bds.tieu_de" />
                <div class="position-absolute top-0 start-0 m-2">
                  <span v-if="bds.is_noi_bat || bds.is_featured" class="badge bg-warning text-dark fw-bold rounded-pill px-3 py-2 shadow-sm d-flex align-items-center gap-1">
                    <i class="bi bi-star-fill"></i> Nổi bật
                  </span>
                </div>
                <div class="position-absolute bottom-0 start-0 m-2 w-100 pe-4">
                  <span class="badge property-status-badge w-100 shadow-sm" :class="getStatusBadgeClass(bds.trang_thai_id)">
                    {{ getStatusText(bds.trang_thai_id) }}
                  </span>
                </div>
              </div>
              
              <!-- Info Section -->
              <div class="col-md-9 p-4 d-flex flex-column justify-content-between">
                <div>
                  <div class="d-flex justify-content-between align-items-start mb-2">
                    <h5 class="fw-bold text-dark mb-0 property-title pe-3">{{ bds.tieu_de }}</h5>
                    <div class="d-flex gap-2 align-items-center shrink-0">
                      <button @click="viewProperty(bds.id)" class="btn-action bg-light text-primary hover-primary" title="Xem chi tiết">
                        <i class="bi bi-eye-fill"></i>
                      </button>
                      <button @click="id_can_xoa = bds.id" class="btn-action bg-light text-danger hover-danger" data-bs-toggle="modal" data-bs-target="#deleteModal" title="Xóa">
                        <i class="bi bi-trash3-fill"></i>
                      </button>
                    </div>
                  </div>
                  
                  <div class="d-flex flex-wrap gap-3 mb-3 text-muted small">
                    <span class="d-flex align-items-center"><i class="bi bi-hash me-1"></i>RE-{{ String(bds.id).padStart(4, "0") }}</span>
                    <span class="d-flex align-items-center"><i class="bi bi-house me-1"></i>{{ bds.loai?.ten_loai || "—" }}</span>
                    <span class="d-flex align-items-center text-truncate" style="max-width:300px;"><i class="bi bi-geo-alt me-1"></i>{{ bds.dia_chi?.dia_chi_chi_tiet || bds.dia_chi || "—" }}</span>
                  </div>
                </div>

                <div class="row align-items-end mt-2 pt-3 border-top border-light">
                  <div class="col-md-5">
                    <p class="text-muted small fw-bold mb-1 tracking-widest text-uppercase">Mức giá</p>
                    <h4 class="fw-bold text-primary mb-0">{{ formatPrice(bds.gia) }}</h4>
                    <small class="text-muted fw-medium">{{ formatPricePerSqm(bds.gia, bds.dien_tich) }}</small>
                  </div>
                  <div class="col-md-4">
                    <p class="text-muted small fw-bold mb-1 tracking-widest text-uppercase">Người đăng</p>
                    <div class="d-flex align-items-center gap-2">
                      <div class="avatar-sm bg-gradient-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm">
                        {{ getInitials(bds.moi_gioi?.ten) }}
                      </div>
                      <span class="fw-bold text-dark">{{ bds.moi_gioi?.ten || "—" }}</span>
                    </div>
                  </div>
                  <div class="col-md-3 text-end" v-if="bds.trang_thai_id == 1">
                     <div class="d-flex gap-2 justify-content-end">
                       <button @click="approveProperty(bds.id)" class="btn btn-success fw-bold px-3 rounded-pill shadow-sm text-white" title="Duyệt ngay">
                         <i class="bi bi-check-circle-fill me-1"></i>Duyệt
                       </button>
                       <button @click="openRejectModal(bds)" class="btn btn-outline-danger fw-bold px-3 rounded-pill" title="Từ chối">
                         <i class="bi bi-x-circle-fill me-1"></i>Từ chối
                       </button>
                     </div>
                  </div>
                  <div class="col-md-3 text-end" v-else>
                    <button @click="viewProperty(bds.id)" class="btn btn-outline-primary fw-bold px-3 rounded-pill" title="Xem chi tiết">
                      <i class="bi bi-eye-fill me-1"></i>Chi tiết
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div class="card-footer bg-white border-top-0 py-4 px-4 rounded-bottom-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
          <small class="text-muted fw-bold">
            Hiển thị <span class="text-primary">{{ pagination.from || 0 }}</span> - <span class="text-primary">{{ pagination.to || 0 }}</span> của tổng số <span class="text-dark">{{ pagination.total || 0 }}</span> tin đăng
          </small>

          <div class="d-flex align-items-center gap-2" v-if="pagination.last_page > 1">
            <button @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page === 1" class="btn btn-sm btn-outline-primary rounded-circle pagination-btn">
              <i class="bi bi-chevron-left"></i>
            </button>
            <button v-for="page in visiblePages" :key="page" @click="changePage(page)" class="btn btn-sm rounded-circle pagination-btn fw-bold" :class="page === pagination.current_page ? 'btn-primary shadow' : 'btn-light text-muted'">
              {{ page }}
            </button>
            <button @click="changePage(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page" class="btn btn-sm btn-outline-primary rounded-circle pagination-btn">
              <i class="bi bi-chevron-right"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ========== MODALS ========== -->

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg glass-modal">
          <div class="modal-header border-0 pb-0 pt-4 px-4">
            <h5 class="modal-title fw-bold text-danger d-flex align-items-center gap-2">
              <div class="icon-box-sm bg-danger text-white rounded-circle"><i class="bi bi-x-lg"></i></div>
              Từ chối tin đăng
            </h5>
            <button type="button" class="btn-close shadow-none bg-light rounded-circle p-2" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body px-4 pb-0 pt-4">
            <div class="p-3 bg-light rounded-3 mb-4 border-start border-danger border-4">
               <p class="text-dark small mb-0">Bạn đang từ chối tin: <strong>{{ rejectTarget?.tieu_de || '—' }}</strong></p>
            </div>
            <label class="form-label fw-bold small text-muted tracking-widest text-uppercase">Lý do từ chối <span class="text-danger">*</span></label>
            <textarea v-model="rejectReason" class="form-control custom-input bg-light border-0 shadow-none" rows="4" placeholder="Nhập lý do từ chối để thông báo cho môi giới (VD: Hình ảnh không rõ nét, sai giá...)"></textarea>
          </div>
          <div class="modal-footer border-0 pt-4 pb-4 px-4">
            <button type="button" class="btn btn-light rounded-pill px-4 fw-bold w-100 w-md-auto mb-2 mb-md-0" data-bs-dismiss="modal">Hủy bỏ</button>
            <button @click="rejectProperty" type="button" :disabled="!rejectReason.trim()" class="btn btn-danger rounded-pill px-4 fw-bold shadow w-100 w-md-auto d-flex align-items-center justify-content-center gap-2">
              <i class="bi bi-send-fill"></i> Xác nhận từ chối
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg rounded-4 text-center p-4 glass-modal">
          <div class="mb-3 mt-2">
            <div class="avatar-lg bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 80px; height: 80px">
              <i class="bi bi-trash3-fill" style="font-size: 2.5rem;"></i>
            </div>
          </div>
          <h4 class="fw-bold mb-3">Xóa tin đăng?</h4>
          <p class="text-muted mb-4 px-2">Hành động này sẽ xóa tin đăng vĩnh viễn khỏi hệ thống. Không thể khôi phục!</p>
          <div class="d-flex flex-column gap-2">
            <button @click="xoaBDS()" type="button" class="btn btn-danger rounded-pill fw-bold w-100 py-2 shadow-sm" data-bs-dismiss="modal">Đồng ý xóa</button>
            <button type="button" class="btn btn-light rounded-pill fw-bold w-100 py-2" data-bs-dismiss="modal">Hủy bỏ</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Property Detail Modal -->
    <div class="modal fade" id="propertyModal" tabindex="-1">
      <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
          <div class="modal-header bg-light border-0 py-3 px-4">
            <h5 class="modal-title fw-bold text-dark property-title pe-4"><i class="bi bi-file-earmark-richtext-fill text-primary me-2"></i> {{ selectedProperty?.tieu_de }}</h5>
            <button type="button" class="btn-close shadow-none bg-white rounded-circle p-2" data-bs-dismiss="modal"></button>
          </div>
          
          <div class="modal-body p-0">
             <div class="row g-0 h-100">
                <!-- Left: Details -->
                <div class="col-lg-7 p-4 p-md-5 border-end border-light overflow-y-auto" style="max-height: 70vh;">
                   <div class="d-flex flex-wrap gap-2 mb-4">
                      <span class="badge bg-primary-soft text-primary px-3 py-2 rounded-pill fw-bold"><i class="bi bi-house-door me-1"></i>{{ selectedProperty?.loai?.ten_loai }}</span>
                      <span class="badge bg-light text-dark px-3 py-2 rounded-pill fw-bold border"><i class="bi bi-arrows-fullscreen me-1"></i>{{ selectedProperty?.dien_tich }} m²</span>
                      <span class="badge bg-light text-dark px-3 py-2 rounded-pill fw-bold border"><i class="bi bi-door-open me-1"></i>{{ selectedProperty?.so_phong_ngu }} PN</span>
                      <span class="badge bg-light text-dark px-3 py-2 rounded-pill fw-bold border"><i class="bi bi-droplet me-1"></i>{{ selectedProperty?.so_phong_tam }} PT</span>
                   </div>

                   <h3 class="fw-bold text-primary mb-1">{{ formatPrice(selectedProperty?.gia) }}</h3>
                   <p class="text-muted fw-medium mb-4"><i class="bi bi-geo-alt-fill me-1"></i>{{ selectedProperty?.dia_chi?.dia_chi_chi_tiet }}, {{ selectedProperty?.dia_chi?.quan?.ten }}, {{ selectedProperty?.dia_chi?.tinh?.ten }}</p>

                   <div class="mb-4">
                      <h6 class="fw-bold text-dark text-uppercase tracking-widest small mb-3">Mô tả chi tiết</h6>
                      <div class="text-secondary mo-ta lh-lg" v-html="formattedMoTa"></div>
                   </div>

                   <div class="card bg-light border-0 rounded-4 p-4 mb-4">
                      <h6 class="fw-bold text-dark text-uppercase tracking-widest small mb-3">Thông tin người đăng</h6>
                      <div class="d-flex align-items-center gap-3">
                         <div class="avatar bg-white shadow-sm text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width:50px;height:50px;font-size:1.2rem;">
                            {{ getInitials(selectedProperty?.moi_gioi?.ten) }}
                         </div>
                         <div>
                            <h6 class="fw-bold mb-1">{{ selectedProperty?.moi_gioi?.ten }}</h6>
                            <p class="mb-0 text-muted small"><i class="bi bi-telephone-fill me-1"></i>{{ selectedProperty?.moi_gioi?.sdt || selectedProperty?.moi_gioi?.so_dien_thoai || 'Đang cập nhật' }}</p>
                         </div>
                      </div>
                   </div>
                </div>

                <!-- Right: Images -->
                <div class="col-lg-5 bg-dark p-4 d-flex flex-column">
                   <h6 class="fw-bold text-white mb-3 text-uppercase tracking-widest small">Hình ảnh đính kèm</h6>
                   <div class="main-image-container flex-grow-1 mb-3 position-relative rounded-4 overflow-hidden shadow">
                      <img v-if="getPrimaryImageUrl(selectedProperty)" :src="getPrimaryImageUrl(selectedProperty)" class="w-100 h-100 object-cover cursor-pointer hover-zoom" @click="openImage(getPrimaryImageUrl(selectedProperty))" />
                   </div>
                   <div class="gallery d-flex gap-2 overflow-x-auto pb-2 custom-scrollbar-dark">
                      <div v-for="(img, index) in limitedImages" :key="index" class="position-relative flex-shrink-0 cursor-pointer hover-zoom" @click="index === 5 && remainingCount > 0 ? openImageAt(5) : openImageAt(index)">
                         <img :src="getImageUrl(img.url)" class="rounded-3 object-cover border border-secondary" style="width: 80px; height: 80px;" />
                         <div v-if="index === 5 && remainingCount > 0" class="overlay-more rounded-3 d-flex align-items-center justify-content-center text-white fw-bold fs-5 bg-dark bg-opacity-75 position-absolute top-0 start-0 w-100 h-100">
                           +{{ remainingCount }}
                         </div>
                      </div>
                   </div>
                </div>
             </div>
          </div>

          <div class="modal-footer border-0 py-3 px-4 bg-light d-flex justify-content-end gap-3">
            <template v-if="selectedProperty?.trang_thai_id == 1">
              <button @click="openRejectModal(selectedProperty)" class="btn btn-outline-danger rounded-pill px-4 fw-bold transition-all hover-elevate">
                <i class="bi bi-x-circle me-2"></i>Từ chối
              </button>
              <button @click="approveProperty(selectedProperty.id)" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm d-flex align-items-center gap-2 transition-all hover-elevate" data-bs-dismiss="modal">
                <i class="bi bi-check-lg"></i>Phê duyệt tin
              </button>
            </template>
            <button v-else type="button" class="btn btn-secondary rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Đóng</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Image Preview Overlay -->
    <transition name="fade-slide">
      <div v-if="showImagePreview" class="image-preview-overlay" @click="showImagePreview = false">
        <button class="btn btn-dark bg-opacity-50 btn-lg rounded-circle" @click.stop="prevImage" :disabled="currentIndex === 0"><i class="bi bi-chevron-left"></i></button>
        <img :src="previewImage" class="preview-img" @click.stop />
        <button class="btn btn-dark bg-opacity-50 btn-lg rounded-circle" @click.stop="nextImage" :disabled="currentIndex >= galleryImages.length - 1"><i class="bi bi-chevron-right"></i></button>
        <button class="btn btn-dark bg-opacity-50 rounded-circle position-absolute top-0 end-0 m-4" @click.stop="showImagePreview = false"><i class="bi bi-x-lg"></i></button>
      </div>
    </transition>
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
        { value: "6", label: "Từ chối", activeClass: "btn-danger text-white" },
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
        // Mở modal bằng JS để tránh lỗi Vue patch đè DOM
        const modalEl = document.getElementById("propertyModal");
        if (modalEl) {
          const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
          modal.show();
        }

        const res = await api.get(`/admin/bds/${id}`);
        if (res.data.status) {
          this.selectedProperty = res.data.data;
        } else {
          if (this.$toast) this.$toast.error(res.data.message || "Lỗi tải thông tin chi tiết");
        }
      } catch (error) {
        console.error("Lỗi xem chi tiết BĐS:", error);
        if (this.$toast) this.$toast.error("Không thể tải thông tin chi tiết BĐS!");
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
          if (response.data.stats) {
            this.statistics.total = response.data.stats.total || this.pagination.total;
            this.statistics.pending = response.data.stats.pending || 0;
            this.statistics.approved = response.data.stats.approved || 0;
            this.statistics.featured = response.data.stats.featured || 0;
          } else {
            this.updateStatistics();
          }
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
      try {
        const response = await api.post(`/admin/bds/duyet`, { id: id, is_duyet: 1 });
        if (response.data.status) {
          toaster.success("Đã duyệt tin đăng thành công! 🎉");
          this.loadProperties();
          
          // Đóng modal chi tiết nếu đang mở
          const modalEl = document.getElementById("propertyModal");
          if (modalEl) {
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
          }
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
      if (!this.rejectTarget) {
        toaster.error("Không tìm thấy tin đăng!");
        return;
      }

      const bdsId = this.rejectTarget.id || this.rejectTarget.bds_id;

      if (!bdsId) {
        toaster.error("Không tìm thấy ID bất động sản!");
        return;
      }

      try {
        const response = await api.post(`/admin/bds/duyet`, { 
          id: bdsId, 
          is_duyet: 0, 
          ly_do: this.rejectReason 
        });

        if (response.data.status) {
          toaster.success("Đã từ chối tin đăng thành công");
          this.loadProperties();
          this.rejectTarget = null;
          this.rejectReason = "";

          // ✅ Đóng modal reject
          const modalEl = document.getElementById("rejectModal");
          if (modalEl) {
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
          }
          
          // Đóng modal chi tiết nếu đang mở
          const detailModalEl = document.getElementById("propertyModal");
          if (detailModalEl) {
            const detailModal = bootstrap.Modal.getInstance(detailModalEl);
            if (detailModal) detailModal.hide();
          }
        } else {
          toaster.error(response.data.message || "Từ chối thất bại");
        }
      } catch (error) {
        console.error("❌ Lỗi từ chối:", error);
        toaster.error(error.response?.data?.message || "Lỗi khi từ chối tin đăng");
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
      if (!url) return "https://via.placeholder.com/400x300?text=No+Image";
      if (typeof url !== 'string') return "https://via.placeholder.com/400x300?text=Invalid+URL";
      if (url.startsWith("http")) return url;
      
      const base = import.meta.env.VITE_API_URL?.replace('/api','') || 'http://localhost:8000';
      // Đảm bảo không bị trùng /storage/
      const cleanUrl = url.startsWith('/') ? url.substring(1) : url;
      const finalUrl = cleanUrl.startsWith('storage/') ? cleanUrl : `storage/${cleanUrl}`;
      
      return `${base}/${finalUrl}`;
    },

    getPrimaryImageUrl(bds) {
      if (!bds) return "https://via.placeholder.com/400x300?text=No+Image";
      
      // Ưu tiên anh_dai_dien_url từ backend (đã có accessor asset())
      if (bds.anh_dai_dien_url) {
        return this.getImageUrl(bds.anh_dai_dien_url);
      }
      
      const images = bds.hinh_anh || bds.hinhAnh || [];
      const firstImage = images[0]?.url;
      return this.getImageUrl(firstImage);
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
      const map = { 1: "Chờ duyệt", 2: "Đã đăng", 3: "Đã bán", 4: "Cho thuê", 5: "Hết hạn", 6: "Bị từ chối" };
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

    getStatusBadgeClass(statusId) {
      const map = {
        1: 'bg-warning text-dark',
        2: 'bg-success text-white',
        3: 'bg-info text-white', // Đã bán
        4: 'bg-primary text-white', // Cho thuê
        5: 'bg-dark text-white', // Hết hạn
        6: 'bg-danger text-white' // Bị từ chối
      };
      return map[statusId] || 'bg-light text-dark border';
    },

    getStatusText(statusId) {
      const map = {
        1: 'Chờ duyệt',
        2: 'Đã đăng',
        3: 'Đã bán',
        4: 'Cho thuê',
        5: 'Hết hạn',
        6: 'Bị từ chối'
      };
      return map[statusId] || 'Không xác định';
    },
  },
};
</script>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap");

.property-management {
  font-family: "Plus Jakarta Sans", sans-serif;
  background-color: #f4f7fe;
  min-height: 100vh;
}

.tracking-widest { letter-spacing: 1.5px; }
.tracking-wide { letter-spacing: 0.5px; }

/* Cards & Containers */
.card {
  border-radius: 20px;
}
.glass-header-card {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
}
.glass-card {
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(10px);
}
.glass-modal {
  background: rgba(255, 255, 255, 0.98);
  backdrop-filter: blur(15px);
}

.header-icon-box {
  width: 56px;
  height: 56px;
}
.icon-box-sm {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Gradients */
.bg-gradient-primary { background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%); }
.premium-stat-card-1 { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); }
.premium-stat-card-2 { background: linear-gradient(135deg, #fef08a 0%, #fde047 100%); }
.premium-stat-card-3 { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
.premium-stat-card-4 { background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); }

.stat-icon-bg {
  position: absolute;
  right: -20px;
  bottom: -20px;
  font-size: 8rem;
  color: rgba(255, 255, 255, 0.15);
  transform: rotate(-15deg);
  pointer-events: none;
}
.premium-stat-card-2 .stat-icon-bg { color: rgba(0, 0, 0, 0.05); }

/* Inputs */
.custom-input {
  border-radius: 12px;
  padding: 12px 16px;
  border: 1px solid #e2e8f0;
  background-color: #f8fafc;
  transition: all 0.3s ease;
}
.custom-input:focus {
  border-color: #3b82f6;
  background-color: #fff;
  box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
}

/* Buttons */
.btn-hover-elevate { transition: all 0.3s ease; }
.btn-hover-elevate:hover { transform: translateY(-2px); box-shadow: 0 8px 15px rgba(59, 130, 246, 0.3) !important; }

.tab-btn { border: 2px solid transparent; background: transparent; color: #64748b; }
.tab-btn-active { background: white; color: #0f172a; border-color: #e2e8f0; }
.tab-btn-inactive:hover { background: rgba(255,255,255,0.5); }

.btn-action {
  width: 38px; height: 38px; border-radius: 12px; border: none; display: inline-flex; align-items: center; justify-content: center; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}
.hover-elevate:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
.hover-primary:hover { background: #4f46e5 !important; color: white !important; transform: translateY(-3px) scale(1.05); }
.hover-danger:hover { background: #ef4444 !important; color: white !important; transform: translateY(-3px) scale(1.05); }

/* Grid / List Layout */
.property-card {
  margin-bottom: 20px;
  animation: slideInUp 0.5s cubic-bezier(0.4, 0, 0.2, 1) forwards;
  opacity: 0;
  transform: translateY(20px);
}
.property-card:hover {
  box-shadow: 0 15px 30px rgba(0,0,0,0.05);
  transform: translateY(-4px);
  border-color: #e2e8f0 !important;
}

@keyframes slideInUp { to { opacity: 1; transform: translateY(0); } }

.property-title {
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
.property-status-badge {
  font-size: 0.85rem; padding: 8px 0; letter-spacing: 0.5px; text-transform: uppercase;
}

.avatar-sm { width: 36px; height: 36px; font-size: 0.8rem; }
.avatar { font-size: 1.2rem; }

.filter-tag { padding: 6px 16px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }

.pagination-btn { width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; transition: all 0.2s; padding: 0; }
.pagination-btn:hover:not(:disabled) { transform: translateY(-2px); }

.pulse-animation { animation: pulse 2s infinite; }
@keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); } 70% { box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); } 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); } }

.empty-state-icon i { animation: float 3s ease-in-out infinite; }
@keyframes float { 0% { transform: translateY(0px); } 50% { transform: translateY(-10px); } 100% { transform: translateY(0px); } }

.fade-slide-enter-active, .fade-slide-leave-active { transition: all 0.3s ease; }
.fade-slide-enter-from, .fade-slide-leave-to { opacity: 0; transform: translateY(-10px); }

.object-cover { object-fit: cover; }
.hover-zoom { transition: transform 0.3s ease; }
.hover-zoom:hover { transform: scale(1.03); z-index: 10; position: relative; }

.custom-scrollbar-dark::-webkit-scrollbar { height: 8px; }
.custom-scrollbar-dark::-webkit-scrollbar-thumb { background: #475569; border-radius: 10px; }
.custom-scrollbar-dark::-webkit-scrollbar-track { background: #1e293b; }

.image-preview-overlay {
  position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.9); display: flex; justify-content: space-between; align-items: center; z-index: 9999; padding: 20px;
}
.preview-img { max-width: 80%; max-height: 90vh; border-radius: 12px; box-shadow: 0 20px 50px rgba(0,0,0,0.5); }
</style>