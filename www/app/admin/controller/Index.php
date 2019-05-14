<?php
namespace app\admin\controller;

use app\admin\controller\Base as AdminBase;

class Index extends AdminBase
{
    public function index()
    {
        return $this->view->fetch();
    }
}
