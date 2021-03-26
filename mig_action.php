<?php 


/**
 * 
 * 2021/03/24
 * 
 * Status : OK
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
 * ACTION : Telecharge Le dernier zip a jour de Wordpress
 * 
 * 2021/03/24
 * 
 * Status : OK
 * 
 */
if(isset($_POST['action_dl_zip'])) {
	if(!empty($_POST['action_dl_zip'])) {

		$retour_action_dl_zip = $migration->wp_download_zip();

		if($retour_action_dl_zip === TRUE) {
			$migration->retour(array('message' => 'Téléchargement de WordPress effectué.'), TRUE);
		} else {
			$migration->retour(array('message' => 'Le Zip existe deja, ou impossible d\'ecrire sur le serveur.'), FALSE);
		}
	}
}

/**
 * ACTION : Telecharge et extrait les fichiers d'un WP recuperé sur le site officiel
 * 
 * 2021/03/24
 * 
 * Status : OK
 * 
 */
if(isset($_POST['action_dl_zip_extract'])) {
	if(!empty($_POST['action_dl_zip_extract'])) {

		$retour_action_dl_zip_extract = $migration->wp_download();

		if($retour_action_dl_zip_extract === TRUE) {
			$migration->retour(array('message' => 'Téléchargement et extraction de WordPress effectué.'), TRUE);
		} else {
			$migration->retour(array('message' => 'Impossible de télécharger les fichiers de Wordpress.'), FALSE);
		}
	}
}

/**
 * ACTION : Telecharge et extrait les fichiers d'un WP recuperé sur le site officiel, si l'option install_full est coché, le WP s'installera, si la Bdd n'existe pas, il tentera de la créer
 * 
 * 2021/03/24
 * 
 * Status : install_full : En cours de test
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
				$opts['dbhost'],
				$opts['dbname'],
				$opts['uname'],
				$opts['pwd'],
				$opts['prefix']
			);
			$migration->set_var_wp($options);

			$retour_action_bdd_existe = $migration->wp_test_bdd();
			if($retour_action_bdd_existe === FALSE) {
				$migration->retour(array('message' => 'La base de données n\'existe pas.'), FALSE);
			}

			$migration->wp_download();
			$migration->wp_install_config($opts);
			$migration->wp_install_wp($opts);
			$migration->wp_htaccess();

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
 * ACTION : Permet de changer les Urls dans la config de Wordpress ainsi que dans les articles, pages et tous les contenus
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
 * ACTION : Creer un fichier htaccess
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
 * ACTION : Extrait les fichiers du zip dans le dossier courant
 */
if(isset($_POST['action_importer'])) {
	if(!empty($_POST['action_importer'])) {

		$retour_import = $migration->wp_import_file();

		if($retour_import === TRUE) {
			$migration->retour(array('message' => 'Votre zip est extrait avec succes.'), TRUE);
		} else {
			$migration->retour(array('message' => 'Impossible d\'extraire le Zip.'), FALSE);
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

/**
 * ACTION : Permet de supprimer toutes les revisions qui ne servent pas
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
 * ACTION : Telecharge et installe tous les plugins de la liste saisie
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
 * ACTION : Supprime les themes WP de base ne servant pas ( si vous utilisez un d'entre eux, ne pas effectuer cette action )
 */
if(isset($_POST['action_delete_theme'])) {
	if(!empty($_POST['action_delete_theme'])) {

		$retour_delete_theme = $migration->wp_delete_theme();

		if($retour_delete_theme === TRUE) {
			$migration->retour(array('message' => 'Themes supprimés avec succes.'), TRUE);
		} else {
			$migration->retour(array('message' => 'Impossible de supprimer les themes.'), FALSE);
		}
	}
}

/**
 * ACTION : Permet d'ajouter un utilisateur avec les droits admin dans l'instance de WP sur le serveur
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
 * ACTION : Permet de modifier le prefix des tables de WP
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
 * ACTION : Purge l'installation courante de WP  (fichiers)
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
 * ACTION : Purge l'installation courante de WP (SQL)
 */
if(isset($_POST['action_purge_sql'])) {
	if(!empty($_POST['action_purge_sql'])) {

		$retour_clean_sql 	= $migration->wp_clean_sql();

		if($retour_clean_sql === TRUE) {
			$migration->retour(array('message' => 'Installation de WP purgée.'), TRUE);
		} else {
			$migration->retour(array('message' => 'Impossible de supprimer les tables.'), FALSE);
		}	
	}
}

/**
 * ACTION : 
 */
if(isset($_POST['action_test_hash'])) {
	if(!empty($_POST['action_test_hash'])) {

		$retour_test_hash 	= $migration->wp_test_hash();

		if($retour_test_hash !== FALSE) {
			$migration->retour(array('message' => 'Nous avons analysé vos fichiers.', 'context' => $retour_test_hash), TRUE);
		} else {
			$migration->retour(array('message' => 'Nous n\'avons pas pu analyser vos fichiers.'), FALSE);
		}	
	}
}

if(isset($_POST['action_creation_hash'])) {
	if(!empty($_POST['action_creation_hash'])) {

		$retour_crea_hash 	= $migration->wp_create_hash();

		if($retour_crea_hash === TRUE) {
			$migration->retour(array('message' => 'La creation du fichier de hash a ete realisé sans probleme.'), TRUE);
		} else {
			$migration->retour(array('message' => 'Impossible de creer le fichier de hashs.'), FALSE);
		}	
	}
}

/**
 * ACTION : Test de l'existance du site de destiation
 */
if(isset($_POST['action_migration_testsite'])) {
	if(!empty($_POST['action_migration_testsite'])) {

		$retour = $migration->test_url_exist($_POST['www_url']);

		if($retour === TRUE) {
			$migration->retour(array('message' => 'Merci, ce site existe bel et bien.'), TRUE);
		} else {
			$migration->retour(array('message' => 'L\'url recherchée n\'exist pas.'), FALSE);
		}
	}
}

/**
 * ACTION : Effectue un Dump SQL, creer le zip des fichiers locaux avec le dump sql, 
 * envoie le zip et le fichier migration.php sur le serveur distant, 
 * lance un appel distant sur migration.php pour effectuer l'export du zip, 
 * l'injection des données SQL, la configuration des Urls, creer le htaccess et nettoie le dossier
 */
if(isset($_POST['action_migration'])) {
	if(!empty($_POST['action_migration'])) {

		$opts_migration = array(
			'www_url' 		=> $_POST['www_url'],
			'ftp_url' 		=> $_POST['ftp_url'],
			'user_ftp' 		=> $_POST['user_ftp'],
			'ftp_pass' 		=> $_POST['ftp_pass'],
			'ftp_folder' 	=> $_POST['ftp_folder'],
			'serveur_sql' 	=> $_POST['serveur_sql'],
			'name_sql' 		=> $_POST['name_sql'],
			'user_sql' 		=> $_POST['user_sql'],
			'pass_sql' 		=> $_POST['pass_sql'],
			'table_prefix' 	=> $table_prefix,
			'type_ftp' 		=> 'ftp'
		);

		// Test si le dossier distant existe
		$ftp_exist_retour = $migration->wp_ftp_is_existedir($opts_migration);

		if($ftp_exist_retour === FALSE){
			$migration->retour(array('message' => 'Erreur FTP : Le dossier cible n\'existe pas.'), FALSE);
		}

		// Exporte le SQL
		$retour_export_sql = $migration->wp_export_sql();

		if($retour_export_sql === FALSE) {
			$migration->retour(array('message' => 'Erreur SQL : Impossible d\'effectuer le Dump SQL.'), FALSE);
		}		

		// Exporte Les fichiers dans le Zip (La migration ne se fait plus via zip mais directement en FTP)
		/*		
		$retour_export_zip = $migration->wp_export_file();

		if($retour_export_zip === FALSE) {
			$migration->retour(array('message' => 'Erreur ZIP : Impossible d\'effectuer le ZIP.'), FALSE);
		}
		*/

		// Envoie les fichiers sur le serveur FTP distant
		$ftp_migration_retour = $migration->wp_ftp_migration($opts_migration);

		// Supprime les fichiers locaux
		$migration->wp_clean_ftp_migration();

		if($ftp_migration_retour === FALSE){
			$migration->retour(array('message' => 'Erreur FTP : Connexion impossible.'), FALSE);
		}

		// Contact le site distant pour activer la methode api_call
		$retour_migration 		= $migration->wp_migration($opts_migration);
		$retour_migration_log 	= $migration->wp_migration_log($opts_migration);

		$retour_api = json_decode($retour_migration);

		if($retour_api->success === TRUE) {
			$migration->retour(array('message' => $retour_api->data->message, 'context' => nl2br($retour_migration_log)), TRUE);
		} else {
			$migration->retour(array('message' => $retour_api->data->message, 'context' => nl2br($retour_migration_log)), FALSE);
		}
	}
}

/**
 * API : Permet d'effectuer les actions de "action_migration" sur le serveur distant
 */
if(isset($_POST['api_call'])) {
	if(!empty($_POST['api_call'])) {

		$opts = array(
			'dbname' 		=> $_POST['dbname'],
			'dbuser' 		=> $_POST['dbuser'],
			'dbpassword' 	=> $_POST['dbpassword'],
			'dbhost' 		=> $_POST['dbhost'],
			'site' 			=> $_POST['site'],
			'table_prefix' 	=> $_POST['table_prefix'],
		);
		//$migration->wp_log(json_encode($_POST));

		// extraction des fichiers
		$retour = $migration->wp_import_file();
		$migration->wp_log('Extraction des fichiers : '.($retour)? 'Ok' : 'Nok');

		// modifie le fichier wordpress avec les infos du nouveau serveur
		$retour = $migration->wp_configfile($opts);
		$migration->wp_log('Configuration du wp-config.php : '.($retour)? 'Ok' : 'Nok');

		// Assigne les variables du WP courant a la class
		$options = array(
			$opts['dbhost'],
			$opts['dbname'],
			$opts['dbuser'],
			$opts['dbpassword'],
			$opts['table_prefix']
		);
		$migration->set_var_wp($options);
		//------- 
		$migration->wp_log('Rechargement des infos du nouveau WP');

		// Test de l'existance de la base de données
		$retour_action_bdd_existe = $migration->wp_test_bdd($options);
		if($retour_action_bdd_existe === FALSE) {
			$migration->retour(array('message' => 'La base de données n\'existe pas.'), FALSE);
		}
		$migration->wp_log('Test de la base de données : '.$retour_action_bdd_existe);

		// Effectue l'importation du SQL
		$retour = $migration->wp_import_sql();
		$migration->wp_log('Importation du SQL : '.($retour)? 'Ok' : 'Nok');

		// Recupere les informations sur le WP courant
		$site_url = $migration->wp_get_info();

		// modification des urls
		$oldurl = $site_url['option_value'];
		$newurl = 'http://'.$_SERVER['SERVER_NAME'] . rtrim(dirname($_SERVER['REQUEST_URI']), '/');
		$retour = $migration->wp_url($oldurl, $newurl);
		$migration->wp_log('Modification des URLs : '.($retour)? 'Ok' : 'Nok');

		// creation du .htaccess
		$retour = $migration->wp_htaccess();
		$migration->wp_log('Creation du htaccess : '.($retour)? 'Ok' : 'Nok');

		// nettoie les fichiers sql et zip
		$retour = $migration->wp_clean_ftp_migration();
		$migration->wp_log('Netoyage des fichiers temporaire de migration : '.($retour)? 'Ok' : 'Nok');

		$migration->retour(array('message' => 'La migration a ete effectuée avec succes.'), TRUE);
	}
}
