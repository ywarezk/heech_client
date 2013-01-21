<?php

/**
 * used to classify user role for the cms
 * @author Yariv Katz
 * @copyright Nerdeez.com
 * @version 1.0
 */
class Application_Plugin_AccessCheck extends Zend_Controller_Plugin_Abstract {
	
        /**
         * holds refrence to zend acl
         * @var Zend_Acl 
         */
	private $_acl=null;
        
        /**
         * holds reference to zend auth
         * @var Zend_Auth 
         */
	private $_auth=null;
	
        /**
         * constractor
         * @param Zend_Acl $acl refers to zend acl mechanizem
         * @param Zend_Auth $auth refers to zend auth system
         */
	public function __construct(Zend_Acl $acl,Zend_Auth $auth){
		$this->_acl=$acl;
		$this->_auth=$auth;
	} 
	
        /**
         * based on the page and the role determine if user can view this file
         * @param Zend_Controller_Request_Abstract $request the request 
         */
	public function preDispatch(Zend_Controller_Request_Abstract  $request){
		$resource=$request->getControllerName();
		$action=$request->getActionName();
		//get user role
		$identity = $this->_auth->getIdentity();
		if($identity==null){
			$role='guest';
		}
		else{
                    switch($identity->role){
                        case 0:
                            $role = 'guest';
                            break;
                        case 1:
                            $role = 'user';
                            break;
                        case 2:
                            $role = 'admin';
                            break;
                        default:
                            $role = 'guest';
                            break;
                    }
		}
		
		if(!$this->_acl->isAllowed($role,$resource,$action)){
			$request->setControllerName('error');
			$request->setActionName('error');
			$request->setParam("errortitle","Security");
                        $request->setParam("errormessage",array("You are not allowed to view this file"));
		}
		
	}
}

?>
