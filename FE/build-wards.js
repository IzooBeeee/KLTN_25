import fs from 'fs-extra';
import path from 'path';

// Cấu hình đường dẫn
const EXPORT_FILE = path.resolve('public/geojson/export.geojson');
const TARGET_DIR = path.resolve('public/geojson/subareas');

console.log("🚀 BẮT ĐẦU XỬ LÝ MAPPING PHƯỜNG/XÃ...\n");

// 1. Hàm chuẩn hóa tên cực mạnh
function normalize(str) {
  if (!str) return '';
  let s = str
    .toLowerCase()
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "") // Bỏ dấu tiếng Việt
    .replace(/đ/g, "d")              // Chuyển đ -> d
    .replace(/[^a-z0-9\s-]/g, "")    // Chỉ giữ lại chữ, số, khoảng trắng và gạch ngang
    .replace(/\s+/g, "-");           // Khoảng trắng -> gạch ngang

  // Bỏ chữ "phuong", "xa" ở đầu (cả dạng có gạch ngang hoặc khoảng trắng)
  s = s.replace(/^(phuong|xa)-/, '');
  s = s.replace(/^(phuong|xa)\s+/, '');
  
  // Xóa gạch ngang thừa
  s = s.replace(/-+/g, "-").replace(/^-|-$/g, '');
  
  return s;
}

// Hàm auto repair JSON
function parseFixJson(raw) {
  try {
    return JSON.parse(raw);
  } catch (e) {
    console.log("  [Auto-Repair] Cố gắng sửa lỗi JSON...");
    let fixed = raw.trim();
    // Xử lý trường hợp file chứa nhiều object cạnh nhau không nằm trong mảng
    if (fixed.startsWith('{') && fixed.endsWith('}') && fixed.includes('}\n{')) {
      fixed = '[' + fixed.replace(/}\s*\{/g, '},{') + ']';
      try { return JSON.parse(fixed); } catch (err) {}
    }
    // Trả về object rỗng để không crash
    return {}; 
  }
}

async function run() {
  if (!fs.existsSync(EXPORT_FILE)) {
    console.error(`❌ LỖI: Không tìm thấy file gốc: ${EXPORT_FILE}`);
    return;
  }

  console.log("📂 Đang đọc dữ liệu từ export.geojson...");
  let exportRaw = fs.readFileSync(EXPORT_FILE, 'utf8');
  let exportData = parseFixJson(exportRaw);
  
  if (!exportData.features || !Array.isArray(exportData.features)) {
    console.error("❌ LỖI: export.geojson không hợp lệ (không có features).");
    return;
  }

  const geomMap = new Map();
  const geomList = []; // Dùng cho fuzzy match

  // 2. Extract toàn bộ features từ OSM
  exportData.features.forEach(f => {
    const props = f.properties || (f.tags ? f.tags : {});
    const adminLvl = props.admin_level || props.tags?.admin_level;
    const boundary = props.boundary || props.tags?.boundary;
    
    // Check boundary=administrative và admin_level=8 (Lấy thêm 6 vì OSM VN hay nhầm cấp)
    if (boundary === 'administrative' && (adminLvl === '8' || adminLvl === '6')) {
      const name = props['name:vi'] || props['name'] || props['name:en'] || 
                   props.tags?.['name:vi'] || props.tags?.['name'];
                   
      if (name && ['Polygon', 'MultiPolygon'].includes(f.geometry?.type)) {
        const slug = normalize(name);
        geomMap.set(slug, f.geometry);
        geomList.push({ slug, name, geometry: f.geometry });
      }
    }
  });

  console.log(`🗺️  Đã extract ${geomMap.size} polygons hợp lệ từ OSM.\n`);

  fs.ensureDirSync(TARGET_DIR);
  
  // Đọc danh sách file trong thư mục đích (nếu user để source trong đây)
  const files = fs.readdirSync(TARGET_DIR).filter(f => f.endsWith('.min.json'));
  
  if (files.length === 0) {
    console.log(`⚠️  Không tìm thấy file .min.json nào trong ${TARGET_DIR}`);
    return;
  }

  let totalProcessed = 0;
  let totalMatched = 0;
  let totalFailed = 0;
  const failedNames = [];

  // 3. Xử lý từng file đích
  for (const file of files) {
    const filePath = path.join(TARGET_DIR, file);
    const rawData = fs.readFileSync(filePath, 'utf8');
    const districtData = parseFixJson(rawData);

    // Chuẩn hóa cấu trúc để lấy được mảng features
    let inputFeatures = [];
    if (Array.isArray(districtData)) {
      inputFeatures = districtData;
    } else if (districtData.features && Array.isArray(districtData.features)) {
      inputFeatures = districtData.features;
    } else if (districtData.type === 'Feature') {
      inputFeatures = [districtData];
    } else if (Object.keys(districtData).length > 0) {
      inputFeatures = [districtData]; // Cố ép thành feature
    }

    if (inputFeatures.length === 0) {
      console.log(`⚠️  [${file}] Không có features nào, ghi đè thành FeatureCollection rỗng.`);
    }

    const outputFeatures = [];

    // 4. Fill geometry
    inputFeatures.forEach(feature => {
      // Auto repair cấu trúc feature nếu thiếu
      if (!feature.type) feature.type = "Feature";
      if (!feature.properties) feature.properties = {};

      const name = feature.properties.name || feature.name || "Unknown";
      let slug = feature.properties.slug ? normalize(feature.properties.slug) : normalize(name);
      
      totalProcessed++;

      // 1. Exact Match
      let geometry = geomMap.get(slug);

      // 2. Fuzzy Match (nếu không exact match)
      if (!geometry) {
        for (const item of geomList) {
          if (item.slug.includes(slug) || slug.includes(item.slug)) {
            geometry = item.geometry;
            break;
          }
        }
      }

      if (geometry) {
        feature.geometry = geometry;
        outputFeatures.push(feature);
        totalMatched++;
      } else {
        console.warn(`  ⚠️ WARNING: Không tìm thấy geometry cho: "${name}" (slug đã chuẩn hóa: ${slug})`);
        feature.geometry = null; // Gán null nếu fail để không crash
        outputFeatures.push(feature);
        totalFailed++;
        failedNames.push(name);
      }
    });

    // 5. Ghi đè file với chuẩn FeatureCollection
    fs.writeJsonSync(filePath, {
      type: "FeatureCollection",
      features: outputFeatures
    }, { spaces: 2 });
    
    console.log(`✅ [${file}] Đã xử lý ${outputFeatures.length} features.`);
  }

  // 6. Logging tổng kết
  console.log(`\n================================`);
  console.log(`📊 TỔNG KẾT BÁO CÁO:`);
  console.log(`- Tổng số phường/xã đã xử lý : ${totalProcessed}`);
  console.log(`- Thành công (có geometry)   : ${totalMatched}`);
  console.log(`- Thất bại (geometry = null) : ${totalFailed}`);
  
  if (totalFailed > 0) {
    console.log(`\nDanh sách các phường/xã thất bại:`);
    failedNames.forEach(n => console.log(`  - ${n}`));
  }
  console.log(`================================\n`);
}

run().catch(err => {
  console.error("❌ CRITICAL ERROR (Caught to prevent crash):", err);
});
