<template>
  <div class="min-h-screen bg-[#f0f4f8] font-['Inter'] p-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h1 class="text-2xl font-black text-gray-900">Quản lý gói tin</h1>
        <p class="text-sm text-gray-400">Quản lý danh sách các gói dịch vụ và bảng giá cho môi giới.</p>
      </div>
      <button @click="showCreateModal = true"
        class="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-700 text-white text-sm font-bold rounded-2xl shadow-xl shadow-blue-200 hover:shadow-2xl hover:shadow-blue-300 hover:-translate-y-1 transition-all active:scale-95 group">
        <svg class="w-5 h-5 transition-transform group-hover:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
        </svg>
        Tạo gói mới
      </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
      <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center justify-between">
        <div>
          <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Tổng số gói</p>
          <div class="text-3xl font-black text-blue-600">{{ danhSachGoiTin.length }}</div>
        </div>
        <div class="w-12 h-12 bg-blue-100 rounded-2xl flex items-center justify-center">
          <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
        </div>
      </div>
      <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl p-5 shadow-sm text-white flex items-center justify-between">
        <div>
          <p class="text-xs font-bold text-green-100 uppercase tracking-widest mb-1">Đang chạy</p>
          <div class="text-3xl font-black">{{ danhSachGoiTin.filter(x => x.trang_thai === 'active').length }}</div>
        </div>
        <div class="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3l14 9-14 9V3z"/></svg>
        </div>
      </div>
      <div class="bg-white rounded-2xl p-5 shadow-sm border border-red-100 flex items-center justify-between">
        <div>
          <p class="text-xs font-bold text-red-400 uppercase tracking-widest mb-1">Đã ngưng</p>
          <div class="text-3xl font-black text-red-500">{{ danhSachGoiTin.filter(x => x.trang_thai === 'inactive').length }}</div>
        </div>
        <div class="w-12 h-12 bg-red-100 rounded-2xl flex items-center justify-center">
          <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="bg-gray-50 border-b border-gray-100">
              <th class="text-left text-xs font-bold text-gray-400 uppercase tracking-widest px-5 py-4">Thông tin gói</th>
              <th class="text-right text-xs font-bold text-gray-400 uppercase tracking-widest px-5 py-4">Giá tiền</th>
              <th class="text-center text-xs font-bold text-gray-400 uppercase tracking-widest px-5 py-4">Số lượng tin</th>
              <th class="text-center text-xs font-bold text-gray-400 uppercase tracking-widest px-5 py-4">Thời hạn</th>
              <th class="text-center text-xs font-bold text-gray-400 uppercase tracking-widest px-5 py-4">Trạng thái</th>
              <th class="text-right text-xs font-bold text-gray-400 uppercase tracking-widest px-5 py-4">Hành động</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-if="danhSachGoiTin.length === 0">
              <td colspan="6" class="text-center py-16">
                <div class="flex flex-col items-center gap-2 text-gray-300">
                  <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                  <p class="text-sm text-gray-400">Chưa có gói tin nào</p>
                </div>
              </td>
            </tr>
            <tr v-for="(value, index) in paginatedList" :key="index" class="hover:bg-gray-50 transition-colors">
              <td class="px-5 py-4">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-600 to-violet-600 flex items-center justify-center flex-shrink-0 shadow-sm">
                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                  </div>
                  <div>
                    <div class="font-bold text-gray-900">{{ value.ten_goi }}</div>
                    <span class="text-xs text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">#PK{{ String(value.id).padStart(3, '0') }}</span>
                  </div>
                </div>
              </td>
              <td class="px-5 py-4 text-right font-black text-blue-600">{{ formatCurrency(value.gia) }}</td>
              <td class="px-5 py-4 text-center">
                <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-50 text-blue-600 text-xs font-bold rounded-full">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                  {{ value.so_luong_tin }} tin
                </span>
              </td>
              <td class="px-5 py-4 text-center text-gray-500">
                <span class="inline-flex items-center gap-1">
                  <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                  {{ value.so_ngay }} ngày
                </span>
              </td>
              <td class="px-5 py-4 text-center">
                <span @click="changeStatus(value, value.trang_thai !== 'active')" class="cursor-pointer inline-flex items-center px-3 py-1 rounded-full text-xs font-bold transition"
                  :class="value.trang_thai === 'active' ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-red-100 text-red-600 hover:bg-red-200'">
                  {{ value.trang_thai === 'active' ? 'Đang hoạt động' : 'Đã khóa' }}
                </span>
              </td>
              <td class="px-5 py-4 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button @click="Object.assign(goi_tin_update, value); showUpdateModal = true"
                    class="w-9 h-9 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center hover:bg-blue-600 hover:text-white hover:shadow-lg hover:shadow-blue-100 transition-all active:scale-90" title="Cập nhật">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                  </button>
                  <button @click="id_can_xoa = value.id; showDeleteModal = true"
                    class="w-9 h-9 bg-red-50 text-red-500 rounded-xl flex items-center justify-center hover:bg-red-500 hover:text-white hover:shadow-lg hover:shadow-red-100 transition-all active:scale-90" title="Xóa">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="px-5 py-3 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3">
        <p class="text-xs text-gray-400">{{ paginationInfo }}</p>
        <div v-if="totalPages > 1" class="flex items-center gap-1">
          <button @click="prevPage" :disabled="currentPage === 1"
            class="w-8 h-8 rounded-lg border border-gray-200 text-gray-500 flex items-center justify-center hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition text-sm">&laquo;</button>
          <button v-for="page in visiblePages" :key="page" @click="goToPage(page)"
            class="w-8 h-8 rounded-lg text-sm font-bold transition"
            :class="page === currentPage ? 'bg-blue-600 text-white shadow-sm' : 'border border-gray-200 text-gray-500 hover:bg-gray-50'">{{ page }}</button>
          <button @click="nextPage" :disabled="currentPage === totalPages"
            class="w-8 h-8 rounded-lg border border-gray-200 text-gray-500 flex items-center justify-center hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition text-sm">&raquo;</button>
        </div>
      </div>
    </div>

    <!-- Create Modal -->
    <Teleport to="body">
      <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
          <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 bg-blue-100 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
              </div>
              <h3 class="font-black text-gray-900">Thêm gói tin mới</h3>
            </div>
            <button @click="showCreateModal = false" class="w-8 h-8 rounded-lg bg-gray-100 text-gray-400 hover:text-gray-600 flex items-center justify-center transition">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>
          <div class="p-6 space-y-4">
            <div>
              <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">Tên gói tin <span class="text-red-500">*</span></label>
              <input v-model="goi_tin_create.ten_goi" type="text" placeholder="VD: Gói VIP 1"
                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">Giá tiền (VNĐ)</label>
                <input v-model="goi_tin_create.gia" type="number" placeholder="0"
                  class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
              </div>
              <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">Số lượng tin</label>
                <input v-model="goi_tin_create.so_luong_tin" type="number" placeholder="0"
                  class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
              </div>
            </div>
            <div>
              <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">Thời hạn (Ngày)</label>
              <input v-model="goi_tin_create.so_ngay" type="number" placeholder="30"
                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
            </div>
          </div>
          <div class="flex gap-3 p-6 pt-0">
            <button @click="showCreateModal = false" class="flex-1 py-3 border-2 border-gray-200 text-gray-500 font-bold rounded-xl hover:bg-gray-50 transition text-sm">Hủy bỏ</button>
            <button @click="themMoiGoiTin(); showCreateModal = false" class="flex-[2] py-3 bg-gradient-to-r from-blue-600 to-indigo-700 text-white font-bold rounded-2xl shadow-lg shadow-blue-100 hover:-translate-y-1 hover:shadow-xl transition-all active:scale-95 text-sm">Lưu gói tin</button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Update Modal -->
    <Teleport to="body">
      <div v-if="showUpdateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
          <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 bg-amber-100 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
              </div>
              <h3 class="font-black text-gray-900">Cập nhật gói tin</h3>
            </div>
            <button @click="showUpdateModal = false" class="w-8 h-8 rounded-lg bg-gray-100 text-gray-400 hover:text-gray-600 flex items-center justify-center transition">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>
          <div class="p-6 space-y-4">
            <div>
              <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">Tên gói tin</label>
              <input v-model="goi_tin_update.ten_goi" type="text"
                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition" />
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">Giá tiền (VNĐ)</label>
                <input v-model="goi_tin_update.gia" type="number"
                  class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition" />
              </div>
              <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">Số lượng tin</label>
                <input v-model="goi_tin_update.so_luong_tin" type="number"
                  class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition" />
              </div>
            </div>
            <div>
              <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">Thời hạn (Ngày)</label>
              <input v-model="goi_tin_update.so_ngay" type="number"
                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition" />
            </div>
          </div>
          <div class="flex gap-3 p-6 pt-0">
            <button @click="showUpdateModal = false" class="flex-1 py-3 border-2 border-gray-200 text-gray-500 font-bold rounded-xl hover:bg-gray-50 transition text-sm">Hủy</button>
            <button @click="capNhatGoiTin(); showUpdateModal = false" class="flex-1 py-3 bg-gradient-to-r from-amber-500 to-amber-600 text-white font-bold rounded-xl shadow-sm hover:-translate-y-0.5 transition-all text-sm">Lưu thay đổi</button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Delete Modal -->
    <Teleport to="body">
      <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden text-center">
          <div class="p-8">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </div>
            <h3 class="text-xl font-black text-gray-900 mb-2">Xóa gói tin?</h3>
            <p class="text-sm text-gray-400 mb-6">Hành động này không thể hoàn tác. Bạn có chắc chắn muốn xóa gói tin này?</p>
            <div class="flex gap-3">
              <button @click="showDeleteModal = false" class="flex-1 py-3 border-2 border-gray-200 text-gray-500 font-bold rounded-xl hover:bg-gray-50 transition text-sm">Hủy</button>
              <button @click="xoaGoiTin(); showDeleteModal = false" class="flex-1 py-3 bg-gradient-to-r from-red-500 to-red-600 text-white font-bold rounded-xl shadow-sm hover:-translate-y-0.5 transition-all text-sm">Xóa ngay</button>
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
      danhSachGoiTin: [],
      id_can_xoa: "",
      goi_tin_create: {},
      goi_tin_update: {},
      showCreateModal: false,
      showUpdateModal: false,
      showDeleteModal: false,
      currentPage: 1,
      itemsPerPage: 5,
      isLoading: false,
    };
  },
  computed: {
    // ✅ Danh sách đã phân trang
    paginatedList() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      const end = start + this.itemsPerPage;
      return this.danhSachGoiTin.slice(start, end); // ✅ Dùng danhSachGoiTin
    },

    // ✅ Tổng số trang
    totalPages() {
      return Math.ceil(this.danhSachGoiTin.length / this.itemsPerPage) || 1;
    },

    // ✅ Các số trang hiển thị (1, 2, 3, 4, 5)
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
      const total = this.danhSachGoiTin.length;
      if (total === 0) return "Hiển thị 0 gói tin";
      const start = (this.currentPage - 1) * this.itemsPerPage + 1;
      const end = Math.min(this.currentPage * this.itemsPerPage, total);
      return `Hiển thị ${start} - ${end} của ${total} gói tin`;
    },
  },

  mounted() {
    this.loadGoiTin();
  },

  methods: {
    // Chuyển trang
    goToPage(page) {
    if (page < 1 || page > this.totalPages || page === this.currentPage) return;
    this.currentPage = page;
    
    // ✅ Scroll lên đầu table (dùng class có thật trong component này)
    this.$nextTick(() => {
      document.querySelector('.table-responsive')?.scrollIntoView({ behavior: 'smooth' });
    });
  },

    // Trang trước / sau
    prevPage() { this.goToPage(this.currentPage - 1); },
  nextPage() { this.goToPage(this.currentPage + 1); },
  
  // ✅ Reset page về 1 (dùng khi có search/filter sau này)
  resetPagination() { this.currentPage = 1; },
    loadGoiTin(page = 1) {
      this.isLoading = true;

      axios
        .get("/admin/goi-tin/data")
        .then((res) => {
          const data = res.data.data;
          this.danhSachGoiTin = data.data || data;
        })
        .catch(() => {
          toaster.error("Lỗi load dữ liệu");
        })
        .finally(() => {
          this.isLoading = false;
        });
    },

    formatCurrency(value) {
      if (!value) return "0 đ";
      return new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND",
      }).format(value);
    },

    // ✅ THÊM MỚI GÓI TIN
    themMoiGoiTin() {
      axios
        .post(
          "/admin/goi-tin/create",
          this.goi_tin_create
        )
        .then((response) => {
          if (
            response.data.success ||
            response.status === 200 ||
            response.status === 201
          ) {
            const message = response.data.message || "Thêm gói tin thành công!";
            this.$toast.success(
              `<span class="text-nowrap"><b>${message}</b></span>`
            );
            this.loadGoiTin();
            this.goi_tin_create = {};
          } else {
            this.$toast.error(
              `<span class="text-nowrap">Thất Bại <b>${
                response.data.message || "Có lỗi xảy ra"
              }</b></span>`
            );
          }
        })
        .catch((error) => {
          console.error("Lỗi khi thêm mới Gói Tin:", error);
          if (
            error.response &&
            error.response.data &&
            error.response.data.errors
          ) {
            const errors = error.response.data.errors;
            const items = Object.values(errors)
              .flat()
              .map((msg) => `<li>${msg}</li>`)
              .join("");
            const messages = `<span class="text-nowrap">Vui lòng kiểm tra lại:<b>${items}</b></span>`;
            this.$toast.error(messages);
          } else {
            this.$toast.error("Đã xảy ra lỗi khi thêm mới Gói Tin.");
          }
        });
    },

    // ✅ CẬP NHẬT GÓI TIN
    capNhatGoiTin() {
      axios
        .put(
          "/admin/goi-tin/update",
          this.goi_tin_update
        )
        .then((response) => {
          if (
            response.data.success ||
            response.status === 200 ||
            response.status === 201
          ) {
            const message =
              response.data.message || "Cập nhật gói tin thành công!";

            this.$toast.success(
              `<span class="text-nowrap"><b>${message}</b></span>`
            );
            this.loadGoiTin();
          } else {
            this.$toast.error(
              `<span class="text-nowrap">Thất Bại <b>${
                response.data.message || "Có lỗi xảy ra"
              }</b></span>`
            );
          }
        })
        .catch((error) => {
          console.error("Lỗi khi cập nhật Gói Tin:", error);
          if (
            error.response &&
            error.response.data &&
            error.response.data.errors
          ) {
            const errors = error.response.data.errors;
            const items = Object.values(errors)
              .flat()
              .map((msg) => `<li>${msg}</li>`)
              .join("");
            const messages = `<span class="text-nowrap">Vui lòng kiểm tra lại:<b>${items}</b></span>`;
            this.$toast.error(messages);
          } else {
            this.$toast.error("Đã xảy ra lỗi khi cập nhật Gói Tin");
          }
        });
    },

    // ✅ XÓA GÓI TIN
    xoaGoiTin() {
      axios
        .delete(`/admin/goi-tin/delete/${this.id_can_xoa}`)
        .then((response) => {
          if (
            response.data.success ||
            response.status === 200 ||
            response.status === 201
          ) {
            const message = response.data.message || "Xóa gói tin thành công!";
            this.$toast.success(
              `<span class="text-nowrap"><b>${message}</b></span>`
            );
            this.loadGoiTin();
          } else {
            this.$toast.error(
              `<span class="text-nowrap">Thất Bại <b>${
                response.data.message || "Có lỗi xảy ra"
              }</b></span>`
            );
          }
        })
        .catch((error) => {
          console.error("Lỗi khi xóa Gói Tin:", error);
          this.$toast.error("Đã xảy ra lỗi khi xóa Gói Tin.");
        });
    },

    //  Lấy token
    getToken() {
      return (
        localStorage.getItem("token") || localStorage.getItem("admin_auth_token")
      );
    },

    async toggleStatus(item) {
      // Đảo trạng thái: 'active' ↔ 'inactive'
      const newStatus = item.trang_thai === "active" ? "inactive" : "active";

      try {
        const token = this.getToken();
        const res = await axios.post(
          '/admin/goi-tin/change-status',
          { id: item.id, trang_thai: newStatus }
        );

        if (res.data?.status) {
          // ✅ Cập nhật UI ngay
          item.trang_thai = newStatus;
          this.$toast.success("Cập nhật trạng thái thành công!");
        }
      } catch (err) {
        this.$toast.error(err.response?.data?.message || "Lỗi cập nhật trạng thái!");
      }
    },

    // THAY ĐỔI TRẠNG THÁI GÓI TIN
    async changeStatus(item, newStatus) {
      try {
        const token = this.getToken();
        const res = await axios.post(
          '/admin/goi-tin/change-status',
          { id: item.id, trang_thai: newStatus ? 'active' : 'inactive' }
        );

        if (res.data?.status) {
          item.trang_thai = newStatus ? "active" : "inactive";
          this.$toast.success("Cập nhật trạng thái thành công!");
        }
      } catch (err) {
        this.$toast.error(err.response?.data?.message || "Lỗi cập nhật trạng thái!");
      }
    },
  },
};
</script>
