<template>
  <div>
    <List 
      v-if="route.query.bo_table && !route.query.wr_id"
    />
    <View 
      v-else-if="route.query.bo_table && route.query.wr_id"
    />
  </div>
</template>

<script lang="ts">
import { defineComponent, toRefs, reactive }  from 'vue';
import List from './List.vue';
import View from './View.vue';
import { useRoute } from 'vue-router'
import { qstr, qstrInitial } from '@/type'

export default defineComponent({
  name : 'Board',
  components : {
    List,
    View,
  }, 
  setup() {
    const route = useRoute();
    const qstr = reactive<qstr>({
      ...qstrInitial,
      ...route.query
    });
    return {
      ...toRefs(qstr),
      route,
    };
  },
})
</script>

<style scoped>

</style>