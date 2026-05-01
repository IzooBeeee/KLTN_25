import re

# 1. Fix TrangChu
path_trang_chu = '/Users/vannhan/KLTN_25/FE/src/components/KhachHang/TrangChu/index.vue'
with open(path_trang_chu, 'r') as f:
    content_tc = f.read()

content_tc = content_tc.replace(
    'isExclusive: Math.random() > 0.8',
    'isExclusive: item.is_noi_bat || false'
)
with open(path_trang_chu, 'w') as f:
    f.write(content_tc)

# 2. Fix DanhSachBatDongSan
path_ds = '/Users/vannhan/KLTN_25/FE/src/components/KhachHang/DanhSachBatDongSan/index.vue'
with open(path_ds, 'r') as f:
    content_ds = f.read()

# Fix is_featured -> is_noi_bat
content_ds = content_ds.replace(
    'v-if="bds.is_featured"',
    'v-if="bds.is_noi_bat"'
)

# Fix location display
content_ds = content_ds.replace(
    "{{ bds.dia_chi?.ten_quan || bds.location || '—' }}",
    "{{ bds.location || '—' }}"
)

# Fix formatPriceFull implementation to format in Tỷ / Triệu
new_format_price = """formatPriceFull(g) { 
      if (!g) return 'Liên hệ'; 
      if (g >= 1_000_000_000) return Math.floor(g / 1_000_000_000) + ' Tỷ'; 
      if (g >= 1_000_000) return Math.floor(g / 1_000_000) + ' Triệu'; 
      return new Intl.NumberFormat('vi-VN').format(g) + ' đ'; 
    }"""
content_ds = re.sub(r"formatPriceFull\(g\)\s*\{.*?\}", new_format_price, content_ds, flags=re.DOTALL)

with open(path_ds, 'w') as f:
    f.write(content_ds)

