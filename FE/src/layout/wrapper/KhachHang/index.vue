<template>
  <div class="layout">
    <!-- Header -->
    <Header />

    <!-- Content -->
    <main class="main-content">
      <slot />
    </main>

    <!-- Footer -->
    <Footer />

    <!-- Chat Bot -->
    <ChatBot />
  </div>
</template>

<script>
import Header from "../../components/KhachHang/Header.vue";
import Footer from "../../components/KhachHang/Footer.vue";
import ChatBot from "../../../components/KhachHang/ChatBot/index.vue";

export default {
  components: { Header, Footer, ChatBot },
};
</script>

<style scoped>
.layout {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background-color: #f9f9f9;
}

.main-content {
  flex: 1;
  width: 100%;
  margin-top: 74px;
  padding: 0;
}
</style>