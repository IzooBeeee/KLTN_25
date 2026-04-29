import fs from 'fs-extra';
import path from 'path';

function normalize(str) {
  return str
    ?.toLowerCase()
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .replace(/đ/g, "d")
    .replace(/[^a-z0-9 ]/g, "")
    .trim()
    .replace(/\s+/g, "-");
}

function checkCoordinates(coords, type) {
  if (type === 'Polygon') {
    return coords.every(ring => ring.every(pt => pt.length === 2 && typeof pt[0] === 'number' && typeof pt[1] === 'number' && pt[0] > 107 && pt[0] < 109 && pt[1] > 15 && pt[1] < 17));
  } else if (type === 'MultiPolygon') {
    return coords.every(poly => poly.every(ring => ring.every(pt => pt.length === 2 && typeof pt[0] === 'number' && typeof pt[1] === 'number' && pt[0] > 107 && pt[0] < 109 && pt[1] > 15 && pt[1] < 17)));
  }
  return false;
}

const PUBLIC_DIR = path.resolve('public/geojson');
const districtsFile = path.join(PUBLIC_DIR, 'danang-districts.min.json');
const subareasDir = path.join(path.resolve('public'), 'subareas');

const errors = [];

try {
  let districtsData;
  try {
    districtsData = JSON.parse(fs.readFileSync(districtsFile, 'utf8'));
  } catch (e) {
    errors.push(`[${districtsFile}] Invalid JSON: ${e.message}`);
    console.log(JSON.stringify(errors, null, 2));
    process.exit(0);
  }

  const validDistricts = new Set();
  
  if (districtsData.type !== 'FeatureCollection') {
    errors.push(`[danang-districts.min.json] type must be FeatureCollection, got ${districtsData.type}`);
  }

  districtsData.features.forEach((f, i) => {
    if (!['Polygon', 'MultiPolygon'].includes(f.geometry?.type)) {
      errors.push(`[danang-districts.min.json:feature_${i}] Invalid geometry type: ${f.geometry?.type}`);
    } else if (!checkCoordinates(f.geometry.coordinates, f.geometry.type)) {
      errors.push(`[danang-districts.min.json:feature_${i}] Invalid coordinates bounds (must be lng~108, lat~16)`);
    }

    const name = f.properties?.name;
    const slug = f.properties?.slug;
    
    if (!name) errors.push(`[danang-districts.min.json:feature_${i}] Missing name`);
    if (!slug) errors.push(`[danang-districts.min.json:feature_${i}] Missing slug`);
    else {
      validDistricts.add(slug);
      const expectedSlug = normalize(name);
      if (slug !== expectedSlug) {
        errors.push(`[danang-districts.min.json:feature_${i}] slug mismatch: got "${slug}", expected "${expectedSlug}"`);
      }
      
      const bNames = ["Phường Cẩm Lệ", "Phường Sơn Trà", "Phường Liên Chiểu", "Phường Thanh Khê", "Xã Hòa Vang", "Phường Hải Châu"];
      if (bNames.includes(name)) {
        errors.push(`[danang-districts.min.json:feature_${i}] Invalid name logically: ${name}`);
      }
    }
  });

  const subareaCounts = {
    'cam-le': 0, 'thanh-khe': 0, 'son-tra': 0, 'ngu-hanh-son': 0, 'lien-chieu': 0, 'hai-chau': 0, 'hoa-vang': 0
  };

  const allSlugs = new Set();

  if (fs.existsSync(subareasDir)) {
    const files = fs.readdirSync(subareasDir).filter(f => f.endsWith('.min.json'));
    
    files.forEach(file => {
      const p = path.join(subareasDir, file);
      try {
        const d = JSON.parse(fs.readFileSync(p, 'utf8'));
        if (d.type !== 'FeatureCollection') {
          errors.push(`[${file}] type must be FeatureCollection`);
        }
        
        d.features.forEach((f, i) => {
          if (!['Polygon', 'MultiPolygon'].includes(f.geometry?.type)) {
            errors.push(`[${file}:feature_${i}] Invalid geometry type: ${f.geometry?.type}`);
          } else if (!checkCoordinates(f.geometry.coordinates, f.geometry.type)) {
            errors.push(`[${file}:feature_${i}] Invalid coordinates bounds`);
          }

          const name = f.properties?.name;
          const slug = f.properties?.slug;
          const district = f.properties?.district;
          const type = f.properties?.type;

          if (!name) errors.push(`[${file}:feature_${i}] Missing name`);
          if (!slug) errors.push(`[${file}:feature_${i}] Missing slug`);
          if (!district) errors.push(`[${file}:feature_${i}] Missing district`);
          if (!type || !['phuong', 'xa'].includes(type)) {
            errors.push(`[${file}:feature_${i}] Invalid or missing type (must be phuong|xa): ${type}`);
          }

          if (name) {
            const expectedSlug = normalize(name);
            if (slug && slug !== expectedSlug) {
              errors.push(`[${file}:feature_${i}] slug mismatch: got "${slug}", expected "${expectedSlug}"`);
            }
            if (slug) {
              if (allSlugs.has(slug)) errors.push(`[${file}:feature_${i}] Duplicate slug: ${slug}`);
              allSlugs.add(slug);
            }

            const bNames = ["Phường Cẩm Lệ", "Phường Sơn Trà", "Phường Liên Chiểu", "Phường Thanh Khê", "Xã Hòa Vang"];
            if (bNames.includes(name)) {
              errors.push(`[${file}:feature_${i}] Invalid name logically: ${name}`);
            }
            if (district === 'hoa-vang' && type !== 'xa') {
              errors.push(`[${file}:feature_${i}] Hòa Vang must have xa, got: ${type}`);
            }
            if (name === "Phường Hải Châu") {
               errors.push(`[${file}:feature_${i}] Hải Châu cannot just be "Phường Hải Châu", needs 1,2..`);
            }
          }

          if (district && !validDistricts.has(district)) {
             errors.push(`[${file}:feature_${i}] District slug not found in danang-districts.min.json: ${district}`);
          }

          if (district) {
             let key = district;
             if (key.startsWith('quan-')) key = key.replace('quan-', '');
             if (key.startsWith('huyen-')) key = key.replace('huyen-', '');
             if (subareaCounts[key] !== undefined) subareaCounts[key]++;
             else subareaCounts[district] = 1;
          }
        });
      } catch (e) {
        errors.push(`[${file}] Invalid JSON or error reading: ${e.message}`);
      }
    });
  } else {
    errors.push(`Directory not found: ${subareasDir}`);
  }

  // Completeness check
  if (subareaCounts['cam-le'] < 6) errors.push(`[Completeness] Cẩm Lệ has only ${subareaCounts['cam-le']} wards (expected ~6)`);
  if (subareaCounts['thanh-khe'] < 10) errors.push(`[Completeness] Thanh Khê has only ${subareaCounts['thanh-khe']} wards (expected ~10)`);
  if (subareaCounts['son-tra'] < 5) errors.push(`[Completeness] Sơn Trà has only ${subareaCounts['son-tra']} wards (expected ~5)`);
  if (subareaCounts['ngu-hanh-son'] < 4) errors.push(`[Completeness] Ngũ Hành Sơn has only ${subareaCounts['ngu-hanh-son']} wards (expected ~4)`);
  if (subareaCounts['lien-chieu'] < 5) errors.push(`[Completeness] Liên Chiểu has only ${subareaCounts['lien-chieu']} wards (expected ~5)`);
  if (subareaCounts['hai-chau'] < 10) errors.push(`[Completeness] Hải Châu has only ${subareaCounts['hai-chau']} wards (expected ~13)`);

  console.log(JSON.stringify({errors, counts: subareaCounts, totalErrors: errors.length}, null, 2));

} catch (err) {
  console.error("Script error:", err);
}
