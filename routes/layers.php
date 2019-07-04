<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type");
header("Access-Control-Allow-Methods", "POST, GET, PUT, DELETE, OPTIONS");
header('Content-Type: application/json');
/*
 * Backend que se encarga de construir los layers de ofertas, regalos etc
 * como publicidad
 * Carlos Estarita
 * 
 */
require_once '../models/config.php';
require_once '../models/connection.php';
require_once '../models/imagenValidacion.php';

if ($_GET) {
    $layerRoute = new Layers();
    switch($_GET['operationType']) {
        case 'getLayers':
            echo $layerRoute->Getlayers();
        break;
    }
}

class Layers {
         public function __construct() {
        $this->BBDD = new \dbDriver;
        $this->driver = $this->BBDD->setPDO();
        $this->BBDD->setPDOConfig($this->driver);
    }
    /*
     * Public function Get Layer
     */
    public function Getlayers () {
        try {
               $layer = $this->BBDD->twiceSearchOption('1', PREFIX.'information', $this->driver, PREFIX.'information_description', 'information_id', 'information_id');
               $this->BBDD->runDriver(array($this->BBDD->scapeCharts(1)), $layer);
               if ($this->BBDD->verifyDriver($layer)) {
                   $ads = array();
                   $ads['status'] = true;
                   $ads['code'] = 200;
                   $ads['layers'] = $this->BBDD->fetchDriver($layer);
                   return json_encode($ads);
               } else {
                   $ads = array();
                   $ads['status'] = false;
                   $ads['code'] = 200;
                   return json_encode($ads);
               }
        } catch (PDOException $ex) {
            return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
        }
        
    }
    protected $BBDD;
    protected $driver;
}