<?php
namespace Company\Import\DB\Tables;

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
				],
			]),
			new Main\ORM\Fields\StringField('IBLOCK_ID', [
				'required' => true,
			]),
			new Main\ORM\Fields\EnumField('MISSING_ITEMS', [
				'required' => true,
				'values' => [
					'nothing',
					'deactivate',
					'delete'
				],
			]),
			new Main\ORM\Fields\TextField('META', [
				'required' => true,
			]),
			new Main\ORM\Fields\TextField('PROPERTY', [
				'required' => true,
			])
		];
	}

	public static function getTableIndexes() : array
	{
		return [];
	}
}