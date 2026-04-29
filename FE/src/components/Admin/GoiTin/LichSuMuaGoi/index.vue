<template>
  <div class="min-h-screen bg-[#f0f4f8] font-['Inter'] p-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h1 class="text-2xl font-black text-gray-900">Lịch sử mua gói</h1>
        <p class="text-sm text-gray-400">Tổng quan lịch sử mua gói tin của môi giới</p>
      </div>
      <div class="flex items-center gap-3">
        <!-- Search -->
        <div class="relative">
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          <input v-model="searchKeyword" @input="debouncedSearch" type="text" placeholder="Tìm theo tên, email..."
            class="pl-10 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition w-56" />
        </div>
        <!-- Export -->
        <div class="relative">
          <button @click="showExportMenu = !showExportMenu"
            class="flex items-center gap-2 px-4 py-2.5 bg-green-600 text-white text-sm font-bold rounded-xl hover:bg-green-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Export
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
          </button>
          <div v-if="showExportMenu" class="absolute right-0 top-full mt-2 bg-white rounded-2xl shadow-xl border border-gray-100 p-2 z-30 w-48">
            <button @click="exportData('csv')" class="w-full flex items-center gap-3 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-xl transition">
              <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
              Xuất file CSV
            </button>
            <button @click="exportData('excel')" class="w-full flex items-center gap-3 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-xl transition">
              <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
              Xuất file Excel
            </button>
            <button @click="exportData('pdf')" class="w-full flex items-center gap-3 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-xl transition">
              <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
              Xuất file PDF
            </button>
          </div>
          <div v-if="showExportMenu" @click="showExportMenu = false" class="fixed inset-0 z-20"></div>
        </div>
      </div>
    </div>

    <!-- Tab Navigation -->
    <div class="flex items-center gap-1 bg-white rounded-2xl p-1.5 shadow-sm border border-gray-100 mb-6 w-fit">
      <button @click="activeTab = 'history'"
        class="flex items-center gap-2 px-4 py-2 text-sm font-bold rounded-xl transition-all"
        :class="activeTab === 'history' ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-400 hover:text-gray-600'">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        Lịch sử mua gói
      </button>
      <button @click="activeTab = 'unmatched'"
        class="flex items-center gap-2 px-4 py-2 text-sm font-bold rounded-xl transition-all"
        :class="activeTab === 'unmatched' ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-400 hover:text-gray-600'">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        Giao dịch chưa khớp
        <span v-if="unmatchedCount > 0" class="bg-red-500 text-white text-xs font-black w-5 h-5 rounded-full flex items-center justify-center">
          {{ unmatchedCount }}
        </span>
      </button>
    </div>

    <!-- Summary Stats -->
    <div v-if="summaryStats" class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
      <div class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-2xl p-5 text-white flex items-center justify-between shadow-sm">
        <div>
          <p class="text-xs font-bold text-blue-200 uppercase tracking-widest mb-1">Tổng doanh thu</p>
          <div class="text-2xl font-black leading-tight">{{ formatCurrency(summaryStats.tong_doanh_thu) }}</div>
          <p class="text-xs text-blue-200 mt-1">{{ summaryStats.tong_moi_gioi }} môi giới đã mua</p>
        </div>
        <div class="w-12 h-12 bg-white/15 rounded-2xl flex items-center justify-center flex-shrink-0">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
      </div>
      <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
          <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Tổng lần mua</p>
          <div class="text-3xl font-black text-blue-600">{{ summaryStats.tong_lan_mua }}</div>
          <p class="text-xs text-gray-400 mt-1">{{ summaryStats.goi_dang_hoat_dong }} gói đang active</p>
        </div>
        <div class="w-12 h-12 bg-blue-100 rounded-2xl flex items-center justify-center flex-shrink-0">
          <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        </div>
      </div>
      <div class="bg-white rounded-2xl p-5 border border-amber-100 shadow-sm flex items-center justify-between">
        <div>
          <p class="text-xs font-bold text-amber-500 uppercase tracking-widest mb-1">Môi giới mới</p>
          <div class="text-3xl font-black text-amber-500">{{ summaryStats.moi_gioi_moi_thang }}</div>
          <p class="text-xs text-gray-400 mt-1">Trong tháng này</p>
        </div>
        <div class="w-12 h-12 bg-amber-100 rounded-2xl flex items-center justify-center flex-shrink-0">
          <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        </div>
      </div>
    </div>

    <!-- Table: Danh sách môi giới (Grouped) -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="bg-gray-50 border-b border-gray-100">
              <th class="text-left text-xs font-bold text-gray-400 uppercase tracking-widest px-5 py-4">Môi giới</th>
              <th class="text-left text-xs font-bold text-gray-400 uppercase tracking-widest px-5 py-4">Số lần mua</th>
              <th class="text-center text-xs font-bold text-gray-400 uppercase tracking-widest px-5 py-4">Gói đang active</th>
              <th class="text-center text-xs font-bold text-gray-400 uppercase tracking-widest px-5 py-4">Tổng tiền</th>
              <th class="text-center text-xs font-bold text-gray-400 uppercase tracking-widest px-5 py-4">Lần mua gần nhất</th>
              <th class="text-right text-xs font-bold text-gray-400 uppercase tracking-widest px-5 py-4">Thao tác</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <!-- Loading -->
            <tr v-if="loading">
              <td colspan="6" class="text-center py-16">
                <div class="flex flex-col items-center gap-3">
                  <div class="w-8 h-8 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
                  <p class="text-sm text-gray-400">Đang tải dữ liệu...</p>
                </div>
              </td>
            </tr>
            <!-- Empty -->
            <tr v-else-if="danhSachGrouped.length === 0">
              <td colspan="6" class="text-center py-16">
                <div class="flex flex-col items-center gap-2">
                  <svg class="w-12 h-12 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                  <p class="text-sm text-gray-400">{{ searchKeyword ? 'Không tìm thấy kết quả' : 'Chưa có lịch sử mua nào' }}</p>
                </div>
              </td>
            </tr>
            <!-- Data -->
            <tr v-else v-for="item in danhSachGrouped" :key="item.moi_gioi.id" class="hover:bg-gray-50 transition-colors">
              <td class="px-5 py-4">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 font-black text-sm flex items-center justify-center flex-shrink-0">
                    {{ getInitials(item.moi_gioi.ten) }}
                  </div>
                  <div>
                    <div class="font-bold text-gray-900">{{ item.moi_gioi.ten }}</div>
                    <div class="text-xs text-gray-400">{{ item.moi_gioi.email }}</div>
                    <div class="text-xs text-gray-400">{{ item.moi_gioi.so_dien_thoai }}</div>
                  </div>
                </div>
              </td>
              <td class="px-5 py-4">
                <span class="inline-flex items-center px-2.5 py-1 bg-gray-100 text-gray-600 text-xs font-bold rounded-full">{{ item.thong_ke.tong_so_lan_mua }} lần</span>
              </td>
              <td class="px-5 py-4 text-center">
                <span class="inline-flex items-center px-2.5 py-1 text-xs font-bold rounded-full"
                  :class="item.thong_ke.so_goi_dang_hoat_dong > 0 ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
                  {{ item.thong_ke.so_goi_dang_hoat_dong }} gói
                </span>
              </td>
              <td class="px-5 py-4 text-center font-black text-blue-600">{{ item.thong_ke.tong_tien_formatted }}</td>
              <td class="px-5 py-4 text-center text-xs text-gray-400">{{ item.thoi_gian.lan_mua_gan_nhat || '-' }}</td>
              <td class="px-5 py-4 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button @click="viewChiTiet(item.moi_gioi.id)"
                    class="w-8 h-8 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center hover:bg-blue-100 transition" title="Xem chi tiết">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                  </button>
                  <button @click="copyInfo(item.moi_gioi)"
                    class="w-8 h-8 bg-gray-100 text-gray-500 rounded-lg flex items-center justify-center hover:bg-gray-200 transition" title="Copy thông tin">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <!-- Pagination -->
      <div v-if="pagination.total > 0" class="px-5 py-3 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3">
        <p class="text-xs text-gray-400">Hiển thị {{ pagination.from }}-{{ pagination.to }} của {{ pagination.total }} môi giới</p>
        <div class="flex items-center gap-1">
          <button @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page === 1"
            class="w-8 h-8 rounded-lg border border-gray-200 text-gray-500 flex items-center justify-center hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition text-sm">&laquo;</button>
          <button v-for="page in visiblePages" :key="page" @click="changePage(page)"
            class="w-8 h-8 rounded-lg text-sm font-bold transition"
            :class="page === pagination.current_page ? 'bg-blue-600 text-white shadow-sm' : 'border border-gray-200 text-gray-500 hover:bg-gray-50'">{{ page }}</button>
          <button @click="changePage(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page"
            class="w-8 h-8 rounded-lg border border-gray-200 text-gray-500 flex items-center justify-center hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition text-sm">&raquo;</button>
        </div>
      </div>
    </div>

    <!-- Unmatched Payments Section -->
    <div v-if="activeTab === 'unmatched'" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="bg-gray-50 border-b border-gray-100">
              <th class="text-left text-xs font-bold text-gray-400 uppercase tracking-widest px-5 py-4">Thời gian</th>
              <th class="text-left text-xs font-bold text-gray-400 uppercase tracking-widest px-5 py-4">Số tiền</th>
              <th class="text-left text-xs font-bold text-gray-400 uppercase tracking-widest px-5 py-4">Mã Sepay</th>
              <th class="text-left text-xs font-bold text-gray-400 uppercase tracking-widest px-5 py-4">Reference</th>
              <th class="text-center text-xs font-bold text-gray-400 uppercase tracking-widest px-5 py-4">Trạng thái</th>
              <th class="text-right text-xs font-bold text-gray-400 uppercase tracking-widest px-5 py-4">Hành động</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-if="!unmatchedPayments.length">
              <td colspan="6" class="text-center py-16">
                <div class="flex flex-col items-center gap-2">
                  <svg class="w-12 h-12 text-green-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                  <p class="text-sm text-gray-400">Không có giao dịch chưa khớp</p>
                </div>
              </td>
            </tr>
            <tr v-else v-for="item in unmatchedPayments" :key="item.id" class="hover:bg-gray-50 transition-colors">
              <td class="px-5 py-4 text-gray-600 text-xs">{{ formatDateTime(item.created_at) }}</td>
              <td class="px-5 py-4 font-black text-green-600">{{ formatCurrency(item.so_tien) }}</td>
              <td class="px-5 py-4"><code class="text-xs bg-gray-100 px-2 py-1 rounded">{{ item.order_code_from_sepay || 'N/A' }}</code></td>
              <td class="px-5 py-4"><code class="text-xs bg-gray-100 px-2 py-1 rounded">{{ item.sepayer_reference }}</code></td>
              <td class="px-5 py-4 text-center">
                <span class="px-2.5 py-1 bg-amber-100 text-amber-700 text-xs font-bold rounded-full">{{ item.status }}</span>
              </td>
              <td class="px-5 py-4 text-right">
                <div class="flex items-center justify-end gap-2 flex-wrap">
                  <button v-for="tx in pendingTransactions" :key="tx.id"
                    @click="matchPayment(item.id, tx.id)"
                    class="px-3 py-1.5 bg-blue-50 text-blue-600 text-xs font-bold rounded-lg hover:bg-blue-100 transition">
                    {{ tx.ma_giao_dich }}
                  </button>
                  <button @click="ignorePayment(item.id)"
                    class="px-3 py-1.5 bg-gray-100 text-gray-500 text-xs font-bold rounded-lg hover:bg-gray-200 transition">
                    Bỏ qua
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal: Chi tiết lịch sử mua của 1 môi giới -->
    <Teleport to="body">
      <div v-if="showDetailModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden">
          <!-- Modal Header -->
          <div class="flex items-start justify-between p-6 border-b border-gray-100 flex-shrink-0">
            <div>
              <h3 class="font-black text-gray-900 text-lg">Lịch sử mua của {{ chiTietData.moi_gioi?.ten }}</h3>
              <p class="text-sm text-gray-400 mt-0.5">{{ chiTietData.moi_gioi?.so_dien_thoai }} • {{ chiTietData.moi_gioi?.email }}</p>
            </div>
            <button @click="closeModal" class="w-8 h-8 rounded-lg bg-gray-100 text-gray-400 hover:text-gray-600 flex items-center justify-center transition flex-shrink-0">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>
          <!-- Modal Body -->
          <div class="p-6 overflow-y-auto flex-1">
            <!-- Loading -->
            <div v-if="loadingChiTiet" class="flex flex-col items-center justify-center py-12 gap-3">
              <div class="w-8 h-8 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
              <p class="text-sm text-gray-400">Đang tải chi tiết...</p>
            </div>
            <template v-else>
              <!-- Mini stats -->
              <div v-if="chiTietData.thong_ke" class="grid grid-cols-3 gap-3 mb-6">
                <div class="bg-gray-50 rounded-2xl p-4 text-center">
                  <p class="text-xs text-gray-400 mb-1">Tổng đơn</p>
                  <div class="text-2xl font-black text-blue-600">{{ chiTietData.thong_ke.tong_don }}</div>
                </div>
                <div class="bg-gray-50 rounded-2xl p-4 text-center">
                  <p class="text-xs text-gray-400 mb-1">Đang active</p>
                  <div class="text-2xl font-black text-green-600">{{ chiTietData.thong_ke.don_dang_hoat_dong }}</div>
                </div>
                <div class="bg-gray-50 rounded-2xl p-4 text-center">
                  <p class="text-xs text-gray-400 mb-1">Tổng tiền</p>
                  <div class="text-lg font-black text-blue-600">{{ chiTietData.thong_ke.tong_tien }}</div>
                </div>
              </div>
              <!-- Purchase list -->
              <div v-if="chiTietData.lich_su_mua?.length > 0">
                <h4 class="font-bold text-gray-800 mb-4 text-sm">Danh sách gói đã mua</h4>
                <div class="space-y-3">
                  <div v-for="don in chiTietData.lich_su_mua" :key="don.id"
                    class="border border-gray-100 rounded-2xl p-4 hover:border-blue-200 transition">
                    <div class="flex items-start justify-between gap-3">
                      <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-2">
                          <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">#{{ don.ma_don_hang }}</span>
                          <span class="text-xs font-bold px-2 py-0.5 rounded-full"
                            :class="don.trang_thai_label?.class === 'success' ? 'bg-green-100 text-green-700' : don.trang_thai_label?.class === 'danger' ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-500'">
                            {{ don.trang_thai_label?.text }}
                          </span>
                        </div>
                        <p class="font-bold text-gray-900 text-sm mb-1">{{ don.goi_tin?.ten_goi }}</p>
                        <p class="text-xs text-gray-400">{{ don.ngay_bat_dau }} → {{ don.ngay_ket_thuc }} • {{ don.goi_tin?.so_luong_tin }} tin / {{ don.goi_tin?.so_ngay }} ngày</p>
                      </div>
                      <div class="text-right flex-shrink-0">
                        <div class="font-black text-blue-600">{{ don.so_tien_formatted }}</div>
                        <div class="text-xs text-gray-400">{{ don.phuong_thuc }}</div>
                        <div class="text-xs text-gray-400">{{ don.ngay_mua }}</div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-10">
                <svg class="w-10 h-10 text-gray-200 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <p class="text-sm text-gray-400">Chưa có lịch sử mua nào</p>
              </div>
            </template>
          </div>
          <!-- Modal Footer -->
          <div class="flex items-center justify-between p-6 border-t border-gray-100 flex-shrink-0">
            <button @click="closeModal" class="px-5 py-2.5 border-2 border-gray-200 text-gray-500 font-bold rounded-xl hover:bg-gray-50 transition text-sm">Đóng</button>
            <div class="flex items-center gap-2">
              <button @click="exportChiTiet('csv')" :disabled="!moiGioiId"
                class="px-4 py-2 bg-green-50 text-green-700 text-xs font-bold rounded-xl hover:bg-green-100 transition disabled:opacity-40">CSV</button>
              <button @click="exportChiTiet('excel')" :disabled="!moiGioiId"
                class="px-4 py-2 bg-blue-50 text-blue-700 text-xs font-bold rounded-xl hover:bg-blue-100 transition disabled:opacity-40">Excel</button>
              <button @click="exportChiTiet('pdf')" :disabled="!moiGioiId"
                class="px-4 py-2 bg-red-50 text-red-600 text-xs font-bold rounded-xl hover:bg-red-100 transition disabled:opacity-40">PDF</button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

  </div>
</template>

<script>
import axios from "../../../../axios/config";
import { createToaster } from "@meforma/vue-toaster";
const toaster = createToaster({ position: "top-right" });

export default {
  data() {
    return {
      moiGioiId: null,
      // Data chính
      danhSachGrouped: [],
      chiTietData: {
        moi_gioi: null,
        thong_ke: null,
        lich_su_mua: [],
      },

      // Tab control
      activeTab: "history", // 'history' hoặc 'unmatched'
      unmatchedPayments: [],
      unmatchedCount: 0,
      pendingTransactions: [],

      // Pagination
      pagination: {
        current_page: 1,
        last_page: 1,
        total: 0,
        from: 0,
        to: 0,
        per_page: 10,
      },

      // Stats summary
      summaryStats: null,
      showExportMenu: false,

      // Loading states
      loading: false,
      loadingChiTiet: false,

      // Search & filter
      searchKeyword: "",
      searchTimeout: null,

      showDetailModal: false,
    };
  },

  computed: {
    // Generate visible page numbers for pagination
    visiblePages() {
      const pages = [];
      const maxVisible = 5;
      let start = Math.max(
        1,
        this.pagination.current_page - Math.floor(maxVisible / 2)
      );
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
    this.loadLichSu();
  },

  methods: {
    getToken() {
      return (
        localStorage.getItem("token") || localStorage.getItem("auth_token")
      );
    },

    // Load unmatched payments
    async loadUnmatchedPayments() {
      try {
        const res = await axios.get("/admin/unmatched-payments");
        if (res.data.status) {
          this.unmatchedPayments = res.data.data;
          this.unmatchedCount = res.data.total || 0;
        }
      } catch (error) {
        console.error("Lỗi load unmatched:", error);
      }
    },

    // Load pending transactions (để khớp)
    async loadPendingTransactions() {
      try {
        const res = await axios.get("/admin/giao-dich/pending");
        if (res.data.status) {
          this.pendingTransactions = res.data.data;
        }
      } catch (error) {
        console.error("Lỗi load pending:", error);
      }
    },

    // Khớp payment thủ công
    async matchPayment(unmatchedId, transactionId) {
      if (!confirm("Xác nhận khớp giao dịch này?")) return;

      try {
        const res = await axios.post(
          `/admin/unmatched-payments/${unmatchedId}/match`,
          { giao_dich_id: transactionId }
        );

        if (res.data.status) {
          toaster.success("Đã khớp giao dịch thành công!");
          this.loadUnmatchedPayments(); // Reload danh sách
        } else {
          toaster.error(res.data.message || "Lỗi khớp giao dịch");
        }
      } catch (error) {
        toaster.error("Không thể khớp giao dịch");
      }
    },

    // Bỏ qua payment
    async ignorePayment(id) {
      if (!confirm("Bỏ qua giao dịch này?")) return;

      try {
        const res = await axios.post(`/admin/unmatched-payments/${id}/ignore`, {
          reason: "Admin bỏ qua",
        });

        if (res.data.status) {
          toaster.success("Đã bỏ qua giao dịch");
          this.loadUnmatchedPayments();
        }
      } catch (error) {
        toaster.error("Không thể bỏ qua");
      }
    },

    formatDateTime(dateStr) {
      if (!dateStr) return "-";
      const date = new Date(dateStr);
      return date.toLocaleString("vi-VN");
    },

    // Watch tab change
    watch: {
      activeTab(newTab) {
        if (newTab === "unmatched") {
          this.loadUnmatchedPayments();
          this.loadPendingTransactions();
        }
      },
    },

    closeModal() {
      this.showDetailModal = false;
      this.$nextTick(() => {
        this.chiTietData = { moi_gioi: null, thong_ke: null, lich_su_mua: [] };
      });
    },
    // Load danh sách grouped (API: lichSuMua)
    async loadLichSu(page = 1) {
      this.loading = true;
      try {
        const params = {
          page,
          per_page: this.pagination.per_page,
          ...(this.searchKeyword && { search: this.searchKeyword }),
        };
        const res = await axios.get("/admin/goi-tin/lich-su-mua", { params });

        if (res.data.status) {
          this.danhSachGrouped = res.data.data.data;
          this.pagination = {
            current_page: res.data.data.current_page,
            last_page: res.data.data.last_page,
            total: res.data.data.total,
            from: res.data.data.from,
            to: res.data.data.to,
            per_page: res.data.data.per_page,
          };

          // Tính summary stats từ data hiện có (hoặc gọi API riêng nếu cần)
          this.calculateSummaryStats();
        }
      } catch (error) {
        console.error("Lỗi load lịch sử:", error);
        toaster.error("Không thể tải danh sách lịch sử");
      } finally {
        this.loading = false;
      }
    },

    // Load chi tiết 1 môi giới (API: chiTietLichSuMua/{id})
    // Load chi tiết 1 môi giới - có debug đầy đủ
    async viewChiTiet(moiGioiId) {
      this.moiGioiId = moiGioiId;
      this.loadingChiTiet = true;
      this.chiTietData = { moi_gioi: null, thong_ke: null, lich_su_mua: [] };
      try {
        // Kiểm tra URL API - sửa lại cho đúng route của bạn
        const res = await axios.get(`/admin/goi-tin/lich-su-mua/${moiGioiId}/chi-tiet`);
        if (res.data.status) {
          this.chiTietData = res.data.data;
          // Show modal
          this.showDetailModal = true;
        } else {
          toaster.error(res.data.message || "Không có dữ liệu");
        }
      } catch (error) {
        // Hiển thị lỗi chi tiết cho dev
        if (error.response) {
          console.error("Status:", error.response.status);
          console.error("Data:", error.response.data);
          toaster.error(
            `Lỗi ${error.response.status}: ${error.response.data.message || ""}`
          );
        } else if (error.request) {
          console.error("No response received:", error.request);
          toaster.error("Không thể kết nối đến server");
        } else {
          toaster.error("Lỗi: " + error.message);
        }
      } finally {
        this.loadingChiTiet = false;
      }
    },
    // Debounced search (tránh gọi API liên tục khi gõ)
    debouncedSearch() {
      clearTimeout(this.searchTimeout);
      this.searchTimeout = setTimeout(() => {
        this.pagination.current_page = 1;
        this.loadLichSu(1);
      }, 300);
    },

    // Change page
    changePage(page) {
      if (
        page < 1 ||
        page > this.pagination.last_page ||
        page === this.pagination.current_page
      )
        return;
      this.pagination.current_page = page;
      this.loadLichSu(page);
      // Scroll to top of table
      document
        .querySelector(".package-history")
        ?.scrollIntoView({ behavior: "smooth" });
    },

    // 🔹 Calculate summary stats from current page data
    calculateSummaryStats() {
      if (!this.danhSachGrouped?.length) {
        this.summaryStats = null;
        return;
      }

      const stats = this.danhSachGrouped.reduce(
        (acc, item) => {
          acc.tong_doanh_thu += item.thong_ke.tong_tien_da_mua;
          acc.tong_lan_mua += item.thong_ke.tong_so_lan_mua;
          acc.goi_dang_hoat_dong += item.thong_ke.so_goi_dang_hoat_dong;
          return acc;
        },
        {
          tong_doanh_thu: 0,
          tong_lan_mua: 0,
          goi_dang_hoat_dong: 0,
          tong_moi_gioi: this.danhSachGrouped.length,
          moi_gioi_moi_thang: this.danhSachGrouped.filter((i) => {
            const muaGanNhat = i.thoi_gian.lan_mua_gan_nhat;
            return (
              muaGanNhat &&
              new Date(muaGanNhat).getMonth() === new Date().getMonth()
            );
          }).length,
        }
      );

      this.summaryStats = stats;
    },

    // Helper: Get initials for avatar
    getInitials(name) {
      if (!name) return "?";
      return name
        .split(" ")
        .map((part) => part[0])
        .slice(0, 2)
        .join("")
        .toUpperCase();
    },

    // Helper: Copy info to clipboard
    copyInfo(moiGioi) {
      const text = `${moiGioi.ten}\nSĐT: ${moiGioi.so_dien_thoai}\nEmail: ${moiGioi.email}`;
      navigator.clipboard
        .writeText(text)
        .then(() => {
          toaster.success("Đã copy thông tin!");
        })
        .catch(() => {
          toaster.error("Không thể copy");
        });
    },

    // Export chi tiết (có thể gọi API export riêng)
    exportChiTiet(format) {
      if (!this.moiGioiId) {
        this.showToast("Không tìm thấy ID môi giới!", "error");
        return;
      }

      const token = localStorage.getItem("auth_token");
      if (!token) {
        this.showToast("Vui lòng đăng nhập lại!", "error");
        return;
      }

      const apiBase = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';
      const url = `${apiBase}/admin/moi-gioi/export/${this.moiGioiId}?format=${format}&token=${token}`;
      window.open(url, "_blank");
      this.showToast(`Đang tải file ${format.toUpperCase()}...`, "success");
    },
    closeDetailModal() {
      this.showDetailModal = false;
      this.moiGioiId = null; // ✅ Clear ID
      this.chiTietData = {
        moi_gioi: null,
        thong_ke: null,
        lich_su_mua: [],
      };
    },

    // Export báo cáo tổng
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

      // URL phải khớp với route backend: /api/admin/-----/export
      const apiBase = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';
      const exportUrl = `${apiBase}/admin/giao-dich/export?${params}`;
      console.log("📤 Export URL:", exportUrl); // Debug

      window.open(exportUrl, "_blank");
      this.showExportMenu = false;
      this.showToast(`Đang tải file ${format.toUpperCase()}...`, "success");
    },

    // Format currency
    formatCurrency(value) {
      if (value === null || value === undefined) return "0 đ";
      return new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND",
        maximumFractionDigits: 0,
      }).format(value);
    },
  },

  beforeUnmount() {
    clearTimeout(this.searchTimeout);
  },
  watch: {
    moiGioiId(newVal) {
      console.log("🆔 moiGioiId changed to:", newVal);
    },
  },
};
</script>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap");

</style>