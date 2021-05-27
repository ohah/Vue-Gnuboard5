<template>
  <div>
    <form @submit.prevent="Register">
      <Panel :toggleable="true">
        <template #header>
          <div>회원가입약관</div>
        </template>
        <div>
          {{cf_stipulation}}
        </div>
      </Panel>
      <Panel :toggleable="true" class="p-mt-4">
        <template #header>
          <div>개인정보처리방침안내</div>
        </template>
        <div>
          {{cf_privacy}}
        </div>
      </Panel>
      <Panel class="p-mt-4">
        <template #header>
          <div>개인정보 입력</div>
        </template>
        <div class="p-field p-grid">
          <div class="p-d-flex p-ai-center p-w-full">
            <label for="email" class="p-col-fixed" style="width:100px">이메일</label>
            <div class="p-col ">
              <InputText id="email" class="p-w-full" type="text" name="mb_email" :value="result.user_email" />
            </div>
          </div>
        </div>
      </Panel>
      <div class="p-d-flex p-jc-end p-my-3">
        <Button label="회원가입" class="p-mr-3" type="submit"/>
        <Button label="취소" class="p-button-secondary" type="button"/>
      </div>
    </form>
  </div>
</template>

<script lang="ts">
import { useRoute, useRouter } from 'vue-router'
import { computed, defineComponent, onMounted, reactive, ref } from 'vue'
import { getAPI, getPostAPI, user_profile, user_profileInitial} from '@/type';
import { RootState } from '@/store';
import { useStore } from 'vuex'
export default defineComponent({
  setup () {
    const route = useRoute();
    const router = useRouter();
    const cf_stipulation = ref<string>('');
    const cf_privacy = ref<string>('');
    const result = reactive<user_profile>(user_profileInitial);
    onMounted(async () => {
      console.log(route.params.provider);
      if(route.params.provider === 'kakao') {
        await Kakao();
      }
      if(route.params.provider === 'google') {
        console.log('구글 실행');
        await Google();
      }
      // console.log(route.params);
      if(!route.params.provider) {
        const res = await getAPI(`/social/token/${route.query.hauth}/?code=${route.query.code}`);
        try {
          result.user_id = res.data.user_profile.user_id;
          result.user_email = res.data.user_profile.user_email;
          result.user_name = res.data.user_profile.user_name;
          result.user_nick = res.data.user_profile.user_nick;
          // result.identifier = res.data.user_profile.identifier;
          // result.webSiteURL = res.data.user_profile.webSiteURL;
          // result.profileURL = res.data.user_profile.profileURL;
          // result.photoURL = res.data.user_profile.photoURL;
          // result.displayName = res.data.user_profile.displayName;
          // result.description = res.data.user_profile.description;
          // result.firstName = res.data.user_profile.firstName;
          // result.lastName = res.data.user_profile.lastName;
          // result.gender = res.data.user_profile.gender;
          // result.language = res.data.user_profile.language;
          // result.age = res.data.user_profile.age;
          // result.birthDay = res.data.user_profile.birthDay;
          // result.birthMonth = res.data.user_profile.birthMonth;
          // result.birthYear = res.data.user_profile.birthYear;
          // result.email = res.data.user_profile.email;
          // result.emailVerified = res.data.user_profile.emailVerified;
          // result.phone = res.data.user_profile.phone;
          // result.address = res.data.user_profile.address;
          // result.country = res.data.user_profile.country;
          // result.region = res.data.user_profile.region;
          // result.city = res.data.user_profile.city;
          // result.zip = res.data.user_profile.zip;
          // result.job_title = res.data.user_profile.job_title;
          // result.organization_name = res.data.user_profile.organization_name;
          // result.sid = res.data.user_profile.sid;
          cf_stipulation.value = res.data.cf_stipulation;
          cf_privacy.value = res.data.cf_privacy;
        }catch (e) {
          // console.log(e);
          if(res.data.mb_nick) {
            state.member = res.data;
            router.push('/');
          }
        }
        
      }
    });
    const Kakao = async () => {
      const res = await getAPI(`/social/popup/kakao`);
      if(res.data.url) {
        location.href = res.data.url;
      }
    }
    const Google = async () => {
      const res = await getAPI(`/social/popup/google`);
      console.log(res.data);
      if(res.data.url) {
        location.href = res.data.url;
      }
    }
    const eamilValue = ref<string>();
    const {state} = useStore<RootState>();
    const member = computed(()=>state.member);
    const Register = async (e:Event) => {
      e.preventDefault();
      console.log(e.target);
      const f = new FormData((e.target as HTMLFormElement));
      f.append('w', '');
      f.append('mb_name', `${result.user_name ? result.user_name : result.user_nick}`);
      f.append('provider', `${route.query.hauth}`);
      f.append('action', 'register');
      f.append('mb_id', `${route.query.hauth}${result.user_id}`);
      f.append('mb_nick_default', `${result.user_nick}`);
      f.append('mb_nick', `${result.user_nick}`);
      const res = await getPostAPI(`/social/update`, f);
      console.log(res);
      if(res.data.msg) {
        alert(res.data.msg);
      } else if(res.data.mb_nick) {
        
      }      
    }
    return {
      result,
      Kakao,
      eamilValue,
      cf_stipulation,
      cf_privacy,
      Register,
    }
  }
})
</script>

<style scoped>

</style>