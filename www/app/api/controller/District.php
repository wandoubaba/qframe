<?php
namespace app\api\controller;

use think\Controller;
use app\common\controller\DistrictService;

class District extends Controller
{
	public function ajaxget_district_by_fid($fid=86)
	{
		return DistrictService::get_district($fid);
	}

	public function get_district_by_fid($fid=86)
	{
		return json_encode(DistrictService::get_district($fid));
	}
}