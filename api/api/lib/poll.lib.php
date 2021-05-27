<?php
trait polllib {
  public function poll($po_id=false) {
    global $g5;
    $config = $this->config;
    $is_admin = $this->is_admin;
    $member = $this->member;
    // 투표번호가 넘어오지 않았다면 가장 큰(최근에 등록한) 투표번호를 얻는다
    if (!$po_id) {
      $row = $this->sql_fetch(" select MAX(po_id) as max_po_id from {$g5['poll_table']} ");
      $po_id = $row['max_po_id'];
    }

    if(!$po_id)
      return;

    $po = $this->sql_fetch(" select * from {$g5['poll_table']} where po_id = '$po_id' ");

    $result = array();
    $result['po'] = $po;

    return $this->data_encode($result);
  }
}