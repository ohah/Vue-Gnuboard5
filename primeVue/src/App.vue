<template>
  <div class="layout-wrapper">
    <Top :class="{'layout-topbar-menu-hidden': !isSidebar}" />
    <Menu class="v-cms-sidebar" :class="{'layout-sidebar-hidden': !isSidebar}" />
    <div class="v-cms-main" :class="{'layout-topbar-menu-hidden': !isSidebar}" >
      <router-view :key="route.fullPath" />
    </div>
  </div>
</template>
<script lang="ts">
import { computed, defineComponent } from 'vue'
import { useRoute } from 'vue-router'
import { RootState } from './store';
import { useStore } from "vuex";
import Menu from './views/Menu.vue'
import Top from './views/Top.vue'
export default defineComponent({
  components : {
    Menu,
    Top
  },
  setup() {
    const route = useRoute();
    const { state, commit } = useStore<RootState>();
    const isSidebar = computed(()=>state.layout.isSidebar);
    const resize = ()=> commit("layout/resize");
    window.addEventListener("resize", function(e) {
      resize();
    }); 
    return {
      isSidebar,
      route
    }
  },
})
</script>

<style>
</style>