<?php
namespace app\admin\controller;

use app\admin\controller\Base as AdminBase;

class Example extends AdminBase
{
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
