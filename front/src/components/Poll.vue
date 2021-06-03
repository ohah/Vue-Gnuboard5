<template>
  <Panel class="p-shadow-2" :header="po_subject">
    <template #icons>
      <button class="p-panel-header-icon p-link p-mr-2" @click="poll_result">
        <span class="pi pi-plus"></span>
      </button>
    </template>
    <form @submit.prevent="poll_submit">
      <div v-for="(item, i) in polist" :key="i">
        <div v-if="item" class="p-py-2 p-d-flex p-ai-center">
          <RadioButton :id="`poll_${i}`" :value="`${i+1}`" v-model="selectPoll"/>
          <label class="p-ml-2" :for="`poll_${i+1}`">{{item}}</label>
        </div>
      </div>
      <Button label="투표하기" type="submit" class="p-w-full" />
      <Dialog header="설문조사 결과" v-model:visible="is_po_result" :modal="true">
        <Panel class="" :header="`${po_result.po.po_subject} 결과`">
          <div v-for="(row, i) in po_result.list" :key="i">
            <p class="p-my-1">{{row.num}} {{row.content}}</p>
            <div class="p-grid p-jc-end">
              <div class="p-col">
                <ProgressBar :value="row.rate">
                  {{row.rate}}%
                </ProgressBar>
              </div>
              <div class="p-col-fixed" style="width:80px">
                <p class="p-d-flex p-jc-end">{{row.cnt}}표</p>
                <p class="p-d-flex p-jc-end">{{row.rate}}%</p>
              </div>
            </div>
          </div>
        </Panel>
        <section class="p-border-b p-my-3" v-for="(row, i) in po_result.list2" :key="i">
          <header>
            <h2 class="p-bold p-py-1">{{row.pc_name}}
              <span class="p-normal p-mx-2"><i class="pi pi-eye"></i> {{row.datetime}}</span>
              <span class="p-sr-only">님의 의견</span>
              <span v-if="row.del"> <i class="pi pi-trash"></i> </span>
            </h2>
            <p class="p-my-1">
              {{row.idea}} 
            </p>
          </header>
        </section>
        <form @submit.prevent="poll_cmt_update" ref="etc_form">
          <div class="p-mt-3 p-border">
            <div class="p-border-b p-px-3 p-py-2">
              <Chip label="기타의견" />
              <span class="p-ml-2"> {{po_result.po.po_etc}} </span>
            </div>
            <Textarea required rows="5" cols="30" class="p-w-full" style="border:0" name="pc_idea"/>
          </div>
          <div class="p-inputtext-sm p-my-3">
            <InputText required class="p-w-full" placeholder="이름" name="pc_name"/>
          </div>
          <div class="p-d-flex p-my-2">
            <Captcha :captcha="po_result.captcha_html"/>
          </div>
          <Button label="의견남기기" class="p-w-full" type="submit" />
        </form>
        <h2 class="p-my-3 p-bold"> 다른 투표 결과 </h2>
        <div class="p-border p-p-3">
          <div class="p-d-flex p-jc-between p-ai-center" v-for="(row, i) in po_result.list3" :key="i">
            <span> {{row.subject}} </span>
            <span> <i class="pi pi-eye"></i> {{row.date}} </span>
          </div>
        </div>
      </Dialog>
    </form>
  </Panel>
</template>

<script lang="ts">
import { defineComponent, onMounted, reactive, ref, toRefs } from 'vue'
import { getAPI, getPostAPI, poll, pollInitial, poll_result, RdURL } from '../type'
import SideView from '@/components/bbs/Sideview.vue'
import Captcha from '@/components/Captcha.vue'

export default defineComponent({
  name:'poll',
  components : {
    SideView,
    Captcha,
  },
  props:{
    po_id:String,
  },
  setup (props) {
    const po = reactive<poll>(pollInitial);
    const po_result = ref<poll_result>();
    const is_po_result = ref<boolean>(false);
    const pocnt = ref<Array<number>>([]);
    const selectPoll = ref<number>();
    const polist = ref<Array<string>>([]);
    const etc_form = ref<HTMLFormElement>();
    const poll_submit = async () => {
      const f = new FormData();
      f.append('gb_poll', `${selectPoll.value}`);
      f.append('po_id', `${po.po_id}`);
      const res = await getPostAPI('/poll_update', f);
      console.log(res);
      if(res.data.msg) {
        alert(res.data.msg);
      }
    }
    const poll_cmt_update = async (e:Event) => {
      e.preventDefault();
      const f = new FormData(etc_form.value);
      f.append('po_id', `${po.po_id}`);
      f.append('w', ``);
      const res = await getPostAPI('/poll_etc_update', f);
      console.log(res);
      if(res.data.msg) {
        alert(res.data.msg);
      }else {
        if(etc_form.value) {
          const inputs = etc_form.value.querySelectorAll('input, textarea');
          inputs.forEach((input:any)=>{
            input.value = "";
          })
        }
        is_po_result.value = true;
        po_result.value = res.data;
      }
    }
    const poll_result = async () => {
      const res = await getAPI(`/poll_result?po_id=${po.po_id}`);
      console.log(res);
      if(res.data.msg) {
        alert(res.data.msg);
      }else {
        is_po_result.value = true;
        po_result.value = res.data;
      }
    }
    const get = async () => {
      const res = await getAPI(`/poll`);
      po.mb_ids = res.data.po.mb_ids;
      po.po_cnt1 = res.data.po.po_cnt1;
      po.po_cnt2 = res.data.po.po_cnt2;
      po.po_cnt3 = res.data.po.po_cnt3;
      po.po_cnt4 = res.data.po.po_cnt4;
      po.po_cnt5 = res.data.po.po_cnt5;
      po.po_cnt6 = res.data.po.po_cnt6;
      po.po_cnt7 = res.data.po.po_cnt7;
      po.po_cnt8 = res.data.po.po_cnt8;
      po.po_cnt9 = res.data.po.po_cnt9;
      po.po_date = res.data.po.po_date;

      pocnt.value[0] = res.data.po.po_cnt1;
      pocnt.value[1] = res.data.po.po_cnt2;
      pocnt.value[2] = res.data.po.po_cnt3;
      pocnt.value[3] = res.data.po.po_cnt4;
      pocnt.value[4] = res.data.po.po_cnt5;
      pocnt.value[5] = res.data.po.po_cnt6;
      pocnt.value[6] = res.data.po.po_cnt7;
      pocnt.value[7] = res.data.po.po_cnt8;
      pocnt.value[8] = res.data.po.po_cnt9;

      po.po_etc = res.data.po.po_etc;
      po.po_id = res.data.po.po_id;
      po.po_ips = res.data.po.po_ips;
      po.po_level = res.data.po.po_level;
      po.po_point = res.data.po.po_point;
      po.po_poll1 = res.data.po.po_poll1;
      po.po_poll2 = res.data.po.po_poll2;
      po.po_poll3 = res.data.po.po_poll3;
      po.po_poll4 = res.data.po.po_poll4;
      po.po_poll5 = res.data.po.po_poll5;
      po.po_poll6 = res.data.po.po_poll6;
      po.po_poll7 = res.data.po.po_poll7;
      po.po_poll8 = res.data.po.po_poll8;
      po.po_poll9 = res.data.po.po_poll9;

      polist.value[0] = res.data.po.po_poll1;
      polist.value[1] = res.data.po.po_poll2;
      polist.value[2] = res.data.po.po_poll3;
      polist.value[3] = res.data.po.po_poll4;
      polist.value[4] = res.data.po.po_poll5;
      polist.value[5] = res.data.po.po_poll6;
      polist.value[6] = res.data.po.po_poll7;
      polist.value[7] = res.data.po.po_poll8;
      polist.value[8] = res.data.po.po_poll9;
      
      po.po_subject = res.data.po.po_subject;
    }
    onMounted(async () =>{
      get();
    })
    return {
      selectPoll,
      pocnt,
      polist,
      ...toRefs(po),
      RdURL,
      po_result,
      is_po_result,
      poll_cmt_update,
      poll_result,
      etc_form,
      poll_submit
    };
  }
})
</script>

<style scoped>

</style>