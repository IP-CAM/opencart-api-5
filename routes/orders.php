<?php
// Archivo que genera ordenes para empresas, clientes y suma el pedido
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type");
header("Access-Control-Allow-Methods", "POST, GET, PUT, DELETE, OPTIONS");
require_once '../models/config.php';
require_once '../models/connection.php';
if ($_GET) {
    $order = new RagazzaOrders();
    switch($_GET['operationType']) {
        case 'OrderByShop':
            echo $order->shopAffiliatedOrders($_GET['shop_id']);
            break;
        case 'CustomerOrder':
            echo $order->CustomerOrders($_GET['user_id']);
            break;
        case 'sumBalance': 
            echo $order->sumOrdersBalance($_GET['shop_id']);
            break;
        case 'ShopSellQty':
            echo $order->countSells($_GET['shop_id']);
            break;
        case 'findOrder':
            echo $order->orderById($_GET['shop_id']);
            break;
        case 'shopDetails':
            echo $order->ShopDetails($_GET['shop_id']);
    }
}
class RagazzaOrders {
   public function __construct() {
        $this->BBDD = new \dbDriver;
        $this->driver = $this->BBDD->setPDO();
        $this->BBDD->setPDOConfig($this->driver);       
   }
   // Empresas
   public function shopAffiliatedOrders($shop_id) {
       try {
           $orders = $this->BBDD->selectDriver('store_id = ?',PREFIX.'order', $this->driver);
           $this->BBDD->runDriver(array(
               $this->BBDD->scapeCharts($shop_id)
           ), $orders);
           if ($this->BBDD->verifyDriver($orders)) {
               $OrderResponse = array();
               $OrderResponse['status'] = true;
               $OrderResponse['statusCode'] = 200;
               $OrderResponse['data'] = $this->BBDD->fetchDriver($orders);
               return json_encode($OrderResponse);
           }else{
               $OrderResponse = array();
               $OrderResponse['status'] = false;
               $OrderResponse['statusCode'] = 400;
               $OrderResponse['msg'] = 'No existe esta empresa';
               return json_encode($OrderResponse);
           }
       } catch (PDOException $ex) {
            throw new Exception('Fallo al conectar con la base de datos'. PHP_EOL. $ex->getMessage(). ' '. $ex->getLine());           
       }
   }
   // Ordenes por cliente
      public function CustomerOrders($user_id) {
       try {
           $orders = $this->BBDD->selectDriver('user_id = ?',PREFIX.'order', $this->driver);
           $this->BBDD->runDriver(array(
               $this->BBDD->scapeCharts($user_id)
           ), $orders);
           if ($this->BBDD->verifyDriver($orders)) {
               $OrderResponse = array();
               $OrderResponse['status'] = true;
               $OrderResponse['statusCode'] = 200;
               $OrderResponse['data'] = $this->BBDD->fetchDriver($orders);
               return json_encode($OrderResponse);
           }else{
               $OrderResponse = array();
               $OrderResponse['status'] = false;
               $OrderResponse['statusCode'] = 400;
               $OrderResponse['msg'] = 'No existe esta empresa';
               return json_encode($OrderResponse);
           }
       } catch (PDOException $ex) {
            throw new Exception('Fallo al conectar con la base de datos'. PHP_EOL. $ex->getMessage(). ' '. $ex->getLine());           
       }
   }
   // Suma total 
   public function sumOrdersBalance($shop_id) {
       try {
           $sum = $this->BBDD->sumDriver('store_id = ?',PREFIX.'order', $this->driver, 'total');
           $this->BBDD->runDriver(array($this->BBDD->scapeCharts($shop_id)), $sum);
           if($this->BBDD->verifyDriver($sum))
            {
                foreach ($this->BBDD->fetchDriver($sum) as $tot) {
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
       } catch (PDOException $ex) {
            throw new Exception('Fallo al conectar con la base de datos'. PHP_EOL. $ex->getMessage(). ' '. $ex->getLine());           
       }
   }
     public function countSells($shop_id) {
         try {
             $count = $this->BBDD->countDriver('store_id = ?',PREFIX.'order', $this->driver);
             $this->BBDD->runDriver(array(
                 $this->BBDD->scapeCharts($shop_id)
             ), $count);
             if ($this->BBDD->verifyDriver($count)) {
                 foreach ($this->BBDD->fetchDriver($count) as $qty) {
                     $success = array();
                     $success['status'] = true;
                     $success['message'] = 'items cargados';
                     $success['total'] = $qty->index;
                     return json_encode($success);
                 }
             } else {
                 $err['status'] = false;
                 $err['message'] = 'No tiene ningun producto';
                 return json_encode($err);
             }
         } catch (Exception $ex) {
             return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
         }
     }
     public function orderById($invoiceNro) {
         $invoice = $this->BBDD->selectDriver('order_id = ?',PREFIX.'order', $this->driver);
         $this->BBDD->runDriver(array($this->BBDD->scapeCharts($invoiceNro)), $invoice);
         if ($this->BBDD->verifyDriver($invoice)) {
             return json_encode($this->BBDD->fetchDriver($invoice));
         }
     }
     // Para obtener los datos del comercio
     public function ShopDetails($user_id) {
         $shop = $this->BBDD->selectDriver('user_id = ?', PREFIX.'shop_customer', $this->driver);
         $this->BBDD->runDriver(array($this->BBDD->scapeCharts($user_id)), $shop);
         if ($this->BBDD->verifyDriver($shop)) {
             return json_encode($this->BBDD->fetchDriver($shop));
         }
     }
   // $sumCart = $this->BBDD->sumDriver('cart_client_id=?',PREFIX."carrito",$this->driver,"cart_price");
   protected $BBDD;
   protected $driver;
}