<template>
  <div class="p-d-flex p-w-full p-jc-between p-mt-2">
    <div v-for="(row, i) in list" :key="i">
      <Button class="p-p-0 p-m-0 p-button-link" @click="Popup(row)">
        <img :src="require(`../assets/social_img/sns_${row}_s.png`)" />
      </Button>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, onMounted } from 'vue'
import { useRouter } from 'vue-router';
export default defineComponent({
  props:{
    list : {
      type:Array,
    },
    ModalHide : {
      type:Function
    }
  },
  setup (props) {
    const router = useRouter();
    const { list, ModalHide } = props;
    const Popup = (platform:string) => {
      router.push({name : 'social', params : {provider : platform}});
      if(ModalHide) ModalHide();
      // return false;
    }
    onMounted(()=>{
      console.log('소셜 로그인');
    })
    return {
      list,
      Popup
    }
  }
})
</script>

<style scoped>

</style>