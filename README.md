# wp-migration

[![Generic badge](https://img.shields.io/badge/Working-Yes-green.svg)](#)  


## Utilisation

Pour utiliser le tools sur votre serveur, utilisez `migration.php` dans `/dist/`

Ou depuis la racine de votre serveur, utilisez la commande suivante :

```
wget https://raw.githubusercontent.com/emulsion-io/wp-migration-url/refs/heads/master/dist/migration.php
```

et accedez à votre serveur via l'url suivante :

```
https://votre-site.com/migration.php
```

## Suivre les changements

Tous les changements notables de ce projet sont listé ici : [CHANGELOG.md](CHANGELOG.md)


## Ajout de vos propres fichiers

### Déclaration de vos fichiers / themes / plugins dans un json

Utiliser le fichier `migration.exemple.json` pour ajouter vos propres installations de Wordpress, plugins et thèmes.

Rennomer le fichier en `migration.json` et placer le à la racine de votre serveur à côté de `migration.php`.

```json 
{
   "wp": [
      {
         "nom": "Mon template WP #1",
         "fichier": "https://site/fichier.zip",
         "sql": "https://site/fichier.zip"
      }
   ],
   "plugins": [
      {
         "nom": "Mon plugin #1",
         "fichier": "https://site/fichier.zip"
      }
   ],
   "themes": [
      {
         "nom": "Mon thème #1",
         "fichier": "https://site/fichier.zip"
      }
   ]
}
```

### Déclaration de vos fichiers / themes / plugins dans le script

#### Ajout de vos propres fichiers d'installation

Utilisez le tableau `$zips_wp` dans `migration.php` pour ajouter vos propres installations de Wordpress.

migration.php ligne 49

```php
$zips_wp = [
   [
      'nom' => 'Mon template WP #1',
      'fichier' => 'https://site/fichier.zip',
      'sql' => 'https://site/fichier.zip'
   ]
];
```

Le Zip de l'installation de Wordpress ne doit pas etre dans un sous dossier.

ex : `mon-wp.zip` doit contenir les fichiers de Wordpress.

Le fichier SQL doit etre un fichier `.sql` contenu dans un fichier `.zip`.

### Ajout de vos propres fichiers de plugins

Utilisez le tableau `$zips_plugins` dans `migration.php` pour ajouter vos propres plugins.

migration.php ligne 60

```php
$zips_plugins = [
   [
      'nom' => 'Mon plugin #1',
      'fichier' => 'https://site/fichier.zip'
   ]
];
```

Le Zip du plugin doit contenir le dossier du plugin. 

ex : `mon-plugin.zip` doit contenir `mon-plugin/` avec les fichiers du plugin.

### Ajout de vos propres fichiers de thèmes

Utilisez le tableau `$zips_themes` dans `migration.php` pour ajouter vos propres thèmes.

migration.php ligne 71

```php
$zips_themes = [
   [
      'nom' => 'Mon thème #1',
      'fichier' => 'https://site/fichier.zip'
   ]
];
```

Le Zip du thème doit contenir le dossier du thème.

ex : `mon-theme.zip` doit contenir `mon-theme/` avec les fichiers du thème.

## Mais sinon, il fait quoi ton script ?

  * Le script permet de télécharger et d'extraire des fichiers ZIP depuis des URLS contenant, par exemple, des installations de Wordpress, des plugins ou des thèmes.
  * Il permet aussi de télécharger des fichiers SQL depuis des URLS contenant des sauvegardes de bases de données.

  * Télécharger et extraire un Wordpress avec possibilité de l'installer
  * Editer wp-config.php - Base de données
  * Editer wp-config.php - Options de développement
  * Modifier les Urls - Dans la BDD
  * Modifier les Urls - Génération des Rqs SQL
  * Creer le fichier .htaccess
  * Effacer toutes les revisions de votre Wordpress
  * Effacer tous les commentaires non validés (Spam)
  * Installer les plugins de votre choix
  * Supprime les thèmes par defaut de Wordpress
  * Supprime des thèmes (selection)
  * Clone des thèmes
  * Ajouter un administrateur à votre installation
  * Modifier le prefix des tables
  * Supprimer tous les fichiers de Wordpress

### Preview

![Preview](https://raw.githubusercontent.com/emulsion-io/wp-migration-url/master/preview.png)

## Developpement

Vous pouvez editer les fichiers dans `/src/` et les merger avec `compile.php`

Pour merger les fichiers, utilisez la commande suivante :

```
php compile.php
```

Possibilité d'effectuer directement la compilation avec la tache vscode : `Ctrl + Shift + B`