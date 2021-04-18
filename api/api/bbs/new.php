<?php
trait bbs_new {
  public function bbs_new ($mb_id) {
    global $g5;
    $config = $this->config;
    parse_str(parse_url($_SERVER["REQUEST_URI"],PHP_URL_QUERY), $query); // GET
    $sql_common = " from {$g5['board_new_table']} a, {$g5['board_table']} b, {$g5['group_table']} c where a.bo_table = b.bo_table and b.gr_id = c.gr_id and b.bo_use_search = 1 ";

    $gr_id = isset($query['gr_id']) ? substr(preg_replace('#[^a-z0-9_]#i', '', $query['gr_id']), 0, 10) : '';
    if ($gr_id) {
      $sql_common .= " and b.gr_id = '$gr_id' ";
    }
    $view = isset($query['view']) ? $query['view'] : "";

    if ($view == "w")
      $sql_common .= " and a.wr_id = a.wr_parent ";
    else if ($view == "c")
      $sql_common .= " and a.wr_id <> a.wr_parent ";
    else
      $view = '';

    $mb_id = substr(preg_replace('#[^a-z0-9_]#i', '', $mb_id), 0, 20);

    if ($mb_id) {
      $sql_common .= " and a.mb_id = '{$mb_id}' ";
    }
    $sql_order = " order by a.bn_id desc ";

    $sql = " select count(*) as cnt {$sql_common} ";
    $row = $this->sql_fetch($sql);
    $total_count = $row['cnt'];

    $rows = $this->is_mobile() ? $config['cf_mobile_page_rows'] : $config['cf_new_rows'];
    $total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
    if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
    $from_record = ($page - 1) * $rows; // 시작 열을 구함

    $list = array();
    $sql = " select a.*, b.bo_subject, b.bo_mobile_subject, c.gr_subject, c.gr_id {$sql_common} {$sql_order} limit {$from_record}, {$rows} ";
    $result = $this->sql_query($sql);
    for ($i=0; $i<count($result); $i++) {
      $row = $result[$i];
      $tmp_write_table = $g5['write_prefix'].$row['bo_table'];
      if ($row['wr_id'] == $row['wr_parent']) {
        // 원글
        $comment = "";
        $comment_link = "";
        $row2 = $this->sql_fetch(" select * from {$tmp_write_table} where wr_id = '{$row['wr_id']}' ");
        $list[$i] = $row2;

        $name = $this->get_sideview($row2['mb_id'], $this->get_text($this->cut_str($row2['wr_name'], $config['cf_cut_name'])), $row2['wr_email'], $row2['wr_homepage']);
        // 당일인 경우 시간으로 표시함
        $datetime = substr($row2['wr_datetime'],0,10);
        $datetime2 = $row2['wr_datetime'];
        if ($datetime == G5_TIME_YMD) {
          $datetime2 = substr($datetime2,11,5);
        } else {
          $datetime2 = substr($datetime2,5,5);
        }
      } else {
        // 코멘트
        $comment = '[코] ';
        $comment_link = '#c_'.$row['wr_id'];
        $row2 = $this->sql_fetch(" select * from {$tmp_write_table} where wr_id = '{$row['wr_parent']}' ");
        $row3 = $this->sql_fetch(" select mb_id, wr_name, wr_email, wr_homepage, wr_datetime from {$tmp_write_table} where wr_id = '{$row['wr_id']}' ");
        $list[$i] = $row2;
        $list[$i]['wr_id'] = $row['wr_id'];
        $list[$i]['mb_id'] = $row3['mb_id'];
        $list[$i]['wr_name'] = $row3['wr_name'];
        $list[$i]['wr_email'] = $row3['wr_email'];
        $list[$i]['wr_homepage'] = $row3['wr_homepage'];

        $name = $this->get_sideview($row3['mb_id'], $this->get_text($this->cut_str($row3['wr_name'], $config['cf_cut_name'])), $row3['wr_email'], $row3['wr_homepage']);
        // 당일인 경우 시간으로 표시함
        $datetime = substr($row3['wr_datetime'],0,10);
        $datetime2 = $row3['wr_datetime'];
        if ($datetime == G5_TIME_YMD) {
          $datetime2 = substr($datetime2,11,5);
        } else {
          $datetime2 = substr($datetime2,5,5);
        }
      }

      $list[$i]['gr_id'] = $row['gr_id'];
      $list[$i]['bo_table'] = $row['bo_table'];
      $list[$i]['name'] = $name;
      $list[$i]['comment'] = $comment;
      $list[$i]['href'] = $this->get_pretty_url($row['bo_table'], $row2['wr_id'], $comment_link);
      $list[$i]['datetime'] = $datetime;
      $list[$i]['datetime2'] = $datetime2;

      $list[$i]['gr_subject'] = $row['gr_subject'];
      $list[$i]['bo_subject'] = (($this->is_mobile() && $row['bo_mobile_subject']) ? $row['bo_mobile_subject'] : $row['bo_subject']);
      $list[$i]['wr_subject'] = $row2['wr_subject'];
      unset($list[$i]['wr_content']);
      unset($list[$i]['wr_password']);
      unset($list[$i]['wr_ip']);
    }

    $write_pages = $this->get_paging($this->is_mobile() ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "?gr_id=$gr_id&amp;view=$view&amp;mb_id=$mb_id&amp;page=");

    $k = 1;
    $group_select = array();
    $group_select[0]['name'] = '전체그룹';
    $group_select[0]['value'] = '';
    $sql = " select gr_id, gr_subject from {$g5['group_table']} order by gr_id ";
    $result = $this->sql_query($sql);
    for ($i=0; $i<count($result); $i++) {
      $row = $result[$i];
      $group_select[$k]['name'] = $row['gr_subject'];
      $group_select[$k]['value'] = $row['gr_id'];
      $group_select[$k]['selected'] = $this->get_selected($gr_id, $row['gr_id']);
      $k++;
    }

    $result = array();
    $result['title'] = '새글';
    $result['group_select'] = $group_select;
    $result['list'] = $list;
    $result['write_pages'] = $write_pages;
    $result['page_rows'] = $rows;
    $result['page'] = $page ? $page : 1;
    $result['total_count'] = $total_count;
    return $this->data_encode($result);
  }
}