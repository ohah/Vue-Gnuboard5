<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
set_time_limit(10);
header('Content-Type: application/json');  // <-- header declaration
require 'router/autoload.php';
require 'api/api.php';
$api = new Gnuboard_api();
date_default_timezone_set("Asia/Seoul");
$router = new \Bramus\Router\Router();
$router->get('/', function() use ($api) {
  echo "테스트 그누보드입니다.";
  //$api->sql_query();
});
$router->match('POST', '/Login', function() use ($api) {  
  $_POST = $api->getPostData();
  if(!$_POST['mb_password']) {
    $api->alert('패스워드를 입력해주세요');
    exit;
  }
  $mb_id = $_POST['mb_id'];
  $mb_password = $_POST['mb_password'];
  echo $api->Login($mb_id, $mb_password);
});
$router->match('POST|PUT', '/Logout', function() use ($api) {  
  echo $api->Logout();
});
$router->match('POST|PUT', '/LoginCheck', function() use ($api) {  
  echo $api->LoginCheck();
});
$router->match('GET', '/configs', function() use ($api) {
  echo json_encode($api->get_config(), JSON_UNESCAPED_UNICODE);
});
$router->match('GET', '/content', function() use ($router, $api) {
  echo $api->content();
});
$router->mount('/faqs', function() use ($router, $api) {
  $router->get('/', function() use ($api) {
    echo $api->get_faq($fa_id);
  });
  $router->get('/(\w+)', function($fa_id) use ($api) {
    echo $api->get_faq($fa_id);
  });
});
$router->match('GET', '/faqsgroup/{fm_id}', function() use ($api) {
  echo $api->get_faq_group($fa_id);
});
$router->match('GET', '/groups', function() use ($api){
  echo json_encode($api->get_group($fa_id), JSON_UNESCAPED_UNICODE);
});
$router->match('GET', '/visit', function() use ($api){
  echo $api->get_visit();
});
$router->match('GET', '/members', function() use ($api){
  echo $api->get_members();
});
$router->mount('/member', function() use ($router, $api) {
  $router->get('/{mb_id}/profile', function($mb_id) use ($api) {
    echo $api->profile($mb_id);
  });
  $router->get('/{mb_id}/scraps', function($mb_id) use ($api) {
    echo $api->get_scrap($mb_id);
  });
  $router->match('GET','/scrap', function() use ($api) {
    echo $api->scrap();
  });
  $router->match('GET','/memo', function() use ($api) {
    echo $api->memo();
  });
  $router->match('GET','/memo_form', function() use ($api) {
    echo $api->memo_form();
  });
  $router->match('POST|PUT','/memo_form_update', function() use ($api) {
    echo $api->memo_form_update();
  });
  $router->match('POST|DELETE','/memo_delete/{me_id}', function($me_id) use ($api) {
    echo $api->memo_delete($me_id);
  });
  $router->match('POST|DELETE','/scrap_delete/{ms_id}', function($ms_id) use ($api) {
    echo $api->scrap_delete($ms_id);
  });
  $router->match('POST|PUT','/scrap_update/{bo_table}/{wr_id}', function($bo_table, $wr_id) use ($api) {
    echo $api->scrap_popin_update($bo_table, $wr_id);
  });
  $router->get('/{mb_id}/points', function($mb_id) use ($api) {
    echo $api->get_point($mb_id);
  });
  $router->get('/{mb_id}', function($mb_id) use ($api) {
    echo json_encode($api->get_member($mb_id), JSON_UNESCAPED_UNICODE);
  });
});
$router->match('GET', '/boards', function() use ($api){
  echo $api->get_board();
});
/**
 * @param qstr;
 */
$router->match('GET', '/search', function() use ($api) { //검색
  echo $api->search();
});
/**
 * @param member profile point;
 */
$router->match('GET', '/point', function() use ($api) { //포인트
  echo $api->point();
});
/**
 * @param move "copy | move";
 */
$router->match('POST|PUT', '/move/{sw}', function($sw) use ($api) { //검색
  echo $api->move($sw);
});
$router->match('POST|PUT', '/move_update/{sw}/{bo_table}', function($sw, $bo_table) use ($api) { //검색
  echo $api->move_update($sw, $bo_table);
});
$router->mount('/board', function() use ($router, $api) {
  $router->match('GET', '/new/{mb_id}', function($mb_id) use ($api) {
    echo $api->bbs_new($mb_id);
  });
  $router->match('GET', '/new_articles', function() use ($api){
    echo $api->get_new_articles();
  });
  $router->match('GET', '/new_comments', function() use ($api){
    echo '지원예정';
  });
  $router->get('/{bo_table}/{wr_id}/good', function($bo_table, $wr_id) use ($api) {
    echo $api->get_board_good($bo_table, $wr_id);
  });
  /**
   * 작성자 제외, 회원만 가능
   * @param bo_table address;
   * @param wr_id address;
   * @param good,nogood address;
   */
  $router->match('POST|PUT', '/good/{bo_table}/{wr_id}/{good}', function($bo_table, $wr_id, $good) use ($api) {
    echo $api->good($bo_table, $wr_id, $good);
  });
  $router->get('/{bo_table}/{wr_id}/files', function($bo_table, $wr_id) use ($api) {
    $api->board_chk($bo_table, $wr_id);
    echo $api->get_board_file($bo_table, $wr_id);
  });
  $router->get('/{bo_table}/{wr_id}/comments', function($bo_table, $wr_id) use ($api) {
    //$api->board_chk($bo_table, $wr_id);
    echo $api->get_cmt_list($bo_table, $wr_id);
  });
  $router->get('/{bo_table}/{wr_id}/comment/{comment_id}/good', function($bo_table, $wr_id, $comment_id) use ($api) {
    $api->board_chk($bo_table, $wr_id);
    echo $api->get_board_good_cmt($bo_table, $wr_id, $comment_id);
  });
  $router->get('/{bo_table}/{wr_id}/comment/{comment_id}/files', function($bo_table, $wr_id, $comment_id) use ($api) {
    $api->board_chk($bo_table, $wr_id);
    echo $api->get_board_file_cmt($mb_id);
  });
  $router->get('/{bo_table}/{wr_id}/comment/{comment_id}', function($bo_table, $wr_id, $comment_id) use ($api) {
    $api->board_chk($bo_table, $wr_id);
    echo $api->get_view_cmt($mb_id);
  });
  /**
   * 글 삭제
   * 작성자, 관리자만 가능
   * @param bo_table address;
   * @param wr_id address;
   * @param comment_id address;
   */
  $router->match('DELETE|POST', '/{bo_table}/{wr_id}/comment/{comment_id}', function($bo_table, $wr_id, $comment_id) use ($api) {    
    echo $api->delete_comment($bo_table, $comment_id);
  });
  /**
   * 글보기 view.php
   * @param bo_table 테이블ID
   * @param wr_id 글ID
   */
  $router->get('/{bo_table}/{wr_id}', function($bo_table, $wr_id) use ($api) {
    $api->board_chk($bo_table, $wr_id);
    echo $api->get_views($bo_table, $wr_id);
  });
  /**
   * 관리자만 가능
   * @param bo_table address;
   * @param POST chk_wr_id;
   */
  $router->match('DELETE|POST', '/{bo_table}/all', function($bo_table) use ($api) { //삭제 
    echo $api->delete_all($bo_table);
  });
  /**
   * 작성자, 관리자만 가능
   * @param bo_table address;
   * @param wr_id address;
   * @param wr_password post;
   */
  $router->match('DELETE|POST', '/{bo_table}/{wr_id}', function($bo_table, $wr_id) use ($api) { //삭제 
    echo $api->delete($bo_table, $wr_id);
  });
  $router->get('/{bo_table}', function($bo_table) use ($api) {
    $api->board_chk($bo_table);
    echo $api->get_bbs_list($bo_table);
  });
});

/**cc
 * 코멘트 쓰기
 * @param write_comment_update(코멘트수정(cu))
 * @param write_comment_update(코멘트쓰기(c))
 */
$router->mount('/write_comment', function() use ($router, $api) {
  $router->match('GET|POST', '/{bo_table}/{wr_id}/{w}', function($bo_table, $wr_id, $w) use ($api) {
    echo $api->write_comment_update($bo_table, $wr_id, $w);
  });
});
/**
 * 글쓰기
 * @param write(글수정)
 * @param write_update(글수정)
 * @param write_update(글쓰기)
 * @param write(글쓰기)
 */
$router->mount('/write', function() use ($router, $api) {
  $router->get('/{bo_table}', function($bo_table) use ($api) {
    echo $api->write($bo_table);
  });
  $router->match('PUT|POST', '/{bo_table}', function($bo_table) use ($api) {
    $api->board_chk($bo_table);
    echo $api->write_update($bo_table);
  });
});
$router->match('PUT|POST', '/modify/{bo_table}/{wr_id}/{w}', function($bo_table, $wr_id, $w) use ($api) {
  echo $api->write($bo_table, $wr_id, $w);
});
$router->match('PUT|POST', '/update/{bo_table}/{wr_id}', function($bo_table, $wr_id) use ($api) {
  $api->board_chk($bo_table);
  echo $api->write_update($bo_table, $wr_id);
});
$router->match('GET', '/menus', function() use ($api){
  echo $api->data_encode($api->get_menu_db());
});
$router->match('GET', '/autosave', function() use ($api){
  echo $api->get_autosave();
});
/**
 * 프로필
 * @param mb_id 맴버 아이디
 */
$router->match('GET', '/profile/{mb_id}', function($mb_id) use ($api){
  echo $api->profile($mb_id);
});
/**
 * 비밀번호
 * @param w sc, c, u, r 등 요청에 맞는 그누보드 기본값
 * @param bo_table 테이블 이름
 * @param wr_id wr_id 값
 */
$router->match('POST', '/password/{w}/{bo_table}/{wr_id}', function($w, $bo_table, $wr_id) use ($api){
  //$_POST = $api->getPostData();
  //if($_POST['input']) echo $api->password($w, $bo_table, $wr_id);
  echo $api->password_check($w, $bo_table, $wr_id);
});
/**
 * 최신글
 * @param bo_table 테이블명
 * @param rows 개수
 * @param subject_len 제목길이
 */
$router->match('GET', '/latest(/[a-z0-9_-]+)?(/[0-9_-]+)?(/[0-9_-]+)?', function($bo_table, $rows, $subject_len) use ($api) {
  $rows = $rows ? $rows : 10;
  $subject_len = $subject_len ? $subject_len : 40;
  echo $api->latest($bo_table, $rows, $subject_len);
});
/**
 * 인기검색어
 * @param pop_cnt 검색어 개수(기본값 7)
 * @param date_cnt 날짜(기본값 3)
 */
$router->match('GET', '/popular(/[0-9_-]+)?(/[0-9_-]+)?', function($pop_cnt = 7, $date_cnt = 3) use ($api){
  $pop_cnt = $pop_cnt ? $pop_cnt : 7;
  $date_cnt = $date_cnt ? $date_cnt : 7;
  echo json_encode($api->popular($pop_cnt, $date_cnt), JSON_UNESCAPED_UNICODE);
});

/**
  * 관리자만 가능
  * @param qa_id address;
*/
$router->match('GET', '/qa/{qa_id}', function($qa_id) use ($api){
  echo json_encode($api->qaview($qa_id), JSON_UNESCAPED_UNICODE);
});

/**
 * 전부다 포스트로 처리
 * @param _POST post;
 */
$router->match('POST|PUT', '/register', function() use ($api) {
  echo $api->register();
});
$router->match('POST|PUT', '/register_form', function() use ($api) {
  echo $api->register_form();
});
$router->match('POST|PUT', '/register_form_update', function() use ($api) {
  echo json_encode($api->register_form_update(), JSON_UNESCAPED_UNICODE);
});
$router->match('GET', '/t', function() use ($api) {
  echo json_encode($api->chk_captcha(), JSON_UNESCAPED_UNICODE);
});
$router->mount('/captcha', function() use ($router, $api) {
  $router->match('GET|POST', '/K', function() use ($api) {
    $_POST = isset($_POST['refresh']) ? $_POST : $api->getPostData();
    if($_POST['refresh']) {
      echo $api->data_encode($api->captcha_html());
      exit;
    }
    require API_PATH.'/plugin/kcaptcha/kcaptcha_image.php';
  });
});
$router->mount('/formmail', function() use ($router, $api) {
  $router->match('GET', '/formmail_send', function() use ($api) {
    echo $api->formmail_send();
  });
  $router->match('GET', '/{email}/{mb_id}', function($email, $mb_id) use ($api) {
    echo $api->formmail($email, $mb_id);
  });
});
$router->match('GET', '/download/{no}', function($no) use ($router, $api) {
  $api->download($no);
});

$router->post('get_write_token/{bo_table}', function($bo_table) use ($api) {
  echo $api->set_write_token($bo_table);
});
$router->post('get_write_comment_token', function() use ($api) {
  echo $api->set_comment_token();
});
$router->mount('/social', function() use ($router, $api) {
  $router->get('/', function() use ($api) {
    echo $api->social();
  });
  $router->get('/config', function() use ($api) {
    echo $api->social_config();
  });
  $router->get('/popup', function() use ($api) {
    echo $api->social_popup();
  });
  $router->get('/token', function() use ($api) {
    echo $api->social_token();
  });
});
$router->run();