<?php
// 문자열 암복호화
class str_encrypt {
  var $salt;
  var $lenght;

  function __construct($salt='') {
    if(!$salt)
      $this->salt = md5(preg_replace('/[^0-9A-Za-z]/', substr(G5_MYSQL_USER, -1), $_SERVER['SERVER_SOFTWARE'].$_SERVER['DOCUMENT_ROOT']));
    else
      $this->salt = $salt;

    $this->length = strlen($this->salt);
  }
  function encrypt($str) {
    $length = strlen($str);
    $result = '';

    for($i=0; $i<$length; $i++) {
        $char    = substr($str, $i, 1);
        $keychar = substr($this->salt, ($i % $this->length) - 1, 1);
        $char    = chr(ord($char) + ord($keychar));
        $result .= $char;
    }

    return strtr(base64_encode($result) , '+/=', '._-');
  }
  function decrypt($str) {
    $result = '';
    $str    = base64_decode(strtr($str, '._-', '+/='));
    $length = strlen($str);

    for($i=0; $i<$length; $i++) {
        $char    = substr($str, $i, 1);
        $keychar = substr($this->salt, ($i % $this->length) - 1, 1);
        $char    = chr(ord($char) - ord($keychar));
        $result .= $char;
    }

    return $result;
  }
}