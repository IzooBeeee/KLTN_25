async function test() {
  const query = `
    [out:json];
    area["name"="Đà Nẵng"]->.searchArea;
    (
      relation["boundary"="administrative"]["admin_level"~"8|9|10"](area.searchArea);
    );
    out tags;
  `;
  try {
    const res = await fetch('https://overpass-api.de/api/interpreter', {
      method: 'POST',
      body: query
    });
    const data = await res.json();
    console.log('Total wards in Da Nang (admin_level 8/9/10):', data.elements.length);
    if (data.elements.length > 0) {
      console.log('Examples:', data.elements.slice(0, 5).map(e => e.tags.name));
    }
  } catch(e) {
    console.error(e);
  }
}
test();
