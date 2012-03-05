<?php
/**
 * File containing the image alias image variation image file removal cronjob part
 *
 * @copyright Copyright (C) 1999 - 2012 Brookins Consulting. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2 (or any later version)
 * @version //autogentag//
 * @package bcimagealias
 */

// General cronjob part options
$phpBin = '/usr/bin/php';
$generatorWorkerScript = 'extension/bcimagealias/bin/php/bcimagealias.php';
$options = '--remove --force';
$result = false;

passthru( "$phpBin ./$generatorWorkerScript $options;", $result );

print_r( $result );

?>
