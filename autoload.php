<?php
/**
 * Yale\Yes3 autoloader
 * Boosted from the PSR-4 site, modified to autoload traits as well as classes.
 * Peter Charpentier, 9/15/2021
 *
 * After registering this autoload function with SPL, the following line
 * would cause the function to attempt to load the Yale\Yes3\ClassOrTraitName class
 * from /path/to/project/[classes|traits]/ClassOrTraitName.php:
 *
 *      $xyz = new Yale\Yes3\ClassName;
 *       - or -
 *      use Yale\Yes3\ClassName;
 *       - or (inside parent class def) -
 *      use \Yale\Yes3\TraitName;
 *
 * The leading backslash for the fully-qualified trait name is required.
 *
 * @param string $classOrTrait The fully-qualified class/trait name.
 * @return void
 */
spl_autoload_register(function ($classOrTrait) {

   // project-specific namespace prefix
   $prefix = 'Yale\\Yes3FieldMapper\\';

   // does the class/trait use the namespace prefix?
   $len = strlen($prefix);
   if (strncmp($prefix, $classOrTrait, $len) !== 0) {
      // no, move to the next registered autoloader
      return;
   }

   // get the relative name
   $relative_classOrTraitName = substr($classOrTrait, $len);

   foreach (['classes', 'traits'] as $subFolder) {

      // base directory for the namespace prefix
      $base_dir = __DIR__ . '/' . $subFolder . '/';

      // replace the namespace prefix with the base directory, replace namespace
      // separators with directory separators in the relative class name, append
      // with .php
      $file = $base_dir . str_replace('\\', '/', $relative_classOrTraitName) . '.php';

      // if the file exists, require it
      if (file_exists($file)) {
         require $file;
      }

   } // subFolder

});

