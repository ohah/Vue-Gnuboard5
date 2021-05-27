<template>
  <div class="home">
    <div class="p-grid">
      <div class="p-col-12 p-md-6">
        <Latest bo_table="notice" :row="3" :subject_len="40"  />
      </div>
      <div class="p-col-12 p-md-6">
        <Latest bo_table="free" :row="3" :subject_len="40"  />
      </div>
      <div class="p-col-12 p-md-6">
        <Visit />
      </div>
      <div class="p-col-12 p-md-6">
        <Poll />
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import Latest from '@/components/Latest.vue';
import Visit from '@/components/Visit.vue';
import Poll from '@/components/Poll.vue';
import { useHead } from '@vueuse/head'
export default defineComponent({
  name: 'home',
  components : {
    Latest,
    Visit,
    Poll
  },
  setup() {
    useHead({
      title: "그누보드5",
    })
  }
});
</script>
