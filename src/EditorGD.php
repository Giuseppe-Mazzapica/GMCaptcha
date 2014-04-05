<?php

namespace GM;

/**
 * Extends WP_Image_Editor_GD because core dosn't allow to access underlying GD resource
 */
class EditorGD extends \WP_Image_Editor_GD implements EditorInterface {

    function getImage() {
        return $this->image;
    }

    function setImage( $image ) {
        $this->image = $image;
    }

    function reload( $code, $new ) {
        ob_start();
        imagejpeg( $this->image );
        $base = base64_encode( ob_get_clean() );
        $json = ['code' => $code, 'new' => $new, 'datauri' => $base ];
        wp_send_json( $json );
    }

}