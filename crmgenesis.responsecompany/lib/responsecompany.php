<?php
/**
 * Created by Ivan Karpovets karpovets90@gmail.com
 * Date: 02.02.2020
 * Time: 19:55
 */

namespace Crmgenesis\ResponseCompany;

use Bitrix\Main\Localization\Loc;

use Bitrix\Main\ORM\Fields;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;

Loc::loadMessages( __FILE__ );

\Bitrix\Main\Loader::includeModule( 'crm' );

class ResponseCompanyTable extends \Bitrix\Main\ORM\Data\DataManager
{
    public static function getTableName()
    {
        return 'crmgenesis_responsecompany';
    }

    public static function getUfId()
    {
        return "CRMGENESIS_RESPONSE_COMPANY";
    }

    public static function getMap()
    {
        return [
            'ID' => new Fields\IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true,
            ]),

            'COMPANY_ID' => new Fields\IntegerField('COMPANY_ID', [
                'required' => true,
                'title' => 'COMPANY_ID',
            ]),

            'DEAL_ID' => new Fields\IntegerField('DEAL_ID', [
                'required' => true,
                'title' => 'DEAL_ID',
            ]),


            'RESPONSE_BODY' => new Fields\TextField('RESPONSE_BODY', [
                'title' => 'RESPONSE_BODY',
            ]),

            'UUID' => new Fields\StringField('UUID', [
                'title' => 'UUID',
            ]),

            'REDIRECT_URL' => new Fields\StringField('REDIRECT_URL', [
                'title' => 'REDIRECT_URL',
            ]),

            'DATE_CREATE' => new Fields\DatetimeField('DATE_CREATE', [
                'default_value' => new \Bitrix\Main\Type\DateTime(),
                'title' => 'DATE_CREATE',
            ]),

            'DATE_MODIFY' => new Fields\DatetimeField('DATE_MODIFY', [
                'default_value' => new \Bitrix\Main\Type\DateTime(),
                'title' => 'DATE_MODIFY',
            ]),

            'CREATED_BY_ID' => new Fields\IntegerField('CREATED_BY_ID', [
                'title' => 'CREATED_BY_ID',
            ]),

            'MODIFY_BY_ID' => new Fields\IntegerField('MODIFY_BY_ID', [
                'title' => 'MODIFY_BY_ID',
            ]),


            //REFERENCE FIELDS https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=43&CHAPTER_ID=011735&LESSON_PATH=3913.5062.5748.11735
            'DEAL'=> (new Reference(
                'DEAL',
                'Bitrix\Crm\DealTable',
                Join::on('this.DEAL_ID', 'ref.ID')
            ))
                ->configureJoinType('inner'),

            'COMPANY'=> (new Reference(
                'COMPANY',
                'Bitrix\Crm\CompanyTable',
                Join::on('this.COMPANY_ID', 'ref.ID')
            ))
                ->configureJoinType('inner'),
        ];
    }
}
