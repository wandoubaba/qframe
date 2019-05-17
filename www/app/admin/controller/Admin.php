<?php
namespace app\admin\controller;

use app\admin\controller\Base as AdminBase;
use app\admin\model\Admin as AdminModel;
use app\admin\model\Role as RoleModel;
use think\Request;
use think\Loader;
use think\Db;
use think\Session;
use app\common\model\Res;

class Admin extends AdminBase
{
	// 渲染管理员列表视图
	public function index()
	{
		$this->view->assign('pagetitle','管理员管理');
		$adminlist = $this->getMyAdminList();
		$this->view->assign('list',$adminlist);
		$this->view->assign('count',count($adminlist));
		// 渲染页面
		return $this->view->fetch('system/admin');
	}

	// 渲染添加管理员视图
	public function add()
	{
		$this->view->assign('pagetitle', '添加管理员');
		// 准备role列表
		$role = RoleModel::all();
		foreach($role as $n=>$val) {
			// array_column只获取对象数组中的menu_id列
			// array_diff返回(数组1-数组2)的差集
			if(Session::has('admin_menus') && !empty(array_diff(
				array_column($val->menus,'menu_id'), 
				array_column(Session::get('admin_menus'), 'menu_id')))
			) {
				// 过滤掉权限高于当前权限的角色
				unset($role[$n]);
			} else {
				$role[$n]['child'] = $val->menus;
			}
		}
		$this->view->assign('list',$role);
		
		return $this->view->fetch('system/admin_add');
	}

	public function edit()
	{
		$this->view->assign('pagetitle', '编辑管理员');

		$id = input('?id') ? input('id') : 0;
    	$admin = AdminModel::get($id);
    	if($admin) {
			// 准备role列表
			$role = RoleModel::all();
			foreach($role as $n=>$val) {
				// array_column只获取对象数组中的menu_id列
				// array_diff返回(数组1-数组2)的差集
				if(Session::has('admin_menus') && !empty(array_diff(
					array_column($val->menus,'menu_id'), 
					array_column(Session::get('admin_menus'), 'menu_id')))
				) {
					// 过滤掉权限高于当前权限的角色
					unset($role[$n]);
				} else {
					$role[$n]['child'] = $val->menus;
				}
			}
			$this->view->assign('list',$role);
			$this->view->assign('admin', $admin);
    	} else {
    		$this->error('参数错误，将转至“添加”页','admin/adminControl/admin_add');
    	}
    	return $this->view->fetch('system/admin_edit');
	}

	public function password()
	{
		$this->view->assign('pagetitle','修改管理员密码');
		$id = input('?id') ? input('id') : 0;
		$admin = AdminModel::get($id);
		if(!$admin) {
			$this->error('参数错误');
			return;
		}
		$this->view->assign('admin',$admin);
		return $this->view->fetch('system/admin_password');
	}

	/**
	 * 执行添加管理员操作
	 * @return [type] [description]
	 */
	public function do_admin_add()
	{
		$res = new Res;
    	$data = input();
    	$data['admin_status'] = input('?admin_status') ? 1 : 0;
    	// 用验证器对数据进行校验
    	$validate = Loader::validate('Admin');
    	$v = $validate->scene('init')->check($data);
    	$res->data = $data;
		if($v) {
			$admin = new AdminModel;
			if(input('?admin_super') && $data['admin_super']) {
				$res->failed('不允许创建超级管理员');
			} else {
				try{
					$data['admin_id'] = $this->apply_full_global_id_str();
					// 调用save($data)方法可触发修改器
					$res->data_row_count = $admin->isUpdate(false)->allowField(true)->save($data);
					if($res->data_row_count) {
						$res->success();
						$res->data = $admin;
					}
				} catch(\Exception $e) {
					$res->failed($e->getMessage());
				}
			}
		} else {
			$res->failed($validate->getError());
		}
		return $res;
	}

	/**
	 * 执行编辑管理员的操作
	 * @return [type] [description]
	 */
	public function do_admin_edit()
	{
    	$res = new Res;
    	$data = input();
    	$data['admin_status'] = input('?admin_status') ? 1 : 0;	// 单独处理checkbox数据
    	// 用验证器对数据进行校验
    	$validate = Loader::validate('Admin');
    	$admin = AdminModel::get(['admin_id'=>$data['admin_id']]);
		$admin->admin_name = $data['admin_name'];
		$admin->admin_email = $data['admin_email'];
    	$admin->admin_telephone = $data['admin_telephone'];
    	$admin->admin_role_id = $data['admin_role_id'];
    	$admin->admin_status = $data['admin_status'];
    	$admin->admin_description = $data['admin_description'];
    	$v = $validate->scene('nopassword')->check($data);
    	$res->data = $admin;
		if($v) {
			try{
				$res->data_row_count = $admin->allowField(true)->isUpdate(true)->save($admin,['admin_id'=>$data['admin_id']]);
				if($res->data_row_count) {
					$res->success();
				}
			} catch(\Exception $e) {
				$res->failed($e->getMessage());
			}
		} else {
			$res->failed($validate->getError());
		}    	
		return $res;
	}

	/**
	 * 执行修改管理员密码的操作
	 * @return [type] [description]
	 */
	public function do_admin_password()
	{
    	$res = new Res;
    	$data = input();
    	// 用验证器对数据进行校验
    	$validate = Loader::validate('Admin');
    	$admin = AdminModel::get(['admin_id'=>$data['admin_id']]);
    	if($admin->admin_account=='admin' || $data['admin_account']=='admin') {
    		$res->failed('不允许操作admin用户');
    	} else {
    		$admin->admin_account = $data['admin_account'];
    		$admin->admin_password = $data['admin_password'];
	    	$v = $validate->scene('password')->check($data);
	    	$res->data = $admin;
			if($v) {
				try{
					$res->data_row_count = $admin->allowField(true)->isUpdate(true)->save($admin,['admin_id'=>$data['admin_id']]);
					if($res->data_row_count) {
						$res->success();
					}
				} catch(\Exception $e) {
					$res->failed($e->getMessage());
				}
			} else {
				$res->failed($validate->getError());
			}
    	}    	
		return $res;
	}

	/**
	 * 执行切换管理员可用状态操作
	 * @param  [type] $id [管理员ID]
	 * @return [type]     [description]
	 */
	public function do_admin_status($id)
	{
    	$res = new Res;
    	$id = input('id');	// 获取页面传入的id
		// 用异常处理的方式执行以下操作
		try {
			$admin = AdminModel::get($id);	// 查询id对应的记录
			$admin['admin_status'] = $admin->getData('admin_status')==1 ? 0 : 1;
			if( ($admin->admin_super==1||$admin->admin_account=='admin') && Session::get('admin_infor')['admin_account']!='admin') {
				$res->failed('不允许对超级管理进行操作');
			} else {
				$res->data_row_count = $admin->isUpdate(true)->allowField(true)->save(['admin_status' => $admin->getData('admin_status')],['admin_id'=>$id]);
				if($res->data_row_count) {
					$res->success($admin['admin_account'].'管理员已切换为“'.$admin['admin_status'].'”状态');	// 生成友好的提示信息
				}
			}
			$res->data = $admin;
		} catch(\Exception $e) {
			$res->failed($e->getMessage());	//将异常信息赋值给$message
		}
		return $res;
	}

	/**
	 * 执行删除管理员的操作
	 * @param  [type] $id [管理员ID]
	 * @return [type]     [description]
	 */
	public function do_admin_delete($id)
	{
		$res = new Res;
    	try {
    		$res->data = AdminModel::get($id);
    		if($res->data) {
    			if($res->data['admin_super']||$res->data['admin_account']=='admin') {
    				$res->failed('不允许删除超级管理员');
    			} else {
					$res->data_row_count = AdminModel::destroy(['admin_id'=>$id]);	// 执行软删除
					// 软删除时不删除对应的关联记录
					$res->success();
				}
			} else {
				$res->failed('不存在该记录');
			}
		} catch(\Exception $e) {
			$res->failed($e->getMessage());	// 获取异常信息
		}
		return $res;
	}

	/**
	 * 执行批量删除管理员的操作
	 * @return [type] [description]
	 */
	public function do_admin_multidelete()
	{
    	$res = new Res;
    			// 取得前台提交的module_ids数组并保存到数组中
		$ids = input('?ids') ? input('ids/a') : [];
		// 后台也判断一下提交的module_id个数
		if(count($ids)==0) {
			// 如果为0，则直接返回提示信息给前台
			$res->failed('没有选中任何数据');
		} else {
			// 用异常处理机制执行以下操作
			try {
				// 遍历$module_ids数组中的元素
				foreach($ids as $id) {
					$res_i = $this->do_admin_delete($id);
					if($res_i->status) {
						$res->data_row_count += 1;
					} else {
						$res->append_message(" ".$res_i->message);
					}
				}
				if(count($ids)==$res->data_row_count) {
					$res->success($res->data_row_count."条数据被成功删除");
				} else {
					$res->failed("<br/>".$res->data_row_count." of ".count($ids)."条数据删除成功。");	// 人性化提示，反馈操作数量
					$res->append_message("<br/>".(count($ids)-$res->data_row_count)." of ".count($ids)."条数据删除失败。");	// 人性化提示，反馈操作数量
				}
				
			} catch(\Exception $e) {
				// 如果操作有异常，反馈失败记录数量和错误信息
				$res->failed((count($authority_ids)-count($deleted_ids))."条数据删除失败，错误信息：".$e->getMessage());
			}
		}
		return $res;
	}
}