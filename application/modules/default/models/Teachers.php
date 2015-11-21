<?php

class Default_Model_Teachers extends Mine_Model_Abstract
{      
    public function __construct ()
    {
        $table = new Default_Model_DbTable_Teachers();
        $this->setTable($table);
    }

    public function getAllForPaging($filters, $orders, $limits)
    {
        $select = null;
        if ($orders['sort']=='name' || $orders['sort']=='only_april') {
            $select = $this->selectOName($filters, $orders, $limits);
            return $this->fetchAllForPaging($select, $limits);
        } else if ($orders['sort']=='id') {
            return $this->selectOId($filters, $orders, $limits);
        }


    }

    private function selectOId ($filters, $orders, $limits) {
        if (isset($filters['search']) && $filters['search'] || isset($filters['only_april']) && $filters['only_april']) {
            $items = array();
            $select = $this->selectOName($filters, null, $limits);
            $select->reset(Zend_Db_Select::COLUMNS)->columns(new Zend_Db_Expr('COUNT(*)'));
            $items['total'] = $this->getDb()->fetchOne($select);

            $select->reset(Zend_Db_Select::COLUMNS)->columns(new Zend_Db_Expr('id'));
            $select->limit($limits['limit'], $limits['start']);
            $select = "Select STRAIGHT_JOIN t.* from `teachers` as t FORCE INDEX (PRIMARY) ," .
                "(". $select . ") as ga " .
            "where t.id = ga.`id` order by id {$orders['dir']}";
            $items['data'] = $this->getDb()->fetchAll($select);
            return $items;
        } else {
            $select = $this->getDb()->select()
                ->from(array('a'=>'teachers'), '*');
            $this->orderBy($select, $orders);
            return $this->fetchAllForPaging($select, $limits);
        }
    }

    //если сортировка по name
    private function selectOName($filters, $orders, $limits, $fields = null) {
        $select = $this->getDb()->select()
            ->from(array('a'=>'teachers'), $fields ? $fields : '*');

        if (isset($filters['search']) && !empty($filters['search'])) {
            $select->where( 'name like ' .  $this->getDb()->quote($filters['search'] . "%") );
        }

        if (isset($filters['only_april']) && $filters['only_april'] == 1) {
            $select->where( 'only_april=1' );
        } else {
            $select->where('only_april in (1,0)');
        }

        if ($orders) {
            $this->orderBy($select, $orders);
        }

        return $select;
    }

    public function existsName ($name)
    {
        $select = $this->getDb()->select()
            ->from(array('a'=>'teachers'), 'id')
            ->where($this->getDb()->quoteInto('name = ?', $name));
        return $this->getDb()->fetchOne($select);
    }

}

