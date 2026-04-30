<template>
  <div class="broker-layout">
    <!-- Menu -->
    <MoiGioiMenu />

    <!-- Content -->
    <div class="main">
      <MoiGioiHeader />

      <div class="content">
        <router-view />
      </div>
      <!-- Footer -->
      <MoiGioiFooter />
    </div>

    <!-- Chat Support -->
    <ChatSupport />
  </div>
</template>

<script>
import MoiGioiMenu from "../../components/MoiGioi/Menu.vue";
import MoiGioiHeader from "../../components/MoiGioi/Header.vue";
import MoiGioiFooter from "../../components/MoiGioi/Footer.vue";
import ChatSupport from "../../../components/Chat/index.vue";

export default {
  name: "MoiGioiLayout",
  components: {
    MoiGioiMenu,
    MoiGioiHeader,
    MoiGioiFooter,
    ChatSupport,
  },

  mounted() {
    this.checkBrokerAuth();
  },

  methods: {
    checkBrokerAuth() {
      // ✅ Chỉ kiểm tra token đúng key — không dùng "user_type" cũ nữa
      const token = localStorage.getItem("moi_gioi_auth_token");
      if (!token) {
        this.$router.push("/moi-gioi/dang-nhap");
      }
    },
  },
};
</script>

<style scoped>
.broker-layout {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background-color: #f8fafc;
}

.main {
  margin-left: 260px;
  width: calc(100% - 260px);
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.content {
  flex: 1;
  padding: 92px 16px 24px;
}

@media (max-width: 1024px) {
  .main {
    width: 100%;
    margin-left: 0;
  }

  .content {
    padding-top: 16px;
  }
}
</style>