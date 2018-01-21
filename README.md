# geoapi-siradmin

Registre des administrations françaises fondé sur la base SIRENE.

L'objectif de ce projet est de simplifier l'accès aux définitions des administrations issues de la base SIRENE.
En effet les standards de dématérialisation des documents d'urbanisme et des servitudes d'utilité publique
utilisent largement le numéro SIREN des administrations, notamment des EPCI,
définies dans la base SIRENE.

Ce projet propose une [API REST](http://siradmin.geoapi.fr/)
de consultation de ces administrations.

Exemples:
- [Nomenclature des catégories juridiques](http://siradmin.geoapi.fr/categoriesJuridiques)

Ce répertoire contient le code de l'API et des scripts de chargement de la base MongoDB.

Documentation complémentaire:
  - [Site SIRENE de l'INSEE](https://www.sirene.fr/)
  - [Description complète des fichiers stocks et mises à jour mensuelles (dessin L2)](https://www.sirene.fr/static-resources/doc/dessin_L2_description_complete.pdf?version=1.14)
  - [Description complète du dessin des fichiers des mises à jour quotidiennes (dessin XL2)](https://www.sirene.fr/static-resources/doc/dessin_XL2_description_complete.pdf?version=1.14)
  - [Page SIRENE sur data.gouv.fr](https://www.data.gouv.fr/fr/datasets/base-sirene-des-entreprises-et-de-leurs-etablissements-siren-siret/)
