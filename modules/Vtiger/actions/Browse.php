<?php

class Vtiger_Browse_Action extends \App\Controller\Action
{
	public function checkPermission(\App\Request $request)
	{
	}

	public function process(\App\Request $request)
	{
		\App\DebugerEx::log('Browse process', $request->getAll());
	}
}
