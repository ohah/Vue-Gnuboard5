<template>
  <section>
    <ul class="p-d-flex p-my-3">
      <li class="p-mx-1">
        <Skeleton width="6rem" height="2rem" />
      </li>
      <li v-for="(item, i) in new Array(3)" :key="i" class="p-mx-1">
        <Skeleton width="6rem" height="2rem" />
      </li>
    </ul>
    <DataView :value="Array.from(Array(10), () => Array(1).fill(true))">
      <template #header>
        <div class="p-p-3">
          <Skeleton width="16rem" />
        </div>
      </template>
      <template #list>
        <ul class="p-d-flex p-jc-between p-ai-center p-w-full p-p-3">
          <li class="p-py-1 p-bold p-d-flex">
            <Skeleton :width="`${Math.random() * (35 - 5) + 0}rem`" class="p-mr-3"/>
            <Skeleton width="1.5rem" />
          </li>
          <li class="p-d-flex">
            <Skeleton width="3rem" height="1.5rem" class="p-mr-2"/>
            <Skeleton width="3rem" height="1.5rem" />
          </li>
        </ul>
      </template>
    </DataView>
  </section>
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