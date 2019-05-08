<?php
/*
 * Retorna todos los manufacturer de la tienda
 */
require_once '../models/config.php';
require_once '../models/connection.php';
if($_GET) {
    $manuList = new filters();
    // echo $manuList->getFilterGroup();
    switch($_GET['operationType']) {
        case 'selectFilters':
            echo $manuList->getFilterGroup();
            break;
        case 'selectFiltersByGroup':
            echo $manuList->getFiltersByGroup($_GET['filter_group']);
            break;
    }
}
class filters {
      public function __construct() {
        $this->BBDD = new \dbDriver;
        $this->driver = $this->BBDD->setPDO();
        $this->BBDD->setPDOConfig($this->driver);
    }
  public function getFilterGroup() {
      $filterGroup = $this->BBDD->selectDriver(null, PREFIX.'filter_group', $this->driver);
      $this->BBDD->runDriver(null, $filterGroup);
      if($this->BBDD->verifyDriver($filterGroup)) {
             $filterDescription = $this->BBDD->selectDriver(null, PREFIX.'filter_group_description', $this->driver);
             $this->BBDD->runDriver(null, $filterDescription);
             return json_encode($this->BBDD->fetchDriver($filterDescription));
          
      } else {
          return null;
      }
  }
  public function getFiltersByGroup($filter_group_id) {
      echo "done";
      $filters = $this->BBDD->selectDriver('filter_group_id = ?',PREFIX.'filter_description', $this->driver);
      $this->BBDD->runDriver(array(
          $this->BBDD->scapeCharts($filter_group_id)
      ), $filters);
      return json_encode($this->BBDD->fetchDriver($filters));
  }
    protected $BBDD;
    protected $driver;
}
