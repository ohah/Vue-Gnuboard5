<?php
trait recaptcha_inv {
    // 캡챠 HTML 코드 출력
    public function captcha_html($class="captcha") {
        $config = $this->config;
        /*
        #hl=ko 표시는 언어지정가능
        */
        $html = '<fieldset id="captcha" class="captcha invisible_recaptcha">';
        $html .= '<script src="https://www.google.com/recaptcha/api.js?hl=ko"></script>';
        $html .= '<script src="'.G5_CAPTCHA_URL.'/recaptcha.js"></script>';
        $html .= '<div id="recaptcha" class="g-recaptcha" data-sitekey="' . $config['cf_recaptcha_site_key'] . '" data-callback="recaptcha_validate" data-badge="inline" data-size="invisible"></div>';
        $html .= '<script>jQuery("#recaptcha").hide().parent(".invisible_recaptcha").hide().closest(".is_captcha_use").hide();</script>';
        $html .= '</fieldset>';

        return $html;
    }

    public function chk_captcha(){
        $config = $this->config;
        $resp = null;
        if ( isset($_POST["g-recaptcha-response"]) && !empty($_POST["g-recaptcha-response"]) ) {
            $reCaptcha = new ReCaptcha_inv_GNU( $config['cf_recaptcha_secret_key'] );
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