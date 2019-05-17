<?php
namespace app\api\controller;

use app\api\controller\Base as ApiBase;
use think\captcha\Captcha as ThinkCaptcha;
use app\common\model\Res;

class Captcha extends ApiBase
{
	/**
	 * 生成验证码图片
	 * @param  integer $fontsize [字符字号，如25]
	 * @param  integer $length   [字符数，如5]
	 * @return [type]            [description]
	 */
	public function get_captcha_image($fontsize=24,$length=4)
	{
		$config = [
    		'useCurve'	=>	false,
    		'length'	=>	$length,
    		'fontSize'	=>	$fontsize,
    	];
		$captcha = new ThinkCaptcha($config);
		return $captcha->entry();
		// return CaptchaService::get_captcha_image($fontsize, $length);
	}
}