<?php

use Bitrix\Main\Localization\Loc;

return [
	'parent_menu' => 'global_menu_services',
	'section' => 'companyimport',
	'sort' => 1000,
	'text' => Loc::getMessage('IMPORT_MENU_CONTROL'),
	'title' => Loc::getMessage('IMPORT_MENU_TITLE'),
	'icon' => 'workflow_menu_icon',
	'items_id' => 'menu_company_import',
	'items' => [
		[
			'text' => Loc::getMessage('IMPORT_MENU_PROFILES'),
			'title' => Loc::getMessage('IMPORT_MENU_PROFILES'),
			'url' => 'company_import_profiles.php?lang=' . LANGUAGE_ID,
			'more_url' => [
				'import_profile_edit.php',
			]
		]
	],
];
