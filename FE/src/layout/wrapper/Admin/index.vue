<template>
  <div class="layout">
    <MenuBDS />

    <div class="main">
      <TopBDS />

      <div class="content">
        <router-view />
      </div>
    </div>
  </div>
</template>

<script>
import MenuBDS from "../../components/Admin/MenuBDS.vue";
import TopBDS from "../../components/Admin/TopBDS.vue";

export default {
  components: { MenuBDS, TopBDS },
};
</script>

<style scoped>
.layout {
  display: flex;
  min-height: 100vh;
  background:
    radial-gradient(circle at top left, rgba(255, 255, 255, 0.88), transparent 22%),
    linear-gradient(180deg, #f7f6f3 0%, #f3f2ef 100%);
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
