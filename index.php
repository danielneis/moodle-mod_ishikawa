<?php
error_reporting(E_ALL);
ini_set("display_errors", "on");
ini_set("log_errors", "on");

require_once('Ishikawa.class.php');

$blocos = array(
        array(
            'a1' => array('text' => 'texto', 'color' => 'red', 'conections' => array(array('nivel' => 1, 'nome' => 'b2'), array('nivel' => 1, 'nome' => 'b3'))),
            'a2' => array('text' => 'texto', 'color' => 'red', 'conections' => array(array('nivel' => 1, 'nome' => 'b1'))),
            'a3' => array('text' => 'texto', 'color' => 'red', 'conections' => array(array('nivel' => 1, 'nome' => 'b2'))),
            ),
        array(
            'b1' => array('text' => 'texto', 'color' => 'red', 'conections' => array(array('nivel' => 0, 'nome' => 'a2'), array('nivel' => 0, 'nome' => 'a1'))),
            'b2' => array('text' => 'texto', 'color' => 'red', 'conections' => array(array('nivel' => 0, 'nome' => 'a1'))),
            'b3' => array('text' => 'texto', 'color' => 'red', 'conections' => array(array('nivel' => 0, 'nome' => 'a2'))),
            ),
        );

$ishikawa = new Ishikawa($blocos);
$ishikawa->draw();

?>
