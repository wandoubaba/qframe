<?php
namespace app\admin\controller;

use app\admin\controller\Base as AdminBase;
use app\admin\model\Menu as MenuModel;
use think\Env;
use app\common\model\Res;

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

    public function controllermethods()
    {
        $this->view->assign('pagetitle', '控制器方法');
        $this->view->assign('list', $this->get_routes('admin'));
        return $this->view->fetch();
    }

    public function test()
    {
        $this->view->assign('pagetitle', '测试页');
        // $menu = new MenuModel;
        // // $routes = $menu->select('menu_route');
        // $routes = array_unique(MenuModel::column('menu_route'));
        // dump($routes);
        // return $this->view->fetch();
        dump($this->get_menu_list());
    }

    public function Ueditor()
    {
        ## TODO:
        #测试Todo
        $this->view->assign('pagetitle','Ueditor');
        return $this->view->fetch();
    }
}
