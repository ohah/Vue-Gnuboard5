import axios from 'axios';
axios.defaults.headers.post['Content-Type'] = 'application/json;charset=utf-8';
axios.defaults.headers.post['Access-Control-Allow-Origin'] = '*';
export async function getMenus() {
  const res = await axios.get<datas>(
    `/api/menus`
  )
  return res.data;
}
export async function getAPI(url: string) {
  const apiurl = window.location.protocol + "//" + window.location.host + "/api";
  const res = await axios.get(`${apiurl}${url}`)
  return res.data;
}
export function serialize(obj: Object) {
  let str = Object.entries(obj).map(([key, val]) => `${key}=${val}`).join('&');
  return str;
}
export async function getPostAPI(url: string, postData?: Object | FormData) {
  const apiurl = window.location.protocol + "//" + window.location.host + "/api";
  const res = await axios.post(apiurl + url, postData);
  return res.data;
}
export function RdURL(url: string) {
  return url.replace(/(http(s)?:\/\/)([a-z0-9\w]+\.*)+[a-z0-9]{2,4}/gi, '')
}
export function newline(text: string) {
  var result = text.replace(/(\\n|\\r\\n)/g, '\r\n');
  return result;
}
export function queryString(url: string) {
  var keyValPairs = [];
  var params: any = {};
  url = url.replace(/.*?\?/, "");
  if (url.length) {
    keyValPairs = url.split('&');
    for (const pairNum in keyValPairs) {
      var key = keyValPairs[pairNum].split('=')[0];
      if (!key.length) continue;
      if (typeof params[key] === 'undefined')
        params[key] = [];
      params[key] = (keyValPairs[pairNum].split('=')[1]);
    }
  }
  return params;
}
export interface datas {
  status: number;
  message: string;
  data: string;
}
export interface status {
  isOpen: boolean,
  isModal: boolean,
  isEvent: boolean
  wr_password: string
  captcha: string,
}
export interface Datum {
  me_id: number;
  me_code: string;
  me_name: string;
  me_link: string;
  me_target: string;
  me_order: number;
  me_use: number;
  me_mobile_use: number;
  ori_me_link: string;
  sub: any[];
}

export type menus = {
  status: number
  message: string
  data: Array<menuData>
}
export interface menuData {
  me_id?: number;
  me_code?: string;
  me_name?: string;
  me_link: string;
  me_target?: string;
  me_order?: number;
  me_use?: number;
  me_mobile_use?: number;
  ori_me_link?: string;
  sub?: menuData[];
}
export interface Config {
  cf_title: string;
  cf_theme: string;
  cf_admin_email_name: string;
  cf_add_script: string;
  cf_use_point: number;
  cf_point_term: number;
  cf_use_copy_log: number;
  cf_use_email_certify: number;
  cf_login_point: number;
  cf_cut_name: number;
  cf_nick_modify: number;
  cf_new_skin: string;
  cf_new_rows: number;
  cf_search_skin: string;
  cf_connect_skin: string;
  cf_faq_skin: string;
  cf_read_point: number;
  cf_write_point: number;
  cf_comment_point: number;
  cf_download_point: number;
  cf_write_pages: number;
  cf_mobile_pages: number;
  cf_link_target: string;
  cf_bbs_rewrite: number;
  cf_delay_sec: number;
  cf_filter: string;
  cf_possible_ip: string;
  cf_intercept_ip: string;
  cf_analytics: string;
  cf_add_meta: string;
  cf_syndi_token: string;
  cf_syndi_except: string;
  cf_member_skin: string;
  cf_use_homepage: number;
  cf_req_homepage: number;
  cf_use_tel: number;
  cf_req_tel: number;
  cf_use_hp: number;
  cf_req_hp: number;
  cf_use_addr: number;
  cf_req_addr: number;
  cf_use_signature: number;
  cf_req_signature: number;
  cf_use_profile: number;
  cf_req_profile: number;
  cf_register_level: number;
  cf_register_point: number;
  cf_icon_level: number;
  cf_use_recommend: number;
  cf_recommend_point: number;
  cf_leave_day: number;
  cf_search_part: number;
  cf_email_use: number;
  cf_email_wr_super_admin: number;
  cf_email_wr_group_admin: number;
  cf_email_wr_board_admin: number;
  cf_email_wr_write: number;
  cf_email_wr_comment_all: number;
  cf_email_mb_super_admin: number;
  cf_email_mb_member: number;
  cf_email_po_super_admin: number;
  cf_prohibit_id: string;
  cf_prohibit_email: string;
  cf_new_del: number;
  cf_memo_del: number;
  cf_visit_del: number;
  cf_popular_del: number;
  cf_optimize_date: Date;
  cf_use_member_icon: number;
  cf_member_icon_size: number;
  cf_member_icon_width: number;
  cf_member_icon_height: number;
  cf_member_img_size: number;
  cf_member_img_width: number;
  cf_member_img_height: number;
  cf_login_minutes: number;
  cf_image_extension: string;
  cf_flash_extension: string;
  cf_movie_extension: string;
  cf_formmail_is_member: number;
  cf_page_rows: number;
  cf_mobile_page_rows: number;
  cf_visit: string;
  cf_max_po_id: number;
  cf_stipulation: string;
  cf_privacy: string;
  cf_open_modify: number;
  cf_memo_send_point: number;
  cf_mobile_new_skin: string;
  cf_mobile_search_skin: string;
  cf_mobile_connect_skin: string;
  cf_mobile_faq_skin: string;
  cf_mobile_member_skin: string;
  cf_captcha_mp3: string;
  cf_editor: string;
  cf_captcha: string;
}
export interface member {
  mb_id: string
  mb_password: string
  mb_level: number
  mb_1?: string
  mb_2?: string
  mb_3?: string
  mb_4?: string
  mb_5?: string
  mb_6?: string
  mb_7?: string
  mb_8?: string
  mb_9?: string
  mb_10?: string
  mb_addr1?: string
  mb_addr2?: string
  mb_addr3?: string
  mb_addr_jibeon?: string
  mb_adult?: string
  mb_birth?: string
  mb_certify?: string
  mb_datetime?: string
  mb_dupinfo?: string
  mb_email?: string
  mb_email_certify?: string
  mb_email_certify2?: string
  mb_homepage?: string
  mb_hp?: string
  mb_intercept_date?: string
  mb_ip?: string
  mb_leave_date?: string
  mb_login_ip?: string
  mb_lost_certify?: string
  mb_mailling?: number
  mb_memo?: string
  mb_memo_call?: string
  mb_memo_cnt?: number
  mb_name?: string
  mb_nick?: string
  mb_nick_date?: string
  mb_no?: number
  mb_open?: number
  mb_open_date?: number
  mb_point?: number
  mb_profile?: string
  mb_recommend?: string
  mb_scrap_cnt?: number
  mb_sex?: string
  mb_signature?: string
  mb_sms?: number
  mb_tel?: string
  mb_today_login?: string
  mb_zip1?: string
  mb_zip2?: string
}

export interface latest {
  bo_subject: string
  url: string
  list: Array<Latest_List>
}

interface Latest_List {
  wr_id: number;
  wr_num: number;
  wr_reply: string;
  wr_parent: number;
  wr_is_comment: number;
  wr_comment: number;
  wr_comment_reply: string;
  ca_name: string;
  wr_option: string;
  wr_subject: string;
  wr_content: string;
  wr_seo_title: string;
  wr_link1: string;
  wr_link2: string;
  wr_link1_hit: number;
  wr_link2_hit: number;
  wr_hit: number;
  wr_good: number;
  wr_nogood: number;
  mb_id: string;
  wr_name: string;
  wr_email: string;
  wr_homepage: string;
  wr_datetime: string;
  wr_file: number;
  wr_last: string;
  wr_ip: string;
  wr_facebook_user: string;
  wr_twitter_user: string;
  wr_1: string;
  wr_2: string;
  wr_3: string;
  wr_4: string;
  wr_5: string;
  wr_6: string;
  wr_7: string;
  wr_8: string;
  wr_9: string;
  wr_10: string;
  file: any;
  is_notice: boolean;
  subject: string;
  comment_cnt: string;
  datetime: string;
  datetime2: string;
  last: string;
  last2: string;
  name: string;
  reply: number;
  icon_reply: string;
  icon_link: string;
  ca_name_href: string;
  href: string;
  comment_href: string;
  icon_new: string;
  icon_hot: string;
  icon_secret: string;
  link: { [key: string]: number | null | string };
  link_href: { [key: string]: number | null | string };
  link_hit: { [key: string]: number | null | string };
  first_file_thumb: FirstFileThumb;
  bo_table: string;
  icon_file: string;
}

interface FirstFileThumb {
  bf_file: string;
  bf_content: string;
}

export interface ViewParent {
  title: string,
  list_href: string;
  prev_wr_subject: string;
  prev_href: string;
  prev_wr_date: string;
  next_wr_subject: string;
  next_href: string;
  next_wr_date: string;
  write_href: string;
  board: Board;
  good_href: string;
  reply_href: string;
  nogood_href: string;
  update_href: string;
  delete_href: string;
  copy_href: string;
  move_href: string;
  scrap_href: string;
  search_href: string;
  is_signature: boolean;
  signature: string;
  view: View;
  link: any;
}

export interface View {
  wr_id: number;
  wr_num: number;
  wr_reply: string;
  wr_parent: number;
  wr_is_comment: number;
  wr_comment: number;
  wr_comment_reply: string;
  ca_name: string;
  wr_option: string;
  wr_subject: string;
  wr_content: string;
  wr_seo_title: string;
  wr_link1: string;
  wr_link2: string;
  wr_link1_hit: number;
  wr_link2_hit: number;
  wr_hit: number;
  wr_good: number;
  wr_nogood: number;
  mb_id: string;
  wr_password: string;
  wr_name: string;
  wr_email: string;
  wr_homepage: string;
  wr_datetime: string;
  wr_file: number;
  wr_last: string;
  wr_ip: string;
  wr_facebook_user: string;
  wr_twitter_user: string;
  wr_1: string;
  wr_2: string;
  wr_3: string;
  wr_4: string;
  wr_5: string;
  wr_6: string;
  wr_7: string;
  wr_8: string;
  wr_9: string;
  wr_10: string;
  is_notice: boolean;
  subject: string;
  comment_cnt: string;
  datetime: string;
  datetime2: string;
  last: string;
  last2: string;
  name: string;
  reply: number;
  icon_reply: string;
  icon_link: string;
  ca_name_href: string;
  href: string;
  comment_href: string;
  icon_new: string;
  icon_hot: string;
  icon_secret: string;
  link: { [key: string]: number | null | string };
  link_href: { [key: string]: number | null | string };
  link_hit: { [key: string]: number | null | string };
  file: any
  icon_file: string;
  content: string;
  rich_content: string;
}

export interface FileObject {
  href: string;
  download: number;
  path: string;
  size: string;
  datetime: string;
  source: string;
  bf_content: string;
  content: string;
  view: null | string;
  file: string;
  image_width: number;
  image_height: number;
  image_type: number;
  bf_fileurl: string;
  bf_thumburl: string;
  bf_storage: string;
}


export interface ListParent {
  title: string;
  category_option: category_option[]
  write_pages: write_pages;
  total_page: number;
  total_count: number;
  page: number;
  write_href: string;
  bo_gallery_cols: number;
  td_width: number;
  page_rows: number
  list: Array<List>;
  qstr: string;
  is_category: boolean;
  admin_href: string;
  rss_href: string;
  is_checkbox: boolean;
  colspan: number;
  is_good: boolean;
  is_nogood: boolean;
}
export interface write_pages {
  //map(arg0: (row: any, i: any) => JSX.Element): import("react").ReactNode;
  [key: number]: {
    name: string,
    url: string,
    active?: boolean
  }
}

export interface List {
  wr_id: number;
  wr_num: number;
  wr_reply: string;
  wr_parent: number;
  wr_is_comment: number;
  wr_comment: number;
  wr_comment_reply: string;
  ca_name: string;
  wr_option: string;
  wr_subject: string;
  wr_content: string;
  wr_seo_title: string;
  wr_link1: string;
  wr_link2: string;
  wr_link1_hit: number;
  wr_link2_hit: number;
  wr_hit: number;
  wr_good: number;
  wr_nogood: number;
  mb_id: string;
  wr_password: string;
  wr_name: string;
  wr_email: string;
  wr_homepage: string;
  wr_datetime: string;
  wr_file: number;
  wr_last: string;
  wr_ip: string;
  wr_facebook_user: string;
  wr_twitter_user: string;
  wr_1: string;
  wr_2: string;
  wr_3: string;
  wr_4: string;
  wr_5: string;
  wr_6: string;
  wr_7: string;
  wr_8: string;
  wr_9: string;
  wr_10: string;
  is_notice: boolean;
  subject: string;
  comment_cnt: number;
  datetime: string;
  datetime2: string;
  last: string;
  last2: string;
  name: string;
  reply: number;
  icon_reply: string;
  icon_link: string;
  ca_name_href: string;
  href: string;
  comment_href: string;
  icon_new: string;
  icon_hot: string;
  icon_secret: string;
  link: { [key: string]: number | null | string };
  link_href: { [key: string]: number | null | string };
  link_hit: { [key: string]: number | null | string };
  file: any;
  num: number;
  icon_file?: string;
  thumb: {
    src: string,
    ori: string,
    alt: string,
  }
}

export interface CommentParent {
  cmt_amt: number;
  is_comment_write: boolean;
  comment_min: number;
  comment_max: number;
  captcha_html: any;
  board: Board;
  comment_common_url: string;
  list: Array<CmtList>;
}


export interface CmtList {
  wr_id: number;
  wr_num: number;
  wr_reply: string;
  wr_parent: number;
  wr_is_comment: number;
  wr_comment: number;
  wr_comment_reply: string;
  ca_name: string;
  wr_option: string;
  wr_subject: string;
  wr_seo_title: string;
  wr_link1: string;
  wr_link2: string;
  wr_link1_hit: number;
  wr_link2_hit: number;
  wr_hit: number;
  wr_good: number;
  wr_nogood: number;
  mb_id: string;
  wr_name: string;
  wr_email: string;
  wr_homepage: string;
  wr_datetime: string;
  wr_file: number;
  wr_last: string;
  wr_ip: string;
  wr_facebook_user: string;
  wr_twitter_user: string;
  wr_1: string;
  wr_2: string;
  wr_3: string;
  wr_4: string;
  wr_5: string;
  wr_6: string;
  wr_7: string;
  wr_8: string;
  wr_9: string;
  wr_10: string;
  name: string;
  content1: string;
  content: string;
  url: string;
  datetime: string;
  ip: string;
  is_reply: boolean;
  is_edit: boolean;
  is_del: boolean;
  is_permission: boolean;
  token: string;
  comment_id: number;
  cmt_depth: number;
  comment: string;
  cmt_sv: number;
  c_reply_href: string;
  c_edit_href: string;
  is_comment_reply_edit: number;
}

export interface qstr {
  sca: string
  sfl: string
  stx: string
  sst: string
  sod: string
  sop: string
  spt: string
  page: string
  w: string
  wr_id: string
  bo_table: string
  gr_id: string
  url: string
  onetable?: string,
}

export interface WriteParent {
  title: string,
  bo_table: string;
  wr_id: number;
  ca_name: string | object
  w: string;
  write: Write;
  is_admin: string,
  is_member: boolean;
  is_guest: boolean;
  write_min: number;
  write_max: number;
  is_notice: boolean;
  is_html: boolean;
  is_secret: number;
  is_mail: boolean;
  is_name: boolean;
  is_password: boolean;
  is_email: boolean;
  is_homepage: boolean;
  is_category: boolean;
  is_link: boolean;
  is_file: boolean;
  is_file_content: boolean;
  captcha_html: any;
  is_use_captcha: number;
  is_dhtml_editor: boolean;
  editor_content_js: string;
  autosave_count: number;
  uid: string
  width: string
  notice_checked: string
  html_checked: string
  html_value: string
  secret_checked: string
  recv_email_checked: string
  name: string
  email: string
  homepage: string
  option: string
  category_option: null | { [key: string]: category_option }[];
  subject: string
  file: any
  file_count: number
  link_count: number
  upload_max_filesize: string
}
export interface Write {
  wr_id: number;
  wr_num: number;
  wr_reply: string;
  wr_parent: number;
  wr_is_comment: number;
  wr_comment: number;
  wr_comment_reply: string;
  ca_name: string;
  wr_option: string;
  wr_subject: string;
  wr_content: string;
  wr_seo_title: string;
  wr_link1: string;
  wr_link2: string;
  wr_link1_hit: number;
  wr_link2_hit: number;
  wr_hit: number;
  wr_good: number;
  wr_nogood: number;
  mb_id: string;
  wr_password: string;
  wr_name: string;
  wr_email: string;
  wr_homepage: string;
  wr_datetime: string;
  wr_file: number;
  wr_last: string;
  wr_ip: string;
  wr_facebook_user: string;
  wr_twitter_user: string;
  wr_1: string;
  wr_2: string;
  wr_3: string;
  wr_4: string;
  wr_5: string;
  wr_6: string;
  wr_7: string;
  wr_8: string;
  wr_9: string;
  wr_10: string;
}

export interface category_option {
  value: string
  selected: boolean
  name: string
}

//66
export interface str_board_list {
  table: string;
  bo_subject: string;
  cnt_cmt: number;
  class: string;
}

export interface movelist {
  gr_subject: string
  bo_subject: string,
  bo_table: string,
}
export interface move {
  list: movelist[],
  wr_id_list: string
}
export const MoveInitial: move = {
  list: [{
    gr_subject: "",
    bo_subject: "",
    bo_table: "",
  }],
  wr_id_list: "",
}
export const WriteInitial = {
  wr_id: 0,
  wr_num: 0,
  wr_reply: "",
  wr_parent: 0,
  wr_is_comment: 0,
  wr_comment: 0,
  wr_comment_reply: "",
  ca_name: "",
  wr_option: "",
  wr_subject: "",
  wr_content: "",
  wr_seo_title: "",
  wr_link1: "",
  wr_link2: "",
  wr_link1_hit: 0,
  wr_link2_hit: 0,
  wr_hit: 0,
  wr_good: 0,
  wr_nogood: 0,
  mb_id: "",
  wr_password: "",
  wr_name: "",
  wr_email: "",
  wr_homepage: "",
  wr_datetime: "",
  wr_file: 0,
  wr_last: "",
  wr_ip: "",
  wr_facebook_user: "",
  wr_twitter_user: "",
  wr_1: "",
  wr_2: "",
  wr_3: "",
  wr_4: "",
  wr_5: "",
  wr_6: "",
  wr_7: "",
  wr_8: "",
  wr_9: "",
  wr_10: "",
}
export const WriteParentInitial: WriteParent = {
  title: '',
  bo_table: "",
  wr_id: 0,
  w: "",
  ca_name: "",
  write: WriteInitial,
  is_admin: "",
  is_member: false,
  is_guest: false,
  write_min: 0,
  write_max: 0,
  is_notice: false,
  is_html: false,
  is_secret: 0,
  is_mail: false,
  is_name: false,
  is_password: false,
  is_email: false,
  is_homepage: false,
  is_category: false,
  is_link: false,
  is_file: false,
  is_file_content: false,
  captcha_html: "",
  is_use_captcha: 0,
  is_dhtml_editor: false,
  editor_content_js: "",
  autosave_count: 0,
  uid: "",
  width: "100%",
  notice_checked: "",
  html_checked: "",
  html_value: "",
  secret_checked: "",
  recv_email_checked: "",
  name: "",
  email: "",
  homepage: "",
  option: "",
  category_option: null,
  subject: "",
  file: "",
  upload_max_filesize: "",
  file_count: 0,
  link_count: 0,
}

export const qstrInitial = {
  sca: "",
  sfl: "",
  stx: "",
  sst: "",
  sod: "",
  sop: "",
  spt: "",
  page: "",
  w: "",
  wr_id: "",
  bo_table: "",
  gr_id: "",
  url: "",
}
export const CmtListInitial = {
  wr_id: 0,
  wr_num: 0,
  wr_reply: "",
  wr_parent: 0,
  wr_is_comment: 0,
  wr_comment: 0,
  wr_comment_reply: "",
  ca_name: "",
  wr_option: "",
  wr_subject: "",
  wr_seo_title: "",
  wr_link1: "",
  wr_link2: "",
  wr_link1_hit: 0,
  wr_link2_hit: 0,
  wr_hit: 0,
  wr_good: 0,
  wr_nogood: 0,
  mb_id: "",
  wr_name: "",
  wr_email: "",
  wr_homepage: "",
  wr_datetime: "",
  wr_file: 0,
  wr_last: "",
  wr_ip: "",
  wr_facebook_user: "",
  wr_twitter_user: "",
  wr_1: "",
  wr_2: "",
  wr_3: "",
  wr_4: "",
  wr_5: "",
  wr_6: "",
  wr_7: "",
  wr_8: "",
  wr_9: "",
  wr_10: "",
  name: "",
  content1: "",
  content: "",
  url: "",
  datetime: "",
  ip: "",
  is_reply: false,
  is_edit: false,
  is_del: false,
  is_permission: false,
  token: "",
  comment_id: 0,
  cmt_depth: 0,
  comment: "",
  cmt_sv: 0,
  c_reply_href: "",
  c_edit_href: "",
  is_comment_reply_edit: 0,
}


export const ListInitial: List = {
  wr_id: 0,
  wr_num: 0,
  wr_reply: "",
  wr_parent: 0,
  wr_is_comment: 0,
  wr_comment: 0,
  wr_comment_reply: "",
  ca_name: "",
  wr_option: "",
  wr_subject: "",
  wr_content: "",
  wr_seo_title: "",
  wr_link1: "",
  wr_link2: "",
  wr_link1_hit: 0,
  wr_link2_hit: 0,
  wr_hit: 0,
  wr_good: 0,
  wr_nogood: 0,
  mb_id: "",
  wr_password: "",
  wr_name: "",
  wr_email: "",
  wr_homepage: "",
  wr_datetime: "",
  wr_file: 0,
  wr_last: "",
  wr_ip: "",
  wr_facebook_user: "",
  wr_twitter_user: "",
  wr_1: "",
  wr_2: "",
  wr_3: "",
  wr_4: "",
  wr_5: "",
  wr_6: "",
  wr_7: "",
  wr_8: "",
  wr_9: "",
  wr_10: "",
  is_notice: false,
  subject: "",
  comment_cnt: 0,
  datetime: "",
  datetime2: "",
  last: "",
  last2: "",
  name: "",
  reply: 0,
  icon_reply: "",
  icon_link: "",
  ca_name_href: "",
  href: "",
  comment_href: "",
  icon_new: "",
  icon_hot: "",
  icon_secret: "",
  link: { "1": null, "2": null },
  link_href: { "1": null, "2": null },
  link_hit: { "1": null, "2": null },
  file: "",
  num: 0,
  icon_file: "",
  thumb: {
    src: '',
    ori: '',
    alt: '',
  }
}

export const BoardInitial = {
  bo_table: "",
  bo_skin: "",
  bo_mobile_skin: "",
  bo_upload_count: 0,
  bo_use_dhtml_editor: 0,
  bo_subject: "",
  bo_image_width: 0,
  gr_id: "",
  bo_mobile_subject: "",
  bo_device: "",
  bo_admin: "",
  bo_list_level: 0,
  bo_read_level: 0,
  bo_write_level: 0,
  bo_reply_level: 0,
  bo_comment_level: 0,
  bo_upload_level: 0,
  bo_download_level: 0,
  bo_html_level: 0,
  bo_link_level: 0,
  bo_count_delete: 0,
  bo_count_modify: 0,
  bo_read_point: 0,
  bo_write_point: 0,
  bo_comment_point: 0,
  bo_download_point: 0,
  bo_use_category: 0,
  bo_category_list: "",
  bo_use_sideview: 0,
  bo_use_file_content: 0,
  bo_use_secret: 0,
  bo_select_editor: "",
  bo_use_rss_view: 0,
  bo_use_good: 0,
  bo_use_nogood: 0,
  bo_use_name: 0,
  bo_use_signature: 0,
  bo_use_ip_view: 0,
  bo_use_list_view: 0,
  bo_use_list_file: 0,
  bo_use_list_content: 0,
  bo_table_width: 0,
  bo_subject_len: 0,
  bo_mobile_subject_len: 0,
  bo_page_rows: 0,
  bo_mobile_page_rows: 0,
  bo_new: 0,
  bo_hot: 0,
  bo_include_head: "",
  bo_include_tail: "",
  bo_content_head: "",
  bo_mobile_content_head: "",
  bo_content_tail: "",
  bo_mobile_content_tail: "",
  bo_insert_content: "",
  bo_gallery_cols: 0,
  bo_gallery_width: 0,
  bo_gallery_height: 0,
  bo_mobile_gallery_width: 0,
  bo_mobile_gallery_height: 0,
  bo_upload_size: 0,
  bo_reply_order: 0,
  bo_use_search: 0,
  bo_order: 0,
  bo_count_write: 0,
  bo_count_comment: 0,
  bo_write_min: 0,
  bo_write_max: 0,
  bo_comment_min: 0,
  bo_comment_max: 0,
  bo_notice: "",
  bo_use_email: 0,
  bo_use_cert: "",
  bo_use_sns: 0,
  bo_use_captcha: 0,
  bo_sort_field: "",
  bo_1_subj: "",
  bo_2_subj: "",
  bo_3_subj: "",
  bo_4_subj: "",
  bo_5_subj: "",
  bo_6_subj: "",
  bo_7_subj: "",
  bo_8_subj: "",
  bo_9_subj: "",
  bo_10_subj: "",
  bo_1: "",
  bo_2: "",
  bo_3: "",
  bo_4: "",
  bo_5: "",
  bo_6: "",
  bo_7: "",
  bo_8: "",
  bo_9: "",
  bo_10: "",
}

export const write_pageInitial = [
  { name: "", url: "" }
]
export const category_optionInitial = {
  value: "",
  selected: false,
  name: "",
}
export const ListParentInitial: ListParent = {
  title: '',
  category_option: [],
  write_pages: write_pageInitial,
  total_page: 0,
  total_count: 0,
  page: 0,
  write_href: "",
  bo_gallery_cols: 0,
  td_width: 0,
  list: [ListInitial],
  qstr: "",
  page_rows: 0,
  is_category: false,
  admin_href: "",
  rss_href: "",
  is_checkbox: false,
  colspan: 0,
  is_good: false,
  is_nogood: false,
}
export const ViewInitial = {
  wr_id: 0,
  wr_num: 0,
  wr_reply: "",
  wr_parent: 0,
  wr_is_comment: 0,
  wr_comment: 0,
  wr_comment_reply: "",
  ca_name: "",
  wr_option: "",
  wr_subject: "",
  wr_content: "",
  wr_seo_title: "",
  wr_link1: "",
  wr_link2: "",
  wr_link1_hit: 0,
  wr_link2_hit: 0,
  wr_hit: 0,
  wr_good: 0,
  wr_nogood: 0,
  mb_id: "",
  wr_password: "",
  wr_name: "",
  wr_email: "",
  wr_homepage: "",
  wr_datetime: "",
  wr_file: 0,
  wr_last: "",
  wr_ip: "",
  wr_facebook_user: "",
  wr_twitter_user: "",
  wr_1: "",
  wr_2: "",
  wr_3: "",
  wr_4: "",
  wr_5: "",
  wr_6: "",
  wr_7: "",
  wr_8: "",
  wr_9: "",
  wr_10: "",
  is_notice: false,
  subject: "",
  comment_cnt: "",
  datetime: "",
  datetime2: "",
  last: "",
  last2: "",
  name: "",
  reply: 0,
  icon_reply: "",
  icon_link: "",
  ca_name_href: "",
  href: "",
  comment_href: "",
  icon_new: "",
  icon_hot: "",
  icon_secret: "",
  link: { "1": null, "2": null },
  link_href: { "1": null, "2": null },
  link_hit: { "1": null, "2": null },
  file: "",
  icon_file: "",
  content: "",
  rich_content: "",
}
export const ViewParentInitial: ViewParent = {
  title: '',
  list_href: "",
  prev_wr_subject: "",
  prev_href: "",
  prev_wr_date: "",
  next_wr_subject: "",
  next_href: "",
  next_wr_date: "",
  write_href: "",
  board: BoardInitial,
  good_href: "",
  reply_href: "",
  nogood_href: "",
  update_href: "",
  delete_href: "",
  copy_href: "",
  move_href: "",
  scrap_href: "",
  search_href: "",
  is_signature: false,
  signature: "",
  view: ViewInitial,
  link: "",
}
export const CmtParentInitial = {
  cmt_amt: 0,
  is_comment_write: false,
  comment_min: 0,
  comment_max: 0,
  captcha_html: "",
  board: BoardInitial,
  comment_common_url: "",
  list: [CmtListInitial],
}
export const GroupSelectInitial = {
  name: "",
  value: "",
  selected: false,
}
export const SearchListInitial = {
  ca_name: '',
  content: '',
  href: '',
  mb_id: '',
  name: '',
  subject: '',
  wr_1: '',
  wr_2: '',
  wr_3: '',
  wr_4: '',
  wr_5: '',
  wr_6: '',
  wr_7: '',
  wr_8: '',
  wr_9: '',
  wr_10: '',
  wr_comment: 0,
  wr_comment_reply: '',
  wr_content: '',
  wr_datetime: '',
  wr_email: '',
  wr_facebook_user: '',
  wr_file: 0,
  wr_good: 0,
  wr_hit: 0,
  wr_homepage: '',
  wr_id: 0,
  wr_is_comment: 0,
  wr_last: '',
  wr_link1: '',
  wr_link1_hit: 0,
  wr_link2: '',
  wr_link2_hit: 0,
  wr_name: '',
  wr_nogood: 0,
  wr_num: 0,
  wr_option: '',
  wr_parent: 0,
  wr_reply: '',
  wr_seo_title: '',
  wr_subject: '',
  wr_twitter_user: '',
}
export const str_board_listInitial = {
  table: "",
  bo_subject: "",
  cnt_cmt: 0,
  class: "",
}
export interface SearchParent {
  str_board_list: str_board_list[]
  group_select: GroupSelect[];
  write_pages: write_pages;
  list: Array<SearchList>;
  total_count: number
  page_rows: number
  page: number
}

export const SearchListParent = {
  str_board_list: [str_board_listInitial],
  group_select: [GroupSelectInitial],
  write_pages: write_pageInitial,
  list: [SearchListInitial],
  total_count: 0,
  page_rows: 0,
  page: 0,
}
export interface bbsObject {
  bo_table: String,
  wr_id?: Number,
  wr_seo_title?: String,
  qstr?: String,
  rows?: 10,
  subject_len?: 3
}
export interface searchObject {
  list: Array<bbsObject>
}
export interface actionObject {
  action: string
  data?: {
    bo_table?: string
    wr_id?: number
  }
}
export interface bbs_view {
  reply_href?: string
  prev_wr_subject?: string
  prev_href?: string
  prev_wr_date?: string
  pnext_wr_subject?: string
  pnext_href?: string
  pnext_wr_date?: string
  next_href?: string
  write_href?: string
  board?: Board
  is_signature?: boolean
  signature?: string
  view?: {
    ca_name?: string
    ca_name_href?: string
    comment_cnt?: number
    comment_href?: string
    content?: string
    datetime?: string
    datetime2?: string
    file?: {
      count?: number
    }
    href?: string
    icon_hot?: string
    icon_link?: string
    icon_new?: string
    icon_reply?: string
    icon_secret?: string
    is_notice?: boolean
    last?: string
    last2?: string
    link?: object
    link_hit?: object
    link_href?: object
    mb_id?: string
    name?: string
    reply?: number
    rich_content?: string
    subject?: string
    wr_1?: string
    wr_2?: string
    wr_3?: string
    wr_4?: string
    wr_5?: string
    wr_6?: string
    wr_7?: string
    wr_8?: string
    wr_9?: string
    wr_10?: string
    wr_comment?: number
    wr_comment_reply?: string
    wr_content?: string
    wr_datetime?: string
    wr_email?: string
    wr_facebook_user?: string
    wr_file?: number
    wr_good?: number
    wr_hit?: number
    wr_homepage?: string
    wr_id?: number
    wr_ip?: string
    wr_is_comment?: number
    wr_last?: string
    wr_link1?: string
    wr_link1_hit?: number
    wr_link2?: string
    wr_link2_hit?: number
    wr_name?: string
    wr_nogood?: number
    wr_num?: number
    wr_option?: string
    wr_parent?: number
    wr_password?: string
    wr_reply?: string
    wr_seo_title?: string
    wr_subject?: string
    wr_twitter_user?: string
  }
}
export interface bbs_list {
  admin_href?: string,
  bo_gallery_cols?: number,
  is_category?: boolean,
  is_checkbox?: boolean,
  list?: any,
  page?: number,
  total_count?: number,
  total_page?: number,
  write_pages?: object,
  qstr?: string,
  rss_href?: string,
  td_width?: number,
  write_href?: string,
}
export interface APIObject {
  status: Number
  message: String,
  data: any,
}
export interface member {
  mb_id: string
  mb_password: string
  mb_level: number
  mb_1?: string
  mb_2?: string
  mb_3?: string
  mb_4?: string
  mb_5?: string
  mb_6?: string
  mb_7?: string
  mb_8?: string
  mb_9?: string
  mb_10?: string
  mb_addr1?: string
  mb_addr2?: string
  mb_addr3?: string
  mb_addr_jibeon?: string
  mb_adult?: string
  mb_birth?: string
  mb_certify?: string
  mb_datetime?: string
  mb_dupinfo?: string
  mb_email?: string
  mb_email_certify?: string
  mb_email_certify2?: string
  mb_homepage?: string
  mb_hp?: string
  mb_intercept_date?: string
  mb_ip?: string
  mb_leave_date?: string
  mb_login_ip?: string
  mb_lost_certify?: string
  mb_mailling?: number
  mb_memo?: string
  mb_memo_call?: string
  mb_memo_cnt?: number
  mb_name?: string
  mb_nick?: string
  mb_nick_date?: string
  mb_no?: number
  mb_open?: number
  mb_point?: number
  mb_profile?: string
  mb_recommend?: string
  mb_scrap_cnt?: number
  mb_sex?: string
  mb_signature?: string
  mb_sms?: number
  mb_tel?: string
  mb_today_login?: string
  mb_zip1?: string
  mb_zip2?: string
}
export interface view_comment {
  comment_max?: number
  comment_min?: number
  is_comment_write?: boolean
  captcha_html?: any,
  board?: Board,
  list?: any
}
export interface Board {
  bo_table?: string;
  bo_skin?: string;
  bo_mobile_skin?: string;
  bo_upload_count?: number;
  bo_use_dhtml_editor?: number;
  bo_subject?: string;
  bo_image_width?: number;
  gr_id?: string;
  bo_mobile_subject?: string;
  bo_device?: string;
  bo_admin?: string;
  bo_list_level?: number;
  bo_read_level?: number;
  bo_write_level?: number;
  bo_reply_level?: number;
  bo_comment_level?: number;
  bo_upload_level?: number;
  bo_download_level?: number;
  bo_html_level?: number;
  bo_link_level?: number;
  bo_count_delete?: number;
  bo_count_modify?: number;
  bo_read_point?: number;
  bo_write_point?: number;
  bo_comment_point?: number;
  bo_download_point?: number;
  bo_use_category?: number;
  bo_category_list?: string;
  bo_use_sideview?: number;
  bo_use_file_content?: number;
  bo_use_secret?: number;
  bo_select_editor?: string;
  bo_use_rss_view?: number;
  bo_use_good?: number;
  bo_use_nogood?: number;
  bo_use_name?: number;
  bo_use_signature?: number;
  bo_use_ip_view?: number;
  bo_use_list_view?: number;
  bo_use_list_file?: number;
  bo_use_list_content?: number;
  bo_table_width?: number;
  bo_subject_len?: number;
  bo_mobile_subject_len?: number;
  bo_page_rows?: number;
  bo_mobile_page_rows?: number;
  bo_new?: number;
  bo_hot?: number;
  bo_include_head?: string;
  bo_include_tail?: string;
  bo_content_head?: string;
  bo_mobile_content_head?: string;
  bo_content_tail?: string;
  bo_mobile_content_tail?: string;
  bo_insert_content?: string;
  bo_gallery_cols?: number;
  bo_gallery_width?: number;
  bo_gallery_height?: number;
  bo_mobile_gallery_width?: number;
  bo_mobile_gallery_height?: number;
  bo_upload_size?: number;
  bo_reply_order?: number;
  bo_use_search?: number;
  bo_order?: number;
  bo_count_write?: number;
  bo_count_comment?: number;
  bo_write_min?: number;
  bo_write_max?: number;
  bo_comment_min?: number;
  bo_comment_max?: number;
  bo_notice?: string;
  bo_use_email?: number;
  bo_use_cert?: string;
  bo_use_sns?: number;
  bo_use_captcha?: number;
  bo_sort_field?: string;
  bo_1_subj?: string;
  bo_2_subj?: string;
  bo_3_subj?: string;
  bo_4_subj?: string;
  bo_5_subj?: string;
  bo_6_subj?: string;
  bo_7_subj?: string;
  bo_8_subj?: string;
  bo_9_subj?: string;
  bo_10_subj?: string;
  bo_1?: string;
  bo_2?: string;
  bo_3?: string;
  bo_4?: string;
  bo_5?: string;
  bo_6?: string;
  bo_7?: string;
  bo_8?: string;
  bo_9?: string;
  bo_10?: string;
}

export interface WritePage {
  name?: number | string;
  active?: boolean;
  url?: string;
}
export interface GroupSelect {
  name?: string;
  value?: string;
  selected?: boolean;
}
export interface SearchList {
  ca_name: string
  content: string
  href: string
  mb_id: string
  name: string
  subject: string
  wr_1: string
  wr_2: string
  wr_3: string
  wr_4: string
  wr_5: string
  wr_6: string
  wr_7: string
  wr_8: string
  wr_9: string
  wr_10: string
  wr_comment: number
  wr_comment_reply: string
  wr_content: string
  wr_datetime: string
  wr_email: string
  wr_facebook_user: string
  wr_file: number
  wr_good: number
  wr_hit: number
  wr_homepage: string
  wr_id: number
  wr_is_comment: number
  wr_last: string
  wr_link1: string
  wr_link1_hit: number
  wr_link2: string
  wr_link2_hit: number
  wr_name: string
  wr_nogood: number
  wr_num: number
  wr_option: string
  wr_parent: number
  wr_reply: string
  wr_seo_title: string
  wr_subject: string
  wr_twitter_user: string
}
export const pointlistInitial: PointList = {
  po_id: 0,
  mb_id: '',
  po_datetime: '',
  po_content: '',
  po_point: 0,
  po_use_point: 0,
  po_expired: 0,
  po_expire_date: '',
  po_mb_point: 0,
  po_rel_table: '',
  po_rel_id: '',
  po_rel_action: '',
  point: '',
  sum_point: 0,
}
export const pointInitial: point = {
  list: [pointlistInitial],
  total_count: 0,
  page: 0,
  page_rows: 0
}
export interface point {
  list: PointList[];
  total_count: number;
  page: number;
  page_rows: number;
}
export interface PointList {
  po_id: number;
  mb_id: string;
  po_datetime: string;
  po_content: string;
  po_point: number;
  po_use_point: number;
  po_expired: number;
  po_expire_date: string;
  po_mb_point: number;
  po_rel_table: string;
  po_rel_id: string;
  po_rel_action: string;
  point: string;
  sum_point: number;
}

export const ScrapListInitial: ScrapList = {
  ms_id: 0,
  mb_id: '',
  bo_table: '',
  wr_id: '',
  ms_datetime: '',
  num: 0,
  opener_href: '',
  opener_href_wr_id: '',
  bo_subject: '',
  subject: '',
  del_href: '',
}
export const ScrapInitial: Scrap = {
  list: [ScrapListInitial],
  total_count: 0,
  page: 0,
  page_rows: 0
}
export interface Scrap {
  list: ScrapList[];
  total_count: number;
  page: number;
  page_rows: number;
}
export interface ScrapList {
  ms_id: number;
  mb_id: string;
  bo_table: string;
  wr_id: string;
  ms_datetime: string;
  num: number;
  opener_href: string;
  opener_href_wr_id: string;
  bo_subject: string;
  subject: string;
  del_href: string;
}


export const MemoListInitial: MemoList = {
  me_id: 0,
  me_recv_mb_id: '',
  me_send_mb_id: '',
  me_send_datetime: '',
  me_read_datetime: '',
  me_memo: '',
  me_send_id: 0,
  me_type: '',
  me_send_ip: '',
  mb_id: '',
  mb_nick: '',
  mb_email: '',
  mb_homepage: '',
  name: '',
  send_datetime: '',
  read_datetime: '',
  view_me_id: 0,
  del_token: '',
  kind: '',
}
export const MemoInitial: Memo = {
  list: [MemoListInitial],
  total_count: 0,
  page: 0,
  page_rows: 0
}

export interface Memo {
  list: MemoList[];
  total_count: number;
  page: number;
  page_rows: number;
}

export interface MemoList {
  me_id: number;
  me_recv_mb_id: string;
  me_send_mb_id: string;
  me_send_datetime: string;
  me_read_datetime: string;
  me_memo: string;
  me_send_id: number;
  me_type: string;
  me_send_ip: string;
  mb_id: string;
  mb_nick: string;
  mb_email: string;
  mb_homepage: string;
  name: string;
  send_datetime: string;
  read_datetime: string;
  view_me_id: number;
  del_token: string;
  kind: string;
}
export interface MemoForm {
  content: string
  captcha_html: any,
}
const visitInitial: visit = {
  all: 0,
  max: 0,
  today: 0,
  yesterday: 0,
}
export interface visit {
  all: number
  max: number
  today: number
  yesterday: number
}
export interface Sideview {
  mb_nick: string
  mb_id?: string
  list: {
    intro?: {
      title: string,
      url: string,
    },
    memo?: {
      title: string,
      url: string,
    },
    email?: {
      title: string,
      url: string,
    },
    profile?: {
      title: string,
      url: string,
    },
    search_mb_id?: {
      title: string,
      url: string,
    },
    new?: {
      title: string,
      url: string,
    },
    mb_info?: {
      title: string,
      url: string,
    },
    mb_point?: {
      title: string,
      url: string,
    },
  }
}
export const profileInitial: profile = {
  mb_sideview: {
    mb_nick: '',
    list: {},
  },
  mb_homepage: '',
  mb_reg_after: 0,
  mb_regsiter_join: '',
  mb_last_connect: '',
  mb_profile: '',
  mb_point: 0,
  mb_level: 0
}
export interface profile {
  mb_sideview: Sideview;
  mb_homepage: string | null;
  mb_reg_after: number;
  mb_regsiter_join: string;
  mb_last_connect: string;
  mb_profile: string;
  mb_point: number;
  mb_level: number
}

export interface bbs_new {
  title: string;
  group_select: GroupSelect[];
  list: NewList[];
  write_pages: WritePage[];
  page_rows: number;
  page: number;
  total_count: number;
}
export const NewListInitial: NewList = {
  wr_id: 0,
  wr_num: 0,
  wr_reply: '',
  wr_parent: 0,
  wr_is_comment: 0,
  wr_comment: 0,
  wr_comment_reply: '',
  ca_name: '',
  wr_option: '',
  wr_subject: '',
  wr_seo_title: '',
  wr_link1: '',
  wr_link2: '',
  wr_link1_hit: 0,
  wr_link2_hit: 0,
  wr_hit: 0,
  wr_good: 0,
  wr_nogood: 0,
  mb_id: '',
  wr_name: '',
  wr_email: '',
  wr_homepage: '',
  wr_datetime: '',
  wr_file: 0,
  wr_last: '',
  wr_facebook_user: '',
  wr_twitter_user: '',
  wr_1: '',
  wr_2: '',
  wr_3: '',
  wr_4: '',
  wr_5: '',
  wr_6: '',
  wr_7: '',
  wr_8: '',
  wr_9: '',
  wr_10: '',
  gr_id: '',
  bo_table: '',
  name: '',
  comment: '',
  href: '',
  datetime: '',
  datetime2: '',
  gr_subject: '',
  bo_subject: '',
}
export interface NewList {
  wr_id: number;
  wr_num: number;
  wr_reply: string;
  wr_parent: number;
  wr_is_comment: number;
  wr_comment: number;
  wr_comment_reply: string;
  ca_name: string;
  wr_option: string;
  wr_subject: string;
  wr_seo_title: string;
  wr_link1: string;
  wr_link2: string;
  wr_link1_hit: number;
  wr_link2_hit: number;
  wr_hit: number;
  wr_good: number;
  wr_nogood: number;
  mb_id: string;
  wr_name: string;
  wr_email: string;
  wr_homepage: string;
  wr_datetime: string;
  wr_file: number;
  wr_last: string;
  wr_facebook_user: string;
  wr_twitter_user: string;
  wr_1: string;
  wr_2: string;
  wr_3: string;
  wr_4: string;
  wr_5: string;
  wr_6: string;
  wr_7: string;
  wr_8: string;
  wr_9: string;
  wr_10: string;
  gr_id: string;
  bo_table: string;
  name: string;
  comment: string;
  href: string;
  datetime: string;
  datetime2: string;
  gr_subject: string;
  bo_subject: string;
}
export const bbs_newInitial: bbs_new = {
  title: '',
  group_select: [GroupSelectInitial],
  list: [NewListInitial],
  write_pages: write_pageInitial,
  page_rows: 0,
  page: 0,
  total_count: 0,
}
export interface socialconfig {
  cf_social_login_use: number,
  cf_social_servicelist: string[],
}
export const pollInitial: poll = {
  po_id: 0,
  po_subject: '',
  po_poll1: '',
  po_poll2: '',
  po_poll3: '',
  po_poll4: '',
  po_poll5: '',
  po_poll6: '',
  po_poll7: '',
  po_poll8: '',
  po_poll9: '',
  po_cnt1: 0,
  po_cnt2: 0,
  po_cnt3: 0,
  po_cnt4: 0,
  po_cnt5: 0,
  po_cnt6: 0,
  po_cnt7: 0,
  po_cnt8: 0,
  po_cnt9: 0,
  po_etc: '',
  po_level: 0,
  po_point: 0,
  po_date: '',
  po_ips: '',
  mb_ids: '',
}
export interface poll {
  po_id: number;
  po_subject: string;
  po_poll1: string;
  po_poll2: string;
  po_poll3: string;
  po_poll4: string;
  po_poll5: string;
  po_poll6: string;
  po_poll7: string;
  po_poll8: string;
  po_poll9: string;
  po_cnt1: number;
  po_cnt2: number;
  po_cnt3: number;
  po_cnt4: number;
  po_cnt5: number;
  po_cnt6: number;
  po_cnt7: number;
  po_cnt8: number;
  po_cnt9: number;
  po_etc: string;
  po_level: number;
  po_point: number;
  po_date: string;
  po_ips: string;
  mb_ids: string;
}
// export const user_profileInitial:user_profile = {
//   identifier:0,
//   webSiteURL:'',
//   profileURL:'',
//   photoURL:'',
//   displayName:'',
//   description:'',
//   firstName:'',
//   lastName:'',
//   gender:'',
//   language:'',
//   age:'',
//   birthDay:'',
//   birthMonth:'',
//   birthYear:'',
//   email:'',
//   emailVerified:'',
//   phone:'',
//   address:'',
//   country:'',
//   region:'',
//   city:'',
//   zip:'',
//   job_title:'',
//   organization_name:'',
//   sid:'',
// }
// export interface user_profile {
//   identifier: number;
//   webSiteURL: string;
//   profileURL: string;
//   photoURL: string;
//   displayName: string;
//   description: string;
//   firstName: string;
//   lastName: string;
//   gender: string;
//   language: string;
//   age: string;
//   birthDay: string;
//   birthMonth: string;
//   birthYear: string;
//   email: string;
//   emailVerified: string;
//   phone: string;
//   address: string;
//   country: string;
//   region: string;
//   city: string;
//   zip: string;
//   job_title: string;
//   organization_name: string;
//   sid: string;
// }
export const user_profileInitial:user_profile = {
  user_id:'',
  user_email:'',
  user_name:'',
  user_nick:'',
}
export interface user_profile {
  user_id:string,
  user_email:string,
  user_name:string,
  user_nick:string,
}
export interface Po {
  po_id: number;
  po_subject: string;
  po_poll1: string;
  po_poll2: string;
  po_poll3: string;
  po_poll4: string;
  po_poll5: string;
  po_poll6: string;
  po_poll7: string;
  po_poll8: string;
  po_poll9: string;
  po_cnt1: number;
  po_cnt2: number;
  po_cnt3: number;
  po_cnt4: number;
  po_cnt5: number;
  po_cnt6: number;
  po_cnt7: number;
  po_cnt8: number;
  po_cnt9: number;
  po_etc: string;
  po_level: number;
  po_point: number;
  po_date: string;
  mb_ids: string;
}

export interface po_list {
  content: string;
  cnt: number;
  rate: number;
  bar: number;
  num: number;
}

export interface po_list3 {
  po_id: number;
  date: string;
  subject: string;
}
export interface po_list2 {
  pc_name:  string;
  name:     string;
  idea:     string;
  datetime: string;
  del:      number;
}
export interface poll_result {
  list: { [key: string]: po_list };
  po: Po;
  get_max_cnt: number;
  list2: po_list2[];
  list3: po_list3[];
  captcha_html: any;
}