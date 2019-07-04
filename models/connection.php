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
        public function Filter($condition, $bbdd, $PDO_port, $bbd2, $bbd3, $searchRow1, $searchRow2, $searchRow3, $searchRow4) {
        if ($condition != null) {
            return $PDO_port->prepare("SELECT DISTINCT * FROM {$bbdd} p LEFT JOIN {$bbd2} pd ON (p.{$searchRow1} = pd.{$searchRow2})"
            . "LEFT JOIN {$bbd3} pc ON (pc.{$searchRow3} = pd.{$searchRow4}) WHERE {$condition}");
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
    // 16
    /*
     * Función que se encargará de hacer busqueda en las 16 tablas de producto 
     */
    public function ProductAllData($condition, $PDO_port, $PREFIX, $bbdd) {
        if ($condition != null) {
            return $PDO_port->prepare("SELECT * FROM {$bbdd} p 
            LEFT JOIN (SELECT product_id, language_id,name,description,tag,meta_title,meta_description,meta_keyword as item FROM {$PREFIX}product_description) AS pD ON (pD.product_id = p.product_id)
            LEFT JOIN {$PREFIX}product_attribute pA ON (pA.product_id = p.product_id) 
            LEFT JOIN (SELECT product_id,price as descuento FROM {$PREFIX}product_discount) AS pDi ON (pDi.product_id = p.product_id) 
            LEFT JOIN {$PREFIX}product_filter pF ON (pF.product_id = p.product_id) 
            LEFT JOIN {$PREFIX}product_option pO ON (pO.product_id = p.product_id) 
            LEFT JOIN {$PREFIX}product_recurring pR ON (pR.product_id = p.product_id) 
            LEFT JOIN {$PREFIX}product_related pRe ON (pRe.product_id = p.product_id) 
            LEFT JOIN {$PREFIX}product_reward pRa ON (pRa.product_id = p.product_id)
            LEFT JOIN (SELECT manufacturer_id, name as alianza FROM {$PREFIX}manufacturer) AS mF ON (mF.manufacturer_id = p.manufacturer_id)
            LEFT JOIN (SELECT manufacturer_id, image as alianzaLogo FROM {$PREFIX}manufacturer) AS mL ON (mL.manufacturer_id = p.manufacturer_id)
            LEFT JOIN {$PREFIX}product_to_category pTc ON (pTc.product_id = p.product_id)
            LEFT JOIN (SELECT category_id, parent_id as catdata FROM {$PREFIX}category) AS pC ON (pC.category_id = pTc.category_id)
            LEFT JOIN (SELECT category_id, name as catname FROM {$PREFIX}category_description) AS cN ON (cN.category_id = pTc.category_id)
            LEFT JOIN (SELECT stock_status_id, name as stock FROM {$PREFIX}stock_status) AS sT ON (sT.stock_status_id = p.stock_status_id)
            WHERE {$condition} GROUP BY p.product_id");
        } else {
            // Hace la misma busqueda por cada table pero sin condicional 
            // Es decir busca toda la data relacionada con un producto sin preguntar el ID
            return $PDO_port->prepare("SELECT * FROM {$bbdd} p 
            LEFT JOIN (SELECT product_id, language_id,name,description,tag,meta_title,meta_description,meta_keyword as item FROM {$PREFIX}product_description) AS pD ON (pD.product_id = p.product_id) 
            LEFT JOIN {$PREFIX}product_attribute pA ON (pA.product_id = p.product_id) 
            LEFT JOIN (SELECT product_id,price as descuento FROM {$PREFIX}product_discount) AS pDi ON (pDi.product_id = p.product_id) 
            LEFT JOIN {$PREFIX}product_filter pF ON (pF.product_id = p.product_id)
            LEFT JOIN (SELECT manufacturer_id, name, image as alianza FROM {$PREFIX}manufacturer) AS mF ON (mF.manufacturer_id = p.manufacturer_id)
            LEFT JOIN {$PREFIX}product_option pO ON (pO.product_id = p.product_id) 
            LEFT JOIN {$PREFIX}product_recurring pR ON (pR.product_id = p.product_id) 
            LEFT JOIN {$PREFIX}product_related pRe ON (pRe.product_id = p.product_id) 
            LEFT JOIN {$PREFIX}product_reward pRa ON (pRa.product_id = p.product_id)
            LEFT JOIN {$PREFIX}product_to_category pTc ON (pTc.product_id = p.product_id)
            LEFT JOIN (SELECT category_id, parent_id as catdata FROM {$PREFIX}category) AS pC ON (pC.category_id = pTc.category_id)
            LEFT JOIN (SELECT category_id, name as catname FROM {$PREFIX}category_description) AS cN ON (cN.category_id = pTc.category_id)
            LEFT JOIN (SELECT stock_status_id, name as stock FROM {$PREFIX}stock_status) AS sT ON (sT.stock_status_id = p.stock_status_id)
            GROUP BY p.product_id");
            // LEFT JOIN (SELECT campos FROM soluclic_product_discount) AS pDi ON (pDi.product_id = p.product_id)
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
    public function GetFilterData($condition, $PDO_port, $bbdd, $bbdd2, $bbdd3) {
        if ($condition != null) {
             return $PDO_port->prepare("SELECT * FROM {$bbdd} p 
                LEFT JOIN (SELECT filter_id, filter_group_id, name as Childs FROM {$bbdd2}) AS pd ON (p.filter_id = pd.filter_id) 
                LEFT JOIN (SELECT filter_group_id, name AS parent FROM {$bbdd3}) AS pc ON (pc.filter_group_id = pd.filter_group_id) 
                WHERE {$condition}");
        } else {
             return $PDO_port->prepare("SELECT * FROM {$bbdd} p 
                LEFT JOIN (SELECT filter_id, filter_group_id, name as Childs FROM {$bbdd2}) AS pd ON (p.filter_id = pd.filter_id) 
                LEFT JOIN (SELECT filter_group_id, name AS parent FROM {$bbdd3}) AS pc ON (pc.filter_group_id = pd.filter_group_id)");
        }
    }
    public function GetFilterCategories($condition, $PDO_port, $bbdd, $bbdd2, $bbdd3) {
        if ($condition != null) {
             return $PDO_port->prepare("SELECT * FROM {$bbdd} p 
                    LEFT JOIN (SELECT product_id, category_id as Category FROM {$bbdd2}) AS cat ON (p.product_id = cat.product_id)
                    LEFT JOIN {$bbdd3} pc ON(pc.category_id = Category)
                    WHERE {$condition} GROUP BY Category");
        } else {
             return $PDO_port->prepare("SELECT * FROM {$bbdd} p 
                    LEFT JOIN (SELECT product_id, category_id as Category FROM {$bbdd2}) AS cat ON (p.product_id = cat.product_id)
                    LEFT JOIN {$bbdd3} pc ON(pc.category_id = Category)
                    ");
        }
    }    
    public function GetFilterCategories2($condition, $PDO_port, $bbdd, $bbdd2, $bbdd3) {
        if ($condition != null) {
             return $PDO_port->prepare("SELECT * FROM {$bbdd} cat 
                LEFT JOIN {$bbdd2} pc ON (pc.category_id = cat.category_id)
                LEFT JOIN (SELECT product_id, category_id as qty FROM {$bbdd3}) as pTc ON (qty = pc.category_id)
                WHERE {$condition} GROUP BY cat.category_id");
        } else {
             return $PDO_port->prepare("SELECT * FROM {$bbdd} p 
                    LEFT JOIN (SELECT product_id, category_id as Category FROM {$bbdd2}) AS cat ON (p.product_id = cat.product_id)
                    LEFT JOIN {$bbdd3} pc ON(pc.category_id = Category)
                    ");
        }
    }     
    
    /*
     * SELECT * FROM soluclic_category cat 
LEFT JOIN soluclic_category_description pc ON (pc.category_id = cat.category_id)
LEFT JOIN (SELECT product_id, category_id as qty FROM soluclic_product_to_category) as pTc ON (qty = pc.category_id)
WHERE pTc.product_id >= 0 GROUP BY cat.category_id
     * 
     * 
SELECT * FROM soluclic_product_filter p 
LEFT JOIN (SELECT filter_id, filter_group_id, name as Childs FROM soluclic_filter_description) AS pd ON (p.filter_id = pd.filter_id) 
LEFT JOIN (SELECT filter_group_id, name AS parent FROM soluclic_filter_group_description) AS pc ON (pc.filter_group_id = pd.filter_group_id) 
WHERE p.product_id = 40
     * 
     * SELECT * FROM soluclic_product p 
LEFT JOIN (SELECT product_id, category_id as Category FROM soluclic_product_to_category) AS cat ON (p.product_id = cat.product_id)
LEFT JOIN soluclic_category_description pc ON(pc.category_id = Category)
WHERE p.manufacturer_id = 8
 
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
