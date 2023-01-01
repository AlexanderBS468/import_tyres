<?php
namespace Company\Import\Tables;

use Bitrix\Main;

class ProfilesTable extends Main\ORM\Data\DataManager
{
	public static function getTableName() : string
	{
		return 'company_import_profiles';
	}

	public static function getMap() : array
	{
		return [
			new Main\ORM\Fields\IntegerField('ID', [
				'autocomplete' => true,
				'primary' => true,
			]),
			new Main\ORM\Fields\StringField('NAME', [
				'required' => true,
			]),
			new Main\ORM\Fields\BooleanField('ACTIVE', [
				'values' => ['0', '1'],
				'default_value' => 1,
			]),
			new Main\ORM\Fields\IntegerField('SORT', [
				'default_value' => 500,
			]),
			new Main\ORM\Fields\StringField('URL', [
				'required' => true,
			]),
			new Main\ORM\Fields\EnumField('ENCODING', [
				'required' => true,
				'values' => [
					'windows-1251',
					'UTF-8'
				],
			]),
			new Main\ORM\Fields\EnumField('SEPARATOR', [
				'required' => true,
				'values' => [
					'TZP',
					'ZPT',
					'TAB',
					'SPS',
					'CTM',
				],
			]),
			new Main\ORM\Fields\StringField('IBLOCK', [
				'required' => true,
			]),
			(new Main\ORM\Fields\ArrayField('META', [
				'required' => true,
			]))
				->configureSerializationJson(),
			(new Main\ORM\Fields\ArrayField('PROPERTY', [
				'required' => true,
			]))
				->configureSerializationJson(),
		];
	}

	public static function getTableIndexes() : array
	{
		return [];
	}
}