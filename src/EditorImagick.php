<?php

namespace GM;

/**
 * Extends WP_Image_Editor_Imagick because core dosn't allow to access underlying Imagick instance
 */
class EditorImagick extends \WP_Image_Editor_Imagick implements EditorInterface {

    function getImage() {
        return $this->image;
    }

    function setImage( $image ) {
        $this->image = $image;
    }

    function reload( $code, $new ) {
        $this->image->setImageFormat( 'JPG' );
        $base = base64_encode( $this->image->getImageBlob() );
        $json = ['code' => $code, 'new' => $new, 'datauri' => $base ];
        wp_send_json( $json );
    }

}