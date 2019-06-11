<?php
namespace app\api\controller;

use app\api\controller\Base as ApiBase;
use app\common\model\Res;
use think\Image;
use think\Request;
use app\common\controller\PictureService;
use app\common\model\Bucket;
use app\common\model\Picture;
use think\Session;

class Ueditor extends ApiBase
{
	private $uploadfolder='/upload/';   //上传地址

	private $scrawlfolder='/upload/_scrawl/';   //涂鸦保存地址

	private $catchfolder='/upload/_catch/';   //远程抓取地址

	// private $configpath=ROOT_PATH.'public/static/lib/ueditor/1.4.3/utf8-php/php/config.json';
	// private $configpath=__DIR__.'/../../../public/static/lib/ueditor/1.4.3/utf8-php/php/config.json';
	private $configpath='/static/lib/ueditor/1.4.3/utf8-php/php/config.json';

	private $config;


	public function index(){
		$this->type=input('edit_type','');
        //header('Access-Control-Allow-Origin: http://www.baidu.com'); //设置http://www.baidu.com允许跨域访问
        //header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With'); //设置允许的跨域header
        date_default_timezone_set("Asia/chongqing");
        error_reporting(E_ERROR);
        header("Content-Type: text/html; charset=utf-8");

        $CONFIG = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents($_SERVER['DOCUMENT_ROOT'].$this->configpath)), true);
		$this->config=$CONFIG;

		$action = input('action');
		switch ($action) {
			case 'config':
				$result =  json_encode($CONFIG);
				break;
		
				/* 上传图片 */
			case 'uploadimage':
				$result = $this->_qiniu_upload();
				break;
				/* 上传涂鸦 */
			case 'uploadscrawl':
				$result = $this->_upload_scrawl();
				break;
				/* 上传视频 */
			case 'uploadvideo':
				$result = $this->_upload(array('maxSize' => 1073741824,/*1G*/'exts'=>array('mp4', 'avi', 'wmv','rm','rmvb','mkv')));
				break;
				/* 上传文件 */
			case 'uploadfile':
				$result = $this->_upload(array('exts'=>array('jpg', 'gif', 'png', 'jpeg','txt','pdf','doc','docx','xls','xlsx','zip','rar','ppt','pptx',)));
				break;
		
				/* 列出图片 */
			case 'listimage':
				$result = $this->_qiniu_list($action);
				break;
				/* 列出文件 */
			case 'listfile':
				$result = $this->_list($action);
				break;		
				/* 抓取远程文件 */
			case 'catchimage':
				$result = $this->_upload_catch();
				break;
		
			default:
				$result = json_encode(array('state'=> '请求地址出错'));
				break;
		}
		
		/* 输出结果 */
		if (isset($_GET["callback"]) && false ) {
			if (preg_match("/^[\w_]+$/", $_GET["callback"])) {
				echo htmlspecialchars($_GET["callback"]) . '(' . $result . ')';
			} else {
				echo json_encode(array(
						'state'=> 'callback参数不合法'
				));
			}
		} else {
			exit($result) ;
		}
	}
	private function _qiniu_upload($config=array())
	{
		$title = '';
		$url='';
		if(!empty($config)){
			$this->config=array_merge($this->config,$config);;
		}

		$file = request()->file('upfile');
		if($file){
			// $validate=array(
			// 	'size'=>10240000 ,// 设置附件上传大小
			// 	'ext'=>$this->config['exts'],
			// );
			$picture = new Picture;
			$picture->admin_id = Session::has('admin_infor')?Session::get('admin_infor')->admin_id:'';
			$pservice = new PictureService;
			$res = $pservice->up_picture($file, $picture);
			if($res->status) {
				// bucket对应的域名				
				$url = $res->result['domain'];
				// 图片在bucket中的key
				$url .= $res->result['key'];
				// 默认插入水印板式
				$url .= '-'.Bucket::get(['bucket_name'=>$res->result['bucket']])->bucket_style_water;
				// echo "<script>console.log('".$img_url."');</script>";
				// 图片压缩
	            // $image = \think\Image::open('./'.$img_url);
	            // $image->thumb(700,1400)->save('./'.$img_url);

				$title = $res->result['key'];
				$state = 'SUCCESS';
			}else{
				$state = $res->message();
			}
		}else{
			$state = '未接收到文件';
		}
		
		$response=array(
			"state" => $state,
			"url" => $url,
			"title" => $title,
			"original" =>$title,
		);
		return json_encode($response);
	}

	//上传
	// private function _upload($config=array())
	// {
	// 	$title = '';
	// 	$url='';
	// 	if(!empty($config)){
	// 		$this->config=array_merge($this->config,$config);;
	// 	}

	// 	$file = request()->file('upfile');
	// 	if($file){
	// 		$validate=array(
	// 			'size'=>10240000 ,// 设置附件上传大小
	// 			'ext'=>$this->config['exts'],
	// 		);
	// 		$info = $file->validate($validate)->move($_SERVER['DOCUMENT_ROOT'] . $this->uploadfolder);
	// 		if($info) {

	// 			$img_url=$this->uploadfolder.$info->getSaveName();
	// 			// echo "<script>console.log('".$img_url."');</script>";
	// 			// 图片压缩
	//             // $image = \think\Image::open('./'.$img_url);
	//             // $image->thumb(700,1400)->save('./'.$img_url);

	// 			$title =$info->getFilename();
	// 			$state = 'SUCCESS';
	// 			$url= $img_url;
	// 		}else{
	// 			$state = $file->getError();
	// 		}
	// 	}else{
	// 		$state = '未接收到文件';
	// 	}
		
	// 	$response=array(
	// 		"state" => $state,
	// 		"url" => $url,
	// 		"title" => $title,
	// 		"original" =>$title,
	// 	);
	// 	return json_encode($response);
	// }

	private function _upload_scrawl()
	{		
		$data = input('post.' . $this->config ['scrawlFieldName']);
        $url='';
        $title = '';
        $oriName = '';
		if (empty ($data)) {
			$state= 'Scrawl Data Empty!';
		} else {
			$pservice = new PictureService;
			// 在服务器保存图片文件
			// $savepath = $pservice->save_picture('png', base64_decode($data), $this->scrawlfolder);
			$url = $pservice->up_scrawl('png', base64_decode($data), $this->scrawlfolder);
			// $url = $this->save_image('png', base64_decode($data), $this->scrawlfolder);
			if ($url) {
				$state = 'SUCCESS';
			} else {
				$state = 'Save scrawl file error!';
			}
		}
		$response=array(
		"state" => $state,
		"url" => $url,
		"title" => $title,
		"original" =>$oriName ,
		);
		return json_encode($response);
	}

	public function save_image($ext = null, $content = null, $path = null)
	{
		$newfile = '';
		// $path=substr($path,0,2)=='./' ? substr($path,1) :$path;
		// $path=substr($path,0,1)=='/' ? substr($path,1) :$path;
		if ($ext && $content) {
		    do {
		        $newfile = $path.str_replace("-","",date('Y-m-d/')) . uniqid() . '.' . $ext;
		    } while (file_exists($newfile));
		    $dir = dirname($newfile);
		    if (!is_dir($_SERVER['DOCUMENT_ROOT'].$dir)) {
		        mkdir($_SERVER['DOCUMENT_ROOT'].$dir, 0777, true);
		    }
		    file_put_contents($_SERVER['DOCUMENT_ROOT'].$newfile, $content);

		    // 图片压缩
			// $img_url='./'.$newfile;    
			// $image = \think\Image::open('./'.$img_url);
			// $image->thumb(700,1400)->save('./'.$img_url);
		}
		return $newfile;
	}


	//抓取远程文件
	private function _upload_catch()
	{
		set_time_limit(0);
		$sret = array(
			'state' => '',
			'list' => null
		);
		$savelist = array();
		$flist = input('post.' . $this->config ['catcherFieldName'].'/a');
		if (empty ($flist)) {
			$sret ['state'] = 'ERROR';
		} else {
			$sret ['state'] = 'SUCCESS';
			foreach ($flist as $f) {
				if (preg_match('/^(http|ftp|https):\\/\\//i', $f)) {
					$ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
					if (in_array('.' . $ext, $this->config ['catcherAllowFiles'])) {
						if ($img = file_get_contents($f)) {

							$savepath = $this->save_image($ext, $img, $this->catchfolder);
							if ($savepath) {
								$savelist [] = array(
									'state' => 'SUCCESS',
									'url' => $savepath,
									'size' => strlen($img),
									'title' => '',
									'original' => '',
									'source' => htmlspecialchars($f)
								);
							} else {
								$savelist [] = array(
									'state' => 'Save remote file error!',
									'url' => '',
									'size' => '',
									'title' => '',
									'original' => '',
									'source' => htmlspecialchars($f),
								);
							}
				
						} else {
							$savelist [] = array(
							'state' => 'Get remote file error',
							'url' => '',
							'size' => '',
							'title' => '',
							'original' => '',
							'source' => htmlspecialchars($f),
							);
						}
					} else {
						$sret ['state'] = 'File ext not allowed';
					}
				} else {
					$savelist [] = array(
						'state' => 'not remote image',
						'url' => '',
						'size' => '',
						'title' => '',
						'original' => '',
						'source' => htmlspecialchars($f),
					);
				}
			}
			$sret ['list'] = $savelist;
		}
		return json_encode($sret);
	}

	private function _qiniu_list($action)
	{
		/* 判断类型 */
		switch ($action) {
			/* 列出文件 */
			case 'listfile':
				$allowFiles = $this->config['fileManagerAllowFiles'];
				$listSize = $this->config['fileManagerListSize'];
				$prefix='/';
				break;
			/* 列出图片 */
			case 'listimage':
			default:
				$allowFiles = $this->config['imageManagerAllowFiles'];
				$listSize = $this->config['imageManagerListSize'];
				$prefix='/';
		}
		// 这里暂时没有用20190606
		$start = 0;
		// 准备文件列表
		$list = [];
		$picture = Picture::all();
		foreach($picture as $n=>$p) {
			$list[] = array(
				'url'=>$p->bucket->bucket_domain.$p->picture_key.'-'.$p->bucket->bucket_style_thumb,
				'title'=>$p->picture_name,
				'url_original'=>$p->bucket->bucket_domain.$p->picture_key.'-'.$p->bucket->bucket_style_water,
			);
		}
		/*
		$files[] = array(
	                        'url'=> substr($path2, strlen($_SERVER['DOCUMENT_ROOT'])),
	                        // 'document_root'=> $_SERVER['DOCUMENT_ROOT'],
	                        // 'root_path'=> ROOT_PATH,
	                        // 'path2'=> $path2,
	                        // 'path'=> $path,
	                        // 'mtime'=> filemtime($path2)
	                    );
		 */
		/* 返回数据 */
		$result = json_encode(array(
			"state" => "SUCCESS",
			"list" => $list,
			"start" => $start,
			"total" => count($list)
		));
		return $result;
	}


	private function _list($action)
	{
		/* 判断类型 */
		switch ($action) {
			/* 列出文件 */
			case 'listfile':
				$allowFiles = $this->config['fileManagerAllowFiles'];
				$listSize = $this->config['fileManagerListSize'];
				$prefix='/';
				break;
			/* 列出图片 */
			case 'listimage':
			default:
				$allowFiles = $this->config['imageManagerAllowFiles'];
				$listSize = $this->config['imageManagerListSize'];
				$prefix='/';
		}
		$allowFiles = substr(str_replace(".", "|", join("", $allowFiles)), 1);
		/* 获取参数 */
		$size = isset($_GET['size']) ? htmlspecialchars($_GET['size']) : $listSize;
		$start = isset($_GET['start']) ? htmlspecialchars($_GET['start']) : 0;

		$end = intval($start) + intval($size);
        $path = $_SERVER['DOCUMENT_ROOT'].$this->uploadfolder;
        $files = $this->getfiles($path, $allowFiles);
        if (!count($files)) {
            return json_encode(array(
                "state" => "no match file",
                "list" => array(),
                "start" => $start,
                "total" => count($files),
                "path" => $path,
                "allowFiles" => $allowFiles
            ));
        }
        /* 获取指定范围的列表 */
        $len = count($files);
        for ($i = min($end, $len) - 1, $list = array(); $i < $len && $i >= 0 && $i >= $start; $i--){
            $list[] = $files[$i];
        }
		/* 返回数据 */
		$result = json_encode(array(
			"state" => "SUCCESS",
			"list" => $list,
			"start" => $start,
			"total" => count($files)
		));
		return $result;
	}
	/**
	 * 遍历获取目录下的指定类型的文件
	 * @param string $path
	 * @param string $allowFiles
	 * @param array $files
	 * @return array
	 */
	function getfiles($path, $allowFiles, &$files = array())
	{
	    if (!is_dir($path)) return null;
	    if(substr($path, strlen($path) - 1) != '/') $path .= '/';
	    $handle = opendir($path);
	    while (false !== ($file = readdir($handle))) {
	        if ($file != '.' && $file != '..') {
	            $path2 = $path . $file;
	            if (is_dir($path2)) {
	                $this->getfiles($path2, $allowFiles, $files);
	            } else {
	                if (preg_match("/\.(".$allowFiles.")$/i", $file)) {
	                    $files[] = array(
	                        'url'=> substr($path2, strlen($_SERVER['DOCUMENT_ROOT'])),
	                        // 'document_root'=> $_SERVER['DOCUMENT_ROOT'],
	                        // 'root_path'=> ROOT_PATH,
	                        // 'path2'=> $path2,
	                        // 'path'=> $path,
	                        // 'mtime'=> filemtime($path2)
	                    );
	                }
	            }
	        }
	    }
	    return $files;
	}
}