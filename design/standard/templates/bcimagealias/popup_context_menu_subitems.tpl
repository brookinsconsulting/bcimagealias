{def $current_user_context_menu_subitems=fetch( 'user', 'current_user' )
     $bcimagealias_function_access_create=fetch( 'user', 'has_access_to',
                                                 hash( 'module',   'bcimagealias',
                                                       'function', 'create',
                                                       'user_id',  $current_user_context_menu_subitems.contentobject_id ) )
     $bcimagealias_function_access_remove=fetch( 'user', 'has_access_to',
                                                 hash( 'module',   'bcimagealias',
                                                       'function', 'remove',
                                                       'user_id',  $current_user_context_menu_subitems.contentobject_id ) )}
{if or( $bcimagealias_function_access_create, $bcimagealias_function_access_remove )}
<script type="text/javascript">
menuArray['BCImageAlias'] = [];
menuArray['BCImageAlias']['depth'] = 1;
menuArray['BCImageAlias']['elements'] = [];
</script>

<hr />
<a id="child-menu-bcimagealias" class="more" href="#" onmouseover="ezpopmenu_showSubLevel( event, 'BCImageAlias', 'child-menu-bcimagealias' ); return false;">{'BC ImageAlias'|i18n( 'extension/bcimagealias/popupmenu' )}</a>
{/if}
