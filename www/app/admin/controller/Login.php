<?php
namespace app\admin\controller;

use app\common\controller\Base as CommonBase;
use think\captcha\Captcha;
use think\Session;
use think\Request;
use think\Loader;
use app\admin\model\Admin as AdminModel;
use app\admin\model\Menu as MenuModel;
use app\admin\model\Role as RoleModel;
use app\common\model\Res;

class Login extends CommonBase
{
    public function index()
    {
    	$this->view->assign('title', $this->get_config('admin_title'));
        $this->view->assign('minititle',$this->get_config('minititle'));
        $this->view->assign('version',$this->get_config('version'));
        $this->view->assign('keywords', $this->get_config('admin_keywords'));
        $this->view->assign('description', $this->get_config('admin_description'));
        $this->view->assign('footer', $this->get_config('admin_footer'));
        $this->view->assign('beian', $this->get_config('beian'));
        return $this->view->fetch('index/login');
    }

	/**
	 * 执行login操作：
	 * 对比用户名密码，
	 * 成功后将登录信息写入session，
	 * 超级管理员手动设置角色，
	 * 超级管理员的菜单为空，
	 * 记录登录IP、时间和次数
	 * 登录成功后将新的密码hash结果写入数据表
	 * @return [type] [如果登录成功，则自动跳转到admin模块的home页]
	 */
	public function do_login()
	{
		$res = new Res;
		$data = input();
		$res->data = $data;
		if(!captcha_check($data['captcha'])) {
			$res['message'] .= '：验证码不正确';
			return $res;
		}
		$validate = Loader::validate('Admin');
		$v = $validate->scene('login')->check($data);
		if(!$v) {
			$res->message .= $validate->getError();
			return $res;
		}
		// 表单验证通过，可以对用户名密码进行验证了
		try {
			$admin = AdminModel::get(['admin_account'=>$data['admin_account']]);
		} catch(\Exception $e) {
			$res->message .= $e->getMessage();
			return $res;
		}
		if(!$admin) {
			// 用户名错误
			$res->message .= '用户名错误';
			return $res;
		}
		if(!to_encrypt_compare($admin->admin_password, $data['admin_password'], $data['admin_account'])) {
			// 密码错误
			$res->message .= '密码错误';
			// $res->message .= $admin->admin_password;
			return $res;
		}
		if($admin->admin_status != '正常') {
			// 账号已停用
			$res->message .= '账号已禁用';
			return $res;
		}
		// 一切判断全部通过，开始准备账号角色权限信息，用异常处理的方式
		try {
			// admin用户天生就是超级管理员
			$admin->admin_super = $admin->admin_account=='admin' ? 1 : $admin->admin_super;
			// 对超级管理员手动配置角色，对非超级管理员通过数据表关联角色
			$role = $admin->admin_super ? new RoleModel(['role_name'=>'超级管理员']) : $admin->role;
			// 根据角色关联菜单，超级管理手动设置菜单权限为null
			$menus = $admin->admin_super ? null : ($admin->role ? $admin->role->menus : null);
			// 将管理员、角色、菜单写入Session
			Session::set('admin_infor', $admin);	// 账号信息
			Session::set('admin_role', $role);	// 角色信息
			Session::set('admin_menus', $menus);	// 菜单权限
			// 把当前IP、时间、登录次数、新的密码更新到数据库中
			$admin->where(['admin_account'=>$admin['admin_account']])
				->setInc('admin_login_count');
			// 执行Db类的update不会触发修改器，所以要将密码做加密处理后再update
			$admin->where(['admin_account'=>$admin['admin_account']])
				->update([
					'admin_password'	=>	to_encrypt($data['admin_password'],$data['admin_account']),
					'admin_login_ip'	=>	get_client_ip(),
					'admin_login_time'	=>	time()
				]);
			// 返回登录成功信息
			$res->message = '登录成功';
			$res->status = true;
			$res->data = $admin;
		} catch(\Exception $e) {
			$res->message .= $e->getMessage();
		}
		return $res;
	}
}
