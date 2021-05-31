<?php
trait scrap {
  public function scrap() {
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
    $qstr = '';
    foreach ($this->qstr as $key => $value) {
      $qstr .= $key.'='.$value;
    }

    $sql_common = " from {$g5['scrap_table']} where mb_id = '{$member['mb_id']}' ";
    $sql_order = " order by ms_id desc ";

    $sql = " select count(*) as cnt $sql_common ";
    $row = $this->sql_fetch($sql);
    $total_count = $row['cnt'];

    $rows = $config['cf_page_rows'];
    $total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
    if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
    $from_record = ($page - 1) * $rows; // 시작 열을 구함


    $list = array();

    $sql = " select *
                $sql_common
                $sql_order
                limit $from_record, $rows ";
    $result = $this->sql_query($sql);
    for ($i=0; $i<count($result); $i++) {
      $row = $result[$i];
      $list[$i] = $row;

      // 순차적인 번호 (순번)
      $num = $total_count - ($page - 1) * $rows - $i;

      // 게시판 제목
      $sql2 = " select bo_subject from {$g5['board_table']} where bo_table = '{$row['bo_table']}'";
      $row2 = $this->sql_fetch($sql2);
      if (!$row2['bo_subject']) $row2['bo_subject'] = '[게시판 없음]';

      // 게시물 제목
      $tmp_write_table = $g5['write_prefix'] . $row['bo_table'];
      $sql3 = " select wr_subject from $tmp_write_table where wr_id = '{$row['wr_id']}'";
      $row3 = $this->sql_fetch($sql3);
      $subject = $this->get_text($this->cut_str($row3['wr_subject'], 100));
      if (!$row3['wr_subject'])
        $row3['wr_subject'] = '[글 없음]';

      $list[$i]['num'] = $num;
      $list[$i]['opener_href'] = $this->get_pretty_url($row['bo_table']);
      $list[$i]['opener_href_wr_id'] = $this->get_pretty_url($row['bo_table'], $row['wr_id']);
      $list[$i]['bo_subject'] = $row2['bo_subject'];
      $list[$i]['subject'] = $subject;
      $list[$i]['del_href'] = './scrap_delete.php?ms_id='.$row['ms_id'].'&amp;page='.$page;
    }
    $result = array();
    $result['list'] = $list;
    $result['total_count'] = $total_count;
    $result['page'] = $page;
    $result['page_rows'] = $rows;
    return $this->data_encode($result);
  }
  /**
	 * scrap_popin_update
	 * @param   string      $bo_table
	 * @param   string      $wr_id
	 * @param   textarea    $_['POST']['wr_content']
	 * @return  array       $data
	*/
  public function scrap_popin_update($bo_table, $wr_id) {
    global $g5;
    $sca = $this->$sca;
    $sfl = $this->$sfl;
    $stx = $this->$stx;
    $sst = $this->$sst;
    $sod = $this->$sod;
    $spt = $this->$spt;
    $page = $this->$page;
    $write_table = $g5['write_prefix'].$bo_table;
    $write = $this->get_write($write_table, $wr_id);
    $member = $this->member;
    $config = $this->config;
    $board = $this->get_board_db($bo_table);
    $is_admin = $this->is_admin;
    $is_guest = $this->$is_guest;
    $is_member = $this->is_member;
    $qstr = '';
    foreach ($this->qstr as $key => $value) {
      $qstr .= $key.'='.$value;
    }

    if(!$is_member) {
      $this->alert('회원만 접근 가능합니다');
    }
    
    // 게시글 존재하는지
    if(!$write['wr_id']) {
      $this->alert('스크랩하시려는 게시글이 존재하지 않습니다.');
    }

    $sql = " select count(*) as cnt from {$g5['scrap_table']}
    where mb_id = '{$member['mb_id']}'
    and bo_table = '{$bo_table}'
    and wr_id = '{$wr_id}'";
    $row = $this->sql_fetch($sql);
    if ($row['cnt']) {
      $this->alert('이미 스크랩하신 글입니다.\r\n 지금 스크랩을 확인하시겠습니까?');
    }


    $wr_content = isset($_POST['wr_content']) ? trim($_POST['wr_content']) : '';

    // 덧글이 넘어오고 코멘트를 쓸 권한이 있다면
    if ($wr_content && ($member['mb_level'] >= $board['bo_comment_level'])) {
      $wr = $this->get_write($write_table, $wr_id);
      // 원글이 존재한다면
      if ($wr['wr_id']) {
        // 세션의 시간 검사
        // 4.00.15 - 댓글 수정시 연속 게시물 등록 메시지로 인한 오류 수정
        if ($w == 'c' && $_SESSION['ss_datetime'] >= (G5_SERVER_TIME - $config['cf_delay_sec']) && !$is_admin)
          $this->alert('너무 빠른 시간내에 게시물을 연속해서 올릴 수 없습니다.');
        
        $this->set_session('ss_datetime', G5_SERVER_TIME);

        $mb_id = $member['mb_id'];
        $wr_name = addslashes($this->clean_xss_tags($board['bo_use_name'] ? $member['mb_name'] : $member['mb_nick']));
        $wr_password = $member['mb_password'];
        $wr_email = addslashes($member['mb_email']);
        $wr_homepage = addslashes($this->clean_xss_tags($member['mb_homepage']));

        $sql = " select max(wr_comment) as max_comment from $write_table
                    where wr_parent = '{$wr_id}' and wr_is_comment = '1' ";
        $row = $this->sql_fetch($sql);
        $row['max_comment'] += 1;

        $sql = " insert into $write_table
                    set ca_name = '{$wr['ca_name']}',
                         wr_option = '',
                         wr_num = '{$wr['wr_num']}',
                         wr_reply = '',
                         wr_parent = '$wr_id',
                         wr_is_comment = '1',
                         wr_comment = '{$row['max_comment']}',
                         wr_content = '$wr_content',
                         mb_id = '$mb_id',
                         wr_password = '$wr_password',
                         wr_name = '$wr_name',
                         wr_email = '$wr_email',
                         wr_homepage = '$wr_homepage',
                         wr_datetime = '".G5_TIME_YMDHIS."',
                         wr_ip = '{$_SERVER['REMOTE_ADDR']}' ";
        $this->pdo_query($sql,
        array(
          "ca_name" => $wr['ca_name'],
          "wr_num" => $wr['wr_num'],
          "wr_parent" => $wr_id,
          "wr_comment" => $row['max_comment'],
          "wr_content" => $wr_content,
          "mb_id" => $mb_id,
          "wr_password" => $wr_password,
          "wr_name" => $wr_name,
          "wr_email" => $wr_email,
          "wr_homepage" => $wr_homepage
        ));
        


        $comment_id = $this->db->lastInsertId();

        // 원글에 코멘트수 증가
        $this->pdo_query(" update $write_table set wr_comment = wr_comment + 1 where wr_id = :wr_id ", array("wr_id"=>$wr_id));

        // 새글 INSERT
        $this->pdo_query(" insert into {$g5['board_new_table']} ( bo_table, wr_id, wr_parent, bn_datetime, mb_id ) values ( :bo_table, '$comment_id', :wr_id, '".G5_TIME_YMDHIS."', '{$member['mb_id']}' ) ", array("bo_table"=>$bo_table, "wr_id"=>$wr_id));

        // 코멘트 1 증가
        $this->pdo_query(" update {$g5['board_table']}  set bo_count_comment = bo_count_comment + 1 where bo_table = :bo_table ", array("bo_table"=>$bo_table));

        // 포인트 부여
        $this->insert_point($member['mb_id'], $board['bo_comment_point'], "{$board['bo_subject']} {$wr_id}-{$comment_id} 댓글쓰기(스크랩)", $bo_table, $comment_id, '댓글');
      }
    }

    $sql = " insert into {$g5['scrap_table']} ( mb_id, bo_table, wr_id, ms_datetime ) values ( '{$member['mb_id']}', :bo_table, :wr_id, '".G5_TIME_YMDHIS."' ) ";
    $this->pdo_query($sql, array("bo_table"=>$bo_table, "wr_id"=>$wr_id));

    $sql = " update `{$g5['member_table']}` set mb_scrap_cnt = '".$this->get_scrap_totals($member['mb_id'])."' where mb_id = '{$member['mb_id']}' ";
    $this->sql_query($sql);

    $this->delete_cache_latest($bo_table);
    $result = array('msg' => '이 글을 스크랩하였습니다');
    return $this->data_encode($result);
  }

  public function scrap_delete($ms_id) {
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

    $sql = " delete from {$g5['scrap_table']} where mb_id = '{$member['mb_id']}' and ms_id = :ms_id";
    $this->pdo_query($sql, array("ms_id"=>$ms_id));

    $sql = " update `{$g5['member_table']}` set mb_scrap_cnt = '".$this->get_scrap_totals($member['mb_id'])."' where mb_id = '{$member['mb_id']}' ";
    $this->sql_query($sql);
    return $this->scrap();
  }
}