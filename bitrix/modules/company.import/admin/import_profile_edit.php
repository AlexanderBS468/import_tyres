<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin.php';

use Bitrix\Main,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main\Loader;
use Company\Import\Admin\Page\ProfileEdit;

Loc::loadMessages(__FILE__);

try
{
	if (!Loader::includeModule('company.import'))
	{
		throw new Main\SystemException(Loc::getMessage('COMPANY_IMPORT_NO_LOAD_MODULE'));
	}

	$ID = $_REQUEST['ID'];

	$classPage = new ProfileEdit();
	$classPage->render();
}
catch (Main\SystemException $exception)
{
	echo (new CAdminMessage([
		'TYPE' => 'ERROR',
		'MESSAGE' => $exception->getMessage(),
	]))->Show();
}

require $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php";
