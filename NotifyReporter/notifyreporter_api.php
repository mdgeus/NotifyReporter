<?php

if (!function_exists('email_notifyreporter')) {

    function email_notifyreporter($recipient, $bug_id, $projectid) {
        #get subject and message from database
        $plug_table = plugin_table('settings');

        $result = db_query_bound("SELECT * FROM $plug_table WHERE projectid=$projectid");

            if (db_num_rows($result) > 0) {

            $row = db_fetch_array($result);

            $email = user_get_email($recipient);
            $subj = $row['subject'];
            $message = $row['message'];

            $subject = str_replace('{BUGID}', $bug_id, $subj);
            $contents = str_replace('{BUGID}', $bug_id, $message);


            if (!is_blank($email)) {
                email_store($email, $subject, $contents);
                if (OFF == config_get('email_send_using_cronjob')) {
                    email_send_all();
                }
            }
        }
    }

}
