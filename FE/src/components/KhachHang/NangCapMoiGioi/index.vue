<template>
  <div class="upgrade-page">

    <!-- ═══ HERO ═══ -->
    <section class="hero-section">
      <div class="hero-bg-img"></div>
      <div class="hero-overlay"></div>
      <div class="hero-content">
        <span class="hero-badge">✨ Nâng cấp tài khoản</span>
        <h1 class="hero-title">
          Trở thành <span class="hero-title-highlight">Môi Giới</span><br/>Chuyên Nghiệp
        </h1>
        <p class="hero-subtitle">
          Mua gói tin để đăng bất động sản, tiếp cận hàng nghìn khách hàng tiềm năng trên toàn quốc.
        </p>
        <div class="hero-stats">
          <div class="hero-stat"><span class="stat-num">10K+</span><span class="stat-label">Khách hàng</span></div>
          <div class="hero-divider"></div>
          <div class="hero-stat"><span class="stat-num">5K+</span><span class="stat-label">BĐS đăng thành công</span></div>
          <div class="hero-divider"></div>
          <div class="hero-stat"><span class="stat-num">98%</span><span class="stat-label">Hài lòng</span></div>
        </div>
      </div>
    </section>

    <div class="page-body">

      <!-- ═══ BENEFITS ═══ -->
      <section class="benefits-section">
        <div class="benefit-card" v-for="b in benefits" :key="b.title">
          <div class="benefit-icon" :style="{ background: b.bg }">
            <span>{{ b.icon }}</span>
          </div>
          <div>
            <h3 class="benefit-title">{{ b.title }}</h3>
            <p class="benefit-desc">{{ b.desc }}</p>
          </div>
        </div>
      </section>

      <!-- ═══ SECTION TITLE ═══ -->
      <div class="section-header">
        <div class="section-tag">Bảng giá</div>
        <h2 class="section-title">Chọn gói phù hợp với bạn</h2>
        <p class="section-sub">Tất cả gói đều bao gồm quyền truy cập đầy đủ vào nền tảng quản lý môi giới</p>
      </div>

      <!-- ═══ LOADING ═══ -->
      <div v-if="loading" class="pkg-grid">
        <div v-for="i in 3" :key="i" class="pkg-skeleton">
          <div class="skel skel-title"></div>
          <div class="skel skel-price"></div>
          <div class="skel skel-line"></div>
          <div class="skel skel-line short"></div>
          <div class="skel skel-line"></div>
          <div class="skel skel-btn"></div>
        </div>
      </div>

      <!-- ═══ EMPTY ═══ -->
      <div v-else-if="packages.length === 0" class="empty-state">
        <div class="empty-icon">📭</div>
        <h4>Chưa có gói tin nào</h4>
        <p>Vui lòng quay lại sau.</p>
      </div>

      <!-- ═══ PACKAGES ═══ -->
      <div v-else class="pkg-grid">
        <div
          v-for="pkg in packages" :key="pkg.id"
          class="pkg-card"
          :class="{ popular: pkg.uu_tien_hien_thi, selected: selectedPkg?.id === pkg.id }"
        >
          <!-- Popular ribbon -->
          <div v-if="pkg.uu_tien_hien_thi" class="popular-ribbon">🔥 Phổ biến nhất</div>

          <!-- Card top -->
          <div class="pkg-header">
            <div class="pkg-icon">{{ getPackageIcon(pkg) }}</div>
            <h3 class="pkg-name">{{ pkg.ten_goi }}</h3>
            <p class="pkg-desc">{{ pkg.mo_ta || 'Gói tin đăng bất động sản' }}</p>
            <div class="pkg-price">
              <span class="price-main">{{ formatCurrencyShort(pkg.gia) }}</span>
              <span class="price-sub">{{ formatCurrency(pkg.gia) }}</span>
            </div>
          </div>

          <!-- Features -->
          <ul class="pkg-features">
            <li>
              <span class="feat-check">✓</span>
              <span><strong>{{ pkg.so_luong_tin }} tin đăng</strong> bất động sản</span>
            </li>
            <li>
              <span class="feat-check">✓</span>
              <span>Hiệu lực <strong>{{ pkg.so_ngay }} ngày</strong></span>
            </li>
            <li>
              <span class="feat-check">✓</span>
              <span>Hiển thị thông tin liên hệ</span>
            </li>
            <li>
              <span class="feat-check">✓</span>
              <span>Quản lý danh sách khách hàng</span>
            </li>
            <li>
              <span class="feat-check">✓</span>
              <span>Thống kê & báo cáo chi tiết</span>
            </li>
            <li>
              <span class="feat-check">✓</span>
              <span>Hỗ trợ ưu tiên 24/7</span>
            </li>
          </ul>

          <!-- CTA -->
          <button class="pkg-btn" :class="{ 'pkg-btn-popular': pkg.uu_tien_hien_thi }" @click="selectPackage(pkg)">
            <span>Chọn gói này</span>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </button>
        </div>
      </div>

      <!-- ═══ TRUST SECTION ═══ -->
      <section class="trust-section">
        <div class="trust-item" v-for="t in trustItems" :key="t.label">
          <span class="trust-icon">{{ t.icon }}</span>
          <div>
            <div class="trust-title">{{ t.label }}</div>
            <div class="trust-desc">{{ t.desc }}</div>
          </div>
        </div>
      </section>

    </div>

    <!-- ═══ CONFIRM MODAL ═══ -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
          <div class="modal-card">

            <!-- Close -->
            <button class="modal-close" @click="showModal = false">✕</button>

            <!-- Header -->
            <div class="modal-header">
              <div class="modal-pkg-icon">{{ getPackageIcon(selectedPkg) }}</div>
              <h3 class="modal-title">Xác nhận mua gói</h3>
              <p class="modal-subtitle">{{ selectedPkg?.ten_goi }}</p>
            </div>

            <!-- Summary -->
            <div class="modal-summary">
              <div class="summary-row">
                <span>Gói tin</span>
                <strong>{{ selectedPkg?.ten_goi }}</strong>
              </div>
              <div class="summary-row">
                <span>Số tin đăng</span>
                <strong>{{ selectedPkg?.so_luong_tin }} tin</strong>
              </div>
              <div class="summary-row">
                <span>Thời hạn</span>
                <strong>{{ selectedPkg?.so_ngay }} ngày</strong>
              </div>
              <div class="summary-row total">
                <span>Tổng thanh toán</span>
                <strong class="total-price">{{ formatCurrency(selectedPkg?.gia) }}</strong>
              </div>
            </div>

            <!-- Info note -->
            <div class="modal-note">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
              Sau khi xác nhận, tài khoản sẽ được nâng cấp lên <strong>Môi Giới</strong> và nhận ngay quyền đăng tin bất động sản.
            </div>

            <!-- Actions -->
            <div class="modal-actions">
              <button class="modal-btn-cancel" @click="showModal = false">Hủy</button>
              <button class="modal-btn-confirm" :class="{ loading: buying }" @click="confirmBuy" :disabled="buying">
                <span v-if="buying" class="spin-icon"></span>
                <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
                {{ buying ? 'Đang xử lý...' : 'Xác nhận mua gói' }}
              </button>
            </div>

          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- ═══ SUCCESS OVERLAY ═══ -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showSuccess" class="modal-overlay">
          <div class="success-card">
            <div class="success-icon-wrap">
              <svg viewBox="0 0 52 52" class="checkmark-svg">
                <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
                <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
              </svg>
            </div>
            <h3>Đăng ký thành công!</h3>
            <p>Tài khoản của bạn đã được nâng cấp lên <strong>Môi Giới</strong>. Hãy đăng nhập với tài khoản môi giới để bắt đầu.</p>
            <button class="success-btn" @click="goToBrokerLogin">Đến trang đăng nhập Môi Giới →</button>
          </div>
        </div>
      </Transition>
    </Teleport>

  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import api from "@/axios/config";
import { useRouter } from "vue-router";

const router = useRouter();
const loading  = ref(false);
const packages = ref([]);
const selectedPkg = ref(null);
const showModal   = ref(false);
const showSuccess = ref(false);
const buying      = ref(false);

const benefits = [
  { icon: "🏠", title: "Đăng tin bất động sản", desc: "Đăng nhiều tin BĐS theo gói, tiếp cận khách hàng rộng rãi.", bg: "#eff6ff" },
  { icon: "📊", title: "Thống kê chi tiết", desc: "Theo dõi lượt xem, yêu thích và tương tác theo thời gian thực.", bg: "#f0fdf4" },
  { icon: "👥", title: "Quản lý khách hàng", desc: "Lưu trữ và quản lý danh sách khách hàng tiềm năng dễ dàng.", bg: "#fdf4ff" },
  { icon: "💬", title: "Chat trực tiếp", desc: "Nhận tin nhắn từ khách hàng quan tâm ngay trên nền tảng.", bg: "#fff7ed" },
];

const trustItems = [
  { icon: "🔒", label: "Thanh toán bảo mật", desc: "Mã hoá SSL 256-bit" },
  { icon: "⚡", label: "Kích hoạt tức thì", desc: "Ngay sau khi thanh toán" },
  { icon: "🔄", label: "Gia hạn dễ dàng", desc: "Mua thêm gói bất cứ lúc nào" },
  { icon: "🎧", label: "Hỗ trợ 24/7", desc: "Đội ngũ luôn sẵn sàng giúp đỡ" },
];

const packageIcons = ["🥉", "🥈", "🥇", "💎"];

const getPackageIcon = (pkg) => {
  if (!pkg) return "📦";
  const idx = packages.value.findIndex(p => p.id === pkg.id);
  return packageIcons[idx] ?? "💎";
};

const fetchPackages = async () => {
  loading.value = true;
  try {
    const res = await api.get("/goi-tin/data");
    const data = res.data?.data;
    packages.value = Array.isArray(data) ? data : data?.data || [];
  } catch (e) {
    console.error("Lỗi tải gói tin:", e);
  } finally {
    loading.value = false;
  }
};

const selectPackage = (pkg) => {
  selectedPkg.value = pkg;
  showModal.value = true;
};

const confirmBuy = async () => {
  if (!selectedPkg.value) return;
  buying.value = true;
  try {
    const res = await api.post("/khach-hang/mua-goi", { goi_tin_id: selectedPkg.value.id });
    showModal.value = false;
    if (res.data?.status) {
      showSuccess.value = true;
    }
  } catch (e) {
    alert(e.response?.data?.message || "Có lỗi xảy ra, vui lòng thử lại.");
  } finally {
    buying.value = false;
  }
};

const goToBrokerLogin = () => {
  showSuccess.value = false;
  router.push("/moi-gioi/dang-nhap");
};

const formatCurrency = (val) =>
  new Intl.NumberFormat("vi-VN", { style: "currency", currency: "VND" }).format(val || 0);

const formatCurrencyShort = (val) => {
  if (!val) return "0đ";
  if (val >= 1_000_000) return (val / 1_000_000).toLocaleString("vi-VN") + "M đ";
  if (val >= 1_000) return (val / 1_000).toLocaleString("vi-VN") + "K đ";
  return val.toLocaleString("vi-VN") + "đ";
};

onMounted(() => fetchPackages());
</script>

<style scoped>
/* ═══ BASE ═══ */
.upgrade-page { background: #f1f5f9; font-family: 'Inter', sans-serif; min-height: 100vh; }

/* ═══ HERO ═══ */
.hero-section {
  position: relative;
  background: linear-gradient(135deg, #0a0e27 0%, #1e3a8a 50%, #1d4ed8 100%);
  padding: 72px 24px 100px;
  text-align: center;
  overflow: hidden;
}
.hero-bg-img {
  position: absolute; inset: 0;
  background-image: url('https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=1600&q=60');
  background-size: cover; background-position: center;
  opacity: 0.08;
}
.hero-overlay {
  position: absolute; bottom: 0; left: 0; right: 0; height: 80px;
  background: #f1f5f9;
  clip-path: ellipse(60% 100% at 50% 100%);
}
.hero-content { position: relative; z-index: 1; max-width: 680px; margin: 0 auto; }
.hero-badge {
  display: inline-block;
  padding: 6px 18px;
  background: rgba(255,255,255,0.12);
  border: 1px solid rgba(255,255,255,0.2);
  border-radius: 999px;
  color: #e0f2fe;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  margin-bottom: 20px;
}
.hero-title {
  font-size: 46px;
  font-weight: 900;
  color: #fff;
  line-height: 1.15;
  margin-bottom: 16px;
}
.hero-title-highlight {
  background: linear-gradient(90deg, #fbbf24, #f59e0b);
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
}
.hero-subtitle { color: #bfdbfe; font-size: 17px; line-height: 1.7; margin-bottom: 36px; }
.hero-stats {
  display: inline-flex;
  align-items: center;
  gap: 28px;
  background: rgba(255,255,255,0.1);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(255,255,255,0.15);
  border-radius: 16px;
  padding: 16px 28px;
}
.hero-stat { text-align: center; }
.stat-num { display: block; font-size: 22px; font-weight: 800; color: #fff; }
.stat-label { font-size: 11px; color: #93c5fd; font-weight: 500; }
.hero-divider { width: 1px; height: 36px; background: rgba(255,255,255,0.2); }

/* ═══ PAGE BODY ═══ */
.page-body { max-width: 1200px; margin: 0 auto; padding: 0 24px 64px; }

/* ═══ BENEFITS ═══ */
.benefits-section {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
  margin: -40px 0 56px;
  position: relative;
  z-index: 2;
}
.benefit-card {
  background: white;
  border-radius: 16px;
  padding: 20px;
  display: flex;
  gap: 14px;
  align-items: flex-start;
  box-shadow: 0 4px 16px rgba(0,0,0,0.07);
  border: 1px solid #f1f5f9;
  transition: transform 0.2s, box-shadow 0.2s;
}
.benefit-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.1); }
.benefit-icon {
  width: 44px; height: 44px; border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  font-size: 22px; flex-shrink: 0;
}
.benefit-title { font-size: 14px; font-weight: 700; color: #1e293b; margin-bottom: 4px; }
.benefit-desc { font-size: 12px; color: #64748b; line-height: 1.5; }

/* ═══ SECTION HEADER ═══ */
.section-header { text-align: center; margin-bottom: 40px; }
.section-tag {
  display: inline-block;
  padding: 4px 14px;
  background: #eff6ff;
  color: #3b82f6;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  margin-bottom: 12px;
}
.section-title { font-size: 32px; font-weight: 800; color: #0f172a; margin-bottom: 8px; }
.section-sub { color: #64748b; font-size: 15px; }

/* ═══ PACKAGES GRID ═══ */
.pkg-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-bottom: 56px; }

/* ═══ PACKAGE CARD ═══ */
.pkg-card {
  background: white;
  border-radius: 20px;
  border: 2px solid #e2e8f0;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  position: relative;
  transition: transform 0.25s, box-shadow 0.25s, border-color 0.25s;
}
.pkg-card:hover { transform: translateY(-4px); box-shadow: 0 16px 40px rgba(0,0,0,0.1); border-color: #94a3b8; }
.pkg-card.popular {
  border-color: #f59e0b;
  box-shadow: 0 8px 32px rgba(245,158,11,0.2);
}
.pkg-card.popular:hover { box-shadow: 0 16px 40px rgba(245,158,11,0.3); }
.pkg-card.selected { border-color: #3b82f6; box-shadow: 0 0 0 4px rgba(59,130,246,0.15); }

.popular-ribbon {
  background: linear-gradient(90deg, #f59e0b, #f97316);
  color: white;
  text-align: center;
  font-size: 12px;
  font-weight: 800;
  padding: 8px;
  letter-spacing: 0.05em;
}

.pkg-header { padding: 28px 28px 0; }
.pkg-icon { font-size: 36px; margin-bottom: 12px; }
.pkg-name { font-size: 20px; font-weight: 800; color: #0f172a; margin-bottom: 4px; }
.pkg-desc { font-size: 13px; color: #64748b; margin-bottom: 20px; line-height: 1.5; }
.pkg-price { margin-bottom: 4px; }
.price-main { font-size: 38px; font-weight: 900; color: #0f172a; display: block; }
.price-sub { font-size: 12px; color: #94a3b8; margin-top: 2px; display: block; }

.pkg-features {
  list-style: none;
  padding: 20px 28px;
  margin: 0;
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 11px;
}
.pkg-features li {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 13.5px;
  color: #475569;
}
.feat-check {
  width: 20px; height: 20px;
  background: #dcfce7;
  color: #16a34a;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 11px; font-weight: 800;
  flex-shrink: 0;
}

.pkg-btn {
  margin: 4px 28px 28px;
  padding: 14px;
  border-radius: 14px;
  border: none;
  background: linear-gradient(135deg, #1d4ed8, #3b82f6);
  color: white;
  font-size: 15px;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: all 0.2s;
}
.pkg-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(59,130,246,0.4); }
.pkg-btn-popular { background: linear-gradient(135deg, #f59e0b, #f97316); }
.pkg-btn-popular:hover { box-shadow: 0 6px 20px rgba(245,158,11,0.4); }

/* ═══ SKELETON ═══ */
.pkg-skeleton { background: white; border-radius: 20px; border: 2px solid #e2e8f0; padding: 28px; }
.skel { background: #f1f5f9; border-radius: 8px; animation: pulse 1.5s infinite; }
.skel-title { height: 20px; width: 60%; margin-bottom: 16px; }
.skel-price { height: 40px; width: 50%; margin-bottom: 24px; }
.skel-line { height: 12px; margin-bottom: 12px; }
.skel-line.short { width: 70%; }
.skel-btn { height: 48px; margin-top: 20px; border-radius: 14px; }
@keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: 0.5; } }

/* ═══ EMPTY ═══ */
.empty-state { text-align: center; padding: 60px 20px; }
.empty-icon { font-size: 56px; margin-bottom: 16px; }
.empty-state h4 { font-size: 18px; font-weight: 700; color: #1e293b; margin-bottom: 6px; }
.empty-state p { color: #64748b; }

/* ═══ TRUST SECTION ═══ */
.trust-section {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
  padding: 28px;
  background: white;
  border-radius: 20px;
  border: 1px solid #f1f5f9;
}
.trust-item { display: flex; align-items: center; gap: 12px; }
.trust-icon { font-size: 28px; }
.trust-title { font-size: 13px; font-weight: 700; color: #1e293b; }
.trust-desc { font-size: 12px; color: #64748b; }

/* ═══ MODAL ═══ */
.modal-overlay {
  position: fixed; inset: 0; z-index: 999;
  background: rgba(15,23,42,0.6);
  backdrop-filter: blur(6px);
  display: flex; align-items: center; justify-content: center;
  padding: 20px;
}
.modal-card {
  background: white;
  border-radius: 24px;
  width: 100%; max-width: 440px;
  overflow: hidden;
  position: relative;
  box-shadow: 0 24px 64px rgba(0,0,0,0.2);
}
.modal-close {
  position: absolute; top: 16px; right: 16px;
  width: 32px; height: 32px;
  border-radius: 50%; border: none;
  background: #f1f5f9; color: #64748b;
  font-size: 14px; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  transition: background 0.15s;
  z-index: 1;
}
.modal-close:hover { background: #e2e8f0; }
.modal-header {
  background: linear-gradient(135deg, #1e3a8a, #1d4ed8);
  padding: 32px 28px 24px;
  text-align: center;
  color: white;
}
.modal-pkg-icon { font-size: 42px; margin-bottom: 10px; }
.modal-title { font-size: 22px; font-weight: 800; margin-bottom: 4px; }
.modal-subtitle { color: #bfdbfe; font-size: 14px; }

.modal-summary { padding: 20px 28px 0; }
.summary-row {
  display: flex; justify-content: space-between; align-items: center;
  padding: 11px 0;
  border-bottom: 1px solid #f1f5f9;
  font-size: 14px;
  color: #475569;
}
.summary-row:last-child { border-bottom: none; }
.summary-row strong { color: #1e293b; font-weight: 600; }
.summary-row.total { padding-top: 14px; border-top: 2px solid #e2e8f0; border-bottom: none; }
.total-price { font-size: 22px; font-weight: 900; color: #2563eb !important; }

.modal-note {
  margin: 16px 28px;
  padding: 12px 16px;
  background: #eff6ff;
  border-radius: 12px;
  font-size: 13px;
  color: #3b82f6;
  display: flex;
  gap: 8px;
  align-items: flex-start;
  line-height: 1.5;
}
.modal-note svg { flex-shrink: 0; margin-top: 1px; }

.modal-actions {
  padding: 16px 28px 28px;
  display: flex;
  gap: 12px;
}
.modal-btn-cancel {
  flex: 1; padding: 14px;
  border: 2px solid #e2e8f0; background: white;
  border-radius: 14px; font-weight: 700; font-size: 14px;
  color: #64748b; cursor: pointer; transition: all 0.15s;
}
.modal-btn-cancel:hover { background: #f8fafc; border-color: #cbd5e1; }
.modal-btn-confirm {
  flex: 2; padding: 14px;
  background: linear-gradient(135deg, #1d4ed8, #3b82f6);
  color: white; border: none; border-radius: 14px;
  font-weight: 700; font-size: 14px; cursor: pointer;
  display: flex; align-items: center; justify-content: center; gap: 8px;
  transition: all 0.2s;
  box-shadow: 0 4px 16px rgba(59,130,246,0.3);
}
.modal-btn-confirm:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(59,130,246,0.4); }
.modal-btn-confirm:disabled { opacity: 0.6; cursor: not-allowed; }
.spin-icon {
  width: 16px; height: 16px;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ═══ SUCCESS CARD ═══ */
.success-card {
  background: white;
  border-radius: 24px;
  padding: 48px 36px;
  text-align: center;
  max-width: 400px;
  width: 100%;
  box-shadow: 0 24px 64px rgba(0,0,0,0.2);
}
.success-icon-wrap {
  width: 80px; height: 80px;
  margin: 0 auto 20px;
}
.checkmark-svg { width: 80px; height: 80px; }
.checkmark-circle {
  stroke: #10b981; stroke-width: 2;
  stroke-dasharray: 166; stroke-dashoffset: 166;
  animation: stroke 0.6s cubic-bezier(0.65,0,0.45,1) forwards;
}
.checkmark-check {
  stroke: #10b981; stroke-width: 2;
  stroke-dasharray: 48; stroke-dashoffset: 48;
  animation: stroke 0.4s cubic-bezier(0.65,0,0.45,1) 0.5s forwards;
}
@keyframes stroke { to { stroke-dashoffset: 0; } }
.success-card h3 { font-size: 22px; font-weight: 800; color: #0f172a; margin-bottom: 10px; }
.success-card p { color: #64748b; font-size: 14px; line-height: 1.7; margin-bottom: 24px; }
.success-btn {
  background: linear-gradient(135deg, #065f46, #10b981);
  color: white; border: none;
  padding: 14px 28px; border-radius: 14px;
  font-weight: 700; font-size: 14px; cursor: pointer;
  transition: all 0.2s;
}
.success-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(16,185,129,0.3); }

/* ═══ MODAL TRANSITION ═══ */
.modal-enter-active, .modal-leave-active { transition: all 0.25s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; transform: scale(0.95); }

/* ═══ RESPONSIVE ═══ */
@media (max-width: 1024px) {
  .benefits-section { grid-template-columns: repeat(2, 1fr); }
  .pkg-grid { grid-template-columns: repeat(2, 1fr); }
  .trust-section { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 640px) {
  .hero-title { font-size: 30px; }
  .hero-stats { flex-direction: column; gap: 10px; }
  .hero-divider { width: 40px; height: 1px; }
  .benefits-section { grid-template-columns: 1fr; }
  .pkg-grid { grid-template-columns: 1fr; }
  .trust-section { grid-template-columns: 1fr; }
}
</style>
