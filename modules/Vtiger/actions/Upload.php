<?php

class Vtiger_Upload_Action extends \App\Controller\Action
{
	public function checkPermission(\App\Request $request)
	{
	}

	public function process(\App\Request $request)
	{
		\App\DebugerEx::log('1) process');

		$attach = \App\Fields\File::uploadAndSave2($request, $_FILES, 'image', 'test');
		\App\DebugerEx::log('2) process', $attach, $request->getAll());

		//var_dump($attach);
		$funcNum = null;
		//http://yeti/file.php?module=Products&action=MultiImage&field=imagename&record=202&key=15bac5c98de002beb514fb6a81142cef8b0e27b1Sp4Z6M6wtm
		$key = $attach[0]['key'];
		$moduleName = $request->getModule();
		$recordId = $request->getInteger('record');
		//$url = "http://yeti/file.php?module={$moduleName}&action=Browse&field=imagename&record={$recordId}&key={$key}";
		$url = "http://yeti/file.php?module={$moduleName}&action=Browse&record={$recordId}&key={$key}";
		//$message = 'OK';
		//echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction($funcNum, '$url', '$message');</script>";

		$arr = [
			'uploaded' => 1,
			'fileName' => $key,
			'url' => $url
		];

		if (isset($attach[0]['error'])) {
			$arr['error']['message'] = $attach[0]['error'];
			$arr['uploaded'] = 0;
		}

		\App\DebugerEx::log($attach, $arr);
		echo \App\Json::encode($arr);
	}
}
