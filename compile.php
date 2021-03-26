<?php

error_reporting(-1);
ini_set('display_errors', '1');

require('vendor/autoload.php');
require('vendor/codeless/jugglecode/src/JuggleCode.php');

$j = new JuggleCode();

$j->masterfile   = 'migration.php';
$j->outfile      = 'dist/migration.php';
$j->mergeScripts = true;
$j->comments     = false;

var_dump($j->run());
