<?php 
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type");
header("Access-Control-Allow-Methods", "POST, GET, PUT, DELETE, OPTIONS");
/*
 * Backend que se encarga de construir los productos desde la empresa
 * Carlos Estarita
 * API RESTFUL 
 */
require_once '../models/config.php';
require_once '../models/connection.php';
require_once '../models/imagenValidacion.php';
if($_GET) {
    // Evaluamos la URL para saber el tipo de petición
    $productRoute = new products();
    switch($_GET['operationType']) {     
     /**************************************************************
     * GETS
     *************************************************************/
        
        case 'select':
            echo $productRoute->selectProducts($_GET['user_id']);         
            break;
        case 'selectProductTable':
            echo $productRoute->getProductAndDescription($_GET['user_id']);
            break;
        case 'selectData':
            echo $productRoute->selectProductDataById($_GET['product_id']);
            break;
        case 'selectDescription':
            echo $productRoute->selectProductDescriptionById($_GET['product_id']);
            break;
        case 'searchProduct':
            echo $productRoute->searchProduct();
            break;
        case 'selectFilter':
            echo $productRoute->selectProductFilterById($_GET['product_id']);
            break;
        case 'getImage':
            echo $productRoute->getImageData($_GET['product_id']);
            break;
        case 'selectCategory':
            echo $productRoute->selectProductcategoryById($_GET['product_id']);
            break;
        case 'selectDiscount':
            echo $productRoute->selectProductDiscountById($_GET['product_id']);
            break;    
        case 'selectSpecial':
            echo $productRoute->selectProductSpecialById($_GET['product_id']);
            break;
        case 'selectImage':
            echo $productRoute->selectProductImagesById($_GET['product_id']);
            break;
        case 'selectTableData':
            echo $productRoute->selectTableData($_GET['product_id']);
            break;
        case 'returnID':
            echo $productRoute->returnIDProduct($_GET['product_id']);
            break;
     /**************************************************************
     *  INSERTS
     *************************************************************/
        case 'insert':
            echo $productRoute->insertProduct();
            break;      
        case 'insertDescription':
            echo $productRoute->insertDescription();
            break;
        case 'insertDiscount':
            echo $productRoute->insertDiscount();
            break;
        case 'insertFilter':
            echo $productRoute->insertProductFilter();
            break;
        case 'insertImage':
            echo $productRoute->insertImage();
            break;
        case 'insertSpecial':
            echo $productRoute->insertSpecial();
            break;
        case 'insertCategory':
            echo $productRoute->insertCategory();
            break;
    /**************************************************************
     *  End INSERTS
     *************************************************************/
     /**************************************************************
     *  DELETES
     *************************************************************/
            
     /**************************************************************
     *  UPDATES
     *************************************************************/
        case 'update':
            echo $productRoute->updateProduct();
            break;
        case 'updateDescription':
            echo $productRoute->updateProductDescription();
            break;
        case 'updateDiscount':
            echo $productRoute->updateDiscount();
            break;
        case 'updateFilter':
            echo $productRoute->updateFilter();
            break;
        case 'updateImages':
            echo $productRoute->updateImages();
            break;
        case 'updateImage':
            echo $productRoute->updateImage();
            break;
        case 'updateSpecial':
            echo $productRoute->updateSpecial();
            break;
        case 'updateProductCategory':
            echo $productRoute->updateProductCategory();
            break;
        case 'insertFirstImage':
            echo $productRoute->updateImage();
            break;
     /**************************************************************
     *  END UPDATE
     *************************************************************/
     /**************************************************************
     *  DELETE
     *************************************************************/
        case 'deleteProduct':
            echo $productRoute->deleteProduct();
            break;
        case 'deleteSpecial':
            echo $productRoute->deleteSpecial();
            break;
        case 'deleteDiscount':
            echo $productRoute->deleteDiscount();
            break;
        case 'deleteImageProduct':
            echo $productRoute->deleteImageProduct();
            break;
        case 'deleteFilter':
            echo $productRoute->deleteFilter();
            break;
        case 'deleteCategory':
            echo $productRoute->deleteCategory();
            break;
     /**************************************************************
     *  END DELETE
     *************************************************************/
        case 'selectOne':
            return $productRoute->getProductById($_GET['product_id'],$_GET['user_id']);
            break;
        case 'countItems':
            echo $productRoute->countItemsPosted($_GET['emp_id']);
            break;
        case 'countSell':
            echo $productRoute->countSell($_GET['emp_id']);
            break;
        default:
            return json_encode('Operación no permitida');
    }
}
class products {
     public function __construct() {
        $this->BBDD = new \dbDriver;
        $this->driver = $this->BBDD->setPDO();
        $this->BBDD->setPDOConfig($this->driver);
    }
    public function selectProducts($user_id) {
        // Obtener todos los productos de la bbdd asociados a la empresa
        try {
            $objectProducts = $this->BBDD->selectDriver('user_id = ?', PREFIX."product", $this->driver);
            $this->BBDD->runDriver(array(
                $this->BBDD->scapeCharts($user_id)
            ), $objectProducts);
            if ($this->BBDD->verifyDriver($objectProducts)) {
                return json_encode($this->BBDD->fetchDriver($objectProducts));
            } else {
                return json_encode('null');
            }
        } catch (PDOException $ex) {
            return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
        }
    }
    /*
     * Para obtener una lista de productos CRUD
     */
        public function selectProductPHP($user_id) {
        // Obtener todos los productos de la bbdd asociados a la empresa
        try {
            $objectProducts = $this->BBDD->selectDriver('user_id = ?', PREFIX."product", $this->driver);
            $this->BBDD->runDriver(array(
                $this->BBDD->scapeCharts($user_id)
            ), $objectProducts);
            if ($this->BBDD->verifyDriver($objectProducts)) {
                return $this->BBDD->fetchDriver($objectProducts);
            } else {
                return null;
            }
        } catch (PDOException $ex) {
            return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
        }
    }
        public function getProductAndDescription($user_id) {
            try {
                $data = $this->selectProductPHP($user_id);
                if ( $data != null ) {
                    foreach ($data as $productData) {
                        $name = $this->BBDD->selectDriver('product_id = ?',PREFIX.'product_description', $this->driver);
                        $this->BBDD->runDriver(array($this->BBDD->scapeCharts($productData->product_id)), $name);
                        $dataName = $this->BBDD->fetchDriver($name);
                        foreach($dataName as $nameData) {
                            $success = array();
                            $success['data'] = $data;
                            $success['status'] = true;
                            return json_encode($success);
                        }
                    }
                } else {
                    $err = array();
                    $err['status'] = false;
                    $err['message'] = 'No hay datos para mostrar';
                    return json_encode($err);
                }
            } catch (PDOException $ex) {
                return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
            }
        }
    public function selectProductDataById($product_id) {
        try {
            $productData = $this->BBDD->selectDriver('product_id = ?',PREFIX.'product', $this->driver);
            $this->BBDD->runDriver(array(
                $this->BBDD->scapeCharts($product_id)
            ), $productData);
            if ($this->BBDD->verifyDriver($productData)) {
                return json_encode($this->BBDD->fetchDriver($productData));
            } else {
                $error = array();
                $error['status'] = false;
                $error['message'] = 'No existe este producto';
                return json_encode($error);
            }
        } catch (PDOException $ex) {
            return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
        }
    }
    /*
     * BUSCADOR
     */
    public function searchProduct() {
        try {
            $product = $this->BBDD->selectDriver('name LIKE ? && user_id = ?',PREFIX.'product_description', $this->driver);
            $this->BBDD->runDriver(array(
                $this->BBDD->scapeCharts("%{$_POST['name']}%"),
                $this->BBDD->scapeCharts($_POST['user_id']),
            ), $product);
            if ( $this->BBDD->verifyDriver($product) ) {
                $success = array();           
                foreach($this->BBDD->fetchDriver($product) as $findImage) {                
                    $success['data'] = $findImage;
                    $image = $this->getPathOld($findImage->product_id,'product_id = ?','product');     
                    $success['status'] = true;             
                    $success['image'] = $image;
                    return json_encode($success);
                }
                // private function getPathOld($image_id, $DB, $DB_ROOT) {
            } else {
                $err = array();
                $err['status'] = false;
                $err['message'] = 'No se encontraron productos';
                $err['data'] = $_POST;
                return json_encode($err);
            }
        } catch (PDOException $ex) {
            return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
        }
    }
        public function returnIDProduct($product_id) {
        try {
            $productData = $this->BBDD->selectDriver('product_id = ?',PREFIX.'product', $this->driver);
            $this->BBDD->runDriver(array(
                $this->BBDD->scapeCharts($product_id)
            ), $productData);
            if ($this->BBDD->verifyDriver($productData)) {
                // return json_encode($this->BBDD->fetchDriver($productData));
                foreach($this->BBDD->fetchDriver($productData) as $id) {
                    return json_encode($id->user_id);
                }
            } else {
                $error = array();
                $error['status'] = false;
                $error['message'] = 'No existe este producto';
                return json_encode($error);
            }
        } catch (PDOException $ex) {
            return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
        }
    }
        public function selectProductDescriptionById($product_id) {
        try {
            $productData = $this->BBDD->selectDriver('product_id = ?',PREFIX.'product_description', $this->driver);
            $this->BBDD->runDriver(array(
                $this->BBDD->scapeCharts($product_id)
            ), $productData);
            if ($this->BBDD->verifyDriver($productData)) {
                return json_encode($this->BBDD->fetchDriver($productData));
            } else {
                $error = array();
                $error['status'] = false;
                $error['message'] = 'No existe este producto';
                return json_encode($error);
            }
        } catch (PDOException $ex) {
            return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
        }
    }
       public function selectProductFilterById($product_id) {
        try {
            $productData = $this->BBDD->selectDriver('product_id = ?',PREFIX.'product_filter', $this->driver);
            $this->BBDD->runDriver(array(
                $this->BBDD->scapeCharts($product_id)
            ), $productData);
            if ($this->BBDD->verifyDriver($productData)) {
                return json_encode($this->BBDD->fetchDriver($productData));
            } else {
                $error = array();
                $error['status'] = false;
                $error['message'] = 'No existe este producto';
                return json_encode($error);
            }
        } catch (PDOException $ex) {
            return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
        }
    }
       public function selectTableData($product_id) {
           try {
            $filter = str_replace("]", "", $this->selectProductFilterById($product_id));
            $cat = str_replace("[", ",", $this->selectProductcategoryById($product_id));
            return $filter.$cat;
           } catch (PDOException $ex) {
               return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
           }
       }
       public function selectProductcategoryById($product_id) {
        try {
            $productData = $this->BBDD->selectDriver('product_id = ?',PREFIX.'product_to_category', $this->driver);
            $this->BBDD->runDriver(array(
                $this->BBDD->scapeCharts($product_id)
            ), $productData);
            if ($this->BBDD->verifyDriver($productData)) {
                return json_encode($this->BBDD->fetchDriver($productData));
            } else {
                $error = array();
                $error['status'] = false;
                $error['message'] = 'No existe este producto';
                return json_encode($error);
            }
        } catch (PDOException $ex) {
            return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
        }
    }
     public function selectProductDiscountById($product_id) {
        try {
            $productData = $this->BBDD->selectDriver('product_id = ?',PREFIX.'product_discount', $this->driver);
            $this->BBDD->runDriver(array(
                $this->BBDD->scapeCharts($product_id)
            ), $productData);
            if ($this->BBDD->verifyDriver($productData)) {
                return json_encode($this->BBDD->fetchDriver($productData));
            } else {
                $error = array();
                $error['status'] = false;
                $error['message'] = 'No existe este producto';
                return json_encode($error);
            }
        } catch (PDOException $ex) {
            return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
        }
    }
     public function selectProductSpecialById($product_id) {
        try {
            $productData = $this->BBDD->selectDriver('product_id = ?',PREFIX.'product_special', $this->driver);
            $this->BBDD->runDriver(array(
                $this->BBDD->scapeCharts($product_id)
            ), $productData);
            if ($this->BBDD->verifyDriver($productData)) {
                return json_encode($this->BBDD->fetchDriver($productData));
            } else {
                $error = array();
                $error['status'] = false;
                $error['message'] = 'No existe este producto';
                return json_encode($error);
            }
        } catch (PDOException $ex) {
            return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
        }
    }
         public function selectProductImagesById($product_id) {
        try {
            $productData = $this->BBDD->selectDriver('product_id = ?',PREFIX.'product_image', $this->driver);
            $this->BBDD->runDriver(array(
                $this->BBDD->scapeCharts($product_id)
            ), $productData);
            if ($this->BBDD->verifyDriver($productData)) {
                return json_encode($this->BBDD->fetchDriver($productData));
            } else {
                $error = array();
                $error['status'] = false;
                $error['message'] = 'No existe este producto';
                return json_encode($error);
            }
        } catch (PDOException $ex) {
            return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
        }
    }
    /**************************************************************
     * INSERT MODE
     *************************************************************/
    /*
     * Función para insertar la data padre de un producto en la tienda 
     * para hacer la relación
     */
    public function insertProduct($user_id) {
        $fileImage = new validacionImagen();
         if($fileImage->_getValidate_($_FILES['image'])) {
             $file = $this->loadImage($_FILES['image']);
             if ($file!=null) {
           try {
            $time = time();
            $date = date("Y-m-d ", $time);
            $sql = '?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?';
            $fields = '
                        user_id,model,sku,upc,ean,jan,isbn,mpn,
                        location,quantity,stock_status_id,image,
                        manufacturer_id, shipping,price,points,
                        tax_class_id,date_available,weight,weight_class_id,
                        length,width,height,length_class_id,subtract,minimum,
                        sort_order,status,viewed,date_added,date_modified
                      ';
            $objectProduct = $this->BBDD->insertDriver($sql,PREFIX."product", $this->driver, $fields);
            $this->BBDD->runDriver(array(
                $this->BBDD->scapeCharts($_REQUEST['user_id']),
                $this->BBDD->scapeCharts($_REQUEST['model']),
                $this->BBDD->scapeCharts($_REQUEST['sku']),
                $this->BBDD->scapeCharts($_REQUEST['upc']),
                $this->BBDD->scapeCharts($_REQUEST['ean']),
                $this->BBDD->scapeCharts($_REQUEST['jan']),
                $this->BBDD->scapeCharts($_REQUEST['isbn']),
                $this->BBDD->scapeCharts($_REQUEST['mpn']),
                $this->BBDD->scapeCharts($_REQUEST['location']),
                $this->BBDD->scapeCharts($_REQUEST['quantity']),
                $this->BBDD->scapeCharts($_REQUEST['stock_status_id']),
                $this->BBDD->scapeCharts(str_replace('../../image/', '', $file)),
                $this->BBDD->scapeCharts($_REQUEST['manufacturer_id']),
                $this->BBDD->scapeCharts($_REQUEST['shipping']),
                $this->BBDD->scapeCharts($_REQUEST['price']),
                $this->BBDD->scapeCharts($_REQUEST['points']),
                $this->BBDD->scapeCharts($_REQUEST['tax_class_id']),
                $this->BBDD->scapeCharts($_REQUEST['date_available']),
                $this->BBDD->scapeCharts($_REQUEST['weight']),
                $this->BBDD->scapeCharts($_REQUEST['weight_class_id']),
                $this->BBDD->scapeCharts($_REQUEST['length']),
                $this->BBDD->scapeCharts($_REQUEST['width']),
                $this->BBDD->scapeCharts($_REQUEST['height']),
                $this->BBDD->scapeCharts($_REQUEST['length_class_id']),
                $this->BBDD->scapeCharts($_REQUEST['subtract']),
                $this->BBDD->scapeCharts($_REQUEST['minimum']),
                $this->BBDD->scapeCharts($_REQUEST['sort_order']),
                $this->BBDD->scapeCharts($_REQUEST['status']),
                $this->BBDD->scapeCharts($_REQUEST['viewed']),
                $this->BBDD->scapeCharts($date),
                $this->BBDD->scapeCharts(''),
            ), $objectProduct);
            $success = array();
            $success['status'] = true;
            $success['message'] = 'El articulo ha sido publicado correctamente';
            $id = $this->getProductByRef($_POST['user_id'], $_POST['model'], $date);
            if($id!=null) {
                $success['data'] = $id;
            } else {
                $success['data'] = 0;
            }
            return json_encode($success);
        } catch (PDOException $ex) {
            return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
        } 
       } else {
           $error = array();
           $error['status'] = false;
           $error['message'] = 'El formato o peso de la imagen es incorrecto';
           return json_encode($error);
       }
     } else {
           $error = array();
           $error['status'] = false;
           $error['message'] = 'El formato o peso de la imagen es incorrecto';
           return json_encode($error);
     }       
   }
        /*
     * Función que se encarga de traer un parametro de tipo File
     * validar formato, tipo y transformar el path 
     */
    public function insertImage() {
        $fileImage = new validacionImagen();
        if($fileImage->_getValidate_($_FILES['image'])) {
            $file = $this->loadImage($_FILES['image']);
            if ($file!=null) {
                // Si pasa toda la validación de la imagen entonces publicamos en la bbdd
                try {
                    $sql = '?,?,?,?';
                    $fields = 'product_image_id,product_id,
                               image, sort_order
                               ';
                    $objectProductImage = $this->BBDD->insertDriver($sql,PREFIX.'product_image', $this->driver, $fields);
                    $this->BBDD->runDriver(array(
                        $this->BBDD->scapeCharts($_POST['product_image_id']),
                        $this->BBDD->scapeCharts($_POST['product_id']),
                        $this->BBDD->scapeCharts(str_replace('../../image/', '', $file)),
                        $this->BBDD->scapeCharts($_POST['sort_order']),
                    ), $objectProductImage);
                        $success = array();
                        $success['status'] = true;
                        $success['message'] = 'Imagen insertada con éxito';
                        $success['data'] = $_POST;
                        return json_encode($success);
                } catch (Exception $ex) {
                    return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
                }
            }else {
                return json_encode('El peso de la imagen o el formato no es correcto');
            }
        } else {
            return json_encode('Debes cargar un archivo');
        }
    }
    /*
     * Función para insertar los datos de lectura en la tienda
     */
    public function insertDescription() {
         try {
        $sql = '?,?,?,?,?,?,?,?,?';
        $fields = 'user_id,product_id,language_id,name,
                   description,tag,meta_title,
                   meta_description,meta_keyword
                   ';
        $objectProductDescription = $this->BBDD->insertDriver($sql,PREFIX.'product_description', $this->driver, $fields);  
        $this->BBDD->runDriver(array(
            $this->BBDD->scapeCharts($_REQUEST['user_id']),
            $this->BBDD->scapeCharts($_REQUEST['product_id']),
            $this->BBDD->scapeCharts(1),
            $this->BBDD->scapeCharts($_REQUEST['name']),
            $this->BBDD->scapeCharts($_REQUEST['description']),
            $this->BBDD->scapeCharts($_REQUEST['tag']),
            $this->BBDD->scapeCharts($_REQUEST['meta_title']),
            $this->BBDD->scapeCharts($_REQUEST['meta_description']),
            $this->BBDD->scapeCharts($_REQUEST['meta_keyword']),
        ), $objectProductDescription);
        return json_encode($_POST);
        } catch (PDOException $ex) {
            return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
        }                   
    }
    // Descuentos de un producto asociado
    public function insertDiscount() {
        $sql = '?,?,?,?,?,?,?,?';
        $fields = 'product_discount_id,product_id,
            customer_group_id, quantity, priority,
            price, date_start, date_end
            ';
        $objectProductDiscount = $this->BBDD->insertDriver($sql,PREFIX.'product_discount', $this->driver, $fields);
        $this->BBDD->runDriver(array(
            $this->BBDD->scapeCharts($_REQUEST['product_discount_id']),
            $this->BBDD->scapeCharts($_REQUEST['product_id']),
            $this->BBDD->scapeCharts($_REQUEST['customer_group_id']),
            $this->BBDD->scapeCharts($_REQUEST['quantity']),
            $this->BBDD->scapeCharts($_REQUEST['priority']),
            $this->BBDD->scapeCharts($_REQUEST['price']),
            $this->BBDD->scapeCharts($_REQUEST['date_start']),
            $this->BBDD->scapeCharts($_REQUEST['date_end'])
        ), $objectProductDiscount);
            $success = array();
            $success['status'] = true;
            $success['message'] = 'Oferta agregada con éxito';
            $success['data'] = $_POST;
            return json_encode($success);
    }
    // Insertar filtros de busqueda a un producto
    public function insertProductFilter() {
        try {
            $sql = '?,?';
            $fields = 'product_id, filter_id';
            $ObjectProductFilter = $this->BBDD->insertDriver($sql,PREFIX.'product_filter', $this->driver, $fields);
            $this->BBDD->runDriver(array(
               $this->BBDD->scapeCharts($_POST['product_id']),
               $this->BBDD->scapeCharts($_POST['filter_id']),
            ), $ObjectProductFilter);
            return json_encode(print_r($_POST));
        } catch (Exception $ex) {
            return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
        }
    }
    // Añadir un descuento a un unico producto solicitado
    public function insertSpecial() {
        try {
            $sql = '?,?,?,?,?,?';
            $fields = 'product_id,customer_group_id,
                       priority,price,date_start,date_end
                       ';
            $objectProductSpecial = $this->BBDD->insertDriver($sql,PREFIX.'product_special', $this->driver, $fields);
            $this->BBDD->runDriver(array(
                $this->BBDD->scapeCharts($_POST['product_id']),
                $this->BBDD->scapeCharts($_POST['customer_group_id']),
                $this->BBDD->scapeCharts($_POST['priority']),
                $this->BBDD->scapeCharts($_POST['price']),
                $this->BBDD->scapeCharts($_POST['date_start']),
                $this->BBDD->scapeCharts($_POST['date_end']),
            ), $objectProductSpecial);
            $success = array();
            $success['status'] = true;
            $success['message'] = 'Oferta agregada con éxito';
            $success['data'] = $_POST;
            return json_encode($success);
        } catch (PDOException $ex) {
            return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
        }
    }
    // Insertar las categorías de los productos
    public function insertCategory() {
        try{
            $sql = '?,?';
            $fields = 'product_id, category_id';
            $objectProductCategory = $this->BBDD->insertDriver($sql, PREFIX.'product_to_category', $this->driver, $fields);
            $this->BBDD->runDriver(array(
                $this->BBDD->scapeCharts($_POST['product_id']),
                $this->BBDD->scapeCharts($_POST['category_id'])
            ), $objectProductCategory);
            return json_encode(print_r($_POST));
        } catch (PDOException $ex) {
            return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
        }
    }
     /**************************************************************
     *  END INSERT MODE
     *************************************************************/
     /**************************************************************
     *  UPDATE MODE
     *************************************************************/
        public function updateProduct() {
         $fileImage = new validacionImagen();
         if($fileImage->_getValidate_($_FILES['image'])) {
             // Con imagen
             $file = $this->loadImage($_FILES['image']);
             if ($file!=null) {
         try {
             $fields = 'model = ?, sku = ?, upc=?,
                        ean=?,jan=?,isbn=?,mpn=?,
                        location=?,quantity=?,
                        stock_status_id=?,image=?,
                        manufacturer_id=?,shipping=?,
                        price=?,points=?,tax_class_id=?,
                        date_available=?,weight=?,
                        weight_class_id=?,length=?,
                        width=?,height=?,length_class_id=?,
                        subtract=?,minimum=?,sort_order=?,
                        status=?,date_added=?,date_modified=?';
             $objectProductUpdate = $this->BBDD->updateDriver('user_id = ? && product_id = ?',PREFIX.'product', $this->driver, $fields);
             $this->BBDD->runDriver(array(
                 $this->BBDD->scapeCharts($_POST['model']),
                 $this->BBDD->scapeCharts($_POST['sku']),
                 $this->BBDD->scapeCharts($_POST['upc']),
                 $this->BBDD->scapeCharts($_POST['ean']),
                 $this->BBDD->scapeCharts($_POST['jan']),
                 $this->BBDD->scapeCharts($_POST['isbn']),
                 $this->BBDD->scapeCharts($_POST['mpn']),
                 $this->BBDD->scapeCharts($_POST['location']),
                 $this->BBDD->scapeCharts($_POST['quantity']),
                 $this->BBDD->scapeCharts($_POST['stock_status_id']),
                 $this->BBDD->scapeCharts(str_replace('../../image/', '', $file)),
                 $this->BBDD->scapeCharts($_POST['manufacturer_id']),
                 $this->BBDD->scapeCharts($_POST['shipping']),
                 $this->BBDD->scapeCharts($_POST['price']),
                 $this->BBDD->scapeCharts($_POST['points']),
                 $this->BBDD->scapeCharts($_POST['tax_class_id']),
                 $this->BBDD->scapeCharts($_POST['date_available']),
                 $this->BBDD->scapeCharts($_POST['weight']),
                 $this->BBDD->scapeCharts($_POST['weight_class_id']),
                 $this->BBDD->scapeCharts($_POST['length']),
                 $this->BBDD->scapeCharts($_POST['width']),
                 $this->BBDD->scapeCharts($_POST['height']),
                 $this->BBDD->scapeCharts($_POST['length_class_id']),
                 $this->BBDD->scapeCharts($_POST['subtract']),
                 $this->BBDD->scapeCharts($_POST['minimum']),
                 $this->BBDD->scapeCharts($_POST['sort_order']),
                 $this->BBDD->scapeCharts($_POST['status']),
                 $this->BBDD->scapeCharts($_POST['date_added']),
                 $this->BBDD->scapeCharts($_POST['date_modified']),
                 $this->BBDD->scapeCharts($_POST['user_id']),
                 $this->BBDD->scapeCharts($_POST['product_id'])                
             ), $objectProductUpdate);
                $success = array();
                $success['status'] = true;
                $success['response'] = '200';
                $success['data'] = $_POST;
                return json_encode($success);
        } catch (PDOException $ex) {
            return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
          } 
        } else {
            $err = array();
            $err['status'] = false;
            $err['response'] = '400';
            $err['message'] = 'El archivo es inválido';
            return json_encode($err);
        }
      } else {
          // Sin imagen
                   try {
             $fields = 'model = ?, sku = ?, upc=?,
                        ean=?,jan=?,isbn=?,mpn=?,
                        location=?,quantity=?,
                        stock_status_id=?,
                        manufacturer_id=?,shipping=?,
                        price=?,points=?,tax_class_id=?,
                        date_available=?,weight=?,
                        weight_class_id=?,length=?,
                        width=?,height=?,length_class_id=?,
                        subtract=?,minimum=?,sort_order=?,
                        status=?,date_added=?,date_modified=?';
             $objectProductUpdate = $this->BBDD->updateDriver('user_id = ? && product_id = ?',PREFIX.'product', $this->driver, $fields);
             $this->BBDD->runDriver(array(
                 $this->BBDD->scapeCharts($_POST['model']),
                 $this->BBDD->scapeCharts($_POST['sku']),
                 $this->BBDD->scapeCharts($_POST['upc']),
                 $this->BBDD->scapeCharts($_POST['ean']),
                 $this->BBDD->scapeCharts($_POST['jan']),
                 $this->BBDD->scapeCharts($_POST['isbn']),
                 $this->BBDD->scapeCharts($_POST['mpn']),
                 $this->BBDD->scapeCharts($_POST['location']),
                 $this->BBDD->scapeCharts($_POST['quantity']),
                 $this->BBDD->scapeCharts($_POST['stock_status_id']),
                 $this->BBDD->scapeCharts($_POST['manufacturer_id']),
                 $this->BBDD->scapeCharts($_POST['shipping']),
                 $this->BBDD->scapeCharts($_POST['price']),
                 $this->BBDD->scapeCharts($_POST['points']),
                 $this->BBDD->scapeCharts($_POST['tax_class_id']),
                 $this->BBDD->scapeCharts($_POST['date_available']),
                 $this->BBDD->scapeCharts($_POST['weight']),
                 $this->BBDD->scapeCharts($_POST['weight_class_id']),
                 $this->BBDD->scapeCharts($_POST['length']),
                 $this->BBDD->scapeCharts($_POST['width']),
                 $this->BBDD->scapeCharts($_POST['height']),
                 $this->BBDD->scapeCharts($_POST['length_class_id']),
                 $this->BBDD->scapeCharts($_POST['subtract']),
                 $this->BBDD->scapeCharts($_POST['minimum']),
                 $this->BBDD->scapeCharts($_POST['sort_order']),
                 $this->BBDD->scapeCharts($_POST['status']),
                 $this->BBDD->scapeCharts($_POST['date_added']),
                 $this->BBDD->scapeCharts($_POST['date_modified']),
                 $this->BBDD->scapeCharts($_POST['user_id']),
                 $this->BBDD->scapeCharts($_POST['product_id'])                
             ), $objectProductUpdate);
                    $success = array();
                    $success['status'] = true;
                    $success['response'] = '200';
                    $success['data'] = $_POST;
                    return json_encode($success);
        } catch (PDOException $ex) {
            return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
          }
      }
    }
    public function updateProductDescription() {
        $fields = 'language_id = ?, name = ?,
                   description = ?, tag = ?,               
                   meta_description=?,
                   meta_keyword=?
                   ';
        $objectProductDescription = $this->BBDD->updateDriver('user_id = ? && product_id = ?',PREFIX.'product_description', $this->driver, $fields);
        $this->BBDD->runDriver(array(
            $this->BBDD->scapeCharts($_POST['language_id']),
            $this->BBDD->scapeCharts($_POST['name']),
            $this->BBDD->scapeCharts($_POST['description']),
            $this->BBDD->scapeCharts($_POST['tag']),          
            $this->BBDD->scapeCharts($_POST['meta_description']),
            $this->BBDD->scapeCharts($_POST['meta_keyword']),
            $this->BBDD->scapeCharts($_POST['user_id']),
            $this->BBDD->scapeCharts($_POST['product_id'])
        ), $objectProductDescription);
        $success = array();
        $success['status'] = true;
        $success['response'] = '200';
        $success['data'] = $_POST;
        return json_encode($success);
    }
    public function updateDiscount() {
            
        if (isset($_POST['quantity']) && (!empty($_POST['quantity']))
           && isset($_POST['price']) && (!empty($_POST['price']))
           && isset($_POST['date_start']) && (!empty($_POST['date_start']))
           && isset($_POST['date_end']) && (!empty($_POST['date_end']))
           ){
            $fields = 'customer_group_id = ?,quantity = ?,priority = ?,
               price = ?,date_start = ?,date_end = ?';
       $objectProductDiscount = $this->BBDD->updateDriver('product_discount_id = ?',PREFIX.'product_discount', $this->driver, $fields);
       $this->BBDD->runDriver(array(
           $this->BBDD->scapeCharts($_POST['customer_group_id']),
           $this->BBDD->scapeCharts($_POST['quantity']),
           $this->BBDD->scapeCharts($_POST['priority']),
           $this->BBDD->scapeCharts($_POST['price']),
           $this->BBDD->scapeCharts($_POST['date_start']),
           $this->BBDD->scapeCharts($_POST['date_end']),
           $this->BBDD->scapeCharts($_POST['product_discount_id'])
       ), $objectProductDiscount);
           $success = array();
           $success['status'] = true;
           $success['response'] = '400';
           $success['message'] = 'Actualizado con éxito';
           $success['data'] = $_POST;
           return json_encode($success);           
           } else {
               $err = array();
               $err['status'] = false;
               $err['response'] = '400';
               $err['message'] = 'Completa todos los campos';
               return json_encode($err);
           }

    }
    public function updateCategory() {
        $fields = 'category_id = ?';
        $objectProductFilter = $this->BBDD->updateDriver('category_id = ? && product_id = ?',PREFIX.'product_to_category', $this->driver, $fields);
        $this->BBDD->runDriver(array(
            $this->BBDD->scapeCharts($_POST['category_id']),
            $this->BBDD->scapeCharts($_POST['category_id']),
            $this->BBDD->scapeCharts($_POST['product_id'])
        ), $objectProductFilter);
                   $success = array();
           $success['status'] = true;
           $success['response'] = '400';
           $success['message'] = 'Actualizado con éxito';
           $success['data'] = $_POST;
           return json_encode($success);   
    }
        public function deleteCategory() {
        $fields = 'category_id = ?';
        $objectProductFilter = $this->BBDD->deleteDriver('category_id = ? && product_id = ?',PREFIX.'product_to_category', $this->driver);
        $this->BBDD->runDriver(array(
            $this->BBDD->scapeCharts($_POST['category_id']),
            $this->BBDD->scapeCharts($_POST['product_id'])
        ), $objectProductFilter);
                   $success = array();
           $success['status'] = true;
           $success['response'] = '400';
           $success['message'] = 'Actualizado con éxito';
           $success['data'] = $_POST;
           return json_encode($success);   
    }
        public function updateFilter() {
        $fields = 'filter_id = ?';
        $objectProductFilter = $this->BBDD->updateDriver('product_id = ?',PREFIX.'product_filter', $this->driver, $fields);
        $this->BBDD->runDriver(array(
            $this->BBDD->scapeCharts($_POST['filter_id']),
            $this->BBDD->scapeCharts($_POST['product_id']),
            //$this->BBDD->scapeCharts($_POST['filter_id'])    
        ), $objectProductFilter);
                   $success = array();
           $success['status'] = true;
           $success['response'] = '400';
           $success['message'] = 'Actualizado con éxito';
           $success['data'] = $_POST;
           return json_encode($success);   
    }
    public function updateImages() {
        $fileImage = new validacionImagen();
        if($fileImage->_getValidate_($_FILES['image'])) {
            // Movemos la imagen a la carpeta de forma temporal
            $file = $this->loadImage($_FILES['image']);
            if ($file != null) {
                // Borramos la imagen anterior por rendimiento
                $old_path = $this->getPathOld($_POST['product_image_id'], 'product_image_id = ?', 'product_image');
                if($old_path != null) {
                     // $delete = unlink("../../image/{$old_path}"); // Borramos la antigua imagen
                         // if($delete) {
                            // Actualizamos por la nueva
                          $fields = 'image = ?';
                          $objectProductImages = $this->BBDD->updateDriver('product_image_id = ?',PREFIX.'product_image', $this->driver, $fields);
                          $this->BBDD->runDriver(array(
                              $this->BBDD->scapeCharts(str_replace('../../image/', '', $file)),
                              $this->BBDD->scapeCharts($_POST['product_image_id']),
                          ), $objectProductImages);
                                $success = array();
                                $success['status'] = true;
                                $success['message'] = 'Imagen insertada con éxito';
                                $success['path'] = $_FILES['image']['name'];
                                $success['data'] = $_POST;
                                return json_encode($success);
                    // }
                } else {
                    return json_encode('No se ha encontrado la imagen');
                }
            } else {
                return json_encode('Formato o tamaño inválido');
            }
        } else {
            return json_encode('Debes cargar una imagen');
        }
    }
        public function updateImage() {
        $fileImage = new validacionImagen();
        if($fileImage->_getValidate_($_FILES['image'])) {
            // Movemos la imagen a la carpeta de forma temporal
            $file = $this->loadImage($_FILES['image']);
            if ($file != null) {
                /*
                 * Verifica si tiene imagen, si tiene pasa al siguiente bloque
                 * sino, actualiza una nueva
                 */
                $productPresentation = $this->getImageData($_POST['product_id']);
                if(!$productPresentation || empty($productPresentation)) {
                          $fields = 'image = ?';
                          
                          $objectProductImages = $this->BBDD->updateDriver('product_id = ?',PREFIX.'product', $this->driver, $fields);
                          $this->BBDD->runDriver(array(
                              $this->BBDD->scapeCharts(str_replace('../../image/', '', $file)),
                              $this->BBDD->scapeCharts($_POST['product_id']),
                          ), $objectProductImages);
                          return json_encode('Imagen actualizada con éxitoo');                  
                } else {
                // Borramos la imagen anterior por rendimiento
                $old_path = $this->getPathOld($_POST['product_id'], 'product_id = ?', 'product');
                if($old_path != null || (!empty($old_path))) {
                   $delete = unlink("../../image/{$old_path}"); // Borramos la antigua imagen
                        if($delete) {
                            // Actualizamos por la nueva
                          $fields = 'image = ?';
                          $objectProductImages = $this->BBDD->updateDriver('product_id = ?',PREFIX.'product_image', $this->driver, $fields);
                          $this->BBDD->runDriver(array(
                              $this->BBDD->scapeCharts(str_replace('../../image/', '', $file)),
                              $this->BBDD->scapeCharts($_POST['product_image_id']),
                          ), $objectProductImages);
                          return json_encode('Imagen actualizada con éxito');
                     }
                } else {
                    return json_encode('No se ha encontrado la imagen');
                }
              }
            } else {
                return json_encode('Formato o tamaño inválido');
            }
        } else {
            return json_encode('Debes cargar una imagen');
        }
    }
    public function updateSpecial() {
            if ( isset($_POST['price']) && (!empty($_POST['price']))
           && isset($_POST['date_start']) && (!empty($_POST['date_start']))
           && isset($_POST['date_end']) && (!empty($_POST['date_end']))
           ){
               try {
            $fields = 'customer_group_id = ?,priority=?,
                price = ?, date_start = ?, date_end = ?';
            $obbjectProductSpecial = $this->BBDD->updateDriver('product_special_id = ?',PREFIX.'product_special', $this->driver, $fields);
            $this->BBDD->runDriver(array(
                $this->BBDD->scapeCharts($_POST['customer_group_id']),
                $this->BBDD->scapeCharts($_POST['priority']),
                $this->BBDD->scapeCharts($_POST['price']),
                $this->BBDD->scapeCharts($_POST['date_start']),
                $this->BBDD->scapeCharts($_POST['date_end']),
                $this->BBDD->scapeCharts($_POST['product_special_id'])
            ), $obbjectProductSpecial);
                       $success = array();
           $success['status'] = true;
           $success['response'] = '400';
           $success['message'] = 'Actualizado con éxito';
           $success['data'] = $_POST;
           return json_encode($success);           
        } catch (PDOException $ex) {
            return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
        }
      } else {
               $err = array();
               $err['status'] = false;
               $err['response'] = '400';
               $err['message'] = 'Completa todos los campos';
               return json_encode($err);
      }
    }
    public function updateProductCategory() {
        $fields = 'category_id = ?';
        $objectProductCategory = $this->BBDD->updateDriver('product_id = ?', PREFIX.'product_to_category', $this->driver, $fields);
        $this->BBDD->runDriver(array(
            $this->BBDD->scapeCharts($_POST['category_id']),
            $this->BBDD->scapeCharts($_POST['product_id'])
        ), $objectProductCategory);
        return json_encode('categoria actualizada');
    }
    /**************************************************************
     * END  UPDATE MODE
     *************************************************************/
     /**************************************************************
     * DELETE MODE
     *************************************************************/
    public function deleteProduct() {
        /*
         * Cómo es un producto general (Universal) Borramos toda la data que trae con el
         */
         $description = $this->deleteDescription($_POST['product_id']);
         $images = $this->deleteImages($_POST['product_id']);
         $filters = $this->deleteProductFilters($_POST['product_id']);
         $discounts = $this->deleteProductDiscounts($_POST['product_id']);
         $specials = $this->deleteProductoSpecial($_POST['product_id']);
         if($description && $images && $filters && $discounts && $specials) {
             // Entonces borramos el articulo
             try {
                 $objectProductDelete = $this->BBDD->deleteDriver('product_id = ?', PREFIX.'product', $this->driver);
                 $this->BBDD->runDriver(array(
                     $this->BBDD->scapeCharts($_POST['product_id'])
                 ), $objectProductDelete);
                 $response = array();
                 $response['status'] = true;
                 $response['message'] = 'Eliminado con éxito';
                 return json_encode($response);
             } catch (PDOException $ex) {
                 return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
             }
         }
    }
    public function deleteDescription($product_id) {
     try {
         $objectProductDelete = $this->BBDD->deleteDriver('product_id = ?', PREFIX.'product_description', $this->driver);
         $this->BBDD->runDriver(array(
             $this->BBDD->scapeCharts($product_id)
         ), $objectProductDelete);
         $sucess = array();
         $sucess['status'] = true;
         $success['message'] = 'Eliminado con éxito';
         return json_encode($success);
     } catch (PDOException $ex) {
         return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
     }        
    }
    public function deleteImages($product_id) {
             try {
         $objectProductDelete = $this->BBDD->deleteDriver('product_id = ?', PREFIX.'product_images', $this->driver);
         $this->BBDD->runDriver(array(
             $this->BBDD->scapeCharts($product_id)
         ), $objectProductDelete);
         $sucess = array();
         $sucess['status'] = true;
         $success['message'] = 'Eliminado con éxito';
         return json_encode($success);
     } catch (PDOException $ex) {
         return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
     }  
    }
    public function deleteProductFilters($product_id) {
      try {
         $objectProductDelete = $this->BBDD->deleteDriver('product_id = ?', PREFIX.'product_filter', $this->driver);
         $this->BBDD->runDriver(array(
             $this->BBDD->scapeCharts($product_id)
         ), $objectProductDelete);
         $sucess = array();
         $sucess['status'] = true;
         $success['message'] = 'Eliminado con éxito';
         return json_encode($success);
     } catch (PDOException $ex) {
         return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
     }         
    }
        public function deleteFilter() {
        $objectProductFilter = $this->BBDD->deleteDriver('filter_id = ? && product_id = ?',PREFIX.'product_filter', $this->driver);
        $this->BBDD->runDriver(array(
            $this->BBDD->scapeCharts($_POST['filter_id']),
            $this->BBDD->scapeCharts($_POST['product_id'])
        ), $objectProductFilter);
                   $success = array();
           $success['status'] = true;
           $success['response'] = '400';
           $success['message'] = 'Actualizado con éxito';
           $success['data'] = $_POST;
           return json_encode($success);   
    }
    public function deleteProductDiscounts($product_id) {
     try {
         $objectProductDelete = $this->BBDD->deleteDriver('product_id = ?', PREFIX.'product_discount', $this->driver);
         $this->BBDD->runDriver(array(
             $this->BBDD->scapeCharts($product_id)
         ), $objectProductDelete);
         $sucess = array();
         $sucess['status'] = true;
         $success['message'] = 'Eliminado con éxito';
         return json_encode($success);
     } catch (PDOException $ex) {
         return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
     }          
    }
    public function deleteProductoSpecial($product_id) {
     try {
         $objectProductDelete = $this->BBDD->deleteDriver('product_id = ?', PREFIX.'product_special', $this->driver);
         $this->BBDD->runDriver(array(
             $this->BBDD->scapeCharts($product_id)
         ), $objectProductDelete);
         $sucess = array();
         $sucess['status'] = true;
         $success['message'] = 'Eliminado con éxito';
         return json_encode($success);
     } catch (PDOException $ex) {
         return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
     }          
    }
    public function deleteSpecial() {
             try {
         $objectProductDelete = $this->BBDD->deleteDriver('product_special_id = ?', PREFIX.'product_special', $this->driver);
         $this->BBDD->runDriver(array(
             $this->BBDD->scapeCharts($_POST['product_special_id'])
         ), $objectProductDelete);
         $sucess = array();
         $sucess['status'] = true;
         $success['message'] = 'Eliminado con éxito';
         return json_encode($success);
     } catch (PDOException $ex) {
         return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
     } 
    }
    public function deleteDiscount() {
        try {
         $objectProductDelete = $this->BBDD->deleteDriver('product_discount_id = ?', PREFIX.'product_discount', $this->driver);
         $this->BBDD->runDriver(array(
             $this->BBDD->scapeCharts($_POST['product_discount_id'])
         ), $objectProductDelete);
         $sucess = array();
         $sucess['status'] = true;
         $success['message'] = 'Eliminado con éxito';
         return json_encode($success);
     } catch (PDOException $ex) {
         return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
     } 
    }
        public function deleteImageProduct() {
        try {
         $objectProductDelete = $this->BBDD->deleteDriver('product_image_id = ?', PREFIX.'product_image', $this->driver);
         $this->BBDD->runDriver(array(
             $this->BBDD->scapeCharts($_POST['product_image_id'])
         ), $objectProductDelete);
         $sucess = array();
         $sucess['status'] = true;
         $success['message'] = 'Eliminado con éxito';
         $success['data'] = $_POST;
         return json_encode($success);
     } catch (PDOException $ex) {
         return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
     } 
    }
     /**************************************************************
     * END DELETE MODE
     *************************************************************/
    public function getProductById($product_id,$user_id) {
        try {
            
        } catch (PDOException $ex) {
            return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
        }        
    }
    // Image function
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
                $FOLDER = "../../image/catalog/ropa/"; // path
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
                    return $src->image;
                }
            } else {
                return null;
            }
        } catch (PDOException $ex) {
            return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
        }
    }
    public function getImageData($product_id) {
        try {
            $productObjectImage = $this->BBDD->selectDriver('product_id = ?',PREFIX.'product', $this->driver);
            $this->BBDD->runDriver(array(
                $this->BBDD->scapeCharts($product_id)
            ), $productObjectImage);
            if($this->BBDD->verifyDriver($productObjectImage)) {
                foreach($this->BBDD->fetchDriver($productObjectImage) as $src) {
                    return $src->image;
                }
            } else {
                return false;
            }
          } catch (Exception $ex) {
            return json_encode('Fallo en la conexión con la base de datos' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
        }
    }
     public function countItemsPosted($emp_id) {
         try {
             $count = $this->BBDD->countDriver('user_id = ?',PREFIX.'product', $this->driver);
             $this->BBDD->runDriver(array(
                 $this->BBDD->scapeCharts($emp_id)
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
          public function countSell($emp_id) {
         try {
             $count = $this->BBDD->countDriver('user_id = ?',PREFIX.'order', $this->driver);
             $this->BBDD->runDriver(array(
                 $this->BBDD->scapeCharts($emp_id)
             ), $count);
             if ($this->BBDD->verifyDriver($count)) {
                 foreach ($this->BBDD->fetchDriver($count) as $qty) {
                     $success = array();
                     $success['status'] = true;
                     $success['message'] = 'ventas';
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
     
    /*}
     * Para buscar el ultimo producto publicado con esa referencia
     * IDEAL para hacer busqueda sin el ID UNICO
     */
    private function getProductByRef($usr_id, $model, $date) {
        try {
            $objectRef = $this->BBDD->selectDriver('user_id = ? && model = ? && date_added = ?',PREFIX.'product', $this->driver);
            $this->BBDD->runDriver(array(
                $this->BBDD->scapeCharts($usr_id),
                $this->BBDD->scapeCharts($model),
                $this->BBDD->scapeCharts("$date 00:00:00")
            ), $objectRef);
            if($this->BBDD->verifyDriver($objectRef)) {
                foreach($this->BBDD->fetchDriver($objectRef) as $id) {
                    return $id->product_id;
                }
            } else {
                return null;
            }
        } catch (PDOException $ex) {
            $error = array();
            $error['status'] = false;
            $error['message'] = 'Fallo al obtener la ID de este producto';
            return json_encode($error);
        }
    }
    protected $BBDD;
    protected $driver;
}

