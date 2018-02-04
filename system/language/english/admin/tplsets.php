<?php
// $Id: tplsets.php 12064 2012-10-10 14:37:53Z skenow $
define('_MD_AM_TPLSETS', 'Templates');
define('_MD_AM_TPLSETS_DSC', 'Templates are sets of html/css files that render the screen layout of modules.');

//%%%%%% Template Manager %%%%%
define('_MD_TPLMAIN','Template Set Manager');
define('_MD_INSTALL','Install');
define('_MD_EDITTEMPLATE','Edit template file');
define('_MD_VIEWTEMPLATE','View template file');
define('_MD_FILENAME','File name');
define('_MD_FILEDESC','Description');
define('_MD_LASTMOD','Last modified');
define('_MD_FILEMOD','Last modified (file)');
define('_MD_FILECSS','CSS');
define('_MD_FILEHTML','HTML');
define('_MD_AM_BTOTADMIN', 'Back to template set manager');
define('_MD_RUSUREDELTH', 'Are you sure that you want to delete this template set and all its template data?');
define('_MD_RUSUREDELTPL', 'Are you sure that you want to delete this template data?');
define('_MD_PLZINSTALL', 'Press the button below to start installation');
define('_MD_PLZGENERATE', 'Press the button below to generate file(s)');
define('_MD_CLONETHEME','Clone a template set');
define('_MD_THEMENAME','Base template set');
define('_MD_NEWNAME','Enter new template set name');
define('_MD_IMPORT','Import');
define('_MD_RUSUREIMPT', 'Importing template data from the templates directory will overwrite your changes in database.<br />Click "Import" to proceed.');
define('_MD_THMSETNAME','Name');
define('_MD_CREATED','Created');
define('_MD_SKIN','Skin');
define('_MD_TEMPLATES','Templates');
define('_MD_EDITSKIN','Edit skin');
define('_MD_NOFILE','No File');
define('_MD_VIEW','View');
define('_MD_COPYDEFAULT','Copy default file');
define('_MD_DLDEFAULT','Download default file');
define('_MD_VIEWDEFAULT','View default template');
define('_MD_DOWNLOAD','Download');
define('_MD_UPLOAD','Upload');
define('_MD_GENERATE','Generate');
define('_MD_CHOOSEFILE', 'Choose file to upload');
define('_MD_UPWILLREPLACE', 'Uploading this file will overwrite the data in database!');
define('_MD_UPLOADTAR', 'Upload a template set');
define('_MD_CHOOSETAR', 'Choose a template set package to upload');
define('_MD_ONLYTAR', 'Must be a tar.gz/.tar file with a valid ImpressCMS template set structure');
define('_MD_NTHEMENAME', 'New template set name');
define('_MD_ENTERTH', 'Enter a template set name for this package. Leave it blank for automatic detection.');
define('_MD_TITLE','Title');
define('_MD_CONTENT','Content');
define('_MD_ACTION','Action');
define('_MD_DEFAULTTHEME','Your site uses this template set as default');
define('_MD_AM_ERRTHEME', 'The following template sets have no valid skin files data. Press delete to remove data related to the template set.');
define('_MD_SKINIMGS','Skin image files');
define('_MD_EDITSKINIMG','Edit skin image files');
define('_MD_IMGFILE','File name');
define('_MD_IMGNEWFILE','Upload new file');
define('_MD_IMGDELETE','Delete');
define('_MD_ADDSKINIMG','Add skin image file');
define('_MD_BLOCKHTML', 'Block HTML');
define('_MD_IMAGES', 'Images');
define('_MD_NOZLIB', 'Zlib support must be enabled on your server');
define('_MD_LASTIMP', 'Last Imported');
define('_MD_FILENEWER', 'A newer file that has not been imported yet exists under the <b>templates</b> directory.');
define('_MD_FILEIMPORT', 'An older file that has not been imported yet exists under the <b>templates</b> directory.');
define('_MD_FILEGENER', 'Template file does not exist. It can be generated (copied from the <b>default</b> template), uploaded, or imported from the <b>templates</b> directory.');

// Added in 1.3
define('_MD_TPLSET_DEFAULT_NOEDIT', 'Default template files cannot be edited.');
define('_MD_TPLSET_INSERT_FAILED', 'Could not insert template file %s to the database.');
define("_MD_TPLSET_TEMPLATE_NOTEXIST", 'Selected template (ID: %s) does not exist');
define("_MD_TPLSET_DELETE_FAIL", 'Could not delete %s from the database.');
define("_MD_TPLSET_DEFAULT_NODELETE", 'Default template files cannot be deleted.');
define("_MD_TPLSET_DELETING", 'Deleting template files...');
define("_MD_TPLSET_DELETE_OK", "Template %s deleted");
define("_MD_TPLSET_UNIQUE_NAME", "Template set name must be a different name.");
define("_MD_TPLSET_EXISTS", "Template set %s already exists");
define("_MD_TPLSET_CREATE_FAILED", "Could not create template set %s");
define("_MD_TPLSET_COPY_FAILED", "Failed copying template %s");
define("_MD_TPLSET_COPY_OK", "Template %s copied");
define("_MD_TPLSET_CREATE_OK", "Template set %s created");
define("_MD_TPLSET_TPLFILES_NOTEXIST", 'Template files for %s do not exist');
define("_MD_TPLSET_FILE_NOTEXIST", 'Selected file does not exist');
define("_MD_TPLSET_INSERT_OK", 'Template %s added to the database.');
define("_MD_TPLSET_INSTALLING_BLOCKS", "Installing block template files");
define("_MD_TPLSET_BLOCK_INSERT_FAILED", "Could not insert block template %s to the database.");
define("_MD_TPLSET_BLOCK_INSERT_OK", "Block template %s added to the database");
define("_MD_TPLSET_TEMPLATE_ADDED",	'Module template files for template set %s generated and installed.');
define("_MD_TPLSET_NAME_NOT_BLANK", "Template name cannot be blank");
define("_MD_TPLSET_INVALID_NAME", "Template name contained invalid characters");
define("_MD_TPLSET_NOT_FOUND", 'Could not find %s in the default template set.');
define("_MD_TPLSET_IMGSET_CREATE_FAILED", "Could not create image set.");
define("_MD_TPLSET_IMGSET_CREATED", "Image set %s created.");
define("_MD_TPLSET_IMGSET_LINK_FAILED", "Failed linking image set to template set");
define("_MD_TPLSET_FILE_UNNECESSARY", 'Template file %s does not need to be installed (PHP files using this template file does not exist');
define("_MD_TPLSET_UPDATED", "Template file %s updated");
define("_MD_TPLSET_COMPILED", "Template file %s compiled");
define("_MD_TPLSET_IMPORT_FAILED", "Could not import file ");
define("_MD_TPLSET_DELETING_DATA", 'Deleting template set data...');
define("_MD_TPLSET_COPYING", "Copying template files...");
define("_MD_TPLSET_INSTALLING", "Installing module template files for template set %s");
define("_MD_TPLSET_CREATE_OWN", "In order to modify templates online, please create a clone of the <strong>default</strong> template set or upload your own template set package.");
define("_MD_TPLSET_STATUS", "Status");
define("_MD_TPLSET_ACTIONS", "Actions");