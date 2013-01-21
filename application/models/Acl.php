<?php

/**
 * used to classify the security constraints
 * @author Yariv Katz
 * @copyright Nerdeez.com
 * @version 1.0
 */
class Application_Model_Acl extends Zend_Acl {
	
	function __construct() { 
            //define the resources
            $this->add(new Zend_Acl_Resource('error'));
            $this->add(new Zend_Acl_Resource('index'));
            $this->add(new Zend_Acl_Resource('login')); 
            $this->add(new Zend_Acl_Resource('register')); 
            $this->add(new Zend_Acl_Resource('admin'));
            $this->add(new Zend_Acl_Resource('user'));
            $this->add(new Zend_Acl_Resource('course'));
		
            //define the participents
            $this->addRole(new Zend_Acl_Role('guest'));
            $this->addRole(new Zend_Acl_Role('user'),'guest');
            $this->addRole(new Zend_Acl_Role('admin'),'user');
		
            //guest securityconstraints
            $this->allow('guest','error');
            $this->allow('guest','index');
            $this->allow('guest','login');
            $this->allow('guest','register');
            $this->allow('guest','course');
                
            //user securityconstraints
            $this->allow('user','user');
	
            //admin security constraints that doesnt really exist
            $this->allow('admin','admin');
	} 
}

?>
