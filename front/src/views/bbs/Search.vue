<template>
  <div>
    <form v-on:submit.prevent="f_search($event)">
      <Toolbar>
        <template #left>
          <div class="p-d-flex p-w-full">
            <Dropdown v-model="qstr.gr_id" class="p-d-flex p-ai-center p-col-4"
              :options="[
                {name: '전체 분류', value: ''},
                ...group_select
              ]" 
              optionLabel="name" 
              optionValue="value"
              placeholder="검색조건을 선택하세요"
            />
            <Dropdown v-model="qstr.sfl" class="p-d-flex p-ai-center p-col-4"
              :options="[
                {label: '제목', value: 'wr_subject'},
                {label: '내용', value: 'wr_content'},
                {label: '제목+내용', value: 'wr_subject||wr_content'},
                {label: '글쓴이', value: 'wr_name,1'},
                {label: '글쓴이(코)', value: 'wr_name,0'},
              ]" 
                optionLabel="label"
                optionValue="value"
                placeholder="검색조건을 선택하세요"
            />
            <InputText v-model="qstr.stx" class="p-col-4" />
          </div>
        </template>
        <template #right>
          <div class="p-d-flex p-w-full">
            <Button type="submit" icon="pi pi-search" label="검색" class="p-mr-2" />
            <ToggleButton v-model="sop" onLabel="AND" offLabel="OR"/>
          </div>
        </template>
      </Toolbar>
    </form>
  </div>
  <section v-if="isLoading">
    <ul class="p-d-flex p-my-3" v-if="str_board_list">
      <li class="p-mx-1">
        <router-link :to="{path : $route.path, query : {sop : qstr.sop, page: 1, stx : qstr.stx, sfl: qstr.sfl}}"> <Button :class="{'p-button-secondary' : !$route.query.onetable}" label="전체게시판" class="p-button-sm p-button-raised" /></router-link>
      </li>
      <li v-for="(item, i) in str_board_list" :key="i" class="p-mx-1">
        <router-link :to="{path : $route.path, query : {onetable : item.table,  sop : qstr.sop, page: 1, stx : qstr.stx, sfl: qstr.sfl}}"> <Button :class="{'p-button-secondary' : item.table === $route.query.onetable}" :label="item.bo_subject" :badge="item.cnt_cmt" class="p-button-sm p-button-raised" /></router-link>
      </li>
    </ul>
    <div v-if="list.length > 0">
      <div v-for="(item, i) in list" :key="i">
        <DataView :value="item" class="p-my-3">
          <template #header>
            <div class="p-p-3">
              {{str_board_list[i].bo_subject}} 내 검색 결과
            </div>
          </template>
          <template #list="row">
            <ul class="board-list p-grid-list" style="padding:1rem 0;" 
              :style="{
                gridTemplateColumns : ['auto', '8rem', '4rem', '4rem'].join(' '),
              }">
              <li v-if="row.data.wr_is_comment === 0" class="p-px-2"> <router-link :to="RdURL(row.data.href)"> <span v-html="row.data.subject"></span> <Avatar class="p-ml-2" v-if="row.data.wr_comment" :label="`${row.data.wr_comment}`" /> </router-link></li>
              <li v-if="row.data.wr_is_comment === 1" class="p-px-2"> <router-link :to="RdURL(row.data.href)"><i class="pi pi-comment"></i> <span v-html="row.data.content"></span> </router-link></li>
              <li><Sideview :data="row.data.name"/> </li>
              <li> {{row.data.wr_hit}} </li>
              <li> {{row.data.datetime2}} </li>
            </ul>
          </template>
          <template #empty>
            <div class="p-d-flex p-p-3 p-jc-center">글 목록이 없습니다.</div>
          </template>
        </DataView>
      </div>
    </div>
    <div v-else>
      <div class="p-d-flex bg-white p-shadow-1 p-p-3 p-jc-center">글 목록이 없습니다.</div>
    </div>
  </section>
  <SearchSkeleton v-else/>
  <Paginator style="background:transparent" :rows="page_rows" :first="(page * 10)" :totalRecords="total_count" @page="pageEvent($event)"></Paginator>
</template>

<script lang="ts">
import { computed, defineComponent, onMounted, reactive, ref, toRefs } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { getAPI, SearchParent, SearchListParent, qstrInitial, qstr, RdURL } from '@/type';
import SearchSkeleton from '@/components/bbs/SearchSkeleton.vue'
import Sideview from '@/components/bbs/Sideview.vue'
import { useHead } from '@vueuse/head'

export default defineComponent({
  components: {
    SearchSkeleton,
    Sideview,
  },
  setup () {
    const Search = reactive<SearchParent>(SearchListParent);
    const route = useRoute();
    const isLoading = ref<boolean>(false);
    const query = Object.keys(route.query).map((key) => [`${key}=${route.query[key]}`]);
    const router = useRouter();
    const qstr = reactive<qstr>({
      ...qstrInitial,
      ...route.query
    });
    const sop = ref(qstr.sop === 'and' ? true : false);
    const pageEvent = (page:any) => {
      if(page.page.toString() != qstr.page) {
        router.push({path : route.path, query : {onetable: qstr.onetable, sop : qstr.sop, page: page.page + 1, stx : qstr.stx, sfl: qstr.sfl}});
      }
    }
    const f_search = (e:Event) => {
      e.preventDefault();      
      qstr.sop = sop.value === true ? 'and' : 'or';
      console.log(qstr.sop);
      router.push({path : route.path, query : {gr_id: qstr.gr_id, sop : qstr.sop, stx : qstr.stx, sfl: qstr.sfl}});
    }
    onMounted(async () => {
      const res = await getAPI(`/search${'?' + query.join('&')}`);
      Search.str_board_list = res.data.str_board_list;
      Search.group_select = res.data.group_select;
      Search.list = res.data.list;
      Search.write_pages = res.data.write_pages;
      Search.page_rows = res.data.page_rows;
      Search.page = res.data.page - 1;
      Search.total_count = res.data.total_count;
      isLoading.value = true;
    });
    useHead({
      title: computed(()=>`${qstr.stx} 전체 검색 결과`),
    })
    return {
      isLoading,
      sop,
      qstr,
      query,
      RdURL,
      pageEvent,
      f_search,
      ...toRefs(Search),
    }
  }
})
</script>

<style scoped>
</style>