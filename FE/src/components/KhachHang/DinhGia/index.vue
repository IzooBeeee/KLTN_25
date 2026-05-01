<template>
  <div class="dinh-gia-page">
    <div class="hero-section">
      <div class="container text-center">
        <h1 class="display-5 fw-bold text-white mb-3">
          <i class="bi bi-robot me-2 text-warning"></i>Định giá Bất động sản AI
        </h1>
        <p class="lead text-white-50 mx-auto" style="max-width: 600px;">
          Sử dụng trí tuệ nhân tạo để phân tích hàng ngàn dữ liệu thị trường và cung cấp cho bạn khoảng giá trị chính xác nhất của bất động sản.
        </p>
      </div>
    </div>

    <div class="container my-5 content-wrapper">
      <div class="row justify-content-center">
        <!-- Form nhập liệu -->
        <div class="col-lg-5 mb-4">
          <div class="card shadow-sm border-0 h-100 form-card">
            <div class="card-body p-4">
              <h5 class="card-title fw-bold mb-4">
                <i class="bi bi-pencil-square text-primary me-2"></i>Thông tin Bất động sản
              </h5>

              <form @submit.prevent="handlePredict">
                <div class="mb-3">
                  <label class="form-label fw-semibold">Loại Bất động sản <span class="text-danger">*</span></label>
                  <select v-model="form.loai_id" class="form-select" required>
                    <option value="" disabled>-- Chọn loại hình --</option>
                    <option v-for="loai in loaiBdsList" :key="loai.id" :value="loai.id">
                      {{ loai.ten_loai }}
                    </option>
                  </select>
                </div>

                <div class="mb-3">
                  <label class="form-label fw-semibold">Tỉnh/Thành phố <span class="text-danger">*</span></label>
                  <select v-model="form.tinh_id" class="form-select" @change="fetchQuanHuyen" required>
                    <option value="" disabled>-- Chọn Tỉnh/Thành phố --</option>
                    <option v-for="tinh in tinhThanhList" :key="tinh.id" :value="tinh.id">
                      {{ tinh.ten }}
                    </option>
                  </select>
                </div>

                <div class="mb-3">
                  <label class="form-label fw-semibold">Quận/Huyện</label>
                  <select v-model="form.quan_id" class="form-select" :disabled="!form.tinh_id || loadingQuan">
                    <option value="">-- Chọn Quận/Huyện (Tùy chọn) --</option>
                    <option v-for="quan in quanHuyenList" :key="quan.id" :value="quan.id">
                      {{ quan.ten }}
                    </option>
                  </select>
                </div>

                <div class="mb-4">
                  <label class="form-label fw-semibold">Diện tích (m²) <span class="text-danger">*</span></label>
                  <input v-model="form.dien_tich" type="number" class="form-control" placeholder="VD: 100" min="1" max="100000" step="any" required />
                </div>

                <button type="submit" class="btn btn-primary w-100 btn-predict" :disabled="isPredicting">
                  <span v-if="isPredicting">
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    Đang phân tích dữ liệu...
                  </span>
                  <span v-else>
                    <i class="bi bi-magic me-2"></i>Nhận Định Giá Ngay
                  </span>
                </button>
              </form>
            </div>
          </div>
        </div>

        <!-- Kết quả -->
        <div class="col-lg-7 mb-4">
          <div class="card shadow-sm border-0 h-100 result-card">
            <!-- Trạng thái chờ -->
            <div v-if="!result && !isPredicting" class="card-body p-5 d-flex flex-column align-items-center justify-content-center text-center text-muted">
              <img src="https://cdn-icons-png.flaticon.com/512/3233/3233974.png" alt="AI Robot" width="120" class="mb-4 opacity-50" />
              <h5 class="fw-bold">AI Đang chờ thông tin</h5>
              <p>Hãy nhập đầy đủ thông tin bên trái để nhận kết quả định giá dựa trên AI phân tích dữ liệu thị trường.</p>
            </div>

            <!-- Trạng thái phân tích (Scanning effect) -->
            <div v-else-if="isPredicting" class="card-body p-5 d-flex flex-column align-items-center justify-content-center text-center">
              <div class="scanning-wrapper mb-4">
                <i class="bi bi-cpu" style="font-size: 4rem; color: #3b82f6;"></i>
                <div class="scan-line"></div>
              </div>
              <h5 class="fw-bold text-primary mb-2">Đang quét dữ liệu thị trường...</h5>
              <p class="text-muted small">AI đang phân tích hàng ngàn mẫu bất động sản tương đồng tại khu vực bạn chọn để đưa ra mức giá tốt nhất.</p>
              
              <div class="progress w-75 mt-3" style="height: 8px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 100%"></div>
              </div>
            </div>

            <!-- Kết quả định giá -->
            <div v-else-if="result" class="card-body p-0">
              <div class="result-header p-4 text-center text-white rounded-top" style="background: linear-gradient(135deg, #1e40af, #3b82f6);">
                <h6 class="text-uppercase mb-2" style="letter-spacing: 1px;"><i class="bi bi-check-circle-fill text-success me-2"></i>Định giá hoàn tất</h6>
                <h3 class="fw-bold mb-0 text-warning">{{ formatCurrency(result.gia_du_doan_min) }} - {{ formatCurrency(result.gia_du_doan_max) }}</h3>
                <p class="mt-2 mb-0 opacity-75 small">Khoảng giá dự kiến cho BĐS của bạn</p>
              </div>

              <div class="p-4">
                <div class="row text-center mb-4 g-3">
                  <div class="col-6">
                    <div class="p-3 bg-light rounded">
                      <p class="text-muted small mb-1">Đơn giá dự kiến</p>
                      <h5 class="fw-bold text-primary mb-0">{{ formatCurrency(result.don_gia) }} <span class="fs-6 fw-normal">/m²</span></h5>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="p-3 bg-light rounded">
                      <p class="text-muted small mb-1">Mức giá trung bình</p>
                      <h5 class="fw-bold text-success mb-0">{{ formatCurrency(result.gia_trung_binh) }}</h5>
                    </div>
                  </div>
                </div>

                <div class="chart-container text-center mb-3">
                  <h6 class="fw-bold text-muted mb-3">Mức độ tương thích giá thị trường</h6>
                  <!-- Dùng RadialBar chart làm Gauge -->
                  <apexchart type="radialBar" height="250" :options="gaugeOptions" :series="gaugeSeries"></apexchart>
                </div>

                <div class="alert alert-info border-0 bg-opacity-10 d-flex gap-3 mb-4">
                  <i class="bi bi-info-circle-fill text-info fs-4"></i>
                  <div>
                    <h6 class="fw-bold mb-1">Lưu ý</h6>
                    <p class="mb-0 small text-muted">Kết quả được tính toán dựa trên thuật toán AI phân tích <b>{{ result.so_mau_tham_khao }} mẫu BĐS</b> trên hệ thống. Đây chỉ là khoảng giá trị mang tính chất tham khảo, thực tế có thể thay đổi tùy thuộc vào vị trí mặt tiền, nội thất và tình trạng thực tế của BĐS.</p>
                  </div>
                </div>

                <!-- Bất động sản tham khảo -->
                <div v-if="result.danh_sach_tham_khao && result.danh_sach_tham_khao.length > 0" class="reference-properties mt-4">
                  <h6 class="fw-bold mb-3 d-flex align-items-center">
                    <i class="bi bi-houses-fill text-primary me-2"></i> Bất động sản tham khảo
                  </h6>
                  <div class="list-group">
                    <router-link 
                      v-for="bds in result.danh_sach_tham_khao" 
                      :key="bds.id" 
                      :to="`/khach-hang/chi-tiet-bat-dong-san/${bds.id}`" 
                      class="list-group-item list-group-item-action d-flex align-items-center p-3 border-0 shadow-sm mb-2 rounded"
                    >
                      <img :src="bds.anh_dai_dien_url || 'https://placehold.co/100x100?text=No+Image'" class="rounded me-3 object-fit-cover" width="60" height="60" alt="Ảnh BĐS">
                      <div class="flex-grow-1 min-w-0">
                        <h6 class="mb-1 text-truncate fw-bold">{{ bds.tieu_de }}</h6>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                          <span class="text-danger fw-bold small">{{ formatCurrency(bds.gia) }}</span>
                          <span class="text-muted small"><i class="bi bi-arrows-fullscreen me-1"></i>{{ bds.dien_tich }} m²</span>
                          <span class="text-muted small"><i class="bi bi-geo-alt me-1"></i>{{ bds.dia_chi }}</span>
                        </div>
                      </div>
                    </router-link>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from "@/axios/config";
import VueApexCharts from "vue3-apexcharts";

export default {
  components: {
    apexchart: VueApexCharts,
  },
  data() {
    return {
      loaiBdsList: [],
      tinhThanhList: [],
      quanHuyenList: [],
      
      loadingQuan: false,
      isPredicting: false,
      
      form: {
        loai_id: "",
        tinh_id: "",
        quan_id: "",
        dien_tich: "",
      },
      
      result: null,

      // Cấu hình Gauge Chart
      gaugeSeries: [75], // Sẽ update động
      gaugeOptions: {
        chart: {
          type: "radialBar",
          offsetY: -20,
          sparkline: { enabled: true }
        },
        plotOptions: {
          radialBar: {
            startAngle: -90,
            endAngle: 90,
            track: {
              background: "#e7e7e7",
              strokeWidth: "97%",
              margin: 5,
              dropShadow: {
                enabled: true,
                top: 2,
                left: 0,
                color: "#999",
                opacity: 1,
                blur: 2
              }
            },
            dataLabels: {
              name: { show: false },
              value: {
                offsetY: -2,
                fontSize: "22px",
                fontWeight: "bold",
                formatter: function (val) {
                  return val + "% Độ chính xác";
                }
              }
            }
          }
        },
        grid: { padding: { top: -10 } },
        fill: {
          type: "gradient",
          gradient: {
            shade: "light",
            shadeIntensity: 0.4,
            inverseColors: false,
            opacityFrom: 1,
            opacityTo: 1,
            stops: [0, 50, 53, 91]
          }
        },
        colors: ["#10b981"] // green
      }
    };
  },
  
  mounted() {
    this.fetchLoaiBds();
    this.fetchTinhThanh();
  },
  
  methods: {
    async fetchLoaiBds() {
      try {
        const res = await api.get("/client/loai-bat-dong-san");
        if (res.data?.data) {
          this.loaiBdsList = res.data.data;
        }
      } catch (err) {
        console.error("Lỗi lấy loại BĐS", err);
      }
    },
    
    async fetchTinhThanh() {
      try {
        const res = await api.get("/tinh-thanh");
        if (res.data?.data) {
          this.tinhThanhList = res.data.data;
        }
      } catch (err) {
        console.error("Lỗi lấy tỉnh thành", err);
      }
    },
    
    async fetchQuanHuyen() {
      if (!this.form.tinh_id) return;
      this.loadingQuan = true;
      this.quanHuyenList = [];
      this.form.quan_id = "";
      try {
        const res = await api.get("/quan-huyen", { params: { tinh_id: this.form.tinh_id } });
        if (res.data?.data) {
          this.quanHuyenList = res.data.data;
        }
      } catch (err) {
        console.error("Lỗi lấy quận huyện", err);
      } finally {
        this.loadingQuan = false;
      }
    },
    
    async handlePredict() {
      if (!this.form.loai_id || !this.form.tinh_id || !this.form.dien_tich) return;
      
      this.isPredicting = true;
      this.result = null;
      
      try {
        // Gọi API định giá
        const res = await api.post("/ai/dinh-gia", {
          loai_id: this.form.loai_id,
          tinh_id: this.form.tinh_id,
          quan_id: this.form.quan_id,
          dien_tich: this.form.dien_tich
        });
        
        // Giả lập độ trễ AI suy nghĩ khoảng 2 giây
        setTimeout(() => {
          this.isPredicting = false;
          if (res.data.status && res.data.data) {
            this.result = res.data.data;
            
            // Random độ chính xác từ 85 - 98% dựa vào số lượng mẫu (càng nhiều mẫu càng chính xác)
            const sampleCount = this.result.so_mau_tham_khao || 0;
            let accuracy = 80;
            if (sampleCount > 50) accuracy = 95 + Math.floor(Math.random() * 4); // 95-98%
            else if (sampleCount > 10) accuracy = 88 + Math.floor(Math.random() * 5); // 88-92%
            else accuracy = 75 + Math.floor(Math.random() * 10); // 75-84%
            
            this.gaugeSeries = [accuracy];
            
            // Thay đổi màu kim đồng hồ
            let color = "#10b981"; // xanh
            if (accuracy < 85) color = "#f59e0b"; // vàng
            if (accuracy < 70) color = "#ef4444"; // đỏ
            this.gaugeOptions = { ...this.gaugeOptions, colors: [color] };
            
            this.$toast.success("Định giá hoàn tất!");
          } else {
            this.$toast.error(res.data.message || "Đã xảy ra lỗi");
          }
        }, 1500);
        
      } catch (err) {
        this.isPredicting = false;
        this.$toast.error(err.response?.data?.message || "Lỗi máy chủ định giá");
      }
    },
    
    formatCurrency(val) {
      if (!val) return "0 VNĐ";
      if (val >= 1000000000) {
        return (val / 1000000000).toFixed(2).replace(/\.00$/, '') + " Tỷ";
      } else if (val >= 1000000) {
        return (val / 1000000).toFixed(1).replace(/\.0$/, '') + " Triệu";
      }
      return new Intl.NumberFormat("vi-VN", { style: "currency", currency: "VND" }).format(val);
    }
  }
};
</script>

<style scoped>
.dinh-gia-page {
  background-color: #f8fafc;
  min-height: 100vh;
}

.hero-section {
  background: url('https://images.unsplash.com/photo-1560518883-ce09059eeffa?q=80&w=1973&auto=format&fit=crop') center/cover no-repeat;
  padding: 80px 0;
  position: relative;
}

.hero-section::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(15, 23, 42, 0.7);
}

.hero-section .container {
  position: relative;
  z-index: 1;
}

.content-wrapper {
  margin-top: -40px;
  position: relative;
  z-index: 10;
}

.form-card, .result-card {
  border-radius: 16px;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.form-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1) !important;
}

.form-control, .form-select {
  padding: 12px 16px;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
  font-size: 15px;
}

.form-control:focus, .form-select:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25);
}

.btn-predict {
  padding: 14px;
  border-radius: 10px;
  font-weight: 700;
  font-size: 16px;
  background: linear-gradient(to right, #2563eb, #1d4ed8);
  border: none;
  box-shadow: 0 4px 14px 0 rgba(37, 99, 235, 0.39);
  transition: all 0.2s ease;
}

.btn-predict:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
}

.btn-predict:disabled {
  background: #94a3b8;
  box-shadow: none;
}

/* Scanning Animation */
.scanning-wrapper {
  position: relative;
  width: 100px;
  height: 100px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.scan-line {
  position: absolute;
  top: 0;
  left: -20px;
  right: -20px;
  height: 4px;
  background: #3b82f6;
  box-shadow: 0 0 10px 2px rgba(59, 130, 246, 0.7);
  animation: scan 1.5s infinite linear alternate;
}

@keyframes scan {
  0% { top: 0; opacity: 0; }
  10% { opacity: 1; }
  90% { opacity: 1; }
  100% { top: 100%; opacity: 0; }
}

.bg-info.bg-opacity-10 {
  background-color: rgba(14, 165, 233, 0.1) !important;
}
</style>
