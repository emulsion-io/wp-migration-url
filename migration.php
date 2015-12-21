<?php

$retour 			= FALSE;
$retour_export 		= FALSE;
$retour_import 		= FALSE;
$retour_htaccess 	= FALSE;

include ('wp-config.php');

try
{
    $bdd = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8', DB_USER, DB_PASSWORD);
}
catch(Exception $e)
{
        die('Erreur : '.$e->getMessage());
}

$req0 = $bdd->prepare('SELECT option_value FROM '.$table_prefix.'options WHERE option_name = \'siteurl\';');
$req0->execute();
$site_url = $req0->fetch();

if(isset($_POST['old']) && isset($_POST['new'])) {

	if(!empty($_POST['old']) && !empty($_POST['new'])) {


		$oldurl = $_POST['old'];
		$newurl = $_POST['new'];


		// Old url et new URL sans / a la fin, avec sous dossier si existant

		# Changer l'URL du site
		$req1 = $bdd->prepare('UPDATE '.$table_prefix.'options SET option_value = replace(option_value, :oldurl, :newurl) WHERE option_name = \'home\' OR option_name = \'siteurl\';');

		# Changer l'URL des GUID
		$req2 = $bdd->prepare('UPDATE '.$table_prefix.'posts SET guid = REPLACE (guid, :oldurl, :newurl);');

		# Changer l'URL des médias dans les articles et pages
		$req3 = $bdd->prepare('UPDATE '.$table_prefix.'posts SET post_content = REPLACE (post_content, :oldurl, :newurl);');

		# Changer l'URL des données meta
		$req4 = $bdd->prepare('UPDATE '.$table_prefix.'postmeta SET meta_value = REPLACE (meta_value, :oldurl, :newurl);');

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

		$retour = TRUE;
	}
}

if(isset($_POST['htaccess'])) {

	if(!empty($_POST['htaccess'])) {

		$path = dirname($_SERVER['REQUEST_URI']);

		/*
		<IfModule mod_rewrite.c>
		RewriteEngine On
		RewriteBase /projets/mont-saleve2015/
		RewriteRule ^index\.php$ - [L]
		RewriteCond %{REQUEST_FILENAME} !-f
		RewriteCond %{REQUEST_FILENAME} !-d
		RewriteRule . /projets/mont-saleve2015/index.php [L]
		</IfModule>
		*/
	}
}

if(isset($_POST['exporter'])) {

	if(!empty($_POST['exporter'])) {

		//-- Dump SQL
		exec('mysqldump --host='.DB_HOST.' --user='.DB_USER.' --password='.DB_PASSWORD.' '.DB_NAME.' > export.sql');

		//-- Zip Files
		Zip('./', "dumpFILES.zip");

		$retour_export = TRUE;
	}
}

if(isset($_POST['importer'])) {

	if(!empty($_POST['importer'])) {

	    $zip = new ZipArchive;
	    $zip->open('dumpFILES.zip');
	    $zip->extractTo('./');
	    $zip->close();

		$retour_import = TRUE;
	}
}
?>

<!DOCTYPE html>
<html lang="en">
   <head>
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name='viewport' content='initial-scale=1,maximum-scale=1,user-scalable=no' />

      <title>Migration Urls Wordpress Easy</title>

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
				  <h1>Migration Urls Wordpress Easy</h1>
				  <p>...</p>
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
                <h2>Ecriture des nouvelles Urls</h2>
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
					<div class="form-group">
						<button type="submit" class="btn btn-default">Mettre a jour</button>
					</div>
				</form>
            </div>
        </article>

        <article class="row">
           	<div class="col-md-12">
           	    <h2>Creation d'une archive avec les fichiers de Wordpress et le SQL</h2>
				<div class="panel panel-info">
					<div class="panel-heading"> 
						<h3 class="panel-title">Ce que fait cet assistant</h3> 
					</div>
					<div class="panel-body">
					    <ul>
					    	<li>Extrait la base données si possible ( depend des serveurs )</li>
					    	<li>Creer un Zip de tout le repertoire courant avec le SQL dedans</li>
					    </ul>
					</div>
				</div>
			</div>

            <div class="col-md-12">
                <?php if($retour_export == TRUE) : ?>
            		<div class="alert alert-success" role="alert">
            		L'export a ete effectue avec succes.
            			<p><a href="/dumpFILES.zip">Telecharger le Dump des Fichers</a></p>
            		</div>
            	<?php endif; ?>
				<form method="post">
					<div class="form-group">
						<input type="hidden" class="form-control" id="exporter" name="exporter" placeholder="" value="test">
					</div>
					<div class="form-group">
						<button type="submit" class="btn btn-default">Creer le Zip des fichiers</button>
					</div>
				</form>

				<?php if(is_file('dumpFILES.zip') && $retour_export == FALSE) : ?>
				<div class="panel panel-success">
					<div class="panel-heading"> 
						<h3 class="panel-title">Information</h3> 
					</div>
					<div class="panel-body">
					    <ul>
					    	<li>Un Dump existe deja, telecharger l'existant ? <a href="/dumpFILES.zip">Telecharger le Dump existant</a></li>
					    </ul>
					</div>
				</div>
				<?php endif; ?>

            </div>
        </article>

        <article class="row">
           	<div class="col-md-12">
           	    <h2>Extraction des fichiers de Wordpress</h2>
				<div class="panel panel-info">
					<div class="panel-heading"> 
						<h3 class="panel-title">Ce que fait cet assistant</h3> 
					</div>
					<div class="panel-body">
					    <ul>
					    	<li>Permet d'extraire le fichier dumpé precedement</li>
					    	<li>Copier/Coller le fichier Zip sans le renommer</li>
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
					<?php if(is_file('dumpFILES.zip')): ?>
					<div class="form-group">
						<button type="submit" class="btn btn-default">Extraire les fichiers dans le dossier courant</button>
					</div>
					<?php endif; ?>
				</form>

				<?php if(!is_file('dumpFILES.zip')): ?>
				<div class="panel panel-warning">
					<div class="panel-heading"> 
						<h3 class="panel-title">Information</h3> 
					</div>
					<div class="panel-body">
					    <ul>
					    	<li>Le fichier dumpFILES.zip n'est pas present sur le serveur</li>
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
					    	<li></li>
					    	<li></li>
					    </ul>
					</div>
				</div>
			</div>

            <div class="col-md-12">
                <?php if($retour_htaccess == TRUE) : ?>
            		<div class="alert alert-success" role="alert">L'extraction des fichiers a ete effectue avec succes.</div>
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

        <footer class="row">
            <div class="col-md-12"></div>
        </footer>

      </div>

      <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
      <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha256-KXn5puMvxCw+dAYznun+drMdG1IFl3agK0p/pqT9KAo= sha512-2e8qq0ETcfWRI4HJBzQiA3UoyFk6tbNyG+qSaIBZLyW9Xf3sWZHN/lxe9fTh1U45DpPf07yj94KsUHHWe4Yk1A==" crossorigin="anonymous"></script>
   </body>
</html>

<?php 

function Zip($source, $destination)
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

?>
