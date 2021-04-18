<template>
  <section v-if="isLoading">
    <form ref="fwrite" v-on:submit.prevent="fwrite_submit" method="post" enctype="multipart/form-data" autocomplete="off" :style="{width : width}">
      <input type="hidden" name="uid" :value="uid"/>
      <input type="hidden" name="w" :value="w"/>
      <input type="hidden" name="bo_table" :value="bo_table"/>
      <input type="hidden" name="wr_id" :value="wr_id"/>
      <input type="hidden" name="sca" :value="qstr.sca"/>
      <input type="hidden" name="sfl" :value="qstr.sfl"/>
      <input type="hidden" name="stx" :value="qstr.stx"/>
      <input type="hidden" name="spt" :value="qstr.spt"/>
      <input type="hidden" name="sst" :value="qstr.sst"/>
      <input type="hidden" name="sod" :value="qstr.sod"/>
      <input type="hidden" name="page" :value="qstr.page"/>
      <ul class="p-d-flex">
        <li v-if="is_notice" ><input type="checkbox" id="notice" name="notice" :value="`${notice_checked}`" /><label for="notice"><span></span>공지</label></li>
        <input v-if="is_dhtml_editor" type="hidden" value="html1" name="html" />
        <li v-else ><input type="checkbox" id="html" name="html"  value="html2" :checked="html_checked ? html_checked : false"><label for="html"><span></span>html</label></li>
        <li v-if="is_admin || is_secret == 1" ><input type="checkbox" id="secret" name="secret" :value="`secret`" :checked="secret_checked ? true : false" /><label for="secret"><span></span>비밀글</label></li>
        <input v-else type="hidden" name="secret" value="secret" />
        <li v-if="is_mail" ><input type="checkbox" id="mail" name="mail"  :checked="recv_email_checked ? recv_email_checked : false" value="mail"><label for="mail"><span></span>답변메일받기</label></li>
      </ul>
      <div v-if="is_category" class="p-mb-2 p-md-mb-0">        
        <Dropdown style="width:100%" v-model="write.ca_name" optionValue="value" :options="category_option" optionLabel="name" placeholder="카테고리를 선택하세요" />
        <label for="ca_name" class="sound_only">분류<strong>필수</strong></label>
      </div>
      <div class="p-grid p-formgrid p-my-1">
        <div class="p-col-12 p-md-6 p-my-1" v-if="is_name" >
          <InputText style="width:100%"  id="wr_name" name="wr_name" type="text" :value="name" placeholder="이름" />
        </div>
        <div class="p-col-12 p-md-6 p-px-0 p-my-1" v-if="is_password">
          <Password class="p-col-12" id="wr_password" name="wr_password" placeholder="비밀번호" />
        </div>
      </div>
      <div class="p-grid p-formgrid p-my-1">
        <div class="p-col-12 p-md-6 p-my-1" v-if="is_email" >
          <InputText style="width:100%" id="wr_email" name="wr_email" type="text" :value="email" placeholder="이메일" />
        </div>
        <div class="p-col-12 p-md-6 p-my-1" v-if="is_password">
          <InputText style="width:100%" id="wr_homepage" name="wr_homepage" type="text" :value="homepage" placeholder="홈페이지" />
        </div>
      </div>
      <div class="p-field p-grid" v-if="option">
        <span class="sound_only">옵션</span>
        <ul class="bo_v_option dark:bg-gray-600 dark:text-gray-400" v-html="option"></ul>
      </div>
      <div class="p-field p-my-2">
        <InputText placeholder="제목을 입력하세요" style="width:100%" id="wr_subject" name="wr_subject" type="text" :value="subject" required/>
      </div>
      <Editor v-if="is_dhtml_editor === true" class="p-mb-3" @text-change="ImageUpload" @image-upload="console.log('upload')" name="wr_content" v-model="write.wr_content" editorStyle="height: 320px"/>
      <Textarea class="p-col-12 p-mb-3" name="wr_content" v-model="write.wr_content" :autoResize="true" rows="10" cols="30" v-else-if="is_dhtml_editor === false"/>
      <div v-for="(n, i) in link_count" :key="i">
        <div class="p-inputgroup p-mb-2">
          <span class="p-inputgroup-addon">
              <i class="pi pi-link"></i>
          </span>
          <InputText placeholder="링크 입력" :name="`wr_link${i+1}`" :value="i === 0 ? write.wr_link1 : write.wr_link2" />
        </div>
      </div>
      <Upload @change="UploadChange" :FileList="file" :file_count="file_count"/>
      <div v-if="is_guest === true">
        <div class="p-d-flex p-my-2">
          <Captcha :captcha="captcha_html"/>
        </div>
      </div>
      <div class="p-d-flex p-jc-end p-my-2">
        <router-link to='/'><Button class="p-button-secondary p-mr-2">취소</Button> </router-link>
        <Button type="submit" id="btn_submit" accesskey="s" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">작성완료</Button>
      </div>
    </form>
  </section>
</template>

<script lang="ts">
import { defineComponent, ref, reactive, onMounted, toRefs, computed } from 'vue';
import { useStore } from 'vuex';
import { useRoute, useRouter} from 'vue-router';
import { getAPI, getPostAPI, WriteParentInitial, WriteParent, qstr, WriteInitial, qstrInitial, RdURL} from '@/type';
import Upload from '@/components/bbs/Upload.vue'
import { useHead } from '@vueuse/head'
import Captcha from '@/components/Captcha.vue'
export default defineComponent({
  name : 'Write',
  components : {
    Upload,
    Captcha
  },
  setup() {
    const { state, dispatch } = useStore(); // 저장소 객체 주입받아 사용하기
    const fwrite = ref<HTMLFormElement>();
    const route = useRoute();
    const router = useRouter();
    const isLoading = ref<Boolean>(false);
    const qstr = reactive<qstr>({
      ...qstrInitial,
      ...route.query
    });
    const bf_file = ref<Object[]>();
    const write = reactive<WriteParent>({
      ...WriteParentInitial
    });
    const UploadChange = (e:any) => {
      bf_file.value = e;
    }
    const request = async () => {
      if(qstr.w === '') {
        const res = await getAPI(`/write/${qstr.bo_table}`)
      if(res.data.msg) {
          alert(res.data.msg)
          router.push('/');
        }
        return res
      } else if(qstr.w === 'u' || qstr.w === 'r') {
        const res = await getPostAPI(`/modify/${qstr.bo_table}/${qstr.wr_id}/${qstr.w}`, {
          wr_password : ''
        })
        if(res.data.msg) {
          alert(res.data.msg)
          // router.push('/');
        }
        return res
      }
    }
    useHead({
      title: computed(() => write.title),
    })
    onMounted(async () => {
      const res = await request();
      write.autosave_count = res.data.autosave_count;
      write.bo_table = res.data.bo_table;
      write.wr_id = res.data.wr_id;
      write.w = res.data.w;
      write.write = res.data.write;
      write.is_member = res.data.is_member;
      write.is_admin = res.data.is_admin;
      write.is_guest = res.data.is_guest;
      write.write_min = res.data.write_min;
      write.is_notice = res.data.is_notice;
      write.category_option = res.data.category_option;
      write.is_html = res.data.is_html;
      write.is_secret = res.data.is_secret;
      write.is_mail = res.data.is_mail;
      write.is_name = res.data.is_name;
      write.is_password = res.data.is_password;
      write.is_email = res.data.is_email;
      write.is_homepage = res.data.is_homepage;
      write.is_category = res.data.is_category;
      write.is_link = res.data.is_link;
      write.is_file = res.data.is_file;
      write.is_file_content = res.data.is_file_content;
      write.captcha_html = res.data.captcha_html;
      write.is_use_captcha = res.data.is_use_captcha;
      write.is_dhtml_editor = res.data.is_dhtml_editor;
      write.editor_content_js = res.data.editor_content_js;
      write.autosave_count = res.data.editor_content_js;
      write.uid = res.data.uid;
      write.notice_checked = res.data.notice_checked;
      write.html_checked = res.data.html_checked;
      write.html_value = res.data.html_value;
      write.secret_checked = res.data.secret_checked;
      write.name = res.data.name;
      write.ca_name = res.data.ca_name;
      write.email = res.data.email;
      write.option = res.data.option;
      write.homepage = res.data.homepage;
      write.subject = res.data.subject;
      write.file = res.data.file;
      bf_file.value = res.data.file;
      write.file_count = res.data.file_count;
      write.width = res.data.width;
      write.link_count = res.data.link_count;
      write.title = res.data.title;
      isLoading.value = true;
      if(!res.data.write) {
        write.write = WriteInitial;
      }
    });
    return {
      ...toRefs(write),
      qstr,
      fwrite,
      bf_file,
      UploadChange,
      isLoading,
    };
  },
  methods : {
    async fwrite_submit() {
      const bo_table = this.qstr.bo_table;
      const f = new FormData(this.fwrite);
      f.append('ca_name', this.write.ca_name);
      f.append('wr_content', this.write.wr_content);
      const token = await getPostAPI(`/get_write_token/${this.qstr.bo_table}`);
      f.append('token', token.token);
      if(this.bf_file) {
        this.bf_file.forEach((file, i)=>{
          if((file as any).bf_no >= 0) {
          } else if((file as any).name !== '' && !(file as any).bf_no) {
            f.append(`bf_file[${i}]`, file as File, (file as File).name);
            f.append(`bf_file_del[${i}]`, '1');
          } else if((file as any).name === ''){
            f.append(`bf_file_del[${i}]`, '1');
          }
        });
      }
      let res;
      if(this.qstr.w === 'r') {
        res = await getPostAPI(`update/${bo_table}/${this.qstr.wr_id}/`, f);
      } else {
        res = await getPostAPI(`/${this.qstr.w === 'u' ? "update/" : 'write/'}${bo_table}${this.qstr.wr_id ? `/${this.qstr.wr_id}` : ''}`, f);
      }
      if(res.data.msg) {
        if(res.data.msg === 'success') {
          this.write.wr_content = '';
          this.write.ca_name = '';
          this.write.wr_link1 = '';
          this.write.wr_link2 = '';
          this.$router.push(RdURL(res.data.url));
        }else {
          alert(res.data.msg);
        }
      }
    },
    async refresh_captcha() {
      const res = await getPostAPI(`/captcha/K`, {refresh : 1});
      if(res.data.g5_captcha_url) {
        const captcha_html = {
          g5_captcha_url : res.data.g5_captcha_url
        }
        this.captcha_html = captcha_html;
      }
    },
    ImageUpload(e:any){
      console.log(e);
    }
  }
})
</script>

<style scoped>
</style>