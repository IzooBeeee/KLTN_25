<template>
  <div class="bd-page">

    <!-- ── Sidebar ── -->
    <aside class="bd-sidebar">
      <div class="bd-sidebar__header">
        <div class="bd-sidebar__logo">
          <i class="fa-solid fa-map-location-dot"></i>
        </div>
        <div>
          <div class="bd-sidebar__title">Bản Đồ BĐS</div>
          <div class="bd-sidebar__sub">{{ totalCount }} bất động sản</div>
        </div>
      </div>

      <!-- Mode toggle -->
      <div class="bd-mode-group">
        <button
          v-for="m in modes" :key="m.key"
          class="bd-mode-btn"
          :class="{ active: mapMode === m.key }"
          @click="setMode(m.key)"
          :title="m.label"
        >
          <i :class="m.icon"></i>
          <span>{{ m.label }}</span>
        </button>
      </div>

      <!-- Stats chips -->
      <div class="bd-stat-row">
        <div class="bd-stat-chip">
          <i class="fa-solid fa-fire" style="color:#ef4444"></i>
          <span>{{ totalCount }}</span>
          <small>Tổng BĐS</small>
        </div>
        <div class="bd-stat-chip">
          <i class="fa-solid fa-location-dot" style="color:#3b82f6"></i>
          <span>{{ visibleCount }}</span>
          <small>Trong khung</small>
        </div>
      </div>

      <div class="bd-divider"></div>

      <!-- Keyword -->
      <div class="bd-field">
        <label>Từ khóa</label>
        <div class="bd-input-wrap">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input v-model="filter.keyword" type="text" placeholder="Tìm tên BĐS..." @keyup.enter="applyFilter" />
        </div>
      </div>

      <!-- Type filter -->
      <div class="bd-field">
        <label>Loại BĐS</label>
        <div class="bd-type-chips">
          <button
            class="bd-type-chip"
            :class="{ active: filter.loai_id === '' }"
            @click="filter.loai_id = ''; applyFilter()"
          >Tất cả</button>
          <button
            v-for="loai in loaiBDS" :key="loai.id"
            class="bd-type-chip"
            :class="{ active: filter.loai_id === loai.id }"
            @click="filter.loai_id = loai.id; applyFilter()"
          >{{ loai.ten_loai }}</button>
        </div>
      </div>

      <!-- Price range -->
      <div class="bd-field">
        <label>Khoảng giá (VNĐ)</label>
        <div class="bd-price-row">
          <input v-model="filter.minPrice" type="number" placeholder="Từ" />
          <span>—</span>
          <input v-model="filter.maxPrice" type="number" placeholder="Đến" />
        </div>
      </div>

      <div class="bd-actions">
        <button class="bd-btn-primary" @click="applyFilter">
          <i class="fa-solid fa-magnifying-glass"></i> Tìm kiếm
        </button>
        <button class="bd-btn-ghost" @click="resetFilter">
          <i class="fa-solid fa-rotate-left"></i> Đặt lại
        </button>
      </div>

      <div class="bd-divider"></div>

      <!-- Nearby -->
      <div class="bd-field">
        <label><i class="fa-solid fa-location-crosshairs" style="color:#10b981"></i> Tìm gần vị trí</label>
        <button class="bd-btn-locate" @click="useMyLocation">
          <i class="fa-solid fa-crosshairs"></i> Dùng vị trí của tôi
        </button>
        <div class="bd-price-row" style="margin-top:.6rem">
          <input v-model="nearby.lat" type="number" step="0.0001" placeholder="Vĩ độ" />
          <input v-model="nearby.lng" type="number" step="0.0001" placeholder="Kinh độ" />
        </div>
        <div class="bd-input-wrap" style="margin-top:.5rem">
          <i class="fa-solid fa-circle-dot" style="color:#8b5cf6"></i>
          <input v-model="nearby.radius" type="number" min="1" max="50" placeholder="Bán kính (km)" />
        </div>
        <button class="bd-btn-nearby" style="margin-top:.6rem" @click="searchNearby">
          <i class="fa-solid fa-radar"></i> Tìm gần đây
        </button>
      </div>

      <!-- Nearby results -->
      <div v-if="nearbyList.length > 0" class="bd-nearby-list">
        <div class="bd-nearby-label">{{ nearbyList.length }} BĐS trong bán kính</div>
        <div
          v-for="item in nearbyList.slice(0, 5)" :key="item.id"
          class="bd-nearby-item"
          @click="focusMarker(item)"
        >
          <div class="bd-nearby-name">{{ item.tieu_de }}</div>
          <div class="bd-nearby-price">{{ formatCurrency(item.gia) }}</div>
          <div class="bd-nearby-dist">{{ item.khoang_cach_km?.toFixed(1) }} km</div>
        </div>
      </div>
    </aside>

    <!-- ── Map ── -->
    <div class="bd-map-wrap">
      <div id="ban-do-leaflet" class="bd-map"></div>

      <!-- Loading pill -->
      <div v-if="mapLoading" class="bd-loading-pill">
        <i class="fa-solid fa-circle-notch fa-spin"></i>
        Đang tải...
      </div>

      <!-- Heatmap legend -->
      <div v-if="mapMode !== 'markers'" class="bd-heat-legend">
        <span style="color:#6366f1">Ít</span>
        <div class="bd-heat-bar"></div>
        <span style="color:#ef4444">Nhiều</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import api from "@/axios/config";
import L from "leaflet";
import "leaflet.heat";

delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl: "https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon-2x.png",
  iconUrl: "https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon.png",
  shadowUrl: "https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png",
});

// ── Type colors ──
const TYPE_COLORS = ["#3b82f6","#10b981","#f59e0b","#ef4444","#8b5cf6","#06b6d4","#f97316","#ec4899"];

// ── State ──
const mapLoading = ref(false);
const loaiBDS = ref([]);
const nearbyList = ref([]);
const totalCount = ref(0);
const visibleCount = ref(0);
const mapMode = ref("both"); // 'heatmap' | 'markers' | 'both'

const modes = [
  { key: "heatmap", label: "Nhiệt độ", icon: "fa-solid fa-fire" },
  { key: "markers", label: "Điểm đánh dấu", icon: "fa-solid fa-location-dot" },
  { key: "both",    label: "Kết hợp", icon: "fa-solid fa-layer-group" },
];

const filter = ref({ minPrice: "", maxPrice: "", loai_id: "", keyword: "" });
const nearby = ref({ lat: "", lng: "", radius: 5 });

let map = null;
let markersLayer = null;
let heatLayer = null;
let allProperties = [];
let refreshTimer = null;
const MARKER_MIN_ZOOM = 11;

// ── Map init ──
const initMap = () => {
  map = L.map("ban-do-leaflet", { zoomControl: true }).setView([16.0544, 108.2022], 7);

  L.tileLayer("https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png", {
    attribution: "© OpenStreetMap © CARTO",
    maxZoom: 19,
  }).addTo(map);

  markersLayer = L.layerGroup().addTo(map);

  // Chỉ cập nhật visible count khi kéo
  map.on("moveend", () => {
    if (!map) return;
    const b = map.getBounds();
    visibleCount.value = allProperties.filter(p => b.contains([p.dia_chi.lat, p.dia_chi.lng])).length;
  });

  // Refresh markers chỉ khi đổi zoom (có ngưỡng tối thiểu)
  map.on("zoomend", () => {
    clearTimeout(refreshTimer);
    refreshTimer = setTimeout(() => refreshMarkers(), 200);
  });
};

// ── Load all properties ──
const loadAllProperties = async () => {
  mapLoading.value = true;
  try {
    const params = {};
    if (filter.value.minPrice) params.min_price = filter.value.minPrice;
    if (filter.value.maxPrice) params.max_price = filter.value.maxPrice;
    if (filter.value.loai_id) params.loai_id = filter.value.loai_id;
    if (filter.value.keyword) params.keyword = filter.value.keyword;

    const res = await api.get("/client/map/bat-dong-san", { params });
    if (res.data?.data) {
      allProperties = res.data.data.filter(p => p.dia_chi?.lat && p.dia_chi?.lng);
      totalCount.value = allProperties.length;
      renderHeatmap();
      refreshMarkers();
      // Fit bounds vào data thực tế
      if (allProperties.length > 0) {
        const latlngs = allProperties.map(p => [p.dia_chi.lat, p.dia_chi.lng]);
        map.fitBounds(L.latLngBounds(latlngs), { padding: [40, 40], maxZoom: 13 });
      }
    }
  } catch (e) {
    console.error("Lỗi tải bản đồ:", e);
  } finally {
    mapLoading.value = false;
  }
};

// ── Heatmap (vẽ 1 lần, không phụ thuộc zoom) ──
const renderHeatmap = () => {
  if (!map) return;
  if (heatLayer) { map.removeLayer(heatLayer); heatLayer = null; }
  if (mapMode.value !== "markers" && allProperties.length > 0) {
    const heatPoints = allProperties.map(p => [p.dia_chi.lat, p.dia_chi.lng, 0.6]);
    heatLayer = L.heatLayer(heatPoints, {
      radius: 35,
      blur: 25,
      maxZoom: 17,
      gradient: { 0.2: "#6366f1", 0.5: "#f59e0b", 1.0: "#ef4444" },
    }).addTo(map);
  }
};

// ── Markers (chỉ render khi zoom đủ lớn) ──
const refreshMarkers = () => {
  if (!map) return;
  markersLayer.clearLayers();
  if (mapMode.value === "heatmap") return;
  const zoom = map.getZoom();
  if (zoom < MARKER_MIN_ZOOM) return; // Ẩn markers khi zoom ra quá

  const bounds = map.getBounds();
  const visible = allProperties.filter(p => bounds.contains([p.dia_chi.lat, p.dia_chi.lng]));
  visibleCount.value = visible.length;

  const typeColorMap = {};
  loaiBDS.value.forEach((l, i) => { typeColorMap[l.id] = TYPE_COLORS[i % TYPE_COLORS.length]; });

  visible.forEach((item) => {
    const color = typeColorMap[item.loai_id] || "#3b82f6";
    const icon = L.divIcon({
      className: "",
      html: `<div style="width:24px;height:24px;border-radius:50% 50% 50% 0;transform:rotate(-45deg);background:${color};border:2px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,.3)"></div>`,
      iconSize: [24, 24],
      iconAnchor: [12, 24],
      popupAnchor: [0, -26],
    });
    const marker = L.marker([item.dia_chi.lat, item.dia_chi.lng], { icon });
    marker.bindPopup(buildPopup(item, color), { maxWidth: 260 });
    markersLayer.addLayer(marker);
  });
};

// ── Refresh cả hai khi đổi mode hoặc filter ──
const refreshLayers = () => { renderHeatmap(); refreshMarkers(); };

// ── Popup HTML ──
const buildPopup = (item, color) => `
  <div style="font-family:Inter,sans-serif;min-width:240px">
    <img src="${item.anh_dai_dien_url || 'https://placehold.co/240x130?text=BĐS'}"
      style="width:100%;height:130px;object-fit:cover;border-radius:10px 10px 0 0;margin:-12px -12px 10px -12px;width:calc(100% + 24px)" />
    <div style="display:flex;align-items:center;gap:6px;margin-bottom:6px">
      <span style="background:${color}22;color:${color};padding:2px 8px;border-radius:20px;font-size:11px;font-weight:700">${item.loai?.ten_loai || "BĐS"}</span>
    </div>
    <div style="font-weight:700;font-size:14px;color:#0f172a;margin-bottom:4px;line-height:1.4">${item.tieu_de}</div>
    <div style="color:#10b981;font-weight:800;font-size:16px;margin-bottom:6px">${formatCurrency(item.gia)}</div>
    <div style="color:#64748b;font-size:12px;margin-bottom:3px">
      <i style="color:#94a3b8" class="fa-solid fa-ruler-combined"></i> ${item.dien_tich} m²
      &nbsp;·&nbsp;
      <i style="color:#94a3b8" class="fa-solid fa-location-dot"></i>
      ${[item.dia_chi?.quan_ten, item.dia_chi?.tinh_ten].filter(Boolean).join(", ")}
    </div>
    <div style="color:#94a3b8;font-size:11px;margin-bottom:8px">${item.dia_chi?.dia_chi_chi_tiet || ""}</div>
    <div style="color:#475569;font-size:12px;margin-bottom:12px">
      Môi giới: <b>${item.moi_gioi?.ten || "—"}</b>
    </div>
    <a href="/khach-hang/chi-tiet-bat-dong-san/${item.id}"
      style="display:block;text-align:center;background:linear-gradient(135deg,#3b82f6,#2563eb);
             color:#fff;padding:9px 12px;border-radius:10px;font-size:13px;font-weight:700;
             text-decoration:none;box-shadow:0 4px 12px rgba(59,130,246,.35)">
      Xem chi tiết →
    </a>
  </div>`;

// ── Controls ──
const setMode = (mode) => {
  mapMode.value = mode;
  renderHeatmap();
  refreshMarkers();
};
const applyFilter = () => loadAllProperties();
const resetFilter = () => {
  filter.value = { minPrice: "", maxPrice: "", loai_id: "", keyword: "" };
  loadAllProperties();
};

const searchNearby = async () => {
  if (!nearby.value.lat || !nearby.value.lng) return;
  mapLoading.value = true;
  try {
    const res = await api.get("/client/map/nearby", {
      params: { lat: nearby.value.lat, lng: nearby.value.lng, radius: nearby.value.radius },
    });
    if (res.data?.data) {
      nearbyList.value = res.data.data;
      if (map) map.setView([nearby.value.lat, nearby.value.lng], 14);
    }
  } finally {
    mapLoading.value = false;
  }
};

const useMyLocation = () => {
  if (!navigator.geolocation) return;
  navigator.geolocation.getCurrentPosition((pos) => {
    nearby.value.lat = pos.coords.latitude.toFixed(6);
    nearby.value.lng = pos.coords.longitude.toFixed(6);
    if (map) map.setView([+nearby.value.lat, +nearby.value.lng], 14);
  });
};

const focusMarker = (item) => {
  if (!map || !item.dia_chi?.lat) return;
  map.setView([item.dia_chi.lat, item.dia_chi.lng], 16);
};

const fetchLoaiBDS = async () => {
  try {
    const res = await api.get("/client/loai-bat-dong-san");
    if (res.data?.data) loaiBDS.value = res.data.data;
  } catch {}
};

const formatCurrency = (val) =>
  new Intl.NumberFormat("vi-VN", { style: "currency", currency: "VND", notation: "compact" }).format(val || 0);

onMounted(async () => {
  await fetchLoaiBDS();
  initMap();
  await loadAllProperties();
});

onUnmounted(() => {
  clearTimeout(refreshTimer);
  if (map) { map.remove(); map = null; }
});
</script>

<style scoped>
/* ── Layout ── */
.bd-page {
  display: flex;
  height: calc(100vh - 74px);
  overflow: hidden;
  font-family: "Inter", sans-serif;
}

/* ── Sidebar ── */
.bd-sidebar {
  width: 300px;
  min-width: 300px;
  background: #0f172a;
  color: #e2e8f0;
  display: flex;
  flex-direction: column;
  gap: 0;
  overflow-y: auto;
  flex-shrink: 0;
  padding: 0 0 1.5rem;
  scrollbar-width: thin;
  scrollbar-color: #334155 transparent;
}

.bd-sidebar__header {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.4rem 1.25rem;
  background: linear-gradient(135deg, #1e293b, #0f172a);
  border-bottom: 1px solid #1e293b;
  position: sticky;
  top: 0;
  z-index: 10;
}
.bd-sidebar__logo {
  width: 44px; height: 44px;
  background: linear-gradient(135deg, #3b82f6, #8b5cf6);
  border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.25rem; color: #fff; flex-shrink: 0;
}
.bd-sidebar__title { font-size: 1rem; font-weight: 800; color: #f1f5f9; }
.bd-sidebar__sub { font-size: .72rem; color: #64748b; }

/* Mode toggle */
.bd-mode-group {
  display: flex;
  gap: .5rem;
  padding: 1rem 1.25rem .75rem;
}
.bd-mode-btn {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: .3rem;
  padding: .6rem .4rem;
  background: #1e293b;
  border: 1px solid #334155;
  border-radius: 12px;
  color: #64748b;
  cursor: pointer;
  font-size: .65rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .04em;
  transition: all .25s;
}
.bd-mode-btn i { font-size: 1rem; }
.bd-mode-btn:hover { background: #263349; color: #94a3b8; }
.bd-mode-btn.active { background: linear-gradient(135deg, #2563eb22, #8b5cf622); border-color: #3b82f6; color: #60a5fa; }

/* Stats row */
.bd-stat-row {
  display: flex;
  gap: .75rem;
  padding: 0 1.25rem .75rem;
}
.bd-stat-chip {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: .15rem;
  background: #1e293b;
  border: 1px solid #334155;
  border-radius: 10px;
  padding: .65rem;
}
.bd-stat-chip span { font-size: 1.1rem; font-weight: 800; color: #f1f5f9; }
.bd-stat-chip small { font-size: .65rem; color: #64748b; font-weight: 500; }
.bd-stat-chip i { font-size: .85rem; }

/* Divider */
.bd-divider {
  height: 1px;
  background: #1e293b;
  margin: .25rem 1.25rem .75rem;
}

/* Fields */
.bd-field {
  padding: 0 1.25rem .9rem;
  display: flex;
  flex-direction: column;
  gap: .4rem;
}
.bd-field label {
  font-size: .7rem;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: .06em;
  display: flex;
  align-items: center;
  gap: .4rem;
}

.bd-input-wrap {
  display: flex;
  align-items: center;
  gap: .6rem;
  background: #1e293b;
  border: 1px solid #334155;
  border-radius: 10px;
  padding: .5rem .75rem;
  transition: border-color .2s;
}
.bd-input-wrap:focus-within { border-color: #3b82f6; }
.bd-input-wrap i { color: #475569; font-size: .85rem; flex-shrink: 0; }
.bd-input-wrap input {
  background: transparent; border: none; outline: none;
  color: #e2e8f0; font-size: .85rem; width: 100%;
}
.bd-input-wrap input::placeholder { color: #475569; }

/* Type chips */
.bd-type-chips {
  display: flex;
  flex-wrap: wrap;
  gap: .4rem;
}
.bd-type-chip {
  padding: .3rem .7rem;
  background: #1e293b;
  border: 1px solid #334155;
  border-radius: 20px;
  color: #64748b;
  font-size: .72rem;
  font-weight: 600;
  cursor: pointer;
  transition: all .2s;
}
.bd-type-chip:hover { border-color: #3b82f6; color: #93c5fd; }
.bd-type-chip.active { background: #2563eb22; border-color: #3b82f6; color: #60a5fa; }

/* Price row */
.bd-price-row {
  display: flex;
  align-items: center;
  gap: .5rem;
}
.bd-price-row input {
  flex: 1;
  background: #1e293b;
  border: 1px solid #334155;
  border-radius: 8px;
  padding: .45rem .65rem;
  color: #e2e8f0;
  font-size: .82rem;
  outline: none;
  transition: border-color .2s;
  min-width: 0;
}
.bd-price-row input:focus { border-color: #3b82f6; }
.bd-price-row input::placeholder { color: #475569; }
.bd-price-row span { color: #475569; flex-shrink: 0; }

/* Buttons */
.bd-actions { display: flex; flex-direction: column; gap: .5rem; padding: 0 1.25rem .25rem; }
.bd-btn-primary {
  width: 100%; padding: .65rem; border-radius: 10px; border: none;
  background: linear-gradient(135deg, #2563eb, #3b82f6);
  color: #fff; font-weight: 700; font-size: .85rem; cursor: pointer;
  display: flex; align-items: center; justify-content: center; gap: .45rem;
  transition: all .25s; box-shadow: 0 4px 14px rgba(59,130,246,.35);
}
.bd-btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(59,130,246,.5); }
.bd-btn-ghost {
  width: 100%; padding: .6rem; border-radius: 10px;
  border: 1px solid #334155; background: transparent;
  color: #64748b; font-weight: 600; font-size: .85rem; cursor: pointer;
  display: flex; align-items: center; justify-content: center; gap: .45rem;
  transition: all .2s;
}
.bd-btn-ghost:hover { border-color: #475569; color: #94a3b8; background: #1e293b; }
.bd-btn-locate {
  width: 100%; padding: .55rem; border-radius: 8px;
  border: 1px solid #134e4a; background: #0f3d3a;
  color: #34d399; font-weight: 600; font-size: .8rem; cursor: pointer;
  display: flex; align-items: center; justify-content: center; gap: .4rem;
  transition: all .2s;
}
.bd-btn-locate:hover { background: #134e4a; }
.bd-btn-nearby {
  width: 100%; padding: .6rem; border-radius: 10px; border: none;
  background: linear-gradient(135deg, #059669, #10b981);
  color: #fff; font-weight: 700; font-size: .85rem; cursor: pointer;
  display: flex; align-items: center; justify-content: center; gap: .45rem;
  transition: all .25s;
}
.bd-btn-nearby:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(16,185,129,.4); }

/* Nearby results */
.bd-nearby-list { padding: 0 1.25rem; }
.bd-nearby-label {
  font-size: .72rem; font-weight: 700; color: #34d399;
  text-transform: uppercase; letter-spacing: .06em; margin-bottom: .6rem;
}
.bd-nearby-item {
  padding: .65rem .85rem;
  background: #1e293b; border: 1px solid #334155; border-radius: 10px;
  margin-bottom: .5rem; cursor: pointer; transition: all .2s;
}
.bd-nearby-item:hover { border-color: #10b981; background: #0f3d3a22; }
.bd-nearby-name { font-size: .85rem; font-weight: 600; color: #e2e8f0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.bd-nearby-price { font-size: .82rem; font-weight: 700; color: #34d399; }
.bd-nearby-dist { font-size: .68rem; color: #64748b; margin-top: .15rem; }

/* ── Map ── */
.bd-map-wrap {
  flex: 1;
  position: relative;
}
.bd-map {
  width: 100%;
  height: 100%;
}

/* Loading pill */
.bd-loading-pill {
  position: absolute;
  top: 14px;
  left: 50%;
  transform: translateX(-50%);
  background: rgba(15,23,42,.9);
  backdrop-filter: blur(8px);
  color: #94a3b8;
  padding: .5rem 1.2rem;
  border-radius: 30px;
  border: 1px solid #334155;
  display: flex;
  align-items: center;
  gap: .5rem;
  font-size: .85rem;
  z-index: 1000;
}

/* Heatmap legend */
.bd-heat-legend {
  position: absolute;
  bottom: 24px;
  right: 14px;
  background: rgba(15,23,42,.85);
  backdrop-filter: blur(8px);
  border: 1px solid #334155;
  border-radius: 12px;
  padding: .6rem 1rem;
  display: flex;
  align-items: center;
  gap: .75rem;
  font-size: .72rem;
  font-weight: 700;
  z-index: 1000;
}
.bd-heat-bar {
  width: 90px;
  height: 10px;
  border-radius: 20px;
  background: linear-gradient(to right, #6366f1, #f59e0b, #ef4444);
}

@media (max-width: 768px) {
  .bd-page { flex-direction: column; height: auto; }
  .bd-sidebar { width: 100%; min-width: unset; max-height: 50vh; }
  .bd-map-wrap { height: 55vh; }
}
</style>
