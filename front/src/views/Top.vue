<template>
	<div class="layout-topbar">
    <Dialog header="비밀번호를 입력하세요" v-model:visible="Modal.profile" :modal="true">
      <Password name="wr_password" class="p-w-full" v-model="ProfilePassword" toggleMask placeholder="비밀번호"></Password>
      <template #footer>
        <Button label="취소" icon="pi pi-times" @click="Modal.profile = false" class="p-button-text"/>
        <Button label="확인" icon="pi pi-check" @click="Profile" autofocus />
      </template>
    </Dialog>
    <div class="layout-topbar-left">
      <button class="p-button-sm p-panel-header-icon p-link p-p-3 text-white" @click="SideBarToggle"><i class="pi pi pi-bars"></i></button>
    </div>
    <div class="layout-topbar-right p-d-flex p-ai-center">
      <div class="p-d-flex p-ai-center p-mx-3" label="V">
        <router-link class="p-mr-2 p-d-flex" :to="{name:'RegsiterAgree'}"><Button icon="pi pi-user-plus" class="p-button-sm" v-tooltip.bottom="'회원가입'"/> </router-link>
        <Button icon="pi pi-search" class="p-button-sm p-mr-2" v-tooltip.bottom="'검색'" @click="toggleSearch"/>
        <OverlayPanel ref="SearchForm" class="p-p-3">
          <form @submit.prevent="fsearch_onsubmit">
            <div class="p-d-flex">
              <InputText type="text" v-model="stx" />
              <Button type="submit" label="검색" icon="pi pi-search" class="p-button-sm p-button-help"/>
            </div>
          </form>
        </OverlayPanel>
        <Button v-if="!member.mb_nick" icon="pi pi-sign-in" v-tooltip.bottom="'로그인'" class="p-button-sm" @click="ShowLogin" />
        <Dialog header="로그인" v-model:visible="loginModal" :modal="true">
          <form @submit.prevent="Login">
            <div class="p-inputgroup p-mb-2">
              <span class="p-inputgroup-addon">
                <i class="pi pi-user"></i>
              </span>
              <InputText placeholder="아이디를 입력하세요" v-model="mb_id" />
            </div>
            <div class="p-inputgroup">
              <span class="p-inputgroup-addon">
                <i class="pi pi-key"></i>
              </span>
              <Password placeholder="비밀번호를 입력하세요" v-model="mb_password" toggleMask></Password>
            </div>
            <div class="p-inputgroup" v-if="cf_social_login_use === 1">
              <Social 
                :list="cf_social_servicelist"
                :ModalHide = LoginModalHide
              />
              <!-- <div v-for="(item, i) in cf_social_servicelist" :key="i">
                <Button :label="item" @click="Popup(item)"/>
              </div> -->
            </div>
            <!-- <iframe ref="iframe" crossorigin="anonymous"/> -->
          </form>
          <template #footer>
            <Button label="취소" icon="pi pi-times" @click="loginModal = !loginModal" class="p-button-text"/>
            <Button label="로그인" icon="pi pi-check" @click="Login" autofocus />
          </template>
        </Dialog>
        <Chip v-if="member.mb_nick" @click="toggleMenu" aria-haspopup="true" aria-controls="overlay_tmenu" style="cursor:pointer" :label="member.mb_nick" 
        image="https://images.unsplash.com/photo-1528892952291-009c663ce843?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=296&q=80" class="p-ml-1"/>
        <OverlayPanel ref="op" v-if="member">
          <Listbox :options="Menus" optionLabel="label" @change="MenusEvent" style="border:0;min-width:10rem">
            <template #option="row">
              <div class="p-d-flex p-jc-between">
                <span> <i :class="row.option.icon" class="p-mr-2"> </i> {{row.option.label}} </span>
                <span v-if="row.option.label === '포인트'"> {{member.mb_point}} </span>
                <span v-if="row.option.label === '메모'"> {{member.mb_memo_cnt}} </span>
                <span v-if="row.option.label === '스크랩'"> {{member.mb_scrap_cnt}} </span>
              </div>
            </template>
          </Listbox>
        </OverlayPanel>
      </div>
    </div>
	</div>
</template>

<script lang="ts">
import { RootState } from '@/store';
import { useStore } from 'vuex'
import { defineComponent,ref, computed, onMounted, reactive, toRefs } from 'vue'
import { usePrimeVue } from "primevue/config";
import { getAPI, getPostAPI, socialconfig } from '../type';
import { useRoute, useRouter } from 'vue-router';
import Social from '../components/Social.vue'
export default defineComponent({
  components : {
    Social,
  },
	setup () {
    const primevue:any = usePrimeVue();
    const {state, dispatch, commit} = useStore<RootState>();
    const member = computed(()=>state.member);
    const router = useRouter();
    const route = useRoute();
    // document.domain = "localhost";
    onMounted(async ()=>{
      primevue.config.locale.weak = "보안에 심각한 문제가 있는 비밀번호입니다";
      primevue.config.locale.medium = "보안에 문제가 있습니다";
      primevue.config.locale.strong = "보안에 문제가 없습니다";
      primevue.config.locale.passwordPrompt = "비밀번호를 입력하세요";
      primevue.config.locale.accept = "확인";
      primevue.config.locale.reject = "취소";
      primevue.config.locale.choose = "업로드";
      const Login_Check = await getPostAPI('/LoginCheck');
      // console.log(state.member);
      if(Login_Check.data.mb_nick) {
        state.member = Login_Check.data;
      }
    });
    const Popup = (platform:string) => {
      router.push({name : 'social', params : {provider : platform}});
      loginModal.value = false;
      // var pop_url = `http://localhost:8080/v/plugin/social/popup.php?provider=${platform}`;
      // var newWin = window.open(
      //   pop_url, 
      //   "social_sing_on", 
      //   "location=0,status=0,scrollbars=1,width=600,height=500"
      // );
      // if(!newWin || newWin.closed || typeof newWin.closed=='undefined')
      //   alert('브라우저에서 팝업이 차단되어 있습니다. 팝업 활성화 후 다시 시도해 주세요.');
      return false;
    }
    const isSidebar = computed(()=>state.layout.isSidebar);
    const SideBarToggle = ()=> commit("layout/toggle");
    const dropdown = ref<boolean>(false)
    const op = ref<HTMLElement>();
    const SearchForm = ref<HTMLElement>();
    const toggleSearch = (event:any) => {
      (SearchForm.value as any).toggle(event);
    }
    const fsearch_onsubmit = (e:Event) => {
      e.preventDefault();
      router.push({path : '/bbs/search', query : {sop : 'and', stx : stx.value, sfl: 'wr_subject||wr_content'}});
      (SearchForm.value as any).hide();
    }
    const Modal = reactive({
      profile : false,
    });
    const ProfilePassword = ref<string>('');
    const Profile = () => {
      Modal.profile = false;
      console.log(ProfilePassword.value);
      router.push({name : 'RegsiterJoin', params : {password : ProfilePassword.value}});
    }
    const Menus = reactive([
      {
        label: '포인트',
        icon: 'pi pi-dollar',
        command: () => {
          (op.value as any).hide();
          router.push({path : '/bbs/point'});
        }
      },
      {
        label: '메모',
        icon: 'pi pi-bell',
        command: () => {
          (op.value as any).hide();
          router.push({path : '/bbs/memo'});
        }
      },
      {
        label: '스크랩',
        icon: 'pi pi-share-alt',
        command: () => {
          (op.value as any).hide();
          router.push({path : '/bbs/scrap'});
        }
      },
      {
        label: '정보수정',
        icon: 'pi pi-user-edit',
        command: () => {
          (op.value as any).hide();
          Modal.profile = true;
        }
      },
      {
        label: '로그아웃',
        icon: 'pi pi-sign-in',
        command: () => {
          (op.value as any).hide();
          logout();
          // router.push(router.currentRoute.value);
          router.push({name:'Home'});
        }
      }
    ]);
    const MenusEvent = (e:any) => {
      const command = Menus.find((menu) => e.value.label === menu.label)?.command;
      if(command) command();
    }
    const lgValue = reactive({
      mb_id:'',
      mb_password:'',
    });
    const loginModal = ref<boolean>(false)
    const login = async (member:any) => {
      await dispatch("LOGIN", member);
      loginModal.value = false;
    }
    const logout = async () => {
      await dispatch("LOGOUT", member);
    }
    const socialLogin = reactive<socialconfig>({
      cf_social_login_use:0,
      cf_social_servicelist:[],
    });
    const ShowLogin = async () => {
      const res = await getAPI('/social/config');
      loginModal.value = true;
      socialLogin.cf_social_login_use = res.data.cf_social_login_use;
      socialLogin.cf_social_servicelist = res.data.cf_social_servicelist;
    }
    const LoginModalHide = () => {
      loginModal.value = false;
    }
    const stx = ref<string>('');
		return {
      Popup,
      ...toRefs(socialLogin),
      MenusEvent,
      Menus,
      ShowLogin,
      router,
      logout,
      login,
      dropdown,
      op,
      SideBarToggle,
      isSidebar,
      loginModal,
      ...toRefs(lgValue),
      member,
      SearchForm,
      LoginModalHide,
      toggleSearch,
      stx,
      fsearch_onsubmit,
      Modal,
      ProfilePassword,
      Profile
		}
	},
  methods: {
    toggle(event:any) {
      (this.$refs.menu as any).toggle(event);
    },
    toggleMenu(event:any) {
      (this.$refs.op as any).toggle(event);
    },
    async Login() {
      if(this.mb_id.length === 0 || this.mb_password.length === 0 ) {
        alert('아이디와 비밀번호를 입력하세요');
      }else {
        this.login({mb_id : this.mb_id,mb_password : this.mb_password});
        this.$forceUpdate();
      }
    },
  }
})
</script>
<style scoped>
</style>