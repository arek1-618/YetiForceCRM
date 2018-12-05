<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

class Vtiger_Text_UIType extends Vtiger_Base_UIType
{
	/**
	 * {@inheritdoc}
	 */
	public function getDBValue($value, $recordModel = false)
	{
		\App\DebugerEx::log('getDBValue', $value);
		return \App\Purifier::decodeHtml($value);
	}

	/**
	 * {@inheritdoc}
	 */
	public function setValueFromRequest(\App\Request $request, Vtiger_Record_Model $recordModel, $requestFieldName = false)
	{
		//\App\DebugerEx::log('setValueFromRequest', $request->getRaw('description'), $request->get('description'), $requestFieldName);
		$fieldName = $this->getFieldModel()->getFieldName();
		\App\DebugerEx::log('0) setValueFromRequest', $fieldName, $requestFieldName);
		if (!$requestFieldName) {
			$requestFieldName = $fieldName;
		}
		if ($this->getFieldModel()->getUIType() === 300) {
			\App\DebugerEx::log('1) setValueFromRequest', $requestFieldName, $request->get('description'));
			$value = $request->getForHtml($requestFieldName, '');
			\App\DebugerEx::log('2) setValueFromRequest', $value);
		} else {
			$value = $request->get($requestFieldName, '');
		}
		$this->validate($value);
		$recordModel->set($fieldName, $this->getDBValue($value, $recordModel));
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, $isUserFormat = false)
	{
		if (empty($value) || isset($this->validate[$value])) {
			return;
		}
		if (!is_string($value)) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $value, 406);
		}
		//Check for HTML tags
		if ($this->getFieldModel()->getUIType() !== 300 && $value !== strip_tags($value)) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $value, 406);
		}
		$this->validate[$value] = true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		$uiType = $this->getFieldModel()->get('uitype');
		\App\DebugerEx::log($uiType, $value);
		if (is_int($length)) {
			if ($uiType === 300) {
				\App\DebugerEx::log($uiType, $value);
				$value = \App\TextParser::htmlTruncate($value, $length);
			} else {
				$value = \App\TextParser::textTruncate($value, $length);
			}
		}
		if ($uiType === 300) {
			return App\Purifier::purifyHtml($value);
		} else {
			return nl2br(\App\Purifier::encodeHtml($value));
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getListViewDisplayValue($value, $record = false, $recordModel = false, $rawText = false)
	{
		return parent::getListViewDisplayValue(trim(strip_tags($value)), $record, $recordModel, $rawText);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTemplateName()
	{
		return 'Edit/Field/Text.tpl';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getAllowedColumnTypes()
	{
		return ['text'];
	}

	/**
	 * {@inheritdoc}
	 */
	public function getOperators()
	{
		return ['e', 'n', 's', 'ew', 'c', 'k', 'y', 'ny'];
	}
}
