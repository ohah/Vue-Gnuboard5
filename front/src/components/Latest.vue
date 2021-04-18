<template>
  <Card class="p-shadow-2">
    <template #title>
      <div class="p-d-flex p-ai-center">
        <span v-if="data"><router-link :to="data.url"> {{data.bo_subject}} </router-link> </span>
        <Skeleton v-else width="8rem" class=""> 날짜 </Skeleton>
        <Button class="p-button-link p-ml-auto p-button-sm" icon="pi pi-plus" />
      </div>
    </template>
    <template #content>
      <div v-if="data">
        <div class="p-d-flex p-p-3 p-ai-center" v-for="row in data.list" :key="row.wr_id">
          <router-link :to="RdURL(row.href)">
            <span class="p-mr-2"> {{row.wr_subject}} </span>
            <Avatar v-if="row.icon_new" label="N" class="p-mr-2" style="background-color:#4caf4f; color: #ffffff" />
            <i v-if="row.icon_secret" class='p-mr-2 pi pi-lock'> </i>
            <i v-if="row.icon_file" class='p-mr-2 pi pi-file'> </i>
            <i v-if="row.icon_hot" class='p-mr-2 pi pi-file'> </i>
            <i v-if="row.icon_link" class='p-mr-2 pi pi-file'> </i>
            <i v-if="row.icon_file" class='p-mr-2 pi pi-file'> </i>
            <Avatar class="p-ml-2" v-if="row.wr_comment" :label="`${row.wr_comment}`" /> 
          </router-link>
          <div class="p-ml-auto p-d-flex">
            <SideView :data="row.name" />
            <div class="p-ml-2">{{row.datetime2}}</div>
          </div>
        </div>
        <div v-if="data.list.length === 0">
          게시물이 없습니다.
        </div>
      </div>
      <div v-else>
        <div class="p-d-flex p-p-3 p-ai-center" v-for="(row, i) in SkeletonArray" :key="i">
          <Skeleton width="25rem" class="p-mr-2"></Skeleton>
          <div class="p-ml-auto p-d-flex">
            <Skeleton width="5rem"></Skeleton>
            <Skeleton width="2rem" class="p-ml-2"></Skeleton>
          </div>
        </div>
      </div>
    </template>
  </Card>
</template>

<script lang="ts">
import { defineComponent, onMounted, ref } from 'vue'
import { getAPI, latest, RdURL } from '../type'
import SideView from '@/components/bbs/Sideview.vue'
export default defineComponent({
  name:'Latest',
  components : {
    SideView
  },
  props:{
    bo_table:String,
    row:Number,
    subject_len:Number,
  },
  setup (props) {
    const data = ref<latest>();
    const SkeletonArray = new Array(props.row).fill(true);
    onMounted(async () => {
      const res = await getAPI(`/latest/${props.bo_table}/${props.row}/${props.subject_len}`);
      data.value = res.data;
    });
    return {
      data,
      SkeletonArray,
      RdURL
    };
  }
})
</script>

<style scoped>

</style>