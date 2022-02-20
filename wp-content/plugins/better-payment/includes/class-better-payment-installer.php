<?php
defined('ABSPATH') || exit();

class Better_Payment_Installer
{

    public static function install() {
        self::create_tables();
        
        //check setup wizard condition
        self::enable_setup_wizard();
    }

    private static function get_schema() {
        global $wpdb;
        return [
            "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}better_payment(
         	 	id bigint(20) NOT NULL AUTO_INCREMENT,
  			 	order_id varchar(50) NOT NULL,
				transaction_id varchar(50) DEFAULT '',
			 	amount decimal(10,2) NOT NULL,
			 	status varchar(50) DEFAULT NULL,
			    source varchar(50) NOT NULL,
			    payment_date datetime DEFAULT NULL,
			    email varchar(50) NOT NULL DEFAULT '',
			    customer_info longtext,
			    form_fields_info longtext,
			    currency varchar(11) NOT NULL DEFAULT '', 
                referer varchar(64) DEFAULT NULL,
			    obj_id text,
			    PRIMARY KEY (id),
			    KEY order_id (order_id),
			    KEY status (status)						
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 "
        ];
    }

    public static function create_tables() {
        global $wpdb;
        $wpdb->hide_errors();
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        $tables = self::get_schema();
        foreach ( $tables as $table ) {
            dbDelta( $table );
        }
    }

    /**
     * Save setup wizard data
     *
     * @since 0.0.2
     */
    public static function enable_setup_wizard()
    {
        if ( !get_option( 'better_payment_setup_wizard' ) ) {
            update_option( 'better_payment_setup_wizard', 'redirect' );
        }
    }
}
