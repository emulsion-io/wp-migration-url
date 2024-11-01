<?php 

/**
 * 
 * Met à jour le script de migration
 * 
 * Status : Ok | 2024-10-09 
 * 
 */
if(isset($_POST['action_update'])) {
	if(!empty($_POST['action_update'])) {

		$retour_update 	= $migration->wp_update();

		if($retour_update === TRUE) {
			$migration->retour(array('message' => 'La mise a jour du script s\'est effectué avec succes.'), TRUE);
		} else {
			$migration->retour(array('message' => 'Impossible d\'effectuer la mise a jour.'), FALSE);
		}
	}
}

/** 
 * ACTION : Telecharge la base de donnée du site sur le serveur
 * 
 * Status : Ok | 2024-10-09
 * 
 */
if(isset($_POST['action_dl_bdd'])) {
	if(!empty($_POST['action_dl_bdd'])) {

		$retour_dl_bdd = $migration->wp_download_bdd($_POST['sql']);

		if($retour_dl_bdd === TRUE) {
			$migration->retour(array('message' => 'La base de donnée est copiée sur votre serveur.'), TRUE);
		} else {
			$migration->retour(array('message' => 'Impossible d\'ajouter votre base de données.'), FALSE);
		}
	}
}

/** 
 * ACTION : Telecharge la base de donnée du site et l'injecte dans l'instance de WP sur le serveur
 * 
 * Status : Ok | 2024-10-09
 * 
 */
if(isset($_POST['action_dl_install_bdd'])) {
	if(!empty($_POST['action_dl_install_bdd'])) {

		$retour_dl_bdd = $migration->wp_download_install_bdd($_POST['sql']);

		if($retour_dl_bdd === TRUE) {
			$migration->retour(array('message' => 'La base de donnée est copiée et installée sur votre serveur.'), TRUE);
		} else {
			$migration->retour(array('message' => 'Impossible d\'ajouter votre base de données.'), FALSE);
		}
	}
}

/**
 * ACTION : Telecharge Le zip
 * 
 * Status : OK | 2024-10-08
 * 
 */
if(isset($_POST['action_dl_zip'])) {
	if(!empty($_POST['action_dl_zip'])) {

		$url = null;
		if( ! empty($_POST['url'])) {
			$url = $_POST['url'];
		}

		$retour_action_dl_zip = $migration->wp_download_zip($url);

		if($retour_action_dl_zip === TRUE) {
			$migration->retour(array('message' => 'Téléchargement de WordPress effectué.'), TRUE);
		} else {
			$migration->retour(array('message' => 'Le Zip existe deja, ou impossible d\'ecrire sur le serveur.'), FALSE);
		}
	}
}

/**
 * ACTION : Telecharge Le dernier zip a jour de Wordpress
 * 
 * Status : OK | 2024-10-08
 * 
 */
if(isset($_POST['action_dl_zip_wp'])) {
	if(!empty($_POST['action_dl_zip_wp'])) {

		$retour_action_dl_zip_wp = $migration->wp_download_wordpress();

		if($retour_action_dl_zip_wp === TRUE) {
			$migration->retour(array('message' => 'Téléchargement de WordPress effectué.'), TRUE);
		} else {
			$migration->retour(array('message' => 'Le Zip existe deja, ou impossible d\'ecrire sur le serveur.'), FALSE);
		}
	}
}

/**
 * ACTION : Telecharge et extrait les fichiers d'un WP recuperé sur le site officiel
 * 
 * Status : OK | 2024-10-09
 * 
 */
if(isset($_POST['action_dl_zip_extract_wp'])) {
	if(!empty($_POST['action_dl_zip_extract_wp'])) {

		$retour_action_dl_zip_extract_wp = $migration->wp_download_extract();

		if($retour_action_dl_zip_extract_wp === TRUE) {
			$migration->retour(array('message' => 'Téléchargement et extraction de WordPress effectué.'), TRUE);
		} else {
			$migration->retour(array('message' => 'Impossible de télécharger les fichiers de Wordpress.'), FALSE);
		}
	}
}

/**
 * ACTION : Telecharge et extrait les fichiers d'un WP recuperé sur le site officiel
 * 
 * Status : OK | 2024-10-09
 * 
 */
if(isset($_POST['action_dl_zip_extract'])) {
	if(!empty($_POST['action_dl_zip_extract'])) {

		$url = null;
		if( ! empty($_POST['url'])) {
			$url = $_POST['url'];
		}

		$retour_action_dl_zip_extract = $migration->wp_download_url_extract($url);

		if($retour_action_dl_zip_extract === TRUE) {
			$migration->retour(array('message' => 'Téléchargement et extraction de WordPress effectué.'), TRUE);
		} else {
			$migration->retour(array('message' => 'Impossible de télécharger les fichiers de Wordpress.'), FALSE);
		}
	}
}

/**
 * ACTION : Telecharge le theme d'un WP
 * 
 * Status : OK | 2024-10-09
 * 
 */
if(isset($_POST['action_dl_zip_theme'])) {
	if(!empty($_POST['action_dl_zip_theme'])) {

		$url = null;
		if( ! empty($_POST['url'])) {
			$url = $_POST['url'];
		}

		$retour_dl_zip_theme = $migration->wp_download_zip_theme($url);

		if($retour_dl_zip_theme === TRUE) {
			$migration->retour(array('message' => 'Téléchargement du theme effectué.'), TRUE);
		} else {
			$migration->retour(array('message' => 'Impossible de télécharger le theme.'), FALSE);
		}
	}
}

/**
 * ACTION : Telecharge et extrait les fichiers du theme d'un WP
 * 
 * Status : OK | 2024-10-09
 * 
 */
if(isset($_POST['action_dl_extract_zip_theme'])) {
	if(!empty($_POST['action_dl_extract_zip_theme'])) {

		$url = null;
		if( ! empty($_POST['url'])) {
			$url = $_POST['url'];
		}

		$retour_dl_extract_zip_theme = $migration->wp_download_extract_zip_theme($url);

		if($retour_dl_extract_zip_theme === TRUE) {
			$migration->retour(array('message' => 'Téléchargement et extraction du theme effectué.'), TRUE);
		} else {
			$migration->retour(array('message' => 'Impossible de télécharger le theme.'), FALSE);
		}
	}
}

/**
 * ACTION : Telecharge le plugin d'un WP
 * 
 * Status :  | 2024-10-09
 * 
 */
if(isset($_POST['action_dl_zip_plugin'])) {
	if(!empty($_POST['action_dl_zip_plugin'])) {

		$url = null;
		if( ! empty($_POST['url'])) {
			$url = $_POST['url'];
		}

		$retour_dl_zip_plugin = $migration->wp_download_zip_plugin($url);

		if($retour_dl_zip_plugin === TRUE) {
			$migration->retour(array('message' => 'Téléchargement du plugin effectué.'), TRUE);
		} else {
			$migration->retour(array('message' => 'Impossible de télécharger le plugin.'), FALSE);
		}
	}
}

/**
 * ACTION : Telecharge et extrait les fichiers du plugin d'un WP
 * 
 * Status :  | 2024-10-09
 * 
 */
if(isset($_POST['action_dl_extract_zip_plugin'])) {
	if(!empty($_POST['action_dl_extract_zip_plugin'])) {

		$url = null;
		if( ! empty($_POST['url'])) {
			$url = $_POST['url'];
		}

		$retour_dl_extract_zip_plugin = $migration->wp_download_extract_zip_plugin($url);

		if($retour_dl_extract_zip_plugin === TRUE) {
			$migration->retour(array('message' => 'Téléchargement et extraction du plugin effectué.'), TRUE);
		} else {
			$migration->retour(array('message' => 'Impossible de télécharger le plugin.'), FALSE);
		}
	}
}

/**
 * ACTION : Supprime les thèmes WP 
 * 
 * Status : Ok | 2024-10-08
 * 
 */
if(isset($_POST['action_delete_theme_choix'])) {
	if(!empty($_POST['action_delete_theme_choix'])) {

		$retour_delete_theme_choix = $migration->wp_delete_theme_choix($_POST['themes']);

		if($retour_delete_theme_choix === TRUE) {
			$migration->retour(array('message' => 'Thèmes supprimés avec succes.'), TRUE);
		} else {
			$migration->retour(array('message' => 'Impossible de supprimer les thèmes.'), FALSE);
		}
	}
}

/**
 * ACTION : Clone les thèmes WP 
 * 
 * Status : Ok | 2024-10-08
 * 
 */
if(isset($_POST['action_clone_theme_choix'])) {
	if(!empty($_POST['action_clone_theme_choix'])) {

		$retour_clone_theme_choix = $migration->wp_clone_theme_choix($_POST['themes']);

		if($retour_clone_theme_choix === TRUE) {
			$migration->retour(array('message' => 'thèmes clonés avec succes.'), TRUE);
		} else {
			$migration->retour(array('message' => 'Impossible de cloner les thèmes.'), FALSE);
		}
	}
}

/**
 * ACTION : Permet de modifier le fichier wp-config.php en mode dev
 * 
 * Status : Ok | 2024-10-08
 * 
 */
if(isset($_POST['action_change_wpconfig_dev'])) {
	if(!empty($_POST['action_change_wpconfig_dev'])) {

		$retour_change_wpconfig_dev = $migration->wp_change_debug($_POST['debug'], $_POST['debug_display'], $_POST['debug_log']);

		if($retour_change_wpconfig_dev === TRUE) {
			$migration->retour(array('message' => 'Le fichier wp-config.php a ete modifie avec succes.'), TRUE);
		} else {
			$migration->retour(array('message' => 'Impossible de modifier le fichier wp-config.php.'), FALSE);
		}
	}
}

/**
 * ACTION : Creer un fichier htaccess
 * 
 * Status : OK | 2024-10-08
 * 
 */
if(isset($_POST['action_htaccess'])) {
	if(!empty($_POST['action_htaccess'])) {

		$retour_htaccess = $migration->wp_htaccess();

		if($retour_htaccess === TRUE) {
			$migration->retour(array('message' => 'Fichier .htaccess crée avec succes.'), TRUE);
		} else {
			$migration->retour(array('message' => 'Impossible de creer le .htaccess.'), FALSE);
		}
	}
}

/**
 * ACTION : Permet de modifier le fichier wp-config.php
 * 
 * Status : Ok | 2024-10-08
 * 
 */
if(isset($_POST['action_change_wpconfig'])) {
	if(!empty($_POST['action_change_wpconfig'])) {

		$retour_change_wpconfig = $migration->wp_change_wpconfig($_POST['db_name'], $_POST['db_user'], $_POST['db_password'], $_POST['db_host'], );

		if($retour_change_wpconfig === TRUE) {
			$migration->retour(array('message' => 'Le fichier wp-config.php a ete modifie avec succes.'), TRUE);
		} else {
			$migration->retour(array('message' => 'Impossible de modifier le fichier wp-config.php.'), FALSE);
		}
	}
}

/**
 * ACTION : Permet de changer les Urls dans la config de Wordpress ainsi que dans les articles, pages et tous les contenus
 * 
 * Status : Ok | 2024-10-08
 * 
 */
if(isset($_POST['action_change_url'])) {
	if(!empty($_POST['action_change_url'])) {

		$oldurl = $_POST['old'];
		$newurl = $_POST['new'];

		$retour_url = $migration->wp_url($oldurl, $newurl);

		if($retour_url === TRUE) {
			$migration->retour(array('message' => 'Les urls sont modifiées avec succes.'), TRUE);
		} else {
			$migration->retour(array('message' => 'Impossible de modifier les urls.'), FALSE);
		}
	}
}

/**
 * ACTION : Permet de supprimer toutes les revisions qui ne servent pas
 * 
 * Status : Ok | 2024-10-08
 * 
 */
if(isset($_POST['action_clean_revision'])) {
	if(!empty($_POST['action_clean_revision'])) {

		$retour_clean_revision = $migration->wp_sql_clean_revision();

		if($retour_clean_revision === TRUE) {
			$migration->retour(array('message' => 'Revision supprimée avec succes.'), TRUE);
		} else {
			$migration->retour(array('message' => 'Impossible de supprimer les revisions.'), FALSE);
		}
	}
}

/**
 * Efface tous les commentaires qui n'ont pas ete validé
 * 
 * Status : Ok | 2024-10-08
 * 
 */
if(isset($_POST['action_clean_spam'])) {
	if(!empty($_POST['action_clean_spam'])) {

		$retour_clean_spam = $migration->wp_sql_clean_spam();

		if($retour_clean_spam === TRUE) {
			$migration->retour(array('message' => 'Spam supprimé avec succes.'), TRUE);
		} else {
			$migration->retour(array('message' => 'Impossible de supprimer les spams.'), FALSE);
		}
	}
}

/**
 * ACTION : Supprime les thèmes WP de base ne servant pas ( si vous utilisez un d'entre eux, ne pas effectuer cette action )
 * 
 * Status : Ok | 2024-10-08
 * 
 */
if(isset($_POST['action_delete_theme'])) {
	if(!empty($_POST['action_delete_theme'])) {

		$retour_delete_theme = $migration->wp_delete_theme();

		if($retour_delete_theme === TRUE) {
			$migration->retour(array('message' => 'Thèmes supprimés avec succes.'), TRUE);
		} else {
			$migration->retour(array('message' => 'Impossible de supprimer les thèmes.'), FALSE);
		}
	}
}

/**
 * ACTION : Permet d'ajouter un utilisateur avec les droits admin dans l'instance de WP sur le serveur
 * 
 * Status : Ok | 2024-10-08
 * 
 */
if(isset($_POST['action_add_user'])) {
	if(!empty($_POST['action_add_user'])) {

		$retour_add_user = $migration->wp_add_user($_POST['user'], $_POST['pass']);

		if($retour_add_user === TRUE) {
			$migration->retour(array('message' => 'Utilisateur ajouté avec succes.'), TRUE);
		} else {
			$migration->retour(array('message' => 'Impossible d\'ajouter un utilisateur.'), FALSE);
		}	
	}
}

/**
 * ACTION : Test de connexion a la base de donnée
 * 
 * Status : OK | 2024-11-01
 * 
 */
if(isset($_POST['action_testconnexion'])) {
	if(!empty($_POST['action_testconnexion'])) {

		$wp_test_bdd = $migration->wp_test_bdd();

		if($wp_test_bdd === TRUE) {
			$migration->retour(array('message' => 'Connexion a la base de donnée reussie.'), TRUE);
		} else {
			$migration->retour(array('message' => 'Impossible de se connecter a la base de donnée.'), FALSE);
		}	
	}
}

/**
 * ACTION : Purge l'installation courante de WP  (fichiers)
 * 
 * Status : OK | 2024-10-08
 * 
 */
if(isset($_POST['action_purge'])) {
	if(!empty($_POST['action_purge'])) {

		$retour_clean_files = $migration->wp_clean_files();

		if($retour_clean_files === TRUE) {
			$migration->retour(array('message' => 'Installation de WP purgée.'), TRUE);
		} else {
			$migration->retour(array('message' => 'Impossible de supprimer les fichiers.'), FALSE);
		}	
	}
}

/**
 * ACTION : Telecharge et installe tous les plugins de la liste saisie
 * 
 * Status : OK | 2024-10-08
 * 
 */
if(isset($_POST['action_plug_install'])) {
	if(!empty($_POST['action_plug_install'])) {

		$retour_plug_install = $migration->wp_install_plugins($_POST['plug_install_liste']);

		if($retour_plug_install === TRUE) {
			$migration->retour(array('message' => 'Plugins installés avec succes.'), TRUE);
		} else {
			$migration->retour(array('message' => 'Impossible d\'installer les plugins.'), FALSE);
		}
	}
}

/**
 * ACTION : Permet de modifier le prefix des tables de WP
 * 
 * Status : Ok | 2024-10-09
 * 
 */
if(isset($_POST['action_prefix_edit'])) {
	if(!empty($_POST['action_prefix_edit'])) {

		$retour_prefix_edit = $migration->wp_rename_prefix($_POST['prefix_edit']);

		if($retour_prefix_edit === TRUE) {
			$migration->retour(array('message' => 'Tables modifiées avec succes.'), TRUE);
		} else {
			$migration->retour(array('message' => 'Impossible de modifier le prefix des tables.'), FALSE);
		}
	}
}

/**
 * ACTION : Telecharge et extrait les fichiers d'un WP recuperé sur le site officiel, si l'option install_full est coché, le WP s'installera, si la Bdd n'existe pas, il tentera de la créer
 * 
 * Status : Ok | 2024-10-09
 * 
 */
if(isset($_POST['action_dl'])) {
	if(!empty($_POST['action_dl'])) {
		
		if($_POST['install_full'] == "false") {

			$retour_action_dl = $migration->wp_download();

			if($retour_action_dl === TRUE) {
				$migration->retour(array('message' => 'Téléchargement de WordPress effectué.'), TRUE);
			} else {
				$migration->retour(array('message' => 'Impossible de télécharger les fichiers de Wordpress.'), FALSE);
			}

		} elseif($_POST['install_full'] == "true") {

			$opts['prefix']         = $_POST['prefix'];
			$opts['dbname']         = $_POST['dbname'];
			$opts['uname']          = $_POST['uname'];
			$opts['pwd']            = $_POST['pwd'];
			$opts['dbhost']         = $_POST['dbhost'];
			$opts['weblog_title']   = $_POST['weblog_title'];
			$opts['user_login']     = $_POST['user_login'];
			$opts['admin_email']    = $_POST['admin_email'];
			$opts['admin_password'] = $_POST['admin_password'];
			$opts['debug']          = ($_POST['debug'] == 'true')? 1 : 0 ;
			$opts['debug_display']  = ($_POST['debug_display'] == 'true')? 1 : 0 ;
			$opts['debug_log']      = ($_POST['debug_log'] == 'true')? 1 : 0 ;
			$opts['blog_public']    = ($_POST['blog_public'] == 'true')? 1 : 0 ;

			// assignation des variables de connexion pour effectuer un test si la bdd existe
			$options = array(
				'dbhost'       => $opts['dbhost'],
				'dbname'       => $opts['dbname'],
				'dbuser'       => $opts['uname'],
				'dbpassword'   => $opts['pwd'],
				'table_prefix' => $opts['prefix']
			);
			$migration->set_var_wp($options);

			$retour_action_bdd_existe = $migration->wp_test_bdd();
			if($retour_action_bdd_existe === FALSE) {
				$migration->retour(array('message' => 'La base de données n\'existe pas.'), FALSE);
			}

			$retour = $migration->wp_download_extract();
			if($retour === FALSE) {
				$migration->retour(array('message' => 'Impossible de télécharger les fichiers de Wordpress.'), FALSE);
			}

			$retour = $migration->wp_install_config($options);
			if($retour === FALSE) {
				$migration->retour(array('message' => 'Impossible de creer le fichier de configuration.'), FALSE);
			}

			$retour = $migration->wp_install_wp($opts);
			if($retour === FALSE) {
				$migration->retour(array('message' => 'Impossible d\'installer Wordpress.'), FALSE);
			}

			$retour = $migration->wp_htaccess();
			if($retour === FALSE) {
				$migration->retour(array('message' => 'Impossible de creer le fichier .htaccess.'), FALSE);
			}

			$migration->retour(array('message' => 'Installation complete effectuée.'), TRUE);
		} else {
			$migration->retour(array('message' => 'Action inconnue'), FALSE);
		}
	}
}

/********************************************************************************************/
/********************************************************************************************/
/********************************************************************************************/
/********************************************************************************************/
/********************************* EN COURS DE TEST *****************************************/
/********************************************************************************************/
/********************************************************************************************/
/********************************************************************************************/
/********************************************************************************************/

/**
 * ACTION : Creer un Zip des fichiers courant
 */
if(isset($_POST['action_exporter'])) {
	if(!empty($_POST['action_exporter'])) {

		$retour_export = $migration->wp_export_file();

		$context = "L'export a ete effectue avec succes.<p><a href=\"/$migration->_file_destination\">Telecharger le zip des fichers</a></p>";

		if($retour_export === TRUE) {
			$migration->retour(array('message' => 'Creation du Zip de vos fichiers effectué avec succes.', 'context' => $context), TRUE);
		} else {
			$migration->retour(array('message' => 'Impossible de creer le Zip.'), FALSE);
		}
	}
}

/**
 * ACTION : effectue un Dump sql de la base de donnée du WP courant dans un fichier .sql
 */
if(isset($_POST['action_exporter_sql'])) {
	if(!empty($_POST['action_exporter_sql'])) {

		$retour_export_sql = $migration->wp_export_sql();

		$context = "L'export a ete effectue avec succes.<p><a href=\"/$migration->_file_sql\">Telecharger le Dump</a></p>";

		if($retour_export_sql === TRUE) {
			$migration->retour(array('message' => 'Dump SQL effectué avec succes.', 'context' => $context), TRUE);
		} else {
			$migration->retour(array('message' => 'Impossible d\'effectuer le Dump SQL.'), FALSE);
		}
	}
}

/**
 * ACTION : Effectue un import du fichier SQL dans la base de donnée utilisé par l'instance de WP installé sur le serveur
 */
if(isset($_POST['action_importer_sql'])) {
	if(!empty($_POST['action_importer_sql'])) {

		$retour_import_sql = $migration->wp_import_sql();

		if($retour_import_sql === TRUE) {
			$migration->retour(array('message' => 'La base de donnée est injecté sur votre serveur.'), TRUE);
		} else {
			$migration->retour(array('message' => 'Impossible d\'ajouter votre base de données.'), FALSE);
		}
	}
}
