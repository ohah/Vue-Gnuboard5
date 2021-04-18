<template>
  <div>
    <Card>
      <template v-slot:content>
        <Steps :model="steps" />
      </template>
    </Card>
    <router-view v-slot="{Component}">
      <keep-alive>
        <component :is="Component" />
      </keep-alive>
    </router-view>
  </div>
</template>

<script lang="ts">
import { defineComponent, reactive } from 'vue'
import { useRouter } from 'vue-router'

export default defineComponent({
  setup () {
    const router = useRouter();
    const steps = reactive([
      {
        label:"회원가입약관",
        to:'/'
      },
      {
        label:"회원가입",
        to:'/bbs/regsiter/join'
      },
      {
        label:"회원가입 완료",
        to:'/bbs/regsiter/complete'
      },
    ])
    return {
      steps,
      router,
    }
  },
  methods: {

  }
})
</script>

<style scoped>

</style>