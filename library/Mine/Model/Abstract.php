<?php

class Mine_Model_Abstract
{
    private   $_table   = null;
    protected $_primary = null;
    protected $_db       = null;

    protected function setTable(&$table)
    {
        $this->_table = $table;
        $primary = $this->_table->info('primary');
        $this->_primary = $primary[1];
        $this->_table->getAdapter()->setFetchMode(Zend_Db::FETCH_ASSOC);
    }

    public function save($data, $id=null)
    {
        if (isset($id) && $id) {
            return $this->_table->update($data, $this->_primary . ' = ' . $id);
        } else {
            return $this->_table->insert($data);
        }
    }

    public function delete($id)
    {
        if (!$id) {
            return false;
        }
        $this->_table->delete($this->_table->getAdapter()->quoteInto($this->_primary . ' = ?', $id));
    }

    public function getById($id)
    {
        if (!$id ) {
            return array();
        }
        $id = intval($id);
        $select = $this->_table->select()->where($this->_table->getAdapter()->quoteInto($this->_primary . '=?', $id));
        return $this->_table->getAdapter()->fetchRow($select);
    }

    public function getDb()
    {
        return $this->_table->getAdapter();
    }

    public function fetchKeyValue ($select, $keyField, $valueField)
    {
        $items = array();
        $statement = $this->getDb()->query($select);

        while ($row = $statement->fetch()) {
            $items[$row[$keyField]] = $row[$valueField];
        }
        return $items;
    }

    public function fetchAllForPaging(&$select, $limits)
    {
        //данные
        $items=array();
        $select->limit($limits['limit'], $limits['start']);
        $items['data'] = $this->getDb()->fetchAll($select);

        //количество
        $select->reset(Zend_Db_Select::COLUMNS)->columns(new Zend_Db_Expr('COUNT(*)'));
        $select->reset(Zend_Db_Select::LIMIT_COUNT);
        $select->reset(Zend_Db_Select::LIMIT_OFFSET);
        $items['total'] = $this->getDb()->fetchone($select);
        return $items;
    }

    public function fetchUnionsForPaging($selects, $orders, $limits)
    {
        //данные
        $select =  $this->getDb()->select()->union($selects);
        $items=array();
        $select->limit($limits['limit'], $limits['start']);
        $this->orderBy($select, $orders);
        $items['data'] = $this->getDb()->fetchAll($select);

        //количество
        foreach ($selects as &$select) {
            $select->reset(Zend_Db_Select::COLUMNS)->columns(new Zend_Db_Expr('COUNT(*) as item_count'));
            $select->reset(Zend_Db_Select::LIMIT_COUNT);
            $select->reset(Zend_Db_Select::LIMIT_OFFSET);
        }
        $select = $this->getDb()->select()->union($selects);
        $counts =  $this->getDb()->fetchAll($select);
        $items['total'] = 0;
        foreach ($counts as $count) {
            $items['total'] += $count['item_count'];
        }
        return $items;
    }

    public function fetchUnionsLimit($selects, $orders, $limits)
    {
        //данные
        $select =  $this->getDb()->select()->union($selects);

        $select->limit($limits['limit'], $limits['start']);
        $this->orderBy($select, $orders);
        return $this->getDb()->fetchAll($select);
    }

    public function fetchLimit(&$select, $limits)
    {
        $select->limit($limits['limit'], $limits['start']);
        return $this->getDb()->fetchAll($select);
    }

    public function orderBy(&$select, $orders)
    {
        if ( isset($orders['sort']) && $orders['sort'] && isset($orders['dir']) && $orders['dir']) {
            $select->order($orders['sort'] . ' ' . $orders['dir']);
        }
    }
}
