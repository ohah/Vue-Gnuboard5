<?php
// $dir 을 포함하여 https 또는 http 주소를 반환한다.
require_once 'jwt/autoload.php';
require_once API_PATH.'/bbs/board.php';
require_once API_PATH.'/bbs/email_certify.php';
require_once API_PATH.'/bbs/download.php';
require_once API_PATH.'/bbs/token.php';
require_once API_PATH.'/bbs/good.php';
require_once API_PATH.'/bbs/qa.php';
require_once API_PATH.'/bbs/password.php';
require_once API_PATH.'/bbs/register.php';
require_once API_PATH.'/str_encrypt.php';
require_once API_PATH.'/bbs/write_update.php';
require_once API_PATH.'/bbs/visit_insert.inc.php';
require_once API_PATH.'/bbs/write.php';
require_once API_PATH.'/bbs/view.php';
require_once API_PATH.'/bbs/new.php';
require_once API_PATH.'/bbs/view_comment.php';
require_once API_PATH.'/bbs/write_comment_update.php';
require_once API_PATH.'/bbs/search.php';
require_once API_PATH.'/bbs/scrap.php';
require_once API_PATH.'/bbs/list.php';
require_once API_PATH.'/bbs/profile.php';
require_once API_PATH.'/bbs/delete.php';
require_once API_PATH.'/bbs/move.php';
require_once API_PATH.'/bbs/memo.php';
require_once API_PATH.'/bbs/point.php';
require_once API_PATH.'/bbs/db_table.optimize.php';
require_once API_PATH.'/bbs/content.php';
require_once API_PATH.'/plugin/kcaptcha/kcaptcha.lib.php';
require_once API_PATH.'/lib/latest.lib.php';
require_once API_PATH.'/lib/register.lib.php';
require_once API_PATH.'/lib/popular.lib.php';
require_once API_PATH.'/lib/uri.lib.php';
require_once API_PATH.'/lib/get_data.lib.php';
require_once API_PATH.'/lib/naver_syndi.lib.php';
require_once API_PATH.'/lib/mailer.lib.php';
require_once API_PATH.'/lib/thumbnail.lib.php';

/** 구글 캡챠 */
require_once API_PATH.'/plugin/recaptcha/recaptcha.class.php';
require_once API_PATH.'/plugin/recaptcha/recaptcha.user.lib.php';
require_once API_PATH.'/plugin/recaptcha_inv/recaptcha.class.php';
require_once API_PATH.'/plugin/recaptcha_inv/recaptcha.user.lib.php';
/** 구글 캡챠 */
use Firebase\JWT\JWT;
class Commonlib {
  use thumbnaillib;
  use board;
  use email_certify;
  use good;
  use qa;
  use visit_insert;
  use bbs_new;
  use password;
  use register;
  use write;
  use write_update;
  use view;
  use view_comment;
  use write_comment_update;
  use profile;
  use delete;
  use download;
  use token;
  use db_optimize;
  use search;
  use scrap;
  use content;
  use bbs_list;
  use KCAPTCHA;
  use recaptcha_inv;
  use recapthca;
  use registerlib;
  use pupularlib;
  use urllib;
  use latestlib;
  use naver_syndilib;
  use get_datalib;
  use mailerlib;
  use move;
  use point;
  use memo;
  public $cookiename = 'gnu_jwt';
  public function __construct() {
  }
  // 리퍼러 체크
  public function referer_check($url='') {
    /*
    // 제대로 체크를 하지 못하여 주석 처리함
    global $g5;

    if (!$url)
        $url = G5_URL;

    if (!preg_match("/^http['s']?:\/\/".$_SERVER['HTTP_HOST']."/", $_SERVER['HTTP_REFERER']))
        alert("제대로 된 접근이 아닌것 같습니다.", $url);
    */
  }
  public function escape_trim($field) {
    $str = call_user_func(G5_ESCAPE_FUNCTION, $field);
    return $str;
  }
  // unescape nl 얻기
  public function conv_unescape_nl($str){
    $search = array('\\r', '\r', '\\n', '\n');
    $replace = array('', '', "\n", "\n");

    return str_replace($search, $replace, $str);
  }
  // 관리자인가?
  public function is_admin($mb_id) {
    if (!$mb_id) return '';
    $is_authority = '';
    if ($this->config['cf_admin'] == $mb_id){
      $is_authority = 'super';
    } else if (isset($this->$group['gr_admin']) && ($this->$group['gr_admin'] == $mb_id)){
      $is_authority = 'group';
    } else if (isset($this->$board['bo_admin']) && ($this->$board['bo_admin'] == $mb_id)){
      $is_authority = 'board';
    }
    return $is_authority;
  }

  public function option_selected($value, $selected, $text=''){ 
    $result = array();
    if (!$text) $text = $value;
    $result['value'] = $value;
    $result['text'] = $text;
    if ($value == $selected) {      
      $result['selected'] = $selected;
    }
    return $result;
  }
  public function get_category_option($bo_table='', $ca_name='') {
    global $g5;
    $is_admin = $this->is_admin;
    $board = $this->get_board_db($bo_table);

    $categories = explode("|", $board['bo_category_list'].($is_admin?"|공지":"")); // 구분자가 | 로 되어 있음
    $str = array();
    for ($i=0; $i<count($categories); $i++) {
      $category = trim($categories[$i]);
      if (!$category) continue;
      $str[$i]['value'] = $categories[$i];
      $str[$i]['name'] = $categories[$i];
      if ($category == $ca_name) {
        $str[$i]['selected'] = true;
      }else{
        $str[$i]['selected'] = false;
      }
    }
    return $str;
  }
  public function sql_query($query, $condition=array()) {
    try {
      $stmt = $this->db->prepare($query);
      if($condition) $stmt->execute($condition);
      else $stmt->execute();
      $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $result;
    } catch(PDOException $e) {
      return "error";
      // return $e->getMessage();
    }
  }
  public function sql_result($query, $condition=array()) {
    try {
      $stmt = $this->db->prepare($query);
      if($condition) return $stmt->execute($condition);
      else return $stmt->execute();
    } catch(PDOException $e) {
      // return $e->getMessage();
    }
  }
  public function sql_fetch($query, $condition=array()) {
    try {
      $stmt = $this->db->prepare($query); 
      if($condition) $stmt->execute($condition);
      else $stmt->execute();
      return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
      return $e->getMessage();
    }
  }
  public function msg($msg) {
    return $this->data_encode(array('msg'=>$msg));
  }
  public function alert($msg, $url = '') {
    echo $this->data_encode(array('msg'=>$msg, 'url'=> $url));
    exit;
  }
  public function captcha_html($class = '') {
    $config = $this->config;
    if($config['cf_captcha'] == 'kcaptcha') {
      return $this->kcaptcha_html($class);
    }else if($config['cf_captcha'] == 'recaptcha') {
    }else if($config['cf_captcha'] == 'recaptcha_inv') {
    }
  }
  public function chk_captcha() {
    $config = $this->config;
    if($config['cf_captcha'] == 'kcaptcha') {
      return $this->chk_kcaptcha();
    }else if($config['cf_captcha'] == 'recaptcha') {
    }else if($config['cf_captcha'] == 'recaptcha_inv') {
    }
  }
  // 1:1문의 설정로드
  public function get_qa_config($fld='*', $is_cache=false) {
    global $g5;
    static $cache = array();

    if( $is_cache && !empty($cache) ){
      return $cache;
    }
    $sql = " select * from {$g5['qa_config_table']} ";
    $cache = run_replace('get_qa_config', $this->sql_fetch($sql));

    return $cache;
  }

  // view_file_link() 함수에서 넘겨진 이미지를 보이게 합니다.
  // {img:0} ... {img:n} 과 같은 형식
  public function view_image($view, $number, $attribute) {
    if ($view['file'][$number]['view'])
      return preg_replace("/>$/", " $attribute>", $view['file'][$number]['view']);
    else
      //return "{".$number."번 이미지 없음}";
      return "";
  }
  // 파일을 보이게 하는 링크 (이미지, 플래쉬, 동영상)
  public function view_file_link($file, $width, $height, $content='') {
    global $g5;
    static $ids;
    $config = $this->config;
    $board = $this->board;
    if (!$file) return;
    $ids++;
    // 파일의 폭이 게시판설정의 이미지폭 보다 크다면 게시판설정 폭으로 맞추고 비율에 따라 높이를 계산
    if ($width > $board['bo_image_width'] && $board['bo_image_width']) {
      $rate = $board['bo_image_width'] / $width;
      $width = $board['bo_image_width'];
      $height = (int)($height * $rate);
    }
    // 폭이 있는 경우 폭과 높이의 속성을 주고, 없으면 자동 계산되도록 코드를 만들지 않는다.
    if ($width)
      $attr = ' width="'.$width.'" height="'.$height.'" ';
    else
      $attr = '';
    if (preg_match("/\.({$config['cf_image_extension']})$/i", $file)) {
      $attr_href = G5_BBS_URL.'/view_image.php?bo_table='.$board['bo_table'].'&fn='.urlencode($file);
      $img = '<a href="'.$attr_href.'" target="_blank" class="view_image">';
      $img .= '<img src="'.G5_DATA_URL.'/file/'.$board['bo_table'].'/'.urlencode($file).'" alt="'.$content.'" '.$attr.'>';
      $img .= '</a>';

      return $img;
    }
  }

  // 휴대폰번호의 숫자만 취한 후 중간에 하이픈(-)을 넣는다.
  public function hyphen_hp_number($hp) {
    $hp = preg_replace("/[^0-9]/", "", $hp);
    return preg_replace("/([0-9]{3})([0-9]{3,4})([0-9]{4})$/", "\\1-\\2-\\3", $hp);
  }
  
  // 문자열이 한글, 영문, 숫자, 특수문자로 구성되어 있는지 검사
  public function check_string($str, $options) {
    global $g5;

    $s = '';
    for($i=0;$i<strlen($str);$i++) {
      $c = $str[$i];
      $oc = ord($c);

      // 한글
      if ($oc >= 0xA0 && $oc <= 0xFF) {
        if ($options & G5_HANGUL) {
          $s .= $c . $str[$i+1] . $str[$i+2];
        }
        $i+=2;
      }
      // 숫자
      else if ($oc >= 0x30 && $oc <= 0x39) {
        if ($options & G5_NUMERIC) {
          $s .= $c;
        }
      }
      // 영대문자
      else if ($oc >= 0x41 && $oc <= 0x5A) {
        if (($options & G5_ALPHABETIC) || ($options & G5_ALPHAUPPER)) {
          $s .= $c;
        }
      }
      // 영소문자
      else if ($oc >= 0x61 && $oc <= 0x7A) {
        if (($options & G5_ALPHABETIC) || ($options & G5_ALPHALOWER)) {
          $s .= $c;
        }
      }
      // 공백
      else if ($oc == 0x20) {
        if ($options & G5_SPACE) {
          $s .= $c;
        }
      }
      else {
        if ($options & G5_SPECIAL) {
          $s .= $c;
        }
      }
    }

    // 넘어온 값과 비교하여 같으면 참, 틀리면 거짓
    return ($str == $s);
  }

  // http://htmlpurifier.org/
  // Standards-Compliant HTML Filtering
  // Safe  : HTML Purifier defeats XSS with an audited whitelist
  // Clean : HTML Purifier ensures standards-compliant output
  // Open  : HTML Purifier is open-source and highly customizable
  public function html_purifier($html) {
    $f = file(G5_PLUGIN_PATH.'/htmlpurifier/safeiframe.txt');
    $domains = array();
    foreach($f as $domain){
      // 첫행이 # 이면 주석 처리
      if (!preg_match("/^#/", $domain)) {
        $domain = trim($domain);
        if ($domain)
          array_push($domains, $domain);
      }
    }
    // 내 도메인도 추가
    array_push($domains, $_SERVER['HTTP_HOST'].'/');
    $safeiframe = implode('|', $domains);

    include_once(G5_PLUGIN_PATH.'/htmlpurifier/HTMLPurifier.standalone.php');
    include_once(G5_PLUGIN_PATH.'/htmlpurifier/extend.video.php');
    $config = HTMLPurifier_Config::createDefault();
    // data/cache 디렉토리에 CSS, HTML, URI 디렉토리 등을 만든다.
    $config->set('Cache.SerializerPath', G5_DATA_PATH.'/cache');
    $config->set('HTML.SafeEmbed', false);
    $config->set('HTML.SafeObject', false);
    $config->set('Output.FlashCompat', false);
    $config->set('HTML.SafeIframe', true);
    if( (function_exists('check_html_link_nofollow') && check_html_link_nofollow('html_purifier')) ){
        $config->set('HTML.Nofollow', true);    // rel=nofollow 으로 스팸유입을 줄임
    }
    $config->set('URI.SafeIframeRegexp','%^(https?:)?//('.$safeiframe.')%');
    $config->set('Attr.AllowedFrameTargets', array('_blank'));
    //유튜브, 비메오 전체화면 가능하게 하기
    $config->set('Filter.Custom', array(new HTMLPurifier_Filter_Iframevideo()));
    $purifier = new HTMLPurifier($config);
    return $purifier->purify($html);
  }
  // 3.31
  // HTML SYMBOL 변환
  // &nbsp; &amp; &middot; 등을 정상으로 출력
  public function html_symbol($str) {
    return preg_replace("/\&([a-z0-9]{1,20}|\#[0-9]{0,3});/i", "&#038;\\1;", $str);
  }
  function autosave_count($mb_id){
    global $g5;
    if ($mb_id) {
      $row = $this->sql_fetch("SELECT count(*) as cnt FROM {$g5['autosave_table']} WHERE mb_id = ?",[$mb_id]);
      return (int)$row['cnt'];
    } else {
      return 0;
    }
  }
  public function is_mobile() {
    return preg_match('/'.G5_MOBILE_AGENT.'/i', $_SERVER['HTTP_USER_AGENT']);
  }
  public function cut_str($str, $len, $suffix="…") {
    $arr_str = preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
    $str_len = count($arr_str);
    if ($str_len >= $len) {
      $slice_str = array_slice($arr_str, 0, $len);
      $str = join("", $slice_str);

      return $str . ($str_len > $len ? $suffix : '');
    } else {
      $str = join("", $arr_str);
      return $str;
    }
  }
  // TEXT 형식으로 변환
  public function get_text($str, $html=0, $restore=false) {
    $source[] = "<";
    $target[] = "&lt;";
    $source[] = ">";
    $target[] = "&gt;";
    $source[] = "\"";
    $target[] = "&#034;";
    $source[] = "\'";
    $target[] = "&#039;";
    if($restore) $str = str_replace($target, $source, $str);
    // 3.31
    // TEXT 출력일 경우 &amp; &nbsp; 등의 코드를 정상으로 출력해 주기 위함
    if ($html == 0) {
      $str = $this->html_symbol($str);
    }
    if ($html) {
      $source[] = "\n";
      $target[] = "<br/>";
    }
    return str_replace($source, $target, $str);
  }
  // 쿠키변수 생성
  public function set_cookie($cookie_name, $value, $expire) {
    global $g5;
    setcookie(md5($cookie_name), base64_encode($value), G5_SERVER_TIME + $expire, '/', G5_COOKIE_DOMAIN);
  }


  // 쿠키변수값 얻음
  public function get_cookie($cookie_name) {
    $cookie = md5($cookie_name);
    if (array_key_exists($cookie, $_COOKIE))
      return base64_decode($_COOKIE[$cookie]);
    else
      return "";
  }
  // url에 http:// 를 붙인다
  public function set_http($url) {
    if (!trim($url)) return;

    if (!preg_match("/^(http|https|ftp|telnet|news|mms)\:\/\//i", $url))
      $url = "http://" . $url;

    return $url;
  }

  // 파일의 용량을 구한다.
  public function get_filesize($size) {
    if ($size >= 1048576) {
      $size = number_format($size/1048576, 1) . "M";
    } else if ($size >= 1024) {
      $size = number_format($size/1024, 1) . "K";
    } else {
      $size = number_format($size, 0) . "byte";
    }
    return $size;
  }

  // 게시글에 첨부된 파일을 얻는다. (배열로 반환)
  public function get_file($bo_table, $wr_id) {
    global $g5;
    $file = array();
    // $file['count'] = 0;
    $sql = "SELECT * FROM {$g5['board_file_table']} WHERE bo_table = ? AND wr_id = ? ORDER BY bf_no";
    $result = $this->sql_query($sql, [$bo_table, $wr_id]);
    for($i=0;$i<count($result);$i++) {
      $row = $result[$i];
      $no = $row['bf_no'];
      $bf_content = $row['bf_content'] ? $this->html_purifier($row['bf_content']) : '';
      $file[$no]['bf_no'] = $row['bf_no'];
      $file[$no]['href'] = G5_BBS_URL."/download.php?bo_table=$bo_table&wr_id=$wr_id&no=$no";
      $file[$no]['download'] = $row['bf_download'];
      // 4.00.11 - 파일 path 추가
      $file[$no]['path'] = G5_DATA_URL.'/file/'.$bo_table;
      $file[$no]['size'] = $this->get_filesize($row['bf_filesize']);
      $file[$no]['datetime'] = $row['bf_datetime'];
      $file[$no]['name'] = addslashes($row['bf_source']);
      $file[$no]['bf_content'] = $bf_content;
      $file[$no]['content'] = $this->get_text($bf_content);
      //$file[$no]['view'] = view_file_link($row['bf_file'], $file[$no]['content']);
      $this->board = $this->get_board_db($bo_table, true);
      $file[$no]['view'] = $this->view_file_link($row['bf_file'], $row['bf_width'], $row['bf_height'], $file[$no]['content']);
      $file[$no]['file'] = $row['bf_file'];
      $file[$no]['image_width'] = $row['bf_width'] ? $row['bf_width'] : 640;
      $file[$no]['image_height'] = $row['bf_height'] ? $row['bf_height'] : 480;
      $file[$no]['image_type'] = $row['bf_type'];
      $file[$no]['bf_fileurl'] = $row['bf_fileurl'];
      $file[$no]['bf_thumburl'] = $row['bf_thumburl'];
      $file[$no]['bf_storage'] = $row['bf_storage'];
      // $file['count']++;
    }
    return $file;
  }

  // 검색 구문을 얻는다.
  public function get_sql_search($search_ca_name, $search_field, $search_text, $search_operator='and') {
    global $g5;

    $str = "";
    if ($search_ca_name)
      $str = " ca_name = '$search_ca_name' ";

    $search_text = strip_tags(($search_text));
    $search_text = trim(stripslashes($search_text));

    if (!$search_text && $search_text !== '0') {
      if ($search_ca_name) {
        return $str;
      } else {
        return '0';
      }
    }

    if ($str)
      $str .= " and ";

    // 쿼리의 속도를 높이기 위하여 ( ) 는 최소화 한다.
    $op1 = "";

    // 검색어를 구분자로 나눈다. 여기서는 공백
    $s = array();
    $s = explode(" ", $search_text);

    // 검색필드를 구분자로 나눈다. 여기서는 +
    $tmp = array();
    $tmp = explode(",", trim($search_field));
    $field = explode("||", $tmp[0]);
    $not_comment = "";
    if (!empty($tmp[1]))
      $not_comment = $tmp[1];

    $str .= "(";
    for ($i=0; $i<count($s); $i++) {
      // 검색어
      $search_str = trim($s[$i]);
      if ($search_str == "") continue;

      // 인기검색어
      $this->insert_popular($field, $search_str);

      $str .= $op1;
      $str .= "(";

      $op2 = "";
      for ($k=0; $k<count($field); $k++) { // 필드의 수만큼 다중 필드 검색 가능 (필드1+필드2...)
        // SQL Injection 방지
        // 필드값에 a-z A-Z 0-9 _ , | 이외의 값이 있다면 검색필드를 wr_subject 로 설정한다.
        $field[$k] = preg_match("/^[\w\,\|]+$/", $field[$k]) ? strtolower($field[$k]) : "wr_subject";

        $str .= $op2;
        switch ($field[$k]) {
          case "mb_id" :
          case "wr_name" :
            $str .= " $field[$k] = '$s[$i]' ";
            break;
          case "wr_hit" :
          case "wr_good" :
          case "wr_nogood" :
            $str .= " $field[$k] >= '$s[$i]' ";
            break;
          // 번호는 해당 검색어에 -1 을 곱함
          case "wr_num" :
            $str .= "$field[$k] = ".((-1)*$s[$i]);
            break;
          case "wr_ip" :
          case "wr_password" :
            $str .= "1=0"; // 항상 거짓
            break;
          // LIKE 보다 INSTR 속도가 빠름
          default :
            if (preg_match("/[a-zA-Z]/", $search_str))
              $str .= "INSTR(LOWER($field[$k]), LOWER('$search_str'))";
            else
              $str .= "INSTR($field[$k], '$search_str')";
            break;
        }
          $op2 = " or ";
      }
      $str .= ")";

      $op1 = " $search_operator ";
    }
    $str .= " ) ";
    if ($not_comment)
      $str .= " and wr_is_comment = '0' ";

    return $str;
  }

  // 인기검색어 입력
  public function insert_popular($field, $str) {
    global $g5;
    if(!in_array('mb_id', $field)) {
      $sql = "INSERT INTO {$g5['popular_table']} SET pp_word = ?, pp_date = ?, pp_ip = ?";
      $this->sql_query($sql, [$str, G5_TIME_YMD, $_SERVER['REMOTE_ADDR']]);
    }
  }

  // 제목을 변환
  public function conv_subject($subject, $len, $suffix='') {
    return $this->get_text($this->cut_str($subject, $len, $suffix));
  }
  public function clean_relative_paths($path) {
    $path_len = strlen($path);
    $i = 0;
    while($i <= $path_len){
      $result = str_replace('../', '', str_replace('\\', '/', $path));

      if((string)$result === (string)$path) break;

      $path = $result;
      $i++;
    }

    return $path;
  }
  // 회원 삭제
  public function member_delete($mb_id) {
    $config = $this->config;
    global $g5;

    $sql = " select mb_name, mb_nick, mb_ip, mb_recommend, mb_memo, mb_level from {$g5['member_table']} where mb_id= ?";
    $mb = $this->sql_fetch($sql, [$mb_id]);

    // 이미 삭제된 회원은 제외
    if(preg_match('#^[0-9]{8}.*삭제함#', $mb['mb_memo']))
      return;

    if ($mb['mb_recommend']) {
      $row = sql_fetch(" select count(*) as cnt from {$g5['member_table']} where mb_id = '".addslashes($mb['mb_recommend'])."' ");
      if ($row['cnt'])
        $this->insert_point($mb['mb_recommend'], $config['cf_recommend_point'] * (-1), $mb_id.'님의 회원자료 삭제로 인한 추천인 포인트 반환', "@member", $mb['mb_recommend'], $mb_id.' 추천인 삭제');
    }

    // 회원자료는 정보만 없앤 후 아이디는 보관하여 다른 사람이 사용하지 못하도록 함 : 061025
    $sql = " update {$g5['member_table']} set mb_password = '', mb_level = 1, mb_email = '', mb_homepage = '', mb_tel = '', mb_hp = '', mb_zip1 = '', mb_zip2 = '', mb_addr1 = '', mb_addr2 = '', mb_birth = '', mb_sex = '', mb_signature = '', mb_memo = '".date('Ymd', G5_SERVER_TIME)." 삭제함\n".sql_real_escape_string($mb['mb_memo'])."' where mb_id = '{$mb_id}' ";

    $this->sql_query($sql);

    // 포인트 테이블에서 삭제
    $this->sql_query(" delete from {$g5['point_table']} where mb_id = ?",[$mb_id]);

    // 그룹접근가능 삭제
    $this->sql_query(" delete from {$g5['group_member_table']} where mb_id = ? ", [$mb_id]);

    // 쪽지 삭제
    $this->sql_query(" delete from {$g5['memo_table']} where me_recv_mb_id = ? or me_send_mb_id = ? ", [$mb_id, $mb_id]);

    // 스크랩 삭제
    $this->sql_query(" delete from {$g5['scrap_table']} where mb_id = ? ", [$mb_id]);

    // 관리권한 삭제
    $this->sql_query(" delete from {$g5['auth_table']} where mb_id = ? ", [$mb_id]);

    // 그룹관리자인 경우 그룹관리자를 공백으로
    $this->sql_query(" update {$g5['group_table']} set gr_admin = '' where gr_admin = ? ", [$mb_id]);

    // 게시판관리자인 경우 게시판관리자를 공백으로
    $this->sql_query(" update {$g5['board_table']} set bo_admin = '' where bo_admin = ? ", [$mb_id]);

    //소셜로그인에서 삭제 또는 해제
    if(function_exists('social_member_link_delete')){
      social_member_link_delete($mb_id);
    }

    // 아이콘 삭제
    @unlink(G5_DATA_PATH.'/member/'.substr($mb_id,0,2).'/'.$mb_id.'.gif');

    // 프로필 이미지 삭제
    @unlink(G5_DATA_PATH.'/member_image/'.substr($mb_id,0,2).'/'.$mb_id.'.gif');

    run_event('member_delete_after', $mb_id);
  }

  // 회원 레이어
  public function get_sideview($mb_id, $name='', $email='', $homepage='', $bo_table = '') {
    global $g5;
    $config = $this->config;
    $is_admin = $this->$is_admin;
    $member = $this->member;
    $sca = $this->qstr['sca'];

    $email = $this->get_string_encrypt($email);
    $homepage = $this->set_http($this->clean_xss_tags($homepage));

    $name     = $this->get_text($name, 0, true);
    $email    = $this->get_text($email);
    $homepage = $this->get_text($homepage);

    $str = array();
    $en_mb_id = $mb_id;
    $result = array();
    $result['mb_nick'] = $name;    
    if ($mb_id) {
      $result['mb_id'] = $mb_id;
      //$tmp_name = "<a href=\"".G5_BBS_URL."/profile.php?mb_id=".$mb_id."\" class=\"sv_member\" title=\"$name 자기소개\" rel="nofollow" target=\"_blank\" onclick=\"return false;\">$name</a>";
      // $str['intro']['title'] = '자기소개';
      // $str['intro']['url'] = G5_BBS_URL.'/profile?mb_id='.$mb_id;

      if ($config['cf_use_member_icon']) {
        $mb_dir = substr($mb_id,0,2);
        $icon_file = G5_DATA_PATH.'/member/'.$mb_dir.'/'.$this->get_mb_icon_name($mb_id).'.gif';

        if (file_exists($icon_file)) {
          $icon_filemtile = (defined('G5_USE_MEMBER_IMAGE_FILETIME') && G5_USE_MEMBER_IMAGE_FILETIME) ? '?'.filemtime($icon_file) : '';
          $width = $config['cf_member_icon_width'];
          $height = $config['cf_member_icon_height'];
          $str['intro']['icon_url'] = G5_DATA_URL.'/member/'.$mb_dir.'/'.$this->get_mb_icon_name($mb_id).'.gif'.$icon_filemtile;
        } else {
          if( defined('G5_THEME_NO_PROFILE_IMG') ){
            $str['intro']['icon_url'] = G5_THEME_NO_PROFILE_IMG;
          } else if( defined('G5_NO_PROFILE_IMG') ){
            $str['intro']['icon_url'] = G5_NO_PROFILE_IMG;
          }
          if ($config['cf_use_member_icon'] == 2) {}// 회원아이콘+이름
            
        }
      } 

    } else {
      if(!$bo_table)
        return $name;
      // $str['intro']['title'] = '이름으로 검색';
      // $str['intro']['url'] = $this->get_pretty_url($bo_table, '', 'sca='.$sca.'&sfl=wr_name,1&stx='.$name);
    }

    if($mb_id) {
      $str['memo']['title'] = '쪽지보내기';
      $str['memo']['url'] = G5_BBS_URL."/memo_form?me_recv_mb_id=".$mb_id;
    }
    if($email) {
      $str['email']['title'] = '이메일';
      $str['email']['url'] = G5_BBS_URL."/formmail?mb_id=".$mb_id."&amp;name=".urlencode($name)."&amp;email=".$email;
    }
    if($homepage) {
      $str['homepage']['title'] = '홈페이지';
      $str['homepage']['url'] = $homepage;
    }
    if($mb_id) {
      $str['profile']['title'] = '자기소개';
      $str['profile']['url'] = G5_BBS_URL."/profile?mb_id=".$mb_id;
    }
    if($bo_table) {
      if($mb_id) {
        $str['search_mb_id']['title'] = '아이디로 검색';
        $str['search_mb_id']['url'] = $this->get_pretty_url($bo_table, '', "sca=".$sca."&amp;sfl=mb_id,1&amp;stx=".$en_mb_id);
      } else {
        $str['search_name']['title'] = '이름으로 검색';
        $str['search_name']['url'] = $this->get_pretty_url($bo_table, '', "sca=".$sca."&amp;sfl=wr_name,1&amp;stx=".$name);
      }
    }
    if($mb_id) {
      $str['new']['title'] = '전체게시물';
      $str['new']['url'] = G5_BBS_URL."/new?mb_id=".$mb_id;
    }
    if($is_admin == "super" && $mb_id) {
      $str['mb_info']['title'] = '회원정보변경';
      $str['mb_info']['url'] = G5_ADMIN_URL."/member_form?w=u&amp;mb_id=".$mb_id;
      $str['mb_point']['title'] = '포인트내역';
      $str['mb_point']['url'] = G5_ADMIN_URL."/point_list?sfl=mb_id&amp;stx=".$mb_id;
    }
    $result['list'] = $str;
    return $result;
  }

  // set_search_font(), get_search_font() 함수를 search_font() 함수로 대체
  public function search_font($stx, $str) {
    $config = $this->config;

    // 문자앞에 \ 를 붙입니다.
    $src = array('/', '|');
    $dst = array('\/', '\|');

    if (!trim($stx) && $stx !== '0') return $str;

    // 검색어 전체를 공란으로 나눈다
    $s = explode(' ', $stx);

    // "/(검색1|검색2)/i" 와 같은 패턴을 만듬
    $pattern = '';
    $bar = '';
    for ($m=0; $m<count($s); $m++) {
      if (trim($s[$m]) == '') continue;
      // 태그는 포함하지 않아야 하는데 잘 안되는군. ㅡㅡa
      //$pattern .= $bar . '([^<])(' . quotemeta($s[$m]) . ')';
      //$pattern .= $bar . quotemeta($s[$m]);
      //$pattern .= $bar . str_replace("/", "\/", quotemeta($s[$m]));
      $tmp_str = quotemeta($s[$m]);
      $tmp_str = str_replace($src, $dst, $tmp_str);
      $pattern .= $bar . $tmp_str . "(?![^<]*>)";
      $bar = "|";
    }

    // 지정된 검색 폰트의 색상, 배경색상으로 대체
    $replace = "<b class=\"sch_word\">\\1</b>";

    return preg_replace("/($pattern)/i", $replace, $str);
  }
  public function check_html_link_nofollow($type=''){
    return true;
  }
  // way.co.kr 의 wayboard 참고
  public function url_auto_link($str) {
    global $g5;
    $config = $this->config;

    // 140326 유창화님 제안코드로 수정
    // http://sir.kr/pg_lecture/461
    // http://sir.kr/pg_lecture/463
    $attr_nofollow = ($this->check_html_link_nofollow('url_auto_link')) ? ' rel="nofollow"' : '';
    $str = str_replace(array("&lt;", "&gt;", "&amp;", "&quot;", "&nbsp;", "&#039;"), array("\t_lt_\t", "\t_gt_\t", "&", "\"", "\t_nbsp_\t", "'"), $str);

    $str = preg_replace("/([^(href=\"?'?)|(src=\"?'?)]|\(|^)((http|https|ftp|telnet|news|mms):\/\/[a-zA-Z0-9\.-]+\.[가-힣\xA1-\xFEa-zA-Z0-9\.:&#!=_\?\/~\+%@;\-\|\,\(\)]+)/i", "\\1<A HREF=\"\\2\" TARGET=\"{$config['cf_link_target']}\" $attr_nofollow>\\2</A>", $str);
    $str = preg_replace("/(^|[\"'\s(])(www\.[^\"'\s()]+)/i", "\\1<A HREF=\"http://\\2\" TARGET=\"{$config['cf_link_target']}\" $attr_nofollow>\\2</A>", $str);
    $str = preg_replace("/[0-9a-z_-]+@[a-z0-9._-]{4,}/i", "<a href=\"mailto:\\0\" $attr_nofollow>\\0</a>", $str);
    $str = str_replace(array("\t_nbsp_\t", "\t_lt_\t", "\t_gt_\t", "'"), array("&nbsp;", "&lt;", "&gt;", "&#039;"), $str);

    return run_replace('url_auto_link', $str);
  }

  // 내용을 변환
  public function conv_content($content, $html, $filter=true) {
    $config = $this->config;
    if ($html) {
      $source = array();
      $target = array();

      $source[] = "//";
      $target[] = "";

      if ($html == 2) { // 자동 줄바꿈
          $source[] = "/\n/";
          $target[] = "<br/>";
      }

      // 테이블 태그의 개수를 세어 테이블이 깨지지 않도록 한다.
      $table_begin_count = substr_count(strtolower($content), "<table");
      $table_end_count = substr_count(strtolower($content), "</table");
      for ($i=$table_end_count; $i<$table_begin_count; $i++) {
        $content .= "</table>";
      }

      $content = preg_replace($source, $target, $content);

      if($filter)
        $content = $this->html_purifier($content);
    } else {// text 이면
      // & 처리 : &amp; &nbsp; 등의 코드를 정상 출력함
      $content = $this->html_symbol($content);

      // 공백 처리
      //$content = preg_replace("/  /", "&nbsp; ", $content);
      $content = str_replace("  ", "&nbsp; ", $content);
      $content = str_replace("\n ", "\n&nbsp;", $content);

      $content = $this->get_text($content, 1);
      $content = $this->url_auto_link($content);
    }

    return $content;
  }

  // 게시물 정보($write_row)를 출력하기 위하여 $list로 가공된 정보를 복사 및 가공
  public function get_list($write_row, $board, $skin_url, $subject_len=40) {
    global $g5, $g5_object;
    $config = $this->config;
    $page = $this->page ? $this->page : 1;
    // $board = $this->get_board_db($bo_table);
    $qstr = '';
    foreach ($this->qstr as $key => $value) {
      if($value) $qstr .= $key.'='.$value;
    }

    //$t = get_microtime();

    $g5_object->set('bbs', $write_row['wr_id'], $write_row, $board['bo_table']);

    // 배열전체를 복사
    $list = $write_row;
    unset($write_row);

    $board_notice = array_map('trim', explode(',', $board['bo_notice']));
    $list['is_notice'] = in_array($list['wr_id'], $board_notice);

    if ($subject_len)
      $list['subject'] = $this->conv_subject($list['wr_subject'], $subject_len, '…');
    else
      $list['subject'] = $this->conv_subject($list['wr_subject'], $board['bo_subject_len'], '…');

    if( ! (isset($list['wr_seo_title']) && $list['wr_seo_title']) && $list['wr_id'] ){
      $this->seo_title_update($this->get_write_table_name($board['bo_table']), $list['wr_id'], 'bbs');
    }

    // 목록에서 내용 미리보기 사용한 게시판만 내용을 변환함 (속도 향상) : kkal3(커피)님께서 알려주셨습니다.
    if ($board['bo_use_list_content']) {
      $html = 0;
      if (strstr($list['wr_option'], 'html1'))
        $html = 1;
      else if (strstr($list['wr_option'], 'html2'))
        $html = 2;

      $list['content'] = $this->conv_content($list['wr_content'], $html);
    }

    $list['comment_cnt'] = '';
    if ($list['wr_comment'])
      $list['comment_cnt'] = "<span class=\"cnt_cmt\">".$list['wr_comment']."</span>";

    // 당일인 경우 시간으로 표시함
    $list['datetime'] = substr($list['wr_datetime'],0,10);
    $list['datetime2'] = $list['wr_datetime'];
    if ($list['datetime'] == G5_TIME_YMD)
      $list['datetime2'] = substr($list['datetime2'],11,5);
    else
      $list['datetime2'] = substr($list['datetime2'],5,5);
    // 4.1
    $list['last'] = substr($list['wr_last'],0,10);
    $list['last2'] = $list['wr_last'];
    if ($list['last'] == G5_TIME_YMD)
      $list['last2'] = substr($list['last2'],11,5);
    else
      $list['last2'] = substr($list['last2'],5,5);

    $list['wr_homepage'] = $this->get_text($list['wr_homepage']);

    $tmp_name = $this->get_text($this->cut_str($list['wr_name'], $config['cf_cut_name'])); // 설정된 자리수 만큼만 이름 출력
    $tmp_name2 = $this->cut_str($list['wr_name'], $config['cf_cut_name']); // 설정된 자리수 만큼만 이름 출력
    if ($board['bo_use_sideview'])
      $list['name'] = $this->get_sideview($list['mb_id'], $tmp_name2, $list['wr_email'], $list['wr_homepage'], $board['bo_table']);
    else
      $list['name'] = '<span class="'.($list['mb_id']?'sv_member':'sv_guest').'">'.$tmp_name.'</span>';

    $reply = $list['wr_reply'];

    $list['reply'] = strlen($reply)*20;

    $list['icon_reply'] = '';
    if ($list['reply'])
      $list['icon_reply'] = '<img src="'.$skin_url.'/img/icon_reply.gif" class="icon_reply" alt="답변글">';

    $list['icon_link'] = '';
    if ($list['wr_link1'] || $list['wr_link2'])
      $list['icon_link'] = '<i class="fa fa-link" aria-hidden="true"></i> ';

    // 분류명 링크
    $list['ca_name_href'] = $this->get_pretty_url($board['bo_table'], '', 'sca='.urlencode($list['ca_name']));

    $list['href'] = $this->get_pretty_url($board['bo_table'], $list['wr_id'], $qstr);
    $list['comment_href'] = $list['href'];

    $list['icon_new'] = '';
    if ($board['bo_new'] && $list['wr_datetime'] >= date("Y-m-d H:i:s", G5_SERVER_TIME - ($board['bo_new'] * 3600)))
      $list['icon_new'] = '<img src="'.$skin_url.'/img/icon_new.gif" class="title_icon" alt="새글"> ';

    $list['icon_hot'] = '';
    if ($board['bo_hot'] && $list['wr_hit'] >= $board['bo_hot'])
      $list['icon_hot'] = '<i class="fa fa-heart" aria-hidden="true"></i> ';

    $list['icon_secret'] = '';
    if (strstr($list['wr_option'], 'secret'))
      $list['icon_secret'] = '<i class="fa fa-lock" aria-hidden="true"></i> ';

    // 링크
    for ($i=1; $i<=G5_LINK_COUNT; $i++) {
      $list['link'][$i] = $this->set_http($this->get_text($list["wr_link{$i}"]));
      $list['link_href'][$i] = G5_BBS_URL.'/link.php?bo_table='.$board['bo_table'].'&amp;wr_id='.$list['wr_id'].'&amp;no='.$i.$qstr;
      $list['link_hit'][$i] = (int)$list["wr_link{$i}_hit"];
    }

    // 가변 파일
    if ($board['bo_use_list_file'] || ($list['wr_file'] && $subject_len == 255) /* view 인 경우 */) {
      $list['file'] = $this->get_file($board['bo_table'], $list['wr_id']);
    } else {
      // $list['file']['count'] = $list['wr_file'];
    }

    if ($list['file']['count'])
      $list['icon_file'] = '<i class="fa fa-download" aria-hidden="true"></i> ';
    
    $list['thumb'] = $this->get_list_thumbnail($board['bo_table'], $list['wr_id'], $board['bo_gallery_width'], $board['bo_gallery_height'], false, true);
    return $list;
  }
  // get_list 의 alias
  public function get_view($write_row, $board, $skin_url) {
    return $this->get_list($write_row, $board, $skin_url, 255);
  }
  // 게시판 테이블에서 하나의 행을 읽음
  public function get_write($write_table, $wr_id, $is_cache=false) {
    global $g5, $g5_object;
    $wr_bo_table = preg_replace('/^'.preg_quote($g5['write_prefix']).'/i', '', $write_table);
    $write = $g5_object->get('bbs', $wr_id, $wr_bo_table);
    if( !$write || $is_cache == false ){
      $sql = "SELECT * FROM {$write_table} WHERE wr_id = ?";
      $write = $this->sql_fetch($sql, [$wr_id]);
      $g5_object->set('bbs', $wr_id, $write, $wr_bo_table);
    }
    return $write;
  }
  public function board_notice($bo_notice, $wr_id, $insert=false) {
    $notice_array = explode(",", trim($bo_notice));
    if($insert && in_array($wr_id, $notice_array))
      return $bo_notice;

    $notice_array = array_merge(array($wr_id), $notice_array);
    $notice_array = array_unique($notice_array);
    foreach ($notice_array as $key=>$value) {
      if (!trim($value))
        unset($notice_array[$key]);
    }
    if (!$insert) {
      foreach ($notice_array as $key=>$value) {
        if ((int)$value == (int)$wr_id)
          unset($notice_array[$key]);
      }
    }
    return implode(",", $notice_array);
  }
  public function get_selected($field, $value) {
    if( is_int($value) ){
      return ((int) $field===$value) ? true : false;
    }
    return ($field===$value) ? true : false;
  }
  public function utf8_strcut( $str, $size, $suffix='...' ) {
    if( function_exists('mb_strlen') && function_exists('mb_substr') ){
      if(mb_strlen($str)<=$size) {
        return $str;
      } else {
        $str = mb_substr($str, 0, $size, 'utf-8');
        $str .= $suffix;
      }

    } else {
      $substr = substr( $str, 0, $size * 2 );
      $multi_size = preg_match_all( '/[\x80-\xff]/', $substr, $multi_chars );
      if ( $multi_size > 0 )
        $size = $size + intval( $multi_size / 3 ) - 1;
      if ( strlen( $str ) > $size ) {
        $str = substr( $str, 0, $size );
        $str = preg_replace( '/(([\x80-\xff]{3})*?)([\x80-\xff]{0,2})$/', '$1', $str );
        $str .= $suffix;
      }
    }
    return $str;
  }
  public function is_use_email_certify($config) {
    if( $config['cf_use_email_certify'] && function_exists('social_is_login_check') ){
      if( $config['cf_social_login_use'] && ($this->et_session('ss_social_provider') || social_is_login_check()) ){      //소셜 로그인을 사용한다면
        $tmp = (defined('G5_SOCIAL_CERTIFY_MAIL') && G5_SOCIAL_CERTIFY_MAIL) ? 1 : 0;
        return $tmp;
      }
    }
    return $config['cf_use_email_certify'];
  }
  public function get_next_num($table) {
    // 가장 작은 번호를 얻어
    $row = $this->sql_fetch("SELECT MIN(wr_num) as min_wr_num FROM {$table}");
    // 가장 작은 번호에 1을 빼서 넘겨줌
    return (int)($row['min_wr_num'] - 1);
  }
  // 문자열 암호화
  public function get_encrypt_string($str) {
    if(defined('G5_STRING_ENCRYPT_FUNCTION') && G5_STRING_ENCRYPT_FUNCTION) {
      $encrypt = call_user_func(G5_STRING_ENCRYPT_FUNCTION, $str);
    } else {
      $encrypt = $this->sql_password($str);
    }
    return $encrypt;
  }

  // 그룹 설정 테이블에서 하나의 행을 읽음
  public function get_group($gr_id, $is_cache=false) {
    global $g5;
    if( is_array($gr_id) ){
      return array();
    }
    static $cache = array();

    $gr_id = preg_replace('/[^a-z0-9_]/i', '', $gr_id);
    $cache = run_replace('get_group_db_cache', $cache, $gr_id, $is_cache);
    $key = md5($gr_id);

    if( $is_cache && isset($cache[$key]) ){
      return $cache[$key];
    }
    $sql = " select * from {$g5['group_table']} where gr_id = ?";

    $group = run_replace('get_group', $this->sql_fetch($sql, [$gr_id]), $gr_id, $is_cache);
    $cache[$key] = array_merge(array('gr_device'=>'', 'gr_subject'=>''), (array) $group);

    return $cache[$key];
  }


  // 비밀번호 비교
  public function check_password($pass, $hash) {
    if(defined('G5_STRING_ENCRYPT_FUNCTION') && G5_STRING_ENCRYPT_FUNCTION === 'create_hash') {
      return validate_password($pass, $hash);
    }
    $password = $this->get_encrypt_string($pass);
    return ($password === $hash);
  }

  // 로그인 패스워드 체크
  public function login_password_check($mb, $pass, $hash) {
    global $g5;
    $mb_id = isset($mb['mb_id']) ? $mb['mb_id'] : '';
    if(!$mb_id)
      return false;
    if(G5_STRING_ENCRYPT_FUNCTION === 'create_hash' && (strlen($hash) === G5_MYSQL_PASSWORD_LENGTH || strlen($hash) === 16)) {      
      if($this->sql_password($pass) === $hash){
        if(!isset($mb['mb_password2']) ){
          $sql = "ALTER TABLE `{$g5['member_table']}` ADD `mb_password2` varchar(255) NOT NULL default '' AFTER `mb_password`";
          $this->sql_query($sql);
        }    
        $new_password = create_hash($pass);
        $sql = "UPDATE {$g5['member_table']} SET mb_password = ?, mb_password2 = ? WHERE mb_id = ?";
        $this->sql_query($sql, [$new_password, $hash, $mb_id]);
        return true;
      }
    }
    return $this->check_password($pass, $hash);
  }
  // 세션변수 생성
  public function set_session($session_name, $value) {
    static $check_cookie = null;
    if( $check_cookie === null ){
      $cookie_session_name = session_name();
      if( ! ($cookie_session_name && isset($_COOKIE[$cookie_session_name]) && $_COOKIE[$cookie_session_name]) && ! headers_sent() ){
        @session_regenerate_id(false);
      }
      $check_cookie = 1;
    }
    if (PHP_VERSION < '5.3.0')
      session_register($session_name);
      // PHP 버전별 차이를 없애기 위한 방법
    $$session_name = $_SESSION[$session_name] = $value;
  }
  // 세션변수값 얻음
  public function get_session($session_name) {
    return isset($_SESSION[$session_name]) ? $_SESSION[$session_name] : '';
  }
  // 이메일 주소 추출
  public function get_email_address($email){
    preg_match("/[0-9a-z._-]+@[a-z0-9._-]{4,}/i", $email, $matches);
    return $matches[0];
  }
  // 파일명에서 특수문자 제거
  public function get_safe_filename($name) {
    $pattern = '/["\'<>=#&!%\\\\(\)\*\+\?]/';
    $name = preg_replace($pattern, '', $name);

    return $name;
  }
  // 마이크로 타임을 얻어 계산 형식으로 만듦
  public function get_microtime() {
    list($usec, $sec) = explode(" ",microtime());
    return ((float)$usec + (float)$sec);
  }
  // 파일명 치환
  public function replace_filename($name) {
    @session_start();
    $ss_id = session_id();
    $usec = $this->get_microtime();
    $file_path = pathinfo($name);
    $ext = $file_path['extension'];
    $return_filename = sha1($ss_id.$_SERVER['REMOTE_ADDR'].$usec); 
    if( $ext )
      $return_filename .= '.'.$ext;

    return $return_filename;
  }
  // XSS 관련 태그 제거
  public function clean_xss_tags($str, $check_entities=0) {
    $str_len = strlen($str);
    $i = 0;
    while($i <= $str_len){
      $result = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $str);
      if( $check_entities ){
        $result = str_replace(array('&colon;', '&lpar;', '&rpar;', '&NewLine;', '&Tab;'), '', $result);
      }        
      $result = preg_replace('#([^\p{L}]|^)(?:javascript|jar|applescript|vbscript|vbs|wscript|jscript|behavior|mocha|livescript|view-source)\s*:(?:.*?([/\\\;()\'">]|$))#ius','$1$2', $result);
      if((string)$result === (string)$str) break;
      $str = $result;
      $i++;
    }
    return $str;
  }
  // 관리자 정보를 얻음
  function get_admin($admin='super', $fields='*') {
    $config = $this->config;
    global $group, $board;
    global $g5;
    $is = false;
    if ($admin == 'board') {
      $mb = $this->sql_fetch("select {$fields} from {$g5['member_table']} where mb_id in (?) limit 1", [$board['bo_admin']]);
      $is = true;
    }
    if (($is && !$mb['mb_id']) || $admin == 'group') {
      $mb = $this->sql_fetch("select {$fields} from {$g5['member_table']} where mb_id in (?) limit 1 ",[$group['gr_admin']]);
      $is = true;
    }
    if (($is && !$mb['mb_id']) || $admin == 'super') {
      $mb = sql_fetch("select {$fields} from {$g5['member_table']} where mb_id in ('?') limit 1 ", [$config['cf_admin']]);
    }

    return $mb;
  }
    
  // $dir 을 포함하여 https 또는 http 주소를 반환한다.
  public function https_url($dir, $https=true){
    if ($https) {
      if (G5_HTTPS_DOMAIN) {
        $url = G5_HTTPS_DOMAIN.'/'.$dir;
      } else {
        $url = G5_URL.'/'.$dir;
      }
    } else {
      if (G5_DOMAIN) {
        $url = G5_DOMAIN.'/'.$dir;
      } else {
        $url = G5_URL.'/'.$dir;
      }
    }

    return $url;
  }


  // XSS 어트리뷰트 태그 제거
  public function clean_xss_attributes($str) {
    $xss_attributes_string = 'onAbort|onActivate|onAttribute|onAfterPrint|onAfterScriptExecute|onAfterUpdate|onAnimationCancel|onAnimationEnd|onAnimationIteration|onAnimationStart|onAriaRequest|onAutoComplete|onAutoCompleteError|onAuxClick|onBeforeActivate|onBeforeCopy|onBeforeCut|onBeforeDeactivate|onBeforeEditFocus|onBeforePaste|onBeforePrint|onBeforeScriptExecute|onBeforeUnload|onBeforeUpdate|onBegin|onBlur|onBounce|onCancel|onCanPlay|onCanPlayThrough|onCellChange|onChange|onClick|onClose|onCommand|onCompassNeedsCalibration|onContextMenu|onControlSelect|onCopy|onCueChange|onCut|onDataAvailable|onDataSetChanged|onDataSetComplete|onDblClick|onDeactivate|onDeviceLight|onDeviceMotion|onDeviceOrientation|onDeviceProximity|onDrag|onDragDrop|onDragEnd|onDragEnter|onDragLeave|onDragOver|onDragStart|onDrop|onDurationChange|onEmptied|onEnd|onEnded|onError|onErrorUpdate|onExit|onFilterChange|onFinish|onFocus|onFocusIn|onFocusOut|onFormChange|onFormInput|onFullScreenChange|onFullScreenError|onGotPointerCapture|onHashChange|onHelp|onInput|onInvalid|onKeyDown|onKeyPress|onKeyUp|onLanguageChange|onLayoutComplete|onLoad|onLoadedData|onLoadedMetaData|onLoadStart|onLoseCapture|onLostPointerCapture|onMediaComplete|onMediaError|onMessage|onMouseDown|onMouseEnter|onMouseLeave|onMouseMove|onMouseOut|onMouseOver|onMouseUp|onMouseWheel|onMove|onMoveEnd|onMoveStart|onMozFullScreenChange|onMozFullScreenError|onMozPointerLockChange|onMozPointerLockError|onMsContentZoom|onMsFullScreenChange|onMsFullScreenError|onMsGestureChange|onMsGestureDoubleTap|onMsGestureEnd|onMsGestureHold|onMsGestureStart|onMsGestureTap|onMsGotPointerCapture|onMsInertiaStart|onMsLostPointerCapture|onMsManipulationStateChanged|onMsPointerCancel|onMsPointerDown|onMsPointerEnter|onMsPointerLeave|onMsPointerMove|onMsPointerOut|onMsPointerOver|onMsPointerUp|onMsSiteModeJumpListItemRemoved|onMsThumbnailClick|onOffline|onOnline|onOutOfSync|onPage|onPageHide|onPageShow|onPaste|onPause|onPlay|onPlaying|onPointerCancel|onPointerDown|onPointerEnter|onPointerLeave|onPointerLockChange|onPointerLockError|onPointerMove|onPointerOut|onPointerOver|onPointerUp|onPopState|onProgress|onPropertyChange|onqt_error|onRateChange|onReadyStateChange|onReceived|onRepeat|onReset|onResize|onResizeEnd|onResizeStart|onResume|onReverse|onRowDelete|onRowEnter|onRowExit|onRowInserted|onRowsDelete|onRowsEnter|onRowsExit|onRowsInserted|onScroll|onSearch|onSeek|onSeeked|onSeeking|onSelect|onSelectionChange|onSelectStart|onStalled|onStorage|onStorageCommit|onStart|onStop|onShow|onSyncRestored|onSubmit|onSuspend|onSynchRestored|onTimeError|onTimeUpdate|onTimer|onTrackChange|onTransitionEnd|onToggle|onTouchCancel|onTouchEnd|onTouchLeave|onTouchMove|onTouchStart|onTransitionCancel|onTransitionEnd|onUnload|onURLFlip|onUserProximity|onVolumeChange|onWaiting|onWebKitAnimationEnd|onWebKitAnimationIteration|onWebKitAnimationStart|onWebKitFullScreenChange|onWebKitFullScreenError|onWebKitTransitionEnd|onWheel';
    
    do {
      $count = $temp_count = 0;

      $str = preg_replace(
        '/(.*)(?:' . $xss_attributes_string . ')(?:\s*=\s*)(?:\'(?:.*?)\'|"(?:.*?)")(.*)/ius',
        '$1-$2-$3-$4',
        $str,
        -1,
        $temp_count
      );
      $count += $temp_count;

      $str = preg_replace(
        '/(.*)(?:' . $xss_attributes_string . ')\s*=\s*(?:[^\s>]*)(.*)/ius',
        '$1$2',
        $str,
        -1,
        $temp_count
      );
      $count += $temp_count;

    } while ($count);

    return $str;
  }

  public function sql_num_rows($result) {
    return $result->fetchColumn();
  }
  // $result에 대한 메모리(memory)에 있는 내용을 모두 제거한다.
  // sql_free_result()는 결과로부터 얻은 질의 값이 커서 많은 메모리를 사용할 염려가 있을 때 사용된다.
  // 단, 결과 값은 스크립트(script) 실행부가 종료되면서 메모리에서 자동적으로 지워진다.
  public function sql_free_result($result) {
    $result->closeCursor();
  }
  

  // 검색어 특수문자 제거
  public function get_search_string($stx){
    $stx_pattern = array();
    $stx_pattern[] = '#\.*/+#';
    $stx_pattern[] = '#\\\*#';
    $stx_pattern[] = '#\.{2,}#';
    $stx_pattern[] = '#[/\'\"%=*\#\(\)\|\+\&\!\$~\{\}\[\]`;:\?\^\,]+#';

    $stx_replace = array();
    $stx_replace[] = '';
    $stx_replace[] = '';
    $stx_replace[] = '.';
    $stx_replace[] = '';

    $stx = preg_replace($stx_pattern, $stx_replace, $stx);

    return $stx;
  }


  //포인트 관련
  public function insert_point($mb_id, $point, $content='', $rel_table='', $rel_id='', $rel_action='', $expire=0) {
    global $g5;
    $config = $this->$config;
    $is_admin = $this->is_admin;
    // 포인트 사용을 하지 않는다면 return
    if (!$config['cf_use_point']) { return 0; }
  
    // 포인트가 없다면 업데이트 할 필요 없음
    if ($point == 0) { return 0; }
  
    // 회원아이디가 없다면 업데이트 할 필요 없음
    if ($mb_id == '') { return 0; }
    $mb = $this->sql_fetch("SELECT mb_id FROM {$g5['member_table']} WHERE mb_id = ?", [$mb_id]);
    if (!$mb['mb_id']) { return 0; }

    // 회원포인트
    $mb_point = $this->get_point_sum($mb_id);

    // 이미 등록된 내역이라면 건너뜀
    if ($rel_table || $rel_id || $rel_action) {
      $sql = "SELECT count(*) as cnt from {$g5['point_table']}
              WHERE mb_id = ?
              AND po_rel_table = ?
              AND po_rel_id = ?
              AND po_rel_action = ? ";
      $row = $this->sql_fetch($sql, [$mb_id, $rel_table, $rel_id, $rel_action]);      
      if ($row['cnt']) return -1;
    }

    // 포인트 건별 생성
    $po_expire_date = '9999-12-31';
    if($config['cf_point_term'] > 0) {
        if($expire > 0)
            $po_expire_date = date('Y-m-d', strtotime('+'.($expire - 1).' days', G5_SERVER_TIME));
        else
            $po_expire_date = date('Y-m-d', strtotime('+'.($config['cf_point_term'] - 1).' days', G5_SERVER_TIME));
    }

    $po_expired = 0;
    if($point < 0) {
        $po_expired = 1;
        $po_expire_date = G5_TIME_YMD;
    }
    $po_mb_point = $mb_point + $point;

    $sql = "INSERT INTO {$g5['point_table']}
            SET mb_id = ?,
            po_datetime = ?,
            po_content = ?,
            po_point = ?,
            po_use_point = ?,
            po_mb_point = ?,
            po_expired = ?,
            po_expire_date = ?,
            po_rel_table = ?,
            po_rel_id = ?,
            po_rel_action = ?";
    $this->sql_query($sql, [$mb_id, G5_TIME_YMDHIS, addslashes($content), $point, '0', $po_mb_point, $po_expired, $po_expire_date, $rel_table, $rel_id, $rel_action]);

    // 포인트를 사용한 경우 포인트 내역에 사용금액 기록
    if($point < 0) {
      $this->insert_use_point($mb_id, $point);
    }

    // 포인트 UPDATE
    $this->sql_query("UPDATE {$g5['member_table']} SET mb_point = ? WHERE mb_id = ?",[$po_mb_point, $mb_id]);

    return 1;
  }
  
  // 한페이지에 보여줄 행, 현재페이지, 총페이지수, URL
  public function get_paging($write_pages, $cur_page, $total_page, $url, $add="") {
    //$url = preg_replace('#&amp;page=[0-9]*(&amp;page=)$#', '$1', $url);
    $url = preg_replace('#(&amp;)?page=[0-9]*#', '', $url);
    $url .= substr($url, -1) === '?' ? 'page=' : '&amp;page=';
    $i = 0;
    $str = array();
    if ($cur_page > 1) {
      $str[$i]['name'] = '처음';
      $str[$i]['url'] = $url.'1'.$add;
      $i++;
    }

    $start_page = ( ( (int)( ($cur_page - 1 ) / $write_pages ) ) * $write_pages ) + 1;
    $end_page = $start_page + $write_pages - 1;

    if ($end_page >= $total_page) $end_page = $total_page;

    if ($start_page > 1) {
      $str[$i]['name'] = '이전';
      $str[$i]['url'] = $url.($start_page-1).$add;
      $i++;
    }

    if ($total_page > 1) {
      for ($k=$start_page;$k<=$end_page;$k++) {
        if ($cur_page != $k) {
          $str[$i]['name'] = $k;
          $str[$i]['url'] = $url.$k.$add;
          $i++;
        } else {
          $str[$i]['name'] = $k;
          $str[$i]['active'] = true;
          $i++;
        }
      }
    }

    if ($total_page > $end_page) {
      $str[$i]['name'] = '다음';
      $str[$i]['url'] = $url.($end_page+1).$add;
      $i++;
    }

    if ($cur_page < $total_page) {
      $str[$i]['name'] = '맨끝';
      $str[$i]['url'] = $url.$total_page.$add;
      $i++;
    }

    return $str;
  }

  // 페이징 코드의 <nav><span> 태그 다음에 코드를 삽입
  function page_insertbefore($paging, $prev_part_href) {
    foreach ($paging as $key => $value) {
      if($paging[$key]['name'] == '이전') {
        $paging[$key]['name'] = '이전검색';
        $paging[$key]['url'] = $prev_part_href;
      }
    }

    return $paging;
  }

  // 페이징 코드의 </span></nav> 태그 이전에 코드를 삽입
  function page_insertafter($paging, $next_part_href) {
    foreach ($paging as $key => $value) {
      if($paging[$key]['name'] == '다음') {
        $paging[$key]['name'] = '다음검색';
        $paging[$key]['url'] = $next_part_href;
      }
    }

    return $paging;
  }
  // 사용포인트 입력
  public function insert_use_point($mb_id, $point, $po_id='')
  {
    global $g5;
    $config = $this->config;
    if($config['cf_point_term'])
      $sql_order = " order by po_expire_date asc, po_id asc ";
    else
      $sql_order = " order by po_id asc ";
  
    $point1 = abs($point);
    $sql = "SELECT po_id, po_point, po_use_point
            FROM {$g5['point_table']}
            WHERE mb_id = ?
            AND po_id <> ?
            AND po_expired = ?
            AND ?
            $sql_order ";
    $result = $this->sql_query($sql, [$mb_id, $po_id, '0', 'po_point > po_use_point']);
    for($i=0; $i<count($result); $i++) {
      $row = $result[$i];
      $point2 = $row['po_point'];
      $point3 = $row['po_use_point'];

      if(($point2 - $point3) > $point1) {
        $sql = "UPDATE {$g5['point_table']}
                SET po_use_point = ?
                WHERE po_id = ?";
        sql_query($sql);
        $this->sql_query($sql, ['po_use_point + '.$point1, $row['po_id']]);
        break;
      } else {
        $point4 = $point2 - $point3;
        $sql = "UPDATE {$g5['point_table']}
                SET po_use_point = ?,
                    po_expired = ?'
                WHERE po_id = ?";
        $this->sql_query($sql, ['po_use_point + '.$point4, '100', $row['po_id']]);
        $point1 -= $point4;
      }
    }
  }
  
  // 사용포인트 삭제
  public function delete_use_point($mb_id, $point) {
    global $g5;
    $config = $this->config;

    if($config['cf_point_term'])
      $sql_order = " order by po_expire_date desc, po_id desc ";
    else
      $sql_order = " order by po_id desc ";

    $point1 = abs($point);
    $sql = "SELECT po_id, po_use_point, po_expired, po_expire_date
            FROM {$g5['point_table']}
            WHERE mb_id = ?
            AND ?
            AND ?
            $sql_order ";
    $result = $this->sql_query($sql, [$mb_id, "po_expired <> '1'", "po_use_point > 0"]);
    for($i=0; $i<count($result); $i++) {
      $row = $result[$i];
      $point2 = $row['po_use_point'];

      $po_expired = $row['po_expired'];
      if($row['po_expired'] == 100 && ($row['po_expire_date'] == '9999-12-31' || $row['po_expire_date'] >= G5_TIME_YMD))
        $po_expired = 0;

      if($point2 > $point1) {
        $sql = "UPDATE {$g5['point_table']}
                SET po_use_point = ?,
                    po_expired = ?
                WHERE po_id = ?";
        $this->sql_query($sql, ["po_use_point - '$point1'", $po_expired, $row['po_id']]);
        break;
      } else {
          $sql = "UPDATE {$g5['point_table']}
                  SET po_use_point = ?,
                      po_expired = ?
                  WHERE po_id = ?";
          $this->sql_query($sql, ["0", $po_expired, $row['po_id']]);
          $point1 -= $point2;
      }
    }
  }
  
  // 소멸포인트 삭제
  public function delete_expire_point($mb_id, $point) {
      global $g5;
      $config = $this->config;
  
      $point1 = abs($point);
      $sql = "SELECT po_id, po_use_point, po_expired, po_expire_date
              FROM {$g5['point_table']}
              WHERE mb_id = ?
              AND po_expired = ?
              AND po_point >= ?
              AND po_use_point > ?
              ORDER BY po_expire_date DESC, po_id DESC";
      $result = $this->sql_query($sql, [$mb_id, '1', 0, 0]);
      for($i=0; $i<count($result); $i++) {
        $row = $result[$i];
        $point2 = $row['po_use_point'];
        $po_expired = '0';
        $po_expire_date = '9999-12-31';
        if($config['cf_point_term'] > 0)
          $po_expire_date = date('Y-m-d', strtotime('+'.($config['cf_point_term'] - 1).' days', G5_SERVER_TIME));

        if($point2 > $point1) {
          $sql = "UPDATE {$g5['point_table']}
                  SET po_use_point = po_use_point - ?,
                      po_expired = ?,
                      po_expire_date = ?
                  WHERE po_id = ?";
          $this->sql_query($sql, [$point1, $po_expired, $po_expire_date, $row['po_id']]);
          break;
        } else {
          $sql = "UPDATE {$g5['point_table']}
                  SET po_use_point = ?,
                      po_expired = ?,
                      po_expire_date = ?
                  WHERE po_id = ?";
          $this->sql_query($sql, ['0', $po_expired, $po_expire_date, $row['po_id']]);
          $point1 -= $point2;
        }
      }
  }
  
  // 회원 정보를 얻는다.
  function get_member($mb_id, $fields='*', $is_cache=false) {
    global $g5;
    if (preg_match("/[^0-9a-z_]+/i", $mb_id))
      return array();
    static $cache = array();
    $key = md5($fields);
    if( $is_cache && isset($cache[$mb_id]) && isset($cache[$mb_id][$key]) ){
      return $cache[$mb_id][$key];
    }
    $sql = " select $fields from {$g5['member_table']} where mb_id = TRIM('$mb_id') ";
    $cache[$mb_id][$key] = run_replace('get_member', $this->sql_fetch($sql), $mb_id, $fields, $is_cache);
    return $cache[$mb_id][$key];
  }

  // 포인트 내역 합계
  public function get_point_sum($mb_id) {
    global $g5;
    $config = $this->config;
    if($config['cf_point_term'] > 0) {
      // 소멸포인트가 있으면 내역 추가
      $expire_point = $this->get_expire_point($mb_id);
      if($expire_point > 0) {
        $mb = $this->sql_fetch("SELECT mb_point FROM {$g5['member_table']} WHERE mb_id = ?", [$mb_id]);
        $content = '포인트 소멸';
        $rel_table = '@expire';
        $rel_id = $mb_id;
        $rel_action = 'expire'.'-'.uniqid('');
        $point = $expire_point * (-1);
        $po_mb_point = $mb['mb_point'] + $point;
        $po_expire_date = G5_TIME_YMD;
        $po_expired = 1;

        $sql = "INSERT INTO {$g5['point_table']}
                SET mb_id = ?,
                    po_datetime = ?,
                    po_content = ?,
                    po_point = ?,
                    po_use_point = ?,
                    po_mb_point = ?,
                    po_expired = ?,
                    po_expire_date = ?,
                    po_rel_table = ?,
                    po_rel_id = ?,
                    po_rel_action = ?";
        $this->sql_query($sql, [$mb_id, G5_TIME_YMDHIS, addslashes($content), $point, '0', $po_mb_point, $po_expired, $po_expire_date, $rel_table, $rel_id, $rel_action]);
        // 포인트를 사용한 경우 포인트 내역에 사용금액 기록
        if($point < 0) {
          $this->insert_use_point($mb_id, $point);
        }
      }

      // 유효기간이 있을 때 기간이 지난 포인트 expired 체크
      $sql = "UPDATE {$g5['point_table']}
              SET po_expired = ?
              WHERE mb_id = ?
              AND po_expired <> ?
              AND po_expire_date <> ?
              AND po_expire_date < ?";
      $this->sql_query($sql, ['1', $mb_id, '1', '9999-12-32', G5_TIME_YMD]);
    }

    // 포인트합
    $sql = "SELECT sum(po_point) as sum_po_point
            FROM {$g5['point_table']}
            WHERE mb_id = ?";
    $row = $this->sql_fetch($sql, [$mb_id]);

    return $row['sum_po_point'];
  }
  
  // 소멸 포인트
  public function get_expire_point($mb_id) {
    global $g5;
    $config = $this->$config;
    if($config['cf_point_term'] == 0)
      return 0;
    $sql = "SELECT sum(po_point - po_use_point) as sum_point
            from {$g5['point_table']}
            WHERE mb_id = ?
            AND po_expired = '0'
            AND po_expire_date <> '9999-12-31'
            AND po_expire_date < '".G5_TIME_YMD."' ";
    $row = $this->sql_fetch($sql, [$mb_id]);

    return $row['sum_point'];
  }
  
  // 포인트 삭제
  public function delete_point($mb_id, $rel_table, $rel_id, $rel_action) {
    global $g5;

    $result = false;
    if ($rel_table || $rel_id || $rel_action) {
      // 포인트 내역정보
      $sql = " select * from {$g5['point_table']}
              where mb_id = '$mb_id'
                and po_rel_table = '$rel_table'
                and po_rel_id = '$rel_id'
                and po_rel_action = '$rel_action' ";
      $this->$row = $this->sql_fetch($sql);

      if($row['po_point'] < 0) {
        $mb_id = $row['mb_id'];
        $po_point = abs($row['po_point']);

        $this->delete_use_point($mb_id, $po_point);
      } else {
        if($row['po_use_point'] > 0) {
          $this->insert_use_point($row['mb_id'], $row['po_use_point'], $row['po_id']);
        }
      }

      $result = $this->sql_query(" delete from {$g5['point_table']}
                where mb_id = '$mb_id'
                  and po_rel_table = '$rel_table'
                  and po_rel_id = '$rel_id'
                  and po_rel_action = '$rel_action'");
      // po_mb_point에 반영
      if(isset($row['po_point'])) {
        $sql = " update {$g5['point_table']}
                set po_mb_point = po_mb_point - '{$row['po_point']}'
                where mb_id = '$mb_id'
                  and po_id > '{$row['po_id']}' ";
        $this->sql_query($sql);
      }

      // 포인트 내역의 합을 구하고
      $sum_point = $this->get_point_sum($mb_id);

      // 포인트 UPDATE
      $sql = " update {$g5['member_table']} set mb_point = '$sum_point' where mb_id = '$mb_id' ";
      $result = $this->sql_query($sql);
    }
    return $result;
  }


  // 게시판 최신글 캐시 파일 삭제
  public function delete_cache_latest($bo_table) {
    if (!preg_match("/^([A-Za-z0-9_]{1,20})$/", $bo_table)) {
      return;
    }

    g5_delete_cache_by_prefix('latest-'.$bo_table.'-');
  }

  // 게시판 첨부파일 썸네일 삭제
  public function delete_board_thumbnail($bo_table, $file) {
    if(!$bo_table || !$file)
      return;

    $fn = preg_replace("/\.[^\.]+$/i", "", basename($file));
    $files = glob(G5_DATA_PATH.'/file/'.$bo_table.'/thumb-'.$fn.'*');
    if (is_array($files)) {
      foreach ($files as $filename)
        unlink($filename);
    }
  }

  // 에디터 이미지 얻기
  public function get_editor_image($contents, $view=true) {
    if(!$contents)
      return false;

    // $contents 중 img 태그 추출
    if ($view)
      $pattern = "/<img([^>]*)>/iS";
    else
      $pattern = "/<img[^>]*src=[\'\"]?([^>\'\"]+[^>\'\"]+)[\'\"]?[^>]*>/i";
    preg_match_all($pattern, $contents, $matchs);

    return $matchs;
  }

  // 에디터 썸네일 삭제
  public function delete_editor_thumbnail($contents) {
    if(!$contents)
      return;
    
    run_event('delete_editor_thumbnail_before', $contents);

    // $contents 중 img 태그 추출
    $matchs = $this->get_editor_image($contents, false);

    if(!$matchs)
      return;

    for($i=0; $i<count($matchs[1]); $i++) {
      // 이미지 path 구함
      $imgurl = @parse_url($matchs[1][$i]);
      $srcfile = dirname(G5_PATH).$imgurl['path'];
      if(! preg_match('/(\.jpe?g|\.gif|\.png)$/i', $srcfile)) continue;
      $filename = preg_replace("/\.[^\.]+$/i", "", basename($srcfile));
      $filepath = dirname($srcfile);
      $files = glob($filepath.'/thumb-'.$filename.'*');
      if (is_array($files)) {
        foreach($files as $filename)
          unlink($filename);
      }
    }

    run_event('delete_editor_thumbnail_after', $contents, $matchs);
  }

  // 1:1문의 첨부파일 썸네일 삭제
  public function delete_qa_thumbnail($file) {
    if(!$file)
        return;

    $fn = preg_replace("/\.[^\.]+$/i", "", basename($file));
    $files = glob(G5_DATA_PATH.'/qa/thumb-'.$fn.'*');
    if (is_array($files)) {
      foreach ($files as $filename)
        unlink($filename);
    }
  }

  /*******************************************************************************
    유일한 키를 얻는다.

    결과 :

        년월일시분초00 ~ 년월일시분초99
        년(4) 월(2) 일(2) 시(2) 분(2) 초(2) 100분의1초(2)
        총 16자리이며 년도는 2자리로 끊어서 사용해도 됩니다.
        예) 2008062611570199 또는 08062611570199 (2100년까지만 유일키)

    사용하는 곳 :
    1. 게시판 글쓰기시 미리 유일키를 얻어 파일 업로드 필드에 넣는다.
    2. 주문번호 생성시에 사용한다.
    3. 기타 유일키가 필요한 곳에서 사용한다.
  *******************************************************************************/
  // 기존의 get_unique_id() 함수를 사용하지 않고 get_uniqid() 를 사용한다.
  public function get_uniqid() {
    global $g5;
    $this->sql_query(" LOCK TABLE {$g5['uniqid_table']} WRITE ");
    while (true) {
      // 년월일시분초에 100분의 1초 두자리를 추가함 (1/100 초 앞에 자리가 모자르면 0으로 채움)
      $key = date('YmdHis', time()) . str_pad((int)((float)microtime()*100), 2, "0", STR_PAD_LEFT);

      $result = $this->sql_result(" insert into {$g5['uniqid_table']} set uq_id = '$key', uq_ip = '{$_SERVER['REMOTE_ADDR']}' ");
      if ($result) break; // 쿼리가 정상이면 빠진다.
      // insert 하지 못했으면 일정시간 쉰다음 다시 유일키를 만든다.
      usleep(10000); // 100분의 1초를 쉰다
    }
    $this->sql_query("UNLOCK TABLES");
    return $key;
  }


  // QUERY STRING 에 포함된 XSS 태그 제거
  public function clean_query_string($query, $amp=true) {
    $qstr = trim($query);

    parse_str($qstr, $out);

    if(is_array($out)) {
        $q = array();

      foreach($out as $key=>$val) {
        if(($key && is_array($key)) || ($val && is_array($val))){
            $q[$key] = $val;
            continue;
        }

        $key = strip_tags(trim($key));
        $val = trim($val);

        switch($key) {
          case 'wr_id':
            $val = (int)preg_replace('/[^0-9]/', '', $val);
            $q[$key] = $val;
            break;
          case 'sca':
            $val = clean_xss_tags($val);
            $q[$key] = $val;
            break;
          case 'sfl':
            $val = preg_replace("/[\<\>\'\"\\\'\\\"\%\=\(\)\s]/", "", $val);
            $q[$key] = $val;
            break;
          case 'stx':
            $val = get_search_string($val);
            $q[$key] = $val;
            break;
          case 'sst':
            $val = preg_replace("/[\<\>\'\"\\\'\\\"\%\=\(\)\s]/", "", $val);
            $q[$key] = $val;
            break;
          case 'sod':
            $val = preg_match("/^(asc|desc)$/i", $val) ? $val : '';
            $q[$key] = $val;
            break;
          case 'sop':
            $val = preg_match("/^(or|and)$/i", $val) ? $val : '';
            $q[$key] = $val;
            break;
          case 'spt':
            $val = (int)preg_replace('/[^0-9]/', '', $val);
            $q[$key] = $val;
            break;
          case 'page':
            $val = (int)preg_replace('/[^0-9]/', '', $val);
            $q[$key] = $val;
            break;
          case 'w':
            $val = substr($val, 0, 2);
            $q[$key] = $val;
            break;
          case 'bo_table':
            $val = preg_replace('/[^a-z0-9_]/i', '', $val);
            $val = substr($val, 0, 20);
            $q[$key] = $val;
            break;
          case 'gr_id':
            $val = preg_replace('/[^a-z0-9_]/i', '', $val);
            $q[$key] = $val;
            break;
          default:
            $val = clean_xss_tags($val);
            $q[$key] = $val;
            break;
        }
      }

      if($amp)
        $sep = '&amp;';
      else
        $sep ='&';

      $str = http_build_query($q, '', $sep);
    } else {
      $str = $this->clean_xss_tags($qstr);
    }

    return $str;
  }


  public function check_mail_bot($ip=''){
    //아이피를 체크하여 메일 크롤링을 방지합니다.
    $check_ips = array('211.249.40.');
    $bot_message = 'bot 으로 판단되어 중지합니다.';
    
    if($ip){
      foreach( $check_ips as $c_ip ){
        if( preg_match('/^'.preg_quote($c_ip).'/', $ip) ) {
          die($bot_message);
        }
      }
    }

    // user agent를 체크하여 메일 크롤링을 방지합니다.
    $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    if ($user_agent === 'Carbon' || strpos($user_agent, 'BingPreview') !== false || strpos($user_agent, 'Slackbot') !== false) { 
      die($bot_message);
    } 
  }
  public function unset_data($data) { //권한이 없는 사용자들에게 노출되면 안되는 그누보드 내용
    if(!$this->is_admin) {
      unset($data['cf_icode_id']);
      unset($data['cf_icode_pw']);
      unset($data['cf_googl_shorturl_apikey']);
      unset($data['cf_google_clientid']);
      unset($data['cf_google_secret']);
      unset($data['cf_icode_server_ip']);
      unset($data['cf_icode_server_port']);
      unset($data['cf_icode_token_key']);
      unset($data['cf_icode_token_key']);
      unset($data['config']['cf_icode_id']);
      unset($data['config']['cf_icode_pw']);
      unset($data['config']['cf_googl_shorturl_apikey']);
      unset($data['config']['cf_google_clientid']);
      unset($data['config']['cf_google_secret']);
      unset($data['config']['cf_icode_server_ip']);
      unset($data['config']['cf_icode_server_port']);
      unset($data['config']['cf_icode_token_key']);
      unset($data['config']['cf_icode_token_key']);
      unset($data['member']['mb_password']);
      unset($data['ss_name']);
      unset($data['sst']);
      unset($data['stx']);
      unset($data['sql']);
      unset($data['sql2']);
      unset($data['sql3']);
      unset($data['sql_search']);
      unset($data['sql_common']);
      unset($data['sql_order']);
      unset($data['result']);
      unset($data['config']['cf_recaptcha_secret_key']);
      unset($data['config']['cf_recaptcha_site_key']);
      if(is_array($data)){
        for ($i=0; $i < count($data); $i++) { 
          if(isset($data[$i]['wr_ip'])) $data[$i]['wr_ip'] = preg_replace("/([0-9]+).([0-9]+).([0-9]+).([0-9]+)/", G5_IP_DISPLAY, $data[$i]['wr_ip']);
          if(is_array($data[$i])){
            for ($k=0; $k < count($data[$i]); $k++) { 
              if(isset($data[$i][$k]['wr_password']))
                $data[$i][$k]['wr_password'] = "";
            }
          }
          if(isset($data[$i]['wr_password']))
            $data[$i]['wr_password'] = "";
        }
      }
      if(isset($data['wr_password']))
        $data['wr_password'] = "";
      if(isset($data['wr_ip'])) $data['wr_ip'] = preg_replace("/([0-9]+).([0-9]+).([0-9]+).([0-9]+)/", G5_IP_DISPLAY, $data['wr_ip']);

      unset($data['mb_password']);
      unset($data['mb_login_ip']);
      unset($data['mb_ip']);
      unset($data['mb_email']);
      unset($data['mb_addr1']);
      unset($data['mb_addr2']);
      unset($data['mb_addr3']);
      unset($data['mb_addr_jibeon']);
      unset($data['mb_birth']);
      unset($data['mb_tel']);
      unset($data['mb_hp']);
    }
    return $data;
  }
}