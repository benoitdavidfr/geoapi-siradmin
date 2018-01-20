<?php
/*PhpDoc:
name: unzip.php
title: unzip.php - lecture du zip et extraction du contenu SIRENE
doc: |
  sans paramètre: liste le contenu
  avec le no d'un fichier comme paramètre: extrait ce fichier
journal: |
  17/1/2018:
    création
*/

$zip = new ZipArchive;
$zippath = '../../geodata/sirene/sirene_201712_L_M.zip';
if ($zip->open($zippath) !== TRUE)
  die("Erreur d'ouverture de $zippath\n");
//echo "argc=$argc\n";

if ($argc <= 1) {
  for ($i=0; $i < $zip->numFiles; $i++) {
    $path = $zip->getNameIndex($i);
    echo "$i: $path\n";
  }
  die();
}

$path = $zip->getNameIndex($argv[1]);
$pathinzip = "zip://$zippath#$path";
$fzip = fopen($pathinzip, 'r');
if ($fzip === FALSE)
  die("Erreur d'ouverture de $pathinzip\n");
if (readfile($pathinzip) === FALSE)
  die("Erreur de lecture de $pathinzip\n");
