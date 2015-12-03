<?php

$retour = FALSE;

if(isset($_POST['old']) && isset($_POST['new'])) {

	if(!empty($_POST['old']) && !empty($_POST['new'])) {

		include ('wp-config.php');

		$oldurl = $_POST['old'];
		$newurl = $_POST['new'];

		try
		{
		    $bdd = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8', DB_USER, DB_PASSWORD);
		}
		catch(Exception $e)
		{
		        die('Erreur : '.$e->getMessage());
		}

		// Old url et new URL sans ? a la fin, avec sous dossier si existant

		# Changer l'URL du site
		$req1 = $bdd->prepare('UPDATE wp_options SET option_value = replace(option_value, :oldurl, :newurl) WHERE option_name = \'home\' OR option_name = \'siteurl\';');

		# Changer l'URL des GUID
		$req2 = $bdd->prepare('UPDATE wp_posts SET guid = REPLACE (guid, :oldurl, :newurl);');

		# Changer l'URL des médias dans les articles et pages
		$req3 = $bdd->prepare('UPDATE wp_posts SET post_content = REPLACE (post_content, :oldurl, :newurl);');

		# Changer l'URL des données meta
		$req4 = $bdd->prepare('UPDATE wp_postmeta SET meta_value = REPLACE (meta_value, :oldurl, :newurl);');

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
		</article>

         <article class="row">
            <div class="col-md-12">
            	<?php if($retour == TRUE) : ?>
            		<div class="alert alert-success" role="alert">L'ecriture des nouvelles urls a bien ete effectue dans la base de données.</div>
            	<?php endif; ?>
				<form method="post">
					<div class="form-group">
						<label for="old">Ancienne URL</label>
						<input type="text" class="form-control" id="old" name="old" placeholder="Ancienne URL">
					</div>
					<div class="form-group">
						<label for="new">Nouvelle URL</label>
						<input type="text" class="form-control" id="new" name="new" placeholder="Nouvelle URL">
					</div>
					<div class="form-group">
						<button type="submit" class="btn btn-default">Mettre a jour</button>
					</div>
				</form>
            </div>
        </article>

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

        <footer class="row">
            <div class="col-md-12"></div>
        </footer>

      </div>

      <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
      <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha256-KXn5puMvxCw+dAYznun+drMdG1IFl3agK0p/pqT9KAo= sha512-2e8qq0ETcfWRI4HJBzQiA3UoyFk6tbNyG+qSaIBZLyW9Xf3sWZHN/lxe9fTh1U45DpPf07yj94KsUHHWe4Yk1A==" crossorigin="anonymous"></script>
   </body>
</html>
