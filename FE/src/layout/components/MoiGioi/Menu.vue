<template>
  <aside class="broker-sidebar">
    <div class="broker-sidebar__brand">
      <p class="broker-sidebar__brand-name">BrokerHub</p>
      <p class="broker-sidebar__brand-subtitle">Professional Real Estate</p>
    </div>

    <nav class="broker-sidebar__nav" aria-label="Menu môi giới">
      <div class="broker-sidebar__group">
        <RouterLink
          v-for="item in primaryItems"
          :key="item.id"
          :to="item.route"
          class="broker-sidebar__link"
          :class="{ 'is-active': route.path.startsWith(item.route) }"
        >
          <span class="material-symbols-outlined">{{ item.icon }}</span>
          <span class="broker-sidebar__label">{{ item.label }}</span>
          <span v-if="item.badge" class="broker-sidebar__badge">{{
            item.badge
          }}</span>
        </RouterLink>
      </div>

      <div class="broker-sidebar__divider"></div>

      <div class="broker-sidebar__group">
        <div class="broker-sidebar__group-title">Quản lý BĐS</div>
        <RouterLink
          v-for="item in propertyItems"
          :key="item.id"
          :to="item.route"
          class="broker-sidebar__link"
          :class="{ 'is-active': route.path.startsWith(item.route) }"
        >
          <span class="material-symbols-outlined">{{ item.icon }}</span>
          <span class="broker-sidebar__label">{{ item.label }}</span>
          <span v-if="item.badge" class="broker-sidebar__badge">{{
            item.badge
          }}</span>
        </RouterLink>
      </div>

      <div class="broker-sidebar__divider"></div>

      <div class="broker-sidebar__group broker-sidebar__group--bottom">
        <RouterLink
          v-for="item in secondaryItems"
          :key="item.id"
          :to="item.route"
          class="broker-sidebar__link"
          :class="{ 
    'is-active': route.path.startsWith(item.route),
    'broker-sidebar__link--upgrade': item.id === 'packages'
  }"
        >
          <span class="material-symbols-outlined">{{ item.icon }}</span>
          <span class="broker-sidebar__label">{{ item.label }}</span>
          <span v-if="item.badge" class="broker-sidebar__badge">{{
            item.badge
          }}</span>
        </RouterLink>
      </div>

      <!-- <div class="broker-sidebar__upgrade-card">
        <div class="upgrade-card__icon">
          <span class="material-symbols-outlined">stars</span>
        </div>
        <div class="upgrade-card__content">
          <h4>Nâng cấp tài khoản</h4>
          <p>Để sử dụng nhiều tính năng hơn</p>
        </div>
        <RouterLink to="/moi-gioi/nang-cap" class="upgrade-card__btn"
          >Nâng cấp</RouterLink
        >
      </div> -->
    </nav>
  </aside>
</template>

<script setup>
import { RouterLink, useRoute } from "vue-router";
import { ref, onMounted } from "vue";
import api from "@/axios/config";

const route = useRoute();

const primaryItems = ref([
  {
    id: "dashboard",
    label: "Trang chủ",
    icon: "dashboard",
    route: "/moi-gioi/trang-chu",
  },
  {
    id: "listings",
    label: "Đăng tin",
    icon: "home_work",
    route: "/moi-gioi/dang-tin",
    badge: null,
  },
  {
    id: "customers",
    label: "Khách hàng",
    icon: "people",
    route: "/moi-gioi/quan-ly-khach-hang",
  },
]);

const propertyItems = ref([
  {
    id: "all-properties",
    label: "Tất cả BĐS",
    icon: "domain",
    route: "/moi-gioi/quan-ly-bat-dong-san",
  },
]);

const secondaryItems = ref([
  {
    id: "notifications",
    label: "Thông báo",
    icon: "notifications",
    route: "/moi-gioi/thong-bao",
    badge: null,
  },
  {
    id: 'packages',
    label: 'Gói tin',
    icon: 'auto_awesome',
    route: '/moi-gioi/goi-tin',
  },
  {
    id: 'lich-su-mua-tin',
    label: 'Lịch sử mua tin',
    icon: 'receipt_long',
    route: '/moi-gioi/lich-su-mua-tin',
  },
  {
    id: "profile",
    label: "Hồ sơ cá nhân",
    icon: "account_circle",
    route: "/moi-gioi/ho-so-ca-nhan",
    badge: null,
  },
]);

const loadUnreadNotifications = async () => {
  try {
    const res = await api.get("/moi-gioi/thong-bao");
    if (res.data?.data) {
      const unread = res.data.data.filter((n) => !n.is_read).length;
      const notifItem = secondaryItems.value.find((i) => i.id === "notifications");
      if (notifItem) notifItem.badge = unread > 0 ? unread : null;
    }
  } catch {
    // silent
  }
};

onMounted(() => {
  loadUnreadNotifications();
});
</script>

<style scoped>
.broker-sidebar {
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
}

.broker-sidebar__brand {
  padding: 6px 10px 24px;
}

.broker-sidebar__brand-name {
  display: block;
  color: #059669;
  font-size: 1.2rem;
  font-weight: 800;
  line-height: 1.15;
  text-transform: uppercase;
  letter-spacing: -0.03em;
  margin: 0;
}

.broker-sidebar__brand-subtitle {
  margin: 8px 0 0;
  color: #6b7280;
  font-family: "Inter", sans-serif;
  font-size: 0.62rem;
  font-weight: 700;
  letter-spacing: 0.18em;
  text-transform: uppercase;
}

.broker-sidebar__nav {
  display: flex;
  flex: 1;
  flex-direction: column;
  gap: 16px;
}

.broker-sidebar__group {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.broker-sidebar__group-title {
  padding: 8px 14px;
  font-size: 0.75rem;
  font-weight: 700;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.broker-sidebar__divider {
  height: 1px;
  margin: 0 10px;
  background: rgba(5, 150, 105, 0.15);
}

.broker-sidebar__link {
  display: flex;
  align-items: center;
  gap: 12px;
  width: 100%;
  min-height: 46px;
  padding: 12px 14px;
  border: 0;
  border-radius: 14px;
  appearance: none;
  color: #4b5563;
  background: transparent;
  font-family: "Inter", sans-serif;
  font-size: 0.9rem;
  font-weight: 600;
  text-align: left;
  cursor: pointer;
  transition: all 0.22s ease;
  position: relative;
}

.broker-sidebar__link .material-symbols-outlined {
  font-size: 1.15rem;
}

.broker-sidebar__link:hover {
  color: #059669;
  background: rgba(255, 255, 255, 0.8);
  transform: translateX(2px);
}

.broker-sidebar__link.is-active {
  color: #059669;
  background: rgba(255, 255, 255, 0.95);
  box-shadow: inset -3px 0 0 #059669, 0 10px 20px rgba(5, 150, 105, 0.1);
  font-weight: 700;
}

.broker-sidebar__badge {
  margin-left: auto;
  padding: 2px 8px;
  background: #ef4444;
  color: white;
  font-size: 0.75rem;
  font-weight: 700;
  border-radius: 10px;
  min-width: 20px;
  text-align: center;
}

.broker-sidebar__label {
  line-height: 1.2;
}

Upgrade Card .broker-sidebar__upgrade-card {
  margin-top: auto;
  padding: 16px;
  background: linear-gradient(135deg, #059669, #10b981);
  border-radius: 12px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
}

.upgrade-card__icon {
  width: 40px;
  height: 40px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.upgrade-card__icon .material-symbols-outlined {
  color: white;
  font-size: 24px;
}

.upgrade-card__content h4 {
  color: white;
  font-size: 0.95rem;
  font-weight: 700;
  margin: 0 0 4px 0;
}

.upgrade-card__content p {
  color: rgba(255, 255, 255, 0.9);
  font-size: 0.8rem;
  margin: 0;
  line-height: 1.3;
}

.upgrade-card__btn {
  padding: 8px 16px;
  background: white;
  color: #059669;
  border: none;
  border-radius: 8px;
  font-weight: 700;
  font-size: 0.85rem;
  cursor: pointer;
  transition: all 0.2s;
  text-align: center;
  text-decoration: none;
  display: block;
}

.upgrade-card__btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

@media (max-width: 1024px) {
  .broker-sidebar {
    position: relative;
    width: 100%;
    padding-bottom: 16px;
  }

  .broker-sidebar__nav {
    gap: 12px;
  }
}

.broker-sidebar__group--bottom {
  margin-top: auto;
  padding-top: 16px;
}

.broker-sidebar__link--upgrade {
  background: linear-gradient(135deg, #059669, #10b981) !important;
  color: white !important;
  box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
}

.broker-sidebar__link--upgrade:hover {
  background: linear-gradient(135deg, #047857, #059669) !important;
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(5, 150, 105, 0.4);
}

.broker-sidebar__link--upgrade .material-symbols-outlined {
  color: white !important;
}
</style>