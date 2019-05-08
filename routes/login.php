<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type");
header("Access-Control-Allow-Methods", "POST, GET, PUT, DELETE, OPTIONS");
require_once '../models/config.php';
require_once '../models/connection.php';
require_once '../models/imagenValidacion.php';
/*
 * Clase que se encarga de hacer los registros y login de la empresa
 */
if($_GET['operationType']) { 
    $shop = new loginShop();
    switch($_GET['operationType']) {
        case 'register':
            echo $shop->registerShop();
        break;
        case 'login':
            echo $shop->getUser();
        break;
        case 'update':
            echo $shop->updateShop();
        break;
        case 'displayShop': 
            echo $shop->getShop($_GET['rgza_shop']);
            break;
    }
}
class loginShop {
     public function __construct() {
        $this->BBDD = new \dbDriver;
        $this->driver = $this->BBDD->setPDO();
        $this->BBDD->setPDOConfig($this->driver);
    }
    /*
     * Función que registra una nueva tienda afiliada a la tienda padre
     */
    public function registerShop() {
       if(!$this->VerifyUser($_POST['shop_email']) && (!$this->VerifyIfIsClient($_POST['shop_email']))) {
            $time = time();
            $date = date("Y-m-d ", $time);
            $sql = '?,?,?,?,?,?,?';
            $fields = 'customer_group_id,shop_name,shop_address,shop_email,shop_phone,shop_password,shop_date_added';
            $ObjectShop = $this->BBDD->insertDriver($sql,PREFIX.'shop_customer', $this->driver, $fields);
            $this->BBDD->runDriver(array(
                $this->BBDD->scapeCharts($_POST['customer_group_id']),
                $this->BBDD->scapeCharts($_POST['shop_name']),
                $this->BBDD->scapeCharts($_POST['shop_address']),
                $this->BBDD->scapeCharts($_POST['shop_email']),
                $this->BBDD->scapeCharts($_POST['shop_phone']),
                $this->BBDD->scapeCharts($this->hashPassword($_POST['shop_password'])),
                $this->BBDD->scapeCharts($date)
            ), $ObjectShop);
            $object = array();
            $object['status'] = true;
            $object['data'] = $_POST;
            return json_encode($object);
        } else {
            $object = array();
            $object['status'] = false;
            $object['message'] = 'El correo ya existe';
            return json_encode($object);
        }
    }
    public function getShop($shop_id) {
        $shop = $this->BBDD->selectDriver('user_id = ?',PREFIX.'shop_customer', $this->driver);
        $this->BBDD->runDriver(array(
            $this->BBDD->scapeCharts($shop_id)
        ), $shop);
        if ($this->BBDD->verifyDriver($shop)) {
            $object = array();
            $object['status'] = true;
            $object['data'] = $this->BBDD->fetchDriver($shop);
            return json_encode($object);
        } else {
            $object = array();
            $object['status'] = false;
            $object['message'] = 'No existe esta ID';
            return json_encode($object);
        }
    }
        public function updateShop() {
            $file = new validacionImagen();
            if ($file->_getValidate_($_FILES['shop_image'])) {
                // UPDATE CON IMAGEN
                $fileImage = $this->loadImage($_FILES['shop_image']);
                if ($fileImage != null) {
                    /*
                     * Verificamos si ya existe o no una imagen
                     * si existe la actualiza, sino la crea nueva
                     */
                    $profilePicture = $this->getImageData($_POST['user_id']);
                    if(!$profilePicture || empty($profilePicture) ) {
                        $fields = 'shop_name = ?, shop_address = ?,
                        shop_phone = ?, shop_image = ?';
                        try {
                            $objectUpdateProfile = $this->BBDD->updateDriver('user_id = ?',PREFIX.'shop_customer', $this->driver, $fields);
                            $this->BBDD->runDriver(array(
                                $this->BBDD->scapeCharts($_POST['shop_name']),
                                $this->BBDD->scapeCharts($_POST['shop_address']),
                                $this->BBDD->scapeCharts($_POST['shop_phone']),
                                $this->BBDD->scapeCharts(str_replace('../../image/', '', $fileImage)),
                                $this->BBDD->scapeCharts($_POST['user_id'])
                            ), $objectUpdateProfile);
                                $success = array();
                                $success['status'] = true;
                                $success['path'] = str_replace('../../image/', '', $fileImage);
                                $success['data'] = $_POST;
                                $success['message'] = 'Perfil actualizado con éxito';
                                return json_encode($success);                            
                        } catch (PDOException $ex) {
                            return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
                        }
                    } else {
                        /*
                         * Borramos la antigua imagen
                         */
                        $old_path = $this->getPathOld($_POST['user_id'], 'user_id = ?', 'shop_customer');
                        if($old_path != null || (!empty($old_path))) {
                            $delete = unlink("../../image/{$old_path}"); // Borramos la antigua imagen
                                if($delete) {
                                    // Actualizamos por la nueva
                                  $fields = 'shop_name = ?, shop_address = ?,
                                  shop_phone = ?, shop_image = ?';
                                  $objectProductImages = $this->BBDD->updateDriver('user_id = ?',PREFIX.'shop_customer', $this->driver, $fields);
                                  $this->BBDD->runDriver(array(
                                    $this->BBDD->scapeCharts($_POST['shop_name']),
                                    $this->BBDD->scapeCharts($_POST['shop_address']),
                                    $this->BBDD->scapeCharts($_POST['shop_phone']),
                                    $this->BBDD->scapeCharts(str_replace('../../image/', '', $fileImage)),
                                    $this->BBDD->scapeCharts($_POST['user_id'])
                                  ), $objectProductImages);
                                    $success = array();
                                    $success['status'] = true;
                                    $success['path'] = str_replace('../../image/', '', $fileImage);
                                    $success['data'] =  $_POST;
                                    $success['message'] = 'Perfil actualizado con éxito';
                                    return json_encode($success);
                             } else {
                                 $error = array();
                                 $error['status'] = false;
                                 $error['message'] = 'No pudimos cargar la imagen';
                                 return json_encode($error);
                             }                          
                        }                       
                    }
                } else {
                    $error = array();
                    $error['status'] = false;
                    $error['message'] = 'El formato o peso de la imagen no es valido';
                    return json_encode($error);
                }
            } else {
                // UPDATE SIN IMAGEN
                try {
                    $fields = 'shop_name = ?, shop_address = ?, shop_phone = ?';
                    $objectUpdateUser = $this->BBDD->updateDriver('user_id = ?',PREFIX.'shop_customer', $this->driver, $fields);
                    $this->BBDD->runDriver(array(
                        $this->BBDD->scapeCharts($_POST['shop_name']),
                        $this->BBDD->scapeCharts($_POST['shop_address']),
                        $this->BBDD->scapeCharts($_POST['shop_phone']),
                        $this->BBDD->scapeCharts($_POST['user_id'])
                    ), $objectUpdateUser);
                    $success = array();
                    $success['status'] = true;
                    $success['data'] = $_POST;
                    $success['message'] = 'Perfil actualizado con éxito';
                    return json_encode($success);
                } catch (PDOException $ex) {
                    $object = array();
                    $object['status'] = false;
                    $object['message'] = 'Fallo al intentar actualizar, inténtalo más tarde';
                    $object['err'] = $ex->getMessage() . ' ' . $ex->getCode() . ' ' . $ex->getLine();
                    return json_encode($object);
                }
            }
        }
    /****************************************************************
     * AUTH MODE
     ****************************************************************/
     /*
     * Función para identificar si este usuario existe antes de registrar
     */
        private function VerifyUser($email)
    {
        $customer = $this->BBDD->selectDriver('shop_email = ?',PREFIX.'shop_customer',$this->driver);
        $this->BBDD->runDriver(array($this->BBDD->scapeCharts("{$email}")),$customer);
        if($this->BBDD->verifyDriver($customer))
        {
            return true;
        }else{
            return false;
        }
    }
            private function VerifyIfIsClient($email)
    {
        $customer = $this->BBDD->selectDriver("email = ?",PREFIX.'customer',$this->driver);
        $this->BBDD->runDriver(array($this->BBDD->scapeCharts("{$email}")),$customer);
        if($this->BBDD->verifyDriver($customer))
        {
            return true;
        }else{
            return false;
        }
    }
        public function getUser()
    {
     $customer = $this->BBDD->selectDriver('shop_email = ? && customer_group_id = ?',PREFIX.'shop_customer',$this->driver);
     $this->BBDD->runDriver(array(
         $this->BBDD->scapeCharts($_POST['shop_email']),
         $this->BBDD->scapeCharts(2)
             ),$customer);
     if($this->BBDD->verifyDriver($customer))
     {
          $this->customerAuth($this->BBDD->fetchDriver($customer));
     }else{
         $ObjectClient = array();
         $ObjectClient['status'] = 'email_wrong';
         echo json_encode($ObjectClient);
     }
   }
   // MODULO 2 DE SESIÓN VERIFICAMOS LA CONTRASEÑA
   protected function customerAuth($ObjectClient)
   {
       foreach($ObjectClient as $cli)
       {
           if($this->pwdAuth($_POST['shop_password'], $cli->shop_password))
           {
               echo $this->sessionAuth($cli->shop_email, $cli->user_id,
                       $cli->status,$cli->shop_name,
                       $cli->customer_group_id,
                       $cli->shop_address,
                       $cli->shop_phone,
                       $cli->shop_image);
           }else{
                $ObjectClient = array();
                $ObjectClient['status'] = 'pwd_wrong';
                echo json_encode($ObjectClient);
           }
       }
   }
   private function pwdAuth($pwd_form, $pwd_bbdd){
       if(password_verify($this->BBDD->scapeCharts($pwd_form), $pwd_bbdd)){
           return true;
       }else{
           return false;
       }
   }
   public function activateAccount($customer)
   {
       $fields = 'status = 1';
       $update = $this->BBDD->updateDriver('email = ?',PREFIX."customer",$this->driver,$fields);
       $this->BBDD->runDriver(array(
           $this->BBDD->scapeCharts($customer)
       ), $update);       
   }
   public function sessionAuth($email,$id,$status,$realname, $customer_type, $address, $phone,$picture)
   {session_start();
       $_SESSION['email'] = $email;
       $_SESSION['id'] = $id;
       $_SESSION['permise'] = $status;
       $_SESSION['realname'] = $realname;
       $_SESSION['customer_type'] = $customer_type;
       $_SESSION['address'] = $address;
       $_SESSION['phone'] = $phone;
       $_SESSION['photo'] = $picture;
       $_SESSION['status'] = $status;
       return $this->returnSession();
   }
    public function returnSession()
    {
        $this->sessionAuth = array();
        $this->sessionAuth['email'] = $_SESSION['email'];
        $this->sessionAuth['realname'] = $_SESSION['realname'];
        $this->sessionAuth['id'] = $_SESSION['id'];
        $this->sessionAuth['permise'] = $_SESSION['status'];
        $this->sessionAuth['customer_type'] = $_SESSION['customer_type'];
        $this->sessionAuth['phone'] = $_SESSION['phone'];
        $this->sessionAuth['address'] = $_SESSION['address'];
        $this->sessionAuth['photo'] = $_SESSION['photo'];
        $this->sessionAuth['status'] = 'done';
        if(isset($_REQUEST['confirm_account']) && (!empty($_REQUEST['confirm_account'])))
        {
            $this->activateAccount($_SESSION['email']);
        }
        return json_encode($this->sessionAuth);
    }
    public function checkSession()
    {
      session_start();
    if(isset($_SESSION['user']) || (isset($_SESSION['id'])) ){
        return true;
    }else{
        return false;
    }
  }
  /***************************************************************************
   * END AUTH MODE
   ***************************************************************************/
  /***************************************************************************
   * PRIVATE STATEMENTS
   ***************************************************************************/
  private function hashPassword($password)
    {
        $cost = ['cost'=>12,];
        return password_hash($this->BBDD->scapeCharts($password),PASSWORD_BCRYPT,$cost);
    }
            private function loadImage($file){
        $IMG = new validacionImagen();
        $IMG->_setImageName_($file["name"]);
        $IMG->_setImageExtension_($file["type"]);
        $IMG->_setImageSize_($file["size"]);
        $IMG->_setImageTMP_($file["tmp_name"]);
        //MODULE 1
        if(strcmp($IMG->_getImageEXT_(), "image/png")==0
            || (strcmp($IMG->_getImageEXT_(), "image/jpg")==0)
            || (strcmp($IMG->_getImageEXT_(), "image/jpeg")==0)
            || (strcmp($IMG->_getImageEXT_(), "image/gif")==0)){
            //MODULE2
            if($IMG->_getImageSize_()<=(1024*1024)){
                $FOLDER = "../../image/catalog/profile/"; // path
                $src = $FOLDER.$IMG->_getImageName_();
                move_uploaded_file($IMG->_getImageTMP_(), $src);
                return $src;
            }else{return null;}    
        }else{return null;}
    }
        /*
     * Función que elimina el path de la imagen viejo, para evitar sobrecarga de contenido
     */
    private function getPathOld($image_id, $DB, $DB_ROOT) {
        try {
            $collection = $this->BBDD->selectDriver($DB,PREFIX.$DB_ROOT, $this->driver);
            $this->BBDD->runDriver(array(
                $this->BBDD->scapeCharts($image_id)
            ), $collection);
            if ($this->BBDD->verifyDriver($collection)) {
                foreach($this->BBDD->fetchDriver($collection) as $src) {
                    return $src->shop_image;
                }
            } else {
                return null;
            }
        } catch (PDOException $ex) {
            return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
        }
    }
        private function getImageData($user_id) {
        try {
            $productObjectImage = $this->BBDD->selectDriver('user_id = ?',PREFIX.'shop_customer', $this->driver);
            $this->BBDD->runDriver(array(
                $this->BBDD->scapeCharts($user_id)
            ), $productObjectImage);
            if($this->BBDD->verifyDriver($productObjectImage)) {
                foreach($this->BBDD->fetchDriver($productObjectImage) as $src) {
                    return $src->shop_image;
                }
            } else {
                return false;
            }
          } catch (Exception $ex) {
            return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
        }
    }
    /***************************************************************************
   * END PRIVATE STATEMENTS
   ***************************************************************************/
    protected $BBDD;
    protected $driver;
    private $sessionAuth;
}

