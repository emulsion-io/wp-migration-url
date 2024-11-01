<?php

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