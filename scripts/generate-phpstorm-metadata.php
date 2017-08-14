<?php
/**
 * The extremely beautiful code below will output PHP array elements that are ready to copy-paste.
 *
 * Instructions:
 * - Run the script like this: `php generate-phpstorm-metadata.php > ~/phpstorm-metadata.txt`
 * - Copy the output into .phpstorm.meta.php, below the 'automatically generated' comment in the file
 */

$directories = [
    __DIR__.'/../code/libraries/joomlatools/library',
    __DIR__.'/../code/libraries/joomlatools/component/koowa'
];

$skip_classes = ['Koowa'];
$skip_identifiers = ['user'];

/*
 * ------
 */
$classes = [];

foreach ($directories as $directory) {
    getClassesInDirectory($directory, $classes);
}

sort($classes);

foreach ($classes as $class) {
	$class = trim($class);

	if (in_array($class, $skip_classes)) {
	    continue;
    }

	if ($class[0] === 'K') {
        $identifier = strtolower(preg_replace('/(?<=\\w)([A-Z])/', '.\\1', substr($class, 1)));

        $map[$identifier] = '\\'.$class;
        $map['lib:'.$identifier] = '\\'.$class;
	} elseif (substr($class, 0, 8) === 'ComKoowa') {
        $identifier = strtolower(preg_replace('/(?<=\\w)([A-Z])/', '.\\1', substr($class, 8)));
        $map['com:koowa.'.$identifier] = '\\'.$class;
	}
}

ksort($map);

foreach ($map as $i => $cls) {
    if (in_array($i, $skip_identifiers)) {
        continue;
    }

    echo "\t\t\t'$i' => $cls::class,\n";
}

function getClassesInDirectory($directory, &$classes)
{
    $it = new RecursiveDirectoryIterator($directory);
    $it = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::LEAVES_ONLY);
    $it = new RegexIterator($it, '(\.' . preg_quote('php') . '$)');

    $p = '#(?<!abstract )class\s+((?:K|ComKoowa)[A-Za-z0-9_\-]+)\s+(?:extends|\{)#';

    foreach ($it as $file) {
        $contents = file_get_contents($file);
        preg_match($p, $contents, $matches);
        if ($matches) {
            $classes[] = $matches[1];
        }
    }
}
