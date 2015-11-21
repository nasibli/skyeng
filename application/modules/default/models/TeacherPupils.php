<?php

class Default_Model_TeacherPupils extends Mine_Model_Abstract
{      
    public function __construct ()
    {
        $table = new Default_Model_DbTable_TeacherPupils();
        $this->setTable($table);
    }

    /*public function getAllForPaging($filters, $orders, $limits)
    {
        $select = $this->getDb()->select()
            ->from(array('a'=>'teachers'), '*');

        if (isset($filters['search']) && !empty($filters['search'])) {
            $select->where( 'name like ' .  $this->getDb()->quote($filters['search'] . "%") );
        }

        $this->orderBy($select, $orders);

        return $this->fetchAllForPaging($select, $limits);
    }*/

    public function exists ($teacherId, $pupilIds)
    {
        $select = $this->getDb()->select()
            ->from(array('a'=>'teacher_pupils'), array('id', 'pupil_id'))
            ->where('teacher_id = ' . $teacherId)
            ->where('pupil_id in ' . '(' . $pupilIds . ')' );
        return $this->fetchKeyValue($select,'pupil_id', 'id');
    }

    public function bulkInsert($sql)
    {
        //$sql = "SET autocommit=0; \n" . $sql ."\n COMMIT;"
        $this->getDb()->query($sql);
    }

    public function getStat($teacherId)
    {
        $select = $this->getDb()->select()
            ->from(array('a'=>'teacher_pupils'), new Zend_Db_Expr('COUNT(id) as cnt, sum(born_april) apr_cnt'))
            ->where('a.teacher_id = ' . intval($teacherId));
        return $this->getDb()->fetchRow($select);
    }

    public function getMaxPair() {
        $sql = "Select  count(`tg`.pupil_id) as pupil_count , tg.teacher_id, tg.teacher_id2 from
            (Select p.teacher_id, p1.teacher_id as teacher_id2, p.pupil_id
            from
            teacher_pupils as p
            inner join teacher_pupils as p1 on p.pupil_id = p1.pupil_id and p.teacher_id != p1.`teacher_id`) as tg
            group by tg.teacher_id, tg.teacher_id2
            order by pupil_count desc
            limit 1";

        return $this->getDb()->fetchRow($sql);
    }

    public function getMaxPairPupils($teacher1, $teacher2) {
        $sql = "Select a.pupil_id, a.teacher_id, b.teacher_id as teacher_id2, p.name from teacher_pupils as a
            inner join teacher_pupils as b on b.pupil_id=a.pupil_id and b.`teacher_id` = $teacher1
            inner join pupils as p on a.`pupil_id` = p.id
            where a.`teacher_id` = $teacher2";
        return $this->getDb()->fetchAll($sql);
    }

}

