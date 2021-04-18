<?php
trait point {
  public function point() {
    global $g5;
    $member = $this->member;
    $config = $this->config;
    $page = $this->qstr['page'];
    $list = array();
    $sql_common = " from {$g5['point_table']} where mb_id = '{$member['mb_id']}' ";
    $sql_order = " order by po_id desc ";
    
    $sql = " select count(*) as cnt {$sql_common} ";
    $row = $this->sql_fetch($sql);
    $total_count = $row['cnt'];
    
    $rows = $config['cf_page_rows'];
    $total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
    if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
    $from_record = ($page - 1) * $rows; // 시작 열을 구함 


    $sum_point1 = $sum_point2 = $sum_point3 = 0;

    $sql = " select *
                {$sql_common}
                {$sql_order}
                limit {$from_record}, {$rows} ";
    $result = $this->sql_query($sql);
    for ($i=0; $i<count($result); $i++) {
      $row = $result[$i];
      $point1 = $point2 = 0;
      $point_use_class = '';
      if ($row['po_point'] > 0) {
        $point1 = '+' .number_format($row['po_point']);
        $sum_point1 += $row['po_point'];
      } else {
        $point2 = number_format($row['po_point']);
        $sum_point2 += $row['po_point'];
        $point_use_class = 'point_use';
      }
      $row['point'] = $point1;
      $row['sum_point'] = $sum_point1;

      $po_content = $row['po_content'];

      $expr = '';
      if($row['po_expired'] == 1)
        $expr = ' txt_expired';
      $list[] = $row;
    }

    $result = array();
    $result['list'] = $list;
    $result['total_count'] = $total_count;
    $result['page'] = $page;
    $result['page_rows'] = $rows;
    return $this->data_encode($result);
  }
}