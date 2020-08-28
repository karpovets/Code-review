<?php
/**
* Created by Ivan Karpovets. Company CRMGENESIS
* karpovets90@gmail.com
* Date: 02.02.2020
* Time: 10:00
*/
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

?>

<div class="leac-title-wrap">
    <div class="leac-title-menu">
        <div class="sidebar-buttons" style="height: 60px">

        </div>
    </div>
</div>

<?

$APPLICATION->IncludeComponent(
    "bitrix:main.ui.grid",
    "",
    array(
        "GRID_ID" => $arResult["GRID_ID"],
        "HEADERS" => $arResult['COLUMNS'],
        "ROWS" => $arResult['PARAMS']['LISTS'],
        "NAV_OBJECT" => $arResult['PARAMS']["NAV_OBJECT"],
        "SORT" => $arResult['PARAMS']["SORT"],
        "ALLOW_COLUMNS_SORT" => true,
        "ALLOW_SORT" => true,
        "ALLOW_PIN_HEADER" => true,
        "SHOW_PAGINATION" => true,
        "SHOW_PAGESIZE" => true,
        "SHOW_ROW_CHECKBOXES" => true,
        "SHOW_CHECK_ALL_CHECKBOXES" => false,
        "SHOW_SELECTED_COUNTER" => false,
        "PAGE_SIZES" => array(
            array("NAME" => "10", "VALUE" => "10"),
        ),
        'SHOW_ACTION_PANEL' => true,
        "TOTAL_ROWS_COUNT" => $arResult['PARAMS']['ROWS_COUNT'],
        "AJAX_MODE" => "Y",
        "AJAX_ID" => CAjax::getComponentID('bitrix:main.ui.grid', '.default', ''),
        "AJAX_OPTION_JUMP" => "N",
        "AJAX_OPTION_HISTORY" => "N",
    ),
    $component, array("HIDE_ICONS" => "Y")
);
?>
