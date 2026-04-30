<template>
  <div class="page-content">
    <!-- Page Header -->
    <div class="page-header">
      <div class="page-title">
        <i class="bx bx-receipt page-icon"></i>
        <div>
          <h4>Lịch sử mua tin</h4>
          <p class="page-subtitle">Toàn bộ lịch sử mua gói đăng tin của bạn</p>
        </div>
      </div>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards">
      <div class="summary-card">
        <div class="card-icon blue"><i class="bx bx-package"></i></div>
        <div class="card-info">
          <span class="card-value">{{ summary.total }}</span>
          <span class="card-label">Tổng giao dịch</span>
        </div>
      </div>
      <div class="summary-card">
        <div class="card-icon green"><i class="bx bx-check-circle"></i></div>
        <div class="card-info">
          <span class="card-value">{{ summary.success }}</span>
          <span class="card-label">Thành công</span>
        </div>
      </div>
      <div class="summary-card">
        <div class="card-icon yellow"><i class="bx bx-time"></i></div>
        <div class="card-info">
          <span class="card-value">{{ summary.pending }}</span>
          <span class="card-label">Chờ xử lý</span>
        </div>
      </div>
      <div class="summary-card">
        <div class="card-icon purple"><i class="bx bx-money"></i></div>
        <div class="card-info">
          <span class="card-value">{{ formatCurrency(summary.tongTien) }}đ</span>
          <span class="card-label">Tổng chi tiêu</span>
        </div>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
      <div class="filter-tabs">
        <button
          v-for="tab in statusTabs"
          :key="tab.value"
          class="filter-tab"
          :class="{ active: filterStatus === tab.value }"
          @click="setFilter(tab.value)"
        >
          {{ tab.label }}
          <span class="tab-count">{{ tabCount(tab.value) }}</span>
        </button>
      </div>
    </div>

    <!-- Table -->
    <div class="table-card">
      <!-- Loading -->
      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <p>Đang tải dữ liệu...</p>
      </div>

      <!-- Empty -->
      <div v-else-if="transactions.length === 0" class="empty-state">
        <i class="bx bx-receipt empty-icon"></i>
        <h5>Chưa có giao dịch nào</h5>
        <p>Bạn chưa mua gói tin nào. Hãy chọn gói phù hợp để bắt đầu đăng tin!</p>
        <router-link to="/moi-gioi/goi-tin" class="btn-buy">
          <i class="bx bx-store"></i> Mua gói tin ngay
        </router-link>
      </div>

      <!-- Data Table -->
      <table v-else class="data-table">
        <thead>
          <tr>
            <th>Mã giao dịch</th>
            <th>Gói tin</th>
            <th>Số tiền</th>
            <th>Phương thức</th>
            <th>Trạng thái</th>
            <th>Ngày tạo</th>
            <th>Ngày thanh toán</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="tx in transactions" :key="tx.id">
            <td>
              <span class="order-code">{{ tx.ma_giao_dich }}</span>
            </td>
            <td>
              <div class="package-cell">
                <span class="package-name">{{ tx.goi_tin?.ten_goi || '—' }}</span>
                <span v-if="tx.goi_tin" class="package-detail">
                  {{ tx.goi_tin.so_luong_tin }} tin · {{ tx.goi_tin.so_ngay }} ngày
                </span>
              </div>
            </td>
            <td>
              <span class="amount">{{ formatCurrency(tx.so_tien) }}đ</span>
            </td>
            <td>
              <span class="method-badge" :class="tx.phuong_thuc">
                <i class="bx bx-credit-card"></i>
                {{ formatMethod(tx.phuong_thuc) }}
              </span>
            </td>
            <td>
              <span class="status-badge" :class="tx.trang_thai">
                {{ formatStatus(tx.trang_thai) }}
              </span>
            </td>
            <td class="text-muted">{{ formatDate(tx.created_at) }}</td>
            <td class="text-muted">{{ tx.paid_at ? formatDate(tx.paid_at) : '—' }}</td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="pagination-wrapper">
        <button
          class="page-btn"
          :disabled="pagination.current_page === 1"
          @click="fetchData(pagination.current_page - 1)"
        >
          <i class="bx bx-chevron-left"></i>
        </button>
        <span class="page-info">
          Trang {{ pagination.current_page }} / {{ pagination.last_page }}
          <small>({{ pagination.total }} giao dịch)</small>
        </span>
        <button
          class="page-btn"
          :disabled="pagination.current_page === pagination.last_page"
          @click="fetchData(pagination.current_page + 1)"
        >
          <i class="bx bx-chevron-right"></i>
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import api from '@/axios/config';

export default {
  name: 'LichSuMuaTin',
  data() {
    return {
      loading: false,
      transactions: [],
      allTransactions: [],
      filterStatus: '',
      pagination: {
        current_page: 1,
        last_page: 1,
        total: 0,
      },
      statusTabs: [
        { label: 'Tất cả',      value: '' },
        { label: 'Thành công',  value: 'success' },
        { label: 'Chờ xử lý',  value: 'pending' },
        { label: 'Đã huỷ',     value: 'cancel' },
      ],
    };
  },
  computed: {
    summary() {
      const all = this.allTransactions;
      return {
        total:    all.length,
        success:  all.filter(t => t.trang_thai === 'success').length,
        pending:  all.filter(t => t.trang_thai === 'pending').length,
        tongTien: all.filter(t => t.trang_thai === 'success').reduce((s, t) => s + Number(t.so_tien), 0),
      };
    },
  },
  mounted() {
    this.fetchAllForSummary();
    this.fetchData(1);
  },
  methods: {
    async fetchAllForSummary() {
      try {
        const res = await api.get('/moi-gioi/giao-dich/lich-su', { params: { per_page: 999 } });
        this.allTransactions = res.data?.data?.data || [];
      } catch (e) {
        console.error(e);
      }
    },

    async fetchData(page = 1) {
      this.loading = true;
      try {
        const params = { page, per_page: 10 };
        if (this.filterStatus) params.trang_thai = this.filterStatus;

        const res = await api.get('/moi-gioi/giao-dich/lich-su', { params });
        const result = res.data?.data;
        this.transactions = result?.data || [];
        this.pagination = {
          current_page: result?.current_page || 1,
          last_page:    result?.last_page    || 1,
          total:        result?.total        || 0,
        };
      } catch (e) {
        console.error(e);
      } finally {
        this.loading = false;
      }
    },

    setFilter(value) {
      this.filterStatus = value;
      this.fetchData(1);
    },

    tabCount(status) {
      if (!status) return this.allTransactions.length;
      return this.allTransactions.filter(t => t.trang_thai === status).length;
    },

    formatCurrency(value) {
      if (!value && value !== 0) return '0';
      return new Intl.NumberFormat('vi-VN').format(value);
    },

    formatDate(dateStr) {
      if (!dateStr) return '—';
      const d = new Date(dateStr);
      return `${String(d.getDate()).padStart(2,'0')}/${String(d.getMonth()+1).padStart(2,'0')}/${d.getFullYear()} ${String(d.getHours()).padStart(2,'0')}:${String(d.getMinutes()).padStart(2,'0')}`;
    },

    formatStatus(status) {
      const map = { success: 'Thành công', pending: 'Chờ xử lý', cancel: 'Đã huỷ', error: 'Thất bại' };
      return map[status] || status;
    },

    formatMethod(method) {
      const map = { sepay: 'SePay', bank: 'Chuyển khoản', cash: 'Tiền mặt' };
      return map[method] || method || '—';
    },
  },
};
</script>

<style scoped>
.page-content {
  padding: 24px 32px;
  width: 100%;
}

/* Header */
.page-header {
  margin-bottom: 24px;
}
.page-title {
  display: flex;
  align-items: center;
  gap: 14px;
}
.page-icon {
  font-size: 36px;
  color: #10b981;
}
.page-title h4 {
  margin: 0;
  font-size: 22px;
  font-weight: 700;
  color: #1f2937;
}
.page-subtitle {
  margin: 2px 0 0;
  font-size: 13px;
  color: #6b7280;
}

/* Summary Cards */
.summary-cards {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
  margin-bottom: 28px;
}
.summary-card {
  background: white;
  border-radius: 14px;
  padding: 22px 24px;
  display: flex;
  align-items: center;
  gap: 14px;
  box-shadow: 0 1px 4px rgba(0,0,0,0.07);
  border: 1px solid #f1f5f9;
}
.card-icon {
  width: 46px;
  height: 46px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 22px;
  flex-shrink: 0;
}
.card-icon.blue   { background: #eff6ff; color: #3b82f6; }
.card-icon.green  { background: #f0fdf4; color: #10b981; }
.card-icon.yellow { background: #fffbeb; color: #f59e0b; }
.card-icon.purple { background: #faf5ff; color: #8b5cf6; }
.card-info { display: flex; flex-direction: column; }
.card-value { font-size: 20px; font-weight: 700; color: #1f2937; line-height: 1.2; }
.card-label { font-size: 12px; color: #6b7280; margin-top: 2px; }

/* Filter Tabs */
.filter-bar {
  margin-bottom: 16px;
}
.filter-tabs {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}
.filter-tab {
  padding: 7px 16px;
  border-radius: 999px;
  border: 1.5px solid #e5e7eb;
  background: white;
  font-size: 13px;
  font-weight: 500;
  color: #6b7280;
  cursor: pointer;
  transition: all 0.15s;
  display: flex;
  align-items: center;
  gap: 6px;
}
.filter-tab:hover { border-color: #10b981; color: #10b981; }
.filter-tab.active { background: #10b981; border-color: #10b981; color: white; }
.tab-count {
  background: rgba(0,0,0,0.12);
  border-radius: 999px;
  padding: 1px 7px;
  font-size: 11px;
}
.filter-tab.active .tab-count { background: rgba(255,255,255,0.25); }

/* Table Card */
.table-card {
  background: white;
  border-radius: 14px;
  box-shadow: 0 1px 4px rgba(0,0,0,0.07);
  border: 1px solid #f1f5f9;
  overflow: hidden;
}

/* Loading */
.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 60px 20px;
  color: #6b7280;
}
.spinner {
  width: 32px; height: 32px;
  border: 3px solid #e5e7eb;
  border-top-color: #10b981;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin-bottom: 12px;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Empty */
.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #6b7280;
}
.empty-icon { font-size: 56px; color: #d1d5db; display: block; margin-bottom: 12px; }
.empty-state h5 { font-size: 17px; font-weight: 600; color: #374151; margin-bottom: 6px; }
.empty-state p { font-size: 13px; margin-bottom: 20px; }
.btn-buy {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: linear-gradient(135deg, #065f46, #10b981);
  color: white;
  padding: 10px 22px;
  border-radius: 999px;
  font-weight: 600;
  font-size: 14px;
  text-decoration: none;
  transition: all 0.2s;
}
.btn-buy:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(16,185,129,0.3); }

/* Table */
.data-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13.5px;
}
.data-table th {
  background: #f8fafc;
  padding: 12px 16px;
  text-align: left;
  font-size: 12px;
  font-weight: 600;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  border-bottom: 1px solid #e5e7eb;
}
.data-table td {
  padding: 14px 16px;
  border-bottom: 1px solid #f1f5f9;
  vertical-align: middle;
  color: #374151;
}
.data-table tbody tr:hover { background: #fafffe; }
.data-table tbody tr:last-child td { border-bottom: none; }

.order-code {
  font-family: 'Courier New', monospace;
  font-size: 12px;
  background: #f1f5f9;
  padding: 4px 10px;
  border-radius: 6px;
  color: #374151;
  display: inline-block;
  max-width: 220px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.package-cell { display: flex; flex-direction: column; }
.package-name { font-weight: 600; color: #1f2937; }
.package-detail { font-size: 12px; color: #6b7280; margin-top: 2px; }

.amount {
  font-weight: 700;
  color: #10b981;
  font-size: 14px;
}

.method-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px 10px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 500;
  background: #f1f5f9;
  color: #475569;
}
.method-badge.sepay { background: #eff6ff; color: #3b82f6; }

.status-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 600;
}
.status-badge.success { background: #dcfce7; color: #15803d; }
.status-badge.pending { background: #fef9c3; color: #a16207; }
.status-badge.cancel  { background: #fee2e2; color: #dc2626; }
.status-badge.error   { background: #fee2e2; color: #dc2626; }

.text-muted { color: #9ca3af; font-size: 13px; }

/* Pagination */
.pagination-wrapper {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 14px;
  padding: 16px;
  border-top: 1px solid #f1f5f9;
}
.page-btn {
  width: 34px; height: 34px;
  border-radius: 8px;
  border: 1.5px solid #e5e7eb;
  background: white;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer;
  font-size: 18px;
  color: #374151;
  transition: all 0.15s;
}
.page-btn:hover:not(:disabled) { border-color: #10b981; color: #10b981; }
.page-btn:disabled { opacity: 0.4; cursor: not-allowed; }
.page-info { font-size: 13px; color: #6b7280; }
.page-info small { margin-left: 6px; }

@media (max-width: 768px) {
  .summary-cards { grid-template-columns: repeat(2, 1fr); }
  .data-table { font-size: 12px; }
  .data-table th, .data-table td { padding: 10px 10px; }
}
</style>
