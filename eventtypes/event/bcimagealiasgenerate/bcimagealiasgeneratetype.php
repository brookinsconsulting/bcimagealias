<?php
/**
 * File containing the BCImageAliasGenerateType class.
 *
 * @copyright Copyright (C) 1999-2011 Brookins Consulting. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2 (or later)
 * @version //autogentag//
 * @package extension/bcimagealias
 */

class BCImageAliasGenerateType extends eZWorkflowEventType {

    const WORKFLOW_TYPE_STRING = "bcimagealiasgenerate";

    function BCImageAliasGenerateType() {
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
                                        'BCImageAliasGenerateType::execute' );
            return eZWorkflowEventType::STATUS_WORKFLOW_CANCELLED;
        }

        $scriptExecutionOptions = array( 'verbose' => false, 'dry' => false );

        $result = BCImageAlias::instance( $scriptExecutionOptions )->createByObject( $object );
 
        if( $result == true )
        {
            return eZWorkflowType::STATUS_ACCEPTED;
        }

        return eZWorkflowEventType::STATUS_WORKFLOW_CANCELLED;
    } 
}

eZWorkflowEventType::registerEventType( BCImageAliasGenerateType::WORKFLOW_TYPE_STRING, "BCImageAliasGenerateType" );

?>
