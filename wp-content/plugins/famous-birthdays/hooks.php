<?php
add_action('init', array('FamousBirthdays', 'add_famous_birthdays_shortcode'));
add_action('widgets_init', array('FamousBirthdays', 'register_famous_birthdays_widget'));
add_action('admin_menu', array('FamousBirthdays', 'add_settings_page_to_menu'));
add_action('admin_enqueue_scripts', array('FamousBirthdays', 'load_scripts'));
add_action('admin_init', array('FamousBirthdays', 'handle_settings_save'));
add_action('admin_notices', array('FamousBirthdaysUtility', 'display_admin_notices'));
add_action('wp_enqueue_scripts', array('FamousBirthdays', 'load_frontend_scripts'));
register_deactivation_hook(FAMBDAY_PLUGIN_FILE, array('FamousBirthdaysUtility', 'delete_transient_data'));
