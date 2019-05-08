<?php
/*
 * Manejar toda la información de los Proveedores de soluclic o clientes soluclickers
 */
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type");
header("Access-Control-Allow-Methods", "POST, GET, PUT, DELETE, OPTIONS");
header('Content-Type: application/json');
// header('Content-type:application/json;charset=utf-8');
require_once '../../models/config.php';
require_once '../../models/connection.php';

if ($_GET) {
    $cliRoute = new SoluClicCustomers();
    switch ($_GET['operationType']) {
        case 'getClient':
           echo $cliRoute->returnAllCustomerData($_GET['customer_id']);
        break;
        case 'AllClients':
            echo $cliRoute->returnAllClients();
        break;
        case 'dataAddr':
             echo $cliRoute->AllClientAddr($_GET['addr_id']);
        break;
        case 'updateDriver':
            echo $cliRoute->ActivatedProvider($_POST['STATUS']);
        break;
    }
}
class SoluClicCustomers {
    public function __construct() {
        $this->BBDD = new \dbDriver;
        $this->driver = $this->BBDD->setPDO();
        $this->BBDD->setPDOConfig($this->driver);
    }
    public function returnAllCustomerData($cli_id) {
        try {
        $objectCli = $this->BBDD->twiceSearchOption('p.customer_id = ?', 
                PREFIX.'customer', 
                $this->driver,
                PREFIX.'address',
                'customer_id', 'customer_id');
        $this->BBDD->runDriver(array($this->BBDD->scapeCharts($cli_id)), $objectCli);
           if ($this->BBDD->verifyDriver($objectCli)) {
                $object = array();
                $object['status'] = true;
                $object['code'] = 200;
                $object['data'] = $this->BBDD->fetchDriver($objectCli);
                return json_encode($object);
            } else {
                $err = array();
                $err['status'] = false;
                $err['code'] = 400;
                $err['msg'] = 'No pudo cargar el cliente';
                return json_encode($err);
            }
        } catch (Exception $ex) {
           return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
        }        
    }
    public function returnAllClients() {
        try {
            $objectCli = $this->BBDD->selectDriver(null, PREFIX.'customer', $this->driver);
            $this->BBDD->runDriver(null, $objectCli);
            if ($this->BBDD->verifyDriver($objectCli)) {
                $object = array();
                $object['status'] = true;
                $object['code'] = 200;
                $object['data'] = $this->BBDD->fetchDriver($objectCli);
                return json_encode($object);
            } else {
                $err = array();
                $err['status'] = false;
                $err['code'] = 400;
                $err['msg'] = 'No pudo cargar los clientes';
                return json_encode($err);
            }
        } catch (Exception $ex) {
           return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
        }
    }
    public function AllClientAddr ($addr_id) {
        try {
            $objectAddr = $this->BBDD->ThirtySearchOption(
                    'p.address_id = ?',
                    PREFIX.'address',
                    $this->driver,
                    PREFIX.'country',
                    PREFIX.'zone',
                    'country_id',
                    'country_id',
                    'zone_id',
                    'zone_id');
        $this->BBDD->runDriver(array(
            $this->BBDD->scapeCharts($addr_id)
        ), $objectAddr);
            if ($this->BBDD->verifyDriver($objectAddr)) {
                $object = array();
                $object['status'] = true;
                $object['code'] = 200;
                $object['data'] = $this->BBDD->fetchDriver($objectAddr);
                return json_encode($object);
        } else {
                $err = array();
                $err['status'] = false;
                $err['code'] = 400;
                $err['msg'] = 'No pudo el cliente';
                return json_encode($err);
          }
        } catch (Exception $ex) {
           return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());            
        }
    }
    public function ActivatedProvider($status) {
        try {
            $objectCli = $this->BBDD->updateDriver('customer_id', PREFIX.'customer', $this->driver, 'status = ?');
            $this->BBDD->runDriver(array($this->BBDD->scapeCharts($status)), $objectCli);
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
// SELECT DISTINCT * FROM `soluclic_customer` p LEFT JOIN `soluclic_address` pd ON (p.customer_id = pd.customer_id) WHERE p.customer_id = 1
// SELECT DISTINCT * FROM `soluclic_address` p LEFT JOIN `soluclic_country` pd ON (p.country_id = pd.country_id) 
// LEFT JOIN `soluclic_zone` pc ON (pc.zone_id = p.zone_id) WHERE p.address_id = 1