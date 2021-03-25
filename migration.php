<?php

/**
 * @author Fabrice Simonet
 * @link http://emulsion.io
 *
 * @version 2.6
*/

/**	
 * 
 * 2021 03 25
 * 
 * Refonte du script.
 * 
 */

/**
 * Copyright (c) 2021 Fabrice Simonet
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, 
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, 
 * DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, 
 * ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

//ini_set("memory_limit", "-1");
//set_time_limit(0);

/**
 * Variable de status d'execution du script
 */
$retour_url            = FALSE;
$retour_migration      = FALSE;
$retour_migration_api  = FALSE;
$retour_migration_log  = FALSE;
$retour_export         = FALSE;
$retour_import         = FALSE;
$retour_export_sql     = FALSE;
$retour_import_sql     = FALSE;
$retour_htaccess       = FALSE;
$retour_dl             = FALSE;
$retour_dl_full        = FALSE;
$retour_clean_revision = FALSE;
$retour_clean_spam     = FALSE;
$retour_plug_install   = FALSE;
$retour_delete_theme   = FALSE;
$retour_add_user       = FALSE;

$migration = new Wp_Migration();

$update = $migration->wp_check_update();

/**
 * Si un fichier wp-config.php existe, le script comprend que WP est deja installé
 */
if(file_exists('wp-config.php')) {

	define( 'WP_INSTALLING', true );

	include ('wp-config.php');

	// Assigne les variables du WP courant a la class
	$options = array(
		DB_HOST,
		DB_NAME,
		DB_USER,
		DB_PASSWORD,
		$table_prefix
	);

	$migration->set_var_wp($options);

	// Recupere les informations sur le WP courant
	$site_url = $migration->wp_get_info();
	// Annonce 
	$wp_exist = TRUE;

} else {
	$site_url['option_value'] = '';

	$wp_exist = FALSE;
}

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
			$migration->retour(array('message' => 'Fichier Htaccess crée avec succes.'), TRUE);
		} else {
			$migration->retour(array('message' => 'Impossible de creer le Htaccess.'), FALSE);
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
 * ACTION : Supprime les themes WP de base ne servant pas ( si vous utilisez un d'autre eux, ne pas effectuer cette action )
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
 * ACTION : Pruge l'installation courante de WP  (fichiers)
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
 * ACTION : Pruge l'installation courante de WP (SQL)
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
 * ACTION : Telecharge et extrait les fichiers d'un WP recuperé sur le site officiel, si l'option install_full est coché, le WP s'installera, si la Bdd n'existe pas, il tentera de la créer
 */
if(isset($_POST['action_dl'])) {
	if(!empty($_POST['action_dl'])) {

		if($_POST['install_full'] == "false") {

			$retour_action_dl = $migration->wp_download();

			if($retour_action_dl === TRUE) {
				$migration->retour(array('message' => 'Telechargement de WordPress effectué.'), TRUE);
			} else {
				$migration->retour(array('message' => 'Impossible de telecharger Wordpress.'), FALSE);
			}

		} elseif($_POST['install_full'] == "true") {

			$opts['prefix'] 		= $_POST['prefix'];
			$opts['debug'] 			= ($_POST['debug'] == 'true')? 1 : 0 ;
			$opts['debug_display'] 	= ($_POST['debug_display'] == 'true')? 1 : 0 ;
			$opts['debug_log'] 		= ($_POST['debug_log'] == 'true')? 1 : 0 ;
			$opts['dbname'] 		= $_POST['dbname'];
			$opts['uname'] 			= $_POST['uname'];
			$opts['pwd'] 			= $_POST['pwd'];
			$opts['dbhost']	 		= $_POST['dbhost'];
			$opts['weblog_title'] 	= $_POST['weblog_title'];
			$opts['user_login'] 	= $_POST['user_login'];
			$opts['admin_email'] 	= $_POST['admin_email'];
			$opts['blog_public'] 	= ($_POST['blog_public'] == 'true')? 1 : 0 ;
			$opts['admin_password'] = $_POST['admin_password'];

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

?>

<!doctype html>
<html lang="en">
	<head>
		<!-- Required meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<title>Migration Wordpress Easy</title>

		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6" crossorigin="anonymous">		<link href='https://fonts.googleapis.com/css?family=Raleway' rel='stylesheet' type='text/css'>

		<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
		<script src="//cdn.jsdelivr.net/npm/sweetalert2@10"></script>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/js/bootstrap.bundle.min.js" integrity="sha384-JEW9xMcG8R+pH31jmWH6WWP0WintQrMb4s7ZOdauHnUtxwoG2vI5DkLtS3qm9Ekf" crossorigin="anonymous"></script>

		<style type="text/css">
			body {
				color: #a2a2a2;
				background-color: #171717;
			}

			.menushow { cursor: pointer; }
			div.col-md-12 > h3:first-child {
				border-style: solid;
    			border-width: 1px;
    			border-color: #ffccbc;
    			padding: 3px;
    			background-color: #fbe9e7;
    			border-radius: 5px;
			}
		</style>
	</head>
	<body>
		<div class="container">
			<header class="row">
				<div class="col-md-12">
					<div class="jumbotron">
						<h1>ToolBox Wordpress</h1>
						<p>La boite a outils pour Wordpress</p>
					</div>
				</div>
			</header>

			<article class="row">
				<div class="col-md-12">
					<div class="panel panel-warning">
						<div class="panel-heading"> 
							<h3 class="panel-title">Important</h3> 
						</div>
						<div class="alert alert-secondary" role="alert">
						Pensez a supprimer le fichier migration.php de votre installation Wordpress apres avoir effectué vos modifications.
						</div>
					</div>
				</div>
			</article>

			<article class="row">
				<div class="col-md-12">
					<div class="panel panel-warning">
						<div class="panel-heading"> 
							<h3 class="panel-title">Information serveur</h3> 
						</div>
						<div class="panel-body">
							<ul>
								<li>Droit sur le dosier courant : <?php echo substr(sprintf('%o', fileperms('.')), -4); ?></li>
								<li>Fonction exec() <?php echo (function_exists('exec'))? " is enabled" : " is disabled"; ?></li>
								<li>Fonction system() <?php echo (function_exists('system'))? " is enabled" : " is disabled"; ?></li>
								<li>Memoire allouée : <?php echo $migration->get_memory_limit(); ?></li>
							</ul>
						</div>
					</div>
				</div>
			</article>

			<article class="row">
				<div class="col-md-12">
					<div class="panel panel-warning">
						<div class="panel-heading"> 
							<h3 class="panel-title">Information sur ce script</h3> 
						</div>
						<div class="panel-body">
							<ul>
								<li>Votre version : <?php echo $update['version_courante']; ?></li>
								<li>Derniere version disponnible : <?php echo $update['version_enligne']; ?></li>
								<?php if($update['maj_dipso'] == TRUE): ?>
								<li>
									<form id="action_update" method="post">
										<button type="submit" id="go_action_update" class="btn btn-default">Effecuer la mise a jour</button>
									</form>
									<script>
										$( "#action_update" ).submit(function( event ) {
											var donnees = {
												'action_update'	: 'ok'
											}
											sendform('action_update', donnees, 'Effecuer la mise a jour du script');
											event.preventDefault();
											$(document).ajaxSuccess(function() {
												setTimeout(function(){ window.location.reload(); }, 2000);
											});
										});
									</script>
								</li>
								<?php endif; ?>
							</ul>
						</div>
					</div>
				</div>
			</article>			

			<h2>Processus de migration automatique d'un Wordpress d'un serveur A vers un serveur B en FTP</h2>

			<article class="row">
				<div class="col-md-12">
					<h3><span class="menushow glyphicon glyphicon-menu-down" aria-hidden="true"></span> Migration du Wordpress sur un autre serveur</h3>
					<div style="display:none;">
						<div class="panel panel-info">
							<div class="panel-heading"> 
								<h3 class="panel-title">Ce que fait cet assistant</h3> 
							</div>
							<div class="panel-body">
							    <ul>
									<li>Dump de la base de donnnées</li>
									<li>Creation de l'archive des fichiers Wordpress et du Dump SQL</li>
									<li>Envoie des fichiers sur le serveur FTP distant</li>
									<li>Extrait les fichiers ainsi que le fichier d'api distant</li>
									<li>Installe le fichier de config.php sur le serveur distant</li>
									<li>Installe la base de données sur le nouveau serveur distant</li>
									<li>Modifie les URLs sur le serveur distant</li>
							    </ul>
							</div>
						</div>

						<form id="action_migration_testsite" method="post">
							<h3>Url du site de destination</h3>
							<div class="form-group">
								<label for="www_url">Url ou sera installé le futur site apres migration</label>
								<input type="text" class="form-control" id="www_url" name="www_url" placeholder="http://" value="">
								<span class="help-block">Exemple : http://test.com/web/clients/monclient</span>
							</div>
							<div class="form-group">
								<button id="go_action_migration_testsite" type="submit" class="btn btn-default">Tester la validité de l'url</button>
							</div>
						</form>
						<script>
							$( "#action_migration_testsite" ).submit(function( event ) {
								var donnees = {
									action_migration_testsite	: 'ok',
									www_url						: $('#www_url').val(),
								}
								sendform('action_migration_testsite', donnees, 'Test de l\'url du site de destination.');
								event.preventDefault();
							});
						</script>

						<form id="action_migration" style="display:none;" method="post">
							<h3>Information du FTP distant</h3>
							<div class="form-group">
								<label for="ftp_url">Url du serveur FTP</label>
								<input type="text" class="form-control" id="ftp_url" name="ftp_url" placeholder="" value="">
							</div>
							<div class="form-group">
								<label for="user_ftp">Utilisateur FTP</label>
								<input type="text" class="form-control" id="user_ftp" name="user_ftp" placeholder="" value="">
							</div>
							<div class="form-group">
								<label for="ftp_pass">Mot de passe FTP</label>
								<input type="text" class="form-control" id="ftp_pass" name="ftp_pass" placeholder="" value="">
							</div>
							<div class="form-group">
								<label for="ftp_folder">Dossier d'installation ( doit exister )</label>
								<input type="text" class="form-control" id="ftp_folder" name="ftp_folder" placeholder="" value="">
								<span class="help-block">Exemple : /web/clients/monclient</span>
							</div>

							<h3>Information de la base de données du serveur cible</h3>

							<div class="form-group">
								<label for="serveur_sql">Serveur MySQL</label>
								<input type="text" class="form-control" id="serveur_sql" name="serveur_sql" placeholder="localhost" value="">
							</div>
							<div class="form-group">
								<label for="name_sql">Nom de la base de données MySQL</label>
								<input type="text" class="form-control" id="name_sql" name="name_sql" placeholder="" value="">
							</div>					
							<div class="form-group">
								<label for="user_sql">Utilisateur MySQL</label>
								<input type="text" class="form-control" id="user_sql" name="user_sql" placeholder="" value="">
							</div>
							<div class="form-group">
								<label for="pass_sql">Mot de passe MySQL</label>
								<input type="text" class="form-control" id="pass_sql" name="pass_sql" placeholder="" value="">
							</div>
							<?php if($wp_exist == TRUE) : ?>
								<div class="form-group">
									<button id="go_action_migration" type="submit" class="btn btn-default">Lancer la migration</button>
								</div>
							<?php else: ?>
							<div class="panel panel-warning">
								<div class="panel-heading"> 
									<h3 class="panel-title">Information</h3> 
								</div>
								<div class="panel-body">
									<ul>
										<li>Wordpress n'est pas installé sur ce serveur.</li>
									</ul>
								</div>
							</div>					
							<?php endif; ?>
						</form>
						<script>
							$( "#action_migration" ).submit(function( event ) {
								var donnees = {
									action_migration	: 'ok',
									www_url				: $('#www_url').val(),
									ftp_url				: $('#ftp_url').val(),
									user_ftp			: $('#user_ftp').val(),
									ftp_pass			: $('#ftp_pass').val(),
									ftp_folder			: $('#ftp_folder').val(),
									serveur_sql			: $('#serveur_sql').val(),
									name_sql			: $('#name_sql').val(),
									user_sql			: $('#user_sql').val(),
									pass_sql			: $('#pass_sql').val()
								}
								sendform('action_migration', donnees, 'Migration du Wordpress sur un autre serveur');
								event.preventDefault();
							});
						</script>
					</div>
				</div>
			</article>

			<h2>Télécharger et/ou installer une nouvelle version de Wordpress sur votre serveur</h2>

			<article class="row">
				<div class="col-md-12">
					<h3><span class="menushow glyphicon glyphicon-menu-down" aria-hidden="true"></span> Telecharger et extraire un Wordpress avec possibilité de l'installer</h3>
					<div style="display:none;">
						<div class="panel panel-info">
							<div class="panel-heading"> 
								<h3 class="panel-title">Ce que fait cet assistant</h3> 
							</div>
							<div class="panel-body">
								<ul>
									<li>Telecharge, extrait et instale un Wordpress dans la derniere version du site officiel</li>
								</ul>
							</div>
						</div>

						<form id="action_dl" method="post">
							<div class="checkbox">
								<label>
									<input type="checkbox" id="install_full" name="install_full" value="1" onclick="$('#install_full_div').toggle();"> Installer le Wordpress sur le serveur
								</label>
							</div>

							<div id="install_full_div" style="display:none;">
								<h3>Information de la base de données</h3>

								<div class="form-group">
									<label for="dbhost">Serveur MySQL</label>
									<input type="text" class="form-control" id="dbhost" name="dbhost" placeholder="localhost" value="">
								</div>
								<div class="form-group">
									<label for="dbname">Nom de la base de données MySQL</label>
									<input type="text" class="form-control" id="dbname" name="dbname" placeholder="" value="">
								</div>					
								<div class="form-group">
									<label for="uname">Utilisateur MySQL</label>
									<input type="text" class="form-control" id="uname" name="uname" placeholder="" value="">
								</div>
								<div class="form-group">
									<label for="pwd">Mot de passe MySQL</label>
									<input type="text" class="form-control" id="pwd" name="pwd" placeholder="" value="">
								</div>

								<h3>Information Wordpress</h3>

								<div class="form-group">
									<label for="prefix">prefix</label>
									<input type="text" class="form-control" id="prefix" name="prefix" placeholder="wp_" value="wp_">
								</div>

								<div class="form-group">
									<label for="weblog_title">Titre du blog</label>
									<input type="text" class="form-control" id="weblog_title" name="weblog_title" placeholder="" value="">
								</div>
								<div class="form-group">
									<label for="user_login">Pseudo</label>
									<input type="text" class="form-control" id="user_login" name="user_login" placeholder="" value="">
								</div>
								<div class="form-group">
									<label for="admin_email">Email</label>
									<input type="text" class="form-control" id="admin_email" name="admin_email" placeholder="" value="">
								</div>
								<div class="form-group">
									<label for="admin_password">Mot de passe</label>
									<input type="text" class="form-control" id="admin_password" name="admin_password" placeholder="" value="">
								</div>

								<div class="checkbox">
									<label>
										<input type="checkbox" id="debug" name="debug" value="1"> debug
									</label>
								</div>
								<div class="checkbox">
									<label>
										<input type="checkbox" id="debug_display" name="debug_display" value="1"> debug_display
									</label>
								</div>
								<div class="checkbox">
									<label>
										<input type="checkbox" id="debug_log" name="debug_log" value="1"> debug_log
									</label>
								</div>					
								<div class="checkbox">
									<label>
										<input type="checkbox" id="blog_public" name="blog_public" value="1"> Indexer le site
									</label>
								</div>
							</div>

							<?php if($wp_exist == TRUE) : ?>
							<div class="panel panel-warning">
								<div class="panel-heading"> 
									<h3 class="panel-title">Information</h3> 
								</div>
								<div class="panel-body">
									<ul>
										<li>Attention une version de Wordpress est deja installé sur le serveur</li>
									</ul>
								</div>
							</div>
							<?php endif; ?>	

							<div class="form-group">
								<button id="go_action_dl" type="submit" class="btn btn-default">Lancer la procedure</button>
							</div>
						</form>
						<script>
							$( "#action_dl" ).submit(function( event ) {
								var donnees = {
									action_dl		: 'ok',
									install_full	: $('#install_full').is(':checked'),
									dbhost			: $('#dbhost').val(),
									dbname			: $('#dbname').val(),
									uname			: $('#uname').val(),
									pwd				: $('#pwd').val(),
									prefix			: $('#prefix').val(),
									weblog_title	: $('#weblog_title').val(),
									user_login		: $('#user_login').val(),
									admin_email		: $('#admin_email').val(),
									admin_password	: $('#admin_password').val(),
									debug			: $('#debug').is(':checked'),
									debug_display	: $('#debug_display').is(':checked'),
									debug_log		: $('#debug_log').is(':checked'),
									blog_public		: $('#blog_public').is(':checked')
								}
								sendform('action_dl', donnees, 'Telecharge, extrait et install un Wordpress');
								event.preventDefault();
							});
						</script>
					</div>
				</div>
			</article>

	        <h2>Export</h2>

			<article class="row">
				<div class="col-md-12">
					<h3><span class="menushow glyphicon glyphicon-menu-down" aria-hidden="true"></span> Créer une archive des fichiers du repertoire courant</h3>
					<div style="display:none;">
						<div class="panel panel-info">
							<div class="panel-heading"> 
								<h3 class="panel-title">Ce que fait cet assistant</h3> 
							</div>
							<div class="panel-body">
								<ul>
									<li>Creer un Zip de tout le repertoire courant</li>
								</ul>
							</div>
						</div>

						<form id="action_exporter" method="post">
							<?php if($wp_exist == TRUE) : ?>
							<div class="form-group">
								<button id="go_action_exporter" type="submit" class="btn btn-default">Creer le Zip</button>
							</div>
							<?php else: ?>
							<div class="panel panel-warning">
								<div class="panel-heading"> 
									<h3 class="panel-title">Information</h3> 
								</div>
								<div class="panel-body">
								    <ul>
								    	<li>Wordpress n'est pas installé sur le serveur</li>
								    </ul>
								</div>
							</div>
							<?php endif; ?>
						</form>
						<script>
							$( "#action_exporter" ).submit(function( event ) {
								var donnees = {
									'action_exporter'	: 'ok'
								}
								sendform('action_exporter', donnees, 'Creation d\'une archive de vos fichiers');
								event.preventDefault();
							});
						</script>

						<?php if(file_exists($migration->_file_destination) && $retour_export == FALSE) : ?>
						<div class="panel panel-success">
							<div class="panel-heading"> 
								<h3 class="panel-title">Information</h3> 
							</div>
							<div class="panel-body">
							    <ul>
							    	<li>Un Zip contenant vos fichiers existe deja, telecharger l'existant ? <a href="/<?=$migration->_file_destination;?>">Telecharger le Zip existant</a></li>
							    </ul>
							</div>
						</div>
						<?php endif; ?>
					</div>
	            </div>
	        </article>

	        <article class="row">
	           	<div class="col-md-12">
	           	    <h3><span class="menushow glyphicon glyphicon-menu-down" aria-hidden="true"></span> Exporter la base de données de votre installation Wordpress</h3>
	           	    <div style="display:none;">
						<div class="panel panel-info">
							<div class="panel-heading"> 
								<h3 class="panel-title">Ce que fait cet assistant</h3> 
							</div>
							<div class="panel-body">
							    <ul>
							    	<li>Extrait la base données de votre installation courante</li>
							    </ul>
							</div>
						</div>

						<form id="action_exporter_sql" method="post">
							<?php if($wp_exist == TRUE) : ?>
							<div class="form-group">
								<button type="submit" class="btn btn-default">Lancer le dump SQL</button>
							</div>
							<?php else: ?>
							<div class="panel panel-warning">
								<div class="panel-heading"> 
									<h3 class="panel-title">Information</h3> 
								</div>
								<div class="panel-body">
								    <ul>
								    	<li>Wordpress n'est pas installé sur le serveur</li>
								    </ul>
								</div>
							</div>
							<?php endif; ?>
						</form>
						<script>
							$( "#action_exporter_sql" ).submit(function( event ) {
								var donnees = {
									'action_exporter_sql'	: 'ok'
								}
								sendform('action_exporter_sql', donnees, 'Exporte la base de données');
								event.preventDefault();
							});
						</script>

						<?php if(file_exists($migration->_file_sql) && $retour_export_sql == FALSE) : ?>
						<div class="panel panel-success">
							<div class="panel-heading"> 
								<h3 class="panel-title">Information</h3> 
							</div>
							<div class="panel-body">
							    <ul>
							    	<li>Un Dump existe deja, telecharger l'existant ? <a href="/<?=$migration->_file_sql;?>">Telecharger le Dump existant</a></li>
							    </ul>
							</div>
						</div>
						<?php endif; ?>
					</div>
	            </div>
	        </article>

			<h2>Import</h2>

	        <article class="row">
	           	<div class="col-md-12">
	           	    <h3><span class="menushow glyphicon glyphicon-menu-down" aria-hidden="true"></span> Importation de votre Zip</h3>
	           	    <div style="display:none;">
						<div class="panel panel-info">
							<div class="panel-heading"> 
								<h3 class="panel-title">Ce que fait cet assistant</h3> 
							</div>
							<div class="panel-body">
							    <ul>
							    	<li>Importe et extrait l'ensemble de vos fichiers dans le dossier courant</li>
							    </ul>
							</div>
						</div>

						<form id="action_importer" method="post">
							<?php if(file_exists($migration->_file_destination)): ?>
							<div class="form-group">
								<button id="go_action_importer" type="submit" class="btn btn-default">Importer et extraire les fichiers</button>
							</div>
							<?php endif; ?>
						</form>
						<script>
							$( "#action_importer" ).submit(function( event ) {
								var donnees = {
									action_importer	: 'ok',
								}
								sendform('action_importer', donnees, 'Importation de votre Zip');
								event.preventDefault();
							});
						</script>

						<?php if(!file_exists($migration->_file_destination)): ?>
						<div class="panel panel-warning">
							<div class="panel-heading"> 
								<h3 class="panel-title">Information</h3> 
							</div>
							<div class="panel-body">
							    <ul>
							    	<li>Le fichier <?=$migration->_file_destination;?> n'est pas present sur le serveur</li>
							    </ul>
							</div>
						</div>
						<?php endif; ?>
					</div>
	            </div>
	        </article>
			
	        <article class="row">
	           	<div class="col-md-12">
	           	    <h3><span class="menushow glyphicon glyphicon-menu-down" aria-hidden="true"></span> Importation de votre Base de données</h3>
					<div style="display:none;">
						<div class="panel panel-info">
							<div class="panel-heading"> 
								<h3 class="panel-title">Ce que fait cet assistant</h3> 
							</div>
							<div class="panel-body">
							    <ul>
							    	<li>Permet d'importer les données de votre dump dans base de données sur votre serveur</li>
							    	<li>La base de données indiquée dans le fichier de configuration doit exister</li>
							    </ul>
							</div>
						</div>

						<form id="action_importer_sql" method="post">
							<?php if(file_exists($migration->_file_sql)): ?>
							<div class="form-group">
								<button id="go_action_importer_sql" type="submit" class="btn btn-default">Importer la base de données</button>
							</div>
							<?php endif; ?>
						</form>
						<script>
							$( "#action_importer_sql" ).submit(function( event ) {
								var donnees = {
									action_importer_sql	: 'ok',
								}
								sendform('action_importer_sql', donnees, 'Importation de votre Base de données');
								event.preventDefault();
							});
						</script>

						<?php if(!file_exists($migration->_file_sql)): ?>
						<div class="panel panel-warning">
							<div class="panel-heading"> 
								<h3 class="panel-title">Information</h3> 
							</div>
							<div class="panel-body">
							    <ul>
							    	<li>Le fichier <?=$migration->_file_sql;?> n'est pas present sur le serveur</li>
							    </ul>
							</div>
						</div>
						<?php endif; ?>
					</div>
	            </div>
	        </article>

			<h2>Outils</h2>

			<article class="row">
				<div class="col-md-12">
					<h3><span class="menushow glyphicon glyphicon-menu-down" aria-hidden="true"></span> Modifier les Urls de votre installation Wordpress</h3>
					<div style="display:none;">
						<div class="panel panel-info">
							<div class="panel-heading"> 
								<h3 class="panel-title">Ce que fait cet assistant</h3> 
							</div>
							<div class="panel-body">
								<ul>
									<li>Modifie les URLs dans la table wp_option, home et siteurl sont affectés</li>
									<li>Modifie les URLs dans la table wp_posts, guid et post_content sont affectés</li>
									<li>Modifie les URLs dans la table wp_postmeta, toutes les meta_value auront la nouvelle URL</li>
								</ul>
							</div>
						</div>

						<form id="action_change_url" method="post">
							<div class="form-group">
								<label for="old">Ancienne URL</label>
								<input type="text" class="form-control" id="old" name="old" placeholder="Ancienne URL sans / a la fin" value="<?php echo $site_url['option_value']; ?>">
							</div>
							<div class="form-group">
								<label for="new">Nouvelle URL</label>
								<input type="text" class="form-control" id="new" name="new" placeholder="Nouvelle URL sans / a la fin" value="http://<?php echo $_SERVER['SERVER_NAME'] . rtrim(dirname($_SERVER['REQUEST_URI']), '/'); ?>">
							</div>
							<?php if($wp_exist == TRUE) : ?>
							<div class="form-group">
								<button type="submit" id="go_action_change_url" class="btn btn-default">Mettre a jour</button>
							</div>
							<?php else: ?>
							<div class="panel panel-warning">
								<div class="panel-heading"> 
									<h3 class="panel-title">Information</h3> 
								</div>
								<div class="panel-body">
									<ul>
										<li>Wordpress n'est pas installé sur ce serveur.</li>
									</ul>
								</div>
							</div>					
							<?php endif; ?>
						</form>
						<script>
							$( "#action_change_url" ).submit(function( event ) {
								var donnees = {
									'action_change_url'	: 'ok',
									'old' 				: $('#old').val(),
									'new' 				: $('#new').val(),
								}
								sendform('action_change_url', donnees, 'Ecriture des nouvelles Urls');
								event.preventDefault();
							});
						</script>
					</div>
				</div>
			</article>

	        <article class="row">
	           	<div class="col-md-12">
	           	    <h3><span class="menushow glyphicon glyphicon-menu-down" aria-hidden="true"></span> Creer le fichier HTACCESS wordpress</h3>
	           	    <div style="display:none;">
						<div class="panel panel-info">
							<div class="panel-heading"> 
								<h3 class="panel-title">Ce que fait cet assistant</h3> 
							</div>
							<div class="panel-body">
							    <ul>
							    	<li>Creer le fichier Htaccess avec la configuration de votre serveur automatiquement</li>
							    	<li>Ajoute des regles de securité pour Wordpress</li>
							    </ul>
							</div>
						</div>

						<form id="action_htaccess" method="post">
							<div class="form-group">
								<button id="go_action_htaccess" type="submit" class="btn btn-default">Creer le fichier HTaccess</button>
							</div>
						</form>
						<script>
							$( "#action_htaccess" ).submit(function( event ) {
								var donnees = {
									'action_htaccess'	: 'ok'
								}
								sendform('action_htaccess', donnees, 'Creer le fichier HTACCESS');
								event.preventDefault();
							});
						</script>
					</div>
				</div>
			</article>

			<article class="row">
				<div class="col-md-12">
					<h3><span class="menushow glyphicon glyphicon-menu-down" aria-hidden="true"></span> Effacer toutes les revisions de votre Wordpress</h3>
					<div style="display:none;">
						<div class="panel panel-info">
							<div class="panel-heading"> 
								<h3 class="panel-title">Ce que fait cet assistant</h3> 
							</div>
							<div class="panel-body">
								<ul>
									<li>Efface les revisions des articles, des pages et de tous les contenus</li>
								</ul>
							</div>
						</div>

						<form id="action_clean_revision" method="post">
							<?php if($wp_exist == TRUE) : ?>
							<div class="form-group">
								<button type="submit" id="go_action_clean_revision" class="btn btn-default">Effacer les revisions</button>
							</div>
							<?php else: ?>
							<div class="panel panel-warning">
								<div class="panel-heading"> 
									<h3 class="panel-title">Information</h3> 
								</div>
								<div class="panel-body">
								    <ul>
								    	<li>Wordpress n'est pas installé sur le serveur</li>
								    </ul>
								</div>
							</div>					
							<?php endif; ?>
						</form>
						<script>
							$( "#action_clean_revision" ).submit(function( event ) {
								var donnees = {
									'action_clean_revision'	: 'ok'
								}
								sendform('action_clean_revision', donnees, 'Efface toutes les revisions');
								event.preventDefault();
							});
						</script>
					</div>
	            </div>
	        </article>

	        <article class="row">
	           	<div class="col-md-12">
	           	    <h3><span class="menushow glyphicon glyphicon-menu-down" aria-hidden="true"></span> Effacer tous les commentaires non validés ( spam )</h3>
					<div style="display:none;">
						<div class="panel panel-info">
							<div class="panel-heading"> 
								<h3 class="panel-title">Ce que fait cet assistant</h3> 
							</div>
							<div class="panel-body">
							    <ul>
							    	<li>Efface tous les commentaires que vous n'avez pas validés</li>
							    	<li>Permet de supprimer tres simplement une vague de spam</li>
							    </ul>
							</div>
						</div>

						<form id="action_clean_spam" method="post">
							<?php if($wp_exist == TRUE) : ?>
							<div class="form-group">
								<button id="go_action_clean_spam" type="submit" class="btn btn-default">Effacer les commentaires non validés</button>
							</div>
							<?php else: ?>
							<div class="panel panel-warning">
								<div class="panel-heading"> 
									<h3 class="panel-title">Information</h3> 
								</div>
								<div class="panel-body">
								    <ul>
								    	<li>Wordpress n'est pas installé sur le serveur</li>
								    </ul>
								</div>
							</div>
							<?php endif; ?>
						</form>
						<script>
							$( "#action_clean_spam" ).submit(function( event ) {
								var donnees = {
									'action_clean_spam'	: 'ok'
								}
								sendform('action_clean_spam', donnees, 'Efface tous les commentaires non validés');
								event.preventDefault();
							});
						</script>
					</div>
	            </div>
	        </article>

	        <article class="row">
	           	<div class="col-md-12">
	           	    <h3><span class="menushow glyphicon glyphicon-menu-down" aria-hidden="true"></span> Installer les plugins de votre choix</h3>
	           	    <div style="display:none;">
						<div class="panel panel-info">
							<div class="panel-heading"> 
								<h3 class="panel-title">Ce que fait cet assistant</h3> 
							</div>
							<div class="panel-body">
							    <ul>
							    	<li>Instale tous les plugins public de votre choix</li>
							    	<li>vous retrouverez tous les plugins Wordpress public sur ce site : https://fr.wordpress.org/plugins/</li>
							    	<li>Merci de séparer les noms par une virgule</li>
							    </ul>
							</div>
						</div>

						<form id="action_plug_install" method="post">
							<div class="form-group">
								<label for="plug_install_liste">Liste des plugins</label>
								<input type="text" class="form-control" id="plug_install_liste" name="plug_install_liste" placeholder="Merci de séparer les noms par une virgule" value="">
							</div>

							<?php if($wp_exist == TRUE) : ?>
							<div class="form-group">
								<button id="go_action_plug_install" type="submit" class="btn btn-default">Installer les plugins</button>
							</div>
							<?php else: ?>
							<div class="panel panel-warning">
								<div class="panel-heading"> 
									<h3 class="panel-title">Information</h3> 
								</div>
								<div class="panel-body">
								    <ul>
								    	<li>Wordpress n'est pas installé sur le serveur</li>
								    </ul>
								</div>
							</div>
							<?php endif; ?>	
						</form>
						<script>
							$( "#action_plug_install" ).submit(function( event ) {
								var donnees = {
									action_plug_install	: 'ok',
									plug_install_liste 	: $('#plug_install_liste').val(),
								}
								sendform('action_plug_install', donnees, 'Installe les plugins');
								event.preventDefault();
							});
						</script>
					</div>
	            </div>
	        </article>

	         <article class="row">
	           	<div class="col-md-12">
	           	    <h3><span class="menushow glyphicon glyphicon-menu-down" aria-hidden="true"></span> Supprime les themes installé par defaut de Wordpress</h3>
	           	    <div style="display:none;">
						<div class="panel panel-info">
							<div class="panel-heading"> 
								<h3 class="panel-title">Ce que fait cet assistant</h3> 
							</div>
							<div class="panel-body">
							    <ul>
							    	<li>Supprime l'ensemble des themes suivant : </li>
							    	<li>twentyfourteen</li>
							    	<li>twentythirteen</li>
							    	<li>twentytwelve</li>
							    	<li>twentyeleven</li>
							    	<li>twentyten</li>
							    </ul>
							</div>
						</div>

						<form id="action_delete_theme" method="post">
							<?php if($wp_exist == TRUE) : ?>
							<div class="form-group">
								<button id="go_action_delete_theme" type="submit" class="btn btn-default">Supprime les themes</button>
							</div>
							<?php else: ?>
							<div class="panel panel-warning">
								<div class="panel-heading"> 
									<h3 class="panel-title">Information</h3> 
								</div>
								<div class="panel-body">
								    <ul>
								    	<li>Wordpress n'est pas installé sur le serveur</li>
								    </ul>
								</div>
							</div>
							<?php endif; ?>	
						</form>
						<script>
							$( "#action_delete_theme" ).submit(function( event ) {
								var donnees = {
									'action_delete_theme'	: 'ok'
								}
								sendform('action_delete_theme', donnees, 'Supprime les themes defaut de Wordpress');
								event.preventDefault();
							});
						</script>
					</div>	
	            </div>
	        </article>       

			<article class="row">
				<div class="col-md-12">
					<h3><span class="menushow glyphicon glyphicon-menu-down" aria-hidden="true"></span> Ajouter un administrateur a votre installation</h3>
					<div style="display:none;">
						<div class="panel panel-info">
							<div class="panel-heading"> 
								<h3 class="panel-title">Ce que fait cet assistant</h3> 
							</div>
							<div class="panel-body">
								<ul>
									<li>Ajouter un Super Admin dans la base de donnees de votre installation courante</li>
								</ul>
							</div>
						</div>

						<form id="action_add_user" method="post">
							<div class="form-group">
								<label for="user">Pseudo</label>
								<input type="text" class="form-control" id="user" name="user" placeholder="" value="">
							</div>
							<div class="form-group">
								<label for="pass">Mot de passe</label>
								<input type="password" class="form-control" id="pass" name="pass" placeholder="" value="">
							</div>
							<?php if($wp_exist == TRUE) : ?>
							<div class="form-group">
								<button id="go_action_add_user" type="submit" class="btn btn-default">Ajouter l'utilisateur</button>
							</div>
							<?php else: ?>
							<div class="panel panel-warning">
								<div class="panel-heading"> 
									<h3 class="panel-title">Information</h3> 
								</div>
								<div class="panel-body">
								    <ul>
								    	<li>Wordpress n'est pas installé sur le serveur</li>
								    </ul>
								</div>
							</div>
							<?php endif; ?>	
						</form>
						<script>
							$( "#action_add_user" ).submit(function( event ) {
								var donnees = {
									action_add_user	: 'ok',
									user 			: $('#user').val(),
									pass 			: $('#pass').val(),
								}
								sendform('action_add_user', donnees, 'Ajouter un utilisateur');
								event.preventDefault();
							});
						</script>
					</div>
				</div>
			</article>

			<article class="row">
				<div class="col-md-12">
					<h3><span class="menushow glyphicon glyphicon-menu-down" aria-hidden="true"></span> Modifier le prefix des tables</h3>
					<div style="display:none;">
						<div class="panel panel-info">
							<div class="panel-heading"> 
								<h3 class="panel-title">Ce que fait cet assistant</h3> 
							</div>
							<div class="panel-body">
								<ul>
									<li>Permet de modifier le prefix wp_ des tables par un de votre choix</li>
								</ul>
							</div>
						</div>

						<form id="action_prefix_edit" method="post">
							<div class="form-group">
								<label for="prefix_edit">Prefix</label>
								<input type="text" class="form-control" id="prefix_edit" name="prefix_edit" placeholder="wp_" value="">
							</div>
							<?php if($wp_exist == TRUE) : ?>
							<div class="form-group">
								<button id="go_action_prefix_edit" type="submit" class="btn btn-default">Modifier le prefix des tables</button>
							</div>
							<?php else: ?>
							<div class="panel panel-warning">
								<div class="panel-heading"> 
									<h3 class="panel-title">Information</h3> 
								</div>
								<div class="panel-body">
								    <ul>
								    	<li>Wordpress n'est pas installé sur le serveur</li>
								    </ul>
								</div>
							</div>
							<?php endif; ?>	
						</form>
						<script>
							$( "#action_prefix_edit" ).submit(function( event ) {
								var donnees = {
									action_prefix_edit	: 'ok',
									prefix_edit 		: $('#prefix_edit').val(),
								}
								sendform('action_prefix_edit', donnees, 'Modifier le prefix des tables');
								event.preventDefault();
							});
						</script>
					</div>	
				</div>
			</article>

			<article class="row">
				<div class="col-md-12">
					<h3><span class="menushow glyphicon glyphicon-menu-down" aria-hidden="true"></span> Supprimer toutes les traces de fichiers de Wordpress</h3>
					<div style="display:none;">
						<div class="panel panel-info">
							<div class="panel-heading"> 
								<h3 class="panel-title">Ce que fait cet assistant</h3> 
							</div>
							<div class="panel-body">
								<ul>
									<li>Efface tous les fichiers correspondant a l'installation d'un Wordpress</li>
								</ul>
							</div>
						</div>

						<form id="action_purge" method="post">
							<div class="form-group">
								<button id="go_action_purge" type="submit" class="btn btn-default">Effacer les fichiers</button>
							</div>
						</form>
						<script>
							$( "#action_purge" ).submit(function( event ) {
								var donnees = {
									action_purge	: 'ok',
								}
								sendform('action_purge', donnees, 'Purger Fichiers Wordpress');
								event.preventDefault();
							});
						</script>
					</div>
				</div>
			</article>			

			<article class="row">
				<div class="col-md-12">
					<h3><span class="menushow glyphicon glyphicon-menu-down" aria-hidden="true"></span> Supprime toutes les tables de la base de données de Wordpress</h3>
					<div style="display:none;">
						<div class="panel panel-info">
							<div class="panel-heading"> 
								<h3 class="panel-title">Ce que fait cet assistant</h3> 
							</div>
							<div class="panel-body">
								<ul>
									<li>Supprime toutes les tables de Wordpress de votre base de données</li>
								</ul>
							</div>
						</div>

						<form id="action_purge_sql" method="post">
							<div class="form-group">
								<button id="go_action_purge_sql" type="submit" class="btn btn-default">Effacer les tables</button>
							</div>
						</form>
						<script>
							$( "#action_purge_sql" ).submit(function( event ) {
								var donnees = {
									action_purge_sql	: 'ok',
								}
								sendform('action_purge_sql', donnees, 'Purger SQL Wordpress');
								event.preventDefault();
							});
						</script>
					</div>
				</div>
			</article>

			<article class="row">
				<div class="col-md-12">
					<h3><span class="menushow glyphicon glyphicon-menu-down" aria-hidden="true"></span> Creation du Hashs des fichiers</h3>
					<div style="display:none;">
						<div class="panel panel-info">
							<div class="panel-heading"> 
								<h3 class="panel-title">Ce que fait cet assistant</h3> 
							</div>
							<div class="panel-body">
								<ul>
									<li>Creer un fichier contenant le hash de tous les fichiers presents dans le dossier courant</li>
									<li>Ce fichier permet de comparer l'etat des fichiers entre le moment de la creation du hash et celle de la comparaison</li>
									<li>Ceci permet de savoir si des fichiers ont été modifié</li>
								</ul>
							</div>
						</div>

						<form id="action_creation_hash" method="post">
							<div class="form-group">
								<button id="go_action_creation_hash" type="submit" class="btn btn-default">Creer le hashs</button>
							</div>
						</form>
						<script>
							$( "#action_creation_hash" ).submit(function( event ) {
								var donnees = {
									action_creation_hash	: 'ok',
								}
								sendform('action_creation_hash', donnees, 'Creation du Hashs des fichiers');
								event.preventDefault();
							});
						</script>
					</div>
				</div>
			</article>

			<article class="row">
				<div class="col-md-12">
					<h3><span class="menushow glyphicon glyphicon-menu-down" aria-hidden="true"></span> Comparaison du Hashs des fichiers</h3>
					<div style="display:none;">
						<div class="panel panel-info">
							<div class="panel-heading"> 
								<h3 class="panel-title">Ce que fait cet assistant</h3> 
							</div>
							<div class="panel-body">
								<ul>
									<li>Compare les fichiers actuels au precedent hash réaslisé</li>
								</ul>
							</div>
						</div>

						<form id="action_test_hash" method="post">
							<?php if(file_exists('migration-hash.php')) : ?>
							<div class="form-group">
								<button id="go_action_test_hash" type="submit" class="btn btn-default">Tester le hashs</button>
							</div>
							<?php else: ?>
							<div class="panel panel-warning">
								<div class="panel-heading"> 
									<h3 class="panel-title">Information</h3> 
								</div>
								<div class="panel-body">
								    <ul>
								    	<li>Le fichier migration-hash.php n'existe pas sur le serveur</li>
								    </ul>
								</div>
							</div>
							<?php endif; ?>
						</form>
						<script>
							$( "#action_test_hash" ).submit(function( event ) {
								var donnees = {
									action_test_hash	: 'ok',
								}
								sendform('action_test_hash', donnees, 'Teste du Hashs des fichiers');
								event.preventDefault();
							});
						</script>
					</div>	
				</div>
			</article>			

			<footer class="row">
				<div class="col-md-12">Developpé par Fabrice Simonet || https://emulsion.io</div>
			</footer>
		</div>
		<script>
			$( ".menushow" ).click(function() {
				if( $( this ).hasClass( "glyphicon-menu-down" ) ) {
					$( this ).removeClass('glyphicon-menu-down').addClass('glyphicon-menu-up');
				} else {
					$( this ).removeClass('glyphicon-menu-up').addClass('glyphicon-menu-down');
				}

				$( this ).parent().next().toggle();
			});
			function sendform(id, donnees, title) {
				$("#go_"+id).button('loading');
				swal({
					title: title,
					text: "Ete-vous sur de vouloir effectuer cette action ?",
					type: "info",
					showCancelButton: true,
					closeOnConfirm: false,
					showLoaderOnConfirm: true,
				},
				function(){
					$.ajax({
						url: "migration.php",
						type : 'post',
						data : donnees,
						dataType: 'json',
						success: function(retour){
							$("#go_"+id).button('reset');
							
							if(retour.success == true)
							{
								swal("Good job!", retour.data.message, "success");

								// condition particuliaire d'actions a realiser en fonction des forms
								
								//-- Affiche la zone d'info pour la migration
								if(id == 'action_migration_testsite') {
									$('#action_migration').show();
								}


							} else {
								swal("Error!", retour.data.message, "error");
							}

							if (typeof retour.data.context != 'undefined') {
								$( "#"+id ).prepend( '<div class="alert alert-success" role="alert">' + retour.data.context + '</div>' );
							}
						}, 
						timeout: function(){
							$("#go_"+id).button('reset');
							swal("Timeout!", "Le temps d'attente est trop long, demande expiré, renter votre chance.", "error");
						},
						error: function(){
							$("#go_"+id).button('reset');
							swal("Error!", "Une erreur est intervenu dans le traitement de la requête, renter votre chance.", "error");
						}
					});
				});
			}
		</script>
   </body>
</html>

<?php 

Class Wp_Migration {

	var $_wp_lang,
		$_wp_api,
		$_wp_dir_core,
		$_version,
		$_file_destination,
		$_file_sql,
		$_file_log,
		$_file_log_ftp,
		$_current_rep,
		$_fileswp;

	var $_dbhost,
		$_dbname,
		$_dbuser,
		$_dbpassword,
		$_table_prefix;

	/**
	 * @var string $this->_wp_lang 			Langue Wordpress
	 * @var string $this->_wp_api 			Url de l'api Wordpress
	 * @var string $this->_wp_dir_core 		Dossier temporaire de copie des fichier WP
	 * @var string $this->_file_destination Nom du fichier Zip créé lors de la sauvegarde
	 * @var string $this->_file_sql 		Nom du fichier sql créé lors du Cump SQL
	 * @var string $this->_file_log 		Nom du fichier de log créé lors de la migration sur le serveur distant
	 */
	public function __construct() {

		$this->_version 			= '2.6.0';
		$this->_wp_lang 			= 'fr_FR';
		$this->_wp_api 				= 'http://api.wordpress.org/core/version-check/1.7/?locale='.$this->_wp_lang;
		$this->_wp_dir_core 		= 'core/';
		$this->_file_destination 	= 'migration_file.zip';
		$this->_file_sql 			= 'migration_bdd.sql';
		$this->_file_log 			= 'logfile.log';
		$this->_file_log_ftp 		= 'ftp.log';
		$this->_current_rep 		= getcwd();
		$this->_fileswp				= array(
			'wp-activate.php',
			'wp-blog-header.php',
			'wp-comments-post.php',
			'wp-config.php',
			'wp-cron.php',
			'wp-links-opml.php',
			'wp-load.php',
			'wp-login.php',
			'wp-mail.php',
			'wp-settings.php',
			'wp-signup.php',
			'wp-trackback.php',
			'xmlrpc.php',
			'index.php',
			'wp-admin',
			'wp-content',
			'wp-includes',
			'.htaccess'
		);
	}

	/**
	 * Assigne les variable du Wordpress courant a la class
	 *
	 * @param mixed[] $options Array Information de connexion a la base de données local 
	 */
	public function set_var_wp($options) {

		$this->_dbhost 			= $options[0];
		$this->_dbname 			= $options[1];
		$this->_dbuser 			= $options[2];
		$this->_dbpassword 		= $options[3];
		$this->_table_prefix 	= $options[4];

		Config::write('db.host', $this->_dbhost);
		Config::write('db.basename', $this->_dbname);
		Config::write('db.user', $this->_dbuser);
		Config::write('db.password', $this->_dbpassword);		
	}

	/**
	 * Recupere les informations sur le WP courant
	 */
	public function wp_get_info(){

	    $bdd = Bdd::getInstance();

		$req = $bdd->dbh->prepare('SELECT option_value FROM '.$this->_table_prefix.'options WHERE option_name = \'siteurl\';');
		$req->execute();

		return $req->fetch();
	}

	/**
	 * Ecrit les log dans le fichier $this->_file_log sur le serveur
	 *
	 * @param string $text 		Ligne de text du log
	 *
	 * @return bool 			retourne true ou false
	 */
	public function wp_log($text){

		if(file_exists($this->_file_log)) {
			$old = file_get_contents($this->_file_log);
		} else {
			$old = "";
		}

		$var = $old.date('Y/m/d h:i:s')." : ".$text."\n";
		file_put_contents($this->_file_log, $var);

		return TRUE;
	}

	/**
	 * Test si le dossier donné par l'utilisateur existe reelement
	 *
	 * @param mixed[] $opts Array informations de connexion au FTP distant
	 */
	public function wp_ftp_is_existedir($opts){	
		$folder_exists = is_dir("ftp://".$opts['user_ftp'].":".$opts['ftp_pass']."@".$opts['ftp_url']."".$opts['ftp_folder']);
		//$folder_exists = is_dir("ftp://".$opts['user_ftp'].":".$opts['ftp_pass']."@".$opts['ftp_url']."".rtrim($opts['ftp_folder'], '/'));
		return $folder_exists;
	}

	/**
	 * Envoie les fichiers zip et migration sur le serveur distant en FTP
	 *
	 * @param mixed[] $opts Array informations de connexion au FTP distant
	 */
	public function wp_ftp_migration($opts, $type_ftp = 'ftp'){

		$file 			= $this->_file_destination;
		$remote_file 	= $this->_file_destination;

		if($type_ftp === 'sftp'){
			$connection = ssh2_connect($opts['ftp_url'], 22);
			ssh2_auth_password($connection, $opts['user_ftp'], $opts['ftp_pass']);

			ssh2_scp_send($connection, 'migration.php', rtrim($opts['ftp_folder'], '/').'/migration.php', 0644);
			ssh2_scp_send($connection, $this->_file_sql, rtrim($opts['ftp_folder'], '/').'/'.$this->_file_sql, 0644);
			ssh2_scp_send($connection, $file, rtrim($opts['ftp_folder'], '/').'/'.$remote_file, 0644);

			return TRUE;
		}

		if($type_ftp === 'ftp'){
			$conn_id = ftp_connect($opts['ftp_url']);
		}elseif ($type_ftp === 'ftps') {
			$conn_id = ftp_ssl_connect($opts['ftp_url']);
		}

		if($conn_id == FALSE){

			return FALSE;
		}

		$login_result = ftp_login($conn_id, $opts['user_ftp'], $opts['ftp_pass']);
		if($login_result == FALSE){

			return FALSE;
		}

		// Envoie de tous les fichiers en FTP
		$this->ftp_putAll($conn_id, '.', rtrim($opts['ftp_folder'], '/'));
		
		ftp_close($conn_id);

		return TRUE;
	}

	/**
	 * Efface les fichiers d'installation du serveur distant ( zip et sql )
	 */
	public function wp_clean_ftp_migration(){

		@unlink($this->_file_destination);
		@unlink($this->_file_sql);
		@unlink($this->_file_log_ftp);

		return TRUE;
	}

	/**
	 * Recupere le Zip de la derniere version en ligne de Wordpress
	 * Extrait le Zip de la version telechargé
	 * Supprime les fichiers telechargés et non utiles
	 */
	public function wp_download(){

		// Get WordPress data
		$wp = json_decode( file_get_contents( $this->_wp_api ) )->offers[0];

		if( ! mkdir($this->_wp_dir_core, 0775)){
			return FALSE;
		}

		file_put_contents( $this->_wp_dir_core . 'wordpress-' . $wp->version . '-' . $this->_wp_lang . '.zip', file_get_contents( $wp->download ) );

		$zip = new ZipArchive;

		// We verify if we can use the archive
		if ( $zip->open( $this->_wp_dir_core . 'wordpress-' . $wp->version . '-' . $this->_wp_lang . '.zip' ) === true ) {

			// Let's unzip
			$zip->extractTo( '.' );
			$zip->close();

			chmod( 'wordpress' , 0775 );

			// We scan the folder
			$files = scandir( 'wordpress' );

			// We remove the "." and ".." from the current folder and its parent
			$files = array_diff( $files, array( '.', '..' ) );

			// We move the files and folders
			foreach ( $files as $file ) {
				rename(  'wordpress/' . $file, './' . $file );
			}

			rmdir( 'wordpress' );
			$this->rrmdir( 'core' );
			unlink( './license.txt' ); 
			unlink( './readme.html' );
			unlink( './wp-content/plugins/hello.php' );

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Ecrit le fichier de configuration
	 *
	 * @param string $opts 		
	 *
	 * @return bool 			retourne true ou false
	 */
	public function wp_install_config($opts){

		// We retrieve each line as an array
		$config_file = file( 'wp-config-sample.php' );

		// Managing the security keys
		$secret_keys = explode( "\n", file_get_contents( 'https://api.wordpress.org/secret-key/1.1/salt/' ) );

		foreach ( $secret_keys as $k => $v ) {
			$secret_keys[$k] = substr( $v, 28, 64 );
		}

		// We change the data
		$key = 0;
		foreach ( $config_file as &$line ) {

			if ( '$table_prefix  =' == substr( $line, 0, 16 ) ) {
				$line = '$table_prefix  = \'' . $this->sanit( $opts['prefix'] ) . "';\r\n";
				continue;
			}

			if ( ! preg_match( '/^define\(\'([A-Z_]+)\',([ ]+)/', $line, $match ) ) {
				continue;
			}

			$constant = $match[1];

			switch ( $constant ) {
				case 'WP_DEBUG'	   :

					// Debug mod
					if ( (int) $opts['debug'] == 1 ) {
						$line = "define('WP_DEBUG', 'true');\r\n";

						// Display error
						if ( (int) $opts['debug_display']  == 1 ) {
							$line .= "\r\n\n " . "/** Affichage des erreurs à l'écran */" . "\r\n";
							$line .= "define('WP_DEBUG_DISPLAY', 'true');\r\n";
						}

						// To write error in a log files
						if ( (int) $opts['debug_log']  == 1 ) {
							$line .= "\r\n\n " . "/** Ecriture des erreurs dans un fichier log */" . "\r\n";
							$line .= "define('WP_DEBUG_LOG', 'true');\r\n";
						}
					}
					
					$line .= "\r\n\n " . "/** On augmente la mémoire limite */" . "\r\n";
					$line .= "define('WP_MEMORY_LIMIT', '256M');" . "\r\n";

					break;
				case 'DB_NAME'     :
					$line = "define('DB_NAME', '" . $this->sanit( $opts['dbname'] ) . "');\r\n";
					break;
				case 'DB_USER'     :
					$line = "define('DB_USER', '" . $this->sanit( $opts['uname'] ) . "');\r\n";
					break;
				case 'DB_PASSWORD' :
					$line = "define('DB_PASSWORD', '" . $this->sanit( $opts['pwd'] ) . "');\r\n";
					break;
				case 'DB_HOST'     :
					$line = "define('DB_HOST', '" . $this->sanit( $opts['dbhost'] ) . "');\r\n";
					break;
				case 'AUTH_KEY'         :
				case 'SECURE_AUTH_KEY'  :
				case 'LOGGED_IN_KEY'    :
				case 'NONCE_KEY'        :
				case 'AUTH_SALT'        :
				case 'SECURE_AUTH_SALT' :
				case 'LOGGED_IN_SALT'   :
				case 'NONCE_SALT'       :
					$line = "define('" . $constant . "', '" . $secret_keys[$key++] . "');\r\n";
					break;

				case 'WPLANG' :
					$line = "define('WPLANG', '" . $this->sanit( $this->_wp_lang ) . "');\r\n";
					break;
			}
		}
		unset( $line );

		$handle = fopen( 'wp-config.php', 'w' );
		foreach ( $config_file as $line ) {
			fwrite( $handle, $line );
		}
		fclose( $handle );

		// We set the good rights to the wp-config file
		chmod( 'wp-config.php', 0666 );
		unlink('wp-config-sample.php' );

		return TRUE;
	}

	/**
	 * On test si la base de donnée est accessible ou existante
	 *
	 * @return bool true|false
	 */
	public function wp_test_bdd(){

		try{
			$bdd = Bdd::getInstance();

			$sql = $bdd->dbh->prepare('SHOW TABLES');
			$sql->execute();
			$row = $sql->fetchAll();
			
			return TRUE;
        }catch(PDOException $e){

        	return FALSE;
		}
	}

	/**
	 * Instale et configure WordPress sur le serveur local dans le dossier courant
	 *
	 * @param mixed[] $opts informations d'inscription du compte admin Wordpress
	 *
	 * @return bool true|false
	 */
	public function wp_install_wp($opts){

		define( 'WP_INSTALLING', true );
		
		require_once( 'wp-load.php' );
		require_once( 'wp-admin/includes/upgrade.php' );
		require_once( 'wp-includes/wp-db.php' );

		// WordPress installation
		wp_install($opts['weblog_title'], $opts['user_login'], $opts['admin_email'], (int) $opts['blog_public'], '', $opts['admin_password']);

		// We update the options with the right siteurl et homeurl value
		$newurl = 'http://'.$_SERVER['SERVER_NAME'] . rtrim(dirname($_SERVER['REQUEST_URI']), '/');
		update_option( 'siteurl', $newurl);
		update_option( 'home', $newurl);

		update_option( 'permalink_structure', '/%postname%/');

		return TRUE;
	}

	/**
	 * Contact l'api distante pour lui donner les ordres du coté serveur distant
	 *
	 * @param mixed[] $opts_migration Array 
	 *
	 * @return bool true|false
	 */
	public function wp_migration($opts_migration) {

		$postdata = http_build_query(
		    array(
		        'api_call' 		=> 'migration',
		        'dbuser' 		=> $opts_migration['user_sql'],
				'dbname' 		=> $opts_migration['name_sql'],
				'dbpassword' 	=> $opts_migration['pass_sql'],
				'dbhost' 		=> $opts_migration['serveur_sql'],
				'site' 			=> $opts_migration['www_url'],
				'table_prefix'  => $opts_migration['table_prefix']
		    )
		);

		$opts = array('http' =>
		    array(
		        'method'  => 'POST',
		        'header'  => 'Content-type: application/x-www-form-urlencoded',
		        'content' => $postdata
		    )
		);

		$context  = stream_context_create($opts);

		ini_set('user_agent','Mozilla/4.0 (compatible; MSIE 6.0)');
		$result = file_get_contents(rtrim($opts_migration['www_url'], '/').'/migration.php', false, $context);

		return $result;
	}

	/**
	 * Recupere sur le serveur distant le fichier de log crée lors de la migration
	 *
	 * @param mixed[] $opts_migration Array 
	 *
	 * @return string retourne le log entier 
	 */	
	public function wp_migration_log($opts_migration) {

		ini_set('user_agent','Mozilla/4.0 (compatible; MSIE 6.0)');
		$result = file_get_contents(rtrim($opts_migration['www_url'], '/').'/'.$this->_file_log);

		return $result;
	}

	/**
	 * Modifie les urls dans la table de configuration et les contenues
	 *
	 * @param string $oldurl ancienne url ( a remplacer ) 
	 * @param string $newurl nouvelle url ( en remplacement )
	 * 
	 */
	public function wp_url($oldurl, $newurl) {

		$bdd = Bdd::getInstance();

		# Changer l'URL du site
		$req1 = $bdd->dbh->prepare('UPDATE '.$this->_table_prefix.'options SET option_value = replace(option_value, :oldurl, :newurl) WHERE option_name = \'home\' OR option_name = \'siteurl\';');

		# Changer l'URL des GUID
		$req2 = $bdd->dbh->prepare('UPDATE '.$this->_table_prefix.'posts SET guid = REPLACE (guid, :oldurl, :newurl);');

		# Changer l'URL des médias dans les articles et pages
		$req3 = $bdd->dbh->prepare('UPDATE '.$this->_table_prefix.'posts SET post_content = REPLACE (post_content, :oldurl, :newurl);');

		# Changer l'URL des données meta
		$req4 = $bdd->dbh->prepare('UPDATE '.$this->_table_prefix.'postmeta SET meta_value = REPLACE (meta_value, :oldurl, :newurl);');

		$req1->execute(array(
		    'oldurl' => $oldurl,
		    'newurl' => $newurl
		));

		$req2->execute(array(
		    'oldurl' => $oldurl,
		    'newurl' => $newurl
		));

		$req3->execute(array(
		    'oldurl' => $oldurl,
		    'newurl' => $newurl
		));

		$req4->execute(array(
		    'oldurl' => $oldurl,
		    'newurl' => $newurl
		));

		return TRUE;
	}

	/**
	 * Creer un htaccess adapté a wordpress
	 */
	public function wp_htaccess() {

		$path = dirname($_SERVER['REQUEST_URI']);

		$ht  = '<IfModule mod_rewrite.c>'."\r\n";
		$ht .= 'RewriteEngine On'."\r\n";
		$ht .= 'RewriteBase '.$path.''."\r\n";
		$ht .= 'RewriteRule ^index\.php$ - [L]'."\r\n";
		$ht .= 'RewriteCond %{REQUEST_FILENAME} !-f'."\r\n";
		$ht .= 'RewriteCond %{REQUEST_FILENAME} !-d'."\r\n";
		$ht .= 'RewriteRule . '.rtrim($path, '/').'/index.php [L]'."\r\n";
		$ht .= '</IfModule>'."\r\n";

		$ht .= '<files wp-config.php>'."\r\n";
		$ht .= '	order allow,deny'."\r\n";
		$ht .= '	deny from all'."\r\n";
		$ht .= '</files> '."\r\n";

		$ht .= '<Files .htaccess>'."\r\n";
		$ht .= '	order allow,deny '."\r\n";
		$ht .= '	deny from all '."\r\n";
		$ht .= '</Files>'."\r\n";

		$ht .= 'Options All -Indexes'."\r\n";
		
		if(file_exists('.htaccess')) {
			copy('.htaccess', '.htaccess.bak');
		}

		if(file_put_contents( '.htaccess', $ht ) !== FALSE){
			return TRUE;
		}
			
		return FALSE;
	}

	/**
	 * Modifie le fichier de configuration et ajoute les lignes de config pour mise a jour via le net et la langue FR
	 *
	 * @param mixed[] $options Array Options de connexion au serveur distant
	 *
	 * @return bool true|false
	 */
	public function wp_configfile($options) {
		
		$filename = 'wp-config.php';

		$content = file_get_contents($filename);

		$content = preg_replace ("/define\('DB_NAME', '(.*)'\);/i", "define('DB_NAME', '".$options['dbname']."');", $content);
		$content = preg_replace ("/define\('DB_USER', '(.*)'\);/i", "define('DB_USER', '".$options['dbuser']."');", $content);
		$content = preg_replace ("/define\('DB_PASSWORD', '(.*)'\);/i", "define('DB_PASSWORD', '".$options['dbpassword']."');", $content);
		$content = preg_replace ("/define\('DB_HOST', '(.*)'\);/i", "define('DB_HOST', '".$options['dbhost']."');", $content);

		file_put_contents($filename , $content );

		// ajouter une ligne a la ligne -8
		$methode = "define('FS_METHOD', 'direct');\ndefine('WPLANG', 'fr_FR');\n";
		$lines = file($filename);
		$num_lines = count($lines);

		if ($num_lines > 9) {
		    array_splice($lines, $num_lines - 9, 0, array($methode));
		    file_put_contents($filename, implode('', $lines));
		} else {
		    file_put_contents($filename, PHP_EOL . $methode, FILE_APPEND);
		}

		chmod( 'wp-config.php', 0666 );
		unlink( 'wp-config-sample.php' );

		return TRUE;
	}

	/**
	 * Test si l'url existe
	 */
	public function test_url_exist($url) {

		$headers = @get_headers($url);

		if ($headers === FALSE) {

			return FALSE;
		} else {

			return TRUE;
		}
	}
	/**
	 * Exporte les fichiers de WP
	 */
	public function wp_export_file() {
		
		if(file_exists($this->_file_destination)){
			unlink($this->_file_destination);
		}

		try {
			$this->Zip($this->_fileswp, $this->_file_destination);

		} catch (Exception $e) {
			
			if(function_exists('exec')){

				$this->Zip_soft('./', $this->_file_destination, 'exec');
			} elseif(function_exists('system')){

				$this->Zip_soft('./', $this->_file_destination, 'system');
			}
		}

		if(file_exists($this->_file_destination)){
			return TRUE;
		}		

		return FALSE;
	}

	/**
	 * Exporte la base de données Wordpress selon 3 types de possibilité pour repondre le plus rapidement a la demande en fonction des serveurs,
	 *
	 * @link http://stackoverflow.com/questions/22195493/export-mysql-database-using-php-only Export php SQL issus de cette doc
	 */
	public function wp_export_sql() {

		if(file_exists($this->_file_sql )){
			unlink($this->_file_sql );
		}

		if(function_exists('exec')){

			$command = 'mysqldump --opt -h' . $this->_dbhost .' -u' . $this->_dbuser .' -p' . $this->_dbpassword .' ' . $this->_dbname .' > '.$this->_file_sql;
			exec($command, $output = array(), $worked);

		} elseif(function_exists('system')){

			system("mysqldump --host=" . $this->_dbhost ." --user=" . $this->_dbuser ." --password=". $this->_dbpassword ." ". $this->_dbname ." > ".$this->_file_sql);

		} else {
			
		    $bdd = Bdd::getInstance();

			$bdd->dbh->query("SET NAMES 'utf8'");

	        $queryTables    = $bdd->dbh->query('SHOW TABLES'); 
	        while($row = $queryTables->fetch()) 
	        { 
	            $target_tables[] = $row[0]; 
	        }

			$content   =  '-- Migration SQL Dump'. "\n";
			$content  .=  '-- version 1.0'. "\n";
			$content  .=  '-- http://www.viky.fr'. "\n";
			$content  .=  '--'. "\n";
			$content  .=  '-- Host: localhost'. "\n";
			$content  .=  '-- Generation Time: '. "\n";
			$content  .=  '-- Server version: '. "\n";
			$content  .=  '-- PHP Version: '. "\n";

	        foreach($target_tables as $table)
	        {

	            $result         =   $bdd->dbh->query('SELECT * FROM '.$table);  
	            $fields_amount  =   $result->columnCount();  
	            $rows_num		=	$result->rowCount();
	            $res            =   $bdd->dbh->query('SHOW CREATE TABLE '.$table); 
	            $TableMLine     =   $res->fetch();

				$content       .=  "\n" . "--" . "\n";
				$content       .= "-- Table structure for table `".$table."`". "\n";
				$content       .= "--". "\n";

	            $content        = (!isset($content) ?  '' : $content) . "\n\n".$TableMLine[1].";\n\n";

	            for ($i = 0, $st_counter = 0; $i < $fields_amount;   $i++, $st_counter=0) 
	            {
	                while($row = $result->fetch())  
	                { //when started (and every after 100 command cycle):
	                    if ($st_counter%100 == 0 || $st_counter == 0 )  
	                    {
	                            $content .= "\nINSERT INTO ".$table." VALUES";
	                    }
	                    $content .= "\n(";
	                    for($j=0; $j<$fields_amount; $j++)  
	                    { 
	                        $row[$j] = str_replace("\n","\\n", addslashes($row[$j]) ); 
	                        if (isset($row[$j]))
	                        {
	                            $content .= '"'.$row[$j].'"' ; 
	                        }
	                        else 
	                        {   
	                            $content .= '""';
	                        }     
	                        if ($j<($fields_amount-1))
	                        {
	                                $content.= ',';
	                        }      
	                    }
	                    $content .=")";
	                    //every after 100 command cycle [or at last line] ....p.s. but should be inserted 1 cycle eariler
	                    if ( (($st_counter+1)%100==0 && $st_counter!=0) || $st_counter+1==$rows_num) 
	                    {   
	                        $content .= ";";
	                    } 
	                    else 
	                    {
	                        $content .= ",";
	                    } 
	                    $st_counter=$st_counter+1;
	                }
	            } 

	            $content .="\n\n\n";
        	}

        	file_put_contents($this->_file_sql , $content);
		}

		if(file_exists($this->_file_sql )){
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Extrait les fichiers dans le dossier courant
	 */
	public function wp_import_file() {
	 
	    $zip = new ZipArchive;

	    $zip->open($this->_file_destination);
	    $zip->extractTo('.');
	    $zip->close();

	    return TRUE;
	}

	/**
	 * Permet d'importer la base de données selon 3 types de possibilites 
	 *
	 * @link http://stackoverflow.com/questions/19751354/how-to-import-sql-file-in-mysql-database-using-php import SQL php issus de cette doc
	 */
	public function wp_import_sql() {

		if(function_exists('exec')){

			$command = 'mysql -h' . $this->_dbhost .' -u' . $this->_dbuser .' -p' . $this->_dbpassword .' ' . $this->_dbname .' < '.$this->_file_sql;
			exec($command, $output = array(), $worked);

			switch($worked){
				case 0:

					return TRUE;
				break;
				case 1:

					return FALSE;
				break;
			}

		} elseif(function_exists('system')){

			system('mysql -h' . $this->_dbhost .' -u' . $this->_dbuser .' -p' . $this->_dbpassword .' ' . $this->_dbname .' < '.$this->_file_sql);

			return TRUE;
		} else {
			
		    $bdd = Bdd::getInstance();

			$templine = '';
			// Read in entire file
			$lines = file($this->_file_sql);
			// Loop through each line
			foreach ($lines as $line)
			{
				// Skip it if it's a comment
				if (substr($line, 0, 2) == '--' || $line == '') {
				    continue;
				}

				// Add this line to the current segment
				$templine .= $line;
				// If it has a semicolon at the end, it's the end of the query
				if (substr(trim($line), -1, 1) == ';')
				{
				    // Perform the query
				    $bdd->dbh->query($templine);
				    // Reset temp variable to empty
				    $templine = '';
				}
			}

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Supprime les revisions de la bdd
	 */
	public function wp_sql_clean_revision() {

		$bdd = Bdd::getInstance();

		$sql = $bdd->dbh->prepare('DELETE FROM '.$this->_table_prefix.'posts WHERE post_type = "revision"');
		
		return $sql->execute();
	}

	/**
	 * Supprime tous les commentaires non approuvés
	 */
	public function wp_sql_clean_spam() {

		$bdd = Bdd::getInstance();

		$sql = $bdd->dbh->prepare('DELETE from '.$this->_table_prefix.'comments WHERE comment_approved = 0');
		
		return $sql->execute();
	}

	/**
	 * Install une liste de plugin separé par une , 
	 *
	 * @param string $plug_off liste separé par une , 
	 */
	public function wp_install_plugins($plug_off){

		$plugins     = explode( ",", $plug_off );
		$plugins     = array_map( 'trim' , $plugins );
		$plugins_dir = 'wp-content/plugins/';

		foreach ( $plugins as $plugin ) {

			// We retrieve the plugin XML file to get the link to downlad it
		    $plugin_repo = file_get_contents( "http://api.wordpress.org/plugins/info/1.0/$plugin.json" );

		    if ( $plugin_repo && $plugin = json_decode( $plugin_repo ) ) {

				$plugin_path = config('wp_dir_plug') . $plugin->slug . '-' . $plugin->version . '.zip';

				if ( ! file_exists( $plugin_path ) ) {
					// We download the lastest version
					if ( $download_link = file_get_contents( $plugin->download_link ) ) {
						file_put_contents( $plugin_path, $download_link );
					}							
				}

		    	// We unzip it
		    	$zip = new ZipArchive;
				if ( $zip->open( $plugin_path ) === true ) {
					$zip->extractTo( $plugins_dir );
					$zip->close();
				}
		    }
		}

		require_once( 'wp-load.php' );
		require_once( 'wp-admin/includes/plugin.php');		

		activate_plugins( array_keys( get_plugins() ) );

		return TRUE;
	}

	/**
	 * Supprime les themes default de Wordpress
	 *
	 */
	public function wp_delete_theme(){

		require_once( 'wp-load.php' );
		require_once( 'wp-admin/includes/upgrade.php' );

		delete_theme( 'twentyfourteen' );
		delete_theme( 'twentythirteen' );
		delete_theme( 'twentytwelve' );
		delete_theme( 'twentyeleven' );
		delete_theme( 'twentyten' );
		// We delete the _MACOSX folder (bug with a Mac)
		delete_theme( '__MACOSX' );
	}

	/**
	 * Ajoute un utilisateur a Wordpress
	 *
	 * @param string $user_login 	Nom utilisateur
	 * @param string $user_pass 	Mot de passe utilisateur
	 *
	 */
	public function wp_add_user($user_login, $user_pass){

		require_once( 'wp-load.php' );
		require_once( 'wp-includes/user.php' );
		
		$userdata = array(
		    'user_login' 	=>  $user_login,
		    'user_url'  	=>  '',
		    'user_email'	=>  '',
		    'user_pass' 	=>  $user_pass,
		    'role' 			=> 'administrator'
		);

		$user_id = wp_insert_user( $userdata ) ;

		//On success
		if ( ! is_wp_error( $user_id ) ) {
		    return TRUE;
		}

		return FALSE;
	}	

	/**
	 * 
	 */
	public function wp_rename_prefix($new_prefix)
	{

		$old_prefix = $this->_table_prefix;

		if( ! is_writable('wp-config.php')){
			return FALSE;
		}

		// Modifier le fichier config
		$file = file('wp-config.php');
		$content = '';
		foreach($file as $line)
		{
			$line = ltrim($line);
			if (!empty($line)){
				if (strpos($line, '$table_prefix') !== false){
					$line = preg_replace("/=(.*)\;/", "= '".$new_prefix."';", $line);
				}
			}
			$content .= $line;
		}
		if (!empty($content)){
			file_put_contents('wp-config.php', $content);
		}

		// Modifier la base de données
		$bdd = Bdd::getInstance();
		
		$sql = $bdd->dbh->prepare("SHOW TABLES LIKE '".$old_prefix."%'");
		$sql->execute();
		$tables = $sql->fetchAll();

		$changed_tables = array();
		foreach ($tables as $table)	{
			$table_old_name = $table[0];

			// To rename the table
			$table_new_name = substr_replace($table_old_name, $new_prefix, 0, strlen($old_prefix));

			$sql1 = $bdd->dbh->prepare("RENAME TABLE `{$table_old_name}` TO `{$table_new_name}`");
			$sql1->execute();

			array_push($changed_tables, $table_new_name);
		}
		
		$sql2 = $bdd->dbh->prepare("UPDATE {$new_prefix}options SET option_name='{$new_prefix}user_roles' WHERE option_name='{$old_prefix}user_roles';");
		$sql2->execute();

		$query = 'UPDATE '.$new_prefix.'usermeta set meta_key = CONCAT(replace(left(meta_key, ' . strlen($old_prefix) . "), '{$old_prefix}', '{$new_prefix}'), SUBSTR(meta_key, " . (strlen($old_prefix) + 1) . ")) where meta_key in ('{$old_prefix}autosave_draft_ids', '{$old_prefix}capabilities', '{$old_prefix}metaboxorder_post', '{$old_prefix}user_level', '{$old_prefix}usersettings','{$old_prefix}usersettingstime', '{$old_prefix}user-settings', '{$old_prefix}user-settings-time', '{$old_prefix}dashboard_quick_press_last_post_id')";
		$sql3 = $bdd->dbh->prepare($query);
		$sql3->execute();

		return TRUE;
	}

	/**
	 * Efface les fichiers de l'installation Wordpress
	 */
	public function wp_clean_files()
	{

		foreach ($this->_fileswp as $key => $file) {
			if(is_dir($file)) {
				$this->rrmdir($file);
			} else {
				@unlink($file);
			}
		}

		return TRUE;
	}

	/**
	 * Efface les tables de WP
	 */
	public function wp_clean_sql()
	{
		$bdd = Bdd::getInstance();

		$sql = $bdd->dbh->prepare("SHOW TABLES LIKE '".$this->_table_prefix."%'");
		$sql->execute();
		$tables = $sql->fetchAll();

		foreach ($tables as $table)	{
			$table_name = $table[0];

			// To rename the table
			$sql1 = $bdd->dbh->prepare("DROP TABLE `{$table_name}`");
			$sql1->execute();
		}

		return TRUE;
	}

	/**
	 * 
	 */
	public function wp_test_hash()
	{

		if(file_exists('migration-hash.php')){
			include('migration-hash.php');

			$retour = '';
			foreach ($fileswp as $key => $value) {
				if(md5_file($key) != $value){
					$retour .= "Le fichier : ".$key." ne correspond plus a la version initial.<br>";
				}
			}

			return $retour;
		}

		return FALSE;
	}

	/**
	 * Telecharge la nouvelle version de migration.php
	 */
	public function wp_update()
	{

		$content = file_get_contents('https://raw.githubusercontent.com/emulsion-io/wp-migration-url/master/migration.php');

		file_put_contents('migration.php', $content);

		return TRUE;
	}

	/**
	 * 
	 * Verifie si une nouvelle version est dispo.
	 * 
	 */
	public function wp_check_update()
	{
		$content = file_get_contents('https://raw.githubusercontent.com/emulsion-io/wp-migration-url/master/version.json');
		$version = json_decode($content);

		$retour['version_courante'] = $this->_version;
		$retour['version_enligne']  = $version->version;

		if($retour['version_courante'] != $retour['version_enligne']) {
			$retour['maj_dipso'] = TRUE;
		} else {
			$retour['maj_dipso'] = FALSE;
		}

		return $retour;
	}	

	/**
	 * 
	 */
	public function wp_create_hash()
	{
		if(file_exists('migration-hash.php')){
			unlink('migration-hash.php');
		}

		$hashfiles = '<?php '."\n";
		$hashfiles .= $this->wp_hashs_files(getcwd(), 0, FALSE);
		$hashfiles .= "\n".'?>';

		file_put_contents('migration-hash.php', $hashfiles);

		return TRUE;
	}

	/**
	 * Create a Directory Map and hash file md5 to php array
	 *
	 * Reads the specified directory and builds an array
	 * representation of it. Sub-folders contained with the
	 * directory will be mapped as well.
	 *
	 * @param	string	$source_dir		Path to source
	 * @param	int	$directory_depth	Depth of directories to traverse
	 *						(0 = fully recursive, 1 = current dir, etc)
	 * @param	bool	$hidden			Whether to show hidden files
	 * @return	array
	 */
	public function wp_hashs_files($source_dir, $directory_depth = 0, $hidden = FALSE)
	{
		if ($fp = @opendir($source_dir))
		{
			$hashs		= '';
			$new_depth	= $directory_depth - 1;
			$source_dir	= rtrim($source_dir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

			while (FALSE !== ($file = readdir($fp)))
			{
				// Remove '.', '..', and hidden files [optional]
				if ($file === '.' OR $file === '..' OR ($hidden === FALSE && $file[0] === '.'))
				{
					continue;
				}

				is_dir($source_dir.$file) && $file .= DIRECTORY_SEPARATOR;

				if (($directory_depth < 1 OR $new_depth > 0) && is_dir($source_dir.$file))
				{	
					$hashs .= $this->wp_hashs_files($source_dir.$file, $new_depth, $hidden);
				}
				else
				{
					$file_current = substr_replace($source_dir.$file, '', 0, strlen($this->_current_rep)+1);
					$hashs .= '$fileswp["'.$file_current.'"] = "'.md5_file($source_dir.$file).'";'."\n";
				}
			}
						
			closedir($fp);
			
			return $hashs;
		}

		return FALSE;
	}

	/**
	 * 
	 * @link
	 */
	public function excludefilesfolderin_zip($serialize = FALSE) {
		$files_current = scandir('.');
		unset($files_current[0]);
		unset($files_current[1]);

		$result = array_diff($files_current, $this->_fileswp);
		
		$listefiles = '';
		if($serialize = TRUE) {
			if (count($result) > 0) {
				foreach ($result as $file) {
					$ext = '';
					if(is_dir($file)) {
						$ext = '/*';
					}
					$listefiles .= ' "'.$file.$ext.'" ';
				}
			}

			return $listefiles;
		}

		return $result;
	}	

	public function ftp_putAll($conn_id, $src_dir, $dst_dir) {
	   $d = dir($src_dir);
	   while($file = $d->read()) { // do this for each file in the directory
	       if ($file != "." && $file != "..") { // to prevent an infinite loop
	           if (is_dir($src_dir."/".$file)) { // do the following if it is a directory
	               if (!@ftp_nlist($conn_id, $dst_dir."/".$file)) {
	                   ftp_mkdir($conn_id, $dst_dir."/".$file); // create directories that do not yet exist
	               }
	               $this->ftp_putAll($conn_id, $src_dir."/".$file, $dst_dir."/".$file); // recursive part
	           } else {
	               $upload = ftp_put($conn_id, $dst_dir."/".$file, $src_dir."/".$file, FTP_BINARY); // put the files
	           }
	           // ecrit dans le fichier ftp.log le fichier en court d'up
	           file_put_contents($this->_file_log_ftp, date('d-m-Y h:i:s')." : ".$src_dir."/".$file, FILE_APPEND);
	       }
	   }
	   $d->close();
	}

	/**
	 * Creer des Zip recursivement en utilisant les fonctions systemes
	 *
	 * @param string $source 		Source des fichiers
	 * @param string $destination 	Fichier de destinations ( zip )
	 * @param string $soft 			Methode de creation du zip
	 *
	 * @return bool true|false
	 */
	public function Zip_soft($source, $destination, $soft = 'exec')
	{	    
		if (!is_readable($source) || ! is_writeable(dirname($destination)) || (file_exists($destination) && !is_file($destination))) {
			// really you should capture some more specific information
			// in your excaption handling

			return false;
		}

		$output 	= '';
		$returnv 	= true;

		if($soft == 'exec') {
			exec('zip -r '.$destination.' '.$source.' -x '.$this->excludefilesfolderin_zip(TRUE).' >/dev/null', $output, $returnv);
		} else {
			system('zip -r '.$destination.' '.$source.' -x '.$this->excludefilesfolderin_zip(TRUE).' >/dev/null', $output);
		}

		return TRUE;
	}

	/**
	 * Creer des Zip recursivement
	 *
	 * @link : http://stackoverflow.com/questions/1334613/how-to-recursively-zip-a-directory-in-php methode php issus de cette doc
	 *
	 * @param array $source 		Listes des fichiers et dossier wp a save dans le zip
	 * @param string $destination 	Fichier de destinations ( zip )
	 *
	 * @return bool true|false
	 */
	public function Zip($source, $destination)
	{
	    if (!extension_loaded('zip')) {
	    //if (!extension_loaded('zip') || !file_exists($source)) {

	        return false;
	    }

	    $zip = new ZipArchive();
	    if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {

	        return false;
	    }

	    $sources = str_replace('\\', '/', realpath('.'));

	    foreach ($source as $key => $value) {

			if (is_dir($value) === true)
			{
			    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($value), RecursiveIteratorIterator::SELF_FIRST);

			    foreach ($files as $file)
			    {
			        $file = str_replace('\\', '/', $file);

			        // Ignore "." and ".." folders
			        if( in_array(substr($file, strrpos($file, '/')+1), array('.', '..')) )
			            continue;

			        $file = realpath($file);

			        if (is_dir($file) === true)
			        {
			            if( ! $zip->addEmptyDir(str_replace($sources . '/', '', $file . '/')))
						{
							@unlink($this->_file_destination);
							throw new Exception("La memoire alouée par le serveur n'est pas sufisante.");
						}
			        }
			        else if (is_file($file) === true)
			        {
			            if( ! $zip->addFromString(str_replace($sources . '/', '', $file), file_get_contents($file)))
						{
							@unlink($this->_file_destination);
							throw new Exception("La memoire alouée par le serveur n'est pas sufisante.");
						}
			        }
			    }
			}
			else if (is_file($value) === true)
			{
			    $zip->addFromString(basename($value), file_get_contents($value));
			}
		}

	    return $zip->close();
	}

	/**
	 * Affiche la memoir php alouée par le serveur
	 *
	 * @link http://stackoverflow.com/questions/10208698/checking-memory-limit-in-php script issus de cette doc
	 *
	 * @param bool $units true|false
	 *
	 * @return string memoire alouée par le serveur
	 */
	public function get_memory_limit($units = TRUE) {
		$memory_limit = ini_get('memory_limit');
		if (preg_match('/^(\d+)(.)$/', $memory_limit, $matches)) {
		    if ($matches[2] == 'M') {
		        $memory_limit = $matches[1] * 1024 * 1024; // nnnM -> nnn MB
		    } else if ($matches[2] == 'K') {
		        $memory_limit = $matches[1] * 1024; // nnnK -> nnn KB
		    }
		}

		return $this->formatBytes($memory_limit, 2, $units);
	}

	/**
	 * Convertie en unité humaine B KB MB GB une information en octets
	 *
	 * @link http://stackoverflow.com/questions/2510434/format-bytes-to-kilobytes-megabytes-gigabytes
	 *
	 * @param int 	$bytes
	 * @param int 	$precision
	 * @param bool 	$units true|false
	 *
	 * @return string chaine d'unités avec son suffixe ou non
	 */
	public function formatBytes($bytes, $precision = 2, $units = TRUE) {
	    $unit = ["B", "KB", "MB", "GB"];
	    $exp = floor(log($bytes, 1024)) | 0;
	    
	    if($units === TRUE) {
	    	return round($bytes / (pow(1024, $exp)), $precision).$unit[$exp];
		} else {
	    	return round($bytes / (pow(1024, $exp)), $precision);
		}
	}

	/**
	 * Supprime recursivement un dossier et ses fichiers
	 *
	 * @param string 	$dir  chemin a effacer
	 *
	 * @return bool true|false
	 */
	public function rrmdir($dir) {
	    if (is_dir($dir)) {
	    	$objects = scandir($dir);
	    	foreach ($objects as $object) {
	       		if ($object != "." && $object != "..") {
	         		if (filetype($dir."/".$object) == "dir") $this->rrmdir($dir."/".$object); else unlink($dir."/".$object);
	       		}
	     	}
	    	reset($objects);
	    	rmdir($dir);

	    	return TRUE;
	   	}

	   	return FALSE;
	}

	/**
	 * Nettoie les chaines de caraceteres avant d'etre utilisées par Wordpress
	 * @param string 	$str
	 */
	private function sanit( $str ) {
		return addcslashes( str_replace( array( ';', "\n" ), '', $str ), '\\' );
	}

	/**
	 * Formate un array php sous forme de json
	 * @param string 	$data 
	 * @param bool 	$success
	 */
    public function retour($data = '', $success = TRUE)
    {

        $json = json_encode(array('success' => $success, 'data' => $data));
        
	    header('Access-Control-Allow-Origin: *');
	    header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date dans le passé
	    header('content-type: text/html; charset=utf-8');

	    echo $json;
		
        exit;
    }	
}

class Bdd
{
    public $dbh; // handle of the db connexion
    private static $instance;

    private function __construct()
    {
        // building data source name from config
        $dsn = 'mysql:host=' . Config::read('db.host') .
               ';dbname='    . Config::read('db.basename') .
               ';charset=utf8';

        $user 		= Config::read('db.user');
        $password 	= Config::read('db.password');

        $this->dbh 	= new PDO($dsn, $user, $password);
    }

    public static function getInstance()
    {
        if (!isset(self::$instance))
        {
            $object = __CLASS__;
            self::$instance = new $object;
        }

        return self::$instance;
    }
}

class Config
{
    static $confArray;

    public static function read($name)
    {

        return self::$confArray[$name];
    }

    public static function write($name, $value)
    {
        self::$confArray[$name] = $value;
    }
}

?>
