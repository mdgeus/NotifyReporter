<?php

if (!function_exists('email_notifyreporter')) {

    function email_notifyreporter($recipient, $bug_id) {
    #get subject and message from database
    $plug_table = plugin_table('settings');

    $sql_sub = "SELECT * FROM $plug_table WHERE set_descr='subject'";
    $r_sub = db_query_bound($sql_sub);
    $row_sub = db_fetch_array($r_sub);
    $sql_mes = "SELECT * FROM $plug_table WHERE set_descr='message'";
    $r_mes = db_query_bound($sql_mes);
    $row_mes = db_fetch_array($r_mes);

        $email = user_get_email($recipient);

        $subject = $row_sub['set_value'];
        //$subject .= email_build_subject($bug_id);
        $message = $row_mes['set_value'];
        $contents = str_replace('{BUGID}', $bug_id, $message);

        //$contents = "\n an issue is created which needs your attention : \n\n";
        //$contents .= "Date: $date \n";
        //$contents .= string_get_bug_view_url_with_fqdn($bug_id, $recipient) . " \n\n";

        if (!is_blank($email)) {
            email_store($email, $subject, $contents);
            if (OFF == config_get('email_send_using_cronjob')) {
                email_send_all();
            }
        }
    }

}
