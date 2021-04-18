<template>
  <div>
    Profile
  </div>
</template>

<script lang="ts">
import { defineComponent, toRefs, reactive, onMounted }  from 'vue';
import List from './List.vue';
import View from './View.vue';
import { useRoute, useRouter } from 'vue-router'
import { getPostAPI, qstr, qstrInitial } from '@/type'

export default defineComponent({
  name : 'Profile',
  components : {
    List,
    View,
  }, 
  setup() {
    const route = useRoute();
    const router = useRouter();
    const qstr = reactive<qstr>({
      ...qstrInitial,
      ...route.query
    });
    onMounted(async ()=>{
      const res = await getPostAPI(`/board/${qstr.bo_table}/all`, {
        mb_password : route.params.password,
      });
      console.log(res);
    })
    return {
      ...toRefs(qstr),
      route,
    };
  },
})
</script>

<style scoped>

</style>