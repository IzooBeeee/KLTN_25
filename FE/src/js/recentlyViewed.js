const KEY = "recently_viewed_bds";
const MAX = 10;

export function saveToRecentlyViewed(item) {
  if (!item?.id) return;
  try {
    const list = getRecentlyViewed();
    const filtered = list.filter((x) => x.id !== item.id);
    const entry = { ...item, viewed_at: new Date().toISOString() };
    filtered.unshift(entry);
    localStorage.setItem(KEY, JSON.stringify(filtered.slice(0, MAX)));
  } catch {}
}

export function getRecentlyViewed() {
  try {
    return JSON.parse(localStorage.getItem(KEY) || "[]");
  } catch {
    return [];
  }
}

export function clearRecentlyViewed() {
  localStorage.removeItem(KEY);
}
