import { RootState } from '@/store'
import { Module } from 'vuex';
const initalState:any = {};
export const captcha:Module<any, RootState> = {
	namespaced: true,
	state: initalState,
	modules: {
	}
}