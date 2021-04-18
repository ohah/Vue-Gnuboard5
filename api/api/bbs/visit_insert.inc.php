<?php
trait visit_insert {
	public function visit_insert() {
		global $g5;
		if ($this->get_cookie('ck_visit_ip') != $_SERVER['REMOTE_ADDR']) {
			$this->set_cookie('ck_visit_ip', $_SERVER['REMOTE_ADDR'], 86400); // 하루동안 저장
			$tmp_row = $this->sql_fetch(" select max(vi_id) as max_vi_id from {$g5['visit_table']} ");
			$vi_id = $tmp_row['max_vi_id'] + 1;

			// $_SERVER 배열변수 값의 변조를 이용한 SQL Injection 공격을 막는 코드입니다. 110810
			$remote_addr = $_SERVER['REMOTE_ADDR'];
			$referer = "";
			if (isset($_SERVER['HTTP_REFERER']))
				$referer = $this->clean_xss_tags(strip_tags($_SERVER['HTTP_REFERER']));
			$user_agent  = $this->clean_xss_tags(strip_tags($_SERVER['HTTP_USER_AGENT']));
			$vi_browser = '';
			$vi_os = '';
			$vi_device = '';
			if(version_compare(phpversion(), '5.3.0', '>=') && defined('G5_BROWSCAP_USE') && G5_BROWSCAP_USE) {
				// Browscap 캐시 파일이 있으면 실행
				if(defined('G5_VISIT_BROWSCAP_USE') && G5_VISIT_BROWSCAP_USE && is_file(G5_DATA_PATH.'/cache/browscap_cache.php')) {
					include_once(G5_PLUGIN_PATH.'/browscap/Browscap.php');

					$browscap = new phpbrowscap\Browscap(G5_DATA_PATH.'/cache');
					$browscap->doAutoUpdate = false;
					$browscap->cacheFilename = 'browscap_cache.php';

					$info = $browscap->getBrowser($_SERVER['HTTP_USER_AGENT']);

					$vi_browser = $info->Comment;
					$vi_os = $info->Platform;
					$vi_device = $info->Device_Type;
				}
			}
			$sql = " insert {$g5['visit_table']} ( vi_id, vi_ip, vi_date, vi_time, vi_referer, vi_agent, vi_browser, vi_os, vi_device ) values ( '{$vi_id}', '{$remote_addr}', '".G5_TIME_YMD."', '".G5_TIME_HIS."', '{$referer}', '{$user_agent}', '{$vi_browser}', '{$vi_os}', '{$vi_device}' ) ";

			$result = $this->sql_result($sql);
			// 정상으로 INSERT 되었다면 방문자 합계에 반영
			if ($result) {
				$sql = " insert {$g5['visit_sum_table']} ( vs_count, vs_date) values ( 1, '".G5_TIME_YMD."' ) ";
				$result = $this->sql_result($sql);

				// DUPLICATE 오류가 발생한다면 이미 날짜별 행이 생성되었으므로 UPDATE 실행
				if (!$result) {
					$sql = " update {$g5['visit_sum_table']} set vs_count = vs_count + 1 where vs_date = '".G5_TIME_YMD."' ";
					$result = $this->sql_query($sql);
				}

				// INSERT, UPDATE 된건이 있다면 기본환경설정 테이블에 저장
				// 방문객 접속시마다 따로 쿼리를 하지 않기 위함 (엄청난 쿼리를 줄임 ^^)

				// 오늘
				$sql = " select vs_count as cnt from {$g5['visit_sum_table']} where vs_date = '".G5_TIME_YMD."' ";
				$row = $this->sql_fetch($sql);
				$vi_today = isset($row['cnt']) ? $row['cnt'] : 0;

				// 어제
				$sql = " select vs_count as cnt from {$g5['visit_sum_table']} where vs_date = DATE_SUB('".G5_TIME_YMD."', INTERVAL 1 DAY) ";
				$row = $this->sql_fetch($sql);
				$vi_yesterday = isset($row['cnt']) ? $row['cnt'] : 0;

				// 최대
				$sql = " select max(vs_count) as cnt from {$g5['visit_sum_table']} ";
				$row = $this->sql_fetch($sql);
				$vi_max = isset($row['cnt']) ? $row['cnt'] : 0;

				// 전체
				$sql = " select sum(vs_count) as total from {$g5['visit_sum_table']} ";
				$row = $this->sql_fetch($sql);
				$vi_sum = isset($row['total']) ? $row['total'] : 0;

				$visit = '오늘:'.$vi_today.',어제:'.$vi_yesterday.',최대:'.$vi_max.',전체:'.$vi_sum;

				// 기본설정 테이블에 방문자수를 기록한 후
				// 방문자수 테이블을 읽지 않고 출력한다.
				// 쿼리의 수를 상당부분 줄임
				$this->sql_query(" update {$g5['config_table']} set cf_visit = '{$visit}' ");
			}
		}
	}
}