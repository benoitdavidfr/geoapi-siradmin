<?php
/*PhpDoc:
name: dessin.inc.php
title: dessin.inc.php - dessin des fichiers socle et mises à jour mensuelle ou quotidienne
doc: |
  Définition de la classe statique Dessin contenant le dessin des fichiers et de la collection administration
  Les champs définis dans les fichiers sont hiérarchisés conformément à la doc INSEE pour faciliter la lecture
  La classe implémente 3 méthodes:
    - initialisation de la liste des champs à partir du fichier lu
    - structuration de l'enregistrement selon la hiérarchie définie
    - construction de l'historique
journal: |
  19-20/1/2018:
    création
*/

class Dessin {
  // structure hiérarchisée des champs du fichier SIRENE
  static $structure = [
    // identification de l'entreprise
    'idEntreprise'=> [
      'SIREN', // Identifiant de l'entreprise
      'NOMEN_LONG', // Nom ou raison sociale de l'entreprise
      'SIGLE', // Sigle de l'entreprise
      'NOM', // Nom de naissance
      'PRENOM', // Prénom
      'CIVILITE', // Civilité des entrepreneurs individuels
      'RNA', // Numéro d’identification au répertoire national des associations
    ],
    // informations sur le siège de l'entreprise
    'infoSiege'=> [
      'NICSIEGE', // Numéro interne de classement de l'établissement siège
      'RPEN', // Région de localisation du siège de l'entreprise
      'DEPCOMEN', // Département et commune de localisation du siège de l'entreprise
      'ADR_MAIL', // Adresse mail
    ],
    // caractéristiques économiques de l'entreprise
    'carEntreprise'=> [
      'NJ', // Nature juridique de l'entreprise
      'LIBNJ', // Libellé de la nature juridique
      'APEN700',
      'LIBAPEN',
      'DAPEN',
      'APRM',
      'ESS',
      'DATEESS',
      'TEFEN',
      'LIBTEFEN',
      'EFENCENT',
      'DEFEN',
      'CATEGORIE',
      'DCREN',
      'AMINTREN',
      'MONOACT',
      'MODEN',
      'PRODEN',
      'ESAANN',
      'TCA',
      'ESAAPEN',
      'ESASEC1N',
      'ESASEC2N',
      'ESASEC3N',
      'ESASEC4N',
    ],
    // localisation géographique de l'établissement
    'loc'=> [
      'RPET', // Région de localisation de l'établissement
      'LIBREG', // Libellé de la région
      'DEPET', // Département de localisation de l'établissement
      'ARRONET', // Arrondissement de localisation de l'établissement
      'CTONET', // Canton de localisation de l'établissement
      'COMET', // Commune de localisation de l'établissement
      'LIBCOM', // Libellé de la commune de localisation de l'établissement
      'DU', // Département de l'unité urbaine de la localisation de l'établissement
      'TU', // T aille de l'unité urbaine
      'UU', // Numéro de l'unité urbaine
      'EPCI', // Localisation de l'établissement dans un établissement public de coopération intercommunale
      'TCD', // Tranche de commune détaillée
      'ZEMET', // Zone d'emploi
    ],
    // adresse géographique
    'adresse'=> [
      'NUMVOIE',
      'INDREP',
      'TYPVOIE',
      'LIBVOIE',
      'CODPOS',
      'CEDEX',
    ],
    // adresse normalisée
    'adrNorm'=> [
      'L1_NORMALISEE',
      'L2_NORMALISEE',
      'L3_NORMALISEE',
      'L4_NORMALISEE',
      'L5_NORMALISEE',
      'L6_NORMALISEE',
      'L7_NORMALISEE',
    ],
    // adresse déclarée
    'adrDecl'=> [
      'L1_DECLAREE',
      'L2_DECLAREE',
      'L3_DECLAREE',
      'L4_DECLAREE',
      'L5_DECLAREE',
      'L6_DECLAREE',
      'L7_DECLAREE',
    ],
    // informations sur l'établissement
    'infoEtab'=> [
      'NIC', // Numéro interne de classement de l'établissement
      'SIEGE', // Qualité de siège ou non de l'établissement
      'ENSEIGNE', // Enseigne ou nom de l'exploitation
      'IND_PUBLIPO', // Indicateur du champ du publipostage
      'DIFFCOM', // Statut de diffusion de l'établissement
      'AMINTRET', // Année et mois d'introduction de l'établissement dans la base de diffusion
    ],
    // caractéristiques économiques de l'établissement
    'carEtab'=> [
      'NATETAB', // Nature de l'établissement d'un entrepreneur individuel
      'LIBNATETAB', // Libellé de la nature de l'établissement
      'APET700', // Activité principale de l'établissement
      'LIBAPET', // Libellé de l'activité principale de l'établissement
      'DAPET', // Année de validité de l'activité principale de l'établissement
      'TEFET', // Tranche d'effectif salarié de l'établissement
      'LIBTEFET', // Libellé de la tranche d'effectif de l'établissement
      'EFETCENT',
      'DEFET',
      'ORIGINE',
      'DCRET',
      'DDEBACT',
      'ACTIVNAT',
      'LIEUACT',
      'ACTISURF',
      'SAISONAT',
      'MODET',
      'PRODET',
      'PRODPART',
      'AUXILT',
    ],
    // données spécifiques aux mises à jour
    'donneesMaj'=> [
      'VMAJ', // Nature de la mise à jour (création, suppression, modification)
      'VMAJ1', // Indicateur de mise à jour n°1
      'VMAJ2', // Indicateur de mise à jour n°2
      'VMAJ3', // Indicateur de mise à jour n°3
      'DATEMAJ', // Date de traitement de la mise à jour
      'EVE', // Type d'événement
      'DATEVE', // Date de l'événement
      'TYPCREH', // Type de création
      'DREACTET', // Date de réactivation de l'établissement (année, mois, jour)
      'DREACTEN', // Date de réactivation de l'entreprise (année, mois, jour)
      'MADRESSE', // Indicateur de mise à jour de l'adresse de localisation de l'établissement
      'MENSEIGNE', // Indicateur de mise à jour de l'enseigne de l'entreprise
      'MAPET', // Indicateur de mise à jour de l'activité principale de l'établissement
      'MPRODET', // Indicateur de mise à jour du caractère productif de l'établissement
      'MAUXILT', // Indicateur de mise à jour du caractère auxiliaire de l'établissement
      'MNOMEN', // Indicateur de la mise à jour du nom ou de la raison sociale
      'MSIGLE', // Indicateur de mise à jour du sigle
      'MNICSIEGE', // Indicateur de mise à jour du Nic du siège ou de l'établissement principal
      'MNJ', // Indicateur de la mise à jour de la nature juridique
      'MAPEN', // Indicateur de mise à jour de l'activité principale de l'entreprise
      'MPRODEN', // Indicateur de mise à jour du caractère productif de l'entreprise
      'SIRETPS', // Siret du prédécesseur ou du successeur
      'TEL', // Téléphone
    ],
  ];
  static $headers;
  
  static function getStructure(): array { return self::$structure; }
    
  static function setHeaders(array $headers): void { self::$headers = $headers; }
  
  static function structure(array $record): ?array {
    $record2 = [];
    foreach ($record as $i => $val) {
      $val2 = trim(utf8_encode($val));
      if ($val2) {
        if (isset(self::$headers[$i]))
          $record2[self::$headers[$i]] = $val2;
        else
          $record2["header$i"] = $val2;
      }
    }
    //print_r($record2); die("Fin");
    if (!isset($record2['SIEGE']) or ($record2['SIEGE']<>'1'))
      return null;
    // Personne morale et organisme soumis au droit administratif
    if (!isset($record2['NJ']) or !preg_match('!^7!', $record2['NJ']))
      return null;
    $struct = [];
    foreach (self::$structure as $level0 => $fields) {
      foreach ($fields as $field) {
        if (isset($record2[$field])) {
          $struct[$level0][$field] = $record2[$field];
          unset($record2[$field]);
        }
      }
    }
    foreach ($record2 as $field => $val)
      $struct['extra'][$field] = $val;
    return $struct;
  }
  
  // met à jour l'historique contenu défini dans prev avec les nouvelles valeurs dans next
  // renvoie le nouvel historique
  static function histos(array $prev, array $next): array {
    $datehisto = (isset($next['donneesMaj']['DATEVE']) ?
                    $next['donneesMaj']['DATEVE'] : 
                    $next['donneesMaj']['DATEMAJ']);
    $histo = ['DATEHISTO'=> $datehisto]; // liste des champs mis à jour
    foreach (self::$structure as $level0 => $fields) {
      foreach ($fields as $field) {
        if (isset($next[$level0][$field]) and isset($prev[$level0][$field])) { // défini dans les 2
          if ($next[$level0][$field] <> $prev[$level0][$field])
            $histo[$field] = $prev[$level0][$field]; // je garde l'ancienne valeur
        }
        elseif (isset($next[$level0][$field]) and !isset($prev[$level0][$field])) { // défini next mais pas prev
          $histo[$field] = null; // je garde l'information que la valeur n'était pas définie
        }
        elseif (!isset($next[$level0][$field]) and isset($prev[$level0][$field])) { // défini prev mais pas next
          $histo[$field] = $prev[$level0][$field]; // je garde l'ancienne valeur
        }
      }
    }
    $histos = (isset($prev['histos']) ? $prev['histos'] : []);
    $histos[] = $histo;
    return $histos;
  }
};


if (basename(__FILE__)<>basename($_SERVER['PHP_SELF'])) return;

$fp = fopen('../../geodata/sirene/liste-modalites-xl2.csv', 'r');
if ($fp === FALSE)
  die("Erreur d'ouverture des modalités\n");
$headers = null;
while ($record = fgetcsv($fp, 0, ';')) {
  if (!$headers) {
    $headers = $record;
    continue;
  }
  $modalites[$record[0]]['libelle'] = utf8_encode($record[1]);
}
fclose($fp);

// Génère la définition Swager 2.0 de Administration
echo "  Administration:\n",
     "    type: object\n",
     "    required:\n";
$structure = Dessin::getStructure();
foreach ($structure as $level0 => $fields) {
  echo "      - $level0\n";
}
echo "    properties:\n";
foreach ($structure as $level0 => $fields) {
  echo "      $level0:\n",
       "        type: object\n",
       "        properties:\n";
  foreach($fields as $field)
    echo "          $field:\n",
         "            type: string\n",
         "            description: ",$modalites[$field]['libelle'],"\n";
}

echo <<<EOT
      histos:
        type: object
        properties:
          DATEHISTO:
            type: string
            description: date de la mise à jour

EOT;
foreach ($structure as $level0 => $fields) {
  foreach($fields as $field)
    echo "          $field:\n",
         "            type: string\n",
         "            description: ",$modalites[$field]['libelle'],"\n";
}
echo <<<EOT
      suppression:
        type: string
        description: si l'administration est supprimée date de la suppression

EOT;
