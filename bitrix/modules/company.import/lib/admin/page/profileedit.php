<?php

namespace Company\Import\Admin\Page;

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Company\Import\DB\Tables\ProfilesTable;

/**
 * @description temp fast version code
 * @todo        need to remove and create component
 * @deprecated
 */
class ProfileEdit
{
	public $loadedModules = [];
	/** @var $tabControl \CAdminForm  */
	public $tabControl = [];
	public $error = '';
	public $arImport = [];
	/** @var $request  Main\HttpRequest */
	public $request;
	public $currentUrl;
	public $arResult = [];

	protected function loadModules() : void
	{
		$this->loadedModules['catalog'] = Loader::includeModule('catalog');
		$this->loadedModules['sale'] = Loader::includeModule('sale');
		$this->loadedModules['iblock'] = Loader::includeModule('iblock');
	}

	protected function inti() : void
	{
		global $APPLICATION;
		$this->loadModules();
		$this->request = Main\Application::getInstance()->getContext()->getRequest();
		$this->currentUrl = $APPLICATION->GetCurPage();
		ClearVars();
		ClearVars( "f_" );
		\CJSCore::Init( array( "jquery" ) );

		$messageTitle = Loc::getMessage('COMPANY_IMPORT_CREATE_TITLE');
		$ID = $this->request->getRaw('ID');
		if ($ID > 0)
		{
			$messageTitle = Loc::getMessage('COMPANY_IMPORT_EDIT_TITLE', ['#ID#' => $ID]);
		}

		global $APPLICATION;
		$APPLICATION->SetTitle($messageTitle);
	}

	public function render() : void
	{
		$this->inti();

		$arTabs = [
			[
				"DIV" => 'edit1',
				"TAB" => Loc::getMessage( 'COMPANY_IMPORT_MAIN_SETTING' ),
				"ICON" => '',
				"TITLE" => Loc::getMessage( 'COMPANY_IMPORT_MAIN_SETTING' ),
			],
			[
				"DIV" => 'edit2',
				"TAB" => Loc::getMessage( 'COMPANY_IMPORT_FIELD_SETTING' ),
				"ICON" => '',
				"TITLE" => Loc::getMessage( 'COMPANY_IMPORT_FIELD_SETTING' ),
			],
		];

		$this->tabControl = new \CAdminForm( "import_edit", $arTabs );

		$arMenu = [
			[
				"TEXT" => Loc::getMessage( 'COMPANY_IMPORT_PROFILE_LIST' ),
				"LINK" => "/bitrix/admin/company_import_profiles.php",
				"ICON" => "btn_list"
			]
		];

		$ID = $this->request->getRaw('ID');
		if( $ID > 0 )
		{
			$this->arImport = ProfilesTable::getList( ['filter' => ["ID" => $ID]])->fetch();

			$arMenu[] = [ "SEPARATOR" => "Y" ];
			$arMenu[] = [
				"TEXT" => Loc::getMessage( 'COMPANY_IMPORT_ADD_PROFILE' ),
				"LINK" => "/bitrix/admin/company_import_profile_edit.php",
				"ICON" => "btn_new"
			];
			$arMenu[] = [
				"TEXT" => Loc::getMessage( 'COMPANY_IMPORT_DELETE_PROFILE' ),
				"LINK" => "javascript:if( confirm( '".Loc::getMessage( 'TITLE_DELETE' )."?') ) window.location='/bitrix/admin/company_import_profiles.php?action_button=delete&ID[]=".$ID."&".bitrix_sessid_get()."#tb';",
				"WARNING" => "Y",
				"ICON" => "btn_delete"
			];
		}

		$this->requestDo();

		( new \CAdminContextMenu( $arMenu ) )
			->Show();

		if( $this->error )
		{
			$this->showError();
		}

		$this->renderAllForm();
	}

	public function showError() : void
	{
		$e = new \CAdminException( [['text' => $this->error]]);
		$mess = $e->GetMessages() ?: Loc::getMessage("COMPANY_IMPORT_ERROR_SAVE");
		echo ( new \CAdminMessage( $mess, $e ) )->Show();
	}

	public function renderAllForm() : void
	{
		$this->tabControl->BeginEpilogContent();
		echo bitrix_sessid_post();
		$ID = $this->request->getRaw('ID');
		?>
		<input type="hidden" name="Update" value="Y">
		<input type="hidden" name="ID" value=<?=$ID?>>
		<?php

		$this->tabControl->EndEpilogContent();

		$this->tabControl->Begin([
			"FORM_ACTION" => $this->currentUrl . "?ID=". (int)$ID ."&lang=".LANG
		]);

		$this->tabControl->BeginNextFormTab();
		$this->mainTab();
		$this->tabControl->BeginNextFormTab();
		$this->tabProps();

		$this->tabControl->Buttons(array(
			"btnSaveAndAdd" => true,
			"back_url" => "company_import_profiles.php?lang=".LANGUAGE_ID,
		));

		$this->tabControl->Show();
	}

	public function mainTab() : void
	{
		$arImport = $this->arImport;

		$ID = $this->request->getRaw('ID');
		if( $ID > 0 )
		{
			$this->tabControl->AddViewField( "ID", "ID", $ID );
		}

		$this->tabControl->AddEditField( 'NAME', Loc::getMessage( 'COMPANY_IMPORT_FIELD_NAME' ), true, array( "size"=>40, "maxlength" => 255 ), array_key_exists( 'NAME', $_REQUEST ) ? $_REQUEST["NAME"] : $arImport["NAME"] );
		$this->tabControl->AddCheckBoxField( 'ACTIVE', Loc::getMessage( 'COMPANY_IMPORT_FIELD_ACTIVE' ), false, 'Y', $_REQUEST["ACTIVE"] === "Y" || $arImport["ACTIVE"] );
		$this->tabControl->AddEditField( 'SORT', Loc::getMessage( 'COMPANY_IMPORT_FIELD_SORT' ), false, array( "size" => 5, "maxlength" => 255 ), $_REQUEST["SORT"] ?: ($arImport["SORT"] ?: 100));

		$this->tabControl->AddSection( 'DATA_SETTINGS', Loc::getMessage( 'COMPANY_IMPORT_SECTION_BLOCK_TITLE' ) );
		$this->tabControl->BeginCustomField( 'URL', Loc::getMessage( 'COMPANY_IMPORT_LINK' ) );

		$this->tabControl->AddDropDownField(
			'TYPE_OF_RESPONSE',
			Loc::getMessage( 'COMPANY_IMPORT_FIELD_TYPE_OF_RESPONSE' ),
			false,
			[
				'CSV' => 'CSV',
			],
			array_key_exists( 'TYPE_OF_RESPONSE', $_REQUEST ) ? $_REQUEST["TYPE_OF_RESPONSE"] : $arImport["TYPE_OF_RESPONSE"]
		);

		?>
		<tr>
			<td><b><?=$this->tabControl->GetCustomLabelHTML()?></b></td>
			<td>
				<input type="text" size="60" maxlength="255" value="<?=array_key_exists( 'URL', $_REQUEST ) ? $_REQUEST["URL"] : $arImport["URL"]?>" id="URL" name="URL" />
				<input type="button" onclick="checkURL()" value="<?=Loc::getMessage( 'COMPANY_IMPORT_BUTTON_CHECK' )?>" />
				<span id="URL_SUCCESS" style="display: none; color: green;"><?=Loc::getMessage( 'COMPANY_IMPORT_RESPONSE_ACCESS' )?></span>
				<span id="URL_ERROR" style="display: none; color: red;"><?=Loc::getMessage( 'COMPANY_IMPORT_RESPONSE_UNAVAILABLE' )?></span>
				<script>
					function checkURL(){
						BX.ajax({
							'url' : '<?=$this->currentUrl?>',
							'method' : 'POST',
							'data' : 'check_url=' + BX( 'URL' ).value,
							'dataType' : 'html',
							'timeout' : 10,
							'async' : true,
							'start' : true,
							'onsuccess' : function( data ){
								BX( 'URL_SUCCESS' ).style.display = 'none';
								BX( 'URL_ERROR' ).style.display = 'none';
								if( data === 'ok' ){
									BX( 'URL_SUCCESS' ).style.display = 'inline';
								}else{
									BX( 'URL_ERROR' ).style.display = 'inline';
								}
							}
						});
					}
				</script>
			</td>
		</tr>
		<?php
		$this->tabControl->EndCustomField( 'URL' );

		$arEncodings = array(
			'windows-1251' => Loc::getMessage( 'COMPANY_IMPORT_FIELD_ENCODING_CYRILLIC1251' ),
			'UTF-8' => Loc::getMessage( 'COMPANY_IMPORT_FIELD_ENCODING_UTF8' ),
		);

		$this->tabControl->AddDropDownField( 'ENCODING', Loc::getMessage( 'COMPANY_IMPORT_FIELD_ENCODING' ), false, $arEncodings, array_key_exists( 'ENCODING', $_REQUEST ) ? $_REQUEST["ENCODING"] : $arImport["ENCODING"] );

		$arSeparators = array(
			'TZP' => Loc::getMessage( 'COMPANY_IMPORT_FIELD_VALUE_SEPARATOR_TZP' ),
			'ZPT' => Loc::getMessage( 'COMPANY_IMPORT_FIELD_VALUE_SEPARATOR_ZPT' ),
			'TAB' => Loc::getMessage( 'COMPANY_IMPORT_FIELD_VALUE_SEPARATOR_TAB' ),
			'SPS' => Loc::getMessage( 'COMPANY_IMPORT_FIELD_VALUE_SEPARATOR_SPS' ),
		);

		$this->tabControl->AddDropDownField( 'SEPARATOR', Loc::getMessage( 'COMPANY_IMPORT_FIELD_SEPARATOR' ), false, $arSeparators, array_key_exists( 'SEPARATOR', $_REQUEST ) ? $_REQUEST["SEPARATOR"] : $arImport["SEPARATOR"] );

		$this->tabControl->AddDropDownField(
			'TYPE_OF_DATA',
			Loc::getMessage( 'COMPANY_IMPORT_FIELD_TYPE_OF_DATA' ),
			false,
			[
				'CATALOG' => Loc::getMessage( 'COMPANY_IMPORT_FIELD_VALUE_TYPE_OF_DATA_CATALOG' ),
			],
			array_key_exists( 'TYPE_OF_DATA', $_REQUEST ) ? $_REQUEST["TYPE_OF_DATA"] : $arImport["TYPE_OF_DATA"]
		);

		$arIBTypes = [];
		$dbRes = \CIBlockType::GetList();
		while($arIBType = $dbRes->Fetch())
		{
			if($arIBType = \CIBlockType::GetByIDLang($arIBType['ID'], LANG)){
				$arIBTypes[$arIBType['ID']] = $arIBType;
			}
		}

		$arIBlocks = $arIBlocksIDsByType = array();
		$rsIBlock = \CIBlock::GetList();
		while($arIBlock = $rsIBlock->Fetch())
		{
			$arIBlocks[$arIBlock['ID']] = $arIBlock;
			$arIBlocksIDsByType[$arIBlock['IBLOCK_TYPE_ID']][] = $arIBlock['ID'];
		}

		$this->tabControl->BeginCustomField('IBLOCK_ID', Loc::getMessage('COMPANY_IMPORT_FIELD_IBLOCK'));
		?>
		<tr id="tr_IBLOCK_ID">
			<td class="adm-detail-content-cell-l" width="40%">
				<span class="adm-required-field"><?=Loc::getMessage('COMPANY_IMPORT_FIELD_IBLOCK')?></span>
			</td>
			<td class="adm-detail-content-cell-r">
				<select name="IBLOCK_ID" id="IBLOCK_ID">
					<?foreach($arIBlocksIDsByType as $ibType => $arIBlocksIDs):?>
						<optgroup label="<?=$arIBTypes[$ibType]['NAME']?>">
							<?foreach($arIBlocksIDs as $ibId):?>
								<option value="<?=$ibId?>"<?=($ibId === $arImport["IBLOCK_ID"] ? ' selected' : '')?>><?=$arIBlocks[$ibId]['NAME'].' ['.$ibId.']'?></option>
							<?endforeach;?>
						</optgroup>
					<?endforeach;?>
				</select>
			</td>
		</tr>
		<?php
		$this->tabControl->EndCustomField('IBLOCK_ID');

		$arMissingItems = [
			"nothing" => Loc::getMessage('COMPANY_IMPORT_FIELD_VALUE_MISSING_1'),
			"deactivate" => Loc::getMessage('COMPANY_IMPORT_FIELD_VALUE_MISSING_2'),
			"delete" => Loc::getMessage('COMPANY_IMPORT_FIELD_VALUE_MISSING_3'),
		];

		$this->tabControl->AddDropDownField( 'MISSING_ITEMS', Loc::getMessage('COMPANY_IMPORT_FIELD_MISSING_ITEMS'), false, $arMissingItems, $arImport["MISSING_ITEMS"] );

		$this->tabControl->BeginCustomField( 'INFO', Loc::getMessage( 'COMPANY_IMPORT_INFO_START' ) );
		$urlCron = ($this->request->isHttps() ? 'https://' : 'http://' . $_SERVER['HTTP_HOST']) . '/bitrix/ajax/start_import.php?id=' . $arImport['ID'];
		?>
		<tr>
			<td colspan="2" align="center">
				<div class="adm-info-message-wrap" align="center">
					<div class="adm-info-message">
						<?=Loc::getMessage( 'COMPANY_IMPORT_CRON' )?>:<br/>
						export LANG=ru_RU.Windows-1251 && curl -L --max-redirs 500 "<?=$urlCron?>"
					</div>
				</div>
			</td>
		</tr>
		<?php
		$this->tabControl->EndCustomField( 'INFO' );
	}

	public function tabProps() : void
	{
		$arImport = $this->arImport;
		$tabControl = $this->tabControl;

		if( $arImport["URL"] )
		{
			$tabControl->BeginCustomField( 'PROPERTY', Loc::getMessage('COMPANY_IMPORT_FIELD_PROPS') );
			$arMeta = json_decode($arImport["META"], true) ?: [];
			$arFields = json_decode($arImport["PROPERTY"], true) ?: [];
			?>
			<tr>
				<td colspan="2" style="padding-bottom: 15px;">
					<input id="download" type="button" onclick="downloadFile()" value="<?=Loc::getMessage('COMPANY_IMPORT_DOWNLOAD_FIELDS')?>" />
					<img id="wait_file" style="display: none;" src="/bitrix/images/main/composite/loading.gif" />
					<span id="error_file" style="display: none; color: red;"><?=Loc::getMessage('COMPANY_IMPORT_ERROR_DOWNLOAD')?></span>
				</td>
			</tr>
			<script>
				function downloadFile(){
					BX( 'wait_file' ).style.display = 'inline-block';
					BX( 'download' ).style.display = 'none';
					BX.ajax({
						'url' : '<?=$this->currentUrl?>',
						'method' : 'POST',
						'data' : 'ID='+$('#tr_ID').find('td.adm-detail-content-cell-r').text().trim()+'&getProps=Y&url=' + BX( 'URL' ).value,
						'dataType' : 'html',
						'timeout' : 10,
						'async' : true,
						'start' : true,
						'onsuccess' : function( data ){
							BX( 'wait_file' ).style.display = 'none';
							if( data === 'error' ){
								BX( 'error_file' ).style.display = 'inline';
							}else{
								BX( 'file_upload' ).innerHTML = data;
							}
							BX( 'download' ).style.display = 'inline';
						}
					});
				}
			</script>
			<tbody id="file_upload">
			<?php
			if( !empty($arFields) )
			{
				$this->propsFields($arMeta, $arFields, $arImport);
			}
			?>
			</tbody>
			<?php
		}
		else
		{
			$tabControl->BeginCustomField( 'PROPERTY', Loc::getMessage('COMPANY_IMPORT_FIELD_SETTING') );
			?>
			<tr>
				<td colspan="2" style="text-align: center"><?=Loc::getMessage('COMPANY_IMPORT_FILE_NOT_FOUND')?></td>
			</tr>
			<?php
		}
		$tabControl->EndCustomField( 'PROPERTY' );
	}

	public function propsFields($arMeta, $arFields, $arImport) : void
	{
		$arCurrencies = [];
		if($this->loadedModules['sale'])
		{
			$rsCurrency = \CCurrency::GetList(($by = "name"), ($order1 = "asc"));
			while($arCurrency = $rsCurrency->fetch())
			{
				$arCurrencies[$arCurrency["CURRENCY"]] = $arCurrency["FULL_NAME"];
			}
		}

		?>
		<tr style="display:none;">
			<td class="hidden_props_meta"></td>
			<td class="hidden_props_meta_values">
				<?php
				foreach( $arMeta as $code => $name )
				{
					if( SITE_CHARSET !== 'UTF-8')
					{
						$arMeta[$code] = iconv('UTF-8', SITE_CHARSET.'//TRANSLIT', $name );
					}
					?>
					<input type="hidden" name="META_<?=$code?>" value="<?=$arMeta[$code]?>" />
					<?php
				}
				?>
			</td>
		</tr>
		<?php


		$arProperties = [];
		$rsProperty = \CIBlockProperty::GetList( ["NAME" => "ASC"], ["IBLOCK_ID" => $arImport["IBLOCK_ID"]]);
		while( $arProperty = $rsProperty->fetch() )
		{
			$arProperties[$arProperty["ID"]] = $arProperty["NAME"];
		}
		?>
		<tr>
			<th width="40%" class="adm-detail-content-cell-l"><?=Loc::getMessage('COMPANY_IMPORT_FIELD_PROPS_NAME')?></th>
			<th class="adm-detail-content-cell-r" style="text-align: left;"><?=Loc::getMessage('COMPANY_IMPORT_FIELD_PROPS_IBLOCK_PROP_VALUE')?></th>
		</tr>
		<?php
		if ($arImport['TYPE_OF_DATA'] === 'CATALOG')
		{
			$isSelected = $arFields['SECTIONS'] ? 'selected="selected"' : '';
			?>
			<tr class="heading">
				<td colspan="2"><?=Loc::getMessage('COMPANY_IMPORT_GROUP_SETTINGS_SECTIONS')?></td>
			</tr>

			<tr>
				<td width="40%" class="adm-detail-content-cell-l"><?=Loc::getMessage('COMPANY_IMPORT_FIELD_CATALOG_CATEGORY')?></td>
				<td class="adm-detail-content-cell-r">
					<select name="FIELD_SECTIONS">
						<option value="" <?=$isSelected?>><?=Loc::getMessage("COMPANY_IMPORT_FIELD_VALUE_IBLOCK_PROP_VALUE_NULL")?></option>
						<?php
						foreach( $arMeta as $meta_name )
						{
							$isSelectedOption = $arFields['SECTIONS'] === $meta_name ? 'selected="selected"' : '';
							?><option value="<?=$meta_name?>" <?=$isSelectedOption?>><?=$meta_name?></option><?php
						}
						?>
					</select>
				</td>
			</tr>
			<?php
		}

		?>
		<tr class="heading">
			<td colspan="2"><?=Loc::getMessage('COMPANY_IMPORT_GROUP_SETTINGS_PROPS')?></td>
		</tr>
		<?php
		foreach( $arMeta as $code => $meta_name )
		{
			?>
			<tr>
				<td width="40%" class="adm-detail-content-cell-l"><?=$meta_name?></td>
				<td class="adm-detail-content-cell-r">
					<select name="FIELD_<?=strtoupper($code)?>">
						<option value=""><?=Loc::getMessage("COMPANY_IMPORT_FIELD_VALUE_IBLOCK_PROP_VALUE_NULL")?></option>
						<optgroup label="<?=Loc::getMessage("COMPANY_IMPORT_FIELD_PROPS_IBLOCK_PROP_FIELDS")?>">
							<option value="XML_ID" <?=$arFields[$code] === 'XML_ID' ? 'selected="selected"' : ''?>><?=Loc::getMessage("COMPANY_IMPORT_FIELD_XML_ID")?></option>
							<option value="NAME" <?='NAME' === $arFields[$code] ? 'selected="selected"' : ''?>><?=Loc::getMessage("COMPANY_IMPORT_FIELD_NAME")?></option>
							<option value="PREVIEW_PICTURE" <?=$arFields[$code] === 'PREVIEW_PICTURE' ? 'selected="selected"' : ''?>><?=Loc::getMessage("COMPANY_IMPORT_FIELD_VALUE_IBLOCK_PROP_VALUE_PREVIEW_PICTURE")?></option>
							<option value="DETAIL_PICTURE" <?=$arFields[$code] === 'DETAIL_PICTURE' ? 'selected="selected"' : ''?>><?=Loc::getMessage("COMPANY_IMPORT_FIELD_VALUE_IBLOCK_PROP_VALUE_DETAIL_PICTURE")?></option>
							<option value="PREVIEW_TEXT" <?=$arFields[$code] === 'PREVIEW_TEXT' ? 'selected="selected"' : ''?>><?=Loc::getMessage("COMPANY_IMPORT_FIELD_VALUE_IBLOCK_PROP_VALUE_PREVIEW_TEXT")?></option>
							<option value="DETAIL_TEXT" <?=$arFields[$code] === 'DETAIL_TEXT' ? 'selected="selected"' : ''?>><?=Loc::getMessage("COMPANY_IMPORT_FIELD_VALUE_IBLOCK_PROP_VALUE_DETAIL_TEXT")?></option>
							<option value="ACTIVE_FROM" <?=$arFields[$code] === 'ACTIVE_FROM' ? 'selected="selected"' : ''?>><?=Loc::getMessage("COMPANY_IMPORT_FIELD_VALUE_IBLOCK_PROP_VALUE_ACTIVE_FROM")?></option>
							<option value="ACTIVE_TO" <?=$arFields[$code] === 'ACTIVE_TO' ? 'selected="selected"' : ''?>><?=Loc::getMessage("COMPANY_IMPORT_FIELD_VALUE_IBLOCK_PROP_VALUE_ACTIVE_TO")?></option>
						</optgroup>
						<optgroup label="<?=Loc::getMessage("COMPANY_IMPORT_FIELD_PROPS_IBLOCK_PROP_PROPERTIES")?>">
							<option value="new"><?=Loc::getMessage('COMPANY_IMPORT_FIELD_PROPS_IBLOCK_PROP_PROPERTY_CREATE_NEW')?></option>
							<?foreach( $arProperties as $prop_id => $name ){?>
								<option value="<?=$prop_id?>" <?=$prop_id === $arFields[$code] ? 'selected="selected"' : ''?>><?=$name?></option>
							<?}?>
						</optgroup>
						<optgroup label="<?=Loc::getMessage("COMPANY_IMPORT_FIELD_PROPS_IBLOCK_PROP_SECTION_FIELDS")?>">
							<option value="SECTION_PICTURE" <?=$arFields[$code] === 'SECTION_PICTURE' ? 'selected="selected"' : ''?>><?=Loc::getMessage("COMPANY_IMPORT_FIELD_PROPS_IBLOCK_PROP_SECTION_FIELDS_VALUE_SECTION_PICTURE")?></option>
							<option value="SECTION_DETAIL_PICTURE" <?=$arFields[$code] === 'SECTION_DETAIL_PICTURE' ? 'selected="selected"' : ''?>><?=Loc::getMessage("COMPANY_IMPORT_FIELD_PROPS_IBLOCK_PROP_SECTION_FIELDS_VALUE_SECTION_DETAIL_PICTURE")?></option>
							<option value="SECTION_DESCRIPTION" <?=$arFields[$code] === 'SECTION_DESCRIPTION' ? 'selected="selected"' : ''?>><?=Loc::getMessage("COMPANY_IMPORT_FIELD_PROPS_IBLOCK_PROP_SECTION_FIELDS_VALUE_SECTION_DESCRIPTION")?></option>
						</optgroup>
						<?php
						if($this->loadedModules['catalog'])
						{
							?>
							<optgroup label="<?=Loc::getMessage("COMPANY_IMPORT_FIELD_PROPS_SALE_PARAM")?>">
								<option value="SHOP_QUANTITY" <?=$arFields[$code] === 'SHOP_QUANTITY' ? 'selected="selected"' : ''?>><?=Loc::getMessage( 'COMPANY_IMPORT_FIELD_PROPS_SALE_PARAM_VALUE_QUANTITY' )?></option>
								<?php
								$dbPriceType = \CCatalogGroup::GetList(["SORT" => "ASC"]);
								while ($arPriceType = $dbPriceType->Fetch())
								{
									?>
									<option value="SHOP_PRICE_<?=$arPriceType['ID']?>" <?=$arFields[$code] === 'SHOP_PRICE_'.$arPriceType['ID'] ? 'selected="selected"' : ''?>><?=$arPriceType["NAME_LANG"]?></option>
									<?php
								}
								$rsStore = \CCatalogStore::GetList();
								while( $arStore = $rsStore->fetch() )
								{
									?>
									<option value="SHOP_STORE_<?=$arStore['ID']?>" <?=$arFields[$code] === 'SHOP_STORE_'.$arStore['ID'] ? 'selected="selected"' : ''?>><?=Loc::getMessage("COMPANY_IMPORT_FIELD_PROPS_SALE_PARAM_VALUE_STORE_NAME")?> "<?=$arStore["TITLE"]?>"</option>
									<?php
								}
								?>
							</optgroup>
							<?php
						}
						?>
					</select>
					<?php
					if($this->loadedModules['sale'] && strpos($arFields[$code], 'SHOP_PRICE') !== false)
					{
						$price_type = substr($arFields[$code], strlen('SHOP_PRICE_'));
						if($arFields['CURRENCY_' . $price_type] === '')
						{
							$arFields['CURRENCY_'.$price_type] = $arFields['CURRENCY'] !== '' ? $arFields['CURRENCY'] : 'SHOP_CURRENCY_RUB';
						}
						?>
						<select name="FIELD_CURRENCY_<?=$price_type?>">
							<?php
							if(is_array($arCurrencies))
							{
								foreach($arCurrencies as $CID => $CNAME)
								{
									?><option value="SHOP_CURRENCY_<?=$CID?>" <?=$arFields['CURRENCY_'.$price_type] === 'SHOP_CURRENCY_'.$CID ? 'selected="selected"' : ''?>><?=$CNAME?></option><?php
								}
							}
							?>
						</select>
						<?php
					}
					?>
				</td>
			</tr>
			<?php
		}
	}

	public function getPropsFields()
	{
		$url = $this->request->getRaw('url');

		$arImport = $this->arImport;

		switch ($arImport["SEPARATOR"])
		{
			case 'ZPT':
				$separator = ',';
				break;
			case 'TAB':
				$separator = '\t';
				break;
			case 'SPS':
				$separator = ' ';
				break;
			case 'TZP':
			default:
				$separator = ';';
				break;
		}

		$csvData = file_get_contents($url);


		if ($csvData !== false)
		{
			$arParamsMeta = ["replace_space" => "_", "replace_other" => "_"];

			$rows = explode("\n", $csvData);

			$row = $rows[0];

			$newCells = [];
			$cells = str_getcsv($row, $separator);

			foreach ($cells as $index => $cell)
			{
				if ($arImport['ENCODING'] !== SITE_CHARSET) {
					$cell = iconv($arImport['ENCODING'], SITE_CHARSET.'//TRANSLIT', $cell);
				}

				$code = strtoupper(\CUtil::translit($cell, "ru", $arParamsMeta));
				$code = preg_replace(['/ \[.*\]/', '/"/'], ['', ''], $code);

				$newCells[$code] = $cell;
			}
			$arFields = json_decode($arImport["PROPERTY"], true) ?: [];

			$this->propsFields($newCells, $arFields, $arImport);
		}

		return "error";
	}

	public function requestDo() : void
	{
		$isPost = $this->request->isPost();

		if( $isPost && ( $this->request->getRaw('check_url')) )
		{
			$url = $this->request->getRaw('check_url');
			global $APPLICATION;
			$APPLICATION->RestartBuffer();
			ob_end_clean();
			ob_get_clean();
			echo $this->checkUrl($url);
			die();
		}

		if( $isPost && ( $this->request->getRaw('getProps') === 'Y') )
		{
			global $APPLICATION;
			$APPLICATION->RestartBuffer();
			ob_end_clean();
			ob_get_clean();
			$this->getPropsFields();
			die();
		}

		if ( !check_bitrix_sessid() ) { return; }

		if( $isPost && ( $this->request->getRaw('save') > 0 || $this->request->getRaw('apply') > 0 ) )
		{
			$this->doSave();
		}
	}

	public function checkUrl(string $URL = '') : string
	{
		$response = [
			'ok' => 'ok',
			'error' => 'error'
		];

		if ($URL === '')
		{
			return $response['error'];
		}

		if(strpos($_REQUEST['check_url'], 'http://') === false && strpos($_REQUEST['check_url'], 'https://') === false)
		{
			return (file_exists($_SERVER['DOCUMENT_ROOT'].'/'.$_REQUEST['url'])) ? $response['ok'] : $response['error'];
		}

		$headers = @get_headers($URL);
		if(
			($headers === false)
			&& function_exists('curl_init')
			&& $curl = @curl_init()
		)
		{
			curl_setopt_array($curl, array(CURLOPT_URL => $URL, CURLOPT_HEADER => true, CURLOPT_RETURNTRANSFER => true, CURLOPT_NOBODY => true));
			$headers = explode("\n", curl_exec($curl));
			curl_close($curl);
		}

		if(strpos($headers[0], '200 OK') === false)
		{
			return $response['error'];
		}

		return $response['ok'];
	}

	public function doSave() : void
	{
		$queryList = $this->request->getPostList();
		$ID = $this->request->getQuery('ID');
		$URL = trim($queryList['URL']);
		$IBLOCK_ID = $queryList['IBLOCK_ID'];

		$arMeta = [];

		foreach( $_REQUEST as $key => $val )
		{
			if( strpos( $key, 'META' ) !== false )
			{
				$code = str_replace( 'META_', '', $key );
				if(SITE_CHARSET !== 'UTF-8')
				{
					$val = iconv(SITE_CHARSET, 'UTF-8//TRANSLIT', $val );
				}
				$arMeta[$code] = $val;
			}
		}

		$arProperty = [];

		foreach( $_REQUEST as $key => $val )
		{
			if( !$val ) { continue; }

			if( strpos( $key, 'FIELD' ) !== false )
			{
				$code = str_replace( 'FIELD_', '', $key );
				if( $val === 'new' && strpos( $val, 'SHOP_' ) === false )
				{
					$name = $arMeta[$code];
					if(SITE_CHARSET !== 'UTF-8'){
						$name = iconv(SITE_CHARSET, 'UTF-8//TRANSLIT', $name );
					}
					$arFields = [
						"NAME" => $name,
						"ACTIVE" => "Y",
						"SORT" => "500",
						"CODE" => $code,
						"PROPERTY_TYPE" => $code === 'IMAGES' ? "F" : "S",
						"MULTIPLE" => $code === 'IMAGES' ? "Y" : "N",
						"IBLOCK_ID" => $IBLOCK_ID
					];
					$obj = \Bitrix\Iblock\PropertyTable::add( $arFields );
					$val = $obj->getId();
				}

				$arProperty[$code] = $val;
			}
		}

		$arFields = [
			"NAME" => trim($queryList['NAME']),
			"ACTIVE" => ($queryList['ACTIVE'] === "Y"),
			"SORT" => $queryList['SORT'] ?: 100,
			"URL" => htmlspecialchars( $URL ),
			"TYPE_OF_RESPONSE" => $queryList['TYPE_OF_RESPONSE'] ?: 'CSV',
			"ENCODING" => $queryList['ENCODING'] ?: 'windows-1251',
			"SEPARATOR" => $queryList['SEPARATOR'] ?: 'TZP',
			"TYPE_OF_DATA" => $queryList['TYPE_OF_DATA'] ?: 'CATALOG',
			"IBLOCK_ID" => $IBLOCK_ID,
			'META' => json_encode($arMeta),
			"PROPERTY" => json_encode($arProperty),
			"MISSING_ITEMS" => $queryList['MISSING_ITEMS'] ?: 'nothing',
		];

		if( $URL )
		{
			$result = $this->checkUrl($URL);
			if ($result === 'error')
			{
				$this->error .= Loc::getMessage( 'COMPANY_IMPORT_ERROR_FILE_UNAVAILABLE' )."<br />";
			}
		}

		if( $ID > 0 )
		{
			$res = ProfilesTable::update( $ID, $arFields );
			if( !$res->isSuccess() )
			{
				$errors = $res->getErrors();
				if( $errors )
				{
					foreach( $errors as $error )
					{
						$this->error .= $error->getMessage().".<br>";
					}
				}
				else
				{
					$this->error .= Loc::getMessage( 'COMPANY_IMPORT_ERROR_UPDATE' )."<br>";
				}
			}
		}
		else
		{
			$dbRes = ProfilesTable::add( $arFields );
			if ( $dbRes->isSuccess() )
			{
				$ID = $dbRes->getId();
			}
			else
			{
				$errors = $dbRes->getErrors();
				if ($errors)
				{
					foreach( $errors as $error )
					{
						$this->error .= $error->getMessage().".<br>";
					}
				}
				else
				{
					$this->error .= Loc::getMessage( 'COMPANY_IMPORT_ERROR_ADD' )."<br>";
				}
			}
		}

		if( mb_strlen( $this->error ) <= 0 )
		{
			$apply = $queryList['apply'];
			$url = ($apply !== '') ? "company_import_profile_edit.php?ID=".$ID."&".$this->tabControl->ActiveTabParam() : "company_import_profiles.php";
			LocalRedirect( $url );
		}
	}
}