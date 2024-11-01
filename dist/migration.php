<?php

/**
 * @author Fabrice Simonet
 * @link https://emulsion.io
 *
 * @version 2.7.6
 *
 * Copyright (c) 2024 Fabrice Simonet
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
 * 
 * $zips_wp = [
 * 	[
 * 		'nom' => 'Mon template WP #1',
 * 		'fichier' => 'https://site/fichier.zip',
 * 		'sql' => 'https://site/fichier.zip'
 * 	],
 * 	[
 * 		'nom' => 'Mon template WP #2',
 * 		'fichier' => 'https://site/fichier.zip',
 * 		'sql' => 'https://site/fichier.zip'
 * 	]
 * ];
 * 
 */
$zips_wp = [];

/**
 * Vos zips de thèmes à installer
 * 
 * $zips_themes = [
 * 	[
 * 		'nom' => 'Mon Theme WP #1',
 * 		'fichier' => 'https://site/fichier.zip'
 * 	],
 * 	[
 * 		'nom' => 'Mon Theme WP #2',
 * 		'fichier' => 'https://site/fichier.zip'
 * 	]
 * ];
 * 
 */
$zips_themes = [];

/**
 * Vos zips de plugins à installer
 * 
 * $zips_plugins = [
 * 	[
 * 		'nom' => 'Mon Plugin WP #1',
 * 		'fichier' => 'https://site/fichier.zip'
 * 	],
 * 	[
 * 		'nom' => 'Mon Plugin WP #2',
 * 		'fichier' => 'https://site/fichier.zip'
 * 	]
 * ];
 * 
 */
$zips_plugins = [];

/**
 * Gestion des instances custom via json
 */
if(file_exists('migration.json')) {
	$instances = json_decode(file_get_contents('migration.json'), true);

	if(isset($instances['wp'])) {
		$zips_wp = $instances['wp'];
	}

	if(isset($instances['themes'])) {
		$zips_themes = $instances['themes'];
	}

	if(isset($instances['plugins'])) {
		$zips_plugins = $instances['plugins'];
	}
}

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



Class Wp_Migration {

	var $_wp_lang,
		$_wp_api,
		$_wp_dir_core,
		$_version,
		$_file_destination,
		$_file_sql,
		$_current_rep,
		$_fileswp;

	var $_dbhost,
		$_dbname,
		$_dbuser,
		$_dbpassword,
		$_table_prefix;

	var $_debug,
		$_debug_display,
		$_debug_log;

	var $_themes,
		$_plugins;

	/**
	 * @var string $this->_wp_lang 				Langue Wordpress
	 * @var string $this->_wp_api 				Url de l'api Wordpress
	 * @var string $this->_wp_dir_core 			Dossier temporaire de copie des fichier WP
	 * @var string $this->_file_destination 	Nom du fichier Zip créé lors de la sauvegarde
	 * @var string $this->_file_sql 				Nom du fichier sql créé lors du Cump SQL
	 */
	public function __construct() {

		$this->_version          = '2.7.6';
		$this->_wp_lang          = 'fr_FR';
		$this->_wp_api           = 'http://api.wordpress.org/core/version-check/1.7/?locale='.$this->_wp_lang;
		$this->_wp_dir_core      = 'core/';
		$this->_current_rep      = getcwd();
		$this->_fileswp          = array(
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
			'license.txt',
			'readme.html',
			'wp-config-sample.php',
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
	public function set_var_wp($options = null) {

		if($options != null){
			$this->_dbhost 			= $options['dbhost'];
			$this->_dbname 			= $options['dbname'];
			$this->_dbuser 			= $options['dbuser'];
			$this->_dbpassword 		= $options['dbpassword'];
			$this->_table_prefix 	= $options['table_prefix'];

			Config::write('db.host', $this->_dbhost);
			Config::write('db.basename', $this->_dbname);
			Config::write('db.user', $this->_dbuser);
			Config::write('db.password', $this->_dbpassword);
		} else {
		
			// Chemin vers le fichier wp-config.php
			$wp_config_file = 'wp-config.php';

			// Si le fichier n'existe pas, on retourne une erreur
			if (!file_exists($wp_config_file)) {
				return false;
			}

			// Lire le contenu du fichier sans l'inclure
			$config_content = file_get_contents($wp_config_file);

			if($config_content === FALSE){
				return FALSE;
			}

			// Expression régulière pour capturer les constantes
			preg_match("/define\(\s*'DB_NAME'\s*,\s*'(.+?)'\s*\);/", $config_content, $db_name);
			preg_match("/define\(\s*'DB_USER'\s*,\s*'(.+?)'\s*\);/", $config_content, $db_user);
			preg_match("/define\(\s*'DB_PASSWORD'\s*,\s*'(.+?)'\s*\);/", $config_content, $db_password);
			preg_match("/define\(\s*'DB_HOST'\s*,\s*'(.+?)'\s*\);/", $config_content, $db_host);
			preg_match("/define\(\s*'DB_CHARSET'\s*,\s*'(.+?)'\s*\);/", $config_content, $db_charset);
			preg_match("/define\(\s*'DB_COLLATE'\s*,\s*'(.+?)'\s*\);/", $config_content, $db_collate);
			preg_match("/\\\$table_prefix\s*=\s*'(.+?)'\s*;/", $config_content, $table_prefix_matches);

			// chercher les lignes de debug
			preg_match("/define\(\s*'WP_DEBUG'\s*,\s*'(.+?)'\s*\);/", $config_content, $debug);
			preg_match("/define\(\s*'WP_DEBUG_DISPLAY'\s*,\s*'(.+?)'\s*\);/", $config_content, $debug_display);
			preg_match("/define\(\s*'WP_DEBUG_LOG'\s*,\s*'(.+?)'\s*\);/", $config_content, $debug_log);

			$this->_dbhost 			= $db_host[1];
			$this->_dbname 			= $db_name[1];
			$this->_dbuser 			= $db_user[1];
			$this->_dbpassword 		= isset($db_password[1]) ? $db_password[1] : '';
			$this->_table_prefix 	= $table_prefix_matches[1];

			$this->_debug = isset($debug[1]) ? filter_var($debug[1], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : null;
			$this->_debug_display = isset($debug_display[1]) ? filter_var($debug_display[1], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : null;
			$this->_debug_log = isset($debug_log[1]) ? filter_var($debug_log[1], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : null;
			
			Config::write('db.host', $this->_dbhost);
			Config::write('db.basename', $this->_dbname);
			Config::write('db.user', $this->_dbuser);
			Config::write('db.password', $this->_dbpassword);
		}
	}

	/**
	 * Recupere les informations sur le WP courant
	 *
	 */
	public function get_theme_plugin_wp() {

		$this->set_var_wp();

		// Chemin vers le fichier wp-config.php
		$wp_config_file = 'wp-config.php';

		// Si le fichier n'existe pas, on retourne une erreur
		if (!file_exists($wp_config_file)) {
			return false;
		}

		require_once( 'wp-config.php' );
		require_once( 'wp-load.php' );

		// Lister les themes installés
		$themes = wp_get_themes();
		$themes = array_keys($themes);

		$this->_themes = $themes;

		// Lister les plugins installés
		require_once( 'wp-admin/includes/plugin.php');
		$plugins = get_option('active_plugins');

		$this->_plugins = $plugins;
	}

	/**
	 * Recupere les informations sur le WP courant
	 */
	public function wp_get_info(string $option = 'siteurl'){

		$this->set_var_wp();

		$bdd = Bdd::getInstance();

		if($bdd->dbh == null){
			return false;
		}

		try {
			$req = $bdd->dbh->prepare('SELECT option_value FROM '.$this->_table_prefix.'options WHERE option_name = "' . $option . '";');
			$req->execute();

			return $req->fetch();
		} catch (PDOException $e) {
			return false;
		}
	}

	/**
	 * Change les informations de la base de données dans le fichier de configuration
	 * 
	 * @param string $dbname 	Nom de la base de données
	 * @param string $dbuser 	Nom de l'utilisateur de la base de données
	 * @param string $dbpass 	Mot de passe de l'utilisateur de la base de données
	 * @param string $dbhost 	Adresse du serveur de la base de données
	 * 
	 * @return bool true|false
	 */
	public function wp_change_wpconfig($dbname, $dbuser, $dbpass, $dbhost){
		
		$this->set_var_wp();

		// Chemin vers le fichier wp-config.php
		$wp_config_file = 'wp-config.php';

		// Si le fichier n'existe pas, on retourne une erreur
		if (!file_exists($wp_config_file)) {
			return false;
		}

		// Lire le contenu du fichier sans l'inclure
		$config_content = file_get_contents($wp_config_file);

		if($config_content === FALSE){
			return FALSE;
		}

		// Expression régulière pour capturer les constantes
		preg_match("/define\(\s*'DB_NAME'\s*,\s*'(.+?)'\s*\);/", $config_content, $db_name);
		preg_match("/define\(\s*'DB_USER'\s*,\s*'(.+?)'\s*\);/", $config_content, $db_user);
		preg_match("/define\(\s*'DB_PASSWORD'\s*,\s*'(.+?)'\s*\);/", $config_content, $db_password);
		preg_match("/define\(\s*'DB_HOST'\s*,\s*'(.+?)'\s*\);/", $config_content, $db_host);

		// Remplacer les valeurs
		$config_content = preg_replace("/define\(\s*'DB_NAME'\s*,\s*'(.+?)'\s*\);/", "define('DB_NAME', '".$dbname."');", $config_content);
		$config_content = preg_replace("/define\(\s*'DB_USER'\s*,\s*'(.+?)'\s*\);/", "define('DB_USER', '".$dbuser."');", $config_content);
		$config_content = preg_replace("/define\(\s*'DB_PASSWORD'\s*,\s*'(.+?)'\s*\);/", "define('DB_PASSWORD', '".$dbpass."');", $config_content);
		$config_content = preg_replace("/define\(\s*'DB_HOST'\s*,\s*'(.+?)'\s*\);/", "define('DB_HOST', '".$dbhost."');", $config_content);

		// Ecrire le contenu dans le fichier
		file_put_contents($wp_config_file, $config_content);

		return true;
	}

	/**
	 * Change les informations de la base de données dans le fichier de configuration
	 * 
	 * @param string $debug 			Mode debug
	 * @param string $debug_display 	Affichage des erreurs
	 * @param string $debug_log 		Ecriture des erreurs dans un fichier log
	 * 
	 */
	public function wp_change_debug($_debug, $_debug_display, $_debug_log){
		
		$this->set_var_wp();

		// Chemin vers le fichier wp-config.php
		$wp_config_file = 'wp-config.php';

		// Si le fichier n'existe pas, on retourne une erreur
		if (!file_exists($wp_config_file)) {
			return false;
		}

		// Lire le contenu du fichier sans l'inclure
		$config_content = file_get_contents($wp_config_file);

		if($config_content === FALSE){
			return FALSE;
		}

		// Expression régulière pour capturer les constantes
		preg_match("/define\(\s*'WP_DEBUG'\s*,\s*(.+?)\s*\);/", $config_content, $debug);
		preg_match("/define\(\s*'WP_DEBUG_DISPLAY'\s*,\s*(.+?)\s*\);/", $config_content, $debug_display);
		preg_match("/define\(\s*'WP_DEBUG_LOG'\s*,\s*(.+?)\s*\);/", $config_content, $debug_log);

		// Remplacer les valeurs
		$config_content = preg_replace("/define\(\s*'WP_DEBUG'\s*,\s*(.+?)\s*\);/", "define('WP_DEBUG', '".$_debug."');", $config_content);
		$config_content = preg_replace("/define\(\s*'WP_DEBUG_DISPLAY'\s*,\s*(.+?)\s*\);/", "define('WP_DEBUG_DISPLAY', '".$_debug_display."');", $config_content);
		$config_content = preg_replace("/define\(\s*'WP_DEBUG_LOG'\s*,\s*(.+?)\s*\);/", "define('WP_DEBUG_LOG', '".$_debug_log."');", $config_content);

		// Ecrire le contenu dans le fichier
		file_put_contents($wp_config_file, $config_content);

		return true;
	}

	/**
	 * Recupere le Zip de la derniere version en ligne de Wordpress ou url custom
	 * 
	 */
	public function wp_download_zip($url = null){

		// Si on a une URL source, on l'utilise
		if($url != null){

			$fichierDest = 'wordpress-custom.zip';
			$file = file_get_contents( $url );

			if($file === FALSE){
				return FALSE;
			}

			file_put_contents( $fichierDest, $file );
		} else {

			// Get WordPress data
			$wp = json_decode( file_get_contents( $this->_wp_api ) )->offers[0];

			$fichierDest = 'wordpress-' . $wp->version . '-' . $this->_wp_lang . '.zip';

			$file = file_get_contents( $wp->download );

			if($file === FALSE){
				return FALSE;
			}

			file_put_contents( $fichierDest, $file );
		}

		return TRUE;
	}

	/**
	 * Récupere le fichier SQL de la base de données
	 */
	public function wp_download_bdd($sql){
		
		$folder = 'bdd_tmp';
		// On crée le dossier temporaire
		if (!is_dir($folder)) {
			if (!mkdir($folder, 0775)) {
				 return FALSE; // Erreur lors de la création du dossier
			}
		}

		$fichierDest = $folder . '/wordpress-sql.zip';

		$file = file_get_contents( $sql );

		if($file === FALSE){
			return FALSE;
		}

		$retour = file_put_contents( $fichierDest, $file );

		if($retour === FALSE){
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Récupere le fichier SQL de la base de données et l'installe
	 */
	public function wp_download_install_bdd($sql){
		
		$this->set_var_wp();

		$folder = 'bdd_tmp';
		// On crée le dossier temporaire
		// On crée le dossier temporaire
		if (!is_dir($folder)) {
			if (!mkdir($folder, 0775)) {
				 return FALSE; // Erreur lors de la création du dossier
			}
		}

		$fichierDest = $folder . '/wordpress-sql.zip';

		$file = file_get_contents( $sql );

		if($file === FALSE){
			return FALSE;
		}

		$retour = file_put_contents( $fichierDest, $file );

		if($retour === FALSE){
			return FALSE;
		}

		$bdd = Bdd::getInstance();

		if($bdd->dbh == null){
			$this->retour(array('message' => 'Connection à votre base de données impossible.'), FALSE);
		}

		$zip = new ZipArchive;

		// We verify if we can use the archive
		if ( $zip->open( $fichierDest ) === true ) {

			// Let's unzip sans verifier
			$zip->extractTo( $folder );

			// Fermeture de l'archive
			$zip->close();

			// On liste les fichiers du dossier pour retrouver le fichier SQL
			$files = scandir( $folder );

			// On parcours les fichiers
			foreach ($files as $file) {
				if (strpos($file, '.sql') !== false) {
					$file_sql = $folder . '/' . $file;
				}
			}

			// On récupère le fichier SQL
			$sql = file_get_contents($file_sql);

			// On exécute le fichier SQL
			$bdd->dbh->exec($sql);

			// on supprime le dossier et tous ses fichiers
			$this->rrmdir( $folder );

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Récupere le Zip du theme
	 */
	public function wp_download_zip_theme($url = null){
		
		// Si on a une URL source, on l'utilise
		if($url != null){

			$folder = 'wp-content/themes';
			$fichierDest = 'theme-custom.zip';

			$file = file_get_contents( $url );

			if($file === FALSE){
				return FALSE;
			}

			$retour = file_put_contents( $folder . '/' . $fichierDest, $file );

			if($retour === FALSE){
				return FALSE;
			}

			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * Récupere le Zip du theme et l'installe
	 */
	public function wp_download_extract_zip_theme($url = null){
		
		// Si on a une URL source, on l'utilise
		if($url != null){

			$folder = 'wp-content/themes';
			$fichierDest = 'theme-custom.zip';
			
			$file = file_get_contents( $url );
			$retour = file_put_contents( $folder . '/' . $fichierDest, $file );

			if($retour === FALSE){
				return FALSE;
			}

			$zip = new ZipArchive;

			// We verify if we can use the archive
			if ( $zip->open( $folder . '/' . $fichierDest ) === true ) {

				// Let's unzip
				$zip->extractTo( $folder );

				// Fermeture de l'archive
				$zip->close();

				// On supprime le fichier zip
				unlink( $folder . '/' . $fichierDest );

				return TRUE;
			}

			return FALSE;
		} else {
			return FALSE;
		}
	}

	/**
	 * Récupere le Zip du plugin
	 */
	public function wp_download_zip_plugin($url = null){
		
		// Si on a une URL source, on l'utilise
		if($url != null){

			$folder = 'wp-content/plugins';
			$fichierDest = 'plugin-custom.zip';
			
			$file = file_get_contents( $url );

			if($file === FALSE){
				return FALSE;
			}

			$retour = file_put_contents( $folder . '/' . $fichierDest, $file );

			if($retour === FALSE){
				return FALSE;
			}

			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * Récupere le Zip du plugin et l'installe
	 */
	public function wp_download_extract_zip_plugin($url = null){
		
		// Si on a une URL source, on l'utilise
		if($url != null){

			$folder = 'wp-content/plugins';
			$fichierDest = 'plugin-custom.zip';
			
			$file = file_get_contents( $url );
			$retour = file_put_contents( $folder . '/' . $fichierDest, $file );

			if($retour === FALSE){
				return FALSE;
			}

			$zip = new ZipArchive;

			// We verify if we can use the archive
			if ( $zip->open( $folder . '/' . $fichierDest ) === true ) {

				// Let's unzip
				$zip->extractTo( $folder );

				// Fermeture de l'archive
				$zip->close();

				// On supprime le fichier zip
				unlink( $folder . '/' . $fichierDest );

				return TRUE;
			}

			return FALSE;
		} else {
			return FALSE;
		}
	}

	/**
	 * Recupere le Zip de la derniere version en ligne de Wordpress et le place dans le dossier courant
	 * 
	 * Status : Ok
	 * 
	 */
	public function wp_download_wordpress(){

		// Get WordPress data
		$wp = json_decode( file_get_contents( $this->_wp_api ) )->offers[0];

		$fichierDest = 'wordpress-' . $wp->version . '-' . $this->_wp_lang . '.zip';
		$file = file_get_contents( $wp->download );

		if($file === FALSE){
			return FALSE;
		}

		$retour = file_put_contents( $fichierDest, $file );

		if($retour === FALSE){
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Recupere le Zip de la derniere version en ligne de Wordpress
	 * Extrait le Zip de la version telechargé
	 * Supprime les fichiers telechargés et non utiles
	 * 
	 *  Status : Ok
	 * 
	 */
	public function wp_download_extract(){

		// Get WordPress data
		$file = file_get_contents( $this->_wp_api );

		$wp = json_decode( $file )->offers[0];
		$fichierDest = 'wordpress-' . $wp->version . '-' . $this->_wp_lang . '.zip';

		// Chemin complet vers le fichier à télécharger
		$fichierComplet = $this->_wp_dir_core . $fichierDest;

		// Vérification si le fichier existe déjà
		if (!file_exists($fichierComplet)) {
			// Vérification si le dossier n'existe pas et le créer si nécessaire
			if (!is_dir($this->_wp_dir_core)) {
				if (!mkdir($this->_wp_dir_core, 0775, true)) {
					return FALSE; // Erreur lors de la création du dossier
				}
			}

			// Télécharger le fichier et l'enregistrer
			if (file_put_contents($fichierComplet, file_get_contents($wp->download)) === false) {
				return FALSE; // Erreur lors du téléchargement du fichier
			}
		}

		$zip = new ZipArchive;

		if ($zip->open($fichierComplet) === true) {
			// Extraction de l'archive
			if (!$zip->extractTo('./')) {
				$zip->close();
				return FALSE; // Erreur lors de l'extraction
			}
	
			// Fermeture de l'archive après l'extraction
			$zip->close();
	
			// Modification des permissions du dossier wordpress
			chmod('wordpress', 0775);
	
			$sourceDir = 'wordpress';
			$destinationDir = './';
			
			// Vérification que le dossier source existe
			if (!is_dir($sourceDir)) {
				return FALSE;
			}
			
			// On scanne le dossier source
			$files = scandir($sourceDir);
			
			if (is_array($files)) {
				// On retire les dossiers "." et ".."
				$files = array_diff($files, array('.', '..'));
			
				// Déplacement des fichiers et dossiers dans le répertoire supérieur
				foreach ($files as $file) {
					$source = $sourceDir . '/' . $file;
					$destination = $destinationDir . $file;
			
					// Vérifier s'il s'agit d'un fichier ou d'un dossier
					if (is_dir($source)) {
							// Utiliser une fonction récursive pour déplacer un dossier
							rrmdir_move($source, $destination);
					} else {
							// Déplacer un fichier
							if (!rename($source, $destination)) {
								return FALSE; // Arrêter l'exécution en cas d'échec
							}
					}
				}
			
				// Suppression du dossier wordpress vide
				rmdir($sourceDir);
			} else {
				return FALSE;
			}

			if (file_exists('./wp-content/plugins/hello.php')) {
				unlink('./wp-content/plugins/hello.php');
			}

			// Suppression du fichier téléchargé et du dossier temporaire
			unlink($fichierComplet);

			return TRUE;
		}

		return FALSE;
	}

	public function wp_download_url_extract($url){

		$fichierDest = 'wordpress-custom.zip';

		$file = file_get_contents( $url );

		if($file === FALSE){
			return FALSE;
		}

		file_put_contents( $fichierDest, $file );

		$zip = new ZipArchive;

		if ($zip->open($fichierDest) === true) {
			// Boucle pour parcourir tous les fichiers dans l'archive ZIP
			for ($i = 0; $i < $zip->numFiles; $i++) {
				$filename = $zip->getNameIndex($i);
	
				// Vérification si le fichier n'est pas un fichier ZIP
				if (substr($filename, -4) !== '.zip') {
					// Extraction de ce fichier seulement
					$zip->extractTo('./', $filename);
				}
			}
	
			// Fermeture de l'archive après l'extraction
			$zip->close();
		} else {
			return FALSE; // Erreur lors de l'ouverture de l'archive
		}

		// Suppression du fichier téléchargé et du dossier temporaire
		unlink($fichierDest);

		return TRUE;
	}

	/**
	 * Ecrit le fichier de configuration
	 *
	 * @param string $opts
	 *
	 * @return bool retourne true ou false
	 */
	public function wp_install_config($opts){

		// We retrieve each line as an array
		$config_file = file_get_contents( 'wp-config-sample.php' );

		// Managing the security keys
		$secret_keys = explode( "\n", file_get_contents( 'https://api.wordpress.org/secret-key/1.1/salt/' ) );

		foreach ( $secret_keys as $k => $v ) {
			$secret_keys[$k] = substr( $v, 28, 64 );
		}

		// We replace the values in the wp-config file
		$config_file = preg_replace( '/define\( \'DB_NAME\', \'(.*)\' \);/', "define( 'DB_NAME', '" . $opts['dbname'] . "' );", $config_file );
		$config_file = preg_replace( '/define\( \'DB_USER\', \'(.*)\' \);/', "define( 'DB_USER', '" . $opts['dbuser'] . "' );", $config_file );
		$config_file = preg_replace( '/define\( \'DB_PASSWORD\', \'(.*)\' \);/', "define( 'DB_PASSWORD', '" . $opts['dbpassword'] . "' );", $config_file );
		$config_file = preg_replace( '/define\( \'DB_HOST\', \'(.*)\' \);/', "define( 'DB_HOST', '" . $opts['dbhost'] . "' );", $config_file );

		$config_file = preg_replace( '/define\( \'AUTH_KEY\',         \'(.*)\' \);/', "define( 'AUTH_KEY','" . $secret_keys[0] . "' );", $config_file );
		$config_file = preg_replace( '/define\( \'SECURE_AUTH_KEY\',  \'(.*)\' \);/', "define( 'SECURE_AUTH_KEY', '" . $secret_keys[1] . "' );", $config_file );
		$config_file = preg_replace( '/define\( \'LOGGED_IN_KEY\',    \'(.*)\' \);/', "define( 'LOGGED_IN_KEY', '" . $secret_keys[2] . "' );", $config_file );
		$config_file = preg_replace( '/define\( \'NONCE_KEY\',        \'(.*)\' \);/', "define( 'NONCE_KEY', '" . $secret_keys[3] . "' );", $config_file );
		$config_file = preg_replace( '/define\( \'AUTH_SALT\',        \'(.*)\' \);/', "define( 'AUTH_SALT', '" . $secret_keys[4] . "' );", $config_file );
		$config_file = preg_replace( '/define\( \'SECURE_AUTH_SALT\', \'(.*)\' \);/', "define( 'SECURE_AUTH_SALT', '" . $secret_keys[5] . "' );", $config_file );
		$config_file = preg_replace( '/define\( \'LOGGED_IN_SALT\',   \'(.*)\' \);/', "define( 'LOGGED_IN_SALT', '" . $secret_keys[6] . "' );", $config_file );
		$config_file = preg_replace( '/define\( \'NONCE_SALT\',       \'(.*)\' \);/', "define( 'NONCE_SALT', '" . $secret_keys[7] . "' );", $config_file );

		$config_file = preg_replace( '/\$table_prefix  = \'wp_\'/', "\$table_prefix  = '" . $opts['table_prefix'] . "'", $config_file );

		// We write the new configuration file
		file_put_contents( 'wp-config.php', $config_file );

		unlink('wp-config-sample.php' );

		return TRUE;
	}

	/**
	 * On test si la base de donnée est accessible ou existante
	 *
	 * @return bool true|false
	 */
	public function wp_test_bdd(){

		$this->set_var_wp();

		try{
			$bdd = Bdd::getInstance();

			if($bdd->dbh == null) {
				$this->retour(array('message' => 'Connection à votre base de données impossible.'), FALSE);
			}

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
		
		require_once( 'wp-config.php' );
		require_once( 'wp-load.php' );
		require_once( 'wp-admin/includes/upgrade.php' );
		require_once( 'wp-includes/wp-db.php' );

		// WordPress installation
		wp_install($opts['weblog_title'], $opts['user_login'], $opts['admin_email'], (int) $opts['blog_public'], '', $opts['admin_password']);

		// We update the options with the right siteurl et homeurl value
		$newurl = 'https://'.$_SERVER['SERVER_NAME'] . rtrim(dirname($_SERVER['REQUEST_URI']), '/');
		update_option( 'siteurl', $newurl);
		update_option( 'home', $newurl);

		update_option( 'permalink_structure', '/%postname%/');

		return TRUE;
	}

	/**
	 * Modifie les urls dans la table de configuration et les contenues
	 *
	 * @param string $oldurl ancienne url ( a remplacer ) 
	 * @param string $newurl nouvelle url ( en remplacement )
	 * 
	 */
	public function wp_url($oldurl, $newurl) {

		$this->set_var_wp();

		$bdd = Bdd::getInstance();

		if($bdd->dbh == null){
			$this->retour(array('message' => 'Connection à votre base de données impossible.'), FALSE);
		}

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
	 * Supprime les revisions de la bdd
	 */
	public function wp_sql_clean_revision() {

		$this->set_var_wp();

		$bdd = Bdd::getInstance();

		if($bdd->dbh == null){
			$this->retour(array('message' => 'Connection à votre base de données impossible.'), FALSE);
		}

		$sql = $bdd->dbh->prepare('DELETE FROM '.$this->_table_prefix.'posts WHERE post_type = "revision"');
		
		return $sql->execute();
	}

	/**
	 * Supprime tous les commentaires non approuvés
	 */
	public function wp_sql_clean_spam() {

		$this->set_var_wp();

		$bdd = Bdd::getInstance();

		if($bdd->dbh == null){
			$this->retour(array('message' => 'Connection à votre base de données impossible.'), FALSE);
		}

		$sql = $bdd->dbh->prepare('DELETE from '.$this->_table_prefix.'comments WHERE comment_approved = 0');
		
		return $sql->execute();
	}

	/**
	 * Install une liste de plugin separé par une , 
	 *
	 * @param string $plug_off liste separé par une , 
	 */
	public function wp_install_plugins($plug_off){

		require_once( 'wp-config.php' );
		require_once( 'wp-load.php' );
		require_once( 'wp-admin/includes/plugin.php');

		$plugins     = explode( ",", $plug_off );
		$plugins     = array_map( 'trim' , $plugins );
		$plugins_dir = 'wp-content/plugins/';

		foreach ( $plugins as $plugin ) {

			// We retrieve the plugin XML file to get the link to downlad it
			$plugin_repo = file_get_contents( "http://api.wordpress.org/plugins/info/1.0/$plugin.json" );

			if ( $plugin_repo && $plugin = json_decode( $plugin_repo ) ) {

				$plugin_path = $plugins_dir . $plugin->slug . '-' . $plugin->version . '.zip';

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

		// Récupérer les plugins actifs
		$plugins_active = array_keys(get_plugins());

		// Tableau pour stocker les plugins à activer
		$plugins_a_activer = [];

		// Boucler sur les plugins actifs pour trouver ceux qui correspondent à ton tableau
		foreach ($plugins_active as $plugin_path) {
			foreach ($plugins as $plugin_name) {
				// Vérifier si le nom du plugin est contenu dans le chemin
				if (strpos($plugin_path, $plugin_name) !== false) {
					$plugins_a_activer[] = $plugin_path; // Ajouter le chemin complet
				}
			}
		}

		// Activer les plugins correspondants
		if (!empty($plugins_a_activer)) {
			activate_plugins($plugins_a_activer);
		}

		return TRUE;
	}

	/**
	 * Supprime les themes default de Wordpress
	 *
	 */
	public function wp_delete_theme(){

		require_once( 'wp-config.php' );
		require_once( 'wp-load.php' );
		require_once( 'wp-admin/includes/upgrade.php' );
		
		delete_theme( 'twentytwentyfour' );
		delete_theme( 'twentyfourteen' );
		delete_theme( 'twentythirteen' );
		delete_theme( 'twentytwelve' );
		delete_theme( 'twentyeleven' );
		delete_theme( 'twentyten' );
		// We delete the _MACOSX folder (bug with a Mac)
		delete_theme( '__MACOSX' );

		return TRUE;
	}

	/**
	 * Supprime les themes de Wordpress
	 *
	 * @param array $themes Nom des themes
	 */
	public function wp_delete_theme_choix($themes){

		require_once( 'wp-config.php' );
		require_once( 'wp-load.php' );
		require_once( 'wp-admin/includes/upgrade.php' );

		foreach ($themes as $theme_name) {
			delete_theme( $theme_name );
		}

		return TRUE;
	}

	/**
	 * Clone les themes de Wordpress
	 */
	public function wp_clone_theme_choix($themes){
		
		// copie les dossiers existants avec -clone a la fin du dossier
		foreach ($themes as $theme) {
			$theme_clone = $theme . '-clone';
			recurse_copy( 'wp-content/themes/' . $theme, 'wp-content/themes/' . $theme_clone );
		}

		return TRUE;
	}

	/**
	 * Ajoute un utilisateur a Wordpress
	 *
	 * @param string $user_login 	Nom utilisateur
	 * @param string $user_pass 	Mot de passe utilisateur
	 *
	 */
	public function wp_add_user($user_login, $user_pass){

		require_once( 'wp-config.php' );
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
		$this->set_var_wp();

		// on verifie que le prefix est valide avec un _ a la fin
		if (substr($new_prefix, -1) != '_'){
			$new_prefix .= '_';
		}

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
		
		if($bdd->dbh == null){
			$this->retour(array('message' => 'Connection à votre base de données impossible.'), FALSE);
		}

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
		$this->set_var_wp();

		$bdd = Bdd::getInstance();

		if($bdd->dbh == null){
			$this->retour(array('message' => 'Connection à votre base de données impossible.'), FALSE);
		}

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
	 * Telecharge la nouvelle version de migration.php
	 */
	public function wp_update()
	{
		$content = file_get_contents('https://raw.githubusercontent.com/emulsion-io/wp-migration-url/master/dist/migration.php');

		file_put_contents('migration.php', $content);

		return TRUE;
	}

	/**
	 * 
	 * Verifie si une nouvelle version est dispo.
	 * 
	 */
	public function migration_check_update()
	{
		$content = file_get_contents('https://raw.githubusercontent.com/emulsion-io/wp-migration-url/master/version.json'.'?'.mt_rand());
		$version = json_decode($content);

		//var_dump($version); exit;

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
		$exp = floor(log((int) $bytes, 1024)) | 0;
		
		if($units === TRUE) {
			return round((int)$bytes / (pow(1024, $exp)), $precision).$unit[$exp];
		} else {
			return round((int)$bytes / (pow(1024, $exp)), $precision);
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
		return addcslashes( str_replace( array( ';', '\n' ), '', $str ), '\\' );
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
		// Construction du DSN
		$dsn = 'mysql:host=' . Config::read('db.host') .
				';dbname='    . Config::read('db.basename') .
				';charset=utf8';
	
		$user = Config::read('db.user');
		$password = Config::read('db.password');
	
		try {
			// Ajout de l'option pour limiter le temps d'attente de la connexion
			$options = [
					PDO::ATTR_TIMEOUT => 1,  // Timeout de 5 secondes
					PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,  // Pour lancer des exceptions sur erreur
			];
	
			// Connexion à la base de données avec un timeout de 5 secondes
			$this->dbh = new PDO($dsn, $user, $password, $options);
		} catch (PDOException $e) {
			//echo 'Connection failed: ' . $e->getMessage();
			//exit;
		}
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
		return isset(self::$confArray[$name]) ? self::$confArray[$name] : false;
	}

	public static function write($name, $value)
	{
		self::$confArray[$name] = $value;
	}
}

/** 
 * Helper : function to display the status of a function
 */
function displayFunctionStatus($functionName, $condition) {
	echo "<li>Fonction {$functionName} : ";
	echo $condition ? "<span class='text-green'>is enabled</span>" : "<span class='text-red'>is disabled</span>";
	echo "</li>";
}

/**
 * Helper : Copie un dossier et son contenu
 */
function recurse_copy($src, $dst) {
	$dir = opendir($src);
	@mkdir($dst);
	while (false !== ($file = readdir($dir))) {
		if (($file != '.') && ($file != '..')) {
			if (is_dir($src . '/' . $file)) {
				recurse_copy($src . '/' . $file, $dst . '/' . $file);
			} else {
				copy($src . '/' . $file, $dst . '/' . $file);
			}
		}
	}
	closedir($dir);
}

/**
 * Fonction récursive pour déplacer tout le contenu d'un dossier.
 */
function rrmdir_move($src, $dst)
{
	// Créer le dossier de destination s'il n'existe pas
	if (!is_dir($dst)) {
		mkdir($dst, 0775);
	}

	// Lire les fichiers et dossiers dans la source
	$items = scandir($src);
	foreach ($items as $item) {
		if ($item == '.' || $item == '..') {
				continue;
		}

		$sourcePath = $src . '/' . $item;
		$destinationPath = $dst . '/' . $item;

		if (is_dir($sourcePath)) {
				// Déplacement récursif pour les sous-dossiers
				rrmdir_move($sourcePath, $destinationPath);
		} else {
				// Déplacer un fichier
				rename($sourcePath, $destinationPath);
		}
	}

	// Suppression du dossier source une fois vide
	rmdir($src);
}

$migration = new Wp_Migration();
$update    = $migration->migration_check_update();

 

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


/**
 * Si un fichier wp-config.php existe, le script comprend que WP est deja installé
 */
if(file_exists('wp-config.php')) {

	define( 'WP_INSTALLING', true );

	$wp_exist = TRUE;

	// Recupere les informations sur le WP courant
	$site_url = $migration->wp_get_info("siteurl");

	if($site_url == false) {
		$site_url = [];
		$site_url['option_value'] = '';

		$wp_exist_install = FALSE;
	} else {
		$migration->get_theme_plugin_wp();

		$wp_exist_install = TRUE;
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
									<?php
										displayFunctionStatus('mail()', function_exists('mail'));
										displayFunctionStatus('file_get_contents()', ini_get('allow_url_fopen'));
										displayFunctionStatus('file_put_contents()', function_exists('file_put_contents'));
										displayFunctionStatus('fopen()', function_exists('fopen'));
										displayFunctionStatus('shell_exec()', function_exists('shell_exec'));
										displayFunctionStatus('exec()', function_exists('exec'));
										displayFunctionStatus('system()', function_exists('system'));
									?>
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
										<button type="submit" id="go_action_update" class="btn btn-primary">Effectuer la mise à jour du script</button>
									</form>
									<script>
										$( "#action_update" ).submit(function( event ) {
											var donnees = {
												'action_update'	: 'ok'
											}
											sendform('action_update', donnees, 'Effectuer la mise à jour du script');
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
								<?php else: ?>
									<ul>
										<li>Wordpress est présent sur ce serveur.</li>
										<?php if($wp_exist_install === TRUE) : ?>
											<li>URL du site : <?php echo $site_url['option_value']; ?></li>
										<?php else : ?>
											<li>URL du site : <span class="text-danger">Wordpress non configuré</span></li>
										<?php endif; ?>
									</ul>
								<?php endif; ?>

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

							</div>
						</div>
					</div>
				</div>
			</article>

			<?php if($zips_wp) : ?>
			<article class="row">
				<div class="col-12">
					<div class="card border-info mb-3" >
						<div class="card-header">Installations personnaliées - Instances</div>
						<div class="card-body">
							<h4 class="card-title"></h4>
							<div class="card-text">

								<div class="row">
									
									<div class="col-12">
										<h4 class="text-info mb-3">Fichiers</h4>
										<h5>Envoyer le Zip</h5>
										<div class="text-warning mb-3">
											Télécharge le zip sur le serveur dans le dossier courant.
										</div>
									</div>

									<?php $i = 0; foreach($zips_wp as $zip) : ?>
										<div class="col-3">
											<form id="action_dl_zip_<?=$i;?>" method="post">
												<button type="submit" id="go_action_dl_zip" class="btn btn-primary"><?= $zip['nom']; ?></button>
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

									<div class="col-12 mt-3">
										<h5>Envoyer et extraire le Zip</h5>
										<div class="text-warning mb-3">
											Télécharge et extrait le zip sur le serveur dans le dossier courant.
										</div>
									</div>

									<?php $i = 0; foreach($zips_wp as $zip) : ?>
										<?php if(! isset($zip['fichier'])) continue; ?>
										<div class="col-3">
											<form id="action_dl_zip_extract_<?=$i;?>" method="post">
												<button type="submit" id="go_action_dl_zip" class="btn btn-primary"><?= $zip['nom']; ?></button>
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

									<div class="col-12 mt-4">
										<h4 class="text-info mb-3">Base de données</h4>
										<h5>Envoyer la base de données</h5>
										<div class="text-warning mb-3">
											Copie la base de données sur le serveur dans le dossier /bdd_tmp/
										</div>
									</div>

									<?php $i = 0; foreach($zips_wp as $zip) : ?>
										<?php if(! isset($zip['sql'])) continue; ?>
										<div class="col-3">
											<form id="action_dl_bdd<?=$i;?>" method="post">
												<button type="submit" id="go_action_dl_bdd" class="btn btn-primary"><?= $zip['nom']; ?> </button>
											</form>
											<script>
												$( "#action_dl_bdd<?=$i;?>" ).submit(function( event ) {
													event.preventDefault();
													var donnees = {
														action_dl_bdd : 'ok',
														sql : '<?= $zip['sql']; ?>'
													}
													sendform('action_dl_bdd', donnees, 'La BDD de <?= $zip['nom']; ?> est copié sur le serveur');
												});
											</script>
										</div>
									<?php $i++; endforeach; ?>

									<div class="col-12 mt-4">
										<h5>Envoyer et installer la base de données</h5>
										<div class="text-warning mb-3">
											Installe la base de données sur le serveur en utilisant le fichier SQL et les infos de connexion de wp-config.php
										</div>
									</div>

									<?php $i = 0; foreach($zips_wp as $zip) : ?>
										<?php if(! isset($zip['sql'])) continue; ?>
										<div class="col-3">
											<form id="action_dl_install_bdd<?=$i;?>" method="post">
												<button type="submit" id="go_action_dl_install_bdd" class="btn btn-primary"><?= $zip['nom']; ?> </button>
											</form>
											<script>
												$( "#action_dl_install_bdd<?=$i;?>" ).submit(function( event ) {
													event.preventDefault();
													var donnees = {
														action_dl_install_bdd : 'ok',
														sql : '<?= $zip['sql']; ?>'
													}
													sendform('action_dl_install_bdd', donnees, 'La BDD de <?= $zip['nom']; ?> est installée sur le serveur');
												});
											</script>
										</div>
									<?php $i++; endforeach; ?>

								</div>

							</div>
						</div>
					</div>
				</div>
			</article>
			<?php endif; ?>

			<?php if($zips_themes) : ?>
			<article class="row">
				<div class="col-12">
					<div class="card border-info mb-3" >
						<div class="card-header">Installations personnaliées - Thèmes</div>
						<div class="card-body">
							<h4 class="card-title"></h4>
							<div class="card-text">

								<div class="row mt-3">
									
									<div class="col-12">
										<h5>Envoyer le Zip</h5>
										<div class="text-warning mb-3">
											Télécharge le zip sur le serveur dans le dossier des thèmes de Wordpress.
										</div>
									</div>

									<?php $i = 0; foreach($zips_themes as $zip) : ?>
										<div class="col-3">
											<form id="action_dl_zip_theme_<?=$i;?>" method="post">
												<button type="submit" id="go_action_dl_zip_theme_" class="btn btn-primary"><?= $zip['nom']; ?></button>
											</form>
											<script>
												$( "#action_dl_zip_theme_<?=$i;?>" ).submit(function( event ) {
													event.preventDefault();
													var donnees = {
														action_dl_zip_theme : 'ok',
														url : '<?= $zip['fichier']; ?>'
													}
													sendform('action_dl_zip_theme', donnees, 'Le zip du theme <?= $zip['nom']; ?> est sur le serveur');
												});
											</script>
										</div>
									<?php $i++; endforeach; ?>

									<div class="col-12 mt-4">
										<h5>Envoyer et extraire le Zip</h5>
										<div class="text-warning mb-3">
											Télécharge et extrait le zip sur le serveur dans le dossier des thèmes de Wordpress.
										</div>
									</div>

									<?php $i = 0; foreach($zips_themes as $zip) : ?>
										<div class="col-3">
											<form id="action_dl_extract_zip_theme_<?=$i;?>" method="post">
												<button type="submit" id="go_action_dl_extract_zip_theme" class="btn btn-primary"><?= $zip['nom']; ?></button>
											</form>
											<script>
												$( "#action_dl_extract_zip_theme_<?=$i;?>" ).submit(function( event ) {
													event.preventDefault();
													var donnees = {
														action_dl_extract_zip_theme : 'ok',
														url : '<?= $zip['fichier']; ?>'
													}
													sendform('action_dl_extract_zip_theme', donnees, 'Le theme <?= $zip['nom']; ?> est installé sur le serveur');
												});
											</script>
										</div>
									<?php $i++; endforeach; ?>

								</div>

							</div>
						</div>
					</div>
				</div>
			</article>
			<?php endif; ?>

			<?php if($zips_plugins) : ?>
			<article class="row">
				<div class="col-12">
					<div class="card border-info mb-3" >
						<div class="card-header">Installations personnaliées - Plugins</div>
						<div class="card-body">
							<h4 class="card-title"></h4>
							<div class="card-text">

								<div class="row mt-3">
									
									<div class="col-12">
										<h5>Envoyer le Zip</h5>
										<div class="text-warning mb-3">
											Télécharge le zip sur le serveur dans le dossier des plugins de Wordpress.
										</div>
									</div>

									<?php $i = 0; foreach($zips_plugins as $zip) : ?>
										<div class="col-3">
											<form id="action_dl_zip_plugin_<?=$i;?>" method="post">
												<button type="submit" id="go_action_dl_zip_plugin_" class="btn btn-primary"><?= $zip['nom']; ?></button>
											</form>
											<script>
												$( "#action_dl_zip_plugin_<?=$i;?>" ).submit(function( event ) {
													event.preventDefault();
													var donnees = {
														action_dl_zip_plugin : 'ok',
														url : '<?= $zip['fichier']; ?>'
													}
													sendform('action_dl_zip_plugin', donnees, 'Le zip du plugin <?= $zip['nom']; ?> est sur le serveur');
												});
											</script>
										</div>
									<?php $i++; endforeach; ?>

									<div class="col-12 mt-4">
										<h5>Envoyer et extraire le Zip</h5>
										<div class="text-warning mb-3">
											Télécharge et extrait le zip sur le serveur dans le dossier des plugins de Wordpress.
										</div>
									</div>

									<?php $i = 0; foreach($zips_plugins as $zip) : ?>
										<div class="col-3">
											<form id="action_dl_extract_zip_plugin_<?=$i;?>" method="post">
												<button type="submit" id="go_action_dl_extract_zip_plugin" class="btn btn-primary"><?= $zip['nom']; ?></button>
											</form>
											<script>
												$( "#action_dl_extract_zip_plugin_<?=$i;?>" ).submit(function( event ) {
													event.preventDefault();
													var donnees = {
														action_dl_extract_zip_plugin : 'ok',
														url : '<?= $zip['fichier']; ?>'
													}
													sendform('action_dl_extract_zip_plugin', donnees, 'Le plugin <?= $zip['nom']; ?> est installé sur le serveur');
												});
											</script>
										</div>
									<?php $i++; endforeach; ?>

								</div>

							</div>
						</div>
					</div>
				</div>
			</article>
			<?php endif; ?>

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
						Télécharger et extraire un Wordpress avec possibilité de l'installer
					</button>
				</div>
				<div class="col-12">
					<div class="collapse" id="tools-1">
						<div class="card card-body">

							<div class="text-warning mb-3">
								Télécharge et extrait Wordpress dans sa derniere version du site officiel, et l'installe en remplissant les options.
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
										<input type="text" class="form-control" id="pwd" name="pwd" placeholder="" value="">
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
										admin_password	: $('#admin_password').val()
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
					<buttonn id="go-tools-2-2" class="btn btn-primary btn-block text-left" type="button" data-toggle="collapse" data-target="#tools-2-2" aria-expanded="false" aria-controls="tools-2-2">Editer wp-config.php - Base de données</buttonn>
				</div>
				<div class="col-12">
					<div class="collapse" id="tools-2-2">
						<div class="card card-body">
							<div class="text-warning mb-3">
								<ul>
									<li>Modifie les informations de connexion de wp-config.php</li>
								</ul>
							</div>

							<form id="action_change_wpconfig" method="post">
								<div class="form-group">
									<label for="db_name">DB_NAME</label>
									<input type="text" class="form-control" id="db_name" name="db_name" placeholder="" value="" required>
								</div>
								<div class="form-group">
									<label for="db_user">DB_USER</label>
									<input type="text" class="form-control" id="db_user" name="db_user" placeholder="" value="" required>
								</div>
								<div class="form-group">
									<label for="db_password">DB_PASSWORD</label>
									<input type="text" class="form-control" id="db_password" name="db_password" placeholder="" value="" required>
								</div>
								<div class="form-group">
									<label for="db_host">DB_HOST</label>
									<input type="text" class="form-control" id="db_host" name="db_host" placeholder="" value="" required>
								</div>
								<?php if($wp_exist == TRUE) : ?>
								<div class="form-group">
									<button type="submit" id="go_action_change_wpconfig" class="btn btn-primary">Mettre a jour</button>
								</div>
								<?php else: ?>
									<div class="alert alert-dismissible alert-danger mt-3">
										<button type="button" class="close" data-dismiss="alert">&times;</button>
										<strong>Oh Attention!</strong> Wordpress n'est pas installé sur ce serveur.
									</div>
								<?php endif; ?>
							</form>
							<script>
								$( "#action_change_wpconfig" ).submit(function( event ) {
									event.preventDefault();

									var donnees = {
										'action_change_wpconfig'	: 'ok',
										'db_name' 				: $('#db_name').val(),
										'db_user' 				: $('#db_user').val(),
										'db_password' 			: $('#db_password').val(),
										'db_host' 				: $('#db_host').val()
									}
									sendform('action_change_wpconfig', donnees, 'Ecriture des nouvelles informations BDD');
									
								});
							</script>

						</div>
					</div>
				</div>
			</div>

			<div class="row mb-3">
				<div class="col-12 mb-2">
					<buttonn id="go-tools-2-3" class="btn btn-primary btn-block text-left" type="button" data-toggle="collapse" data-target="#tools-2-3" aria-expanded="false" aria-controls="tools-2-3">Editer wp-config.php - Options de développement</buttonn>
				</div>
				<div class="col-12">
					<div class="collapse" id="tools-2-3">
						<div class="card card-body">
							<div class="text-warning mb-3">
								<ul>
									<li>Modifie les informations de debug de wp-config.php</li>
									<li>Debug : Affiche les erreurs PHP</li>
									<li>Debug_display : Affiche les erreurs PHP a l'ecran</li>
									<li>Debug_log : Ecrit les erreurs PHP dans un fichier</li>
								</ul>
							</div>

							<form id="action_change_wpconfig_dev" method="post">

								<div class="form-group form-check">
									<input type="checkbox" id="edit_debug" name="debug" class="form-check-input" value="1" <?= ($migration->_debug) ? 'checked' : '' ; ?>> 
									<label class="form-check-label" for="edit_debug">Debug</label>
								</div>

								<div class="form-group form-check">
									<input type="checkbox" id="edit_debug_display" name="debug_display" class="form-check-input" value="1" <?= ($migration->_debug_display) ? 'checked' : '' ; ?>> 
									<label class="form-check-label" for="edit_debug_display">Debug_display</label>
								</div>

								<div class="form-group form-check">
									<input type="checkbox" id="edit_debug_log" name="debug_log" class="form-check-input" value="1" <?= ($migration->_debug_log) ? 'checked' : '' ; ?>> 
									<label class="form-check-label" for="edit_debug_log">Debug_log</label>
								</div>

								<?php if($wp_exist == TRUE) : ?>
								<div class="form-group">
									<button type="submit" id="go_action_change_wpconfig_dev" class="btn btn-primary">Mettre a jour</button>
								</div>
								<?php else: ?>
									<div class="alert alert-dismissible alert-danger mt-3">
										<button type="button" class="close" data-dismiss="alert">&times;</button>
										<strong>Oh Attention!</strong> Wordpress n'est pas installé sur ce serveur.
									</div>
								<?php endif; ?>
							</form>
							<script>
								$( "#action_change_wpconfig_dev" ).submit(function( event ) {
									event.preventDefault();

									var donnees = {
										'action_change_wpconfig_dev'	: 'ok',
										'debug' 					: $('#edit_debug').is(':checked'),
										'debug_display' 		: $('#edit_debug_display').is(':checked'),
										'debug_log' 			: $('#edit_debug_log').is(':checked')
									}
									sendform('action_change_wpconfig_dev', donnees, 'Ecriture des nouvelles informations de debug');
									
								});
							</script>

						</div>
					</div>
				</div>
			</div>

			<div class="row mb-3">
				<div class="col-12 mb-2">
					<buttonn id="go-tools-2" class="btn btn-primary btn-block text-left" type="button" data-toggle="collapse" data-target="#tools-2" aria-expanded="false" aria-controls="tools-2">Modifier les Urls - Dans la BDD</buttonn>
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
					<buttonn id="go-tools-2-1" class="btn btn-primary btn-block text-left" type="button" data-toggle="collapse" data-target="#tools-2-1" aria-expanded="false" aria-controls="tools-2">Modifier les Urls - Génération des Rqs SQL</buttonn>
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
									<li>/!\ Ecrase le fichier .htaccess existant</li>
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
									<li>Efface les révisions* des articles, des pages et de tous les contenus.</li>
									<li>* Les révisions sont des versions antérieures de vos contenus qui peuvent être restaurés.</li>
								</ul>
							</div>

							<form id="action_clean_revision" method="post">
								<?php if($wp_exist == TRUE) : ?>
								<div class="form-group">
									<button type="submit" id="go_action_clean_revision" class="btn btn-primary">Effacer les révisions</button>
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
									<li>Permet de supprimer très simplement une vague de spam</li>
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
									<li>Merci de séparer les noms par une virgule, utiliser le slug d'url en guise de nom.</li>
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
					<button id="go-tools-7" class="btn btn-primary btn-block text-left" type="button" data-toggle="collapse" data-target="#tools-7" aria-expanded="false" aria-controls="tools-7">Supprime les thèmes par defaut de Wordpress</button>
				</div>
				<div class="col-12">
					<div class="collapse" id="tools-7">
						<div class="card card-body">
							<div class="text-warning mb-3">
								<ul>
									<li>Supprime l'ensemble des thèmes suivant : </li>
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
									<button id="go_action_delete_theme" type="submit" class="btn btn-primary">Supprime les thèmes</button>
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
									sendform('action_delete_theme', donnees, 'Supprime les thèmes defaut de Wordpress');
								});
							</script>
						</div>
					</div>
				</div>
			</div>

			<div class="row mb-3">
				<div class="col-12 mb-2">
					<button id="go-tools-7-1" class="btn btn-primary btn-block text-left" type="button" data-toggle="collapse" data-target="#tools-7-1" aria-expanded="false" aria-controls="tools-7-1">Supprime des thèmes (selection)</button>
				</div>
				<div class="col-12">
					<div class="collapse" id="tools-7-1">
						<div class="card card-body">
							<div class="text-warning mb-3">
								<ul>
									<li>Supprime les thèmes suivant : </li>
								</ul>
							</div>

							<form id="action_delete_theme_choix" method="post">

								<?php if($wp_exist == TRUE) : ?>
									<?php foreach($migration->_themes as $theme) : ?>
										<div class="form-group">
											<div class="custom-control custom-checkbox">
												<input type="checkbox" id="theme_<?= $theme ?>" name="theme" class="custom-control-input" value="<?= $theme ?>"> 
												<label class="custom-control-label" for="theme_<?= $theme ?>"><?= $theme ?></label>
											</div>
										</div>
									<?php endforeach; ?>
								<?php endif; ?>

								<?php if($wp_exist == TRUE) : ?>
								<div class="form-group">
									<button id="go_action_delete_theme" type="submit" class="btn btn-primary">Supprime les thèmes</button>
								</div>
								<?php else: ?>
									<div class="alert alert-dismissible alert-danger mt-3">
										<button type="button" class="close" data-dismiss="alert">&times;</button>
										<strong>Oh Attention!</strong> Wordpress n'est pas installé sur ce serveur.
									</div>
								<?php endif; ?>	
							</form>
							<script>
								$( "#action_delete_theme_choix" ).submit(function( event ) {
									event.preventDefault();
									var donnees = {
										'action_delete_theme_choix'	: 'ok',
										'themes': []
									}

									$('input[name="theme"]:checked').each(function() {
										donnees['themes'].push($(this).val());
									});
									sendform('action_delete_theme_choix', donnees, 'Supprime les thèmes');
								});
							</script>
						</div>
					</div>
				</div>
			</div>

			<div class="row mb-3">
				<div class="col-12 mb-2">
					<button id="go-tools-7-2" class="btn btn-primary btn-block text-left" type="button" data-toggle="collapse" data-target="#tools-7-2" aria-expanded="false" aria-controls="tools-7-2">Clone des thèmes</button>
				</div>
				<div class="col-12">
					<div class="collapse" id="tools-7-2">
						<div class="card card-body">
							<div class="text-warning mb-3">
								<ul>
									<li>Clone les thèmes suivant : </li>
								</ul>
							</div>

							<form id="action_clone_theme_choix" method="post">

								<?php if($wp_exist == TRUE) : ?>
									<?php foreach($migration->_themes as $theme) : ?>
										<div class="form-group">
											<div class="custom-control custom-checkbox">
												<input type="checkbox" id="clone_theme_<?= $theme ?>" name="clone_theme" class="custom-control-input" value="<?= $theme ?>"> 
												<label class="custom-control-label" for="clone_theme_<?= $theme ?>"><?= $theme ?></label>
											</div>
										</div>
									<?php endforeach; ?>
								<?php endif; ?>

								<?php if($wp_exist == TRUE) : ?>
								<div class="form-group">
									<button id="go_action_clone_theme_choix" type="submit" class="btn btn-primary">Clone les thèmes</button>
								</div>
								<?php else: ?>
									<div class="alert alert-dismissible alert-danger mt-3">
										<button type="button" class="close" data-dismiss="alert">&times;</button>
										<strong>Oh Attention!</strong> Wordpress n'est pas installé sur ce serveur.
									</div>
								<?php endif; ?>	
							</form>
							<script>
								$( "#action_clone_theme_choix" ).submit(function( event ) {
									event.preventDefault();
									var donnees = {
										'action_clone_theme_choix'	: 'ok',
										'themes': []
									}

									$('input[name="clone_theme"]:checked').each(function() {
										donnees['themes'].push($(this).val());
									});
									sendform('action_clone_theme_choix', donnees, 'Clone les thèmes');
								});
							</script>
						</div>
					</div>
				</div>
			</div>

			<div class="row mb-3">
				<div class="col-12 mb-2">
					<button id="go-tools-8" class="btn btn-primary btn-block text-left" type="button" data-toggle="collapse" data-target="#tools-8" aria-expanded="false" aria-controls="tools-8">Ajouter un administrateur à votre installation</button>
				</div>
				<div class="col-12">
					<div class="collapse" id="tools-8">
						<div class="card card-body">
							<div class="text-warning mb-3">
								<ul>
									<li>Ajouter un Super Admin dans la base de données de votre installation</li>
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
					<button id="go-tools-10" class="btn btn-primary btn-block text-left" type="button" data-toggle="collapse" data-target="#tools-10" aria-expanded="false" aria-controls="tools-10">Supprimer tous les fichiers de Wordpress</button>
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

			<div class="row mb-3">
				<div class="col-12 mb-2">
					<button id="go-tools-11" class="btn btn-primary btn-block text-left" type="button" data-toggle="collapse" data-target="#tools-11" aria-expanded="false" aria-controls="tools-11">Test la connexion à la base de données avec le wp-config.php</button>
				</div>
				<div class="col-12">
					<div class="collapse" id="tools-11">
						<div class="card card-body">
							<div class="text-warning mb-3">
								<ul>
									<li>Test la connexion à la base de données avec le wp-config.php</li>
								</ul>
							</div>

							<form id="action_testconnexion" method="post">
								<div class="form-group">
									<button id="go_action_testconnexion" type="submit" class="btn btn-primary">Tester la connexion</button>
								</div>
							</form>
							<script>
								$( "#action_testconnexion" ).submit(function( event ) {
									event.preventDefault();
									var donnees = {
										action_testconnexion	: 'ok',
									}
									sendform('action_testconnexion', donnees, 'Tester la connexion à la base de données');
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
