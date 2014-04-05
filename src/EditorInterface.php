<?php

namespace GM;

interface EditorInterface {

    function getImage();

    function setImage( $image );

    function reload( $code, $new );
}