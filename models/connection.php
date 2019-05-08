<?php
require_once 'config.php';
class dbDriver
/*
 * Clase que genera las conexiones dinamicamente
 */
{
    public function __construct() { 
    }
    public function setPDO()
    {
        return new PDO(PDO_HOSTNAME, PDO_USER, PDO_PASS);
    }
    public function setPDOConfig($PDO_CONSTRUCTOR)
    {
        $obj = $PDO_CONSTRUCTOR;
        $obj->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $obj->exec(PDO_CHAR);
    }
    public function setBBDD($object){
        $this->BBDD = $object;
    }
    public function getBBDD(){
        return $this->BBDD;
    }
    public function selectDriver($condition,$bbdd, $PDO_port)
    {
        if($condition!=null){
           return $PDO_port->prepare("SELECT * FROM {$bbdd} WHERE {$condition}");
        }else{
           return $PDO_port->prepare("SELECT * FROM {$bbdd}");
            
            }       
    }
    public function twiceSearchOption($condition, $bbdd, $PDO_port, $bbd2, $searchRow1, $searchRow2) {
        if ($condition != null) {
            return $PDO_port->prepare("SELECT DISTINCT * FROM {$bbdd} p LEFT JOIN {$bbd2} pd ON (p.{$searchRow1} = pd.{$searchRow2})"
            . "WHERE {$condition}");
        } else {
            return $PDO_port->prepare("SELECT * FROM {$bbdd}");
        }
    }
    public function ThirtySearchOption($condition, $bbdd, $PDO_port, $bbd2, $bbd3, $searchRow1, $searchRow2, $searchRow3, $searchRow4) {
        if ($condition != null) {
            return $PDO_port->prepare("SELECT DISTINCT * FROM {$bbdd} p LEFT JOIN {$bbd2} pd ON (p.{$searchRow1} = pd.{$searchRow2})"
            . "LEFT JOIN {$bbd3} pc ON (pc.{$searchRow3} = p.{$searchRow4}) WHERE {$condition}");
        } else {
            return $PDO_port->prepare("SELECT * FROM {$bbdd}");
        }
    }
    public function multipleOptiosn($condition,$bbdd, $PDO_port, $bbd2, $bbdd3) {
        if($condition!=null){
           // return $PDO_port->prepare("SELECT * FROM {$bbdd} WHERE {$condition}");
            return $PDO_port->prepare("SELECT DISTINCT * FROM {$bbdd} p LEFT JOIN {$bbd2} pd ON (p.product_id = pd.product_id) "
            . "LEFT JOIN {$bbdd3} pc ON (pc.product_id = pd.product_id)  WHERE {$condition}");
        }else{
           return $PDO_port->prepare("SELECT * FROM {$bbdd}");       
        }  
    }
        public function multipleLeftBySearch($condition,$bbdd, $PDO_port, $bbd2, $bbdd3, $query1, $query2) {
        if($condition!=null){
           // return $PDO_port->prepare("SELECT * FROM {$bbdd} WHERE {$condition}");
            return $PDO_port->prepare("SELECT DISTINCT * FROM {$bbdd} p LEFT JOIN {$bbd2} pd ON (p.{$query1} = pd.{$query2}) "
            . "LEFT JOIN {$bbdd3} pc ON (pc.category_id = pd.category_id)  WHERE {$condition}");
        }else{
           return $PDO_port->prepare("SELECT * FROM {$bbdd}");       
        }  
    }   
    public function Explorer($condition,$bbdd, $PDO_port, $bbd2, $bbdd3, $query1, $query2, $row) {
        return $PDO_port->prepare("SELECT DISTINCT * "
                . "FROM {$bbdd} p LEFT JOIN {$bbd2} pd ON (p.product_id = pd.product_id)"
                . "LEFT JOIN {$bbdd3} pc ON (pc.category_id = pd.category_id)"
                . "WHERE MATCH ({$row}) AGAINST ('{$condition}')");
    }
    public function getLastInsert($PDO_PORT) {
        return $PDO_PORT->lastInsertId();

    }
    /*
     * // 		$user_query = $this->db->query("SELECT * FROM " . DB_PREFIX . 
// 		"user WHERE username = '" . $this->db->escape($username) . "' AND 
// 		(password = SHA1(CONCAT(salt, SHA1(CONCAT(salt, SHA1('" . $this->db->escape($password) . "'))))) 
// 		OR password = '" . $this->db->escape(md5($password)) . "') AND status = '1'");
     */

    // SELECT * FROM ARTICULOS WHERE MATCH(TITULO, DESARROLLO) AGAINST ('$busqueda')
    /*
     * SELECT DISTINCT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . 
     * "product_description pd ON (p.product_id = pd.product_id) 
     * WHERE p.product_id = '" . (int)$product_id . "'
     */
                            /*
                         * SELECT DISTINCT *, MATCH (name) AGAINST ('Chaqueta') AS 
                         * items FROM rgza_product_description p LEFT JOIN 
                         * rgza_product_to_category pd ON (p.product_id = pd.product_id)
LEFT JOIN rgza_category pc ON (pc.category_id = pd.category_id)
WHERE MATCH (name) AGAINST ('Marca Anika') ORDER BY items DESC LIMIT 50

                         */
    public function scapeCharts($value)
    {
        return htmlentities(addslashes($value));
    }
    public function countDriver($condition,$bbdd,$PDO_port)
    {
        if($condition!=null)
        {
           return $PDO_port->prepare("SELECT COUNT(*) as 'index' FROM {$bbdd} WHERE {$condition}");
        }else{
            return $PDO_port->prepare("SELECT COUNT(*) as 'index' FROM {$bbdd}");
        }
    }
    public function countDriverByGroup($condition,$bbdd,$PDO_port,$field,$group)
    {
        if($condition!=null)
        {
            return $PDO_port->prepare("SELECT SUM({$field} as index FROM {$bbdd} WHERE {$condition} GROUP BY {$group} ASC");
        }else{
            return $PDO_port->prepare("SELECT SUM{$field} as index FROM {$bbdd} GROUP BY {$group} ASC ");
        }
    }
    public function sumDriver($condition,$bbdd,$PDO_port,$field)
    {
        if($condition!=null)
        {
            return $PDO_port->prepare("SELECT SUM({$field}) AS total FROM {$bbdd} WHERE {$condition}");
        }else{
            return $PDO_port->prepare("SELECT SUM({$field}) AS total FROM {$bbdd}");
        }
    }
    
    public function insertDriver($condition,$bbdd,$PDO_port,$fields)
    {
        //$this->setobjectPDO($this->BBDD->prepare("INSERT INTO {$bbdd} VALUES ({$condition})"));
        return $PDO_port->prepare("INSERT INTO {$bbdd}({$fields}) VALUES({$condition})");
    }
    public function updateDriver($condition,$bbdd,$PDO_port,$fields){
        return $PDO_port->prepare("UPDATE {$bbdd} SET {$fields} WHERE {$condition}");
    }
    public function deleteDriver($condition,$bbdd,$PDO_port)
    {
        if($condition!='')
        {
            return $PDO_port->prepare("DELETE FROM {$bbdd} WHERE {$condition}");
        }else{
            return $PDO_port->prepare("DELETE FROM {$bbdd}");
        }
    }
   public function runDriver($sentence,$PDO_OBJECT)
   {
       if($sentence!=null)
       {
           $PDO_OBJECT->execute($sentence);
       }else{
           $PDO_OBJECT->execute();
       }
   }
   public function cicleDriver($PDO_OBJECT){
       foreach($PDO_OBJECT->fetchAll(PDO::FETCH_OBJ) as $array){
           return $array;
       }
   }
   public function fetchDriver($PDO_OBJECTS)
   {
       return $PDO_OBJECTS->fetchAll(PDO::FETCH_OBJ);
   }
   public function verifyDriver($PDO_OBJECT)
   {
       if($PDO_OBJECT->rowCount()!=0)
       {
           return true;
       }else{
           return false;
       }
   }
    public function setobjectPDO($PDO_OBJECT)
    {
        return $PDO_OBJECT;

    }
    public function getObjectPDO()
    {
        return $this->PDO;
    }
    public function setQuery($arrayResponse)
    {
        $this->query = $arrayResponse;
    }
    public function getQuery()
    {
        return $this->query;
    }
    protected $BBDD;
    protected $query;
    protected $PDO;
}
