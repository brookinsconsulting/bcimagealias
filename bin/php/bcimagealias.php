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
                                     'eZ Publish content tree node content object attribute image alias variation image file generator / remover. ' .
                                     'This script makes sure that content object attribute image ' .
                                     'alias variation image files are generated (before they are requested by users) or removed (for maintinence).',
                                     'extended-description' => '1. fetch ezcontentclass having an ezimage (or other defined datatype) attributes, ' .
                                                               '2.1. fetch objects of these classes,' .
                                                               '2.2. purge image alias image variation image files for all content objects' .
                                                               '3. fetch node (and possibly node children) and create array of unique objects' .
                                                               '3.1. purge image alias image variation image files for all content objects',
                                     'use-session' => false,
                                     'use-modules' => false,
                                     'use-extensions' => true ) );

// Fetch default script options
$options = $script->getOptions( '[force;][dry;][script-verbose;][generate;][remove;][troubleshoot-level;][related-siteaccesses;][object-id;][node-id;][subtree-children;]',
                                '[name]', array( 'force' => 'Force generation or removal. Disable delayed startup. Example: ' . "'--force'" . ' is an optional parameter which defaults to false',
                                                 'dry' => 'Use only with ' . "'--remove'" . ' parameter to make no system changes. Simulate the removal of content object attribute image datatype image alias image variation image files from system. Example: ' . "'--dry'" . ' is an optional parameter which defaults to false',
                                                 'script-verbose' => 'Use this parameter to display verbose script output without disabling script iteration counting of images generated or removed. Example: ' . "'--script-verbose'" . ' is an optional parameter which defaults to false',
                                                 'generate' => 'Generate content object attribute image datatype image alias image variation image files. Either ' . "'--generate' or '--remove'" . ' are required parameters',
                                                 'remove' => 'Remove existing content object attribute image datatype image alias image variation image files from system. Either ' . "'--generate' or '--remove'" . ' are required parameters',
                                                 'troubleshoot-level' => 'Use only with ' . "'--script-verbose'" . ' parameter to see more of execution internals. Example: ' . "'--troubleshoot-level=2'" . ' is an optional parameter which defaults to 1',
                                                 'related-siteaccesses' => 'Use only with ' . "'--generate'" . ' or ' . "'--remove'" . ' parameters to fetch image alias settings definitions from all related siteaccess to current siteaccess. Example: ' . "'--related-siteaccesses=true'" . ' is an optional parameter which defaults to false',
                                                 'object-id' => 'Use only with ' . "'--generate'" . ' or ' . "'--remove'" . ' parameters to perform operations on a single content object. Example: ' . "'--object-id=2'" . ' is an optional parameter which defaults to false',
                                                 'node-id' => 'Use only with ' . "'--generate'" . ' or ' . "'--remove'" . ' parameters to perform operations on a single content tree node. Do not use with ' . "'--object-id'" . ' parameter. Example: --' . "'node-id=2'" . ' is an optional parameter which defaults to false',
                                                 'subtree-children' => 'Use only with ' . "'--node-id'" . ' parameter to perform operations on all content tree node subtree children as well. Example: ' . "'--subtree-children'" . ' is an optional parameter which defaults to false'
                                               ) );

// Script parameters
$siteAccess = $options['siteaccess'] ? $options['siteaccess'] : false;
$troubleshootLevel = isset( $options['troubleshoot-level'] ) ? $options['troubleshoot-level'] : 1;
$troubleshoot = ( isset( $options['troubleshoot-level'] ) && $options['troubleshoot-level'] > 0 ) ? true : false;
$verbose = isset( $options['script-verbose'] ) ? true : false;
$force = isset( $options['force'] ) ? true : false;
$dry = isset( $options['dry'] ) ? true : false;
$generate = isset( $options['generate'] ) ? true : false;
$remove = isset( $options['remove'] ) ? true : false;
$currentSiteaccess = isset( $options['related-siteaccesses'] ) ? false : true;
$objectID = isset( $options['object-id'] ) ? $options['object-id'] : false;
$nodeID = isset( $options['node-id'] ) ? $options['node-id'] : false;
$subtreeChildren = isset( $options['subtree-children'] ) ? $options['subtree-children'] : false;

if( !$generate && !$remove )
{
    $cli->warning( "To run this script you must pass one of the required arguments --generate or --remove to use this extension script" );
    $cli->warning( "You can run this script with --dry switch to just view which files are going to be generated or removed." );
    $cli->output();
    // Shutdown the script and exit eZ
    $script->shutdown( 1 );
}

// Alert user to current siteaccess used for process
if ( $siteAccess || ( $verbose && $siteAccess != '' ) || ( $dry && $siteAccess != '' ) )
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
if( $currentSiteaccess )
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
            if( $troubleshoot && $troubleshootLevel >= 3 )
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
                if( $troubleshoot && $troubleshootLevel >= 3 )
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
    $cli->warning( "You must enable the bcimagealias extension for the default siteaccess first and clear all ini caches to use this extension script" );
    $cli->output();
    // Shutdown the script and exit eZ
    $script->shutdown( 1 );
}

// Fetch content class image attributes
$contentClassImageAttributes = BCImageAlias::fetchContentClassImageAttributes();

// Perform node / all specific operations
if( ( $nodeID != false && !$objectID ) || ( !$objectID && !$nodeID ) )
{
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
}
elseif( $objectID != false && !$nodeID )
{
    /**
     * Fetch content object
     */
    // Default datatypes to generate image alias variations
    $imageDataTypeStrings = eZINI::instance( 'bcimagealias.ini' )->variable( 'BCImageAliasSettings', 'ImageDataTypeStringList' );

    $object = eZContentObject::fetch( $objectID );
    $objectDataMap = $object->attribute( 'data_map' );
    $objectContentClassImageAttributes = array();

    foreach( $objectDataMap as $objectAttribute )
    {
        if( in_array( $objectAttribute->attribute( 'data_type_string' ), $imageDataTypeStrings ) )
        {
            $objectContentClassImageAttributes[] = $objectAttribute;
        }
    }

    $contentObjectImageAttributesCount = count( $objectContentClassImageAttributes );
    $imageAliasVariationCount = $contentObjectImageAttributesCount * count( $aliases ) * count( $imageDataTypeStrings );
    $script->resetIteration( $imageAliasVariationCount );
}

// Warn user and wait for user abort
if ( !$force )
{
    // Perform operastions for cases which do not object specific
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

// Test for cases which do not operate by object
if( !$objectID && !$nodeID )
{
    // Perform operastions for cases which do not operate by object
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
}
elseif( $objectID != false && !$nodeID )
{
    // Test for cases which operate by object
    if( $generate && !$remove )
    {
        // Alert the user to what is happening
        if( $dry && $generate )
        {
            $headerActionMessage = "Dry run: Pretending to generate image alias image variation image files for a single content object\n";
        }
        else
        {
            $headerActionMessage = "Generating image alias image variation image files for a single content object\n";
        }
    }
    else
    {
        // Alert the user to what is happening
        if( $dry && $remove )
        {
            $headerActionMessage = "Dry run: Pretending to remove image alias image variation files for a single content object\n";
        }
        else
        {
            $headerActionMessage = "Removing image alias image variation files for a single content object\n";
        }
    }
}
elseif( $nodeID != false && !$objectID )
{
    // Test for cases which operate by object
    if( $generate && !$remove )
    {
        // Alert the user to what is happening
        if( $dry && $generate && !$subtreeChildren )
        {
            $headerActionMessage = "Dry run: Pretending to generate image alias image variation image files for a single content tree node\n";
        }
        elseif( $dry && $generate && $subtreeChildren )
        {
            $headerActionMessage = "Dry run: Pretending to generate image alias image variation image files for a single content tree node (and all subtree child nodes)\n";
        }
        elseif( !$dry && $generate && !$subtreeChildren )
        {
            $headerActionMessage = "Generating image alias image variation image files for a single content tree node\n";
        }
        elseif( !$dry && $generate && $subtreeChildren )
        {
            $headerActionMessage = "Generating image alias image variation image files for a single content tree node (and all subtree child nodes)\n";
        }
    }
    else
    {
        // Alert the user to what is happening
        if( $dry && $remove && !$subtreeChildren )
        {
            $headerActionMessage = "Dry run: Pretending to remove image alias image variation files for a single content tree node\n";
        }
        elseif( $dry && $remove && $subtreeChildren )
        {
            $headerActionMessage = "Dry run: Pretending to remove image alias image variation files for a single content tree node (and all subtree child nodes)\n";
        }
        elseif( !$dry && $remove && !$subtreeChildren )
        {
            $headerActionMessage = "Removing image alias image variation files for a single content tree node\n";
        }
        elseif( !$dry && $remove && $subtreeChildren )
        {
            $headerActionMessage = "Removing image alias image variation files for a single content tree node (and all subtree child nodes)\n";
        }
    }
}
$cli->output( $cli->stylize( 'header', $headerActionMessage ) );

// Test for cases which do not operate by object
if( !$objectID && !$nodeID )
{
    // Perform operastions for cases which do not operate by object
    if( $generate && !$remove )
    {
        // Attempt to generate image alias variations
        $result = BCImageAlias::instance( $scriptExecutionOptions )->createByAttributes( $contentObjectImageAttributes );
    }
    else
    {
        // Attempt to remove image alias variation files
        $result = BCImageAlias::instance( $scriptExecutionOptions )->removeByAttributes( $contentObjectImageAttributes );
    }
}
elseif( $objectID != false && !$nodeID )
{
    // Test for cases which operate by object
    if( $generate && !$remove )
    {
        // Attempt to generate image alias variations
        $result = BCImageAlias::instance( $scriptExecutionOptions )->createByObject( $object );
    }
    else
    {
        // Attempt to remove image alias variation files
        $result = BCImageAlias::instance( $scriptExecutionOptions )->removeByObject( $object );
    }
}
elseif( $nodeID != false && !$objectID )
{
    // Fetch the provided node
    $node = eZContentObjectTreeNode::fetch( $nodeID );

    if( is_object( $node ) )
    {
        // Test for cases which operate by node
        if( $generate && !$remove && !$subtreeChildren )
        {
            // Attempt to generate image alias variations
            $result = BCImageAlias::instance( $scriptExecutionOptions )->createByObject( $node->attribute( 'object' ) );
        }
        elseif( $generate && !$remove && $subtreeChildren )
        {
            // Subtree fetch parameters for the ordering of child nodes fetched to be processed
            $subtreeParams = array(
                                   'MainNodeOnly' => true,
                                   'Depth' => 4,
                                   'SortBy' => array( 'depth', true ) );

            // Attempt to generate image alias variations
            $result = BCImageAlias::instance( $scriptExecutionOptions )->createByNodeSubtree( $node, $subtreeParams );
        }
        elseif( $remove && !$generate && !$subtreeChildren )
        {
            // Attempt to remove image alias variation files
            $result = BCImageAlias::instance( $scriptExecutionOptions )->removeByObject( $node->attribute( 'object' ) );
        }
        elseif( $remove && !$generate && $subtreeChildren )
        {
            // Subtree fetch parameters for the ordering of child nodes fetched to be processed
            $subtreeParams = array(
                                   'MainNodeOnly' => true,
                                   'Depth' => 4,
                                   'SortBy' => array( 'depth', true ) );

            // Attempt to remove image alias variation files
            $result = BCImageAlias::instance( $scriptExecutionOptions )->removeByNodeSubtree( $node, $subtreeParams );
        }
    }
}

// Test for cases which do not operate by object
if( !$objectID && !$nodeID )
{
    // Perform operastions for cases which do not operate by object
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
        if( $dry )
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
}
elseif( $objectID != false || $nodeID != false )
{
    // Test for cases which operate by object
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
        if( $dry )
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
