<?php

class Default_Model_Pupils extends Mine_Model_Abstract
{      
    public function __construct ()
    {
        $table = new Default_Model_DbTable_Pupils();
        $this->setTable($table);
    }

    public function getAllForPaging($filters, $orders, $limits)
    {

        if ($orders['sort'] == 'name' || ($orders['sort']=='date_birth' && isset($filters['search']) && $filters['search'])) {
            $select = $this->selectOName($filters, $orders, $limits);
            return $this->fetchAllForPaging($select, $limits);
        } else if ((!isset($filters['search']) || !$filters['search']) && $orders['sort'] == 'date_birth') {
            $select = $this->selectOName($filters, $orders, $limits);
            return $this->fetchAllForPaging($select, $limits);
        } else if ($orders['sort'] == 'id') {
            return $this->selectOId($filters, $orders, $limits);
        }

    }

    private function selectOId ($filters, $orders, $limits) {
        if (isset($filters['search']) && $filters['search'] ||
            isset($filters['date_birth_from']) && $filters['date_birth_from'] ||
            isset($filters['date_birth_to']) && $filters['date_birth_to'] ||
            isset($filters['id']) && $filters['id'])
        {
            $items = array();
            $select = $this->selectOName($filters, null, $limits);
            $select->reset(Zend_Db_Select::COLUMNS)->columns(new Zend_Db_Expr('COUNT(*)'));
            $items['total'] = $this->getDb()->fetchOne($select);
            $select->reset(Zend_Db_Select::COLUMNS)->columns(new Zend_Db_Expr('a.id'));
            $select->limit($limits['limit'], $limits['start']);
            $select = "Select STRAIGHT_JOIN p.* from `pupils` as p FORCE INDEX (PRIMARY) ," .
                "(". $select . ") as ga " .
                "where p.id = ga.`id` order by p.id {$orders['dir']}";
            $items['data'] = $this->getDb()->fetchAll($select);
            return $items;
        } else {

            $select = $this->getDb()->select()
                ->from(array('a'=>'pupils'), 'a.*');
            $this->orderBy($select, $orders);

            return $this->fetchAllForPaging($select, $limits);
        }

    }

    private function selectOName($filters, $orders, $limits) {
        $select = $this->getDb()->select()
            ->from(array('a'=>'pupils'), 'a.*');

        if (isset($filters['search']) && !empty($filters['search'])) {
            $select->where( 'name like ' .  $this->getDb()->quote($filters['search'] . "%") );
        }

        if (isset($filters['date_birth_from']) && $filters['date_birth_from']) {
            $select->where($this->getDb()->quoteInto('date_birth >= ?', $filters['date_birth_from']));
        }

        if (isset($filters['date_birth_to']) && $filters['date_birth_to']) {
            $select->where($this->getDb()->quoteInto('date_birth <= ?', $filters['date_birth_to']));
        }

        if (isset($filters['id']) && intval($filters['id']) != 0) {
            $select->join(array('tp'=>'teacher_pupils'), 'tp.pupil_id = a.id and tp.teacher_id='.intval($filters['id']), '');
        }
        if ($orders) {
            $orders['sort'] = 'a.' . $orders['sort'];
            $this->orderBy($select, $orders);
        }
        return $select;
    }

    public function existsName ($name)
    {
        $select = $this->getDb()->select()
            ->from(array('a'=>'pupils'), 'id')
            ->where($this->getDb()->quoteInto('name = ?', $name));
        return $this->getDb()->fetchOne($select);
    }

    public function existsEmail ($email)
    {
        $select = $this->getDb()->select()
            ->from(array('a'=>'pupils'), 'id')
            ->where($this->getDb()->quoteInto('email = ?', $email));
        return $this->getDb()->fetchOne($select);
    }

}

