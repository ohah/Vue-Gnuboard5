<?php
// 인기검색어 출력
// $skin_dir : 스킨 디렉토리
// $pop_cnt : 검색어 몇개
// $date_cnt : 몇일 동안
trait pupularlib {
  public function popular($pop_cnt=7, $date_cnt=3) {
    $config = $this->config;
    global $g5;

    $date_gap = date("Y-m-d", G5_SERVER_TIME - ($date_cnt * 86400));
    $sql = " select pp_word, count(*) as cnt from {$g5['popular_table']} where pp_date between :date_gap and :G5_TIME_YMD group by pp_word order by cnt desc, pp_word limit 0, $pop_cnt ";
    $list = $this->pdo_query($sql, array("date_gap" => $date_gap, "G5_TIME_YMD"=>G5_TIME_YMD));

    return $list;
  }
}