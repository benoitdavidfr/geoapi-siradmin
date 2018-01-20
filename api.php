<?php
/*PhpDoc:
name: api.php
title: api.php - code de l'API
doc: |
journal: |
  20/1/2018:
    création
*/
require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/mongouri.inc.php';
require_once __DIR__.'/../../spyc/spyc2.inc.php';

// /
// affichage de la doc soit en HTML soit en JSON
if (!isset($_SERVER['PATH_INFO']) or ($_SERVER['PATH_INFO']=='/')) {
  if (!isset($_SERVER['HTTP_ACCEPT'])) {
    echo "<pre>_SERVER="; print_r($_SERVER); echo "</pre>\n"; die();
  }
  $http_accepts = explode(',',$_SERVER['HTTP_ACCEPT']);
  //echo "<pre>http_accepts="; print_r($http_accepts); echo "</pre>\n"; die();
  foreach ($http_accepts as $http_accept)
    if (in_array($http_accept,['text/html','application/json']))
      break;
  if ($http_accept=='application/json') {
    header('Content-type: application/json; charset="utf8"');
    echo file_get_contents('https://api.swaggerhub.com/apis/benoitdavidfr/siradmin.geoapi.fr');
    die();
  }
  header('HTTP/1.1 307 Temporary Redirect');
  header("Location: https://swaggerhub.com/apis/benoitdavidfr/siradmin.geoapi.fr");
  die();
}

// /terms
if (preg_match('!^/terms$!', $_SERVER['PATH_INFO'])) {
  header('Content-type: text/html; charset="utf8"');
  echo file_get_contents(__DIR__.'/terms.html');
  die();
}

function showNomenclature(array $nomenclature, int $level=1) {
  foreach ($nomenclature as $code => $contenu) {
    if (isset($contenu['enfants'])) {
      echo "<h",$level+1,">$code : $contenu[libelle]</h",$level+1,">\n";
      echo "<ul>\n";
      showNomenclature($contenu['enfants'], $level+1);
      echo "</ul>\n";
    }
    else {
      echo "<li>$code : $contenu[libelle]\n";
    }
  }
}

// /categoriesJuridiques
if (preg_match('!^/categoriesJuridiques$!', $_SERVER['PATH_INFO'])) {
  $yaml = spycLoad(__DIR__.'/categoriesjuridiques.yaml');
  if (!$yaml) {
    header("HTTP/1.1 500 Internal Server Error");
    header('Content-type: text/plain; charset="utf8"');
    die("Erreur: fichier naturesjuridiques.yaml non trouvé\n");
  }
  $http_accepts = explode(',',$_SERVER['HTTP_ACCEPT']);
  //echo "<pre>http_accepts="; print_r($http_accepts); echo "</pre>\n"; die();
  foreach ($http_accepts as $http_accept)
    if (in_array($http_accept,['text/html','text/yaml','application/json']))
      break;
  if ($http_accept=='text/yaml') {
    header('Content-type: text/yaml; charset="utf8"');
    die(spycDump($yaml));
  }
  elseif ($http_accept=='application/json') {
    header('Content-type: application/json; charset="utf8"');
    die(json_encode($yaml, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
  }
  else {
    header('Content-type: text/html; charset="utf8"');
    echo "<html><head><meta charset='UTF-8'><title>$yaml[titre]</title></head><body>\n",
         "<h1>$yaml[titre]</h1>",
         "$yaml[doc]\n";
    showNomenclature($yaml['nomenclature']);
    die();
  }
}

// /administrations
if (preg_match('!^/administrations$!', $_SERVER['PATH_INFO'], $matches)) {
  $query = [];
  if (isset($_GET['departement']))
    $query['loc.DEPET'] = $_GET['departement'];
  if (isset($_GET['nj']))
    $query['carEntreprise.NJ'] = new MongoDB\BSON\Regex("^$_GET[nj]", 'i');
  $mgdbclient = new MongoDB\Client($mongouri);
  $basesirene = $mgdbclient->sirene;
  header('Content-type: application/json; charset="utf8"');
  echo "[\n";
  $first = true;
  foreach ($basesirene->administration->find($query) as $admin) {
    $admin = json_decode(json_encode($admin), true);
    $admin = [
      'SIREN'=> $admin['idEntreprise']['SIREN'],
      'NOMEN_LONG'=> $admin['idEntreprise']['NOMEN_LONG'],
      'NJ'=> $admin['carEntreprise']['NJ'],
      'LIBNJ'=> $admin['carEntreprise']['LIBNJ'],
      'uri'=> 'http://siradmin.geoapi.fr/admins/'.$admin['idEntreprise']['SIREN'],
    ];
    if ($first)
      $first = false;
    else
      echo ",\n";
    echo json_encode($admin, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
  }
  echo "]\n";
  die();
}

// /administrations/{siren}
if (preg_match('!^/administrations/([^/]+)$!', $_SERVER['PATH_INFO'], $matches)) {
  $siren = $matches[1];
  $mgdbclient = new MongoDB\Client($mongouri);
  $basesirene = $mgdbclient->sirene;
  $admin = $basesirene->administration->findOne(['_id'=> $siren]);
  if (!$admin) {
    header("HTTP/1.1 404 Bad Request");
    header('Content-type: text/plain; charset="utf8"');
    die("Aucune administration ne correspond au code SIREN $siren\n");
  }
  $admin = json_decode(json_encode($admin), true);
  unset($admin['_id']);
  if (in_array($admin['carEntreprise']['NJ'], ['7343','7344','7346','7347','7348'])) {
    $admin['communes'] = [];
    foreach ($basesirene->administration->find(['carEntreprise.NJ'=> '7210', 'loc.EPCI'=> $siren]) as $commune) {
      $commune = json_decode(json_encode($commune), true);
      $admin['communes'][] = [
        'DEPCOMEN'=> $commune['infoSiege']['DEPCOMEN'],
        'SIREN'=> $commune['idEntreprise']['SIREN'],
        'NOMEN_LONG'=> $commune['idEntreprise']['NOMEN_LONG'],
      ];
    }
  }
  header('Content-type: application/json; charset="utf8"');
  die(json_encode($admin, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
}


header('HTTP/1.1 400 Bad Request');
header('Content-type: text/plain; charset="utf8"');
die("Requête inconnue\n");

