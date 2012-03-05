<?php
/**
 * File containing the bcimagealias module configuration file, module.php
 *
 * @copyright Copyright (C) 1999 - 2012 Brookins Consulting. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2 (or any later version)
 * @version //autogentag//
 * @package bcimagealias
 */

// Define module name
$Module = array( 'name' => 'Creator/Remover of image alias image variation files of nodes of the content tree of eZ Publish' );

// Define module view and parameters
$ViewList = array();

// Define create module view parameters
$ViewList['create'] = array( 'functions' => array( 'create' ),
                             'script' => 'create.php',
                             'default_navigation_part' => 'bcimagealiasnavigationpart',
                             'ui_context' => 'administration',
                             'params' => array( 'NodeID', 'Children', 'Regenerate', 'CurrentSiteaccess' ),
                             'unordered_params' => array() );

// Define remove module view parameters
$ViewList['remove'] = array( 'functions' => array( 'remove' ),
                             'script' => 'remove.php',
                             'default_navigation_part' => 'bcimagealiasnavigationpart',  
                             'ui_context' => 'administration',
                             'params' => array( 'NodeID', 'Children', 'Regenerate', 'CurrentSiteaccess' ),
                             'unordered_params' => array() );

// Define function 'create' parameters
$classParameters = array( 'Class' => array( 'name'=> 'Class',
                                            'values'=> array(),
                                            'path' => 'classes/',
                                            'file' => 'ezcontentclass.php',
                                            'class' => 'eZContentClass',
                                            'function' => 'fetchList',
                                            'parameter' => array( 0, false, false, array( 'name' => 'asc' ) ) ) );
$FunctionList['create'] = $classParameters;

// Define function 'remove' parameters
$FunctionList['remove'] = $classParameters;

?>
