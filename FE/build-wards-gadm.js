import fs from 'fs-extra';
import path from 'path';

const GADM_FILE = path.resolve('public/geojson/export.geojson');
const TARGET_DIR = path.resolve('public/geojson/subareas');

function normalize(str) {
  if (!str) return '';
  let s = str.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace(/đ/g, "d").replace(/[^a-z0-9]/g, "");
  s = s.replace(/^(phuong|xa)/, '');
  return s;
}

async function run() {
  const gadmData = fs.readJsonSync(GADM_FILE);
  const districtMap = new Map();

  // 1. Group by District (NAME_2)
  gadmData.features.forEach(f => {
    if (f.properties && f.properties.NAME_1 === 'ĐàNẵng') {
      const distName = f.properties.NAME_2;
      const wardName = f.properties.NAME_3;
      
      if (!distName || !wardName) return;
      if (!['Polygon', 'MultiPolygon'].includes(f.geometry?.type)) return;

      const distSlug = normalize(distName).replace(/[^a-z0-9]/g, "-").replace(/-+/g, "-");
      
      if (!districtMap.has(distSlug)) {
        districtMap.set(distSlug, []);
      }
      
      // Make a properly dashed slug for the ward
      let wardSlug = wardName.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace(/đ/g, "d").replace(/[^a-z0-9\s-]/g, "").replace(/\s+/g, "-");
      wardSlug = wardSlug.replace(/^(phuong|xa)-/, '').replace(/^(phuong|xa)\s+/, '').replace(/-+/g, "-").replace(/^-|-$/g, '');

      const feature = {
        type: "Feature",
        properties: {
          name: wardName,
          slug: wardSlug,
          district: distName,
          type: f.properties.TYPE_3 || "Phường/Xã"
        },
        geometry: f.geometry
      };
      
      districtMap.get(distSlug).push(feature);
    }
  });

  console.log(`🗺️  Đã tìm thấy dữ liệu cho ${districtMap.size} quận/huyện của Đà Nẵng trong GADM.`);
  let totalMapped = 0;

  // 2. Write new JSON files
  for (const [distSlug, features] of districtMap.entries()) {
    // Custom district slug mapping for Da Nang
    let targetSlug = distSlug;
    if (distSlug === 'camle') targetSlug = 'cam-le';
    if (distSlug === 'haichau') targetSlug = 'hai-chau';
    if (distSlug === 'hoavang') targetSlug = 'hoa-vang';
    if (distSlug === 'lienchieu') targetSlug = 'lien-chieu';
    if (distSlug === 'nguhanhson') targetSlug = 'ngu-hanh-son';
    if (distSlug === 'sontra') targetSlug = 'son-tra';
    if (distSlug === 'thanhkhe') targetSlug = 'thanh-khe';

    const filePath = path.join(TARGET_DIR, `${targetSlug}.min.json`);
    
    fs.writeJsonSync(filePath, { type: "FeatureCollection", features: features }, { spaces: 2 });
    console.log(`✅ [${targetSlug}.min.json] Tạo mới thành công ${features.length} features.`);
    totalMapped += features.length;
  }
  
  console.log(`🎉 Xong! Đã map thành công ${totalMapped} features.`);
}

run().catch(console.error);
