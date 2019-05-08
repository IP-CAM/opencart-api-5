<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type");
header("Access-Control-Allow-Methods", "POST, GET, PUT, DELETE, OPTIONS");
header('Content-Type: application/json');
// header('Content-type:application/json;charset=utf-8');
require_once '../../models/config.php';
require_once '../../models/connection.php';
if ($_GET) {
    $loginRoute = new SoluClicLogin();
    switch ($_GET['operationType']) {
        case 'loginAdmin':
            echo $loginRoute->loginAdmin($_POST['USERNAME'], $_POST['PWD']);
        break;
    }
}
class SoluClicLogin {
      public function __construct() {
        $this->BBDD = new \dbDriver;
        $this->driver = $this->BBDD->setPDO();
        $this->BBDD->setPDOConfig($this->driver);
    }
    
    public function loginAdmin($username, $password) {
        try {
            $sql = "SHA1(CONCAT(salt, SHA1(CONCAT(salt, SHA1(?) ) ) ))";
        $loginAdmin = $this->BBDD->selectDriver(
                "username = ? AND password = {$sql}",
                PREFIX.'user',
                $this->driver);
        $this->BBDD->runDriver(array(
            $this->BBDD->scapeCharts($username),
            $this->BBDD->scapeCharts($password)
        ), $loginAdmin);
        if ($this->BBDD->verifyDriver($loginAdmin)) {
            $object = array();
            $object['status'] = true;
            $object['code'] = 200;          
            $object['data'] = $this->BBDD->fetchDriver($loginAdmin);
            return json_encode($object);            
        } else {
           $err = array();
            $err['status'] = false;
            $err['code'] = 400;
            $err['test'] = $this->driver->query('SELECT * FROM soluclic_user WHERE username = "admin" AND password = SHA1(CONCAT(salt, SHA1(CONCAT(salt, SHA1("Carlos251414") ) ) ))');
            $err['msg'] = 'No pudo cargar el admin';
            return json_encode($err);
        }         
        } catch (PDOException $ex) {
           return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine() + ' ' . $ex->getTraceAsString());
        }
    }
    protected $BBDD;
    protected $driver;
}
// 		$user_query = $this->db->query("SELECT * FROM " . DB_PREFIX . 
// 		"user WHERE username = '" . $this->db->escape($username) . "' AND 
// 		(password = SHA1(CONCAT(salt, SHA1(CONCAT(salt, SHA1('" . $this->db->escape($password) . "'))))) 
// 		OR password = '" . $this->db->escape(md5($password)) . "') AND status = '1'");

// SELECT * FROM `soluclic_user` WHERE username = 
// 'admin' AND password = SHA1(CONCAT(`salt`,   SHA1(CONCAT(`salt`, SHA1('Carlos251414') ) ) ));