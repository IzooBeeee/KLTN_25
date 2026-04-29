async function test() {
  const query = `
    [out:json][timeout:25];
    area["name"="Đà Nẵng"]->.searchArea;
    (
      relation["boundary"="administrative"]["admin_level"~"8|9|10"](area.searchArea);
    );
    out count;
  `;
  try {
    const res = await fetch('https://overpass-api.de/api/interpreter', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'data=' + encodeURIComponent(query)
    });
    const data = await res.json();
    console.log('Result:', data);
  } catch(e) {
    console.error(e);
  }
}
test();
