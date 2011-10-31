<?php
/**
 * File containing the BCImageAliasGenerateObjectImageAliasVariationsType class.
 *
 * @copyright Copyright (C) 1999-2011 Brookins Consulting. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2 (or later)
 * @version //autogentag//
 * @package extension/bcimagealias
 */

class BCImageAliasGenerateObjectImageAliasVariationsType extends eZWorkflowEventType {

    const WORKFLOW_TYPE_STRING = "bcimagealiasgenerateobjectimagealiasvariations";

    function BCImageAliasGenerateObjectImageAliasVariationsType() {
        $this->eZWorkflowEventType( self::WORKFLOW_TYPE_STRING, "BC ImageAlias - Generate Object Image Alias Variation Image Files" );
        /* define trigger here */
        $this->setTriggerTypes( array( 'content' => array( 'publish' => array( 'before' ) ) ) );
    }

    function execute($process, $event) {
        // Fetch parameters
        $parameters = $process->attribute( 'parameter_list' );
        $objectID = $parameters['object_id'];

	    // Fetch content object 
        $object = eZContentObject::fetch( $objectID );

        if ( !$object )
        {
            eZDebugSetting::writeError( 'extension-bcimagealias-generate-image-alias-variations-workflow-on-non-object',
                                        $parameters['object_id'],
                                        'BCImageAliasGenerateObjectImageAliasVariationsType::execute' );
            return eZWorkflowEventType::STATUS_WORKFLOW_CANCELLED;
        }

        $result = eZImageAlias::createByObject( $object );
 
        if( $result == true )
        {
            return eZWorkflowType::STATUS_ACCEPTED;
        }

        return eZWorkflowEventType::STATUS_WORKFLOW_CANCELLED;
    } 
}

eZWorkflowEventType::registerEventType( BCImageAliasGenerateObjectImageAliasVariationsType::WORKFLOW_TYPE_STRING, "BCImageAliasGenerateObjectImageAliasVariationsType" );

?>
