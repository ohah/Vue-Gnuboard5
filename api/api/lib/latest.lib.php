<?php
trait latestlib {
  public function latest($bo_table, $rows=10, $subject_len=40, $cache_time=1, $options='') {
    global $g5;
    
    $time_unit = 3600;  // 1시간으로 고정
    $caches = false;

    if(G5_USE_CACHE) {
      $cache_file_name = "latest-{$bo_table}-{$skin_dir}-{$rows}-{$subject_len}-".g5_cache_secret_key();
      $caches = g5_get_cache($cache_file_name, $time_unit * $cache_time);
      $cache_list = isset($caches['list']) ? $caches['list'] : array();
      g5_latest_cache_data($bo_table, $cache_list);
    }

    if( $caches === false ){
      $list = array();

      $board = $this->get_board_db($bo_table, true);

      if(!$board){
        return '';
      }

      $bo_subject = $this->get_text($board['bo_subject']);

      $tmp_write_table = $g5['write_prefix'] . $bo_table; // 게시판 테이블 전체이름
      $sql = " select * from {$tmp_write_table} where wr_is_comment = 0 order by wr_num limit 0, {$rows} ";
      
      $result = $this->pdo_query($sql);
      for ($i=0; $i<count($result); $i++) {
        $row = $result[$i];
        try {
          unset($row['wr_password']);     //패스워드 저장 안함( 아예 삭제 )
        } catch (Exception $e) {
        }
        $row['wr_content'] = '';
        $row['wr_email'] = '';              //이메일 저장 안함
        if (strstr($row['wr_option'], 'secret')){           // 비밀글일 경우 내용, 링크, 파일 저장 안함
          $row['wr_content'] = $row['wr_link1'] = $row['wr_link2'] = '';
          $row['file'] = array('count'=>0);
        }
        $list[$i] = $this->get_list($row, $board, $latest_skin_url, $subject_len);

        $list[$i]['first_file_thumb'] = (isset($row['wr_file']) && $row['wr_file']) ? $this->get_board_file_db($bo_table, $row['wr_id'], 'bf_file, bf_content', "and bf_type between '1' and '3'", true) : array('bf_file'=>'', 'bf_content'=>'');
        $list[$i]['bo_table'] = $bo_table;
        // 썸네일 추가
        if($options && is_string($options)) {
          $options_arr = explode(',', $options);
          $thumb_width = $options_arr[0];
          $thumb_height = $options_arr[1];
          $thumb = $this->get_list_thumbnail($bo_table, $row['wr_id'], $thumb_width, $thumb_height, false, true);
          // 이미지 썸네일
          if($thumb['src']) {
            $list[$i]['img'] = array();
            $list[$i]['img']['thumb'] = $thumb['src'];
            $list[$i]['img']['ori'] = $thumb['ori'];
            $list[$i]['img']['thumb_width'] = $thumb_width;
            $list[$i]['img']['thumb_height'] = $thumb_height;
            $list[$i]['img']['thumb_alt'] = $thumb['alt'];
          }
        }

        if(! isset($list[$i]['icon_file'])) $list[$i]['icon_file'] = '';
      }
      g5_latest_cache_data($bo_table, $list);

      if(G5_USE_CACHE) {
        $caches = array(
          'list' => $list,
          'bo_subject' => $this->sql_escape_string($bo_subject),
        );

        g5_set_cache($cache_file_name, $caches, $time_unit * $cache_time);
      }
    } else {
      $list = $cache_list;
      $bo_subject = (is_array($caches) && isset($caches['bo_subject'])) ? $caches['bo_subject'] : '';
    }

    $result = array();
    $result['bo_subject'] = $bo_subject;
    $result['url'] = $this->get_pretty_url($bo_table);
    $result['list'] = $this->unset_data($list);
    //return $result;
    return $this->data_encode($result);
  }
}