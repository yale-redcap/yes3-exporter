<?php

define('YES3_NAMESPACE_PREFIX', "Yale\\Yes3FieldMapper\\");

/**
 * EM class/trait autoloader
 * 
 * Boosted from the PSR-4 site, modified to autoload traits as well as classes.
 * Peter Charpentier, 9/15/2021
 *
 * After registering this autoload function with SPL, any of the following lines
 * will cause the function to attempt to load the [EM namespace]\ClassOrTraitName class
 * from /path/to/project/[classes|traits]/ClassOrTraitName.php:
 *
 *      $xyz = new [EM namespace]\ClassName;
 *       - or -
 *      use [EM namespace]\ClassName;
 *       - or (inside parent class def) -
 *      use \[EM namespace]\TraitName;
 *
 * The leading backslash for the fully-qualified trait name is required.
 *
 * @param string $classOrTrait The fully-qualified class/trait name.
 * @return void
 */
spl_autoload_register(function ($classOrTrait) {

   // does the class/trait use the namespace prefix?
   $len = strlen(YES3_NAMESPACE_PREFIX);
   if (strncmp(YES3_NAMESPACE_PREFIX, $classOrTrait, $len) !== 0) {
      // no, move to the next registered autoloader
      return;
   }

   // de-namespaced class or trait name
   $relative_classOrTraitName = substr($classOrTrait, $len);

   foreach (['classes', 'traits'] as $subFolder) {

      // base directory for the namespace prefix
      $base_dir = __DIR__ . DIRECTORY_SEPARATOR . $subFolder . DIRECTORY_SEPARATOR;

      // replace the namespace prefix with the base directory, replace namespace
      // separators with directory separators in the relative class name, append
      // with .php
      $file = $base_dir . str_replace('\\', DIRECTORY_SEPARATOR, $relative_classOrTraitName) . '.php';

      // if the file exists, require it
      if (file_exists($file)) {
         require $file;
      }

   } // subFolder

});

