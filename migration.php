<?php

/**
 * @author Fabrice Simonet
 * @link http://viky.fr
 *
 * @version 2.5 codename Eulalie
*/

/**
 * Copyright (c) 2016 Fabrice Simonet, Matthieu Andre
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
$retour_url 			= FALSE;
$retour_migration 		= FALSE;
$retour_migration_api 	= FALSE;
$retour_migration_log 	= FALSE;
$retour_export 			= FALSE;
$retour_import 			= FALSE;
$retour_export_sql 		= FALSE;
$retour_import_sql 		= FALSE;
$retour_htaccess 		= FALSE;
$retour_dl 				= FALSE;
$retour_dl_full			= FALSE;
$retour_clean_revision 	= FALSE;
$retour_clean_spam 		= FALSE;
$retour_plug_install 	= FALSE;
$retour_delete_theme 	= FALSE;
$retour_add_user 		= FALSE;

$migration = new Wp_Migration();

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

			$retour_action_bdd_existe = $migration->wp_install_bdd($opts);

			if($retour_action_bdd_existe === FALSE) {
				$migration->retour(array('message' => 'La base de données n\'existe pas.'), FALSE);
			}

			$migration->wp_download();
			$migration->wp_install_config($opts);
			$migration->wp_install_wp($opts);

			$migration->retour(array('message' => 'Installation complete effectuée.'), TRUE);
		} else {
			$migration->retour(array('message' => 'Action inconnue'), FALSE);
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
			'table_prefix' 	=> $table_prefix
		);

		// Exporte le SQL
		$migration->wp_export_sql();

		// Exporte Les fichiers dans le Zip avec le SQL
		$migration->wp_export_file();

		// Envoie les fichiers sur le serveur FTP distant
		$ftp_migration_retour = $migration->wp_ftp_migration($opts_migration);

		// Supprime les fichiers locaux
		$migration->wp_clean_ftp_migration();

		if($ftp_migration_retour == FALSE){
			echo "erreur FTP, connexion impossible"; exit;
		}

		// Contact le site distant pour activer la methode api_call
		$retour_migration 		= $migration->wp_migration($opts_migration);
		$retour_migration_log 	= $migration->wp_migration_log($opts_migration);

		if($retour_migration === TRUE) {
			$migration->retour(array('message' => 'Migration effectuée avec succes.', 'context' => $retour_migration_log), TRUE);
		} else {
			$migration->retour(array('message' => 'Impossible d\'ajouter un utilisateur.'), FALSE);
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
		$migration->wp_import_file();
		$migration->wp_log('Extraction des fichiers');

		// modifie le fichier wordpress avec les infos du nouveau serveur
		$migration->wp_configfile($opts);
		$migration->wp_log('Configuration du wp-config.php');

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

		// Effectue l'importation du SQL
		$migration->wp_import_sql();
		$migration->wp_log('Importation du SQL');

		// Recupere les informations sur le WP courant
		$site_url = $migration->wp_get_info();

		// modification des urls
		$oldurl = $site_url['option_value'];
		$newurl = 'http://'.$_SERVER['SERVER_NAME'] . rtrim(dirname($_SERVER['REQUEST_URI']), '/');
		$migration->wp_url($oldurl, $newurl);
		$migration->wp_log('Modification des URLs');

		// creation du .htaccess
		$migration->wp_htaccess();
		$migration->wp_log('Creation du htaccess');

		// nettoie les fichiers sql et zip
		$migration->wp_clean_ftp_migration();
		$migration->wp_log('Netoyage des fichiers temporaire de migration');

		return TRUE;
	}
}

?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name='viewport' content='initial-scale=1,maximum-scale=1,user-scalable=no' />

		<title>Migration Wordpress Easy</title>

		<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet" integrity="sha256-7s5uDGW3AHqw6xtJmNNtr+OBRJUlgkNJEo78P4b0yRw= sha512-nNo+yCHEyn0smMxSswnf/OnX6/KwJuZTlNZBjauKhTK0c+zT+q5JOCx0UFhXQ6rJR9jg6Es8gPuD2uZcYDLqSw==" crossorigin="anonymous">
		<link href='https://fonts.googleapis.com/css?family=Raleway' rel='stylesheet' type='text/css'>
		<link href='https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css' rel='stylesheet' type='text/css'>

		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha256-KXn5puMvxCw+dAYznun+drMdG1IFl3agK0p/pqT9KAo= sha512-2e8qq0ETcfWRI4HJBzQiA3UoyFk6tbNyG+qSaIBZLyW9Xf3sWZHN/lxe9fTh1U45DpPf07yj94KsUHHWe4Yk1A==" crossorigin="anonymous"></script>

		<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
	</head>
	<body>
		<div class="container">
			<header class="row">
				<div class="col-md-12">
					<div class="jumbotron">
						<h1>Migration Wordpress Easy</h1>
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
						<div class="panel-body">
						Pensez a supprimer le fichier migration.php de votre installation Wordpress apres avoir effectuer vos modifications.
						</div>
					</div>
				</div>
			</article>

			<article class="row">
				<div class="col-md-12">
					<div class="panel panel-warning">
						<div class="panel-heading"> 
							<h3 class="panel-title">Info Serveur</h3> 
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

			<h2>Processus de migration automatique d'un Wordpress d'un serveur A vers un serveur B en FTP</h2>

			<article class="row">
				<div class="col-md-12">
					<h3>Migration du Wordpress sur un autre serveur</h3>
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
				</div>
				<div class="col-md-12">
					<form id="action_migration" method="post">
						<h3>Url http du futur site</h3>
						<div class="form-group">
							<label for="www_url">Url http du serveur</label>
							<input type="text" class="form-control" id="www_url" name="www_url" placeholder="http://" value="">
						</div>
						<h3>Information du FTP cible</h3>
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
						<div class="form-group">
							<button id="go_action_migration" type="submit" class="btn btn-default">Lancer la migration</button>
						</div>
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
			</article>

			<h2>Outils de manipulation et de realisation de tache pour Wordpress.</h2>

			<article class="row">
				<div class="col-md-12">
					<h3>Telecharge et extrait une installation de Wordpress avec possibilité de l'installer</h3>
					<div class="panel panel-info">
						<div class="panel-heading"> 
							<h3 class="panel-title">Ce que fait cet assistant</h3> 
						</div>
						<div class="panel-body">
							<ul>
								<li>Telecharge, extrait et installe un Wordpress derniere version en date depuis le site officiel</li>
							</ul>
						</div>
					</div>
				</div>

				<div class="col-md-12">
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
			</article>

			<article class="row">
				<div class="col-md-12">
					<h3>Ecriture des nouvelles Urls</h3>
					<div class="panel panel-info">
						<div class="panel-heading"> 
							<h3 class="panel-title">Ce que fait cet assistant</h3> 
						</div>
						<div class="panel-body">
							<ul>
								<li>Modification des URLs dans la table wp_option, home et siteurl sont affectés</li>
								<li>Modification des URLs dans la table wp_posts, guid et post_content sont affectés</li>
								<li>Modification des URLs dans la table wp_postmeta, toutes les meta_value auront la nouvelle URL</li>
							</ul>
						</div>
					</div>
				</div>
				<div class="col-md-12">
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
									<li>Wordpress n'est pas installé sur le serveur</li>
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
			</article>

	        <h2>Export</h2>

			<article class="row">
				<div class="col-md-12">
					<h3>Creation d'une archive de vos fichiers</h3>
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
				</div>

	            <div class="col-md-12">
					<form id="action_exporter" method="post">
						<?php if($wp_exist == TRUE) : ?>
						<div class="form-group">
							<button id="go_action_exporter" type="submit" class="btn btn-default">Creer le Zip des fichiers</button>
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
	        </article>

	        <article class="row">
	           	<div class="col-md-12">
	           	    <h3>Exporte la base de données de votre Wordpress</h3>
					<div class="panel panel-info">
						<div class="panel-heading"> 
							<h3 class="panel-title">Ce que fait cet assistant</h3> 
						</div>
						<div class="panel-body">
						    <ul>
						    	<li>Extrait la base données</li>
						    </ul>
						</div>
					</div>
				</div>

	            <div class="col-md-12">
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
	        </article>

			<h2>Import</h2>

	        <article class="row">
	           	<div class="col-md-12">
	           	    <h3>Importation de votre Zip</h3>
					<div class="panel panel-info">
						<div class="panel-heading"> 
							<h3 class="panel-title">Ce que fait cet assistant</h3> 
						</div>
						<div class="panel-body">
						    <ul>
						    	<li>Extrait l'ensemble de vos fichiers dans le dossier courant</li>
						    </ul>
						</div>
					</div>
				</div>

	            <div class="col-md-12">
					<form id="action_importer" method="post">
						<?php if(file_exists($migration->_file_destination)): ?>
						<div class="form-group">
							<button id="go_action_importer" type="submit" class="btn btn-default">Extraire les fichiers dans le dossier courant</button>
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
	        </article>
			
	        <article class="row">
	           	<div class="col-md-12">
	           	    <h3>Importation de votre Base de données</h3>
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
				</div>

	            <div class="col-md-12">
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
	        </article>

	        <article class="row">
	           	<div class="col-md-12">
	           	    <h2>Creer le fichier HTACCESS wordpress</h2>
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
				</div>

	            <div class="col-md-12">
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
			</article>

			<article class="row">
				<div class="col-md-12">
					<h2>Efface toutes les revisions de votre wordpress</h2>
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
				</div>

	            <div class="col-md-12">
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
	        </article>

	        <article class="row">
	           	<div class="col-md-12">
	           	    <h2>Efface tous les commentaires non validés</h2>
					<div class="panel panel-info">
						<div class="panel-heading"> 
							<h3 class="panel-title">Ce que fait cet assistant</h3> 
						</div>
						<div class="panel-body">
						    <ul>
						    	<li>Efface tous les commentaires non validés</li>
						    </ul>
						</div>
					</div>
				</div>

	            <div class="col-md-12">
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
	        </article>

	        <article class="row">
	           	<div class="col-md-12">
	           	    <h2>Installe les plugins de votre choix</h2>
					<div class="panel panel-info">
						<div class="panel-heading"> 
							<h3 class="panel-title">Ce que fait cet assistant</h3> 
						</div>
						<div class="panel-body">
						    <ul>
						    	<li>Installe les plugins listé par une virgule</li>
						    </ul>
						</div>
					</div>
				</div>

	            <div class="col-md-12">
					<form id="action_plug_install" method="post">
						<div class="form-group">
							<label for="plug_install_liste">Liste des plugins</label>
							<input type="text" class="form-control" id="plug_install_liste" name="plug_install_liste" placeholder="" value="">
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
	        </article>

	         <article class="row">
	           	<div class="col-md-12">
	           	    <h2>Supprime les themes defaut de Wordpress</h2>
					<div class="panel panel-info">
						<div class="panel-heading"> 
							<h3 class="panel-title">Ce que fait cet assistant</h3> 
						</div>
						<div class="panel-body">
						    <ul>
						    	<li>twentyfourteen</li>
						    	<li>twentythirteen</li>
						    	<li>twentytwelve</li>
						    	<li>twentyeleven</li>
						    	<li>twentyten</li>
						    </ul>
						</div>
					</div>
				</div>

	            <div class="col-md-12">
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
	        </article>       

	         <article class="row">
	           	<div class="col-md-12">
	           	    <h2>Ajouter un utilisateur</h2>
					<div class="panel panel-info">
						<div class="panel-heading"> 
							<h3 class="panel-title">Ce que fait cet assistant</h3> 
						</div>
						<div class="panel-body">
						    <ul>
						    	<li>Ajouter un Super Admin</li>
						    </ul>
						</div>
					</div>
				</div>

	            <div class="col-md-12">         	
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
			</article> 

			<footer class="row">
				<div class="col-md-12">Developpé par Fabrice Simonet || Interface de Matthieu Andre</div>
			</footer>
		</div>
		<script>
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
							} else {
								swal("Error!", retour.data.message, "error");
							}

							if (typeof retour.data.context != 'undefined') {
								$( "#"+id ).prepend( '<div class="alert alert-success" role="alert">' + retour.data.context + '</div>' );
							}
						}, 
						timeout: function(){
							swal("Timeout!", "Le temps d'attente est trop long, demande expiré, renter votre chance.", "error");
						},
						error: function(){
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
		$_file_destination,
		$_file_sql,
		$_file_log;

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

		$this->_wp_lang 			= 'fr_FR';
		$this->_wp_api 				= 'http://api.wordpress.org/core/version-check/1.7/?locale='.$this->_wp_lang;
		$this->_wp_dir_core 		= 'core/';
		$this->_file_destination 	= 'migration_file.zip';
		$this->_file_sql 			= 'migration_bdd.sql';
		$this->_file_log 			= 'logfile.log';
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
	 * Envoie les fichiers zip et migration sur le serveur distant en FTP
	 *
	 * @param mixed[] $opts Array informations de connexion au FTP distant
	 */
	public function wp_ftp_migration($opts){

		$file 			= $this->_file_destination;
		$remote_file 	= $this->_file_destination;

		$conn_id = ftp_connect($opts['ftp_url']);
		if($conn_id == FALSE){

			return FALSE;
		}

		$login_result = ftp_login($conn_id, $opts['user_ftp'], $opts['ftp_pass']);
		if($login_result == FALSE){

			return FALSE;
		}

		// Envoie le fichier migration.php qui sert d'api distante
		ftp_put($conn_id, rtrim($opts['ftp_folder'], '/').'/'.'migration.php', 'migration.php', FTP_ASCII);
		// Envoie le fichier contenant le site a migrer
		if (ftp_put($conn_id, rtrim($opts['ftp_folder'], '/').'/'.$remote_file, $file, FTP_ASCII)) {
			ftp_close($conn_id);

			return TRUE;
		} else {
			ftp_close($conn_id);

			return FALSE;
		}
	}

	/**
	 * Efface les fichiers d'installation du serveur distant ( zip et sql )
	 */
	public function wp_clean_ftp_migration(){

		unlink($this->_file_destination);
		unlink($this->_file_sql);
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

					//if ( (int) $this->conf_post_revisions >= 0 ) {
					//	$line .= "\r\n\n " . "/** Désactivation des révisions d'articles */" . "\r\n";
					//	$line .= "define('WP_POST_REVISIONS', " . (int) $this->conf_post_revisions . ");";
					//}

					//if ( (int) $this->conf_disallow_file_edit == 1 ) {
					//	$line .= "\r\n\n " . "/** Désactivation de l'éditeur de thème et d'extension */" . "\r\n";
					//	$line .= "define('DISALLOW_FILE_EDIT', true);";
					//}

					//if ( (int) $this->conf_autosave_interval >= 60 ) {
					//	$line .= "\r\n\n " . "/** Intervalle des sauvegardes automatique */" . "\r\n";
					//	$line .= "define('AUTOSAVE_INTERVAL', " . (int) $this->conf_autosave_interval . ");";
					//}
					
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
	 * @param mixed[] $opts information de connexion a la base de donnée
	 *
	 * @return bool true|false
	 */
	public function wp_install_bdd($opts){

		// assignation des variables de connexion pour effectuer un test si la bdd existe
		$this->set_var_wp(array($opts['dbhost'], $opts['dbname'], $opts['uname'], $opts['pwd'], $opts['prefix']));

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
		
		//	We remove the default content
		/*
		if ( $this->default_content == '1' ) {
			wp_delete_post( 1, true ); // We remove the article "Hello World"
			wp_delete_post( 2, true ); // We remove the "Exemple page"
		}
		*/

		//	We update permalinks
		/*
		if ( ! empty( $this->permalink_structure ) ) {
			update_option( 'permalink_structure', $this->permalink_structure );
		}
		*/

		//	We update the media settings
		/*
		if ( ! empty( $this->thumbnail_size_w ) || !empty($this->thumbnail_size_h ) ) {
			update_option( 'thumbnail_size_w', (int) $this->thumbnail_size_w );
			update_option( 'thumbnail_size_h', (int) $this->thumbnail_size_h );
			update_option( 'thumbnail_crop', (int) $this->thumbnail_crop );
		}

		if ( ! empty( $_POST['medium_size_w'] ) || !empty( $this->medium_size_h ) ) {
			update_option( 'medium_size_w', (int) $this->medium_size_w );
			update_option( 'medium_size_h', (int) $this->medium_size_h );
		}

		if ( ! empty( $_POST['large_size_w'] ) || !empty( $this->large_size_h ) ) {
			update_option( 'large_size_w', (int) $this->large_size_w );
			update_option( 'large_size_h', (int) $this->large_size_h );
		}

		update_option( 'uploads_use_yearmonth_folders', (int) $this->uploads_use_yearmonth_folders );

		*/

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
	 * Exporte les fichiers de WP
	 */
	public function wp_export_file() {
		
		if(file_exists($this->_file_destination)){
			unlink($this->_file_destination);
		}
		
		if(function_exists('exec')){

			$this->Zip_soft('./', $this->_file_destination, 'exec');
		} elseif(function_exists('system')){

			$this->Zip_soft('./', $this->_file_destination, 'system');
		} else {

			$this->Zip('./', $this->_file_destination);
		}
	
		return TRUE;
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

			switch($worked){
				case 0:
					return TRUE;
				break;
				case 1:
					return FALSE;
				break;
				case 2:
					return FALSE;
				break;
			}

		} elseif(function_exists('system')){

			system("mysqldump --host=" . $this->_dbhost ." --user=" . $this->_dbuser ." --password=". $this->_dbpassword ." ". $this->_dbname ." > ".$this->_file_sql);

			return TRUE;
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

        	return TRUE;
		}
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
			exec("zip -r $destination $source", $output, $returnv);
		} else {
			system("zip -r $destination $source", $output, $returnv);
		}

		return !$returnv;
	}

	/**
	 * Creer des Zip recursivement
	 *
	 * @link : http://stackoverflow.com/questions/1334613/how-to-recursively-zip-a-directory-in-php methode php issus de cette doc
	 *
	 * @param string $source 		Source des fichiers
	 * @param string $destination 	Fichier de destinations ( zip )
	 *
	 * @return bool true|false
	 */
	public function Zip($source, $destination)
	{
	    if (!extension_loaded('zip') || !file_exists($source)) {

	        return false;
	    }

	    $zip = new ZipArchive();
	    if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {

	        return false;
	    }

	    $source = str_replace('\\', '/', realpath($source));

	    if (is_dir($source) === true)
	    {
	        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

	        foreach ($files as $file)
	        {
	            $file = str_replace('\\', '/', $file);

	            // Ignore "." and ".." folders
	            if( in_array(substr($file, strrpos($file, '/')+1), array('.', '..')) )
	                continue;

	            $file = realpath($file);

	            if (is_dir($file) === true)
	            {
	                if( ! $zip->addEmptyDir(str_replace($source . '/', '', $file . '/')))
					{
						throw new Exception("La memoire alouée par le serveur n'est pas sufisante");
					}
	            }
	            else if (is_file($file) === true)
	            {
	                if( ! $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file)))
					{
						throw new Exception("La memoire alouée par le serveur n'est pas sufisante");
					}
	            }
	        }
	    }
	    else if (is_file($source) === true)
	    {
	        $zip->addFromString(basename($source), file_get_contents($source));
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
	         		if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object);
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
