<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

class crmgenesis_responsecompany extends \CModule
{
    const MODULE_ID = 'crmgenesis.responsecompany';

    public function __construct()
    {
        $arModuleVersion = array();

        include __DIR__ . '/version.php';

        if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion))
        {
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        }

        $this->MODULE_ID = self::MODULE_ID;
        $this->MODULE_NAME = Loc::getMessage(self::MODULE_ID.'_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage(self::MODULE_ID.'_MODULE_DESCRIPTION');
        $this->MODULE_GROUP_RIGHTS = 'N';
        $this->PARTNER_NAME = Loc::getMessage(self::MODULE_ID.'_MODULE_PARTNER_NAME');
        $this->PARTNER_URI = Loc::getMessage(self::MODULE_ID.'_MODULE_PARTNER_URI');
    }

    public function InstallFiles($arParams = array())
    {
        // CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/local/modules/".self::MODULE_ID."/install/components", $_SERVER["DOCUMENT_ROOT"]."/local/components", true, true);
        // CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/local/modules/".self::MODULE_ID."/install/templates", $_SERVER["DOCUMENT_ROOT"]."/local/templates", true, true);
        return true;
    }

    public function UnInstallFiles()
    {
        //DeleteDirFilesEx("/local/components/crmgenesis/response.company");
        return true;
    }

    public function doInstall()
    {
        ModuleManager::registerModule($this->MODULE_ID);
        if (Loader::includeModule($this->MODULE_ID))
        {
            $this->InstallDB();
        }

    }

    public function doUninstall()
    {
        global $APPLICATION, $step;
        $step = IntVal($step);
        if($step<2)
            $APPLICATION->IncludeAdminFile(Loc::getMessage(self::MODULE_ID."_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/local/modules/".$this->MODULE_ID."/install/unstep1.php");
        elseif($step==2)
        {
            $this->UnInstallDB(array(
                "savedata" => $_REQUEST["savedata"],
            ));

            $GLOBALS["errors"] = $this->errors;

            $APPLICATION->IncludeAdminFile(Loc::getMessage(self::MODULE_ID."_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/local/modules/".$this->MODULE_ID."/install/unstep2.php");
        }
    }

    public function InstallDB($arParams = array())
    {
        global $DB, $APPLICATION;

        if (!$DB->Query("SELECT 'x' FROM crmgenesis_responsecompany", true))
        {
            $errors = $DB->RunSQLBatch(__DIR__ . "/db/" . strtolower($DB->type) . '/install.sql');
        }

        if (!empty($errors))
        {
            $APPLICATION->ThrowException(implode("", $errors));
            return false;
        }

        return true;
    }

    public function UnInstallDB($arParams = array())
    {
        global $DB;
        if (array_key_exists("savedata", $arParams) && $arParams["savedata"] != "Y") {
            $DB->RunSQLBatch(__DIR__ . "/db/" . strtolower($DB->type) . '/uninstall.sql');
        }

        \Bitrix\Main\Config\Option::delete($this->MODULE_ID);
        ModuleManager::unregisterModule($this->MODULE_ID);
        return true;
    }
}
