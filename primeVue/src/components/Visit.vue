<template>
  <Card class="p-shadow-2">
    <template #title>
      <slot name="header">
        접속자 집계
      </slot>
    </template>
    <template #content>
      <div v-if="data">
        <ul class="p-grid">
          <li class="p-col-6"><span> 오늘 : </span><span>{{data.today}}</span></li>
          <li class="p-col-6"><span> 어제 : </span><span>{{data.yesterday}}</span></li>
          <li class="p-col-6"><span> 최대 : </span><span>{{data.max}}</span></li>
          <li class="p-col-6"><span> 전체 : </span><span>{{data.all}}</span></li>
        </ul>
      </div>
      <div v-else>
        <ul class="p-grid">
          <li class="p-col-6 p-d-flex"><Skeleton width="2rem" class="p-mr-2"></Skeleton><Skeleton width="5rem" class=""></Skeleton></li>
          <li class="p-col-6 p-d-flex"><Skeleton width="2rem" class="p-mr-2"></Skeleton><Skeleton width="5rem" class=""></Skeleton></li>
          <li class="p-col-6 p-d-flex"><Skeleton width="2rem" class="p-mr-2"></Skeleton><Skeleton width="5rem" class=""></Skeleton></li>
          <li class="p-col-6 p-d-flex"><Skeleton width="2rem" class="p-mr-2"></Skeleton><Skeleton width="5rem" class=""></Skeleton></li>
        </ul>
      </div>
    </template>
  </Card>
</template>

<script lang="ts">
import { defineComponent, onMounted, ref } from 'vue'
import { getAPI, latest, visit } from '../type'
export default defineComponent({
  name:'Visit',
  props:{
    bo_table:String,
    row:Number,
    subject_len:Number,
  },
  setup (props) {
    const data = ref<visit>();
    const SkeletonArray = new Array(props.row).fill(true);
    onMounted(async () => {
      const res = await getAPI(`/visit`);
      data.value = res.data;
    });
    return {
      data,
      SkeletonArray
    };
  }
})
</script>

<style scoped>

</style>