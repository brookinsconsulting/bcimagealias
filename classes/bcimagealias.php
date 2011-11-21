<?php
/**
 * File containing the BCImageAlias class.
 *
 * @copyright Copyright (C) 1999 - 2011 Brookins Consulting. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2 (or later)
 * @version //autogentag//
 * @package bcimagealias
 */

class BCImageAlias {

    /**
     * Default constructor of the BCImageAlias class
     *
     * @param array $options Options used by the instance
     */
    function BCImageAlias( $options = false )
    {
        if( is_array( $options ) )
        {
            self::$ExecutionOptions = array_merge( self::$ExecutionOptions , $options );
        }
    }

    /**
     * Static constructor of the BCImageAlias class
     *
     * @param array $options Options used by the instance
     * @return object BCImageAlias
     * @static
     */
    static function instance( $options = false )
    {
        return new BCImageAlias( $options );
    }
    
    /**
     * Fetch content class attributes by dataTypestring
     *
     * @param array $imageDataTypeStrings array of image datatype strings. ie: array( 'ezimage' )
     * @return array Array of content class attributes, empty array if not
     * @static
     */
    static function fetchContentClassImageAttributes( $imageDataTypeStrings = false )
    {
        $contentClassImageAttributes = array();
        if( !is_array( $imageDataTypeStrings ) )
        {
            // Default datatypes to generate image alias variations
            $imageDataTypeStrings = eZINI::instance( 'bcimagealias.ini' )->variable( 'BCImageAliasSettings', 'ImageDataTypeStringList' );
        }
        foreach( $imageDataTypeStrings as $dataTypeString )
        {
            $contentClassImageAttributes = array_merge( $contentClassImageAttributes, eZContentClassAttribute::fetchList(
                true,
                array(
                      'data_type' => $dataTypeString
                )
            ));
        }
        
        return $contentClassImageAttributes;
    }

    /**
     * Fetch content object 'image' attributes by content class attributes
     *
     * @param array $contentClassImageAttributes Array of content class attributes
     * @return array Array of content object attributes, empty array if not
     * @static
     */
    static function fetchImageAttributesByClassAttributes( $contentClassImageAttributes = array(), $firstVersion = true )
    {
        // Default datatypes to generate image alias variations
        $imageDataTypeStrings = eZINI::instance( 'bcimagealias.ini' )->variable( 'BCImageAliasSettings', 'ImageDataTypeStringList' );

        $contentObjectImageAttributes = array();
        foreach ( $contentClassImageAttributes as $contentClassAttribute )
        {
            $contentObjectAttributes = eZContentObjectAttribute::fetchSameClassAttributeIDList(
                                           $contentClassAttribute->attribute( 'id' )
                                       );
            foreach ( $contentObjectAttributes as $contentObjectAttribute )
            {
                if( in_array( $contentObjectAttribute->attribute( 'data_type_string' ), $imageDataTypeStrings ) && $contentObjectAttribute->attribute( 'has_content' ) == true )
                {
                    $contentObjectImageAttributes[] = $contentObjectAttribute;
                }
            }
        }

        return $contentObjectImageAttributes;
    }

    /**
     * Create if alias variation does not exist by content object
     *
     * @param object $object Object of class ezcontentobject. Required
     * @return bool true if any image alias generation is called, false if not
     * @static
     */
    static function createByObject( $object = false )
    {
        if ( !$object )
        {
            return false;
        }

        $executionOptions = self::executionOptions();
        $contentObjectAttributes = $object->contentObjectAttributes();
        
        $contentClassImageAttributes = self::fetchContentClassImageAttributes();
        $contentClassImageAttributesArray = array();

        foreach( $contentClassImageAttributes as $contentClassImageAttribute )
        {
            $contentClassImageAttributesArray[] = $contentClassImageAttribute->attribute( 'id' );
        }

        $results = array();

        // Iterate over object attributes
        foreach ( $contentObjectAttributes as $contentObjectAttribute )
        {
            // Test to ensure only attributes of class image are used
            if ( in_array( $contentObjectAttribute->attribute('contentclassattribute_id') , 
                           $contentClassImageAttributesArray ) )
            {
                // Trigger the image alias variation generation
                $results[] = self::createByAttribute( $contentObjectAttribute );
            }
            else
            {
                $results[] = false;
            }
        }
        
        // Optional debug output
        if( $executionOptions[ 'troubleshoot' ] == true )
        {
            self::displayMessage( 'Here are the content object image attribute generation attempt results' );            
            self::displayMessage( 'True will show up as a 1. Theses results do not affect workflow completion as image aliases will not always be generated' );
            print_r( $results ); echo "\n";
        }

        // Calculate return results based on execution options and results comparison
        if( in_array( true, $results ) && count( $contentObjectAttributes ) == count( $results ) )
        {
            return true;
        }

        return false;
    }

    /**
     * Create image alias variation by contentObjectAttribute
     *
     * @param object $contentObjectAttribute object of class eZContentObjectAtribute
     * @return bool true if any image alias generation is called, false if not
     * @static
     */
    static function createByAttribute( $contentObjectAttribute = false )
    {
        if ( !$contentObjectAttribute )
        {
            return false;
        }

        $results = array();
        $result = array();
        $executionOptions = self::executionOptions();

        // Default image alias settings
        $aliases = eZINI::instance( 'image.ini' )->variable( 'AliasSettings', 'AliasList' );

        // Don't try to create original image alias
        unset( $aliases['original'] );

        // Default datatypes to generate image alias variations
        $imageDataTypeStrings = eZINI::instance( 'bcimagealias.ini' )->variable( 'BCImageAliasSettings', 'ImageDataTypeStringList' );

        if( !in_array( $contentObjectAttribute->attribute( 'data_type_string' ), $imageDataTypeStrings ) || $contentObjectAttribute->attribute( 'has_content' ) == false )
        {
            return false;
        }

        // Fetch content object attribute content the image alias handler object
        $imageHandler = $contentObjectAttribute->content();

        // Fetch eZImageManager instance
        $imageManager = eZImageManager::factory();

        // Fetch the image alias handler object's alias list
        $aliasList = $imageHandler->aliasList();

        $original = $aliasList['original'];
        $basename = $original['basename'];

        // Optional debug output
        if( $executionOptions[ 'troubleshoot' ] == true )
        {
            self::displayMessage( 'Current content object image attribute image alias list entries within attribute handler content:' );
            print_r( $imageHandler->ContentObjectAttributeData['DataTypeCustom']['alias_list'] ); echo "\n\n";
            self::displayMessage( 'Number of ini image aliases: ' . count( $aliases ), "\n\n\n" );
        }

        // Initializse alias foreach counter at one, 1
        $aliasCounter = 1;

        // Iterate through image alias list from settings
        foreach( $aliases as $alias )
        {
            // Optional debug output
            if( $executionOptions[ 'troubleshoot' ] == true )
            {
                self::displayMessage( 'Iteration ' . $aliasCounter . ' of ' . count( $aliases ) . ' | Preparing to attempt to generate the "' . $alias . '" image alias variation' );
            }

            // Store a temporary record of the alias not yet generated this iteration
            $result[ $alias ] = false;

            // Iterate alias foreach counter
            $aliasCounter++;

            if ( !$imageManager->hasAlias( $alias ) )
            {
                // Optional debug output
                if( $executionOptions[ 'troubleshoot' ] == true )
                {
                    self::displayMessage( "eZImageManger claims: $alias does not exist in system" );
                }
                continue;
            }

            // Skip generating aliases which already exist if force option is false
            if( isset( $aliasList[ $alias ] )
                && $executionOptions[ 'force' ] == false )
            {
                continue;
            }

            // Skip generation if force is not true and dry is true
            if( $executionOptions[ 'force' ] == false && $executionOptions[ 'dry' ] == true )
            {
                // Optional debug output
                if( $executionOptions[ 'troubleshoot' ] == true )
                {
                    // Alert user of dry alias calculation
                    $message = "Dry run: Calculating generation of datatype " . $contentObjectAttribute->attribute( 'data_type_string' ) . "type image alias " . $alias .
                               " image variation " . "\n";

                    self::displayMessage( $message );
                }

                continue;
            }

            // Create $alias the image alias image variation image file on disk immediately 
            if ( $imageManager->createImageAlias( $alias, $aliasList,
                                                  array( 'basename' => $basename ) ) )
            {
                // Optional debug output
                if( $executionOptions[ 'troubleshoot' ] == true )
                {
                    self::displayMessage( 'Specific alias added to aliasList (in attribute):' );
                    print_r( $aliasList[ $alias ] ); echo "\n";
                }

                // Store a record of the alias generated this iteration
                $result[ $alias ] = true;

                // Uncomment the following line to write a error log entry (for debug)
                // error_log( __CLASS__ . __METHOD__ . ": Created alias $alias" );
            }
            else
            {
                // Store a record of the alias not generated this iteration
                $result[ $alias ] = false;

                // Uncomment the following line to write a error log entry (for debug)
                // error_log( __CLASS__ . __METHOD__ . ": Fail creating alias $alias" );
            }

            // Optional debug output
            if( $executionOptions[ 'troubleshoot' ] == true )
            {
                self::displayMessage( 'Leaving create image alias if block' );
                self::displayMessage( 'Looping to next image alias from ini settings', "\n\n\n" );
            }
        }

        $aliasesGenerated = array_keys( $result, true );
        $aliasesGeneratedCount = count( $aliasesGenerated );

        // Only prepare alias meta data when alias(s) have been created
        if ( is_array( $result ) && in_array( true, array_keys( $result, true ) ) )
        {
            $aliasAlertnativeText = $imageHandler->displayText( $original['alertnative_text'] );
            $aliasOriginalFilename = $original['original_filename'];

            foreach ( $aliasList as $aliasKey => $aliasListItem )
            {
            
                // Test for newly added alias
                // if ( ( !isset( $aliasListItem['is_new'] ) or $aliasListItem['is_new'] == '' ) && $executionOptions[ 'force' ] == true )
                if ( $executionOptions[ 'force' ] == true )
                {
                    $aliasListItem['is_new'] = true;
                    $aliasListItem['is_valid'] = true;
                }

                // Prepare meta data
                $aliasListItem['original_filename'] = $aliasOriginalFilename;
                $aliasListItem['text'] = $aliasAlertnativeText;

                // Test for alias file url and add meta data
                if ( $aliasListItem['url'] )
                {
                    $aliasListItemFile = eZClusterFileHandler::instance( $aliasListItem['url'] );
                    if( $aliasListItemFile->exists() )
                    {
                        $aliasListItem['filesize'] = $aliasListItemFile->size();
                    }
                }

                // Test for newly added alias
                if ( $aliasListItem['is_new'] )
                {
                    eZImageFile::appendFilepath( $imageHandler->ContentObjectAttributeData['id'], $aliasListItem['url'] );
                }

                // Add alias image variation image file meta data back into aliasList
                $aliasList[ $aliasKey ] = $aliasListItem;

                // Track successful generation attempts
                if ( isset( $result[ $aliasKey ] ) && $result[ $aliasKey ] == true )
                {
                    $results[] = true;
                    $message = "Generated datatype " . $contentObjectAttribute->attribute( 'data_type_string' ) . "type image alias " . $aliasListItem['name'] .
                               " image variation " . $aliasListItem['url'] . "\n";

                    self::scriptIterate( $message );
                }
                elseif ( !isset( $result[ $aliasKey ] ) )
                {
                    $results[] = true;
                }
                else
                {
                    $results[] = false;
                }
            }

            /**
             * Note: The following code replaces the use of this example private method unavailable at the time of publishing
             *
             * $imageHandler->setAliasList( $aliasList );
             */
            $imageHandler->ContentObjectAttributeData['DataTypeCustom']['alias_list'] = $aliasList;
            $imageHandler->addImageAliases( $aliasList );
            // $imageHandler->store( $contentObjectAttribute );

            // Optional debug output
            if( $executionOptions[ 'troubleshoot' ] == true )
            {
                print_r( $aliasList ); echo "\n\n";
                print_r( $imageHandler ); echo "\n\n";
            }
        }

        // Optional debug output
        if( $executionOptions[ 'troubleshoot' ] == true )
        {
            self::displayMessage( "\n" . 'Content object attribute image alias image variation generation attempts completed' );

            $coaID = $contentObjectAttribute->attribute( 'id' );
            $coaVersion = $contentObjectAttribute->attribute( 'version' );
            $contentObjectAttributeRefetched = eZContentObjectAttribute::fetch( $coaID, $coaVersion );

            self::displayMessage( 'Displaying saved re-feched data_text of attribute image handler. You should see this list fully populated with all generated image alias file urls' );
            // print_r( $contentObjectAttributeRefetched ); //>attribute( 'content' )->aliasList()
            print_r( $contentObjectAttributeRefetched->attribute( 'content' )->aliasList() );
            // print_r( $contentObjectAttributeRefetched->attribute( 'content' )->ContentObjectAttributeData['data_text'] ); echo "\n\n";


            $objectLookup = eZContentObject::fetch( $contentObjectAttribute->attribute( 'contentobject_id' ) );
            $objectLookupDM = $objectLookup->dataMap();
            
            print_r( $objectLookupDM[ 'image' ]->content()->aliasList( true ) ); echo "\n\n";

            self::displayMessage( 'Here are the content object image attribute image alias generation attempt results' );
            self::displayMessage( 'Generated aliases will show up as a 1. Theses results do not affect workflow completion as image aliases will not always be generated' );
            print_r( $result ); echo "\n";
        }

        // Calculate return results based on execution options and results comparison
        if( in_array( true, $results ) && count( $aliases ) == count( $result )
            && $executionOptions[ 'dry' ] == false
            && $executionOptions[ 'force' ] == true )
        {
            // Optional debug output
            if( $executionOptions[ 'troubleshoot' ] == true )
            {
                self::displayMessage( 'Generation attempts calculate as successfull, at least once. All aliases possible attempted' );
                self::displayMessage( 'Variation images generated: ' . $aliasesGeneratedCount . ' out of ' . count( $result ), "\n\n\n" );
            }

            return true;
        }
        elseif( in_array( true, $results )
                && $executionOptions[ 'dry' ] == false
                && $executionOptions[ 'force' ] == false )
        {
            // Optional debug output
            if( $executionOptions[ 'troubleshoot' ] == true )
            {
                self::displayMessage( 'Generation attempts calculate as successfull, at least once. All aliases possible attempted' );
                self::displayMessage( 'Variations images generated: ' . $aliasesGeneratedCount . ' out of ' . count( $result ), "\n\n\n" );
            }

            return true;
        }

        return false;
    }

    /**
     * Attempt to generate content object 'image' attribute image variations by content object attribute
     *
     * @param object $contentClassImageAttribute object of class eZContentObjectAttribute
     * @return bool true if any image alias generation is called, false if not
     * @static
     */
    static function createByAttributes( $contentObjectAttributes = false )
    {
        if ( !is_array( $contentObjectAttributes ) )
        {
            return false;
        }

        $results = array();
        $executionOptions = self::executionOptions();

        $contentClassImageAttributes = self::fetchContentClassImageAttributes();
        $contentClassImageAttributesArray = array();

        // Iterate over content class image attributes
        foreach( $contentClassImageAttributes as $contentClassImageAttribute )
        {
            $contentClassImageAttributesArray[] = $contentClassImageAttribute->attribute( 'id' );
        }

        // Iterate over object attributes
        foreach( $contentObjectAttributes as $contentObjectAttribute )
        {
            if ( in_array( $contentObjectAttribute->attribute('contentclassattribute_id') , 
                           $contentClassImageAttributesArray ) )
            {
                // Trigger the image alias variation generation
                $results[] = self::createByAttribute( $contentObjectAttribute );
            }
            else
            {
                // Record the failure to create by attribute because the attribute was of the wrong content class
                $results[] = false;
            }
        }

        // Calculate return results based on execution options and results comparison
        if( in_array( true, $results ) && count( $contentObjectAttributes ) == count( $results )  && $executionOptions[ 'dry' ] == false )
        {
            return true;
        }
        return false;
    }

    /**
     * Attempt to remove content object 'image' attribute image variations by content object attributes
     *
     * @param array $contentClassImageAttributes Array of objects of class eZContentObjectAttribute
     * @return bool true if succcesfull, false otherwise
     * @static
     */    
    static function removeAllAliases( $contentObjectAttributes = false )
    {
        if ( !is_array( $contentObjectAttributes ) )
        {
            return false;
        }
        
        $filePaths = array();
        $results = array();
        $uniqueContentObjectAttributes = array();
        $executionOptions = self::executionOptions();
        $messageCount = 0;

        foreach( $contentObjectAttributes as $contentObjectAttribute )
        {
            $contentObjectID = $contentObjectAttribute->attribute( 'contentobject_id' );
            $contentObjectAttributeID = $contentObjectAttribute->attribute( 'id' );
            $contentObjectAttributeVersion = $contentObjectAttribute->attribute( 'version' );


            // Optional debug output
            if( $executionOptions[ 'troubleshoot' ] == true && $executionOptions[ 'troubleshootLevel' ] == 1 )
            {
                self::displayMessage( 'Content object attribute', "\n" );
                print_r( $uniqueContentObjectAttributes ); echo "\n\n";

                self::displayMessage( 'Iterating over image attribute of object:' );

                $object = eZContentObject::fetch( $contentObjectAttribute->attribute( 'contentobject_id' ) );

                self::displayMessage( $object->attribute( 'name' ), "\n" );
                self::displayMessage( 'ContentObjectID: ' . $contentObjectAttribute->attribute( 'contentobject_id' ) );

                print_r( $contentObjectAttribute->content()->aliasList( true ) ); echo "\n\n";
            }

            $results[] = self::removeAllAliasesByAttribute( $contentObjectAttribute );
        }

        // Calculate return results based on execution options and results comparison
        if( in_array( true, $results ) && count( $contentObjectAttributes ) == count( $results ) && $executionOptions[ 'dry' ] == false )
        {
            return true;
        }
        return false;
    }

    /**
     * Attempt to remove content object 'image' attribute image variations by content object attribute
     *
     * @param object $contentClassImageAttribute object of objects of class eZContentObjectAttribute
     * @return bool true if succcesfull, false otherwise
     * @static
     */
    static function removeAllAliasesByAttribute( $contentObjectAttribute = false )
    {
        if ( !is_object( $contentObjectAttribute ) )
        {
            return false;
        }

        $filePaths = array();
        $results = array();
        $executionOptions = self::executionOptions();
        $messageCount = 0;

        $imageHandler = $contentObjectAttribute->attribute( 'content' );
        $aliasList = $imageHandler->aliasList();
        $aliasListWithoutOriginal = $imageHandler->aliasList();

        // Optional debug output
        if( $executionOptions[ 'troubleshoot' ] == true && $executionOptions[ 'troubleshootLevel' ] == 2 )
        {
            self:displayMessage( 'All attribute image aliases stored in content:' );
            print_r( $imageHandler->aliasList() ); echo "\n\n";
        }

        // Do not process the orginal image alias
        unset( $aliasListWithoutOriginal['original'] );
        unset( $aliasList['original'] );

        if( count( $aliasList ) == 0 )
        {
            return false;
        }

        $attributeData = $imageHandler->originalAttributeData(); 
        $contentObjectAttributeID = $attributeData['attribute_id'];

        if ( $attributeData['attribute_version'] === null )
        {
            $files = eZImageFile::fetchForContentObjectAttribute( $contentObjectAttributeID, true );
            $dirs = array();
            $count = 0;
            foreach ( $files as $filepath )
            {
                $file = eZClusterFileHandler::instance( $filepath );
                if ( $file->exists() )
                {
                    $filePaths[] = $filepath;
                    if( $executionOptions[ 'dry' ] == false )
                    {
                        $file->fileDelete( $filepath );
                        $dirs[] = eZDir::dirpath( $filepath );
                    }
                    $count++;
                }
            }
            if( $executionOptions[ 'dry' ] == false )
            {
                $dirs = array_unique( $dirs );
                foreach ( $dirs as $dirpath )
                {
                    eZDir::cleanupEmptyDirectories( $dirpath );
                }
                eZImageFile::removeForContentObjectAttribute( $contentObjectAttributeID );
                $message = "Removed datatype " . $contentObjectAttribute->attribute( 'data_type_string' ) . "type image alias variation " . $filePaths[ $messageCount ] . "\n";
            }
            else
            {
                $message = "Dry run: Remove datatype " . $contentObjectAttribute->attribute( 'data_type_string' ) . "type image alias variation " . $filePaths[ $messageCount ] . "\n";
            }
            while( $messageCount < $count )
            {
                self::scriptIterate( $message );   
                $messageCount++;
                $result = true;
            }
        }
        else
        {
            $contentObjectAttributeVersion = $imageHandler->ContentObjectAttributeData['version'];
            $contentObjectAttributeID = $imageHandler->ContentObjectAttributeData['id'];

            // We loop over each image alias, and look up the file in ezcontentobject_attribute
            // Only images referenced by one version will be removed
            foreach ( $aliasList as $aliasName => $alias )
            {
                $dirpath = $alias['dirpath'];
                $doNotDelete = false; // Do not delete files from storage

                if ( $alias['is_valid'] && $alias['name'] != 'original' )
                {
                    $filepath = $alias['url'];

                    // Calculate appropriate message to Alert user with
                    if( $executionOptions[ 'dry' ] == true )
                    {
                        $message = "Dry run: Calculating removal of datatype " . $contentObjectAttribute->attribute( 'data_type_string' ) . "type image alias variation " . $filepath . "\n";
                    }
                    else
                    {
                        $message = "Removed standard datatype " . $contentObjectAttribute->attribute( 'data_type_string' ) . "type image alias variation " . $filepath . "\n";
                    }

                    if ( !$doNotDelete && $executionOptions[ 'dry' ] == false )
                    {
                        $file = eZClusterFileHandler::instance( $filepath );

                        if ( $file->exists() )
                        {               
                            $file->purge();
                            eZImageFile::removeFilepath( $contentObjectAttributeID, $filepath );
                            eZDir::cleanupEmptyDirectories( $dirpath );

                            self::scriptIterate( $message );
                            $results[] = true;
                        }
                        else
                        {
                            eZDebug::writeError( "Image file $filepath for alias $aliasName does not exist, could not remove from disk", __METHOD__ );
                        }
                        
                        $doc = $imageHandler->ContentObjectAttributeData['DataTypeCustom']['dom_tree'];
                        foreach ( $doc->getElementsByTagName( 'alias' ) as $aliasNode )
                        {
                            $aliasNode->parentNode->removeChild( $aliasNode );
                        }
                        $imageHandler->ContentObjectAttributeData['DataTypeCustom']['dom_tree'] = $doc;
                        unset( $imageHandler->ContentObjectAttributeData['DataTypeCustom']['alias_list'] );

                        $imageHandler->storeDOMTree( $doc, true, $contentObjectAttribute );
                    }
                    else
                    {
                        self::scriptIterate( $message );
                        $results[] = true;
                    }
                }
            }
        }

        // Calculate return results based on execution options and results comparison
        if( in_array( true, $results ) && count( $aliasList ) == count( $results ) && $executionOptions[ 'dry' ] == false )
        {
            return true;
        }
        return false;
    }

    /**
     * Trigger cli script iteration and verbose iteration message display
     *
     * @param string $message string of message text to use to alert user of iteration message
     * @return void
     * @static
     */
    static function scriptIterate( $message = '' )
    {
        $executionOptions = self::executionOptions();
        if( isset( $_SERVER['argv'] ) && $executionOptions[ 'iterate' ] )
        {
            if( $message != '' )
            {
                $cli = eZCLI::instance();
                $script = eZScript::instance();

                // Alter the user to what is happening
                self::displayMessage( $message );

                // Iterate script, Alert the user to what is happening at the moment
                $script->iterate( $cli, true, $message );
            }
        }
    }

    /**
     * Trigger display of cli script message to user and verbose iteration message display
     *
     * @param string $message String of message text to use to alert user of iteration message. Required
     * @param string $newline String of the newlines to use after displaying a message. Optional
     * @return void
     * @static
     */
    static function displayMessage( $message = '', $newline = "\n\n" )
    {

        $executionOptions = self::executionOptions();
        if( isset( $_SERVER['argv'] ) )
        {
            if( $message != '' )
            {
                $cli = eZCLI::instance();
                $script = eZScript::instance();
                if ( $executionOptions[ 'verbose' ] )
                {
                    // Alert the user to what is happening at the moment
                    $cli->output( $message );
                }
            }
        }
        // Optional debug output
        elseif( $executionOptions[ 'troubleshoot' ] == true && $message != '' )
        {
            print_r( $message );
            echo $newline;
        }
    }

    /**
     * Return current BCImageAlias static object property executionOptions an array of execution options
     *
     * @return array
     * @static
     */
    static function executionOptions()
    {
        return self::$ExecutionOptions;
    }

    /**
     * Array of default execution options
     *
     * Verbose = true, Enabled by default
     * Dry = true, Enabled by default. Does not write any files to disk
     * Iterate = false, Disabled by default. Does not attempt to run as eZ cli script (Workflow event execution)
     * Force = false, Disabled by default. Does not attempt to force generation of all image alias image variation image files unless they need to be created (not created yet)
     *
     * @static
     * @access public
     */
    static public $ExecutionOptions = array( 'verbose' => false, 'dry' => true, 'iterate' => false, 'force' => false );

}

?>
