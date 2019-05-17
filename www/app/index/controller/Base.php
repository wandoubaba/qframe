<?php
namespace app\index\controller;

use app\common\controller\Base as CommonBase;

class Base extends CommonBase
{
    public function _initialize()
    {
        // 继承父类的_initialize()方法
        parent::_initialize();
        $this->view->assign('title', $this->get_config('title'));
        $this->view->assign('minititle',$this->get_config('minititle'));
        $this->view->assign('version',$this->get_config('version'));
        $this->view->assign('keywords', $this->get_config('keywords'));
        $this->view->assign('description', $this->get_config('description'));
    }
}
