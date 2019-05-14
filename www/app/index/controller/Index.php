<?php
namespace app\index\controller;

use app\index\controller\Base as IndexBase;

class Index extends IndexBase
{
    public function index()
    {
    	return $this->view->fetch('sample/index');
    }
}
