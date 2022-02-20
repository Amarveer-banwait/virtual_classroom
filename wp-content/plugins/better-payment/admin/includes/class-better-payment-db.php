<?php

/**
 * This class responsible for database work
 * using wordpress functionality 
 * get_option and update_option.
 */
class Better_Payment_DB
{
    public static function get_table_name()
    {
        global $wpdb;

        $table   = "{$wpdb->prefix}better_payment";
        return $table;
    }
    /**
     * Get all default settings value.
     *
     * @param string $name
     * @return array
     */
    public static function default_settings()
    {
        return apply_filters('better_payment_option_default_settings', array(
            'better_payment_settings_general_general_stripe' => esc_html__('yes', 'better-payment'),
            'better_payment_settings_general_general_paypal' => esc_html__('yes', 'better-payment'),
            'better_payment_settings_general_general_email' => esc_html__('yes', 'better-payment'),
            'better_payment_settings_general_general_currency' => esc_html__('USD', 'better-payment'),

            'better_payment_settings_general_email_to' => esc_html__('', 'better-payment'),
            'better_payment_settings_general_email_subject' => esc_html__('', 'better-payment'),
            'better_payment_settings_general_email_message_admin' => esc_html__('', 'better-payment'),
            'better_payment_settings_general_email_from_email' => esc_html__('', 'better-payment'),
            'better_payment_settings_general_email_from_name' => esc_html__('', 'better-payment'),
            'better_payment_settings_general_email_reply_to' => esc_html__('', 'better-payment'),
            'better_payment_settings_general_email_cc' => esc_html__('', 'better-payment'),
            'better_payment_settings_general_email_bcc' => esc_html__('', 'better-payment'),
            'better_payment_settings_general_email_send_as' => esc_html__('html', 'better-payment'),

            'better_payment_settings_general_email_subject_customer' => esc_html__('', 'better-payment'),
            'better_payment_settings_general_email_message_customer' => esc_html__('', 'better-payment'),
            'better_payment_settings_general_email_from_email_customer' => esc_html__('', 'better-payment'),
            'better_payment_settings_general_email_from_name_customer' => esc_html__('', 'better-payment'),
            'better_payment_settings_general_email_reply_to_customer' => esc_html__('', 'better-payment'),
            'better_payment_settings_general_email_cc_customer' => esc_html__('', 'better-payment'),
            'better_payment_settings_general_email_bcc_customer' => esc_html__('', 'better-payment'),
            'better_payment_settings_general_email_send_as_customer' => esc_html__('html', 'better-payment'),

            'better_payment_settings_payment_stripe_live_mode' => esc_html__('no', 'better-payment'),
            'better_payment_settings_payment_stripe_live_public' => esc_html__('', 'better-payment'),
            'better_payment_settings_payment_stripe_live_secret' => esc_html__('', 'better-payment'),
            'better_payment_settings_payment_stripe_test_public' => esc_html__('', 'better-payment'),
            'better_payment_settings_payment_stripe_test_secret' => esc_html__('', 'better-payment'),
            'better_payment_settings_payment_paypal_live_mode' => esc_html__('no', 'better-payment'),
            'better_payment_settings_payment_paypal_email' => esc_html__('', 'better-payment'),            
            'better_payment_settings_opt_in' => esc_html__('', 'better-payment'),
            'better_payment_settings_payment_paypal_live_client_id' => esc_html__('', 'better-payment'),
            'better_payment_settings_payment_paypal_test_client_id' => esc_html__('', 'better-payment'),
            'better_payment_settings_payment_paypal_live_secret' => esc_html__('', 'better-payment'),
            'better_payment_settings_payment_paypal_test_secret' => esc_html__('', 'better-payment'),
        ));
    }
    /**
     * Get all settings value from options table.
     *
     * @param string $name
     * @return array
     */
    public static function get_settings($name = '')
    {
        $settings = get_option('better_payment_settings', true);
        $default = self::default_settings();
        if (!empty($name) && isset($settings[$name])) {
            return $settings[$name];
        }

        if (!empty($name) && !isset($settings[$name]) && isset($default[$name])) {
            return $default[$name];
        }

        if (!empty($name) && !isset($settings[$name])  && !isset($default[$name])) {
            return '';
        }

        return is_array($settings) ? $settings : [];
    }
    /**
     * Update settings 
     * @param array $value
     * @return boolean
     */
    public static function update_settings($value, $key = '')
    {
        if (!empty($key)) {
            return update_option($key, $value);
        }
        return update_option('better_payment_settings', $value);
    }

    /**
     * Get all transactions from better payment table.
     *
     * @param string $args
     * @return array
     */
    public static function get_transactions($args = [], $count_only=0)
    {
        global $wpdb;
        $items = '';

        $defaults = array(
            'search_text' => '',
            'payment_date_from' => '',
            'payment_date_to' => '',
            'order_by' => 'id',
            'order' => 'DESC',
            'source' => '', 
            'paged' => 1,
            'per_page' => 20, 
            'offset' => 0, 
        );

        $allowed_order_by = array(
            'id',
            'payment_date',
            'email',
            'amount',
            'status'
        );

        $allowed_order = array(
            'DESC',
            'ASC'
        );

        $args = wp_parse_args($args, $defaults);
        $table   = self::get_table_name();

        $search_text  = sanitize_text_field($args['search_text']);
        $search_text = empty($search_text) ? '' : "%$search_text%";

        $order_by = in_array($args['order_by'], $allowed_order_by) ? sanitize_text_field($args['order_by']) : $defaults['order_by'];
        $order = in_array($args['order'], $allowed_order) ? sanitize_text_field($args['order']) : $defaults['order'];
        $offset = $args['offset'] > 0 ? intval($args['offset']) : $defaults['offset'];
        $per_page = $args['per_page'] > 0 ? intval($args['per_page']) : $defaults['per_page'];
        
        $whereQuery = ''; 

        if($search_text != '' && strlen($search_text) >= 2){
            $whereQuery = $wpdb->prepare(' AND email LIKE %s OR transaction_id LIKE %s OR amount LIKE %s OR source LIKE %s ', $search_text, $search_text, $search_text, $search_text );
        }

        if( $args['source'] != '' ){
            $whereQuery .= $wpdb->prepare(" AND source = %s", $args['source']); 
        }

        if( $args['payment_date_from'] != '' && $args['payment_date_to'] != ''){
            $whereQuery .= $wpdb->prepare(" AND payment_date BETWEEN %s AND %s", $args['payment_date_from'], $args['payment_date_to']); 
        }

        if($count_only === 1){
            $items = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT count(id) FROM $table WHERE 1
                    $whereQuery
                    ORDER BY $order_by $order
                    LIMIT %d, %d
                    ",
                    $offset,
                    $per_page
                )
            );
        }else {
            $items = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM $table WHERE 1
                    $whereQuery
                    ORDER BY $order_by $order
                    LIMIT %d, %d
                    ",
                    $offset,
                    $per_page
                )
            );
        }

        return $items;
    }

    /**
     * Get the count of total transactions
     *
     * @return int
     */
    public static function get_transaction_count($args = '')
    {
        global $wpdb;
        $table = self::get_table_name();

        $count = 0;
        
        if($args !== ''){
            $count = self::get_transactions($args, 1);
        }else {
            $count = $wpdb->get_var(
                "SELECT count(id) FROM $table"
            );
        }

        return $count;
    }

    /**
     * Get transactions analytics
     *
     * @return int
     */
    public static function get_transactions_analytics()
    {
        global $wpdb;
        $transaction_analytics = [];

        $table = self::get_table_name();

        $count_all = (int) $wpdb->get_var(
            "SELECT count(id) FROM $table"
        );

        $count_paid = (int) $wpdb->get_var($wpdb->prepare( 
            "SELECT count(id) FROM $table WHERE status LIKE %s", 'paid' 
        ));

        $transaction_analytics['all'] = $count_all;
        $transaction_analytics['paid'] = $count_paid;

        return $transaction_analytics;
    }

    /**
     * Get transaction details from better payment table.
     *
     * @param string $args
     * @return object 
     */
    public static function get_transaction($id)
    {
        global $wpdb;

        $table = self::get_table_name();
        $transaction_id = (int) sanitize_text_field($id);
        
        $item = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table WHERE id = %d",
                $transaction_id
            )
        );

        return $item;
    }

    /**
     * Delete a transaction
     *
     * @param  int $id
     *
     * @return int|boolean
     */
    public static function delete_transaction($id)
    {
        global $wpdb;
        $table = self::get_table_name();

        return $wpdb->delete(
            $table,
            ['id' => $id],
            ['%d']
        );
    }
}
