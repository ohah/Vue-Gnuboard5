<template>
  <Toast position="bottom-right" />
  <Dialog v-model:visible="isMove">
    <template #header>
		  <h3>이동/복사</h3>
	  </template>
    <ul v-if="move">
      <li v-for="(row, i) in move.list" :key="i" class="p-d-flex list-move-copy p-ai-center p-my-1 p-py-2 p-jc-between">
        <div class="p-d-inline-flex p-ai-center">
          <Checkbox class="p-mr-2" v-model="isMoveCheck" :value="row.bo_table" />
          <span class="p-mr-2"> {{row.gr_subject}} > </span>
          <span class="p-mr-2"> {{row.bo_subject}}({{row.bo_table}}) </span>
        </div>
        <div class="p-d-inline-flex p-ai-center p-bold" v-if="bo_table === row.bo_table">
          현재
        </div>
      </li>
    </ul>
    <template #footer>
      <Button label="취소" icon="pi pi-times" class="p-button-text" @click="isMove = false"/>
      <Button label="확인" icon="pi pi-check" autofocus @click="mc_submit"/>
    </template>
  </Dialog>
  <div>
    <div class="">
      <div class="">
        <span> Total {{total_count}}건 </span> {{page}} 페이지 
      </div>
    </div>
    <ul class="p-d-flex p-my-3">
      <li v-for="(item, i) in category_option" :key="i" class="p-mx-1">
        <router-link :to="RdURL(item.url)"> <Button :class="{'p-button-secondary' : item.active == true}" :label="item.name" class="p-button-sm p-button-raised"/></router-link>
      </li>
    </ul>
    <ul class="p-d-flex p-my-3 p-jc-end">
      <li v-if="admin_href" class="p-mx-2"> <a :href="admin_href"><Button label="" icon="pi pi-cog" class="p-button-sm p-button-danger p-button-text" v-tooltip.bottom="'관리자'"/> </a></li>
      <li class="p-mx-2"> 
        <Button @click="toggleSearch" label="" icon="pi pi-search" v-tooltip.bottom="'검색'" class="p-button-sm p-button-help p-button-text"/>
        <OverlayPanel ref="Search" style="padding:0">
          <form @submit.prevent="Search_onSubmit($event)">
            <div class="p-d-flex">
              <Dropdown v-model="sfl" style="width:100%"
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
            </div>
            <div class="p-d-flex">
              <InputText type="text" v-model="stx" />
              <Button type="submit" label="검색" icon="pi pi-search" class="p-button-sm p-button-help"/>
            </div>
          </form>
        </OverlayPanel>
      </li>
      <li v-if="write_href" class="p-mx-2"> <router-link :to="RdURL(write_href)"><Button label="" icon="pi pi-pencil" class="p-button-sm p-button-secondary p-button-text" v-tooltip.bottom="'글쓰기'" /> </router-link></li>
      <Button v-if="admin_href" @click="admin_menu_toogle" label="" icon="pi pi-ellipsis-v" class="p-button-sm p-button-plain p-button-text" aria-haspopup="true" aria-controls="overlay_tmenu"/>
      <Menu ref="admin_menu" :model="admin_items" style="border:0" :popup="true" />
    </ul>
    <SelectButton v-model="Listlayout" :options="ListOptions" optionValue="value" class="p-my-1 p-jc-end p-d-flex">
      <template #option="slotProps">
        <i :class="slotProps.option.icon"></i>
      </template>
    </SelectButton>
    <DataView v-if="isLoading === true" :value="list" :layout="Listlayout">
      <template #header>
        <ul class="board-list p-grid-list p-header-list" style="padding:1rem 0;" v-if="Listlayout === 'list'" 
          :style="{
            gridTemplateColumns : config.colWidth,
          }">
          <li class="p-jc-center" v-if="is_checkbox"> <Checkbox v-model="all_check" :binary="true" @click="Allcheck()"/> </li>
          <li class="p-jc-center"> 번호 </li>
          <li class="p-px-2"> 제목 </li>
          <li> 글쓴이 </li>
          <li class="p-jc-center" v-if="is_good"> <i class="pi pi-thumbs-up"></i> </li>
          <li class="p-jc-center" v-if="is_nogood"> <i class="pi pi-thumbs-down"></i> </li>
          <li class="p-jc-center"> <i class="pi pi-eye"></i> </li>
          <li class="p-jc-center"> <i class="pi pi-clock"></i> </li>
        </ul>
      </template>
      <template #list="row">
        <ul class="board-list p-grid-list" style="padding:1rem 0;" 
          :style="{
            gridTemplateColumns : config.colWidth,
          }">
          <li v-if="is_checkbox" class="p-jc-center"> <Checkbox v-model="checkbox" :value="row.data.wr_id" /> </li>
          <li class="p-jc-center"> {{row.data.num}} </li>
          <li class="p-list-subject" :style="{paddingLeft : row.data.reply ? `${row.data.reply.toString().length * 2.5}px` : ``}">
            <div class="p-d-flex p-ai-center">
              <i v-if="row.data.icon_reply" class='p-mr-2 pi pi-reply' style="transform: rotate(270deg)"> </i>
              <Avatar class="p-mr-2 p-px-1" v-if="row.data.ca_name" style="white-space:nowrap;">{{row.data.ca_name}} </Avatar>
              <router-link :to="RdURL(row.data.href)">
                <span class="p-mr-2"> {{row.data.wr_subject}} </span>
                <Avatar v-if="row.data.icon_new" label="N" class="p-mr-2" style="background-color:#4caf4f; color: #ffffff" />
                <i v-if="row.data.icon_secret" class='p-mr-2 pi pi-lock'> </i>
                <i v-if="row.data.icon_file" class='p-mr-2 pi pi-file'> </i>
                <i v-if="row.data.icon_hot" class='p-mr-2 pi pi-file'> </i>
                <i v-if="row.data.icon_link" class='p-mr-2 pi pi-file'> </i>
                <i v-if="row.data.icon_file" class='p-mr-2 pi pi-file'> </i>
                <Avatar class="p-ml-2" v-if="row.data.wr_comment" :label="`${row.data.wr_comment}`" /> 
              </router-link>
            </div>
          </li>
          <li> <Sideview :data="row.data.name"/>  </li>
          <li class="p-jc-end p-pr-3" v-if="is_good"> {{row.data.wr_good}} </li>
          <li class="p-jc-end p-pr-3" v-if="is_nogood"> {{row.data.wr_nogood}} </li>
          <li class="p-jc-end p-pr-3"> {{row.data.wr_hit}} </li>
          <li class="p-jc-end p-pr-3"> {{row.data.datetime2}} </li>
        </ul>
        <ul class="p-m-list">
          <li class="p-d-flex p-ai-center p-py-2 p-px-2">
            <i v-if="row.data.icon_reply" class='p-mr-2 pi pi-reply' style="transform: rotate(270deg)"> </i>
            <Avatar class="p-mr-2 p-px-1" v-if="row.data.ca_name" style="white-space:nowrap;">{{row.data.ca_name}} </Avatar>
            <router-link :to="RdURL(row.data.href)">
              <span class="p-mr-2"> {{row.data.wr_subject}} </span>
              <Avatar v-if="row.data.icon_new" label="N" class="p-mr-2" style="background-color:#4caf4f; color: #ffffff" />
              <i v-if="row.data.icon_secret" class='p-mr-2 pi pi-lock'> </i>
              <i v-if="row.data.icon_file" class='p-mr-2 pi pi-file'> </i>
              <i v-if="row.data.icon_hot" class='p-mr-2 pi pi-file'> </i>
              <i v-if="row.data.icon_link" class='p-mr-2 pi pi-file'> </i>
              <i v-if="row.data.icon_file" class='p-mr-2 pi pi-file'> </i>
              <Avatar class="p-ml-2" v-if="row.data.wr_comment" :label="`${row.data.wr_comment}`" /> 
            </router-link>
          </li>
          <li class="p-d-flex p-py-1 p-jc-between p-px-2">
            <div class="p-d-flex p-ai-center text-gray-500">
              <Sideview :data="row.data.name"/>
              <span class="p-mx-1 p-d-flex p-ai-center"> <i class="pi pi-eye p-mr-1"></i> {{row.data.wr_hit}} </span>
              <span v-if="is_good" class="p-mx-1 p-d-flex p-ai-center"> <i class="pi pi-thumbs-up p-mr-1"></i> {{row.data.wr_good}} </span>
              <span v-if="is_nogood" class="p-mx-1 p-d-flex p-ai-center"> <i class="pi pi-thumbs-down p-mr-1"></i> {{row.data.wr_nogood}} </span>
            </div>
            <span class="p-mx-1 text-gray-500"> <i class="pi pi-clock"></i> {{row.data.datetime2}} </span>
          </li>
        </ul>
      </template>
      <template #grid="row">
        <div class="p-col-12 p-md-4 p-lg-3">
          <div class="p-m-2 p-shadow-2" style="background:var(--surface-0)">
            <Galleria :value="[row.data]" :numVisible="1" :showThumbnails="false">
              <template #item="{item}">
                <router-link :to="RdURL(row.data.href)" style="display:contents;">
                  <div v-if="item.thumb.src" >
                    <img :src="`${item.thumb.src}`" :alt="item.source" class="grid-image"/>
                  </div>
                  <div v-else class="grid-no-image">
                    NO IMAGE
                  </div>
                </router-link>
              </template>
              <template #caption="{item}">
                <p class="p-d-flex p-jc-between p-py-2 p-px-2"> 
                  <span cass="p-bold"><router-link :to="RdURL(item.href)">{{item.wr_subject}}</router-link></span>
                  <span class="text-gray-500 p-pr-2">{{item.wr_datetime.substr(5,5)}}</span>
                </p>
                <Sideview :data="item.name" class="p-py-1 p-pr-2 p-d-flex p-jc-end"/>
              </template>
            </Galleria>
            <!-- <div v-if="row.data.thumb.src" >
              <img :src="row.data.thumb.src" class="grid-image"/>
            </div>
            <div v-else class="grid-no-image">
              NO IMAGE
            </div> -->
          </div>
        </div>
      </template>
      <template #empty>
        <div class="p-d-flex p-p-3 p-jc-center">글 목록이 없습니다.</div>
      </template>
    </DataView>
    <DataView v-else :value="Array.from(Array(15), () => Array(8).fill(true))">
      <template #header>
        <ul class="board-list p-grid-list" style="padding:1rem 0;grid-template-columns:2.5rem auto 8rem 4rem 4rem">
          <li class="p-px-1"> 번호 </li>
          <li class="p-px-1"> 제목 </li>
          <li class="p-px-1"> 글쓴이 </li>
          <li class="p-px-1"> 조회수 </li>
          <li class="p-px-1"> 날짜 </li>
        </ul>
      </template>
      <template #list>
        <ul class="board-list p-grid-list" style="padding:1rem 0;grid-template-columns:2.5rem auto 8rem 4rem 4rem" >
          <li class="p-mx-1"><Skeleton width="1.5rem" /></li>
          <li class="p-mx-1"><Skeleton width="80%" /></li>
          <li class="p-mx-1"><Skeleton width="4rem" /></li>
          <li class="p-mx-1"><Skeleton width="1rem" /></li>
          <li class="p-mx-1"><Skeleton width="3rem" /></li>
        </ul>
      </template>
      <template #empty>
        <div class="p-d-flex p-p-3">글 목록이 없습니다.</div>
      </template>
    </DataView>
    <Paginator style="background:transparent" :rows="page_rows" :first="(page * 10)" :totalRecords="total_count" @page="onPage($event)"></Paginator>
    <ul class="p-d-flex p-jc-end">
      <li v-if="write_href" class="p-mx-2"> <router-link :to="RdURL(write_href)"><Button label="" icon="pi pi-pencil" class="p-button-sm p-button-secondary p-button-text" v-tooltip.bottom="'글쓰기'" /> </router-link></li>
    </ul>
  </div>
  <ConfirmDialog></ConfirmDialog>
</template>

<script lang="ts">
import { defineComponent, computed, ref, reactive, onMounted, toRefs} from 'vue';
import { useRoute, useRouter } from 'vue-router'
import { getAPI, move, serialize, qstr, qstrInitial, RdURL, ListParent, Board, ListParentInitial, getPostAPI, MoveInitial} from '@/type'
import { useStore } from 'vuex';
import { RootState } from '@/store';
import { PrimeIcons } from 'primevue/api';
import { useToast } from "primevue/usetoast";
import { useConfirm } from "primevue/useconfirm";
import { useHead } from '@vueuse/head'
import ConfirmDialog from 'primevue/confirmdialog';
import Sideview from '@/components/bbs/Sideview.vue';

interface configState {
  colspan:number,
  col:Array<any>,
  colWidth:string,
  search:boolean
}
export default defineComponent({
  name : 'List',
  components : {
    ConfirmDialog,
    Sideview,
  },
  setup() {
    const route = useRoute();
    const confirm = useConfirm();
    const router = useRouter();
    const toast = useToast();
    const ListOptions = ref([
      {icon: 'pi pi-bars', value: 'list'},
      {icon: 'pi pi-th-large', value: 'grid'},
    ]);
    const Listlayout = ref<string>(`list`);
    const isLoading = ref<boolean>(false);
    const store = useStore<RootState>();
    const member = computed(()=>store.state.member);
    const qstr = reactive<qstr>({
      ...qstrInitial,
      ...route.query
    });
    
    const isMoveCheck = ref<string[]>([]);
    const isMove = ref<boolean>(false);
    const checkbox = ref<number[]>([]);
    const all_check = ref<boolean>(false);
    const config = reactive<configState>({
      colspan : 5,
      col : ["2.5rem", "auto", "6rem", "4rem", "4rem"],
      colWidth : '',
      search : false,
    });
    const sw = ref<string>('move');
    const Search = ref<HTMLElement>();
    const admin_menu = ref<HTMLElement>();
    const res = reactive<ListParent>(ListParentInitial);
    const move = reactive<move>(MoveInitial);
    const board = ref<Board>();
    const admin_items = reactive([
				{
					label: '선택삭제',
					icon: PrimeIcons.TRASH,
					command: () => {
            confirm.require({
              message: [
                '선택한 게시물을 정말 삭제하시겠습니까?',
                '한번 삭제한 자료는 복구할 수 없습니다.',
                '답변글이 있는 게시글을 선택하신 경우',
                '답변글도 선택하셔야 게시글이 삭제됩니다.',
              ].join('\r\n'),
              header: '안내',
              icon: 'pi pi-info-circle',
              acceptClass: 'p-button-danger',
              accept: async () => {
                //callback to execute when user confirms the action
                const f = new FormData();
                f.append('bo_table', qstr.bo_table)
                if(checkbox.value) {
                  checkbox.value.forEach((value, i) => {
                    f.append(`chk_wr_id[${i}]`, `${value}`);
                  });
                }
                const res = await getPostAPI(`/board/${qstr.bo_table}/all`, f);
                getList();
              },
              reject: () => {
                //callback to execute when user rejects the action
              }
            });
					}
				},
				{
					label: '선택복사',
					icon: PrimeIcons.COPY,
					command: async () => {
            if(checkbox.value.length > 0) {
              isMove.value = true;
              const f = new FormData();
              checkbox.value.forEach((value, i) => {
                f.append(`chk_wr_id[${i}]`, `${value}`);
              });
              sw.value = 'copy';
              const res = await getPostAPI(`/move/copy`, f);
              move.list = res.data.list;
              move.wr_id_list = res.data.wr_id_list;
            } else {
              confirm.require({
                message: [
                  '삭제 할 게시물을 선택해주세요',
                ].join('\r\n'),
                header: '안내',
                icon: 'pi pi-info-circle',
                acceptClass: 'p-button-danger',
              });
            }
					}
				},
				{
					label: '선택이동',
					icon: PrimeIcons.PRINT,
					command: async () => {
            if(checkbox.value.length > 0) {
              isMove.value = true;
              const f = new FormData();
              checkbox.value.forEach((value, i) => {
                f.append(`chk_wr_id[${i}]`, `${value}`);
              });
              sw.value = 'move';
              const res = await getPostAPI(`/move/move`, f);
              move.list = res.data.list;
              move.wr_id_list = res.data.wr_id_list;
            } else {
              confirm.require({
                message: [
                  '이동 할 게시글을 선택해주세요.',
                ].join('\r\n'),
                header: '안내',
                icon: 'pi pi-info-circle',
                acceptClass: 'p-button-danger',
              });
            }
					}
				},
		]);
    const mc_submit = async () => {
      const f = new FormData();
      isMoveCheck.value.forEach((value, i) => {
        f.append(`chk_bo_table[]`, `${value}`);
      });
      f.append(`wr_id_list`, `${checkbox.value.join(',')}`);
      console.log(checkbox.value, isMoveCheck.value);
      const res = await getPostAPI(`/move_update/${sw.value}/${qstr.bo_table}/`,f);
      if(res.data.msg === 'success') {
        toast.add({severity:'success', summary: '성공', detail:`선택한 게시물이 선택한 게시판으로 ${sw.value ==='move' ? "이동하였습니다" : "복사되었습니다"}`, life: 3000});
      }else {
        toast.add({severity:'error', summary: '경고', detail:res.data.msg, life: 3000});
      }
      getList();
      isMove.value = false;
    }
    const pageEvent = (page:number) => {
      // qstr.page = `${page}`;
      if(page.toString() != qstr.page) {
        router.push({path : route.path, query : {bo_table : qstr.bo_table, page: page, stx : qstr.stx, sfl: qstr.sfl}});
      }
      // getList();
    }
    onMounted(async () => {
      getList();
    });
    useHead({
      title: computed(() => res.title),
    })
    const getList = async () => {
      const _res = await getAPI(`/board/${qstr.bo_table}?${serialize(qstr)}`);
      res.write_href = _res.data.write_href;
      res.total_page = _res.data.total_page;
      res.total_count = _res.data.total_count;
      res.page = _res.data.page;
      res.write_href = _res.data.write_href;
      res.bo_gallery_cols = _res.data.bo_gallery_cols;
      res.td_width = _res.data.td_width;
      res.list = _res.data.list;
      res.qstr = _res.data.qstr;
      res.is_category = _res.data.is_category;
      res.admin_href = _res.data.admin_href;
      res.rss_href = _res.data.rss_href;
      res.is_checkbox = _res.data.is_checkbox;
      res.colspan = _res.data.colspan;
      res.is_good = _res.data.is_good;
      res.is_nogood = _res.data.is_nogood;
      res.page_rows = _res.data.page_rows;
      res.write_pages = _res.data.write_pages;
      res.category_option = _res.data.category_option;
      res.title = _res.data.title;
      board.value = _res.data.board;
      if(isLoading.value === false) {
        if(res.is_checkbox === true ) config.col.splice(0,0,['2rem']); //내일 할 것. 삭제 시 이 푸시 부분이 또 동작함.
        if(res.is_good === true) config.col.push('4rem'); //내일 할 것. 삭제 시 이 푸시 부분이 또 동작함.
        if(res.is_nogood === true) config.col.push('4rem'); //내일 할 것. 삭제 시 이 푸시 부분이 또 동작함.
      }
      config.colWidth = config.col.join(' ');
      isLoading.value = true;
    }
    return {
      ...toRefs(qstr),
      ...toRefs(res),
      RdURL,
      config,
      pageEvent,
      Search,
      isLoading,
      member,
      checkbox,
      all_check,
      admin_menu,
      Listlayout,
      admin_items,
      ListOptions,
      isMove,
      move,
      sw,
      mc_submit,
      isMoveCheck,
    };
  },
  methods : {
    onPage(event:any) {
      this.pageEvent(event.page+1)
    },
    toggleSearch(event:any) {
      (this.$refs.Search as any).toggle(event);
    },
    Search_onSubmit(e:Event) {
      e.preventDefault();
      const stx = this.stx;
      const sfl = this.sfl;
      if(!stx || !sfl) {
        this.$toast.add({severity:'error', summary: '경고', detail:'검색조건과 검색어를 입력해주세요.', life: 3000});
      }else {
        this.$router.push({path : this.$route.path, query : {bo_table : this.bo_table, stx : stx, sfl: sfl}});
        (this.$refs.Search as any).toggle(e);
      }
    },
    Allcheck() {
      if(this.all_check === false) {
        const data = this.list.map(row => {
          return row.wr_id;
        });
        this.checkbox = data;
      }else if(this.all_check === true){
        this.checkbox = [];
      }      
    },
    admin_menu_toogle(e:Event) {
      (this.$refs.admin_menu as any).toggle(e);
    }
  }
})
</script>

<style scoped>
.grid-no-image, .grid-image{width:100%;height:10rem;display: flex;justify-items: center;justify-content: center;align-items: center;border:1px solid var(--surface-200);background-color:var(--surface-400);color:var(--surface-50);}
.grid-footer{min-height:2rem}
</style>