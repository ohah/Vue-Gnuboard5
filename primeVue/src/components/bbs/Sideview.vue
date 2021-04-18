<template>
  <div v-if="data.mb_nick">
    <Button @click="toggle" :label="data.mb_nick" class="p-button-text p-button-sm p-button-link p-button-secondary" style="padding:0"/>
    <OverlayPanel ref="collapse" :dismissable="true">
      <Listbox :options="data.list" optionLabel="label" @change="move" style="border:0;min-width:10rem">
        <template #option="row">
          <div class="p-d-flex p-jc-between" @click="move(row.option.url)">
            <!-- <router-link :to="RdURL(row.option.url)"> {{row.option.title}} </router-link> -->
            {{row.option.title}}
          </div>
        </template>
      </Listbox>
    </OverlayPanel>
    <Dialog :header="`${data.mb_nick}님의 자기 소개`" v-model:visible="isModal" :modal="true">
      <Splitter style="height:100px;width:450px" layout="vertical" gutterSize="0" class="p-p-2">
        <SplitterPanel :size="50" :minSize="10" gutterSize="0">
          <Splitter style="height:50" gutterSize="0" class="p-d-flex p-ai-center">
            <SplitterPanel :size="50" :minSize="10">
              <strong class="p-bold"> 회원권한</strong><span>{{mb_level}}</span>
            </SplitterPanel>
            <SplitterPanel :size="50" :minSize="10">
              <strong class="p-bold"> 포인트</strong><span>{{mb_point}}</span>
            </SplitterPanel>
          </Splitter>
        </SplitterPanel>
        <SplitterPanel  :size="50" :minSize="20">
          <Splitter style="height:50" gutterSize="0" class="p-d-flex p-ai-center">
            <SplitterPanel :size="50" :minSize="10">
              <strong class="p-bold"> 회원가입일</strong><span>{{mb_regsiter_join}}</span>
            </SplitterPanel>
            <SplitterPanel :size="50" :minSize="10">
              <strong class="p-bold"> 최종접속일</strong><span>{{mb_last_connect}}</span>
            </SplitterPanel>
          </Splitter>
        </SplitterPanel>
      </Splitter>
      <Fieldset legend="인사말" class="p-my-3">
        {{mb_profile}}
      </Fieldset>
      <template #footer>
        <Button label="닫기" icon="pi pi-check" @click="isModal = false"/>
      </template>
    </Dialog>
  </div>
  <div v-else v-html="data">
  </div>
</template>

<script lang="ts">
import { computed, defineComponent, PropType, reactive, ref, toRefs } from 'vue'
import { getAPI, profile, profileInitial, RdURL, Sideview } from '@/type'
import { useRouter } from 'vue-router';
import { useStore } from 'vuex';
import { RootState } from '@/store';

interface SideViewEvent {
  originalEvent:MouseEvent,
  value:{
    title:string,
    url:string,
  }
}
export default defineComponent({
  props : {
    data : {
      type:Object as PropType<Sideview>,
    },
  },
  setup (props) {
    const collapse = ref<HTMLElement>();
    const profile = reactive<profile>(profileInitial);
    const isModal = ref<boolean>(false);
    const toggle = (e:Event) => {
      (collapse.value as any).toggle(e);
    };
    const store = useStore<RootState>();
    const member = computed(()=>store.state.member)
    const router = useRouter();
    const get_profile = async (mb_id:string) => {
      const res = await getAPI(`/member/${mb_id}/profile`);
      profile.mb_sideview = res.data.mb_sideview;
      profile.mb_homepage = res.data.mb_homepage;
      profile.mb_reg_after = res.data.mb_reg_after;
      profile.mb_regsiter_join = res.data.mb_regsiter_join;
      profile.mb_last_connect = res.data.mb_last_connect;
      profile.mb_profile = res.data.mb_profile;
      profile.mb_point = res.data.mb_point;
      profile.mb_level = res.data.mb_level;
    }
    const move = async (sideview:SideViewEvent) => {
      console.log(sideview.value.title, sideview.value.url);
      if(sideview.value.title === '자기소개') {
        if(!member.value.mb_id) {
          alert('회원만 조회 가능합니다');
        }else {
          await get_profile(`${props.data?.mb_id}`);
          isModal.value = true;
        }
      }else {
        router.push(RdURL(sideview.value.url));
      }
    }
    return {
      toggle,
      move,
      isModal,
      collapse,
      RdURL,
      ...toRefs(profile),
      ...toRefs(props)
    }
  },
  methods : {
    
  }
})
</script>

<style scoped>

</style>