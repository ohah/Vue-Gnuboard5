<template>
  <Panel class="p-shadow-2" :header="po_subject">
    <template #icons>
      <button class="p-panel-header-icon p-link p-mr-2" @click="toggle">
        <span class="pi pi-plus"></span>
      </button>
    </template>
    <div v-for="(item, i) in polist" :key="i">
      <div v-if="item" class="p-py-2 p-d-flex p-ai-center">
        <RadioButton :id="`poll_${i}`" :value="i" v-model="selectPoll"/>
        <label class="p-ml-2" :for="`poll_${i}`">{{item}}</label>
      </div>
    </div>
    <Button label="투표하기" class="p-w-full" />
  </Panel>
</template>

<script lang="ts">
import { defineComponent, onMounted, reactive, ref, toRefs } from 'vue'
import { getAPI, latest, poll, pollInitial, RdURL } from '../type'
import SideView from '@/components/bbs/Sideview.vue'
export default defineComponent({
  name:'poll',
  components : {
    SideView
  },
  props:{
    po_id:String,
  },
  setup (props) {
    const po = reactive<poll>(pollInitial);
    const pocnt = ref<Array<number>>([]);
    const selectPoll = ref<number>();
    const polist = ref<Array<string>>([]);
    onMounted(async () =>{
      const res = await getAPI(`/poll`);
      console.log(res.data);
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
    })
    return {
      selectPoll,
      pocnt,
      polist,
      ...toRefs(po),
      RdURL
    };
  }
})
</script>

<style scoped>

</style>