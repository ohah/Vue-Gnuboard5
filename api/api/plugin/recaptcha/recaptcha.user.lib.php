<?php
trait recapthca {
    // 캡챠 HTML 코드 출력
    public function captcha_html($class="captcha") {
        $config = $this->config;
        $html = '<fieldset id="captcha" class="captcha recaptcha">';
        $html .= '<script src="https://www.google.com/recaptcha/api.js?hl=ko"></script>';
        $html .= '<script src="'.G5_CAPTCHA_URL.'/recaptcha.js"></script>';
        $html .= '<div class="g-recaptcha" data-sitekey="'.$config['cf_recaptcha_site_key'].'"></div>';
        $html .= '</fieldset>';
        return $html;
    }

    public function chk_captcha(){
        $config = $this->config;
        $resp = null;
        if ( isset($_POST["g-recaptcha-response"]) && !empty($_POST["g-recaptcha-response"]) ) {
            $reCaptcha = new ReCaptcha_GNU( $config['cf_recaptcha_secret_key'] );
            $resp = $reCaptcha->verify($_POST["g-recaptcha-response"], $_SERVER["REMOTE_ADDR"]);
        }

        if( ! $resp ){
            return false;
        }

        if ($resp != null && $resp->success) {
            return true;
        }
        return false;
    }
}