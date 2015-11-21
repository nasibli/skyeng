<?php
    class Mine_Api_Instance
    {
        public static $session = null;
        
        public static function session($name, $value=null, $attr=null)
        {
            if (isset($value)) {
                self::$session->$name = $value;
            } else {
                return self::$session->$name;
            }
        }
        
        public static function sessionDelete($name) 
        {
            unset(self::$session->$name);
        }
    }
