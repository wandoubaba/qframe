<?php
namespace app\admin\model;

use app\common\model\BaseCU;

class Menu extends BaseCU
{
	// 定义数据表中menu_visible字段获取器
	public function getMenuVisibleAttr($value) {
		$visible = [
			0	=>	'隐藏',
			1	=>	'显示'
		];
		return $visible[$value];
	}

	public function getMenuFatherIdAttr($value) {
		$result = '';
		if($value != 0) {
			$result = $this->where(['menu_id'=>$value])->value('menu_name');
		}
		return $result;
	}

	// 通过role_menu表关联role
	public function roles()
	{
		return $this->belongsToMany('Role','app\admin\model\RoleMenu');
	}
}