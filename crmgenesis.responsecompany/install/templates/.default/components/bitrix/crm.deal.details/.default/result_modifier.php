<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
use \Bitrix\Main\Localization\Loc;

$defTemplateFolder = $componentPath . "/templates/".basename(__DIR__)."/";



$file = $_SERVER["DOCUMENT_ROOT"] . $defTemplateFolder . basename( __FILE__ );
if( file_exists( $file ) )
{
    require $file;
}
if ( \Bitrix\Main\Loader::includeModule( 'crmgenesis.responsecompany' ) && $arResult["ENTITY_ID"] > 0)
{

    $arResult["TABS"][] = [
        'id' => 'tab_slips',
        'name' => Loc ::getMessage( 'TAB_RESPONSE_COMPANY' ),
        'loader' => [
            'serviceUrl' => getLocalPath( "components/crmgenesis/response.company/lazyload.ajax.php" ) . '?&site=' . SITE_ID . '&' . bitrix_sessid_get() . '&entityId=' .$arResult["ENTITY_ID"],
            'componentData' => [
                'template' => '',
                'params' => array(
                    'DEAL_ID' => $arResult["ENTITY_ID"],
                )
            ]
        ]
    ];
}
