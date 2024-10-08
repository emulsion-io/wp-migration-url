<?php

/**
 * @author Fabrice Simonet
 * @link http://emulsion.io
 *
 * @version 2.6
*/

/**	
 * 
 * 2024-10-08
 * 
 * Reprise du script.
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

if(ini_get('allow_url_fopen') == FALSE) {
	echo "La fonction file_get_contents() est désactivée sur ce serveur, veuillez l'activer pour continuer."; exit;
}

/**
 * Vos zips d'installation de Wordpress
 */
$zips_wp = [];

/**
 * Variable de status d'execution du script
 */
$retour_url                   = FALSE;
$retour_migration             = FALSE;
$retour_migration_api         = FALSE;
$retour_migration_log         = FALSE;
$retour_export                = FALSE;
$retour_import                = FALSE;
$retour_export_sql            = FALSE;
$retour_import_sql            = FALSE;
$retour_htaccess              = FALSE;
$retour_dl                    = FALSE;
$retour_dl_full               = FALSE;
$retour_clean_revision        = FALSE;
$retour_clean_spam            = FALSE;
$retour_plug_install          = FALSE;
$retour_delete_theme          = FALSE;
$retour_add_user              = FALSE;
$retour_action_dl_zip         = FALSE;
$retour_action_dl_zip_extract = FALSE;

include('mig_class.php');

$migration = new Wp_Migration();
$update    = $migration->migration_check_update();

include('mig_action.php');

/**
 * Si un fichier wp-config.php existe, le script comprend que WP est deja installé
 */
if(file_exists('wp-config.php')) {

	define( 'WP_INSTALLING', true );

	$migration->set_var_wp();

	$wp_exist = TRUE;
	$wp_exist_install = TRUE;

	// Recupere les informations sur le WP courant
	$site_url = $migration->wp_get_info("siteurl");

	if($site_url == false) {
		$site_url = [];
		$site_url['option_value'] = '';

		$wp_exist_install = FALSE;
	}

} else {
	$site_url['option_value'] = '';

	$wp_exist = FALSE;
	$wp_exist_install = FALSE;

}

?>

<!doctype html>
<html lang="en">
	<head>
		<!-- Required meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<title>Toolbox Wordpress</title>
		<link rel="icon" type="image/png" href="https://emulsion.io/favicon.png" />

		<link rel="stylesheet" href="https://cdn.emulsion.io/wp-migration/css/bootstrap.css">
		<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
		<script src="//cdn.jsdelivr.net/npm/sweetalert2@10"></script>
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@sweetalert2/themes@4.0.3/dark/dark.min.css">
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-Piv4xVNRyMGpqkS2by6br4gNJ7DXjqk09RmUpJ8jgGtD7zP9yug3goQfGII0yAns" crossorigin="anonymous"></script>
		
		<style type="text/css">
			body { background-color: #14161a; }
			.menushow { cursor: pointer; }
			.text-orange { color: #ffb70f; }
			.text-orange:hover { color: #fff; }
			.border-info { border-color: #ffb70f !important; }
			.card { background-color: #181b20; }
			.text-green { color: #00ff00; }
			.text-red { color: #ff0000; }
		</style>
	</head>
	<body>
		<div class="container">

			<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
				<a class="navbar-brand" href="#">Toolbox</a>
				<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar" aria-controls="navbar" aria-expanded="false" aria-label="Toggle navigation">
					<span class="navbar-toggler-icon"></span>
				</button>

				<div class="collapse navbar-collapse" id="navbar">
					<ul class="navbar-nav mr-auto">
						<li class="nav-item active">
							<a class="nav-link" href="#">Home</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="https://emulsion.io">Emulsion.io</a>
						</li>
						<li class="nav-item dropdown">
							<a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Outils</a>
							<div class="dropdown-menu">
								<a class="dropdown-item open-tools" data-open="#tools-1" data-go="#go-tools-1">Telecharger et extraire un Wordpress avec possibilité de l'installer</a>
								<a class="dropdown-item open-tools" data-open="#tools-2" data-go="#go-tools-2">Modifier les Urls de votre installation Wordpress</a>
								<a class="dropdown-item open-tools" data-open="#tools-2" data-go="#go-tools-2-1">Modifier les Urls de votre installation Wordpress en SQL</a>
								<a class="dropdown-item open-tools" data-open="#tools-3" data-go="#go-tools-3">Creer le fichier .htaccess</a>
								<a class="dropdown-item open-tools" data-open="#tools-4" data-go="#go-tools-4">Effacer toutes les revisions de votre Wordpress</a>
								<a class="dropdown-item open-tools" data-open="#tools-5" data-go="#go-tools-5">Effacer tous les commentaires non validés (Spam)</a>
								<a class="dropdown-item open-tools" data-open="#tools-6" data-go="#go-tools-6">Installer les plugins de votre choix</a>
								<a class="dropdown-item open-tools" data-open="#tools-7" data-go="#go-tools-7">Supprime les themes par defaut de Wordpress</a>
								<a class="dropdown-item open-tools" data-open="#tools-8" data-go="#go-tools-8">Ajouter un administrateur a votre installation</a>
								<div class="dropdown-divider"></div>
								<a class="dropdown-item open-tools" data-open="#tools-9" data-go="#go-tools-9">Modifier le prefix des tables</a>
								<a class="dropdown-item open-tools" data-open="#tools-10" data-go="#go-tools-10">Supprime toutes les fichiers de Wordpress</a>
							</div>
						</li>
					</ul>
				</div>
			</nav>

			<header class="row">
				<div class="col-12">
					<div class="jumbotron mt-4">
						<h1><span class="text-orange">Toolbox</span> Wordpress</h1>
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
				<div class="col-12 col-md-6">
					<div class="card border-info mb-3" >
						<div class="card-header">Votre Serveur</div>
						<div class="card-body">
							<h4 class="card-title"></h4>
							<div class="card-text">
								<ul>
									<li>Droit sur le dosier courant : <?php echo substr(sprintf('%o', fileperms('.')), -4); ?></li>
									<li>Version de PHP : <?php echo phpversion(); ?></li>

									<li>Fonction mail() : <?php echo (function_exists('mail'))? " <span class='text-green'>is enabled</span>" : " <span class='text-red'>is disabled</span>"; ?></li>
									<li>Fonction file_get_contents() <?php echo ((ini_get('allow_url_fopen'))) ? " <span class='text-green'>is enabled</span>" : " <span class='text-red'>is disabled</span>"; ?></li>
									<li>Fonction file_put_contents() <?php echo (function_exists('file_put_contents'))? " <span class='text-green'>is enabled</span>" : "<span class='text-red'> is disabled</span>"; ?></li>
									<li>Fonction fopen() <?php echo (function_exists('fopen'))? " <span class='text-green'>is enabled</span>" : " <span class='text-red'>is disabled</span>"; ?></li>
									<li>Fonction shell_exec() <?php echo (function_exists('shell_exec'))? " <span class='text-green'>is enabled</span>" : " <span class='text-red'>is disabled</span>"; ?></li>
									<li>Fonction exec() <?php echo (function_exists('exec'))? " <span class='text-green'>is enabled</span>" : " <span class='text-red'>is disabled</span>"; ?></li>
									<li>Fonction system() <?php echo (function_exists('system'))? " <span class='text-green'>is enabled</span>" : " <span class='text-red'>is disabled</span>"; ?></li>
									<li>Memoire allouée : <?php echo $migration->get_memory_limit(); ?></li>
								</ul>
							</div>
						</div>
					</div>
				</div>

				<div class="col-12 col-md-6">
					<div class="card border-info mb-3" >
						<div class="card-header">Ce Script</div>
						<div class="card-body">
							<h4 class="card-title"></h4>
							<div class="card-text">
								<ul>
									<li>Votre version : <?php echo $update['version_courante']; ?></li>
									<li>Derniere version disponnible : <?php echo $update['version_enligne']; ?></li>
								</ul>
								<?php if($update['maj_dipso'] == TRUE): ?>
							
									<form id="action_update" method="post">
										<button type="submit" id="go_action_update" class="btn btn-primary">Effecuer la mise a jour du script</button>
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
						<div class="card-header">Information sur votre installation Wordpress</div>
						<div class="card-body">
							<h4 class="card-title"></h4>
							<div class="card-text">
								<?php if($wp_exist === false) : ?>
									Wordpress n'est pas présent sur ce serveur, voulez-vous l'installer ?
									<div class="row mt-3">
										<div class="col-6">
											<form id="action_dl_zip" method="post">
												<button type="submit" id="go_action_dl_zip" class="btn btn-primary">Envoie le zip de Wordpress sur le serveur</button>
											</form>
											<script>
												$( "#action_dl_zip" ).submit(function( event ) {
													event.preventDefault();
													var donnees = {
														action_dl_zip : 'ok'
													}
													sendform('action_dl_zip', donnees, 'Le zip de Wordpress est sur le serveur');
												});
											</script>
										</div>
										<div class="col-6">
											<form id="action_dl_zip_extract" method="post">
												<button type="submit" id="go_action_dl_zip_extract" class="btn btn-primary">Envoyer et extraire Wordpress sur le serveur</button>
											</form>
											<script>
												$( "#action_dl_zip_extract" ).submit(function( event ) {
													event.preventDefault();
													var donnees = {
														action_dl_zip_extract : 'ok',
													}
													sendform('action_dl_zip_extract', donnees, 'Wordpress est extrait sur le serveur');
												});
											</script>
										</div>
									</div>
									<div class="row mt-3">
										<?php if($zips_wp) : ?>
											<div class="col-12">
												<h3>Vos instances Custom Wordpress</h3>
											</div>

											<?php $i = 0; foreach($zips_wp as $zip) : ?>
												<div class="col-6">
													<form id="action_dl_zip_extract_<?=$i;?>" method="post">
														<button type="submit" id="go_action_dl_zip" class="btn btn-primary">Envoyer et extraire le zip de <?= $zip['nom']; ?> sur le serveur</button>
													</form>
													<script>
														$( "#action_dl_zip_extract_<?=$i;?>" ).submit(function( event ) {
															event.preventDefault();
															var donnees = {
																action_dl_zip_extract : 'ok',
																url : '<?= $zip['fichier']; ?>'
															}
															sendform('action_dl_zip_extract', donnees, 'Le Wordpress de <?= $zip['nom']; ?> est extrait sur le serveur');
														});
													</script>
												</div>
											<?php $i++; endforeach; ?>
										<?php endif; ?>
									</div>
									<div class="row mt-3">
										<?php if($zips_wp) : ?>
											<?php $i = 0; foreach($zips_wp as $zip) : ?>
												<div class="col-6">
													<form id="action_dl_zip_<?=$i;?>" method="post">
														<button type="submit" id="go_action_dl_zip" class="btn btn-primary">Envoyer le zip de <?= $zip['nom']; ?> sur le serveur</button>
													</form>
													<script>
														$( "#action_dl_zip_<?=$i;?>" ).submit(function( event ) {
															event.preventDefault();
															var donnees = {
																action_dl_zip : 'ok',
																url : '<?= $zip['fichier']; ?>'
															}
															sendform('action_dl_zip', donnees, 'Le zip du Wordpress de <?= $zip['nom']; ?> est sur le serveur');
														});
													</script>
												</div>
											<?php $i++; endforeach; ?>
										<?php endif; ?>
									</div>

								<?php else: ?>
									<ul>
										<li>Wordpress est présent sur ce serveur.</li>
										<?php if($wp_exist_install === TRUE) : ?>
											<li>URL du site : <?php echo $site_url['option_value']; ?></li>
										<?php else : ?>
											<li>URL du site : <span class="text-danger">Wordpress non configuré</span></li>
										<?php endif; ?>
										<li>Version installée de WP : <? //= $wp_version; ?></li>
									</ul>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</div>

			</article>

			<h2>Outils</h2>

			<?php /*
			<div class="row mb-3">
				<div class="col-12 mb-2">
					<button class="btn btn-primary btn-block text-left" type="button" data-toggle="collapse" data-target="#tools-0" aria-expanded="false" aria-controls="tools-1">Tools-0</button>
				</div>
				<div class="col-12">
					<div class="collapse" id="tools-0">
						<div class="card card-body">
							<div class="text-warning mb-3">
								
							</div>
						</div>
					</div>
				</div>
			</div>
			*/ ?>

			<div class="row mb-3">
				<div class="col-12 mb-2">
					<button id="go-tools-1" class="btn btn-primary btn-block text-left" type="button" data-toggle="collapse" data-target="#tools-1" aria-expanded="false" aria-controls="tools-1">
						Telecharger et extraire un Wordpress avec possibilité de l'installer
					</button>
				</div>
				<div class="col-12">
					<div class="collapse" id="tools-1">
						<div class="card card-body">

							<div class="text-warning mb-3">
								Telecharge et extrait Wordpress dans sa derniere version du site officiel, et l'installe en remplissant les options.
							</div>
						
							<form id="action_dl" method="post">

								<div class="custom-control custom-checkbox">
									<input type="checkbox" class="custom-control-input" id="install_full" name="install_full" value="1" >
									<label class="custom-control-label" for="install_full">Activer les options d'instalations sur le serveur pour installer la base de données.</label>
								</div>

								<div id="install_full_div" class="mt-3" style="display:none;">
									<h3>Information de la base de données</h3>

									<div class="form-group">
										<label for="dbhost">Serveur MySQL</label>
										<input type="text" class="form-control tools-1-required" id="dbhost" name="dbhost" placeholder="localhost" value="" >
									</div>
									<div class="form-group">
										<label for="dbname">Nom de la base de données MySQL</label>
										<input type="text" class="form-control tools-1-required" id="dbname" name="dbname" placeholder="" value="" >
									</div>					
									<div class="form-group">
										<label for="uname">Utilisateur MySQL</label>
										<input type="text" class="form-control tools-1-required" id="uname" name="uname" placeholder="" value="">
									</div>
									<div class="form-group">
										<label for="pwd">Mot de passe MySQL</label>
										<input type="text" class="form-control tools-1-required" id="pwd" name="pwd" placeholder="" value="">
									</div>

									<div class="form-group">
										<label for="prefix">Prefix des tables ( laisser wp_ par defaut )</label>
										<input type="text" class="form-control tools-1-required" id="prefix" name="prefix" placeholder="wp_" value="wp_">
									</div>

									<h3>Information Wordpress</h3>

									<div class="form-group">
										<label for="weblog_title">Titre du site</label>
										<input type="text" class="form-control tools-1-required" id="weblog_title" name="weblog_title" placeholder="" value="">
									</div>
									<div class="form-group">
										<label for="user_login">Utilisateur ( administrateur )</label>
										<input type="text" class="form-control tools-1-required" id="user_login" name="user_login" placeholder="" value="">
									</div>
									<div class="form-group">
										<label for="admin_email">Email</label>
										<input type="text" class="form-control tools-1-required" id="admin_email" name="admin_email" placeholder="" value="">
									</div>
									<div class="form-group">
										<label for="admin_password">Mot de passe</label>
										<input type="text" class="form-control tools-1-required" id="admin_password" name="admin_password" placeholder="" value="">
									</div>

									<div class="custom-control custom-checkbox">
										<input type="checkbox" id="debug" name="debug" class="custom-control-input" value="1"> 
										<label class="custom-control-label" for="debug">Debug</label>
									</div>
									<div class="custom-control custom-checkbox">
										<input type="checkbox" id="debug_display" name="debug_display" class="custom-control-input" value="1"> 
										<label class="custom-control-label" for="debug_display">Debug_display</label>
									</div>
									<div class="custom-control custom-checkbox">
										<input type="checkbox" id="debug_log" name="debug_log" class="custom-control-input" value="1"> 
										<label class="custom-control-label" for="debug_log">Debug_log</label>
									</div>					
									<div class="custom-control custom-checkbox">
										<input type="checkbox" id="blog_public" name="blog_public" class="custom-control-input" value="1"> 
										<label class="custom-control-label" for="blog_public">Indexer le site</label>
									</div>
								</div>

								<?php if($wp_exist == TRUE) : ?>
									<div class="alert alert-dismissible alert-danger mt-3">
										<button type="button" class="close" data-dismiss="alert">&times;</button>
										<strong>Oh Attention!</strong> Une version de Wordpress est deja installé sur le serveur
									</div>
								<?php endif; ?>	

								<div class="form-group mt-3">
									<button id="go_action_dl" type="submit" class="btn btn-primary">Lancer la procedure</button>
								</div>
							</form>
							<script>
								$( "#action_dl" ).submit(function( event ) {
									event.preventDefault();

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

									sendform('action_dl', donnees, 'Telecharge, extrait et install Wordpress');
								});
							
								var tools_1_required = 0;
								$("#install_full").on('click', function() {
									$('#install_full_div').toggle();
									if(tools_1_required === 0){
										$('.tools-1-required').prop('required', true);
										tools_1_required = 1;
									} else {
										$('.tools-1-required').prop('required', false);
										tools_1_required = 0;
									}
								});

							</script>

						</div>
					</div>
				</div>
			</div>
	
			<div class="row mb-3">
				<div class="col-12 mb-2">
					<buttonn id="go-tools-2" class="btn btn-primary btn-block text-left" type="button" data-toggle="collapse" data-target="#tools-2" aria-expanded="false" aria-controls="tools-2">Modifier les Urls de votre installation Wordpress</buttonn>
				</div>
				<div class="col-12">
					<div class="collapse" id="tools-2">
						<div class="card card-body">
							<div class="text-warning mb-3">
								<ul>
									<li>Modifie les URLs dans la table wp_option, home et siteurl sont affectés</li>
									<li>Modifie les URLs dans la table wp_posts, guid et post_content sont affectés</li>
									<li>Modifie les URLs dans la table wp_postmeta, toutes les meta_value auront la nouvelle URL</li>
								</ul>
							</div>

							<form id="action_change_url" method="post">
								<div class="form-group">
									<label for="old">Ancienne URL</label>
									<input type="text" class="form-control" id="old" name="old" placeholder="Ancienne URL sans / a la fin" value="<?php echo $site_url['option_value']; ?>" required>
								</div>
								<div class="form-group">
									<label for="new">Nouvelle URL</label>
									<input type="text" class="form-control" id="new" name="new" placeholder="Nouvelle URL sans / a la fin" value="https://<?php echo $_SERVER['SERVER_NAME'] . rtrim(dirname($_SERVER['REQUEST_URI']), '/'); ?>" required>
								</div>
								<?php if($wp_exist == TRUE) : ?>
								<div class="form-group">
									<button type="submit" id="go_action_change_url" class="btn btn-primary">Mettre a jour</button>
								</div>
								<?php else: ?>
									<div class="alert alert-dismissible alert-danger mt-3">
										<button type="button" class="close" data-dismiss="alert">&times;</button>
										<strong>Oh Attention!</strong> Wordpress n'est pas installé sur ce serveur.
									</div>
								<?php endif; ?>
							</form>
							<script>
								$( "#action_change_url" ).submit(function( event ) {
									event.preventDefault();

									var donnees = {
										'action_change_url'	: 'ok',
										'old' 				: $('#old').val(),
										'new' 				: $('#new').val()
									}
									sendform('action_change_url', donnees, 'Ecriture des nouvelles Urls');
									
								});
							</script>

						</div>
					</div>
				</div>
			</div>

			<div class="row mb-3">
				<div class="col-12 mb-2">
					<buttonn id="go-tools-2-1" class="btn btn-primary btn-block text-left" type="button" data-toggle="collapse" data-target="#tools-2-1" aria-expanded="false" aria-controls="tools-2">Modifier les Urls de votre installation Wordpress en SQL</buttonn>
				</div>
				<div class="col-12">
					<div class="collapse" id="tools-2-1">
						<div class="card card-body">
							<div class="text-warning mb-3">
								<ul>
									<li>Modifie les URLs dans la table wp_option, home et siteurl sont affectés</li>
									<li>Modifie les URLs dans la table wp_posts, guid et post_content sont affectés</li>
									<li>Modifie les URLs dans la table wp_postmeta, toutes les meta_value auront la nouvelle URL</li>
								</ul>
							</div>

							<form id="action_change_url_sql" method="post">
								<div class="form-group">
									<label for="old">Ancienne URL</label>
									<input type="text" class="form-control" id="oldUrl" name="oldUrl" placeholder="Ancienne URL sans / a la fin" value="<?php echo $site_url['option_value']; ?>" required>
								</div>
								<div class="form-group">
									<label for="new">Nouvelle URL</label>
									<input type="text" class="form-control" id="newUrl" name="newUrl" placeholder="Nouvelle URL sans / a la fin" value="http://<?php echo $_SERVER['SERVER_NAME'] . rtrim(dirname($_SERVER['REQUEST_URI']), '/'); ?>" required>
								</div>
								<div class="form-group">
									<label for="new">Préfixe des tables</label>
									<input type="text" class="form-control" id="prefix" name="prefix" placeholder="wp" value="wp" required>
								</div>
								
								<div class="form-group">
									<button type="submit" id="go_action_change_url_sql" class="btn btn-primary">Mettre a jour</button>
								</div>

								<div class="form-group">
									<label for="sqlOutput">Requêtes SQL</label>
									<textarea class="form-control" id="sqlOutput" rows="10" readonly></textarea>
								</div>

								<div class="form-group">
									<button type="button" class="btn btn-primary" onclick="copyToClipboard()">Copier</button>
								</div>

							</form>
							<script>
								$( "#action_change_url_sql" ).submit(function( event ) {
									event.preventDefault();

									generateSQL();
								});
							</script>

						</div>
					</div>
				</div>
			</div>

			<div class="row mb-3">
				<div class="col-12 mb-2">
					<button id="go-tools-3" class="btn btn-primary btn-block text-left" type="button" data-toggle="collapse" data-target="#tools-3" aria-expanded="false" aria-controls="tools-3">Creer le fichier .htaccess</button>
				</div>
				<div class="col-12">
					<div class="collapse" id="tools-3">
						<div class="card card-body">
							<div class="text-warning mb-3">
								<ul>
									<li>Creer le fichier .htaccess avec la configuration de votre serveur automatiquement</li>
									<li>Ajoute des regles de securité pour Wordpress</li>
								</ul>
							</div>

							<form id="action_htaccess" method="post">
								<div class="form-group">
									<button id="go_action_htaccess" type="submit" class="btn btn-primary">Creer le fichier .htaccess</button>
								</div>
							</form>
							<script>
								$( "#action_htaccess" ).submit(function( event ) {
									var donnees = {
										'action_htaccess'	: 'ok'
									}
									sendform('action_htaccess', donnees, 'Creer le fichier .htaccess');
									event.preventDefault();
								});
							</script>
						</div>
					</div>
				</div>
			</div>
			
			<div class="row mb-3">
				<div class="col-12 mb-2">
					<button id="go-tools-4" class="btn btn-primary btn-block text-left" type="button" data-toggle="collapse" data-target="#tools-4" aria-expanded="false" aria-controls="tools-4">Effacer toutes les revisions de votre Wordpress</button>
				</div>
				<div class="col-12">
					<div class="collapse" id="tools-4">
						<div class="card card-body">
							<div class="text-warning mb-4">
								<ul>
									<li>Efface les revisions des articles, des pages et de tous les contenus.</li>
									<li>Les revisions sont des versions antérieures de vos page qui peuvent etre restaurer.</li>
								</ul>
							</div>

							<form id="action_clean_revision" method="post">
								<?php if($wp_exist == TRUE) : ?>
								<div class="form-group">
									<button type="submit" id="go_action_clean_revision" class="btn btn-primary">Effacer les revisions</button>
								</div>
								<?php else: ?>
									<div class="alert alert-dismissible alert-danger mt-3">
										<button type="button" class="close" data-dismiss="alert">&times;</button>
										<strong>Oh Attention!</strong> Wordpress n'est pas installé sur ce serveur.
									</div>
								<?php endif; ?>
							</form>
							<script>
								$( "#action_clean_revision" ).submit(function( event ) {
									event.preventDefault();
									var donnees = {
										'action_clean_revision'	: 'ok'
									}
									sendform('action_clean_revision', donnees, 'Efface toutes les revisions');
								});
							</script>
						</div>
					</div>
				</div>
			</div>

			<div class="row mb-3">
				<div class="col-12 mb-2">
					<button id="go-tools-5" class="btn btn-primary btn-block text-left" type="button" data-toggle="collapse" data-target="#tools-5" aria-expanded="false" aria-controls="tools-5">Effacer tous les commentaires non validés (Spam)</button>
				</div>
				<div class="col-12">
					<div class="collapse" id="tools-5">
						<div class="card card-body">
							<div class="text-warning mb-3">
								<ul>
									<li>Efface tous les commentaires que vous n'avez pas validés</li>
									<li>Permet de supprimer tres simplement une vague de spam</li>
								</ul>
							</div>

							<form id="action_clean_spam" method="post">
								<?php if($wp_exist == TRUE) : ?>
								<div class="form-group">
									<button id="go_action_clean_spam" type="submit" class="btn btn-primary">Effacer les commentaires non validés</button>
								</div>
								<?php else: ?>
									<div class="alert alert-dismissible alert-danger mt-3">
										<button type="button" class="close" data-dismiss="alert">&times;</button>
										<strong>Oh Attention!</strong> Wordpress n'est pas installé sur ce serveur.
									</div>
								<?php endif; ?>
							</form>
							<script>
								$( "#action_clean_spam" ).submit(function( event ) {
									event.preventDefault();
									var donnees = {
										'action_clean_spam'	: 'ok'
									}
									sendform('action_clean_spam', donnees, 'Efface tous les commentaires non validés');
								});
							</script>
						</div>
					</div>
				</div>
			</div>

			<div class="row mb-3">
				<div class="col-12 mb-2">
					<button id="go-tools-6" class="btn btn-primary btn-block text-left" type="button" data-toggle="collapse" data-target="#tools-6" aria-expanded="false" aria-controls="tools-6">Installer les plugins de votre choix</button>
				</div>
				<div class="col-12">
					<div class="collapse" id="tools-6">
						<div class="card card-body">
							<div class="text-warning mb-3">
								<ul>
									<li>Instale tous les plugins public de votre choix</li>
									<li>vous retrouverez tous les plugins Wordpress public sur ce site : https://fr.wordpress.org/plugins/</li>
									<li>Merci de séparer les noms par une virgule</li>
								</ul>
							</div>

							<form id="action_plug_install" method="post">
								<div class="form-group">
									<label for="plug_install_liste">Liste des plugins</label>
									<input type="text" class="form-control" id="plug_install_liste" name="plug_install_liste" placeholder="Merci de séparer les noms par une virgule" value="">
								</div>

								<?php if($wp_exist == TRUE) : ?>
								<div class="form-group">
									<button id="go_action_plug_install" type="submit" class="btn btn-primary">Installer les plugins</button>
								</div>
								<?php else: ?>
									<div class="alert alert-dismissible alert-danger mt-3">
										<button type="button" class="close" data-dismiss="alert">&times;</button>
										<strong>Oh Attention!</strong> Wordpress n'est pas installé sur ce serveur.
									</div>
								<?php endif; ?>	
							</form>
							<script>
								$( "#action_plug_install" ).submit(function( event ) {
									event.preventDefault();
									var donnees = {
										action_plug_install	: 'ok',
										plug_install_liste 	: $('#plug_install_liste').val(),
									}
									sendform('action_plug_install', donnees, 'Installe les plugins');
								});
							</script>
						</div>
					</div>
				</div>
			</div>

			<div class="row mb-3">
				<div class="col-12 mb-2">
					<button id="go-tools-7" class="btn btn-primary btn-block text-left" type="button" data-toggle="collapse" data-target="#tools-7" aria-expanded="false" aria-controls="tools-7">Supprime les themes par defaut de Wordpress</button>
				</div>
				<div class="col-12">
					<div class="collapse" id="tools-7">
						<div class="card card-body">
							<div class="text-warning mb-3">
								<ul>
									<li>Supprime l'ensemble des themes suivant : </li>
									<li>twentytwentyfour</li>
									<li>twentyfourteen</li>
									<li>twentythirteen</li>
									<li>twentytwelve</li>
									<li>twentyeleven</li>
									<li>twentyten</li>
								</ul>
							</div>

							<form id="action_delete_theme" method="post">
								<?php if($wp_exist == TRUE) : ?>
								<div class="form-group">
									<button id="go_action_delete_theme" type="submit" class="btn btn-primary">Supprime les themes</button>
								</div>
								<?php else: ?>
									<div class="alert alert-dismissible alert-danger mt-3">
										<button type="button" class="close" data-dismiss="alert">&times;</button>
										<strong>Oh Attention!</strong> Wordpress n'est pas installé sur ce serveur.
									</div>
								<?php endif; ?>	
							</form>
							<script>
								$( "#action_delete_theme" ).submit(function( event ) {
									event.preventDefault();
									var donnees = {
										'action_delete_theme'	: 'ok'
									}
									sendform('action_delete_theme', donnees, 'Supprime les themes defaut de Wordpress');
								});
							</script>
						</div>
					</div>
				</div>
			</div>

			<div class="row mb-3">
				<div class="col-12 mb-2">
					<button id="go-tools-8" class="btn btn-primary btn-block text-left" type="button" data-toggle="collapse" data-target="#tools-8" aria-expanded="false" aria-controls="tools-8">Ajouter un administrateur a votre installation</button>
				</div>
				<div class="col-12">
					<div class="collapse" id="tools-8">
						<div class="card card-body">
							<div class="text-warning mb-3">
								<ul>
									<li>Ajouter un Super Admin dans la base de donnees de votre installation</li>
								</ul>
							</div>

							<form id="action_add_user" method="post">
								<div class="form-group">
									<label for="user">Pseudo</label>
									<input type="text" class="form-control" id="user" name="user" placeholder="" value="" required>
								</div>
								<div class="form-group">
									<label for="pass">Mot de passe</label>
									<input type="password" class="form-control" id="pass" name="pass" placeholder="" value="" required>
								</div>
								<?php if($wp_exist == TRUE) : ?>
								<div class="form-group">
									<button id="go_action_add_user" type="submit" class="btn btn-primary">Ajouter l'utilisateur</button>
								</div>
								<?php else: ?>
									<div class="alert alert-dismissible alert-danger mt-3">
										<button type="button" class="close" data-dismiss="alert">&times;</button>
										<strong>Oh Attention!</strong> Wordpress n'est pas installé sur ce serveur.
									</div>
								<?php endif; ?>	
							</form>
							<script>
								$( "#action_add_user" ).submit(function( event ) {
									event.preventDefault();
									var donnees = {
										action_add_user	: 'ok',
										user 			: $('#user').val(),
										pass 			: $('#pass').val(),
									}
									sendform('action_add_user', donnees, 'Ajouter un utilisateur');
								});
							</script>
						</div>
					</div>
				</div>
			</div>

			<div class="row mb-3">
				<div class="col-12 mb-2">
					<button id="go-tools-9" class="btn btn-primary btn-block text-left" type="button" data-toggle="collapse" data-target="#tools-9" aria-expanded="false" aria-controls="tools-9">Modifier le prefix des tables</button>
				</div>
				<div class="col-12">
					<div class="collapse" id="tools-9">
						<div class="card card-body">
							<div class="text-warning mb-3">
								<ul>
									<li>Permet de modifier le prefix wp_ des tables par un de votre choix</li>
								</ul>
							</div>

							<form id="action_prefix_edit" method="post">
								<div class="form-group">
									<label for="prefix_edit">Prefix</label>
									<input type="text" class="form-control" id="prefix_edit" name="prefix_edit" placeholder="wp_" value="" required>
								</div>
								<?php if($wp_exist == TRUE) : ?>
								<div class="form-group">
									<button id="go_action_prefix_edit" type="submit" class="btn btn-primary">Modifier le prefix des tables</button>
								</div>
								<?php else: ?>
									<div class="alert alert-dismissible alert-danger mt-3">
										<button type="button" class="close" data-dismiss="alert">&times;</button>
										<strong>Oh Attention!</strong> Wordpress n'est pas installé sur ce serveur.
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
				</div>
			</div>

			<div class="row mb-3">
				<div class="col-12 mb-2">
					<button id="go-tools-10" class="btn btn-primary btn-block text-left" type="button" data-toggle="collapse" data-target="#tools-10" aria-expanded="false" aria-controls="tools-10">Supprimer toutes les fichiers de Wordpress</button>
				</div>
				<div class="col-12">
					<div class="collapse" id="tools-10">
						<div class="card card-body">
							<div class="text-warning mb-3">
								<ul>
									<li>Efface tous les fichiers correspondant à l'installation de Wordpress</li>
								</ul>
							</div>

							<form id="action_purge" method="post">
								<div class="form-group">
									<button id="go_action_purge" type="submit" class="btn btn-primary">Effacer les tous fichiers Wordpress</button>
								</div>
							</form>
							<script>
								$( "#action_purge" ).submit(function( event ) {
									event.preventDefault();
									var donnees = {
										action_purge	: 'ok',
									}
									sendform('action_purge', donnees, 'Purger Fichiers Wordpress');
								});
							</script>
						</div>
					</div>
				</div>
			</div>

			<footer class="row">
				<div class="col-12 my-3 text-center">
					<strong>ToolBox Wordpress</strong> par <a class="text-orange" href="https://emulsion.io">Fabrice Simonet</a>.
					<a href="https://emulsion.io" title="Agence emulsion.io | Simonet Fabrice" style="text-decoration:none; margin-top:20px; display: block;">
						<img src="https://cdn.emulsion.io/signature/fond-noir.svg" width="25px" height="23px" alt="Agence emulsion.io | Simonet Fabrice"> <span style="color:#fff; vertical-align: bottom;">Emulsion</span><span style="color:#ffa726; vertical-align: bottom;">.io</span>
					</a>
				</div>
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

			$(".open-tools").on('click', function() {

				$( $(this).attr('data-open') ).collapse();
				scroll_to_anchor($(this).attr('data-go'));

			});
			
			function scroll_to_anchor(anchor_id) {
				var tag = $(anchor_id);
				$('html,body').animate({scrollTop: tag.offset().top},'slow');
			}

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
									Swal.fire({
										icon: 'error',
										title: 'Erreur',
										text: retour.data.message
									});
								}
							}, 
							timeout: function(){
								$("#go_"+id).button('reset');
								Swal.fire({
									icon: 'error',
									title: 'Erreur',
									text: "Le temps d'attente est trop long, demande expiré, retenter votre chance."
								});
							},
							error: function(){
								$("#go_"+id).button('reset');
								Swal.fire({
									icon: 'error',
									title: 'Erreur',
									text: "Une erreur est intervenu dans le traitement de la requête, retenter votre chance."
								});
							}
						});
					},
					allowOutsideClick: () => !Swal.isLoading()
				}).then((result) => {
					if (result.isConfirmed) {
						Swal.fire({
							icon: 'success',
							title: 'Action terminé',
							text: result.value.data.message
						});
					}
				})
			}

			function ensureHttps(url) {
				if (url.startsWith('http://')) {
				} else if (!url.startsWith('https://')) {
					url = 'https://' + url;
				}
				
				return url.endsWith('/') ? url.slice(0, -1) : url;
			}

			function generateSQL() {
					let oldUrl = document.getElementById('oldUrl').value;
					let newUrl = document.getElementById('newUrl').value;
					const prefix = document.getElementById('prefix').value;

					if (!oldUrl || !newUrl || !prefix) {
						alert("Veuillez remplir tous les champs.");
						return;
					}

					oldUrl = ensureHttps(oldUrl);
					newUrl = ensureHttps(newUrl);

					const sqlQueries = `
UPDATE ${prefix}_options SET option_value = replace(option_value, '${oldUrl}', '${newUrl}') WHERE option_name = 'home' OR option_name = 'siteurl';
UPDATE ${prefix}_posts SET guid = replace(guid, '${oldUrl}', '${newUrl}');
UPDATE ${prefix}_posts SET post_content = replace(post_content, '${oldUrl}', '${newUrl}'); 
UPDATE ${prefix}_postmeta SET meta_value = replace(meta_value, '${oldUrl}', '${newUrl}');

UPDATE ${prefix}_revslider_slides SET params = replace(params, '${oldUrl}', '${newUrl}');
UPDATE ${prefix}_revslider_slides SET layers = replace(layers, '${oldUrl}', '${newUrl}');
UPDATE ${prefix}_revslider_sliders SET params = replace(params, '${oldUrl}', '${newUrl}');
					`;

					document.getElementById('sqlOutput').value = sqlQueries.trim();
			}

			function copyToClipboard() {
				const textarea = document.getElementById('sqlOutput');
				textarea.select();
				document.execCommand('copy');
				alert('Requêtes SQL copiées dans le presse-papiers');
			}
		</script>
   </body>
</html>
