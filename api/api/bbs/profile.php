<?php
trait profile{
  public function profile($mb_id) {
    $member = $this->member;
    $is_admin = $this->is_admin;
    $is_guest = $this->is_guest;
    $is_member = $this->is_member;
    
    if (!$member['mb_id'])
      $this->alert('회원만 이용하실 수 있습니다.');

    if (!$member['mb_open'] && $is_admin != 'super' && $member['mb_id'] != $mb_id)
      $this->alert('자신의 정보를 공개하지 않으면 다른분의 정보를 조회할 수 없습니다.\\n\\n정보공개 설정은 회원정보수정에서 하실 수 있습니다.');
    
    $mb_id = isset($mb_id) ? $mb_id : '';

    $mb = $this->get_member($mb_id);

    if (!$mb['mb_id'])
      $this->alert('회원정보가 존재하지 않습니다.\\n\\n탈퇴하였을 수 있습니다.');

    if (!$mb['mb_open'] && $is_admin != 'super' && $member['mb_id'] != $mb_id)
      $this->alert('정보공개를 하지 않았습니다.');

    $mb_sideview = $this->get_sideview($mb['mb_id'], $this->get_text($mb['mb_nick']), $mb['mb_email'], $mb['mb_homepage'], $mb['mb_open']);

    // 회원가입후 몇일째인지? + 1 은 당일을 포함한다는 뜻
    $sql = " select (TO_DAYS('".G5_TIME_YMDHIS."') - TO_DAYS('{$mb['mb_datetime']}') + 1) as days ";
    $row = $this->sql_fetch($sql);
    $mb_reg_after = $row['days'];

    $mb_homepage = $this->set_http($this->get_text($this->clean_xss_tags($mb['mb_homepage'])));
    $mb_profile = $mb['mb_profile'] ? $this->conv_content($mb['mb_profile'],0) : '소개 내용이 없습니다.';

    $result = array();
    $result['mb_sideview'] = $mb_sideview;
    $result['mb_homepage'] = $mb_homepage;
    $result['mb_reg_after'] = $mb_reg_after;
    $result['mb_regsiter_join'] = ($member['mb_level'] >= $mb['mb_level']) ?  substr($mb['mb_datetime'],0,10) ." (".number_format($mb_reg_after)." 일)" : "알 수 없음";
    $result['mb_last_connect'] = ($member['mb_level'] >= $mb['mb_level']) ? $mb['mb_today_login'] : "알 수 없음";
    $result['mb_profile'] = $mb_profile;
    $result['mb_point'] = $mb['mb_point'];
    $result['mb_level'] = $mb['mb_level'];

    return $this->data_encode($result);
  }
}