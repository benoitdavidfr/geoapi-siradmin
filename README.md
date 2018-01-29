# geoapi-siradmin

Registre des administrations françaises fondé sur la base SIRENE.

L'objectif de ce projet est de simplifier l'identification des administrations issue de la base SIRENE.
En effet les standards de dématérialisation des documents d'urbanisme et des servitudes d'utilité publique
utilisent largement le numéro SIREN des administrations, notamment des EPCI,
défini dans la base SIRENE.

Ce projet propose une [API REST](http://siradmin.geoapi.fr/)
de consultation des informations sur ces administrations qui permet principalement:
- de rechercher des administrations situées dans un département donné et correspondant à certaines catégories juridiques,
- de consulter la nomenclature juridique utilisée par la base SIRENE,
- d'accéder aux informations associées à une administration définie par son numéro SIREN.

Les administrations concernées sont celles ayant comme catégorie juridique une sous-catégorie de "7 : Personne morale et organisme soumis au droit administratif".

Exemples de requêtes:
- [Nomenclature des catégories juridiques](http://siradmin.geoapi.fr/categoriesJuridiques)
- [Liste des services déconcentrés départementaux du département 14](http://siradmin.geoapi.fr/admins?nj=7172&departement=14)
- [Liste des communautés urbaines (7343), métropoles (7344), communautés de communes (7346), communautés de villes (7347) et
communautés d'agglomération (7348) du département 13](http://siradmin.geoapi.fr/admins?nj=734[34678]&departement=13)
- [Informations associées à la métropole d'Aix-Marseille-Provence avec la liste des communes correspondantes](http://siradmin.geoapi.fr/admins/200054807)

La base SIRENE est diffusée sur data.gouv.fr sous la forme de fichiers zip correspondant à un cliché régulier
et aux mises à jour quotidiennes.
Ce projet consiste à télécharger ces fichiers et à exposer la base sous la forme d'une API.
Ce dépôt contient le code de l'API et les scripts de chargement des fichiers SIRENE dans une base MongoDB
utilisée par l'API.

Documentation complémentaire:
  - [Site SIRENE de l'INSEE](https://www.sirene.fr/)
  - [Description complète des fichiers stocks et mises à jour mensuelles (dessin L2)](https://www.sirene.fr/static-resources/doc/dessin_L2_description_complete.pdf?version=1.14)
  - [Description complète du dessin des fichiers des mises à jour quotidiennes (dessin XL2)](https://www.sirene.fr/static-resources/doc/dessin_XL2_description_complete.pdf?version=1.14)
  - [Page SIRENE sur data.gouv.fr](https://www.data.gouv.fr/fr/datasets/base-sirene-des-entreprises-et-de-leurs-etablissements-siren-siret/)
