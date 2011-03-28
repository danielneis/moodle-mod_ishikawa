<?php

require_once('Retangulo.class.php');
require_once('Linha.class.php');

class Ishikawa  {

    var $blocos = array();

    var $linhas = array();

    var $dados;

    var $inicio_x = 10;
    var $inicio_y = 10;

    var $offset = 30;

    function __construct($dados) {
        $this->dados = $dados;
    }

    function draw() {
        $nivel = 0;
        $tamanho = 0;

        $ponto_x = $this->inicio_x;
        $ponto_y = $this->inicio_y;

        // Cria blocos
        foreach ($this->dados as $niveis) {
            foreach ($niveis as $nome_bloco => $bloco) {
                $b = new Retangulo($ponto_x, $ponto_y);
                $ponto_x = $b->bottom_x + $this->offset;
                if ($b->bottom_y > $tamanho) {
                    $tamanho = $b->bottom_y;
                }
                $this->blocos[$nivel][$nome_bloco] = $b;
            }
            $ponto_y = $tamanho + $this->offset;
            $ponto_x = $this->inicio_x;
            $nivel++;
        }

        // Gera conexões
        foreach ($this->dados as $nivel => $niveis) {
            foreach ($niveis as $nome_bloco => $bloco) {
                foreach ($bloco['conections'] as $connection) {
                    $this->geraLinha($this->blocos[$nivel][$nome_bloco], $this->blocos[$connection['nivel']][$connection['nome']]);
                }
            }
        }

        $this->printBlocos();
    }

    function printBlocos() {
        $img = imagecreatetruecolor(800, 800);
        $green = imagecolorallocate($img, 132, 135, 28);

        foreach ($this->blocos as $niveis) {
            foreach ($niveis as $bloco) {
                imagerectangle($img, $bloco->upper_x, $bloco->upper_y, $bloco->bottom_x, $bloco->bottom_y, $green);
            }
        }

        foreach ($this->linhas as $linha) {
            imageline($img, $linha->xi, $linha->yi, $linha->xf, $linha->yf, $green);
        }

        header('Content-Type: image/jpeg');

        imagejpeg($img);
        imagedestroy($img);
    }

    function geraLinha($origem, $destino) {
        $funcoes = array('pontoMedioTopo', 'pontoMedioBase', 'pontoMedioLateralDireita', 'pontoMedioLateralEsquerda');
        $comprimento_reta = 9999;
        $func1 = '';
        $func2 = '';
        $pontos = array();
        foreach ($funcoes as $funcao1) {
            foreach ($funcoes as $funcao2) {
                list($xi_tmp, $yi_tmp) = call_user_func(array($origem, $funcao1));
                list($xf_tmp, $yf_tmp) = call_user_func(array($destino, $funcao2));
                $comprimento_reta_temp = $this->comprimentoReta($xi_tmp, $xf_tmp, $yi_tmp, $yf_tmp);

                if ($comprimento_reta_temp < $comprimento_reta) {
                    list($xi, $xf, $yi, $yf) = array($xi_tmp, $xf_tmp, $yi_tmp, $yf_tmp);
                    $comprimento_reta = $comprimento_reta_temp;
                    $func1 = $funcao1;
                    $func2 = $funcao2;
                    $pontos = array($xi, $xf, $yi, $yf);
                }
            }
        }

        $this->linhas[] = new Linha($xi, $xf, $yi, $yf);
    }

    function comprimentoReta($xi, $xf, $yi, $yf) {

        return sqrt(pow($xf-$xi, 2) + pow($yf-$yi,2));

    }

}

?>
