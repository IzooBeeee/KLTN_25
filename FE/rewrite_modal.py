import re

path = '/Users/vannhan/KLTN_25/FE/src/components/MoiGioi/QuanLyBDS/index.vue'
with open(path, 'r') as f:
    content = f.read()

# 1. Update the Modal HTML
new_modal_html = """
        <div class="modal-body px-4 py-4">
          <div v-if="isLoadingDetail" class="text-center py-5 text-muted">
            <div class="spinner-border text-primary mb-3" role="status"></div>
            <p>Đang tải chi tiết...</p>
          </div>
          <div v-else class="row g-4">
            <!-- Thông tin cơ bản -->
            <div class="col-12"><h6 class="fw-bold text-primary border-bottom pb-2">1. Thông tin cơ bản</h6></div>
            <div class="col-12">
              <label class="form-label text-muted fw-bold small text-uppercase">📌 Tiêu Đề</label>
              <input v-model="editingProperty.title" type="text" class="form-control form-control-lg bg-light border-0 rounded-3 fs-6" />
            </div>
            <div class="col-md-6">
              <label class="form-label text-muted fw-bold small text-uppercase">Loại Bất Động Sản</label>
              <select v-model="editingProperty.loai_id" class="form-select form-control-lg bg-light border-0 rounded-3 fs-6">
                <option value="">Chọn loại</option>
                <option v-for="loai in loaiBDSList" :key="loai.id" :value="loai.id">{{ loai.ten }}</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label text-muted fw-bold small text-uppercase">💰 Giá (VNĐ)</label>
              <input v-model="editingProperty.gia" type="number" class="form-control form-control-lg bg-light border-0 rounded-3 fs-6 fw-bold text-primary" />
            </div>
            <div class="col-md-4">
              <label class="form-label text-muted fw-bold small text-uppercase">📐 Diện Tích (m²)</label>
              <input v-model="editingProperty.dien_tich" type="number" class="form-control form-control-lg bg-light border-0 rounded-3 fs-6" />
            </div>
            <div class="col-md-4">
              <label class="form-label text-muted fw-bold small text-uppercase">🛏️ Phòng Ngủ</label>
              <input v-model.number="editingProperty.so_phong_ngu" type="number" class="form-control form-control-lg bg-light border-0 rounded-3 fs-6" />
            </div>
            <div class="col-md-4">
              <label class="form-label text-muted fw-bold small text-uppercase">🚿 Phòng Tắm</label>
              <input v-model.number="editingProperty.so_phong_tam" type="number" class="form-control form-control-lg bg-light border-0 rounded-3 fs-6" />
            </div>

            <!-- Vị trí -->
            <div class="col-12 mt-4"><h6 class="fw-bold text-primary border-bottom pb-2">2. Vị trí</h6></div>
            <div class="col-md-4">
              <label class="form-label text-muted fw-bold small text-uppercase">Tỉnh / Thành</label>
              <select v-model="editingProperty.tinh_id" @change="onTinhChange" class="form-select form-control-lg bg-light border-0 rounded-3 fs-6">
                <option value="">Chọn tỉnh</option>
                <option v-for="tinh in tinhThanhList" :key="tinh.id" :value="tinh.id">{{ tinh.ten }}</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label text-muted fw-bold small text-uppercase">Quận / Huyện</label>
              <select v-model="editingProperty.quan_id" @change="onQuanChange" class="form-select form-control-lg bg-light border-0 rounded-3 fs-6">
                <option value="">Chọn quận</option>
                <option v-for="quan in quanHuyenList" :key="quan.id" :value="quan.id">{{ quan.ten }}</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label text-muted fw-bold small text-uppercase">Phường / Xã</label>
              <select v-model="editingProperty.phuong_id" class="form-select form-control-lg bg-light border-0 rounded-3 fs-6">
                <option value="">Chọn phường</option>
                <option v-for="phuong in phuongList" :key="phuong.id" :value="phuong.id">{{ phuong.ten }}</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label text-muted fw-bold small text-uppercase">Địa chỉ chi tiết</label>
              <input v-model="editingProperty.dia_chi_chi_tiet" type="text" class="form-control form-control-lg bg-light border-0 rounded-3 fs-6" />
            </div>

            <!-- Hình ảnh -->
            <div class="col-12 mt-4"><h6 class="fw-bold text-primary border-bottom pb-2">3. Hình ảnh</h6></div>
            <div class="col-12">
              <div class="d-flex flex-wrap gap-2 mb-3">
                <div v-for="(img, idx) in imagePreviewUrls" :key="idx" class="position-relative" style="width: 100px; height: 100px; border-radius: 8px; overflow: hidden; border: 1px solid #ddd;">
                  <img :src="img" class="w-100 h-100" style="object-fit: cover" />
                  <button @click="removeImage(idx)" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 p-0" style="width: 20px; height: 20px; line-height: 1;">&times;</button>
                </div>
                <div class="position-relative" style="width: 100px; height: 100px; border-radius: 8px; border: 2px dashed #0d6efd; display: flex; align-items: center; justify-content: center; cursor: pointer;" @click="$refs.fileInput.click()">
                  <span class="fs-3 text-primary">+</span>
                </div>
                <input ref="fileInput" type="file" multiple accept="image/*" class="d-none" @change="handleFileSelect" />
              </div>
            </div>

            <!-- Mô tả -->
            <div class="col-12 mt-4"><h6 class="fw-bold text-primary border-bottom pb-2">4. Mô tả chi tiết</h6></div>
            <div class="col-12">
              <textarea v-model="editingProperty.mo_ta" rows="6" class="form-control bg-light border-0 rounded-3 fs-6"></textarea>
            </div>
          </div>
        </div>
"""
# Replace old modal body
content = re.sub(r'<div class="modal-body px-4 py-4">.*?</div>\s*<div class="modal-footer', new_modal_html + '\n        <div class="modal-footer', content, flags=re.DOTALL)

# 2. Add state variables in script
new_state = """
const isLoadingDetail = ref(false);
const loaiBDSList = ref([]);
const tinhThanhList = ref([]);
const quanHuyenList = ref([]);
const phuongList = ref([]);
const oldImages = ref([]);
const newImages = ref([]);
const deletedImages = ref([]);
const imagePreviewUrls = ref([]);
"""
content = content.replace('const isSubmitting = ref(false);', 'const isSubmitting = ref(false);\n' + new_state)

# 3. Add handleEdit logic
new_handleEdit = """
const handleEdit = async (property) => {
  editingProperty.value = {
    id: property.id,
    title: property.title,
    dia_chi_id: "",
    gia: parseInt(property.raw?.gia) || 0,
    dien_tich: parseInt(property.raw?.dien_tich) || 0,
    so_phong_ngu: parseInt(property.raw?.so_phong_ngu) || 0,
    so_phong_tam: parseInt(property.raw?.so_phong_tam) || 0,
    mo_ta: property.raw?.mo_ta || "",
    loai_id: property.raw?.loai_id || "",
    tinh_id: "",
    quan_id: "",
    phuong_id: "",
    dia_chi_chi_tiet: "",
    latitude: null,
    longitude: null,
  };
  
  showEditModal.value = true;
  isLoadingDetail.value = true;
  
  // Reset images
  oldImages.value = [];
  newImages.value = [];
  deletedImages.value = [];
  imagePreviewUrls.value = [];

  try {
    const res = await api.get(`/client/bat-dong-san/${property.id}`);
    if (res.data?.status) {
      const detail = res.data.data;
      editingProperty.value.mo_ta = detail.mo_ta || "";
      
      if (detail.dia_chi) {
        editingProperty.value.tinh_id = detail.dia_chi.tinh_id || "";
        editingProperty.value.quan_id = detail.dia_chi.quan_id || "";
        editingProperty.value.phuong_id = detail.dia_chi.phuong_xa_id || "";
        editingProperty.value.dia_chi_chi_tiet = detail.dia_chi.dia_chi_chi_tiet || "";
        editingProperty.value.latitude = detail.dia_chi.latitude;
        editingProperty.value.longitude = detail.dia_chi.longitude;

        if (editingProperty.value.tinh_id) await onTinhChange();
        if (editingProperty.value.quan_id) await onQuanChange();
      }

      if (detail.hinh_anh) {
        oldImages.value = detail.hinh_anh;
        imagePreviewUrls.value = detail.hinh_anh.map(img => getImageUrl(img));
      }
    }
  } catch (e) {
    console.error("Lỗi lấy chi tiết BDS", e);
  } finally {
    isLoadingDetail.value = false;
  }
};
"""
content = re.sub(r'const handleEdit = \(property\) => \{.*?\n\};', new_handleEdit, content, flags=re.DOTALL)

# 4. Add dropdown & image logic
new_logic = """
const onTinhChange = async () => {
  quanHuyenList.value = [];
  editingProperty.value.quan_id = "";
  editingProperty.value.phuong_id = "";
  if (!editingProperty.value.tinh_id) return;
  try {
    const res = await api.get(`/quan-huyen-by-tinh`, { params: { tinh_id: editingProperty.value.tinh_id } });
    if (res.data?.status) quanHuyenList.value = res.data.data;
  } catch (error) {}
};

const onQuanChange = async () => {
  phuongList.value = [];
  editingProperty.value.phuong_id = "";
  if (!editingProperty.value.quan_id) return;
  try {
    const res = await api.get(`/phuong-xa-by-quan`, { params: { quan_id: editingProperty.value.quan_id } });
    if (res.data?.status) phuongList.value = res.data.data;
  } catch (error) {}
};

const handleFileSelect = (event) => {
  const files = Array.from(event.target.files);
  files.forEach((file) => {
    newImages.value.push(file);
    const reader = new FileReader();
    reader.onload = (e) => imagePreviewUrls.value.push(e.target.result);
    reader.readAsDataURL(file);
  });
};

const removeImage = (index) => {
  if (index < oldImages.value.length) {
    deletedImages.value.push(oldImages.value[index].id);
    oldImages.value.splice(index, 1);
  } else {
    const newIdx = index - oldImages.value.length;
    newImages.value.splice(newIdx, 1);
  }
  imagePreviewUrls.value.splice(index, 1);
};
"""
content = content.replace('const submitEdit = async () => {', new_logic + '\nconst submitEdit = async () => {')

# 5. Modify submitEdit to use FormData
new_submitEdit = """
const submitEdit = async () => {
  if (!editingProperty.value.title.trim()) {
    alert("⚠️ Vui lòng nhập tiêu đề");
    return;
  }
  isSubmitting.value = true;
  errorMessage.value = "";

  try {
    const formData = new FormData();
    formData.append("id", editingProperty.value.id);
    formData.append("tieu_de", editingProperty.value.title);
    formData.append("gia", editingProperty.value.gia || 0);
    formData.append("dien_tich", editingProperty.value.dien_tich || 0);
    formData.append("so_phong_ngu", editingProperty.value.so_phong_ngu || 0);
    formData.append("so_phong_tam", editingProperty.value.so_phong_tam || 0);
    formData.append("mo_ta", editingProperty.value.mo_ta || "");
    if (editingProperty.value.loai_id) formData.append("loai_id", editingProperty.value.loai_id);
    if (editingProperty.value.tinh_id) formData.append("tinh_id", editingProperty.value.tinh_id);
    if (editingProperty.value.quan_id) formData.append("quan_id", editingProperty.value.quan_id);
    if (editingProperty.value.phuong_id) formData.append("phuong_id", editingProperty.value.phuong_id);
    if (editingProperty.value.dia_chi_chi_tiet) formData.append("dia_chi_chi_tiet", editingProperty.value.dia_chi_chi_tiet);
    if (editingProperty.value.latitude) formData.append("latitude", editingProperty.value.latitude);
    if (editingProperty.value.longitude) formData.append("longitude", editingProperty.value.longitude);

    deletedImages.value.forEach((id, idx) => {
      formData.append(`deleted_images[${idx}]`, id);
    });

    newImages.value.forEach((file) => {
      formData.append("hinh_anh[]", file);
    });

    const res = await api.post("/moi-gioi/bds/update", formData, {
      headers: { "Content-Type": "multipart/form-data" },
    });

    if (res.data.status) {
      successMessage.value = "✅ Cập nhật thành công! Đang chờ duyệt lại...";
      showEditModal.value = false;
      setTimeout(() => {
        loadBatDongSan();
        successMessage.value = "";
      }, 2000);
    } else {
      errorMessage.value = `❌ ${res.data.message || "Cập nhật thất bại"}`;
    }
  } catch (error) {
    const message = error.response?.data?.message || error.message || "Lỗi không xác định";
    errorMessage.value = `❌ Cập nhật thất bại: ${message}`;
  } finally {
    isSubmitting.value = false;
  }
};
"""
content = re.sub(r'const submitEdit = async \(\) => \{.*?\n\};', new_submitEdit, content, flags=re.DOTALL)

# 6. Load initial data on mount
load_initial = """
const loadInitialData = async () => {
  try {
    const [loaiRes, tinhRes] = await Promise.all([
      api.get("/loai-bat-dong-san"),
      api.get("/tinh-thanh")
    ]);
    if (loaiRes.data?.status) loaiBDSList.value = loaiRes.data.data;
    if (tinhRes.data?.status) tinhThanhList.value = tinhRes.data.data;
  } catch (e) {}
};
"""
content = content.replace('onMounted(() => {', load_initial + '\nonMounted(() => {\n  loadInitialData();')

with open(path, 'w') as f:
    f.write(content)
