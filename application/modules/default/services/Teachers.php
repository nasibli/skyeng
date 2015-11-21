<?php
class Default_Service_Teachers
{
    
    private $_teachersModel = null;

    public function __construct () 
    {
        $this->_teachersModel = new Default_Model_Teachers();
    }
    
    public function save($post) 
    {
        $res = array('success'=>true, 'errors' => array() );

        if (!isset($post['name']) || empty($post['name'])) {
            $res['success']=false;
            $res['errors'][] = 'Имя обязательно для ввода';
        }

        if (!isset($post['phone']) || empty($post['phone'])) {
            $res['success']=false;
            $res['errors'][] = 'Телефон обязателен для ввода';
        }

        if (!isset($post['gender']) || !in_array($post['genderId'], array(1,2))  ) {
            $res['success']=false;
            $res['errors'][] = 'Пол обязателен для ввода';
        }

        if ($this->_teachersModel->existsName($post['name'])) {
            $res['success']=false;
            $res['errors'][] = 'Пользователь с именем ' . $post['name']  . ' уже существует, введите другое имя';
        }

        if (!$res['success']) {
            return $res;
        }

        $this->_teachersModel->save(array('name'=>$post['name'], 'gender'=>$post['genderId'], 'phone'=>$post['phone']));

        return $res;
    }

    public function addPupils ($post)
    {

        $post = Zend_Json::decode($post['data_']);
        $post = $post['data'];
        $res = array('success'=>true, 'errors'=>array());
        if (!isset($post['id']) || !Zend_Validate::is($post['id'], 'Int')) {
            $res['success']  = false;
            $res['errors'][] = 'Некорректный идентификатор преподавателя';
        }

        if (!isset($post['pupils']) || !is_array($post['pupils'])) {
            $res['success']  = false;
            $res['errors'][] = 'Некорректный идентификатор преподавателя';
        }

        $pupilIds = '';
        foreach ($post['pupils'] as $pupilId => $value) {
          if (!Zend_Validate::is($pupilId, 'Int')) {
              $res['success']  = false;
              $res['errors'][] = 'Некорректные идентификаторы учеников';
              break;
          }
          $pupilIds .= $pupilIds ? ',' . $pupilId : $pupilId;
        }

        if (!$res['success']) {
            return $res;
        }

        $tpModel = new Default_Model_TeacherPupils();
        $teacherId = $post['id'];
        $exPupils = $tpModel->exists($teacherId, $pupilIds);

        $sqlInsert = "Insert into teacher_pupils (teacher_id, pupil_id, born_april) VALUES ";
        $values = '';
        foreach ($post['pupils'] as $pupilId => $bornApril) {
            if (isset($exPupils[$pupilId])) {
                continue;
            }
            $values .= $values ? ", \n($teacherId, $pupilId, $bornApril)" : "\n($teacherId, $pupilId, $bornApril)";
        }

        if ($values) {
            $tpModel->bulkInsert($sqlInsert . $values);
        }
        $stat = $tpModel->getStat($teacherId);
        if ($stat) {
            $this->_teachersModel->save(array('pupil_count'=>$stat['cnt'], 'only_april' => $stat['cnt']==$stat['apr_cnt'] ? 1 : 0), $teacherId);
        }

        return $res;
    }

    public function getAllForPaging ($filters, $orders, $limits)
    {

        $items = null;
        $items = $this->_teachersModel->getAllForPaging($filters, $orders, $limits);

        return $items;
    }

    public function getMaxPair ()
    {
        $tpModel = new Default_Model_TeacherPupils();
        $teachers = $tpModel->getMaxPair();

        if (! $teachers) {
            return;
        }
        $pupils = $tpModel->getMaxPairPupils($teachers['teacher_id'], $teachers['teacher_id2']);
        return array('teacher1' => $this->_teachersModel->getById($teachers['teacher_id']),
            'teacher2' => $this->_teachersModel->getById($teachers['teacher_id2']), 'pupils' => $pupils);
    }

}

