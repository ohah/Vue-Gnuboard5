<template>
  <DataView :value="list" v-if="isLoading">
    <template #header>
      <div class="p-p-3">{{member.mb_nick}}님의 스크랩 내역</div>
    </template>
    <template #list="row">
      <ul class="p-d-flex p-jc-between p-ai-center p-w-full p-p-3">
        <li>
          <p class="p-py-1 p-bold"><router-link :to="RdURL(row.data.opener_href)">{{row.data.subject}}</router-link></p>
          <p class="p-py-1 text-gray-600"><Chip class="p-mr-2" :label="row.data.bo_subject" /><i class="p-mr-2 pi pi-clock"></i>{{row.data.ms_datetime}}</p>
        </li>
        <li><Button class="p-bold" @click="Delete(row.data.ms_id)"><i class="pi pi-trash"></i></Button></li>
      </ul>
    </template>
    <template #empty>
      <div class="p-d-flex p-p-3">스크랩 내역이 없습니다.</div>
    </template>
  </DataView>
  <ScrapSkeleton v-else />
  <ConfirmDialog></ConfirmDialog>
  <Paginator style="background:transparent" @page="pageEvent($event)" :rows="page_rows" :first="(page * 10)" :totalRecords="total_count" ></Paginator>
</template>

<script lang="ts">
import { defineComponent, toRefs, reactive, onMounted, computed, ref }  from 'vue';
import List from './List.vue';
import View from './View.vue';
import { useRoute, useRouter } from 'vue-router'
import { getAPI, getPostAPI, qstr, qstrInitial, Scrap, ScrapInitial, RdURL } from '@/type'
import { useStore } from 'vuex';
import { useConfirm } from "primevue/useconfirm";
import ConfirmDialog from 'primevue/confirmdialog';
import ScrapSkeleton from '@/components/bbs/ScrapSkeleton.vue';
import { useHead } from '@vueuse/head'

export default defineComponent({
  name : 'Scrap',
  components : {
    List,
    View,
    ConfirmDialog,
    ScrapSkeleton,
  }, 
  setup() {
    const store = useStore();
    const route = useRoute();
    const isLoading = ref<boolean>(false);
    const member = computed(()=>store.state.member);
    const router = useRouter();
    const confirm = useConfirm();
    const qstr = reactive<qstr>({
      ...qstrInitial,
      ...route.query
    });
    const Scrap = reactive<Scrap>(ScrapInitial);
    const pageEvent = (page:any) => {
      if(page.toString() != qstr.page) {
        router.push({path : route.path, query : {page: page.page + 1}});
      }
    }
    const Delete = async (ms_id:number) => {
      confirm.require({
        message: '스크랩을 삭제하시겠습니까?',
        header: '경고',
        icon: 'pi pi-exclamation-triangle',
        accept: async () => {
          const res = await getPostAPI(`/member/scrap_delete/${ms_id}`);
          //callback to execute when user confirms the action
          Scrap.list = res.data.list;
          Scrap.total_count = res.data.total_count;
          Scrap.page = res.data.page;
          Scrap.page_rows = res.data.page_rows;
        },
        reject: () => {
          //callback to execute when user rejects the action
        }
      });
    }
    const getScrap = async () => {
      const res = await getAPI(`/member/scrap`);
      Scrap.list = res.data.list;
      Scrap.total_count = res.data.total_count;
      Scrap.page = res.data.page;
      Scrap.page_rows = res.data.page_rows;
      isLoading.value = true;
    }
    useHead({
      title: computed(()=>`${member.value.mb_nick ? member.value.mb_nick : ''}님의 스크랩 내역`),
    });
    onMounted(async ()=>{
      getScrap();
    })
    return {
      ...toRefs(qstr),
      route,
      member,
      pageEvent,
      Delete,
      isLoading,
      RdURL,
      ...toRefs(Scrap),
    };
  },
})
</script>

<style scoped>

</style>