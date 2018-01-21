<?php
/*PhpDoc:
name: see.php
title: see.php - visualise les fichiers SIRENE
doc: |
  Voir les usages
  http://files.data.gouv.fr/sirene/sirene_{AAAAMM}_M_M.zip = mise à jour mensuelle fin du mois {AAAAMM}
  sirene_{AAAAMM}_L_M.zip = stock à la fin du mois {AAAAMM}
  wget http://files.data.gouv.fr/sirene/sirene_201612_L_M.zip
  wget http://files.data.gouv.fr/sirene/sirene_201707_M_M.zip
  wget http://files.data.gouv.fr/sirene/sirene_201706_M_M.zip
  wget http://files.data.gouv.fr/sirene/sirene_201705_M_M.zip
  wget http://files.data.gouv.fr/sirene/sirene_201704_M_M.zip
  wget http://files.data.gouv.fr/sirene/sirene_201703_M_M.zip
  wget http://files.data.gouv.fr/sirene/sirene_201702_M_M.zip
  wget http://files.data.gouv.fr/sirene/sirene_201701_M_M.zip
  wget http://files.data.gouv.fr/sirene/sirene_201612_L_M.zip
journal: |
  17/1/2018:
    création
*/
//echo "argc=$argc\n"; die();
$dirpath = '../../geodata/sirene';
if ($argc <= 1) {
  echo "usage: php see.php {file} [{cmde}]
 où cmde vaut:
   - headers : liste les champs d'en-tête
   - query : affiche la requête
   - absente : liste les enregistrements satisfaisant la requête
   - {SIREN} : affiche l'enregistrement correspondant
";
  $dh = opendir($dirpath);
  if (!$dh)
    die ("erreur dirpath=$dirpath");
  echo "Liste des fichiers:\n";
  while (($file = readdir($dh)) !== false) {
    if (preg_match('!sirene_(\d+_._.)\.zip!', $file, $matches))
      echo " - $matches[1]\n";
  }
  closedir($dh);
  die();
}

$query = [
  //'NJ'=> '!^4!', // Personne morale de droit public soumise au droit commercial
  'NJ'=> '!^7!', // Personne morale et organisme soumis au droit administratif
  //'NJ'=> '!^71!', // Administration de l'état
  //'NJ'=> '!^7113!', // Ministère
  //'NJ'=> '!^7120!', // Service central d'un ministère
  'SIEGE'=> '!^1$!',
  //'DEPET'=> '!^14$!',
  'VMAJ'=> '!^E$!',
];
$zippath = "$dirpath/sirene_$argv[1].zip";

if (($argc > 2) and ($argv[2]=='query')) {
  echo "query = "; print_r($query);
  die();
}
if (($argc > 2) and is_numeric($argv[2]))
  $query['SIREN'] = "!^$argv[2]$!";

$zip = new ZipArchive;
if ($zip->open($zippath) !== TRUE)
  die("Erreur d'ouverture de $zippath\n");

for ($numFile=0; $numFile < $zip->numFiles; $numFile++) {
  $path = $zip->getNameIndex($numFile);
  $pathinzip = "zip://$zippath#$path";
  $fzip = fopen($pathinzip, 'r');
  if ($fzip === FALSE)
    die("Erreur d'ouverture de $pathinzip\n");
  $headers = null;
  while ($record = fgetcsv($fzip, 0, ';')) {
    if (!$headers) {
      $headers = $record;
      if ($argv[1]=='headers') {
        echo "headers = "; print_r($headers);
        die();
      }
      continue;
    }
    //print_r($record);
    $record2 = [];
    foreach ($record as $i => $val) {
      $record2[$headers[$i]] = utf8_encode($val);
    }
    foreach ($query as $field => $reg) {
      //echo "query: $field => $reg\n";
      if (!preg_match($reg, $record2[$field])) {
        //echo "skipped $record2[SIREN] | $record2[NOMEN_LONG] | $field -> ",$record2[$field],"\n";
        continue 2;
      }
    }
    if (($argc > 2) and is_numeric($argv[2]))
      print_r($record2);
    else
      echo "$record2[SIREN]-$record2[NIC] | $record2[NOMEN_LONG] | $record2[NJ] | $record2[LIBNJ]\n";
  }
  fclose($fzip);
}
