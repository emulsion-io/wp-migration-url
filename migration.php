<?php

/**
 * @author Fabrice Simonet
 * @link http://viky.fr
 */

/**
 * Variable de status d'execution du script
 */
$retour 				= FALSE;
$retour_migration		= FALSE;
$retour_migration_api	= FALSE;
$retour_export 			= FALSE;
$retour_import 			= FALSE;
$retour_export_sql		= FALSE;
$retour_import_sql		= FALSE;
$retour_htaccess 		= FALSE;
$retour_dl 				= FALSE;
$retour_clean_revision 	= FALSE;
$retour_clean_spam 		= FALSE;

$migration = new Wp_Migration();

/**
 * Si un fichier wp-config.php existe, le script comprend que WP est deja installé
 */
if(is_file('wp-config.php')){

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

if(isset($_POST['old']) && isset($_POST['new'])) {
	if(!empty($_POST['old']) && !empty($_POST['new'])) {

		$oldurl = $_POST['old'];
		$newurl = $_POST['new'];

		$migration->wp_url($oldurl, $newurl);

		$retour = TRUE;
	}
}

/**
 * Creer un fichier htaccess
 */
if(isset($_POST['htaccess'])) {
	if(!empty($_POST['htaccess'])) {

		$migration->wp_htaccess();

		$retour_htaccess = TRUE;
	}
}

if(isset($_POST['exporter'])) {
	if(!empty($_POST['exporter'])) {

		$retour_export = $migration->wp_export_file();

	}
}

if(isset($_POST['exporter_sql'])) {
	if(!empty($_POST['exporter_sql'])) {

		$migration->wp_export_sql();

		$retour_export_sql = TRUE;
	}
}

/**
 * Extrait les fichiers du zip
 */
if(isset($_POST['importer'])) {
	if(!empty($_POST['importer'])) {

		$migration->wp_import_file();

		$retour_import = TRUE;
	}
}

if(isset($_POST['importer_sql'])) {
	if(!empty($_POST['importer_sql'])) {

		$migration->wp_import_sql();

		$retour_import_sql = TRUE;
	}
}

/**
 * Telecharge et extrait les fichiers d'un WP frais
 */
if(isset($_POST['dl'])) {
	if(!empty($_POST['dl'])) {

		$migration->wp_download();

		$retour_dl = TRUE;
	}
}

if(isset($_POST['clean_revision'])) {
	if(!empty($_POST['clean_revision'])) {

		$migration->wp_sql_clean_revision();

		$retour_clean_revision = TRUE;
	}
}

if(isset($_POST['clean_spam'])) {
	if(!empty($_POST['clean_spam'])) {

		$migration->wp_sql_clean_spam();

		$retour_clean_spam = TRUE;
	}
}

if(isset($_POST['migration'])) {
	if(!empty($_POST['migration'])) {

		$opts_migration = array(
			'www_url' => $_POST['www_url'],
			'ftp_url' => $_POST['ftp_url'],
			'user_ftp' => $_POST['user_ftp'],
			'ftp_pass' => $_POST['ftp_pass'],
			'ftp_folder' => $_POST['ftp_folder'],
			'serveur_sql' => $_POST['serveur_sql'],
			'user_sql' => $_POST['user_sql'],
			'pass_sql' => $_POST['pass_sql']
		);

		// Exporte le SQL
		$migration->wp_export_sql();

		// Exporte Les fichiers dans le Zip avec le SQL
		$migration->wp_export_file();

		// Envoie les fichiers sur le serveur FTP distant
		$migration->wp_ftp_migration($opts_migration);

		// Supprime les fichiers locaux
		$migration->wp_clean_ftp_migration();

		// Contact le site distant pour activer la methode api_call
		$retour_migration = $migration->wp_migration($opts_migration);
	}
}

if(isset($_POST['api_call'])) {
	if(!empty($_POST['api_call'])) {

		$opts = array(
			'dbname' 		=> $opts['dbname'],
			'dbpassword' 	=> $opts['dbpassword'],
			'dbhost' 		=> $opts['dbhost'],
			'site' 			=> $opts['site']
		);

		// extraction des fichiers
		$migration->wp_import_file();

		// modifie le fichier wordpress avec les infos du nouveau serveur
		$migration->wp_configfile($opts);

		// Effectue l'importation du SQL

		// nettoie les fichiers sql et zip
		$migration->wp_clean_ftp_migration();

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

      <!-- Font -->
      <link href='https://fonts.googleapis.com/css?family=Raleway' rel='stylesheet' type='text/css'>
      <!-- Css -->

      <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
      <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
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
					Pensez a supprimer le fichier migration.php de votre installation Wordpress apres avoir effectuer le changement des URLs.
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
							<li>Fonction shell_exec() <?php echo (function_exists('shell_exec'))? " is enabled" : " is disabled"; ?></li>
							<li>Fonction popen() <?php echo (function_exists('popen'))? " is enabled" : " is disabled"; ?></li>
							<li>Fonction passthru() <?php echo (function_exists('passthru'))? " is enabled" : " is disabled"; ?></li>
							<li>Fonction proc_open() <?php echo (function_exists('proc_open'))? " is enabled" : " is disabled"; ?></li>
						</ul>
					</div>
				</div>
			</div>
		</article>		
		<?php 
			/**
			 * Processus de migration automatique d'un Wordpress
			 */
		?>

		<h2>Processus de migration automatique d'un Wordpress</h2>

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
            	<?php if($retour == TRUE) : ?>
            		<div class="alert alert-success" role="alert">L'installation est effectuée avec succes</div>
            	<?php endif; ?>
				<form method="post">
					<input type="hidden" class="form-control" id="migration" name="migration" placeholder="" value="test">
					<h3>Url http du futur site</h3>
					<div class="form-group">
						<label for="www_url">Url http du serveur</label>
						<input type="text" class="form-control" id="www_url" name="www_url" placeholder="" value="">
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
						<label for="user_sql">Utilisateur MySQL</label>
						<input type="text" class="form-control" id="user_sql" name="user_sql" placeholder="" value="">
					</div>
					<div class="form-group">
						<label for="pass_sql">Mot de passe MySQL</label>
						<input type="text" class="form-control" id="pass_sql" name="pass_sql" placeholder="" value="">
					</div>					
					<div class="form-group">
						<button type="submit" class="btn btn-default">Lancer la migration</button>
					</div>
				</form>
            </div>
        </article>

		<h2>Liste des outils indepandant du processus d'installation automatique.</h2>

		<?php 
			/**
			 * Liste des outils idepandant du processus d'installation automatique.
			 */
		?>

        <article class="row">
           	<div class="col-md-12">
           	    <h3>Telecharge, extrait et install un Wordpress officiel depuis le site wordpress.com</h3>
				<div class="panel panel-info">
					<div class="panel-heading"> 
						<h3 class="panel-title">Ce que fait cet assistant</h3> 
					</div>
					<div class="panel-body">
					    <ul>
					    	<li>Telecharge, extrait et install un Wordpress derniere version en date</li>
					    </ul>
					</div>
				</div>
			</div>

            <div class="col-md-12">
                <?php if($retour_dl == TRUE) : ?>
            		<div class="alert alert-success" role="alert">
            			L'extraction des fichiers a ete effectue avec succes.
            			<p>Vous pouvez a present installer Wordpress en vous rendant a <a href="http://<?php echo $_SERVER['SERVER_NAME'] . dirname($_SERVER['REQUEST_URI']); ?>">http://<?php echo $_SERVER['SERVER_NAME'] . dirname($_SERVER['REQUEST_URI']); ?></a></p>
            		</div>
            	<?php endif; ?>
				<form method="post">
					<div class="form-group">
						<input type="hidden" class="form-control" id="dl" name="dl" placeholder="" value="test">
					</div>
					<div class="form-group">
						<button type="submit" class="btn btn-default">Telecharger et Extraire un wordpress</button>
					</div>
				</form>
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
            	<?php if($retour == TRUE) : ?>
            		<div class="alert alert-success" role="alert">L'ecriture des nouvelles urls a bien ete effectue dans la base de données.</div>
            	<?php endif; ?>
				<form method="post">
					<div class="form-group">
						<label for="old">Ancienne URL</label>
						<input type="text" class="form-control" id="old" name="old" placeholder="Ancienne URL sans / a la fin" value="<?php echo $site_url['option_value']; ?>">
					</div>
					<div class="form-group">
						<label for="new">Nouvelle URL</label>
						<input type="text" class="form-control" id="new" name="new" placeholder="Nouvelle URL sans / a la fin" value="http://<?php echo $_SERVER['SERVER_NAME'] . dirname($_SERVER['REQUEST_URI']); ?>">
					</div>
					<?php if($wp_exist == TRUE) : ?>
					<div class="form-group">
						<button type="submit" class="btn btn-default">Mettre a jour</button>
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
            </div>
        </article>

        <h2>Export</h2>

        <article class="row">
           	<div class="col-md-12">
           	    <h3>Creation d'une archive avec les fichiers de Wordpress</h3>
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
                <?php if($retour_export == TRUE) : ?>
            		<div class="alert alert-success" role="alert">
            		L'export a ete effectue avec succes.
            			<p><a href="/migration_file.zip">Telecharger le Dump des Fichers</a></p>
            		</div>
            	<?php endif; ?>
				<form method="post">
					<div class="form-group">
						<input type="hidden" class="form-control" id="exporter" name="exporter" placeholder="" value="test">
					</div>
					<?php if($wp_exist == TRUE) : ?>
					<div class="form-group">
						<button type="submit" class="btn btn-default">Creer le Zip des fichiers</button>
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

				<?php if(is_file('migration_file.zip') && $retour_export == FALSE) : ?>
				<div class="panel panel-success">
					<div class="panel-heading"> 
						<h3 class="panel-title">Information</h3> 
					</div>
					<div class="panel-body">
					    <ul>
					    	<li>Un Dump existe deja, telecharger l'existant ? <a href="/migration_file.zip">Telecharger le Dump existant</a></li>
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
                <?php if($retour_export_sql == TRUE) : ?>
            		<div class="alert alert-success" role="alert">
            			L'export a ete effectue avec succes.
            			<p><a href="/migration_bdd.sql">Telecharger le Dump</a></p>
            		</div>
            	<?php endif; ?>
				<form method="post">
					<div class="form-group">
						<input type="hidden" class="form-control" id="exporter_sql" name="exporter_sql" placeholder="" value="test">
					</div>
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

				<?php if(is_file('migration_bdd.sql') && $retour_export_sql == FALSE) : ?>
				<div class="panel panel-success">
					<div class="panel-heading"> 
						<h3 class="panel-title">Information</h3> 
					</div>
					<div class="panel-body">
					    <ul>
					    	<li>Un Dump existe deja, telecharger l'existant ? <a href="/migration_bdd.sql">Telecharger le Dump existant</a></li>
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
					    	<li>Permet d'extraire le fichier exporté precedement</li>
					    </ul>
					</div>
				</div>
			</div>

            <div class="col-md-12">
                <?php if($retour_import == TRUE) : ?>
            		<div class="alert alert-success" role="alert">L'extraction des fichiers a ete effectue avec succes.</div>
            	<?php endif; ?>
				<form method="post">
					<div class="form-group">
						<input type="hidden" class="form-control" id="importer" name="importer" placeholder="" value="test">
					</div>
					<?php if(is_file('migration_file.zip')): ?>
					<div class="form-group">
						<button type="submit" class="btn btn-default">Extraire les fichiers dans le dossier courant</button>
					</div>
					<?php endif; ?>
				</form>

				<?php if(!is_file('migration_file.zip')): ?>
				<div class="panel panel-warning">
					<div class="panel-heading"> 
						<h3 class="panel-title">Information</h3> 
					</div>
					<div class="panel-body">
					    <ul>
					    	<li>Le fichier migration_file.zip n'est pas present sur le serveur</li>
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
					    	<li>Permet d'importer la base de données exportée</li>
					    </ul>
					</div>
				</div>
			</div>

            <div class="col-md-12">
                <?php if($retour_import_sql == TRUE) : ?>
            		<div class="alert alert-success" role="alert">L'importation de la base de données a ete effectue avec succes.</div>
            	<?php endif; ?>
				<form method="post">
					<div class="form-group">
						<input type="hidden" class="form-control" id="importer_sql" name="importer_sql" placeholder="" value="test">
					</div>
					<?php if(is_file('migration_bdd.sql')): ?>
					<div class="form-group">
						<button type="submit" class="btn btn-default">Extraire les fichiers dans le dossier courant</button>
					</div>
					<?php endif; ?>
				</form>

				<?php if(!is_file('migration_bdd.sql')): ?>
				<div class="panel panel-warning">
					<div class="panel-heading"> 
						<h3 class="panel-title">Information</h3> 
					</div>
					<div class="panel-body">
					    <ul>
					    	<li>Le fichier migration_bdd.sql n'est pas present sur le serveur</li>
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
                <?php if($retour_htaccess == TRUE) : ?>
            		<div class="alert alert-success" role="alert">Le fichier a ete ecrit avec succes.</div>
            	<?php endif; ?>
				<form method="post">
					<div class="form-group">
						<input type="hidden" class="form-control" id="htaccess" name="htaccess" placeholder="" value="test">
					</div>
					<div class="form-group">
						<button type="submit" class="btn btn-default">Creer le fichier HTaccess</button>
					</div>
				</form>
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
					    	<li>Efface les revisions des articles et pages et de tous les contenus</li>
					    </ul>
					</div>
				</div>
			</div>

            <div class="col-md-12">
                <?php if($retour_clean_revision == TRUE) : ?>
            		<div class="alert alert-success" role="alert">Les revisions ont ete supprimées avec succes.</div>
            	<?php endif; ?>
				<form method="post">
					<div class="form-group">
						<input type="hidden" class="form-control" id="clean_revision" name="clean_revision" placeholder="" value="test">
					</div>
					<?php if($wp_exist == TRUE) : ?>
					<div class="form-group">
						<button type="submit" class="btn btn-default">Effacer les revisions</button>
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
                <?php if($retour_clean_spam == TRUE) : ?>
            		<div class="alert alert-success" role="alert">Les commentaires ont ete supprimés avec succes.</div>
            	<?php endif; ?>
				<form method="post">
					<div class="form-group">
						<input type="hidden" class="form-control" id="clean_spam" name="clean_spam" placeholder="" value="test">
					</div>
					<?php if($wp_exist == TRUE) : ?>
					<div class="form-group">
						<button type="submit" class="btn btn-default">Effacer les commentaires non validés</button>
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
            </div>
        </article>

        <footer class="row">
            <div class="col-md-12"></div>
        </footer>

      </div>

      <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
      <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha256-KXn5puMvxCw+dAYznun+drMdG1IFl3agK0p/pqT9KAo= sha512-2e8qq0ETcfWRI4HJBzQiA3UoyFk6tbNyG+qSaIBZLyW9Xf3sWZHN/lxe9fTh1U45DpPf07yj94KsUHHWe4Yk1A==" crossorigin="anonymous"></script>
   </body>
</html>

<?php 

Class Wp_Migration {

	var $_wp_lang,
		$_wp_api,
		$_wp_dir_core;

	var $_dbhost,
		$_dbname,
		$_dbuser,
		$_dbpassword,
		$_table_prefix;

	public function __construct() {

		$this->_wp_lang 		= 'fr_FR';
		$this->_wp_api 			= 'http://api.wordpress.org/core/version-check/1.7/?locale='.$this->_wp_lang;
		$this->_wp_dir_core 	= 'core/';

	}

	/**
	 * Assigne les variable du Wordpress courant a la class
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

	public function wp_ftp_migration($opts){

		$file = 'migration_file.zip';
		$remote_file = 'migration_file.zip';

		$conn_id = ftp_connect($opts['ftp_url']);

		$login_result = ftp_login($conn_id, $opts['user_ftp'], $opts['ftp_pass']);

		// Charge un fichier
		ftp_put($conn_id, rtrim($opts['ftp_folder'], '/').'/'.'migration.php', 'migration.php', FTP_ASCII);

		if (ftp_put($conn_id, rtrim($opts['ftp_folder'], '/').'/'.$remote_file, $file, FTP_ASCII)) {
			ftp_close($conn_id);
			return TRUE;
		} else {
			ftp_close($conn_id);
			return FALSE;
		}
	}

	public function wp_clean_ftp_migration(){
		unlink('migration_file.zip');
		unlink('migration_bdd.sql');
	}

	/**
	 * Recupere le Zip de la derniere version en ligne de Wordpress
	 * Extrait le Zip de la version telechargé
	 * Supprime les fichiers telechargés et non utiles
	 */
	public function wp_download(){

		// Get WordPress data
		$wp = json_decode( file_get_contents( $config['wp_api'] ) )->offers[0];

		mkdir($config['wp_dir_core'], 0775);

		file_put_contents( $config['wp_dir_core'] . 'wordpress-' . $wp->version . '-' .  $config['wp_lang'] . '.zip', file_get_contents( $wp->download ) );

		$zip = new ZipArchive;

		// We verify if we can use the archive
		if ( $zip->open( $config['wp_dir_core'] . 'wordpress-' . $wp->version . '-' . $config['wp_lang']  . '.zip' ) === true ) {

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
	 * Contact l'api distante pour lui donner les ordres du coté serveur distant
	 */
	public function wp_migration($opts_migration) {

		$postdata = http_build_query(
		    array(
		        'api_call' 		=> 'migration',
				'dbname' 		=> $opts_migration['user_sql'],
				'dbpassword' 	=> $opts_migration['pass_sql'],
				'dbhost' 		=> $opts_migration['serveur_sql'],
				'site' 			=> $opts_migration['www_url']
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

	public function wp_url($oldurl, $newurl) {

		$bdd = Bdd::getInstance();

		# Changer l'URL du site
		$req1 = $bdd->dbh->prepare('UPDATE '.$table_prefix.'options SET option_value = replace(option_value, :oldurl, :newurl) WHERE option_name = \'home\' OR option_name = \'siteurl\';');

		# Changer l'URL des GUID
		$req2 = $bdd->dbh->prepare('UPDATE '.$table_prefix.'posts SET guid = REPLACE (guid, :oldurl, :newurl);');

		# Changer l'URL des médias dans les articles et pages
		$req3 = $bdd->dbh->prepare('UPDATE '.$table_prefix.'posts SET post_content = REPLACE (post_content, :oldurl, :newurl);');

		# Changer l'URL des données meta
		$req4 = $bdd->dbh->prepare('UPDATE '.$table_prefix.'postmeta SET meta_value = REPLACE (meta_value, :oldurl, :newurl);');

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
	}

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
		
		if(is_file('.htaccess')) {
			copy('.htaccess', '.htaccess.bak');
		}

		file_put_contents( '.htaccess', $ht );

	}

	public function wp_configfile($opts) {
		
		$filename = 'wp-config.php';

		$content = file_get_contents($filename);

		$content = preg_replace ("/define\('DB_NAME', '(.*)'\);/i", "define('DB_NAME', '".$opts['dbname']."');", $content);
		$content = preg_replace ("/define\('DB_USER', '(.*)'\);/i", "define('DB_USER', '".$opts['dbname']."');", $content);
		$content = preg_replace ("/define\('DB_PASSWORD', '(.*)'\);/i", "define('DB_PASSWORD', '".$opts['dbpassword']."');", $content);
		$content = preg_replace ("/define\('DB_HOST', '(.*)'\);/i", "define('DB_HOST', '".$opts['dbhost']."');", $content);

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

	}

	/**
	 * Exporte les fichiers de WP
	 */
	public function wp_export_file() {
		
		$this->Zip('./', "migration_file.zip");

		return TRUE;

	}

	public function wp_export_sql() {

		if(function_exists('exec')){
			$command = 'mysqldump --opt -h' . $this->_dbhost .' -u' . $this->_dbuser .' -p' . $this->_dbpassword .' ' . $this->_dbname .' > migration_bdd.sql';
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
			system("mysqldump --host=" . $this->_dbhost ." --user=" . $this->_dbuser ." --password=". $this->_dbpassword ." ". $this->_dbname ." > migration_bdd.sql");
		} else {
	
		}
	}

	public function wp_import_file() {
	 
	    $zip = new ZipArchive;

	    $zip->open('migration_file.zip');
	    $zip->extractTo('.');
	    $zip->close();

	}

	public function wp_import_sql() {

		if(function_exists('exec')){
			$command = 'mysql -h' . $this->_dbhost .' -u' . $this->_dbuser .' -p' . $this->_dbpassword .' ' . $this->_dbname .' < migration_bdd.sql';
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
			system('mysql -h' . $this->_dbhost .' -u' . $this->_dbuser .' -p' . $this->_dbpassword .' ' . $this->_dbname .' < migration_bdd.sql');
		} else {

		}
	}

	/**
	 * Supprime les revisions de la bdd
	 */
	public function wp_sql_clean_revision() {

		$bdd = Bdd::getInstance();

		$sql = $bdd->dbh->prepare('DELETE FROM '.$this->_table_prefix.'posts WHERE post_type = "revision"');
		$sql->execute();

	}

	/**
	 * Supprime tous les commentaires non approuvés
	 */
	public function wp_sql_clean_spam() {

		$bdd = Bdd::getInstance();

		$sql = $bdd->dbh->prepare('DELETE from '.$this->_table_prefix.'comments WHERE comment_approved = 0');
		$sql->execute();

	}

	/**
	 * Creer des Zip recursivement
	 *
	 * @link : http://stackoverflow.com/questions/1334613/how-to-recursively-zip-a-directory-in-php
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
	                $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
	            }
	            else if (is_file($file) === true)
	            {
	                $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
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
	 * Supprime recursivement un dossier et ses fichiers
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
	   }
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

        $this->dbh = new PDO($dsn, $user, $password);
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
