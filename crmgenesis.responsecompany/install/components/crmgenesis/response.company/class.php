<?php
/**
 * Created by Ivan Karpovets. Company CRMGENESIS
 * karpovets90@gmail.com
 * Date: 02.02.2020
 * Time: 10:00
 */
if ( !defined( 'B_PROLOG_INCLUDED' ) || B_PROLOG_INCLUDED !== true )
    die();

use Bitrix\Main\Loader,
    Bitrix\Main\Config\Option,
    Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
\Bitrix\Main\Loader::includeModule( 'crmgenesis.responsecompany' );

class CIcrmgenesisResponseCompany extends CBitrixComponent
{

    protected $gridId = "response_company";
    protected $gridOptions;

    protected function init()
    {
        $this->gridOptions = new \Bitrix\Main\Grid\Options($this->gridId);
    }

    public function onPrepareComponentParams ( $arParams )
    {
        global $USER;
        $arParams["USER_ID"] = $USER->getId();
        $arParams["IS_ADMIN"] = $USER->isAdmin();
        if ($arParams["OWNER_ID"] > 0) {
            $arParams["OWNER_ID"] = intval($arParams["OWNER_ID"]);
        } else {
            $arParams["OWNER_ID"] = intval($_REQUEST["entityId"]);
        }

        return $arParams;
    }

    public function executeComponent ()
    {
        if (!$this->arParams["OWNER_ID"] ||
            !Loader::includeModule('iblock') ||
            !Loader::includeModule('crm') ||
            !Loader::includeModule('crmgenesis.responsecompany'))
        {
            return;
        }

        $this->init();

        $this->arResult = [
            "COLUMNS" => $this->getListsColumn(),
            "PARAMS" => $this->getPreventData($this->arParams["OWNER_ID"])
        ];
        $this->arResult["GRID_ID"] = $this->gridId;

        $this->includeComponentTemplate();
    }

    private function getListsColumn()
    {
        $columns = [
            ['id' => 'ID', 'name' => 'ID', 'sort' => 'ID', 'default' => false],
            ['id' => 'CRMGENESIS_RESPONSECOMPANY_RESPONSE_COMPANY_COMPANY_TITLE', 'name' => Loc::getMessage('COMPANY_NAME'), 'sort' => 'COMPANY_NAME', 'default' => true],
            ['id' => 'DEAL_ID', 'name' => Loc::getMessage('DEAL_ID'), 'sort' => 'DEAL_ID', 'default' => false],
            ['id' => 'CRMGENESIS_RESPONSECOMPANY_RESPONSE_COMPANY_DEAL_TITLE', 'name' => Loc::getMessage('DEAL_ID'), 'sort' => 'DEAL_ID', 'default' => false],
            ['id' => 'RESPONSE_BODY', 'name' => Loc::getMessage('RESPONSE_BODY'), 'sort' => 'RESPONSE_BODY', 'default' => true],
            ['id' => 'STATUS_NAME', 'name' => Loc::getMessage('STATUS_NAME'), 'sort' => 'STATUS_NAME', 'default' => true],
            ['id' => 'REDIRECT_URL', 'name' => Loc::getMessage('REDIRECT_URL'), 'sort' => 'REDIRECT_URL', 'default' => true],
            ['id' => 'DATE_CREATE', 'name' => Loc::getMessage('DATE_CREATE'), 'sort' => 'DATE_MODIFY', 'default' => true],
        ];

        return $columns;
    }

    private function getPreventData($ownerId)
    {
        global $USER;
        $userId = $USER->getId();
        $sorting = $this->gridOptions->GetSorting(array("sort" => array("ID" => "DESC")));
        $navParams = $this->gridOptions->GetNavParams();
        $pageSize = $navParams['nPageSize'];

        $nav = new \Bitrix\Main\UI\PageNavigation($this->gridId);
        $nav->allowAllRecords(false)
            ->setPageSize($pageSize)
            ->initFromUri();


        $resEl = [];
        $filter = [];
        $rowCount = 0;

        $filter = ["DEAL_ID"=> $ownerId];

        $dbItems = \Crmgenesis\ResponseCompany\ResponseCompanyTable::getList([
            "select" => ["*", "COMPANY", "DEAL"],
            "filter" => $filter,
            "order" => $sorting["sort"],
            "offset" => $nav->getOffset(),
            "limit" => $nav->getLimit() + 1,
            "count_total" => 1
        ]);
        $resRows = $dbItems->fetchAll();
        if (!empty($resRows)) {
            foreach ($resRows as $key=> $resRow) {
                $rowCount++;
                $actions = [];
                
                $data = $resRow;

                $resEl[] = [
                    'data'    => $data,
                    'actions' => $actions,
                ];
            }

        }

        $nav->setRecordCount($nav->getOffset() + $rowCount);

        return ['LISTS'=> $resEl, 'SORT'=> $sorting["sort"], 'SORT_VARS'=> $sorting["vars"], 'NAV_OBJECT'=> $nav, "ROWS_COUNT" => $dbItems->getCount()];
    }

    private function getCompanyName($companyId)
    {
        $result = '';
        if ($companyId > 0)
        {
            $params = [
                'filter' => ['=ID' => $companyId],
                'select' => ['ID', 'TITLE']
            ];

            if ($company = \Bitrix\Crm\CompanyTable::getRow($params))
            {
                $result = (string)'<a href="/crm/company/details/' . $company['ID'] . '/">' . $company['TITLE'] . '</a>';
            }
        }
        return $result;
    }

    private function getStatusName($statusId)
    {
        $result = '';
        if ($statusId > 0)
        {
            $params = [
                'filter' => ['=ID' => $statusId],
                'select' => ['ID', 'NAME']
            ];

            if ($status = \Bitrix\Iblock\ElementTable::getRow($params))
            {
                $result = (string)$status['NAME'];
            }
        }
        return $result;
    }
}
