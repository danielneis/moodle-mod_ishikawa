<?php
require_once('Retangulo.class.php');
require_once('seta.class.php');

class Ishikawa  {

    var $retangulos = array();

    var $setas = array();

    var $blocks;

    var $inicio_x = 10;
    var $inicio_y = 10;

    var $offset = 50;

    var $im;
    var $draw;

    function __construct($blocks, $connections) {

        $this->blocks = $blocks;
        $this->connections = $connections;

        $this->im = new Imagick();
        $this->draw = new ImagickDraw();    //Create a new drawing class (?)

        $this->im->newImage(700, 700, new ImagickPixel('lightgray'));

        $this->draw->setFillColor('white');    // Set up some colors to use for fill and outline:w
        $this->draw->setStrokeColor( new ImagickPixel('black'));
    }


    function draw() {
        $this->ponto_x_atual = $this->inicio_x;
        $this->ponto_y_atual = $this->inicio_y;

        $this->generate_tail();

        $this->generate_multinivel('causes');

        $this->generate_axis();

        $this->generate_multinivel('consequences');

        $this->generate_head();

        // Gera conexões
        /*
        foreach ($this->blocks as $nivel => $niveis) {
            foreach ($niveis as $nome_retangulo => $retangulo) {
                foreach ($retangulo['conections'] as $connection) {
                    $this->geraSeta($this->retangulos[$nivel][$nome_retangulo],
                                     $this->retangulos[$connection['nivel']][$connection['nome']]);
                }
            }
        }
        */

        $this->printme();
    }

    private function printme() {

        $this->retangulos['head']->draw();

        $parts = array('causes', 'axis', 'consequences');
        foreach ($parts as $p) {
            foreach($this->retangulos[$p] as $retangulo) {
                $retangulo->draw();
            }
        }

        $this->retangulos['tail']->draw();

        foreach ($this->setas as $seta) {
            $seta->draw();
        }

        $this->im->drawImage($this->draw);    // Apply the stuff from the draw class to the image canvas
        $this->im->setImageFormat('jpg');    // Give the image a format

        header('Content-type: image/jpeg');     // Prepare the web browser to display an image
        echo $this->im;                // Publish it to the world!
    }

    private function geraSeta($origem, $destino) {
        $funcoes = array('pontoMedioTopo', 'pontoMedioBase', 'pontoMedioLateralDireita', 'pontoMedioLateralEsquerda');
        $menor_comprimento_reta = 9999;
        foreach ($funcoes as $funcao1) {
            foreach ($funcoes as $funcao2) {
                list($xi_tmp, $yi_tmp) = call_user_func(array($origem, $funcao1));
                list($xf_tmp, $yf_tmp) = call_user_func(array($destino, $funcao2));
                $comprimento_reta_tmp = $this->comprimentoReta($xi_tmp, $xf_tmp, $yi_tmp, $yf_tmp);

                if ($comprimento_reta_tmp < $menor_comprimento_reta) {
                    $xi = $xi_tmp;
                    $xf = $xf_tmp;
                    $yi = $yi_tmp;
                    $yf = $yf_tmp;
                    $menor_comprimento_reta = $comprimento_reta_tmp;
                }
            }
        }

        $this->setas[] = new Seta($xi, $xf, $yi, $yf, $this->draw);
    }

    private function comprimentoReta($xi, $xf, $yi, $yf) {
        return sqrt(pow($xf-$xi, 2) + pow($yf-$yi,2));
    }

    private function generate_head() {
        $this->retangulos['head'] = new Retangulo($this->ponto_x_atual, $this->ponto_y_atual, $this->blocks['head_text'], $this->draw, $this->im);
        $this->ponto_x_atual = $this->retangulos['head']->bottom_x + $this->offset;
    }

    private function generate_tail() {
        $this->retangulos['tail'] = new Retangulo($this->ponto_x_atual, $this->ponto_y_atual, $this->blocks['tail_text'], $this->draw, $this->im);
        $this->ponto_x_tail = $this->ponto_x_atual;
    }

    private function generate_multinivel($multinivel) {
        $maior_altura = 0;
        foreach ($this->blocks[$multinivel] as $nivel_y => $bls) {
            foreach ($bls as $nivel_x => $block) {

                $retangulo = new Retangulo($this->ponto_x_atual, $this->ponto_y_atual, $block->texto, $this->draw, $this->im);
                $this->ponto_x_atual = $retangulo->bottom_x + $this->offset;

                if ($retangulo->bottom_y > $maior_altura) {
                    $maior_altura = $retangulo->bottom_y;
                }

                $this->retangulos[$multinivel][$block->id] = $retangulo;
            }
            $this->ponto_y_atual = $maior_altura + $this->offset;
            $this->ponto_x_atual = $this->ponto_x_tail;
        }
    }

    private function generate_axis() {
        $maior_altura = 0;
        foreach ($this->blocks['axis'] as $nivel_x => $block) {
            $retangulo = new Retangulo($this->ponto_x_atual, $this->ponto_y_atual, $block->texto, $this->draw, $this->im);
            $this->ponto_x_atual = $retangulo->bottom_x + $this->offset;

            if ($retangulo->bottom_y > $maior_altura) {
                $maior_altura = $retangulo->bottom_y;
            }
            $this->retangulos['axis'][$block->id] = $retangulo;

            $this->ponto_y_atual = $maior_altura + $this->offset;
            $this->ponto_x_atual = $this->ponto_x_tail;
        }
    }
}
?>
