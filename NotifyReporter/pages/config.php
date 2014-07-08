<?php
auth_reauthenticate();
access_ensure_global_level(config_get('manage_plugin_threshold'));
html_page_top1('config');
html_page_top2();
print_manage_menu();

require_once( config_get('plugin_path') . 'notifyreporter' . DIRECTORY_SEPARATOR . 'notifyreporter_api.php' );

# tables
$plug_table = plugin_table('settings');
$prj_table = db_get_table('mantis_project_table');

if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $sql = "SELECT * FROM $plug_table WHERE id=$id";
    $result = db_query_bound($sql);
    $row = db_fetch_array($result);
    ?>
    <form name="addit" method="post" action="<?php echo plugin_page('config') ?>">
        <input type="hidden" name="id" value="<?php echo $id; ?>" />
        <table class="width100" cellspacing="0">
            <tr <?php echo helper_alternate_class() ?>>
                <td class="form-title" colspan="2">Edit email message for project: <?php echo htmlspecialchars(project_get_name($row['projectid'])); ?></td>
            </tr>
            <tr <?php echo helper_alternate_class() ?>>
                <td></td>
                <td valign="top" rowspan="3">
                    <ul>
                        <li>You can use {BUGID} to indicate if and where you want to mention the bugid.</li>
                    </ul>
                </td>
            </tr>
            <tr <?php echo helper_alternate_class() ?>>
                <td class="form-title" >
                    <label for="subject">Subject:</label>
                    <input name="subject" id="subject" type="text" size="89" value="<?php echo $row['subject']; ?>" /></td>
                </td>
            </tr>
            <tr <?php echo helper_alternate_class() ?>>
                <td class="form-title" >
                    <label for="message">Message:</label>
                    <textarea name="message" id="message" cols="75" rows="10"><?php echo $row['message']; ?></textarea>
                </td>
            </tr>
            <tr>
                <td><button name="save" id="save" onclick="this.form.submit();">Save</button></td>
            </tr>
        </table>
    </form>
    <?php
}

if (isset($_POST['add'])) {
# insert it
    $projectid = $_POST['projectid'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];

    if (($projectid > 0) && ($subject != '') && ($message != '')) {
        $ins_sql = "INSERT INTO $plug_table
                    SET projectid = $projectid,
                        subject = '" . $subject . "',
                        message = '" . $message . "'
                    ";
        $insert = db_query_bound($ins_sql);
        if ($insert) {
            header('Location: ' . plugin_page('config') . ' ');
            //die();
        } else {
            echo $error = '<font color="red">Could not save this. Please try again.</font>';
        }
    } else {
        echo $error = '<font color="red">You must fill in all fields</font>';
    }
}

if (isset($_POST['del'])) {
    # now delete it
    $id = $_POST['id'];
    $del_sql = "DELETE FROM $plug_table WHERE id=$id";
    $delete = db_query_bound($del_sql);
    if ($delete) {
        header('Location: ' . plugin_page('config') . ' ');
        //die();
    } else {
        echo $error = '<font color="red">Could not delete this. Please try again.</font>';
    }
}

if (isset($_POST['save'])) {
# insert it

    $id = $_POST['id'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];

    $ins_sql = "UPDATE $plug_table
                    SET subject = '" . $subject . "',
                        message = '" . $message . "'
                        WHERE id=$id
                    ";
    $insert = db_query_bound($ins_sql);
    if ($insert) {
        header('Location: ' . plugin_page('config') . ' ');
        //die();
    } else {
        echo $error = '<font color="red">Could not save this. Please try again.</font>';
    }
}

if (!$_POST) {

    $sql = "SELECT * FROM $plug_table WHERE 1";
    $res = mysql_query($sql);
    if (mysql_num_rows($res) > 0) {
        ?>
        <br />
        <table class="width100" cellspacing="0">
            <tr <?php echo helper_alternate_class() ?>>
                <td class="form-title" colspan="4">Reporting an issue for these projects will send an email to the reporter.</td>
            </tr>
            <tr <?php echo helper_alternate_class() ?>>
                <td class="form-title" >Project:</td>
                <td class="form-title" >Subject:</td>
                <td class="form-title" >Message:</td>
                <td class="form-title" ></td>
            </tr>
            <?php
            $sql = "SELECT * FROM $plug_table WHERE 1 ORDER BY projectid";
            $result = db_query_bound($sql);
            while ($row = db_fetch_array($result)) {
                ?>
                <tr <?php echo helper_alternate_class() ?>>
                    <td><?php echo project_get_name($row['projectid']); ?></td>
                    <td><?php echo $row['subject']; ?></td>
                    <td><?php echo substr($row['message'], 0, 100); ?></td>
                    <td> <form name="editline" id="editline" method="post" action="<?php echo plugin_page('config') ?>">
                            <input type="hidden" name="id" id="id" value="<?php echo $row['id']; ?>" />
                            <button name="edit" id="edit" onclick="this.form.submit();"><img src="<?php echo config_get('path'); ?>plugins/NotifyReporter/img/glyphicons_150_edit.png" height="16" border="0" /></button>
                        </form>
                        <form name="editline" id="editline" method="post" action="<?php echo plugin_page('config') ?>">
                                <input type="hidden" name="id" id="id" value="<?php echo $row['id']; ?>" />
                            <button name="del" id="del" onclick="this.form.submit();"><img src="<?php echo config_get('path'); ?>plugins/NotifyReporter/img/glyphicons_016_bin.png" height="16" border="0" /></button>
                        </form>
                    </td>
                </tr>
                <?php
            }
            ?>
        </table>
        <?php
    }
    ?>
    <br />
    <form name="addit" method="post" action="<?php echo plugin_page('config') ?>">
        <table class="width100" cellspacing="0">
            <tr <?php echo helper_alternate_class() ?>>
                <td class="form-title" colspan="2">Add email message for project:</td>
            </tr>
            <tr <?php echo helper_alternate_class() ?>>
                <td class="form-title" >
                    <label for="projectid">Project:</label>
                    <select name="projectid" id="projectid">
                        <option value="">Select Project</option>
                        <?php
                        $pr_sql = "SELECT id FROM $prj_table WHERE id NOT IN (SELECT projectid FROM $plug_table) ORDER BY name ASC";
                        $pr_result = db_query_bound($pr_sql);
                        while ($pr = db_fetch_array($pr_result)) {
                            if ($pr['id'] == $projectid) {
                                $selected = 'selected';
                            } else {
                                $selected = '';
                            }
                            echo '<option ' . $selected . ' value="' . $pr['id'] . '">' . htmlspecialchars(project_get_name($pr['id'])) . '</option>';
                        }
                        ?>
                    </select></td>
                <td valign="top" rowspan="3">
                    <ul>
                        <li>You can use {BUGID} to indicate if and where you want to mention the bugid.</li>
                    </ul>
                </td>
            </tr>
            <tr <?php echo helper_alternate_class() ?>>
                <td class="form-title" >
                    <label for="subject">Subject:</label>
                    <input name="subject" id="subject" type="text" size="89" value="" /></td>
                </td>
            </tr>
            <tr <?php echo helper_alternate_class() ?>>
                <td class="form-title" >
                    <label for="message">Message:</label>
                    <textarea name="message" id="message" cols="75" rows="10"></textarea>
                </td>
            </tr>
            <tr>
                <td><button name="add" id="add" onclick="this.form.submit();">Add</button></td>
            </tr>
        </table>
    </form>
    <?php
}
html_page_bottom1(__FILE__);
