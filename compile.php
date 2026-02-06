<?php

// Fichier de départ qui contient les require/include
$input_file = 'src/migration.php';
// Fichier de sortie qui contiendra tout fusionné
$output_file = 'dist/migration.php';

function mergeIncludes($filePath, array &$processedFiles = [], $isBaseFile = false, $originalInclude = null) {
    $resolvedPath = realpath($filePath);
    if ($resolvedPath === false) {
        if ($originalInclude !== null) {
            return $originalInclude;
        }
        throw new Exception("Le fichier {$filePath} n'existe pas.");
    }

    // Éviter les boucles infinies en cas d'inclusions circulaires
    if (in_array($resolvedPath, $processedFiles, true)) {
        return '';
    }
    $processedFiles[] = $resolvedPath;

    $content = file_get_contents($resolvedPath);

    // Supprimer les balises PHP ouvrantes et fermantes si ce n'est pas le fichier de base
    if (!$isBaseFile) {
        $content = preg_replace('/<\?php|\?>/', '', $content);
    }

    // Regex pour trouver les include, include_once, require, require_once
    $pattern = '/\b(include|include_once|require|require_once)\s*\(?\s*[\'\"]([^\'\"]+)[\'\"]\s*\)?\s*;/';

    return preg_replace_callback($pattern, function ($matches) use (&$processedFiles, $resolvedPath) {
        $includedFilePath = dirname($resolvedPath) . DIRECTORY_SEPARATOR . $matches[2];
        return mergeIncludes($includedFilePath, $processedFiles, false, $matches[0]);
    }, $content);
}

try {
    $processedFiles = [];
    // Fusionner le contenu du fichier d'entrée
    $mergedContent = mergeIncludes($input_file, $processedFiles, true);

    // Créer le dossier de sortie s'il n'existe pas
    if (!is_dir(dirname($output_file))) {
        mkdir(dirname($output_file), 0777, true);
    }

    // Écrire le contenu fusionné dans le fichier de sortie
    file_put_contents($output_file, $mergedContent);
    echo "Fichier fusionné généré avec succès : {$output_file}\n";
} catch (Exception $e) {
    echo 'Erreur : ' . $e->getMessage() . "\n";
}

?>