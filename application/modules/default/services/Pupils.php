<?php
class Default_Service_Pupils
{
    
    private $_pupilsModel = null;

    public function __construct () 
    {
        $this->_pupilsModel = new Default_Model_Pupils();
    }

    public function save($post) 
    {
        $res = array('success'=>true, 'errors' => array() );

        if (!isset($post['name']) || empty($post['name'])) {
            $res['success']=false;
            $res['errors'][] = 'Имя обязательно для ввода';
        }

        if (!isset($post['email']) || empty($post['email']) || !Zend_Validate::is($post['email'],'EmailAddress')) {
            $res['success']=false;
            $res['errors'][] = 'Введите корректно электронную почту';
        }

        if (!isset($post['date_birth']) || empty($post['date_birth']) || !strtotime($post['date_birth'])) {
            $res['success']=false;
            $res['errors'][] = 'Введите корректно дату рождения';
        }

        if (!isset($post['level_id']) || empty($post['level_id']) || !in_array($post['level_id'], array(1,2,3,4,5,6))) {
            $res['success']=false;
            $res['errors'][] = 'Укажите корректно уровень';
        }

        if ($this->_pupilsModel->existsName($post['name'])) {
            $res['success']=false;
            $res['errors'][] = 'Пользователь с именем ' . $post['name']  . ' уже существует';
        }

        if ($this->_pupilsModel->existsEmail($post['email'])) {
            $res['success']=false;
            $res['errors'][] = 'Пользователь с почтой ' . $post['email']  . ' уже существует';
        }

        if (!$res['success']) {
            return $res;
        }

        $this->_pupilsModel->save(array('name'=>$post['name'], 'email'=>$post['email'], 'level_id'=>$post['level_id'], 'date_birth' => strtotime($post['date_birth'])));

        return $res;
    }


    public function getAllForPaging ($filters, $orders, $limits)
    {
        if (isset($filters['date_birth_from']) && !empty($filters['date_birth_from'])) {
            $filters['date_birth_from'] = strtotime( Mine_Api_DateTime::dateRangeBegToMySql($filters['date_birth_from']));
        }
        if (isset($filters['date_birth_to']) && !empty($filters['date_birth_to'])) {
            $filters['date_birth_to'] =  strtotime(Mine_Api_DateTime::dateRangeEndToMySql($filters['date_birth_to']));
        }

        $items = null;
        $items = $this->_pupilsModel->getAllForPaging($filters, $orders, $limits);

        foreach ($items['data'] as &$item) {
            $item['date_birth'] = Mine_Api_DateTime::unixToString($item['date_birth'], Mine_Api_DateTime::formatDDMMYYYYdot);
        }

        return $items;
    }

}

