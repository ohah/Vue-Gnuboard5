<?php
trait board {
  public function board_chk($bo_table, $wr_id = '') {
    global $g5;
    $sca = $this->qstr['sca'];
    $sfl = $this->qstr['sfl'];
    $stx = $this->qstr['stx'];
    $sst = $this->qstr['sst'];
    $sod = $this->qstr['sod'];
    $spt = $this->qstr['spt'];
    $page = $this->qstr['page'];
    $write_table = $g5['write_prefix'].$bo_table;
    $res = $this->sql_query("SELECT * FROM {$write_table}");
    $member = $this->member;
    $config = $this->config;
    $board = $this->get_board_db($bo_table);
    $qstr = '';
    foreach ($this->qstr as $key => $value) {
      if($value) $qstr .= $key.'='.$value;
    }
    $write_table = $g5['write_prefix'].$bo_table;
    $write = $this->get_write($write_table, $wr_id);
    $is_admin = $this->is_admin;
    $is_guest = $this->$is_guest;
    $is_member = $this->is_member;
    $group = $this->get_group($board['gr_id']);
    if ((isset($wr_id) && $wr_id)) {
      // 글이 없을 경우 해당 게시판 목록으로 이동
      if(!$write['wr_id']) {
        $this->alert('글이 존재하지 않습니다\r\n글이 삭제 되었거나 이동된 경우입니다.');
      }
      if (isset($group['gr_use_access']) && $group['gr_use_access']) {
        if($is_guest) {
          $this->alert('비회원은 이 게시판에 접근할 권한이 없습니다.\r\n회원이시라면 로그인 후 이용해 보십시오.');
        }
        if ($is_admin == "super" || $is_admin == "group") {
          ;
        } else {
          // 그룹접근        
          $row = $this->sql_fetch("SELECT count(*) as cnt FROM {$g5['group_member_table']} WHERE gr_id = ? AND mb_id = ?", [$board['gr_id'], $member['mb_id']]);
          if (!$row['cnt']) {
            $this->alert('접근 권한이 없으므로 글읽기가 불가합니다.\r\n궁금하신 사항은 관리자에게 문의 바랍니다.');
          }
        }
      }
      // 로그인된 회원의 권한이 설정된 읽기 권한보다 작다면
      if ($member['mb_level'] < $board['bo_read_level']) {
        if ($this->is_member) {
          $this->alert('글을 읽을 권한이 없습니다.');
          exit;
        } else {
          $this->alert('글을 읽을 권한이 없습니다.\\n\\n회원이시라면 로그인 후 이용해 보십시오.');
          exit;
        }
      }
      // 본인확인을 사용한다면
      if ($config['cf_cert_use'] && !$is_admin) {
        // 인증된 회원만 가능
        if ($board['bo_use_cert'] != '' && $is_guest) {
          $this->alert('이 게시판은 본인확인 하신 회원님만 글읽기가 가능합니다.\\n\\n회원이시라면 로그인 후 이용해 보십시오.');
          exit;
        }
        if ($board['bo_use_cert'] == 'cert' && !$member['mb_certify']) {
          $this->alert('이 게시판은 본인확인 하신 회원님만 글읽기가 가능합니다.\\n\\n회원정보 수정에서 본인확인을 해주시기 바랍니다.');
          exit;
        }
        if ($board['bo_use_cert'] == 'adult' && !$member['mb_adult']) {
          $this->alert('이 게시판은 본인확인으로 성인인증 된 회원님만 글읽기가 가능합니다.\\n\\n현재 성인인데 글읽기가 안된다면 회원정보 수정에서 본인확인을 다시 해주시기 바랍니다.');
          exit;
        }
        if ($board['bo_use_cert'] == 'hp-cert' && $member['mb_certify'] != 'hp') {
          $this->alert('이 게시판은 휴대폰 본인확인 하신 회원님만 글읽기가 가능합니다.\\n\\n회원정보 수정에서 휴대폰 본인확인을 해주시기 바랍니다.');
          exit;
        }
        if ($board['bo_use_cert'] == 'hp-adult' && (!$member['mb_adult'] || $member['mb_certify'] != 'hp')) {
          $this->alert('이 게시판은 휴대폰 본인확인으로 성인인증 된 회원님만 글읽기가 가능합니다.\\n\\n현재 성인인데 글읽기가 안된다면 회원정보 수정에서 휴대폰 본인확인을 다시 해주시기 바랍니다.');
          exit;
        }
      }

      // 자신의 글이거나 관리자라면 통과
      if (($write['mb_id'] && $write['mb_id'] === $member['mb_id']) || $is_admin) {
        ;
      } else {
        // 비밀글이라면
        if (strstr($write['wr_option'], "secret")) {
          // 회원이 비밀글을 올리고 관리자가 답변글을 올렸을 경우
          // 회원이 관리자가 올린 답변글을 바로 볼 수 없던 오류를 수정
          $is_owner = false;
          if ($write['wr_reply'] && $member['mb_id']) {
            $row = $this->sql_fetch("SELECT mb_id {$write_table} WHERE wr_num = ? AND wr_reply = ? AND wr_is_comment = ?", [$write['wr_num'], '', '0']);
            if ($row['mb_id'] === $member['mb_id']) $is_owner = true;
          }
          $ss_name = 'ss_secret_'.$bo_table.'_'.$write['wr_num'];
          if (!$is_owner) {
            if (!$this->get_session($ss_name)) {
              $pass = $this->password('s', $bo_table, $wr_id);
              if(is_array($pass) && $pass['action']) {
                echo $this->data_encode($pass);
                exit;
              }
            }
          }
          $this->set_session($ss_name, TRUE);
        }
      }
      // 한번 읽은글은 브라우저를 닫기전까지는 카운트를 증가시키지 않음
      $ss_name = 'ss_view_'.$bo_table.'_'.$wr_id;
      if (!$this->get_session($ss_name)) {
        $this->sql_query(" update {$write_table} set wr_hit = wr_hit + 1 where wr_id = '{$wr_id}' ");
        // 자신의 글이면 통과
        if ($write['mb_id'] && $write['mb_id'] === $member['mb_id']) {
          ;
        } else if ($is_guest && $board['bo_read_level'] == 1 && $write['wr_ip'] == $_SERVER['REMOTE_ADDR']) {
          // 비회원이면서 읽기레벨이 1이고 등록된 아이피가 같다면 자신의 글이므로 통과
          ;
        } else {
          // 글읽기 포인트가 설정되어 있다면
          if ($config['cf_use_point'] && $board['bo_read_point'] && $member['mb_point'] + $board['bo_read_point'] < 0) {
            $this->alert('보유하신 포인트('.number_format($member['mb_point']).')가 없거나 모자라서 글읽기('.number_format($board['bo_read_point']).')가 불가합니다.\r\n포인트를 모으신 후 다시 글읽기 해 주십시오.');
            exit;
          }
          //인서트 포인트 함수 추가해야함
          $this->insert_point($member['mb_id'], $board['bo_read_point'], (($this->is_mobile() && $board['bo_mobile_subject']) ? $board['bo_mobile_subject'] : $board['bo_subject']).' '.$wr_id.' 글읽기', $bo_table, $wr_id, '읽기');
        }
        $this->set_session($ss_name, TRUE);
      }
    }else { //리스트 검사
      if ($member['mb_level'] < $board['bo_list_level']) {
        if ($member['mb_id']) {
          $this->alert('목록을 읽을 권한이 없습니다.');
        }else {
          $this->alert('목록을 볼 권한이 없습니다.\r\n회원이시라면 로그인 후 이용해 보십시오.');
        }
      }

      // 본인확인을 사용한다면
      if ($config['cf_cert_use'] && !$is_admin) {
        // 인증된 회원만 가능
        if ($board['bo_use_cert'] != '' && $is_guest) {
          $this->alert('이 게시판은 본인확인 하신 회원님만 글읽기가 가능합니다.\r\n회원이시라면 로그인 후 이용해 보십시오.');
        }
        if ($board['bo_use_cert'] == 'cert' && !$member['mb_certify']) {
          $this->alert('이 게시판은 본인확인 하신 회원님만 글읽기가 가능합니다.\r\n회원정보 수정에서 본인확인을 해주시기 바랍니다.');
        }
        if ($board['bo_use_cert'] == 'adult' && !$member['mb_adult']) {
          $this->alert('이 게시판은 본인확인으로 성인인증 된 회원님만 글읽기가 가능합니다.\r\n현재 성인인데 글읽기가 안된다면 회원정보 수정에서 본인확인을 다시 해주시기 바랍니다.');
        }
        if ($board['bo_use_cert'] == 'hp-cert' && $member['mb_certify'] != 'hp') {
          $this->alert('이 게시판은 휴대폰 본인확인 하신 회원님만 글읽기가 가능합니다.\r\n회원정보 수정에서 휴대폰 본인확인을 해주시기 바랍니다.');
        }
        if ($board['bo_use_cert'] == 'hp-adult' && (!$member['mb_adult'] || $member['mb_certify'] != 'hp')) {
          $this->alert('이 게시판은 휴대폰 본인확인으로 성인인증 된 회원님만 글읽기가 가능합니다.\r\n현재 성인인데 글읽기가 안된다면 회원정보 수정에서 휴대폰 본인확인을 다시 해주시기 바랍니다.');
        }
      }
    }
    return "";
  }
}