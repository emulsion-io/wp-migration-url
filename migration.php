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
error_reporting(-1);
ini_set('display_errors', '1');

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

include('mig_class.php');
include('mig_action.php');

$migration = new Wp_Migration();
$update    = $migration->wp_check_update();

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

?>

<!doctype html>
<html lang="en">
	<head>
		<!-- Required meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<title>Migration Wordpress Easy</title>

		<link rel="stylesheet" href="/css/bootstrap.css" crossorigin="anonymous">

		<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
		<script src="//cdn.jsdelivr.net/npm/sweetalert2@10"></script>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-Piv4xVNRyMGpqkS2by6br4gNJ7DXjqk09RmUpJ8jgGtD7zP9yug3goQfGII0yAns" crossorigin="anonymous"></script>
		
		<style type="text/css">
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

		<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
			<a class="navbar-brand" href="#">Navbar</a>
			<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarColor02" aria-controls="navbarColor02" aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>

			<div class="collapse navbar-collapse" id="navbarColor02">
				<ul class="navbar-nav mr-auto">
					<li class="nav-item active">
					<a class="nav-link" href="#">Home
						<span class="sr-only">(current)</span>
					</a>
					</li>
					<li class="nav-item">
					<a class="nav-link" href="#">Features</a>
					</li>
					<li class="nav-item">
					<a class="nav-link" href="#">Pricing</a>
					</li>
					<li class="nav-item">
					<a class="nav-link" href="#">About</a>
					</li>
					<li class="nav-item dropdown">
					<a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Dropdown</a>
					<div class="dropdown-menu">
						<a class="dropdown-item" href="#">Action</a>
						<a class="dropdown-item" href="#">Another action</a>
						<a class="dropdown-item" href="#">Something else here</a>
						<div class="dropdown-divider"></div>
						<a class="dropdown-item" href="#">Separated link</a>
					</div>
					</li>
				</ul>
				<form class="form-inline my-2 my-lg-0">
					<input class="form-control mr-sm-2" type="text" placeholder="Search">
					<button class="btn btn-secondary my-2 my-sm-0" type="submit">Search</button>
				</form>
			</div>
			</nav>

			<header class="row">
				<div class="col-12">
					<div class="jumbotron mt-4">
						<h1>ToolBox Wordpress</h1>
						<p>La boite a outils pour Wordpress</p>
					</div>
				</div>
			</header>

			<article class="row">
				<div class="col-12">
					<div class="alert alert-dismissible alert-primary" role="alert">
						Pensez a supprimer le fichier migration.php de votre installation Wordpress apres avoir effectué vos modifications.
					</div>
				</div>
			</article>

			<article class="row">
				<div class="col-6">
					<div class="card border-info mb-3" >
						<div class="card-header">Serveur</div>
						<div class="card-body">
							<h4 class="card-title">Informations : </h4>
							<div class="card-text">
								<ul>
									<li>Droit sur le dosier courant : <?php echo substr(sprintf('%o', fileperms('.')), -4); ?></li>
									<li>Fonction exec() <?php echo (function_exists('exec'))? " is enabled" : " is disabled"; ?></li>
									<li>Fonction system() <?php echo (function_exists('system'))? " is enabled" : " is disabled"; ?></li>
									<li>Memoire allouée : <?php echo $migration->get_memory_limit(); ?></li>
								</ul>
							</div>
						</div>
					</div>
				</div>

				<div class="col-6">
					<div class="card border-info mb-3" >
						<div class="card-header">Script</div>
						<div class="card-body">
							<h4 class="card-title">Informations : </h4>
							<div class="card-text">
								<ul>
									<li>Votre version : <?php echo $update['version_courante']; ?></li>
									<li>Derniere version disponnible : <?php echo $update['version_enligne']; ?></li>
								</ul>
								<?php if($update['maj_dipso'] == TRUE): ?>
							
									<form id="action_update" method="post">
										<button type="submit" id="go_action_update" class="btn btn-primary">Effecuer la mise a jour</button>
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
								<?php endif; ?>

							</div>
						</div>
					</div>
				</div>

			</article>

			<article class="row">
				<div class="col-12">
					<div class="card border-info mb-3" >
						<div class="card-header">Wordpress</div>
						<div class="card-body">
							<h4 class="card-title">Informations : </h4>
							<div class="card-text">
								<?php if($wp_exist === false) : ?>
									Wordpress n'est pas présent sur ce serveur, voulez-vous l'installer ?
									<div class="row mt-3">
										<div class="col-6">
											<form id="action_dl_zip" method="post">
												<button type="submit" id="go_action_dl_zip" class="btn btn-primary">Télecharger le zip de Wordpress sur le serveur</button>
											</form>
											<script>
												$( "#action_dl_zip" ).submit(function( event ) {
													event.preventDefault();
													var donnees = {
														action_dl_zip	: 'ok'
													}
													sendform('action_dl_zip', donnees, 'Telecharge, extrait et install un Wordpress');
												});
											</script>
										</div>
										<div class="col-6">
											<form id="action_update" method="post">
												<button type="submit" id="go_action_update" class="btn btn-primary">Télecharger et Installer Wordpress sur le serveur</button>
											</form>
										</div>
									</div>
							
								<?php else: ?>
									Wordpress est présent sur ce serveur.
								<?php endif; ?>
							</div>
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
									uname				: $('#uname').val(),
									pwd				: $('#pwd').val(),
									prefix			: $('#prefix').val(),
									weblog_title	: $('#weblog_title').val(),
									user_login		: $('#user_login').val(),
									admin_email		: $('#admin_email').val(),
									admin_password	: $('#admin_password').val(),
									debug				: $('#debug').is(':checked'),
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

				Swal.fire({
					title: 'Etes-vous sur de vouloir effectuer cette action ?',
					confirmButtonText: 'Oui',
					showCancelButton: true,
					text:	'',
					showLoaderOnConfirm: true,
					preConfirm: () => {

						return $.ajax({
							url: "migration.php",
							type : 'post',
							data : donnees,
							dataType: 'json',
							success: function(retour){
								$("#go_"+id).button('reset');
								
								if(retour.success == true) {
									
									// condition particuliaire d'actions a realiser en fonction des forms
									
									//-- Affiche la zone d'info pour la migration
									if(id == 'action_migration_testsite') {
										$('#action_migration').show();
									}

									return retour.data.message;

								} else {
									Swal.showValidationMessage(retour.data.message);
								}

								/*if (typeof retour.data.context != 'undefined') {
									$( "#"+id ).prepend( '<div class="alert alert-success" role="alert">' + retour.data.context + '</div>' );
								}*/
							}, 
							timeout: function(){
								$("#go_"+id).button('reset');
								Swal.showValidationMessage("Le temps d'attente est trop long, demande expiré, renter votre chance.");
							},
							error: function(){
								$("#go_"+id).button('reset');
								Swal.showValidationMessage("Une erreur est intervenu dans le traitement de la requête, renter votre chance.");
							}
						});
					},
 					allowOutsideClick: () => !Swal.isLoading()
				}).then((result) => {
					//console.log(result)
					if (result.isConfirmed) {
						Swal.fire({
							title: 'Action terminé',
							text: result.value.data.message
						});
					}
				})
			}
		</script>
   </body>
</html>
