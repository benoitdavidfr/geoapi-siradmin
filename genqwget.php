<?php
/*PhpDoc:
name: genqwget.php
title: genqwget.php - génération des cmdes wget pour télécharger les mise à jour quotidiennes
doc: |
  cmdes de la forme:
    wget http://files.data.gouv.fr/sirene/sirene_aaaazzz_E_Q.zip
  uniquement les jours de semaine
  Ce script peut être pipé avec un shell
journal: |
  20/1/2018:
    création
*/

$start = '2017-01-02T2359';
$now = new DateTime('now');

for(
  $dateTime = new DateTime($start);
    $dateTime->diff($now)->invert==0;
      $dateTime->add(new DateInterval('P1D'))
) {
  if ($dateTime->format('N') <= 5) {
    echo "echo ",$dateTime->format('r'),"\n";
    $path = 'http://files.data.gouv.fr/sirene/sirene_'
            .$dateTime->format('Y').sprintf('%03d',$dateTime->format('z')+1)
            .'_E_Q.zip';
    echo "wget $path\n";
  }
}