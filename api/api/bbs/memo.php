<?php
trait memo  {
  public function memo () {
    global $g5;
    $sca = $this->qstr['sca'];
    $sfl = $this->qstr['sfl'];
    $stx = $this->qstr['stx'];
    $sst = $this->qstr['sst'];
    $sod = $this->qstr['sod'];
    $spt = $this->qstr['spt'];
    $page = $this->qstr['page'];
    $write_table = $g5['write_prefix'].$bo_table;
    $member = $this->member;
    $config = $this->config;
    $is_admin = $this->is_admin;
    $is_guest = $this->is_guest;
    $is_member = $this->is_member;
    
    parse_str(parse_url($_SERVER["REQUEST_URI"],PHP_URL_QUERY), $query); // GET

    if ($is_guest)
      $this->alert('회원만 이용하실 수 있습니다.');

    $this->set_session('ss_memo_delete_token', $token = uniqid(time()));

    $kind = isset($query['kind']) ? $this->clean_xss_tags($query['kind'], 0, 1) : 'recv';

    if ($kind == 'recv')
      $unkind = 'send';
    else if ($kind == 'send')
      $unkind = 'recv';
    else
      $this->alert(''.$kind .'값을 넘겨주세요.');

    if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)

    run_event('memo_list', $kind, $unkind, $page);

    $sql = " select count(*) as cnt from {$g5['memo_table']} where me_{$kind}_mb_id = '{$member['mb_id']}' and me_type = :kind";
    $row = $this->pdo_fetch($sql, array("kind"=>$kind));
    $total_count = $row['cnt'];

    $total_page  = ceil($total_count / $config['cf_page_rows']);  // 전체 페이지 계산
    $from_record = ((int) $page - 1) * $config['cf_page_rows']; // 시작 열을 구함

    if ($kind == 'recv') {
      $kind_title = '받은';
      $recv_img = 'on';
      $send_img = 'off';
    }
    else {
      $kind_title = '보낸';
      $recv_img = 'off';
      $send_img = 'on';
    }

    $list = array();

    $sql = " select a.*, b.mb_id, b.mb_nick, b.mb_email, b.mb_homepage
                from {$g5['memo_table']} a
                left join {$g5['member_table']} b on (a.me_{$unkind}_mb_id = b.mb_id)
                where a.me_{$kind}_mb_id = '{$member['mb_id']}' and a.me_type = :kind
                order by a.me_id desc limit $from_record, {$config['cf_page_rows']} ";

    $result = $this->pdo_query($sql, array("kind"=>$kind));
    for ($i=0; $i<count($result); $i++) {
      $row = $result[$i];
      $list[$i] = $row;

      $mb_id = $row["me_{$unkind}_mb_id"];

      if ($row['mb_nick'])
        $mb_nick = $row['mb_nick'];
      else
        $mb_nick = '정보없음';

      $name = $this->get_sideview($row['mb_id'], $row['mb_nick'], $row['mb_email'], $row['mb_homepage']);

      if (substr($row['me_read_datetime'],0,1) == 0)
          $read_datetime = '아직 읽지 않음';
      else
          $read_datetime = substr($row['me_read_datetime'],2,14);

      $send_datetime = substr($row['me_send_datetime'],2,14);

      $list[$i]['mb_id'] = $mb_id;
      $list[$i]['name'] = $name;
      $list[$i]['send_datetime'] = $send_datetime;
      $list[$i]['read_datetime'] = $read_datetime;
      $list[$i]['view_me_id'] = $row['me_id'];
      $list[$i]['del_token'] = $token;
      $list[$i]['kind'] = $kind;
    }

    $write_pages = $this->get_paging($this->is_mobile() ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "./memo.php?kind=$kind".$qstr."&amp;page=");

    $result = array();
    $result['list'] = $list;
    $result['total_count'] = $total_count;
    $result['page'] = $page;
    $result['page_rows'] = $config['cf_page_rows'];

    return $this->data_encode($result);
  }
  public function memo_form() {
    global $g5;
    $sca = $this->qstr['sca'];
    $sfl = $this->qstr['sfl'];
    $stx = $this->qstr['stx'];
    $sst = $this->qstr['sst'];
    $sod = $this->qstr['sod'];
    $spt = $this->qstr['spt'];
    $page = $this->qstr['page'];
    $write_table = $g5['write_prefix'].$bo_table;
    $member = $this->member;
    $config = $this->config;
    $is_admin = $this->is_admin;
    $is_guest = $this->is_guest;
    $is_member = $this->is_member;
    if ($is_guest)
      $this->alert('회원만 이용하실 수 있습니다.');

    if (!$member['mb_open'] && $is_admin != 'super' && $member['mb_id'] != $mb_id)
      $this->alert("자신의 정보를 공개하지 않으면 다른분에게 쪽지를 보낼 수 없습니다. 정보공개 설정은 회원정보수정에서 하실 수 있습니다.");
    
    parse_str(parse_url($_SERVER["REQUEST_URI"],PHP_URL_QUERY), $query); // GET
    $me_recv_mb_id = $query['me_recv_mb_id'];
    $me_id = $query['me_id'];
    // 탈퇴한 회원에게 쪽지 보낼 수 없음
    if ($me_recv_mb_id) {
      $mb = $this->get_member($me_recv_mb_id);
      if (!$mb['mb_id'])
        $this->alert('회원정보가 존재하지 않습니다.\\n\\n탈퇴하였을 수 있습니다.');

      if (!$mb['mb_open'] && $is_admin != 'super')
        $this->alert('정보공개를 하지 않았습니다.');

      // 4.00.15
      $row = $this->pdo_fetch(" select me_memo from {$g5['memo_table']} where me_id = :me_id and (me_recv_mb_id = '{$member['mb_id']}' or me_send_mb_id = '{$member['mb_id']}') ", array("me_id"=>$me_id));
      if ($row['me_memo'])  {
          $content = "\n\n\n".' >'
                          ."\n".' >'
                          ."\n".' >'.str_replace("\n", "\n> ", get_text($row['me_memo'], 0))
                          ."\n".' >'
                          .' >';

      }
    }
    $content = "";
    $result = array();
    $result['content'] = $content;
    $result['captcha_html'] = $this->captcha_html();

    return $this->data_encode($result);
  }
  public function memo_form_update () {
    global $g5;
    $sca = $this->qstr['sca'];
    $sfl = $this->qstr['sfl'];
    $stx = $this->qstr['stx'];
    $sst = $this->qstr['sst'];
    $sod = $this->qstr['sod'];
    $spt = $this->qstr['spt'];
    $page = $this->qstr['page'];
    $write_table = $g5['write_prefix'].$bo_table;
    $member = $this->member;
    $config = $this->config;
    $is_admin = $this->is_admin;
    $is_guest = $this->is_guest;
    $is_member = $this->is_member;
    if ($is_guest)
      $this->alert('회원만 이용하실 수 있습니다.');

    if (!$this->chk_captcha()) {
      $this->alert('자동등록방지 숫자가 틀렸습니다.');
    }

    $recv_list = isset($_POST['me_recv_mb_id']) ? explode(',', trim($_POST['me_recv_mb_id'])) : array();
    $str_nick_list = '';
    $msg = '';
    $error_list  = array();
    $member_list = array('id'=>array(), 'nick'=>array());

    run_event('memo_form_update_before', $recv_list);

    for ($i=0; $i<count($recv_list); $i++) {
      $row = $this->sql_fetch(" select mb_id, mb_nick, mb_open, mb_leave_date, mb_intercept_date from {$g5['member_table']} where mb_id = :recv_list ", array("recv_list"=>$recv_list[$i]));
      if ($row) {
        if ($is_admin || ($row['mb_open'] && (!$row['mb_leave_date'] && !$row['mb_intercept_date']))) {
          $member_list['id'][]   = $row['mb_id'];
          $member_list['nick'][] = $row['mb_nick'];
        } else {
          $error_list[]   = $recv_list[$i];
        }
      }
    }

    $error_msg = implode(",", $error_list);

    if ($error_msg && !$is_admin)
      $this->alert("회원아이디 '{$error_msg}' 은(는) 존재(또는 정보공개)하지 않는 회원아이디 이거나 탈퇴, 접근차단된 회원아이디 입니다.\\n쪽지를 발송하지 않았습니다.");

    if (! count($member_list['id'])){
      $this->alert('해당 회원이 존재하지 않습니다.');
    }

    if (!$is_admin) {
      if (count($member_list['id'])) {
        $point = (int)$config['cf_memo_send_point'] * count($member_list['id']);
        if ($point) {
          if ($member['mb_point'] - $point < 0) {
            $this->alert('보유하신 포인트('.number_format($member['mb_point']).'점)가 모자라서 쪽지를 보낼 수 없습니다.');
          }
        }
      }
    }

    for ($i=0; $i<count($member_list['id']); $i++) {
      $tmp_row = $this->sql_fetch(" select max(me_id) as max_me_id from {$g5['memo_table']} ");
      $me_id = $tmp_row['max_me_id'] + 1;

      $recv_mb_id   = $member_list['id'][$i];
      $recv_mb_nick = $this->get_text($member_list['nick'][$i]);

      // 받는 회원 쪽지 INSERT
      $sql = " insert into {$g5['memo_table']} ( me_recv_mb_id, me_send_mb_id, me_send_datetime, me_memo, me_read_datetime, me_type, me_send_ip ) values ( '$recv_mb_id', '{$member['mb_id']}', '".G5_TIME_YMDHIS."', :me_memo, '0000-00-00 00:00:00' , 'recv', '{$_SERVER['REMOTE_ADDR']}' )";

      $this->pdo_query($sql, array("me_memo"=>$_POST['me_memo']));

      if( $me_id = $this->db->lastInsertId() ){
        // 보내는 회원 쪽지 INSERT
        $sql = " insert into {$g5['memo_table']} ( me_recv_mb_id, me_send_mb_id, me_send_datetime, me_memo, me_read_datetime, me_send_id, me_type , me_send_ip ) values ( '$recv_mb_id', '{$member['mb_id']}', '".G5_TIME_YMDHIS."', :me_memo, '0000-00-00 00:00:00', :me_id, 'send', '{$_SERVER['REMOTE_ADDR']}' ) ";
        $this->pdo_query($sql, array("me_memo"=>$_POST['me_memo'], "me_id"=>$me_id));
        $member_list['me_id'][$i] = $me_id;
      }

      // 실시간 쪽지 알림 기능
      $sql = " update {$g5['member_table']} set mb_memo_call = '{$member['mb_id']}', mb_memo_cnt = '".$this->get_memo_not_read($recv_mb_id)."' where mb_id = '$recv_mb_id' ";
      $this->sql_query($sql);

      if (!$is_admin) {
        $this->insert_point($member['mb_id'], (int)$config['cf_memo_send_point'] * (-1), $recv_mb_nick.'('.$recv_mb_id.')님께 쪽지 발송', '@memo', $recv_mb_id, $me_id);
      }
    }
    if ($member_list) {
      $redirect_url = G5_HTTP_BBS_URL."/memo.php?kind=send";
      $str_nick_list = implode(',', $member_list['nick']);
      run_event('memo_form_update_after', $member_list, $str_nick_list, $redirect_url, $_POST['me_memo']);
      $this->alert($str_nick_list." 님께 쪽지를 전달하였습니다.", $redirect_url, false);
    } else {
      $redirect_url = G5_HTTP_BBS_URL."/memo_form.php";
      run_event('memo_form_update_failed', $member_list, $redirect_url, $_POST['me_memo']);
      $this->alert("회원아이디 오류 같습니다.", $redirect_url, false);
    }
  }
  
  public function memo_delete ($me_id) {
    global $g5;
    $sca = $this->qstr['sca'];
    $sfl = $this->qstr['sfl'];
    $stx = $this->qstr['stx'];
    $sst = $this->qstr['sst'];
    $sod = $this->qstr['sod'];
    $spt = $this->qstr['spt'];
    $page = $this->qstr['page'];
    $write_table = $g5['write_prefix'].$bo_table;
    $member = $this->member;
    $config = $this->config;
    $is_admin = $this->is_admin;
    $is_guest = $this->is_guest;
    $is_member = $this->is_member;
    if (!$is_member)
      $this->alert('회원만 이용하실 수 있습니다.');

    parse_str(parse_url($_SERVER["REQUEST_URI"],PHP_URL_QUERY), $query); // GET
    $token = $query['token'];
    $delete_token = $this->get_session('ss_memo_delete_token');
    $this->set_session('ss_memo_delete_token', '');
    if (!($token && $delete_token == $token))
      $this->alert('토큰 에러로 삭제 불가합니다.');

    $me_id = $me_id ? (int) $me_id : 0;

    $sql = " select * from {$g5['memo_table']} where me_id = :me_id ";
    $row = $this->pdo_fetch($sql, array("me_id"=>$me_id));

    $sql = " delete from {$g5['memo_table']}
                where me_id = :me_id
                and (me_recv_mb_id = '{$member['mb_id']}' or me_send_mb_id = '{$member['mb_id']}') ";
    $this->sql_query($sql, array("me_id"=>$me_id));

    if (!$row['me_read_datetime'][0]) { // 메모 받기전이면
      $sql = " update {$g5['member_table']}
                  set mb_memo_call = ''
                  where mb_id = '{$row['me_recv_mb_id']}'
                  and mb_memo_call = '{$row['me_send_mb_id']}' ";
      $this->sql_query($sql);

      $sql = " update `{$g5['member_table']}` set mb_memo_cnt = '".$this->get_memo_not_read($member['mb_id'])."' where mb_id = '{$member['mb_id']}' ";
      $this->sql_query($sql);
    }

    run_event('memo_delete', $me_id, $row);

    return $this->memo();
  }
}