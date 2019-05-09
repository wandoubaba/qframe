<?php
namespace app\admin\controller;

use app\admin\controller\Base;

class Login extends Base
{
    public function index()
    {
        return $this->view->fetch('index/login');
    }
}
