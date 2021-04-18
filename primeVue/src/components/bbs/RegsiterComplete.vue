<template>
  <Card class="p-shadow-2 p-mt-4">
    <template #content>
      회원가입을 축하합니다!
    </template>
  </Card>
</template>

<script lang="ts">
import { useRoute } from 'vue-router';
import { computed, defineComponent, onMounted } from 'vue'
import { useStore } from 'vuex';
import { RootState } from '@/store';
import { useHead } from '@vueuse/head'

export default defineComponent({
  setup () {
    const route = useRoute();
    const { dispatch } = useStore<RootState>();
    const mb_id = route.params.mb_id;
    const mb_password = route.params.mb_id;
    const login = async (member:any) => {
      await dispatch("LOGIN", member);
    }
    useHead({
      title: `회원가입 완료`,
    })
    onMounted(()=>{
      login({mb_id : mb_id,mb_password : mb_password});
    });
    return {}
  }
})
</script>

<style scoped>

</style>