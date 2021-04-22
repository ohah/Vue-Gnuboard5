<?php
trait formmail {
  public function formmail ($email, $mb_id) {
    global $g5;
    $member = $this->member;
    $config = $this->config;
    $is_admin = $this->is_admin;
    $is_guest = $this->is_guest;
    $is_member = $this->is_member;
    $qstr = '';

    if (!$config['cf_email_use'])
      $this->alert('환경설정에서 \"메일발송 사용\"에 체크하셔야 메일을 발송할 수 있습니다.\\n\\n관리자에게 문의하시기 바랍니다.');

    if (!$is_member && $config['cf_formmail_is_member'])
      $this->alert('회원만 이용하실 수 있습니다.');

    $mb_id = isset($mb_id) ? $this->get_search_string($mb_id) : '';

    if ($is_member && !$member['mb_open'] && $is_admin != "super" && $member['mb_id'] != $mb_id)
      $this->alert('자신의 정보를 공개하지 않으면 다른분에게 메일을 보낼 수 없습니다.\\n\\n정보공개 설정은 회원정보수정에서 하실 수 있습니다.');

    if ($mb_id) {
      $mb = $this->get_member($mb_id);
      if (!$mb['mb_id'])
        $this->alert('회원정보가 존재하지 않습니다.\\n\\n탈퇴한 회원일 수 있습니다.');

      if (!$mb['mb_open'] && $is_admin != "super")
        $this->alert('정보공개를 하지 않았습니다.');
    }

    $sendmail_count = (int)$this->get_session('ss_sendmail_count') + 1;
    if ($sendmail_count > 3)
      $this->alert('한번 접속후 일정수의 메일만 발송할 수 있습니다.\\n\\n계속해서 메일을 보내시려면 다시 로그인 또는 접속하여 주십시오.');

    
    $email_enc = new str_encrypt();
    $email_dec = $email_enc->decrypt($email);
    
    $email = $this->get_email_address($email_dec);
    if(!$email)
      $this->alert('이메일이 올바르지 않습니다.');
    
    $email = $email_enc->encrypt($email);
    
    if (!$name)
      $name = $email;
    else
      $name = $this->get_text(stripslashes($name), true);
    
    if (!isset($type))
      $type = 0;
    
    $type_checked[0] = $type_checked[1] = $type_checked[2] = "";
    $type_checked[$type] = 'checked';
      
    $result = array();
    $result['title'] = '메일 쓰기';
    $result['captcha_html'] = $this->captcha_html();
    return $this->data_encode($result);
  }
  public function formmail_send () {
    @extract($_POST);
    if (!$config['cf_email_use'])
      $this->alert('환경설정에서 "메일발송 사용"에 체크하셔야 메일을 발송할 수 있습니다.\\n\\n관리자에게 문의하시기 바랍니다.');

    if (!$is_member && $config['cf_formmail_is_member'])
      $this->alert('회원만 이용하실 수 있습니다.');

    $email_enc = new str_encrypt();
    $to = $email_enc->decrypt($to);

    if (!$this->chk_captcha()) {
      $this->alert('자동등록방지 숫자가 틀렸습니다.');
    }

    if (!preg_match("/([0-9a-zA-Z_-]+)@([0-9a-zA-Z_-]+)\.([0-9a-zA-Z_-]+)/", $to)){
      $this->alert('E-mail 주소가 형식에 맞지 않아서, 메일을 보낼수 없습니다.');
    }

    $file = array();
    for ($i=1; $i<=$attach; $i++) {
      if ($_FILES['file'.$i]['name'])
        $file[] = attach_file($_FILES['file'.$i]['name'], $_FILES['file'.$i]['tmp_name']);
    }

    $content = stripslashes($content);
    if ($type == 2) {
      $type = 1;
      $content = str_replace("\n", "<br>", $content);
    }

    // html 이면
    if ($type) {
      $current_url = G5_URL;
      $mail_content = '<!doctype html><html lang="ko"><head><meta charset="utf-8"><title>메일보내기</title><link rel="stylesheet" href="'.$current_url.'/style.css"></head><body>'.$content.'</body></html>';
    }
    else
      $mail_content = $content;

    $this->mailer($fnick, $fmail, $to, $subject, $mail_content, $type, $file);

    // 임시 첨부파일 삭제
    if(!empty($file)) {
      foreach($file as $f) {
        @unlink($f['path']);
      }
    }
    $result = array();
    return $this->data_encode($result);
  }
}