<template>
  <OverlayPanel ref="replyOverlay">
    <form name="fviewcomment" v-on:submit.prevent="editreply_submit($event, 'reply')" method="post" autocomplete="off" class="w-full p-p-2">
      <div v-if="member.mb_level === 1 && member">
        <label for="wr_name" class="sound_only">이름<strong> 필수</strong></label>
        <InputText type="text" v-model="edit.wr_name" class="p-mr-2" name="wr_name" placeholder="이름" />
        <label for="wr_password" class="sound_only">비밀번호<strong> 필수</strong></label>
        <Password name="wr_password" v-model="edit.wr_password" toggleMask placeholder="비밀번호"></Password>
      </div>
      <Textarea name="wr_content" v-model="edit.wr_content" class="p-w-full p-my-1" rows="5" cols="30" placeholder="댓글을 입력해주세요" />
      <div v-if="member.mb_level === 1 && member">
        <Captcha v-model="edit.wr_captcha" :captcha="captcha"/>
      </div>
    <div class="p-d-flex p-jc-end">
      <Button type="submit">댓글등록</Button>
    </div>
    </form>
  </OverlayPanel>
  <OverlayPanel ref="editOverlay">
    <form name="fviewcomment" v-on:submit.prevent="editreply_submit($event, 'edit')" method="post" autocomplete="off" class="w-full p-p-2">
      <div v-if="member.mb_level === 1 && member">
        <label for="wr_name" class="sound_only">이름<strong> 필수</strong></label>
        <InputText type="text" v-model="edit.wr_name" class="p-mr-2" name="wr_name" placeholder="이름" />
        <label for="wr_password" class="sound_only">비밀번호<strong> 필수</strong></label>
        <Password name="wr_password" v-model="edit.wr_password" toggleMask placeholder="비밀번호"></Password>
      </div>
      <Textarea name="wr_content" v-model="edit.wr_content" class="p-w-full p-my-1" rows="5" cols="30" placeholder="댓글을 입력해주세요" />
      <div v-if="member.mb_level === 1 && member">
        <Captcha v-model="edit.wr_captcha" :captcha="captcha"/>
      </div>
      <div class="p-d-flex p-jc-end">
        <Button type="submit">댓글등록</Button>
      </div>
    </form>
  </OverlayPanel>
  <OverlayPanel ref="deleteOverlay">
    <form name="fviewcomment" v-on:submit.prevent="delete_submit" method="post" autocomplete="off" class="w-full my-1">
      <div v-if="member.mb_level === 1 && member">
        <Password type="text" v-model="edit.wr_password" class="p-mr-2" toggleMask name="wr_password" placeholder="비밀번호를 입력하세요" />
      </div>
      <div v-else>
        <h2> 정말 삭제하시겠습니까? </h2>
      </div>
      <div class="p-d-flex p-jc-end p-mt-2">
        <Button type="button" class="p-mr-2" @click="$refs.deleteOverlay.hide(e);">취소</Button>
        <Button type="submit" class="p-button-danger">삭제하기</Button>
      </div>
    </form>
  </OverlayPanel>
  <Panel :toggleable="true" :collapsed="false" class="p-shadow-1 cmt-panel p-mt-3" v-if="isLoading === true">
    <template #header>
      <span class="p-bold">댓글 {{cmt_amt}}건 </span>
    </template>
    <DataView :value="list" ref="cmt_list">
      <template #list="row">
        <ul style="display:block;width:100%;" @mouseover="show(row.data.wr_id)" @mouseleave="hide(row.data.wr_id)" :style="`margin-left:${row.data.wr_comment_reply.length * 5}px`" :data-wr_id="row.data.wr_id">
          <div class="p-d-flex p-w-full p-jc-between p-p-2"> 
            <h2> 
              <SideView :data="row.data.name" />
              <span v-if="board.bo_use_ip_view !== 0" class="font-normal text-sm">
                <span class="sound_only">아이피</span>
                <span class="md:text-base text-xs truncate">({{row.data.ip}})</span>
              </span>
            </h2>
            <ul>
              <small class="datetime text-gray-500 text-sm"><i class="pi pi-clock p-mr-2"></i><time dateTime="">{{row.data.datetime}}</time></small>
              <div class="p-hidden edit-menu">
                <span class="p-buttonset">
                  <Button type="button" style="padding:0 0.325rem" @click="reply_cmt($event, row.data)" class="p-button-secondary p-button-sm p-button-text" label="답변" icon="pi pi-reply" />
                  <Button type="button" style="padding:0 0.325rem" @click="edit_cmt($event, row.data)" class="p-button-secondary p-button-sm p-button-text" label="수정" icon="pi pi-user-edit" />
                  <Button type="button" style="padding:0 0.325rem" @click="delete_cmt($event, row.data)" class="p-button-secondary p-button-sm p-button-text" label="삭제" icon="pi pi-trash" />
                </span>
              </div>
            </ul>
          </div>
            <div class="p-py-2 p-p-2" v-html="row.data.content"></div>
        </ul>
        <Divider />
      </template>
      <template #grid="row">
        <div style="padding: .5em" class="p-col-12 p-md-3">
          <Panel :header="row.data.vin" style="text-align: center">
            <img :src="'demo/images/car/' + row.data.brand + '.png'" :alt="row.data.brand"/>
            <div class="car-detail">{{row.data.year}} - {{row.data.color}}</div>
            <Button icon="pi pi-search"></Button>
          </Panel>
        </div>
      </template>
      <template #empty>
        <div class="p-d-flex p-p-3 p-jc-center">댓글 목록이 없습니다.</div>
      </template>
    </DataView>
  </Panel>
  <CommentSkeleton v-else />
  <Panel :toggleable="true" :collapsed="false" class="p-shadow-1 p-my-3 cmt-panel" v-if="is_comment_write === true">
    <template #header>
      <span class="p-bold">댓글 쓰기</span>
    </template>
    <form name="fviewcomment" v-on:submit.prevent="fviewcomment_submit($event)" method="post" autocomplete="off" class="w-full my-1">
      <h2 class="sound_only">댓글쓰기</h2>
      <span class="sound_only">내용</span>
        <strong id="char_cnt" v-if="comment_min || comment_max"><span id="char_count"></span>글자</strong>
        <Textarea name="wr_content" class="p-w-full" v-model="cmt_write.wr_content" rows="5" cols="30" placeholder="댓글을 입력해주세요" />
        <div class="p-mb-2">
          <div class="p-my-2">
            <div v-if="member.mb_level === 1 && member">
              <label for="wr_name" class="sound_only">이름<strong> 필수</strong></label>
              <InputText type="text" v-model="cmt_write.wr_name" class="p-mr-2" name="wr_name" placeholder="이름" />
              <label for="wr_password" class="sound_only">비밀번호<strong> 필수</strong></label>
              <Password name="wr_password" v-model="cmt_write.wr_password" toggleMask placeholder="비밀번호"></Password>
            </div>              
          </div>
          <div class="text-right w-full" id="captcha">
            <div v-if="member.mb_level === 1 && member">
              <div ref="captcha" class="p-d-flex p-my-2">
                <Captcha v-model="cmt_write.wr_captcha" :captcha="captcha"/>
              </div>
            </div>
            <div class="p-d-flex p-jc-end p-ai-center p-mx-3">
              <Checkbox name="wr_secret" value="secret" v-model="wr_secret" />
              <label for="wr_secret">비밀글</label>
              <Button type="submit">댓글등록</Button>
            </div>
          </div>
        </div>
    </form>
  </Panel>
</template>

<script lang="ts">
import { defineComponent, computed, reactive, onMounted, toRefs, ref} from 'vue';
import { useStore } from 'vuex';
import { useRoute } from 'vue-router'
import { CmtList, getPostAPI, view_comment as _view_comment } from '@/type'
import { getAPI, qstr, qstrInitial, CommentParent, BoardInitial, CmtListInitial} from '@/type'
import { RootState } from '@/store';
import SideView from '@/components/bbs/Sideview.vue'
import Captcha from '@/components/Captcha.vue'
import CommentSkeleton from '@/components/bbs/CommentSkeleton.vue';
export default defineComponent({
  name : 'View_Comment',
  components: {
    CommentSkeleton,
    SideView,
    Captcha
  },
  props : {
    bo_table:String,
    wr_id:String
  },
  setup(props) {
    const isLoading = ref<boolean>(false);
    const cmt_list = ref<HTMLElement>();
    const route = useRoute();
    const editorOverlay = ref<HTMLElement>();
    const replyOverlay = ref<HTMLElement>();
    const deleteOverlay = ref<HTMLElement>();
    const qstr = reactive<qstr>({
      ...qstrInitial,
      ...route.query
    });
    const wr_secret = ref<string[]>([]);
    const bo_table = props.bo_table ? props.bo_table : '';
    const wr_id = props.wr_id;
    const { state, dispatch } = useStore<RootState>(); // 저장소 객체 주입받아 사용하기
    const member = computed(() => state.member);
    const captcha = computed(() => state.captcha);
    const cmt = reactive<CommentParent>({
      cmt_amt : 0,
      is_comment_write:false,
      comment_min:0,
      comment_max:0,
      captcha_html:"",
      board:BoardInitial,
      comment_common_url:"",
      list: [CmtListInitial],
    });
    const edit = reactive({
      wr_name:'',
      wr_password:'',
      wr_content:'',
      wr_captcha:'',
      wr_id:'',
      token:'',
    })
    const cmt_write = reactive({
      wr_name:'',
      wr_password:'',
      wr_content:'',
      wr_captcha:'',
    });
    const refresh_captcha = async () => {
      dispatch("REFRESH_CAPTCHA");
    }
    const fviewcomment_submit = async (e:Event) => {
      e.preventDefault();
      const f = new FormData();
      f.append("w", "c");
      f.append("bo_table" , qstr.bo_table);
      f.append("wr_id" , qstr.wr_id);
      f.append("sca" , qstr.sca);
      f.append("sfl" , qstr.sfl);
      f.append("stx" , qstr.stx);
      f.append("spt" , qstr.spt);
      f.append("page" , qstr.page);
      f.append("is_good", "");
      f.append('bo_table', qstr.bo_table);
      f.append('wr_name', cmt_write.wr_name);
      f.append('wr_password', cmt_write.wr_password);
      f.append('comment_id', qstr.wr_id);
      f.append('wr_content', cmt_write.wr_content);
      f.append('captcha_key', cmt_write.wr_captcha);
      f.append('wr_secret', wr_secret.value.join(''));
      const token = await getPostAPI(`/get_write_comment_token`);
      f.append('token', token.token);
      const res = await getPostAPI(`/write_comment/${qstr.bo_table}/${qstr.wr_id}/cu`, f);
      if(res.data.msg) {
        alert(res.data.msg)
      } else {
        cmt.cmt_amt = res.data.cmt_amt;
        cmt.is_comment_write = res.data.is_comment_write;
        cmt.comment_min = res.data.comment_min;
        cmt.comment_max = res.data.comment_max;
        cmt.captcha_html = res.data.captcha_html;
        dispatch("GET_CAPTCHA", res.data.captcha_html);
        cmt.board = res.data.board;
        cmt.comment_common_url = res.data.comment_common_url;
        cmt.list = res.data.list;
        cmt.cmt_amt = res.data.cmt_amt;
        cmt_write.wr_name = '';
        cmt_write.wr_password = '';
        cmt_write.wr_content = '';
        cmt_write.wr_captcha = '';
        edit.token = '';
      }
    }
    const getCmt = async () => {
      const res = await getAPI(`/board/${bo_table}/${wr_id}/comments`);
      cmt.cmt_amt = res.data.cmt_amt;
      cmt.is_comment_write = res.data.is_comment_write;
      cmt.comment_min = res.data.comment_min;
      cmt.comment_max = res.data.comment_max;
      cmt.captcha_html = res.data.captcha_html;
      dispatch("GET_CAPTCHA", res.data.captcha_html);
      cmt.board = res.data.board;
      cmt.comment_common_url = res.data.comment_common_url;
      cmt.list = res.data.list;
      cmt.cmt_amt = res.data.cmt_amt;
      isLoading.value = true;
    }
    onMounted(async () => {
      getCmt();
    });
    return {
      ...toRefs(cmt),
      edit,
      wr_secret,
      isLoading,
      cmt_list,
      member,
      editorOverlay,
      replyOverlay,
      deleteOverlay,
      qstr,
      cmt_write,
      cmt,
      refresh_captcha,
      fviewcomment_submit,
      captcha,
      getCmt,
    }
  },
  methods : {
    show (wr_id:number) {
      document.querySelector(`[data-wr_id='${wr_id}'] .edit-menu`)?.classList.remove('p-hidden');
      document.querySelector(`[data-wr_id='${wr_id}'] .datetime`)?.classList.add('p-hidden');
    },
    hide (wr_id:number) {
      document.querySelector(`[data-wr_id='${wr_id}'] .edit-menu`)?.classList.add('p-hidden');
      document.querySelector(`[data-wr_id='${wr_id}'] .datetime`)?.classList.remove('p-hidden');
    },
    reply_cmt (e:any, _row:CmtList) {
      (this.$refs.replyOverlay as any).show(e);
      this.edit.wr_content = ``;
      this.edit.wr_id = `${_row.wr_id}`;
    },
    edit_cmt (e:any, _row:CmtList) {
      (this.$refs.editOverlay as any).show(e);
      this.edit.wr_content = `${_row.content}`;
      this.edit.wr_id = `${_row.wr_id}`;
    },
    delete_cmt (e:any, _row:CmtList) {
      (this.$refs.deleteOverlay as any).show(e);
      this.edit.wr_id = `${_row.wr_id}`;
      console.log('delete_cmt',_row);
      this.edit.token = _row.token;
    },
    async editreply_submit (e:Event, type:string) {
      e.preventDefault();      
      const f = new FormData();
      if(type ==="edit") f.append("w", "cu");
      if(type === "reply") f.append("w", "c");
      f.append("bo_table" , this.qstr.bo_table);
      f.append("wr_id" , this.qstr.wr_id);
      f.append("sca" , this.qstr.sca);
      f.append("sfl" , this.qstr.sfl);
      f.append("stx" , this.qstr.stx);
      f.append("spt" , this.qstr.spt);
      f.append("page" , this.qstr.page);
      f.append("is_good", "");
      f.append('bo_table', this.qstr.bo_table);
      f.append('wr_name', this.edit.wr_name);
      f.append('wr_password', this.edit.wr_password);
      if(type === "edit") f.append('comment_id', this.edit.wr_id.toString());
      if(type === "reply") f.append('comment_id', this.edit.wr_id.toString());
      f.append('wr_content', this.edit.wr_content);
      f.append('captcha_key', this.edit.wr_captcha);
      const token = await getPostAPI(`/get_write_comment_token`);
      f.append('token', token.token);
      const res = await getPostAPI(`/write_comment/${this.qstr.bo_table}/${this.qstr.wr_id}/cu`, f);
      if(res.data.msg) {
        alert(res.data.msg)
      }else {
        this.cmt.cmt_amt = res.data.cmt_amt;
        this.cmt.is_comment_write = res.data.is_comment_write;
        this.cmt.comment_min = res.data.comment_min;
        this.cmt.comment_max = res.data.comment_max;
        this.cmt.captcha_html = res.data.captcha_html;
        this.cmt.board = res.data.board;
        this.cmt.comment_common_url = res.data.comment_common_url;
        this.cmt.list = res.data.list;
        this.cmt.cmt_amt = res.data.cmt_amt;
        this.edit.wr_name = '';
        this.edit.wr_password = '';
        this.edit.wr_content = '';
        this.edit.wr_captcha = '';
        this.edit.token = '';
      }
      if(type === "reply") (this.$refs.replyOverlay as any).hide(e);
      if(type === "edit") (this.$refs.editOverlay as any).hide(e);
    },
    async delete_submit (e:Event) {
      e.preventDefault();
      const f = new FormData();
      f.append('bo_table', this.qstr.bo_table);
      // f.append('wr_password', edit.wr_password);
      f.append('comment_id', this.edit.wr_id.toString());
      f.append('token', this.edit.token);
      const res = await getPostAPI(`/board/${this.qstr.bo_table}/${this.qstr.wr_id}/comment/${this.edit.wr_id}`, f)
      if(res.data.msg) {
        alert(res.data.msg)
      } else {
        this.cmt.cmt_amt = res.data.cmt_amt;
        this.cmt.is_comment_write = res.data.is_comment_write;
        this.cmt.comment_min = res.data.comment_min;
        this.cmt.comment_max = res.data.comment_max;
        this.cmt.captcha_html = res.data.captcha_html;
        this.cmt.board = res.data.board;
        this.cmt.comment_common_url = res.data.comment_common_url;
        this.cmt.list = res.data.list;
        this.cmt.cmt_amt = res.data.cmt_amt;
        this.edit.wr_name = '';
        this.edit.wr_password = '';
        this.edit.wr_content = '';
        this.edit.wr_captcha = '';
        this.edit.token = '';
        (this.$refs.deleteOverlay as any).hide(e);
      }
    }
  }
})
</script>
<style scoped>

</style>