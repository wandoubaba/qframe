<?php
namespace app\common\controller;

use think\Controller;

use app\common\model\District as DistrictModel;

class Base extends Controller
{
    public function _initialize()
	{
		// 继承父类的_initialize()方法
		parent::_initialize();
		$this->view->assign('title','qThink');
        $this->view->assign('minititle','qT');
	}

	/**
     * 根据father_id查询对应的地区数据
     * @param  integer $father_id 上一级地区ID，默认86为查询省级
     * @return [type]             返回数据集
     */
    protected function get_district($father_id)
    {
        $district = new DistrictModel;
        $list = $district->where(['district_father_id'=>$father_id])->order(['district_id'=>'asc'])->select();
        return $list;
    }

    /**
     * 根据district_id查询对应的父级地区数据
     * @param  integer $id 要查询的district_id
     * @return [type]      返回数据集
     */
    protected function get_district_father($id)
    {
        $district = DistrictModel::get($id);
        $father = DistrictModel::get($district->district_father_id);
        return $father;
    }

    /**
     * 指定一个起始father_id，取得该father_id下的所有子district列表
     * @param  [type] $start_father_id [description]
     * @return [type]                  [description]
     */
    protected function get_district_child($start_father_id)
    {
        $district = new DistrictModel;
        $list = $district->where('district_father_id','eq',$start_father_id)->order(['district_id'=>'asc'])->select();
        return $this->get_district_list($list);
    }

    /**
     * 传入一个district列表，取得包含所有子district的列表
     * @param  [type] $list [description]
     * @return [type]       [description]
     */
    protected function get_district_list($list)
    {
        if($list) {
            $district = new DistrictModel;
            foreach($list as $index=>$item) {
                $child = $district->where('district_father_id','eq',$item['district_id'])->order(['district_id'=>'asc'])->select();
                $list[$index]['child'] = $this->get_district_list($child);
            }
        }
        return $list;
    }
}
