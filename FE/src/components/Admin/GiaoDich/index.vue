<template>
  <div class="p-4">
    <!-- Header -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="card border-0 shadow-sm">
          <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-md-center py-3 gap-3">
            <div>
              <h4 class="mb-0 fw-bold text-primary">
                <i class="bi bi-credit-card-2-front me-2"></i>Quản lý giao dịch
              </h4>
              <small class="text-muted">Theo dõi tất cả giao dịch thanh toán mua gói tin trong hệ thống.</small>
            </div>
            <div class="d-flex gap-2 flex-wrap">
              <select v-model="filterStatus" class="form-select form-select-sm" style="width:160px">
                <option value="">Tất cả trạng thái</option>
                <option value="pending">Chờ xử lý</option>
                <option value="success">Thành công</option>
                <option value="failed">Thất bại</option>
                <option value="cancelled">Đã hủy</option>
              </select>
              <button @click="fetchData" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-clockwise me-1"></i>Làm mới
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
          <div class="fs-4 fw-bold text-primary">{{ stats.total }}</div>
          <div class="text-muted small">Tổng giao dịch</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
          <div class="fs-4 fw-bold text-success">{{ stats.success }}</div>
          <div class="text-muted small">Thành công</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
          <div class="fs-4 fw-bold text-warning">{{ stats.pending }}</div>
          <div class="text-muted small">Chờ xử lý</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
          <div class="fs-4 fw-bold text-danger">{{ stats.failed }}</div>
          <div class="text-muted small">Thất bại</div>
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="card border-0 shadow-sm">
      <div class="card-body p-0">
        <!-- Loading -->
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary" role="status"></div>
          <div class="mt-2 text-muted small">Đang tải dữ liệu...</div>
        </div>

        <!-- Empty -->
        <div v-else-if="filteredData.length === 0" class="text-center py-5">
          <i class="bi bi-inbox fs-1 text-muted opacity-50"></i>
          <p class="text-muted mt-2">Không có giao dịch nào</p>
        </div>

        <!-- Table -->
        <div v-else class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
              <tr>
                <th class="ps-4 py-3">Mã GD</th>
                <th class="py-3">Môi giới</th>
                <th class="py-3">Gói tin</th>
                <th class="py-3">Số tiền</th>
                <th class="py-3">Phương thức</th>
                <th class="py-3">Trạng thái</th>
                <th class="py-3">Mã SePay</th>
                <th class="pe-4 py-3">Ngày tạo</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in filteredData" :key="item.id">
                <td class="ps-4">
                  <span class="badge bg-light text-dark border fw-normal">{{ item.ma_giao_dich || '#' + item.id }}</span>
                </td>
                <td>
                  <div class="fw-medium">{{ item.moi_gioi?.ten || '—' }}</div>
                  <div class="text-muted small">{{ item.moi_gioi?.email || '' }}</div>
                </td>
                <td>
                  <span class="badge bg-primary bg-opacity-10 text-primary">
                    {{ item.goi_tin?.ten_goi || '—' }}
                  </span>
                </td>
                <td class="fw-bold text-success">{{ formatCurrency(item.so_tien) }}</td>
                <td>
                  <span class="badge bg-info bg-opacity-10 text-info">{{ item.phuong_thuc || 'SePay' }}</span>
                </td>
                <td>
                  <span :class="statusClass(item.trang_thai)" class="badge">
                    {{ statusLabel(item.trang_thai) }}
                  </span>
                </td>
                <td>
                  <span class="text-muted small font-monospace">{{ item.ma_sepay_txn_ref || '—' }}</span>
                </td>
                <td class="pe-4 text-muted small">{{ formatDate(item.created_at) }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.last_page > 1" class="d-flex justify-content-between align-items-center px-4 py-3 border-top">
          <small class="text-muted">
            Hiển thị {{ pagination.from }}–{{ pagination.to }} / {{ pagination.total }} giao dịch
          </small>
          <nav>
            <ul class="pagination pagination-sm mb-0">
              <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
                <button class="page-link" @click="fetchData(pagination.current_page - 1)">
                  <i class="bi bi-chevron-left"></i>
                </button>
              </li>
              <li v-for="p in pagination.last_page" :key="p" class="page-item" :class="{ active: pagination.current_page === p }">
                <button class="page-link" @click="fetchData(p)">{{ p }}</button>
              </li>
              <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
                <button class="page-link" @click="fetchData(pagination.current_page + 1)">
                  <i class="bi bi-chevron-right"></i>
                </button>
              </li>
            </ul>
          </nav>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import api from "@/axios/config";

const loading = ref(false);
const allData = ref([]);
const filterStatus = ref("");
const pagination = ref({ current_page: 1, last_page: 1, total: 0, from: 1, to: 1 });

const stats = ref({ total: 0, success: 0, pending: 0, failed: 0 });

const filteredData = computed(() => {
  if (!filterStatus.value) return allData.value;
  return allData.value.filter((d) => d.trang_thai === filterStatus.value);
});

const fetchData = async (page = 1) => {
  loading.value = true;
  try {
    const res = await api.get("/admin/giao-dich/data", { params: { page } });
    if (res.data?.status) {
      const d = res.data.data;
      allData.value = d.data || d;
      if (res.data.stats) {
        stats.value = res.data.stats;
      }
      if (d.current_page) {
        pagination.value = {
          current_page: d.current_page,
          last_page: d.last_page,
          total: d.total,
          from: d.from,
          to: d.to,
        };
      }
    }
  } catch (e) {
    console.error("Lỗi tải giao dịch:", e);
  } finally {
    loading.value = false;
  }
};

const formatCurrency = (val) =>
  new Intl.NumberFormat("vi-VN", { style: "currency", currency: "VND" }).format(val || 0);

const formatDate = (val) =>
  val ? new Date(val).toLocaleDateString("vi-VN", { day: "2-digit", month: "2-digit", year: "numeric", hour: "2-digit", minute: "2-digit" }) : "—";

const statusClass = (status) => {
  const map = { success: "bg-success", pending: "bg-warning text-dark", failed: "bg-danger", fail: "bg-danger", cancelled: "bg-secondary" };
  return map[status] || "bg-light text-dark";
};

const statusLabel = (status) => {
  const map = { success: "Thành công", pending: "Chờ xử lý", failed: "Thất bại", fail: "Thất bại", cancelled: "Đã hủy" };
  return map[status] || status;
};

onMounted(() => fetchData());
</script>
