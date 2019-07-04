<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type");
header("Access-Control-Allow-Methods", "POST, GET, PUT, DELETE, OPTIONS");
header('Content-Type: application/json');
/*
 * Servicio que se encarga de manipular el carrito
 * Carlos Estarita
 * API RESTFUL 
 */
require_once '../models/config.php';
require_once '../models/connection.php';
if($_GET) {
    $cartRoute = new CartModel();
    switch ($_GET['operationType']) {
        case 'session':
            if (isset($_GET['token']) && (!empty($_GET['token']))) {
                echo $cartRoute->session($_GET['token']);
            } else {
                echo $cartRoute->session();             
            }
        break;
        case 'addItem':
            echo $cartRoute->add();
        break;
        case 'delItem':
            echo $cartRoute->del($_GET['product_id'], $_GET['token']);
        break;
        case 'getItems':
            echo $cartRoute->get($_GET['token']);
        break;
        // wishlist
        case 'addWishItem':
            echo $cartRoute->addWishList();
        break;
        case 'rmWishItem':
            echo $cartRoute->rmItemList($_GET['product_id'], $_GET['customer_id']);
        break;
        case 'getWishList':
            echo $cartRoute->getWishList($_GET['customer_id']);
        break;
        case 'getWishListNoData':
            echo $cartRoute->getWishListWithoutParams($_GET['customer_id']);
        break;    
 }
}
class CartModel {
     public function __construct() {
        $this->BBDD = new \dbDriver;
        $this->driver = $this->BBDD->setPDO();
        $this->BBDD->setPDOConfig($this->driver);
    }
    public function add() {
        try {
            // Verificamos si un item ya esta en el carrito
            $itemExists = $this->BBDD->selectDriver('product_id = ? && session_id = ?', PREFIX.'cart', $this->driver);
            $this->BBDD->runDriver(array(
                $this->BBDD->scapeCharts($_POST['product_id']),
                $this->BBDD->scapeCharts($_POST['session'])
            ), $itemExists);
            if ($this->BBDD->verifyDriver($itemExists)) {
                // Existe por lo que solo lo actualizamos
                $fields = 'quantity = quantity + 1';
                $itemUpdate = $this->BBDD->updateDriver('product_id = ? && session_id = ?', PREFIX.'cart', $this->driver, $fields);
                $this->BBDD->runDriver(array(
                    $this->BBDD->scapeCharts($_POST['product_id']),
                    $this->BBDD->scapeCharts($_POST['session'])
                ), $itemUpdate);
                $ObjectCart = array();
                $ObjectCart['status'] = true;
                $ObjectCart['object'] = $_POST;
                $ObjectCart['operation'] = 'update';
                return json_encode($ObjectCart);
            } else {
                // No existe, lo creamos sin problemas
                $sql = '?, ?, ?, ?, ?, ?, ?, NOW()';
                $fields = 'api_id, customer_id, session_id, product_id, recurring_id, option, quantity, date_added';
                $cart = $this->BBDD->insertDriver($sql, PREFIX.'cart', $this->driver, $fields);
                $this->BBDD->runDriver(array(
                    $this->BBDD->scapeCharts($_POST['api_id']),
                    $this->BBDD->scapeCharts($_POST['customer_id']),
                    $this->BBDD->scapeCharts($_POST['session']),
                    $this->BBDD->scapeCharts($_POST['product_id']),
                    $this->BBDD->scapeCharts($_POST['recurring_id']),
                    $this->BBDD->scapeCharts($_POST['option']),
                    $this->BBDD->scapeCharts($_POST['quantity']),
                ), $cart);
                $ObjectCart = array();
                $ObjectCart['status'] = true;
                $ObjectCart['object'] = $_POST;
                $ObjectCart['operation'] = 'create';
                $ObjectCart['cart_id'] = $this->BBDD->getLastInsert($this->driver);
                return json_encode($ObjectCart);                
            }
        } catch (PDOException $ex) {
            return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
        }          
    }
    public function del($product, $token) {
        try {
            $cart = $this->BBDD->deleteDriver('product_id = ? && session_id = ?', PREFIX.'cart', $this->driver);
            $this->BBDD->runDriver(array(
                $this->BBDD->scapeCharts($product),
                $this->BBDD->scapeCharts($token)
            ), $cart);
            $ObjectCart = array();
            $ObjectCart['status'] = true;
            $ObjectCart['operation'] = 'delete';
            return json_encode($ObjectCart);
        } catch (PDOException $ex) {
            return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
        }
    }
    public function update() {
        
    }
    public function get($token) {
        try {
            // Mostramos toda la data de un producto en el carrito para la vista
            $objectCart = array(); // Donde iremos metiendo toda la data de productos en JSON
            $cart = $this->BBDD->selectDriver('session_id = ?', PREFIX.'cart', $this->driver);
            $this->BBDD->runDriver(array(
                $this->BBDD->scapeCharts($token)
            ), $cart);
            if ($this->BBDD->verifyDriver($cart)) {
                // Hacemos un bucle por cada item en el carrito que este
                foreach ($this->BBDD->fetchDriver($cart) as $items) {
                 // Tiene items en el carrito, ahora mostramos los datos del producto
                    $model = $this->BBDD->ProductAllData('p.product_id = ?', $this->driver, 'soluclic_', PREFIX.'product');
                    $this->BBDD->runDriver(array($this->BBDD->scapeCharts($items->product_id)), $model); 
                    array_push($objectCart, $this->BBDD->fetchDriver($model));
                }
                $resp = array();
                $resp['status'] = true;
                $resp['data'] = $objectCart;
                return json_encode($resp);
            } else {
                $resp = array();
                $resp['status'] = false;
                $resp['msg'] = 'No hay items en el carrito';
                return json_encode($resp);
            }
        } catch (PDOException $ex) {
            return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
        }
    }
    /*
     * Wishlist
     * Permite a los usuarios agregar items a la lista
     */
    public function addWishList() {
        // Determinamos si ese item ya esta en la lista, sino lo creamos
        $validator = $this->BBDD->selectDriver('customer_id = ? && product_id = ?', PREFIX.'customer_wishlist', $this->driver);
        $this->BBDD->runDriver(array(
            $this->BBDD->scapeCharts($_POST['customer_id']),
            $this->BBDD->scapeCharts($_POST['product_id'])
        ), $validator);
        if ($this->BBDD->verifyDriver($validator)) {
            // Existe por lo que no puede volver a guardarlo
                $ObjectCart = array();
                $ObjectCart['status'] = false;
                $ObjectCart['msg'] = 'Este item ya esta en tu lista';
                return json_encode($ObjectCart);
        } else {
            try {
                    $sql = '?, ?, NOW()';
                    $fields = 'customer_id, product_id, date_added';
                    $wishlist = $this->BBDD->insertDriver($sql, PREFIX.'customer_wishlist', $this->driver, $fields);
                    $this->BBDD->runDriver(array(
                        $this->BBDD->scapeCharts($_POST['customer_id']),
                        $this->BBDD->scapeCharts($_POST['product_id'])
                    ), $wishlist);
                        $ObjectCart = array();
                        $ObjectCart['status'] = true;
                        $ObjectCart['object'] = $_POST;
                        return json_encode($ObjectCart);             
            } catch (PDOException $ex) {
                    return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
           }            
        }
    }
    public function rmItemList($product_id, $customer_id) {
        try {
            $wish = $this->BBDD->deleteDriver('customer_id = ? && product_id = ?', PREFIX.'customer_wishlist', $this->driver);
            $this->BBDD->runDriver(array(
                $this->BBDD->scapeCharts($customer_id),
                $this->BBDD->scapeCharts($product_id)
            ), $wish);
            $ObjectCart = array();
            $ObjectCart['status'] = true;
            $ObjectCart['operation'] = 'delete';
            return json_encode($ObjectCart);
        } catch (PDOException $ex) {
            return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
        }
    }
    public function getWishList($customer_id) {
        try {
            // Mostramos toda la data de un producto en el carrito para la vista
            $objectCart = array(); // Donde iremos metiendo toda la data de productos en JSON
            $cart = $this->BBDD->selectDriver('customer_id = ?', PREFIX.'customer_wishlist', $this->driver);
            $this->BBDD->runDriver(array(
                $this->BBDD->scapeCharts($customer_id)
            ), $cart);
            if ($this->BBDD->verifyDriver($cart)) {
                // Hacemos un bucle por cada item en el carrito que este
                foreach ($this->BBDD->fetchDriver($cart) as $items) {
                 // Tiene items en el carrito, ahora mostramos los datos del producto
                    $model = $this->BBDD->ProductAllData('p.product_id = ?', $this->driver, 'soluclic_', PREFIX.'product');
                    $this->BBDD->runDriver(array($this->BBDD->scapeCharts($items->product_id)), $model); 
                    array_push($objectCart, $this->BBDD->fetchDriver($model));
                }
                $resp = array();
                $resp['status'] = true;
                $resp['data'] = $objectCart;
                return json_encode($resp);
            } else {
                $resp = array();
                $resp['status'] = false;
                $resp['msg'] = 'No hay items en el carrito';
                return json_encode($resp);
            }            
        } catch (PDOException $ex) {
            return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
        }
    }
    public function getWishListWithoutParams($customer_id) {
        try {
            $favorite = $this->BBDD->selectDriver('customer_id = ?', PREFIX.'customer_wishlist', $this->driver);
            $this->BBDD->runDriver(array(
                $this->BBDD->scapeCharts($customer_id)
            ), $favorite);
            if ($this->BBDD->verifyDriver($favorite)) {                
                $resp = array();
                $resp['status'] = true;
                $resp['data'] = $this->BBDD->fetchDriver($favorite);
                return json_encode($resp);               
            } else {
                $resp = array();
                $resp['status'] = false;
                $resp['msg'] = 'No esta guardado';
                return json_encode($resp);                
            }
        } catch (PDOException $ex) {
            return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
        }
    }
    public function session($session_id = '') {
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
                return json_encode($session_id);
    }
    protected $BBDD;
    protected $driver;
    protected $session_id;
  }
