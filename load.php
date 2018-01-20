<?php
{/*PhpDoc:
name: load.php
title: load.php - charge dans MongoDB une sélection du fichier SIRENE
tables:
  - name: administration
    title: administration - sélection des administrations dans SIRENE
    database: [sirene]
    doc: |
      voir schema.yaml
  - name: genealogie
    title: genealogie - généalogie des chargements
    database: [sirene]
    doc: |
      voir schema.yaml
  - name: historique
    title: historique - recopie du contenu des fichiers de mise à jour
    database: [sirene]
    doc: |
      voir schema.yaml
  - name: erreur
    title: erreur - erreurs de chargement
    database: [sirene]
    doc: |
      voir schema.yaml
  
doc: |
  Restriction aux catégories juridiques (7 - Personne morale et organisme soumis au droit administratif)
journal: |
  20/1/2018:
    restructuration du schema pour stocker l'historique de chaque entité
  18/1/2018:
    restriction aux sièges et aux administrations
    chgt de nom de la collection -> administration
    sélection des champs
    enregistrement de la codification des natures juridiques
  17/1/2018:
    création
*/}

require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/mongouri.inc.php';
require_once __DIR__.'/dessin.inc.php';

if ($argc <= 1) {
  die("usage: php $argv[0] {cmde} [{file}]
où {cmde} vaut:
  - ini: réinitialisation et chargement de l'état initial
  - majm: chargement de la mises à jour mensuelles définies en paramètre
  - majq: chargement de la mises à jour quotidiennes définies en paramètre
");
}

function integrationMiseAJours(string $zippath, $basesirene) {
  $zip = new ZipArchive;
  if ($zip->open($zippath) !== TRUE)
    die("Erreur d'ouverture de $zippath\n");

  $today = date(DateTime::ATOM);
  $basesirene->genealogie->insertOne([
    'date'=> $today,
    'doc'=> "Chargement le $today des MAJ $zippath",
  ]);
  echo "Mise à jour de la base SIRENE à partir du fichier $zippath\n";
  
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
        Dessin::setHeaders($headers);
        continue;
      }
      //print_r($record);
      if (!($struct = Dessin::structure($record)))
        continue;
      $struct['fichier'] = $zippath;
      $basesirene->historique->insertOne($struct);
      unset($struct['fichier']);
      //print_r($struct);
      // suppression ou sortie de la diffusion
      if (in_array($struct['donneesMaj']['VMAJ'], ['E','O'])) {
        $siren = $struct['idEntreprise']['SIREN'];
        $doc = $basesirene->administration->findOne(['_id'=> $siren]);
        if (!$doc)
          die("organisme $siren non trouvé\n");
        $doc = json_decode(json_encode($doc), true);
        $struct['histos'] = Dessin::histos($doc, $struct);
        $struct['suppression'] = $struct['donneesMaj']['DATEMAJ'];
        $basesirene->administration->replaceOne(['_id'=>$siren], $struct);
        //echo "Suppression de $siren\n";
      }
      // etat initial
      elseif ($struct['donneesMaj']['VMAJ']=='I') {
      }
      // etat final
      elseif ($struct['donneesMaj']['VMAJ']=='F') {
        $siren = $struct['idEntreprise']['SIREN'];
        $doc = $basesirene->administration->findOne(['_id'=> $siren]);
        if ($doc) {
          $doc = json_decode(json_encode($doc), true);
          $struct['histos'] = Dessin::histos($doc, $struct);
          $struct['_id'] = $siren;
          $basesirene->administration->replaceOne(['_id'=>$siren], $struct);
          //echo "Mise à jour de $siren\n";
        }
        else {
          $struct['_id'] = $siren;
          $basesirene->administration->insertOne($struct);
          $message = "organisme $siren non trouvé ligne ".__LINE__;
          $basesirene->erreur->insertOne([
            'message'=> $message,
            'fichier'=> $zippath,
            'date'=> $today,
          ]);
          echo "Erreur: $message\n";
        }
      }
      // création ou entrée dans la diffusion
      elseif (in_array($struct['donneesMaj']['VMAJ'], ['C','D'])) {
        $siren = $struct['idEntreprise']['SIREN'];
        $doc = $basesirene->administration->findOne(['_id'=> $siren]);
        // si l'enregistrement n'existe pas déjà, il est créé
        if (!$doc) {
          $struct['_id'] = $siren;
          $basesirene->administration->insertOne($struct);
          //echo "Création de $siren\n";
        }
        // si l'enregistrement existe déjà, il est mis à jour
        else {
          $doc = json_decode(json_encode($doc), true);
          $struct['histos'] = Dessin::histos($doc, $struct);
          $struct['_id'] = $siren;
          $basesirene->administration->replaceOne(['_id'=>$siren], $struct);
          //echo "Création de $siren ayant existé\n";
        }
      }
      else
        die("VMAJ ".$struct['donneesMaj']['VMAJ']." inconnu\n");
    }
    fclose($fzip);
  }
}

$mgdbclient = new MongoDB\Client($mongouri);
$basesirene = $mgdbclient->sirene;

if ($argv[1]=='ini') {
  $basesirene->drop();
  $zippath = '../../geodata/sirene/sirene_201612_L_M.zip';
  $today = date(DateTime::ATOM);
  $basesirene->genealogie->insertOne([
    '_id'=> $today,
    'doc'=> "Chargement le $today de $zippath\n,"
            ." restriction aux catégories juridiques '7 Personne morale et organisme soumis au droit administratif'\n"
            ." et aux sièges, c a d aux entreprises donc en excluant leurs établissements",
  ]);
  echo "Initialisation de la base SIRENE à partir du fichier $zippath\n";
  
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
        Dessin::setHeaders($headers);
        continue;
      }
      //print_r($record);
      if ($struct = Dessin::structure($record)) {
        $struct['_id'] = $struct['idEntreprise']['SIREN'];
        $basesirene->administration->insertOne($struct);
      }
    }
    fclose($fzip);
  }
  echo "Fin OK initialisation à partir du fichier $zippath\n";
}

// intégration des mises à jour mensuelles ou quotidiennes
elseif (in_array($argv[1], ['majm','majq'])) {
  $maj = ($argv[1]=='majm' ? 'M_M': 'E_Q');
  $dirpath = '../../geodata/sirene';
  if ($argc <= 2) {
    if (!($dh = opendir($dirpath)))
      die ("erreur dirpath=$dirpath");
    echo "Liste des fichiers:\n";
    while (($file = readdir($dh)) !== false) {
      if (preg_match("!sirene_(\d+_$maj)\.zip!", $file, $matches))
        echo " - $matches[1]\n";
    }
    closedir($dh);
    die();
  }
  
  $zippath = "$dirpath/sirene_$argv[2].zip";
  if (is_file($zippath)) {
    integrationMiseAJours($zippath, $basesirene);
    die("Fin OK mise à jour à partir du fichier $zippath\n");
  }
  else {
    $pattern = $argv[2];
    if (!($dh = opendir($dirpath)))
      die ("erreur dirpath=$dirpath");
    while (($file = readdir($dh)) !== false) {
      if (preg_match("!sirene_(\d+_$maj)\.zip!", $file, $matches)) {
        if (preg_match("!$pattern!", $matches[1])) {
          $zippath = "$dirpath/sirene_$matches[1].zip";
          integrationMiseAJours($zippath, $basesirene);
        }
      }
    }
    closedir($dh);
    die("Fin OK mise à jour à partir des fichiers $dirpath/sirene_$pattern.zip\n");
  }
}

else
  die("argument $argv[1] non prévu\n");

