<template>
  <div>
    <component :is="layout">
      <router-view />
    </component>

    <!-- 🔥 CHAT POPUP GLOBAL -->
    <ChatPopup />
  </div>
</template>

<script>
import BlankLayout from "@/layout/wrapper/Blank/index.vue";
import AdminLayout from "@/layout/wrapper/Admin/index.vue";
import KhachHangLayout from "@/layout/wrapper/KhachHang/index.vue";
import MoiGioiLayout from "@/layout/wrapper/MoiGioi/index.vue";

// 🔥 IMPORT CHAT POPUP
import ChatPopup from "@/components/KhachHang/ChatPopup/ChatPopup.vue";

export default {
  components: {
    "blank-layout": BlankLayout,
    "admin-layout": AdminLayout,
    "khach-hang-layout": KhachHangLayout,
    "moi-gioi-layout": MoiGioiLayout,

    // 🔥 REGISTER
    ChatPopup
  },

  computed: {
    layout() {
      return (this.$route.meta.layout || "khach-hang") + "-layout";
    }
  }
};
</script>