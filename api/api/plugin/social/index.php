<?php
define('API_PLUGIN_PATH', API_PATH.'/plugin');
define('API_PLUGIN_URL', API_URL.'/plugin');
// 소셜로그인 테이블 정보가 dbconfig에 없으면 소셜 테이블 정의
if( !isset($g5['social_profile_table']) ){
  $g5['social_profile_table'] = G5_TABLE_PREFIX.'member_social_profiles';
}

//플러그인 폴더 이름 및 스킨 폴더 이름
define('G5_SOCIAL_LOGIN_DIR', 'social');

// 소셜로그인 login_start 파라미터 이름입니다. 기본값은 hauth.start
define('G5_SOCIAL_LOGIN_START_PARAM', 'hauth.start');

// 소셜로그인 login_done 파라미터 이름입니다. 기본값은 hauth.done
define('G5_SOCIAL_LOGIN_DONE_PARAM', 'hauth.done');

define('G5_SOCIAL_LOGIN_PATH', API_PLUGIN_PATH.'/'.G5_SOCIAL_LOGIN_DIR);
define('G5_SOCIAL_LOGIN_URL', API_PLUGIN_URL.'/'.G5_SOCIAL_LOGIN_DIR);

// 소셜로그인 SOCIAL_LOGIN_BASE_URL 기본값은 G5_SOCIAL_LOGIN_URL.'/'
define('G5_SOCIAL_LOGIN_BASE_URL', G5_SOCIAL_LOGIN_URL.'/');


//소셜 로그인 팝업을 사용하면 true
//define('G5_SOCIAL_USE_POPUP', false );        //팝업을 사용하지 않을 경우

//소셜 db 테이블에 기록된 내용중에 mb_id가 없는 소셜 데이터를 몇일 이후에 삭제합니다.
//해당 기간동안 중복 회원가입을 막는 역할을 합니다.
//0 이면 체크를 하지 않습니다.
define('G5_SOCIAL_DELETE_DAY', 0);

// 메일 인증관련, false 이면 메일인증을 받지 않고 로그인됩니다. true 이고 기본환경설정에서 메일인증설정이 활성화 되어 있는 경우 메일인증을 받아야만 로그인 됩니다.
define('G5_SOCIAL_CERTIFY_MAIL', false);

// 소셜 DEBUG 관련 설정, 기본값은 false, true 로 설정시 data/tmp/social_anystring.log 파일이 생성됩니다.
define('G5_SOCIAL_IS_DEBUG', false);
/*
* HybridAuth
* http://hybridauth.sourceforge.net | http://github.com/hybridauth/hybridauth
* (c) 2009-2015, HybridAuth authors | http://hybridauth.sourceforge.net/licenses.html
*/

// ------------------------------------------------------------------------
//	HybridAuth End Point
// ------------------------------------------------------------------------
trait social {
	public function social() {
	
	}
  public function social_config() { 
    $result = array();
    $result['cf_social_login_use'] = $this->config['cf_social_login_use'];
    $result['cf_social_servicelist'] = explode(',', $this->config['cf_social_servicelist']);
    return $this->data_encode($result);
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
  public function social_token() {
    $config = $this->config;
		$api_key = $config['cf_kakao_rest_key'];
		$secret = $config['cf_kakao_client_secret'];
		$js_key = $config['cf_kakao_js_apikey'];
    $_GET = $this->REQUEST_URI();

    $returnCode = $_GET["code"]; 
    $callbacURI = urlencode("http://localhost:8080/social/?hauth=kakao"); 
    $returnUrl = "https://kauth.kakao.com/oauth/token?grant_type=authorization_code&client_id=".$api_key."&redirect_uri=".$callbacURI."&code=".$returnCode;
    

    $result = $this->curl_get_contents($returnUrl); //Access Token만 따로 뺌
    $accessToken = $result->access_token;
    
    $getProfileUrl = "https://kapi.kakao.com/v2/user/me";
    
       
    $headers = array();
    $header = "Bearer ".$accessToken; // Bearer 다음에 공백 추가
    $headers[] = "Authorization: ".$header;
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    curl_close ($ch);
    $profile = $this->curl_get_contents($getProfileUrl, $headers);
    print_r($profileResponse);
    
    $userId = $profileResponse->id;
    $userName = $profileResponse->properties->nickname;
    $userEmail = $profileResponse->kakao_account->email;
  }
	public function social_popup() {
		$config = $this->config;
		$api_key = $config['cf_kakao_rest_key'];
		$secret = $config['cf_kakao_client_secret'];
		$js_key = $config['cf_kakao_js_apikey'];
		$callbacURI = "http://localhost:8080/social/?hauth=kakao";
		// http://localhost/plugin/social/?hauth.done=kakao
		$kakaoLoginUrl = "https://kauth.kakao.com/oauth/authorize?client_id=".$api_key."&redirect_uri=".$callbacURI."&response_type=code";
		$result = array();
		$result['url'] = $kakaoLoginUrl;
		return $this->data_encode($result);
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

}