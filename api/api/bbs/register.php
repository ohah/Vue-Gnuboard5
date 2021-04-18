<?php
trait register {
  public function register_form() {
    global $g5;
    $_POST = isset($_POST['agree']) ? $_POST : $this->getPostData();
    @extract($_POST);
    $member = $this->member;
    $config = $this->config;
    $is_admin = $this->is_admin;
    $is_guest = $this->$is_guest;
    $is_member = $this->is_member;
    run_event('register_form_before');
    
    // 불법접근을 막도록 토큰생성
    $token = md5(uniqid(rand(), true));
    $this->set_session("ss_token", $token);
    $this->set_session("ss_cert_no",   "");
    $this->set_session("ss_cert_hash", "");
    $this->set_session("ss_cert_type", "");

    $is_social_login_modify = false;

    /*
    if( isset($_REQUEST['provider']) && $_REQUEST['provider']  && function_exists('social_nonce_is_valid') ){   //모바일로 소셜 연결을 했다면
      if( social_nonce_is_valid($this->get_session("social_link_token"), $provider) ){  //토큰값이 유효한지 체크
        $w = 'u';   //회원 수정으로 처리
        $_POST['mb_id'] = $member['mb_id'];
        $is_social_login_modify = true;
      }
    }
    */

    if ($w == "") {

      // 회원 로그인을 한 경우 회원가입 할 수 없다
      // 경고창이 뜨는것을 막기위해 아래의 코드로 대체
      // alert("이미 로그인중이므로 회원 가입 하실 수 없습니다.", "./");
      if ($is_member) {
        $this->alert('이미 로그인 중이므로 회원 가입 하실 수 없습니다.');
      }
  
      // 리퍼러 체크
      $this->referer_check();
  
      if (!isset($_POST['agree']) || !$_POST['agree']) {
        $this->alert('회원가입약관의 내용에 동의하셔야 회원가입 하실 수 있습니다.', G5_BBS_URL.'/register.php');
      }
  
      if (!isset($_POST['agree2']) || !$_POST['agree2']) {
        $this->alert('개인정보처리방침안내의 내용에 동의하셔야 회원가입 하실 수 있습니다.', G5_BBS_URL.'/register.php');
      }
  
      $agree  = preg_replace('#[^0-9]#', '', $_POST['agree']);
      $agree2 = preg_replace('#[^0-9]#', '', $_POST['agree2']);
  
      $member['mb_birth'] = '';
      $member['mb_sex']   = '';
      $member['mb_name']  = '';
      if (isset($_POST['birth'])) {
        $member['mb_birth'] = $_POST['birth'];
      }
      if (isset($_POST['sex'])) {
        $member['mb_sex']   = $_POST['sex'];
      }
      if (isset($_POST['mb_name'])) {
        $member['mb_name']  = $_POST['mb_name'];
      }
  
      $g5['title'] = '회원 가입';
  
    } else if ($w == 'u') {    
      if ($is_admin)
        $this->alert('관리자의 회원정보는 관리자 화면에서 수정해 주십시오.', G5_URL);
  
      if (!$is_member)
        $this->alert('로그인 후 이용하여 주십시오.', G5_URL);
  
      if ($member['mb_id'] != $_POST['mb_id'])
        $this->alert('로그인된 회원과 넘어온 정보가 서로 다릅니다.');

        if($_POST['mb_id'] && ! (isset($_POST['mb_password']) && $_POST['mb_password'])){
          if( ! $is_social_login_modify ){
            $this->alert('비밀번호를 입력해 주세요.');
          }
        }
  
      if (isset($_POST['mb_password'])) {
        // 수정된 정보를 업데이트후 되돌아 온것이라면 비밀번호가 암호화 된채로 넘어온것임
        if (isset($_POST['is_update']) && $_POST['is_update']) {
          $tmp_password = $_POST['mb_password'];
          $pass_check = ($member['mb_password'] === $tmp_password);
        } else {
          $pass_check = $this->check_password($_POST['mb_password'], $member['mb_password']);
        }

        if (!$pass_check)
          $this->alert('비밀번호가 틀립니다.');
      }

      $g5['title'] = '회원 정보 수정';
  
      $this->set_session("ss_reg_mb_name", $member['mb_name']);
      $this->set_session("ss_reg_mb_hp", $member['mb_hp']);
  
      $member['mb_email']       = $this->get_text($member['mb_email']);
      $member['mb_homepage']    = $this->get_text($member['mb_homepage']);
      $member['mb_birth']       = $this->get_text($member['mb_birth']);
      $member['mb_tel']         = $this->get_text($member['mb_tel']);
      $member['mb_hp']          = $this->get_text($member['mb_hp']);
      $member['mb_addr1']       = $this->get_text($member['mb_addr1']);
      $member['mb_addr2']       = $this->get_text($member['mb_addr2']);
      $member['mb_signature']   = $this->get_text($member['mb_signature']);
      $member['mb_recommend']   = $this->get_text($member['mb_recommend']);
      $member['mb_profile']     = $this->get_text($member['mb_profile']);
      $member['mb_1']           = $this->get_text($member['mb_1']);
      $member['mb_2']           = $this->get_text($member['mb_2']);
      $member['mb_3']           = $this->get_text($member['mb_3']);
      $member['mb_4']           = $this->get_text($member['mb_4']);
      $member['mb_5']           = $this->get_text($member['mb_5']);
      $member['mb_6']           = $this->get_text($member['mb_6']);
      $member['mb_7']           = $this->get_text($member['mb_7']);
      $member['mb_8']           = $this->get_text($member['mb_8']);
      $member['mb_9']           = $this->get_text($member['mb_9']);
      $member['mb_10']          = $this->get_text($member['mb_10']);
    } else {
      $this->alert('w 값이 제대로 넘어오지 않았습니다.');
    }

    // 회원아이콘 경로
    $mb_icon_path = G5_DATA_PATH.'/member/'.substr($member['mb_id'],0,2).'/'.$this->get_mb_icon_name($member['mb_id']).'.gif';
    $mb_icon_filemtile = (defined('G5_USE_MEMBER_IMAGE_FILETIME') && G5_USE_MEMBER_IMAGE_FILETIME && file_exists($mb_icon_path)) ? '?'.filemtime($mb_icon_path) : '';
    $mb_icon_url  = G5_DATA_URL.'/member/'.substr($member['mb_id'],0,2).'/'.$this->get_mb_icon_name($member['mb_id']).'.gif'.$mb_icon_filemtile;

    // 회원이미지 경로
    $mb_img_path = G5_DATA_PATH.'/member_image/'.substr($member['mb_id'],0,2).'/'.$this->get_mb_icon_name($member['mb_id']).'.gif';
    $mb_img_filemtile = (defined('G5_USE_MEMBER_IMAGE_FILETIME') && G5_USE_MEMBER_IMAGE_FILETIME && file_exists($mb_img_path)) ? '?'.filemtime($mb_img_path) : '';
    $mb_img_url  = G5_DATA_URL.'/member_image/'.substr($member['mb_id'],0,2).'/'.$this->get_mb_icon_name($member['mb_id']).'.gif'.$mb_img_filemtile;

    //$register_action_url = G5_HTTPS_BBS_URL.'/register_form_update.php';
    $req_nick = !isset($member['mb_nick_date']) || (isset($member['mb_nick_date']) && $member['mb_nick_date'] <= date("Y-m-d", G5_SERVER_TIME - ($config['cf_nick_modify'] * 86400)));
    $required = ($w=='') ? 'required' : '';
    $readonly = ($w=='u') ? 'readonly' : '';

    $agree  = isset($_REQUEST['agree']) ? preg_replace('#[^0-9]#', '', $_REQUEST['agree']) : '';
    $agree2 = isset($_REQUEST['agree2']) ? preg_replace('#[^0-9]#', '', $_REQUEST['agree2']) : '';
    
    run_event('register_form_after', $w, $agree, $agree2);
    
    unset($config['cf_icode_id']);
    unset($config['cf_icode_pw']);
    unset($config['cf_googl_shorturl_apikey']);
    unset($config['cf_google_clientid']);
    unset($config['cf_google_secret']);
    unset($config['cf_icode_server_ip']);
    unset($config['cf_icode_server_port']);
    unset($config['cf_icode_token_key']);
    unset($config['cf_icode_token_key']);
    unset($config['cf_recaptcha_secret_key']);
    unset($config['cf_recaptcha_site_key']);
    unset($config['cf_admin']);
    unset($config['cf_admin_email']);
    unset($config["cf_cert_use"]);
    unset($config["cf_cert_ipin"]);
    unset($config["cf_cert_hp"]);
    unset($config["cf_cert_kcb_cd"]);
    unset($config["cf_cert_kcp_cd"]);
    unset($config["cf_lg_mid"]);
    unset($config["cf_lg_mert_key"]);
    unset($config["cf_cert_limit"]);
    unset($config["cf_cert_req"]);
    unset($config["cf_sms_use"]);
    unset($config["cf_sms_type"]);
    unset($config["cf_social_login_use"]);
    unset($config["cf_social_servicelist"]);
    unset($config["cf_payco_clientid"]);
    unset($config["cf_payco_secret"]);
    unset($config["cf_facebook_appid"]);
    unset($config["cf_facebook_secret"]);
    unset($config["cf_twitter_key"]);
    unset($config["cf_twitter_secret"]);
    unset($config["cf_naver_clientid"]);
    unset($config["cf_naver_secret"]);
    unset($config["cf_kakao_rest_key"]);
    unset($config["cf_kakao_client_secret"]);
    unset($config["cf_kakao_js_apikey"]);
    unset($config["cf_1_subj"]);
    unset($config["cf_2_subj"]);
    unset($config["cf_3_subj"]);
    unset($config["cf_4_subj"]);
    unset($config["cf_5_subj"]);
    unset($config["cf_6_subj"]);
    unset($config["cf_7_subj"]);
    unset($config["cf_8_subj"]);
    unset($config["cf_9_subj"]);
    unset($config["cf_10_subj"]);
    unset($config["cf_1"]);
    unset($config["cf_2"]);
    unset($config["cf_3"]);
    unset($config["cf_4"]);
    unset($config["cf_5"]);
    unset($config["cf_6"]);
    unset($config["cf_7"]);
    unset($config["cf_8"]);
    unset($config["cf_9"]);
    unset($config["cf_10"]);
    $result = array();
    $result['config'] = $config;
    $result['w'] = $w;
    $result['agree'] = $agree;
    $result['agree2'] = $agree2;
    $result['cert_type'] = $member['mb_certify'];
    $reulst['mb_sex'] = $member['mb_sex'];
    $reulst['mb_nick_date'] = $member['mb_nick_date'];
    if (isset($member['mb_nick_date']) && $member['mb_nick_date'] > date("Y-m-d", G5_SERVER_TIME - ($config['cf_nick_modify'] * 86400))) {
      $reulst['mb_nick_default'] = $this->get_text($member['mb_nick']);
      $reulst['mb_nick'] = $this->get_text($member['mb_nick']);
    }
    $result['member'] = $member;
    $result['req_nick'] = $req_nick;
    if(file_exists($mb_img_path)) {
      $result['mb_img_url'] = $mb_img_url;
    }
    $result['G5_SERVER_TIME'] = G5_SERVER_TIME;
    $result['captcha_html'] = $this->captcha_html();
    return $this->data_encode($result);
  }
  public function register () {
    $config = $this->config;
    $this->set_session("ss_mb_reg", "");
    $result = array();
    $result['cf_stipulation'] = $this->get_text($config['cf_stipulation']);
    return $this->data_encode($result);
  }
  public function register_form_update() {
    global $g5;
    $w = $_POST['w'] ? $_POST['w'] : '';
    $member = $this->member;
    $config = $this->config;
    $is_admin = $this->is_admin;
    $is_guest = $this->$is_guest;
    $is_member = $this->is_member;

    //리퍼러 체크
    $this->referer_check();

    if (!($w == '' || $w == 'u')) {
      $this->alert('w 값이 제대로 넘어오지 않았습니다.');
    }
    
    if ($w == 'u' && $is_admin == 'super') {
      if (file_exists(G5_PATH.'/DEMO'))
        $this->alert('데모 화면에서는 하실(보실) 수 없는 작업입니다.');
    }
    
    if (!$this->chk_captcha()) {
      $this->alert('자동등록방지 숫자가 틀렸습니다.');
    }
    
    if($w == 'u')
      $mb_id = isset($_SESSION['ss_mb_id']) ? trim($_SESSION['ss_mb_id']) : '';
    else if($w == '')
      $mb_id = isset($_POST['mb_id']) ? trim($_POST['mb_id']) : '';
    else
      $this->alert('잘못된 접근입니다', G5_URL);
    
    if(!$mb_id)
      $this->alert('회원아이디 값이 없습니다. 올바른 방법으로 이용해 주십시오.');

    $mb_password    = isset($_POST['mb_password']) ? trim($_POST['mb_password']) : '';
    $mb_password_re = isset($_POST['mb_password_re']) ? trim($_POST['mb_password_re']) : '';
    $mb_name        = isset($_POST['mb_name']) ? trim($_POST['mb_name']) : '';
    $mb_nick        = isset($_POST['mb_name']) ? trim($_POST['mb_nick']) : '';
    $mb_email       = isset($_POST['mb_name']) ? trim($_POST['mb_email']) : '';
    $mb_sex         = isset($_POST['mb_sex'])           ? trim($_POST['mb_sex'])         : "";
    $mb_birth       = isset($_POST['mb_birth'])         ? trim($_POST['mb_birth'])       : "";
    $mb_homepage    = isset($_POST['mb_homepage'])      ? trim($_POST['mb_homepage'])    : "";
    $mb_tel         = isset($_POST['mb_tel'])           ? trim($_POST['mb_tel'])         : "";
    $mb_hp          = isset($_POST['mb_hp'])            ? trim($_POST['mb_hp'])          : "";
    $mb_zip1        = isset($_POST['mb_zip'])           ? substr(trim($_POST['mb_zip']), 0, 3) : "";
    $mb_zip2        = isset($_POST['mb_zip'])           ? substr(trim($_POST['mb_zip']), 3)    : "";
    $mb_addr1       = isset($_POST['mb_addr1'])         ? trim($_POST['mb_addr1'])       : "";
    $mb_addr2       = isset($_POST['mb_addr2'])         ? trim($_POST['mb_addr2'])       : "";
    $mb_addr3       = isset($_POST['mb_addr3'])         ? trim($_POST['mb_addr3'])       : "";
    $mb_addr_jibeon = isset($_POST['mb_addr_jibeon'])   ? trim($_POST['mb_addr_jibeon']) : "";
    $mb_signature   = isset($_POST['mb_signature'])     ? trim($_POST['mb_signature'])   : "";
    $mb_profile     = isset($_POST['mb_profile'])       ? trim($_POST['mb_profile'])     : "";
    $mb_recommend   = isset($_POST['mb_recommend'])     ? trim($_POST['mb_recommend'])   : "";
    $mb_mailling    = isset($_POST['mb_mailling'])      ? trim($_POST['mb_mailling'])    : "";
    $mb_sms         = isset($_POST['mb_sms'])           ? trim($_POST['mb_sms'])         : "";
    $mb_1           = isset($_POST['mb_1'])             ? trim($_POST['mb_1'])           : "";
    $mb_2           = isset($_POST['mb_2'])             ? trim($_POST['mb_2'])           : "";
    $mb_3           = isset($_POST['mb_3'])             ? trim($_POST['mb_3'])           : "";
    $mb_4           = isset($_POST['mb_4'])             ? trim($_POST['mb_4'])           : "";
    $mb_5           = isset($_POST['mb_5'])             ? trim($_POST['mb_5'])           : "";
    $mb_6           = isset($_POST['mb_6'])             ? trim($_POST['mb_6'])           : "";
    $mb_7           = isset($_POST['mb_7'])             ? trim($_POST['mb_7'])           : "";
    $mb_8           = isset($_POST['mb_8'])             ? trim($_POST['mb_8'])           : "";
    $mb_9           = isset($_POST['mb_9'])             ? trim($_POST['mb_9'])           : "";
    $mb_10          = isset($_POST['mb_10'])            ? trim($_POST['mb_10'])          : "";
    
    $mb_name        = $this->clean_xss_tags($mb_name);
    $mb_email       = $this->get_email_address($mb_email);
    $mb_homepage    = $this->clean_xss_tags($mb_homepage);
    $mb_tel         = $this->clean_xss_tags($mb_tel);
    $mb_zip1        = preg_replace('/[^0-9]/', '', $mb_zip1);
    $mb_zip2        = preg_replace('/[^0-9]/', '', $mb_zip2);
    $mb_addr1       = $this->clean_xss_tags($mb_addr1);
    $mb_addr2       = $this->clean_xss_tags($mb_addr2);
    $mb_addr3       = $this->clean_xss_tags($mb_addr3);
    $mb_addr_jibeon = preg_match("/^(N|R)$/", $mb_addr_jibeon) ? $mb_addr_jibeon : '';

    run_event('register_form_update_before', $mb_id, $w);


    if ($w == '' || $w == 'u') {

      if ($msg = $this->empty_mb_id($mb_id))         $this->alert($msg, ""); // alert($msg, $url, $error, $post);
      if ($msg = $this->valid_mb_id($mb_id))         $this->alert($msg, "");
      if ($msg = $this->count_mb_id($mb_id))         $this->alert($msg, "");
  
      // 이름, 닉네임에 utf-8 이외의 문자가 포함됐다면 오류
      // 서버환경에 따라 정상적으로 체크되지 않을 수 있음.
      $tmp_mb_name = iconv('UTF-8', 'UTF-8//IGNORE', $mb_name);
      if($tmp_mb_name != $mb_name) {
        $this->alert('이름을 올바르게 입력해 주십시오.');
      }
      $tmp_mb_nick = iconv('UTF-8', 'UTF-8//IGNORE', $mb_nick);
      if($tmp_mb_nick != $mb_nick) {
        $this->alert('닉네임을 올바르게 입력해 주십시오.');
      }
  
      if ($w == '' && !$mb_password)
        $this->alert('비밀번호가 넘어오지 않았습니다.');
      if($w == '' && $mb_password != $mb_password_re)
        $this->alert('비밀번호가 일치하지 않습니다.');
  
      if ($msg = $this->empty_mb_name($mb_name))      $this->alert($msg, "", true, true);
      if ($msg = $this->empty_mb_nick($mb_nick))      $this->alert($msg, "", true, true);
      if ($msg = $this->empty_mb_email($mb_email))    $this->alert($msg, "", true, true);
      if ($msg = $this->reserve_mb_id($mb_id))        $this->alert($msg, "", true, true);
      if ($msg = $this->reserve_mb_nick($mb_nick))    $this->alert($msg, "", true, true);
      // 이름에 한글명 체크를 하지 않는다.
      //if ($msg = valid_mb_name($mb_name))     alert($msg, "", true, true);
      if ($msg = $this->valid_mb_nick($mb_nick))      $this->alert($msg, "", true, true);
      if ($msg = $this->valid_mb_email($mb_email))    $this->alert($msg, "", true, true);
      if ($msg = $this->prohibit_mb_email($mb_email)) $this->alert($msg, "", true, true);
  
      // 휴대폰 필수입력일 경우 휴대폰번호 유효성 체크
      if (($config['cf_use_hp'] || $config['cf_cert_hp']) && $config['cf_req_hp']) {
          if ($msg = $this->valid_mb_hp($mb_hp))     $this->alert($msg, "", true, true);
      }
  
      if ($w=='') {
        if ($msg = $this->exist_mb_id($mb_id))     $this->alert($msg);

        if ($this->get_session('ss_check_mb_id') != $mb_id || $this->get_session('ss_check_mb_nick') != $mb_nick || $this->get_session('ss_check_mb_email') != $mb_email) {
          $this->set_session('ss_check_mb_id', '');
          $this->set_session('ss_check_mb_nick', '');
          $this->set_session('ss_check_mb_email', '');

          //$this->alert('올바른 방법으로 이용해 주십시오.');
        }

        // 본인확인 체크
        if($config['cf_cert_use'] && $config['cf_cert_req']) {
          $post_cert_no = isset($_POST['cert_no']) ? trim($_POST['cert_no']) : '';
          if($post_cert_no !== $this->get_session('ss_cert_no') || ! $this->get_session('ss_cert_no'))
            $this->alert("회원가입을 위해서는 본인확인을 해주셔야 합니다.");
        }

        if ($config['cf_use_recommend'] && $mb_recommend) {
          if (!$this->exist_mb_id($mb_recommend))
            $this->alert("추천인이 존재하지 않습니다.");
        }

        if (strtolower($mb_id) == strtolower($mb_recommend)) {
          $this->alert('본인을 추천할 수 없습니다.');
        }
      } else {
        // 자바스크립트로 정보변경이 가능한 버그 수정
        // 닉네임수정일이 지나지 않았다면
        if ($member['mb_nick_date'] > date("Y-m-d", G5_SERVER_TIME - ($config['cf_nick_modify'] * 86400)))
            $mb_nick = $member['mb_nick'];
        // 회원정보의 메일을 이전 메일로 옮기고 아래에서 비교함
        $old_email = $member['mb_email'];
      }
  
      run_event('register_form_update_valid', $w, $mb_id, $mb_nick, $mb_email);
  
      if ($msg = $this->exist_mb_nick($mb_nick, $mb_id))     $this->alert($msg, "", true, true);
      if ($msg = $this->exist_mb_email($mb_email, $mb_id))   $this->alert($msg, "", true, true);
    }
    

    //===============================================================
    //  본인확인
    //---------------------------------------------------------------
    $mb_hp = $this->hyphen_hp_number($mb_hp);
    if($config['cf_cert_use'] && $this->get_session('ss_cert_type') && $this->get_session('ss_cert_dupinfo')) {
        // 중복체크
        $sql = " select mb_id from {$g5['member_table']} where mb_id <> '{$member['mb_id']}' and mb_dupinfo = '".$this->get_session('ss_cert_dupinfo')."' ";
        $row = $this->sql_fetch($sql);
        if ($row['mb_id']) {
          $this->alert("입력하신 본인확인 정보로 가입된 내역이 존재합니다.\\n회원아이디 : ".$row['mb_id']);
        }
    }

    $sql_certify = '';
    $md5_cert_no = $this->get_session('ss_cert_no');
    $cert_type = $this->get_session('ss_cert_type');
    if ($config['cf_cert_use'] && $cert_type && $md5_cert_no) {
      // 해시값이 같은 경우에만 본인확인 값을 저장한다.
      if ($this->get_session('ss_cert_hash') == md5($mb_name.$cert_type.$this->get_session('ss_cert_birth').$md5_cert_no)) {
        $sql_certify .= " , mb_hp = '{$mb_hp}' ";
        $sql_certify .= " , mb_certify  = '{$cert_type}' ";
        $sql_certify .= " , mb_adult = '".$this->get_session('ss_cert_adult')."' ";
        $sql_certify .= " , mb_birth = '".$this->get_session('ss_cert_birth')."' ";
        $sql_certify .= " , mb_sex = '".$this->get_session('ss_cert_sex')."' ";
        $sql_certify .= " , mb_dupinfo = '".$this->get_session('ss_cert_dupinfo')."' ";
        if($w == 'u')
          $sql_certify .= " , mb_name = '{$mb_name}' ";
      } else {
        $sql_certify .= " , mb_hp = '{$mb_hp}' ";
        $sql_certify .= " , mb_certify  = '' ";
        $sql_certify .= " , mb_adult = 0 ";
        $sql_certify .= " , mb_birth = '' ";
        $sql_certify .= " , mb_sex = '' ";
      }
    } else {
      if ($this->get_session("ss_reg_mb_name") != $mb_name || $this->get_session("ss_reg_mb_hp") != $mb_hp) {
        $sql_certify .= " , mb_hp = '{$mb_hp}' ";
        $sql_certify .= " , mb_certify = '' ";
        $sql_certify .= " , mb_adult = 0 ";
        $sql_certify .= " , mb_birth = '' ";
        $sql_certify .= " , mb_sex = '' ";
      }
    }
      



    //===============================================================
    if ($w == '') {
      $sql = " insert into {$g5['member_table']}
                  set mb_id = '{$mb_id}',
                      mb_password = '".$this->get_encrypt_string($mb_password)."',
                      mb_name = '{$mb_name}',
                      mb_nick = '{$mb_nick}',
                      mb_nick_date = '".G5_TIME_YMD."',
                      mb_email = '{$mb_email}',
                      mb_homepage = '{$mb_homepage}',
                      mb_tel = '{$mb_tel}',
                      mb_zip1 = '{$mb_zip1}',
                      mb_zip2 = '{$mb_zip2}',
                      mb_addr1 = '{$mb_addr1}',
                      mb_addr2 = '{$mb_addr2}',
                      mb_addr3 = '{$mb_addr3}',
                      mb_addr_jibeon = '{$mb_addr_jibeon}',
                      mb_signature = '{$mb_signature}',
                      mb_profile = '{$mb_profile}',
                      mb_today_login = '".G5_TIME_YMDHIS."',
                      mb_datetime = '".G5_TIME_YMDHIS."',
                      mb_ip = '{$_SERVER['REMOTE_ADDR']}',
                      mb_level = '{$config['cf_register_level']}',
                      mb_recommend = '{$mb_recommend}',
                      mb_login_ip = '{$_SERVER['REMOTE_ADDR']}',
                      mb_mailling = '{$mb_mailling}',
                      mb_sms = '{$mb_sms}',
                      mb_open = '{$mb_open}',
                      mb_open_date = '".G5_TIME_YMD."',
                      mb_1 = '{$mb_1}',
                      mb_2 = '{$mb_2}',
                      mb_3 = '{$mb_3}',
                      mb_4 = '{$mb_4}',
                      mb_5 = '{$mb_5}',
                      mb_6 = '{$mb_6}',
                      mb_7 = '{$mb_7}',
                      mb_8 = '{$mb_8}',
                      mb_9 = '{$mb_9}',
                      mb_10 = '{$mb_10}'
                      {$sql_certify} ";

      // 이메일 인증을 사용하지 않는다면 이메일 인증시간을 바로 넣는다
      if (!$config['cf_use_email_certify'])
          $sql .= " , mb_email_certify = '".G5_TIME_YMDHIS."' ";
      $this->sql_query($sql);

      // 회원가입 포인트 부여
      $this->insert_point($mb_id, $config['cf_register_point'], '회원가입 축하', '@member', $mb_id, '회원가입');

      // 추천인에게 포인트 부여
      if ($config['cf_use_recommend'] && $mb_recommend)
        $this->insert_point($mb_recommend, $config['cf_recommend_point'], $mb_id.'의 추천인', '@member', $mb_recommend, $mb_id.' 추천');

      // 회원님께 메일 발송
      if ($config['cf_email_mb_member']) {
        $subject = '['.$config['cf_title'].'] 회원가입을 축하드립니다.';

        // 어떠한 회원정보도 포함되지 않은 일회용 난수를 생성하여 인증에 사용
        if ($config['cf_use_email_certify']) {
            $mb_md5 = md5(pack('V*', rand(), rand(), rand(), rand()));
            $this->sql_query(" update {$g5['member_table']} set mb_email_certify2 = '$mb_md5' where mb_id = '$mb_id' ");
            $certify_href = G5_BBS_URL.'/email_certify.php?mb_id='.$mb_id.'&amp;mb_md5='.$mb_md5;
        }

        ob_start();
        include_once ('./register_form_update_mail1.php');
        $content = ob_get_contents();
        ob_end_clean();
        
        $content = run_replace('register_form_update_mail_mb_content', $content, $mb_id);

        $this->mailer($config['cf_admin_email_name'], $config['cf_admin_email'], $mb_email, $subject, $content, 1);

        run_event('register_form_update_send_mb_mail', $config['cf_admin_email_name'], $config['cf_admin_email'], $mb_email, $subject, $content);

        // 메일인증을 사용하는 경우 가입메일에 인증 url이 있으므로 인증메일을 다시 발송되지 않도록 함
        if($config['cf_use_email_certify'])
          $old_email = $mb_email;
      }

      // 최고관리자님께 메일 발송
      if ($config['cf_email_mb_super_admin']) {
        $subject = run_replace('register_form_update_mail_admin_subject', '['.$config['cf_title'].'] '.$mb_nick .' 님께서 회원으로 가입하셨습니다.', $mb_id, $mb_nick);

        ob_start();
        include_once ('./register_form_update_mail2.php');
        $content = ob_get_contents();
        ob_end_clean();
        
        $content = run_replace('register_form_update_mail_admin_content', $content, $mb_id);

        $this->mailer($mb_nick, $mb_email, $config['cf_admin_email'], $subject, $content, 1);

        run_event('register_form_update_send_admin_mail', $mb_nick, $mb_email, $config['cf_admin_email'], $subject, $content);
      }

      // 메일인증 사용하지 않는 경우에만 로그인
      if (!$config['cf_use_email_certify'])
        $this->set_session('ss_mb_id', $mb_id);

      $this->set_session('ss_mb_reg', $mb_id);

    } else if ($w == 'u') {
      if (!trim($this->get_session('ss_mb_id')))
        $this->alert('로그인 되어 있지 않습니다.');
  
      if (trim($_POST['mb_id']) != $mb_id)
        $this->alert("로그인된 정보와 수정하려는 정보가 틀리므로 수정할 수 없습니다.\\n만약 올바르지 않은 방법을 사용하신다면 바로 중지하여 주십시오.");
  
      $sql_password = "";
      if ($mb_password)
        $sql_password = " , mb_password = '".$this->get_encrypt_string($mb_password)."' ";
  
      $sql_nick_date = "";
      if ($mb_nick_default != $mb_nick)
        $sql_nick_date =  " , mb_nick_date = '".G5_TIME_YMD."' ";
  
      $sql_open_date = "";
      if ($mb_open_default != $mb_open)
        $sql_open_date =  " , mb_open_date = '".G5_TIME_YMD."' ";
  
      // 이전 메일주소와 수정한 메일주소가 틀리다면 인증을 다시 해야하므로 값을 삭제
      $sql_email_certify = '';
      if ($old_email != $mb_email && $config['cf_use_email_certify'])
        $sql_email_certify = " , mb_email_certify = '' ";
  
      $sql = "update {$g5['member_table']}
              set mb_nick = ?,
                  mb_mailling = ?,
                  mb_sms = ?,
                  mb_open = ?,
                  mb_email = ?,
                  mb_homepage = ?,
                  mb_tel = ?,
                  mb_zip1 = ?,
                  mb_zip2 = ?,
                  mb_addr1 = ?,
                  mb_addr2 = ?,
                  mb_addr3 = ?,
                  mb_addr_jibeon = ?,
                  mb_signature = ?,
                  mb_profile = ?,
                  mb_1 = ?,
                  mb_2 = ?,
                  mb_3 = ?,
                  mb_4 = ?,
                  mb_5 = ?,
                  mb_6 = ?,
                  mb_7 = ?,
                  mb_8 = ?,
                  mb_9 = ?,
                  mb_10 = ?
                  {$sql_password}
                  {$sql_nick_date}
                  {$sql_open_date}
                  {$sql_email_certify}
                  {$sql_certify}
            where mb_id = ?";

        $this->sql_query($sql, [$mb_nick, $mb_mailling, $mb_sms, $mb_open, $mb_email, $mb_homepage, $mb_tel, $mb_zip1, $mb_zip2, $mb_addr1, $mb_addr2, $mb_addr3, $mb_addr_jibeon, $mb_signature, $mb_profile, $mb_1, $mb_2, $mb_2, $mb_3, $mb_4, $mb_5, $mb_6, $mb_7, $mb_8, $mb_9, $mb_10, $mb_id]);
    }

    // 회원 아이콘
    $mb_dir = G5_DATA_PATH.'/member/'.substr($mb_id,0,2);

    // 아이콘 삭제
    if (isset($_POST['del_mb_icon'])) {
      @unlink($mb_dir.'/'.$this->get_mb_icon_name($mb_id).'.gif');
    }


    $msg = "";

    // 아이콘 업로드
    $mb_icon = '';
    $image_regex = "/(\.(gif|jpe?g|png))$/i";
    $mb_icon_img = $this->get_mb_icon_name($mb_id).'.gif';

    if (isset($_FILES['mb_icon']) && is_uploaded_file($_FILES['mb_icon']['tmp_name'])) {
      if (preg_match($image_regex, $_FILES['mb_icon']['name'])) {
        // 아이콘 용량이 설정값보다 이하만 업로드 가능
        if ($_FILES['mb_icon']['size'] <= $config['cf_member_icon_size']) {
          @mkdir($mb_dir, G5_DIR_PERMISSION);
          @chmod($mb_dir, G5_DIR_PERMISSION);
          $dest_path = $mb_dir.'/'.$mb_icon_img;
          move_uploaded_file($_FILES['mb_icon']['tmp_name'], $dest_path);
          chmod($dest_path, G5_FILE_PERMISSION);
          if (file_exists($dest_path)) {
            //=================================================================\
            // 090714
            // gif 파일에 악성코드를 심어 업로드 하는 경우를 방지
            // 에러메세지는 출력하지 않는다.
            //-----------------------------------------------------------------
            $size = @getimagesize($dest_path);
            if (!($size[2] === 1 || $size[2] === 2 || $size[2] === 3)) { // jpg, gif, png 파일이 아니면 올라간 이미지를 삭제한다.
              @unlink($dest_path);
            } else if ($size[0] > $config['cf_member_icon_width'] || $size[1] > $config['cf_member_icon_height']) {
              $thumb = null;
              if($size[2] === 2 || $size[2] === 3) {
                //jpg 또는 png 파일 적용
                $thumb = $this->thumbnail($mb_icon_img, $mb_dir, $mb_dir, $config['cf_member_icon_width'], $config['cf_member_icon_height'], true, true);
                if($thumb) {
                  @unlink($dest_path);
                  rename($mb_dir.'/'.$thumb, $dest_path);
                }
              }
              if( !$thumb ){
                  // 아이콘의 폭 또는 높이가 설정값 보다 크다면 이미 업로드 된 아이콘 삭제
                  @unlink($dest_path);
              }
            }
            //=================================================================\
          }
          } else {
            $msg .= '회원아이콘을 '.number_format($config['cf_member_icon_size']).'바이트 이하로 업로드 해주십시오.';
          }
        } else {
          $msg .= $_FILES['mb_icon']['name'].'은(는) 이미지 파일이 아닙니다.';
        }
    }


    // 회원 프로필 이미지
    if( $config['cf_member_img_size'] && $config['cf_member_img_width'] && $config['cf_member_img_height'] ){
      $mb_tmp_dir = G5_DATA_PATH.'/member_image/';
      $mb_dir = $mb_tmp_dir.substr($mb_id,0,2);
      if( !is_dir($mb_tmp_dir) ){
        @mkdir($mb_tmp_dir, G5_DIR_PERMISSION);
        @chmod($mb_tmp_dir, G5_DIR_PERMISSION);
      }

      // 아이콘 삭제
      if (isset($_POST['del_mb_img'])) {
        @unlink($mb_dir.'/'.$mb_icon_img);
      }

      // 회원 프로필 이미지 업로드
      $mb_img = '';
      if (isset($_FILES['mb_img']) && is_uploaded_file($_FILES['mb_img']['tmp_name'])) {

        $msg = $msg ? $msg."\\r\\n" : '';

        if (preg_match($image_regex, $_FILES['mb_img']['name'])) {
          // 아이콘 용량이 설정값보다 이하만 업로드 가능
          if ($_FILES['mb_img']['size'] <= $config['cf_member_img_size']) {
            @mkdir($mb_dir, G5_DIR_PERMISSION);
            @chmod($mb_dir, G5_DIR_PERMISSION);
            $dest_path = $mb_dir.'/'.$mb_icon_img;
            move_uploaded_file($_FILES['mb_img']['tmp_name'], $dest_path);
            chmod($dest_path, G5_FILE_PERMISSION);
            if (file_exists($dest_path)) {
              $size = @getimagesize($dest_path);
              if (!($size[2] === 1 || $size[2] === 2 || $size[2] === 3)) { // gif jpg png 파일이 아니면 올라간 이미지를 삭제한다.
                @unlink($dest_path);
              } else if ($size[0] > $config['cf_member_img_width'] || $size[1] > $config['cf_member_img_height']) {
                $thumb = null;
                if($size[2] === 2 || $size[2] === 3) {
                  //jpg 또는 png 파일 적용
                  $thumb = $this->thumbnail($mb_icon_img, $mb_dir, $mb_dir, $config['cf_member_img_width'], $config['cf_member_img_height'], true, true);
                  if($thumb) {
                    @unlink($dest_path);
                    rename($mb_dir.'/'.$thumb, $dest_path);
                  }
                }
                if( !$thumb ){
                  // 아이콘의 폭 또는 높이가 설정값 보다 크다면 이미 업로드 된 아이콘 삭제
                  @unlink($dest_path);
                }
              }
              //=================================================================\
            }
          } else {
            $msg .= '회원이미지을 '.number_format($config['cf_member_img_size']).'바이트 이하로 업로드 해주십시오.';
          }
        } else {
          $msg .= $_FILES['mb_img']['name'].'은(는) gif/jpg 파일이 아닙니다.';
        }
      }
    }


    // 인증메일 발송
    if ($config['cf_use_email_certify'] && $old_email != $mb_email) {
      $subject = '['.$config['cf_title'].'] 인증확인 메일입니다.';

      // 어떠한 회원정보도 포함되지 않은 일회용 난수를 생성하여 인증에 사용
      $mb_md5 = md5(pack('V*', rand(), rand(), rand(), rand()));

      $this->sql_query(" update {$g5['member_table']} set mb_email_certify2 = ? where mb_id = ? ", [$mb_md5, $mb_id]);

      $certify_href = G5_BBS_URL.'/email_certify.php?mb_id='.$mb_id.'&amp;mb_md5='.$mb_md5;

      ob_start();
      include_once ('./register_form_update_mail3.php');
      $content = ob_get_contents();
      ob_end_clean();
      
      $content = run_replace('register_form_update_mail_certify_content', $content, $mb_id);

      $this->mailer($config['cf_admin_email_name'], $config['cf_admin_email'], $mb_email, $subject, $content, 1);

      run_event('register_form_update_send_certify_mail', $config['cf_admin_email_name'], $config['cf_admin_email'], $mb_email, $subject, $content);
    }


    if(isset($_SESSION['ss_cert_type'])) unset($_SESSION['ss_cert_type']);
    if(isset($_SESSION['ss_cert_no'])) unset($_SESSION['ss_cert_no']);
    if(isset($_SESSION['ss_cert_hash'])) unset($_SESSION['ss_cert_hash']);
    if(isset($_SESSION['ss_cert_hash'])) unset($_SESSION['ss_cert_birth']);
    if(isset($_SESSION['ss_cert_hash'])) unset($_SESSION['ss_cert_adult']);

    if ($msg)
      $this->alert($msg);

    run_event('register_form_update_after', $mb_id, $w);
    if($w == '') {
      return array('success' => true);
    } else {
      if ($old_email != $mb_email && $config['cf_use_email_certify']) {
        $this->set_session('ss_mb_id', '');
        $this->alert('회원 정보가 수정 되었습니다.\n\nE-mail 주소가 변경되었으므로 다시 인증하셔야 합니다.', G5_URL);
      }
    }
  }
}