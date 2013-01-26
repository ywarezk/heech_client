<?php

/**
 * required in all my controllers
 */
require_once APPLICATION_PATH . '/controllers/Nerdeez_Controller_Action.php';

/**
 * controller for the facebook application
 *
 * @author Yariv Katz
 * @copyright Heechikerz.com
 * @version 1.0
 */
class FacebookController extends Nerdeez_Controller_Action{
    
    /**
     * main index page
     */
    public function indexAction()
    {
        // action body
    }
    
    /**
     * redirect the user here in order to login
     */
    public function loginAction(){
        //if the user is logged in redirect back to the index page
        if ($this->isFBLoggedIn()){
            $this->_redirector->gotoSimple('index', 'facebook');
            return;
        }
        
        //get the login url
        $this -> view -> sLoginUrl = $this->_facebook->getLoginUrl();
    }
    
    /**
     * take the drive details from the user
     */
    public function detailsAction(){
        //pass the type to the view
        $this->view->sType = $this->_aData['type'];
        
        //load the css needed for the calendar
        $layout = new Zend_Layout();
        $layout -> getView() -> headLink()->prependStylesheet($this->view->baseUrl('styles/jquery-ui.css'));
        $layout -> getView() -> headLink()->prependStylesheet($this->view->baseUrl('styles/jquery.ui.timepicker.css'));
    }
    
}

?>
