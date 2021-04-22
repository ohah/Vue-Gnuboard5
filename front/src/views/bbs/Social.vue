<template>
  <div>
    <Button label="코드 발급" @click="Kakao" />
    {{$route}}
  </div>
</template>

<script lang="ts">
import { useRoute, useRouter } from 'vue-router'
import { defineComponent, onMounted } from 'vue'
import { getAPI } from '@/type';
import axios from 'axios';

export default defineComponent({
  setup () {
    const route = useRoute();
    const router = useRouter();
    console.log(route.query);
    onMounted(async()=>{
      const test  = await axios.get('http://localhost/plugin/social/?hauth.done=kakao');
      console.log(test);
      const res = await getAPI(`/social/token?code=${route.query.code}&hauth=${route.query.hauth}`);
      console.log(res);
    });
    const Kakao = async () => {
      const res = await getAPI(`/social/popup`);
      if(res.data.url) {
        location.href = res.data.url;
      }
    }
    return {
      Kakao,
    }
  }
})
</script>

<style scoped>

</style>