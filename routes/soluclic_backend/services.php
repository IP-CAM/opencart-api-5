<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type");
header("Access-Control-Allow-Methods", "POST, GET, PUT, DELETE, OPTIONS");
header('Content-Type: application/json');
// header('Content-type:application/json;charset=utf-8');
require_once '../../models/config.php';
require_once '../../models/connection.php';
if ($_GET) {
    $roleRoute = new SoluClicServices();
    switch ($_GET['operationType']) {
        case 'selectServices':
            echo $roleRoute->returnServices();
            break;
        case 'selectServiceById':
            echo $roleRoute->ReturnServiceById($_GET['SRV_ID']);
            break;
        case 'createService':
            echo $roleRoute->createNewService();
            break;
        case 'updateService':
            echo $roleRoute->updateService($_POST['srv_id']);
            break;
        case 'removeService':
            echo $roleRoute->removeService($_GET['srv_id']);
            break;
        default:
            echo json_encode('Operación no permitida');
    }
}

class SoluClicServices {
     public function __construct() {
        $this->BBDD = new \dbDriver;
        $this->driver = $this->BBDD->setPDO();
        $this->BBDD->setPDOConfig($this->driver);
    }
   public function returnServices() {
        try {
            $objectServices = $this->BBDD->selectDriver(null, PREFIX.'client_services', $this->driver);
            $this->BBDD->runDriver(null, $objectServices);
            if ($this->BBDD->verifyDriver($objectServices)) {
                $object = array();
                $object['status'] = true;
                $object['code'] = 200;
                $object['data'] = $this->BBDD->fetchDriver($objectServices);
                return json_encode($object);
            } else {
                $err = array();
                $err['status'] = false;
                $err['code'] = 400;
                $err['msg'] = 'No pudo cargar los roles';
                return json_encode($err);
            }
        } catch (Exception $ex) {
           return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
        }
    }
        public function ReturnServiceById($srv_id) {
        try {
            $objectServices = $this->BBDD->selectDriver('srv_id = ?', PREFIX.'client_services', $this->driver);
            $this->BBDD->runDriver(array(
                $this->BBDD->scapeCharts($srv_id)
            ), $objectServices);
            if ($this->BBDD->verifyDriver($objectServices)) {
                $object = array();
                $object['status'] = true;
                $object['code'] = 200;
                $object['data'] = $this->BBDD->fetchDriver($objectServices);
                return json_encode($object);
            } else {
                $err = array();
                $err['status'] = false;
                $err['code'] = 400;
                $err['msg'] = 'No pudo cargar el rol';
                return json_encode($err);
            }
        } catch (Exception $ex) {
           return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
        }        
    }
        public function createNewService() {
        $sql = '?';
        $fields = 'srv_name';
        try {
            $objectService = $this->BBDD->insertDriver($sql, PREFIX.'client_services', $this->driver, $fields);
            $this->BBDD->runDriver(array(
                $this->BBDD->scapeCharts($_POST['SRV_NAME'])
            ), $objectService);
                $object = array();
                $object['status'] = true;
                $object['code'] = 200;
                $object['data'] = $this->BBDD->getLastInsert($this->driver);
                return json_encode($object);
        } catch (Exception $ex) {
           return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());            
        }
    }
      public function updateService($srv_id) {
        $fields = 'srv_name = ?';
        try {
            $objectService = $this->BBDD->updateDriver('srv_id = ?', PREFIX.'client_services', $this->driver, $fields);
            $this->BBDD->runDriver(array(
                $this->BBDD->scapeCharts($_POST['SRV_NAME']),
                $this->BBDD->scapeCharts($srv_id)
            ), $objectService);
            $object = array();
            $object['status'] = true;
            $object['code'] = 200;
            return json_encode($object);
        } catch (Exception $ex) {
           return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());            
        }
    }
     public function removeService($srv_id) {
        try {
           $objectRol = $this->BBDD->deleteDriver('srv_id = ?', PREFIX.'client_services', $this->driver);
           $this->BBDD->runDriver(array(
               $this->BBDD->scapeCharts($srv_id)
           ), $objectRol);
            $object = array();
            $object['status'] = true;
            $object['code'] = 200;
            return json_encode($object);           
        } catch (Exception $ex) {
           return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());                        
        }
    }
    protected $driver;
    protected $BBDD;
}
