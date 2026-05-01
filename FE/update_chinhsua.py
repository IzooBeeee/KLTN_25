import re

path = '/Users/vannhan/KLTN_25/FE/src/components/MoiGioi/ChinhSuaBDS/index.vue'
with open(path, 'r') as f:
    content = f.read()

# 1. Update imports
content = content.replace(
    'import { useRouter } from "vue-router";',
    'import { useRouter, useRoute } from "vue-router";'
)

# 2. Add route and state
content = content.replace(
    'const router = useRouter();',
    'const router = useRouter();\nconst route = useRoute();\nconst bdsId = route.params.id;\nconst deletedImages = ref([]);\nconst oldImages = ref([]);'
)

# 3. Add fetch logic and replace restoreFormFromLocal
fetch_logic = """
const fetchBDSDetail = async () => {
  try {
    const res = await api.get('/client/bat-dong-san/' + bdsId);
    if (res.data?.status) {
      const data = res.data.data;
      form.tieu_de = data.tieu_de;
      form.mo_ta = data.mo_ta;
      form.gia = data.gia;
      form.dien_tich = data.dien_tich;
      form.loai_id = data.loai_id;
      form.so_phong_ngu = data.so_phong_ngu || 1;
      form.so_phong_tam = data.so_phong_tam || 1;
      
      if (data.dia_chi) {
        form.tinh_id = data.dia_chi.tinh_id || "";
        form.quan_id = data.dia_chi.quan_id || "";
        form.phuong_id = data.dia_chi.phuong_xa_id || "";
        form.dia_chi_chi_tiet = data.dia_chi.dia_chi_chi_tiet || "";
        soNha.value = form.dia_chi_chi_tiet;
        form.latitude = data.dia_chi.latitude || null;
        form.longitude = data.dia_chi.longitude || null;
        
        // Trigger load quan_huyen
        if (form.tinh_id) await onTinhChange();
        if (form.quan_id) {
           // delay to let quan load
           setTimeout(() => onQuanChange(), 500);
        }
      }
      
      // Load images
      if (data.hinh_anh && data.hinh_anh.length > 0) {
        oldImages.value = data.hinh_anh;
        imagePreviewUrls.value = data.hinh_anh.map(img => getImageUrl(img.url));
      }
    }
  } catch (error) {
    console.error("Lỗi lấy dữ liệu BDS:", error);
    Swal.fire("Lỗi", "Không thể lấy dữ liệu bài đăng", "error");
  }
};
"""

content = content.replace('// ===== DATA & CACHE KEYS =====', fetch_logic + '\n// ===== DATA & CACHE KEYS =====')

content = content.replace(
    'restoreFormFromLocal();',
    'await fetchBDSDetail();'
)

# Remove autoSave
content = re.sub(r'autoSaveInterval = setInterval.*?\n\s*\}, 60000\);', '', content, flags=re.DOTALL)

# Modify removeImage to handle old images
remove_img_logic = """
const removeImage = (index) => {
  const isOldImage = index < oldImages.value.length;
  if (isOldImage) {
    deletedImages.value.push(oldImages.value[index].id);
    oldImages.value.splice(index, 1);
  } else {
    // new image
    const newIdx = index - oldImages.value.length;
    form.images.splice(newIdx, 1);
  }
  imagePreviewUrls.value.splice(index, 1);
};
"""
content = re.sub(r'const removeImage = \(index\) => \{.*?\n\};', remove_img_logic, content, flags=re.DOTALL)

# Modify submitForm
new_submit_logic = """
    formData.append("id", bdsId);
    if (deletedImages.value.length > 0) {
        deletedImages.value.forEach((id, idx) => {
            formData.append(`deleted_images[${idx}]`, id);
        });
    }
"""

content = content.replace(
    'const submitForm = async () => {',
    'const submitForm = async () => {'
)

content = content.replace(
    'formData.append("moi_gioi_id",',
    new_submit_logic + '\n    formData.append("moi_gioi_id",'
)

content = content.replace(
    'api.post("/moi-gioi/bds/create"',
    'api.post("/moi-gioi/bds/update"'
)

# UI texts
content = content.replace('Đăng Tin Mới', 'Chỉnh Sửa Tin')
content = content.replace('Đăng tin ngay', 'Cập nhật ngay')

with open(path, 'w') as f:
    f.write(content)
