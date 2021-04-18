<template>
  <div>
    {{content}}
  </div>
</template>

<script lang="ts">
import { defineComponent, toRefs, reactive, onMounted, ref }  from 'vue';
import { useRoute } from 'vue-router'
import { getAPI, qstr, qstrInitial } from '@/type'

export default defineComponent({
  name : 'Content',
  setup() {
    const route = useRoute();
    const content = ref<any>({});
    const getContent = async () => {
      const co_id = route.query.co_id;
      const res = await getAPI(`/content?co_id=${co_id}`);
      content.value = res.data;
      console.log(content);
    } 
    onMounted(async()=>{
      getContent();
    })   
    const qstr = reactive<qstr>({
      ...qstrInitial,
      ...route.query
    });
    return {
      content,
      ...toRefs(qstr),
      route,
    };
  },
})
</script>

<style scoped>

</style>