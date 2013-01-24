<?php

/**
 * required for the extend part
 */
require_once 'Zend/Controller/Action.php';

/**
 * facebook php sdk located in library
 */
require_once("facebook.php");


/**
 * enum for the data we get in post get params
 */
class Nerdeez_ParamTypes{
    const INTEGER = 0;
    const STRING = 1;
    const JSONARRAYNUMBERS = 2;
}

class Nerdeez_Errors{
    const SUCCESS = 0;
    const PASSWORD_MISMATCH = 1;
    const PASSWORD_LENGTH = 2;
    const EMAIL_INVALID= 3;
    const EMAIL_EXISTS= 4;
    const LOGIN_ACTIVATED= 5;
    const LOGIN_FAILED= 6;
    const LOGIN_FAILED_ACTIVATE= 7;
    const LOGIN_PASSWORD_CHANGED= 8;
    const FACEBOOK_REGISTER_FAIL = 9;
    public $MESSAGES = array(
        Nerdeez_Errors::SUCCESS => 'Success',
        Nerdeez_Errors::PASSWORD_MISMATCH => "Retype password don't match",
        Nerdeez_Errors::PASSWORD_LENGTH => "Password length must be more than 5 letters",
        Nerdeez_Errors::EMAIL_INVALID => 'Invalid email format',
        Nerdeez_Errors::EMAIL_EXISTS => 'Email address already exists',
        Nerdeez_Errors::LOGIN_ACTIVATED => 'Your account was successfully activated, You can now login.',
        Nerdeez_Errors::LOGIN_FAILED => 'Invalid email or password.',
        Nerdeez_Errors::LOGIN_FAILED_ACTIVATE => 'You have to activate your account before login.',
        Nerdeez_Errors::LOGIN_PASSWORD_CHANGED => "You're password is changed. You can now login with your new password",
        Nerdeez_Errors::FACEBOOK_REGISTER_FAIL => "facebook registration failure, make sure you are loggd in to facebook.",
    );
}

/**
 * all of nerdeez controllers will extend this class
 *
 * @author Yariv Katz
 * @copyright nerdeez.com Ltd.
 * @version 1.0
 */
abstract class Nerdeez_Controller_Action extends Zend_Controller_Action{
    
    /**
     * redirector helper for other pages
     * @var Zend_Controller_Action_Helper_Redirector 
     */
    protected $_redirector = null;
    
    /**
     * this var will hold all the params sanitized
     * @var Array
     */
    protected $_aData = array();
    
    /**
     * this will hold the facebook php sdk instance
     * @var Facebook facebook php sdk
     */
    protected $_facebook = null;


    /**
     * this array will hold all the possible params from get post
     * @var Array 
     */
    protected $_aParams = array(
        array('name' => 'error_reason' , 'type' => Nerdeez_ParamTypes::STRING , 'length' => 300) ,
        array('name' => 'error' , 'type' => Nerdeez_ParamTypes::STRING , 'length' => 300) ,
        array('name' => 'error_description' , 'type' => Nerdeez_ParamTypes::STRING , 'length' => 300) ,
        array('name' => 'message' , 'type' => Nerdeez_ParamTypes::STRING , 'length' => 300) ,
        array('name' => 'title' , 'type' => Nerdeez_ParamTypes::STRING , 'length' => 100) ,
    );
    
    /**
     * common init for all my controllers
     */
    public function init(){ 
        //set the redirector
        $this->_redirector = $this->_helper->getHelper('Redirector');
        
        //get the params
        $aData=$this->getRequest()->getParams();
        
        //sanitize all the vars and put them in a local array
        foreach ($this->_aParams as $aParam) {
            $sName = $aParam['name'];
            if (!isset ($aData[$sName])) continue;
            $iValue = $aData[$sName];
            $iType = $aParam['type'];
            $iLength = isset ($aParam['length'])? $aParam['length'] : 0;
            $iMin = isset ($aParam['min'])? $aParam['min'] : 0;
            $iMax = isset ($aParam['max'])? $aParam['max'] : 0;
            
            //sanitize integer 
            if ($iType === Nerdeez_ParamTypes::INTEGER){
                if (!is_numeric($iValue)){
                    $this->_redirector->gotoSimple('error', 'error', NULL, array(
                        'title'         => 'Invalid Params',
                        'message'       => 'You gave invalid params',
                    ));
                    return;
                }
                if ( $iValue <= $iMin){
                    $this->_redirector->gotoSimple('error', 'error', NULL, array(
                        'title'         => 'Invalid Params',
                        'message'       => 'You gave invalid params',
                    ));
                    return;
                }
                if ($iMax > 0 && $iValue > $iMax){
                    $this->_redirector->gotoSimple('error', 'error', NULL, array(
                        'title'         => 'Invalid Params',
                        'message'       => 'You gave invalid params',
                    ));
                    return;
                }
            }
            
            //sanitize string
            if ($iType === Nerdeez_ParamTypes::STRING || $iType === Nerdeez_ParamTypes::JSONARRAYNUMBERS){
                if ($this ->sanitize_Title($iValue, $iLength) === NULL){
                    $this->_redirector->gotoSimple('error', 'error', NULL, array(
                        'title'         => 'Invalid Params',
                        'message'       => 'You gave invalid params',
                    ));
                    return;
                }
                
                //sanitize json array
                if ($iType === Nerdeez_ParamTypes::JSONARRAYNUMBERS){
                    $aIds = json_decode(str_replace('\\', '', $iValue));
                    if (is_array($aIds)){
                        foreach($aIds as $iId){
                            if (!is_numeric($iId)){
                                $this->_redirector->gotoSimple('error', 'error', NULL, array(
                                    'title'         => 'Invalid Params',
                                    'message'       => 'You gave invalid params',
                                ));
                                return;
                            }
                            if ( $iId <= $iMin){
                                $this->_redirector->gotoSimple('error', 'error', NULL, array(
                                    'title'         => 'Invalid Params',
                                    'message'       => 'You gave invalid params',
                                ));
                                return;
                            }
                            if ($iMax > 0 && $iId > $iMax){
                                $this->_redirector->gotoSimple('error', 'error', NULL, array(
                                    'title'         => 'Invalid Params',
                                    'message'       => 'You gave invalid params',
                                ));
                                return;
                            }
                        }
                        $iValue = $aIds;
                    }
                    else{
                        $iValue = array($aIds);
                    }
                }
            }
            //value is sanitized now you can put it in our array and sleep in peace
            $this -> _aData[$sName] = $iValue;
        }
        
        //init the facebook sdk
        $this->_facebook = new Facebook(array(
            'appId'         => $this->getFBAppID(),
            'secret'        => $this->getFromConfig('facebook_app_secret'),
            'fileUpload'    => FALSE,
        ));
        
        //if the user is logged in then continue else redirect to login page
        if(!$this->isFBLoggedIn()){
            if ($this->getRequest()->getActionName() !== 'login' 
                    || $this->getRequest() ->getControllerName() != 'facebook'){
                $this->_redirector->gotoSimple('login', 'facebook');
            }
        }
        
        //set the layout
        $layout = new Zend_Layout();
        $layout->setLayoutPath(APPLICATION_PATH . '/layouts/scripts/default.phtml');
        $layout -> sUrl = $this->sGetUrl();
        $layout -> sFBID = $this ->getFBAppID();
        
        //if there is error values redirect them to the error page
        if(isset($this->_aData['error_reason'])){
            $this->_redirector->gotoSimple('error', 'error', NULL, array(
                'title'     => $this->_aData['error_reason'],
                'message'   => $this->_aData['error_description'],
            ));
            return;
        }
    }
    
    /**
     * gets the referer for this page
     * @return String the referer
     */
    protected function getReferer(){
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $referer = $request->getHeader('referer'); 
        //return $referer;
        
        $sRedirectUrl = NULL;
        $aUrl = parse_url($referer);
        $sRedirectUrl = $aUrl['scheme'] . '://' . $aUrl['host'] . $aUrl['path'];
        return $sRedirectUrl;
    }
    
    /**
     * gets the referer for this page
     * @return String the referer
     */
    protected function getRefererWithGetParams(){
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $referer = $request->getHeader('referer'); 
        return $referer;
    }
    
    /**
     *Strips and trims the tag and makes sure the length is no longer than langth chars
     * 
     * @param String $title 
     * @param int $length check the string is no longer than this length
     * @return String null if title is invalid or sanitized title if valid
     */
    protected function sanitize_Title($title , $length){
        if($title == null) return "";
        $title = str_replace('\\', '', $title);
        $link = $this->getMysqlConnection();
        $data = array('title' => mysql_real_escape_string($title , $link));
        $filters=array('title' => array('StringTrim' , 'StripTags'));
        $validators=array('title' => array(array('StringLength', array(0, $length))));
        $input = new Zend_Filter_Input($filters, $validators, $data);
        if(!$input->isValid()){         
            return null;
        }
        $data = $input->getEscaped();
        return $data['title'];       
    }
    
    /**
     * gets the mysql connection data from the config and returns the mysql link resource
     * @return ResourceBundle MySQL link identifier on success or FALSE on failure. 
     */
    private function getMysqlConnection(){
        //connect to config file 
        $config = new Zend_Config_Ini('../application/configs/application.ini','production');
        
        //get host user password 
        $host = $config->resources->db->params->host;
        $user = $config->resources->db->params->username;
        $pass = $config->resources->db->params->password;
        
        $link = mysql_connect('localhost', $user, $pass)
        OR die(mysql_error());
        
        return $link;
    }
    
    /**
     * for the ajax functions call this to disable the view loading
     */
    protected function disableView(){
        $this->_helper->layout()->disableLayout(); 
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
    }
    
    /**
     * gets a value from the config file
     * @param String $sKey the key to retrieve
     * @return String the value 
     */
    protected function getFromConfig($sKey){
        $config = new Zend_Config_Ini('../application/configs/application.ini','production');
        return $config->{$sKey};
    }
    
    /**
     *send mail to {$mail} with  content {$body}
     * 
     * @param String $mail - the mail address
     * @param String $body  - the text content of the mail 
     */
    public function reportByMail($email , $body , $title){
        $mail = new Zend_Mail();
        $mail ->setBodyHtml($body);
        $mail ->setFrom('admin@heechikerz.com');
        $mail ->addTo($email);
        $mail ->setSubject($title);
        $mail ->setReturnPath('yariv@heechikerz.com');
        $mail ->send();
    }
    
    /**
     * when ajax was completed successfully pass it to the user
     */
    public function ajaxReturnSuccess($aExtraData = array()){
        $userData=array(array_merge(array('status'=>'success','data'=>''), $aExtraData));
        $dojoData= new Zend_Dojo_Data('status',$userData);
	echo $dojoData->toJson();
    }
    
    /**
     * when ajax was completed successfully pass it to the user
     * @param String $sMsg the failed message to send
     */
    public function ajaxReturnFailed($aExtraData = array()){
        $userData=array(array_merge(array('status'=>'failed'), $aExtraData));
        $dojoData= new Zend_Dojo_Data('status',$userData);
	echo $dojoData->toJson();
    }
    
    
    /**
     * is this development or production server
     * @return Bool TRUE if this is production server
     */
    protected function isProduction(){
        //server is development
        if ($_SERVER['SERVER_ADDR'] === $this->getFromConfig('developmentip')){
            return FALSE;
        }
        else{
            return TRUE;
        }
    }
    
    /**
     * 
     */
    public function preDispatch() {
        parent::preDispatch();
        
        //set all the js files and css files
        $layout = new Zend_Layout();
        if ($this -> isProduction()){
            $layout -> getView() -> headScript() -> appendFile($this->view->baseUrl('js/static.min.js'));
            //$layout -> getView() -> headLink()->prependStylesheet($this->view->baseUrl('styles/static.min.css'));
        }
        else{
            $layout -> getView() -> headScript() -> prependFile($this->view->baseUrl('js/heech.js'));
            $layout -> getView() -> headScript() -> prependFile($this->view->baseUrl('js/spin.min.js'));
            $layout -> getView() -> headScript() -> prependFile($this->view->baseUrl('js/tooltip.js'));
            $layout -> getView() -> headScript() -> prependFile($this->view->baseUrl('js/jquery.validate.js'));
            $layout -> getView() -> headScript() -> prependFile($this->view->baseUrl('js/jquery-1.9.0.min.js'));
            $layout -> getView() -> headScript() -> prependFile($this->view->baseUrl('js/less-1.3.3.min.js'));
            //$layout -> getView() -> headLink()->prependStylesheet($this->view->baseUrl('styles/styles.less'));
        }
    }
    
    /**
     * init the paginator
     * @param Zend_Db_Table_Select $select the selection from the database
     * @param int $page the page of the paginator
     */
    protected function setPagination($select, $page = 1){
        $adapter = new Zend_Paginator_Adapter_DbSelect($select);
        $paginator = new Zend_Paginator($adapter);
        $paginator->setCurrentPageNumber($page);
        $this -> view -> paginator = $paginator;
    }
    
    /**
     * returns the url of the site
     * @return String the url of the site without http://www. 
     */
    public function sGetUrl(){
        //$https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
      	return
    		/*($https ? 'https://' : 'http://').*/
    		(!empty($_SERVER['REMOTE_USER']) ? $_SERVER['REMOTE_USER'].'@' : '').
    		(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ($_SERVER['SERVER_NAME'].
    		($https && $_SERVER['SERVER_PORT'] === 443 ||
    		$_SERVER['SERVER_PORT'] === 80 ? '' : ':'.$_SERVER['SERVER_PORT']))).
    		substr($_SERVER['SCRIPT_NAME'],0, strrpos($_SERVER['SCRIPT_NAME'], '/'));
    }
    
    /**
     * determine if the user is registered
     * @return Boolean true if registered
     */
    public function isRegistered(){
        $isIdentity = FALSE;
        $auth = Zend_Auth::getInstance();
        $auth->setStorage(new Zend_Auth_Storage_Session('Users'));
        $isIdentity = $auth->hasIdentity();
        return $isIdentity;
    }
    
    /**
     * creates a random salt string
     * @return String
     */
    public function createSaltStringWithLength($length){
        $dynamicSalt = '';
        for ($i = 0; $i < $length; $i++) {
            $dynamicSalt .= chr(rand(33, 126));
        }
        return $dynamicSalt;
    }
    
    /**
     * gets the user info from the auth
     */
    protected function getUserInfo(){
        $auth = Zend_Auth::getInstance();
        $auth->setStorage(new Zend_Auth_Storage_Session('Users'));
        return $auth ->getIdentity();
    }
    
    /**
     * gets the current action url
     * @return String the url we are currently visiting
     */
    public function getUrl(){
        return $this->getRequest()->getHttpHost() . $this->view->url();
    }
    
    /**
     * gets the user details from the db and update the user info
     */
    public function updateUserInfo(){
         //get the model
        $mUsers = new Application_Model_DbTable_Users();
        
        //get the columns
        $aCols = $mUsers ->getModelColumns();
        
        //get the user info
        $oCurrentUser = $this ->getUserInfo();
        
        //get the user row
        $rUser = $mUsers ->getRowWithId($oCurrentUser -> id);
        
        //from the user row create the users object
        $oUser = NULL;
        $oUser = new stdClass();
        foreach ($aCols as $sCol) {
            $oUser -> $sCol = $rUser[$sCol];
        }
        
        //write the object to auth
        $auth = Zend_Auth::getInstance();
        $auth->setStorage(new Zend_Auth_Storage_Session('Users'));
        $auth->getStorage()->write($oUser);
    }
    
    /**
     * 
     * @param String $email the email string to check
     * @return Boolean
     */
    public function isValidEmail($email){
        return preg_match("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$^", $email);
    }
    
    /**
     * when you want to update the auth with a user row
     * @param Zend_Db_Table_Row $rUser the user row to update
     * @return NULL 
     */
    protected function writeUserRowToAuthStorage($rUser){
        //get the users model
        $mModel = new Application_Model_DbTable_Users();
        
        //get the columns from the model
        $aCols = NULL;
        $aCols = $mModel->info(Zend_Db_Table_Abstract::COLS);
        
        //from the user row create the users object
        $oUser = NULL;
        $oUser = new stdClass();
        foreach ($aCols as $sCol) {
            $oUser -> $sCol = $rUser[$sCol];
        }
        
        //write the object to auth
        $auth = Zend_Auth::getInstance();
        $auth->setStorage(new Zend_Auth_Storage_Session('Users'));
        $auth->getStorage()->write($oUser);
    }
    
    /**
     * gets a facebook token and retrieve the facebook object
     * @param String $sToken
     * @return Object the object from facebook
     */
    public function fromFBTokenToObject($sToken){
        $graph_url = "https://graph.facebook.com/me?access_token=" . $sToken;
        $details = json_decode(file_get_contents($graph_url));
        return $details;
    }
    
    /**
     * will return a facebook  id based on this ip
     */
    public function getFBAppID(){
        return $this->getFromConfig('facebook_app_id');
    }
    
    private function parse_signed_request($signed_request) {
        list($encoded_sig, $payload) = explode('.', $signed_request, 2); 

        // decode the data
        $sig = $this -> base64_url_decode($encoded_sig);
        $data = json_decode($this -> base64_url_decode($payload), true);

        if (strtoupper($data['algorithm']) !== 'HMAC-SHA256') {
            $this->_redirector->gotoSimple('error', 'error', NULL, array(
                'title'     => 'Signed request error',
                'message'   => 'Facebook request varification failure',
            ));
            return;
        }

        // Adding the verification of the signed_request below
        $expected_sig = hash_hmac('sha256', $payload, $this->getFromConfig('facebook_app_secret'), $raw = true);
        if ($sig !== $expected_sig) {
            $this->_redirector->gotoSimple('error', 'error', NULL, array(
                'title'     => 'Signed request error',
                'message'   => 'Facebook request varification failure',
            ));
            return;
        }

        return $data;
    }

    private function base64_url_decode($input) {
        return base64_decode(strtr($input, '-_', '+/'));
    }
    
    /**
     * check if the user is logged in with his facebook account
     * @return Boolean true if logged in false if not
     */
    protected function isFBLoggedIn(){
        $user_id = $this -> _facebook->getUser();
        if($user_id){
            return TRUE;
        }
        else{
            return FALSE;
        }
    }
    
}



?>
