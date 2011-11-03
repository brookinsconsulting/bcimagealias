<?php
/**
 * File containing the BCImageAlias class.
 *
 * @copyright Copyright (C) 1999-2011 Brookins Consulting. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2 (or later)
 * @version //autogentag//
 * @package extension/bcimagealias
 */

class BCImageAlias {

    /**
     * Constructor of the BCImageAlias class
     *
     * @param array $options Used by the instance
     */
    function BCImageAlias( $options = false )
    {
        if( is_array( $options ) )
        {
            self::$ExecutionOptions = $options;
        }
    }

    /**
     * Constructor of the BCImageAlias class
     *
     * @param array $options Used by the instance
     * @return BCImageAlias
     */
    static function instance( $options = false )
    {
        return new BCImageAlias( $options );
    }
    
    /**
     * Fetch content class attributes by dataTypestring
     *
     * @param $imageDataTypeStrings array of image datatype strings. ie: array( 'ezimage' )
     * @return array of content class attributes, empty array if not
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
     * @param $contentClassImageAttributes array of content class attributes
     * @return array of content object attributes, empty array if not
     */
    static function fetchImageAttributesByClassAttributes( $contentClassImageAttributes = array() )
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
     * @param $object object of class ezcontentobject
     * @return true if any image alias generation is called, false if not
     */
    static function createByObject( $object = false )
    {
        if ( !$object )
        {
            return false;
        }

        $contentObjectAttributes = $object->contentObjectAttributes();
        $results = array();

        // Iterate over object attributes
        foreach ( $contentObjectAttributes as $contentObjectAttribute )
        {
            // Trigger the image alias variation generation
            $results[] = self::createByAttribute( $contentObjectAttribute );
        }

        if( in_array( true, $results ) && count( $contentObjectAttributes ) == count( $results ) )
        {
            return true;
        }

        return false;
    }

    /**
     * Create image alias variation
     *
     * @param $contentObjectAttribute object of class eZContentObjectAtribute
     * @return true if any image alias generation is called, false if not
     */
    static function createByAttribute( $contentObjectAttribute = false )
    {
        if ( !$contentObjectAttribute )
        {
            return false;
        }

        $results = array();
        $executionOptions = self::executionOptions();

        // Default image alias settings
        $aliases = eZINI::instance( 'image.ini' )->variable( 'AliasSettings', 'AliasList' );

        // Fetch image alias handler
        $imageHandler = $contentObjectAttribute->content();

        // Don't try to create original image alias
        unset( $aliases['original'] );

        // Iterate through image alias list from settings
        foreach( $aliases as $alias )
        {
            $imageManager = eZImageManager::factory();
            $aliasList = $imageHandler->aliasList();
            $result = false;

            if ( !$imageManager->hasAlias( $alias ) )
            {
                return false;
            }

            $original = $aliasList['original'];
            $basename = $original['basename'];

            if( $executionOptions[ 'dry' ] == true )
            {
                $message = "Dry run: Calculating generation of datatype " . $contentObjectAttribute->attribute( 'data_type_string' ) . "type image alias " . $alias .
                           " image variation " . $aliasList[ $alias ]['url'] . "\n";

                self::scriptIterate( $message );
            }
            else
            {
                if ( $imageManager->createImageAlias( $alias, $aliasList,
                                                      array( 'basename' => $basename ) ) )
                {
                    $result = true;
                    $aliasAlternativeText = $imageHandler->displayText( $original['alternative_text'] );
                    $aliasOriginalFilename = $original['original_filename'];
                    foreach ( $aliasList as $aliasKey => $aliasListItem )
                    {
                        $aliasListItem['original_filename'] = $aliasOriginalFilename;
                        $aliasListItem['text'] = $aliasAlternativeText;

                        if ( $aliasListItem['url'] )
                        {
                            $aliasListItemFile = eZClusterFileHandler::instance( $aliasListItem['url'] );
                            if( $aliasListItemFile->exists() )
                            {
                                $aliasListItem['filesize'] = $aliasListItemFile->size();
                            }
                        }
                        if ( $aliasListItem['is_new'] )
                        {
                            eZImageFile::appendFilepath( $imageHandler->ContentObjectAttributeData['id'], $aliasListItem['url'] );
                        }
                        
                        $aliasList[ $aliasKey ] = $aliasListItem;
                    }
                    // $imageHandler->setAliasList( $aliasList );
                    $imageHandler->ContentObjectAttributeData['DataTypeCustom']['alias_list'] = $aliasList;
                    $imageHandler->addImageAliases( $aliasList );

                    // Track successful generation attempts
                    if ( $result == true )
                    {
                        $results[] = true;
                        $message = "Generated datatype " . $contentObjectAttribute->attribute( 'data_type_string' ) . "type image alias " . $alias .
                                   " image variation " . $aliasListItem['url'] . "\n";
                        
                        self::scriptIterate( $message );
                    }
                }
            }
        }

        if( in_array( true, $results ) && count( $aliases ) == count( $results ) && $executionOptions[ 'dry' ] == false )
        {
            return true;
        }

        return false;
    }

    /**
     * Attempt to generate content object 'image' attribute image variations by content object attribute
     *
     * @param $contentClassImageAttribute object of class eZContentObjectAttribute
     * @return true if any image alias generation is called, false if not
     */
    static function createByAttributes( $contentObjectAttributes = false )
    {
        if ( !is_array( $contentObjectAttributes ) )
        {
            return false;
        }

        $results = array();
        $executionOptions = self::executionOptions();

        foreach( $contentObjectAttributes as $contentObjectAttribute )
        {
            $results[] = self::createByAttribute( $contentObjectAttribute );
        }

        if( in_array( true, $results ) && count( $contentObjectAttributes ) == count( $results )  && $executionOptions[ 'dry' ] == false )
        {
            return true;
        }
        return false;
    }

    /**
     * Attempt to remove content object 'image' attribute image variations by content object attributes
     *
     * @param $contentClassImageAttributes array of objects of class eZContentObjectAttribute
     * @return bool true if succcesfull, false otherwise
     */    
    static function removeAllAliases( $contentObjectAttributes = false )
    {
        if ( !is_array( $contentObjectAttributes ) )
        {
            return false;
        }
        
        $filePaths = array();
        $results = array();
        $executionOptions = self::executionOptions();
        $messageCount = 0;

        foreach( $contentObjectAttributes as $contentObjectAttribute )
        {
            $results[] = self::removeAllAliasesByAttribute( $contentObjectAttribute );
        }

        if( in_array( true, $results ) && count( $contentObjectAttributes ) == count( $results ) && $executionOptions[ 'dry' ] == false )
        {
            return true;
        }
        return false;
    }

    /**
     * Attempt to remove content object 'image' attribute image variations by content object attribute
     *
     * @param $contentClassImageAttribute object of objects of class eZContentObjectAttribute
     * @return bool true if succcesfull, false otherwise
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

        // Do not process the orginal image alias
        unset( $aliasList['original'] );

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

                    // Fetch ezimage attributes that use $filepath
                    // Always returns current attribute (array of $contentObjectAttributeID and $contentObjectAttributeVersion)
                    $dbResult = eZImageFile::fetchImageAttributesByFilepath( $filepath, $contentObjectAttributeID );
                    $dbResultCount = count( $dbResult );
                    // Check if there are the attributes.
                    if ( $dbResultCount > 0 )
                    {
                        $doNotDelete = true;
                        foreach ( $dbResult as $res )
                        {
                            // We only look results where the version matches
                            if ( $res['version'] == $contentObjectAttributeVersion )
                            {
                                // If more than one result has been returned, it means
                                // that another version is using the same image,
                                // and we should not delete this file
                                if ( $dbResultCount > 1 )
                                {
                                    continue;
                                }
                                // Only one result means that the current attribute
                                // & version are the only ones using this image,
                                // and it can be removed
                                else
                                {
                                    $doNotDelete = false;
                                }
                            }

                            eZImageFile::appendFilepath( $res['id'], $filepath, true );
                        }
                    }

                    // Calculate appropriate message to alter user with
                    if( $executionOptions[ 'dry' ] == true )
                    {
                        $message = "Dry run: Calculating removal of datatype " . $contentObjectAttribute->attribute( 'data_type_string' ) . "type image alias variation " . $filepath . "\n";
                    }
                    else
                    {
                        $message = "Removed datatype " . $contentObjectAttribute->attribute( 'data_type_string' ) . "type image alias variation " . $filepath . "\n";
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

        if( in_array( true, $results ) && count( $aliasList ) == count( $results ) && $executionOptions[ 'dry' ] == false )
        {
            return true;
        }
        return false;
    }

    /**
     * Trigger cli script iteration and verbose iteration message display
     *
     * @param $message string of message text to use to alert user of iteration message
     * @return void
     */
    static function scriptIterate( $message = '' )
    {
        if( $message != '' )
        {
            $cli = eZCLI::instance();
            $script = eZScript::instance();
            $executionOptions = self::executionOptions();
            if ( $executionOptions[ 'verbose' ] )
            {
                // Alter the user to what is happening at the moment
                $cli->output( $message );
            }
            // Alert the user to what is happening at the moment
            $script->iterate( $cli, true, $message );
        }
    }

    /**
     * Return current BCImageAlias static object property executionOptions an array of execution options
     *
     * @return array
     */
    static function executionOptions()
    {
        return self::$ExecutionOptions;
    }

    /// \privatesection
    static public $ExecutionOptions = array( 'verbose' => false, 'dry' => true );
}

?>
