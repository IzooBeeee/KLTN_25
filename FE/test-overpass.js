import fetch from 'node-fetch';

async function test() {
  const query = `
    [out:json];
    area["name"="Đà Nẵng"]->.searchArea;
    (
      relation["boundary"="administrative"]["admin_level"~"8|9|10"](area.searchArea);
    );
    out count;
  `;
  const res = await fetch('https://overpass-api.de/api/interpreter', {
    method: 'POST',
    body: query
  });
  const data = await res.json();
  console.log('OSM feature count:', data);
}

test().catch(console.error);
