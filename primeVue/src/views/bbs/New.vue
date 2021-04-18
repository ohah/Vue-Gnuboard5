<template>
  <div>
    <form v-on:submit.prevent="f_search($event)">
      <Toolbar>
        <template #left>
          <div class="p-d-flex p-w-full">
            <Dropdown v-model="qstr.gr_id" class="p-d-flex p-ai-center p-col-4"
              :options="[
                ...group_select
              ]" 
              optionLabel="name" 
              optionValue="value"
              placeholder="검색조건을 선택하세요"
            />
            <Dropdown v-model="view" class="p-d-flex p-ai-center p-col-4"
              :options="[
                {label: '전체게시물', value: ''},
                {label: '원글만', value: 'w'},
                {label: '코멘트만', value: 'c'},
              ]" 
                optionLabel="label"
                optionValue="value"
                placeholder="검색조건을 선택하세요"
            />
            <InputText v-model="route.query.mb_id" class="p-col-4" />
          </div>
        </template>
        <template #right>
          <div class="p-d-flex p-w-full">
            <Button type="submit" icon="pi pi-search" label="검색" class="p-mr-2" />
          </div>
        </template>
      </Toolbar>
    </form>
  </div>
  <section v-if="list.length > 0">
    <DataView :value="list" class="p-my-3">
      <template #header>
        <ul class="board-list p-grid-list" style="padding:1rem 0;" 
          :style="{
            gridTemplateColumns : ['6rem', '6rem', 'auto', '8rem', '4rem'].join(' '),
          }">
          <li class="p-px-1">그룹</li>
          <li class="p-px-1">게시판</li>
          <li class="p-px-1">제목</li>
          <li class="p-px-1">이름</li>
          <li class="p-px-1">일시</li>
        </ul>
      </template>
      <template #list="row">
        <ul class="board-list p-grid-list" style="padding:1rem 0;" 
          :style="{
            gridTemplateColumns : ['6rem', '6rem', 'auto', '8rem', '4rem'].join(' '),
          }">
          <li class="p-px-1">{{row.data.gr_subject}}</li>
          <li class="p-px-1"><router-link :to="{name : '/bbs/board', query : {bo_table : row.data.bo_table}}" >{{row.data.bo_subject}}</router-link></li>
          <li class="p-px-1"><router-link :to="RdURL(row.data.href)"> {{row.data.comment}}{{row.data.wr_subject}} </router-link></li>
          <li class="p-px-1"><Sideview :data="row.data.name"/> </li>
          <li class="p-px-1"> {{row.data.datetime2}} </li>
        </ul>
      </template>
      <template #empty>
        <div class="p-d-flex p-p-3 p-jc-center">글 목록이 없습니다.</div>
      </template>
    </DataView>
  </section>
  <Paginator style="background:transparent" :rows="page_rows" :first="(page * 10)" :totalRecords="total_count" @page="pageEvent($event)"></Paginator>
</template>

<script lang="ts">
import { defineComponent, toRefs, reactive, onMounted,computed, ref }  from 'vue';
import { useRoute, useRouter } from 'vue-router'
import { bbs_new, bbs_newInitial, getAPI, qstr, qstrInitial,RdURL } from '@/type'
import { useHead } from '@vueuse/head'
import Sideview from '@/components/bbs/Sideview.vue';
export default defineComponent({
  name : 'New',
  components: {
    Sideview
  },
  setup() {
    const route = useRoute();
    const router = useRouter();
    const New = reactive<bbs_new>(bbs_newInitial);
    const qstr = reactive<qstr>({
      ...qstrInitial,
      ...route.query
    });
    const view = ref<string>(`${route.query.view ? route.query.view : ''}`);
    const pageEvent = (page:any) => {
      if(page.page.toString() != qstr.page) {
        router.push({path : route.path, query : {gr_id: qstr.gr_id, view : view.value, mb_id : route.query.mb_id, page: page.page + 1}});
      }
    }
    const getContent = async () => {
      const mb_id = route.query.mb_id;
      const res = await getAPI(`/board/new/${mb_id}?gr_id=${qstr.gr_id}&view=${view.value}`);
      New.group_select = res.data.group_select;
      New.list = res.data.list;
      New.page = res.data.page;
      New.page_rows = res.data.page_rows;
      New.title = res.data.title;
      New.total_count = res.data.total_count;
      New.write_pages = res.data.write_pages;
      console.log(res.data.list);
    } 
    const f_search = (e:Event) => {
      e.preventDefault();      
      router.push({path : route.path, query : {gr_id: qstr.gr_id, view : view.value, mb_id : route.query.mb_id}});
    }
    useHead({
      title: computed(()=>New.title),
    })
    onMounted(async()=>{
      getContent();
    })
    return {
      f_search,
      ...toRefs(New),
      qstr,
      view,
      route,
      pageEvent,
      RdURL,
    };
  },
})
</script>

<style scoped>

</style>