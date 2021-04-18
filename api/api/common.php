<?php 
//echo API_PATH;
require API_PATH.'/../jwt/autoload.php';

use Firebase\JWT\JWT;
trait common {
  public $dsn = "mysql:host=".G5_MYSQL_HOST.";port=3306;dbname=".G5_MYSQL_DB.";charset=utf8";
  public $db;
  public $key = 'haskdlfjoieqimfqeif';
  public $config = array();
  public $member = array('mb_id'=>'', 'mb_level'=> 1, 'mb_name'=> '', 'mb_point'=> 0, 'mb_certify'=>'', 'mb_email'=>'', 'mb_open'=>'', 'mb_homepage'=>'', 'mb_tel'=>'', 'mb_hp'=>'', 'mb_zip1'=>'', 'mb_zip2'=>'', 'mb_addr1'=>'', 'mb_addr2'=>'', 'mb_addr3'=>'', 'mb_addr_jibeon'=>'', 'mb_signature'=>'', 'mb_profile'=>'');
  public $board  = array('bo_table'=>'', 'bo_skin'=>'', 'bo_mobile_skin'=>'', 'bo_upload_count' => 0, 'bo_use_dhtml_editor'=>'', 'bo_subject'=>'', 'bo_image_width'=>0);
  public $group  = array('gr_device'=>'', 'gr_subject'=>'');
  public $g5     = array('title'=>'');
  public $is_member = false;
  public $is_guest = false;
  public $is_admin = '';
  public $qaconfig = array();
  public $g5_debug = array('php'=>array(),'sql'=>array());
  public $write = array();
  public $qstr = array('sca'=>'', 'sfl'=>'','stx'=>'','sst'=>'','sst'=>'','sod'=>'','spt'=>'','page'=>'');
  public $g5_object;
  public function __construct() {
    global $g5, $g5_object;
    $this->g5_object = $g5_object;
    try {
      $this->db = new PDO($this->dsn, G5_MYSQL_USER, G5_MYSQL_PASSWORD);
      $this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
      $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
      $this->alert('DB 연결에 실패하였습니다');
      $e->getMessage();
    }
    /*******************************************************************************
    ** 공통 변수, 상수, 코드
    *******************************************************************************/
    error_reporting( E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING );
    // 보안설정이나 프레임이 달라도 쿠키가 통하도록 설정
    header('P3P: CP="ALL CURa ADMa DEVa TAIa OUR BUS IND PHY ONL UNI PUR FIN COM NAV INT DEM CNT STA POL HEA PRE LOC OTC"');
    if (!defined('G5_SET_TIME_LIMIT')) define('G5_SET_TIME_LIMIT', 0);
    @set_time_limit(G5_SET_TIME_LIMIT);
  
    if( version_compare( PHP_VERSION, '5.2.17' , '<' ) ){
      die(sprintf('PHP 5.2.17 or higher required. Your PHP version is %s', PHP_VERSION));
    }
    
    //==============================================================================


    //==============================================================================
    // SESSION 설정
    //------------------------------------------------------------------------------
    @ini_set("session.use_trans_sid", 0);    // PHPSESSID를 자동으로 넘기지 않음
    @ini_set("url_rewriter.tags",""); // 링크에 PHPSESSID가 따라다니는것을 무력화함 (해뜰녘님께서 알려주셨습니다.)

    session_save_path(G5_SESSION_PATH);

    if (isset($SESSION_CACHE_LIMITER))
      @session_cache_limiter($SESSION_CACHE_LIMITER);
    else
      @session_cache_limiter("no-cache, must-revalidate");

    ini_set("session.cache_expire", 180); // 세션 캐쉬 보관시간 (분)
    ini_set("session.gc_maxlifetime", 10800); // session data의 garbage collection 존재 기간을 지정 (초)
    ini_set("session.gc_probability", 1); // session.gc_probability는 session.gc_divisor와 연계하여 gc(쓰레기 수거) 루틴의 시작 확률을 관리합니다. 기본값은 1입니다. 자세한 내용은 session.gc_divisor를 참고하십시오.
    ini_set("session.gc_divisor", 100); // session.gc_divisor는 session.gc_probability와 결합하여 각 세션 초기화 시에 gc(쓰레기 수거) 프로세스를 시작할 확률을 정의합니다. 확률은 gc_probability/gc_divisor를 사용하여 계산합니다. 즉, 1/100은 각 요청시에 GC 프로세스를 시작할 확률이 1%입니다. session.gc_divisor의 기본값은 100입니다.

    session_set_cookie_params(0, '/');
    ini_set("session.cookie_domain", G5_COOKIE_DOMAIN);
    //ini_set("session.cookie_domain", API_URL);

    //==============================================================================
    // 공용 변수
    //------------------------------------------------------------------------------
    // 기본환경설정
    // 기본적으로 사용하는 필드만 얻은 후 상황에 따라 필드를 추가로 얻음

    $this->config = $this->get_config(); //그누보드 설정
    $this->config['cf_captcha'] = $this->config['cf_captcha'] ? $this->config['cf_captcha'] : 'kcaptcha';
    if(isset($_COOKIE[$this->cookiename])) {
      $decoded = JWT::decode($_COOKIE[$this->cookiename], $this->key, array('HS256')); //로그인 여부
      $mb_id = $decoded->aud;
      $this->member = $this->get_member($mb_id); //회원정보 설정
      $this->is_admin = $this->is_admin($mb_id);
      $this->is_member = true;
      $this->is_guest = false;
    }else {
      $this->is_guest = true;
      $this->is_admin = false;
      $this->is_member = false;
      $this->member['mb_id'] = '';
      $this->member['mb_level'] = 1; // 비회원의 경우 회원레벨을 가장 낮게 설정
    }
    $this->db_optimize();
    $this->visit_insert();
    // 본인인증 또는 쇼핑몰 사용시에만 secure; SameSite=None 로 설정합니다.
    if( $this->config['cf_cert_use'] || (defined('G5_YOUNGCART_VER') && G5_YOUNGCART_VER) ) {
      // Chrome 80 버전부터 아래 이슈 대응
      // https://developers-kr.googleblog.com/2020/01/developers-get-ready-for-new.html?fbclid=IwAR0wnJFGd6Fg9_WIbQPK3_FxSSpFLqDCr9bjicXdzy--CCLJhJgC9pJe5ss
      if(!function_exists('session_start_samesite')) {
        function session_start_samesite($options = array()) {
          global $g5;
          $res = @session_start($options);

          // IE 브라우저 또는 엣지브라우저 또는 IOS 모바일과 http환경에서는 secure; SameSite=None을 설정하지 않습니다.
          if( preg_match('/Edge/i', $_SERVER['HTTP_USER_AGENT']) || preg_match('/(iPhone|iPod|iPad).*AppleWebKit.*Safari/i', $_SERVER['HTTP_USER_AGENT']) || preg_match('~MSIE|Internet Explorer~i', $_SERVER['HTTP_USER_AGENT']) || preg_match('~Trident/7.0(; Touch)?; rv:11.0~',$_SERVER['HTTP_USER_AGENT']) || ! (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on') ){
            return $res;
          }

          $headers = headers_list();
          krsort($headers);
          foreach ($headers as $header) {
            if (!preg_match('~^Set-Cookie: PHPSESSID=~', $header)) continue;
            $header = preg_replace('~; secure(; HttpOnly)?$~', '', $header) . '; secure; SameSite=None';
            header($header, false);
            $g5['session_cookie_samesite'] = 'none';
            break;
          }
          return $res;
        }
      }

      session_start_samesite();
    } else {
      @session_start();
    }

    define('G5_HTTP_BBS_URL',  $this->https_url(G5_BBS_DIR, false));
    define('G5_HTTPS_BBS_URL', $this->https_url(G5_BBS_DIR, true));
    
    $query = $this->REQUEST_URI(); //common.php 에서 받는 리퀘스트 대신 쿼리로 받음
    if (isset($query['sca']))  {
      $sca = $this->clean_xss_tags(trim($query['sca']));
      if ($sca) {
        $sca = preg_replace("/[\<\>\'\"\\\'\\\"\%\=\(\)\/\^\*]/", "", $sca);
        $this->qstr['sca'] = urlencode($sca);
      }
    } else {
      $sca = '';
    }
    if (isset($query['sfl']))  {
      $sfl = trim($query['sfl']);
      $sfl = preg_replace("/[\<\>\'\"\\\'\\\"\%\=\(\)\/\^\*\s]/", "", $sfl);
      if ($sfl)
        $this->qstr['sfl'] = urlencode($sfl); // search field (검색 필드)
    } else {
      $sfl = '';
    }

    if (isset($query['stx']))  { // search text (검색어)
      $stx = $this->get_search_string(trim($query['stx']));
      if ($stx || $stx === '0')
        $this->qstr['stx'] = urlencode($this->cut_str($stx, 20, ''));
    } else {
      $sx = '';
    }

    if (isset($query['sst']))  {
      $sst = trim($query['sst']);
      $sst = preg_replace("/[\<\>\'\"\\\'\\\"\%\=\(\)\/\^\*\s]/", "", $sst);
      if ($sst)
        $this->qstr['sst'] = urlencode($sst); // search sort (검색 정렬 필드)
    } else {
      $sst = '';
    }

    if (isset($query['sod']))  { // search order (검색 오름, 내림차순)
      $sod = preg_match("/^(asc|desc)$/i", $sod) ? $sod : '';
      if ($sod)
        $this->qstr['sod'] = urlencode($sod);
    } else {
      $sod = '';
    }

    if (isset($query['sop']))  { // search operator (검색 or, and 오퍼레이터)
      $sop = preg_match("/^(or|and)$/i", $sop) ? $sop : '';
      if ($sop)
        $this->qstr['sop'] = urlencode($sop);
    } else {
      $sop = '';
    }

    if (isset($query['spt']))  { // search part (검색 파트[구간])
      $spt = (int)$spt;
      if ($spt)
        $this->qstr['spt'] = urlencode($spt);
    } else {
      $spt = '';
    }

    if (isset($query['page'])) { // 리스트 페이지
      $page = (int)$query['page'];
      if ($page)
        $this->qstr['page'] = urlencode($page);
    } else {
      $page = '';
    }

    if (isset($query['onetable'])) { // 리스트 페이지
      $onetable = $query['onetable'];
      if ($onetable)
        $this->qstr['onetable'] = urlencode($onetable);
    } else {
      $onetable = '';
    }
    
    if (isset($query['w'])) {
      $w = substr($w, 0, 2);
    } else {
      $w = '';
    }

    if (isset($query['wr_id'])) {
      $wr_id = (int)$query['wr_id'];
    } else {
      $wr_id = 0;
    }
    

    if (isset($query['bo_table']) && ! is_array($query['bo_table'])) {
      $bo_table = preg_replace('/[^a-z0-9_]/i', '', trim($query['bo_table']));
      $bo_table = substr($bo_table, 0, 20);
    } else {
      $bo_table = '';
    }

    // URL ENCODING
    if (isset($query['url'])) {
      $url = strip_tags(trim($query['url']));
      $urlencode = urlencode($url);
    } else {
      $url = '';
      $urlencode = urlencode($_SERVER['REQUEST_URI']);
      if (G5_DOMAIN) {
        $p = @parse_url(G5_DOMAIN);
        $urlencode = G5_DOMAIN.urldecode(preg_replace("/^".urlencode($p['path'])."/", "", $urlencode));
      }
    }

    if (isset($query['gr_id'])) {
      if (!is_array($query['gr_id'])) {
        $gr_id = preg_replace('/[^a-z0-9_]/i', '', trim($query['gr_id']));
      }
    } else {
      $gr_id = '';
    }
    
    $write_table = '';
    if ($bo_table) {
      $board = $this->get_board_db($bo_table, true);
      if (isset($board['bo_table']) && $board['bo_table']) {
        $this->set_cookie("ck_bo_table", $board['bo_table'], 86400 * 1);
        $gr_id = $board['gr_id'];
        $write_table = $g5['write_prefix'] . $bo_table; // 게시판 테이블 전체이름

        if (isset($wr_id) && $wr_id) {
          $write = $this->get_write($write_table, $wr_id);
        } else if (isset($wr_seo_title) && $wr_seo_title) {
          $write = $this->get_content_by_field($write_table, 'bbs', 'wr_seo_title', $this->generate_seo_title($wr_seo_title));
          if( isset($write['wr_id']) ){
            $wr_id = $write['wr_id'];
          }
        }
      }
      // 게시판에서 
      if (isset($board['bo_select_editor']) && $board['bo_select_editor']) {
        $this->$config['cf_editor'] = $board['bo_select_editor'];
      }
    }

    if ($gr_id && !is_array($gr_id)) {
      $this->$group = $this->get_group($gr_id, true);
    }

    if ($this->is_admin != 'super') {
      // 접근가능 IP
      $cf_possible_ip = trim($this->config['cf_possible_ip']);
      if ($cf_possible_ip) {
        $is_possible_ip = false;
        $pattern = explode("\n", $cf_possible_ip);
        for ($i=0; $i<count($pattern); $i++) {
          $pattern[$i] = trim($pattern[$i]);
          if (empty($pattern[$i]))
            continue;
          $pattern[$i] = str_replace(".", "\.", $pattern[$i]);
          $pattern[$i] = str_replace("+", "[0-9\.]+", $pattern[$i]);
          $pat = "/^{$pattern[$i]}$/";
          $is_possible_ip = preg_match($pat, $_SERVER['REMOTE_ADDR']);
          if ($is_possible_ip)
            break;
        }
        if (!$is_possible_ip) {
          echo $this->msg("접근이 가능하지 않습니다");
          exit;
        }
      }

      // 접근차단 IP
      $is_intercept_ip = false;
      $pattern = explode("\n", trim($this->config['cf_intercept_ip']));
      for ($i=0; $i<count($pattern); $i++) {
        $pattern[$i] = trim($pattern[$i]);
        if (empty($pattern[$i]))
          continue;
        $pattern[$i] = str_replace(".", "\.", $pattern[$i]);
        $pattern[$i] = str_replace("+", "[0-9\.]+", $pattern[$i]);
        $pat = "/^{$pattern[$i]}$/";
        $is_intercept_ip = preg_match($pat, $_SERVER['REMOTE_ADDR']);
        if ($is_intercept_ip){
          echo $this->msg('접근이 불가합니다');
          exit;
        }
      }
  
    }
  }  
  public function REQUEST_URI() {
    parse_str(parse_url($_SERVER["REQUEST_URI"],PHP_URL_QUERY), $query); // parse query string
    return $query;
  }
  // multi-dimensional array에 사용자지정 함수적용
  public function array_map_deep($fn, $array) {
    if(is_array($array)) {
      foreach($array as $key => $value) {
        if(is_array($value)) {
          $array[$key] = array_map_deep($fn, $value);
        } else {
          $array[$key] = call_user_func($fn, $value);
        }
      }
    } else {
      $array = call_user_func($fn, $array);
    }

    return $array;
  }
  // SQL Injection 대응 문자열 필터링
  public function sql_escape_string($str) {
    if(defined('G5_ESCAPE_PATTERN') && defined('G5_ESCAPE_REPLACE')) {
      $pattern = G5_ESCAPE_PATTERN;
      $replace = G5_ESCAPE_REPLACE;

      if($pattern)
        $str = preg_replace($pattern, $replace, $str);
    }

    $str = call_user_func('addslashes', $str);

    return $str;
  }

}
