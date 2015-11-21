<?php

class Mine_Api_DateTime {
    const formatDDMMYYYYdot = 'd.m.Y';
    const formatDDMMYYYYHHMMdot = 'd.m.Y H:i';
    const formatMusqlDate   = 'Y-m-d';
    
    public static function curDateMySql()
    {
        return date('Y-m-d H:i:s');
    }
    
    public static function dateRangeBegToMySql($date)
    {
        return date('Y-m-d 00:00:00', strtotime($date));
    }
    
    public static function dateRangeEndToMySql($date)
    {
        return date('Y-m-d 23:59:59', strtotime($date));
    }


    public static function DateMySqlToJqueryDatePicker($date)
    {
        return date('Y-m-d', strtotime($date));
    }
    
    public static function dateDiffDays($unixDate, $unixDateSub)
    {
        $dateDiff = $unixDate - $unixDateSub;
        return round($dateDiff/(60*60*24));
    }
    
    public static function stringToMysqlString($strDate, $format)
    {
        $formattedDate = '';
        switch ($format) {
            case self::formatDDMMYYYYdot:
                $dateParts = explode('.', $strDate);
                $formattedDate = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0] . ' 00:00:00';
                break;
            case self::formatMusqlDate:
                $formattedDate = $strDate . ' 00:00:00';
                break;
        }
        return $formattedDate;
    }
    
    public static function unixToString($unixDate, $format=null)
    {
        if ($format==null) {
            $format = self::formatDDMMYYYYHHMMdot;
        }
        return date($format, $unixDate);
    }
}

