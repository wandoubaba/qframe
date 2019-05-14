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

	/**
	 * 验证输入的验证码
	 * @param  [type] $str [输入的字符串]
	 * @return [type]      [app\common\model\Res对象实例]
	 */
	public function check_captcha($str)
	{
		$res = new Res;
		if(captcha_check($str)) {
			$res->success('验证通过','验证码输入正确');
		} else {
			$res->failed('验证失败','验证码输入不正确');
		}
		return $res;
	}
}