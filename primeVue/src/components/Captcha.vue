<template>
  <div v-if="captcha.g5_captcha_url" class="p-d-sm-flex p-d-block p-ai-center">
    <div class="p-d-flex">
      <img v-if="captcha.g5_captcha_url" :src="captcha.g5_captcha_url" />
      <div style="flex-direction: column;display: flex;" class="p-mx-2">
        <Button class="p-my-1" icon="pi pi-play" />
        <Button class="p-my-1" icon="pi pi-undo" @click="refresh_captcha" />
      </div>
    </div>
    <InputText @input="onInput" v-model="captcha_text" style="height:40px;" class="p-mx-2 " name="captcha_key"/>
  </div>
</template>

<script lang="ts">
import { getPostAPI } from '@/type';
import { defineComponent, PropType, toRefs } from 'vue'
export interface Captcha {
  g5_captcha_url:string,
}
export default defineComponent({
  props:{
    modelValue:String,
    captcha:{
      type:Object as PropType<Captcha>,
    }
  },
  emits :['update:modelValue'],
  setup (props) {
    const refresh_captcha = async () => {
      const res = await getPostAPI(`/captcha/K`, {refresh : 1});
      if(res.data.g5_captcha_url) {
        if(props.captcha)
          props.captcha.g5_captcha_url = res.data.g5_captcha_url;
      }
    };
    return {
      refresh_captcha,
      props,
      ...toRefs(props),
    }
  },
  methods: {
    onInput(event:any) {
      this.$emit('update:modelValue', event.target.value);
    }
  },
})
</script>