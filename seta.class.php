<?php
class Seta {

    var $xi, $xf, $yi, $yf;
    var $draw;

    function __construct($xi, $xf, $yi, $yf, $draw) {
        $this->xi = $xi;
        $this->xf = $xf;
        $this->yi = $yi;
        $this->yf = $yf;
        $this->draw = $draw;
    }

    function draw() {
        $cores = array('red', 'black', 'green', 'yellow', 'orange', 'pink', 'white');

        $cor = $cores[rand(0,6)];
        $this->draw->setStrokeColor($cor);
        $this->draw->setFillColor($cor);

        $this->draw->line($this->xi, $this->yi, $this->xf, $this->yf);

        $this->draw->ellipse($this->xf, $this->yf, 6, 6, 0, 360);
    }
}
?>
