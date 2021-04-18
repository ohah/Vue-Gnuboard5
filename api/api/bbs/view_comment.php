<?php
trait view_comment {
  public function get_cmt_list($bo_table, $wr_id) {
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

    if ($is_guest && $board['bo_comment_level'] < 2) {
      $captcha_html = $this->captcha_html('_comment');
    }
    
    $c_id = isset($_GET['c_id']) ? $this->clean_xss_tags($_GET['c_id'], 1, 1) : '';
    $c_wr_content = '';
    
   
    $list = array();
    
    $is_comment_write = false;
    if ($member['mb_level'] >= $board['bo_comment_level'])
      $is_comment_write = true;
    
    // 코멘트 출력
    //$sql = " select * from {$write_table} where wr_parent = '{$wr_id}' and wr_is_comment = 1 order by wr_comment desc, wr_comment_reply ";
    $sql = " select * from $write_table where wr_parent = ? and wr_is_comment = 1 order by wr_comment, wr_comment_reply ";
    $result = $this->sql_query($sql, [$wr_id]);
    for ($i=0; $i<count($result); $i++) {
      $row = $result[$i];
      $list[$i] = $row;
  
  
      $tmp_name = $this->get_text($this->cut_str($row['wr_name'], $config['cf_cut_name'])); // 설정된 자리수 만큼만 이름 출력
      if ($board['bo_use_sideview'])
        $list[$i]['name'] = $this->get_sideview($row['mb_id'], $tmp_name, $row['wr_email'], $row['wr_homepage']);
      else
        $list[$i]['name'] = '<span class="'.($row['mb_id']?'member':'guest').'">'.$tmp_name.'</span>';
  
  
  
      // 공백없이 연속 입력한 문자 자르기 (way 보드 참고. way.co.kr)
      //$list[$i]['content'] = eregi_replace("[^ \n<>]{130}", "\\0\n", $row['wr_content']);
      $list[$i]['content'] = $list[$i]['content1']= '비밀글 입니다.';
      if (!strstr($row['wr_option'], 'secret') ||
        $is_admin ||
        ($write['mb_id']===$member['mb_id'] && $member['mb_id']) ||
        ($row['mb_id']===$member['mb_id'] && $member['mb_id'])) {
        $list[$i]['content1'] = $row['wr_content'];
        $list[$i]['content'] = $this->conv_content($row['wr_content'], 0, 'wr_content');
        $list[$i]['content'] = $this->search_font($stx, $list[$i]['content']);
      } else {  
        if(!$this->get_session($ss_name)) {
          $list[$i]['content'] = '댓글내용 확인';
          $list[$i]['url'] = API_URL.'/password/'.$bo_table.'/sc/'.$list[$i]['wr_id'];
          unset($list[$i]['wr_content']);
        } else {
          $list[$i]['content'] = $this->conv_content($row['wr_content'], 0, 'wr_content');
          $list[$i]['content'] = $this->search_font($stx, $list[$i]['content']);
        }
      }
  
      $list[$i]['datetime'] = substr($row['wr_datetime'],2,14);
  
      // 관리자가 아니라면 중간 IP 주소를 감춘후 보여줍니다.
      $list[$i]['ip'] = $row['wr_ip'];
      if (!$is_admin)
        $list[$i]['ip'] = preg_replace("/([0-9]+).([0-9]+).([0-9]+).([0-9]+)/", G5_IP_DISPLAY, $row['wr_ip']);
  
      $list[$i]['is_reply'] = false;
      $list[$i]['is_edit'] = false;
      $list[$i]['is_del']  = false;
      if ($is_comment_write || $is_admin) {
        $token = '';

        if ($member['mb_id']) {
          if ($row['mb_id'] === $member['mb_id'] || $is_admin) {
            $this->set_session('ss_delete_comment_'.$row['wr_id'].'_token', $token = uniqid(time()));
            $list[$i]['is_permission']  = true;
            $list[$i]['token'] = $token;
            $list[$i]['is_edit']   = true;
            $list[$i]['is_del']    = true;
          }
        } else {
          if (!$row['mb_id']) {
            $list[$i]['is_permission'] = false;
            $this->set_session('ss_delete_comment_'.$row['wr_id'].'_token', $token = uniqid(time()));
            $list[$i]['token'] = $token;
            $list[$i]['is_del']   = true;
          }
        }

        if (strlen($row['wr_comment_reply']) < 5)
          $list[$i]['is_reply'] = true;
      }
  
      // 05.05.22
      // 답변있는 코멘트는 수정, 삭제 불가
      if ($i > 0 && !$is_admin) {
        if ($row['wr_comment_reply']) {
          $tmp_comment_reply = substr($row['wr_comment_reply'], 0, strlen($row['wr_comment_reply']) - 1);
          if ($tmp_comment_reply == $list[$i-1]['wr_comment_reply']) {
            $list[$i-1]['is_edit'] = false;
            $list[$i-1]['is_del'] = false;
          }
        }
      }
    }
    
    //  코멘트수 제한 설정값
    if ($is_admin) {
      $comment_min = $comment_max = 0;
    } else {
      $comment_min = (int)$board['bo_comment_min'];
      $comment_max = (int)$board['bo_comment_max'];
    }

    for ($i=0; $i<count($list); $i++) {
      $list[$i]['comment_id'] = $list[$i]['wr_id'];
      $list[$i]['cmt_depth'] = strlen($list[$i]['wr_comment_reply']) * 50;
      $list[$i]['comment'] = $list[$i]['content'];
      $list[$i]['comment'] = preg_replace("/\[\<a\s.*href\=\"(http|https|ftp|mms)\:\/\/([^[:space:]]+)\.(mp3|wma|wmv|asf|asx|mpg|mpeg)\".*\<\/a\>\]/i", "<script>doc_write(obj_movie('$1://$2.$3'));</script>", $comment);
      $list[$i]['cmt_sv'] = $cmt_amt - $i + 1; // 댓글 헤더 z-index 재설정 ie8 이하 사이드뷰 겹침 문제 해결
      $list[$i]['c_reply_href'] = $comment_common_url.'&c_id='.$comment_id.'&w=c#bo_vc_w';
      $list[$i]['c_edit_href'] = $comment_common_url.'&c_id='.$comment_id.'&w=cu#bo_vc_w';
      $list[$i]['is_comment_reply_edit'] = ($list[$i]['is_reply'] || $list[$i]['is_edit'] || $list[$i]['is_del']) ? 1 : 0;
    }

    $result = array();
    $result['cmt_amt'] = count($list);
    $result['is_comment_write'] = $is_comment_write;
    $result['comment_min'] = $comment_min;
    $result['comment_max'] = $comment_max;
    $result['captcha_html'] = $captcha_html;
    $result['board'] = $board;
    $result['comment_common_url'] = $this->short_url_clean(G5_BBS_URL.'/board.php?'.$this->clean_query_string($_SERVER['QUERY_STRING']));
    $result['list'] = $this->unset_data($list);
    return $this->data_encode($result, JSON_UNESCAPED_UNICODE);
  }
}