#!/usr/bin/env php
<?php
/**
 * File containing the image alias image variation image file generator / remover
 *
 * @copyright Copyright (C) 1999 - 2011 Brookins Consulting. All rights reserved.
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt GNU GPL v2 (or later)
 * @version //autogentag//
 * @package bcimagealias
 */

// Add a starting timing point tracking script execution time
$srcStartTime = microtime();

// Load existing class autoloads
require 'autoload.php';

// Disable php time limit to prevent script execution time limit errors
set_time_limit( 0 );

// Load cli and script environment
$cli = eZCLI::instance();
$script = eZScript::instance( array( 'description' =>
                                     'eZ Publish content object attribute image alias variation generator / remover. ' .
                                     'This script makes sure that content object attribute image' .
                                     ' alias variation image files are generated (before they are requested by users) or removed (for maintinence).',
                                     'extended-description' => '1. fetch ezcontentclass having an ezimage attribute, ' .
                                                               '2. fetch objects of these classes, ' .
                                                               '3. purge image alias image variation image files for all content objects',
                                     'use-session' => false,
                                     'use-modules' => false,
                                     'use-extensions' => true ) );

// Fetch default script options
$options = $script->getOptions( '[generate;][remove;][force;][dry;]',
                                '[name]', array( 'force' => 'Force generation or removal. Disable delayed startup', 'dry' => 'Display calculated execution. Make no system changes',
                                                 'generate' => 'Generate content object attribute image datatype image alias image variation image files',
                                                 'remove' => 'Remove existing content object attribute image datatype image alias image variation image files from system'  ) );

// Script parameters
$siteAccess = $options['siteaccess'] ? $options['siteaccess'] : false;
$verbose = isset( $options['verbose'] ) ? true : false;
$force = isset( $options['force'] ) ? true : false;
$dry = isset( $options['dry'] ) ? true : false;
$generate = isset( $options['generate'] ) ? true : false;
$remove = isset( $options['remove'] ) ? true : false;

if( $generate == false && $remove == false )
{
    $cli->warning( "To run this script you must pass one of the required arguments --generate or --remove to use this extension script" );
    $cli->warning( "You can run this script with --dry switch to just view which files are going to be generated or removed." );
    $cli->output();
    // Shutdown the script and exit eZ
    $script->shutdown( 1 );
}

// Alert user to current siteaccess used for process
if ( $siteAccess || ( $verbose && $siteAccess != '' ) || ( $dry && $siteAccess!= '' ) )
{
    if ( in_array( $siteAccess, eZINI::instance()->variable( 'SiteAccessSettings', 'AvailableSiteAccessList' ) ) )
    {
        $cli->output( "Using siteaccess $siteAccess for ezpgenerateimagealiases.php" );
    }
    else
    {
        $cli->notice( "Siteaccess $siteAccess does not exist, using default siteaccess" );
    }
}

// General script options
$scriptExecutionOptions = array( 'verbose' => $verbose, 'dry' => $dry, 'iterate' => true, 'force', $force, 'troubleshoot' => true, 'troubleshootLevel' => 1 );
$script->initialize();
$script->setIterationData( '.', '~' );
$isQuiet = $script->isQuiet();
$script->startup();

// Default content count storage and default values
$contentClassImageAttributesCount = 0;
$contentObjectImageAttributes = array();
$contentObjectImageAttributesCount = 0;

// Default image alias settings
$aliases = eZINI::instance( 'image.ini' )->variable( 'AliasSettings', 'AliasList' );

// Default datatypes to generate image alias variations
$imageDataTypeStrings = eZINI::instance( 'bcimagealias.ini' )->variable( 'BCImageAliasSettings', 'ImageDataTypeStringList' );

if( !is_array( $imageDataTypeStrings ) )
{
    $cli->warning( "You must first clear all ini caches to use this extension script" );
    $cli->output();
    // Shutdown the script and exit eZ
    $script->shutdown( 1 );
}

// Fetch content class image attributes
$contentClassImageAttributes = BCImageAlias::fetchContentClassImageAttributes();

// Fetch content object image attributes
$contentObjectImageAttributes = BCImageAlias::fetchImageAttributesByClassAttributes( $contentClassImageAttributes );
$contentObjectImageAttributesCount = count( $contentObjectImageAttributes );

// Estimated image alias variations
// Based on the number of image aliases in the current siteaccess
// defined in current siteaccess settings * the number of content
// object attribute datatype images (number of images used within eZ Publish Content Trees)
$imageAliasVariationCount = $contentObjectImageAttributesCount * count( $aliases ) * count( $imageDataTypeStrings );
$script->resetIteration( $imageAliasVariationCount );

// Warn user and wait for user abort
if ( !$force )
{
    if( $generate && !$remove )
    {
        if( $dry )
        {
            $headerMessage = "\nNumber of image alias variations to pretend to generate: $imageAliasVariationCount";
            $cli->output( $cli->stylize( 'header', $headerMessage ) );

        }
        else
        {
            $headerMessage = "\nNumber of image alias variations to generate: $imageAliasVariationCount";
            $cli->output( $cli->stylize( 'header', $headerMessage ) );

            $cli->warning( "This script will attempt to CREATE all content object image alias variation files registered in the database content and site settings." );
            $cli->warning( "You can run this script with --dry switch to just view which files are going to be generated." );
        }
    }
    else
    {
        if( $dry )
        {
            $headerMessage = "Number of image alias variations to pretend to remove: $imageAliasVariationCount";
            $cli->output( $cli->stylize( 'header', $headerMessage ) );
        }
        else
        {
            $headerMessage = "Number of image alias variations to remove: $imageAliasVariationCount";
            $cli->output( $cli->stylize( 'header', $headerMessage ) );

            $cli->warning( "This script will attempt to REMOVE all content object image alias variation files registered in the database content and site settings." );
            $cli->warning( "You can run this script with --dry switch to just view which files are going to be removed." );
        }
    }
    $cli->warning( "You have 10 seconds to stop the script execution before it starts (press Ctrl-C)." );

    sleep( 10 );
    $cli->output();
}

if( $generate && !$remove )
{
    // Alert the user to what is happening
    if( $dry && $generate )
    {
        $headerActionMessage = "Dry run: Pretending to generate image alias image variation image files for all content objects\n";
    }
    else
    {
        $headerActionMessage = "Generating image alias image variation image files for all content objects\n";
    }
}
else
{
    // Alert the user to what is happening
    if( $dry && $remove )
    {
        $headerActionMessage = "Dry run: Pretending to remove image alias image variation files for all content objects\n";
    }
    else
    {
        $headerActionMessage = "Removing image alias image variation files for all content objects\n";
    }
}
$cli->output( $cli->stylize( 'header', $headerActionMessage ) );


if( $generate && !$remove )
{
    // Attempt to generate image alias variations
    $result = BCImageAlias::instance( $scriptExecutionOptions )->createByAttributes( $contentObjectImageAttributes );
}
else
{
    // Attempt to remove image alias variation files
    $result = BCImageAlias::instance( $scriptExecutionOptions )->removeAllAliases( $contentObjectImageAttributes );
}

if( $generate && !$remove )
{
    // Alert the user to what has happened
    if( $dry )
    {
        $footerMessage = "\nPretended to generate " . $script->IterationIndex . " image alias variation image files. No image alias variation image files created!\n";
    }
    else
    {
        $footerMessage = "\nGenerated " . $script->IterationIndex . " image alias variation image files. Image alias variation image files created!\n";
    }
}
else
{
    // Alert the user to what has happened
    if( $script->IterationIndex == 0 && $dry == false )
    {
        $footerMessageSummary = ". No image alias variation image files deleted!\n";
    }
    else
    {
        $footerMessageSummary = ". Image alias variation image files deleted!\n";
    }
    if( $dry )
    {
        $footerMessage = "\nNumber of images alias variation files in system: " . $script->IterationIndex . $footerMessageSummary;
    }
    else
    {
        $footerMessage = "\nNumber of images alias variation files removed: " . $script->IterationIndex . $footerMessageSummary;
    }
}
$cli->output( $footerMessage );

// Add a stoping timing point tracking and calculating total script execution time
$srcStopTime = microtime();
$startTime = next( explode( " ", $srcStartTime ) ) + current( explode( " ", $srcStartTime ) );
$stopTime = next( explode( " ", $srcStopTime ) ) + current( explode( " ", $srcStopTime ) );
$executionTime = round( $stopTime - $startTime, 2 );

// Alert the user to how long the script execution took place
$cli->output( "This script execution completed in " . $executionTime . " seconds" . ".\n" );

// Shutdown the script and exit eZ
$script->shutdown();

?>
