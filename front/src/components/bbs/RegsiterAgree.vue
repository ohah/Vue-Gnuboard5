<template>
  <Toast position="bottom-right" />
  <Card class="p-shadow-2 p-mt-4">
    <template #title>
      회원가입 약관
    </template>
    <template v-slot:content>
      <div class="p-fluid">
        <div class="p-field">
          <div class="p-d-flex p-ai-center p-jc-between">
            <label for="agree">회원가입약관 및 개인정보처리방침안내의 내용에 동의하셔야 회원가입 하실 수 있습니다.</label>
            <Checkbox id="agree" value="agree" v-model="agree" @click="Allcheck()"/>
          </div>
          <Textarea id="firstname" class="p-mt-2" type="text" v-model="cf_stipulation" disabled />
        </div>
        <div class="p-field">
          <div class="p-d-flex p-ai-center p-jc-between">
            <label for="agree2">개인정보처리방침안내</label>
            <Checkbox id="agree2" value="agree2" v-model="agree" @click="Allcheck()" />
          </div>
          <table class="agree2-table">
            <thead>
              <th> 목적 </th>
              <th> 항목 </th>
              <th> 보유기간 </th>
            </thead>
            <tbody>
              <tr>
                <td> 이용자 식별 및 본인여부 확인 </td>
                <td> 아이디, 이름, 비밀번호 </td>
                <td> 회원 탈퇴 시까지 </td>
              </tr>
              <tr>
                <td> 고객서비스 이용에 관한 통지, CS대응을 위한 이용자 식별 </td>
                <td> 연락처 (이메일, 휴대전화번호) </td>
                <td> 회원 탈퇴 시까지 </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="p-field">
          <div class="p-d-flex p-ai-center">
            <Checkbox id="all_check" v-model="all_check" :binary="true" @click="Allcheck()"/>
            <label for="all_check">회원가입 약관에 모두 동의합니다.</label>
          </div>
        </div>
      </div>

    </template>
    <template v-slot:footer>
      <div class="p-grid p-nogutter p-justify-between">
        <i></i>
        <Button label="회원가입" @click="nextPage()" icon="pi pi-angle-right" iconPos="right" />
      </div>
    </template>
  </Card>
</template>

<script lang="ts">
import { getPostAPI } from '@/type';
import { useRouter } from 'vue-router';
import { computed, defineComponent, onMounted, reactive, ref, toRef, toRefs } from 'vue'
import { useToast } from "primevue/usetoast";
import { useHead } from '@vueuse/head'

export default defineComponent({
  setup () {
    const cf_stipulation = ref<string>('');
    const cf_privacy = ref<string>('');
    const all_check = ref<boolean>(false);
    const agree = ref<string[]>();
    const router = useRouter();
    const toast = useToast();
    const nextPage = () => {
      if(agree.value && agree.value.length == 2) {
        router.push({name : 'RegsiterJoin'})
      } else {
        toast.add({severity:'error', summary: '경고', detail:'회원가입 약관을 읽고 동의해주세요.', life: 3000});
      }
    }
    useHead({
      title: `회원가입 약관`,
    })
    onMounted(async () => {
      const res = await getPostAPI(`/register`);
      // console.log(res);
      cf_stipulation.value = res.data.cf_stipulation;
    });
    return {
      cf_stipulation,
      agree,
      all_check,
      nextPage,
    }
  },
  methods : {
    Allcheck() {
      if(this.all_check === false) {
        this.agree = ['agree', 'agree2'];
      }else if(this.all_check === true){
        this.agree = [];
      }
      if(this.agree && this.agree.length === 2) {
        this.all_check = false;
      }
      console.log(this.agree, this.agree?.length);
      if(this.agree && this.agree.length !== 2) {
        this.all_check = true;
      }
    },
  }
})
</script>

<style scoped>
.agree2-table {border-collapse: collapse;width:100%}
.agree2-table th, .agree2-table td{border:1px solid var(--surface-300);padding:0.5rem;vertical-align: middle;}
.agree2-table th{width:33%;font-weight: 800;}
</style>