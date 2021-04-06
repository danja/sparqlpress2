<?php
# include("/home/gpreeti/php/JSON.php");
# require_once 'JSON.php';

echo "Hello world!<br>";



$json = new Services_JSON();

$marks = array(
            "mohammad" => array (
               "physics" => 35,
               "maths" => 30,
               "chemistry" => 39
            ),

            "qadir" => array (
               "physics" => 30,
               "maths" => 32,
               "chemistry" => 29
            ),

            "zara" => array (
               "physics" => 31,
               "maths" => 22,
               "chemistry" => 39
            )
         );
$marks=$json->encode($marks);
print_r($marks);
$marks = $json->decode($marks);
#var_dump($marks);
# print"$marks";
print_r($marks);

echo "Hello world!<br>";
?>
