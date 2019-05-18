<?php
namespace app\admin\controller;

use app\admin\controller\Base as AdminBase;
use think\Db;

class Index extends AdminBase
{
    public function index()
    {
        $this->view->assign('pagetitle','欢迎');
    	$mysqlversion = Db::query('select version() as ver;')[0]['ver'];
        $this->view->assign('mysqlversion',$mysqlversion);
        return $this->view->fetch('index/home');
    }
}
