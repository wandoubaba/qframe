<?php
namespace app\admin\controller;

use app\admin\controller\Base as AdminBase;

class Login extends AdminBase
{
    public function index()
    {
        return $this->view->fetch('index/login');
    }
}
