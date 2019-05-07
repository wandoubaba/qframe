<?php
namespace app\common\controller;

use think\Controller;
use think\captcha\Captcha;
use app\common\model\District as DistrictModel;
use app\common\model\Res;

class Base extends Controller
{
    public function _initialize()
	{
		// 继承父类的_initialize()方法
		parent::_initialize();
		$this->view->assign('title','qFrame');
        $this->view->assign('minititle','qF');
	}
}
