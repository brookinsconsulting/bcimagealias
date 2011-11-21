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

# Generate all image alias image variation image files for all image aliases not just variations which which do not yet exist.
# WorkflowEventForceAliasImageVariationGeneration=enabled
WorkflowEventForceAliasImageVariationGeneration=disabled

# End WorkflowEvent execution fatally to allow developer to review execution troubleshooting output. Enables display of execution troubleshooting output.
# WorkflowEventTroubleshootAliasImageVariationGeneration=enabled
WorkflowEventTroubleshootAliasImageVariationGeneration=disabled

*/ ?>
