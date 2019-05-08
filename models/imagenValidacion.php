<?php
class validacionImagen{
    public function __construct() {
        
    }
    public static function __val__($IMG_TAMAÑO_,$IMG_EXTENSION_,$IMG_TMP_,$IMG_NAME_){
        
        
    }
    public function _setImageSize_($image_SIZE){
        $this->img_size = $image_SIZE;
    }
    public function _setImageExtension_($Image_EXT){
        $this->img_extension = $Image_EXT;
    }
    public function _setImageTMP_($IMAGE_TEMP){
        $this->img_tmp = $IMAGE_TEMP;
    }
    public function _setImageName_($IMAGE_NAME){
        $this->img_name = $IMAGE_NAME;
    }
    public function _getImageSize_(){
        return $this->img_size;
    }
    public function _getImageEXT_(){
        return $this->img_extension;
    }
    public function _getImageTMP_(){
        return $this->img_tmp;
    }
    public function _getImageName_(){
        return $this->img_name;
    }
    public function _getValidate_($IMAGE){
        if(isset($IMAGE) && (!empty($IMAGE))){
            return TRUE;
        }
    }
    protected $img_size,
              $img_extension,
              $img_tmp,
              $img_name
            ;
}
