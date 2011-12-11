<?php /* #?ini charset="utf-8"?
# eZ Publish configuration file for bcimagealias extension

# NOTE: It is not recommended to edit this files directly, instead
#       a file in override should be created for setting the
#       values that is required for your site. Create
#       a file called settings/override/bcimagealias.ini.append.php

[BCImageAliasSettings]
ImageDataTypeStringList[]
ImageDataTypeStringList[]=ezimage
# Uncomment and customize the following line to support custom image datatypes
# ImageDataTypeStringList[]=ezprofileimage
# ImageDataTypeStringList[]=ezimageextended

# Regenerate all image alias image variation image files for all image aliases not just variations which which do not yet exist.
# WorkflowEventRegenerateAliasImageVariations=enabled
WorkflowEventRegenerateAliasImageVariations=disabled

# End WorkflowEvent execution fatally to allow developer to review execution troubleshooting output. Enables display of execution troubleshooting output.
# WorkflowEventTroubleshootAliasImageVariationCreation=enabled
WorkflowEventTroubleshootAliasImageVariationCreation=disabled

# Workflow verbose execution level setting. Use only with above setting enabled, otherwise ignored. Values supported are 1, 2, 3, 4, 5
WorkflowEventTroubleshootAliasImageVariationCreationLevel=1

# Create all image alias image variations for current siteaccess (if set to 'enabled')
# Create all image alias image variations for all related siteaccesses (if set to 'disabled')
WorkflowEventCurrentSiteAccessAliasImageVariationCreation=disabled

# Create all image aliases under object main node and all child nodes
WorkflowEventSubtreeImageAliasImageVariationCreation=enabled

*/ ?>
