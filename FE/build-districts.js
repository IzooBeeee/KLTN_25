import fs from 'fs-extra';

console.log("🚀 START");

const raw = fs.readFileSync('public/geojson/danang-districts.geojson', 'utf-8');
const data = JSON.parse(raw);

console.log("📦 Data loaded:", data.type);
console.log("👉 features:", data.features?.length);

function normalize(str) {
  return str
    ?.toLowerCase()
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .replace(/\s+/g, "-");
}

const districts = data.features.map(f => {
  const name = f.properties?.tags?.name || f.properties?.name;

  if (!name) return null;

  return {
    type: "Feature",
    properties: {
      name,
      slug: normalize(name)
    },
    geometry: f.geometry
  };
}).filter(Boolean);

fs.ensureDirSync('public/geojson');

fs.writeFileSync(
  'public/geojson/danang-districts.min.json',
  JSON.stringify({
    type: "FeatureCollection",
    features: districts
  }, null, 2)
);

console.log("✅ DONE districts:", districts.length);