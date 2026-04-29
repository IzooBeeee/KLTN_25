import fs from 'fs';
import path from 'path';

const dir = path.resolve('public/geojson/subareas');

const districts = {
  "thanh-khe": ["Thanh Khê Đông", "Thanh Khê Tây", "Xuân Hà", "Tam Thuận", "Chính Gián", "Thạc Gián", "Vĩnh Trung", "Tân Chính", "Hòa Khê", "An Khê"],
  "lien-chieu": ["Hòa Minh", "Hòa Khánh Nam", "Hòa Khánh Bắc", "Hòa Hiệp Nam", "Hòa Hiệp Bắc"],
  "son-tra": ["Thọ Quang", "Nại Hiên Đông", "Mân Thái", "An Hải Bắc", "An Hải Tây", "An Hải Đông", "Phước Mỹ"],
  "ngu-hanh-son": ["Mỹ An", "Khuê Mỹ", "Hòa Hải", "Hòa Quý"]
};

function normalizeSlug(name) {
  return "phuong-" + name.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace(/đ/g, "d").replace(/\s+/g, "-");
}

for (const [dist, wards] of Object.entries(districts)) {
  const features = wards.map(w => ({
    type: "Feature",
    properties: {
      name: "Phường " + w,
      slug: normalizeSlug(w),
      district: dist,
      type: "phuong"
    },
    geometry: null
  }));

  const fc = {
    type: "FeatureCollection",
    features: features
  };

  fs.writeFileSync(path.join(dir, `${dist}.min.json`), JSON.stringify(fc, null, 2));
}

console.log("Restored 4 files successfully.");
