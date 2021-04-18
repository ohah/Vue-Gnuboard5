<?php
if (!defined('_GNUBOARD_')) exit;

//include_once(dirname(__FILE__).'/URI/uri.class.php');

trait urllib{
  // 짧은 주소 형식으로 만들어서 가져온다.
  public function get_pretty_url($folder, $no='', $query_string='', $action='') {
    global $g5;
    $config = $this->config;    
    $boards = $this->get_board_names();
    $segments = array();
    $url = $add_query = '';

    if( $url = run_replace('get_pretty_url', $url, $folder, $no, $query_string, $action) ){
      return $url;
    }

    // use shortten url
    if($config['cf_bbs_rewrite']) {
      $segments[0] = G5_URL;
      if( $folder === 'content' && $no ) {     // 내용관리
        $segments[1] = $folder;
        if( $config['cf_bbs_rewrite'] > 1 ){
          $get_content = $this->get_contnt_db($no, true);
          $segments[2] = $get_content['co_seo_title'] ? urlencode($get_content['co_seo_title']).'/' : urlencode($no);
        } else {
          $segments[2] = urlencode($no);
        }
      } else if(in_array($folder, $boards)) {     // 게시판
        
        $segments[1] = $folder;
        if($no) {
          if( $config['cf_bbs_rewrite'] > 1 ){
            $write_table = $g5['write_prefix'].$folder;
            $get_wrtie = $this->get_write($g5['write_prefix'].$folder, $no, true);
            $segments[2] = $get_write['wr_seo_title'] ? urlencode($get_write['wr_seo_title']).'/' : urlencode($no);
          } else {
            $segments[2] = urlencode($no);
          }
        } else if($action) {
          $segments[2] = urlencode($action);
        }
      } else {
        $segments[1] = $folder;
        if($no) {
          $no_array = explode("=", $no);
          $no_value = end($no_array);
          $segments[2] = urlencode($no_value);
        }
      }
      if($query_string) {
        // If the first character of the query string is '&', replace it with '?'.
        if(substr($query_string, 0, 1) == '&') {
          $add_query = preg_replace("/\&amp;/", "?", $query_string, 1);
        } else {
          $add_query = '?'. $query_string;
        }
      }
    } else { // don't use shortten url
      if(in_array($folder, $boards)) {
        $url = G5_BBS_URL. '/board.php?bo_table='. $folder;
        if($no) {
          $url .= '&wr_id='. $no;
        }
        if($query_string) {
          if(substr($query_string, 0, 1) !== '&') {
            $url .= '&';
          }
          $url .= $query_string;
        }
      } else {
          $url = G5_BBS_URL. '/'.$folder.'.php';
          if($no) {
            $url .= ($folder === 'content') ? '?co_id='. $no : '?'. $no;
          }
          if($query_string) {
            $url .= ($no ? '?' : '&'). $query_string;
          }
      }
      $segments[0] = $url;
    }
    
    return str_replace(".php" , "", implode('/', $segments).$add_query);
  }

  public function short_url_clean($string_url, $add_qry='') {
    $config = $this->config;
    global $g5;
    if( isset($config['cf_bbs_rewrite']) && $config['cf_bbs_rewrite'] ){
      $string_url = str_replace('&amp;', '&', $string_url);
      $url=parse_url($string_url);
      $page_name = basename($url['path'],".php");

      if( stripos(preg_replace('/^https?:/i', '', $string_url), preg_replace('/^https?:/i', '', G5_BBS_URL)) === false || ! in_array($page_name, $array_page_names) ){   //게시판이 아니면 리턴
        return run_replace('false_short_url_clean', $string_url, $url, $page_name, $array_page_names);
      }

      $return_url = '';
      parse_str($url['query'], $vars);

      if( $page_name === 'write' ){
        $vars['action'] = 'write';
        $allow_param_keys = array('bo_table'=>'', 'action'=>'');
      } else if( $page_name === 'content' ){
        $vars['action'] = 'content';
        $allow_param_keys = array('action'=>'', 'co_id'=>'');
      } else {
        $allow_param_keys = array('bo_table'=>'', 'wr_id'=>'');
      }
      $s = array();
      foreach( $allow_param_keys as $key=>$v ){
        if( !isset($vars[$key]) || empty($vars[$key]) ) continue;
        $s[$key] = $vars[$key];
      }

      if( $config['cf_bbs_rewrite'] > 1 && $page_name === 'board' && (isset($s['wr_id']) && $s['wr_id']) && (isset($s['bo_table']) && $s['bo_table']) ) {
        $get_write = $this->sql_fetch("SELECT * FROM {$write_table} WHERE wr_id = ?",$s['wr_id']);
        if( $get_write['wr_seo_title'] ){
          unset($s['wr_id']);
          $s['wr_seo_title'] = urlencode($get_write['wr_seo_title']).'/';
        }
      }

      $fragment = isset($url['fragment']) ? '#'.$url['fragment'] : '';

      $host = G5_URL;

      if( isset($url['host']) ){
        $str_path = isset($url['path']) ? $url['path'] : '';
        $http = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on') ? 'https://' : 'http://';
        $port = (isset($url['port']) && ($url['port']!==80 || $url['port']!==443)) ? ':'.$url['port'] : '';
        $host = $http.$url['host'].$port.str_replace($array_file_paths, '', $str_path);
      }

      $add_param = '';

      if( $result = array_diff_key($vars, $allow_param_keys ) ){
        $add_param = '?'.http_build_query($result,'','&');
      }

      if( $add_qry ){
        $add_param .= $add_param ? '&'.$add_qry : '?'.$add_qry;
      }

      foreach($s as $k => $v) { $return_url .= '/'.$v; }

      return $host.$return_url.$add_param.$fragment;
    }

    return str_replace(".php", "", $string_url);
  }

  public function correct_goto_url($url){
    if( substr($url, -1) !== '/' ){
      return $url.'/';
    }

    return $url;
  }

  public function generate_seo_title($string, $wordLimit=G5_SEO_TITEL_WORD_CUT){
    $separator = '-';
    
    if($wordLimit != 0){
      $wordArr = explode(' ', $string);
      $string = implode(' ', array_slice($wordArr, 0, $wordLimit));
    }

    $quoteSeparator = preg_quote($separator, '#');

    $trans = array(
      '&.+?;'                    => '',
      '[^\w\d _-]'            => '',
      '\s+'                    => $separator,
      '('.$quoteSeparator.')+'=> $separator
    );

    $string = strip_tags($string);

    if( function_exists('mb_convert_encoding') ){
      $string = mb_convert_encoding($string, 'UTF-8', 'UTF-8');
    }

    foreach ($trans as $key => $val){
      $string = preg_replace('#'.$key.'#iu', $val, $string);
    }

    $string = strtolower($string);

    return trim(trim($string, $separator));
  }

  public function exist_seo_url($type, $seo_title, $write_table, $sql_id=0){
    global $g5;
    $exists_title = '';
    $sql_id = preg_replace('/[^a-z0-9_\-]/i', '', $sql_id);
    // 영카트 상품코드의 경우 - 하이픈이 들어가야 함
    if( $type === 'bbs' ){
      $row = $this->sql_fetch("SELECT wr_seo_title FROM {$write_table} WHERE wr_seo_title = ? AND wr_id <> ? LIMIT 1",[$seo_title, $sql_id]);
      $exists_title = $row['wr_seo_title'];
    } else if ( $type === 'content' ){
      $row = $this->sql_fetch("SELECT co_seo_title FROM {$write_table} WHERE co_seo_title = ? AND co_id <> ? LIMIT 1",[$seo_title, $sql_id]);
      $row = sql_fetch($sql);
      $exists_title = $row['co_seo_title'];
    } else {
      //return run_replace('exist_check_seo_title', $seo_title, $type, $write_table, $sql_id);
    }

    if ($exists_title)
      return 'is_exists';
    else
      return '';
  }

  public function exist_seo_title_recursive($type, $seo_title, $write_table, $sql_id=0){
    static $count = 0;

    $seo_title_add = ($count > 0) ? $this->utf8_strcut($seo_title, 200 - ($count+1), '')."-$count" : $seo_title;

    if(!$this->exist_seo_url($type, $seo_title_add, $write_table, $sql_id) ){
      return $seo_title_add;
    }
    
    $count++;

    if( $count > 198 ){
      return $seo_title_add;
    }
    return $this->exist_seo_title_recursive($type, $seo_title, $write_table, $sql_id);
  }

  public function seo_title_update($db_table, $pk_id, $type='bbs') {
    global $g5;
    $pk_id = (int) $pk_id;
    if( $type === 'bbs' ){          
      $write = $this->sql_fetch("SELECT * FROM {$db_table} WHERE wr_id = ?",[$pk_id]);
      if( ! $write['wr_seo_title'] && $write['wr_subject'] ){
        $wr_seo_title = $this->exist_seo_title_recursive('bbs', $this->generate_seo_title($write['wr_subject']), $db_table, $pk_id);
        $this->sql_query("UPDATE {$db_table} SET wr_seo_title = ? WHERE wr_id = ?", [$wr_seo_title, $pk_id]);
        sql_query($sql);
      }
    } else if ( $type === 'content' ){
      $sql = " select * from {$g5['content_table']} where co_id = '$co_id' ";
      $co = $this->sql_fetch("SELECT * FROM {$g5['content_table']} WHERE co_id = ?", [$co_id]);
      if( ! $co['co_seo_title'] && $co['co_subject'] ){
        $co_seo_title = $this->exist_seo_title_recursive('content', $this->generate_seo_title($co['co_subject']), $db_table, $pk_id);
        $this->sql_fetch("UPDATE {$db_table} SET co_seo_title = ? WHERE co_id = ?", [$co_se_title, $pk_id]);
        sql_query($sql);
      }
    }
  }
}