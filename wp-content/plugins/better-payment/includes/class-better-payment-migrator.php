<?php
defined('ABSPATH') || exit();

class Better_Payment_Migrator
{

    public static function migrator() {
        self::update_tables();
    }

    public static function update_tables() {
        global $wpdb;
        $wpdb->hide_errors();
        $table_name = "{$wpdb->prefix}better_payment";

        //Add column
        $column_name = sanitize_text_field("refund_info");
        $column_type = sanitize_text_field("longtext");
        $row = $wpdb->get_results(  "SELECT column_name FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '$table_name' AND column_name = '$column_name'"  );

        if(empty($row)){
            $wpdb->query($wpdb->prepare("ALTER TABLE $table_name ADD $column_name $column_type"));
        }
    }
}
