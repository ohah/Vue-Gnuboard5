<template>
  <div>
    <Panel :toggleable="true" :collapsed="false" class="p-shadow-1">
      <template #header>
        <div class="p-d-flex">
          <Skeleton width="2rem" class="p-mr-2"></Skeleton>
          <Skeleton width="1rem" class="p-mr-2"></Skeleton>
        </div>
      </template>
      <template #icons>
        <button class="p-panel-header-icon p-link p-mr-2" @click="toggle">
          <span class="pi pi-cog"></span>
        </button>
      </template>
      <section ref="bo_v_info" class="p-d-flex p-jc-end text-gray-500" v-for="(item, i) in new Array(5).fill(true)" :key="i">
        <ul class="p-p-2" style="display:block;width:100%;">
          <div class="p-d-flex p-w-full p-jc-between"> 
            <h2 class="p-d-flex"> 
              <Skeleton width="4rem" class="p-mr-2"></Skeleton>
            </h2>
            <ul>
              <Skeleton width="6rem" class="p-mr-2"></Skeleton>
            </ul>
          </div>
          <div style="display:block;width:100%">
            <Skeleton width="95%" class="p-mr-2 p-my-1"></Skeleton>
            <Skeleton width="50%" class="p-mr-2 p-my-1"></Skeleton>
            <Divider />
          </div>
        </ul>
      </section>
    </Panel>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue'

export default defineComponent({
  setup () {    
    return {}
  }
})
</script>

<style scoped>

</style>