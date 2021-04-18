<template>
  <Toast position="bottom-right" />
  <form v-on:submit.prevent="fregisterform_submit">
    <Panel class="p-mt-4">
      <template #header>
        사이트 이용정보 입력
      </template>
      <div class="p-fluid">
        <div class="p-field">
          <label for="mb_id">아이디</label>
          <InputText id="mb_id" type="text" placeholder="영문자, 숫자, _만 입력가능. 최소 3자이상 입력하세요." v-model="mb_id" :readonly="member.mb_id ? true : false"/>
        </div>
        <div class="p-field">
          <label for="mb_password">비밀번호</label>
          <Password id="mb_password" v-model="mb_password" />
        </div>
        <div class="p-field">
          <label for="mb_password">비밀번호 확인</label>
          <Password id="mb_password" v-model="mb_password_re" />
        </div>
      </div>
    </Panel>
    <Panel class="p-mt-4" v-if="config">
      <template #header>
        개인정보 입력
      </template>
      <div class="p-fluid">
        <div class="p-field">
          <label for="mb_name">이름</label>
          <InputText id="mb_name" v-model="mb_name" :readonly="member.mb_name ? true : false" />
        </div>
      </div>
      <div class="p-fluid">
        <div class="p-field">
          <label for="mb_nick">닉네임</label>
          <InputText id="mb_nick" v-model="mb_nick" :readonly="member.mb_nick ? true : false" />
        </div>
      </div>
      <div class="p-fluid">
        <div class="p-field">
          <label for="mb_email">이메일</label>
          <InputText id="mb_email" v-model="mb_email"/>
        </div>
      </div>
      <div class="p-fluid" v-if="config.cf_use_homepage">
        <div class="p-field">
          <label for="mb_homepage">홈페이지</label>
          <InputText id="mb_homepage" v-model="mb_homepage"/>
        </div>
      </div>
      <div class="p-fluid" v-if="config.cf_use_tel">
        <div class="p-field">
          <label for="mb_tel">전화번호</label>
          <InputText id="mb_tel" v-model="mb_tel"/>
        </div>
      </div>
      <div class="p-fluid" v-if="config.cf_use_hp">
        <div class="p-field">
          <label for="mb_hp">휴대폰번호</label>
          <InputText id="mb_hp" v-model="mb_hp" />
        </div>
      </div>
      <div class="p-fluid" v-if="config.cf_use_addr">
        <div class="p-field">
          <label for="mb_zip">주소</label>
          <InputText class="p-my-1" id="mb_zip" @click="DaumPostCode = !DaumPostCode" v-model="mb_zip" placeholder="우편번호" readonly/>
          <InputText class="p-my-1" id="mb_addr1" @click="DaumPostCode = !DaumPostCode" v-model="mb_addr1" placeholder="기본주소" readonly />
          <VueDaumPostcode v-if="DaumPostCode" @complete="DuamPostCodeResult" />
          <InputText class="p-my-1" id="mb_addr2" v-model="mb_addr2" placeholder="상세주소"/>
          <InputText class="p-my-1" id="mb_addr3" v-model="mb_addr3" placeholder="참고항목"/>
        </div>
      </div>
    </Panel>
    <Panel class="p-mt-4" v-if="config">
      <template #header>
        기타 개인설정
      </template>
      <div class="p-fluid" v-if="config.cf_use_signature">
        <div class="p-field">
          <label for="mb_name">서명</label>
          <Textarea v-model="mb_signature" rows="5" cols="30" />
        </div>
      </div>
      <div class="p-fluid" v-if="config.cf_use_profile">
        <div class="p-field">
          <label for="mb_profile">자기소개</label>
          <InputText id="mb_profile" v-model="mb_profile"/>
        </div>
      </div>
      <div class="p-fluid p-d-flex">
        <div class="p-field p-d-flex p-ai-center">
          <Checkbox id="mb_mailling" class="p-ai-center p-d-flex" v-model="mb_mailling" value="1" />
          <label class="p-ai-center p-d-flex p-ml-2 p-mb-0" for="mb_mailling">정보메일을 받겠습니다.</label>
        </div>
      </div>
      <div class="p-fluid p-d-flex" v-if="config.cf_use_hp">
        <div class="p-field p-d-flex p-ai-center">
          <Checkbox id="mb_sms" class="p-ai-center p-d-flex" v-model="mb_sms" value="1" />
          <label class="p-ai-center p-d-flex p-ml-2 p-mb-0" for="mb_sms">휴대폰 문자메세지를 받겠습니다.</label>
        </div>
      </div>
      <div class="p-fluid p-d-flex">
        <div class="p-field p-d-flex p-ai-center">
          <Checkbox id="mb_open" class="p-ai-center p-d-flex" v-model="mb_open" value="1" />
          <label class="p-ai-center p-d-flex p-ml-2 p-mb-0" for="mb_open">다른분들이 나의 정보를 볼 수 있도록 합니다.</label>
        </div>
      </div>
      <div class="p-fluid" v-if="config.cf_use_recommend === 1 && !member.mb_id">
        <div class="p-field">
          <label for="mb_recommend">추천인</label>
          <InputText id="mb_recommend" v-model="mb_recommend"/>
        </div>
      </div>
      <div ref="captcha" class="p-d-flex p-my-2">
        <div v-if="captcha_html.g5_captcha_url" class="p-d-flex p-ai-center">
          <img v-if="captcha_html.g5_captcha_url" :src="captcha_html.g5_captcha_url" />
          <div style="flex-direction: column;display: flex;" class="p-mx-2">
            <Button class="p-my-1" icon="pi pi-play" />
            <Button class="p-my-1" icon="pi pi-undo" @click="refresh_captcha" />
          </div>
          <InputText style="height:40px;" class="p-mx-2" v-model="captcha_key" name="captcha_key"/>
        </div>
        <div v-else v-html="captcha_html"> </div>
      </div>
    </Panel>
    <div class="p-d-flex p-w-full p-jc-end">
      <Button class="p-my-1" type="submit" label="확인"/>
    </div>
  </form>
</template>

<script lang="ts">
import { RootState } from '@/store';
import { useStore } from 'vuex';
import { Config, getPostAPI } from '@/type'
import { computed, defineComponent, onMounted, reactive, ref, toRefs } from 'vue'
import { useToast } from "primevue/usetoast";
import { useRoute, useRouter } from 'vue-router';
import { useHead } from '@vueuse/head'
import { VueDaumPostcode, VueDaumPostcodeCompleteResult} from 'vue-daum-postcode'

export default defineComponent({
  components: {
    VueDaumPostcode,
  },
  setup () {
    const config = ref<Config>();
    const {state, dispatch, commit} = useStore<RootState>();
    const router = useRouter();
    const route = useRoute();
    const captcha_html = ref();
    const member = computed(()=>state.member);
    const toast = useToast();
    const join = reactive({
      mb_id : '',
      mb_password : '',
      mb_level : 1,
      mb_password_re : '',
      mb_1 : '',
      mb_2 : '',
      mb_3 : '',
      mb_4 : '',
      mb_5 : '',
      mb_6 : '',
      mb_7 : '',
      mb_8 : '',
      mb_9 : '',
      mb_10 : '',
      mb_addr1 : '',
      mb_addr2 : '',
      mb_addr3 : '',
      mb_addr_jibeon : '',
      mb_adult : '',
      mb_birth : '',
      mb_certify : '',
      mb_datetime : '',
      mb_dupinfo : '',
      mb_email : '',
      mb_email_certify : '',
      mb_email_certify2 : '',
      mb_homepage : '',
      mb_hp : '',
      mb_intercept_date : '',
      mb_ip : '',
      mb_leave_date : '',
      mb_login_ip : '',
      mb_lost_certify : '',
      mb_mailling : 0,
      mb_memo : '',
      mb_memo_call : '',
      mb_memo_cnt : 0,
      mb_name : '',
      mb_nick : '',
      mb_nick_date : '',
      mb_no : 0,
      mb_open : 0,
      mb_point : 0,
      mb_profile : '',
      mb_recommend : '',
      mb_scrap_cnt : 0,
      mb_sex : '',
      mb_signature : '',
      mb_sms : 0,
      mb_tel : '',
      mb_today_login : '',
      mb_zip : '',
      mb_zip1 : '',
      mb_zip2 : '',
      captcha_key : '',
    });
    useHead({
      title: computed(()=>member.value.mb_nick ? `${member.value.mb_nick}님의 정보수정` : `회원가입`),
    })
    const fregisterform_submit = async () => {
      const f = new FormData();
      if(route.params.password) {
        f.append('w', '');
      }else {
        f.append('w', 'u');
      }
      f.append('mb_id', join.mb_id);
      f.append('mb_password', join.mb_password);
      f.append('mb_password_re', join.mb_password_re);
      f.append('mb_name', join.mb_name);
      f.append('mb_nick', join.mb_nick);
      f.append('mb_email', join.mb_email);
      f.append('mb_sex', join.mb_sex);
      f.append('mb_birth', join.mb_birth);
      f.append('mb_homepage', join.mb_homepage);
      f.append('mb_tel', join.mb_tel);
      f.append('mb_hp', join.mb_hp);
      f.append('mb_zip', join.mb_zip);
      f.append('mb_addr1', join.mb_addr1);
      f.append('mb_addr2', join.mb_addr2);
      f.append('mb_addr3', join.mb_addr3);
      f.append('mb_addr_jibeon', join.mb_addr_jibeon);
      f.append('mb_signature', join.mb_signature);
      f.append('mb_profile', join.mb_profile);
      f.append('mb_recommend', join.mb_recommend);
      f.append('mb_mailling', `${join.mb_mailling}`);
      f.append('mb_sms', `${join.mb_sms}`);
      f.append('mb_1', `${join.mb_1}`);
      f.append('mb_2', `${join.mb_2}`);
      f.append('mb_3', `${join.mb_3}`);
      f.append('mb_4', `${join.mb_4}`);
      f.append('mb_5', `${join.mb_5}`);
      f.append('mb_6', `${join.mb_6}`);
      f.append('mb_7', `${join.mb_7}`);
      f.append('mb_8', `${join.mb_8}`);
      f.append('mb_9', `${join.mb_9}`);
      f.append('mb_10', `${join.mb_10}`);
      f.append('captcha_key', `${join.captcha_key}`);
      const res = await getPostAPI(`/register_form_update`, f);
      console.log(res);
      try {
        if(res.data.msg) {
          toast.add({severity:'error', summary: '경고', detail: res.data.msg, life: 3000});  
        }
      } catch (error) {
        router.push({name : 'RegsiterComplete', params : {mb_id : join.mb_id, mb_password : join.mb_password}});
      }
    }
    onMounted(async ()=>{
      let w = '';
      if(route.params.password) {
        w = 'u';
      }
      // w = 'u';
      // route.params.mb_id = member.value.mb_id;
      const res = await getPostAPI(`/register_form`, {agree : "1", agree2 : "2", w : w, mb_id : route.params.mb_id, mb_password : route.params.password});
      if(res.data.msg) {
        alert(res.data.msg);
        router.push('/');
      }else {
        join.mb_id = member.value.mb_id;
        join.mb_name = member.value.mb_name ?  member.value.mb_name : '';
        join.mb_nick = member.value.mb_nick ? member.value.mb_nick : '';
        join.mb_email = member.value.mb_email ? member.value.mb_email : '';
        join.mb_sex = member.value.mb_sex ? member.value.mb_sex : '';
        join.mb_birth = member.value.mb_birth ? member.value.mb_birth : '';
        join.mb_homepage = member.value.mb_homepage ? member.value.mb_homepage : '';
        join.mb_tel = member.value.mb_tel ? member.value.mb_tel : '';
        join.mb_hp = member.value.mb_hp ? member.value.mb_hp : '';
        join.mb_addr1 = member.value.mb_addr1 ? member.value.mb_addr1 : '';
        join.mb_addr2 = member.value.mb_addr2 ? member.value.mb_addr2 : '';
        join.mb_addr3 = member.value.mb_addr3 ? member.value.mb_addr3 : '';
        join.mb_addr_jibeon = member.value.mb_addr_jibeon ? member.value.mb_addr_jibeon : '';
        join.mb_signature = member.value.mb_signature ? member.value.mb_signature : '';
        join.mb_profile = member.value.mb_profile ? member.value.mb_profile : '';
        join.mb_recommend = member.value.mb_recommend ? member.value.mb_recommend : '';
        join.mb_mailling = member.value.mb_mailling ? member.value.mb_mailling : 0;
        join.mb_sms = member.value.mb_sms ? member.value.mb_sms : 0;
        join.mb_1 = member.value.mb_1 ? member.value.mb_1 : '';
        join.mb_2 = member.value.mb_2 ? member.value.mb_2 : '';
        join.mb_3 = member.value.mb_3 ? member.value.mb_3 : '';
        join.mb_4 = member.value.mb_4 ? member.value.mb_4 : '';
        join.mb_5 = member.value.mb_5 ? member.value.mb_5 : '';
        join.mb_6 = member.value.mb_6 ? member.value.mb_6 : '';
        join.mb_7 = member.value.mb_7 ? member.value.mb_7 : '';
        join.mb_8 = member.value.mb_8 ? member.value.mb_8 : '';
        join.mb_9 = member.value.mb_9 ? member.value.mb_9 : '';
        join.mb_10 = member.value.mb_10 ? member.value.mb_10 : '';
      }
      config.value = res.data.config;
      captcha_html.value = res.data.captcha_html;
    });
    const DaumPostCode = ref<boolean>(false);
    return {
      config,
      captcha_html,
      ...toRefs(join),
      member,
      fregisterform_submit,
      DaumPostCode,
    }
  },
  methods : {
    async refresh_captcha() {
      const res = await getPostAPI(`/captcha/K`, {refresh : 1});
      if(res.data.g5_captcha_url) {
        console.log(res.data.captcha_html);
        const captcha_html = {
          g5_captcha_url : res.data.g5_captcha_url
        }
        this.captcha_html = captcha_html;
      }
    },
    DuamPostCodeResult(result:VueDaumPostcodeCompleteResult) {
      this.mb_zip = result.zonecode;
      this.mb_addr1 = result.address;
      this.DaumPostCode = false;
    }
  }
})
</script>

<style scoped>
.p-field label{font-weight:800}
</style>