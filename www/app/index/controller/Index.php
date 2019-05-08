<?php
namespace app\index\controller;

use app\common\controller\Base;

class Index extends Base
{
    public function index()
    {
        return $this->view->fetch();
    }

    public function chart()
    {
    	$this->view->assign('pagetitle','chart.js演示');
    	return $this->view->fetch();
    }

    public function linkage()
    {
    	$this->view->assign('pagetitle','无限联动演示');
    	return $this->view->fetch();
    }
}
