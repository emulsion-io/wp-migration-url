# Changelog

Tous les changements notables de ce projet seront documentés dans ce fichier.

Le format est basé sur [Keep a Changelog](https://keepachangelog.com/fr/1.1.0/), et ce projet adhère à [SemVer](https://semver.org/lang/fr/) pour la gestion des versions.

## [2.7.6] - 2024-11-01
### Fixed
- Chargement des fichiers d'installation personnalisés., verifiez que le fichier est bien présent sur le serveur distant.
- $this->set_var_wp(); qui n'était pas appelé dans la méthode `wp_download_install_bdd()`.

## [2.7.5] - 2024-10-10
### Ajouté
- Ajout de la possibilité de déclarer vos propres fichiers d'installation dans un fichier json.
