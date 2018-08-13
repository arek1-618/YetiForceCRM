<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class Vtiger_ListAjax_View extends Vtiger_List_View
{
	use \App\Controller\ExposeMethod,
		App\Controller\ClearProcess;

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getListViewCount');
		$this->exposeMethod('getRecordsCount');
		$this->exposeMethod('getPageCount');
	}

	/**
	 * Function to get the page count for list.
	 *
	 * @return total number of pages
	 */
	public function getPageCount(\App\Request $request)
	{
		$listViewCount = $this->getListViewCount($request);
		$pagingModel = new Vtiger_Paging_Model();
		$pageLimit = $pagingModel->getPageLimit();
		$pageCount = ceil((int) $listViewCount / (int) $pageLimit);

		if ($pageCount == 0) {
			$pageCount = 1;
		}
		$result = [];
		$result['page'] = $pageCount;
		$result['numberOfRecords'] = $listViewCount;
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	/**
	 * Function returns the number of records for the current filter.
	 *
	 * @param \App\Request $request
	 */
	public function getRecordsCount(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$cvId = App\CustomView::getInstance($moduleName)->getViewId();
		$count = $this->getListViewCount($request);

		$result = [];
		$result['module'] = $moduleName;
		$result['viewname'] = $cvId;
		$result['count'] = $count;

		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSON);
		$response->setResult($result);
		$response->emit();
	}

	/**
	 * Function to get listView count.
	 *
	 * @param \App\Request $request
	 */
	public function getListViewCount(\App\Request $request)
	{
		$moduleName = $request->getModule();
		if (!$this->listViewModel) {
			$cvId = App\CustomView::getInstance($moduleName)->getViewId();
			if (!$cvId) {
				$cvId = 0;
			}
			$this->listViewModel = Vtiger_ListView_Model::getInstance($moduleName, $cvId);
		}
		if (!$request->isEmpty('operator', true)) {
			$this->listViewModel->set('operator', $request->getByType('operator', 1));
		}
		if (!$request->isEmpty('search_key', true) && !$request->isEmpty('search_value', true)) {
			$this->listViewModel->set('search_key', $request->getByType('search_key', 1));
			$this->listViewModel->set('search_value', $request->get('search_value'));
		}
		if ($request->has('entityState')) {
			$this->listViewModel->set('entityState', $request->getByType('entityState'));
		}
		$searchParmams = $request->getArray('search_params');
		if (!empty($searchParmams) && is_array($searchParmams)) {
			$transformedSearchParams = $this->listViewModel->get('query_generator')->parseBaseSearchParamsToCondition($searchParmams);
			$this->listViewModel->set('search_params', $transformedSearchParams);
		}
		return $this->listViewModel->getListViewCount();
	}
}
