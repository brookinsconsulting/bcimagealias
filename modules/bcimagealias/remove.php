<?php
/**
 * File containing the bcimagealias/remove module view.
 *
 * @copyright Copyright (C) 1999 - 2011 Brookins Consulting. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2 (or later)
 * @version //autogentag//
 * @package bcimagealias
 */

/**
 * Default module parameters
 */
$Module = $Params['Module'];

/**
 * Default parent node ID, root node ID setting
 */
$defaultNodeID = eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'RootNode' );

/**
 * Default parameter to remove node child image aliases
 */
$defaultRemoveChildNodeAliases = true;

/**
 * Default parameter to remove current siteacess image aliases
 */
$defaultRemoveCurrentSiteacessAliases = true;
$defaultRegenerateCurrentSiteacessAliases = true;

/**
 * Default class instances
 */
$http = eZHTTPTool::instance();

// General script options
$executionOptions = array( 'verbose' => false, 'dry' => false, 'iterate' => false,
                           'force' => $defaultRegenerateCurrentSiteacessAliases, 'troubleshoot' => false, 'troubleshootLevel' => 0,
                           'current-siteaccess' => $defaultRemoveCurrentSiteacessAliases );

/**
 * Request parameters
 *   'node-id' => array( 2 ), // Defaults to remove nodes as primary content tree node_id of 2
 *   'children' => true, // Defaults to remove child node image aliases
 *   'redirect' => 'default', // Defaults to 'default' which prevents referer or node alias redirection
 *   'subtree-params' => array( 'MainNodeOnly' => true,
 *                              'Depth' => 4,
 *                              'SortBy' => array( 'depth', true ) ) // Defaults to array of default subtree fetch parameters
 *                                                                   // for the ordering of child nodes fetched to be processed
 *
 */
 
// Subtree fetch parameters for the ordering of child nodes fetched to be processed

$parameters = array( 'node-id' => $defaultNodeID,
                     'children' => $defaultRemoveChildNodeAliases,
                     'redirect' => 'default',
                     'subtree-params' => array( 'MainNodeOnly' => true,
                                                'Depth' => 4,
                                                'SortBy' => array( 'depth', true ) ) );

eZSession::set( 'bcimagealias_remove_parameters', $parameters );

/**
 * Test for selected nodes from browse selection request to include in parameters
 */
if ( $http->hasPostVariable( 'SelectedNodeIDArray' ) &&
     !$http->hasPostVariable( 'BrowseCancelButton' ) )
{
    $parameters['node-id'] = is_array( $http->postVariable( 'SelectedNodeIDArray' ) ) ? current( $http->postVariable( 'SelectedNodeIDArray' ) ) : $http->postVariable( 'SelectedNodeIDArray' );
    $parameters['redirect'] =  'url_alias';

    eZSession::set( 'bcimagealias_remove_parameters', $parameters );
}

/**
 * Test for existance of module variable, 'NodeID'
 */
if ( isset( $Params[ 'NodeID' ] ) && $Params[ 'NodeID' ] != '' )
{
    $parameters['node-id'] =  $Params[ 'NodeID' ];
    $parameters['redirect'] =  'url_alias';

    eZSession::set( 'bcimagealias_remove_parameters', $parameters );
}
elseif( $http->hasVariable( 'NodeID' ) && $http->variable( 'NodeID' ) != '' )
{
    $parameters['node-id'] =  $http->variable( 'NodeID' );
    $parameters['redirect'] =  'url_alias';

    eZSession::set( 'bcimagealias_remove_parameters', $parameters );
}

/**
 * Test for existance of module variable, 'Children'
 */
if ( isset( $Params[ 'Children' ] ) && $Params[ 'Children' ] != '' )
{
    $parameters['children'] = $Params[ 'Children' ] == 'true' ? true : false;

    eZSession::set( 'bcimagealias_remove_parameters', $parameters );
}
elseif( $http->hasVariable( 'Children' ) && $http->variable( 'Children' ) != '' )
{
    $parameters['children'] = $http->variable( 'Children' ) == 'true' ? true : false;

    eZSession::set( 'bcimagealias_remove_parameters', $parameters );
}

/**
 * Test for existance of module variable, 'CurrentSiteaccess'
 */
if ( isset( $Params[ 'CurrentSiteaccess' ] ) && $Params[ 'CurrentSiteaccess' ] != '' && $Params[ 'CurrentSiteaccess' ] != 'true' )
{
     $executionOptions['current-siteaccess'] = $Params[ 'CurrentSiteaccess' ] == 'true' ? true : false;
}
elseif( $http->hasVariable( 'CurrentSiteaccess' ) && $http->variable( 'CurrentSiteaccess' ) != '' && $http->variable( 'CurrentSiteaccess' ) != 'true' )
{
     $executionOptions['current-siteaccess'] = $http->variable( 'CurrentSiteaccess' ) == 'true' ? true : false;
}

// Fetch and use parameters from session directly
$parameters = eZSession::get( 'bcimagealias_remove_parameters', $parameters );

// print_r($parameters); echo "\n\n"; die();

/**
 * Test for non existance of parameter variable, 'node-id' or invalid value and display browse view
 */
if ( !isset( $parameters[ 'node-id' ] ) || $parameters[ 'node-id' ] == '' )
{
    return redirectToContentBrowseModuleView( false, $Module );
}

// print_r($parameters); echo "\n\n"; die();

 
/**
 * Validate request to remove content
 */
if ( isset( $parameters[ 'node-id' ] ) && $parameters[ 'node-id' ] != '' && is_string( $parameters[ 'node-id' ] ) )
{
    // Fetch the provided node
    $node = eZContentObjectTreeNode::fetch( $parameters[ 'node-id' ] );

    if( is_object( $node ) )
    {     
    
        if( $parameters[ 'children' ] == true )
        {
            /**
             * Perform remove subtree content requests
             */
            $resultParameters = BCImageAlias::instance( $executionOptions )->removeByNodeSubtree( $node, $parameters['subtree-params'] );
        }
        else
        {
            /**
             * Perform remove content requests
             */
            $resultParameters = BCImageAlias::instance( $executionOptions )->removeByObject( $node->attribute( 'object' ), $parameters['subtree-params'] );
        }

        if( isset( $_SERVER['HTTP_REFERER'] ) && strpos( $_SERVER['HTTP_REFERER'], 'content/browse' ) === false )
        {
            $redirectUrl = $_SERVER['HTTP_REFERER'];
        }
        elseif( $node->hasAttribute( 'url_alias' ) && $node->attribute( 'url_alias' ) != '' )
        {
            $redirectUrl = $node->attribute( 'url_alias' );
            $redirectUrlInstance = eZURI::instance( $redirectUrl );
            $redirectUrlInstance->transformURI( $redirectUrl, false, 'full' );
        }
        else
        {
            $redirectUrl = '/content/view/full/' . $node->attribute( 'node_id' );
            $redirectUrlInstance = eZURI::instance( $redirectUrl );
            $redirectUrlInstance->transformURI( $redirectUrl, false, 'full' );
        }
        if( $parameters[ 'redirect' ] != 'default' )
        {
            //echo $redirectUrl; die();
            $http->redirect( $redirectUrl );
        }
        else
        {
            return redirectToContentBrowseModuleView( false, $Module );
        }
    }
} 

/**
 * Pass module view default template parameters
 *
 * @param array $parameters Array of parameters. Optional 
 * @param object $module Object of eZModule. Required  
 * @return string String of url to content/browse
 */
function redirectToContentBrowseModuleView( $parameters = false, $module )
{
    if( $parameters == false )
    {
        // Fetch and use parameters from session directly
        $parameters = eZSession::get( 'bcimagealias_remove_parameters', $parameters );
    }

    /**
     * Fetch array of container classes
     */
    $classes = eZPersistentObject::fetchObjectList( eZContentClass::definition(),
                                                    array( 'identifier' ), // field filters
                                                    array( 'is_container' => 1 ), // conds
                                                    null, // sort
                                                    null, // limit
                                                    false ); // as object

    /**
     * Prepare array of allowed class identifiers based on above fetch results
     */
    $allowedClasses = array();
    foreach ( $classes as $class )
    {
        $allowedClasses[] = $class['identifier'];
    }

    /**
     * Return browse for node selection view limited to allowed classes
     */
    return eZContentBrowse::browse( array( 'action_name' => 'BCImageAliasRemoveAliasesAddNode',
                                           'from_page' => '/bcimagealias/remove',
                                           'class_array' => $allowedClasses,
                                           'persistent_data' => array( 'ParametersSerialized' => serialize( $parameters ) ) ), $module );
}

?>
