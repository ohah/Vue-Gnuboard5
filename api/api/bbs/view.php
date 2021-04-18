<?php
trait view {
  public function get_views($bo_table, $wr_id) {
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
      if($value) $qstr .= $key.'='.$value;
    }
    // 게시판에서 두단어 이상 검색 후 검색된 게시물에 코멘트를 남기면 나오던 오류 수정
    $sop = strtolower($sop);
    if ($sop != 'and' && $sop != 'or')
      $sop = 'and';

    $sql_search = "";
    // 검색이면
    if ($sca || $stx || $stx === '0') {
      // where 문을 얻음
      $sql_search = $this->get_sql_search($sca, $sfl, $stx, $sop);
      $search_href = $this->get_pretty_url($bo_table,'','&page='.$page.$qstr);
      $list_href = $this->get_pretty_url($bo_table);
    } else {
      $search_href = '';
      $list_href = $this->get_pretty_url($bo_table,'',$qstr);
    }

    if (!$board['bo_use_list_view']) {
      if ($sql_search)
        $sql_search = " and " . $sql_search;
  
      // 윗글을 얻음
      $sql = " select wr_id, wr_subject, wr_datetime from {$write_table} where wr_is_comment = 0 and wr_num = '{$write['wr_num']}' and wr_reply < '{$write['wr_reply']}' {$sql_search} order by wr_num desc, wr_reply desc limit 1 ";
      $prev = $this->sql_fetch($sql);
      // 위의 쿼리문으로 값을 얻지 못했다면
      if (! (isset($prev['wr_id']) && $prev['wr_id'])) {
        $sql = " select wr_id, wr_subject, wr_datetime from {$write_table} where wr_is_comment = 0 and wr_num < '{$write['wr_num']}' {$sql_search} order by wr_num desc, wr_reply desc limit 1 ";
        $prev = $this->sql_fetch($sql);
      }
  
      // 아래글을 얻음
      $sql = " select wr_id, wr_subject, wr_datetime from {$write_table} where wr_is_comment = 0 and wr_num = '{$write['wr_num']}' and wr_reply > '{$write['wr_reply']}' {$sql_search} order by wr_num, wr_reply limit 1 ";
      $next = $this->sql_fetch($sql);
      // 위의 쿼리문으로 값을 얻지 못했다면
      if (! (isset($next['wr_id']) && $next['wr_id'])) {
        $sql = " select wr_id, wr_subject, wr_datetime from {$write_table} where wr_is_comment = 0 and wr_num > '{$write['wr_num']}' {$sql_search} order by wr_num, wr_reply limit 1 ";
        $next = $this->sql_fetch($sql);
      }
    }

    // 이전글 링크
    $prev_href = '';
    if (isset($prev['wr_id']) && $prev['wr_id']) {
      $prev_wr_subject = $this->get_text($this->cut_str($prev['wr_subject'], 255));
      $prev_href = $this->get_pretty_url($bo_table, $prev['wr_id'], $qstr);
      $prev_wr_date = $prev['wr_datetime'];
    }

    // 다음글 링크
    $next_href = '';
    if (isset($next['wr_id']) && $next['wr_id']) {
      $next_wr_subject = $this->get_text($this->cut_str($next['wr_subject'], 255));
      $next_href = $this->get_pretty_url($bo_table, $next['wr_id'], $qstr);
      $next_wr_date = $next['wr_datetime'];
    }

    // 쓰기 링크
    $write_href = '';
    if ($member['mb_level'] >= $board['bo_write_level']) {
      $write_href = $this->short_url_clean(G5_BBS_URL.'/write.php?bo_table='.$bo_table);
    }

    // 답변 링크
    $reply_href = '';
    if ($member['mb_level'] >= $board['bo_reply_level']) {
      $reply_href = $this->short_url_clean(G5_BBS_URL.'/write.php?w=r&bo_table='.$bo_table.'&wr_id='.$wr_id.$qstr);
    }

    // 수정, 삭제 링크
    $update_href = $delete_href = '';
    // 로그인중이고 자신의 글이라면 또는 관리자라면 비밀번호를 묻지 않고 바로 수정, 삭제 가능
    if (($member['mb_id'] && ($member['mb_id'] === $write['mb_id'])) || $is_admin) {
      $update_href = $this->short_url_clean(G5_BBS_URL.'/write.php?w=u&bo_table='.$bo_table.'&wr_id='.$wr_id.'&page='.$page.$qstr);
      $this->set_session('ss_delete_token', $token = uniqid(time()));
      $delete_href = 'bo_table='.$bo_table.'&wr_id='.$wr_id.'&token='.$token.'&page='.$page.urldecode($qstr);
    }
    else if (!$write['mb_id']) { // 회원이 쓴 글이 아니라면
      $update_href = G5_BBS_URL.'/write?w=u&bo_table='.$bo_table.'&wr_id='.$wr_id.'&page='.$page.$qstr;
      $delete_href = 'w=d&bo_table='.$bo_table.'&wr_id='.$wr_id.'&page='.$page.$qstr;
    }

    // 최고, 그룹관리자라면 글 복사, 이동 가능
    $copy_href = $move_href = '';
    if ($write['wr_reply'] == '' && ($is_admin == 'super' || $is_admin == 'group')) {
      $copy_href = G5_BBS_URL.'/move.php?sw=copy&bo_table='.$bo_table.'&wr_id='.$wr_id.'&page='.$page.$qstr;
      $move_href = G5_BBS_URL.'/move.php?sw=move&bo_table='.$bo_table.'&wr_id='.$wr_id.'&page='.$page.$qstr;
    }

    $scrap_href = '';
    $good_href = '';
    $nogood_href = '';
    if ($is_member) {
      // 스크랩 링크
      $scrap_href = G5_BBS_URL.'/scrap_popin.php?bo_table='.$bo_table.'&wr_id='.$wr_id;

      // 추천 링크
      if ($board['bo_use_good'])
        $good_href = G5_BBS_URL.'/good.php?bo_table='.$bo_table.'&wr_id='.$wr_id.'&good=good';

      // 비추천 링크
      if ($board['bo_use_nogood'])
        $nogood_href = G5_BBS_URL.'/good.php?bo_table='.$bo_table.'&wr_id='.$wr_id.'&good=nogood';
    }

    $view = $this->get_view($write, $board, $board_skin_path);

    if (strstr($sfl, 'subject'))
      $view['subject'] = $this->search_font($stx, $view['subject']);

    $html = 0;
    if (strstr($view['wr_option'], 'html1'))
      $html = 1;
    else if (strstr($view['wr_option'], 'html2'))
      $html = 2;

    $view['content'] = $this->conv_content($view['wr_content'], $html);
    if (strstr($sfl, 'content'))
      $view['content'] = $this->search_font($stx, $view['content']);

    //$view['rich_content'] = preg_replace("/{이미지\:([0-9]+)[:]?([^}]*)}/ie", "view_image(\$view, '\\1', '\\2')", $view['content']);
    function conv_rich_content($matches) {
      global $view;
      return $this->view_image($view, $matches[1], $matches[2]);
    }
    $view['rich_content'] = preg_replace_callback("/{이미지\:([0-9]+)[:]?([^}]*)}/i", "conv_rich_content", $view['content']);

    $is_signature = false;
    $signature = '';
    if ($board['bo_use_signature'] && $view['mb_id']) {
      $is_signature = true;
      $mb = $this->get_member($view['mb_id']);
      $signature = $mb['mb_signature'];

      $signature = $this->conv_content($signature, 1);
    }

    $result = array();
    $g5['board_title'] = (($this->is_mobile() && $board['bo_mobile_subject']) ? $board['bo_mobile_subject'] : $board['bo_subject']).$pagetitle;
    $result['title'] = strip_tags($this->conv_subject($write['wr_subject'], 255))." > ".$g5['board_title'];
    // 답변 링크    
    $result['list_href'] = $list_href;
    $result['reply_href'] = $reply_href;
    $result['update_href'] = $update_href;
    $result['delete_href'] = $delete_href;
    $result['copy_href'] = $copy_href;
    $result['move_href'] = $move_href;
    $result['good_href'] = $good_href;
    $result['nogood_href'] = $nogood_href;
    $result['scrap_href'] = $scrap_href;
    $result['prev_wr_subject'] = $prev_wr_subject;
    $result['prev_href'] = $prev_href;
    $result['prev_wr_date'] = $prev_wr_date;
    $result['next_wr_subject'] = $next_wr_subject;
    $result['next_href'] = $next_href;
    $result['next_wr_date'] = $next_wr_date;
    $result['next_href'] = $next_href;
    $result['write_href'] = $write_href;
    $result['board'] = $board;
    $result['good_href'] = $good_href;
    $result['nogood_href'] = $nogood_href;
    $result['is_signature'] = $is_signature;
    $result['signature'] = $signature;
    $result['view'] = $this->unset_data($view);
    return $this->data_encode($result);
  }
}