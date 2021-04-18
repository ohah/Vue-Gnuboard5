<?php
trait move {
  public function move($sw) {
    global $g5;
    $is_admin = $this->is_admin;
    $member = $this->member;
    if ($sw === 'move')
      $act = '이동';
    else if ($sw === 'copy')
      $act = '복사';
    else
      $this->alert('sw 값이 제대로 넘어오지 않았습니다.');

    // 게시판 관리자 이상 복사, 이동 가능
    if ($is_admin != 'board' && $is_admin != 'group' && $is_admin != 'super')
      $this->alert("게시판 관리자 이상 접근이 가능합니다.");

    $g5['title'] = '게시물 ' . $act;
    $wr_id_list = '';
    if ($wr_id)
      $wr_id_list = $wr_id;
    else {
      $comma = '';

      $count_chk_wr_id = (isset($_POST['chk_wr_id']) && is_array($_POST['chk_wr_id'])) ? count($_POST['chk_wr_id']) : 0;

      for ($i=0; $i<$count_chk_wr_id; $i++) {
        $wr_id_val = isset($_POST['chk_wr_id'][$i]) ? preg_replace('/[^0-9]/', '', $_POST['chk_wr_id'][$i]) : 0;
        $wr_id_list .= $comma . $wr_id_val;
        $comma = ',';
      }
    }

    //$sql = " select * from {$g5['board_table']} a, {$g5['group_table']} b where a.gr_id = b.gr_id and bo_table <> '$bo_table' ";
    // 원본 게시판을 선택 할 수 있도록 함.
    $sql = " select gr_subject, bo_subject, bo_table from {$g5['board_table']} a, {$g5['group_table']} b where a.gr_id = b.gr_id ";
    if ($is_admin == 'group')
      $sql .= " and b.gr_admin = '{$member['mb_id']}' ";
    else if ($is_admin == 'board')
      $sql .= " and a.bo_admin = '{$member['mb_id']}' ";
    $sql .= " order by a.gr_id, a.bo_order, a.bo_table ";
    $list = $this->sql_query($sql);
    $result = array();
    $result['list'] = $list;
    $result['wr_id_list'] = $wr_id_list;
    return $this->data_encode($result);
  }
  public function move_update($sw, $bo_table) {
    global $g5;
    $is_admin = $this->is_admin;
    $member = $this->member;
    $config = $this->config;
    $write_table = $g5['write_prefix'].$bo_table;
    $board = $this->get_board_db($bo_table);

    if ($sw === 'move')
      $act = '이동';
    else if ($sw === 'copy')
      $act = '복사';
    $count_chk_bo_table = (isset($_POST['chk_bo_table']) && is_array($_POST['chk_bo_table'])) ? count($_POST['chk_bo_table']) : 0;

    // 게시판 관리자 이상 복사, 이동 가능
    if ($is_admin != 'board' && $is_admin != 'group' && $is_admin != 'super')
      $this->alert_close('게시판 관리자 이상 접근이 가능합니다.');

    if ($sw != 'move' && $sw != 'copy')
      $this->alert('sw 값이 제대로 넘어오지 않았습니다.');

    if(! $count_chk_bo_table)
      $this->alert('게시물을 '.$act.'할 게시판을 한개 이상 선택해 주십시오.', $url);

    // 원본 파일 디렉토리
    $src_dir = G5_DATA_PATH.'/file/'.$bo_table;

    $save = array();
    $save_count_write = 0;
    $save_count_comment = 0;
    $cnt = 0;
    
    $wr_id_list = isset($_POST['wr_id_list']) ? preg_replace('/[^0-9\,]/', '', $_POST['wr_id_list']) : '';

    $sql = " select distinct wr_num from $write_table where wr_id in ({$wr_id_list}) order by wr_id ";
    $result = $this->sql_query($sql);
    for($q=0;$q<count($result);$q++) {
      $row = $result[$q];
      $save[$cnt]['wr_contents'] = array();

      $wr_num = $row['wr_num'];
      for ($i=0; $i<$count_chk_bo_table; $i++) {
        $move_bo_table = isset($_POST['chk_bo_table'][$i]) ? preg_replace('/[^a-z0-9_]/i', '', $_POST['chk_bo_table'][$i]) : '';

        // 취약점 18-0075 참고
        $sql = "select * from {$g5['board_table']} where bo_table = '".$move_bo_table."' ";
        $move_board = $this->sql_fetch($sql);
        // 존재하지 않다면
        if( !$move_board['bo_table'] ) continue;

        $move_write_table = $g5['write_prefix'] . $move_bo_table;

        $src_dir = G5_DATA_PATH.'/file/'.$bo_table; // 원본 디렉토리
        $dst_dir = G5_DATA_PATH.'/file/'.$move_bo_table; // 복사본 디렉토리

        $count_write = 0;
        $count_comment = 0;

        $next_wr_num = $this->get_next_num($move_write_table);

        $sql2 = " select * from $write_table where wr_num = '$wr_num' order by wr_parent, wr_is_comment, wr_comment desc, wr_id ";
        $result2 = $this->sql_query($sql2);
        for($j=0;$j<count($result2);$j++) {
          $row2 = $result2[$i];
          $save[$cnt]['wr_contents'][] = $row2['wr_content'];

          $nick = $this->cut_str($member['mb_nick'], $config['cf_cut_name']);
          if (!$row2['wr_is_comment'] && $config['cf_use_copy_log']) {
            if(strstr($row2['wr_option'], 'html')) {
              $log_tag1 = '<div class="content_'.$sw.'">';
              $log_tag2 = '</div>';
            } else {
              $log_tag1 = "\n";
              $log_tag2 = '';
            }

            $row2['wr_content'] .= "\n".$log_tag1.'[이 게시물은 '.$nick.'님에 의해 '.G5_TIME_YMDHIS.' '.$board['bo_subject'].'에서 '.($sw == 'copy' ? '복사' : '이동').' 됨]'.$log_tag2;
          }

          // 게시글 추천, 비추천수
          $wr_good = $wr_nogood = 0;
          if ($sw == 'move' && $i == 0) {
            $wr_good = $row2['wr_good'];
            $wr_nogood = $row2['wr_nogood'];
          }

          $sql = " insert into $move_write_table
                      set wr_num = '$next_wr_num',
                          wr_reply = '{$row2['wr_reply']}',
                          wr_is_comment = '{$row2['wr_is_comment']}',
                          wr_comment = '{$row2['wr_comment']}',
                          wr_comment_reply = '{$row2['wr_comment_reply']}',
                          ca_name = '".addslashes($row2['ca_name'])."',
                          wr_option = '{$row2['wr_option']}',
                          wr_subject = '".addslashes($row2['wr_subject'])."',
                          wr_content = '".addslashes($row2['wr_content'])."',
                          wr_link1 = '".addslashes($row2['wr_link1'])."',
                          wr_link2 = '".addslashes($row2['wr_link2'])."',
                          wr_link1_hit = '{$row2['wr_link1_hit']}',
                          wr_link2_hit = '{$row2['wr_link2_hit']}',
                          wr_hit = '{$row2['wr_hit']}',
                          wr_good = '{$wr_good}',
                          wr_nogood = '{$wr_nogood}',
                          mb_id = '{$row2['mb_id']}',
                          wr_password = '{$row2['wr_password']}',
                          wr_name = '".addslashes($row2['wr_name'])."',
                          wr_email = '".addslashes($row2['wr_email'])."',
                          wr_homepage = '".addslashes($row2['wr_homepage'])."',
                          wr_datetime = '{$row2['wr_datetime']}',
                          wr_file = '{$row2['wr_file']}',
                          wr_last = '{$row2['wr_last']}',
                          wr_ip = '{$row2['wr_ip']}',
                          wr_1 = '".addslashes($row2['wr_1'])."',
                          wr_2 = '".addslashes($row2['wr_2'])."',
                          wr_3 = '".addslashes($row2['wr_3'])."',
                          wr_4 = '".addslashes($row2['wr_4'])."',
                          wr_5 = '".addslashes($row2['wr_5'])."',
                          wr_6 = '".addslashes($row2['wr_6'])."',
                          wr_7 = '".addslashes($row2['wr_7'])."',
                          wr_8 = '".addslashes($row2['wr_8'])."',
                          wr_9 = '".addslashes($row2['wr_9'])."',
                          wr_10 = '".addslashes($row2['wr_10'])."' ";
          $this->sql_query($sql);

          $insert_id = $this->db->lastInsertId();

          // 코멘트가 아니라면
          if (!$row2['wr_is_comment']) {
            $save_parent = $insert_id;

            $sql3 = " select * from {$g5['board_file_table']} where bo_table = '$bo_table' and wr_id = '{$row2['wr_id']}' order by bf_no ";
            $result3 = $this->sql_query($sql3);
            for ($k=0; $k<count($result3); $k++) {
              $row3 = $result3[$k];
              if ($row3['bf_file']) {
                // 원본파일을 복사하고 퍼미션을 변경
                // 제이프로님 코드제안 적용
                $copy_file_name = ($bo_table !== $move_bo_table) ? $row3['bf_file'] : $row2['wr_id'].'_copy_'.$insert_id.'_'.$row3['bf_file'];
                $is_exist_file = is_file($src_dir.'/'.$row3['bf_file']) && file_exists($src_dir.'/'.$row3['bf_file']);
                if( $is_exist_file ){
                  @copy($src_dir.'/'.$row3['bf_file'], $dst_dir.'/'.$copy_file_name);
                  @chmod($dst_dir.'/'.$row3['bf_file'], G5_FILE_PERMISSION);
                }

                $row3 = run_replace('bbs_move_update_file', $row3, $copy_file_name, $bo_table, $move_bo_table, $insert_id);
              }

              $sql = " insert into {$g5['board_file_table']}
                          set bo_table = '$move_bo_table',
                              wr_id = '$insert_id',
                              bf_no = '{$row3['bf_no']}',
                              bf_source = '".addslashes($row3['bf_source'])."',
                              bf_file = '$copy_file_name',
                              bf_download = '{$row3['bf_download']}',
                              bf_content = '".addslashes($row3['bf_content'])."',
                              bf_fileurl = '".addslashes($row3['bf_fileurl'])."',
                              bf_thumburl = '".addslashes($row3['bf_thumburl'])."',
                              bf_storage = '".addslashes($row3['bf_storage'])."',
                              bf_filesize = '{$row3['bf_filesize']}',
                              bf_width = '{$row3['bf_width']}',
                              bf_height = '{$row3['bf_height']}',
                              bf_type = '{$row3['bf_type']}',
                              bf_datetime = '{$row3['bf_datetime']}' ";
              $this->sql_query($sql);

              if ($sw == 'move' && $row3['bf_file'])
                $save[$cnt]['bf_file'][$k] = $src_dir.'/'.$row3['bf_file'];
            }

              $count_write++;

              if ($sw == 'move' && $i == 0) {
                // 스크랩 이동
                $this->sql_query(" update {$g5['scrap_table']} set bo_table = '$move_bo_table', wr_id = '$save_parent' where bo_table = '$bo_table' and wr_id = '{$row2['wr_id']}' ");

                // 최신글 이동
                $this->sql_query(" update {$g5['board_new_table']} set bo_table = '$move_bo_table', wr_id = '$save_parent', wr_parent = '$save_parent' where bo_table = '$bo_table' and wr_id = '{$row2['wr_id']}' ");

                // 추천데이터 이동
                $this->sql_query(" update {$g5['board_good_table']} set bo_table = '$move_bo_table', wr_id = '$save_parent' where bo_table = '$bo_table' and wr_id = '{$row2['wr_id']}' ");
              }
          } else  {
            $count_comment++;

            if ($sw == 'move') {
              // 최신글 이동
              $this->sql_query(" update {$g5['board_new_table']} set bo_table = '$move_bo_table', wr_id = '$insert_id', wr_parent = '$save_parent' where bo_table = '$bo_table' and wr_id = '{$row2['wr_id']}' ");
            }
          }

          $this->sql_query(" update $move_write_table set wr_parent = '$save_parent' where wr_id = '$insert_id' ");

          if ($sw == 'move')
            $save[$cnt]['wr_id'] = $row2['wr_parent'];

          $cnt++;
        }

        $this->sql_query(" update {$g5['board_table']} set bo_count_write = bo_count_write + '$count_write' where bo_table = '$move_bo_table' ");
        $this->sql_query(" update {$g5['board_table']} set bo_count_comment = bo_count_comment + '$count_comment' where bo_table = '$move_bo_table' ");
        
        run_event('bbs_move_copy', $row2, $move_bo_table, $insert_id, $next_wr_num, $sw);

        $this->delete_cache_latest($move_bo_table);
      }

      $save_count_write += $count_write;
      $save_count_comment += $count_comment;
    }

    $this->delete_cache_latest($bo_table);

    if ($sw == 'move') {
      for ($i=0; $i<count($save); $i++) {
        if( isset($save[$i]['bf_file']) && $save[$i]['bf_file'] ){
          for ($k=0; $k<count($save[$i]['bf_file']); $k++) {
            $del_file = run_replace('delete_file_path', $this->clean_relative_paths($save[$i]['bf_file'][$k]), $save[$i]);

            if ( is_file($del_file) && file_exists($del_file) ){
                @unlink($del_file);
            }
            
            // 썸네일 파일 삭제, 먼지손 님 코드 제안
            $this->delete_board_thumbnail($bo_table, basename($save[$i]['bf_file'][$k]));
          }
        }
          
        for ($k=0; $k<count($save[$i]['wr_contents']); $k++){
          $this->delete_editor_thumbnail($save[$i]['wr_contents'][$k]);
        }

        $this->sql_query(" delete from $write_table where wr_parent = '{$save[$i]['wr_id']}' ");
        $this->sql_query(" delete from {$g5['board_new_table']} where bo_table = '$bo_table' and wr_id = '{$save[$i]['wr_id']}' ");
        $this->sql_query(" delete from {$g5['board_file_table']} where bo_table = '$bo_table' and wr_id = '{$save[$i]['wr_id']}' ");
      }
      $this->sql_query(" update {$g5['board_table']} set bo_count_write = bo_count_write - '$save_count_write', bo_count_comment = bo_count_comment - '$save_count_comment' where bo_table = '$bo_table' ");
    }
    $result = array();
    $result['msg'] = 'success';
    return $this->data_encode($result);
  }
}