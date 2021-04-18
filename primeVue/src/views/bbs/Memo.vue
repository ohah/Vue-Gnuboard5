<template>
  <Card>
    <template #title>
      {{member.mb_nick}}님의 쪽지함
    </template>
    <template #content>
      <TabView @tab-change="TabChange" :activeIndex="activeIndex">
        <TabPanel header="받은쪽지">
          <DataView :value="list" v-if="isLoading === true">
            <template #list="row">
              <ul class="p-d-flex p-jc-between p-ai-center p-w-full p-p-3">
                <li>
                  <p class="p-d-flex p-ai-center"> <SideView :data="row.data.name" class="p-mr-2" /> <span class="text-gray-500"> {{row.data.me_send_datetime}} </span> </p>
                  <p class="p-d-flex p-ai-center"> {{row.data.me_memo}} </p>
                </li>
                <li> <Button @click="memo_delete(row.data.me_id, 'recv', row.data.del_token)" icon="pi pi-trash" /> </li>
              </ul>
            </template>
            <template #empty>
              <div class="p-d-flex p-p-3">받은 쪽지 내역이 없습니다.</div>
            </template>
          </DataView>
          <MemoSkeleton v-if="isLoading === false" />
        </TabPanel>
        <TabPanel header="보낸쪽지">
          <DataView :value="list" v-if="isLoading === true">
            <template #list="row">
              <ul class="p-d-flex p-jc-between p-ai-center p-w-full p-p-3">
                <li>
                  <p class="p-d-flex p-ai-center"> <SideView :data="row.data.name" class="p-mr-2" /> <span class="text-gray-500"> {{row.data.me_send_datetime}} </span> </p>
                  <p class="p-d-flex p-ai-center"> {{row.data.me_memo}} </p>
                </li>
                <li> <Button @click="memo_delete(row.data.me_id, 'send', row.data.del_token)" icon="pi pi-trash" /> </li>
              </ul>
            </template>
            <template #empty>
              <div class="p-d-flex p-p-3">받은 쪽지 내역이 없습니다.</div>
            </template>
          </DataView>
          <MemoSkeleton v-if="isLoading === false" />
        </TabPanel>
        <TabPanel header="쪽지쓰기">
          <form v-on:submit.prevent="fmemoform_submit">
            <Chips v-model="memo_data.me_recv_mb_id" separator="," class="p-w-full p-my-2"/>
            <p class="text-gray-500">여러 회원에게 보낼때는 컴마(,)로 구분하세요. </p>
            <p class="text-gray-500">쪽지 보낼때 회원당 500점의 포인트를 차감합니다. </p>
            <Textarea v-model="content" rows="5" cols="30" class="p-w-full p-my-2" />
            <Captcha v-model="memo_data.captcha_key" :captcha="captcha_html"/>
            <Button type="submit" label="보내기" />
          </form>
        </TabPanel>
      </TabView>
    </template>
  </Card>
  <ConfirmDialog></ConfirmDialog>
  <Paginator style="background:transparent" @page="pageEvent($event)" :rows="page_rows" :first="(page * 10)" :totalRecords="total_count" ></Paginator>
</template>

<script lang="ts">
import { defineComponent, toRefs, reactive, onMounted, ref, computed }  from 'vue';
import { useRoute, useRouter } from 'vue-router'
import { getAPI, qstr, qstrInitial, Memo, MemoInitial, getPostAPI, MemoForm } from '@/type'
import { useStore } from 'vuex';
import SideView from '@/components/bbs/Sideview.vue'
import MemoSkeleton from '@/components/bbs/MemoSkeleton.vue'
import { useConfirm } from "primevue/useconfirm";
import ConfirmDialog from 'primevue/confirmdialog';
import Captcha from '@/components/Captcha.vue'
import { useHead } from '@vueuse/head'
interface tabEvent extends MouseEvent{
  index:number
}
export default defineComponent({
  name : 'Memo',
  components : {
    SideView,
    ConfirmDialog,
    MemoSkeleton,
    Captcha
  },
  setup() {
    const route = useRoute();
    const activeIndex = ref<number>(route.query.me_recv_mb_id ? 2 : 0);
    const confirm = useConfirm();
    const router = useRouter();
    const store = useStore();
    const qstr = reactive<qstr>({
      ...qstrInitial,
      ...route.query
    });
    const member = computed(()=>store.state.member)
    const Memo = reactive<Memo>(MemoInitial);
    const Memo_form = reactive<MemoForm>({
      content:'',
      captcha_html:''
    })
    const refresh_captcha = async () => {
      const res = await getPostAPI(`/captcha/K`, {refresh : 1});
      if(res.data.g5_captcha_url) {
        const captcha_html = {
          g5_captcha_url : res.data.g5_captcha_url
        }
        Memo_form.captcha_html = captcha_html;
      }
    };
    const isLoading = ref<boolean>(false);
    const pageEvent = (page:any) => {
      if(page.toString() != qstr.page) {
        router.push({path : route.path, query : {page: page.page + 1}});
      }
    }
    const fmemoform_submit = async () => {
      const f = new FormData();
      f.append('me_memo', Memo_form.content);
      f.append('me_recv_mb_id', memo_data.me_recv_mb_id.join(','));
      f.append('captcha_key', memo_data.captcha_key);
      const res = await getPostAPI(`/member/memo_form_update`, f);
      if(res.data.msg) {
        confirm.require({
          message: [
            res.data.msg,
          ].join('\r\n'),
          header: '안내',
          icon: 'pi pi-info-circle',
          acceptClass: 'p-button-danger',
          accept: async () => {
            //callback to execute when user confirms the action
          },
          reject: () => {
            //callback to execute when user rejects the action
          }
        });
      }
      try {
      } catch (error) {
        
      }
    }
    useHead({
      title: computed(()=>`${member.value.mb_nick ? member.value.mb_nick : ''}님의 쪽지함`),
    })
    const getMemo = async (kind = 'recv') => {
      isLoading.value = false;
      const res = await getAPI(`/member/memo?kind=${kind}`);
      Memo.list = res.data.list;
      Memo.total_count = res.total_count;
      Memo.page = res.data.page;
      Memo.page_rows = res.data.page_rows;
      isLoading.value = true;
    }
    const getMemoForm = async () => {
      const res = await getAPI(`/member/memo_form`);
      Memo_form.captcha_html = res.data.captcha_html;
      Memo_form.content = res.data.content;
      console.log(res);
    }
    const TabChange = async (e:tabEvent) => {
      if(e.index === 0) await getMemo();
      else if(e.index === 1) await getMemo('send');
      else if(e.index === 2) await getMemoForm();
    }
    const memo_delete = async (me_id:number, kind = 'recv', del_token:string) => {
      confirm.require({
        message: [
          '정말 삭제하시겠습니까?'
        ].join('\r\n'),
        header: '안내',
        icon: 'pi pi-info-circle',
        acceptClass: 'p-button-danger',
        accept: async () => {
          const res = await getPostAPI(`/member/memo_delete/${me_id}?kind=${kind}&token=${del_token}`);
          Memo.list = res.data.list;
          Memo.total_count = res.total_count;
          Memo.page = res.data.page;
          Memo.page_rows = res.data.page_rows;
          isLoading.value = true;
        },
        reject: () => {
          //callback to execute when user rejects the action
        }
      });
    }
    onMounted(async ()=>{
      getMemo();
    });
    const memo_data = reactive({
      me_recv_mb_id:route.query.me_recv_mb_id ? [route.query.me_recv_mb_id] : [],
      captcha_key:'',
    })
    return {
      isLoading,
      qstr,
      route,
      member,
      pageEvent,
      TabChange,
      refresh_captcha,
      activeIndex,
      fmemoform_submit,
      ...toRefs(Memo_form),
      ...toRefs(Memo),
      memo_data,
      memo_delete,
    };
  },
})
</script>

<style scoped>
</style>