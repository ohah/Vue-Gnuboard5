<template>
  <DataView :value="list" v-if="isLoading">
    <template #header>
      <div class="p-p-3">{{member.mb_nick}}님의 포인트 내역</div>
    </template>
    <template #list="row">
      <ul class="p-d-flex p-jc-between p-ai-center p-w-full p-p-3">
        <li>
          <p class="p-py-1">{{row.data.po_content}}</p>
          <p class="p-py-1 text-gray-600"><i class="p-mr-2 pi pi-clock"></i>{{row.data.po_datetime}}</p>
        </li>
        <li><span class="p-bold">{{row.data.point}}</span></li>
      </ul>
    </template>
    <template #footer v-if="list">
      <div class="p-d-flex p-jc-between p-ai-center p-w-full p-p-3">
        <div> 소계 </div>
        <div> {{member.mb_point}} </div>
      </div>
    </template>
    <template #empty>
      <div class="p-d-flex p-p-3">포인트 내역이 없습니다.</div>
    </template>
  </DataView>
  <PointSkeleton v-else />
  <Paginator style="background:transparent" @page="pageEvent($event)" :rows="page_rows" :first="(page * 10)" :totalRecords="total_count" ></Paginator>
</template>

<script lang="ts">
import { defineComponent, toRefs, reactive, onMounted, computed, ref }  from 'vue';
import { useRoute, useRouter } from 'vue-router'
import { getAPI, pointInitial, qstr, qstrInitial, point } from '@/type'
import { useStore } from 'vuex';
import PointSkeleton from '@/components/bbs/PointSkeleton.vue'
import { useHead } from '@vueuse/head'
export default defineComponent({
  name : 'Point',
  components: {
    PointSkeleton,
  },
  setup() {
    const store = useStore();
    const isLoading = ref<boolean>(false);
    const member = computed(()=>store.state.member);
    const route = useRoute();
    const router = useRouter();
    const qstr = reactive<qstr>({
      ...qstrInitial,
      ...route.query
    });
    const pageEvent = (page:any) => {
      if(page.toString() != qstr.page) {
        router.push({path : route.path, query : {page: page.page + 1}});
      }
    }
    const point = reactive<point>(pointInitial);
    onMounted(async () => {
      const res = await getAPI(`/point?page=${qstr.page}`);
      point.list = res.data.list;
      point.total_count = res.data.total_count;
      point.page = res.data.page;
      point.page_rows = res.data.page_rows;
      isLoading.value = true;
    });
    useHead({
      title: computed(()=>`${member.value.mb_nick ? member.value.mb_nick : ''}님의 포인트 내역`),
    });
    return {
      isLoading,
      member,
      route,
      ...toRefs(point),
      pageEvent,
    };
  },
})
</script>

<style scoped>

</style>