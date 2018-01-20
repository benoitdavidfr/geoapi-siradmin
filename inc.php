<?php
/*PhpDoc:
name: inc.php
title: inc.php - API d'accès
doc: |
journal: |
  18/1/2018:
    création
*/
function sirene(string $siren): array {
  $mgdbclient = new MongoDB\Client($mongouri);
  $basesirene = $mgdbclient->sirene;
  $doc = $basesirene->administration->findOne(['_id'=> $siren]);
  if (!$doc)
    return [];
  $doc = json_decode(json_encode($doc), true);
  foreach ($basesirene->commune->find(['EPCI'=> $siren]) as $com) {
    
  }
}