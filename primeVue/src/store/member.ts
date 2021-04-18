import {getPostAPI, member as memberType} from '@/type'
import {RootState} from '@/store'
import { Module } from 'vuex';
const initalState:memberType = {mb_id : "", mb_password : "", mb_level : 1}
export const member:Module<memberType, RootState> = {
	namespaced: true,
	state: initalState,
	modules: {
	}
}