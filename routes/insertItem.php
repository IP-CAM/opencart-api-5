<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type");
header("Access-Control-Allow-Methods", "POST, GET, PUT, DELETE, OPTIONS");
/*
 * CLASE que se encarga de hacer insert en varias tablas
 * para posicionar un artículo
 * Carlos Estarita
 */
require_once '../models/config.php';
require_once '../models/connection.php';
if($_POST) {
    $items = new insertItems();
    echo $items->insertAll();
}
class insertItems {
    public function __construct() {
        $this->BBDD = new \dbDriver;
        $this->driver = $this->BBDD->setPDO();
        $this->BBDD->setPDOConfig($this->driver);
    }
    public function insertAll() {
        try {
            $manufacturer = $this->insertManufacturer();
            $category = $this->insertCategory();
            $filter = $this->insertFilter();
            if ($manufacturer && $category && $filter) {
                $success = array();
                $success['status'] = true;
                $success['message'] = 'Datos agregado correctamente';
                return json_encode($success);
            }
        } catch (PDOException $ex) {
            $err = array();
            $err['status'] = false;
            $err['message'] = 'Fallo al realizar esta petición'. $ex->getMessage();
            return json_encode($err);
        }        
    }
    public function insertManufacturer() {
         try {
            $fields = 'manufacturer_id = ?';
            $manufacturer = $this->BBDD->updateDriver('product_id = ?',PREFIX.'product', $this->driver, $fields);
            $this->BBDD->runDriver(array(
                $this->BBDD->scapeCharts($_POST['manufacturer']),
                $this->BBDD->scapeCharts($_POST['product_id'])
            ), $manufacturer);
            return true;
        } catch (PDOException $ex) {
            $err = array();
            $err['status'] = false;
            $err['message'] = 'Fallo al realizar esta petición'. $ex->getMessage();
            return json_encode($err);
        }       
    }
    public function insertCategory() {
        try {
            $sql = '?,?';
            $fields = 'product_id, category_id';
            $insertCategory = $this->BBDD->insertDriver($sql,PREFIX.'product_to_category',$this->driver, $fields);
            $this->BBDD->runDriver(array(
                $this->BBDD->scapeCharts($_POST['product_id']),
                $this->BBDD->scapeCharts($_POST['subcategory'])
            ), $insertCategory);
            return true;
        } catch (PDOException $ex) {
            $err = array();
            $err['status'] = false;
            $err['message'] = 'Fallo al realizar esta petición'. $ex->getMessage();
            return json_encode($err);
        }
    }
    public function insertFilter() {
        try {
            $sql = '?,?';
            $fields = 'product_id, filter_id';
            $insertFilter = $this->BBDD->insertDriver($sql,PREFIX.'product_filter',$this->driver, $fields);
            $this->BBDD->runDriver(array(
                $this->BBDD->scapeCharts($_POST['product_id']),
                $this->BBDD->scapeCharts($_POST['filter'])
            ), $insertFilter);
            return true;
        } catch (PDOException $ex) {
            $err = array();
            $err['status'] = false;
            $err['message'] = 'Fallo al realizar esta petición'. $ex->getMessage();
            return json_encode($err);
        }        
    }
    protected $BBDD;
    protected $driver;
}

