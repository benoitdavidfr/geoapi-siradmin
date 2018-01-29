<?php
/*PhpDoc:
name: inc.php
title: inc.php - API d'accès en Php
doc: |
journal: |
  21/1/2018:
    création
*/
require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/mongouri.inc.php';
require_once __DIR__.'/../../spyc/spyc2.inc.php';

class Siradmin {
  private $mongouri = '';
  
  function __construct(string $mongouri) { $this->mongouri = $mongouri; }
  
  static function doc() {
    $yaml = spycLoad(__DIR__.'/apidefinition.yaml');
    if (!$yaml)
      throw new Exception("Erreur: fichier apidefinition.yaml non trouvé\n");
    return $yaml;
  }
  
  static function categoriesJuridiques() {
    $yaml = spycLoad(__DIR__.'/categoriesjuridiques.yaml');
    if (!$yaml)
      throw new Exception("Erreur: fichier naturesjuridiques.yaml non trouvé\n");
    return $yaml;
  }
  
  function admins(array $params=[]) {
    if (!isset($params['nj']))
      throw new Exception("Erreur de requête : le paramètre nj est absent\n");
    $query = ['carEntreprise.NJ'=> new MongoDB\BSON\Regex("^$params[nj]", 'i')];
    if (isset($params['departement']))
      $query['loc.DEPET'] = $params['departement'];
    if (isset($params['nom']))
      $query['idEntreprise.NOMEN_LONG'] = new MongoDB\BSON\Regex($params['nom'], 'i');
    $mgdbclient = new MongoDB\Client($this->mongouri);
    $admins = [];
    foreach ($mgdbclient->sirene->administration->find($query) as $admin) {
      $admin = json_decode(json_encode($admin), true);
      $admins[] = [
        'SIREN'=> $admin['idEntreprise']['SIREN'],
        'NOMEN_LONG'=> $admin['idEntreprise']['NOMEN_LONG'],
        'NJ'=> $admin['carEntreprise']['NJ'],
        'LIBNJ'=> $admin['carEntreprise']['LIBNJ'],
        'uri'=> 'http://siradmin.geoapi.fr/admins/'.$admin['idEntreprise']['SIREN'],
      ];
    }
    return $admins;
  }
  
  function adminsParSiren(string $siren) {
    if ((strlen($siren)<>9) or !preg_match('!^\d+$!',$siren))
      throw new Exception("Requête incorrecte : le paramètre '$siren' ne correspond pas à un numéro SIREN\n");
    $mgdbclient = new MongoDB\Client($this->mongouri);
    $basesirene = $mgdbclient->sirene;
    $admin = $basesirene->administration->findOne(['_id'=> $siren]);
    if (!$admin)
      throw new Exception("Aucune administration ne correspond au numéro SIREN '$siren'\n");
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
    return $admin;
  }
};