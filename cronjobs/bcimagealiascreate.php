<?php
/**
 * File containing the bcimagealias image alias image variation image file creator cronjob part
 *
 * @copyright Copyright (C) 1999 - 2011 Brookins Consulting. All rights reserved.
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt GNU GPL v2 (or later)
 * @version //autogentag//
 * @package bcimagealias
 */

// General cronjob part options
$phpBin = '/usr/bin/php';
$generatorWorkerScript = 'extension/bcimagealias/bin/php/bcimagealias.php';
$options = '--create --force';
$result = false;

passthru( "$phpBin ./$generatorWorkerScript $options;", $result );

print_r( $result );

?>
