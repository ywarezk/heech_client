<?php
/**
 * This class contains diffrent auxilary function thats in use by all the classes
 *
 * @author Yariv Katz
 * @copyright Knowledge-Share.com Ltd.
 */
class Application_Model_KSFunctions {  
    
    //constants
    //constants will always start with c
    const cPOSTERRORTITLE = 'Bad message post params';
    
    const cPOSTERRORVALUESERIAL = "Please don't modify the serial field";
    
    const cPOSTERRORVALUETITLE = "Title must be shorter than 200 characters";
    
    const cPOSTERRORVALUEDISCLAIMER = "You must read and agree to the disclaimer";
    
    const cPOSTERRORVALUEDIR = "Bad Directory please dont change the hidden fields";
    
    const cPOSTERRORVALUEDIRECTORYCREATE = "Failed to create the directory";
    
    const cPOSTERRORVALUEFAILEDMOVE = "Failed to move files to their appropriate directory";
    
    const cMESSAGEERROR = 'Invalid Message';
    
    const cMESSAGENOTFOUND = 'The message you specified was not found';
    
    const cMETADESCRIPTIONMAIN = 'Students place to share knowledge , create your classroom and knowledge sharing community and start uploading and downloading lectures  , notes and homework';
    
    const cDESCRIPTIONEDITABLETIME = 3600;
    
    const cUPDATEMESSAGEERROR = 'Update message error';
    
    const cUPDATEMESSAGEERRORAUTH = 'User not authorized to edit this message';

    const cMAXFILESIZEALLOWED = 104857600;  //* 1024 * 1024;
    
    const cMAXFILESIZEPROFILEPICALLOWED = 1048576;
    
    const c_FOLDER_PROFILES = "/upload/profiles/";
    
    const c_FOLDER_COURSES = "/upload/";

    const cBADID = "Bad post id";
    
    const cBADHTML = "Unsafe html - videos allowed are only from youtube and pictures ,ust be valid image files with no php embeded";
    
    const cBADSEARCHFORM = "Bad params for search form";
    
    const cBADSEARCHFORMSTRING = "Search string must not exceed 300 characters";
    
    CONST cBADIMAGE = "Detected php code in image";
    
    CONST cBADNAME = "Your name can be up to 100 characters";
    
    CONST cBADNAME2 = "The name length must be larger than 0 and smaller than 100 ";
    
    CONST cBADLOGIN = "Problem with your login please try again";
    
    /**
     * number of search results per page
     */
    const cNUMSEARCHRESULTS = 10;
    
    const cSTATICSALT = 'tBHrUMVcHRV';
    
    const c_UPDATEIMAGE_ERROR_MULFILES = 'Only single profile image is allowed please upload only one file';
    
    const c_UPDATEIMAGE_ERROR_MOVEFILE = 'Failed to move file';
    
    const c_BAD_PARAMS = 'Bad params';
    
    //the limit from which the search file is not displayed
    const c_ELFINDER_LOWLIMIT = -100;
    
    //the count download limit from which a semester recieves a file
    const c_ELFINDER_DOWNLOADLIMIT = 5;
    
    //the times hw file need to be uploaded to be considered an hw file
    const c_ELFINDER_HWLIMIT = 5;
    
    //the number of points a file gets and after that it becomes an answer to hw 
    const c_ELFINDER_HWPOINTS = 10;
    
    //the number of points a file gets after he was downloaded due to hw
    const c_ELFINDER_HWDOWNLOADPOINTS = 10;
    
    //black list of characters to remove 
    const c_CHARACTERS_BLIST = 'אבגדהוזחטיכלמנסעפצקרשתךםןףץ: ';
    
    //characters whits list
    const c_CHARACTERS_WLIST = '{}()<>=|-+−';
    
    /****************const error messages *********************/
    
    const cERROR_404 = 'You entered a bad URL';
    
    //private members
    
    
    
    
    
    /**
     * 
     * Self detection of bugs mechanizem i will report myself to the event and hopefully will catch it
     * @param String $message i will try to put here all the data
     */
    public function bugReport($message , $mail = NULL){
        //grab the email from config
        $email = $this->grabFromConfigFile('BugReportMail');        
        
        //create the mail body
        $body = "<HTML><BODY><CENTER>
        <h1>What'p bitch - There was a Bug Report</h1>
        <p>
        " . $message ."
        </p>
        <p>
            mail: " . $mail . "
        </p>
        <p>
        Regards, Knowledge-Share.com Team
        </p>
        </CENTER></BODY>
        </HTML>";		
        
        //mail title
        $title = "Knowledge-Share Bug Report";
        
        //send the mail 
        $this->reportByMail($email, $body, $title);
    }
    
    /**
     *send mail to {$mail} with  content {$body}
     * 
     * @param String $mail - the mail address
     * @param String $body  - the text content of the mail 
     */
    public function reportByMail($email , $body , $title){
        //TODO
        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        $headers .= 'From: admin@knowledge-share.com' . "\r\n";
        @mail($email,$title,$body,$headers);
    }
    
    /**
     *grabs data from the config file
     * 
     * @param String $key - the key from config file to grab 
     * @return String the value taken from config
     */
    public function grabFromConfigFile($key){
        $config = new Zend_Config_Ini('../application/configs/application.ini','production');
        return $config->{$key};
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
     * create a unique serial number for registration
     * @return String a random serial
     */
    public function createSerial(){
        $pool = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $countPool = strlen($pool) ;
        $totalChars = 12 ;

        $serial = '' ;
        for ($i = 0 ; $i < $totalChars ; $i++) {
            $currIndex = mt_rand(0, $countPool) ;
            $currChar = $pool[$currIndex] ;
            $serial .= $currChar ;
        }
        return $serial;
    }
    
    /**
     * get the auth user id
     * @return int user id
     */
    public function getUserId(){
        $auth = Zend_Auth::getInstance();
        $auth->setStorage(new Zend_Auth_Storage_Session('Users'));
        try{
            return $auth -> getIdentity() -> id;
        }
        catch(Exception $e){
            return 0;
        }
    }
    
    /**
     * creates a random salt string
     * @return String
     */
    public function createSaltString(){
        $dynamicSalt = '';
        for ($i = 0; $i < 50; $i++) {
            $dynamicSalt .= chr(rand(33, 126));
        }
        return $dynamicSalt;
    }
    
    /**
     * sends registration activation mail
     * @param String $serial the serial number for the activation 
     * @param int the row of the user
     * @param String $email the email address to send to
     */
    public function sendActivationMail($serial , $users_id , $email){
        //create the mail body
        $body = '<HTML><BODY><CENTER>
        <h1>Knowledge-Share Account activation</h1>
        <p>
        Please confirm your Knowledge-Share account by clicking this link:
        </p>
        <p>
            <a href="http://'. $this ->sGetUrl() .'/register/activateaccount/id/'. $users_id . '/token/'. $serial .'">
                activate account
            </a>
        </p>
        <p>
        Regards, Knowledge-Share Team
        </p>
        </CENTER></BODY>
        </HTML>';		
        
        //mail title
        $title = "Knowledge-Share account activation";
        
        //send the mail 
        $this->reportByMail($email, $body, $title);
    }
    
    /**
     * creates a user name to be set in the users table 
     * @return String the user name
     */
    public function createUserName(){
        return 'Student_' . rand(0, 99999);
    }
    
    /**
     * :)
     * check if id is a valid number up to 10 digits
     * 
     * @param int $id - the id to check 
     * @param Bool true if valid false otherwise
     */
    public function is_IdValid($id){
        if(!isset ($id) || $id == NULL) return false;
        $data = array('id' => $id);
        $filters=null;
        $validators=array();
        $validators['id']=array(array('StringLength', array(0, 10)),'Digits');
        //check data is sanitized
        $input = new Zend_Filter_Input($filters, $validators, $data);
        if(!$input->isValid()){
            return FALSE;
        }
        return TRUE;
    }
    
    /**
     * when i need to get the users ip address
     * @return String the user ip address 
     */
    public function getRealIpAddr(){ 
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) //check ip from share internet 
        { 
            $ip=$_SERVER['HTTP_CLIENT_IP']; 
        } 
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) //to check ip is pass from proxy 
        { 
            $ip=$_SERVER['HTTP_X_FORWARDED_FOR']; 
        } 
        else 
        { 
            $ip=$_SERVER['REMOTE_ADDR']; 
        } 
        return $ip; 
    }
    
    /**
     * creates a random salt string
     * @return String
     */
    public function createSaltString2($length){
        $dynamicSalt = '';
        for ($i = 0; $i < $length; $i++) {
            $dynamicSalt .= chr(rand(33, 126));
        }
        return $dynamicSalt;
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
     * sends reset password mail
     * @param String $serial the serial number for the activation 
     * @param int the row of the user
     * @param String $email the email address to send to
     */
    public function sendResetPasswordMail($serial , $users_id , $email){
        //create the mail body
        $body = '<HTML><BODY><CENTER>
        <h1>' . $this->getSiteName() . ' Password Reset</h1>
        <p>
        You recently asked to reset your ' . $this->getSiteName() . ' password. To complete your request, please follow this link:
        </p>
        <p>
            <a href="http://www.' . $this->getSiteUrl(). '/login/approvereset/id/'. $users_id . '/token/'. $serial .'">
                Reset Password
            </a>
        </p>
        <p>
        If this is a mistake please ignore this mail
        </p>
        <p>
        Regards, ' . $this->getSiteName() . ' Team
        </p>
        </CENTER></BODY>
        </HTML>';		
        
        //mail title
        $title = $this->getSiteName() . " Reset Password";
        
        //send the mail 
        $this->reportByMail($email, $body, $title);
    }
    
    /**
     * sends reset password mail
     * @param String $serial the serial number for the activation 
     * @param int the row of the user
     * @param String $email the email address to send to
     */
    public function sendNewPasswordMail($email, $password){
        //create the mail body
        $body = '<HTML><BODY><CENTER> 
        <h1>'. $this ->getSiteName() .' New Password</h1>
        <p>
        You recently asked to reset your ' . $this ->getSiteName() . ' password. Your new password is:
        </p>
        <p>
            '. $password . '
        </p>
        <p>
        Regards, ' . $this ->getSiteName() . ' Team
        </p>
        </CENTER></BODY>
        </HTML>';		
        
        //mail title
        $title = $this ->getSiteName() . " New Password";
        
        //send the mail 
        $this->reportByMail($email, $body, $title);
    }
    
    /**
     * from the full path im grabbing just the name
     * @param String $sPath the path to grab
     * @return String the name of the file
     */
    public function grabFileNameFromPath($sPath){
        $aPath = explode('/', $sPath);
        if ($aPath[count($aPath) - 1] !== '' && $aPath[count($aPath) - 1] != NULL){
            return $aPath[count($aPath) - 1];
        }
        else{
            if (count($aPath) >= 2){
                return $aPath[count($aPath) - 2];
            }
            else{
                return "";
            }
        }
    }
}

?>
