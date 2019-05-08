<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type");
header("Access-Control-Allow-Methods", "POST, GET, PUT, DELETE, OPTIONS");

/*
 * Clase que controlará el sistema de notificaciones
 */
require_once '../models/config.php';
require_once '../models/connection.php';
if ($_GET) {
    $sendPush = new push();
    switch ($_GET['operationType']) {
        case 'getPush': 
            echo $sendPush->getPush($_GET['emp_id']);
            break;
        case 'insertPush':
            echo $sendPush->insertPush();
            break;
    }
}
class push {
    public function __construct() {
        $this->BBDD = new \dbDriver;
        $this->driver = $this->BBDD->setPDO();
        $this->BBDD->setPDOConfig($this->driver);
    }
    public function getPush($employer_id) {
        try {
            $notifications = $this->BBDD->selectDriver('employ_id = ?',PREFIX.'notification', $this->driver);
            $this->BBDD->runDriver(array($this->BBDD->scapeCharts($employer_id)), $notifications);
            if ($this->BBDD->verifyDriver($notifications)) {
                $success = array();
                $success['status'] = true;
                $success['message'] = 'Notificaciones cargadas';
                $success['data'] = $this->BBDD->fetchDriver($notifications);
                return json_encode($success);
            } else {
                $err = array();
                $err['status'] = false;
                $err['message'] = 'No hay notificaciones cargadas';
                return json_encode($err);
            }
        } catch (Exception $ex) {
            throw new Exception('Fallo al conectar con la base de datos'. PHP_EOL. $ex->getMessage(). ' '. $ex->getLine());
        }
    }
    public function insertPush() {
        try {
            $fields = 'employ_id,from_id,message,date';
            $sql = '?,?,?,?';
            $time = time();
            $date = date("Y-m-d ", $time);                      
            $hour =  date("H:i:s", $time);
            $push = $this->BBDD->insertDriver($sql,PREFIX.'notification',$this->driver, $fields);
            $this->BBDD->runDriver(array(
                $this->BBDD->scapeCharts($_POST['employ_id']),
                $this->BBDD->scapeCharts($_POST['from_id']),
                $this->BBDD->scapeCharts($_POST['message']),
                $this->BBDD->scapeCharts($hour),
            ), $push);
            $success = array();
            $success['status'] = true;
            $success['message'] = 'Notificación cargada';
            $success['data'] = $_POST;
            return json_encode($success);
        } catch (Exception $ex) {
            throw new Exception('Fallo al conectar con la base de datos'. PHP_EOL. $ex->getMessage(). ' '. $ex->getLine());
        }
    }
    protected $BBDD;
    protected $driver;
}
