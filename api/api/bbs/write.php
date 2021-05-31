<?php
/**
 * write.php
 */
trait write {
  public function write($bo_table, $wr_id = '', $w = '') {
    global $g5;
    $write_table = $g5['write_prefix'].$bo_table;
    $write = $this->get_write($write_table, $wr_id);
    $member = $this->member;
    $is_admin = $this->is_admin;
    $is_guest = $this->is_guest;
    $is_member = $this->is_member;
    $config = $this->config;
    $board = $this->get_board_db($bo_table);
    $group = $this->get_group($board['gr_id']);
    $wr_password = $this->getPostData()['wr_password'] ? $this->getPostData()['wr_password'] : '';
    
    if (!$board['bo_table']) {
      $this->alert('존재하지 않는 게시판입니다.');
    }
    if (!$bo_table) {
      $this->alert("bo_table 값이 넘어오지 않았습니다.\r\nwrite.php?bo_table=code 와 같은 방식으로 넘겨 주세요.");
    }
    $notice_array = explode(',', trim($board['bo_notice']));

    if (!($w == '' || $w == 'u' || $w == 'r')) {
      $this->alert('w 값이 제대로 넘어오지 않았습니다.');
    }

    if ($w == 'u' || $w == 'r') {
      if ($write['wr_id']) {
        // 가변 변수로 $wr_1 .. $wr_10 까지 만든다.
        for ($i=1; $i<=10; $i++) {
          $vvar = "wr_".$i;
          $$vvar = $write['wr_'.$i];
        }
      } else {
        $this->alert("글이 존재하지 않습니다.\r\n삭제되었거나 이동된 경우입니다.");
      }
    }

    run_event('bbs_write', $board, $wr_id, $w);

    if ($w == '') {
      if ($wr_id) {
        $this->alert('글쓰기에는 \$wr_id 값을 사용하지 않습니다.');
      }
  
      if ($member['mb_level'] < $board['bo_write_level']) {
        if ($member['mb_id']) {
          $this->alert('글을 쓸 권한이 없습니다.');
        } else {
          $this->alert("글을 쓸 권한이 없습니다.\\n회원이시라면 로그인 후 이용해 보십시오.");
        }
      }
  
      // 음수도 true 인것을 왜 이제야 알았을까?
      if ($is_member) {
        $tmp_point = ($member['mb_point'] > 0) ? $member['mb_point'] : 0;
        if ($tmp_point + $board['bo_write_point'] < 0 && !$is_admin) {
          $this->alert('보유하신 포인트('.number_format($member['mb_point']).')가 없거나 모자라서 글쓰기('.number_format($board['bo_write_point']).')가 불가합니다.\r\n포인트를 적립하신 후 다시 글쓰기 해 주십시오.');
        }
      }
  
      $title_msg = '글쓰기';
    } else if ($w == 'u') {
      // 김선용 1.00 : 글쓰기 권한과 수정은 별도로 처리되어야 함
      //if ($member['mb_level'] < $board['bo_write_level']) {
      if($member['mb_id'] && $write['mb_id'] === $member['mb_id']) {
        ;
      } else if ($member['mb_level'] < $board['bo_write_level']) {
        if ($member['mb_id']) {
          $this->alert('글을 수정할 권한이 없습니다.');
        } else {
          $this->alert('글을 수정할 권한이 없습니다.\r\n회원이시라면 로그인 후 이용해 보십시오.');
        }
      }
    
      $len = strlen($write['wr_reply']);
      if ($len < 0) $len = 0;
      $reply = substr($write['wr_reply'], 0, $len);
  
      // 원글만 구한다.
      $sql = " select count(*) as cnt from {$write_table}
                where wr_reply like :reply
                and wr_id <> :wr_id
                and wr_num = :wr_num
                and wr_is_comment = 0 ";
      $row = $this->pdo_fetch($sql, array("reply"=>$reply.'%', "wr_id"=>$write['wr_id'], "wr_num" => $write['wr_num']));
      if ($row['cnt'] && !$is_admin)
        $this->alert('이 글과 관련된 답변글이 존재하므로 수정 할 수 없습니다.\r\n답변글이 있는 원글은 수정할 수 없습니다.');
  
      // 코멘트 달린 원글의 수정 여부
    $sql = " select count(*) as cnt from {$write_table}
              where wr_parent = :wr_parent
              and mb_id <> :mb_id
              and wr_is_comment = 1 ";
      $row = $this->pdo_fetch($sql, array("wr_parent"=>$wr_id, "mb_id"=>$member['mb_id']));
      if ($board['bo_count_modify'] && $row['cnt'] >= $board['bo_count_modify'] && !$is_admin)
        $this->alert('이 글과 관련된 댓글이 존재하므로 수정 할 수 없습니다.\r\n댓글이 '.$board['bo_count_modify'].'건 이상 달린 원글은 수정할 수 없습니다.');

    } else if ($w == 'r') {
      if ($member['mb_level'] < $board['bo_reply_level']) {
        if ($member['mb_id'])
          $this->alert('글을 답변할 권한이 없습니다.');
        else
          $this->alert('답변글을 작성할 권한이 없습니다.\r\n회원이시라면 로그인 후 이용해 보십시오.');
      }
  
      $tmp_point = isset($member['mb_point']) ? $member['mb_point'] : 0;
      if ($tmp_point + $board['bo_write_point'] < 0 && !$is_admin)
        $this->alert('보유하신 포인트('.number_format($member['mb_point']).')가 없거나 모자라서 글답변('.number_format($board['bo_comment_point']).')가 불가합니다.\r\n포인트를 적립하신 후 다시 글답변 해 주십시오.');
  
      //if (preg_match("/[^0-9]{0,1}{$wr_id}[\r]{0,1}/",$board['bo_notice']))
      if (in_array((int)$wr_id, $notice_array))
        $this->alert('공지에는 답변 할 수 없습니다.');
  
      //----------
      // 4.06.13 : 비밀글을 타인이 열람할 수 있는 오류 수정 (헐랭이, 플록님께서 알려주셨습니다.)
      // 코멘트에는 원글의 답변이 불가하므로
      if ($write['wr_is_comment'])
        $this->alert('정상적인 접근이 아닙니다.');
  
      // 비밀글인지를 검사
      if (strstr($write['wr_option'], 'secret')) {
        if ($write['mb_id']) {
          // 회원의 경우는 해당 글쓴 회원 및 관리자
          if (!($write['mb_id'] === $member['mb_id'] || $is_admin))
            $this->alert('비밀글에는 자신 또는 관리자만 답변이 가능합니다.');
        } else {
          // 비회원의 경우는 비밀글에 답변이 불가함
          if (!$is_admin)
            $this->alert('비회원의 비밀글에는 답변이 불가합니다.');
        }
      }
      //----------
  
      // 게시글 배열 참조
      $reply_array = &$write;
  
      // 최대 답변은 테이블에 잡아놓은 wr_reply 사이즈만큼만 가능합니다.
      if (strlen($reply_array['wr_reply']) == 10)
        $this->alert('더 이상 답변하실 수 없습니다.\r\n답변은 10단계 까지만 가능합니다.');
  
      $reply_len = strlen($reply_array['wr_reply']) + 1;
      if ($board['bo_reply_order']) {
        $begin_reply_char = 'A';
        $end_reply_char = 'Z';
        $reply_number = +1;
        $sql = " select MAX(SUBSTRING(wr_reply, {$reply_len}, 1)) as reply from {$write_table} where wr_num = '{$reply_array['wr_num']}' and SUBSTRING(wr_reply, {$reply_len}, 1) <> '' ";
      } else {
        $begin_reply_char = 'Z';
        $end_reply_char = 'A';
        $reply_number = -1;
        $sql = " select MIN(SUBSTRING(wr_reply, {$reply_len}, 1)) as reply from {$write_table} where wr_num = '{$reply_array['wr_num']}' and SUBSTRING(wr_reply, {$reply_len}, 1) <> '' ";
      }
      if ($reply_array['wr_reply']) $sql .= " and wr_reply like '{$reply_array['wr_reply']}%' ";
      $row = $this->sql_fetch($sql);
  
      if (!$row['reply'])
        $reply_char = $begin_reply_char;
      else if ($row['reply'] == $end_reply_char) // A~Z은 26 입니다.
        $this->alert('더 이상 답변하실 수 없습니다.\r\n답변은 26개 까지만 가능합니다.');
      else
        $reply_char = chr(ord($row['reply']) + $reply_number);
  
      $reply = $reply_array['wr_reply'] . $reply_char;
  
      $title_msg = '글답변';
  
      $write['wr_subject'] = 'Re: '.$write['wr_subject'];
    }
    // 그룹접근 가능
    if (!empty($group['gr_use_access'])) {
      if ($is_guest) {
        $this->alert("접근 권한이 없습니다.\r\n회원이시라면 로그인 후 이용해 보십시오.");
      }

      if ($is_admin == 'super' || $group['gr_admin'] === $member['mb_id'] || $board['bo_admin'] === $member['mb_id']) {
        ; // 통과
      } else {
        // 그룹접근
        $sql = " select gr_id from {$g5['group_member_table']} where gr_id = :gr_id and mb_id = :mb_id";
        $row = $this->pdo_fetch($sql, array("gr_id"=>$board['gr_id'], "mb_id"=>$member['mb_id']));
        if (!$row['gr_id'])
          $this->alert('접근 권한이 없으므로 글쓰기가 불가합니다.\r\n궁금하신 사항은 관리자에게 문의 바랍니다.');
      }
    }

    // 본인확인을 사용한다면
    if ($config['cf_cert_use'] && !$is_admin) {
      // 인증된 회원만 가능
      if ($board['bo_use_cert'] != '' && $is_guest) {
        $this->alert('이 게시판은 본인확인 하신 회원님만 글쓰기가 가능합니다.\r\n회원이시라면 로그인 후 이용해 보십시오.');
      }

      if ($board['bo_use_cert'] == 'cert' && !$member['mb_certify']) {
        $this->alert('이 게시판은 본인확인 하신 회원님만 글쓰기가 가능합니다.\r\n회원정보 수정에서 본인확인을 해주시기 바랍니다.');
      }

      if ($board['bo_use_cert'] == 'adult' && !$member['mb_adult']) {
        $this->alert('이 게시판은 본인확인으로 성인인증 된 회원님만 글쓰기가 가능합니다.\r\n성인인데 글쓰기가 안된다면 회원정보 수정에서 본인확인을 다시 해주시기 바랍니다.');
      }

      if ($board['bo_use_cert'] == 'hp-cert' && $member['mb_certify'] != 'hp') {
        $this->alert('이 게시판은 휴대폰 본인확인 하신 회원님만 글읽기가 가능합니다.\r\n회원정보 수정에서 휴대폰 본인확인을 해주시기 바랍니다.');
      }

      if ($board['bo_use_cert'] == 'hp-adult' && (!$member['mb_adult'] || $member['mb_certify'] != 'hp')) {
        $this->alert('이 게시판은 휴대폰 본인확인으로 성인인증 된 회원님만 글읽기가 가능합니다.\r\n현재 성인인데 글읽기가 안된다면 회원정보 수정에서 휴대폰 본인확인을 다시 해주시기 바랍니다.');
      }
    }

    // 글자수 제한 설정값
    if ($is_admin || $board['bo_use_dhtml_editor']) {
      $write_min = $write_max = 0;
    } else {
      $write_min = (int)$board['bo_write_min'];
      $write_max = (int)$board['bo_write_max'];
    }

    $g5['title'] = (($this->is_mobile() && $board['bo_mobile_subject']) ? $board['bo_mobile_subject'] : $board['bo_subject']).' '.$title_msg;

    $is_notice = false;
    $notice_checked = '';
    if ($is_admin && $w != 'r') {
      $is_notice = true;

      if ($w == 'u') {
        // 답변 수정시 공지 체크 없음
        if ($write['wr_reply']) {
          $is_notice = false;
        } else {
          if (in_array((int)$wr_id, $notice_array)) {
            $notice_checked = 'checked';
          }
        }
      }
    }

    $is_html = false;
    if ($member['mb_level'] >= $board['bo_html_level'])
      $is_html = true;

    $is_secret = $board['bo_use_secret'];

    $is_mail = false;
    if ($config['cf_email_use'] && $board['bo_use_email'])
      $is_mail = true;

    $recv_email_checked = '';
    if ($w == '' || strstr($write['wr_option'], 'mail'))
      $recv_email_checked = 'checked';

    $is_name     = false;
    $is_password = false;
    $is_email    = false;
    $is_homepage = false;
    if ($is_guest || ($is_admin && $w == 'u' && $member['mb_id'] !== $write['mb_id'])) {
      $is_name = true;
      $is_password = true;
      $is_email = true;
      $is_homepage = true;
    }

    $is_category = false;
    $category_option = '';
    if ($board['bo_use_category']) {
      $ca_name = "";
      if (isset($write['ca_name']))
        $ca_name = $write['ca_name'];
      $category_option = $this->get_category_option($bo_table, $ca_name);
      $is_category = true;
    }

    $is_link = false;
    if ($member['mb_level'] >= $board['bo_link_level']) {
      $is_link = true;
      $link_count = G5_LINK_COUNT;
    }

    $is_file = false;
    if ($member['mb_level'] >= $board['bo_upload_level']) {
      $is_file = true;
    }

    $is_file_content = false;
    if ($board['bo_use_file_content']) {
      $is_file_content = true;
    }

    $file_count = (int)$board['bo_upload_count'];

    $name     = "";
    $email    = "";
    $homepage = "";
    if ($w == "" || $w == "r") {
      if ($is_member) {
        if (isset($write['wr_name'])) {
          $name = $this->get_text($this->cut_str(stripslashes($write['wr_name']),20));
        }
        $email = $this->get_email_address($member['mb_email']);
        $homepage = $this->get_text(stripslashes($member['mb_homepage']));
      }
    }

    $html_checked   = "";
    $html_value     = "";
    $secret_checked = "";

    if ($w == '') {
      $password_required = 'required';
    } else if ($w == 'u') {
      $password_required = '';
      if (!$is_admin) {
        if (!($is_member && $member['mb_id'] === $write['mb_id'])) {
          if (!$this->check_password($wr_password, $write['wr_password'])) {
            $is_wrong = run_replace('invalid_password', false, 'write', $write);
            $ss_name = 'ss_secret_'.$bo_table.'_'.$write['wr_num'];
            $pass = $this->get_session($ss_name, TRUE);
            if(!$is_wrong && !$pass) $this->alert('비밀번호가 틀립니다.');
          }
        }
      }

      $name = $this->get_text($this->cut_str(stripslashes($write['wr_name']),20));
      $email = $this->get_email_address($write['wr_email']);
      $homepage = $this->get_text(stripslashes($write['wr_homepage']));

      for ($i=1; $i<=G5_LINK_COUNT; $i++) {
        $write['wr_link'.$i] = $this->get_text($write['wr_link'.$i]);
        $link[$i] = $write['wr_link'.$i];
      }

      if (strstr($write['wr_option'], 'html1')) {
        $html_checked = 'checked';
        $html_value = 'html1';
      } else if (strstr($write['wr_option'], 'html2')) {
        $html_checked = 'checked';
        $html_value = 'html2';
      }

      if (strstr($write['wr_option'], 'secret')) {
        $secret_checked = 'checked';
      }

      $file = $this->get_file($bo_table, $wr_id);
      if($file_count < $file['count']) {
        $file_count = $file['count'];
      }

      for($i=0;$i<$file_count;$i++){
        if(! isset($file[$i])) {
          // $file[$i] = array('file'=>null, 'source'=>null, 'size'=>null);
        }
      }

    } else if ($w == 'r') {
      if (strstr($write['wr_option'], 'secret')) {
        $is_secret = true;
        $secret_checked = 'checked';
      }

      $password_required = "required";

      for ($i=1; $i<=G5_LINK_COUNT; $i++) {
        $write['wr_link'.$i] = $this->get_text($write['wr_link'.$i]);
      }
    }

    $this->set_session('ss_bo_table', $bo_table);
    $this->set_session('ss_wr_id', $wr_id);

    $subject = "";
    if (isset($write['wr_subject'])) {
      $subject = str_replace("\"", "&#034;", $this->get_text($this->cut_str($write['wr_subject'], 255), 0));
    }

    $content = '';
    if ($w == '') {
      $content = $this->html_purifier($board['bo_insert_content']);
    } else if ($w == 'r') {
      if (!strstr($write['wr_option'], 'html')) {
          $content = "\n\n\n &gt; "
                  ."\n &gt; "
                  ."\n &gt; ".str_replace("\n", "\n> ", $this->get_text($write['wr_content'], 0))
                  ."\n &gt; "
                  ."\n &gt; ";

      }
    } else {
      $content = $this->get_text($write['wr_content'], 0);
    }

    $upload_max_filesize = number_format($board['bo_upload_size']) . ' 바이트';
    $width = $board['bo_table_width'];
    if ($width <= 100)
      $width .= '%';
    else
      $width .= 'px';

    $captcha_html = '';
    $captcha_js   = '';
    $is_use_captcha = ((($board['bo_use_captcha'] && $w !== 'u') || $is_guest) && !$is_admin) ? 1 : 0;

    if ($is_use_captcha) {
      $captcha_html = $this->captcha_html();
      //$captcha_js   = $this->chk_captcha_js();
    }

    $is_dhtml_editor = false;
    $is_dhtml_editor_use = false;
    $editor_content_js = '';
    if(!$this->is_mobile())
      $is_dhtml_editor_use = true;

    // 모바일에서는 G5_IS_MOBILE_DHTML_USE 설정에 따라 DHTML 에디터 적용
    if ($config['cf_editor'] && $is_dhtml_editor_use && $board['bo_use_dhtml_editor'] && $member['mb_level'] >= $board['bo_html_level']) {
      $is_dhtml_editor = true;
      if ( $w == 'u' && (! $is_member || ! $is_admin || $write['mb_id'] !== $member['mb_id']) ){
        // kisa 취약점 제보 xss 필터 적용
        $content = $this->get_text($this->html_purifier($write['wr_content']), 0);
      }
      if(is_file(G5_EDITOR_PATH.'/'.$config['cf_editor'].'/autosave.editor.js'))
        $editor_content_js = '<script src="'.G5_EDITOR_URL.'/'.$config['cf_editor'].'/autosave.editor.js"></script>'.PHP_EOL;
    }
    //$editor_html = editor_html('wr_content', $content, $is_dhtml_editor);
    //$editor_js = '';
    //$editor_js .= get_editor_js('wr_content', $is_dhtml_editor);
    //$editor_js .= chk_editor_js('wr_content', $is_dhtml_editor);

    // 임시 저장된 글 수
    $autosave_count = $this->autosave_count($member['mb_id']);
    $uid = $this->get_uniqid();
    $result = array();
    $result['title'] = $g5['title'];
    $result['uid'] = $uid;
    $result['w'] = $w;
    $result['bo_table'] = $bo_table;
    $result['wr_id'] = $wr_id;
    $result['sca'] = $sca;
    $result['sfl'] = $sfl;
    $result['stx'] = $stx;
    $result['stp'] = $stp;
    $result['sst'] = $sst;
    $result['sod'] = $sod;
    $result['page'] = $page;
    $result['file_count'] = $file_count;
    $result['ca_name'] = $ca_name;
    $result['link_count'] = $link_count;
    $result['is_notice'] = $is_notice;
    $result['is_html'] = $is_html;
    $result['is_secret'] = $is_secret;
    $result['is_mail'] = $is_mail;
    $result['is_dhtml_editor'] = $is_dhtml_editor;
    $result['is_category'] = $is_category;
    $result['upload_max_filesize'] = $upload_max_filesize;
    $result['is_name'] = $is_name;
    $result['is_password'] = $is_password;
    $result['is_email'] = $is_email;
    $result['is_homepage'] = $is_homepage;
    $result['option'] = $option;
    $result['is_member'] = $is_member;
    $result['editor_content_js'] = $editor_content_js;
    $result['autosave_count'] = $autosave_count;
    $result['is_name'] = $is_name;
    $result['is_password'] = $is_password;
    $result['is_email'] = $is_email;
    $result['is_homepage'] = $is_homepage;
    $result['is_member'] = $is_member;
    $result['autosave_count'] = $autosave_count;
    $result['write_min'] = $write_min;
    $result['write_max'] = $write_max;
    $result['editor_html'] = $editor_html;
    $result['is_link'] = $is_link;
    $result['is_file'] = $is_file;
    $result['is_file_content'] = $is_file_content;
    $result['file'] = $file;
    $result['subject'] = $subject;
    $result['is_use_captcha'] = $is_use_captcha;
    $result['captcha_html'] = $captcha_html;
    $result['html_checked'] = $html_checked;
    $result['html_value'] = $html_value;
    $result['secret_checked'] = $secret_checked;
    $result['is_admin'] = $is_admin;
    $result['is_guest'] = $is_guest;
    $result['write'] = $write;
    $result['width'] = $width;
    $result['notice_checked'] = $notice_checked;
    $result['recv_email_checked'] = $recv_email_checked;
    $result['category_option'] = $category_option;
    $result['name'] = $name;
    $result['email'] = $email;
    $result['homepage'] = $homepage;
    $result['option'] = $option;
    // $arr = get_defined_vars();
    // $filter = [
    //   'uid', 'w', 'bo_table', 'wr_id', 'sca', 'sfl',' stx', 'stp', 'sst', 'sod', 'page', 'file_count', 'ca_name', 'link_count', 
    //   'is_notice', 'is_html', 'is_secret', 'is_mail', 'is_dhtml_editor', 'is_category', 'upload_max_filesize', 
    //   'is_name', 'is_password', 'is_email', 'is_homepage', 'option', 'is_member', 'editor_content_js', 'autosave_count',
    //   'write_min', 'write_max', 'editor_html', 'is_link', 'is_file', 'is_file_content', 'file', 'subject', 
    //   'is_use_captcha', 'captcha_html', 'html_checked', 'html_value', 'secret_checked', 'is_admin',  'is_guest',
    //   'write', 'width', 'notice_checked', 'recv_email_checked', 'category_option', 'name', 'email', 'homepage', 'option'
    // ];
    // foreach ( $arr as $key => $value ) {
    //   if(!in_array($key, $filter)) {
    //     unset($arr[$key]);
    //     //$data[$key] = $value;
    //   }
    // }
    return $this->data_encode($result);
  }
}