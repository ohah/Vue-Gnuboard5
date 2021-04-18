<template>
  <DataView :value="Array.from(Array(10), () => Array(1).fill(true))">
    <template #header>
      <div class="p-p-3">
        <Skeleton width="16rem" />
      </div>
    </template>
    <template #list>
      <ul class="p-d-flex p-jc-between p-ai-center p-w-full p-p-3">
        <li>
          <p class="p-py-1 p-bold"><Skeleton width="12rem" /></p>
          <p class="p-py-1 text-gray-600 p-d-flex">
            <Skeleton width="6rem" />
          </p>
        </li>
        <li><Skeleton width="3rem" height="1.5rem" /></li>
      </ul>
    </template>
    <template #footer>
      <div class="p-d-flex p-jc-between p-ai-center p-w-full p-p-3">
        <Skeleton width="4rem" class="p-mr-3" /> <Skeleton width="6rem" />
      </div>
    </template>
  </DataView>
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