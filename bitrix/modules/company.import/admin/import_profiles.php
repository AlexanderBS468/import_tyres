<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin.php';

use Bitrix\Main,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main\Loader;
use Company\Import\DB\Tables\ProfilesTable;

Loc::loadMessages(__FILE__);

try
{
	if (!Loader::includeModule('company.import'))
	{
		throw new Main\SystemException(Loc::getMessage('COMPANY_IMPORT_NO_LOAD_MODULE'));
	}

	global $APPLICATION;
	$APPLICATION->SetTitle( Loc::getMessage('COMPANY_IMPORT_TITLE') );

	$classPage = new PageProfiles();
	$classPage->render();
}
catch (Main\SystemException $exception)
{
	echo (new CAdminMessage([
		'TYPE' => 'ERROR',
		'MESSAGE' => $exception->getMessage(),
	]))->Show();
}

/**
 * @description temp fast version code
 * @todo        need to remove and create component
 * @deprecated
 */

class PageProfiles
{
	public $oSort;
	/** @var $lAdmin \CAdminList */
	public $lAdmin;
	/** @var $request \Bitrix\Main\HttpRequest */
	public $request;

	public function render() : void
	{
		$sTableID = "table_import";

		$this->oSort = new \CAdminSorting( $sTableID, "ID", "asc" );
		$this->lAdmin = new \CAdminList( $sTableID, $this->oSort );

		$profiles = ProfilesTable::getList()->fetchAll();

		$this->request = Main\Application::getInstance()->getContext()->getRequest();

		if ( $this->request->getRaw('action_button') )
		{
			global $APPLICATION;
			$APPLICATION->RestartBuffer();
			$this->requestDo();
		}

		$this->lAdmin->AddHeaders([
			["id" => "ID", "content" => "ID", "sort" => "id", "default" => true],
			["id" => "NAME", "content" => Loc::getMessage('COMPANY_IMPORT_NAME'), "sort" => "NAME", "default" => true],
			["id" => "ACTIVE", "content" => Loc::getMessage('COMPANY_IMPORT_ACTIVE'), "sort" => "ACTIVE", "default" => true],
			["id" => "SORT", "content" => Loc::getMessage('COMPANY_IMPORT_SORT'), "sort" => "SORT", "default" => true]
		]);

		foreach ($profiles as $profile)
		{
			$row = $this->lAdmin->AddRow( $profile["ID"], $profile, "company_import_profile_edit.php?ID=".$profile["ID"], Loc::getMessage('COMPANY_IMPORT_CHANGE') );

			$row->AddField( "ID", "<a href=\"company_import_profile_edit.php?ID=".$profile["ID"]."\">".$profile["ID"]."</a>" );
			$row->AddField( "NAME", $profile["NAME"] );
			$row->AddField( "ACTIVE", $profile["ACTIVE"] === "Y" ? Loc::getMessage('COMPANY_IMPORT_YES') : Loc::getMessage('COMPANY_IMPORT_NO') );
			$row->AddField( "SORT", $profile["SORT"] );

			$arActions = [];

			$arActions[] = [
				"ICON" => "start",
				"TEXT" => Loc::getMessage('COMPANY_IMPORT_START'),
				"TITLE" => Loc::getMessage('COMPANY_IMPORT_START'),
				"ACTION" => $this->lAdmin->ActionRedirect( "company_import_profile_edit.php?ID=".$profile["ID"].'&company_import_profile_edit=edit3' ),
				"DEFAULT" => true,
			];

			$arActions[] = [
				"ICON" => "edit",
				"TEXT" => Loc::getMessage('COMPANY_IMPORT_CHANGE'),
				"TITLE" => Loc::getMessage('COMPANY_IMPORT_CHANGE'),
				"ACTION" => $this->lAdmin->ActionRedirect( "company_import_profile_edit.php?ID=".$profile["ID"] ),
				"DEFAULT" => true,
			];
			$arActions[] = ["SEPARATOR" => true];
			$arActions[] = [
				"ICON" => "delete",
				"TEXT" => Loc::getMessage('COMPANY_IMPORT_DELETE'),
				"TITLE" => Loc::getMessage('COMPANY_IMPORT_DELETE'),
				"ACTION" => "if( confirm('".Loc::getMessage('COMPANY_IMPORT_DELETE_QUESTION')."') ) ".$this->lAdmin->ActionDoGroup( $profile["ID"], "delete" ),
			];

			$row->AddActions( $arActions );
		}

		$this->lAdmin->AddGroupActionTable(
			[
				"delete" => Loc::getMessage('COMPANY_IMPORT_DELETE'),
				"activate" => Loc::getMessage('COMPANY_IMPORT_ACTIVATE'),
				"deactivate" => Loc::getMessage('COMPANY_IMPORT_DEACTIVATE'),
			]
		);

		$arContext = [
			[
				"TEXT" => Loc::getMessage('COMPANY_IMPORT_ADD_IMPORT'),
				"TITLE" => Loc::getMessage('COMPANY_IMPORT_ADD_IMPORT'),
				"LINK" => "company_import_profile_edit.php?lang=" . LANGUAGE_ID,
				"ICON" => "btn_new"
			]
		];
		$this->lAdmin->AddAdminContextMenu( $arContext );

		$this->lAdmin->CheckListMode();
		$this->lAdmin->DisplayList();

	}

	public function requestDo()
	{
		$profiles = $this->request->getRaw('ID');

		if (!$profiles) { return; }

		if (!is_array($profiles))
		{
			$profiles = [$profiles];
		}

		$actionButton = $this->request->getRaw('action_button');
		$message = '';

		foreach( $profiles as $profile )
		{
			switch( $actionButton )
			{
				case "delete":
					$dbRes = ProfilesTable::delete( $profile );
					if( !$dbRes->isSuccess() )
					{
						$message = $dbRes->getErrorMessages() ?: Loc::getMessage('COMPANY_IMPORT_ERROR_DELETE');
					}

					break;
				case "activate":
				case "deactivate":
					$arFields = [
						"ACTIVE" => $actionButton === "activate"
					];

					$dbRes = ProfilesTable::update( $profile, $arFields );
					if( !$dbRes->isSuccess() )
					{
						$message = $dbRes->getErrorMessages() ?: Loc::getMessage('COMPANY_IMPORT_ERROR_UPDATE');
					}

					break;
			}

			$this->lAdmin->AddGroupError( $message, $profile );
		}

		if( mb_strlen( $message ) <= 0 )
		{
			LocalRedirect( 'company_import_profiles.php?lang=' . LANGUAGE_ID );
		}
	}
}

require( $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php" );
