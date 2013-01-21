<?php
/**
 * required for the extend part
 */
require_once 'Zend/Db/Table/Abstract.php';

/**
 * All nerdeez models will extend this class
 * will have common functions to all db tables
 *
 * @author Yariv Katz
 * @copyright Nerdeez.com Ltd.
 * @version 1.0
 */
abstract class Nerdeez_Db_Table extends Zend_Db_Table_Abstract{
    
    /**
     * all the tables will have a column pk named id
     * @var String
     */
    protected $_primary = 'id';
    
    /**
     * all the models will put their table create if not exist table in here
     * @var String 
     */
    protected $_sqlCreateTable = NULL;
    
    /**
     * will hold the name of the table
     * @var String
     */
    protected $_name = NULL;
    
    /**
     * a list of sql statments to try and execute 
     * will work only for the first time
     * @var Array 
     */
    protected $_aAlterStatments = NULL;

    /**
     * all the models will create their table in this function
     */
    public function __construct() {
        //set the default db adapter
        $config = new Zend_Config_Ini('../application/configs/application.ini','production');
        $db = Zend_Db::factory($config->resources->db->adapter,array(
            'host' => $config->resources->db->params->host , 
            'username' => $config ->resources->db->params->username , 
            'password' => $config -> resources->db->params->password ,
            'dbname' => $config -> resources->db->params -> dbname ,
        ));
        Zend_Db_Table_Abstract::setDefaultAdapter($db);
        $stmt = new Zend_Db_Statement_Pdo($db,
                                          "SET NAMES 'utf8'");
        $stmt->execute();
        
        //call the parent constructor
        parent::__construct((array('name'=> $this ->_name)));
        
        //if the _sqlCreateTable is not null than create table
        if ($this -> _sqlCreateTable !== NULL){
            $db->query($this -> _sqlCreateTable);
        }
        
        //try and execute the list of alter sql statments
        if ($this->_aAlterStatments == NULL) return;
        foreach ($this->_aAlterStatments as $sSqlStmt) {
            try{
                $db -> query($sSqlStmt);
            }
            catch(Exception $e){
                continue;
            }
        }
    }
    
    /**
     * delete a row in table by its pk
     * @param int $id the pk of the row
     */
    public function deleteRowWithId($id) {
        $this->delete('id =' . (int) $id);
    }
    
    /**
     * fetch a row with this id from the table
     * @param int $id the id of the row to retrieve
     * @return Zend_Db_Table_Row the row to return or Null if didnt find
     */
    public function getRowWithId($id){
        $rsRows = $this -> fetchAll($this -> select() -> where('id = ?' , $id));
        if ($rsRows -> count() == 0){
            return NULL;
        }
        return $rsRows -> getRow(0);
    }
    
    /**
     * returns the reference map
     * @return array
     */
    public function getReferenceMap() {
        return $this->_referenceMap;
    }
    
    /**
     * iterate on all the rowset and deletes all the rows
     * @param Zend_Db_Table_Rowset $rsRows the rowset to delete
     * @return void
     */
    public function deleteRowset($rsRows){
        if ($rsRows == NULL) return;
        foreach ($rsRows as $rRow) {
            $this->deleteRowWithId($rRow['id']);
        }
    }
    
    /**
     * returns the table columns
     */
    public function getModelColumns(){
        return $this -> info(Zend_Db_Table_Abstract::COLS);
    }
    
    
    
    
}

?>
