<?php
/*
Plugin Name: De-IP
Description: Set all comment-ips to 127.0.0.1 after a defined time
Version: 0.2
License: WTFPL
Author: Florian Holzhauer
Author URI: http://holzhauer.it/
*/

/* This program is free software. It comes without any warranty, to
* the extent permitted by applicable law. You can redistribute it
* and/or modify it under the terms of the Do What The Fuck You Want
* To Public License, Version 2, as published by Sam Hocevar. See
* http://sam.zoy.org/wtfpl/COPYING for more details. */

$deip_default = array(
    'delete_after' => 3,
    'version' => '0.1',
);

function deip_admin()
{
    global $deip_default;
    $deip_settings = get_option('deip_settings');
    $deip_version = '0.1';
    $error = array();
    if (!current_user_can('manage_options')) {
        die('Permission denied');
    }

    if (isset($_POST['submitted'])) {
        $new = trim($_POST['delete_after']);
        if (is_numeric($new)) {
            $submitted_settings = array(
                'delete_after' => $new,
                'version' => $deip_version,
            );

            if ($deip_settings != $submitted_settings) {
                //something has changed. :)
                update_option('deip_settings', $submitted_settings);
                $deip_settings = $submitted_settings;
            }
        }
    } else {
        if (empty($deip_settings)) {
            //new installation?
            update_option('deip_settings', $deip_default);
            $deip_settings = $deip_default;
        }
    } ?>

    <div class="wrap">
        <h2>De-IP</h2>

        <form name="settings" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>?page=deip.php" method="post">
            <table width="100%" cellspacing="2" cellpadding="2" class="editform">
                <tr valign="top">
                    <th scope="row" width="33%"><label for="delete_after">Delete after days:</label></th>
                    <td><input name="delete_after" type="text" size="5"
                               value="<?php echo $deip_settings['delete_after']; ?>" class="code"/>
                        <br/>All commenter ips older than this amount of days will be reset to 127.0.0.1
                    </td>
                </tr>
            </table>
            <p class="submit"><input type="hidden" name="submitted"/><input type="submit" name="Submit"
                                                                            value="Update Settings &raquo;"/></p>
        </form>
    </div>

<?php
}

function do_deip_adminmenu()
{
    if (current_user_can('manage_options')) {
        add_submenu_page('plugins.php', 'DeIP', 'DeIP', 9, basename(__FILE__), 'deip_admin');
    }
}


/***
End of admin functions
 ***/

function do_deip()
{
    global $deip_default, $wpdb;
    $deip_settings = get_option('deip_settings');
    if (empty($deip_settings)) {
        $del = $deip_default['delete_after'];
    } else {
        $del = $deip_settings['delete_after'];
    }
    $cmt = $wpdb->comments; //comments table
    $query = "UPDATE " . $cmt . " SET comment_author_IP='127.0.0.1' ";
    $query .= "WHERE comment_date < DATE_SUB(NOW(), INTERVAL " . $del . " DAY);";
    $wpdb->query($query);
}

add_action('comment_post', 'do_deip');
add_action('admin_menu', 'do_deip_adminmenu');

