<?php

class PupilsController extends Zend_Controller_Action
{

    private $_pupilsService = null;

    public function init()
    {
        $this->_pupilsService = new Default_Service_Pupils();
        $this->_helper->AjaxContext()
            ->addActionContext('list', 'json')
            ->addActionContext('save', 'json')
            ->initContext('json');

        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();

    }

    public function listAction () {
        $filters = $this->getFiltersFromPost(array('search', 'date_birth_from', 'date_birth_to', 'id'));
        $orders = $this->getOrdersFromPost('id', 'desc');
        $limits = $this->getLimitsFromPost(0,50);
        $this->view->res = $this->_pupilsService->getAllForPaging ($filters, $orders, $limits);
    }

    public function saveAction () {
        $res = $this->_pupilsService->save($this->getRequest()->getPost());
        $this->view->success = $res['success'];
        $this->view->errors = $res['errors'];
    }

    private function getFiltersFromPost($filterNames)
    {
        $request = $this->getRequest();
        $res = array();
        foreach ($filterNames as $filterName) {
            $res[$filterName] = $request->getParam($filterName, null);
        }
        return $res;

    }

    private function getOrdersFromPost($defaultSort, $defaultDir)
    {
        $request = $this->getRequest();
        $res['sort'] = $this->getRequest()->getParam('sort', $defaultSort);
        $res['dir']  = $this->getRequest()->getParam('dir',  $defaultDir);
        return $res;
    }

    private function getLimitsFromPost($defaultStart, $defaultLimit)
    {
        $request = $this->getRequest();
        $res['start'] = $this->getRequest()->getParam('start', $defaultStart);
        $res['limit'] = $this->getRequest()->getParam('limit',  $defaultLimit);
        return $res;
    }


}

