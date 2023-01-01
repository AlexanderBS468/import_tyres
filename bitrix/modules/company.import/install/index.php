<?php

use Bitrix\Main;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class company_import extends CModule
{

	public $MODULE_ID = 'company.import';
	public $MODULE_NAME;
	public $MODULE_DESCRIPTION;
	public $MODULE_VERSION;
	public $MODULE_VERSION_DATE;
	public $PARTNER_NAME;
	public $PARTNER_URI;

	public function __construct()
	{
		$arModuleVersion = null;

		include __DIR__ . '/version.php';

		if (isset($arModuleVersion) && is_array($arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion['VERSION'];
			$this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
		}

		$this->MODULE_NAME = Loc::getMessage('T_CUSTOM_MODULE_NAME');
		$this->MODULE_DESCRIPTION = Loc::getMessage('T_CUSTOM_MODULE_DESCRIPTION');

		$this->PARTNER_NAME = Loc::getMessage('T_CUSTOM_PARTNER_NAME');
		$this->PARTNER_URI = Loc::getMessage('T_CUSTOM_PARTNER_URI');
	}

	public function DoInstall() : bool
	{
		global $APPLICATION;

		$result = true;

		try
		{
			$this->checkRequirements();

			Main\ModuleManager::registerModule($this->MODULE_ID);

			if (Main\Loader::includeModule($this->MODULE_ID))
			{
				$this->InstallDB();
				$this->InstallEvents();
				$this->InstallAgents();
				$this->InstallFiles();
			}
			else
			{
				throw new Main\SystemException(GetMessage('T_CUSTOM_MODULE_NOT_REGISTERED'));
			}
		}
		catch (\Exception $exception)
		{
			$result = false;
			$APPLICATION->ThrowException($exception->getMessage());
		}

		return $result;
	}

	protected function checkRequirements() : void
	{
		// require php version

		$requirePhp = '7.2.0';

		if (CheckVersion(PHP_VERSION, $requirePhp) === false)
		{
			throw new Main\SystemException(GetMessage('T_CUSTOM_INSTALL_REQUIRE_PHP',
				['#VERSION#' => $requirePhp]));
		}

		// required modules

		$requireModules = [
			'main' => '19.5.0',
			'iblock' => '19.5.0',
			'sale' => '19.5.0',
		];

		if (class_exists(ModuleManager::class))
		{
			foreach ($requireModules as $moduleName => $moduleVersion)
			{
				$currentVersion = Main\ModuleManager::getVersion($moduleName);

				if ($currentVersion !== false && CheckVersion($currentVersion, $moduleVersion))
				{
					unset($requireModules[$moduleName]);
				}
			}
		}

		if (!empty($requireModules))
		{
			foreach ($requireModules as $moduleName => $moduleVersion)
			{
				throw new Main\SystemException(GetMessage('T_CUSTOM_INSTALL_REQUIRE_MODULE', [
					'#MODULE#' => $moduleName,
					'#VERSION#' => $moduleVersion,
				]));
			}
		}
	}

	public function InstallDB()
	{

	}

	public function InstallEvents()
	{

	}

	public function InstallAgents()
	{

	}

	public function InstallFiles()
	{
		CopyDirFiles(__DIR__ . '/admin', $_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/admin', true, true);
	}

	public function DoUninstall()
	{
		if (Main\Loader::includeModule($this->MODULE_ID))
		{
			$request = Main\Context::getCurrent()->getRequest();
			$isSaveData = $request->get('savedata') === 'Y';

			if (!$isSaveData)
			{
				$this->UnInstallDB();
			}

			$this->UnInstallEvents();
			$this->UnInstallAgents();
			$this->UnInstallFiles();
		}

		Main\ModuleManager::unRegisterModule($this->MODULE_ID);
	}

	public function UnInstallDB()
	{

	}

	public function UnInstallEvents()
	{

	}

	public function UnInstallAgents()
	{

	}

	public function UnInstallFiles()
	{
		DeleteDirFiles(__DIR__ . '/admin', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin');
	}
}
