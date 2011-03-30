<?php

class Retangulo {

    var $upper_x, $upper_y, $bottom_x, $bottom_y;

    var $largura = 150;
    var $altura;

    var $text_lines;

    function __construct($x, $y, $text) {

        $this->text_lines = $this->str_in_lines($text, $this->largura);

        $im = new Imagick;
        $draw = new ImagickDraw;
        $metrics =  $im->queryfontmetrics($draw, $text);

        $this->altura = count($this->text_lines) * $metrics['textHeight'];
        $this->altura += 5; // total espacamento entre linhas

        $this->upper_x = $x;
        $this->upper_y = $y;
        $this->bottom_x = $x + $this->largura;
        $this->bottom_y = $y + $this->altura;
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

    function str_in_lines($text, $max_width) {

        $im = new Imagick();
        $draw = new ImagickDraw();

        $words = explode(" ", $text);
        $lines = array();
        $i=0;
        while ($i < count($words)) {//as long as there are words

            $line = "";
            do { //append words to line until the fit in size

                if ($line != "") {
                    $line .= " ";
                }
                $line .= $words[$i];

                $i++;

                if (($i) == count($words)) {
                    break; //last word -> break
                }

                //messure size of line + next word
                $linePreview = $line." ".$words[$i];
                $metrics = $im->queryFontMetrics($draw, $linePreview);
            } while($metrics["textWidth"] <= $max_width);

            $lines[] = $line;
        }
        return $lines;
    }
}

?>
