async function test() {
  const query = `
    [out:json][timeout:25];
    area["name"="Đà Nẵng"]->.a;
    (
      relation["boundary"="administrative"](area.a);
    );
    out tags;
  `;
  try {
    const res = await fetch('https://overpass-api.de/api/interpreter', {
      method: 'POST',
      body: query
    });
    const data = await res.json();
    const adminLevels = {};
    const names = [];
    data.elements.forEach(e => {
      const lvl = e.tags.admin_level || 'none';
      adminLevels[lvl] = (adminLevels[lvl] || 0) + 1;
      if (lvl === '8' || lvl === '9' || lvl === '10' || e.tags.name.includes('Phường')) {
        names.push(e.tags.name);
      }
    });
    console.log('Admin levels:', adminLevels);
    console.log('Wards found:', names.slice(0, 10));
    console.log('Total wards:', names.length);
  } catch(e) {
    console.error(e);
  }
}
test();
