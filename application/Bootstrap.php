<?php
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    protected function _initSession()
    {
        Zend_Session::start();
        Mine_Api_Instance::$session = new Zend_Session_Namespace('news_application');
    }
    
    /*public function _initPlugins()
	{
		Zend_Controller_Front::getInstance()->registerPlugin(new Mine_Plugin_ApplicationPredispatch());
	}*/

    public function _initRegistry()
	{
		/*Zend_Registry::set('Zend_Locale', 'fr');*/
        /*Zend_Registry::set('Time_Zone', 'Europe/Paris');*/
        /*$arr = array(
                'adapter' => 'Array',
                'content' => APPLICATION_PATH .  "/../data/locales/fr.php",
                'locale'  => Zend_Registry::get('Zend_Locale')
            );
        $translate = new Zend_Translate($arr);
        Zend_Validate::setDefaultTranslator($translate);*/
            
	}
    
}

