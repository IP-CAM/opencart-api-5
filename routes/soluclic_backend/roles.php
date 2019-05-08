<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type");
header("Access-Control-Allow-Methods", "POST, GET, PUT, DELETE, OPTIONS");
header('Content-Type: application/json');
// header('Content-type:application/json;charset=utf-8');
require_once '../../models/config.php';
require_once '../../models/connection.php';
if ($_GET) {
    $roleRoute = new SoluClicRole();
    switch ($_GET['operationType']) {
        case 'selectRoles':
            echo $roleRoute->returnRoles();
            break;
        case 'selectRolById':
            echo $roleRoute->ReturnRoleById($_GET['role_id']);
            break;
        case 'createRole':
            echo $roleRoute->createNewRole();
            break;
        case 'updateRole':
            echo $roleRoute->updateRole($_POST['role_id']);
            break;
        case 'removeRole':
            echo $roleRoute->removeRole($_GET['role_id']);
            break;
        default:
            echo json_encode('Operación no permitida');
    }
}
class SoluClicRole {
    public function __construct() {
        $this->BBDD = new \dbDriver;
        $this->driver = $this->BBDD->setPDO();
        $this->BBDD->setPDOConfig($this->driver);
    }
    public function returnRoles() {
        try {
            $objectRol = $this->BBDD->selectDriver(null, PREFIX.'role', $this->driver);
            $this->BBDD->runDriver(null, $objectRol);
            if ($this->BBDD->verifyDriver($objectRol)) {
                $object = array();
                $object['status'] = true;
                $object['code'] = 200;
                $object['data'] = $this->BBDD->fetchDriver($objectRol);
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
    public function ReturnRoleById($role) {
        try {
            $objectRol = $this->BBDD->selectDriver('role_id = ?', PREFIX.'role', $this->driver);
            $this->BBDD->runDriver(array(
                $this->BBDD->scapeCharts($role)
            ), $objectRol);
            if ($this->BBDD->verifyDriver($objectRol)) {
                $object = array();
                $object['status'] = true;
                $object['code'] = 200;
                $object['data'] = $this->BBDD->fetchDriver($objectRol);
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
    public function createNewRole() {
        $sql = '?';
        $fields = 'role_name';
        try {
            $objectRol = $this->BBDD->insertDriver($sql, PREFIX.'role', $this->driver, $fields);
            $this->BBDD->runDriver(array(
                $this->BBDD->scapeCharts($_POST['ROL_NAME'])
            ), $objectRol);
                $object = array();
                $object['status'] = true;
                $object['code'] = 200;
                $object['data'] = $this->BBDD->getLastInsert($this->driver);
                return json_encode($object);
        } catch (Exception $ex) {
           return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());            
        }
    }
    public function updateRole($role_id) {
        $fields = 'role_name = ?';
        try {
            $objectRol = $this->BBDD->updateDriver('role_id = ?', PREFIX.'role', $this->driver, $fields);
            $this->BBDD->runDriver(array(
                $this->BBDD->scapeCharts($_POST['ROL_NAME']),
                $this->BBDD->scapeCharts($role_id)
            ), $objectRol);
            $object = array();
            $object['status'] = true;
            $object['code'] = 200;
            return json_encode($object);
        } catch (Exception $ex) {
           return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());            
        }
    }
    public function removeRole($role_id) {
        try {
           $objectRol = $this->BBDD->deleteDriver('role_id = ?', PREFIX.'role', $this->driver);
           $this->BBDD->runDriver(array(
               $this->BBDD->scapeCharts($role_id)
           ), $objectRol);
            $object = array();
            $object['status'] = true;
            $object['code'] = 200;
            return json_encode($object);           
        } catch (Exception $ex) {
           return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());                        
        }
    }
    protected $BBDD;
    protected $driver;
}