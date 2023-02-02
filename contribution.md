### Git Fork

Connectez-vous à votre comte [Github](https://github.com/) puis rendez-vous sur le repository du projet: [ToDo-Co](https://github.com/fabienVernieres/ToDo-Co)

Faîtes un Fork du projet, cela va cloner le repository sur votre compte Github.

Récupérez l'adresse du dépôt (ex: https://github.com/votrecompte/ToDo-Co.git).

En local, rendez-vous dans votre dossier de travail et faîtes un `git clone https://github.com/votrecompte/ToDo-Co.git`

Pour éviter les conflits lors de vos futures pull request, exécutez la commande:
`git remote add upstream https://github.com/fabienVernieres/ToDo-Co.git`

Ainsi vous pouvez importer la dernière version du projet avant de pouvoir travailler dessus en faisant un:
`git pull upstream main`

Pour envoyer votre contribution au projet, faîtes:
`git push origin main`

Ensuite dans votre espace utilisateur [Github](https://github.com), faîtes un `New Pull Request`. Ainsi vos modifications seront placées en attente de validation, et l'administrateur pourra effectuer un `Merge pull request` (fusionner les versions).

### Qualité du code

Votre code doit respecter les normes PSR1 et PSR4.

En bref:

- Les fichiers DOIVENT utiliser uniquement les balises <?php et <?= .

- Les fichiers DOIVENT utiliser uniquement du code PHP en UTF-8 sans BOM.

- Les fichiers DEVRAIENT déclarer des symboles (classes, fonctions, constantes, etc.) OU causer un effet de bord (générer une sortie, modifier les paramètres .ini, etc.) mais ne DEVRAIENT PAS faire les deux.

- Les Namespaces et classes DOIVENT suivre un PSR “autoloading” [[PSR-0](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md), [PSR-4](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md)].

- Les noms de classes DOIVENT être déclarés en UpperCamelCase.

- Les constantes de classes DOIVENT être déclarées en majuscules avec un séparateur underscore.

- Les noms de Méthodes DOIVENT être déclarés en camelCase.

- Un nom de classe totalement qualifié a la forme suivante :
  \<NamespaceName>(\<SubNamespaceNames>)\*\<ClassName>

- Un nom de classe totalement qualifié DOIT avoir un namespace principal, également appelé “vendor namespace”.

- Un nom de classe totalement qualifié PEUT avoir un ou plusieurs nom de "sous namespaces".

- Un nom de classe totalement qualifié DOIT se terminer par un nom de classe.

- Les underscores n'ont pas de signification dans le nom de classe totalement qualifié.

- Les caractères alphabétiques dans le nom de classe totalement qualifié PEUVENT être toute combinaison de majuscules et minuscules.

- Tous les noms de classes DOIVENT être référencés dans un modèle sensible à la casse.

### Recommandations supplémentaires:

- Les noms de propriétés doivent être déclarés en camelCase().

- Commentez vos classes, méthodes et tout ce qui peut aider à mieux comprendre votre code.

- Typer vos variables, paramètres et retours de méthodes.
