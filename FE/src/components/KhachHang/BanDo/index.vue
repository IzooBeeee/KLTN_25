<template>
  <div class="ban-do-page">
    <!-- Filter Sidebar -->
    <div class="filter-panel">
      <h6 class="fw-bold mb-3"><i class="bi bi-funnel me-2"></i>Bộ lọc bản đồ</h6>

      <div class="mb-3">
        <label class="form-label small fw-semibold">Khoảng giá</label>
        <div class="d-flex gap-2">
          <input v-model="filter.minPrice" type="number" class="form-control form-control-sm" placeholder="Từ (VNĐ)" />
          <input v-model="filter.maxPrice" type="number" class="form-control form-control-sm" placeholder="Đến (VNĐ)" />
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label small fw-semibold">Loại bất động sản</label>
        <select v-model="filter.loai_id" class="form-select form-select-sm">
          <option value="">Tất cả loại</option>
          <option v-for="loai in loaiBDS" :key="loai.id" :value="loai.id">{{ loai.ten_loai }}</option>
        </select>
      </div>

      <button @click="applyFilter" class="btn btn-primary btn-sm w-100 rounded-pill mb-2">
        <i class="bi bi-search me-1"></i>Tìm kiếm
      </button>
      <button @click="resetFilter" class="btn btn-outline-secondary btn-sm w-100 rounded-pill">
        <i class="bi bi-arrow-counterclockwise me-1"></i>Đặt lại
      </button>

      <hr class="my-3" />
      <h6 class="fw-bold mb-3"><i class="bi bi-geo me-2"></i>Tìm gần vị trí</h6>

      <div class="mb-2">
        <label class="form-label small fw-semibold">Vĩ độ (Lat)</label>
        <input v-model="nearby.lat" type="number" step="0.0001" class="form-control form-control-sm" placeholder="10.7769" />
      </div>
      <div class="mb-2">
        <label class="form-label small fw-semibold">Kinh độ (Lng)</label>
        <input v-model="nearby.lng" type="number" step="0.0001" class="form-control form-control-sm" placeholder="106.7009" />
      </div>
      <div class="mb-3">
        <label class="form-label small fw-semibold">Bán kính (km)</label>
        <input v-model="nearby.radius" type="number" min="1" max="50" class="form-control form-control-sm" placeholder="5" />
      </div>

      <button @click="useMyLocation" class="btn btn-outline-info btn-sm w-100 rounded-pill mb-2">
        <i class="bi bi-crosshair me-1"></i>Dùng vị trí của tôi
      </button>
      <button @click="searchNearby" class="btn btn-success btn-sm w-100 rounded-pill">
        <i class="bi bi-radar me-1"></i>Tìm gần đây
      </button>

      <!-- Nearby results -->
      <div v-if="nearbyList.length > 0" class="mt-3">
        <h6 class="small fw-bold text-success">{{ nearbyList.length }} BĐS gần vị trí</h6>
        <div v-for="item in nearbyList.slice(0, 5)" :key="item.id" class="nearby-item" @click="focusMarker(item)">
          <div class="fw-medium small text-truncate">{{ item.tieu_de }}</div>
          <div class="text-success small fw-bold">{{ formatCurrency(item.gia) }}</div>
          <div class="text-muted" style="font-size:11px">{{ item.khoang_cach_km?.toFixed(1) }} km</div>
        </div>
      </div>
    </div>

    <!-- Map Container -->
    <div class="map-container">
      <div id="ban-do-leaflet" class="leaflet-map"></div>
      <div v-if="mapLoading" class="map-loading">
        <div class="spinner-border text-primary"></div>
        <span class="ms-2">Đang tải bản đồ...</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import api from "@/axios/config";
import L from "leaflet";

delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl: "https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon-2x.png",
  iconUrl: "https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon.png",
  shadowUrl: "https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png",
});

const mapLoading = ref(false);
const loaiBDS = ref([]);
const nearbyList = ref([]);

const filter = ref({ minPrice: "", maxPrice: "", loai_id: "" });
const nearby = ref({ lat: "", lng: "", radius: 5 });

let map = null;
let markersLayer = null;

const isLoggedIn = !!localStorage.getItem("auth_token");

const initMap = () => {
  map = L.map("ban-do-leaflet").setView([10.7769, 106.7009], 12);
  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    attribution: "© OpenStreetMap contributors",
    maxZoom: 19,
  }).addTo(map);
  markersLayer = L.layerGroup().addTo(map);

  map.on("moveend", () => loadMapProperties());
};

const loadMapProperties = async () => {
  if (!map) return;
  mapLoading.value = true;
  try {
    const b = map.getBounds();
    const bounds = { north: b.getNorth(), south: b.getSouth(), east: b.getEast(), west: b.getWest() };
    const params = { bounds: JSON.stringify(bounds) };
    if (filter.value.minPrice) params.min_price = filter.value.minPrice;
    if (filter.value.maxPrice) params.max_price = filter.value.maxPrice;
    if (filter.value.loai_id) params.loai_id = filter.value.loai_id;

    const endpoint = isLoggedIn ? "/khach-hang/map/bat-dong-san" : "/khach-hang/map/bat-dong-san";
    const res = await api.get(endpoint, { params });
    if (res.data?.data) {
      renderMarkers(res.data.data);
    }
  } catch (e) {
    console.error("Lỗi tải bản đồ:", e);
  } finally {
    mapLoading.value = false;
  }
};

const renderMarkers = (items) => {
  markersLayer.clearLayers();
  items.forEach((item) => {
    const lat = item.dia_chi?.lat;
    const lng = item.dia_chi?.lng;
    if (!lat || !lng) return;

    const marker = L.marker([lat, lng]);
    const popupContent = `
      <div style="min-width:220px;font-family:Inter,sans-serif">
        <img src="${item.anh_dai_dien_url || 'https://placehold.co/220x120?text=No+Image'}"
          style="width:100%;height:120px;object-fit:cover;border-radius:8px;margin-bottom:8px" />
        <div style="font-weight:700;font-size:14px;margin-bottom:4px">${item.tieu_de}</div>
        <div style="color:#16a34a;font-weight:700;font-size:15px">${formatCurrency(item.gia)}</div>
        <div style="color:#6b7280;font-size:12px;margin:4px 0">${item.dien_tich} m² · ${item.dia_chi?.dia_chi_chi_tiet || ''}</div>
        <div style="color:#6b7280;font-size:12px;margin-bottom:8px">Môi giới: ${item.moi_gioi?.ten || '—'}</div>
        <a href="/khach-hang/chi-tiet-bat-dong-san/${item.id}"
          style="display:block;text-align:center;background:#2563eb;color:#fff;padding:6px 12px;border-radius:20px;font-size:13px;font-weight:600;text-decoration:none">
          Xem chi tiết
        </a>
      </div>`;
    marker.bindPopup(popupContent);
    markersLayer.addLayer(marker);
  });
};

const applyFilter = () => loadMapProperties();
const resetFilter = () => {
  filter.value = { minPrice: "", maxPrice: "", loai_id: "" };
  loadMapProperties();
};

const searchNearby = async () => {
  if (!nearby.value.lat || !nearby.value.lng) return;
  mapLoading.value = true;
  try {
    const res = await api.get("/khach-hang/map/nearby", {
      params: { lat: nearby.value.lat, lng: nearby.value.lng, radius: nearby.value.radius },
    });
    if (res.data?.data) {
      nearbyList.value = res.data.data;
      renderMarkers(res.data.data);
      if (map) map.setView([nearby.value.lat, nearby.value.lng], 14);
    }
  } catch (e) {
    console.error("Lỗi tìm gần đây:", e);
  } finally {
    mapLoading.value = false;
  }
};

const useMyLocation = () => {
  if (!navigator.geolocation) return;
  navigator.geolocation.getCurrentPosition((pos) => {
    nearby.value.lat = pos.coords.latitude.toFixed(6);
    nearby.value.lng = pos.coords.longitude.toFixed(6);
    if (map) map.setView([nearby.value.lat, nearby.value.lng], 14);
  });
};

const focusMarker = (item) => {
  if (!map || !item.dia_chi?.lat || !item.dia_chi?.lng) return;
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
  loadMapProperties();
});

onUnmounted(() => {
  if (map) { map.remove(); map = null; }
});
</script>

<style scoped>
.ban-do-page {
  display: flex;
  height: calc(100vh - 74px);
  overflow: hidden;
}

.filter-panel {
  width: 280px;
  min-width: 280px;
  background: #fff;
  border-right: 1px solid #e5e7eb;
  padding: 20px 16px;
  overflow-y: auto;
  flex-shrink: 0;
}

.map-container {
  flex: 1;
  position: relative;
}

.leaflet-map {
  width: 100%;
  height: 100%;
}

.map-loading {
  position: absolute;
  top: 16px;
  left: 50%;
  transform: translateX(-50%);
  background: rgba(255, 255, 255, 0.95);
  padding: 8px 16px;
  border-radius: 30px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  display: flex;
  align-items: center;
  z-index: 1000;
  font-size: 14px;
}

.nearby-item {
  padding: 8px 10px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  margin-bottom: 6px;
  cursor: pointer;
  transition: background 0.2s;
}

.nearby-item:hover {
  background: #f0f9ff;
  border-color: #3b82f6;
}

@media (max-width: 768px) {
  .ban-do-page {
    flex-direction: column;
    height: auto;
  }
  .filter-panel {
    width: 100%;
    border-right: none;
    border-bottom: 1px solid #e5e7eb;
  }
  .map-container {
    height: 450px;
  }
}
</style>
