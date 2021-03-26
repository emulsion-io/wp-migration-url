<?php
error_reporting(-1);
ini_set('display_errors', '1');
$retour_url = FALSE;
$retour_migration = FALSE;
$retour_migration_api = FALSE;
$retour_migration_log = FALSE;
$retour_export = FALSE;
$retour_import = FALSE;
$retour_export_sql = FALSE;
$retour_import_sql = FALSE;
$retour_htaccess = FALSE;
$retour_dl = FALSE;
$retour_dl_full = FALSE;
$retour_clean_revision = FALSE;
$retour_clean_spam = FALSE;
$retour_plug_install = FALSE;
$retour_delete_theme = FALSE;
$retour_add_user = FALSE;
$retour_action_dl_zip = FALSE;
$retour_action_dl_zip_extract = FALSE;
class Wp_Migration
{
    public $_wp_lang, $_wp_api, $_wp_dir_core, $_version, $_file_destination, $_file_sql, $_file_log, $_file_log_ftp, $_current_rep, $_fileswp;
    public $_dbhost, $_dbname, $_dbuser, $_dbpassword, $_table_prefix;
    public function __construct()
    {
        $this->_version = '2.6.0';
        $this->_wp_lang = 'fr_FR';
        $this->_wp_api = 'http://api.wordpress.org/core/version-check/1.7/?locale=' . $this->_wp_lang;
        $this->_wp_dir_core = 'core/';
        $this->_file_destination = 'migration_file.zip';
        $this->_file_sql = 'migration_bdd.sql';
        $this->_file_log = 'logfile.log';
        $this->_file_log_ftp = 'ftp.log';
        $this->_current_rep = getcwd();
        $this->_fileswp = array('wp-activate.php', 'wp-blog-header.php', 'wp-comments-post.php', 'wp-config.php', 'wp-cron.php', 'wp-links-opml.php', 'wp-load.php', 'wp-login.php', 'wp-mail.php', 'wp-settings.php', 'wp-signup.php', 'wp-trackback.php', 'xmlrpc.php', 'index.php', 'wp-admin', 'wp-content', 'wp-includes', '.htaccess');
    }
    public function set_var_wp($options)
    {
        $this->_dbhost = $options[0];
        $this->_dbname = $options[1];
        $this->_dbuser = $options[2];
        $this->_dbpassword = $options[3];
        $this->_table_prefix = $options[4];
        Config::write('db.host', $this->_dbhost);
        Config::write('db.basename', $this->_dbname);
        Config::write('db.user', $this->_dbuser);
        Config::write('db.password', $this->_dbpassword);
    }
    public function wp_get_info(string $option = 'siteurl')
    {
        $bdd = Bdd::getInstance();
        $req = $bdd->dbh->prepare('SELECT option_value FROM ' . $this->_table_prefix . 'options WHERE option_name = "' . $option . '";');
        $req->execute();
        return $req->fetch();
    }
    public function wp_log($text)
    {
        if (file_exists($this->_file_log)) {
            $old = file_get_contents($this->_file_log);
        } else {
            $old = '';
        }
        $var = $old . date('Y/m/d h:i:s') . ' : ' . $text . '
';
        file_put_contents($this->_file_log, $var);
        return TRUE;
    }
    public function wp_ftp_is_existedir($opts)
    {
        $folder_exists = is_dir('ftp://' . $opts['user_ftp'] . ':' . $opts['ftp_pass'] . '@' . $opts['ftp_url'] . '' . $opts['ftp_folder']);
        return $folder_exists;
    }
    public function wp_ftp_migration($opts, $type_ftp = 'ftp')
    {
        $file = $this->_file_destination;
        $remote_file = $this->_file_destination;
        if ($type_ftp === 'sftp') {
            $connection = ssh2_connect($opts['ftp_url'], 22);
            ssh2_auth_password($connection, $opts['user_ftp'], $opts['ftp_pass']);
            ssh2_scp_send($connection, 'migration.php', rtrim($opts['ftp_folder'], '/') . '/migration.php', 420);
            ssh2_scp_send($connection, $this->_file_sql, rtrim($opts['ftp_folder'], '/') . '/' . $this->_file_sql, 420);
            ssh2_scp_send($connection, $file, rtrim($opts['ftp_folder'], '/') . '/' . $remote_file, 420);
            return TRUE;
        }
        if ($type_ftp === 'ftp') {
            $conn_id = ftp_connect($opts['ftp_url']);
        } elseif ($type_ftp === 'ftps') {
            $conn_id = ftp_ssl_connect($opts['ftp_url']);
        }
        if ($conn_id == FALSE) {
            return FALSE;
        }
        $login_result = ftp_login($conn_id, $opts['user_ftp'], $opts['ftp_pass']);
        if ($login_result == FALSE) {
            return FALSE;
        }
        $this->ftp_putAll($conn_id, '.', rtrim($opts['ftp_folder'], '/'));
        ftp_close($conn_id);
        return TRUE;
    }
    public function wp_clean_ftp_migration()
    {
        @unlink($this->_file_destination);
        @unlink($this->_file_sql);
        @unlink($this->_file_log_ftp);
        return TRUE;
    }
    public function wp_download_zip()
    {
        $wp = json_decode(file_get_contents($this->_wp_api))->offers[0];
        file_put_contents('wordpress-' . $wp->version . '-' . $this->_wp_lang . '.zip', file_get_contents($wp->download));
        return TRUE;
    }
    public function wp_download()
    {
        $wp = json_decode(file_get_contents($this->_wp_api))->offers[0];
        if (!mkdir($this->_wp_dir_core, 509)) {
            return FALSE;
        }
        file_put_contents($this->_wp_dir_core . 'wordpress-' . $wp->version . '-' . $this->_wp_lang . '.zip', file_get_contents($wp->download));
        $zip = new ZipArchive();
        if ($zip->open($this->_wp_dir_core . 'wordpress-' . $wp->version . '-' . $this->_wp_lang . '.zip') === true) {
            $zip->extractTo('.');
            $zip->close();
            chmod('wordpress', 509);
            $files = scandir('wordpress');
            $files = array_diff($files, array('.', '..'));
            foreach ($files as $file) {
                rename('wordpress/' . $file, './' . $file);
            }
            rmdir('wordpress');
            $this->rrmdir('core');
            unlink('./license.txt');
            unlink('./readme.html');
            unlink('./wp-content/plugins/hello.php');
            return TRUE;
        }
        return FALSE;
    }
    public function wp_install_config($opts)
    {
        $config_file = file('wp-config-sample.php');
        $secret_keys = explode('
', file_get_contents('https://api.wordpress.org/secret-key/1.1/salt/'));
        foreach ($secret_keys as $k => $v) {
            $secret_keys[$k] = substr($v, 28, 64);
        }
        $key = 0;
        foreach ($config_file as &$line) {
            if ('$table_prefix  =' == substr($line, 0, 16)) {
                $line = '$table_prefix  = \'' . $this->sanit($opts['prefix']) . '\';
';
                continue;
            }
            if (!preg_match('/^define\\(\'([A-Z_]+)\',([ ]+)/', $line, $match)) {
                continue;
            }
            $constant = $match[1];
            switch ($constant) {
                case 'WP_DEBUG':
                    if ((int) $opts['debug'] == 1) {
                        $line = 'define(\'WP_DEBUG\', \'true\');
';
                        if ((int) $opts['debug_display'] == 1) {
                            $line .= '

 ' . '/** Affichage des erreurs à l\'écran */' . '
';
                            $line .= 'define(\'WP_DEBUG_DISPLAY\', \'true\');
';
                        }
                        if ((int) $opts['debug_log'] == 1) {
                            $line .= '

 ' . '/** Ecriture des erreurs dans un fichier log */' . '
';
                            $line .= 'define(\'WP_DEBUG_LOG\', \'true\');
';
                        }
                    }
                    $line .= '

 ' . '/** On augmente la mémoire limite */' . '
';
                    $line .= 'define(\'WP_MEMORY_LIMIT\', \'256M\');' . '
';
                    break;
                case 'DB_NAME':
                    $line = 'define(\'DB_NAME\', \'' . $this->sanit($opts['dbname']) . '\');
';
                    break;
                case 'DB_USER':
                    $line = 'define(\'DB_USER\', \'' . $this->sanit($opts['uname']) . '\');
';
                    break;
                case 'DB_PASSWORD':
                    $line = 'define(\'DB_PASSWORD\', \'' . $this->sanit($opts['pwd']) . '\');
';
                    break;
                case 'DB_HOST':
                    $line = 'define(\'DB_HOST\', \'' . $this->sanit($opts['dbhost']) . '\');
';
                    break;
                case 'AUTH_KEY':
                case 'SECURE_AUTH_KEY':
                case 'LOGGED_IN_KEY':
                case 'NONCE_KEY':
                case 'AUTH_SALT':
                case 'SECURE_AUTH_SALT':
                case 'LOGGED_IN_SALT':
                case 'NONCE_SALT':
                    $line = 'define(\'' . $constant . '\', \'' . $secret_keys[$key++] . '\');
';
                    break;
                case 'WPLANG':
                    $line = 'define(\'WPLANG\', \'' . $this->sanit($this->_wp_lang) . '\');
';
                    break;
            }
        }
        unset($line);
        $handle = fopen('wp-config.php', 'w');
        foreach ($config_file as $line) {
            fwrite($handle, $line);
        }
        fclose($handle);
        chmod('wp-config.php', 438);
        unlink('wp-config-sample.php');
        return TRUE;
    }
    public function wp_test_bdd()
    {
        try {
            $bdd = Bdd::getInstance();
            $sql = $bdd->dbh->prepare('SHOW TABLES');
            $sql->execute();
            $row = $sql->fetchAll();
            return TRUE;
        } catch (PDOException $e) {
            return FALSE;
        }
    }
    public function wp_install_wp($opts)
    {
        define('WP_INSTALLING', true);
        ;
        ;
        ;
        wp_install($opts['weblog_title'], $opts['user_login'], $opts['admin_email'], (int) $opts['blog_public'], '', $opts['admin_password']);
        $newurl = 'https://' . $_SERVER['SERVER_NAME'] . rtrim(dirname($_SERVER['REQUEST_URI']), '/');
        update_option('siteurl', $newurl);
        update_option('home', $newurl);
        update_option('permalink_structure', '/%postname%/');
        return TRUE;
    }
    public function wp_migration($opts_migration)
    {
        $postdata = http_build_query(array('api_call' => 'migration', 'dbuser' => $opts_migration['user_sql'], 'dbname' => $opts_migration['name_sql'], 'dbpassword' => $opts_migration['pass_sql'], 'dbhost' => $opts_migration['serveur_sql'], 'site' => $opts_migration['www_url'], 'table_prefix' => $opts_migration['table_prefix']));
        $opts = array('http' => array('method' => 'POST', 'header' => 'Content-type: application/x-www-form-urlencoded', 'content' => $postdata));
        $context = stream_context_create($opts);
        ini_set('user_agent', 'Mozilla/4.0 (compatible; MSIE 6.0)');
        $result = file_get_contents(rtrim($opts_migration['www_url'], '/') . '/migration.php', false, $context);
        return $result;
    }
    public function wp_migration_log($opts_migration)
    {
        ini_set('user_agent', 'Mozilla/4.0 (compatible; MSIE 6.0)');
        $result = file_get_contents(rtrim($opts_migration['www_url'], '/') . '/' . $this->_file_log);
        return $result;
    }
    public function wp_url($oldurl, $newurl)
    {
        $bdd = Bdd::getInstance();
        $req1 = $bdd->dbh->prepare('UPDATE ' . $this->_table_prefix . 'options SET option_value = replace(option_value, :oldurl, :newurl) WHERE option_name = \'home\' OR option_name = \'siteurl\';');
        $req2 = $bdd->dbh->prepare('UPDATE ' . $this->_table_prefix . 'posts SET guid = REPLACE (guid, :oldurl, :newurl);');
        $req3 = $bdd->dbh->prepare('UPDATE ' . $this->_table_prefix . 'posts SET post_content = REPLACE (post_content, :oldurl, :newurl);');
        $req4 = $bdd->dbh->prepare('UPDATE ' . $this->_table_prefix . 'postmeta SET meta_value = REPLACE (meta_value, :oldurl, :newurl);');
        $req1->execute(array('oldurl' => $oldurl, 'newurl' => $newurl));
        $req2->execute(array('oldurl' => $oldurl, 'newurl' => $newurl));
        $req3->execute(array('oldurl' => $oldurl, 'newurl' => $newurl));
        $req4->execute(array('oldurl' => $oldurl, 'newurl' => $newurl));
        return TRUE;
    }
    public function wp_htaccess()
    {
        $path = dirname($_SERVER['REQUEST_URI']);
        $ht = '<IfModule mod_rewrite.c>' . '
';
        $ht .= 'RewriteEngine On' . '
';
        $ht .= 'RewriteBase ' . $path . '' . '
';
        $ht .= 'RewriteRule ^index\\.php$ - [L]' . '
';
        $ht .= 'RewriteCond %{REQUEST_FILENAME} !-f' . '
';
        $ht .= 'RewriteCond %{REQUEST_FILENAME} !-d' . '
';
        $ht .= 'RewriteRule . ' . rtrim($path, '/') . '/index.php [L]' . '
';
        $ht .= '</IfModule>' . '
';
        $ht .= '<files wp-config.php>' . '
';
        $ht .= '	order allow,deny' . '
';
        $ht .= '	deny from all' . '
';
        $ht .= '</files> ' . '
';
        $ht .= '<Files .htaccess>' . '
';
        $ht .= '	order allow,deny ' . '
';
        $ht .= '	deny from all ' . '
';
        $ht .= '</Files>' . '
';
        $ht .= 'Options All -Indexes' . '
';
        if (file_exists('.htaccess')) {
            copy('.htaccess', '.htaccess.bak');
        }
        if (file_put_contents('.htaccess', $ht) !== FALSE) {
            return TRUE;
        }
        return FALSE;
    }
    public function wp_configfile($options)
    {
        $filename = 'wp-config.php';
        $content = file_get_contents($filename);
        $content = preg_replace('/define\\(\'DB_NAME\', \'(.*)\'\\);/i', 'define(\'DB_NAME\', \'' . $options['dbname'] . '\');', $content);
        $content = preg_replace('/define\\(\'DB_USER\', \'(.*)\'\\);/i', 'define(\'DB_USER\', \'' . $options['dbuser'] . '\');', $content);
        $content = preg_replace('/define\\(\'DB_PASSWORD\', \'(.*)\'\\);/i', 'define(\'DB_PASSWORD\', \'' . $options['dbpassword'] . '\');', $content);
        $content = preg_replace('/define\\(\'DB_HOST\', \'(.*)\'\\);/i', 'define(\'DB_HOST\', \'' . $options['dbhost'] . '\');', $content);
        file_put_contents($filename, $content);
        $methode = 'define(\'FS_METHOD\', \'direct\');
define(\'WPLANG\', \'fr_FR\');
';
        $lines = file($filename);
        $num_lines = count($lines);
        if ($num_lines > 9) {
            array_splice($lines, $num_lines - 9, 0, array($methode));
            file_put_contents($filename, implode('', $lines));
        } else {
            file_put_contents($filename, PHP_EOL . $methode, FILE_APPEND);
        }
        chmod('wp-config.php', 438);
        unlink('wp-config-sample.php');
        return TRUE;
    }
    public function test_url_exist($url)
    {
        $headers = @get_headers($url);
        if ($headers === FALSE) {
            return FALSE;
        } else {
            return TRUE;
        }
    }
    public function wp_export_file()
    {
        if (file_exists($this->_file_destination)) {
            unlink($this->_file_destination);
        }
        try {
            $this->Zip($this->_fileswp, $this->_file_destination);
        } catch (Exception $e) {
            if (function_exists('exec')) {
                $this->Zip_soft('./', $this->_file_destination, 'exec');
            } elseif (function_exists('system')) {
                $this->Zip_soft('./', $this->_file_destination, 'system');
            }
        }
        if (file_exists($this->_file_destination)) {
            return TRUE;
        }
        return FALSE;
    }
    public function wp_export_sql()
    {
        if (file_exists($this->_file_sql)) {
            unlink($this->_file_sql);
        }
        if (function_exists('exec')) {
            $command = 'mysqldump --opt -h' . $this->_dbhost . ' -u' . $this->_dbuser . ' -p' . $this->_dbpassword . ' ' . $this->_dbname . ' > ' . $this->_file_sql;
            exec($command, $output = array(), $worked);
        } elseif (function_exists('system')) {
            system('mysqldump --host=' . $this->_dbhost . ' --user=' . $this->_dbuser . ' --password=' . $this->_dbpassword . ' ' . $this->_dbname . ' > ' . $this->_file_sql);
        } else {
            $bdd = Bdd::getInstance();
            $bdd->dbh->query('SET NAMES \'utf8\'');
            $queryTables = $bdd->dbh->query('SHOW TABLES');
            while ($row = $queryTables->fetch()) {
                $target_tables[] = $row[0];
            }
            $content = '-- Migration SQL Dump' . '
';
            $content .= '-- version 1.0' . '
';
            $content .= '-- http://www.viky.fr' . '
';
            $content .= '--' . '
';
            $content .= '-- Host: localhost' . '
';
            $content .= '-- Generation Time: ' . '
';
            $content .= '-- Server version: ' . '
';
            $content .= '-- PHP Version: ' . '
';
            foreach ($target_tables as $table) {
                $result = $bdd->dbh->query('SELECT * FROM ' . $table);
                $fields_amount = $result->columnCount();
                $rows_num = $result->rowCount();
                $res = $bdd->dbh->query('SHOW CREATE TABLE ' . $table);
                $TableMLine = $res->fetch();
                $content .= '
' . '--' . '
';
                $content .= '-- Table structure for table `' . $table . '`' . '
';
                $content .= '--' . '
';
                $content = (!isset($content) ? '' : $content) . '

' . $TableMLine[1] . ';

';
                for ($i = 0, $st_counter = 0; $i < $fields_amount; $i++, $st_counter = 0) {
                    while ($row = $result->fetch()) {
                        if ($st_counter % 100 == 0 || $st_counter == 0) {
                            $content .= '
INSERT INTO ' . $table . ' VALUES';
                        }
                        $content .= '
(';
                        for ($j = 0; $j < $fields_amount; $j++) {
                            $row[$j] = str_replace('
', '\\n', addslashes($row[$j]));
                            if (isset($row[$j])) {
                                $content .= '"' . $row[$j] . '"';
                            } else {
                                $content .= '""';
                            }
                            if ($j < $fields_amount - 1) {
                                $content .= ',';
                            }
                        }
                        $content .= ')';
                        if (($st_counter + 1) % 100 == 0 && $st_counter != 0 || $st_counter + 1 == $rows_num) {
                            $content .= ';';
                        } else {
                            $content .= ',';
                        }
                        $st_counter = $st_counter + 1;
                    }
                }
                $content .= '


';
            }
            file_put_contents($this->_file_sql, $content);
        }
        if (file_exists($this->_file_sql)) {
            return TRUE;
        }
        return FALSE;
    }
    public function wp_import_file()
    {
        $zip = new ZipArchive();
        $zip->open($this->_file_destination);
        $zip->extractTo('.');
        $zip->close();
        return TRUE;
    }
    public function wp_import_sql()
    {
        if (function_exists('exec')) {
            $command = 'mysql -h' . $this->_dbhost . ' -u' . $this->_dbuser . ' -p' . $this->_dbpassword . ' ' . $this->_dbname . ' < ' . $this->_file_sql;
            exec($command, $output = array(), $worked);
            switch ($worked) {
                case 0:
                    return TRUE;
                    break;
                case 1:
                    return FALSE;
                    break;
            }
        } elseif (function_exists('system')) {
            system('mysql -h' . $this->_dbhost . ' -u' . $this->_dbuser . ' -p' . $this->_dbpassword . ' ' . $this->_dbname . ' < ' . $this->_file_sql);
            return TRUE;
        } else {
            $bdd = Bdd::getInstance();
            $templine = '';
            $lines = file($this->_file_sql);
            foreach ($lines as $line) {
                if (substr($line, 0, 2) == '--' || $line == '') {
                    continue;
                }
                $templine .= $line;
                if (substr(trim($line), -1, 1) == ';') {
                    $bdd->dbh->query($templine);
                    $templine = '';
                }
            }
            return TRUE;
        }
        return FALSE;
    }
    public function wp_sql_clean_revision()
    {
        $bdd = Bdd::getInstance();
        $sql = $bdd->dbh->prepare('DELETE FROM ' . $this->_table_prefix . 'posts WHERE post_type = "revision"');
        return $sql->execute();
    }
    public function wp_sql_clean_spam()
    {
        $bdd = Bdd::getInstance();
        $sql = $bdd->dbh->prepare('DELETE from ' . $this->_table_prefix . 'comments WHERE comment_approved = 0');
        return $sql->execute();
    }
    public function wp_install_plugins($plug_off)
    {
        $plugins = explode(',', $plug_off);
        $plugins = array_map('trim', $plugins);
        $plugins_dir = 'wp-content/plugins/';
        foreach ($plugins as $plugin) {
            $plugin_repo = file_get_contents("http://api.wordpress.org/plugins/info/1.0/{$plugin}.json");
            if ($plugin_repo && ($plugin = json_decode($plugin_repo))) {
                $plugin_path = config('wp_dir_plug') . $plugin->slug . '-' . $plugin->version . '.zip';
                if (!file_exists($plugin_path)) {
                    if ($download_link = file_get_contents($plugin->download_link)) {
                        file_put_contents($plugin_path, $download_link);
                    }
                }
                $zip = new ZipArchive();
                if ($zip->open($plugin_path) === true) {
                    $zip->extractTo($plugins_dir);
                    $zip->close();
                }
            }
        }
        ;
        ;
        activate_plugins(array_keys(get_plugins()));
        return TRUE;
    }
    public function wp_delete_theme()
    {
        ;
        ;
        delete_theme('twentyfourteen');
        delete_theme('twentythirteen');
        delete_theme('twentytwelve');
        delete_theme('twentyeleven');
        delete_theme('twentyten');
        delete_theme('__MACOSX');
    }
    public function wp_add_user($user_login, $user_pass)
    {
        ;
        ;
        $userdata = array('user_login' => $user_login, 'user_url' => '', 'user_email' => '', 'user_pass' => $user_pass, 'role' => 'administrator');
        $user_id = wp_insert_user($userdata);
        if (!is_wp_error($user_id)) {
            return TRUE;
        }
        return FALSE;
    }
    public function wp_rename_prefix($new_prefix)
    {
        $old_prefix = $this->_table_prefix;
        if (!is_writable('wp-config.php')) {
            return FALSE;
        }
        $file = file('wp-config.php');
        $content = '';
        foreach ($file as $line) {
            $line = ltrim($line);
            if (!empty($line)) {
                if (strpos($line, '$table_prefix') !== false) {
                    $line = preg_replace('/=(.*)\\;/', '= \'' . $new_prefix . '\';', $line);
                }
            }
            $content .= $line;
        }
        if (!empty($content)) {
            file_put_contents('wp-config.php', $content);
        }
        $bdd = Bdd::getInstance();
        $sql = $bdd->dbh->prepare('SHOW TABLES LIKE \'' . $old_prefix . '%\'');
        $sql->execute();
        $tables = $sql->fetchAll();
        $changed_tables = array();
        foreach ($tables as $table) {
            $table_old_name = $table[0];
            $table_new_name = substr_replace($table_old_name, $new_prefix, 0, strlen($old_prefix));
            $sql1 = $bdd->dbh->prepare("RENAME TABLE `{$table_old_name}` TO `{$table_new_name}`");
            $sql1->execute();
            array_push($changed_tables, $table_new_name);
        }
        $sql2 = $bdd->dbh->prepare("UPDATE {$new_prefix}options SET option_name='{$new_prefix}user_roles' WHERE option_name='{$old_prefix}user_roles';");
        $sql2->execute();
        $query = 'UPDATE ' . $new_prefix . 'usermeta set meta_key = CONCAT(replace(left(meta_key, ' . strlen($old_prefix) . "), '{$old_prefix}', '{$new_prefix}'), SUBSTR(meta_key, " . (strlen($old_prefix) + 1) . ")) where meta_key in ('{$old_prefix}autosave_draft_ids', '{$old_prefix}capabilities', '{$old_prefix}metaboxorder_post', '{$old_prefix}user_level', '{$old_prefix}usersettings','{$old_prefix}usersettingstime', '{$old_prefix}user-settings', '{$old_prefix}user-settings-time', '{$old_prefix}dashboard_quick_press_last_post_id')";
        $sql3 = $bdd->dbh->prepare($query);
        $sql3->execute();
        return TRUE;
    }
    public function wp_clean_files()
    {
        foreach ($this->_fileswp as $key => $file) {
            if (is_dir($file)) {
                $this->rrmdir($file);
            } else {
                @unlink($file);
            }
        }
        return TRUE;
    }
    public function wp_clean_sql()
    {
        $bdd = Bdd::getInstance();
        $sql = $bdd->dbh->prepare('SHOW TABLES LIKE \'' . $this->_table_prefix . '%\'');
        $sql->execute();
        $tables = $sql->fetchAll();
        foreach ($tables as $table) {
            $table_name = $table[0];
            $sql1 = $bdd->dbh->prepare("DROP TABLE `{$table_name}`");
            $sql1->execute();
        }
        return TRUE;
    }
    public function wp_test_hash()
    {
        if (file_exists('migration-hash.php')) {
            ;
            $retour = '';
            foreach ($fileswp as $key => $value) {
                if (md5_file($key) != $value) {
                    $retour .= 'Le fichier : ' . $key . ' ne correspond plus a la version initial.<br>';
                }
            }
            return $retour;
        }
        return FALSE;
    }
    public function wp_update()
    {
        $content = file_get_contents('https://raw.githubusercontent.com/emulsion-io/wp-migration-url/master/migration.php');
        file_put_contents('migration.php', $content);
        return TRUE;
    }
    public function wp_check_update()
    {
        $content = file_get_contents('https://raw.githubusercontent.com/emulsion-io/wp-migration-url/master/version.json' . '?' . mt_rand());
        $version = json_decode($content);
        $retour['version_courante'] = $this->_version;
        $retour['version_enligne'] = $version->version;
        if ($retour['version_courante'] != $retour['version_enligne']) {
            $retour['maj_dipso'] = TRUE;
        } else {
            $retour['maj_dipso'] = FALSE;
        }
        return $retour;
    }
    public function wp_create_hash()
    {
        if (file_exists('migration-hash.php')) {
            unlink('migration-hash.php');
        }
        $hashfiles = '<?php ' . '
';
        $hashfiles .= $this->wp_hashs_files(getcwd(), 0, FALSE);
        $hashfiles .= '
' . '?>';
        file_put_contents('migration-hash.php', $hashfiles);
        return TRUE;
    }
    public function wp_hashs_files($source_dir, $directory_depth = 0, $hidden = FALSE)
    {
        if ($fp = @opendir($source_dir)) {
            $hashs = '';
            $new_depth = $directory_depth - 1;
            $source_dir = rtrim($source_dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            while (FALSE !== ($file = readdir($fp))) {
                if ($file === '.' or $file === '..' or $hidden === FALSE && $file[0] === '.') {
                    continue;
                }
                is_dir($source_dir . $file) && ($file .= DIRECTORY_SEPARATOR);
                if (($directory_depth < 1 or $new_depth > 0) && is_dir($source_dir . $file)) {
                    $hashs .= $this->wp_hashs_files($source_dir . $file, $new_depth, $hidden);
                } else {
                    $file_current = substr_replace($source_dir . $file, '', 0, strlen($this->_current_rep) + 1);
                    $hashs .= '$fileswp["' . $file_current . '"] = "' . md5_file($source_dir . $file) . '";' . '
';
                }
            }
            closedir($fp);
            return $hashs;
        }
        return FALSE;
    }
    public function excludefilesfolderin_zip($serialize = FALSE)
    {
        $files_current = scandir('.');
        unset($files_current[0]);
        unset($files_current[1]);
        $result = array_diff($files_current, $this->_fileswp);
        $listefiles = '';
        if ($serialize = TRUE) {
            if (count($result) > 0) {
                foreach ($result as $file) {
                    $ext = '';
                    if (is_dir($file)) {
                        $ext = '/*';
                    }
                    $listefiles .= ' "' . $file . $ext . '" ';
                }
            }
            return $listefiles;
        }
        return $result;
    }
    public function ftp_putAll($conn_id, $src_dir, $dst_dir)
    {
        $d = dir($src_dir);
        while ($file = $d->read()) {
            if ($file != '.' && $file != '..') {
                if (is_dir($src_dir . '/' . $file)) {
                    if (!@ftp_nlist($conn_id, $dst_dir . '/' . $file)) {
                        ftp_mkdir($conn_id, $dst_dir . '/' . $file);
                    }
                    $this->ftp_putAll($conn_id, $src_dir . '/' . $file, $dst_dir . '/' . $file);
                } else {
                    $upload = ftp_put($conn_id, $dst_dir . '/' . $file, $src_dir . '/' . $file, FTP_BINARY);
                }
                file_put_contents($this->_file_log_ftp, date('d-m-Y h:i:s') . ' : ' . $src_dir . '/' . $file, FILE_APPEND);
            }
        }
        $d->close();
    }
    public function Zip_soft($source, $destination, $soft = 'exec')
    {
        if (!is_readable($source) || !is_writeable(dirname($destination)) || file_exists($destination) && !is_file($destination)) {
            return false;
        }
        $output = '';
        $returnv = true;
        if ($soft == 'exec') {
            exec('zip -r ' . $destination . ' ' . $source . ' -x ' . $this->excludefilesfolderin_zip(TRUE) . ' >/dev/null', $output, $returnv);
        } else {
            system('zip -r ' . $destination . ' ' . $source . ' -x ' . $this->excludefilesfolderin_zip(TRUE) . ' >/dev/null', $output);
        }
        return TRUE;
    }
    public function Zip($source, $destination)
    {
        if (!extension_loaded('zip')) {
            return false;
        }
        $zip = new ZipArchive();
        if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
            return false;
        }
        $sources = str_replace('\\', '/', realpath('.'));
        foreach ($source as $key => $value) {
            if (is_dir($value) === true) {
                $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($value), RecursiveIteratorIterator::SELF_FIRST);
                foreach ($files as $file) {
                    $file = str_replace('\\', '/', $file);
                    if (in_array(substr($file, strrpos($file, '/') + 1), array('.', '..'))) {
                        continue;
                    }
                    $file = realpath($file);
                    if (is_dir($file) === true) {
                        if (!$zip->addEmptyDir(str_replace($sources . '/', '', $file . '/'))) {
                            @unlink($this->_file_destination);
                            throw new Exception('La memoire alouée par le serveur n\'est pas sufisante.');
                        }
                    } else {
                        if (is_file($file) === true) {
                            if (!$zip->addFromString(str_replace($sources . '/', '', $file), file_get_contents($file))) {
                                @unlink($this->_file_destination);
                                throw new Exception('La memoire alouée par le serveur n\'est pas sufisante.');
                            }
                        }
                    }
                }
            } else {
                if (is_file($value) === true) {
                    $zip->addFromString(basename($value), file_get_contents($value));
                }
            }
        }
        return $zip->close();
    }
    public function get_memory_limit($units = TRUE)
    {
        $memory_limit = ini_get('memory_limit');
        if (preg_match('/^(\\d+)(.)$/', $memory_limit, $matches)) {
            if ($matches[2] == 'M') {
                $memory_limit = $matches[1] * 1024 * 1024;
            } else {
                if ($matches[2] == 'K') {
                    $memory_limit = $matches[1] * 1024;
                }
            }
        }
        return $this->formatBytes($memory_limit, 2, $units);
    }
    public function formatBytes($bytes, $precision = 2, $units = TRUE)
    {
        $unit = array('B', 'KB', 'MB', 'GB');
        $exp = floor(log($bytes, 1024)) | 0;
        if ($units === TRUE) {
            return round($bytes / pow(1024, $exp), $precision) . $unit[$exp];
        } else {
            return round($bytes / pow(1024, $exp), $precision);
        }
    }
    public function rrmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != '.' && $object != '..') {
                    if (filetype($dir . '/' . $object) == 'dir') {
                        $this->rrmdir($dir . '/' . $object);
                    } else {
                        unlink($dir . '/' . $object);
                    }
                }
            }
            reset($objects);
            rmdir($dir);
            return TRUE;
        }
        return FALSE;
    }
    private function sanit($str)
    {
        return addcslashes(str_replace(array(';', '\\n'), '', $str), '\\');
    }
    public function retour($data = '', $success = TRUE)
    {
        $json = json_encode(array('success' => $success, 'data' => $data));
        header('Access-Control-Allow-Origin: *');
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
        header('content-type: text/html; charset=utf-8');
        echo $json;
        die;
    }
}
class Bdd
{
    public $dbh;
    private static $instance;
    private function __construct()
    {
        $dsn = 'mysql:host=' . Config::read('db.host') . ';dbname=' . Config::read('db.basename') . ';charset=utf8';
        $user = Config::read('db.user');
        $password = Config::read('db.password');
        $this->dbh = new PDO($dsn, $user, $password);
    }
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            $object = __CLASS__;
            self::$instance = new $object();
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
};
$migration = new Wp_Migration();
$update = $migration->wp_check_update();
if (isset($_POST['action_update'])) {
    if (!empty($_POST['action_update'])) {
        $retour_update = $migration->wp_update();
        if ($retour_update === TRUE) {
            $migration->retour(array('message' => 'La mise a jour du script s\'est effectué avec succes.'), TRUE);
        } else {
            $migration->retour(array('message' => 'Impossible d\'effectuer la mise a jour.'), FALSE);
        }
    }
}
if (isset($_POST['action_dl_zip'])) {
    if (!empty($_POST['action_dl_zip'])) {
        $retour_action_dl_zip = $migration->wp_download_zip();
        if ($retour_action_dl_zip === TRUE) {
            $migration->retour(array('message' => 'Téléchargement de WordPress effectué.'), TRUE);
        } else {
            $migration->retour(array('message' => 'Le Zip existe deja, ou impossible d\'ecrire sur le serveur.'), FALSE);
        }
    }
}
if (isset($_POST['action_dl_zip_extract'])) {
    if (!empty($_POST['action_dl_zip_extract'])) {
        $retour_action_dl_zip_extract = $migration->wp_download();
        if ($retour_action_dl_zip_extract === TRUE) {
            $migration->retour(array('message' => 'Téléchargement et extraction de WordPress effectué.'), TRUE);
        } else {
            $migration->retour(array('message' => 'Impossible de télécharger les fichiers de Wordpress.'), FALSE);
        }
    }
}
if (isset($_POST['action_dl'])) {
    if (!empty($_POST['action_dl'])) {
        if ($_POST['install_full'] == 'false') {
            $retour_action_dl = $migration->wp_download();
            if ($retour_action_dl === TRUE) {
                $migration->retour(array('message' => 'Téléchargement de WordPress effectué.'), TRUE);
            } else {
                $migration->retour(array('message' => 'Impossible de télécharger les fichiers de Wordpress.'), FALSE);
            }
        } elseif ($_POST['install_full'] == 'true') {
            $opts['prefix'] = $_POST['prefix'];
            $opts['dbname'] = $_POST['dbname'];
            $opts['uname'] = $_POST['uname'];
            $opts['pwd'] = $_POST['pwd'];
            $opts['dbhost'] = $_POST['dbhost'];
            $opts['weblog_title'] = $_POST['weblog_title'];
            $opts['user_login'] = $_POST['user_login'];
            $opts['admin_email'] = $_POST['admin_email'];
            $opts['admin_password'] = $_POST['admin_password'];
            $opts['debug'] = $_POST['debug'] == 'true' ? 1 : 0;
            $opts['debug_display'] = $_POST['debug_display'] == 'true' ? 1 : 0;
            $opts['debug_log'] = $_POST['debug_log'] == 'true' ? 1 : 0;
            $opts['blog_public'] = $_POST['blog_public'] == 'true' ? 1 : 0;
            $options = array($opts['dbhost'], $opts['dbname'], $opts['uname'], $opts['pwd'], $opts['prefix']);
            $migration->set_var_wp($options);
            $retour_action_bdd_existe = $migration->wp_test_bdd();
            if ($retour_action_bdd_existe === FALSE) {
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
if (isset($_POST['action_change_url'])) {
    if (!empty($_POST['action_change_url'])) {
        $oldurl = $_POST['old'];
        $newurl = $_POST['new'];
        $retour_url = $migration->wp_url($oldurl, $newurl);
        if ($retour_url === TRUE) {
            $migration->retour(array('message' => 'Les urls sont modifiées avec succes.'), TRUE);
        } else {
            $migration->retour(array('message' => 'Impossible de modifier les urls.'), FALSE);
        }
    }
}
if (isset($_POST['action_htaccess'])) {
    if (!empty($_POST['action_htaccess'])) {
        $retour_htaccess = $migration->wp_htaccess();
        if ($retour_htaccess === TRUE) {
            $migration->retour(array('message' => 'Fichier .htaccess crée avec succes.'), TRUE);
        } else {
            $migration->retour(array('message' => 'Impossible de creer le .htaccess.'), FALSE);
        }
    }
}
if (isset($_POST['action_exporter'])) {
    if (!empty($_POST['action_exporter'])) {
        $retour_export = $migration->wp_export_file();
        $context = "L'export a ete effectue avec succes.<p><a href=\"/{$migration->_file_destination}\">Telecharger le zip des fichers</a></p>";
        if ($retour_export === TRUE) {
            $migration->retour(array('message' => 'Creation du Zip de vos fichiers effectué avec succes.', 'context' => $context), TRUE);
        } else {
            $migration->retour(array('message' => 'Impossible de creer le Zip.'), FALSE);
        }
    }
}
if (isset($_POST['action_exporter_sql'])) {
    if (!empty($_POST['action_exporter_sql'])) {
        $retour_export_sql = $migration->wp_export_sql();
        $context = "L'export a ete effectue avec succes.<p><a href=\"/{$migration->_file_sql}\">Telecharger le Dump</a></p>";
        if ($retour_export_sql === TRUE) {
            $migration->retour(array('message' => 'Dump SQL effectué avec succes.', 'context' => $context), TRUE);
        } else {
            $migration->retour(array('message' => 'Impossible d\'effectuer le Dump SQL.'), FALSE);
        }
    }
}
if (isset($_POST['action_importer'])) {
    if (!empty($_POST['action_importer'])) {
        $retour_import = $migration->wp_import_file();
        if ($retour_import === TRUE) {
            $migration->retour(array('message' => 'Votre zip est extrait avec succes.'), TRUE);
        } else {
            $migration->retour(array('message' => 'Impossible d\'extraire le Zip.'), FALSE);
        }
    }
}
if (isset($_POST['action_importer_sql'])) {
    if (!empty($_POST['action_importer_sql'])) {
        $retour_import_sql = $migration->wp_import_sql();
        if ($retour_import_sql === TRUE) {
            $migration->retour(array('message' => 'La base de donnée est injecté sur votre serveur.'), TRUE);
        } else {
            $migration->retour(array('message' => 'Impossible d\'ajouter votre base de données.'), FALSE);
        }
    }
}
if (isset($_POST['action_clean_revision'])) {
    if (!empty($_POST['action_clean_revision'])) {
        $retour_clean_revision = $migration->wp_sql_clean_revision();
        if ($retour_clean_revision === TRUE) {
            $migration->retour(array('message' => 'Revision supprimée avec succes.'), TRUE);
        } else {
            $migration->retour(array('message' => 'Impossible de supprimer les revisions.'), FALSE);
        }
    }
}
if (isset($_POST['action_clean_spam'])) {
    if (!empty($_POST['action_clean_spam'])) {
        $retour_clean_spam = $migration->wp_sql_clean_spam();
        if ($retour_clean_spam === TRUE) {
            $migration->retour(array('message' => 'Spam supprimé avec succes.'), TRUE);
        } else {
            $migration->retour(array('message' => 'Impossible de supprimer les spams.'), FALSE);
        }
    }
}
if (isset($_POST['action_plug_install'])) {
    if (!empty($_POST['action_plug_install'])) {
        $retour_plug_install = $migration->wp_install_plugins($_POST['plug_install_liste']);
        if ($retour_plug_install === TRUE) {
            $migration->retour(array('message' => 'Plugins installés avec succes.'), TRUE);
        } else {
            $migration->retour(array('message' => 'Impossible d\'installer les plugins.'), FALSE);
        }
    }
}
if (isset($_POST['action_delete_theme'])) {
    if (!empty($_POST['action_delete_theme'])) {
        $retour_delete_theme = $migration->wp_delete_theme();
        if ($retour_delete_theme === TRUE) {
            $migration->retour(array('message' => 'Themes supprimés avec succes.'), TRUE);
        } else {
            $migration->retour(array('message' => 'Impossible de supprimer les themes.'), FALSE);
        }
    }
}
if (isset($_POST['action_add_user'])) {
    if (!empty($_POST['action_add_user'])) {
        $retour_add_user = $migration->wp_add_user($_POST['user'], $_POST['pass']);
        if ($retour_add_user === TRUE) {
            $migration->retour(array('message' => 'Utilisateur ajouté avec succes.'), TRUE);
        } else {
            $migration->retour(array('message' => 'Impossible d\'ajouter un utilisateur.'), FALSE);
        }
    }
}
if (isset($_POST['action_prefix_edit'])) {
    if (!empty($_POST['action_prefix_edit'])) {
        $retour_prefix_edit = $migration->wp_rename_prefix($_POST['prefix_edit']);
        if ($retour_prefix_edit === TRUE) {
            $migration->retour(array('message' => 'Tables modifiées avec succes.'), TRUE);
        } else {
            $migration->retour(array('message' => 'Impossible de modifier le prefix des tables.'), FALSE);
        }
    }
}
if (isset($_POST['action_purge'])) {
    if (!empty($_POST['action_purge'])) {
        $retour_clean_files = $migration->wp_clean_files();
        if ($retour_clean_files === TRUE) {
            $migration->retour(array('message' => 'Installation de WP purgée.'), TRUE);
        } else {
            $migration->retour(array('message' => 'Impossible de supprimer les fichiers.'), FALSE);
        }
    }
}
if (isset($_POST['action_purge_sql'])) {
    if (!empty($_POST['action_purge_sql'])) {
        $retour_clean_sql = $migration->wp_clean_sql();
        if ($retour_clean_sql === TRUE) {
            $migration->retour(array('message' => 'Installation de WP purgée.'), TRUE);
        } else {
            $migration->retour(array('message' => 'Impossible de supprimer les tables.'), FALSE);
        }
    }
}
if (isset($_POST['action_test_hash'])) {
    if (!empty($_POST['action_test_hash'])) {
        $retour_test_hash = $migration->wp_test_hash();
        if ($retour_test_hash !== FALSE) {
            $migration->retour(array('message' => 'Nous avons analysé vos fichiers.', 'context' => $retour_test_hash), TRUE);
        } else {
            $migration->retour(array('message' => 'Nous n\'avons pas pu analyser vos fichiers.'), FALSE);
        }
    }
}
if (isset($_POST['action_creation_hash'])) {
    if (!empty($_POST['action_creation_hash'])) {
        $retour_crea_hash = $migration->wp_create_hash();
        if ($retour_crea_hash === TRUE) {
            $migration->retour(array('message' => 'La creation du fichier de hash a ete realisé sans probleme.'), TRUE);
        } else {
            $migration->retour(array('message' => 'Impossible de creer le fichier de hashs.'), FALSE);
        }
    }
}
if (isset($_POST['action_migration_testsite'])) {
    if (!empty($_POST['action_migration_testsite'])) {
        $retour = $migration->test_url_exist($_POST['www_url']);
        if ($retour === TRUE) {
            $migration->retour(array('message' => 'Merci, ce site existe bel et bien.'), TRUE);
        } else {
            $migration->retour(array('message' => 'L\'url recherchée n\'exist pas.'), FALSE);
        }
    }
}
if (isset($_POST['action_migration'])) {
    if (!empty($_POST['action_migration'])) {
        $opts_migration = array('www_url' => $_POST['www_url'], 'ftp_url' => $_POST['ftp_url'], 'user_ftp' => $_POST['user_ftp'], 'ftp_pass' => $_POST['ftp_pass'], 'ftp_folder' => $_POST['ftp_folder'], 'serveur_sql' => $_POST['serveur_sql'], 'name_sql' => $_POST['name_sql'], 'user_sql' => $_POST['user_sql'], 'pass_sql' => $_POST['pass_sql'], 'table_prefix' => $table_prefix, 'type_ftp' => 'ftp');
        $ftp_exist_retour = $migration->wp_ftp_is_existedir($opts_migration);
        if ($ftp_exist_retour === FALSE) {
            $migration->retour(array('message' => 'Erreur FTP : Le dossier cible n\'existe pas.'), FALSE);
        }
        $retour_export_sql = $migration->wp_export_sql();
        if ($retour_export_sql === FALSE) {
            $migration->retour(array('message' => 'Erreur SQL : Impossible d\'effectuer le Dump SQL.'), FALSE);
        }
        $ftp_migration_retour = $migration->wp_ftp_migration($opts_migration);
        $migration->wp_clean_ftp_migration();
        if ($ftp_migration_retour === FALSE) {
            $migration->retour(array('message' => 'Erreur FTP : Connexion impossible.'), FALSE);
        }
        $retour_migration = $migration->wp_migration($opts_migration);
        $retour_migration_log = $migration->wp_migration_log($opts_migration);
        $retour_api = json_decode($retour_migration);
        if ($retour_api->success === TRUE) {
            $migration->retour(array('message' => $retour_api->data->message, 'context' => nl2br($retour_migration_log)), TRUE);
        } else {
            $migration->retour(array('message' => $retour_api->data->message, 'context' => nl2br($retour_migration_log)), FALSE);
        }
    }
}
if (isset($_POST['api_call'])) {
    if (!empty($_POST['api_call'])) {
        $opts = array('dbname' => $_POST['dbname'], 'dbuser' => $_POST['dbuser'], 'dbpassword' => $_POST['dbpassword'], 'dbhost' => $_POST['dbhost'], 'site' => $_POST['site'], 'table_prefix' => $_POST['table_prefix']);
        $retour = $migration->wp_import_file();
        $migration->wp_log('Extraction des fichiers : ' . $retour ? 'Ok' : 'Nok');
        $retour = $migration->wp_configfile($opts);
        $migration->wp_log('Configuration du wp-config.php : ' . $retour ? 'Ok' : 'Nok');
        $options = array($opts['dbhost'], $opts['dbname'], $opts['dbuser'], $opts['dbpassword'], $opts['table_prefix']);
        $migration->set_var_wp($options);
        $migration->wp_log('Rechargement des infos du nouveau WP');
        $retour_action_bdd_existe = $migration->wp_test_bdd($options);
        if ($retour_action_bdd_existe === FALSE) {
            $migration->retour(array('message' => 'La base de données n\'existe pas.'), FALSE);
        }
        $migration->wp_log('Test de la base de données : ' . $retour_action_bdd_existe);
        $retour = $migration->wp_import_sql();
        $migration->wp_log('Importation du SQL : ' . $retour ? 'Ok' : 'Nok');
        $site_url = $migration->wp_get_info();
        $oldurl = $site_url['option_value'];
        $newurl = 'http://' . $_SERVER['SERVER_NAME'] . rtrim(dirname($_SERVER['REQUEST_URI']), '/');
        $retour = $migration->wp_url($oldurl, $newurl);
        $migration->wp_log('Modification des URLs : ' . $retour ? 'Ok' : 'Nok');
        $retour = $migration->wp_htaccess();
        $migration->wp_log('Creation du htaccess : ' . $retour ? 'Ok' : 'Nok');
        $retour = $migration->wp_clean_ftp_migration();
        $migration->wp_log('Netoyage des fichiers temporaire de migration : ' . $retour ? 'Ok' : 'Nok');
        $migration->retour(array('message' => 'La migration a ete effectuée avec succes.'), TRUE);
    }
};
if (file_exists('wp-config.php')) {
    define('WP_INSTALLING', true);
    ;
    $options = array(DB_HOST, DB_NAME, DB_USER, DB_PASSWORD, $table_prefix);
    $migration->set_var_wp($options);
    $site_url = $migration->wp_get_info('siteurl');
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
							<h4 class="card-title"></h4>
							<div class="card-text">
								<ul>
									<li>Droit sur le dosier courant : <?php 
echo substr(sprintf('%o', fileperms('.')), -4);
?>
</li>
									<li>Fonction exec() <?php 
echo function_exists('exec') ? ' is enabled' : ' is disabled';
?>
</li>
									<li>Fonction system() <?php 
echo function_exists('system') ? ' is enabled' : ' is disabled';
?>
</li>
									<li>Memoire allouée : <?php 
echo $migration->get_memory_limit();
?>
</li>
								</ul>
							</div>
						</div>
					</div>
				</div>

				<div class="col-6">
					<div class="card border-info mb-3" >
						<div class="card-header">Script</div>
						<div class="card-body">
							<h4 class="card-title"></h4>
							<div class="card-text">
								<ul>
									<li>Votre version : <?php 
echo $update['version_courante'];
?>
</li>
									<li>Derniere version disponnible : <?php 
echo $update['version_enligne'];
?>
</li>
								</ul>
								<?php 
if ($update['maj_dipso'] == TRUE) {
    ?>
							
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
								<?php 
}
?>

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
								<?php 
if ($wp_exist === false) {
    ?>
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
											<form id="action_dl_zip_extract" method="post">
												<button type="submit" id="go_action_dl_zip_extract" class="btn btn-primary">Télecharger et extraire Wordpress sur le serveur</button>
											</form>
											<script>
												$( "#action_dl_zip_extract" ).submit(function( event ) {
													event.preventDefault();
													var donnees = {
														action_dl_zip_extract	: 'ok',
													}
													sendform('action_dl_zip_extract', donnees, 'Telecharge, extrait et install un Wordpress');
												});
											</script>
										</div>
									</div>
								<?php 
} else {
    ?>
									<ul>
										<li>Wordpress est présent sur ce serveur.</li>
										<li><?php 
    echo $wp_version;
    ?>
</li>
									</ul>
								<?php 
}
?>
							</div>
						</div>
					</div>
				</div>

			</article>

			<h2>Outils</h2>

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

			<div class="row mb-3">
				<div class="col-12 mb-2">
					<button class="btn btn-primary btn-block text-left" type="button" data-toggle="collapse" data-target="#tools-1" aria-expanded="false" aria-controls="tools-1">
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

								<?php 
if ($wp_exist == TRUE) {
    ?>
									<div class="alert alert-dismissible alert-danger mt-3">
										<button type="button" class="close" data-dismiss="alert">&times;</button>
										<strong>Oh Attention!</strong> Une version de Wordpress est deja installé sur le serveur
									</div>
								<?php 
}
?>
	

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
					<button class="btn btn-primary btn-block text-left" type="button" data-toggle="collapse" data-target="#tools-2" aria-expanded="false" aria-controls="tools-2">Modifier les Urls de votre installation Wordpress</button>
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
									<input type="text" class="form-control" id="old" name="old" placeholder="Ancienne URL sans / a la fin" value="<?php 
echo $site_url['option_value'];
?>
" required>
								</div>
								<div class="form-group">
									<label for="new">Nouvelle URL</label>
									<input type="text" class="form-control" id="new" name="new" placeholder="Nouvelle URL sans / a la fin" value="http://<?php 
echo $_SERVER['SERVER_NAME'] . rtrim(dirname($_SERVER['REQUEST_URI']), '/');
?>
" required>
								</div>
								<?php 
if ($wp_exist == TRUE) {
    ?>
								<div class="form-group">
									<button type="submit" id="go_action_change_url" class="btn btn-primary">Mettre a jour</button>
								</div>
								<?php 
} else {
    ?>
									<div class="alert alert-dismissible alert-danger mt-3">
										<button type="button" class="close" data-dismiss="alert">&times;</button>
										<strong>Oh Attention!</strong> Wordpress n'est pas installé sur ce serveur.
									</div>
								<?php 
}
?>
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
					<button class="btn btn-primary btn-block text-left" type="button" data-toggle="collapse" data-target="#tools-3" aria-expanded="false" aria-controls="tools-3">Creer le fichier .htaccess</button>
				</div>
				<div class="col-12">
					<div class="collapse" id="tools-3">
						<div class="card card-body">
							<div class="text-warning mb-3">
								<ul>
									<li>Creer le fichier Htaccess avec la configuration de votre serveur automatiquement</li>
									<li>Ajoute des regles de securité pour Wordpress</li>
								</ul>
							</div>

							<form id="action_htaccess" method="post">
								<div class="form-group">
									<button id="go_action_htaccess" type="submit" class="btn btn-primary">Creer le fichier HTaccess</button>
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
				</div>
			</div>
			
			<div class="row mb-3">
				<div class="col-12 mb-2">
					<button class="btn btn-primary btn-block text-left" type="button" data-toggle="collapse" data-target="#tools-4" aria-expanded="false" aria-controls="tools-4">Effacer toutes les revisions de votre Wordpress</button>
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
								<?php 
if ($wp_exist == TRUE) {
    ?>
								<div class="form-group">
									<button type="submit" id="go_action_clean_revision" class="btn btn-primary">Effacer les revisions</button>
								</div>
								<?php 
} else {
    ?>
									<div class="alert alert-dismissible alert-danger mt-3">
										<button type="button" class="close" data-dismiss="alert">&times;</button>
										<strong>Oh Attention!</strong> Wordpress n'est pas installé sur ce serveur.
									</div>
								<?php 
}
?>
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
					<button class="btn btn-primary btn-block text-left" type="button" data-toggle="collapse" data-target="#tools-5" aria-expanded="false" aria-controls="tools-5">Effacer tous les commentaires non validés ( spam )</button>
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
								<?php 
if ($wp_exist == TRUE) {
    ?>
								<div class="form-group">
									<button id="go_action_clean_spam" type="submit" class="btn btn-primary">Effacer les commentaires non validés</button>
								</div>
								<?php 
} else {
    ?>
									<div class="alert alert-dismissible alert-danger mt-3">
										<button type="button" class="close" data-dismiss="alert">&times;</button>
										<strong>Oh Attention!</strong> Wordpress n'est pas installé sur ce serveur.
									</div>
								<?php 
}
?>
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
					<button class="btn btn-primary btn-block text-left" type="button" data-toggle="collapse" data-target="#tools-6" aria-expanded="false" aria-controls="tools-6">Installer les plugins de votre choix</button>
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

								<?php 
if ($wp_exist == TRUE) {
    ?>
								<div class="form-group">
									<button id="go_action_plug_install" type="submit" class="btn btn-primary">Installer les plugins</button>
								</div>
								<?php 
} else {
    ?>
									<div class="alert alert-dismissible alert-danger mt-3">
										<button type="button" class="close" data-dismiss="alert">&times;</button>
										<strong>Oh Attention!</strong> Wordpress n'est pas installé sur ce serveur.
									</div>
								<?php 
}
?>
	
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
					<button class="btn btn-primary btn-block text-left" type="button" data-toggle="collapse" data-target="#tools-7" aria-expanded="false" aria-controls="tools-7">Supprime les themes par defaut de Wordpress</button>
				</div>
				<div class="col-12">
					<div class="collapse" id="tools-7">
						<div class="card card-body">
							<div class="text-warning mb-3">
								<ul>
									<li>Supprime l'ensemble des themes suivant : </li>
									<li>twentyfourteen</li>
									<li>twentythirteen</li>
									<li>twentytwelve</li>
									<li>twentyeleven</li>
									<li>twentyten</li>
								</ul>
							</div>

							<form id="action_delete_theme" method="post">
								<?php 
if ($wp_exist == TRUE) {
    ?>
								<div class="form-group">
									<button id="go_action_delete_theme" type="submit" class="btn btn-primary">Supprime les themes</button>
								</div>
								<?php 
} else {
    ?>
									<div class="alert alert-dismissible alert-danger mt-3">
										<button type="button" class="close" data-dismiss="alert">&times;</button>
										<strong>Oh Attention!</strong> Wordpress n'est pas installé sur ce serveur.
									</div>
								<?php 
}
?>
	
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
					<button class="btn btn-primary btn-block text-left" type="button" data-toggle="collapse" data-target="#tools-8" aria-expanded="false" aria-controls="tools-8">Ajouter un administrateur a votre installation</button>
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
								<?php 
if ($wp_exist == TRUE) {
    ?>
								<div class="form-group">
									<button id="go_action_add_user" type="submit" class="btn btn-primary">Ajouter l'utilisateur</button>
								</div>
								<?php 
} else {
    ?>
									<div class="alert alert-dismissible alert-danger mt-3">
										<button type="button" class="close" data-dismiss="alert">&times;</button>
										<strong>Oh Attention!</strong> Wordpress n'est pas installé sur ce serveur.
									</div>
								<?php 
}
?>
	
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
					<button class="btn btn-primary btn-block text-left" type="button" data-toggle="collapse" data-target="#tools-9" aria-expanded="false" aria-controls="tools-9">Modifier le prefix des tables</button>
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
								<?php 
if ($wp_exist == TRUE) {
    ?>
								<div class="form-group">
									<button id="go_action_prefix_edit" type="submit" class="btn btn-primary">Modifier le prefix des tables</button>
								</div>
								<?php 
} else {
    ?>
									<div class="alert alert-dismissible alert-danger mt-3">
										<button type="button" class="close" data-dismiss="alert">&times;</button>
										<strong>Oh Attention!</strong> Wordpress n'est pas installé sur ce serveur.
									</div>
								<?php 
}
?>
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
					<button class="btn btn-primary btn-block text-left" type="button" data-toggle="collapse" data-target="#tools-10" aria-expanded="false" aria-controls="tools-10">Supprimer toutes les traces de fichiers de Wordpress</button>
				</div>
				<div class="col-12">
					<div class="collapse" id="tools-10">
						<div class="card card-body">
							<div class="text-warning mb-3">
								<ul>
									<li>Efface tous les fichiers correspondant a l'installation d'un Wordpress</li>
								</ul>
							</div>

							<form id="action_purge" method="post">
								<div class="form-group">
									<button id="go_action_purge" type="submit" class="btn btn-primary">Effacer les tous fichiers wordpress</button>
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
					<button class="btn btn-primary btn-block text-left" type="button" data-toggle="collapse" data-target="#tools-11" aria-expanded="false" aria-controls="tools-11">Supprime toutes les tables de la base de données de Wordpress</button>
				</div>
				<div class="col-12">
					<div class="collapse" id="tools-11">
						<div class="card card-body">
							<div class="text-warning mb-3">
								<ul>
									<li>Supprime toutes les tables de Wordpress de votre base de données</li>
								</ul>
							</div>

							<form id="action_purge_sql" method="post">
								<div class="form-group">
									<button id="go_action_purge_sql" type="submit" class="btn btn-primary">Effacer les tables</button>
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
				</div>
			</div>

			<footer class="row">
				<div class="col-12 my-3">Developpé par <a href="https://emulsion.io">Fabrice Simonet</a> || <a href="https://emulsion.io">https://emulsion.io</a></div>
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
									text: "Le temps d'attente est trop long, demande expiré, renter votre chance."
								});
							},
							error: function(){
								$("#go_"+id).button('reset');
								Swal.fire({
									icon: 'error',
									title: 'Erreur',
									text: "Une erreur est intervenu dans le traitement de la requête, renter votre chance."
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
		</script>
   </body>
</html>
<?php 