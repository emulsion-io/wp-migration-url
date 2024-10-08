# wp-migration

[![Generic badge](https://img.shields.io/badge/Working-Yes-green.svg)](#)  


## Utilisation

Pour utiliser le tools sur votre serveur, utilisez `migration.php` dans `/dist/`

Ou depuis la racine de votre serveur, utilisez la commande suivante :

```
wget https://github.com/emulsion-io/wp-migration-url/releases/download/v2.7/migration.php
```

et accedez à votre serveur via l'url suivante :

```
https://votre-site.com/migration.php
```

## Ajout de vos propres installation WP

Utilisez le tableau `$zips_wp` dans `migration.php` pour ajouter vos propres installations de Wordpress.

migration.php ligne 42

```php
/**
 * Vos zips d'installation de Wordpress
 * 
 * $zips_wp = [
 * 	[
 * 		'nom' => 'Mon template WP #1',
 * 		'fichier' => 'https://site/fichier.zip',
 * 		'sql' => 'https://site/fichier.zip'
 * 	],
 * 	[
 * 		'nom' => 'Mon template WP #2',
 * 		'fichier' => 'https://site/fichier.zip',
 * 		'sql' => 'https://site/fichier.zip'
 * 	]
 * ];
 * 
 */
$zips_wp = [
   [
      'nom' => 'Mon template WP #1',
      'fichier' => 'https://site/fichier.zip',
      'sql' => 'https://site/fichier.zip'
   ]
];
```

## Developpement

Vous pouvez editer les fichiers dans `/src/` et les merger avec `compile.php`

Pour merger les fichiers, utilisez la commande suivante :

```
php compile.php
```
