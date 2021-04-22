<template>
  <div v-if="isLoading">
    <Toast position="bottom-right" />
    <Dialog header="비밀번호를 입력하세요" v-model:visible="PasswordModal" :modal="true">
      <Password name="wr_password" class="p-w-full" v-model="m.password" toggleMask placeholder="비밀번호"></Password>
      <template #footer>
        <Button label="취소" icon="pi pi-times" @click="PasswordModal = false" class="p-button-text"/>
        <Button label="확인" icon="pi pi-check" @click="password" autofocus />
      </template>
    </Dialog>
    <Dialog header="삭제" v-model:visible="Modal.Delete.is" :modal="true">
      삭제하시겠습니까?
      <template #footer>
        <Button label="취소" icon="pi pi-times" @click="Modal.Delete.is = false" class="p-button-text"/>
        <Button label="확인" icon="pi pi-check" @click="delete_update" autofocus />
      </template>
    </Dialog> 
    <Dialog :header="view.wr_subject" v-model:visible="Modal.Scrap.is" :style="{minWidth : '350px'}" :modal="true">
      <Textarea class="p-w-full" v-model="Modal.Scrap.content" rows="5" cols="30" placeholder="스크랩을 하시면서 감사 혹은 격려의 댓글을 남기실 수 있습니다."/>
      <template #footer>
        <form v-on:submit.prevent="scrap_update">
          <Button label="취소" icon="pi pi-times" @click="Modal.Scrap.is = false" class="p-button-text"/>
          <Button label="스크랩" icon="pi pi-check" type="submit" autofocus />
        </form>
      </template>
    </Dialog>
    <Panel :toggleable="true" :collapsed="false" class="p-shadow-1">
      <template #header>
        <div>
          <Chip v-if="view.ca_name" :label="view.ca_name" class="p-mr-2 custom-chip" /><span class="p-bold">{{view.wr_subject}}</span>
        </div>
      </template>
      <section ref="bo_v_info" class="p-d-flex p-jc-end text-gray-500">
        <h2 class="sound_only">페이지 정보</h2>
        <div class="p-d-flex p-px-1">
          <div class="p-mr-2 p-d-flex" >
            <i class="pi pi-user-edit p-mr-2"></i> <SideView :data="view.name" /> <span v-if="board.bo_use_ip_view"> {{view.wr_ip}} </span>
          </div>
          <div class="ddd">
            <i class="pi pi-comment"></i> <span class="p-px-1"><a href="#bo_vc"> <i class="far fa-comment-dots"></i> {{view.wr_comment}} 건</a></span>
            <i class="pi pi-eye"></i><span class="p-px-1">{{view.wr_hit}} 회</span>
            <span class="p-px-1"><span class="sound_only">작성일</span><i class="far fa-clock"></i> {{view.datetime}} </span>
          </div>
        </div>
      </section>
      <Divider />
      <ul class="p-d-flex p-jc-end">
        <router-link class="p-d-flex" :to="RdURL(list_href)"><Button class="p-px-2 p-mx-1 p-button-raised p-button-secondary"><i class="pi pi-list"></i><span class="p-ml-1 p-d-none p-d-md-inline">목록</span></Button></router-link>
        <router-link class="p-d-flex" :to="RdURL(reply_href)"><Button class="p-px-2 p-mx-1 p-button-raised p-button-secondary"><i class="pi pi-reply"></i><span class="p-ml-1 p-d-none p-d-md-inline">답변</span></Button></router-link>
        <router-link class="p-d-flex" :to="RdURL(write_href)"><Button class="p-px-2 p-mx-1 p-button-raised p-button-secondary"><i class="pi pi-pencil"></i><span class="p-ml-1 p-d-none p-d-md-inline">글쓰기</span></Button></router-link>
        <Button v-if="update_href" @click="modify(RdURL(update_href));" class="p-px-2 p-mx-1 p-button-raised p-button-secondary"><i class="pi pi-user-edit"></i><span class="p-ml-1 p-d-none p-d-md-inline">수정</span></Button>
        <Button v-if="delete_href" @click="view_delete(delete_href)" class="p-px-2 p-mx-1 p-button-raised p-button-secondary"><i class="pi pi-trash"></i><span class="p-ml-1 p-d-none p-d-md-inline">삭제</span></Button>
        <Button v-if="copy_href" class="p-px-2 p-mx-1 p-button-raised p-button-secondary"><router-link :to="RdURL(copy_href)" class="cursor-pointer"><i class="pi pi-copy"></i><span class="p-ml-1 p-d-none p-d-md-inline">복사</span></router-link></Button>
        <Button v-if="move_href" class="p-px-2 p-mx-1 p-button-raised p-button-secondary"><route-link :to="RdURL(move_href)" class="cursor-pointer"><i class="pi pi-wallet"></i><span class="p-ml-1 p-d-none p-d-md-inline">이동</span></route-link></Button>
      </ul>
      <Divider />
      <section id="bo_v_atc" class="p-pb-3">
        <div v-for="(file, i) in view.file" :key="i" >
          <Galleria v-if="imageExt(file.name)" :value="[file]" :numVisible="1" :showThumbnails="false" >
            <template #item="{item}">
              <img :src="`${item.path}/${item.file}`" class="p-mb-5 p-cursor-pointer" style="max-height:25rem;"  @click="ImageView[i] = !ImageView[i]"/>                
            </template>
          </Galleria>
          <Galleria v-if="imageExt(file.name)" :value="[file]" :numVisible="1" :showThumbnails="false" :fullScreen="true" v-model:visible="ImageView[i]">
            <template #item="{item}">
              <img :src="`${item.path}/${item.file}`" style="width:100%"/>                
            </template>
          </Galleria>
        </div>
        <h2 id="bo_v_atc_title" class="sound_only">본문</h2>
        <div class="p-d-flex p-jc-end">
          <Button v-if="scrap_href" @click="Modal.Scrap.is = true;" class="p-px-2 p-mx-1 p-button-text p-button-secondary"><i class="pi pi-bookmark p-mr-1"></i>스크랩</Button>
        </div>
        <div id="bo_v_con" class="p-mb-4" v-html="view.content"></div>
        <!-- 추천 비추천 시작 { -->
        <div id="bo_v_act" class="p-d-flex p-jc-center p-w-full" v-if="good_href || nogood_href">
          <div class="p-mr-3 p-cursor-pointer" v-if="good_href">
            <Avatar size="large" icon="pi pi-thumbs-up" v-badge.info="`${view.wr_good}`" @click="vote('good')"/>
          </div>
          <span class="p-cursor-pointer" v-if="nogood_href">
            <Avatar size="large" icon="pi pi-thumbs-down" v-badge.danger="`${view.wr_nogood}`" @click="vote('nogood')" />
          </span>
        </div>
        <div v-else>
          <div v-if="board.bo_use_good || board.bo_use_nogood" id="bo_v_act" class="p-d-flex p-jc-center p-w-full" >
            <div class="p-mr-3" v-if="board.bo_use_good">
              <Avatar size="large" icon="pi pi-thumbs-up" v-badge.danger="`${view.wr_good}`"/>
              <span class="sound_only">추천</span>
            </div>
            <div v-if="board.bo_use_nogood">
              <Avatar size="large" icon="pi pi-thumbs-down" v-badge.danger="`${view.wr_nogood}`"/>
              <span class="sound_only">비추천</span>
            </div>
          </div>
        </div>
        <!-- }  추천 비추천 끝 -->
      </section>
      <div v-for="(file, i) in view.file" :key="i">
        <div v-if="imageExt(file.name) === false" class="p-shadow-1 p-p-2 p-my-1 p-d-flex p-jc-between">
          <div>
            <a :href="file.href" class="file-href">
              <p class="p-my-2">
                <span class="p-bold p-mr-2">{{file.name}}</span>
                <span class="text-gray-500">{{file.size}}</span>
              </p>
              <p class="p-my-2">
                <span class="text-gray-500">{{file.download}}회 다운로드</span>
                <span class="text-gray-500">{{file.datetime}}</span>
              </p>
            </a>
          </div>
        </div>
      </div>
      <div class="p-p-2 p-my-2 wr_link p-d-flex p-jc-between p-ai-center" v-if="view.wr_link1">
        <div>
          <a :href="view.wr_link1" target="_blank">
            <p class="p-my-2 p-d-flex p-ai-center">
              <i class="pi pi-link"> </i>
              <span class="p-bold p-ml-2">{{view.wr_link1}}</span>
            </p>
          </a>
        </div>
      </div>
      <div class="p-p-2 p-my-2 wr_link p-d-flex p-jc-between p-ai-center" v-if="view.wr_link2">
        <div>
          <a :href="view.wr_link2" target="_blank">
            <p class="p-my-2 p-d-flex p-ai-center">
              <i class="pi pi-link"> </i>
              <span class="p-bold p-ml-2">{{view.wr_link2}}</span>
            </p>
          </a>
        </div>
      </div>
    </Panel>
    <section class="p-pt-3 text-gray-600" v-if="prev_href || next_href">
      <div class="p-shadow-1 bg-white" style="border-radius:3px;">
        <div class="p-d-flex p-px-3 p-pt-3" v-if="prev_href">
          <span class="p-mr-2"><i class="pi pi-angle-up"></i> 이전글</span><router-link :to="RdURL(prev_href)">{{prev_wr_subject}}</router-link> <span class="p-ml-auto">{{prev_wr_date}}</span>
        </div>
        <Divider />
        <div class="p-d-flex p-px-3 p-pb-3" v-if="next_href">
          <span class="p-mr-2"><i class="pi pi-angle-down"></i> 다음글</span><router-link :to="RdURL(next_href)">{{next_wr_subject}}</router-link> <span class="p-ml-auto">{{next_wr_date}}</span>
        </div>
      </div>
    </section>
  </div>
  <div v-else>
    <ViewSkeleton />
  </div>
  <ViewComment v-if="view && isLoading"
    ref="Comment"
    :bo_table="bo_table"
    :wr_id="wr_id"
  />
  <ConfirmDialog></ConfirmDialog>
</template>

<script lang="ts">
import { defineComponent, computed, reactive, onMounted, ref, toRefs}  from 'vue';
import { useRoute, useRouter } from 'vue-router'
import { useStore } from 'vuex';
import { useToast } from "primevue/usetoast";
import { getAPI, getPostAPI, ViewParent, qstrInitial, qstr, queryString, RdURL, member as _member, ViewParentInitial, newline} from '@/type'
import  ViewComment from './ViewComment.vue'
import { RootState } from '@/store';
import SideView from '@/components/bbs/Sideview.vue'
import ViewSkeleton from '@/components/bbs/ViewSkeleton.vue';
import ConfirmDialog from 'primevue/confirmdialog';
import { useConfirm } from "primevue/useconfirm";
import { useHead } from '@vueuse/head'
export default defineComponent({
  name : 'View',
  components : {
    ViewComment,
    ViewSkeleton,
    ConfirmDialog,
    SideView
  },
  setup() {    
    const route = useRoute();
    const router = useRouter();
    const isLoading = ref<boolean>(false);
    const PasswordModal = ref<boolean>(false);
    const toast = useToast();
    const confirm = useConfirm();
    const Comment = ref<InstanceType<typeof ViewComment>>();
    const Modal = reactive({
      Scrap : {
        is : false,
        content : '',
      },
      Delete : {
        is : false,
        password : '',
        isPassword : false,
        token:'',
      }
    });
    const wr_password = ref<HTMLInputElement>();
    const qstr = reactive<qstr>({
      ...qstrInitial,
      ...route.query
    });
    const vote = async (type:string) => {
      const res = await getPostAPI(`/board/good/${qstr.bo_table}/${qstr.wr_id}/${type}`);
      if(res.data.error) {
        toast.add({severity:'warn', summary: '안내', detail: res.data.error, life: 3000});
      } else {
        if(type === 'good') {
          data.view.wr_good = res.data.count;
          toast.add({severity:'success', summary: '안내', detail: '추천하셨습니다', life: 3000});
        }else if(type === 'nogood') {
          toast.add({severity:'danger', summary: '안내', detail: '비추천하셨습니다', life: 3000});
          data.view.wr_nogood = res.data.count;
        }
      }
    }
    const w = ref<string>('s');
    const url = ref<string>('');
    const { state, dispatch } = useStore<RootState>(); // 저장소 객체 주입받아 사용하기
    const member = computed(() => state.member);
    const data = reactive<ViewParent>(ViewParentInitial);
    useHead({
      title: computed(() => data.title),
    })
    const getVIEW = async () => {
      const res = await getAPI(`/board/${qstr.bo_table}/${qstr.wr_id}`);
      if(res.data.msg) {
        confirm.require({
          message: newline(res.data.msg),
          header: '안내',
          icon: 'pi pi-info-circle',
          acceptClass: 'p-button-danger',
          rejectClass: 'p-hidden',
          accept: async () => {
            router.push({name : '/bbs/board', query : {bo_table : qstr.bo_table}})
          },
          reject: () => {
            router.push({name : '/bbs/board', query : {bo_table : qstr.bo_table}})
          }
        });
      } else {
        data.list_href = res.data.list_href;
        data.reply_href = res.data.reply_href;
        data.update_href = res.data.update_href;
        data.delete_href = res.data.delete_href;
        data.copy_href = res.data.copy_href;
        data.move_href = res.data.move_href;
        data.good_href = res.data.good_href;
        data.scrap_href = res.data.scrap_href;
        data.prev_wr_subject = res.data.prev_wr_subject;
        data.prev_href = res.data.prev_href;
        data.prev_wr_date = res.data.prev_wr_date;
        data.next_wr_subject = res.data.next_wr_subject;
        data.next_href = res.data.next_href;
        data.next_wr_date = res.data.next_wr_date;
        data.write_href = res.data.write_href;
        data.next_wr_subject = res.data.next_wr_subject;
        data.good_href = res.data.good_href;
        data.nogood_href = res.data.nogood_href;
        data.is_signature = res.data.is_signature;
        data.signature = res.data.signature;
        data.view = res.data.view;
        data.board = res.data.board;
        data.title = res.data.title;
        isLoading.value = true;
      }
    }
    const m = reactive({
      password:'',
      url:'',
    });
    const scrap_update = async (e:Event) => {
      e.preventDefault();
      const f = new FormData();
      f.append('wr_content', Modal.Scrap.content);
      const res = await getPostAPI(`/member/scrap_update/${qstr.bo_table}/${qstr.wr_id}`, f);
      Modal.Scrap.is = false;
      toast.add({severity:'info', summary: '안내', detail: res.data.msg, life: 3000});
      Comment.value?.getCmt();
    }
    const password = async () => {
      const w = 's'
      // router.push(m.url);
      if(m.password === '') {
        alert('비밀번호를 입력하세요');
      } else {
        const result = await getPostAPI(`/password/${w}/${qstr.bo_table}/${qstr.wr_id}`, {
          wr_password : m.password,
          input : m.url,
        });
        if(result.data.msg !== 'success') {
          alert(result.data.msg);
        }else {
          router.push(m.url)
        }
      }
    }
    const modify = async (url:string) => {
      const w = 's'
      const result = await getPostAPI(`/password/${w}/${qstr.bo_table}/${qstr.wr_id}`);
      if(result.data.msg !== 'success') {
        m.url = url;
        PasswordModal.value = true;
      }else {
        router.push(url)
      }
    }
    const imageExt = (name:string) => {
      var _fileLen = name.length;
      var _lastDot = name.lastIndexOf('.');
      var _fileExt = name.substring(_lastDot, _fileLen).toLowerCase();
      const ext = ['.jpg','.jpeg','.png','.bmp','.gif','.webp', '.tiff'];
      if(ext.indexOf(_fileExt) !== -1) {
        return true;
      }else {
        return false;
      }
    }
    const view_delete = async (delete_href:string) => {
      const href = queryString(delete_href);
      Modal.Delete.is = true;
      if(href.token) {
        Modal.Delete.isPassword = false;
        Modal.Delete.token = href.token;
        console.log('바로 삭제 가능');
      }else {
        Modal.Delete.isPassword = true;
        Modal.Delete.token = '';
        console.log('비밀번호');
      }
    }
    const delete_update = async () => {
      const f = new FormData();
      f.append('wr_password', Modal.Delete.password);
      f.append('token', Modal.Delete.token);
      const res = await getPostAPI(`/board/${qstr.bo_table}/${qstr.wr_id}`, f); 
      if(res.data.msg === 'success') {
        router.push({name : '/bbs/board', query : {bo_table : qstr.bo_table}})
      }else {
        alert(res.data.msg);
      }
    }
    onMounted(async () => {
      getVIEW();
    });
    const ImageView = reactive({});
    return {
      ImageView,
      ...toRefs(qstr),
      ...toRefs(data),
      isLoading,
      route,
      member,
      w,
      m,
      imageExt,
      wr_password,
      getVIEW,
      RdURL,
      url,
      password,
      vote,
      PasswordModal,
      modify,
      Modal,
      Comment,
      view_delete,
      scrap_update,
      delete_update
    }
  },
  methods: {
    
  },
})
</script>
<style scoped>
.wr_link{border:1px solid var(--surface-300);border-radius: 0.25rem;}
.wr_link:hover, .wr_link:hover + a{border:1px solid var(--blue-400);color:var(--blue-400)}
</style>