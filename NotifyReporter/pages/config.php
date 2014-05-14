<?php
auth_reauthenticate();
access_ensure_global_level(config_get('manage_plugin_threshold'));
html_page_top1('config');
html_page_top2();
print_manage_menu();

require_once( config_get('plugin_path') . 'notifyreporter' . DIRECTORY_SEPARATOR . 'notifyreporter_api.php' );

# tables
$plug_table = plugin_table('settings');

if (isset($_POST['edit'])) {
    $what = $_POST['what'];
    $sql = "SELECT * FROM $plug_table WHERE set_descr='$what'";
    $result = db_query_bound($sql);
    $row = db_fetch_array($result);
    ?>
    <form name="save" id="save" method="post" action="<?php echo plugin_page('config') ?>">
        <input type="hidden" name="what" value="<?php echo $what; ?>" />
        <table class="width50" cellspacing="0">
            <tr <?php echo helper_alternate_class() ?>>
                <td class="form-title">Edit <?php echo $what; ?></td>
            </tr>
            <tr <?php echo helper_alternate_class() ?>>
                <?php
                if ($what == 'subject') {
                    ?>
                    <td class="form-title" ><input name="set_value" id="set_value" type="text" size="80" value="<?php echo $row['set_value']; ?>" /></td>
                    <?php
                }
                if ($what == 'message') {
                    ?>
                    <td class="form-title" >You can use {BUGID} to indicate if and where you want to mention the bugid.
                        <textarea name="set_value" id="set_value" cols="75" rows="10"><?php echo $row['set_value']; ?></textarea></td>
                    <?php
                }
                ?>
            </tr>
            <tr <?php echo helper_alternate_class() ?>>
                <td><button name="save" id="save" onclick="this.form.submit();">Save</button></td>
            </tr>
        </table>
    </form>
    <?php
}

if (isset($_POST['save'])) {
    # update it
    $what = $_POST['what'];
    $value = $_POST['set_value'];

    $ins_sql = "UPDATE $plug_table
                    SET set_value = '" . $value . "'
                    WHERE set_descr = '" . $what . "'
                    ";
    $insert = db_query_bound($ins_sql);
    if ($insert) {
        header('Location: ' . plugin_page('config') . ' ');
        die();
    } else {
        echo $message = "Could not save this. Please try again.";
    }
}
if (!$_POST) {
    ?>
    <br />
    <table class="width100" cellspacing="0">
        <tr <?php echo helper_alternate_class() ?>>
            <td class="form-title" colspan="4">Set subject and message text.</td>
        </tr>
        <tr <?php echo helper_alternate_class() ?>>
            <td class="form-title" >Description:</td>
            <td class="form-title" >Value:</td>
            <td class="form-title" ></td>
        </tr>
        <?php
        $sql = "SELECT * FROM $plug_table WHERE 1";
        $result = db_query_bound($sql);
        while ($row = db_fetch_array($result)) {
            ?>
            <tr <?php echo helper_alternate_class() ?>>
                <td><?php echo $row['set_descr']; ?></td>
                <td><?php echo substr($row['set_value'], 0, 100); ?></td>
                <td> <form name="editline" id="editline" method="post" action="<?php echo plugin_page('config') ?>">
                        <input type="hidden" name="what" id="what" value="<?php echo $row['set_descr']; ?>" />
                        <button name="edit" id="edit" onclick="this.form.submit();">edit</button>
                    </form>
                </td>
            </tr>
            <?php
        }
        ?>
    </table>

    <?php
}
html_page_bottom1(__FILE__);
