<script type="text/javascript">
menuArray['BCImageAlias'] = [];
menuArray['BCImageAlias']['depth'] = 1;
menuArray['BCImageAlias']['elements'] = [];
</script>

<hr />
<a id="menu-bcimagealias" class="more" href="#" onmouseover="ezpopmenu_showSubLevel( event, 'BCImageAlias', 'menu-bcimagealias' ); return false;">{'Image variations'|i18n( 'extension/bcimagealias/popupmenu' )}</a>

{* Create current siteacess node aliases *}
<form id="menu-form-contextmenu-createcurrentsiteaccessnodealiases" method="post" action={"/bcimagealias/create"|ezurl}>
  <input type="hidden" name="ContentObjectID" value="%objectID%" />
  <input type="hidden" name="NodeID" value="%nodeID%" />
  <input type="hidden" name="Children" value="false" />
  <input type="hidden" name="Regenerate" value="false" />
  <input type="hidden" name="CurrentSiteaccess" value="true" />
  <input type="hidden" name="CreateNodeAliases" value="x" />
  <input type="hidden" name="CurrentURL" value="%currentURL%" />
</form>

{* Create related siteacess node aliases *}
<form id="menu-form-contextmenu-createrelatedsiteaccessnodealiases" method="post" action={"/bcimagealias/create"|ezurl}>
  <input type="hidden" name="ContentObjectID" value="%objectID%" />
  <input type="hidden" name="NodeID" value="%nodeID%" />
  <input type="hidden" name="Children" value="false" />
  <input type="hidden" name="Regenerate" value="false" />
  <input type="hidden" name="CurrentSiteaccess" value="false" />
  <input type="hidden" name="CreateNodeAliases" value="x" />
  <input type="hidden" name="CurrentURL" value="%currentURL%" />
</form>

{* Create siteaccess node subtree aliases *}
<form id="menu-form-contextmenu-createcurrentsiteaccessnodesubtreealiases" method="post" action={"/bcimagealias/create"|ezurl}>
  <input type="hidden" name="ContentObjectID" value="%objectID%" />
  <input type="hidden" name="NodeID" value="%nodeID%" />
  <input type="hidden" name="Children" value="true" />
  <input type="hidden" name="Regenerate" value="false" />
  <input type="hidden" name="CurrentSiteaccess" value="true" />
  <input type="hidden" name="CreateNodeAliases" value="x" />
  <input type="hidden" name="CurrentURL" value="%currentURL%" />
</form>

{* Create related siteaccess node subtree aliases *}
<form id="menu-form-contextmenu-createrelatedsiteaccessnodesubtreealiases" method="post" action={"/bcimagealias/create"|ezurl}>
  <input type="hidden" name="ContentObjectID" value="%objectID%" />
  <input type="hidden" name="NodeID" value="%nodeID%" />
  <input type="hidden" name="Children" value="true" />
  <input type="hidden" name="Regenerate" value="false" />
  <input type="hidden" name="CurrentSiteaccess" value="false" />
  <input type="hidden" name="CreateNodeAliases" value="x" />
  <input type="hidden" name="CurrentURL" value="%currentURL%" />
</form>

{* Regenerate current siteacess node aliases *}
<form id="menu-form-contextmenu-regeneratecurrentsiteaccessnodealiases" method="post" action={"/bcimagealias/create"|ezurl}>
  <input type="hidden" name="ContentObjectID" value="%objectID%" />
  <input type="hidden" name="NodeID" value="%nodeID%" />
  <input type="hidden" name="Children" value="false" />
  <input type="hidden" name="Regenerate" value="true" />
  <input type="hidden" name="CurrentSiteaccess" value="true" />
  <input type="hidden" name="CreateNodeAliases" value="x" />
  <input type="hidden" name="CurrentURL" value="%currentURL%" />
</form>

{* Regenerate related siteacess node aliases *}
<form id="menu-form-contextmenu-regeneraterelatedsiteaccessnodealiases" method="post" action={"/bcimagealias/create"|ezurl}>
  <input type="hidden" name="ContentObjectID" value="%objectID%" />
  <input type="hidden" name="NodeID" value="%nodeID%" />
  <input type="hidden" name="Children" value="false" />
  <input type="hidden" name="Regenerate" value="true" />
  <input type="hidden" name="CurrentSiteaccess" value="false" />
  <input type="hidden" name="CreateNodeAliases" value="x" />
  <input type="hidden" name="CurrentURL" value="%currentURL%" />
</form>

{* Regenerate siteaccess node subtree aliases *}
<form id="menu-form-contextmenu-regeneratecurrentsiteaccessnodesubtreealiases" method="post" action={"/bcimagealias/create"|ezurl}>
  <input type="hidden" name="ContentObjectID" value="%objectID%" />
  <input type="hidden" name="NodeID" value="%nodeID%" />
  <input type="hidden" name="Children" value="true" />
  <input type="hidden" name="Regenerate" value="true" />
  <input type="hidden" name="CurrentSiteaccess" value="true" />
  <input type="hidden" name="CreateNodeAliases" value="x" />
  <input type="hidden" name="CurrentURL" value="%currentURL%" />
</form>

{* Regenerate related siteaccess node subtree aliases *}
<form id="menu-form-contextmenu-regeneraterelatedsiteaccessnodesubtreealiases" method="post" action={"/bcimagealias/create"|ezurl}>
  <input type="hidden" name="ContentObjectID" value="%objectID%" />
  <input type="hidden" name="NodeID" value="%nodeID%" />
  <input type="hidden" name="Children" value="true" />
  <input type="hidden" name="Regenerate" value="true" />
  <input type="hidden" name="CurrentSiteaccess" value="false" />
  <input type="hidden" name="CreateNodeAliases" value="x" />
  <input type="hidden" name="CurrentURL" value="%currentURL%" />
</form>


{* Remove current siteacess node aliases *}
<form id="menu-form-contextmenu-removecurrentsiteaccessnodealiases" method="post" action={"/bcimagealias/remove"|ezurl}>
  <input type="hidden" name="ContentObjectID" value="%objectID%" />
  <input type="hidden" name="NodeID" value="%nodeID%" />
  <input type="hidden" name="Children" value="false" />
  <input type="hidden" name="Regenerate" value="false" />
  <input type="hidden" name="CurrentSiteaccess" value="true" />
  <input type="hidden" name="RemoveNodeAliases" value="x" />
  <input type="hidden" name="CurrentURL" value="%currentURL%" />
</form>

{* Remove related siteacess node aliases *}
<form id="menu-form-contextmenu-removerelatedsiteaccessnodealiases" method="post" action={"/bcimagealias/remove"|ezurl}>
  <input type="hidden" name="ContentObjectID" value="%objectID%" />
  <input type="hidden" name="NodeID" value="%nodeID%" />
  <input type="hidden" name="Children" value="false" />
  <input type="hidden" name="Regenerate" value="false" />
  <input type="hidden" name="CurrentSiteaccess" value="false" />
  <input type="hidden" name="RemoveNodeAliases" value="x" />
  <input type="hidden" name="CurrentURL" value="%currentURL%" />
</form>

{* Remove current siteaccess node subtree aliases *}
<form id="menu-form-contextmenu-removecurrentsiteaccessnodesubtreealiases" method="post" action={"/bcimagealias/remove"|ezurl}>
  <input type="hidden" name="ContentObjectID" value="%objectID%" />
  <input type="hidden" name="NodeID" value="%nodeID%" />
  <input type="hidden" name="Children" value="true" />
  <input type="hidden" name="Regenerate" value="false" />
  <input type="hidden" name="CurrentSiteaccess" value="true" />
  <input type="hidden" name="RemoveNodeAliases" value="x" />
  <input type="hidden" name="CurrentURL" value="%currentURL%" />
</form>

{* Remove related siteaccess node subtree aliases *}
<form id="menu-form-contextmenu-removerelatedsiteaccessnodesubtreealiases" method="post" action={"/bcimagealias/remove"|ezurl}>
  <input type="hidden" name="ContentObjectID" value="%objectID%" />
  <input type="hidden" name="NodeID" value="%nodeID%" />
  <input type="hidden" name="Children" value="true" />
  <input type="hidden" name="Regenerate" value="false" />
  <input type="hidden" name="CurrentSiteaccess" value="true" />
  <input type="hidden" name="RemoveNodeAliases" value="x" />
  <input type="hidden" name="CurrentURL" value="%currentURL%" />
</form>

