<?php
namespace app\common\controller;

use think\Controller;
use app\common\model\Config as ConfigModel;

class Base extends Controller
{
    public function _initialize()
	{
		// 继承父类的_initialize()方法
		parent::_initialize();
        $this->view->assign('beian', $this->get_config('beian'));
        $this->view->assign('tongji', $this->get_config('tongji'));
	}

	/**
	 * 根据键名取得系统配置项的值
	 * @param  [type]  $key                    [指定config_key]
	 * @param  boolean $use_html_entity_decode [是否对结果进行html_entity_decode操作，默认为true]
	 * @return [type]                          [description]
	 */
    protected function get_config($key, $use_html_entity_decode=true)
    {
        $value = '';
        try{
            $config = ConfigModel::get(['config_key'=>$key]);
            $value = $use_html_entity_decode ? html_entity_decode($config->config_value) : $config->config_value;
        } catch (\Exception $e) {
            $value = $e->getMessage();
        }
        return $value;
    }

    
    /**
     * 申请一个global_id并将现有global_id值增加step
     * @param  integer $section     中间需要加的区代码
     * @param  integer $sn_length   序号字符串长度，左补0，默认为2
     * @param  integer $step        序号需要增长的步长
     * @return string               字符串格式为：[时间戳].[section].[sn]
     */
    protected function apply_full_global_id_str($section=0, $sn_length=2, $step=1)
    {
        /*
        字符串格式为：[时间戳].[section].[sn]
         */
        $section_str = $section==0 ? '' : str_pad($section,6,'0',STR_PAD_LEFT); //section长6个数字
        $sn_str = str_pad($this->apply_global_sn($step),$sn_length,'0',STR_PAD_LEFT);   //序号长6个数字
        $full = time().$section_str.$sn_str;
        return $full;
    }

    /**
     * 取得当前系统全局ID的数值
     * @param  integer $step 增长步长
     * @return [type]        返回ID数值
     */
    protected function apply_global_sn($step=1)
    {
        $config = ConfigModel::get(['config_key'=>'max_global_id']);
        if(!$config) {
            // 没取到，调用set_config创建配置项
            $this->set_config('max_global_id', 0, true, '当前ID序号', 0);
            $config = ConfigModel::get(['config_key'=>'max_global_id']);
        }
        // 根据update_time和当前时间戳的比较判断ID是否需要改变
        if($config->getData()['update_time']==time()) {
            // update_time和当前时间戳在同一秒，需要改变ID
            $config->config_value+=$step;
        } else {
            // update_time和当前时间戳不在同一秒，将序号复位到1
            $config->config_value = 1;
        }
        // 强行更新update_time
        $config->update_time = time();
        $config->save();
        // 返回ID数值
        return $config->config_value;
    }

    /**
     * 获取指定模块下的所有方法的路由
     * @param  [type] $module 模块名字符串
     * @return [type]         路由字符串数组（小写）
     */
    protected function get_routes($module)
    {
        $controllers = $this->get_controllers($module);
        $actions = [];
        $routes = [];
        foreach( $controllers as $key=>$value) {
            $controllerpath = $module.'/'.strtolower($value);
            $classpath = 'app\\'.$module.'\\controller' . '\\' . $value;
            $baseclasspath = 'app\\'.$module.'\\controller\\Base';
            $actions[$value] = $this->get_actions($classpath, $baseclasspath);
            foreach($actions[$value] as $n=>$val) {
                $route = $controllerpath.'/'.$val;
                array_push($routes, $route);
            }
        }
        return $routes;
    }

    /**
     * 获取指定模块下的所有控制器
     * @param  [type] $module               [模块名]
     * @param  string $base_controller_name 可以指定忽略Base控制器
     * @return [type]                       控制器数组（区分大小写）
     */
    protected function get_controllers($module, $base_controller_name='')
    {
        $dir = APP_PATH.$module.'/controller';
        $pathList = glob($dir . '/*.php');
        $res = [];
        foreach($pathList as $key => $value) {
            $res[] = basename($value, '.php');
        }
        $res = array_diff($res, [$base_controller_name]);
        return $res;
    }

    /**
     * 获取控制器下的所有方法名
     * @param  [type] $controller_path      指定控制器路径（类名）
     * @param  string $base_controller_path 可以指定忽略Base类的路径
     * @return [type]                       方法名数组（区分大小写）
     */
    protected function get_actions($controller_path, $base_controller_path='')
    {
        $methods = get_class_methods(new $controller_path());
        $baseMethods = $base_controller_path? get_class_methods(new $base_controller_path()) : [];
        $res = array_diff($methods, $baseMethods);
        return $res;
    }

}
