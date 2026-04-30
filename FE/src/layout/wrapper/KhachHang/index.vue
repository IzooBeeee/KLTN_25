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

    <!-- FAB -->
    <!-- <button class="fixed bottom-8 right-8 bg-primary text-white p-4 rounded-full shadow-xl hover:scale-110 transition-transform flex items-center gap-2 z-40">
      <span class="material-symbols-outlined">calendar_month</span>
      <span class="font-bold hidden md:inline">Đặt lịch</span>
    </button> -->
  </div>
</template>

<script>
import Header from "../../components/KhachHang/Header.vue";
import Footer from "../../components/KhachHang/Footer.vue";

export default {
  components: { Header, Footer },
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