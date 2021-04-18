<template>
  <div>
    <Panel :toggleable="true" :collapsed="false" class="p-shadow-1">
      <template #header>
        <div class="p-d-flex">
          <Skeleton width="2rem" class="p-mr-2"></Skeleton>
          <Skeleton width="15rem" class="p-mr-2"></Skeleton>
        </div>
      </template>
      <template #icons>
        <button class="p-panel-header-icon p-link p-mr-2" @click="toggle">
          <span class="pi pi-cog"></span>
        </button>
      </template>
      <section ref="bo_v_info" class="p-d-flex p-jc-end text-gray-500">
        <h2 class="sound_only">페이지 정보</h2>
        <div class="p-d-flex p-px-1">
          <div class="p-mr-2 p-d-flex">
            <Skeleton width="4rem" class="p-mr-2"></Skeleton>
          </div>
          <div class="p-d-flex p-px-1">
            <span class="p-px-1"><Skeleton width="2rem" class="p-mr-2"></Skeleton></span>
            <span class="p-px-1"><Skeleton width="2rem" class="p-mr-2"></Skeleton></span>
            <span class="p-px-1"><span class="sound_only">작성일</span><Skeleton width="2rem" class="p-mr-2"></Skeleton> </span>
          </div>
        </div>
      </section>
      <Divider />
      <ul class="p-d-flex p-jc-end">
        <Skeleton width="4rem" height="2rem" class="p-mr-2"></Skeleton>
        <Skeleton width="4rem" height="2rem" class="p-mr-2"></Skeleton>
        <Skeleton width="4rem" height="2rem" class="p-mr-2"></Skeleton>
      </ul>
      <Divider />
      <section id="bo_v_atc" class="p-pb-3">
        <Skeleton :width="`${Math.random() * (100 - 0) + 0}%`" class="p-my-3" v-for="(row, i) in new Array(10).fill(true)" :key="i">
        </Skeleton>
      </section>
    </Panel>
    <section class="p-py-3 text-gray-600">
      <div class="p-shadow-1 bg-white p-p-3" style="border-radius:3px;">
        <div class="p-d-flex">
          <Skeleton width="2rem" class="p-mr-4" ></Skeleton>
          <Skeleton width="30rem" ></Skeleton>
          <Skeleton width="6rem" class="p-ml-auto" ></Skeleton>
        </div>
        <Divider />
        <div class="p-d-flex">
          <Skeleton width="2rem" class="p-mr-4" ></Skeleton>
          <Skeleton width="30rem" ></Skeleton>
          <Skeleton width="6rem" class="p-ml-auto" ></Skeleton>
        </div>
      </div>
    </section>
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