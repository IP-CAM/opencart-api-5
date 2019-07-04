<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type");
header("Access-Control-Allow-Methods", "POST, GET, PUT, DELETE, OPTIONS");
header('Content-Type: application/json');
/*
 * Clase que se encarga de administrat todos los clientes de la app cliente
 * Carlos Estarita
 * API RESTFUL 
 */
require_once '../models/config.php';
require_once '../models/connection.php';
require_once '../models/imagenValidacion.php';
if ($_GET) {
    $customerRoute = new CustomerAuth();
    switch ($_GET['operationType']) {
        case 'loginNewCustomerAPI':
            echo $customerRoute->LoginCustomer();
        break;
        case 'registerNewCustomerAPI':
            echo $customerRoute->RegisterNewCustomer();
        break;
    }
}
class CustomerAuth {
    public function __construct() {
        $this->BBDD = new \dbDriver;
        $this->driver = $this->BBDD->setPDO();
        $this->BBDD->setPDOConfig($this->driver);
    }
      
 // Registrar un cliente nuevo  
    public function RegisterNewCustomer() {
        try {
            $salt = $this->token(9);
            $token = $this->session();
            $sql = '?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()';
            $fields = 'customer_group_id, store_id, language_id, firstname, lastname,'
                    . 'email, telephone, fax, password, salt, cart, wishlist,'
                    . 'newsletter, address_id, custom_field, ip, status, safe,'
                    . 'token, code, date_added';
            // Verificamos si el usuario no esta registrado
            $validation = $this->ValidateEmaiL($_POST['email'], $_POST['customer_group_id']);
            if ($validation) {
                $ObjectCustomer = array();
                $ObjectCustomer['status'] = false;
                $ObjectCustomer['operation'] = 'ya registrado';
                return json_encode($ObjectCustomer); 
            } else {
                // Registramos
            $customer = $this->BBDD->insertDriver($sql, PREFIX.'customer', $this->driver, $fields);
            $this->BBDD->runDriver(array(
                $this->BBDD->scapeCharts($_POST['customer_group_id']),
                $this->BBDD->scapeCharts($_POST['store_id']),
                $this->BBDD->scapeCharts($_POST['language_id']),
                $this->BBDD->scapeCharts($_POST['firstname']),
                $this->BBDD->scapeCharts($_POST['lastname']),
                $this->BBDD->scapeCharts($_POST['email']),
                $this->BBDD->scapeCharts($_POST['telephone']),
                $this->BBDD->scapeCharts($_POST['fax']),
                $this->BBDD->scapeCharts(sha1($salt . sha1($salt . sha1($_POST['password'])))),
                $this->BBDD->scapeCharts($salt),
                $this->BBDD->scapeCharts($_POST['cart']),
                $this->BBDD->scapeCharts($_POST['wishlist']),
                $this->BBDD->scapeCharts($_POST['newsletter']),
                $this->BBDD->scapeCharts($_POST['address_id']),
                $this->BBDD->scapeCharts($_POST['custom_field']),
                $this->BBDD->scapeCharts($_POST['ip']),
                $this->BBDD->scapeCharts($_POST['status']),
                $this->BBDD->scapeCharts($_POST['safe']),
                $this->BBDD->scapeCharts($token),
                $this->BBDD->scapeCharts($_POST['code']),
            ), $customer);
                $ObjectCustomer = array();
                $ObjectCustomer['status'] = true;
                $ObjectCustomer['object'] = $_POST;
                $ObjectCustomer['operation'] = 'create';
                $ObjectCustomer['customer_id'] = $this->BBDD->getLastInsert($this->driver);
                $ObjectCustomer['token'] = $token;
                return json_encode($ObjectCustomer);                 
            }
        } catch (PDOException $ex) {
            return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
        }
    }
public function LoginCustomer() {
    try {
             $sql = "SHA1(CONCAT(salt, SHA1(CONCAT(salt, SHA1(?) ) ) ))";
             $loginCustomer = $this->BBDD->selectDriver(
                "email = ? AND password = {$sql} AND customer_group_id = ?",
                PREFIX.'customer',
                $this->driver);
        $this->BBDD->runDriver(array(
            $this->BBDD->scapeCharts($_POST['email']),
            $this->BBDD->scapeCharts($_POST['password']),
            $this->BBDD->scapeCharts($_POST['customer_group_id'])
        ), $loginCustomer);
        if ($this->BBDD->verifyDriver($loginCustomer)) {
            $object = array();
            $object['status'] = true;
            $object['code'] = 200;          
            $object['data'] = $this->BBDD->fetchDriver($loginCustomer);
            return json_encode($object);            
        } else {
            $err = array();
            $err['status'] = false;
            $err['code'] = 400;
            $err['msg'] = 'No encuentra al usuario';
            return json_encode($err);
        }        
    } catch (PDOException $ex) {
            return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
    }
}    
    // Verifica si el usuario ya esta registrado en cualquiera de las plataformas app, o delivery
 public function ValidateEmaiL($email, $customer_group) {
     try {
         $validation = $this->BBDD->selectDriver('email = ? && customer_group_id = ?', PREFIX.'customer', $this->driver);
         $this->BBDD->runDriver(array(
             $this->BBDD->scapeCharts($email),
             $this->BBDD->scapeCharts($customer_group)), $validation);
     if ($this->BBDD->verifyDriver($validation)) {
         return true;
     } else {
         return false;
     }
     } catch (PDOException $ex) {
            return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
     }
 }

/*
 * $this->db->query("INSERT INTO " . DB_PREFIX . "customer SET customer_group_id = '" . 
(int)$data['customer_group_id'] . "', firstname = '" . 
$this->db->escape($data['firstname']) . "', lastname = '" . 
$this->db->escape($data['lastname']) . "', email = '" . $this->db->escape($data['email']) . "', 
telephone = '" . $this->db->escape($data['telephone']) . "', 
custom_field = '" .
 $this->db->escape(isset($data['custom_field']) ? 
 json_encode($data['custom_field']) : json_encode(array())) .
  "', newsletter = '" . (int)$data['newsletter'] . "',
salt = '" . $this->db->escape($salt = token(9)) . "',
password = '" . $this->db->escape(sha1($salt . sha1($salt . sha1($data['password'])))) . "',
 status = '" . (int)$data['status'] . "', 
 safe = '" . (int)$data['safe'] . "',
  date_added = NOW()");

 */
    // Para generar un salt en opencart
    private function token($length = 32) {
	// Create random token
	$string = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
	
	$max = strlen($string) - 1;
	
	$token = '';
	
	for ($i = 0; $i < $length; $i++) {
		$token .= $string[mt_rand(0, $max)];
	}	
	
	return $token;
} 
    private function session($session_id = '') {
            if (!$session_id) {
            if (function_exists('random_bytes')) {
                    $session_id = substr(bin2hex(random_bytes(26)), 0, 26);
            } else {
                    $session_id = substr(bin2hex(openssl_random_pseudo_bytes(26)), 0, 26);
            }
                }

                if (preg_match('/^[a-zA-Z0-9,\-]{22,52}$/', $session_id)) {
                        $this->session_id = $session_id;
                } else {
                        exit('Error: Invalid session ID!');
                }				
                return $session_id;
    }
    protected $driver;
    protected $BBDD;
}