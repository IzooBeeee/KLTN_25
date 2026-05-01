import re

path = '/Users/vannhan/KLTN_25/FE/src/components/MoiGioi/QuanLyBDS/index.vue'
with open(path, 'r') as f:
    content = f.read()

# Cast tinh_id, quan_id, phuong_id from API
content = content.replace(
    'editingProperty.value.tinh_id = detail.dia_chi.tinh_id || "";',
    'editingProperty.value.tinh_id = detail.dia_chi.tinh_id ? String(detail.dia_chi.tinh_id) : "";'
)
content = content.replace(
    'editingProperty.value.quan_id = detail.dia_chi.quan_id || "";',
    'editingProperty.value.quan_id = detail.dia_chi.quan_id ? String(detail.dia_chi.quan_id) : "";'
)
content = content.replace(
    'editingProperty.value.phuong_id = detail.dia_chi.phuong_xa_id || "";',
    'editingProperty.value.phuong_id = detail.dia_chi.phuong_xa_id ? String(detail.dia_chi.phuong_xa_id) : "";'
)

# Also cast loai_id
content = content.replace(
    'loai_id: property.raw?.loai_id || "",',
    'loai_id: property.raw?.loai_id ? String(property.raw.loai_id) : "",'
)

# And in loadInitialData, map options
content = content.replace(
    'if (loaiRes.data?.status) loaiBDSList.value = loaiRes.data.data;',
    'if (loaiRes.data?.status) loaiBDSList.value = loaiRes.data.data.map(x => ({...x, id: String(x.id)}));'
)
content = content.replace(
    'if (tinhRes.data?.status) tinhThanhList.value = tinhRes.data.data;',
    'if (tinhRes.data?.status) tinhThanhList.value = tinhRes.data.data.map(x => ({...x, id: String(x.id)}));'
)

# In onTinhChange, map options
content = content.replace(
    'if (res.data?.status) quanHuyenList.value = res.data.data;',
    'if (res.data?.status) quanHuyenList.value = res.data.data.map(x => ({...x, id: String(x.id)}));'
)

# In onQuanChange, map options
content = content.replace(
    'if (res.data?.status) phuongList.value = res.data.data;',
    'if (res.data?.status) phuongList.value = res.data.data.map(x => ({...x, id: String(x.id)}));'
)

with open(path, 'w') as f:
    f.write(content)
