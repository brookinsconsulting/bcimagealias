<?php
/**
 * File containing the BCImageAliasGenerateObjectImageVariationsType class.
 *
 * @copyright Copyright (C) 1999-2011 Brookins Consulting. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2 (or later)
 * @version //autogentag//
 * @package bcimagealias
 */

class BCImageAliasGenerateObjectImageVariationsType extends eZWorkflowEventType
{
    /**
     * Workflow Event Type String
     */
    const WORKFLOW_TYPE_STRING = "bcimagealiasgenerateobjectimagevariations";

    /**
     * Default constructor
     */
    function BCImageAliasGenerateObjectImageVariationsType()
    {
        /**
         * Define workflow event type. This assigns the name of the workflow event within the eZ Publish administration module views
         */
        $this->eZWorkflowEventType( self::WORKFLOW_TYPE_STRING, "BC ImageAlias - Generate Object Image Alias Variation Image Files" );

        /**
         * Define trigger type. This workflow event requires the following to 'content, after, publish'
         */
        $this->setTriggerTypes( array( 'content' => array( 'publish' => array( 'after' ) ) ) );
    }

    /**
     * Workflow Event Type execute method
     */
    function execute( $process, $event )
    {
        /**
         * Fetch workflow process parameters
         */
        $parameters = $process->attribute( 'parameter_list' );
        $objectID = $parameters['object_id'];
        $version = $parameters['version'];

        /**
         * Fetch workflow event execution settings
         */
        $workflowEventForceGeneration = eZINI::instance( 'bcimagealias.ini' )->variable( 'BCImageAliasSettings', 'WorkflowEventForceAliasImageVariationGeneration' ) == 'enabled' ? true : false;
        $workflowEventTroubleshootGeneration = eZINI::instance( 'bcimagealias.ini' )->variable( 'BCImageAliasSettings', 'WorkflowEventTroubleshootAliasImageVariationGeneration' ) == 'enabled' ? true : false;
        $workflowEventCurrentSiteAccessGeneration = eZINI::instance( 'bcimagealias.ini' )->variable( 'BCImageAliasSettings', 'WorkflowEventCurrentSiteAccessAliasImageVariationGeneration' ) == 'enabled' ? true : false;

        /**
         * BCImageAlias execution parameters
         */
        $BCImageAliasExecutionParams = array( 'verbose' => false, 'dry' => false, 'iterate' => false,
                                              'force' => $workflowEventForceGeneration,
                                              'troubleshoot' => $workflowEventTroubleshootGeneration,
                                              'troubleshootLevel' => 1,
                                              'current-siteaccess' => $workflowEventCurrentSiteAccessGeneration );

        /**
         * Optional debug output
         */
        if( $BCImageAliasExecutionParams['troubleshoot'] == true )
        {
            echo "Workflow parameters: \n\n";
            print_r( $parameters ); echo "\n\n";
        }

        /**
         * Fetch content object
         */
        $object = eZContentObject::fetch( $objectID, $version );

        /**
         * Test for the rare chance we would not have been given an object. Terminate workflow event execution after writing debug error report
         */
        if( !$object )
        {
            eZDebugSetting::writeError( 'extension-bcimagealias-generate-image-alias-variations-workflow-on-non-object',
                                        $objectID,
                                        'BCImageAliasGenerateObjectImageVariationsType::execute' );
            return eZWorkflowEventType::STATUS_WORKFLOW_CANCELLED;
        }

        /**
         * Generate image alias image variation image files by content object
         */
        $result = BCImageAlias::instance( $BCImageAliasExecutionParams )->createByObject( $object );

        /**
         * Optional debug output
         */
        if( $BCImageAliasExecutionParams['troubleshoot'] == true )
        {
            die("\nTroubleshooting exection option enabled: Ending workflow event just before the end of it's execution to allow you to read related output.\n\n\n");
        }

        /**
         * Test result for failure to generate image aliases. Non-fatal workflow event execution result. Write debug error report just in case this is a problem
         */
        if( $result == false )
        {
            eZDebugSetting::writeError( 'extension-bcimagealias-generate-image-alias-variations-workflow-object-failure-to-create',
                                        $objectID,
                                        'BCImageAliasGenerateObjectImageVariationsType::execute' );
        }

        /**
         * Return default succesful workflow event status code, by default, regardless of results of execution, always.
         * Image alias image variation image files may not always need to be generated. Also returning any other status
         * will result in problems with the succesfull and normal completion of the workflow event process
         */
        return eZWorkflowType::STATUS_ACCEPTED;
    } 
}

/**
 * Register workflow event type class BCImageAliasGenerateObjectImageVariationsType
 */
eZWorkflowEventType::registerEventType( BCImageAliasGenerateObjectImageVariationsType::WORKFLOW_TYPE_STRING, "BCImageAliasGenerateObjectImageVariationsType" );

?>
