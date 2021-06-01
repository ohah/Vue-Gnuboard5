<?php
use Firebase\JWT\JWT;
trait social {
  public $user_profile = array(
    'identifier' => '',
    'webSiteURL' => '',
    'profileURL' => '',
    'photoURL' => '',
    'displayName' => '',
    'description' => '',
    'firstName' => '',
    'lastName' => '',
    'gender' => '',
    'language' => '',
    'age' => '',
    'birthDay' => '',
    'birthMonth' => '',
    'birthYear' => '',
    'email' => '',
    'emailVerified' => '',
    'phone' => '',
    'address' => '',
    'country' => '',
    'region' => '',
    'city' => '',
    'zip' => '',
    'job_title' => '',
    'organization_name' => '',
    'sid' => '',
  );
  public function get_social_convert_id($identifier, $service) {
    return strtolower($service).'_'.hash('adler32', md5($identifier));
  }
  public function __construct() {
  }
	public function social() {
    echo "무-야-호";
    exit;
    $_GET = $this->REQUEST_URI();
    print_r($_GET);
    $is_member = $this->is_member;
    $provider_name = $_GET['kakao'];
    if( ! (isset($_SESSION['sl_userprofile']) && is_array($_SESSION['sl_userprofile'])) ){ 
      $_SESSION['sl_userprofile'] = array(); 
    }

    if( ! $is_member ){ 
      $_SESSION['sl_userprofile'][$provider_name] = json_encode( $user_profile );
    }
    $this->set_session('ss_social_provider', $provider_name);
	}
  public function social_config() { 
    $result = array();
    $result['cf_social_login_use'] = $this->config['cf_social_login_use'];
    $result['cf_social_servicelist'] = explode(',', $this->config['cf_social_servicelist']);
    return $this->data_encode($result);
  }
  public function curl_post_contents($returnUrl,$headers=[], $params) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $returnUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if(count($headers) > 0)
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $loginResponse = curl_exec ($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close ($ch);
    
    // var_dump($loginResponse); // Kakao API 서버로 부터 받아온 값
    return json_decode($loginResponse);
  }
  public function curl_get_contents($returnUrl,$headers=[]) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $returnUrl);
    curl_setopt($ch, CURLOPT_POST, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if(count($headers) > 0)
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $loginResponse = curl_exec ($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close ($ch);
    
    // var_dump($loginResponse); // Kakao API 서버로 부터 받아온 값
    return json_decode($loginResponse);
  }
  public function Kakao() {
    $config = $this->config;
		$api_key = $config['cf_kakao_rest_key'];
		$secret = $config['cf_kakao_client_secret'];
		$js_key = $config['cf_kakao_js_apikey'];
    $_GET = $this->REQUEST_URI();
    
    $returnCode = $_GET["code"]; 
    $callbacURI = $this->callbackURI('kakao');
    $returnUrl = "https://kauth.kakao.com/oauth/token?grant_type=authorization_code&client_id=".$api_key."&redirect_uri=".$callbacURI."&code=".$returnCode;
    
    
    $result = $this->curl_get_contents($returnUrl); //Access Token만 따로 뺌
    $accessToken = $result->access_token;
    
    $getProfileUrl = "https://kapi.kakao.com/v2/user/me";
    
    $headers = array();
    $header = "Bearer ".$accessToken; // Bearer 다음에 공백 추가
    $headers[] = "Authorization: ".$header;
    $params = array('property_keys'=>array('kakao_account.email'));		// v2 parameter
    $data = $this->curl_post_contents($getProfileUrl, $headers, $params);
    if ( ! isset( $data->id ) ) {
      $this->alert("User profile request failed! returned an invalid response.");
    }
    $user_profile = (object)$this->user_profile;
    $user_profile->identifier  = @ $data->id;
    $user_profile->displayName = @ $data->properties->nickname;
    $user_profile->photoURL    = @ $data->properties->thumbnail_image;
    //$email = @ $data->properties->kaccount_email;	// v1 version
    
    $email = @ $data->kakao_account->email;   // v2 version

    if( $email ){
      $user_profile->email = $email;
    }

    $user_profile->sid = $this->get_social_convert_id( $this->user->profile->identifier, $this->providerId );
    // $user_profile = json_encode($user_profile, true);
    // print_r($user_profile);
    $this->user_profile = $user_profile;
    $_SESSION['user_profile'] = $user_profile;
    $mb_password = sha1( str_shuffle( "0123456789abcdefghijklmnoABCDEFGHIJ" ) );
    $this->social_check_login_before('kakao');
    // return $this->data_encode($user_profile);
  }
  public function social_token($provider_name) {
    if($provider_name === 'kakao') {
      return $this->kakao();
    }
    if($provider_name === 'google') {
      return $this->Google();
    }
  }
  public function callbackURI($platform = 'kakao', $url = G5_URL) {
    $url = G5_URL;
    $url = "http://localhost:8080";
    if($platform === 'kakao') {
      return urlencode("{$url}/social/?hauth={$platform}");
    }else {
      return "{$url}/social/?hauth={$platform}";
    }    
  }
  public function Google(){
    require_once 'google/vendor/autoload.php';
    $config = $this->config;
    $result = array();
    // init configuration
    $clientID = $config['cf_google_clientid'];
    $clientSecret = $config['cf_google_secret'];
    $redirectUri = $this->callbackURI('google');
      
    // create Client Request to access Google API
    $client = new Google_Client();
    $client->setClientId($clientID);
    $client->setClientSecret($clientSecret);
    $client->setRedirectUri($redirectUri);
    $client->addScope("email");
    $client->addScope("profile");
    // authenticate code from Google OAuth Flow
    $_GET = $this->REQUEST_URI();
    if (isset($_GET['code'])) {
      $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
      $client->setAccessToken($token['access_token']);
      // get profile info
      $google_oauth = new Google_Service_Oauth2($client);
      $google_account_info = $google_oauth->userinfo->get();
      $result['email'] =  $google_account_info->email;
      $result['name']=  $google_account_info->name;
      $result['id']=  $google_account_info->id;
      $result['picture']=  $google_account_info->picture;
      $user_profile = (object)$this->user_profile;
      $user_profile->identifier  = $google_account_info->id;
      $user_profile->displayName = $google_account_info->name;
      $user_profile->photoURL    = $google_account_info->picture;
      $user_profile->email = $google_account_info->email;
      $user_profile->sid = $this->get_social_convert_id( $this->user->profile->identifier, $this->providerId );
      // $user_profile = json_encode($user_profile, true);
      // print_r($user_profile);
      $this->user_profile = $user_profile;
      $_SESSION['user_profile'] = $user_profile;
      $mb_password = sha1( str_shuffle( "0123456789abcdefghijklmnoABCDEFGHIJ" ) );
      $this->social_check_login_before('google');
      // now you can use this profile info to create account in your website and make user logged in.
    } else {
      $result['url'] = $client->createAuthUrl();
    }
    return $this->data_encode($result);
  }
	public function social_popup($provider) {
		$config = $this->config;
    if($provider === 'kakao') {
      $api_key = $config['cf_kakao_rest_key'];
      $secret = $config['cf_kakao_client_secret'];
      $js_key = $config['cf_kakao_js_apikey'];
      $callbacURI = $this->callbackURI('kakao');
      // http://localhost/plugin/social/?hauth.done=kakao
      $kakaoLoginUrl = "https://kauth.kakao.com/oauth/authorize?client_id=".$api_key."&redirect_uri=".$callbacURI."&response_type=code";
      $result = array();
      $result['url'] = $kakaoLoginUrl;
      return $this->data_encode($result);
    }else if($provider === 'google') {
      return $this->Google();
    }
	}
	public function social_user_profile_replace( $mb_id, $provider, $profile ){
    global $g5;

    if( !$mb_id )
			return;

    // $profile 에 성별, 나이, 생일 등의 정보가 포함되어 있습니다.

    //받아온 정보를 암호화 하여
    $object_sha = sha1( serialize( $profile ) );
    
    $provider = strtolower($provider);

    $sql = sprintf("SELECT mp_no, mb_id from {$g5['social_profile_table']} where provider= '%s' and identifier = '%s' ", $provider, $profile->identifier);
    $result = $this->sql_query($sql);
    for($i=0;$i<count($result);$i++){   //혹시 맞지 않는 데이터가 있으면 삭제합니다.
			$row = $result[$i];
			if( $row['mb_id'] != $mb_id ){
				$this->sql_query(sprintf("DELETE FROM {$g5['social_profile_table']} where mp_no=%d", $row['mp_no']));
			}
    }
    
    $sql = sprintf("SELECT mp_no, object_sha, mp_register_day from {$g5['social_profile_table']} where mb_id= '%s' and provider= '%s' and identifier = '%s' ", $mb_id, $provider, $profile->identifier);

    $row = $this->sql_fetch($sql);

    $table_data = array(
      "mp_no"    =>  ! empty($row) ? $row['mp_no'] : 'NULL',
      'mb_id' =>  "'". $mb_id. "'",
      'provider'  => "'".  $provider . "'",
      'object_sha'    => "'". $object_sha . "'",
      'mp_register_day' => ! empty($row) ? "'".$row['mp_register_day']."'" : "'". G5_TIME_YMDHIS . "'",
      'mp_latest_day' => "'". G5_TIME_YMDHIS . "'",
    );

    $fields = array( 
      'identifier',
      'profileurl',
      'photourl',
      'displayname',
      'description',
    );

    foreach( (array) $profile as $key => $value ){
      $key = strtolower($key);
			if( in_array( $key, $fields ) )	{
				$value = (string) $value;
				$table_data[ $key ] = "'". $value. "'";
			}
    }
    
    $fields  = '`' . implode( '`, `', array_keys( $table_data ) ) . '`';
    $values = implode( ", ", array_values( $table_data )  );

    $sql = "REPLACE INTO {$g5['social_profile_table']} ($fields) VALUES ($values) ";

    $this->sql_query($sql);

    return $this->db->lastInsertId();

  }

  public function social_relace_nick($nick=''){
    if( empty($nick) ) return '';
    return preg_replace("/[ #\&\+\-%@=\/\\\:;,\.'\"\^`~\_|\!\?\*$#<>()\[\]\{\}]/i", "", $nick);
  }
  public function social_get_data($by='provider', $provider, $user_profile){
    global $g5;
    // 소셜 가입이 되어 있는지 체크
    if( $by == 'provider' ){
      $sql = sprintf("select * from {$g5['social_profile_table']} where provider = '%s' and identifier = '%s' order by mb_id desc ", $provider, $user_profile->identifier);
      $row = $this->sql_fetch($sql);
      if( !empty($row['mb_id']) ){
        return $row;    //mb_id 가 있는 경우에만 데이터를 리턴합니다.
      }
      return false;
    } else if ( $by == 'member' ){  // 아이디 또는 이메일이나 별명으로 이미 가입되어 있는지 체크
      $email = ($user_profile->emailVerified) ? $user_profile->emailVerified : $user_profile->email;
      $sid = preg_match("/[^0-9a-z_]+/i", "", $user_profile->sid);
      $nick = $this->social_relace_nick($user_profile->displayName);
      if( !$nick ){
        $tmp = explode("@", $email);
        $nick = $tmp[0];
      }

      $sql = "select mb_nick, mb_email from {$g5['member_table']} where mb_nick = '".$nick."' ";

      if( !empty($email) ){
        $sql .= sprintf(" or mb_email = '%s' ", $email);
      }

      $result = $this->sql_query($sql);

      $exists = array();
      for($i=0;$i<count($result);$i++){
        $row = $result[$i];
        if($row['mb_nick'] && $row['mb_nick'] == $nick){
          $exists['mb_nick'] = $nick;
        }
        if($row['mb_email'] && $row['mb_email'] == $email){
            $exists['mb_email'] = $email;
        }
      }
      return $exists;
    }

    return null;
  }

  public function social_logout_with_adapter($adapter=null){
    if( is_object( $adapter ) ){
      $adapter->logout();
    }
    $this->social_login_session_clear(1);
  }
  public function social_login_session_clear($mycf=0){
    $_SESSION["HA::STORE"]        = array(); // used by hybridauth library. to clear as soon as the auth process ends.
    $_SESSION["HA::CONFIG"]       = array(); // used by hybridauth library. to clear as soon as the auth process ends.
    $this->set_session('sl_userprofile', '');
    $this->set_session('social_login_redirect', '');
    if(!$mycf){
      $this->set_session('ss_social_provider', '');
    }
  }
  public function Social_Login($mb_id) {
    global $g5;
    $mb = $this->get_member($mb_id);
    // 차단된 아이디인가?
    if ($mb['mb_intercept_date'] && $mb['mb_intercept_date'] <= date("Ymd", G5_SERVER_TIME)) {
      $date = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})/", "\\1년 \\2월 \\3일", $mb['mb_intercept_date']);
      $this->alert('회원님의 아이디는 접근이 금지되어 있습니다.\n처리일 : '.$date);
    }
    // 탈퇴한 아이디인가?
    if ($mb['mb_leave_date'] && $mb['mb_leave_date'] <= date("Ymd", G5_SERVER_TIME)) {
      $date = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})/", "\\1년 \\2월 \\3일", $mb['mb_leave_date']);
      $this->alert('탈퇴한 아이디이므로 접근하실 수 없습니다.\n탈퇴일 : '.$date);
    }
    // 메일인증 설정이 되어 있다면
    $config = $this->config;
    $this->is_use_email_certify($config);
    if ( $this->is_use_email_certify($config) && !preg_match("/[1-9]/", $mb['mb_email_certify'])) {
      $ckey = md5($mb['mb_ip'].$mb['mb_datetime']);
      $this->alert('이메일 인증을 받으셔야 로그인이 가능합니다');
    }    
    $payload = array(
      "iss" => "localhost",
      "iss" => "creator",
      "aud" => $mb_id,
      "iat" => time(),
      "nbf" => time(),
      "exp" => strtotime("+7 day", time()),
    );
    $jwt = JWT::encode($payload, $this->key);
    setcookie($this->cookiename, $jwt, strtotime("+7 day", time()), '/');
    $decoded = JWT::decode($jwt, $this->key, array('HS256'));
    return $this->data_encode($this->get_member($mb_id));
  }
  public function social_check_login_before($provider_name=''){
    global $g5;
    $is_member = $this->is_member;
    $member = $this->member;
    $config = $this->config;
    $user_profile = $this->user_profile;
    if( $provider_name ) {
      //소셜로 이미 가입 했다면 로그인 처리 합니다.
      if( $user_provider = $this->social_get_data('provider', $provider_name, $user_profile) ){
        if( $is_member ){
          $msg = "이미 로그인 하셨거나 잘못된 요청입니다.";
          if( $mylink ){
            $msg = "이미 연결된 아이디가 있거나, 잘못된 요청입니다.";
          }
          if( $use_popup == 1 || ! $use_popup ){   //팝업이면
            $this->alert( $msg );
          } else {
            $this->alert( $msg );
          }
          exit;
        }
        //데이터가 틀리면 데이터를 갱신 후 로그인 처리 합니다.
        $mb_id = $user_provider['mb_id'];
        //이미 소셜로 가입된 데이터가 있다면 password를 필요하지 않으니, 패스워드를 무작위 생성하여 넘깁니다.
        $sql = "SELECT count(*) as cnt FROM {$g5['social_profile_table']} WHERE identifier = '{$user_profile->identifier}' AND provider = '{$provider_name}'";
        $row = $this->sql_fetch($sql);
        if($row['cnt'] > 0) {   //회원이면
          $sql = "SELECT mb_id FROM {$g5['social_profile_table']} WHERE identifier = '{$user_profile->identifier}' AND provider = '{$provider_name}'";
          $res = $this->sql_fetch($sql);
          echo $this->Social_Login($res['mb_id']);
          exit;
        };
        $mb_password = sha1( str_shuffle( "0123456789abcdefghijklmnoABCDEFGHIJ" ) );
        // echo $this->Login($mb_id, $mb_password);
        exit;
      //소셜 데이터와 회원데이터가 일치 하는 경우 계정와 연결할지, 새로 계정을 만들지 선택합니다.
      } else {
        if( $is_member && !empty($user_profile) ){   //회원이면
          $this->social_user_profile_replace($member['mb_id'], $provider_name, $user_profile);

          if(!$this->get_session('ss_social_provider') ){
            $this->set_session('ss_social_provider', $provider_name);
          }
        } else { //회원이 아니면
          if(!$is_member ){
            $result = array();
            // $result['user_profile'] = $this->user_profile;
            $result['user_profile'] = array();
            $result['user_profile']['user_id'] = $user_profile->sid ? preg_replace("/[^0-9a-z_]+/i", "", $user_profile->sid) : $this->get_social_convert_id($user_profile->identifier, $provider_name);
            $result['user_profile']['user_email'] = $user_profile->emailVerified ? $user_profile->emailVerified : $user_profile->email;
            $result['user_profile']['user_name'] = $user_profile->username ? $this->remove_blank($user_profile->username) : '';
            $result['user_profile']['user_nick'] = $this->remove_blank($user_profile->displayName);
            $result['cf_stipulation'] = $this->config['cf_stipulation'];
            $result['cf_privacy'] = $this->config['cf_privacy'];
            echo $this->data_encode($result);
            exit;
          }
        }
      }
    }
  }
  public function remove_blank($str) {
    return preg_replace("/\s+/","",$str);
  }
  public function exist_mb_id_recursive($mb_id){
    static $count = 0;
    $mb_id_add = ($count > 0) ? $mb_id.(string)$count : $mb_id;
    if( ! $this->exist_mb_id($mb_id_add) ){
      return $mb_id_add;
    }
    $count++;
    return $this->exist_mb_id_recursive($mb_id);
  }
  public function exist_mb_nick_recursive($mb_nick){
    static $count = 0;
    $mb_nick_add = ($count > 0) ? $mb_nick.(string)$count : $mb_nick;
    if( ! $this->exist_mb_nick($mb_nick_add, '') ){
      return $mb_nick_add;
    }
    $count++;
    return $this->exist_mb_nick_recursive($mb_nick);
  }

  public function social_profile_img_resize($path, $file_url, $width, $height){
    
    // getimagesize 경우 php.ini 에서 allow_url_fopen 이 활성화 되어 있어야 원격이미지를 읽어올수 있습니다.
    list($w, $h, $ext) = @getimagesize($file_url);
    if( $w && $h && $ext ){
        $ratio = max($width/$w, $height/$h);
        $h = ceil($height / $ratio);
        $x = ($w - $width / $ratio) / 2;
        $w = ceil($width / $ratio);

        $tmp = imagecreatetruecolor($width, $height);
        
        if($ext == 1){
            $image = imagecreatefromgif($file_url);
        } else if($ext == 3) {
            $image = imagecreatefrompng($file_url);
        } else {
            $image = imagecreatefromjpeg($file_url);
        }
        imagecopyresampled($tmp, $image,
        0, 0,
        $x, 0,
        $width, $height,
        $w, $h);

        switch ($ext) {
        case '2':
          imagejpeg($tmp, $path, 100);
          break;
        case '3':
          imagepng($tmp, $path, 0);
          break;
        case '1':
          imagegif($tmp, $path);
          break;
        }
        
        chmod($path, G5_FILE_PERMISSION);

        /* cleanup memory */
        imagedestroy($image);
        imagedestroy($tmp);
    }
  }

  public function social_update () {
    global $g5;
    @extract($_POST);
    $config = $this->config;
    $user_profile = $_SESSION['user_profile'];
    // $this->social_token($provider);
    if( ! $mb_nick || ! $mb_name ){
      $tmp = explode('@', $mb_email);
      $mb_nick = $mb_nick ? $mb_nick : $tmp[0];
      $mb_name = $mb_name ? $mb_name : $tmp[0];
      $mb_nick = $this->exist_mb_nick_recursive($mb_nick, '');
    }
    if(!$mb_password ){
      $mb_password = md5(pack('V*', rand(), rand(), rand(), rand()));
    }
    
    if ($msg = $this->valid_mb_id($mb_id))         $this->alert($msg);
    if ($msg = $this->empty_mb_name($mb_name))       $this->alert($msg);
    if ($msg = $this->empty_mb_nick($mb_nick))     $this->alert($msg);
    if ($msg = $this->empty_mb_email($mb_email))   $this->alert($msg);
    if ($msg = $this->reserve_mb_id($mb_id))       $this->alert($msg);
    if ($msg = $this->reserve_mb_nick($mb_nick))   $this->alert($msg);
    // 이름에 한글명 체크를 하지 않는다.
    //if ($msg = valid_mb_name($mb_name))     $this->alert($msg, "", true, true);
    if ($msg = $this->valid_mb_nick($mb_nick))     $this->alert($msg);
    if ($msg = $this->valid_mb_email($mb_email))   $this->alert($msg);
    if ($msg = $this->prohibit_mb_email($mb_email))$this->alert($msg);

    if ($msg = $this->exist_mb_id($mb_id))     $this->alert($msg);
    if ($msg = $this->exist_mb_nick($mb_nick, $mb_id))     $this->alert($msg);
    if ($msg = $this->exist_mb_email($mb_email, $mb_id))   $this->alert($msg);

    if( $mb = $this->get_member($mb_id) ) {
      $this->alert("이미 등록된 회원이 존재합니다.");
    }

    $data = array(
      'mb_id' =>  $mb_id,
      'mb_password'   =>  $mb_password,
      'mb_nick'   =>  $mb_nick,
      'mb_email'  =>  $mb_email,
      'mb_name'   =>  $mb_name,
    );

    $mb_email_certify = G5_TIME_YMDHIS;

    //메일인증을 사용한다면
    if( defined('G5_SOCIAL_CERTIFY_MAIL') && G5_SOCIAL_CERTIFY_MAIL && $config['cf_use_email_certify'] ){
      $mb_email_certify = '';
    }

    //회원 메일 동의
    $mb_mailling = $_POST['mb_mailling'] ? 1 : 0;
    //회원 정보 공개
    $mb_open = $_POST['mb_open'] ? 1 : 0;

    $sql = "insert into {$g5['member_table']}
            set mb_id = '{$mb_id}',
                mb_password = '".$mb_password."',
                mb_name = '{$mb_name}',
                mb_nick = '{$mb_nick}',
                mb_nick_date = '".G5_TIME_YMD."',
                mb_email = '{$mb_email}',
                mb_email_certify = '".$mb_email_certify."',
                mb_today_login = '".G5_TIME_YMDHIS."',
                mb_datetime = '".G5_TIME_YMDHIS."',
                mb_ip = '{$_SERVER['REMOTE_ADDR']}',
                mb_level = '{$config['cf_register_level']}',
                mb_login_ip = '{$mb_mailling}',
                mb_sms = '0',
                mb_open = '{$mb_open}',
                mb_open_date = '".G5_TIME_YMD."' ";

    $result = $this->sql_result($sql);

    if($result) {
      // 회원가입 포인트 부여
      $this->insert_point($mb_id, $config['cf_register_point'], '회원가입 축하', '@member', $mb_id, '회원가입');

      // 최고관리자님께 메일 발송
      if ($config['cf_email_mb_super_admin']) {
        
      }

      $mb = $this->get_member($mb_id);
      
      $this->set_session('ss_mb_reg', $mb['mb_id']);

      $this->social_login_success_after($mb, '', 'register');

      if( !empty($user_profile->photoURL) && ($config['cf_register_level'] >= $config['cf_icon_level']) ){  //회원 프로필 사진이 있고, 회원 아이콘를 올릴수 있는 조건이면
        
        // 회원아이콘
        $mb_dir = G5_DATA_PATH.'/member/'.substr($mb_id,0,2);
        @mkdir($mb_dir, G5_DIR_PERMISSION);
        @chmod($mb_dir, G5_DIR_PERMISSION);
        $dest_path = "$mb_dir/$mb_id.gif";
        
        $this->social_profile_img_resize($dest_path, $user_profile->photoURL, $config['cf_member_icon_width'], $config['cf_member_icon_height'] );
        
        // 회원이미지
        if( is_dir(G5_DATA_PATH.'/member_image/') ) {
          $mb_dir = G5_DATA_PATH.'/member_image/'.substr($mb_id,0,2);
          @mkdir($mb_dir, G5_DIR_PERMISSION);
          @chmod($mb_dir, G5_DIR_PERMISSION);
          $dest_path = "$mb_dir/$mb_id.gif";
          
          $this->social_profile_img_resize($dest_path, $user_profile->photoURL, $config['cf_member_img_width'], $config['cf_member_img_height'] );
        }
      }

      if( $mb_email_certify ){    //메일인증 사용 안하면
        //바로 로그인 처리
        $this->set_session('ss_mb_id', $mb['mb_id']);

      } else {    // 메일인증을 사용한다면
        $subject = '['.$config['cf_title'].'] 인증확인 메일입니다.';
        // 어떠한 회원정보도 포함되지 않은 일회용 난수를 생성하여 인증에 사용
        $mb_md5 = md5(pack('V*', rand(), rand(), rand(), rand()));
        $this->sql_query(" update {$g5['member_table']} set mb_email_certify2 = '$mb_md5' where mb_id = '$mb_id' ");
        $certify_href = G5_BBS_URL.'/email_certify.php?mb_id='.$mb_id.'&amp;mb_md5='.$mb_md5;
        ob_start();
        include_once (G5_BBS_PATH.'/register_form_update_mail3.php');
        $content = ob_get_contents();
        ob_end_clean();
        $this->mailer($config['cf_admin_email_name'], $config['cf_admin_email'], $mb_email, $subject, $content, 1);
      }

    }
  }

  //소셜 로그인 후 계정 업데이트
  public function social_login_success_after($mb, $link='', $mode='', $tmp_create_info=array()) {
    global $g5;
    $config = $this->$config;
    $provider = $_POST['provider'];
    $user_profile = $_SESSION['user_profile'];
    if( isset($mb['mb_id']) && !empty($mb['mb_id']) && $provider) {
      $mb_id = $mb['mb_id'];
      //로그인에 성공 했으면  기존 데이터와 비교하여 틀린 값이 없으면 업데이트 합니다.
      $this->social_user_profile_replace($mb_id, $provider, $user_profile);
      //소셜로그인의 provider 이름( naver, kakao, facebook 기타 등등 ) 서비스 이름을 세션에 입력합니다.
      $this->set_session('ss_social_provider', $provider);
      //소셜로그인 최초 받아온 세션에 저장된 값을 삭제합니다.
      if( isset($_SESSION['sl_userprofile']) && isset($_SESSION['sl_userprofile'][$provider]) ){
        unset($_SESSION['sl_userprofile'][$provider]);
      }
      if($mode=='register'){   //회원가입 했다면
        return;
      }
    }
    return $link;
  }
}