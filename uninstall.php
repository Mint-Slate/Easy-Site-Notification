<?php
    if (!defined('WP_UNINSTALL_PLUGIN')) {
        die;
    }
    delete_option('snE_enabled');
    delete_option('snE_content');
    delete_option('snE_ID');
    delete_option('snE_elementID');