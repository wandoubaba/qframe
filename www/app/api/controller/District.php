<?php
namespace app\api\controller;

use app\api\controller\Base as ApiBase;
use app\common\controller\DistrictService;

class District extends ApiBase
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