<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type");
header("Access-Control-Allow-Methods", "POST, GET, PUT, DELETE, OPTIONS");
header('Content-Type: application/json');

/*
 * Clase que retorna las direcciónes de envío que posee el Usuario
 * o crear una nueva
 * Carlos Estarita
 */
require_once '../models/config.php';
require_once '../models/connection.php';
if ($_GET) {
    $address = new Address();
    switch($_GET['operationType']) {
        case 'getAddress':
            echo $address->getAddress($_GET['customer_id']);
            break;
        /*
         * Update new get address
         */
        case 'getAddressNewApi':
            echo $address->GetAddressNewAPI($_GET['customer_id']);
        break;
        case 'createAddr':
            echo $address->CreateNewAddr();
        break;
        case 'updateAddr':
            echo $address->updateAddr();
        break;
    /*
     * End update
     */
        case 'getCountry': 
            echo $address->getCountry($_GET['country_id']);
            break;
        case 'getState': 
            echo $address->getZone($_GET['zone_id']);
            break;
        case 'getPayer':
            echo $address->getPayerDetails($_GET['customer_id']);
            break;
        case 'getAllCountries':
            echo $address->getAllCountries();
            break;
        case 'getAllZones':
            echo $address->getAllZones();
            break;
        case 'getRegion':
            echo $address->getAllZonesByCountry($_GET['country_id']);
            break;
    }
}
class Address {
    public function __construct() {
        $this->BBDD = new \dbDriver;
        $this->driver = $this->BBDD->setPDO();
        $this->BBDD->setPDOConfig($this->driver);
    }
    public function getAddress($customer_id) {
        $addr = $this->BBDD->selectDriver('customer_id = ?',PREFIX.'address', $this->driver);
        $this->BBDD->runDriver(array($this->BBDD->scapeCharts($customer_id)), $addr);
        if ($this->BBDD->verifyDriver($addr)) {
            $success = array();
            $success['status'] = true;
            $success['data'] = $this->BBDD->fetchDriver($addr);
            return json_encode($success);
        } else {
            $err = array();
            $err['status'] = false;
            $err['message'] = 'No tiene direcciones cargadas';
            return json_encode($err);
        }
    }
    // Actualización 4/7/2019 obtener la dirección con lef joins
    public function GetAddressNewAPI($customer_id) {
        try {
            $addr = $this->BBDD->ThirtySearchOption('customer_id = ?',
                    PREFIX.'address',
                    $this->driver,
                    PREFIX.'country', PREFIX.'zone',
                    'country_id',
                    'country_id',
                    'zone_id',
                    'zone_id');
            $this->BBDD->runDriver(array($this->BBDD->scapeCharts($customer_id)), $addr);
            if ($this->BBDD->verifyDriver($addr)) {
                $success = array();
                $success['status'] = true;
                $success['data'] = $this->BBDD->fetchDriver($addr);
                return json_encode($success);                
            } else {
                $err = array();
                $err['status'] = false;
                $err['message'] = 'No tiene direcciones cargadas';
                return json_encode($err);                
            }
        } catch (Exception $ex) {
          return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
        }
    }
    // Crear una nueva dirección
    public function CreateNewAddr() {
        try {
            $sql = '?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?';
            $fields = 'customer_id, firstname, lastname, company, address_1, address_2, city, postcode, country_id, zone_id, custom_field';
            $addr = $this->BBDD->insertDriver($sql, PREFIX.'address', $this->driver, $fields);
            $this->BBDD->runDriver(array(
                $this->BBDD->scapeCharts($_POST['customer_id']),
                $this->BBDD->scapeCharts($_POST['firstname']),
                $this->BBDD->scapeCharts($_POST['lastname']),
                $this->BBDD->scapeCharts($_POST['company']),
                $this->BBDD->scapeCharts($_POST['address_1']),
                $this->BBDD->scapeCharts($_POST['address_2']),
                $this->BBDD->scapeCharts($_POST['city']),
                $this->BBDD->scapeCharts($_POST['postcode']),
                $this->BBDD->scapeCharts($_POST['country_id']),
                $this->BBDD->scapeCharts($_POST['zone_id']),
                $this->BBDD->scapeCharts($_POST['custom_field']),
            ), $addr);
            $objectAddr = array();
            $objectAddr['status'] = true;
            $objectAddr['data'] = $_POST;
            $objectAddr['id'] = $this->BBDD->getLastInsert($this->driver);
            return json_encode($objectAddr);
        } catch (PDOException $ex) {
            return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
        }
    }
    public function updateAddr() {
        try {
            $fields = 'firstname = ?, lastname = ?, company = ?, address_1 = ?, address_2 = ?,'
                    . 'city = ?, postcode = ?, country_id = ?, zone_id = ?, custom_field = ?';
            $addr = $this->BBDD->updateDriver('address_id = ? AND customer_id = ?', PREFIX.'address', $this->driver, $fields);
            $this->BBDD->runDriver(array(
                $this->BBDD->scapeCharts($_POST['firstname']),
                $this->BBDD->scapeCharts($_POST['lastname']),
                $this->BBDD->scapeCharts($_POST['company']),
                $this->BBDD->scapeCharts($_POST['address_1']),
                $this->BBDD->scapeCharts($_POST['address_2']),
                $this->BBDD->scapeCharts($_POST['city']),
                $this->BBDD->scapeCharts($_POST['postcode']),
                $this->BBDD->scapeCharts($_POST['country_id']),
                $this->BBDD->scapeCharts($_POST['zone_id']),
                $this->BBDD->scapeCharts($_POST['custom_field']), 
                $this->BBDD->scapeCharts($_POST['address_id']),
                $this->BBDD->scapeCharts($_POST['customer_id']),
            ), $addr);
            $objectAddr = array();
            $objectAddr['status'] = true;
            $objectAddr['data'] = $_POST;
            $objectAddr['id'] = $this->BBDD->getLastInsert($this->driver);
            return json_encode($objectAddr);
        } catch (PDOException $ex) {
            return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
        }
    }
    public function getCountry($country_id) {
        $country = $this->BBDD->selectDriver('country_id = ?',PREFIX.'country', $this->driver);
        $this->BBDD->runDriver(array($this->BBDD->scapeCharts($country_id)), $country);
        foreach ($this->BBDD->fetchDriver($country) as $CODE) {
            $iso = array();
            $iso['status'] = true;
            $iso['name'] = $CODE->name;
            $iso['iso_code'] = $CODE->iso_code_2;
            return json_encode($iso);
        }
    }
    public function getZone($zone_id) {
        $region = $this->BBDD->selectDriver('zone_id = ?', PREFIX.'zone', $this->driver);
        $this->BBDD->runDriver(array($this->BBDD->scapeCharts($zone_id)), $region);
        foreach ($this->BBDD->fetchDriver($region) as $REGION) {
            $region = array();
            $region['status'] = true;
            $region['name'] = $REGION->name;
            return json_encode($region);
        }
    }
        public function getPayerDetails($customer_id) {
        $client = $this->BBDD->selectDriver('customer_id = ?',PREFIX.'customer', $this->driver);
        $this->BBDD->runDriver(array($this->BBDD->scapeCharts($customer_id)), $client);
        if ($this->BBDD->verifyDriver($client)) {
            foreach ($this->BBDD->fetchDriver($client) as $buyer) {
                $success = array();
                $success['status'] = true;
                $success['name'] = $buyer->firstname;
                $success['surname'] = $buyer->lastname;
                $success['email'] = $buyer->email;
                $success['mobile'] = $buyer->telephone;
                return json_encode($success);
            }
        } else {
            $err = array();
            $err['status'] = false;
            $err['message'] = 'No tiene direcciones cargadas';
            return json_encode($err);
        }
    }
    public function getAllCountries() {
        $country = $this->BBDD->selectDriver(null,PREFIX.'country', $this->driver);
        $this->BBDD->runDriver(null, $country);
        return json_encode($this->BBDD->fetchDriver($country));
    }
        public function getAllZonesByCountry($country) {
        $zone = $this->BBDD->selectDriver('country_id = ?',PREFIX.'zone', $this->driver);
        $this->BBDD->runDriver(array($this->BBDD->scapeCharts($country)), $zone);
        return json_encode($this->BBDD->fetchDriver($zone));
    }
    protected $BBDD;
    protected $driver;
}
