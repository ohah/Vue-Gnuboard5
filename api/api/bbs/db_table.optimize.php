<?php
trait db_optimize {
  public function db_optimize () {
    global $g5;
    $config = $this->config;
    // 최고관리자일 때만 실행
    if($config['cf_admin'] != $member['mb_id'] || $is_admin != 'super')
      return;

    // 실행일 비교
    if(isset($config['cf_optimize_date']) && $config['cf_optimize_date'] >= G5_TIME_YMD)
      return;

    // 설정일이 지난 접속자로그 삭제
    if($config['cf_visit_del'] > 0) {
      $tmp_before_date = date("Y-m-d", G5_SERVER_TIME - ($config['cf_visit_del'] * 86400));
      $sql = "DELETE FROM {$g5['visit_table']} WHERE vi_date < ?";
      $this->sql_query($sql, [$tmp_before_date]);
      $this->sql_query(" OPTIMIZE TABLE `{$g5['visit_table']}`, `{$g5['visit_sum_table']}` ");
    }

    // 설정일이 지난 인기검색어 삭제
    if($config['cf_popular_del'] > 0) {
      $tmp_before_date = date("Y-m-d", G5_SERVER_TIME - ($config['cf_popular_del'] * 86400));
      $sql = "DELETE FROM {$g5['popular_table']} WHERE pp_date < ?";
      $this->sql_query($sql, [$tmp_before_date]);
      $this->sql_query(" OPTIMIZE TABLE `{$g5['popular_table']}` ");
    }

    // 설정일이 지난 최근게시물 삭제
    if($config['cf_new_del'] > 0) {
      $sql = "DELETE FROM {$g5['board_new_table']} WHERE (TO_DAYS('".G5_TIME_YMDHIS."') - TO_DAYS(bn_datetime)) > '{$config['cf_new_del']}' ";
      $this->sql_query($sql);
      $this->sql_query(" OPTIMIZE TABLE `{$g5['board_new_table']}` ");
    }

    // 설정일이 지난 쪽지 삭제
    if($config['cf_memo_del'] > 0) {
      $sql = "DELETE FROM {$g5['memo_table']} WHERE (TO_DAYS('".G5_TIME_YMDHIS."') - TO_DAYS(me_send_datetime)) > '{$config['cf_memo_del']}' ";
      $this->sql_query($sql);
      $this->sql_query(" OPTIMIZE TABLE `{$g5['memo_table']}` ");
    }

    // 탈퇴회원 자동 삭제
    if($config['cf_leave_day'] > 0) {
      $sql = " select mb_id from {$g5['member_table']}
                  where (TO_DAYS('".G5_TIME_YMDHIS."') - TO_DAYS(mb_leave_date)) > '{$config['cf_leave_day']}'
                  and mb_memo not regexp '^[0-9]{8}.*삭제함' ";
      $result = $this->sql_query($sql);
      while ($row=sql_fetch_array($result)) {        
        $this->member_delete($row['mb_id']);
      }
    }

    // 음성 캡챠 파일 삭제
    $captcha_mp3 = glob(G5_DATA_PATH.'/cache/kcaptcha-*.mp3');
    if($captcha_mp3 && is_array($captcha_mp3)) {
      foreach ($captcha_mp3 as $file) {
        if (filemtime($file) + 86400 < G5_SERVER_TIME) {
          @unlink($file);
        }
      }
    }

    // 실행일 기록
    if(isset($config['cf_optimize_date'])) {
      $this->sql_query(" update {$g5['config_table']} set cf_optimize_date = ?", [G5_TIME_YMD]);
    }
  }
}