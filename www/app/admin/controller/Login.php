<?php
namespace app\admin\controller;

use app\admin\controller\Base;
use think\captcha\Captcha;	// 用于生成验证码

class Login extends Base
{
    public function index()
    {
        return $this->view->fetch();
    }
}
