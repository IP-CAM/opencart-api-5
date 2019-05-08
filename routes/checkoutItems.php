<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type");
header("Access-Control-Allow-Methods", "POST, GET, PUT, DELETE, OPTIONS");
/*
 * Clase que se encargará de construir el parametro Items
 * de PlaceToPay
 */
require_once '../models/config.php';
require_once '../models/connection.php';
if ($_GET) {
    $items = new items();
    switch($_GET['operationType']) {
        case 'cartItems':
            echo $items->cart($_GET['customer_id']);
            break;
        case 'cartData':
            echo $items->cartData($_GET['product_id']);
            break;
        case 'cartDescription':
            echo $items->cartDescription($_GET['product_id']);
            break;
        case 'sumCart':
            echo $items->sumCart($_GET['customer_id']);
            break;
    }
}
class items {
    public function __construct() {
        $this->BBDD = new \dbDriver;
        $this->driver = $this->BBDD->setPDO();
        $this->BBDD->setPDOConfig($this->driver);
    }
    public function verifyItems($customer_id) {
        try {
            $cart = $this->BBDD->selectDriver('cart_client_id = ?',PREFIX.'carrito', $this->driver);
            $this->BBDD->runDriver(array($this->BBDD->scapeCharts($customer_id)), $cart);
            if ($this->BBDD->verifyDriver($cart)) {
                return true;
            } else {
                return false;
            }
        } catch (PDOException $ex) {
            return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
        }
    }
    public function cart($customer_id) {
        try {
            if ($this->verifyItems($customer_id)) {
                $cart = $this->BBDD->selectDriver('cart_client_id = ?',PREFIX.'carrito', $this->driver);
                $this->BBDD->runDriver(array($this->BBDD->scapeCharts($customer_id)), $cart);
                $success = array();
                $success['status'] = true;
                $success['message'] = 'Hay items en tu carrito';
                $success['data'] = $this->BBDD->fetchDriver($cart);
                return json_encode($success);
            } else {
                $err = array();
                $err['status'] = false;
                $err['message'] = 'No tiene items en el carrito';
                return json_encode($err);
            }
            
        } catch (PDOException $ex) {
            return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
        }
    }
    public function cartData($product_id){
        try {
            $itemData = $this->BBDD->selectDriver('product_id = ?',PREFIX.'product', $this->driver);
            $this->BBDD->runDriver(array(
                $this->BBDD->scapeCharts($product_id)
            ), $itemData);
            foreach ($this->BBDD->fetchDriver($itemData) as $data) {
                $success = array();
                $success['message'] = 'Data carga con éxito';
                $success['data'] = $data;
                return json_encode($success);
            }
        } catch (PDOException $ex) {
             return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
        }
    }
    public function cartDescription($product_id){
        try {
            $itemData = $this->BBDD->selectDriver('product_id = ?',PREFIX.'product_description', $this->driver);
            $this->BBDD->runDriver(array(
                $this->BBDD->scapeCharts($product_id)
            ), $itemData);
            foreach ($this->BBDD->fetchDriver($itemData) as $description) {
                $success = array();
                $success['message'] = 'Descripción cargada con éxito';
                $success['data'] = $description;
                return json_encode($success);
            }
        } catch (PDOException $ex) {
             return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
        }
    }
    public function sumCart($customer_id)
    {
        $sumCart = $this->BBDD->sumDriver('cart_client_id=?',PREFIX."carrito",$this->driver,"cart_price");
        $this->BBDD->runDriver(array(
            $this->BBDD->scapeCharts($customer_id)
        ), $sumCart);
        if($this->BBDD->verifyDriver($sumCart))
        {
            foreach ($this->BBDD->fetchDriver($sumCart) as $tot) {
                $success = array();
                $success['status'] = true;
                $success['total'] = $tot->total;
                return json_encode($success);
            }
        }else{
            $err = array();
            $err['status'] = false;
            $err['total'] = 0;
            return json_encode($err);
        }
    }
    protected $BBDD;
    protected $driver;
}
