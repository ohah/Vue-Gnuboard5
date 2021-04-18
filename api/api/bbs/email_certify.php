<?php
trait email_certify {
  public function email_certify () {
    if(function_exists('check_mail_bot')){ check_mail_bot($_SERVER['REMOTE_ADDR']); }
    $mb_id  = isset($_GET['mb_id']) ? trim($_GET['mb_id']) : '';
    $mb_md5 = isset($_GET['mb_md5']) ? trim($_GET['mb_md5']) : '';
    $sql = " select mb_id, mb_email_certify2, mb_leave_date, mb_intercept_date from {$g5['member_table']} where mb_id = ? ";
    $row = sql_fetch($sql, [$mb_id]);
    if (!$row['mb_id'])
      $this->alert('존재하는 회원이 아닙니다.', G5_URL);

    if ( $row['mb_leave_date'] || $row['mb_intercept_date'] ){
      $this->alert('탈퇴 또는 차단된 회원입니다.', G5_URL);
    }
    // 인증 링크는 한번만 처리가 되게 한다.
    $this->sql_query(" update {$g5['member_table']} set mb_email_certify2 = '' where mb_id = ?", [$mb_id]);

    if ($mb_md5) {
      if ($mb_md5 == $row['mb_email_certify2']) {
        $this->sql_query(" update {$g5['member_table']} set mb_email_certify = ? where mb_id = ? ", [G5_TIME_YMDHIS, $mb_id]);
        $this->alert("메일인증 처리를 완료 하였습니다.\\n\\n지금부터 {$mb_id} 아이디로 로그인 가능합니다.", G5_URL);
      } else {
        $this->alert('메일인증 요청 정보가 올바르지 않습니다.', G5_URL);
      }
    }

    $this->alert('제대로 된 값이 넘어오지 않았습니다.', G5_URL);
  }
}