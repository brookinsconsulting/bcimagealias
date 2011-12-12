#!/usr/bin/env php
<?php
/**
 * File containing the bcimagealias image alias image variation file remover
 *
 * @copyright Copyright (C) 1999 - 2011 Brookins Consulting. All rights reserved.
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt GNU GPL v2 (or later)
 * @version //autogentag//
 * @package bcimagealias
 */

// Load existing class autoloads
require 'autoload.php';

// Load cli and script environment
$cli = eZCLI::instance();
$script = eZScript::instance( array( 'description' =>
                                     'eZ Publish content object attribute image alias variation file remover. ' .
                                     'This script makes sure that content object attribute image' .
                                     ' alias variations are removed from the filesystem.',
                                     'extended-description' => '1. fetch ezcontentclass having an ezimage attribute, ' .
                                                               '2. fetch objects of these classes, ' .
                                                               '3. purge image alias for all version',
                                     'use-session' => false,
                                     'use-modules' => false,
                                     'use-extensions' => true ) );

// Fetch default script options
$options = $script->getOptions( '[force;][dry;][regenerate;][php-bin:][related-siteaccesses;][object-id;][classes;][attributes;][aliases;][node-id;][subtree-children;][script-verbose-level;]',
                                '[name]', array( 'force' => 'Force disables delayed startup. Example: ' . "'--force'" . ' is an optional parameter which defaults to false',
                                                 'regenerate' => 'Regenerate forces creation or removal. Recreates existing image aliases regardless if they already exist. Example: ' . "'--regenerate'" . ' is an optional parameter which defaults to false',
                                                 'dry' => 'Use only with ' . "'--remove'" . ' parameter to make no system changes. Simulate the removal of content object attribute image datatype image alias image variation image files from system. Example: ' . "'--dry'" . ' is an optional parameter which defaults to false',
                                                 'create' => 'Generate content object attribute image datatype image alias image variation image files. Either ' . "'--create' or '--remove'" . ' are required parameters',
                                                 'remove' => 'Remove existing content object attribute image datatype image alias image variation image files from system. Either ' . "'--create' or '--remove'" . ' are required parameters',
                                                 'related-siteaccesses' => 'Use only with ' . "'--create'" . ' or ' . "'--remove'" . ' parameters to fetch image alias settings definitions from all related siteaccess to current siteaccess. Example: ' . "'--related-siteaccesses=true'" . ' is an optional parameter which defaults to false',
                                                 'object-id' => 'Use only with ' . "'--create'" . ' or ' . "'--remove'" . ' parameters to perform operations on a single content object. Example: ' . "'--object-id=2'" . ' is an optional parameter which defaults to false',
                                                 'attributes' => 'Use only with ' . "'--create'" . ' or ' . "'--remove'" . ' parameters to perform operations on a single content object class attribute identifier. Example: ' . "'--attributes=image,profile,category-image'" . ' is an optional parameter which defaults to false',
                                                 'aliases' => 'Use only with ' . "'--create'" . ' or ' . "'--remove'" . ' parameters to perform operations on a single content object image attribute image alias. Example: ' . "'--aliases=large,medium,small'" . ' is an optional parameter which defaults to false',
                                                 'node-id' => 'Use only with ' . "'--create'" . ' or ' . "'--remove'" . ' parameters to perform operations on a single content tree node. Do not use with ' . "'--object-id'" . ' parameter. Example: --' . "'node-id=2'" . ' is an optional parameter which defaults to false',
                                                 'subtree-children' => 'Use only with ' . "'--node-id'" . ' parameter to perform operations on all content tree node subtree children as well. Example: ' . "'--subtree-children'" . ' is an optional parameter which defaults to false',
                                                 'classes' => 'Use only with ' . "'--create'" . ' or ' . "'--remove'" . ' parameters to perform operations on only specific content object classes. Example: ' . "'--classes=article,folder'" . ' is an optional parameter which defaults to false',
                                                 'script-verbose' => 'Use this parameter to display verbose script output without disabling script iteration counting of images created or removed. Example: ' . "'--script-verbose'" . ' is an optional parameter which defaults to false',
                                                 'script-verbose-level' => 'Use only with ' . "'--script-verbose'" . ' parameter to see more of execution internals. Example: ' . "'--script-verbose-level=2'" . ' is an optional parameter which defaults to 1'
                                               ) );

// Script parameters
$siteAccess = $options['siteaccess'] ? $options['siteaccess'] : false;
$scriptVerboseLevel = isset( $options['script-verbose-level'] ) ? $options['script-verbose-level'] : 1;
$troubleshoot = ( isset( $options['script-verbose-level'] ) && $options['script-verbose-level'] > 0 ) ? true : false;
$verbose = isset( $options['script-verbose'] ) ? true : false;
$force = isset( $options['force'] ) ? true : false;
$regenerate = isset( $options['regenerate'] ) ? true : false;
$dry = isset( $options['dry'] ) ? true : false;
$create = isset( $options['create'] ) ? true : false;
$remove = isset( $options['remove'] ) ? true : false;
$currentSiteaccess = isset( $options['related-siteaccesses'] ) ? false : true;
$objectID = isset( $options['object-id'] ) ? $options['object-id'] : false;
$attributes = isset( $options['attributes'] ) ? explode( ',', $options['attributes'] ) : false;
$imageAliases = isset( $options['aliases'] ) ? explode( ',', $options['aliases'] ) : false;
$nodeID = isset( $options['node-id'] ) ? $options['node-id'] : false;
$subtreeChildren = ( isset( $options['subtree-children'] ) && $options['subtree-children'] != 'true' ) ? $options['subtree-children'] : true;
$classes = isset( $options['classes'] ) ? explode( ',', $options['classes'] ) : false;

// Script php and script worker parameters
$phpBin = isset( $options['php-bin'] ) ? $options['php-bin'] : '/usr/bin/php';
$generatorWorkerScript = 'extension/bcimagealias/bin/php/bcimagealias.php';

// Script options
$options = ( $dry ? ' --dry ' : '' )
           . ( $force ? '--force ' : '' )
           . ( $verbose ? '--script-verbose ' : '' )
           . ( $scriptVerboseLevel ? '--script-verbose-level=' . $scriptVerboseLevel : '' )
           . ( $regenerate ? '--regenerate ' : '' )
           . ( $objectID ? '--object-id=' . $objectID : '' )
           . ( $nodeID ? '--node-id=' . $nodeID : '' )
           . ( $subtreeChildren ? '--subtree-children ' : '' )
           . ( $currentSiteaccess ? '--related-siteaccesses ' : '' )
           . ( $imageAliases ? '--aliases=' . $imageAliases : '' )
           . ( $attributes ? '--attributes=' . $attributes : '' )
           . ( $classes ? '--classes ' . $classes : '' )
           . '--remove';

// General script options
$scriptExecutionOptions = array( 'verbose' => $verbose, 'dry' => $dry );
$script->initialize();
$script->setIterationData( '.', '~' );
$isQuiet = $script->isQuiet();
$script->startup();

// Run command and capture result
$result = false;
passthru( "$phpBin ./$removalWorkerScript $options;", $result );

// Print command results to screen
print_r( $result );

// Shutdown the script and exit eZ
$script->shutdown();

?>
