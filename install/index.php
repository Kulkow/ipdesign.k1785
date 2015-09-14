<?
IncludeModuleLangFile(__FILE__);
Class ipdesign_k1785 extends CModule
{
	const MODULE_ID = 'ipdesign.k1785';
	var $MODULE_ID = 'ipdesign.k1785';
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $strError = '';

	function __construct()
	{
		$arModuleVersion = array();
		include(dirname(__FILE__)."/version.php");
		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		$this->MODULE_NAME = GetMessage("ipdesign.k1785_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("ipdesign.k1785_MODULE_DESC");

		$this->PARTNER_NAME = GetMessage("ipdesign.k1785_PARTNER_NAME");
		$this->PARTNER_URI = GetMessage("ipdesign.k1785_PARTNER_URI");
	}

	function InstallDB($arParams = array())
	{
		RegisterModuleDependences('main', 'OnBuildGlobalMenu', self::MODULE_ID, 'CIpdesignK', 'OnBuildGlobalMenu');
		$arFilter = array(
    	"TYPE_ID" => "SENDWISHLIST",
    );
		$rsET = CEventType::GetList($arFilter);
		$arET = $rsET->Fetch();
		if(empty($arET)){
			$et = new CEventType;
			$arMessageFields = array("USER_ID" => $UserProduct['USER']['ID'],
													 "USER_NAME" => $UserProduct['USER']["NAME"].' '.$UserProduct['USER']["LAST_NAME"],
													 "USER_EMAIL" => $UserProduct['USER']["EMAIL"],
													 "PRODUCTS_LIST" => $strListProduct);
			$DESCRIPTION = '#USER_ID# - '.GetMessage("IT_BASKET_SENDER_USER_ID");
			$DESCRIPTION .= '\n#USER_NAME# - '.GetMessage("IT_BASKET_SENDER_USER_NAME");
			$DESCRIPTION .= '\n#USER_EMAIL# - '.GetMessage("IT_BASKET_SENDER_USER_EMAIL");
			$DESCRIPTION .= '\n#PRODUCTS_LIST# - '.GetMessage("IT_BASKET_SENDER_PRODUCTS_LIST");
	    $sendType = $et->Add(array(
	        "LID"           => 'ru',
	        "EVENT_NAME"    => "SENDWISHLIST",
	        "NAME"          => GetMessage("IT_SENDER_TYPE_NAME"),
	        "DESCRIPTION"   => $DESCRIPTION
	        ));
		}else{
			$sendType = $arET['ID'];
		}
		$rsMess = CEventMessage::GetList($by="site_id", $order="desc", array('TYPE_ID' => 'SENDWISHLIST'));
		$arMess = $rsMess->Fetch();
		if(empty($arMess)){
			$message = '';
			$message .= '<h2>'.GetMessage("IT_BASKET_SENDER_TEMPLATE_HELLO").',#USER_NAME#</h2>';
			$message .= '<p>'.GetMessage("IT_BASKET_SENDER_TEMPLATE_WHISHLIST").'</p>';
			$message .= '<hr />';
			$message .= '#PRODUCTS_LIST#';
			$arFields = array("ACTIVE" => 'Y',
												"EVENT_NAME" => GetMessage("IT_SENDER_TYPE_NAME"),
											 "LID" => array("ru","en"),
											 "EMAIL_FROM" => "#SALE_EMAIL#",
											 "EMAIL_TO" => "#USER_EMAIL#",
											 "BCC" => "",
											 "MESSAGE" => $message,
											 "BODY_TYPE" => "html"
										 );
			$emess = new CEventMessage;
			$emess->Add($arFields);
		};
		RegisterModuleDependences("sale", "OnCondSaleActionsControlBuildList", self::MODULE_ID, "CSaleActionCtrlBasketMain", "GetControlDescr");

		return true;
	}

	function UnInstallDB($arParams = array())
	{
		UnRegisterModuleDependences('main', 'OnBuildGlobalMenu', self::MODULE_ID, 'CIpdesignK', 'OnBuildGlobalMenu');
		UnRegisterModuleDependences("sale", "OnCondSaleActionsControlBuildList", self::MODULE_ID, "CSaleActionCtrlBasketMain", "GetControlDescr");
		return true;
	}

	function InstallEvents()
	{
		return true;
	}

	function UnInstallEvents()
	{
		return true;
	}

	function InstallFiles($arParams = array())
	{
		if (is_dir($p = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::MODULE_ID.'/admin'))
		{
			if ($dir = opendir($p))
			{
				while (false !== $item = readdir($dir))
				{
					if ($item == '..' || $item == '.' || $item == 'menu.php')
						continue;
					file_put_contents($file = $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/'.self::MODULE_ID.'_'.$item,
					'<'.'? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/'.self::MODULE_ID.'/admin/'.$item.'");?'.'>');
				}
				closedir($dir);
			}
		}
		if (is_dir($p = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::MODULE_ID.'/install/components'))
		{
			if ($dir = opendir($p))
			{
				while (false !== $item = readdir($dir))
				{
					if ($item == '..' || $item == '.')
						continue;
					CopyDirFiles($p.'/'.$item, $_SERVER['DOCUMENT_ROOT'].'/bitrix/components/'self::MODULE_ID.'/'.$item, $ReWrite = True, $Recursive = True);
				}
				closedir($dir);
			}
		}
		return true;
	}

	function UnInstallFiles()
	{
		if (is_dir($p = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::MODULE_ID.'/admin'))
		{
			if ($dir = opendir($p))
			{
				while (false !== $item = readdir($dir))
				{
					if ($item == '..' || $item == '.')
						continue;
					unlink($_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/'.self::MODULE_ID.'_'.$item);
				}
				closedir($dir);
			}
		}
		if (is_dir($p = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::MODULE_ID.'/install/components'))
		{
			if ($dir = opendir($p))
			{
				while (false !== $item = readdir($dir))
				{
					if ($item == '..' || $item == '.' || !is_dir($p0 = $p.'/'.$item))
						continue;

					$dir0 = opendir($p0);
					while (false !== $item0 = readdir($dir0))
					{
						if ($item0 == '..' || $item0 == '.')
							continue;
						DeleteDirFilesEx('/bitrix/components/'self::MODULE_ID.'/'.$item.'/'.$item0);
					}
					closedir($dir0);
				}
				closedir($dir);
			}
		}
		return true;
	}

	function DoInstall()
	{
		global $APPLICATION;
		$this->InstallFiles();
		$this->InstallDB();
		RegisterModule(self::MODULE_ID);
	}

	function DoUninstall()
	{
		global $APPLICATION;
		UnRegisterModule(self::MODULE_ID);
		$this->UnInstallDB();
		$this->UnInstallFiles();
	}
}
?>
