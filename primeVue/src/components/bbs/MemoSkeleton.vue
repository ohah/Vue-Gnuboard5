<template>
  <DataView :value="Array.from(Array(5), () => Array(1).fill(true))">
    <template #list>
      <ul class="p-d-flex p-jc-between p-ai-center p-w-full p-p-3">
        <li>
          <p class="p-py-1 p-bold p-d-flex"><Skeleton width="4rem" class="p-mr-2" /><Skeleton width="6rem" /></p>
          <p class="p-py-1 text-gray-600 p-d-flex">
            <Skeleton width="8rem" class="p-mr-3" /> <Skeleton width="6rem" />
          </p>
        </li>
        <li><Skeleton width="3rem" height="3rem" /></li>
      </ul>
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