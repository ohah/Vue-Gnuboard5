import { createStore, Store } from 'vuex'
import { provide, inject } from 'vue';
import { layout, layoutType } from './layout'
import { getPostAPI, member as memberType } from '@/type';
import { member } from './member';
import { captcha } from './captcha';
export interface RootState {
  layout:layoutType,
  member:memberType,
  captcha:any,
}
const store = createStore({
  mutations: {
		async LOGIN(state, member:memberType) {
			const res = await getPostAPI('/Login', member);
      if(res.data.msg) {
        alert(res.data.msg);
      } else {
				state.member = res.data;
				return state;
      }
    },
		async LOGOUT(state, member:memberType) {
      state.member = member;
      return state;
    },
	},
	actions: {
		async LOGIN({state, commit}, member:memberType) {
      commit('LOGIN', member);
    },
		async LOGOUT({state, commit}) {
      await commit('LOGOUT', member);
      const res = await getPostAPI('/Logout');
      state.member = res.data;
      const res2 = await getPostAPI(`/captcha/K`, {refresh : 1});
      state.captcha = res2.data;
      return state;
    },
    async GET_CAPTCHA({state, commit}, captcha:any) {
      state.captcha = captcha;
      return state;
    },
    async REFRESH_CAPTCHA({state, commit}) {
      const res = await getPostAPI(`/captcha/K`, {refresh : 1});
      state.captcha = res.data;
      return state;
    },
	},
  modules : { layout, member, captcha }
})
// Provide 구분 값
const StoreSymbol = Symbol();

// 저장소 제공 헬퍼 함수
export const provideStore = () => {
  provide(StoreSymbol, store);
};

// 저장소 주입 헬퍼 함수
export const useStore = () => {
  const store = inject<Store<RootState>>(StoreSymbol);
  if (!store) {
    throw new Error('No store provided');
  }
  return store;
};

export default store;