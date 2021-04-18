<?php
trait delete {
  public function delete($bo_table, $wr_id) {
    global $g5;
    $sca = $this->qstr['sca'];
    $sfl = $this->qstr['sfl'];
    $stx = $this->qstr['stx'];
    $sst = $this->qstr['sst'];
    $sod = $this->qstr['sod'];
    $spt = $this->qstr['spt'];
    $page = $this->qstr['page'];
    $write_table = $g5['write_prefix'].$bo_table;
    $write = $this->get_write($write_table, $wr_id);
    $member = $this->member;
    $config = $this->config;
    $board = $this->get_board_db($bo_table);
    $is_admin = $this->is_admin;
    $is_guest = $this->is_guest;
    $is_member = $this->is_member;
    $qstr = '';
    foreach ($this->qstr as $key => $value) {
      $qstr .= $key.'='.$value;
    }

    $wr_password = $_POST['wr_password'] ? $_POST['wr_password'] : '';
    $token = $_POST['token'] ? $_POST['token'] : '';

    $delete_token = $this->get_session('ss_delete_token');
    $this->set_session('ss_delete_token', '');

    if (!($token && $delete_token == $token)) {
      $this->alert('토큰 에러로 삭제 불가합니다.'.$token);
    }

    //$wr = $this->sql_fetch(" select * from $write_table where wr_id = '$wr_id' ");

    $count_write = $count_comment = 0;

    if ($is_admin == 'super') { // 최고관리자 통과
      ;
    } else if ($is_admin == 'group') { // 그룹관리자
      $mb = $this->get_member($write['mb_id']);
      if ($member['mb_id'] != $group['gr_admin']) // 자신이 관리하는 그룹인가?
        $this->alert('자신이 관리하는 그룹의 게시판이 아니므로 삭제할 수 없습니다.');
      else if ($member['mb_level'] < $mb['mb_level']) // 자신의 레벨이 크거나 같다면 통과
        $this->alert('자신의 권한보다 높은 권한의 회원이 작성한 글은 삭제할 수 없습니다.');
    } else if ($is_admin == 'board') { // 게시판관리자이면
      $mb = $this->get_member($write['mb_id']);
      if ($member['mb_id'] != $board['bo_admin']) // 자신이 관리하는 게시판인가?
        $this->alert('자신이 관리하는 게시판이 아니므로 삭제할 수 없습니다.');
      else if ($member['mb_level'] < $mb['mb_level']) // 자신의 레벨이 크거나 같다면 통과
        $this->alert('자신의 권한보다 높은 권한의 회원이 작성한 글은 삭제할 수 없습니다.');
    } else if ($member['mb_id']) {
      if ($member['mb_id'] !== $write['mb_id'])
        $this->alert('자신의 글이 아니므로 삭제할 수 없습니다.');
    } else {
      if ($write['mb_id'])
        $this->alert('로그인 후 삭제하세요.', G5_BBS_URL.'/login.php?url='.urlencode($this->get_pretty_url($bo_table, $wr_id)));
      else if (!$this->check_password($wr_password, $write['wr_password']))
        $this->alert('비밀번호가 틀리므로 삭제할 수 없습니다.');
    }

    $len = strlen($write['wr_reply']);
    if ($len < 0) $len = 0;
    $reply = substr($write['wr_reply'], 0, $len);

    // 원글만 구한다.
    $sql = " select count(*) as cnt from $write_table
                where wr_reply like '$reply%'
                and wr_id <> '{$write['wr_id']}'
                and wr_num = '{$write['wr_num']}'
                and wr_is_comment = 0 ";
    $row = $this->sql_fetch($sql);
    if ($row['cnt'] && !$is_admin)
      $this->alert('이 글과 관련된 답변글이 존재하므로 삭제 할 수 없습니다.\r\n우선 답변글부터 삭제하여 주십시오.');

    // 코멘트 달린 원글의 삭제 여부
    $sql = " select count(*) as cnt from $write_table
                where wr_parent = '$wr_id'
                and mb_id <> '{$member['mb_id']}'
                and wr_is_comment = 1 ";
    $row = $this->sql_fetch($sql);
    if ($row['cnt'] >= $board['bo_count_delete'] && !$is_admin) {
      $this->alert('이 글과 관련된 코멘트가 존재하므로 삭제 할 수 없습니다.\r\n코멘트가 '.$board['bo_count_delete'].'건 이상 달린 원글은 삭제할 수 없습니다.');
    }


    // 나라오름님 수정 : 원글과 코멘트수가 정상적으로 업데이트 되지 않는 오류를 잡아 주셨습니다.
    //$sql = " select wr_id, mb_id, wr_comment from $write_table where wr_parent = '$write[wr_id]' order by wr_id ";
    $sql = " select wr_id, mb_id, wr_is_comment, wr_content from $write_table where wr_parent = '{$write['wr_id']}' order by wr_id ";
    $result = $this->sql_query($sql);
    for($i=0;$i<count($result);$i++) {
      $row = $result[$i];
      // 원글이라면
      if (!$row['wr_is_comment']) {
        // 원글 포인트 삭제
        if (!$this->delete_point($row['mb_id'], $bo_table, $row['wr_id'], '쓰기'))
          $this->insert_point($row['mb_id'], $board['bo_write_point'] * (-1), "{$board['bo_subject']} {$row['wr_id']} 글삭제");

        // 업로드된 파일이 있다면 파일삭제
        $sql2 = " select * from {$g5['board_file_table']} where bo_table = '$bo_table' and wr_id = '{$row['wr_id']}' ";
        $result2 = $this->sql_query($sql2);
        for($k=0;$k<count($result2);$k++) {
          $row2 = $result[$k];
          $delete_file = run_replace('delete_file_path', G5_DATA_PATH.'/file/'.$bo_table.'/'.str_replace('../', '', $row2['bf_file']), $row2);
          if( file_exists($delete_file) ){
            @unlink($delete_file);
          }
          // 썸네일삭제
          if(preg_match("/\.({$config['cf_image_extension']})$/i", $row2['bf_file'])) {
            $this->delete_board_thumbnail($bo_table, $row2['bf_file']);
          }
        }

        // 에디터 썸네일 삭제
        $this->delete_editor_thumbnail($row['wr_content']);

        // 파일테이블 행 삭제
        $this->sql_query(" delete from {$g5['board_file_table']} where bo_table = '$bo_table' and wr_id = '{$row['wr_id']}' ");

        $count_write++;
      } else {
        // 코멘트 포인트 삭제
        if (!$this->delete_point($row['mb_id'], $bo_table, $row['wr_id'], '댓글')) {
          $this->insert_point($row['mb_id'], $board['bo_comment_point'] * (-1), "{$board['bo_subject']} {$write['wr_id']}-{$row['wr_id']} 댓글삭제");
        }

        $count_comment++;
      }
    }

    // 게시글 삭제
    $this->sql_query(" delete from $write_table where wr_parent = '{$write['wr_id']}' ");

    // 최근게시물 삭제
    $this->sql_query(" delete from {$g5['board_new_table']} where bo_table = '$bo_table' and wr_parent = '{$write['wr_id']}' ");

    // 스크랩 삭제
    $this->sql_query(" delete from {$g5['scrap_table']} where bo_table = '$bo_table' and wr_id = '{$write['wr_id']}' ");

    $bo_notice = $this->board_notice($board['bo_notice'], $write['wr_id']);
    $this->sql_query(" update {$g5['board_table']} set bo_notice = '{$bo_notice}' where bo_table = '{$bo_table}' ");

    // 글숫자 감소
    if ($count_write > 0 || $count_comment > 0) {
      $this->sql_query(" update {$g5['board_table']} set bo_count_write = bo_count_write - '{$count_write}', bo_count_comment = bo_count_comment - '{$count_comment}' where bo_table = '{$bo_table}' ");
    }

    $this->delete_cache_latest($bo_table);

    run_event('bbs_delete', $write, $board);
    $result = array('msg' => 'success');
    return $this->data_encode($result);
  }
  public function delete_comment($bo_table, $comment_id) {
    global $g5;
    $sca = $this->qstr['sca'];
    $sfl = $this->qstr['sfl'];
    $stx = $this->qstr['stx'];
    $sst = $this->qstr['sst'];
    $sod = $this->qstr['sod'];
    $spt = $this->qstr['spt'];
    $page = $this->qstr['page'];
    $write_table = $g5['write_prefix'].$bo_table;
    $write = $this->get_write($write_table, $wr_id);
    $member = $this->member;
    $config = $this->config;
    $board = $this->get_board_db($bo_table);
    $is_admin = $this->is_admin;
    $is_guest = $this->is_guest;
    $is_member = $this->is_member;
    $qstr = '';
    foreach ($this->qstr as $key => $value) {
      $qstr .= $key.'='.$value;
    }
    @extract($_POST);
    $delete_comment_token = $this->get_session('ss_delete_comment_'.$comment_id.'_token');
    $this->set_session('ss_delete_comment_'.$comment_id.'_token', '');

    if (!($token && $delete_comment_token == $token))
      $this->alert('토큰 에러로 삭제 불가합니다.');

    $write = $this->get_write($write_table , $comment_id);

    if (!$write['wr_id'] || !$write['wr_is_comment'])
      $this->alert('등록된 코멘트가 없거나 코멘트 글이 아닙니다.');

    if ($is_admin == 'super') {// 최고관리자 통과
      ;
    } else if ($is_admin == 'group') { // 그룹관리자
      $mb = $this->get_member($write['mb_id']);
      if ($member['mb_id'] === $group['gr_admin']) { // 자신이 관리하는 그룹인가?
        if ($member['mb_level'] >= $mb['mb_level']) {// 자신의 레벨이 크거나 같다면 통과
          ;
        } else {
          $this->alert('그룹관리자의 권한보다 높은 회원의 코멘트이므로 삭제할 수 없습니다.');
        }
      } else
        $this->alert('자신이 관리하는 그룹의 게시판이 아니므로 코멘트를 삭제할 수 없습니다.');
    } else if ($is_admin === 'board') { // 게시판관리자이면
      $mb = $this->get_member($write['mb_id']);
      if ($member['mb_id'] === $board['bo_admin']) { // 자신이 관리하는 게시판인가?
        if ($member['mb_level'] >= $mb['mb_level']) { // 자신의 레벨이 크거나 같다면 통과
          ;
        }else {
          $this->alert('게시판관리자의 권한보다 높은 회원의 코멘트이므로 삭제할 수 없습니다.');
        }
      } else
        $this->alert('자신이 관리하는 게시판이 아니므로 코멘트를 삭제할 수 없습니다.');
    } else if ($member['mb_id']) {
      if ($member['mb_id'] !== $write['mb_id'])
        $this->alert('자신의 글이 아니므로 삭제할 수 없습니다.');
    } else {
      if (!$this->check_password($wr_password, $write['wr_password']))
        $this->alert('비밀번호가 틀립니다.');
    }

    $len = strlen($write['wr_comment_reply']);
    if ($len < 0) $len = 0;
    $comment_reply = substr($write['wr_comment_reply'], 0, $len);

    $sql = " select count(*) as cnt from {$write_table}
            where wr_comment_reply like '{$comment_reply}%'
            and wr_id <> '{$comment_id}'
            and wr_parent = '{$write['wr_parent']}'
            and wr_comment = '{$write['wr_comment']}'
            and wr_is_comment = 1 ";
    $row = $this->sql_fetch($sql);
    if ($row['cnt'] && !$is_admin)
      $this->alert('이 코멘트와 관련된 답변코멘트가 존재하므로 삭제 할 수 없습니다.');

    // 코멘트 포인트 삭제
    if (!$this->delete_point($write['mb_id'], $bo_table, $comment_id, '댓글'))
      $this->insert_point($write['mb_id'], $board['bo_comment_point'] * (-1), "{$board['bo_subject']} {$write['wr_parent']}-{$comment_id} 댓글삭제");

    // 코멘트 삭제
    $this->sql_query(" delete from {$write_table} where wr_id = '{$comment_id}' ");

    // 코멘트가 삭제되므로 해당 게시물에 대한 최근 시간을 다시 얻는다.
    $sql = " select max(wr_datetime) as wr_last from {$write_table} where wr_parent = '{$write['wr_parent']}' ";
    $row = $this->sql_fetch($sql);

    // 원글의 코멘트 숫자를 감소
    $this->sql_query(" update {$write_table} set wr_comment = wr_comment - 1, wr_last = '{$row['wr_last']}' where wr_id = '{$write['wr_parent']}' ");

    // 코멘트 숫자 감소
    $this->sql_query(" update {$g5['board_table']} set bo_count_comment = bo_count_comment - 1 where bo_table = '{$bo_table}' ");

    // 새글 삭제
    $this->sql_query(" delete from {$g5['board_new_table']} where bo_table = '{$bo_table}' and wr_id = '{$comment_id}' ");

    $this->delete_cache_latest($bo_table);

    run_event('bbs_delete_comment', $comment_id, $board);

    return $this->get_cmt_list($bo_table, $write['wr_parent']);
  }

  public function delete_all($bo_table) {
    global $g5;
    $sca = $this->qstr['sca'];
    $sfl = $this->qstr['sfl'];
    $stx = $this->qstr['stx'];
    $sst = $this->qstr['sst'];
    $sod = $this->qstr['sod'];
    $spt = $this->qstr['spt'];
    $page = $this->qstr['page'];
    $write_table = $g5['write_prefix'].$bo_table;
    // $write = $this->get_write($write_table, $wr_id);
    $member = $this->member;
    $config = $this->config;
    $board = $this->get_board_db($bo_table);
    $is_admin = $this->is_admin;
    $is_guest = $this->is_guest;
    $is_member = $this->is_member;
    $qstr = '';
    foreach ($this->qstr as $key => $value) {
      $qstr .= $key.'='.$value;
    }

    if(!$is_admin)
      $this->alert('접근 권한이 없습니다.', G5_URL);

    $count_write = 0;
    $count_comment = 0;

    $tmp_array = array();
    if ($wr_id) // 건별삭제
      $tmp_array[0] = $wr_id;
    else // 일괄삭제
      $tmp_array = (isset($_POST['chk_wr_id']) && is_array($_POST['chk_wr_id'])) ? $_POST['chk_wr_id'] : array();

    $chk_count = count($tmp_array);

    if($chk_count > ($this->is_mobile() ? $board['bo_mobile_page_rows'] : $board['bo_page_rows']))
      $this->alert('올바른 방법으로 이용해 주십시오.');


    // 거꾸로 읽는 이유는 답변글부터 삭제가 되어야 하기 때문임
    for ($i=$chk_count-1; $i>=0; $i--) {
      $write = $this->sql_fetch(" select * from $write_table where wr_id = '{$tmp_array[$i]}'");
      
      if ($is_admin == 'super') {// 최고관리자 통과
        ;
      } else if ($is_admin == 'group') {// 그룹관리자
        $mb = $this->get_member($write['mb_id']);
        if ($member['mb_id'] == $group['gr_admin']) {// 자신이 관리하는 그룹인가?
          if ($member['mb_level'] >= $mb['mb_level']) // 자신의 레벨이 크거나 같다면 통과
            ;
          else
            continue;
        }
        else
          continue;
      } else if ($is_admin == 'board') {// 게시판관리자이면
        $mb = $this->get_member($write['mb_id']);
        if ($member['mb_id'] == $board['bo_admin']) {// 자신이 관리하는 게시판인가?
          if ($member['mb_level'] >= $mb['mb_level']){ // 자신의 레벨이 크거나 같다면 통과
            ;
          } else {
            continue;
          }
        } else if ($member['mb_id'] && $member['mb_id'] == $write['mb_id']) {// 자신의 글이라면
          ;
        } else if ($wr_password && !$write['mb_id'] && $this->check_password($wr_password, $write['wr_password'])) {// 비밀번호가 같다면
          ;
        } else {
          continue;   // 나머지는 삭제 불가
        }

      }

      $len = strlen($write['wr_reply']);
      if ($len < 0) $len = 0;
      $reply = substr($write['wr_reply'], 0, $len);

      // 원글만 구한다.
      $sql = " select count(*) as cnt from $write_table
              where wr_reply like '$reply%'
              and wr_id <> '{$write['wr_id']}'
              and wr_num = '{$write['wr_num']}'
              and wr_is_comment = 0 ";
      $row = $this->sql_fetch($sql);
      if ($row['cnt'])
        continue;

      // 나라오름님 수정 : 원글과 코멘트수가 정상적으로 업데이트 되지 않는 오류를 잡아 주셨습니다.
      //$sql = " select wr_id, mb_id, wr_comment from {$write_table} where wr_parent = '{$write[wr_id]}' order by wr_id ";
      $sql = " select wr_id, mb_id, wr_is_comment, wr_content from $write_table where wr_parent = '{$write['wr_id']}' order by wr_id ";
      $result = $this->sql_query($sql);
      for($j=0;$j<count($result);$j++) {
        $row = $result[$j];
        // 원글이라면
        if (!$row['wr_is_comment']) {
          // 원글 포인트 삭제
          if (!$this->delete_point($row['mb_id'], $bo_table, $row['wr_id'], '쓰기'))
            $this->insert_point($row['mb_id'], $board['bo_write_point'] * (-1), "{$board['bo_subject']} {$row['wr_id']} 글 삭제");

          // 업로드된 파일이 있다면
          $sql2 = " select * from {$g5['board_file_table']} where bo_table = '$bo_table' and wr_id = '{$row['wr_id']}' ";
          $result2 = $this->sql_query($sql2);
          for($k=0;$k<count($result);$k++) {
            $row2 = $result2[$k];
            // 파일삭제
            $delete_file = run_replace('delete_file_path', G5_DATA_PATH.'/file/'.$bo_table.'/'.str_replace('../', '',$row2['bf_file']), $row2);
            if( file_exists($delete_file) ){
              @unlink($delete_file);
            }

            // 썸네일삭제
            if(preg_match("/\.({$config['cf_image_extension']})$/i", $row2['bf_file'])) {
              $this->delete_board_thumbnail($bo_table, $row2['bf_file']);
            }
          }
          // 에디터 썸네일 삭제
          $this->delete_editor_thumbnail($row['wr_content']);
          // 파일테이블 행 삭제
          $this->sql_query(" delete from {$g5['board_file_table']} where bo_table = '$bo_table' and wr_id = '{$row['wr_id']}' ");

          $count_write++;
        } else {
          // 코멘트 포인트 삭제
          if (!$this->delete_point($row['mb_id'], $bo_table, $row['wr_id'], '댓글'))
            $this->insert_point($row['mb_id'], $board['bo_comment_point'] * (-1), "{$board['bo_subject']} {$write['wr_id']}-{$row['wr_id']} 댓글삭제");

          $count_comment++;
        }
        

        // 게시글 삭제
        $this->sql_query(" delete from $write_table where wr_parent = '{$write['wr_id']}' ");

        // 최근게시물 삭제
        $this->sql_query(" delete from {$g5['board_new_table']} where bo_table = '$bo_table' and wr_parent = '{$write['wr_id']}' ");

        // 스크랩 삭제
        $this->sql_query(" delete from {$g5['scrap_table']} where bo_table = '$bo_table' and wr_id = '{$write['wr_id']}' ");

        /*
        // 공지사항 삭제
        $notice_array = explode(',', trim($board['bo_notice']));
        $bo_notice = "";
        for ($k=0; $k<count($notice_array); $k++)
            if ((int)$write['wr_id'] != (int)$notice_array[$k])
                $bo_notice .= $notice_array[$k].',';
        $bo_notice = trim($bo_notice);
        */
        $bo_notice = $this->board_notice($board['bo_notice'], $write['wr_id']);
        $this->sql_query(" update {$g5['board_table']} set bo_notice = '$bo_notice' where bo_table = '$bo_table' ");
        $board['bo_notice'] = $bo_notice;
      }

      // 글숫자 감소
      if ($count_write > 0 || $count_comment > 0) {
        $this->sql_query(" update {$g5['board_table']} set bo_count_write = bo_count_write - '$count_write', bo_count_comment = bo_count_comment - '$count_comment' where bo_table = '$bo_table' ");
      }

      // 4.11
      $this->delete_cache_latest($bo_table);

      run_event('bbs_delete_all', $tmp_array, $board);

    }
  }
}