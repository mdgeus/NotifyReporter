<?php

class NotifyReporterPlugin extends MantisPlugin {

    function register() {
        $this->name = 'Notify reporter';
        $this->description = 'Send email after reporting an issue to the reporter with a predefined text';
        $this->version = '1.02';
        $this->requires = array('MantisCore' => '1.2.0');
        $this->author = 'Dennis Geus';
        $this->contact = 'Dennis@hands-off.it';
        $this->url = 'https://github.com/mdgeus/notifyreporter';
        $this->page = 'config.php';
    }

    function hooks() {
        return array(
            'EVENT_REPORT_BUG' => 'checkandnotify'
        );
    }

    function checkandnotify($p_event, $p_bugdata) {
        require_once( config_get('plugin_path') . 'notifyreporter' . DIRECTORY_SEPARATOR . 'notifyreporter_api.php' );

        # check the table for entries, check if someone has to be notified and email the notification
        $bug_id = $p_bugdata->id;
        $reporter_id = $p_bugdata->reporter_id;
        $project_id = $p_bugdata->project_id;

        # send the email to the reporter
        email_notifyreporter($reporter_id, $bug_id, $project_id);
    }

    /** uninstall and install functions * */
    function uninstall() {
        global $g_db;
        # remove the table created at installation
        $request = 'DROP TABLE ' . plugin_table('settings');
        $g_db->Execute($request);

        # IMPORTANT : erase information about the plugin stored in Mantis
        # Without this request, you cannot create the table again (if you re-install)
        $request = "DELETE FROM " . db_get_table('mantis_config_table') . " WHERE config_id = 'plugin_notifyreporter_schema'";
        $g_db->Execute($request);
    }

    function schema() {
        # v1.00
        $schema[] = array('CreateTableSQL', array(plugin_table('settings'), "
				id  I   NOTNULL UNSIGNED ZEROFILL AUTOINCREMENT PRIMARY,
                                set_descr C(200) default NULL,
				set_value XL     default NULL
				"));
        //$schema[] = array('InsertData', array(plugin_table('settings'), " (set_descr, set_value) VALUES ('subject', 'This is the subject') "));
        //$schema[] = array('InsertData', array(plugin_table('settings'), " (set_descr, set_value) VALUES ('message', 'This is the actual email message')"));
        #end v1.00
        # v1.01
        $schema[] = array('AddColumnSQL', array(plugin_table('settings'), "projectid I  default NULL AFTER set_value \" '' \""));
        $schema[] = Array('RenameColumnSQL', Array(plugin_table('settings'), "set_descr", "subject", "set_descr C(200) default NULL" ) );
        $schema[] = Array('RenameColumnSQL', Array(plugin_table('settings'), "set_value", "message", "set_value XL     default NULL" ) );

        return $schema;
    }

}
