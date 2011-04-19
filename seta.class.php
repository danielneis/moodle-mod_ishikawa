<?php
class Seta {

    var $xi, $xf, $yi, $yf;
    var $draw;

    function __construct($xi, $xf, $yi, $yf, $draw, $color) {
        $this->xi = $xi;
        $this->xf = $xf;
        $this->yi = $yi;
        $this->yf = $yf;
        $this->color = $color;
        $this->draw = $draw;
    }

    function draw() {
        $this->draw->setStrokeColor($this->color);
        $this->draw->setFillColor($this->color);

        $this->draw->line($this->xi, $this->yi, $this->xf, $this->yf);

        $this->draw->ellipse($this->xf, $this->yf, 6, 6, 0, 360);
    }
}
?>
