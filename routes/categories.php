<?php
require_once '../models/config.php';
require_once '../models/connection.php';
/*
 * Clase para retornar las categorías
 */
if($_GET) {
    $categories = new categories();
    switch($_GET['operationType']) {
        case 'parentMenu':
            echo $categories->getParentMenu();
            break;
        case 'parentMenuDescription':
            echo $categories->getParentMenuDescription();
            break;
        case 'subMenu':
            echo $categories->OpenCartSubMenu($_GET['parent']);
            break;
    }
}
class categories {
    public function __construct() {
        $this->BBDD = new \dbDriver;
        $this->driver = $this->BBDD->setPDO();
        $this->BBDD->setPDOConfig($this->driver);
    }
    public function getParentMenu() {
        $objectMenu = $this->BBDD->selectDriver('parent_id = ?',PREFIX.'category', $this->driver);
        $this->BBDD->runDriver(array(
            $this->BBDD->scapeCharts(0)
        ), $objectMenu);
        echo json_encode($this->BBDD->fetchDriver($objectMenu));
    }
        public function getParentMenuDescription() {
        $objectMenu = $this->BBDD->selectDriver('category_id = ?',PREFIX.'category_description', $this->driver);
        $this->BBDD->runDriver(array(
            $this->BBDD->scapeCharts($_GET['category_id'])
        ), $objectMenu);
        echo json_encode($this->BBDD->fetchDriver($objectMenu));
    }
        public function OpenCartSubMenu($parent)
    {
        $submenu = $this->BBDD->selectDriver("parent_id=?",PREFIX."category",$this->driver);
        $this->BBDD->runDriver(array(htmlentities(addslashes($parent))),$submenu);
        if($this->BBDD->verifyDriver($submenu))
        {
            return json_encode($this->BBDD->fetchDriver($submenu));
        }else{
            return false;
        }
    }
    protected $BBDD;
    protected $driver;
}
