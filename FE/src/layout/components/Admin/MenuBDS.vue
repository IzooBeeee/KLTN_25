<template>
  <aside class="admin-sidebar">
    <div class="admin-sidebar__brand">
      <p class="admin-sidebar__brand-name">Architectural Curator</p>
      <p class="admin-sidebar__brand-subtitle">Real Estate Authority</p>
    </div>

    <nav class="admin-sidebar__nav" aria-label="Menu quản trị">
      <div class="admin-sidebar__group">
        <RouterLink
          v-for="item in primaryItems"
          :key="item.id"
          :to="item.route"
          class="admin-sidebar__link"
          :class="{ 'is-active': route.path.startsWith(item.route) }"
        >
          <span class="material-symbols-outlined">{{ item.icon }}</span>
          <span class="admin-sidebar__label">{{ item.label }}</span>
        </RouterLink>
      </div>

      <div class="admin-sidebar__divider"></div>

      <div class="admin-sidebar__group">
        <RouterLink
          v-for="item in secondaryItems"
          :key="item.id"
          :to="item.route"
          class="admin-sidebar__link"
          :class="{ 'is-active': route.path.startsWith(item.route) }"
        >
          <span class="material-symbols-outlined">{{ item.icon }}</span>
          <span class="admin-sidebar__label">{{ item.label }}</span>
        </RouterLink>
      </div>
    </nav>
  </aside>
</template>

<script setup>
import { RouterLink, useRoute } from "vue-router";

const route = useRoute();

const primaryItems = [
  {
    id: "dashboard",
    label: "Dashboard",
    icon: "dashboard",
    route: "/admin/dashboard",
  },
  {
    id: "customers",
    label: "Quản lý khách hàng",
    icon: "group",
    route: "/admin/khach-hang",
  },
  {
    id: "brokers",
    label: "Quản lý môi giới",
    icon: "handshake",
    route: "/admin/moi-gioi",
  },
  {
    id: "properties",
    label: "Quản lý bất động sản",
    icon: "domain",
    route: "/admin/bat-dong-san",
  },
  {
    id: "history",
    label: "Quản lý loại BDS",
    icon: "history_edu",
    route: "/admin/loai-bat-dong-san",
  },
  {
    id: "packages",
    label: "Quản lý gói tin",
    icon: "inventory_2",
    route: "/admin/goi-tin",
  },
  {
    id: "lich-su-mua-goi",
    label: "Lịch sử mua gói",
    icon: "history",
    route: "/admin/lich-su-mua-goi",
  },
  {
    id: "transactions",
    label: "Giao dịch",
    icon: "payments",
    route: "/admin/giao-dich",
  },
];

const secondaryItems = [
  {
    id: "permissions",
    label: "Phân quyền",
    icon: "admin_panel_settings",
    route: "/admin/phan-quyen",
  },
  {
    id: "settings",
    label: "Hồ sơ cá nhân",
    icon: "people",
    route: "/admin/ho-so-ca-nhan",
  },
];
</script>

<style scoped>
.admin-sidebar {
  position: fixed;
  top: 0;
  left: 0;
  bottom: 0;
  z-index: 30;
  width: 260px;
  display: flex;
  flex-direction: column;
  padding: 24px 16px 22px;
  background: linear-gradient(180deg, #efefee 0%, #ececea 100%);
  border-right: 1px solid rgba(27, 35, 126, 0.04);
  overflow-y: auto;
  transition: all 0.3s ease; /* Thêm hiệu ứng mượt khi resize */
}

.admin-sidebar::-webkit-scrollbar {
  width: 4px;
}

.admin-sidebar::-webkit-scrollbar-thumb {
  background: rgba(26, 35, 126, 0.1);
  border-radius: 4px;
}

.admin-sidebar__brand {
  padding: 6px 10px 24px;
}

.admin-sidebar__brand-name {
  display: block;
  color: #1a237e;
  font-size: 1.2rem;
  font-weight: 800;
  line-height: 1.15;
  text-transform: uppercase;
  letter-spacing: -0.03em;
  margin: 0;
}

.admin-sidebar__brand-subtitle {
  margin: 8px 0 0;
  color: #8c91a2;
  font-family: "Inter", sans-serif;
  font-size: 0.62rem;
  font-weight: 700;
  letter-spacing: 0.18em;
  text-transform: uppercase;
}

.admin-sidebar__nav {
  display: flex;
  flex: 1;
  flex-direction: column;
  gap: 16px;
}

.admin-sidebar__group {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.admin-sidebar__divider {
  height: 1px;
  margin: 0 10px;
  background: rgba(120, 126, 148, 0.18);
}

.admin-sidebar__link {
  display: flex;
  align-items: center;
  gap: 12px;
  width: 100%;
  min-height: 46px;
  padding: 12px 14px;
  border: 0;
  border-radius: 14px;
  appearance: none;
  color: #61687b;
  background: transparent;
  font-family: "Inter", sans-serif;
  font-size: 0.9rem;
  font-weight: 600;
  text-align: left;
  cursor: pointer;
  transition: all 0.22s ease;
  position: relative;
}

.admin-sidebar__link .material-symbols-outlined {
  font-size: 1.15rem;
}

.admin-sidebar__link:hover {
  color: #1a237e;
  background: rgba(255, 255, 255, 0.62);
  transform: translateX(2px);
}

.admin-sidebar__link.is-active {
  color: #1a237e;
  background: rgba(255, 255, 255, 0.86);
  box-shadow: inset -3px 0 0 #1a237e, 0 10px 20px rgba(22, 34, 94, 0.08);
  font-weight: 700;
}

.admin-sidebar__label {
  line-height: 1.2;
}

/* Tương tự logic Responsive của Môi giới */
@media (max-width: 1024px) {
  .admin-sidebar {
    position: relative;
    width: 100%;
    padding-bottom: 16px;
    height: auto;
    border-right: none;
    border-bottom: 1px solid rgba(27, 35, 126, 0.04);
  }

  .admin-sidebar__nav {
    gap: 12px;
  }
}
</style>