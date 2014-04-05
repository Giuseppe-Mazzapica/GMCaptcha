<?php

namespace GM;

interface ImageInterface {

    function setCode( $code );

    function setColor();

    function addNoise();

    function addText();

    function getImage();
}