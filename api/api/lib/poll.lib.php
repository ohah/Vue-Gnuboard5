<?php
trait polllib {
  public function poll($po_id=false) {
    global $g5;
    $config = $this->config;
    $is_admin = $this->is_admin;
    $member = $this->member;
    // 투표번호가 넘어오지 않았다면 가장 큰(최근에 등록한) 투표번호를 얻는다
    if (!$po_id) {
      $row = $this->pdo_fetch(" select MAX(po_id) as max_po_id from {$g5['poll_table']} ");
      $po_id = $row['max_po_id'];
    }

    if(!$po_id)
      return;

    $po = $this->pdo_fetch(" select * from {$g5['poll_table']} where po_id = :po_id", array("po_id"=>$po_id));

    $result = array();
    $result['po'] = $po;

    return $this->data_encode($result);
  }
  public function poll_update() {
    global $g5;
    extract($_POST);
    $config = $this->config;
    $is_admin = $this->is_admin;
    $member = $this->member;

    $po_id = isset($_POST['po_id']) ? preg_replace('/[^0-9]/', '', $_POST['po_id']) : 0;

    $po = $this->pdo_fetch(" select * from {$g5['poll_table']} where po_id = :po_id ", array("po_id"=>$_POST['po_id']));
    if (! (isset($po['po_id']) && $po['po_id']))
      $this->alert('po_id 값이 제대로 넘어오지 않았습니다.');

    if ($member['mb_level'] < $po['po_level'])
      $this->alert('권한 '.$po['po_level'].' 이상 회원만 투표에 참여하실 수 있습니다.');

    $gb_poll = isset($_POST['gb_poll']) ? preg_replace('/[^0-9]/', '', $_POST['gb_poll']) : 0;
    if(!$gb_poll)
      $this->alert('항목을 선택하세요.');
    $search_mb_id = false;
    $search_ip = false;

    if($is_member) {
      // 투표했던 회원아이디들 중에서 찾아본다
      $ids = explode(',', trim($po['mb_ids']));
      for ($i=0; $i<count($ids); $i++) {
        if ($member['mb_id'] == trim($ids[$i])) {
          $search_mb_id = true;
          break;
        }
      }
    } else {
      // 투표했던 ip들 중에서 찾아본다
      $ips = explode(',', trim($po['po_ips']));
      for ($i=0; $i<count($ips); $i++) {
        if ($_SERVER['REMOTE_ADDR'] == trim($ips[$i])) {
          $search_ip = true;
          break;
        }
      }
    }

    $post_skin_dir = isset($_POST['skin_dir']) ? clean_xss_tags($_POST['skin_dir'], 1, 1) : '';
    $result_url = G5_BBS_URL."/poll_result.php?po_id=$po_id&skin_dir={$post_skin_dir}";

    // 없다면 선택한 투표항목을 1증가 시키고 ip, id를 저장
    if (!($search_ip || $search_mb_id)) {
      $po_ips = $po['po_ips'] . $_SERVER['REMOTE_ADDR'].",";
      $mb_ids = $po['mb_ids'];
      if ($is_member) { // 회원일 때는 id만 추가
        $mb_ids .= $member['mb_id'].',';
        $sql = " update {$g5['poll_table']} set po_cnt{$gb_poll} = po_cnt{$gb_poll} + 1, mb_ids = '$mb_ids' where po_id = :po_id";  
      } else {
        $sql = " update {$g5['poll_table']} set po_cnt{$gb_poll} = po_cnt{$gb_poll} + 1, po_ips = '$po_ips' where po_id = :po_id";
      }
      $this->pdo_query($sql, array("po_id"=>$po_id));
    } else {
      $this->alert(addcslashes($po['po_subject'], '"\\/').'에 이미 참여하셨습니다.');
    }
    if (!$search_mb_id)
      $this->insert_point($member['mb_id'], $po['po_point'], $po['po_id'] . '. ' . $this->cut_str($po['po_subject'],20) . ' 투표 참여 ', '@poll', $po['po_id'], '투표');
 
    return $this->poll($po_id);
  }

  public function poll_result() {
    global $g5;
    $config = $this->config;
    $is_admin = $this->is_admin;
    $member = $this->member;
    $_GET = $_GET['po_id'] ? $_GET : $this->REQUEST_URI();
    $po_id = $_GET['po_id'] ? (int) $_GET['po_id'] : '';

    $po = $this->pdo_fetch(" select * from {$g5['poll_table']} where po_id = :po_id ", array("po_id"=>$po_id));
    if (!$po['po_id'])
      $this->alert('설문조사 정보가 없습니다.');
    
    if ($member['mb_level'] < $po['po_level'])
      $this->alert('권한 '.$po['po_level'].' 이상의 회원만 결과를 보실 수 있습니다.');
    
    $g5['title'] = '설문조사 결과';
    
    $po_subject = $po['po_subject'];
    
    $max = 1;
    $total_po_cnt = 0;
    $poll_max_count = 9;
    
    for ($i=1; $i<=$poll_max_count; $i++) {
      $poll = $po['po_poll'.$i];
      if (! $poll) break;
  
      $count = $po['po_cnt'.$i];
      $total_po_cnt += $count;
  
      if ($count > $max)
        $max = $count;
    }
    $nf_total_po_cnt = number_format($total_po_cnt);
    
    $list = array();
    
    for ($i=1; $i<=$poll_max_count; $i++) {
      $poll = $po['po_poll'.$i];
      if (!$poll) { break; }
  
      $list[$i]['content'] = $poll;
      $list[$i]['cnt'] = $po['po_cnt'.$i];
      $list[$i]['rate'] = 0;
  
      if ($total_po_cnt > 0)
        $list[$i]['rate'] = ($list[$i]['cnt'] / $total_po_cnt) * 100;
  
      $bar = (int)($list[$i]['cnt'] / $max * 100);
  
      $list[$i]['bar'] = $bar;
      $list[$i]['num'] = $i;
    }
    
    $list2 = array();
    
    // 기타의견 리스트
    $sql = "select a.*, b.mb_open
            from {$g5['poll_etc_table']} a
            left join {$g5['member_table']} b on (a.mb_id = b.mb_id)
            where po_id = :po_id order by pc_id desc ";
    $result = $this->pdo_query($sql,
      array("po_id"=>$po_id)
    );
    for ($i=0; $i<count($result); $i++) {
      $row = $result[$i];
      $list2[$i]['pc_name']  = $this->get_text($row['pc_name']);
      $list2[$i]['name']     = $this->get_sideview($row['mb_id'], $this->get_text($this->cut_str($row['pc_name'],10)), '', '', $row['mb_open']);
      $list2[$i]['idea']     = $this->get_text($this->cut_str($row['pc_idea'], 255));
      $list2[$i]['datetime'] = $row['pc_datetime'];
  
      $list2[$i]['del'] = '';
      if ($is_admin == 'super' || ($row['mb_id'] == $member['mb_id'] && $row['mb_id']))
        $list2[$i]['del'] = $row['pc_id'];
    }
    
    // 기타의견 입력
    $is_etc = false;
    if ($po['po_etc']) {
      $is_etc = true;
      $po_etc = $po['po_etc'];
      if ($member['mb_id'])
        $name = '<b>'.$member['mb_nick'].'</b> <input type="hidden" name="pc_name" value="'.$member['mb_nick'].'">';
      else
        $name = '<input type="text" name="pc_name" size="10" class="input" required>';
    }
    
    $list3 = array();
    
    // 다른투표
    $sql = " select po_id, po_subject, po_date from {$g5['poll_table']} order by po_id desc ";
    $result = $this->sql_query($sql);
    for ($i=0; $i<count($result); $i++) {
      $row2 = $result[$i];
      $list3[$i]['po_id'] = $row2['po_id'];
      $list3[$i]['date'] = substr($row2['po_date'],2,8);
      $list3[$i]['subject'] = $this->cut_str($row2['po_subject'],60,"…");
    }
    $get_max_cnt = 0;

    if ((int) $total_po_cnt > 0){
      foreach( $list as $k => $v ) {
        $get_max_cnt = max( array( $get_max_cnt, $v['cnt'] ) );     // 가장 높은 투표수를 뽑습니다.
      }
    }
    if($is_admin) {
      unset($po['po_ips']);
    }
    $result = array();
    $result['list'] = $list;
    $result['po'] = $po;
    $reulst['po_subject'] = $po_subject;
    $result['get_max_cnt'] = $get_max_cnt;
    $result['list2'] = $list2;
    $result['list3'] = $list3;
    $result['captcha_html'] = $this->captcha_html();
    return $this->data_encode($result);
  }


  public function poll_etc_update() {
    global $g5;
    $config = $this->config;
    $member = $this->member;
    $is_admin = $this->is_member;
    if (!$this->chk_captcha()) {
      $this->alert('자동등록방지 숫자가 틀렸습니다.');
    }
    if ($w == '') {
      $po_id   = isset($_POST['po_id']) ? (int) $_POST['po_id'] : '';
      $pc_name = isset($_POST['pc_name']) ? $this->clean_xss_tags($_POST['pc_name'], 1, 1) : '';
      $pc_idea = isset($_POST['pc_idea']) ? $this->clean_xss_tags($_POST['pc_idea'], 1, 1) : '';

      $po = $this->pdo_fetch(" select * from {$g5['poll_table']} where po_id = :po_id", array("po_id"=>$po_id));
      if (!$po['po_id'])
        $this->alert('po_id 값이 제대로 넘어오지 않았습니다.');

      $tmp_row = $this->pdo_fetch(" select max(pc_id) as max_pc_id from {$g5['poll_etc_table']} ");
      $pc_id = $tmp_row['max_pc_id'] + 1;
      $sql = " insert into {$g5['poll_etc_table']}
              ( pc_id, po_id, mb_id, pc_name, pc_idea, pc_datetime )
              values ( :pc_id, :po_id, '{$member['mb_id']}', :pc_name, :pc_idea, '".G5_TIME_YMDHIS."' ) ";
      $this->sql_query($sql,
        array(
          "pc_id"=>$pc_id,
          "po_id"=>$po_id,
          "pc_name"=>$pc_name,
          "pc_idea"=>$pc_idea,
      ));

      $pc_idea = stripslashes($pc_idea);

      $name = $this->get_text($this->cut_str($pc_name, $config['cf_cut_name']));
      $mb_id = '';
      if ($member['mb_id'])
        $mb_id = '('.$member['mb_id'].')';

      // 환경설정의 투표 기타의견 작성시 최고관리자에게 메일발송 사용에 체크되어 있을 경우
      if ($config['cf_email_po_super_admin'])  {
        // $subject = $po['po_subject'];
        // $content = $pc_idea;

        // ob_start();
        // include_once ('./poll_etc_update_mail.php');
        // $content = ob_get_contents();
        // ob_end_clean();

        // // 관리자에게 보내는 메일
        // $admin = get_admin('super');
        // $from_email = $member['mb_email'] ? $member['mb_email'] : $admin['mb_email'];
        // mailer($name, $from_email, $admin['mb_email'], '['.$config['cf_title'].'] 설문조사 기타의견 메일', $content, 1);
      }
    } else if ($w == 'd') {
      if ($member['mb_id'] || $is_admin == 'super') {
        $sql = " delete from {$g5['poll_etc_table']} where pc_id = :pc_id ";
        if (!$is_admin)
          $sql .= " and mb_id = '{$member['mb_id']}' ";
        $this->sql_query($sql, array("pc_id"=>$pc_id));
      }
    }

    $_GET['po_id'] = $po_id;
    return $this->poll_result();
  }


}