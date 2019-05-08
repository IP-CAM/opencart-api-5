<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type");
header("Access-Control-Allow-Methods", "POST, GET, PUT, DELETE, OPTIONS");



require_once '../models/config.php';
require_once '../models/connection.php';
// Clase que hace el buscador inteligente de la tienda
// Carlos Estarita
if ($_GET) {
    $explorer = new explorer();
    switch($_GET['operationType']) {
        case 'intellisence': 
            echo $explorer->searchOnDb($_GET['search']);
            break;
    }
}
 class explorer {
    public function __construct() {
        $this->BBDD = new \dbDriver;
        $this->driver = $this->BBDD->setPDO();
        $this->BBDD->setPDOConfig($this->driver);
    }
    public function searchOnDb($item) {
            if ($item<>'') {
                $partialItem = explode(' ', $item);
                if (count($partialItem) == 1) {
                    // one letter search
                    $searchItems = $this->BBDD->multipleLeftBySearch(
                            'name LIKE ?',
                            PREFIX.'product_description',
                            $this->driver,
                            PREFIX.'product_to_category',
                            PREFIX.'category',
                            'product_id',
                            'product_id');
                    $this->BBDD->runDriver(array($this->BBDD->scapeCharts("%{$item}%")), $searchItems);
                    if ($this->BBDD->verifyDriver($searchItems)) {
                        $resp = array();
                        $resp['status'] = true;
                        $resp['statusCode'] = 200;
                        $resp['search'] = $this->BBDD->fetchDriver($searchItems);
                        return json_encode($resp);
                    }
                } else {
                    // Buscador inteligente
                    if (count($partialItem) > 1) {
                        $searchItems = $this->BBDD->Explorer(
                                (string)$item,
                                PREFIX.'product_description',
                                $this->driver,
                                PREFIX.'product_to_category',
                                PREFIX.'category',
                                (string)$item,
                                null,
                                'name');
                        $this->BBDD->runDriver(array($this->BBDD->scapeCharts($item)), $searchItems);
                        if ($this->BBDD->verifyDriver($searchItems)) {
                            $resp = array();
                            $resp['status'] = true;
                            $resp['statusCode'] = 200;
                            $resp['search'] = $this->BBDD->fetchDriver($searchItems);
                            return json_encode($resp);
                        }
                        /*
                         * SELECT DISTINCT *, MATCH (name) AGAINST ('Chaqueta') AS 
                         * items FROM rgza_product_description p LEFT JOIN 
                         * rgza_product_to_category pd ON (p.product_id = pd.product_id)
LEFT JOIN rgza_category pc ON (pc.category_id = pd.category_id)
WHERE MATCH (name) AGAINST ('Marca Anika') ORDER BY items DESC LIMIT 50

                         */
                    }
                } 
            }
    }
    protected $BBDD;
    protected $driver;
 }