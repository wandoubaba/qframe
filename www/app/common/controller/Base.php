<?php
namespace app\common\controller;

use think\Controller;

class Base extends Controller
{
    public function _initialize()
	{
		// 继承父类的_initialize()方法
		parent::_initialize();
	}
}
