<?php
trait content {
  public function content () {
    parse_str(parse_url($_SERVER["REQUEST_URI"],PHP_URL_QUERY), $query); // GET
    $co_id = isset($query['co_id']) ? preg_replace('/[^a-z0-9_]/i', '', $query['co_id']) : 0;
    $co_seo_title = isset($query['co_seo_title']) ? $this->clean_xss_tags($query['co_seo_title'], 1, 1) : '';
    
    // 내용
    if($co_seo_title){
      $co = $this->get_content_by_field($g5['content_table'], 'content', 'co_seo_title', $this->generate_seo_title($co_seo_title));
      $co_id = isset($co['co_id']) ? $co['co_id'] : 0;
    } else {
      $co = $this->get_content_db($co_id);
    }

    if( ! (isset($co['co_seo_title']) && $co['co_seo_title']) && isset($co['co_id']) && $co['co_id'] ){
      $this->seo_title_update($g5['content_table'], $co['co_id'], 'content');
    }
    $result = array();
    $result['co'] = $co;
    return $this->data_encode($result);
  }
}