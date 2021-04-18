import {RootState} from '@/store'
import { Module } from 'vuex';
export interface layoutType {
	isSidebar:boolean
}
const initalState:layoutType = {isSidebar : window.innerWidth > 768 ? true : false}
export const layout:Module<layoutType, RootState> = {
	namespaced: true,
	state: initalState,
	mutations: {
		toggle(state:layoutType) {
			if (state.isSidebar === true) state.isSidebar = false;
			else state.isSidebar = true;
		},
		resize(state:layoutType) {
			const vlaue =  window.innerWidth > 768 ? true : false;
			state.isSidebar = vlaue;
		}
	},
	actions: {		
	},
	modules: {
	}
}