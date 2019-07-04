<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type");
header("Access-Control-Allow-Methods", "POST, GET, PUT, DELETE, OPTIONS");
header('Content-Type: application/json');
/*
 * Backend que se encarga de construir los productos desde la empresa
 * Carlos Estarita
 * API RESTFUL 
 */
require_once '../models/config.php';
require_once '../models/connection.php';
if($_GET) {
    $openCartItems = new Items();
    switch($_GET['operationType']) {
        /* UPDATE NEW API
         */ 
        case 'selectManufacturersProductAPI-ID':
            echo $openCartItems->GetManufacturerProducts($_GET['manufacturer_id']);
        break;
        case 'StoreCategories':
            echo $openCartItems->GetManufacturerCategories($_GET['manufacturer_id']);
        break;
        case 'GetCategoriesAPI':
            echo $openCartItems->GetCategories();
        break;
        case 'ExplorerStores':
            echo $openCartItems->GetStoresByKeyword($_GET['manufacturer_id']);
        break;            
         /* 
         * END UPDATES
         */
        case 'stock':
            echo $openCartItems->stock();
        break;
        case 'length':
            echo $openCartItems->lengthType();
            break;
        case 'status':
            echo $openCartItems->productStatus();
            break;
        case 'weight':
            echo $openCartItems->weight();
            break;
        case 'filterGroup':
            echo $openCartItems->filterGroup();
            break;
        case 'filter':
            echo $openCartItems->filter($_GET['filter_group_id']);
            break;
        case 'filterAll':
            echo $openCartItems->filterAll();
            break;        
        case 'manufacturer':
            echo $openCartItems->manufacturer();
            break;
        case 'category':
            echo $openCartItems->category();
            break;
        case 'categoryAll':
            echo $openCartItems->categoryAll();
            break;
        case 'categoryDescription':
            echo $openCartItems->categoryDescriptionById($_GET['category_id']);
            break;
        case 'categoryById':
            echo $openCartItems->categoryId($_GET['category_id']);
            break;
    }
}
/*
 * Clase que se encarga de traer datos de formularios
 * de OpenCart
 */
class Items {
    public function __construct() {
        $this->BBDD = new \dbDriver;
        $this->driver = $this->BBDD->setPDO();
        $this->BBDD->setPDOConfig($this->driver);
    }
    public function stock() {
        $stockType = $this->BBDD->selectDriver(null,PREFIX.'stock_status', $this->driver);
        $this->BBDD->runDriver(null, $stockType);
        return json_encode($this->BBDD->fetchDriver($stockType));
    }
    public function lengthType() {
        $lengthType = $this->BBDD->selectDriver(null,PREFIX.'length_class_description', $this->driver);
        $this->BBDD->runDriver(null, $lengthType);
        return json_encode($this->BBDD->fetchDriver($lengthType));
    }
    public function productStatus() {
        $statusType = $this->BBDD->selectDriver(null,PREFIX.'return_status', $this->driver);
        $this->BBDD->runDriver(null, $statusType);
        return json_encode($this->BBDD->fetchDriver($statusType));
    }
    public function weight() {
        $weightType = $this->BBDD->selectDriver(null,PREFIX.'weight_class_description', $this->driver);
        $this->BBDD->runDriver(null, $weightType);
        return json_encode($this->BBDD->fetchDriver($weightType));        
    }
        public function filterGroup() {
        $filterGroupType = $this->BBDD->selectDriver(null,PREFIX.'filter_group_description', $this->driver);
        $this->BBDD->runDriver(null, $filterGroupType);
        return json_encode($this->BBDD->fetchDriver($filterGroupType));        
    }
        public function filter($filter_group_id) {
        $filterType = $this->BBDD->selectDriver('filter_group_id = ?',PREFIX.'filter_description', $this->driver);
        $this->BBDD->runDriver(array(
            $this->BBDD->scapeCharts($filter_group_id)
        ), $filterType);
        return json_encode($this->BBDD->fetchDriver($filterType));        
    }
        public function filterAll() {
        $filterType = $this->BBDD->selectDriver(null,PREFIX.'filter_description', $this->driver);
        $this->BBDD->runDriver(null, $filterType);
        return json_encode($this->BBDD->fetchDriver($filterType));        
    }    
        public function manufacturer() {
        $manufacturerType = $this->BBDD->selectDriver(null,PREFIX.'manufacturer', $this->driver);
        $this->BBDD->runDriver(null, $manufacturerType);
        return json_encode($this->BBDD->fetchDriver($manufacturerType));        
    }   
        // Productos de una tienda especifica por ejemplo Todos los productos de la tienda Apple
        public function GetManufacturerProducts($manufacturer_id) {
        try {
           $objectProduct = $this->BBDD->ProductAllData('p.manufacturer_id = ?', $this->driver, 'soluclic_', PREFIX.'product');
           $this->BBDD->runDriver(array(
               $this->BBDD->scapeCharts($manufacturer_id)
           ), $objectProduct);
             if ($this->BBDD->verifyDriver($objectProduct)) {
                $items = array();
                $items['status'] = true;
                $items['code'] = 200;
                $items['data'] = $this->BBDD->fetchDriver($objectProduct);
                return json_encode($items);
            } else {
                $items = array();
                $items['status'] = false;
                $items['code'] = 200;
                return json_encode($items);
            }
        } catch (PDOException $ex) {
            return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
        }
    }
    // Obtener las categorías que maneja una tienda
        public function GetManufacturerCategories($manufacturer_id) {
          try {
              $ObjectCategory = $this->BBDD->GetFilterCategories('p.manufacturer_id = ?', $this->driver, PREFIX.'product', PREFIX.'product_to_category', PREFIX.'category_description');
              $this->BBDD->runDriver(array(
                  $this->BBDD->scapeCharts($manufacturer_id)
              ), $ObjectCategory);
              if ($this->BBDD->verifyDriver($ObjectCategory)) {
                $items = array();
                $items['status'] = true;
                $items['code'] = 200;
                $items['data'] = $this->BBDD->fetchDriver($ObjectCategory);
                return json_encode($items);
              } else {
                $items = array();
                $items['status'] = false;
                $items['code'] = 400;
                return json_encode($items);
              }
          } catch (PDOException $ex) {
            return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
          }
        }
        public function GetStoresByKeyword($keyword) {
            try {
                $objectStore = $this->BBDD->selectDriver('name LIKE ?', PREFIX.'manufacturer', $this->driver);
                $this->BBDD->runDriver(array(
                    $this->BBDD->scapeCharts("%{$keyword}%")
                ), $objectStore);
                if ($this->BBDD->verifyDriver($objectStore)) {
                $items = array();
                $items['status'] = true;
                $items['code'] = 200;
                $items['data'] = $this->BBDD->fetchDriver($objectStore);
                return json_encode($items);                    
                } else {
                $items = array();
                $items['status'] = false;
                $items['code'] = 400;
                return json_encode($items);                    
                }
            } catch (PDOException $ex) {
                return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());

            }
        }
        public function GetCategories() {
          try {
              $ObjectCategory = $this->BBDD->GetFilterCategories2('pTc.product_id >= ?', $this->driver, PREFIX.'category', PREFIX.'category_description', PREFIX.'product_to_category');
              $this->BBDD->runDriver(array(
                  $this->BBDD->scapeCharts(0)
              ), $ObjectCategory);
              if ($this->BBDD->verifyDriver($ObjectCategory)) {
                $items = array();
                $items['status'] = true;
                $items['code'] = 200;
                $items['data'] = $this->BBDD->fetchDriver($ObjectCategory);
                return json_encode($items);
              } else {
                $items = array();
                $items['status'] = false;
                $items['code'] = 400;
                return json_encode($items);
              }
          } catch (PDOException $ex) {
            return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
          }
        }
        public function category($category_id = 0) {
        $categoryType = $this->BBDD->selectDriver('parent_id = ?',PREFIX.'category', $this->driver);
        $this->BBDD->runDriver(array(
            $this->BBDD->scapeCharts(0)
        ), $categoryType);        
        return json_encode($this->BBDD->fetchDriver($categoryType));
             
    } 
        public function categoryAll() {
        $categoryType = $this->BBDD->selectDriver('parent_id != ?',PREFIX.'category', $this->driver);
        $this->BBDD->runDriver(array(
            $this->BBDD->scapeCharts(0)
        ), $categoryType);        
        return json_encode($this->BBDD->fetchDriver($categoryType));
             
    }    
            public function categoryId($category_id) {
        $categoryType = $this->BBDD->selectDriver('parent_id = ?',PREFIX.'category', $this->driver);
        $this->BBDD->runDriver(array(
            $this->BBDD->scapeCharts($category_id)
        ), $categoryType);        
        return json_encode($this->BBDD->fetchDriver($categoryType));
             
    }
        public function categoryDescriptionById($category_id) {
        $manufacturerType = $this->BBDD->selectDriver('category_id = ?',PREFIX.'category_description', $this->driver);
        $this->BBDD->runDriver(array(
            $this->BBDD->scapeCharts($category_id)
        ), $manufacturerType);
        return json_encode($this->BBDD->fetchDriver($manufacturerType));        
    }
        public function categoryDescription() {
        $catDType = $this->BBDD->selectDriver(null,PREFIX.'category_description', $this->driver);
        $this->BBDD->runDriver(null, $catDType);
        return json_encode($this->BBDD->fetchDriver($catDType));        
    } 
    protected $BBDD;
    protected $driver;
}
