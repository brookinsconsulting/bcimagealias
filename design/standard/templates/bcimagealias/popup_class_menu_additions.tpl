<hr />

<a id="bcimage-alias-create-by-node-menu-view" class="more" href="#" onmouseover="ezpopmenu_hide('BCImageAliasRemoveByNode'); ezpopmenu_showSubLevel( event, 'BCImageAliasCreateByNode', 'bcimage-alias-create-by-node-menu-view' ); return false;">{"Create image variations"|i18n("extension/bcimagealias/popupmenu")}</a>

<a id="bcimage-alias-remove-by-node-menu-view" class="more" href="#" onmouseover="ezpopmenu_hide('BCImageAliasCreateByNode'); ezpopmenu_showSubLevel( event, 'BCImageAliasRemoveByNode', 'bcimage-alias-remove-by-node-menu-view' ); return false;">{"Remove image variations"|i18n("extension/bcimagealias/popupmenu")}</a>

{* Create current siteacess node aliases *}
<form id="menu-form-createcurrentsiteaccessnodealiases" method="post" action={"/bcimagealias/create"|ezurl}>
  <input type="hidden" name="ContentObjectID" value="%objectID%" />
  <input type="hidden" name="NodeID" value="%nodeID%" />
  <input type="hidden" name="Children" value="false" />
  <input type="hidden" name="Regenerate" value="false" />
  <input type="hidden" name="CurrentSiteaccess" value="true" />
  <input type="hidden" name="CreateNodeAliases" value="x" />
</form>

{* Create related siteacess node aliases *}
<form id="menu-form-createrelatedsiteaccessnodealiases" method="post" action={"/bcimagealias/create"|ezurl}>
  <input type="hidden" name="ContentObjectID" value="%objectID%" />
  <input type="hidden" name="NodeID" value="%nodeID%" />
  <input type="hidden" name="Children" value="false" />
  <input type="hidden" name="Regenerate" value="false" />
  <input type="hidden" name="CurrentSiteaccess" value="false" />
  <input type="hidden" name="CreateNodeAliases" value="x" />
</form>

{* Create siteaccess node subtree aliases *}
<form id="menu-form-createcurrentsiteaccessnodesubtreealiases" method="post" action={"/bcimagealias/create"|ezurl}>
  <input type="hidden" name="ContentObjectID" value="%objectID%" />
  <input type="hidden" name="NodeID" value="%nodeID%" />
  <input type="hidden" name="Children" value="true" />
  <input type="hidden" name="Regenerate" value="false" />
  <input type="hidden" name="CurrentSiteaccess" value="true" />
  <input type="hidden" name="CreateNodeAliases" value="x" />
</form>

{* Create related siteaccess node subtree aliases *}
<form id="menu-form-createrelatedsiteaccessnodesubtreealiases" method="post" action={"/bcimagealias/create"|ezurl}>
  <input type="hidden" name="ContentObjectID" value="%objectID%" />
  <input type="hidden" name="NodeID" value="%nodeID%" />
  <input type="hidden" name="Children" value="true" />
  <input type="hidden" name="Regenerate" value="false" />
  <input type="hidden" name="CurrentSiteaccess" value="false" />
  <input type="hidden" name="CreateNodeAliases" value="x" />
</form>

{* Regenerate current siteacess node aliases *}
<form id="menu-form-regeneratecurrentsiteaccessnodealiases" method="post" action={"/bcimagealias/create"|ezurl}>
  <input type="hidden" name="ContentObjectID" value="%objectID%" />
  <input type="hidden" name="NodeID" value="%nodeID%" />
  <input type="hidden" name="Children" value="false" />
  <input type="hidden" name="Regenerate" value="true" />
  <input type="hidden" name="CurrentSiteaccess" value="true" />
  <input type="hidden" name="CreateNodeAliases" value="x" />
</form>

{* Regenerate related siteacess node aliases *}
<form id="menu-form-regeneraterelatedsiteaccessnodealiases" method="post" action={"/bcimagealias/create"|ezurl}>
  <input type="hidden" name="ContentObjectID" value="%objectID%" />
  <input type="hidden" name="NodeID" value="%nodeID%" />
  <input type="hidden" name="Children" value="false" />
  <input type="hidden" name="Regenerate" value="true" />
  <input type="hidden" name="CurrentSiteaccess" value="false" />
  <input type="hidden" name="CreateNodeAliases" value="x" />
</form>

{* Regenerate siteaccess node subtree aliases *}
<form id="menu-form-regeneratecurrentsiteaccessnodesubtreealiases" method="post" action={"/bcimagealias/create"|ezurl}>
  <input type="hidden" name="ContentObjectID" value="%objectID%" />
  <input type="hidden" name="NodeID" value="%nodeID%" />
  <input type="hidden" name="Children" value="true" />
  <input type="hidden" name="Regenerate" value="true" />
  <input type="hidden" name="CurrentSiteaccess" value="true" />
  <input type="hidden" name="CreateNodeAliases" value="x" />
</form>

{* Regenerate related siteaccess node subtree aliases *}
<form id="menu-form-regeneraterelatedsiteaccessnodesubtreealiases" method="post" action={"/bcimagealias/create"|ezurl}>
  <input type="hidden" name="ContentObjectID" value="%objectID%" />
  <input type="hidden" name="NodeID" value="%nodeID%" />
  <input type="hidden" name="Children" value="true" />
  <input type="hidden" name="Regenerate" value="true" />
  <input type="hidden" name="CurrentSiteaccess" value="false" />
  <input type="hidden" name="CreateNodeAliases" value="x" />
</form>


{* Remove current siteacess node aliases *}
<form id="menu-form-removecurrentsiteaccessnodealiases" method="post" action={"/bcimagealias/remove"|ezurl}>
  <input type="hidden" name="ContentObjectID" value="%objectID%" />
  <input type="hidden" name="NodeID" value="%nodeID%" />
  <input type="hidden" name="Children" value="false" />
  <input type="hidden" name="Regenerate" value="false" />
  <input type="hidden" name="CurrentSiteaccess" value="true" />
  <input type="hidden" name="RemoveNodeAliases" value="x" />
</form>

{* Remove related siteacess node aliases *}
<form id="menu-form-removerelatedsiteaccessnodealiases" method="post" action={"/bcimagealias/remove"|ezurl}>
  <input type="hidden" name="ContentObjectID" value="%objectID%" />
  <input type="hidden" name="NodeID" value="%nodeID%" />
  <input type="hidden" name="Children" value="false" />
  <input type="hidden" name="Regenerate" value="false" />
  <input type="hidden" name="CurrentSiteaccess" value="false" />
  <input type="hidden" name="RemoveNodeAliases" value="x" />
</form>

{* Remove current siteaccess node subtree aliases *}
<form id="menu-form-removecurrentsiteaccessnodesubtreealiases" method="post" action={"/bcimagealias/remove"|ezurl}>
  <input type="hidden" name="ContentObjectID" value="%objectID%" />
  <input type="hidden" name="NodeID" value="%nodeID%" />
  <input type="hidden" name="Children" value="true" />
  <input type="hidden" name="Regenerate" value="false" />
  <input type="hidden" name="CurrentSiteaccess" value="true" />
  <input type="hidden" name="RemoveNodeAliases" value="x" />
</form>

{* Remove related siteaccess node subtree aliases *}
<form id="menu-form-removerelatedsiteaccessnodesubtreealiases" method="post" action={"/bcimagealias/remove"|ezurl}>
  <input type="hidden" name="ContentObjectID" value="%objectID%" />
  <input type="hidden" name="NodeID" value="%nodeID%" />
  <input type="hidden" name="Children" value="true" />
  <input type="hidden" name="Regenerate" value="false" />
  <input type="hidden" name="CurrentSiteaccess" value="true" />
  <input type="hidden" name="RemoveNodeAliases" value="x" />
</form>

