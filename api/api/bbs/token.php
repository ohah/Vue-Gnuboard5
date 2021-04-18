<?php
trait token {
    // 불법접근을 막도록 토큰을 생성하면서 토큰값을 리턴
  public function get_write_token($bo_table) {
    $token = md5(uniqid(rand(), true));
    $this->set_session('ss_write_'.$bo_table.'_token', $token);

    return $token;
  }
  // POST로 넘어온 토큰과 세션에 저장된 토큰 비교
  function check_write_token($bo_table) {
    if(!$bo_table)
      $this->alert('올바른 방법으로 이용해 주십시오.', G5_URL);

    $token = $this->get_session('ss_write_'.$bo_table.'_token');
    $this->set_session('ss_write_'.$bo_table.'_token', '');

    if(!$token || !$_REQUEST['token'] || $token != $_REQUEST['token'])
      $this->alert('올바른 방법으로 이용해 주십시오.', G5_URL);

    return true;
  }
  public function set_write_token($bo_table) {
    if(!$bo_table) {
      $this->alert('게시판 정보가 올바르지 않습니다');
    }
     
      $this->set_session('ss_write_'.$bo_table.'_token', '');
      $token = $this->get_write_token($bo_table);

      return json_encode(array('error'=>'', 'token'=>$token, 'url'=>''));
  }
  public function set_comment_token() {
    $ss_name = 'ss_comment_token';

    $this->set_session($ss_name, '');

    $token = $this->_token();
    $res = $this->get_session($ss_name)."?";

    $this->set_session($ss_name, $token);

    return json_encode(array('token'=>$token));
  }
    // 토큰 생성
  public function _token() {    
    return md5(uniqid(rand(), true));
  }

  // 불법접근을 막도록 토큰을 생성하면서 토큰값을 리턴
  public function get_token() {
    $token = md5(uniqid(rand(), true));
    $this->set_session('ss_token', $token);

    return $token;
  }
  // POST로 넘어온 토큰과 세션에 저장된 토큰 비교
  public function check_token() {
    $this->set_session('ss_token', '');
    return true;
  }

}