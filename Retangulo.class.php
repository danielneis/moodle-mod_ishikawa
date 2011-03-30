<?php

class Retangulo {

    var $upper_x, $upper_y, $bottom_x, $bottom_y;

    var $largura = 150;
    var $altura = 50;

    function __construct($x, $y) {
        $this->upper_x = $x;
        $this->upper_y = $y;
        $this->bottom_x = $x + $this->largura + rand(0,50);
        $this->bottom_y = $y + $this->altura + rand(0,50);
    }

    function pontoMedioTopo() {
        return array(($this->bottom_x + $this->upper_x) / 2, $this->upper_y);
    }

    function pontoMedioBase() {
        return array(($this->bottom_x + $this->upper_x) / 2, $this->bottom_y);
    }

    function pontoMedioLateralDireita() {
        return array($this->bottom_x, ($this->upper_y + $this->bottom_y)/2);
    }

    function pontoMedioLateralEsquerda() {
        return array($this->upper_x, ($this->upper_y + $this->bottom_y)/2);
    }
}

?>
