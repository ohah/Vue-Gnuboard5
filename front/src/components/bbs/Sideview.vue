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
    <Dialog :header="`${data.mb_nick}님께 메일보내기`" v-model:visible="isEmail" :modal="true">
      <form @submit.prevent="email_submit" class="p-d-block p-py-4">
        <div v-if="member.mb_level === 1">
          <span class="p-float-label p-w-full p-my-4">
            <InputText class="p-w-full" id="fnick" required type="text" v-model="fnick" />
            <label for="fnick">이름</label>
          </span>
          <span class="p-float-label p-w-full p-my-4">
            <InputText class="p-w-full"  id="fmail" required type="text" v-model="fmail" />
            <label for="fmail">E-mail</label>
          </span>
        </div>
        <span class="p-float-label p-w-full p-my-4">
          <InputText class="p-w-full" id="username" type="text" v-model="subject" />
          <label for="username">제목</label>
        </span>
        <div class="p-d-flex">
          <div v-for="type of types" :key="type.key" class="p-field-radiobutton p-px-2">
            <RadioButton :id="type.key" name="type" :value="type.value" v-model="typeValue" />
            <label :for="type.key">{{type.name}}</label>
          </div>
        </div>
        <Textarea v-model="content" rows="5" cols="30" class="p-w-full" />
        <FileUpload class="p-my-3" name="file[]" :fileLimit="2" :showCancelButton="false" :showUploadButton="false" />
        <Captcha :captcha="captcha_html"/>
      </form>
      <template #footer>
        <Button label="닫기" icon="pi pi-check" @click="isEmail = false"/>
        <Button label="보내기" icon="pi pi-check" @click="isEmail = false"/>
      </template>
    </Dialog>
  </div>
  <div v-else v-html="data">
  </div>
</template>

<script lang="ts">
import { computed, defineComponent, PropType, reactive, ref, toRefs } from 'vue'
import { getAPI, getPostAPI, profile, profileInitial, queryString, RdURL, Sideview } from '@/type'
import { useRouter } from 'vue-router';
import { useStore } from 'vuex';
import { RootState } from '@/store';
import Captcha from '@/components/Captcha.vue';

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
  components : {
    Captcha,
  },
  setup (props) {
    const collapse = ref<HTMLElement>();
    const profile = reactive<profile>(profileInitial);
    const isModal = ref<boolean>(false);
    const isEmail = ref<boolean>(false);
    const toggle = (e:Event) => {
      (collapse.value as any).toggle(e);
    };
    const email_submit = async (e:Event) => {
      e.preventDefault();
      const f = new FormData(e.target as any);
      const res = await getPostAPI('/formmail_send');
      if(res.data.msg) {
        alert(res.data.msg)
      }else {
        isEmail.value = false;
      }
    }
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
    const types = ref([
      {name: 'TEXT', key: 'type_text', value: 0}, 
      {name: 'HTML', key: 'type_html', value : 1}, 
      {name: 'TEXT+HTML', key: 'type_both', value : 2}, 
    ]);
    const emailData = reactive({
      fnick : '',
      fmail : '',
      subject : '',
      typeValue: '0',
      content : '',
      captcha_html: ({} as any),
    });
    const get_email = async (email:string) => {
      const res = await getAPI(`/formmail/${email}/${props.data?.mb_id}`);
      emailData.captcha_html = res.data.captcha_html;
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
      }else if(sideview.value.title === '이메일') {
        await get_email(queryString(sideview.value.url).email);
        isEmail.value = true;
      }else {
        router.push(RdURL(sideview.value.url));
      }
    }
    return {
      ...toRefs(emailData),
      types,
      toggle,
      move,
      isEmail,
      isModal,
      email_submit,
      collapse,
      member,
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