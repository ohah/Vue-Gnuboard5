<?php
trait qa {
  public function qalist() {
    global $g5;
    $sca = $this->$sca;
    $sfl = $this->$sfl;
    $stx = $this->$stx;
    $sst = $this->$sst;
    $sod = $this->$sod;
    $spt = $this->$spt;
    $page = $this->$page;
    $member = $this->member;
    $config = $this->config;    
    $is_admin = $this->is_admin;
    $is_guest = $this->$is_guest;
    $is_member = $this->is_member;

    if($is_guest)
      $this->alert('회원이시라면 로그인 후 이용해 보십시오.');

    $qaconfig = $this->get_qa_config();

    $token = '';
    if( $is_admin ){
      $token = $this->_token();
      $this->set_session('ss_qa_delete_token', $token);
    }

    $is_auth = $is_admin ? true : false;

    $category_option = '';
    $result = array();
    $category_option = array();
    $k = 1;
    if ($qaconfig['qa_category']) {
      $category_href = G5_BBS_URL.'/qalist.php';

      $category_option .= '<li><a href="'.$category_href.'"';
      if ($sca==''){
        $category_option[0]['active'] = true;
      }
      $category_option[0]['name'] = '전체';

      $categories = explode('|', $qaconfig['qa_category']); // 구분자가 | 로 되어 있음
      for ($i=0; $i<count($categories); $i++) {
        $category = trim($categories[$i]);
        if ($category=='') continue;
        $category_option .= '<li><a href="'.($category_href."?sca=".urlencode($category)).'"';
        $category_option[$k]['sca'] = urlencode($category);
        if ($category==$sca) { // 현재 선택된 카테고리라면
          $category_option[$k]['active'] = true;
        }
        $category_option[$k]['name'] = $category;
        $k++;
      }
    }



    $sql_common = " from {$g5['qa_content_table']} ";
    $sql_search = " where qa_type = '0' ";

    if(!$is_admin)
      $sql_search .= " and mb_id = '{$member['mb_id']}' ";

    if($sca) {
      if (preg_match("/[a-zA-Z]/", $sca))
        $sql_search .= " and INSTR(LOWER(qa_category), LOWER('$sca')) > 0 ";
      else
        $sql_search .= " and INSTR(qa_category, '$sca') > 0 ";
    }

    $stx = trim($stx);
    if($stx) {
      if (preg_match("/[a-zA-Z]/", $stx))
        $sql_search .= " and ( INSTR(LOWER(qa_subject), LOWER('$stx')) > 0 or INSTR(LOWER(qa_content), LOWER('$stx')) > 0 )";
      else
        $sql_search .= " and ( INSTR(qa_subject, '$stx') > 0 or INSTR(qa_content, '$stx') > 0 ) ";
    }

    $sql_order = " order by qa_num ";

    $sql = " select count(*) as cnt
            $sql_common
            $sql_search ";
    $row = $this->sql_fetch($sql);
    $total_count = $row['cnt'];

    $page_rows = $this->is_mobile() ? $qaconfig['qa_mobile_page_rows'] : $qaconfig['qa_page_rows'];
    $total_page  = ceil($total_count / $page_rows);  // 전체 페이지 계산
    if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
    $from_record = ($page - 1) * $page_rows; // 시작 열을 구함

    $sql = " select *
            $sql_common
            $sql_search
            $sql_order
            limit $from_record, $page_rows ";
    $result = $this->sql_query($sql);

    $list = array();
    $num = $total_count - ($page - 1) * $page_rows;
    $subject_len = $this->is_mobile() ? $qaconfig['qa_mobile_subject_len'] : $qaconfig['qa_subject_len'];
    for($i=0; $i<count($result); $i++) {
      $row = $result[$i];
      $list[$i] = $row;

      $list[$i]['category'] = $this->get_text($row['qa_category']);
      $list[$i]['subject'] = $this->conv_subject($row['qa_subject'], $subject_len, '…');
      if ($stx) {
        $list[$i]['subject'] = $this->search_font($stx, $list[$i]['subject']);
      }

      $list[$i]['view_href'] = G5_BBS_URL.'/qaview.php?qa_id='.$row['qa_id'].$qstr;

      $list[$i]['icon_file'] = '';
      if(trim($row['qa_file1']) || trim($row['qa_file2']))
        $list[$i]['icon_file'] = '<img src="'.$qa_skin_url.'/img/icon_file.gif">';

      $list[$i]['name'] = $this->get_text($row['qa_name']);
      // 사이드뷰 적용시
      //$list[$i]['name'] = get_sideview($row['mb_id'], $row['qa_name']);
      $list[$i]['date'] = substr($row['qa_datetime'], 2, 8);

      $list[$i]['num'] = $num - $i;
    }

    $is_checkbox = false;
    $admin_href = '';
    if($is_admin) {
      $is_checkbox = true;
      $admin_href = G5_ADMIN_URL.'/qa_config.php';
    }

    $list_href = G5_BBS_URL.'/qalist.php';
    $write_href = G5_BBS_URL.'/qawrite.php';

    $list_pages = preg_replace('/(\.php)(&amp;|&)/i', '$1?', $this->get_paging($this->is_mobile() ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, './qalist.php'.$qstr.'&amp;page='));

  }

  public function qaview($qa_id) {
    global $g5;
    $sca = $this->$sca;
    $sfl = $this->$sfl;
    $stx = $this->$stx;
    $sst = $this->$sst;
    $sod = $this->$sod;
    $spt = $this->$spt;
    $page = $this->$page;
    $member = $this->member;
    $config = $this->config;    
    $is_admin = $this->is_admin;
    $is_guest = $this->$is_guest;
    $is_member = $this->is_member;
    $qstr = '';
    foreach ($this->qstr as $key => $value) {
      $qstr .= $key.'='.$value;
    }

    if($is_guest)
      $this->alert('회원이시라면 로그인 후 이용해 보십시오.', './login.php?url='.urlencode(G5_BBS_URL.'/qalist.php'));

    $qaconfig = $this->get_qa_config();

    $token = '';
    if( $is_admin ){
      $token = $this->_token();
      $this->set_session('ss_qa_delete_token', $token);
    }

    $is_auth = $is_admin ? true : false;

    $category_option = '';
    $result = array();
    $category_option = array();
    $k = 1;
    if ($qaconfig['qa_category']) {
      $category_href = G5_BBS_URL.'/qalist.php';

      $category_option .= '<li><a href="'.$category_href.'"';
      if ($sca==''){
        $category_option[0]['active'] = true;
      }
      $category_option[0]['name'] = '전체';

      $categories = explode('|', $qaconfig['qa_category']); // 구분자가 | 로 되어 있음
      for ($i=0; $i<count($categories); $i++) {
        $category = trim($categories[$i]);
        if ($category=='') continue;
        $category_option .= '<li><a href="'.($category_href."?sca=".urlencode($category)).'"';
        $category_option[$k]['sca'] = urlencode($category);
        if ($category==$sca) { // 현재 선택된 카테고리라면
          $category_option[$k]['active'] = true;
        }
        $category_option[$k]['name'] = $category;
        $k++;
      }
    }

    $sql = " select * from {$g5['qa_content_table']} where qa_id = '$qa_id' ";
    if(!$is_admin) {
      $sql .= " and mb_id = '{$member['mb_id']}' ";
    }

    $view = $this->sql_fetch($sql);

    if(!(isset($view['qa_id']) && $view['qa_id']))
      $this->alert('게시글이 존재하지 않습니다.\\n삭제되었거나 자신의 글이 아닌 경우입니다.');

    $subject_len = $this->is_mobile() ? $qaconfig['qa_mobile_subject_len'] : $qaconfig['qa_subject_len'];

    $view['category'] = $this->get_text($view['qa_category']);
    $view['subject'] = $this->conv_subject($view['qa_subject'], $subject_len, '…');
    $view['content'] = $this->conv_content($view['qa_content'], $view['qa_html']);
    $view['name'] = $this->get_text($view['qa_name']);
    $view['datetime'] = $view['qa_datetime'];
    $view['email'] = $this->get_text($this->get_email_address($view['qa_email']));
    $view['hp'] = $view['qa_hp'];

    if (trim($stx))
    $view['subject'] = $this->search_font($stx, $view['subject']);

    if (trim($stx))
      $view['content'] = $this->search_font($stx, $view['content']);

    // 이전글, 다음글
    $sql = " select qa_id, qa_subject
                from {$g5['qa_content_table']}
                where qa_type = '0' ";
    if(!$is_admin) {
      $sql .= " and mb_id = '{$member['mb_id']}' ";
    }

    // 이전글
    $prev_search = " and qa_num < '{$view['qa_num']}' order by qa_num desc limit 1 ";
    $prev = $this->sql_fetch($sql.$prev_search);

    $prev_href = '';
    if (isset($prev['qa_id']) && $prev['qa_id']) {
      $prev_qa_subject = $this->get_text($this->cut_str($prev['qa_subject'], 255));
      $prev_href = G5_BBS_URL.'/qaview.php?qa_id='.$prev['qa_id'].$qstr;
    }

    // 다음글
    $next_search = " and qa_num > '{$view['qa_num']}' order by qa_num asc limit 1 ";
    $next = $this->sql_fetch($sql.$next_search);

    $next_href = '';
    if (isset($next['qa_id']) && $next['qa_id']) {
      $next_qa_subject = $this->get_text($this->cut_str($next['qa_subject'], 255));
      $next_href = G5_BBS_URL.'/qaview.php?qa_id='.$next['qa_id'].$qstr;
    }


    // 관련질문
    $rows = 10;
    $sql = " select *
                from {$g5['qa_content_table']}
                where qa_id <> ?
                  and qa_related = ?
                  and qa_type = ?
                order by qa_num, qa_type
                limit 0, $rows ";
    $result = $this->sql_query($sql, [$qa_id, $view['qa_related'], '0']);

    $rel_list = array();
    $rel_count = 0;
    for($i=0; $i<count($result); $i++) {
      $row = $result[$i];
      $rel_list[$i] = $row;
      $rel_list[$i]['category'] = $this->get_text($row['qa_category']);
      $rel_list[$i]['subject'] = $this->conv_subject($row['qa_subject'], $subject_len, '…');
      $rel_list[$i]['name'] = $this->get_text($row['qa_name']);
      $rel_list[$i]['date'] = substr($row['qa_datetime'], 2, 8);
      $rel_list[$i]['view_href'] = G5_BBS_URL.'/qaview.php?qa_id='.$row['qa_id'].$qstr;
      $rel_count++;
    }
    $view['rel_count'] = $rel_count;

    $update_href = '';
    $delete_href = '';
    $write_href = G5_BBS_URL.'/qawrite.php';
    $rewrite_href = G5_BBS_URL.'/qawrite.php?w=r&amp;qa_id='.$view['qa_id'];
    $list_href = G5_BBS_URL.'/qalist.php'.preg_replace('/^&amp;/', '?', $qstr);

    if(($view['qa_type'] && $is_admin) || (!$view['qa_type'] && $view['qa_status'] == 0)) {
      $update_href = G5_BBS_URL.'/qawrite.php?w=u&amp;qa_id='.$view['qa_id'].$qstr;
      $delete_href = G5_BBS_URL.'/qadelete.php?qa_id='.$view['qa_id'].'&amp;token='.$token.$qstr;
    }

    // 질문글이고 등록된 답변이 있다면
    $answer = array();
    $answer_update_href = '';
    $answer_delete_href = '';
    if(!$view['qa_type'] && $view['qa_status']) {
      $sql = " select *
              from {$g5['qa_content_table']}
              where qa_type = ?
              and qa_parent = ?";
      $answer = $this->sql_fetch($sql, ['1', $view['qa_id']]);

      if($is_admin) {
        $answer_update_href = G5_BBS_URL.'/qawrite.php?w=u&amp;qa_id='.$answer['qa_id'].$qstr;
        $answer_delete_href = G5_BBS_URL.'/qadelete.php?qa_id='.$answer['qa_id'].'&amp;token='.$token.$qstr;
      }
    }

    $stx = $this->get_text(stripslashes($stx));

    $is_dhtml_editor = false;
    // 모바일에서는 DHTML 에디터 사용불가
    if ($config['cf_editor'] && $qaconfig['qa_use_editor'] && $this->is_mobile()) {
      $is_dhtml_editor = true;
    }
    $editor_html = $this->editor_html('qa_content', $content, $is_dhtml_editor);
    $editor_js = '';
    $editor_js .= $this->get_editor_js('qa_content', $is_dhtml_editor);
    $editor_js .= $this->chk_editor_js('qa_content', $is_dhtml_editor);

    $ss_name = 'ss_qa_view_'.$qa_id;
    if(!$this->get_session($ss_name))
      $this->set_session($ss_name, TRUE);

    // 첨부파일
    $view['img_file'] = array();
    $view['download_href'] = array();
    $view['download_source'] = array();
    $view['img_count'] = 0;
    $view['download_count'] = 0;

    for ($i=1; $i<=2; $i++) {
      if(preg_match("/\.({$config['cf_image_extension']})$/i", $view['qa_file'.$i])) {
        $attr_href = run_replace('thumb_view_image_href', G5_BBS_URL.'/view_image.php?fn='.urlencode('/'.G5_DATA_DIR.'/qa/'.$view['qa_file'.$i]), '/'.G5_DATA_DIR.'/qa/'.$view['qa_file'.$i], '', '', '', '');
        $view['img_file'][] = '<a href="'.$attr_href.'" target="_blank" class="view_image"><img src="'.G5_DATA_URL.'/qa/'.$view['qa_file'.$i].'"></a>';
        $view['img_count']++;
        continue;
      }

      if ($view['qa_file'.$i]) {
        $view['download_href'][] = G5_BBS_URL.'/qadownload.php?qa_id='.$view['qa_id'].'&amp;no='.$i;
        $view['download_source'][] = $view['qa_source'.$i];
        $view['download_count']++;
      }
    }

    $html_value = '';
    $html_checked = '';
    if (isset($view['qa_html']) && $view['qa_html']) {
      $html_checked = 'checked';
      $html_value = $view['qa_html'];

      if($view['qa_html'] == 1 && !$is_dhtml_editor)
        $html_value = 2;
    }
    
  }
  public function qa_write($qa_id) {
    global $g5;
    $sca = $this->$sca;
    $sfl = $this->$sfl;
    $stx = $this->$stx;
    $sst = $this->$sst;
    $sod = $this->$sod;
    $spt = $this->$spt;
    $page = $this->$page;
    $member = $this->member;
    $config = $this->config;    
    $is_admin = $this->is_admin;
    $is_guest = $this->$is_guest;
    $is_member = $this->is_member;
    $qstr = '';
    foreach ($this->qstr as $key => $value) {
      $qstr .= $key.'='.$value;
    }
    if($is_guest)
      $this->alert('회원이시라면 로그인 후 이용해 보십시오.', './login.php?url='.urlencode(G5_BBS_URL.'/qalist.php'));

    $qaconfig = $this->get_qa_config();
    $token = $this->_token();
    $this->set_session('ss_qa_write_token', $token);

    /*==========================
    $w == a : 답변
    $w == r : 추가질문
    $w == u : 수정
    ==========================*/

    if($w == 'u' || $w == 'r') {
      $sql = " select * from {$g5['qa_content_table']} where qa_id = '$qa_id' ";
      if(!$is_admin) {
          $sql .= " and mb_id = '{$member['mb_id']}' ";
      }

      $write = $this->sql_fetch($sql);

      if($w == 'u') {
        if(!$write['qa_id'])
          $this->alert('게시글이 존재하지 않습니다.\\n삭제되었거나 자신의 글이 아닌 경우입니다.');

        if(!$is_admin) {
          if($write['qa_type'] == 0 && $write['qa_status'] == 1)
            $this->alert('답변이 등록된 문의글은 수정할 수 없습니다.');

          if($write['mb_id'] != $member['mb_id'])
            $this->alert('게시글을 수정할 권한이 없습니다.\\n\\n올바른 방법으로 이용해 주십시오.', G5_URL);
        }
      }
    }

    // 분류
    $category_option = array();
    if(trim($qaconfig['qa_category'])) {
      $category = explode('|', $qaconfig['qa_category']);
      for($i=0; $i<count($category); $i++) {
        $category_option[] = $this->option_selected($category[$i], $write['qa_category']);
      }
    } else {
      $this->alert('1:1문의 설정에서 분류를 설정해 주십시오');
    }

    $is_dhtml_editor = false;
    if ($config['cf_editor'] && $qaconfig['qa_use_editor'] && (!$this->is_mobile() || defined('G5_IS_MOBILE_DHTML_USE') && G5_IS_MOBILE_DHTML_USE)) {
      $is_dhtml_editor = true;
    }

    // 추가질문에서는 제목을 공백으로
    if($w == 'r')
      $write['qa_subject'] = '';

    $content = '';
    if ($w == '') {
      $content = $this->html_purifier($qaconfig['qa_insert_content']);
    } else if($w == 'r') {
      if($is_dhtml_editor)
        $content = '<div><br><br><br>====== 이전 답변내용 =======<br></div>';
      else
        $content = "\n\n\n\n====== 이전 답변내용 =======\n";

      $content .= $this->get_text($write['qa_content'], 0);
    } else {
      //$content = get_text($write['qa_content'], 0);
      
      // KISA 취약점 권고사항 Stored XSS
      $content = $this->get_text($this->html_purifier($write['qa_content']), 0);
    }

    /*
    $editor_html = editor_html('qa_content', $content, $is_dhtml_editor);
    $editor_js = '';
    $editor_js .= get_editor_js('qa_content', $is_dhtml_editor);
    $editor_js .= chk_editor_js('qa_content', $is_dhtml_editor);
    */

    $upload_max_filesize = number_format($qaconfig['qa_upload_size']) . ' 바이트';

    $html_value = '';
    if (isset($write['qa_html']) && $write['qa_html']) {
      $html_checked = 'checked';
      $html_value = $write['qa_html'];

      if($w == 'r' && $write['qa_html'] == 1 && !$is_dhtml_editor)
        $html_value = 2;
    }

    $is_email = false;
    $req_email = '';
    if($qaconfig['qa_use_email']) {
      $is_email = true;

      if($qaconfig['qa_req_email'])
        $req_email = 'required';

      if($w == '' || $w == 'r')
        $write['qa_email'] = $member['mb_email'];

      if($w == 'u' && $is_admin && $write['qa_type'])
        $is_email = false;
    }

    $is_hp = false;
    $req_hp = '';
    if($qaconfig['qa_use_hp']) {
      $is_hp = true;

      if($qaconfig['qa_req_hp'])
        $req_hp = 'required';

      if($w == '' || $w == 'r')
        $write['qa_hp'] = $member['mb_hp'];

      if($w == 'u' && $is_admin && $write['qa_type'])
        $is_hp = false;
    }

    $result= array();
    $result['w'] = $w;
    $result['qa_id'] = $qa_id;
    $result['sca'] = $sca;
    $result['stx'] = $stx;
    $result['page'] = $page;
    $result['token'] = $token;
    $result['html_value'] = $html_value;
    $result['category_option'] = $category_option;
    $result['is_email'] = $category_option;
    $result['is_hp'] = $category_option;
    $result['write'] = $write;
    return $result;
  }
}