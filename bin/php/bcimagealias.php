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
$options = $script->getOptions( '[force;][dry;][script-verbose;][generate;][remove;][troubleshoot-level;][related-siteaccesses;]',
                                '[name]', array( 'force' => 'Force generation or removal. Disable delayed startup. Optional. Defaults to false',
                                                 'dry' => 'Use only with --' . "'remove'" . ' parameter to make no system changes. Simulate the removal of content object attribute image datatype image alias image variation image files from system. Example: ' . "'--dry'" . ' is an optional parameter which defaults to false',
                                                 'script-verbose' => 'Use this parameter to display verbose script output without disabling script iteration counting of images generated or removed. Example: ' . "'--script-verbose'" . ' is an optional parameter which defaults to false',
                                                 'generate' => 'Generate content object attribute image datatype image alias image variation image files. Either ' . "'--generate' or '--remove'" . ' are required parameters',
                                                 'remove' => 'Remove existing content object attribute image datatype image alias image variation image files from system. Either ' . "'--generate' or '--remove'" . ' are required parameters',
                                                 'troubleshoot-level' => 'Use only with --' . "'script-verbose'" . ' parameter to see more of execution internals. Example: --' . "'troubleshoot-level'" . '=2 is an optional parameter which defaults to 1',
                                                 'related-siteaccesses' => 'Use only with --' . "'generate'" . ' parameter to fetch image alias settings definitions from all related siteaccess to current siteaccess. Example: --' . "'related-siteaccesses'" . '=true is an optional parameter which defaults to false'
                                               ) );

// Script parameters
$siteAccess = $options['siteaccess'] ? $options['siteaccess'] : false;
$troubleshootLevel = isset( $options['troubleshoot-level'] ) ? $options['troubleshoot-level'] : 1;
$troubleshoot = isset( $options['troubleshoot-level'] ) ? true : true;
$verbose = isset( $options['script-verbose'] ) ? true : false;
$force = isset( $options['force'] ) ? true : false;
$dry = isset( $options['dry'] ) ? true : false;
$generate = isset( $options['generate'] ) ? true : false;
$remove = isset( $options['remove'] ) ? true : false;
$currentSiteaccess = isset( $options['related-siteaccesses'] ) ? false : true;

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
$scriptExecutionOptions = array( 'verbose' => $verbose, 'dry' => $dry, 'iterate' => true,
                                 'force' => $force, 'troubleshoot' => $troubleshoot, 'troubleshootLevel' => $troubleshootLevel,
                                 'current-siteaccess' => $currentSiteaccess );
$script->initialize();
$script->setIterationData( '.', '~' );
$isQuiet = $script->isQuiet();
$script->startup();

// Default content count storage and default values
$contentClassImageAttributesCount = 0;
$contentObjectImageAttributes = array();
$contentObjectImageAttributesCount = 0;
$aliases = array();

// Switch based on siteaccess usage
if( $currentSiteaccess == true )
{
    // Default image alias settings
    $aliases = eZINI::instance( 'image.ini' )->variable( 'AliasSettings', 'AliasList' );
}
else
{
    // Load default related siteaccess image alias settings
    $relatedSiteAccesses = eZINI::instance( 'site.ini' )->variable( 'SiteAccessSettings', 'RelatedSiteAccessList' );
    if( is_array( $relatedSiteAccesses ) )
    {
        foreach( $relatedSiteAccesses as $relatedSiteAccess )
        {
            // Optional debug output
            if( $troubleshoot == true && $troubleshootLevel >= 3 )
            {
                $cli->output( 'Fetching related siteaccess ' . "'" . $relatedSiteAccess . "'" . ' image.ini:[AliasSettings] AliasList[] image aliases defined' );
            }

            $siteaccessAliases = eZINI::getSiteAccessIni( $relatedSiteAccess, 'image.ini' )->variable( 'AliasSettings', 'AliasList' );

            // Default related siteaccess image alias settings
            if( $siteaccessAliases != false )
            {
                // Add siteaccess defined image aliases into array
                foreach( $siteaccessAliases as $siteaccessAlias )
                {
                    if( !in_array( $siteaccessAlias, $aliases ) )
                    {
                        $aliases[] = $siteaccessAlias;
                    }
                }

                // Add default siteacess settings aliases into array
                foreach( eZINI::instance( 'image.ini', 'settings', null, null, false, true )->variable( 'AliasSettings', 'AliasList' ) as $defaultSettingAlias )
                {
                    if( !in_array( $defaultSettingAlias, $aliases ) )
                    {
                        $aliases[] = $defaultSettingAlias;
                    }
                }

                // Optional debug output
                if( $troubleshoot == true && $troubleshootLevel >= 3 )
                {
                    $cli->output( 'All siteaccess ' . "'" . $relatedSiteAccess . "'" . ' image.ini:[AliasSettings] AliasList[] image aliases defined' );
                    print_r( $aliases ); self::displayMessage( '', "\n");
                }
            }
        }
    }
}

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
$fetchContentClassImageAttributesFirstVersion = false;
$contentObjectImageAttributes = BCImageAlias::fetchImageAttributesByClassAttributes( $contentClassImageAttributes, $fetchContentClassImageAttributesFirstVersion );
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
    if( $dry == true )
    {
        $footerMessageSummary = ". No image alias variation image files deleted!\n";
    }
    else
    {
        $footerMessageSummary = ". Image alias variation image files deleted!\n";
    }
    if( $verbose )
    {
        $footerMessage = "";
    }
    else
    {
        $footerMessage = "\n";
    }
    if( $dry )
    {
        $footerMessage .= 'Number of images alias variation files in system: ' . $script->IterationIndex . $footerMessageSummary;
    }
    else
    {
        $footerMessage .= $script->IterationIndex == 0 ? '' : "\n" . 'Number of images alias variation files removed: ' . $script->IterationIndex . $footerMessageSummary;
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
