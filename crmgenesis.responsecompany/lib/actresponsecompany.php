<?php
/**
 * Created by Ivan Karpovets karpovets90@gmail.com
 * Date: 02.02.2020
 * Time: 19:55
 */

namespace Crmgenesis\ResponseCompany;

use Bitrix\Main\Localization\Loc;
use Crmgenesis\Companyactivity\ActCompany;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use CIBlockElement as Element;

Loc::loadMessages( __FILE__ );

Loader::includeModule('crmgenesis.companyactivity');
Loader::includeModule('bizproc');
Loader::includeModule('crm');
Loader::includeModule('iblock');

class ActResponseCompany
{
    
    const MODULE_ID = "crmgenesis.responsecompany";

    /**
     * @brief Получаем ответ из Программы
     * @param array $arParams
     * @return array
     */
    public function getStatusCompany($arParams)
    {
        if ($arParams->contractor_uuid) {
            $company_id = self::getCompanyId($arParams->contractor_uuid);
        }

        if ($arParams->contractor_uuid) {
            $deal_id = self::getDealId($arParams->application_uuid);
        }

        if ($company_id > 0 && $deal_id > 0) {
            $resStatus = self::getStatusResponse($arParams->response_status, $company_id);
            $statusId = $resStatus['STATUS_ID'];
            $fields = [
                'COMPANY_ID'=> $company_id,
                'DEAL_ID'=> $deal_id,
                'STATUS_ID'=> $statusId,
                'RESPONSE_BODY'=> json_encode($arParams->response_status),
                'UUID'=> $arParams->workflow_instance_id,
                'REDIRECT_URL'=> $resStatus['REDIRECT_URL'],
            ];
            $resE = self::createEntityItem($fields);

            $response_from_the_system = false;

            // Пришел ответ, мы понимаем что это ответ положительный и отправляем в Программы запрос что успешно, и апдейтим БП
            if (in_array($statusId, self::STATUS_S)) {

                // отправляем запрос в Программы, что Заявка принята
                $arRes = [
                    'DistributionResult' => [
                        'contractor_uuid' => $arParams->contractor_uuid,
                        'workflow_instance_id' => $arParams->workflow_instance_id,
                        'redirect_url' => $resStatus['REDIRECT_URL'],
                        'result' => 'accepted'
                    ]
                ];
                // отключаем, статус отправляем всем далее
            } else {
                // отправляем запрос в Программы, что Заявка отклонена поставщиком
                $arRes = [
                    'DistributionResult' => [
                        'contractor_uuid' => $arParams->contractor_uuid,
                        'workflow_instance_id' => $arParams->workflow_instance_id,
                        'redirect_url' => $resStatus['REDIRECT_URL'],
                        'result' => 'rejected'
                    ]
                ];
            }

            $url = Option::get(ActCompany::MODULE_ID, 'SAVE_RESULT_SENDER');
            $res_response = ActCompany::sendRequestFinMe($url, $arRes, "PUT");

            if ($res_response['status'] == 'error') {
                return $res_response;
            }

            $resTrue = self::getDealFields($deal_id, 'UF_COMPANY_NS');

            if ($response_from_the_system && !$resTrue) {
                // Пришел ответ, мы знаем что он не успешный, и отправляем в Программы запрос что не успешно, и апдейтим БП
                $res_bp = ActCompany::sendExternalEventW($arParams->workflow_instance_id, $statusId);

                if ($res_bp['status'] == 'error') {
                    return $res_bp;
                }
            } else {
                // отправляем далее на рассылку
                ActCompany::getCompanyList([], $company_id, $arParams->workflow_instance_id, $deal_id, $statusId);
            }

            return $resE;
        }
        
    }

    /**
     * @brief Распарсиваем и получаем статус ответа
     * @param object $statuses
     * @param int $companyId
     * @return array
     */
    private function getStatusResponse($statuses, $companyId) {
        $resRedirect = ['STATUS_ID'=> 0, 'REDIRECT_URL'=> ''];
        $arSelect = Array("ID", "PROPERTY_STATUS_ZAYAVKI", "NAME", "PROPERTY_KOD_OTVETA");
        $arFilter = Array("IBLOCK_ID"=> self::IBLOCK_ID_STATUSES, "=PROPERTY_VYBRAT_PARTNERA"=> $companyId);
        $res = Element::GetList(Array("ID"=> "ASC"), $arFilter, false, false, $arSelect);
        while ($ob = $res->GetNextElement()) {
            $arFields = $ob->GetFields();
            $redirectUrl = '';
            $stringCheck = '"'.$arFields["NAME"].'":"'.$arFields["PROPERTY_KOD_OTVETA_VALUE"].'"';
            if ($statuses->redirectURL) {
                $redirectUrl = $statuses->redirectURL;
            }
            if (strstr(json_encode($statuses), $stringCheck) ) {
                return ['STATUS_ID'=> $arFields["PROPERTY_STATUS_ZAYAVKI_VALUE"], 'REDIRECT_URL'=> $redirectUrl];
            }
        }

        return $resRedirect;

    }

    /**
     * @brief получение Ид ключу UUID
     * @param int $id
     * @return int
     */
    private function getCompanyId($id)
    {
        $companyId = 0;
        if ($id) {
            $params = [
                'filter' => ['UF_FINME_ID_COMP' => $id],
                'select' => ['ID', 'TITLE', 'UF_SORT'],
                'order' => ['UF_SORT' => 'ASC'],
            ];
            if ($companyFields = \Bitrix\Crm\CompanyTable::getRow($params)) {
                $companyId = $companyFields['ID'];
            }
        }
        return $companyId;
    }

    /**
     * @brief получение Ид по ключу UUID
     * @param int $id
     * @return int
     */
    private function getDealId($id)
    {
        $dealId = 0;
        if ($id) {
            $params = [
                'filter' => ['UF_FINME_ID'=> $id],
                'select' => ['ID', 'TITLE'],
            ];
            if ($dealFields = \Bitrix\Crm\DealTable::getRow($params)) {
                $dealId = $dealFields['ID'];
            }
        }
        return $dealId;
    }

    /**
     * @brief получение полей сделки по ИД сделки
     * @param int $id
     * @param string $select
     * @return string
     */
    private function getDealFields($id, $select)
    {
        $dealFields = \Bitrix\Crm\DealTable::getRow([
            'filter' => ['ID'=> $id],
            'select' => ['ID', $select],
        ]);
        return $dealFields[$select];
    }

    /**
     * @brief Записываем результат ответа из Программы
     * @param array $fields
     * @return array
     */
    public function createEntityItem($fields)
    {
        global $USER;
        $params_data = $fields;

        if (!$fields['ID']) {
            $params_data['CREATED_BY_ID'] = $USER->GetID();
            $params_data['DATE_CREATE'] = new \Bitrix\Main\Type\DateTime( date("d.m.Y H:i:s"), "d.m.Y H:i:s" );
        }

        if ($fields['ID'] > 0) {
            $addResult = \Crmgenesis\ResponseCompany\ResponseCompanyTable::update($fields['ID'], $params_data);
        } else {
            $addResult = \Crmgenesis\ResponseCompany\ResponseCompanyTable::add($params_data);
        }

        if (!$addResult->isSuccess())
        {
            $arRes = ['status'=> 'error', 'result'=> $addResult->getErrorMessages()];
        }
        else
        {
            $arRes = ['status'=> 'success', 'result'=> $addResult->getId()];
        }

        return $arRes;
    }

    /**
     * @brief Отправляем событие в БП
     * @param string $uuid
     * @param int $statusId
     * @return array
     */
    private function sendExternalEventW($uuid, $statusId)
    {

        $arErrorsTmp = [];
        $arErrors = [];
        $fatalErrorMessage ='';

        try
        {
            \CBPRuntime::SendExternalEvent(
                $uuid,
                'ResponseCompany',
                array("StatusId" => $statusId, "User" => $GLOBALS["USER"]->GetID()),
                $arErrorsTmp
            );
        }
        catch(Exception $e)
        {
            $arErrors = array(
                "code" => $e->getCode(),
                "message" => $e->getMessage(),
                "file" => $e->getFile()." [".$e->getLine()."]"
            );
        }

        if (!empty($arErrors)) {
            return ["status" => "error", 'text'=> 'Business process not found.'];
        }

    }

    /**
     * @brief Получаем список ответов в список сделок
     * @param int $ownerId
     * @return string
     */
    public function getResponseListHtml($ownerId)
    {
        $html = '';
        if ($ownerId > 0) {
            $filter = ["DEAL_ID" => $ownerId];
            $dbItems = \Crmgenesis\ResponseCompany\ResponseCompanyTable::getList([
                "select" => ["COMPANY"],
                "filter" => $filter,
                "limit" => 10,
            ]);
            $resRows = $dbItems->fetchAll();
            if (!empty($resRows)) {
                foreach ($resRows as $key => $resRow) {
                    $html .= $resRow['CRMGENESIS_RESPONSECOMPANY_RESPONSE_COMPANY_COMPANY_TITLE'].'<br/>';
                }

            }
        }
        return $html;
    }

    /**
     * @brief  Получаем Тайтл Компании
     * @param int $companyId
     * @return string
     */
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

    /**
     * @brief  Получаем Тайтл Статуса
     * @param int $statusId
     * @return string
     */
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

    /**
     * @brief Сохранения логов в файл
     * @param string $title
     * @param array $arr
     */
    public function saveLog($title, $arr = [])
    {
        $log_file = $_SERVER['DOCUMENT_ROOT'].'/logs/log_'.self::MODULE_ID.'_'.date("d.m.Y").'.txt';
        $mode = 'ab';

        if (!file_exists($log_file)) $mode = 'x';

        if ($fp = fopen($log_file, $mode)) {
            fwrite($fp, $title);
            if (!empty($arr)) {
                fwrite($fp, print_r($arr, true));
            }
            fwrite($fp, "\n");
            fclose($fp);
        }

    }

}
