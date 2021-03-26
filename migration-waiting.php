
			<article class="row">
				<div class="col-12">
					<h2>Processus de migration automatique d'un Wordpress d'un serveur A vers un serveur B en FTP</h2>
				</div>
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



			<article class="row">
				<div class="col-12">
					<h2>Export</h2>
				</div>
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







