<?php

namespace Company\Import;

use Bitrix\Main\Config\Option;

class Config
{
	public static function getModuleName():string
	{
		return 'company.import';
	}

	public static function getModulePath():string
	{
		return __DIR__;
	}

	public static function getNamespace():string
	{
		return '\\' . __NAMESPACE__;
	}

	public static function getOption(string $name, $default = "", $siteId = false)
	{
		$moduleName = static::getModuleName();
		$optionValue = Option::get($moduleName, $name, null, $siteId);

		return $optionValue;
	}

	public static function setOption(string $name, $value = "", $siteId = ""):void
	{
		$moduleName = static::getModuleName();

		Option::set($moduleName, $name, $value, $siteId);
	}

	public static function removeOption(string $name) : void
	{
		$moduleName = static::getModuleName();

		Option::delete($moduleName, [ 'name' => $name ]);
	}
}
