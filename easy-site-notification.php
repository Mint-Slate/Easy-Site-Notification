<?php
/**
 * Plugin Name: Easy Site Notification
 * Description: This plugin allows you to place a div anywhere on your site and treat it as a notification with on and off + content control through the admin
 * Version: 1.0
 * Author: Mint Slate
 * Author URI: http://mintslate.com
 * License: GPL2
 */

/*  Copyright 2016  Mint Slate  (email : shawn@mintslate.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
**************************************************************************/

// Blocks direct access to plugin
defined('ABSPATH') or die("Access Forbidden");

// Define The Easy Site Notifiction Plugin
define('snE_PLUGIN_VERSION', '1.0');
define('snE_PLUGIN__MINIMUM_WP_VERSION', '4.5');
define('snE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('snE_PLUGIN_PATH', plugin_dir_path(__FILE__));

final class snE
{
    public function __construct()
    {
        register_activation_hook(__FILE__, array(
            $this,
            'snE_activate'
        ));
        add_filter("plugin_action_links_" . plugin_basename(__FILE__), array(
            $this,
            'snE_settings_link'
        ));

        add_action('admin_menu', array(
            $this,
            'snE_admin_menu'
        ));
        add_action('init', array(
            $this,
            'do_output_buffer'
        ));
        if ( !is_admin() ){
            if(get_option('snE_enabled') == '1'){
                $snEContent = get_option('snE_content');
                $elementID = get_option('snE_elementID');

                //includes script and feeds php var to script
                wp_enqueue_script('jquery');
                wp_register_script( 'snEScript', snE_PLUGIN_URL . 'public/includes/js/snE-scripts.js' );
                $scriptVars = array(
                    'content'=> $snEContent,
                    'elementID'=> $elementID
                );
                wp_localize_script( 'snEScript', 'snE', $scriptVars );
                wp_enqueue_script( 'snEScript' );
                
            }else{
                $IDName = get_option('snE_ID');
                $newClass = "#$IDName{display:none!important;}";
                wp_enqueue_style('custom-style', snE_PLUGIN_URL . 'public/includes/css/snE.css');
                wp_add_inline_style( 'custom-style', $newClass );
            }
        }
    }

    //Adds a settings link on the plugin page
    public function snE_settings_link($links)
    {
        $settings_link = '<a href="admin.php?page=snE-admin">' . __('Settings') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    public function snE_enqueue()
    {
        // CSS
        wp_register_style('snE_admin_css', snE_PLUGIN_URL . 'includes/css/snE.css');
        wp_enqueue_style('snE_admin_css');

    }

    //Set menu settings
    public function snE_admin_menu()
    {
        add_menu_page(
            'Easy Site Notification',
            'Site Notification',
            'manage_options',
            'snE-admin',
            array(
                $this,
                'snE_init'
            ),
            'dashicons-megaphone',
            26
        );
    }

    public function snE_activate()
    {
        add_option('snE_enabled', '0', '', 'yes');
        add_option('snE_content', '', '', 'yes');
        add_option('snE_ID', '', '', 'yes');
		add_option('snE_elementID', '', '', 'yes');
    }

    public function snE_init()
    {
        $this->snE_enqueue();
        $notificationEnabled = get_option('snE_enabled');
        $notificationContent = get_option('snE_content');
        $notificationID = get_option('snE_ID');
		$notificationElementID = get_option('snE_elementID');
        ?>
        <div class="row">
            <div class="col-sm-12">
                <h1>Easy Site Notification</h1>
            </div>
            <?php
            if(isset($_POST["notification-enabled"])){

                // sanitize data
                $noteContent = wp_kses_data($_POST["notification-content"]);
                $noteContent = balanceTags($noteContent,true);
                $noteContent = trim($noteContent);

                // make sure enabled is 1 or 0
                $enabled = trim(strip_tags($_POST["notification-enabled"]));

                if( $enabled != '0' && $enabled != '1' ){
                    $enabled = '0';
                }
                update_option('snE_enabled', $enabled);
                update_option('snE_content', $noteContent);
                update_option('snE_ID', trim(strip_tags($_POST["notification-id"])));
				update_option('snE_elementID', trim(strip_tags($_POST["notification-elementid"])));
                exit(wp_redirect(admin_url('admin.php?page=snE-admin')));
            }
            ?>
            <form method="POST" action="">
                <div class="input-option">
                    <label class="input-label">Enable Notification</label>
                    <span class="input-description">Show or hide the row that is the notification row.</span>
                    <select name="notification-enabled">
                        <option value="0" <?php if($notificationEnabled == 0){echo "selected='selected'";}?>>No</option>
                        <option value="1" <?php if($notificationEnabled == 1){echo "selected='selected'";}?>>Yes</option>
                    </select>
                </div>
                <div class="input-option">
                    <label class="input-label">Notification Content</label>
                        <span class="input-description">The content you want to show inside of the row. Basic html supported.</span>
                        <textarea rows="4" cols="50" name="notification-content"><?php echo $notificationContent;?></textarea>
                </div>
                <div class="input-option">
                    <label class="input-label">Notification Row ID</label>
                    <span class="input-description">The ID of the entire row that should be showen or hidden.</span>
                        <input name="notification-id" type="text" value="<?php echo $notificationID;?>"/>
                </div>
				<div class="input-option">
                    <label class="input-label">Element Content ID</label>
                    <span class="input-description">The ID of the html element inside the row that the content should be inserted into. Normally a p tag.</span>
                        <input name="notification-elementid" type="text" value="<?php echo $notificationElementID;?>"/>
                </div>
                <div class="input-option">
                    <button id="btn-to-hide" class="button button-primary">Save</button>
                </div>
            </form>
        </div>
        <?php
    }
}
new snE();