<template>
  <div class="tv-page">

    <!-- Hero -->
    <div class="tv-hero">
      <div class="tv-hero__orbs">
        <span class="tv-orb tv-orb-1"></span>
        <span class="tv-orb tv-orb-2"></span>
      </div>
      <div class="tv-hero__content">
        <div class="tv-hero__badge"><i class="fa-solid fa-calculator"></i> Công cụ tài chính</div>
        <h1 class="tv-hero__title">Tính Vay <span class="tv-hero__accent">Mua Nhà</span></h1>
        <p class="tv-hero__sub">Tính toán ngưỡng trả nợ hàng tháng, tổng chi phí lãi vay và lịch trả nợ chi tiết</p>
      </div>
    </div>

    <div class="tv-body">

      <!-- ── LEFT: Inputs ── -->
      <div class="tv-inputs">

        <!-- Bank presets -->
        <div class="tv-card">
          <div class="tv-card__label"><i class="fa-solid fa-building-columns" style="color:#3b82f6"></i> Lãi suất ngân hàng tham khảo</div>
          <div class="tv-bank-chips">
            <button
              v-for="b in banks" :key="b.name"
              class="tv-bank-chip"
              :class="{ active: selectedBank === b.name }"
              @click="applyBank(b)"
            >
              <span class="tv-bank-name">{{ b.name }}</span>
              <span class="tv-bank-rate">{{ b.rate }}%/năm</span>
            </button>
          </div>
        </div>

        <!-- Main inputs -->
        <div class="tv-card">
          <div class="tv-card__label"><i class="fa-solid fa-sliders" style="color:#8b5cf6"></i> Thông số vay</div>

          <div class="tv-field">
            <label>Giá trị bất động sản</label>
            <div class="tv-input-wrap">
              <input v-model.number="form.giaTriBDS" type="number" min="0" placeholder="VD: 3000000000" @input="calculate" />
              <span class="tv-unit">₫</span>
            </div>
            <div class="tv-hint">{{ formatBig(form.giaTriBDS) }}</div>
          </div>

          <div class="tv-field">
            <label>Vốn tự có</label>
            <div class="tv-slider-row">
              <input v-model.number="form.vonTuCoPct" type="range" min="10" max="90" step="5" class="tv-slider" @input="calculate" />
              <div class="tv-slider-val">{{ form.vonTuCoPct }}%</div>
            </div>
            <div class="tv-hint">{{ formatBig(vonTuCo) }} — Vay: {{ formatBig(soTienVay) }}</div>
          </div>

          <div class="tv-field">
            <label>Lãi suất / năm</label>
            <div class="tv-input-wrap">
              <input v-model.number="form.laiSuat" type="number" min="1" max="30" step="0.1" @input="calculate" />
              <span class="tv-unit">%</span>
            </div>
          </div>

          <div class="tv-field">
            <label>Kỳ hạn vay</label>
            <div class="tv-tab-group">
              <button
                v-for="y in [5,10,15,20,25,30]" :key="y"
                class="tv-tab"
                :class="{ active: form.kyHan === y }"
                @click="form.kyHan = y; calculate()"
              >{{ y }} năm</button>
            </div>
          </div>

          <div class="tv-field">
            <label>Phương thức tính lãi</label>
            <div class="tv-tab-group">
              <button class="tv-tab" :class="{ active: form.method === 'reducing' }" @click="form.method='reducing'; calculate()">Dư nợ giảm dần</button>
              <button class="tv-tab" :class="{ active: form.method === 'flat' }" @click="form.method='flat'; calculate()">Phẳng (flat rate)</button>
            </div>
          </div>
        </div>
      </div>

      <!-- ── RIGHT: Results ── -->
      <div class="tv-results" v-if="result">

        <!-- KPI row -->
        <div class="tv-kpi-row">
          <div class="tv-kpi tv-kpi--primary">
            <div class="tv-kpi__label">Trả hàng tháng</div>
            <div class="tv-kpi__value">{{ formatCurrency(result.monthly) }}</div>
            <div class="tv-kpi__sub">{{ form.kyHan * 12 }} tháng</div>
          </div>
          <div class="tv-kpi">
            <div class="tv-kpi__label">Tổng tiền lãi</div>
            <div class="tv-kpi__value tv-kpi__value--red">{{ formatCurrency(result.totalInterest) }}</div>
            <div class="tv-kpi__sub">{{ result.interestPct }}% giá trị vay</div>
          </div>
          <div class="tv-kpi">
            <div class="tv-kpi__label">Tổng phải trả</div>
            <div class="tv-kpi__value tv-kpi__value--dark">{{ formatCurrency(result.totalPayment) }}</div>
            <div class="tv-kpi__sub">Gốc + Lãi</div>
          </div>
        </div>

        <!-- Charts row -->
        <div class="tv-charts-row">
          <!-- Donut -->
          <div class="tv-card tv-card--chart">
            <div class="tv-card__head">
              <span class="tv-card__title"><i class="fa-solid fa-chart-pie" style="color:#8b5cf6"></i> Cơ cấu khoản vay</span>
            </div>
            <apexchart type="donut" height="260" :options="donutOptions" :series="donutSeries" />
          </div>

          <!-- Area chart -->
          <div class="tv-card tv-card--chart">
            <div class="tv-card__head">
              <span class="tv-card__title"><i class="fa-solid fa-chart-area" style="color:#3b82f6"></i> Dư nợ theo thời gian</span>
            </div>
            <apexchart type="area" height="260" :options="areaOptions" :series="areaSeries" />
          </div>
        </div>

        <!-- Amortization table -->
        <div class="tv-card">
          <div class="tv-card__head">
            <span class="tv-card__title"><i class="fa-solid fa-table" style="color:#10b981"></i> Lịch trả nợ (12 tháng đầu)</span>
            <div class="tv-card__badge">Tháng 1 — 12</div>
          </div>
          <div class="tv-table-wrap">
            <table class="tv-table">
              <thead>
                <tr>
                  <th>Tháng</th>
                  <th>Trả gốc</th>
                  <th>Trả lãi</th>
                  <th>Thanh toán</th>
                  <th>Dư nợ còn lại</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="row in result.schedule" :key="row.month">
                  <td><span class="tv-month-badge">T{{ row.month }}</span></td>
                  <td class="tv-col-principal">{{ formatCurrency(row.principal) }}</td>
                  <td class="tv-col-interest">{{ formatCurrency(row.interest) }}</td>
                  <td class="tv-col-payment"><strong>{{ formatCurrency(row.payment) }}</strong></td>
                  <td class="tv-col-balance">{{ formatCurrency(row.balance) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Tips -->
        <div class="tv-tips">
          <div class="tv-tip" v-for="t in tips" :key="t.text">
            <i :class="t.icon" :style="{ color: t.color }"></i>
            <span>{{ t.text }}</span>
          </div>
        </div>
      </div>

      <!-- Empty state -->
      <div v-else class="tv-empty">
        <i class="fa-solid fa-calculator"></i>
        <p>Nhập thông tin bên trái để xem kết quả tính toán</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import VueApexCharts from "vue3-apexcharts";

const apexchart = VueApexCharts;

// ── Bank presets ──
const banks = [
  { name: "Vietcombank", rate: 7.5 },
  { name: "BIDV",        rate: 7.8 },
  { name: "VietinBank",  rate: 7.9 },
  { name: "Agribank",    rate: 7.2 },
  { name: "Techcombank", rate: 8.5 },
  { name: "MB Bank",     rate: 8.2 },
  { name: "VPBank",      rate: 8.9 },
  { name: "TPBank",      rate: 8.7 },
];

const selectedBank = ref("");

const applyBank = (b) => {
  selectedBank.value = b.name;
  form.value.laiSuat = b.rate;
  calculate();
};

// ── Form ──
const form = ref({
  giaTriBDS: 3000000000,
  vonTuCoPct: 30,
  laiSuat: 7.5,
  kyHan: 20,
  method: "reducing",
});

const vonTuCo = computed(() => (form.value.giaTriBDS * form.value.vonTuCoPct) / 100);
const soTienVay = computed(() => form.value.giaTriBDS - vonTuCo.value);

// ── Result state ──
const result = ref(null);

// ── Calculation ──
const calculate = () => {
  const P = soTienVay.value;
  const r = form.value.laiSuat / 100 / 12;
  const n = form.value.kyHan * 12;

  if (!P || P <= 0 || !r || !n) { result.value = null; return; }

  let monthly, schedule = [];

  if (form.value.method === "reducing") {
    // Annuity formula
    monthly = (P * r * Math.pow(1 + r, n)) / (Math.pow(1 + r, n) - 1);
    let balance = P;
    for (let i = 1; i <= Math.min(12, n); i++) {
      const interest = balance * r;
      const principal = monthly - interest;
      balance -= principal;
      schedule.push({
        month: i,
        interest: Math.round(interest),
        principal: Math.round(principal),
        payment: Math.round(monthly),
        balance: Math.max(0, Math.round(balance)),
      });
    }
  } else {
    // Flat rate
    const totalInterestFlat = P * (form.value.laiSuat / 100) * form.value.kyHan;
    monthly = (P + totalInterestFlat) / n;
    const monthlyPrincipal = P / n;
    const monthlyInterest = totalInterestFlat / n;
    let balance = P;
    for (let i = 1; i <= Math.min(12, n); i++) {
      balance -= monthlyPrincipal;
      schedule.push({
        month: i,
        interest: Math.round(monthlyInterest),
        principal: Math.round(monthlyPrincipal),
        payment: Math.round(monthly),
        balance: Math.max(0, Math.round(balance)),
      });
    }
  }

  const totalPayment = monthly * n;
  const totalInterest = totalPayment - P;

  // Area chart data (yearly balance)
  const balanceByYear = [];
  const yearLabels = [];
  if (form.value.method === "reducing") {
    let bal = P;
    const r2 = form.value.laiSuat / 100 / 12;
    for (let y = 0; y <= form.value.kyHan; y++) {
      balanceByYear.push(Math.round(bal));
      yearLabels.push("Năm " + y);
      for (let m = 0; m < 12 && bal > 0; m++) {
        const interest = bal * r2;
        const principal = monthly - interest;
        bal -= principal;
      }
    }
  } else {
    const principalPerMonth = P / n;
    let bal = P;
    for (let y = 0; y <= form.value.kyHan; y++) {
      balanceByYear.push(Math.round(Math.max(0, bal)));
      yearLabels.push("Năm " + y);
      bal -= principalPerMonth * 12;
    }
  }

  updateCharts(P, totalInterest, balanceByYear, yearLabels);

  result.value = {
    monthly: Math.round(monthly),
    totalPayment: Math.round(totalPayment),
    totalInterest: Math.round(totalInterest),
    interestPct: ((totalInterest / P) * 100).toFixed(1),
    schedule,
  };
};

// ── Charts ──
const donutSeries = ref([0, 0]);
const donutOptions = ref({
  chart: { type: "donut", fontFamily: "Inter,sans-serif" },
  labels: ["Vốn tự có", "Tiền vay", "Tổng lãi"],
  colors: ["#10b981", "#3b82f6", "#ef4444"],
  plotOptions: { pie: { donut: { size: "65%", labels: { show: true, total: { show: true, label: "Tổng chi phí", formatter: (w) => formatCurrency(w.globals.seriesTotals.reduce((a, b) => a + b, 0)) } } } } },
  dataLabels: { enabled: false },
  legend: { position: "bottom", fontSize: "12px" },
  tooltip: { y: { formatter: (v) => formatCurrency(v) } },
});

const areaSeries = ref([{ name: "Dư nợ", data: [] }]);
const areaOptions = ref({
  chart: { type: "area", toolbar: { show: false }, fontFamily: "Inter,sans-serif", animations: { enabled: true } },
  stroke: { curve: "smooth", width: 3 },
  fill: { type: "gradient", gradient: { shadeIntensity: 1, opacityFrom: 0.5, opacityTo: 0.05 } },
  dataLabels: { enabled: false },
  xaxis: { categories: [], labels: { style: { fontSize: "11px" }, rotate: -30 } },
  yaxis: { labels: { formatter: (v) => { if (v >= 1e9) return (v / 1e9).toFixed(1) + " Tỷ"; if (v >= 1e6) return (v / 1e6).toFixed(0) + " Tr"; return v; } } },
  colors: ["#3b82f6"],
  grid: { borderColor: "#f1f5f9", strokeDashArray: 4 },
  tooltip: { y: { formatter: (v) => formatCurrency(v) } },
});

const updateCharts = (principal, totalInterest, balanceByYear, yearLabels) => {
  donutSeries.value = [Math.round(vonTuCo.value), Math.round(principal), Math.round(totalInterest)];
  areaSeries.value = [{ name: "Dư nợ còn lại", data: balanceByYear }];
  areaOptions.value = { ...areaOptions.value, xaxis: { ...areaOptions.value.xaxis, categories: yearLabels } };
};

// ── Tips ──
const tips = computed(() => {
  if (!result.value) return [];
  const ratio = result.value.monthly / (form.value.giaTriBDS * 0.005);
  return [
    { icon: "fa-solid fa-circle-info", color: "#3b82f6", text: `Tiêu chuẩn: thu nhập tháng cần ít nhất ${formatCurrency(result.value.monthly * 3)} để đảm bảo tỷ lệ trả nợ dưới 33%.` },
    { icon: "fa-solid fa-lightbulb", color: "#f59e0b", text: `Trả thêm ${formatCurrency(result.value.monthly * 0.1)} mỗi tháng có thể rút ngắn kỳ hạn vay đáng kể.` },
    { icon: "fa-solid fa-shield-halved", color: "#10b981", text: `Lãi suất ưu đãi thường áp dụng 1-3 năm đầu. Hãy tính với lãi suất thả nổi sau đó.` },
  ];
});

// ── Helpers ──
const formatCurrency = (v) =>
  new Intl.NumberFormat("vi-VN", { style: "currency", currency: "VND" }).format(v || 0);

const formatBig = (v) => {
  if (!v) return "—";
  if (v >= 1e9) return (v / 1e9).toFixed(2) + " tỷ đồng";
  if (v >= 1e6) return (v / 1e6).toFixed(0) + " triệu đồng";
  return formatCurrency(v);
};

onMounted(() => calculate());
</script>

<style scoped>
/* ── Page ── */
.tv-page { display: flex; flex-direction: column; min-height: 100vh; background: #f1f5f9; font-family: "Inter", sans-serif; }

/* ── Hero ── */
.tv-hero {
  position: relative; overflow: hidden;
  background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 55%, #2563eb 100%);
  padding: 3rem 2rem 2.5rem; color: #fff;
}
.tv-hero__orbs { position: absolute; inset: 0; pointer-events: none; }
.tv-orb { position: absolute; border-radius: 50%; filter: blur(70px); opacity: .4; }
.tv-orb-1 { width: 350px; height: 350px; background: linear-gradient(135deg,#8b5cf6,#3b82f6); top: -100px; right: -80px; animation: float 18s infinite ease-in-out; }
.tv-orb-2 { width: 250px; height: 250px; background: linear-gradient(135deg,#10b981,#3b82f6); bottom: -80px; left: 20%; animation: float 22s infinite ease-in-out reverse; }
@keyframes float { 0%,100%{transform:translate(0,0)} 50%{transform:translate(20px,-20px)} }
.tv-hero__content { position: relative; z-index: 10; max-width: 700px; }
.tv-hero__badge {
  display: inline-flex; align-items: center; gap: .5rem;
  padding: .4rem 1rem; background: rgba(255,255,255,.1); backdrop-filter: blur(8px);
  border: 1px solid rgba(255,255,255,.2); border-radius: 50px;
  font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em;
  color: rgba(255,255,255,.9); margin-bottom: 1rem;
}
.tv-hero__title { font-size: 2.4rem; font-weight: 900; margin: 0 0 .75rem; line-height: 1.2; }
.tv-hero__accent { background: linear-gradient(135deg,#60a5fa,#a78bfa); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
.tv-hero__sub { font-size: .95rem; color: rgba(255,255,255,.7); margin: 0; }

/* ── Body ── */
.tv-body {
  display: grid; grid-template-columns: 380px 1fr;
  gap: 1.5rem; padding: 1.5rem; align-items: start;
}

/* ── Cards (shared) ── */
.tv-card {
  background: #fff; border-radius: 18px; padding: 1.25rem;
  box-shadow: 0 1px 4px rgba(0,0,0,.06); border: 1px solid #e2e8f0; margin-bottom: 1.25rem;
}
.tv-card:last-child { margin-bottom: 0; }
.tv-card__label {
  display: flex; align-items: center; gap: .5rem;
  font-size: .8rem; font-weight: 700; color: #334155;
  text-transform: uppercase; letter-spacing: .06em; margin-bottom: 1rem;
}
.tv-card__head {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 1rem;
}
.tv-card__title { display: flex; align-items: center; gap: .5rem; font-size: .9rem; font-weight: 700; color: #0f172a; }
.tv-card__badge {
  font-size: .72rem; font-weight: 700; color: #64748b;
  background: #f1f5f9; padding: .25rem .7rem; border-radius: 20px;
}
.tv-card--chart { flex: 1; margin-bottom: 0; }

/* ── Bank chips ── */
.tv-bank-chips { display: flex; flex-wrap: wrap; gap: .5rem; }
.tv-bank-chip {
  display: flex; flex-direction: column; align-items: flex-start;
  padding: .5rem .85rem; background: #f8fafc; border: 1px solid #e2e8f0;
  border-radius: 12px; cursor: pointer; transition: all .2s;
}
.tv-bank-chip:hover { border-color: #3b82f6; background: #eff6ff; }
.tv-bank-chip.active { border-color: #3b82f6; background: #dbeafe; }
.tv-bank-name { font-size: .78rem; font-weight: 700; color: #0f172a; }
.tv-bank-rate { font-size: .7rem; color: #64748b; }
.tv-bank-chip.active .tv-bank-name { color: #1d4ed8; }
.tv-bank-chip.active .tv-bank-rate { color: #2563eb; }

/* ── Fields ── */
.tv-field { margin-bottom: 1.1rem; }
.tv-field label { display: block; font-size: .78rem; font-weight: 600; color: #64748b; margin-bottom: .4rem; }
.tv-input-wrap {
  display: flex; align-items: center;
  border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden;
  transition: border-color .2s;
}
.tv-input-wrap:focus-within { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.1); }
.tv-input-wrap input {
  flex: 1; border: none; outline: none; padding: .7rem .9rem;
  font-size: .9rem; font-weight: 600; color: #0f172a; background: transparent;
}
.tv-unit { padding: .7rem .9rem; background: #f8fafc; color: #64748b; font-size: .85rem; font-weight: 700; border-left: 1px solid #e2e8f0; }
.tv-hint { font-size: .75rem; color: #94a3b8; margin-top: .3rem; }

/* Slider */
.tv-slider-row { display: flex; align-items: center; gap: .75rem; }
.tv-slider {
  flex: 1; -webkit-appearance: none; appearance: none; height: 6px; border-radius: 3px;
  background: linear-gradient(to right, #3b82f6 0%, #3b82f6 var(--pct, 0%), #e2e8f0 var(--pct, 0%));
  outline: none; cursor: pointer;
}
.tv-slider::-webkit-slider-thumb {
  -webkit-appearance: none; width: 18px; height: 18px; border-radius: 50%;
  background: #3b82f6; border: 2px solid #fff; box-shadow: 0 2px 6px rgba(59,130,246,.5);
  cursor: pointer;
}
.tv-slider-val { min-width: 44px; text-align: right; font-size: .95rem; font-weight: 800; color: #1d4ed8; }

/* Tab group */
.tv-tab-group { display: flex; flex-wrap: wrap; gap: .4rem; }
.tv-tab {
  padding: .4rem .85rem; border: 1px solid #e2e8f0; border-radius: 8px;
  background: #f8fafc; color: #64748b; font-size: .8rem; font-weight: 600; cursor: pointer; transition: all .2s;
}
.tv-tab:hover { border-color: #3b82f6; color: #2563eb; }
.tv-tab.active { background: #dbeafe; border-color: #3b82f6; color: #1d4ed8; }

/* ── Results ── */
/* KPI row */
.tv-kpi-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 1.25rem; }
.tv-kpi {
  background: #fff; border-radius: 18px; padding: 1.25rem;
  border: 1px solid #e2e8f0; box-shadow: 0 1px 4px rgba(0,0,0,.06); text-align: center;
}
.tv-kpi--primary { background: linear-gradient(135deg,#1e40af,#3b82f6); border-color: transparent; }
.tv-kpi--primary .tv-kpi__label { color: rgba(255,255,255,.8); }
.tv-kpi--primary .tv-kpi__value { color: #fff; }
.tv-kpi--primary .tv-kpi__sub { color: rgba(255,255,255,.7); }
.tv-kpi__label { font-size: .72rem; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: .06em; margin-bottom: .4rem; }
.tv-kpi__value { font-size: 1.3rem; font-weight: 900; color: #0f172a; word-break: break-all; }
.tv-kpi__value--red { color: #ef4444; }
.tv-kpi__value--dark { color: #0f172a; }
.tv-kpi__sub { font-size: .72rem; color: #94a3b8; margin-top: .3rem; }

/* Charts row */
.tv-charts-row { display: grid; grid-template-columns: 1fr 1.4fr; gap: 1.25rem; margin-bottom: 1.25rem; }

/* Table */
.tv-table-wrap { overflow-x: auto; border-radius: 10px; }
.tv-table { width: 100%; border-collapse: collapse; font-size: .84rem; }
.tv-table thead th {
  background: #f8fafc; padding: .65rem 1rem; text-align: left;
  font-size: .7rem; font-weight: 700; color: #64748b;
  text-transform: uppercase; letter-spacing: .06em;
  border-bottom: 1px solid #e2e8f0; white-space: nowrap;
}
.tv-table tbody tr { border-bottom: 1px solid #f1f5f9; transition: background .15s; }
.tv-table tbody tr:hover { background: #f8fafc; }
.tv-table td { padding: .65rem 1rem; color: #334155; }
.tv-month-badge { background: #eff6ff; color: #2563eb; border-radius: 6px; padding: .2rem .55rem; font-size: .78rem; font-weight: 700; }
.tv-col-principal { color: #1d4ed8; font-weight: 600; }
.tv-col-interest { color: #ef4444; font-weight: 600; }
.tv-col-payment { color: #0f172a; }
.tv-col-balance { color: #64748b; }

/* Tips */
.tv-tips { display: flex; flex-direction: column; gap: .65rem; }
.tv-tip {
  display: flex; align-items: flex-start; gap: .75rem;
  padding: .85rem 1rem; background: #fff; border-radius: 12px;
  border: 1px solid #e2e8f0; font-size: .83rem; color: #475569; line-height: 1.5;
}
.tv-tip i { margin-top: .1rem; font-size: 1rem; flex-shrink: 0; }

/* Empty */
.tv-empty {
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  gap: 1rem; color: #cbd5e1; padding: 4rem; font-size: .9rem; background: #fff;
  border-radius: 18px; border: 1px solid #e2e8f0;
}
.tv-empty i { font-size: 3rem; }

/* Responsive */
@media (max-width: 1100px) {
  .tv-body { grid-template-columns: 1fr; }
  .tv-kpi-row { grid-template-columns: 1fr; }
  .tv-charts-row { grid-template-columns: 1fr; }
}
@media (max-width: 640px) {
  .tv-hero__title { font-size: 1.7rem; }
}
</style>
