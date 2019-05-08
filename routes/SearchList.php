<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type");
header("Access-Control-Allow-Methods", "POST, GET, PUT, DELETE, OPTIONS");
require_once '../models/config.php';
require_once '../models/connection.php';

if ($_GET) {
    $tab = new tabList();
    switch($_GET['operationType']) {
        case 'tabList': 
            echo $tab->sortProductByTab($_GET['category_id'], $_GET['session']);
            break;
    }
}
class tabList {
    public function __construct() {
        $this->BBDD = new \dbDriver;
        $this->driver = $this->BBDD->setPDO();
        $this->BBDD->setPDOConfig($this->driver);
    }
    public function sortProductByTab($category_id, $session) {
        $product = $this->BBDD->multipleOptiosn('category_id = ?', 
                PREFIX.'product_to_category',
                $this->driver,
                PREFIX.'product',
                PREFIX.'product_description');
        $this->BBDD->runDriver(array($this->BBDD->scapeCharts($category_id)), $product);
        if ($this->BBDD->verifyDriver($product)) {
            foreach($this->BBDD->fetchDriver($product) as $item) {
                $price = money_format("%n", $item->price);
                $category = base64_encode($item->category_id);
                $product = base64_encode($item->product_id);
                $code = md5($session);
                $date = date("Y-m-d ", time());
                            echo "
                 <div class='col-xs-12 col-sm-4 col-md-4 col-xl-2'>
                    <div style='width: 18rem; border:0px' class='card'>
                        <a href='product.php?metakey={$product}&category={$category}'>
                         <img  class='card-img-top img-thumbnail img-responsive' src='./ragazza_backend/image/{$item->image}' alt='rgza_{$item->name}'>                           
                        </a>
                        <div class='card-body'>
                            <h3 class='card-title text-center h3'>
                                <a href='product.php?metakey={$product}&category={$category}' class='product_name'>
                                    {$item->name}
                                </a>
                            </h3>
                            <h5 class='card-subtitle text-danger text-center'>
                                $ {$price}
                            </h5>
                            <br>
                            <p class='card-text text-center'>
                                <button  data-button-action='add-to-cart' data-bind='cat_{$item->product_id}' style='padding:10px' class='btn btn-primary text-center add-to-cart'>
                                    Añadir al carrito
                                </button>
                                <form method='POST' id='cat_data{$item->product_id}'>
                                    <fieldset style='display:none'>
                                    <input  type='hidden' name='api_id' value='0'>
                                    <input  type='hidden' name='customer_id' value='{$session}'>
                                    <input  type='hidden' name='session_id' value='{$code}'>
                                    <input  type='hidden' name='product_id' value='{$item->product_id}'>
                                    <input type='hidden' name='price' value='{$item->price}'>
                                    <input  type='hidden' name='recurring_id' value='0'>   
                                    <input  type='hidden' name='option' value='[]'>  
                                    <input type='hidden' name='quantity' value='1'>      
                                    <input type='hidden' name='name' value='{$item->name}'>
                                    <input type='hidden' name='date' value='{$date}'>                            
                                    </fieldset>
                                      <script>
                                         $(document).ready(function()
                                         {
                                             //AJAX CART
                                            $('button[data-bind=cat_{$item->product_id}]').click(
                                                    function()
                                            {
                                             $.ajax({
                                                url:'app/controllers/cart.php',
                                                method:'POST', 
                                                dataType:'json',
                                                data:$('#cat_data{$item->product_id}').serialize(),
                                                cache:false,
                                                success:function(XMLHttpRequest)
                                                {
                                                    $('#cart_name').text(XMLHttpRequest.name);
                                                    $('#cart_title').text(XMLHttpRequest.name);
                                                    $('#cart_price').text(XMLHttpRequest.price);
                                                    $('#cart_image').attr('src','ragazza_backend/image/'+XMLHttpRequest.image).attr('alt',XMLHttpRequest.name);
                                                    $('#cart_price2').text(XMLHttpRequest.price);
                                                    $('#cart_shipping').text(XMLHttpRequest.tax);
                                                    $('#cart_total').text(XMLHttpRequest.total);
                                                    $('#trigger_modal').trigger('click');


                                                },
                                                error:function(XMLHttpRequest)
                                                {
                                                    alert('error');
                                                    console.log(XMLHttpRequest);
                                                }
                                             });
                                               return false;
                                            });
                                         });     
                                     </script>
                                </form>
                            </p>
                        </div>
                    </div>
                </div>
                 ";   
            }
        } else {
        }
    }
    /*
     * SELECT DISTINCT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . 
     * "product_description pd ON (p.product_id = pd.product_id) 
     * WHERE p.product_id = '" . (int)$product_id . "'
     */
    protected $driver;
    protected $BBDD;
}
