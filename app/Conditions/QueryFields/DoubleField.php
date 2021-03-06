<?php

namespace App\Conditions\QueryFields;

/**
 * Double Query Field Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */
class DoubleField extends IntegerField
{
	/**
	 * {@inheritdoc}
	 */
	public function getValue()
	{
		return \App\Fields\Double::formatToDb($this->value);
	}
}
