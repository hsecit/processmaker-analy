<?php

use ProcessMaker\BusinessModel\WebEntry;
use ProcessMaker\Core\JobsManager;
use ProcessMaker\Model\Delegation;
use ProcessMaker\Model\Process;
use ProcessMaker\Validation\MySQL57;

CLI::taskName('info');
CLI::taskDescription(<<<EOT
Print information about the current system and any specified workspaces.

  If no workspace is specified, show information about all available workspaces
EOT
);
CLI::taskArg('workspace-name', true, true);
CLI::taskRun("run_info");

CLI::taskName('workspace-backup');
CLI::taskDescription(<<<EOT
  Backup the specified workspace to a file.

  BACKUP-FILE is the backup filename which will be created. If it contains
  slashes, it will be treated as a path and filename, either absolute or relative.
  Otherwise, it will be treated as a filename inside the "shared/backups" directory.
  If no BACKUP-FILE is specified, it will use the workspace name as the filename.

  A backup archive will contain all information about the specified workspace
  so that it can be restored later. The archive includes a database dump and
  all the workspace files.
EOT
);
CLI::taskArg('workspace', false);
CLI::taskArg('backup-file', true);
CLI::taskOpt("filesize", "Split the backup file in multiple files which are compressed. The maximum size of these files is set to MAX-SIZE in megabytes. If MAX-SIZE is not set, then it is 1000 megabytes by default. It may be necessary to use this option if using a 32 bit Linux/UNIX system which limits its maximum file size to 2GB. This option does not work on Windows systems.", "s:", "filesize=");
CLI::taskRun("run_workspace_backup");

CLI::taskName('workspace-restore');
CLI::taskDescription(<<<EOT
  Restore a workspace from a backup file

  BACKUP-FILE is the backup filename. If it contains slashes, it will be
  treated as a path and filename, either absolute or relative. Otherwise, it
  will be treated as a filename inside the 'shared/backups' directory.

  Specify the WORKSPACE to restore to a different workspace name. Otherwise,
  it will restore to the same workspace name as the original backup.
EOT
);
CLI::taskArg('backup-file', false);
CLI::taskArg('workspace', true);
CLI::taskOpt("overwrite", "If a workspace already exists, overwrite it.", "o", "overwrite");
CLI::taskOpt("info", "Show information about backup file, but do not restore any workspaces.", "i");
CLI::taskOpt("multiple", "Restore from multiple compressed backup files which are numbered.", "m");
CLI::taskOpt("workspace", "Specify which workspace to restore if multiple workspaces are present in the backup file.
        Ex: -wworkflow.", "w:", "workspace=");
CLI::taskOpt("lang", "Specify the language which will be used to rebuild the case cache list. If this option isn't included, then 'en' (English) will be used by default.", "l:", "lang=");
CLI::taskOpt("port", "Specify the port number used by MySQL. If not specified, then the port 3306 will be used by default.", "p:");
CLI::taskRun("run_workspace_restore");

CLI::taskName('cacheview-repair');
CLI::taskDescription(<<<EOT
  Create and populate the APP_CACHE_VIEW table

  Specify the workspaces whose cases cache should be repaired. If no workspace
  is specified, then the cases will be repaired on all available workspaces.

  In order to improve the performance, ProcessMaker includes a cache of cases
  in the table APP_CACHE_VIEW. This table must be in sync with the database
  to present the correct information in the cases inbox. This command will
  create the table and populate it with the right information. This only needs
  to be used after upgrading ProcessMaker or if the cases inbox is out of sync.
EOT
);
CLI::taskArg('workspace', true, true);
CLI::taskOpt("lang", "Specify the language to rebuild the case cache list. If not specified, then 'en' (English) will be used by default.\n        Ex: -lfr (French) Ex: --lang=zh-CN (Mainland Chinese)", "l:", "lang=");
CLI::taskRun("run_cacheview_upgrade");

CLI::taskName('database-upgrade');
CLI::taskDescription(<<<EOT
  Upgrade or repair the database schema to match the latest version

  Specify the workspaces whose database schema should be upgraded or repaired.
  If no workspace is specified, then the database schema will be upgraded or
  repaired on all available workspaces.

  This command will read the system schema and attempt to modify the workspaces
  tables to match this new schema. Use this command to fix corrupted database
  schemas or after ProcessMaker has been upgraded, so the database schemas will
  be changed to match the new ProcessMaker code.
EOT
);
CLI::taskArg('workspace', true, true);
CLI::taskRun("run_database_upgrade");

CLI::taskName('plugins-database-upgrade');
CLI::taskDescription(<<<EOT
  Upgrade or repair the database schema for plugins to match the latest version

  Specify the workspaces whose database schema should be upgraded or repaired
  for plugins. If no workspace is specified, then the database schema will be
  upgraded or repaired on all available workspaces.

  This is the same as database-upgrade but it works with schemas provided
  by plugins. This is useful if plugins are installed that include
  database schemas.
EOT
);
CLI::taskArg('workspace', true, true);
CLI::taskRun("run_plugins_database_upgrade");

CLI::taskName('translation-repair');
CLI::taskDescription(<<<EOT
  Upgrade or repair translations for the specified workspace(s).

  If no workspace is specified, the command will be run in all workspaces. More
  than one workspace can be specified.

  This command will go through each language installed in ProcessMaker and
  update the translations for the workspace(s) to match the current version of
  ProcessMaker.
EOT
);
CLI::taskArg('workspace-name', true, true);
CLI::taskOpt('noxml', 'If this option is enabled, the XML files will not be modified.', 'NoXml', 'no-xml');
CLI::taskOpt('nomafe', 'If this option is enabled, the Front End (BPMN Designer and Bootstrap Forms) translation file will not be modified.', 'NoMafe', 'no-mafe');
CLI::taskRun("run_translation_upgrade");

CLI::taskName('migrate-cases-folders');
CLI::taskDescription(<<<EOT
  Migrating cases folders of the workspaces

  Specify the WORKSPACE to migrate from a existing workspace.
EOT
);
//CLI::taskArg('workspace', true);
CLI::taskOpt("workspace", "Select the workspace whose case folders will be migrated, if multiple workspaces are present in the server.\n        Ex: -wworkflow.        Ex: --workspace=workflow", "w:", "workspace=");
CLI::taskRun("runStructureDirectories");

CLI::taskName('database-verify-consistency');
CLI::taskDescription(<<<EOT
  Verify that the database data is consistent so any database-upgrade operation will be executed flawlessly.

  Specify the workspaces whose database schema should be verified. If none are specified, then
  all available workspaces will be specified.

  This command will read the system schema and data in an attempt to verify the database
  integrity. Use this command to check the database data consistency before any costly
  database-upgrade operation is planned to be executed.
EOT
);
CLI::taskArg('workspace', true, true);
CLI::taskRun("run_database_verify_consistency");

CLI::taskName('database-verify-migration-consistency');
CLI::taskDescription(<<<EOT
  Verify that the already migrated data is consistent there was not
  data loss of any kind

  Specify the workspaces whose database schema should be verified.
  The workspace parameter is mandatory.

  This command will read the Cancelled, Completed, Inbox, My Inbox, participated
  unassigned data in an attempt to verify the database integrity. It's recommended
  to run this script after the migrate cases job has been executed.
EOT
);
CLI::taskArg('workspace', true, true);
CLI::taskRun("run_database_verify_migration_consistency");

CLI::taskName('migrate-itee-to-dummytask');
CLI::taskDescription(<<<EOT
  Migrate the Intermediate throw Email Event to Dummy task

  Specify the workspaces, the processes in this workspace will be updated.

  If no workspace is specified, the command will be run in all workspaces.
EOT
);
CLI::taskArg('workspace', true, true);
CLI::taskRun("run_migrate_itee_to_dummytask");


CLI::taskName('migrate-indexing-acv');
CLI::taskDescription(<<<EOT
  Migrate and populate the indexes for the new relation fields to avoid the use of APP_CACHE_VIEW table

  Specify the workspace, the self-service cases in this workspace will be updated.

  If no workspace is specified, the command will be running in all workspaces.
EOT
);
CLI::taskArg('workspace', true, true);
CLI::taskRun("run_migrate_indexing_acv");

CLI::taskName('migrate-content');
CLI::taskDescription(<<<EOT
  Migrating the content schema to match the latest version

  Specify the WORKSPACE to migrate from a existing workspace.

  If no workspace is specified, then the tables schema will be upgraded or
  migrate on all available workspaces.
EOT
);
CLI::taskArg('workspace', true, true);
CLI::taskOpt("lang", "Specify the language to migrate the content data. If not specified, then 'en' (English) will be used by default.\n        Ex: -lfr (French) Ex: --lang=zh-CN (Mainland Chinese)", "l:", "lang=");
CLI::taskRun("run_migrate_content");

CLI::taskName('migrate-plugins-singleton-information');
CLI::taskDescription(<<<EOT
  Migrating the content schema to match the latest version

  Specify the WORKSPACE to migrate from an existing workspace.

  If no workspace is specified, then the tables schema will be upgraded or
  migrated to all available workspaces.
EOT
);
CLI::taskArg('workspace', true, true);
CLI::taskRun("run_migrate_plugin");

CLI::taskName('migrate-self-service-value');
CLI::taskDescription(<<<EOT
  Migrate the Self-Service values to a new related table APP_ASSIGN_SELF_SERVICE_VALUE_GROUPS

  Specify the workspaces, the self-service cases in this workspace will be updated.

  If no workspace is specified, the command will be run in all workspaces.
EOT
);
CLI::taskArg('workspace', true, true);
CLI::taskRun("run_migrate_self_service_value");

/**
 * Complete the PRO_ID and USR_ID in the LIST_* tables.
 *
 * It calls the list_ids@cliListIds.php
 */
CLI::taskName('list-ids');
CLI::taskDescription(<<<EOT
    Complete the PRO_ID and USR_ID in the LIST_* tables.
EOT
);
CLI::taskOpt("lang", "", "lLANG", "lang=LANG");
CLI::taskArg('workspace');
CLI::taskRun("cliListIds");

/**
 * Upgrade the CONTENT table
 */
CLI::taskName('upgrade-content');
CLI::taskDescription(<<<EOT
    Upgrade the content table
EOT
);
CLI::taskArg('workspace');
CLI::taskRun("run_upgrade_content");

/**
 *
 */
CLI::taskName('regenerate-pmtable-classes');
CLI::taskDescription(<<<EOT
    Regenerate the class with incorrect reference

    This method recursively finds all PHP files that reference the path PATH_DATA 
    incorrectly, which is caused by importing processes where the data directory 
    of ProcessMaker has different routes. Modified files are backed up with the 
    extension '.backup' in the same directory.
EOT
);
CLI::taskArg('workspace');
CLI::taskRun("regenerate_pmtable_classes");


/**
 * Remove the DYN_CONTENT_HISTORY
 */
CLI::taskName('clear-dyn-content-history-data');
CLI::taskDescription(<<<EOT
    Clear History of Use data from APP_HISTORY table
EOT
);
CLI::taskArg('workspace');
CLI::taskRun("run_clear_dyn_content_history_data");

/**
 * Sync JSON definition of the Forms with Input Documents information
 */
CLI::taskName('sync-forms-with-info-from-input-documents');
CLI::taskDescription(<<<EOT
    Sync JSON definition of the Forms with Input Documents information
EOT
);
CLI::taskArg('workspace');
CLI::taskRun("run_sync_forms_with_info_from_input_documents");

/**
 * Remove the deprecated files
 */
CLI::taskName('remove-unused-files');
CLI::taskDescription(<<<EOT
    Remove the deprecated files.
EOT
);
CLI::taskRun("remove_deprecated_files");

/*********************************************************************/
CLI::taskName("check-queries-incompatibilities");
CLI::taskDescription(<<<EOT
  Check queries incompatibilities (MySQL 5.7) for the specified workspace(s).

  This command checks the queries incompatibilities (MySQL 5.7) in the specified workspace(s).

  If no workspace is specified, the command will be run in all workspaces.
  More than one workspace can be specified.
EOT
);
CLI::taskArg("workspace-name", true, true);
CLI::taskRun("run_check_queries_incompatibilities");
/*********************************************************************/

/**
 * This command executes "artisan" loading the workspace connection parameters
 */
CLI::taskName('artisan');
CLI::taskDescription(<<<EOT
    This command executes "artisan" loading the workspace parameters.
Example:
    ./processmaker artisan queue:work --workspace=workflow

To see other command options please refer to the artisan help. 
    php artisan --help
EOT
);
CLI::taskRun("run_artisan");

/**
 * Add a font to be used in Documents generation (TinyMCE editor and/or TCPDF library)
 */
CLI::taskName('documents-add-font');
CLI::taskDescription(<<<EOT
Add a font to be used in Documents generation (TinyMCE editor and/or TCPDF library).
EOT
);
CLI::taskOpt('type', <<<EOT
Can be "TrueType" or "TrueTypeUnicode", if the option is not specified the default value is "TrueType"
EOT
,'t', 'type=');
CLI::taskOpt('tinymce', <<<EOT
Can be "true" or "false", if the option is not specified the default value is "true". If the value is "false" the optional arguments [FRIENDLYNAME] [FONTPROPERTIES] are omitted. 
EOT
    ,'tm', 'tinymce=');
CLI::taskArg('fontFileName', false);
CLI::taskArg('friendlyName', true);
CLI::taskArg('fontProperties', true);
CLI::taskRun('documents_add_font');

/**
 * List the registered fonts
 */
CLI::taskName('documents-list-registered-fonts');
CLI::taskDescription(<<<EOT
List the registered fonts.
EOT
);
CLI::taskRun('documents_list_registered_fonts');

/**
 * Remove a font used in Documents generation (TinyMCE editor and/or TCPDF library)
 */
CLI::taskName('documents-remove-font');
CLI::taskDescription(<<<EOT
Remove a font used in Documents generation (TinyMCE editor and/or TCPDF library).
EOT
);
CLI::taskArg('fontFileName', false);
CLI::taskRun('documents_remove_font');

/**
 * Add +async option to scheduler commands in table SCHEDULER.
 */
CLI::taskName('add-async-option-to-scheduler-commands');
CLI::taskDescription(<<<EOT
    Add +async option to scheduler commands in table SCHEDULER.
EOT
);
CLI::taskArg('workspace');
CLI::taskRun('add_async_option_to_scheduler_commands');

/**
 * Convert Web Entries v1.0 to v2.0 for BPMN processes in order to deprecate the old version.
 */
CLI::taskName('convert-old-web-entries');
CLI::taskDescription(<<<EOT
Convert Web Entries v1.0 to v2.0 for BPMN processes in order to deprecate the old version.
EOT
);
CLI::taskRun('convert_old_web_entries');

/**
 * Populate the column APP_DELEGATION.DEL_TITLE with the case title APPLICATION.APP_TITLE
 */
CLI::taskName('migrate-case-title-to-threads');
CLI::taskDescription(<<<EOT
Populate the new column APPLICATION.APP_TITLE into the APP_DELEGATION table
EOT
);
CLI::taskArg('WORKSPACE', false);
CLI::taskArg('caseNumberFrom', true);
CLI::taskArg('caseNumberTo', true);
CLI::taskRun('migrate_case_title_to_threads');

/**
 * Function run_info
 * 
 * @param array $args
 * @param array $opts
 */
function run_info($args, $opts)
{
    WorkspaceTools::printSysInfo();

    //Check if the command is executed by a specific workspace
    $workspaces = get_workspaces_from_args($args);
    if (count($args) === 1) {
        $workspaces[0]->printMetadata(false);
    } else {
        foreach ($workspaces as $workspace) {
            echo "\n";
            passthru(PHP_BINARY . " processmaker info " . $workspace->name);
        }
    }
}

/**
 * We will upgrade the CONTENT table
 * If we apply the command for all workspaces, we will need to execute one by one by redefining the constants
 * @param string $args, workspaceName that we need to apply the upgrade-content
 * @param string $opts
 *
 * @return void
 */
function run_upgrade_content($args, $opts)
{
    //Check if the command is executed by a specific workspace
    if (count($args) === 1) {
        upgradeContent($args, $opts);
    } else {
        $workspaces = get_workspaces_from_args($args);
        foreach ($workspaces as $workspace) {
            passthru(PHP_BINARY . ' processmaker upgrade-content ' . $workspace->name);
        }
    }
}

/**
 * This function will upgrade the CONTENT table for a workspace
 * This function is executed only for one workspace
 * @param array $args, workspaceName that we will to apply the command
 * @param array $opts, we can send additional parameters
 *
 * @return void
 */
function upgradeContent($args, $opts)
{
    try {
        //Load the attributes for the workspace
        $arrayWorkspace = get_workspaces_from_args($args);
        //Loop, read all the attributes related to the one workspace
        $wsName = $arrayWorkspace[key($arrayWorkspace)]->name;
        Bootstrap::setConstantsRelatedWs($wsName);
        $workspaces = get_workspaces_from_args($args);
        foreach ($workspaces as $workspace) {
            try {
                G::outRes("Upgrading content for " . pakeColor::colorize($workspace->name, "INFO") . "\n");
                $workspace->upgradeContent($workspace->name, true);
            } catch (Exception $e) {
                G::outRes("Errors upgrading content of workspace " . CLI::info($workspace->name) . ": " . CLI::error($e->getMessage()) . "\n");
            }
        }
    } catch (Exception $e) {
        G::outRes(CLI::error($e->getMessage()) . "\n");
    }
}

/**
 * We will repair the translation in the languages defined in the workspace
 * Verify if we need to execute an external program for each workspace
 * If we apply the command for all workspaces, we will need to execute one by one by redefining the constants
 * @param string $args, workspaceName that we need to apply the database-upgrade
 * @param string $opts
 *
 * @return void
 */
function run_translation_upgrade($args, $opts)
{
    $noXml = array_key_exists('noxml', $opts) ? '--no-xml' : '';
    $noMafe = array_key_exists('nomafe', $opts) ? '--no-mafe' : '';
    if (!empty($noXml)) {
        $noMafe = ' ' . $noMafe;
    }
    //Check if the command is executed by a specific workspace
    if (count($args) === 1) {
        translation_upgrade($args, $opts);
    } else {
        $workspaces = get_workspaces_from_args($args);
        foreach ($workspaces as $workspace) {
            passthru(PHP_BINARY . ' processmaker translation-repair ' . $noXml . $noMafe . ' ' . $workspace->name);
        }
    }
}

/**
 * This function will regenerate the translation for a workspace
 * This function is executed only for one workspace
 * @param array $args, workspaceName that we will to apply the command
 * @param array $opts, noxml and nomafe flags
 *
 * @return void
 */
function translation_upgrade($args, $opts)
{
    try {
        //Load the attributes for the workspace
        $arrayWorkspace = get_workspaces_from_args($args);
        //Loop, read all the attributes related to the one workspace
        $wsName = $arrayWorkspace[key($arrayWorkspace)]->name;
        Bootstrap::setConstantsRelatedWs($wsName);
        $workspaces = get_workspaces_from_args($args);
        $flagUpdateXml = (!array_key_exists('noxml', $opts));
        $flagUpdateMafe = (!array_key_exists('nomafe', $opts));
        foreach ($workspaces as $workspace) {
            try {
                G::outRes("Upgrading translation for " . pakeColor::colorize($workspace->name, "INFO") . "\n");
                $workspace->upgradeTranslation($flagUpdateXml, $flagUpdateMafe);
            } catch (Exception $e) {
                G::outRes("Errors upgrading translation of workspace " . CLI::info($workspace->name) . ": " . CLI::error($e->getMessage()) . "\n");
            }
        }
    } catch (Exception $e) {
        G::outRes(CLI::error($e->getMessage()) . "\n");
    }
}

function run_cacheview_upgrade($args, $opts)
{
    $filter = new InputFilter();
    $opts = $filter->xssFilterHard($opts);
    $args = $filter->xssFilterHard($args);
    $workspaces = get_workspaces_from_args($args);
    $lang = array_key_exists("lang", $opts) ? $opts['lang'] : 'en';
    foreach ($workspaces as $workspace) {
        try {
            G::outRes("Upgrading cache view for " . pakeColor::colorize($workspace->name, "INFO") . "\n");
            $workspace->upgradeCacheView(true, false, $lang);
        } catch (Exception $e) {
            G::outRes("Errors upgrading cache view of workspace " . CLI::info($workspace->name) . ": " . CLI::error($e->getMessage()) . "\n");
        }
    }
}

function run_plugins_database_upgrade($args, $opts)
{
    $workspaces = get_workspaces_from_args($args);
    foreach ($workspaces as $workspace) {
        try {
            CLI::logging("Upgrading plugins database for " . CLI::info($workspace->name) . "\n");
            $workspace->upgradePluginsDatabase();
        } catch (Exception $e) {
            CLI::logging("Errors upgrading plugins database: " . CLI::error($e->getMessage()));
        }
    }
}

function run_database_export($args, $opts)
{
    if (count($args) < 2) {
        throw new Exception("Please provide a workspace name and a directory for export");
    }
    $workspace = new WorkspaceTools($args[0]);
    $workspace->exportDatabase($args[1]);
}

function run_database_import($args, $opts)
{
    throw new Exception("Not implemented");
}

/**
 * Check if we need to execute an external program for each workspace
 * If we apply the command for all workspaces we will need to execute one by one by redefining the constants
 * @param string $args, workspaceName that we need to apply the database-upgrade
 *
 * @return void
 */
function run_database_upgrade($args)
{
    //Check if the command is executed by a specific workspace
    if (count($args) === 1) {
        database_upgrade($args);
    } else {
        $workspaces = get_workspaces_from_args($args);
        foreach ($workspaces as $workspace) {
            passthru(PHP_BINARY . ' processmaker database-upgrade ' . $workspace->name);
        }
    }
}

function run_migrate_new_cases_lists($args, $opts)
{
    migrate_new_cases_lists("migrate", $args, $opts);
}

function run_migrate_counters($args, $opts)
{
    migrate_counters("migrate", $args);
}

function run_migrate_list_unassigned($args, $opts)
{
    migrate_list_unassigned("migrate", $args, $opts);
}

/**
 * This function is executed only by one workspace
 * @param array $args, workspaceName for to apply the database-upgrade
 *
 * @return void
 */
function database_upgrade($args)
{
    // Sanitize parameters sent
    $filter = new InputFilter();
    $args = $filter->xssFilterHard($args);

    // Load the attributes for the workspace
    $workspaces = get_workspaces_from_args($args);

    // Get the name of the first workspace
    $wsName = $workspaces[key($workspaces)]->name;

    // Initialize workspace values
    Bootstrap::setConstantsRelatedWs($wsName);

    // Print a informative message
    print_r("Upgrading database in " . pakeColor::colorize($wsName, "INFO") . "\n");

    // Loop to update the databases of all workspaces
    foreach ($workspaces as $workspace) {
        try {
            $workspace->upgradeDatabase();
            $workspace->close();
        } catch (Exception $e) {
            G::outRes("> Error: " . CLI::error($e->getMessage()) . "\n");
        }
    }
}

function delete_app_from_table($con, $tableName, $appUid, $col = "APP_UID")
{
    $stmt = $con->createStatement();
    $sql = "DELETE FROM " . $tableName . " WHERE " . $col . "='" . $appUid . "'";
    $rs = $stmt->executeQuery($sql, ResultSet::FETCHMODE_NUM);
}

function run_drafts_clean($args, $opts)
{
    echo "Cleaning drafts\n";

    if (count($args) < 1) {
        throw new Exception("Please specify a workspace name");
    }
    $workspace = $args[0];

    if (!file_exists(PATH_DB . $workspace . '/db.php')) {
        throw new Exception('Could not find workspace ' . $workspace);
    }

    $allDrafts = false;
    if (count($args) < 2) {
        echo "Cases older them this much days will be deleted (ENTER for all): ";
        $days = rtrim(fgets(STDIN), "\n");
        if ($days == "") {
            $allDrafts = true;
        }
    } else {
        $days = $args[1];
        if (strcmp($days, "all") == 0) {
            $allDrafts = true;
        }
    }

    if (!$allDrafts && (!is_numeric($days) || intval($days) <= 0)) {
        throw new Exception("Days value is not valid: " . $days);
    }

    if ($allDrafts) {
        echo "Removing all drafts\n";
    } else {
        echo "Removing drafts older than " . $days . " days\n";
    }

    /* Load the configuration from the workspace */
    require_once(PATH_DB . $workspace . '/db.php');
    require_once(PATH_THIRDPARTY . 'propel/Propel.php');

    PROPEL::Init(PATH_METHODS . 'dbConnections/rootDbConnections.php');
    $con = Propel::getConnection("root");

    $stmt = $con->createStatement();

    if (!$allDrafts) {
        $dateSql = "AND DATE_SUB(CURDATE(),INTERVAL " . $days . " DAY) >= APP_CREATE_DATE";
    } else {
        $dateSql = "";
    }
    /* Search for all the draft cases */
    $sql = "SELECT APP_UID FROM APPLICATION WHERE APP_STATUS='DRAFT'" . $dateSql;
    $appRows = $stmt->executeQuery($sql, ResultSet::FETCHMODE_ASSOC);

    /* Tables to remove the cases from */
    $tables = array(
        "APPLICATION",
        "APP_DELEGATION",
        "APP_CACHE_VIEW",
        "APP_THREAD",
        "APP_DOCUMENT",
        "APP_EVENT",
        "APP_HISTORY",
        "APP_MESSAGE"
    );

    echo "Found " . $appRows->getRecordCount() . " cases to remove";
    foreach ($appRows as $row) {
        echo ".";
        $appUid = $row['APP_UID'];
        foreach ($tables as $table) {
            delete_app_from_table($con, $table, $appUid);
        }
        delete_app_from_table($con, "CONTENT", $appUid, "CON_ID");
        if (file_exists(PATH_DB . $workspace . '/files/' . $appUid)) {
            echo "\nRemoving files from " . $appUid . "\n";
            G::rm_dir(PATH_DB . $workspace . '/files/' . $appUid);
        }
    }
    echo "\n";
}

function run_workspace_backup($args, $opts)
{
    $workspaces = array();
    if (sizeof($args) > 2) {
        $filename = array_pop($args);
        foreach ($args as $arg) {
            $workspaces[] = new WorkspaceTools($arg);
        }
    } elseif (sizeof($args) > 0) {
        $workspace = new WorkspaceTools($args[0]);
        $workspaces[] = $workspace;
        if (sizeof($args) == 2) {
            $filename = $args[1];
        } else {
            $filename = "{$workspace->name}.tar";
        }
    } else {
        throw new Exception("No workspace specified for backup");
    }


    foreach ($workspaces as $workspace) {
        if (!$workspace->workspaceExists()) {
            throw new Exception("Workspace '{$workspace->name}' not found");
        }
    }

    //If this is a relative path, put the file in the backups directory
    if (strpos($filename, "/") === false && strpos($filename, '\\') === false) {
        $filename = PATH_DATA . "backups/$filename";
    }
    CLI::logging("Backing up to $filename\n");

    $filesize = array_key_exists("filesize", $opts) ? $opts['filesize'] : -1;

    if ($filesize >= 0) {
        if (!Bootstrap::isLinuxOs()) {
            CLI::error("This is not a Linux enviroment, cannot use this filesize [-s] feature.\n");
            return;
        }
        $multipleBackup = new MultipleFilesBackup($filename, $filesize); //if filesize is 0 the default size will be took
        //using new method
        foreach ($workspaces as $workspace) {
            $multipleBackup->addToBackup($workspace);
        }
        $multipleBackup->letsBackup();
    } else {
        //ansient method to backup into one large file
        $backup = WorkspaceTools::createBackup($filename);

        foreach ($workspaces as $workspace) {
            $workspace->backup($backup);
        }
    }
    CLI::logging("\n");
    WorkspaceTools::printSysInfo();
    foreach ($workspaces as $workspace) {
        CLI::logging("\n");
        $workspace->printMetadata(false);
    }
}

function run_workspace_restore($args, $opts)
{
    if (sizeof($args) > 0) {
        $filename = $args[0];

        G::verifyPath(PATH_DATA . 'upgrade', true);

        if (isset($args[1]) && strlen($args[1]) >= 30) {
            eprintln("Invalid workspace name, insert a maximum of 30 characters.", 'red');
            return;
        }

        if (strpos($filename, "/") === false && strpos($filename, '\\') === false) {
            $filename = PATH_DATA . "backups/$filename";
            if (!file_exists($filename) && substr_compare($filename, ".tar", -4, 4, true) != 0) {
                $filename .= ".tar";
            }
        }
        $info = array_key_exists("info", $opts);
        $lang = array_key_exists("lang", $opts) ? $opts['lang'] : 'en';
        $port = array_key_exists("port", $opts) ? $opts['port'] : '';
        $optionMigrateHistoryData = [
        ];
        if ($info) {
            WorkspaceTools::getBackupInfo($filename);
        } else {
            CLI::logging("Restoring from $filename\n");
            $workspace = array_key_exists("workspace", $opts) ? $opts['workspace'] : null;
            $overwrite = array_key_exists("overwrite", $opts);
            $multiple = array_key_exists("multiple", $opts);
            $dstWorkspace = isset($args[1]) ? $args[1] : null;
            if (!empty($multiple)) {
                if (!Bootstrap::isLinuxOs()) {
                    CLI::error("This is not a Linux enviroment, cannot use this multiple [-m] feature.\n");
                    return;
                }
                MultipleFilesBackup::letsRestore($filename, $workspace, $dstWorkspace, $overwrite);
            } else {
                $anotherExtention = ".*"; //if there are files with and extra extention: e.g. <file>.tar.number
                $multiplefiles = glob($filename . $anotherExtention); // example: //shared/workflow_data/backups/myWorkspace.tar.*
                if (count($multiplefiles) > 0) {
                    CLI::error("Processmaker found these files: .\n");
                    foreach ($multiplefiles as $index => $value) {
                        CLI::logging($value . "\n");
                    }
                    CLI::error("Please, you should use -m parameter to restore them.\n");
                    return;
                }
                WorkspaceTools::restore($filename, $workspace, $dstWorkspace, $overwrite, $lang, $port, $optionMigrateHistoryData);
            }
        }
    } else {
        throw new Exception("No workspace specified for restore");
    }
}

/**
 * Migrating cases folders of the workspaces
 * 
 * @param array $command
 * @param array $args
 */
function runStructureDirectories($command, $args)
{
    $workspaces = get_workspaces_from_args($command);
    if (count($command) === 1) {
        try {
            $workspace = $workspaces[0];
            CLI::logging(": " . CLI::info($workspace->name) . "\n");
            $workspace->updateStructureDirectories($workspace->name);
            $workspace->close();
        } catch (Exception $e) {
            CLI::logging("Errors upgrading workspace " . CLI::info($workspace->name) . ": " . CLI::error($e->getMessage()) . "\n");
        }
    } else {
        $count = count($workspaces);
        $countWorkspace = 0;
        foreach ($workspaces as $index => $workspace) {
            $countWorkspace++;
            CLI::logging("Updating workspaces ($countWorkspace/$count)");
            passthru(PHP_BINARY . " processmaker migrate-cases-folders " . $workspace->name);
        }
    }
}

function run_database_verify_consistency($args, $opts)
{
    verifyAppCacheConsistency($args);
}

function run_database_verify_migration_consistency($args, $opts)
{
    verifyMigratedDataConsistency($args);
}

function verifyAppCacheConsistency($args)
{
    $workspaces = get_workspaces_from_args($args);
    foreach ($workspaces as $workspace) {
        verifyWorkspaceConsistency($workspace);
    }
}

function verifyWorkspaceConsistency($workspace)
{
    $isConsistent = true;
    print_r("Verifying data in workspace " . pakeColor::colorize($workspace->name, "INFO") . "\n");
    $inconsistentUsers = $workspace->hasMissingUsers();
    $inconsistentTasks = $workspace->hasMissingTasks();
    $inconsistentProcesses = $workspace->hasMissingProcesses();
    $inconsistentDelegations = $workspace->hasMissingAppDelegations();

    if ($inconsistentUsers || $inconsistentTasks || $inconsistentProcesses || $inconsistentDelegations) {
        $isConsistent = false;
    }
    return $isConsistent;
}

function verifyMigratedDataConsistency($args)
{
    $workspaces = get_workspaces_from_args($args);
    $inconsistentRecords = 0;
    foreach ($workspaces as $workspace) {
        print_r("Verifying data in workspace " . pakeColor::colorize($workspace->name, "INFO") . "\n");
        $lists = array(
            'LIST_CANCELLED',
            'LIST_COMPLETED',
            'LIST_INBOX',
            'LIST_PARTICIPATED_HISTORY',
            'LIST_PARTICIPATED_LAST',
            'LIST_MY_INBOX',
            'LIST_UNASSIGNED',
        );
        foreach ($lists as $list) {
            $inconsistentRecords += $workspace->verifyListData($list);
        }
    }
    return $inconsistentRecords;
}

function run_migrate_itee_to_dummytask($args, $opts)
{
    $filter = new InputFilter();
    $opts = $filter->xssFilterHard($opts);
    $args = $filter->xssFilterHard($args);
    $arrayWorkspace = get_workspaces_from_args($args);
    foreach ($arrayWorkspace as $workspace) {
        try {
            $ws = new WorkspaceTools($workspace->name);
            $res = $ws->migrateIteeToDummytask($workspace->name);
        } catch (Exception $e) {
            G::outRes("> Error: " . CLI::error($e->getMessage()) . "\n");
        }
    }
}

/**
 * Check if we need to execute an external program for each workspace
 * If we apply the command for all workspaces we will need to execute one by one by redefining the constants
 * @param string $args, workspaceName that we need to apply the database-upgrade
 * @param string $opts, specify the language
 *
 * @return void
 */
function run_migrate_content($args, $opts)
{
    //Check the additional parameters
    $lang = array_key_exists("lang", $opts) ? '--lang=' . $opts['lang'] : '--lang=' . SYS_LANG;
    //Check if the command is executed by a specific workspace
    if (count($args) === 1) {
        migrate_content($args, $opts);
    } else {
        $workspaces = get_workspaces_from_args($args);
        foreach ($workspaces as $workspace) {
            passthru(PHP_BINARY . ' processmaker migrate-content ' . $lang . ' ' . $workspace->name);
        }
    }
}

/**
 * This function is executed only by one workspace
 * @param array $args, workspaceName for to apply the migrate-content
 * @param array $opts, specify the language
 *
 * @return void
 */
function migrate_content($args, $opts)
{
    $filter = new InputFilter();
    $args = $filter->xssFilterHard($args);
    $workspaces = get_workspaces_from_args($args);
    $lang = array_key_exists("lang", $opts) ? $opts['lang'] : SYS_LANG;
    $start = microtime(true);
    //We defined the constants related the workspace
    $wsName = $workspaces[key($workspaces)]->name;
    Bootstrap::setConstantsRelatedWs($wsName);
    //Loop, read all the attributes related to the one workspace
    CLI::logging("> Optimizing content data...\n");
    foreach ($workspaces as $workspace) {
        print_r('Regenerating content in: ' . pakeColor::colorize($workspace->name, 'INFO') . "\n");
        CLI::logging("-> Regenerating content \n");
        $workspace->migrateContentRun($lang);
    }
    $stop = microtime(true);
    CLI::logging("<*>   Optimizing content data Process took " . ($stop - $start) . " seconds.\n");
}

function run_migrate_self_service_value($args, $opts)
{
    $filter = new InputFilter();
    $args = $filter->xssFilterHard($args);
    $workspaces = get_workspaces_from_args($args);
    $start = microtime(true);
    CLI::logging("> Optimizing Self-Service data...\n");
    foreach ($workspaces as $workspace) {
        print_r('Migrating records in: ' . pakeColor::colorize($workspace->name, 'INFO') . "\n");
        CLI::logging("-> Migrating Self-Service records \n");
        $workspace->migrateSelfServiceRecordsRun($workspace->name);
    }
    $stop = microtime(true);
    CLI::logging("<*>   Migrating Self-Service records Process took " . ($stop - $start) . " seconds.\n");
}

function run_migrate_indexing_acv($args, $opts)
{
    $filter = new InputFilter();
    $args = $filter->xssFilterHard($args);
    $workspaces = get_workspaces_from_args($args);
    $start = microtime(true);
    CLI::logging("> Migrating and populating indexing for avoiding the use of table APP_CACHE_VIEW...\n");
    foreach ($workspaces as $workspace) {
        print_r('Indexing for APP_CACHE_VIEW: ' . pakeColor::colorize($workspace->name, 'INFO') . "\n");
        $workspace->migratePopulateIndexingACV($workspace->name);
    }
    $stop = microtime(true);
    CLI::logging("<*>   Migrating and populating indexing for avoiding the use of table APP_CACHE_VIEW process took " . ($stop - $start) . " seconds.\n");
}

function run_migrate_plugin($args, $opts)
{
    $workspaces = get_workspaces_from_args($args);
    //Check if the command is executed by a specific workspace
    /** @var WorkspaceTools $workspace */
    if (count($workspaces) === 1) {
        $workspace = array_shift($workspaces);
        CLI::logging('Regenerating Singleton in: ' . pakeColor::colorize($workspace->name, 'INFO') . "\n");
        $workspace->migrateSingleton($workspace->name);
        CLI::logging("-> Regenerating Singleton \n");
    } else {
        CLI::logging("> Migrating and populating data...\n");
        $start = microtime(true);
        foreach ($workspaces as $workspace) {
            passthru(PHP_BINARY . ' processmaker migrate-plugins-singleton-information ' . $workspace->name);
        }
        $stop = microtime(true);
        CLI::logging("<*>   Migrating and populating data Singleton took " . ($stop - $start) . " seconds.\n");
    }
}

/**
 * This method recursively finds all PHP files that reference the path PATH_DATA
 * incorrectly, which is caused by importing processes where the data directory
 * of ProcessMaker has different routes. Modified files are backed up with the
 * extension '.backup' in the same directory.
 *
 * @param array $args
 * @param array $opts
 * @throws Exception
 * @return void
 */
function regenerate_pmtable_classes($args, $opts)
{
    if (sizeof($args) > 0) {
        $start = microtime(true);
        CLI::logging("> Updating generated class files for PM Tables...\n");

        Bootstrap::setConstantsRelatedWs($args[0]);
        $workspaceTools = new WorkspaceTools($args[0]);
        $workspaceTools->fixReferencePathFiles(PATH_DATA_SITE . "classes", PATH_DATA);

        $stop = microtime(true);
        CLI::logging("<*>   Updating generated class files for PM Tables took " . ($stop - $start) . " seconds.\n");
    } else {
        throw new Exception("No workspace specified for updating generated class files.");
    }
}


/**
 * Will be clean the History of use from the table
 * Will be remove the DYN_CONTENT_HISTORY from APP_HISTORY
 *
 * @param array $args
 * @param array $opts
 *
 * @return void
 */
function run_clear_dyn_content_history_data($args, $opts)
{
    $workspaces = get_workspaces_from_args($args);
    $start = microtime(true);
    CLI::logging("> Cleaning history data from APP_HISTORY...\n");
    foreach ($workspaces as $workspace) {
        CLI::logging('Remove history of use: ' . pakeColor::colorize($workspace->name, 'INFO') . "\n");
        $workspace->clearDynContentHistoryData(true);
    }
    $stop = microtime(true);
    CLI::logging("<*>   Cleaning history data from APP_HISTORY process took " . ($stop - $start) . " seconds.\n");
}

/**
 * Sync JSON definition of the Forms with Input Documents information
 *
 * @param array $args
 * @param array $opts
 *
 * @return void
 * @see workflow/engine/bin/tasks/cliWorkspaces.php CLI::taskRun()
 */
function run_sync_forms_with_info_from_input_documents($args, $opts)
{
    if (count($args) === 1) {
        //This variable is not defined and does not involve its value in this
        //task, it is removed at the end of the method.
        $_SERVER['REQUEST_URI'] = '';
        if (!defined('SYS_SKIN')) {
            $config = System::getSystemConfiguration();
            define('SYS_SKIN', $config['default_skin']);
        }
        CLI::logging('Sync JSON definition of the Forms with Input Documents information from workspace: ' . pakeColor::colorize($args[0], 'INFO') . "\n");
        $workspaceTools = new WorkspaceTools($args[0]);
        $workspaceTools->syncFormsWithInputDocumentInfo();
        unset($_SERVER['REQUEST_URI']);
    } else {
        $workspaces = get_workspaces_from_args($args);
        foreach ($workspaces as $workspace) {
            passthru(PHP_BINARY . ' processmaker sync-forms-with-info-from-input-documents ' .
                $workspace->name);
        }
    }
}

/**
 * Remove the deprecated files
 *
 * @return void
 * @see workflow/engine/bin/tasks/cliWorkspaces.php CLI::taskRun()
 * @link https://wiki.processmaker.com/3.3/processmaker_command
 */
function remove_deprecated_files()
{
    //The constructor requires an argument, so we send an empty value in order to use the class.
    $workspaceTools = new WorkspaceTools('');
    $workspaceTools->removeDeprecatedFiles();
    CLI::logging("<*> The deprecated files has been removed. \n");
}

/**
 * This function review the queries for each workspace or for an specific workspace
 *
 * @param array $args
 *
 * @return void
 */
function run_check_queries_incompatibilities($args)
{
    try {
        $workspaces = get_workspaces_from_args($args);
        if (count($args) === 1) {
            CLI::logging("> Workspace: " . $workspaces[0]->name . PHP_EOL);
            check_queries_incompatibilities($workspaces[0]->name);
        } else {
            foreach ($workspaces as $workspace) {
                passthru(PHP_BINARY . " processmaker check-queries-incompatibilities " . $workspace->name);
            }
        }
        echo "Done!\n\n";
    } catch (Exception $e) {
        G::outRes(CLI::error($e->getMessage()) . "\n");
    }
}

/**
 * Check for the incompatibilities in the queries for the specific workspace
 *
 * @param string $wsName
 */
function check_queries_incompatibilities($wsName)
{
    Bootstrap::setConstantsRelatedWs($wsName);
    require_once(PATH_DB . $wsName . '/db.php');
    System::initLaravel();

    $query = Process::query()->select('PRO_UID', 'PRO_TITLE');
    $processesToCheck = $query->get()->values()->toArray();

    $obj = new MySQL57();
    $resTriggers = $obj->checkIncompatibilityTriggers($processesToCheck);

    if (!empty($resTriggers)) {
        foreach ($resTriggers as $trigger) {
            echo ">> The \"" . $trigger['PRO_TITLE'] . "\" process has a trigger called: \"" . $trigger['TRI_TITLE'] . "\" that contains UNION queries. Review the code to discard incompatibilities with MySQL5.7." . PHP_EOL;
        }
    } else {
        echo ">> No MySQL 5.7 incompatibilities in triggers found for this workspace." . PHP_EOL;
    }

    $resDynaforms = $obj->checkIncompatibilityDynaforms($processesToCheck);

    if (!empty($resDynaforms)) {
        foreach ($resDynaforms as $dynaform) {
            echo ">> The \"" . $dynaform['PRO_TITLE'] . "\" process has a dynaform called: \"" . $dynaform['DYN_TITLE'] . "\" that contains UNION queries. Review the code to discard incompatibilities with MySQL5.7." . PHP_EOL;
        }
    } else {
        echo ">> No MySQL 5.7 incompatibilities in dynaforms found for this workspace." . PHP_EOL;
    }

    $resVariables = $obj->checkIncompatibilityVariables($processesToCheck);

    if (!empty($resVariables)) {
        foreach ($resVariables as $variable) {
            echo ">> The \"" . $variable['PRO_TITLE'] . "\" process has a variable called: \"" . $variable['VAR_NAME'] . "\" that contains UNION queries. Review the code to discard incompatibilities with MySQL5.7." . PHP_EOL;
        }
    } else {
        echo ">> No MySQL 5.7 incompatibilities in variables found for this workspace." . PHP_EOL;
    }
}

/**
 * This function obtains the connection parameters and passes them to the artisan. 
 * All artisan options can be applied. For more information on artisan options use 
 * php artisan --help
 * @param array $args
 */
function run_artisan($args)
{
    $jobsManager = JobsManager::getSingleton()->init();
    $workspace = $jobsManager->getOptionValueFromArguments($args, "--workspace");
    if ($workspace !== false) {
        config(['system.workspace' => $workspace]);

        $sw = in_array($args[0], ['queue:work', 'queue:listen']);
        $tries = $jobsManager->getOptionValueFromArguments($args, "--tries");
        if ($sw === true && $tries === false) {
            $tries = $jobsManager->getTries();
            array_push($args, "--tries={$tries}");
        }
        array_push($args, "--processmakerPath=" . PROCESSMAKER_PATH);

        $command = "artisan " . implode(" ", $args);
        CLI::logging("> {$command}\n");
        passthru(PHP_BINARY . " {$command}");
    } else {
        CLI::logging("> The --workspace option is undefined.\n");
    }
}

/**
 * Add a font to be used in Documents generation (TinyMCE editor and/or TCPDF library)
 *
 * @param array $args
 * @param array $options
 */
function documents_add_font($args, $options)
{
    try {
        // Validate the main required argument
        if (empty($args)) {
            throw new Exception('Please send the font filename.');
        }

        // Load and initialize optional arguments and options
        $fontFileName = $args[0];
        $fontFriendlyName = $args[1] ?? '';
        $fontProperties = $args[2] ?? '';
        $fontType = $options['type'] ?? 'TrueType';
        $inTinyMce = !empty($options['tinymce']) ? $options['tinymce'] === 'true' : true;
        $name = '';

        // Check fonts path
        OutputDocument::checkTcPdfFontsPath();

        // Check if the font file exist
        if (!file_exists(PATH_DATA . 'fonts' . PATH_SEP . $fontFileName)) {
            throw new Exception("Font '{$fontFileName}' not exists.");
        }

        // Check if the font file was already added
        if (OutputDocument::existTcpdfFont($fontFileName)) {
            throw new Exception("Font '{$fontFileName}' already added.");
        }

        // Check if the friendly font name is valid
        if (preg_match('/[^0-9A-Za-z ]/', $fontFriendlyName)) {
            throw new Exception('The friendly font name is using an incorrect format please use only letters, numbers and spaces.');
        }

        // Check if the font type is valid
        if (!in_array($fontType, ['TrueType', 'TrueTypeUnicode'])) {
            throw new Exception("Font type '{$fontType}' is invalid.");
        }

        // Convert TTF file to the format required by TCPDF library
        $tcPdfFileName = TCPDF_FONTS::addTTFfont(PATH_DATA . 'fonts' . PATH_SEP . $fontFileName, $fontType);

        // Check if the conversion was successful
        if ($tcPdfFileName === false) {
            throw new Exception("The font file '{$fontFileName}' cannot be converted.");
        }

        // Include font definition, in order to use the variable $name
        require_once K_PATH_FONTS . $tcPdfFileName . '.php';

        // Build the font family name to be used in the styles
        $fontFamilyName = strtolower($name);
        $fontFamilyName = str_replace('-', ' ', $fontFamilyName);
        $fontFamilyName = str_replace(['bold', 'oblique', 'italic', 'regular'], '', $fontFamilyName);
        $fontFamilyName = trim($fontFamilyName);

        // Add new font
        $font = [
            'fileName' => $fontFileName,
            'tcPdfFileName' => $tcPdfFileName,
            'familyName' => $fontFamilyName,
            'inTinyMce' => $inTinyMce,
            'friendlyName' => !empty($fontFriendlyName) ? $fontFriendlyName : $fontFamilyName,
            'properties' => $fontProperties
        ];
        OutputDocument::addTcPdfFont($font);

        // Print finalization message
        CLI::logging("Font '{$fontFileName}' added successfully." . PHP_EOL . PHP_EOL);
    } catch (Exception $e) {
        // Display the error message
        CLI::logging($e->getMessage() . PHP_EOL . PHP_EOL);
    }
}

/**
 * List the registered fonts
 */
function documents_list_registered_fonts()
{
    // Check fonts path
    OutputDocument::checkTcPdfFontsPath();

    // Get registered fonts
    $fonts = OutputDocument::loadTcPdfFontsList();

    // Display information
    CLI::logging(PHP_EOL);
    if (!empty($fonts)) {
        foreach ($fonts as $fileName => $font) {
            $inTinyMce = $font['inTinyMce'] ? 'Yes' : 'No';
            CLI::logging("TTF Filename: {$fileName}" . PHP_EOL);
            CLI::logging("TCPDF Filename: {$font['tcPdfFileName']}" . PHP_EOL);
            CLI::logging("Display in TinyMCE: {$inTinyMce}" . PHP_EOL . PHP_EOL . PHP_EOL);
        }
    } else {
        CLI::logging('It has not been added fonts yet.' . PHP_EOL . PHP_EOL);
    }
}

/**
 * Remove a font used in Documents generation (TinyMCE editor and/or TCPDF library)
 *
 * @param array $args
 */
function documents_remove_font($args)
{
    try {
        // Validate the main required argument
        if (empty($args)) {
            throw new Exception('Please send the font filename.');
        }

        // Load arguments
        $fontFileName = $args[0];

        // Check fonts path
        OutputDocument::checkTcPdfFontsPath();

        // Check if the font file exist
        if (!file_exists(PATH_DATA . 'fonts' . PATH_SEP . $fontFileName)) {
            throw new Exception("Font '{$fontFileName}' not exists.");
        }

        // Check if the font file was registered
        if (!OutputDocument::existTcpdfFont($fontFileName)) {
            throw new Exception("Font '{$fontFileName}' was not registered.");
        }

        // Get registered font
        $font = OutputDocument::loadTcPdfFontsList()[$fontFileName];

        // Remove TCPDF font files
        $extensions = ['ctg.z', 'php', 'z'];
        foreach ($extensions as $extension) {
            if (file_exists(PATH_DATA . 'fonts' . PATH_SEP . 'tcpdf' . PATH_SEP . $font['tcPdfFileName'] . '.' . $extension)) {
                unlink(PATH_DATA . 'fonts' . PATH_SEP . 'tcpdf' . PATH_SEP . $font['tcPdfFileName'] . '.' . $extension);
            }
        }

        // Remove font
        OutputDocument::removeTcPdfFont($fontFileName);

        // Print finalization message
        CLI::logging("Font '{$fontFileName}' removed successfully." . PHP_EOL . PHP_EOL);
    } catch (Exception $e) {
        // Display the error message
        CLI::logging($e->getMessage() . PHP_EOL . PHP_EOL);
    }
}

/**
 * Add +async option to scheduler commands in table SCHEDULER.
 * @param array $args
 * @param string $opts
 */
function add_async_option_to_scheduler_commands($args, $opts)
{
    if (count($args) === 1) {
        Bootstrap::setConstantsRelatedWs($args[0]);
        $workspaceTools = new WorkspaceTools($args[0]);

        CLI::logging("> Adding +async option to scheduler commands...\n");
        $start = microtime(true);
        $workspaceTools->addAsyncOptionToSchedulerCommands(true);
        CLI::logging("<*>   Adding +async option to scheduler commands took " . (microtime(true) - $start) . " seconds.\n");
    } else {
        $workspaces = get_workspaces_from_args($args);
        foreach ($workspaces as $workspace) {
            passthru(PHP_BINARY . ' processmaker add-async-option-to-scheduler-commands ' . $workspace->name);
        }
    }
}

/**
 * Convert Web Entries v1.0 to v2.0 for BPMN processes in order to deprecate the old version.
 *
 * @param array $args
 */
function convert_old_web_entries($args)
{
    try {
        if (!empty($args)) {
            // Print initial message
            $start = microtime(true);
            CLI::logging("> Converting Web Entries v1.0 to v2.0 for BPMN processes...\n");

            // Set workspace constants and initialize DB connection
            Bootstrap::setConstantsRelatedWs($args[0]);
            Propel::init(PATH_CONFIG . 'databases.php');

            // Convert Web Entries
            WebEntry::convertFromV1ToV2();

            // Print last message
            $stop = microtime(true);
            CLI::logging("<*>   Converting Web Entries v1.0 to v2.0 for BPMN processes data took " . ($stop - $start) . " seconds.\n");
        } else {
            // If a workspace is not specified, get all available workspaces in the server
            $workspaces = get_workspaces_from_args($args);

            // Execute the command for each workspace
            foreach ($workspaces as $workspace) {
                passthru(PHP_BINARY . ' processmaker convert-old-web-entries ' . $workspace->name);
            }
        }
    } catch (Exception $e) {
        // Display the error message
        CLI::logging($e->getMessage() . PHP_EOL . PHP_EOL);
    }
}

/**
 * Populate the new column APPLICATION.APP_TITLE into the APP_DELEGATION table
 * 
 * @param array $args
 */
function migrate_case_title_to_threads($args)
{
    //The constructor requires an argument, so we send an empty value in order to use the class.
    $workspaceTools = new WorkspaceTools('');
    $workspaceTools->migrateCaseTitleToThreads($args);
}
