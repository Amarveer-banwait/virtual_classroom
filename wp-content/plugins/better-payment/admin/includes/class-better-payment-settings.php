<?php
defined('ABSPATH') || exit();

class Better_Payment_Settings
{
	var $page_slug_prefix = '';
	/**
	 * @var int|string
	 */

	protected $file_version;

	public function __construct($page_slug_prefix = 'better-payment')
	{
		$this->page_slug_prefix = $page_slug_prefix;

		add_action('admin_enqueue_scripts', [$this, 'handle_scripts_and_styles']);
		$this->file_version = defined('WP_DEBUG') && WP_DEBUG ? time() : BETTER_PAYMENT_VERSION;

		add_action('admin_menu', [$this, 'register_menu']);
		add_action('wp_ajax_better_payment_settings_action', [$this, 'save_settings_ajax']);

		//Transactions page: actions
		add_action('wp_ajax_better-payment-delete-transaction', [$this, 'admin_transaction_delete']);
		add_action('wp_ajax_better-payment-filter-transaction', [$this, 'admin_transaction_filter']);
		add_action('wp_ajax_better-payment-view-transaction', [$this, 'admin_transaction_view']);
		
		//EL Editor Style
        add_action('elementor/editor/before_enqueue_scripts', [$this, 'editor_enqueue_scripts']);
	}

	public function handle_scripts_and_styles()
	{
		$menu_slugs = $this->better_payment_menu_slugs();

		if (!empty($_REQUEST['page']) && in_array($_REQUEST['page'], $menu_slugs)) {
			$this->enqueue_styles();
			$this->enqueue_scripts();
		}
	}

	public function enqueue_scripts()
	{
		wp_enqueue_script('bp-admin-settings', BETTER_PAYMENT_ADMIN_ASSET_URL . 'js/admin.min.js', ['jquery'], $this->file_version, true);

		wp_localize_script('bp-admin-settings', 'betterPaymentObj', array(
			'nonce'  => wp_create_nonce('better_payment_admin_nonce'),
			'alerts' => [
				'confirm' => __('Are you sure?', 'better-payment'),
				'confirm_description' => __("You won't be able to revert this!", 'better-payment'),
				'yes' => __('Yes, delete it!', 'better-payment'),
				'no' => __('No, cancel!', 'better-payment'),
			],
			'messages' => [
				'success' => __('Changes saved successfully!', 'better-payment'),
				'error' => __('Opps! something went wrong!', 'better-payment'),
				'no_action_taken' => __('No action taken!', 'better-payment'),
			]
		));

		wp_enqueue_script('toastr-js-admin', BETTER_PAYMENT_URL . 'asset/vendor/toastr/js/toastr.min.js', array('jquery'), BETTER_PAYMENT_VERSION, true);
        wp_enqueue_script( 'sweetalert2-js', BETTER_PAYMENT_URL . 'asset/vendor/sweetalert2/js/sweetalert2.min.js', array( 'jquery' ), BETTER_PAYMENT_VERSION, true );
		wp_enqueue_script( 'jquery-ui-datepicker' );

		do_action('better_payment/admin/after_enqueue_scripts');
	}

	public function enqueue_styles()
	{
    	wp_register_style( 'jquery-ui', BETTER_PAYMENT_URL . 'asset/vendor/jquery-ui/css/jquery-ui.min.css' );
    	wp_enqueue_style( 'jquery-ui' );  

		wp_enqueue_style('bp-settings-style', BETTER_PAYMENT_ADMIN_ASSET_URL . 'css/style.min.css', null, $this->file_version);
		wp_enqueue_style('bp-icon-admin', BETTER_PAYMENT_ADMIN_ASSET_URL . 'icon/style.min.css');
		wp_enqueue_style('toastr-css-admin', BETTER_PAYMENT_URL . 'asset/vendor/toastr/css/toastr.min.css');
        wp_enqueue_style( 'sweetalert2-css', BETTER_PAYMENT_URL . 'asset/vendor/sweetalert2/css/sweetalert2.min.css' );
	}

	// editor styles
    public function editor_enqueue_scripts()
    {
        // bp icon font
        wp_enqueue_style(
            'bp-icon-admin-editor',
            BETTER_PAYMENT_ADMIN_ASSET_URL . 'icon/style.min.css',
            false
        );
    }

	public function register_menu()
	{
		add_menu_page(
			__('Better Payment', 'better-payment'),
			__('Better Payment', 'better-payment'),
			'manage_options',
			$this->page_slug_prefix . '-settings',
			[$this, 'render_better_payment_admin_pages'],
			BETTER_PAYMENT_ADMIN_ASSET_URL . 'img/better-payment-icon-white-small.png',
			64
		);

		add_submenu_page(
			$this->page_slug_prefix . '-settings',
			__('Settings', 'better-payment'),
			__('Settings', 'better-payment'),
			'manage_options',
			$this->page_slug_prefix . '-settings',
			[$this, 'render_better_payment_admin_pages'],
			64
		);

		add_submenu_page(
			$this->page_slug_prefix . '-settings',
			__('Transactions', 'better-payment'),
			__('Transactions', 'better-payment'),
			'manage_options',
			$this->page_slug_prefix . '-transactions',
			[$this, 'render_better_payment_admin_pages'],
			64
		);
	}

	public function render_better_payment_admin_pages()
	{
		$bp_admin_saved_settings = Better_Payment_DB::get_settings();
		$bp_admin_all_transactions_count = Better_Payment_DB::get_transaction_count();

		$template = !empty($_GET['page']) ? sanitize_text_field($_GET['page']) : 'better-payment-settings';

		$menu_slugs = $this->better_payment_menu_slugs();
		$template = in_array($template, $menu_slugs) ? $template : 'better-payment-settings';

		$transaction_pagination_args = [];
		$bp_admin_all_transactions_paginations = '';
		$viewMode = 0;

		if ($template == 'better-payment-transactions') {
			//Transactions Page
			$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
			$id     = isset($_GET['id']) ? intval($_GET['id']) : 0;
			$template = 'better-payment-transaction';

			switch ($action) {
				case 'edit':
					$template .= '-edit';
					break;

				case 'view':
					$viewMode = 1;
					$template .= '-view';
					break;

				default:
					$template .= '-list';
					break;
			}

			$ui_postfix = '';
			$template .= $ui_postfix;

			//Pagination
			$transaction_pagination_paged = isset($_GET['paged']) && intval($_GET['paged']) > 1 ? intval($_GET['paged']) : 1;
			$transaction_pagination_order = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 'DESC';
			$transaction_pagination_orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'id';
			$transaction_pagination_per_page = isset($_GET['per_page']) ? intval($_GET['per_page']) : 20;
			$transaction_pagination_source = isset($_GET['source']) ? sanitize_text_field($_GET['source']) : '';

			if ($transaction_pagination_paged) {
				$transaction_pagination_args = [
					'number' => $transaction_pagination_per_page,
					'offset' => ($transaction_pagination_paged - 1) * $transaction_pagination_per_page,
					'orderby' => $transaction_pagination_orderby,
					'order' => $transaction_pagination_order
				];
			}
			$bp_admin_all_transactions_paginations = self::bp_paginate_links($transaction_pagination_paged, $transaction_pagination_per_page, $bp_admin_all_transactions_count);
			$paginations_showing_entities_html = self::bp_paginate_showing_entities_html(intval($transaction_pagination_paged), intval($transaction_pagination_per_page), intval($bp_admin_all_transactions_count));
		}

		$bp_admin_all_transactions = Better_Payment_DB::get_transactions($transaction_pagination_args);
		$bp_admin_all_transactions_analytics = Better_Payment_DB::get_transactions_analytics();
		
		//Transaction details/view page
		if($viewMode){
			$transaction_id = isset($_REQUEST['id']) ? (int) sanitize_text_field($_REQUEST['id']) : 0;
			if($transaction_id == 0){
				_e('Record not found!', 'better-payment');
				exit;
			}
			$bp_admin_transaction = Better_Payment_DB::get_transaction($transaction_id);
			
			//Fetch referer content
			$better_payment_helper = new Better_Payment_Helper();
			$bp_admin_transaction_form_fields = is_object($bp_admin_transaction) && isset($bp_admin_transaction) ? maybe_unserialize($bp_admin_transaction->form_fields_info) : [];
			$referer_page_id = !empty($bp_admin_transaction_form_fields['referer_page_id']) ? $bp_admin_transaction_form_fields['referer_page_id'] : '';
			$referer_widget_id = !empty($bp_admin_transaction_form_fields['referer_widget_id']) ? $bp_admin_transaction_form_fields['referer_widget_id'] : '';
			
			$bp_transaction_referer_content = '';
			
			if($referer_page_id != '' && $referer_widget_id != ''){
				$bp_transaction_referer_content = $better_payment_helper->get_better_payment_widget_settings($referer_page_id, $referer_widget_id);
			}
		}

		include_once BETTER_PAYMENT_ADMIN_VIEW_PATH . "$template.php";
	}

	public function better_payment_menu_slugs()
	{
		return ['better-payment-settings', 'better-payment-transactions'];
	}

	public static function save_settings_ajax()
	{
		/**
		 * Verify the Nonce
		 */
		if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'better_payment_admin_nonce')) {
			return;
		}

		if (isset($_POST['form_data']) && $_POST['reset_button'] == 'true') {
			self::save_default_settings(1);
			wp_send_json_success("success");
		} else if (isset($_POST['form_data'])) {
			self::save_settings($_POST['form_data']);
			wp_send_json_success("success");
		} else {
			wp_send_json_error("error");
		}

		die;
	}

	/**
	 * This function is responsible for 
	 * save all settings data, including checking the disable field to prevent
	 * users manipulation.
	 *
	 * @param array $values
	 * @return void
	 */
	public static function save_settings($posted_fields = [])
	{
		$saved_settings = Better_Payment_DB::get_settings();
		$default_settings = Better_Payment_DB::default_settings();

		$saved_settings = wp_parse_args($default_settings, $saved_settings); // newly added default values to merge
		
		$data = [];

		if (!empty($posted_fields)) {
			$new_posted_fields = [];
			foreach ($posted_fields as $posted_field) {
				$new_posted_fields[$posted_field['name']] = $posted_field['value'];
			}
		}

		if (count($new_posted_fields)) {
			
			foreach ($saved_settings as $saved_settings_item_key => $saved_settings_item_value) {
				if (isset($new_posted_fields[$saved_settings_item_key]) && $new_posted_fields[$saved_settings_item_key] != '') {
					$data[$saved_settings_item_key] = self::sanitize_field($saved_settings_item_key, $new_posted_fields[$saved_settings_item_key]);
				} else {
					$data[$saved_settings_item_key] = $saved_settings_item_value;
				}
			}
		}

		if (count($data)) {
			Better_Payment_DB::update_settings($data);
		}

		return;
	}

	public static function save_default_settings($reset = 0)
	{
		$default_settings = Better_Payment_DB::default_settings();
		$saved_settings = Better_Payment_DB::get_settings();

		//Do nothing : if already saved && not reset
		if (!empty($saved_settings) && (!$reset)) {
			return false;
		}

		return Better_Payment_DB::update_settings($default_settings);
	}

	public function admin_transaction_view()
	{
		$response = [];

		if (!wp_verify_nonce($_REQUEST['nonce'], 'better_payment_admin_nonce')) {
			$response['message'] = __("Access Denied!", 'better-payment');
			wp_send_json_error($response);
		}

		if (!current_user_can('manage_options')) {
			$response['message'] = __("Access Denied!", 'better-payment');
			wp_send_json_error($response);
		}

		$transaction_id = isset($_REQUEST['id']) ? (int) sanitize_text_field($_REQUEST['id']) : 0;

		$bp_admin_transaction = '';

		if($transaction_id == 0){
			return __('Record not found!', 'better-payment');
		}

		$bp_admin_transaction = Better_Payment_DB::get_transaction($transaction_id);

		include_once BETTER_PAYMENT_ADMIN_VIEW_PATH . "template-transaction-view.php";
		exit;
	}

	public function admin_transaction_delete()
	{
		$response = [];

		if (!wp_verify_nonce($_REQUEST['nonce'], 'better_payment_admin_nonce')) {
			$response['message'] = __("Access Denied!", 'better-payment');
			wp_send_json_error($response);
		}

		if (!current_user_can('manage_options')) {
			$response['message'] = __("Access Denied!", 'better-payment');
			wp_send_json_error($response);
		}

		$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

		if (Better_Payment_DB::delete_transaction($id)) {
			$response['message'] = __("Deleted successfully", 'better-payment');

			$current_page = sanitize_text_field($_REQUEST['currentPage']);
			$per_page = sanitize_text_field($_REQUEST['perPage']);
			$total_entry_count = sanitize_text_field($_REQUEST['totalEntryCount']);

			$response['pagination_showing_entities_html'] = self::bp_paginate_showing_entities_html(intval($current_page), intval($per_page), intval($total_entry_count) - 1);
			
			wp_send_json_success($response);
		} else {
			$response['message'] = __("Something went wrong!", 'better-payment');
			wp_send_json_error( $response );
		}
	}

	public function admin_transaction_filter()
	{
		$response = [];

		if (!wp_verify_nonce($_REQUEST['nonce'], 'better_payment_admin_nonce')) {
			$response['message'] = __("Access Denied!", 'better-payment');
			wp_send_json_error($response);
		}

		if (!current_user_can('manage_options')) {
			$response['message'] = __("Access Denied!", 'better-payment');
			wp_send_json_error($response);
		}
		
		$filter_search_text = ( isset($_REQUEST['filterFormData']['search_text']) && !empty($_REQUEST['filterFormData']['search_text']) ) ? sanitize_text_field( $_REQUEST['filterFormData']['search_text'] ) : '';
		$filter_payment_date_from = ( isset($_REQUEST['filterFormData']['payment_date_from']) && !empty($_REQUEST['filterFormData']['payment_date_from']) ) ? sanitize_text_field( $_REQUEST['filterFormData']['payment_date_from'] ) : '';
		$filter_payment_date_to = ( isset($_REQUEST['filterFormData']['payment_date_to']) && !empty($_REQUEST['filterFormData']['payment_date_to']) ) ? sanitize_text_field( $_REQUEST['filterFormData']['payment_date_to'] ) : '';
		$filter_order_by = ( isset($_REQUEST['filterFormData']['order_by']) && !empty($_REQUEST['filterFormData']['order_by']) ) ? sanitize_text_field( $_REQUEST['filterFormData']['order_by'] ) : 'id';
		$filter_order = ( isset($_REQUEST['filterFormData']['order']) && !empty($_REQUEST['filterFormData']['order']) ) ? sanitize_text_field( $_REQUEST['filterFormData']['order'] ) : 'DESC';
		$filter_source = ( isset($_REQUEST['filterFormData']['source']) && !empty($_REQUEST['filterFormData']['source']) ) ? sanitize_text_field( $_REQUEST['filterFormData']['source'] ) : '';
		$filter_paged = ( isset($_REQUEST['filterFormData']['paged']) && !empty($_REQUEST['filterFormData']['paged']) ) ? intval( $_REQUEST['filterFormData']['paged'] ) : 1;
		$filter_per_page = ( isset($_REQUEST['filterFormData']['per_page']) && !empty($_REQUEST['filterFormData']['per_page']) ) ? intval( $_REQUEST['filterFormData']['per_page'] ) : 20;
		
		$args = array(
            'search_text' => $filter_search_text,
            'payment_date_from' => $filter_payment_date_from, 
            'payment_date_to' => $filter_payment_date_to, 
            'order_by' => $filter_order_by,
            'order' => $filter_order,
            'source' => $filter_source, 
            'paged' => $filter_paged, 
            'per_page' => $filter_per_page, 
        );

		if( !empty($args['payment_date_from']) ){
			$args['payment_date_from'] = date('Y-m-d H:i:s',strtotime($args['payment_date_from']));
		}
		if( !empty($args['payment_date_to']) ){
			$args['payment_date_to'] = date('Y-m-d H:i:s',strtotime($args['payment_date_to']));
		}

		$args['offset'] = ( $args['paged'] - 1) * $args['per_page'];  
		$bp_admin_all_transactions = Better_Payment_DB::get_transactions($args);
		
		//Pagination
		$bp_admin_all_transactions_count = Better_Payment_DB::get_transaction_count($args);
		$bp_admin_all_transactions_paginations = self::bp_paginate_links($args['paged'], $args['per_page'], $bp_admin_all_transactions_count );
		$paginations_showing_entities_html = self::bp_paginate_showing_entities_html($args['paged'], $args['per_page'], $bp_admin_all_transactions_count );

		include_once BETTER_PAYMENT_ADMIN_VIEW_PATH . "template-transaction-list.php";
		exit;
	}

	public static function bp_paginate_links($current_page, $per_page, $total_entry_count)
	{
		$pagination_html = '';

		$current_page = intval($current_page);
		$per_page = intval($per_page);
		$total_entry_count = intval($total_entry_count);

		$total_page_count = ceil($total_entry_count / $per_page);

		$previous_page = $current_page - 1;
		$next_page = $current_page + 1;

		$prev_button_disabled = (1 == $current_page) ? 'disabled' : '';
		$next_button_disabled = $total_page_count == $current_page ? 'disabled' : '';

		$prev_button_link = ($current_page > 1) ? admin_url("admin.php?page=better-payment-transactions&per_page={$per_page}&paged={$previous_page}") : "#";
		$next_button_link = ($current_page < $total_page_count) ? admin_url("admin.php?page=better-payment-transactions&per_page={$per_page}&paged={$next_page}") : "#";

		$showing_entry_from = $per_page * ($current_page - 1) + 1;
		
		$pagination_html .= "<ul class=''>";

		//Showing entries content
		if ($showing_entry_from > $total_entry_count) {
			return '';
		}

		if ( $total_page_count == 1 ) {
			return '';
		}
		
		//Previous button
		if($current_page > 1){
			$pagination_html .= "<li><a href='$prev_button_link' $prev_button_disabled  class='pagination-previous'><i class='bp-icon bp-caret-left'></i></a></li>";
		}

		//Pagination links 
		for ($i = 1; $i <= $total_page_count; $i++) {
			$is_current_class = ($i == $current_page) ? 'is-current active' : '';
			$page_link = admin_url("admin.php?page=better-payment-transactions&per_page={$per_page}&paged={$i}");

			$pagination_html .= "
					<li>
						<a href=\"$page_link\" class=\"pagination-link $is_current_class \" aria-label=\"Page $i\"> $i </a>
					</li>
				";
		}

		//Next button
		if($current_page < $total_page_count){
			$pagination_html .= "<li><a href='$next_button_link' class='pagination-next' $next_button_disabled><i class='bp-icon bp-caret-right'></i></a></li>";
		}

		$pagination_html .= "</ul>";

		return $pagination_html;
	}

	public static function bp_paginate_showing_entities_html($current_page, $per_page, $total_entry_count)
	{
		$pagination_showing_entries_html = '';

		$current_page = intval($current_page);
		$per_page = intval($per_page);
		$total_entry_count = intval($total_entry_count);

		$total_page_count = ceil($total_entry_count / $per_page);

		$showing_entry_from = $per_page * ($current_page - 1) + 1;
		$showing_entry_to = $per_page * $current_page;

		//Showing entries content
		if ($total_page_count == 1 || $current_page == $total_page_count) {
			$showing_entry_to = $total_entry_count;
		}

		$pagination_showing_entries_html = __("Showing {$showing_entry_from}-{$showing_entry_to} of $total_entry_count entries", "better-payment");

		if ($showing_entry_from > $total_entry_count) {
			return '';
		}

		if ($total_page_count == 1) {
			return $pagination_showing_entries_html;
		}

		return $pagination_showing_entries_html;
	}

	/**
	 * This function is responsible for the data sanitization
	 *
	 * @param array $field
	 * @param string|array $value
	 * @return string|array
	 */
	public static function sanitize_field($field, $value)
	{
		if (isset($field['sanitize']) && !empty($field['sanitize'])) {
			if (function_exists($field['sanitize'])) {
				$value = call_user_func($field['sanitize'], $value);
			}
			return $value;
		}

		if (is_array($field) && isset($field['type'])) {
			switch ($field['type']) {
				case 'text':
					$value = sanitize_text_field($value);
					break;
				case 'textarea':
					$value = sanitize_textarea_field($value);
					break;
				case 'email':
					$value = sanitize_email($value);
					break;
				default:
					return $value;
					break;
			}
		} else {
			$value = sanitize_text_field($value);
		}

		return $value;
	}
}
