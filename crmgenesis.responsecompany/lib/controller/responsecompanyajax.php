<?php

namespace Crmgenesis\ResponseCompany\Controller;

use Bitrix\Main\Engine\Controller;

class ResponseCompanyAjax extends Controller
{
    public function setSectionsAction()
    {
        $request = $this->getRequest();
        $sections = $request->get('sections');
        /*
         * что то делаем)
         */
        return ['response' => 'success', 'sect' => $sections];
    }
}
