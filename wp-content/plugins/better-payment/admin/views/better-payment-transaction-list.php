<?php
/*
 * Transactions list page
 *  All undefined vars comes from 'render_better_payment_admin_pages' method
 *  $bp_admin_all_transactions : contains all values
 */
?>

<?php
$bp_admin_settings_transactions_all_count = isset($bp_admin_all_transactions_analytics['all']) ? $bp_admin_all_transactions_analytics['all'] : 0;
$bp_admin_settings_transactions_paid_count = isset($bp_admin_all_transactions_analytics['paid']) ? $bp_admin_all_transactions_analytics['paid'] : 0;
$bp_admin_settings_transactions_processing_count = $bp_admin_settings_transactions_all_count - $bp_admin_settings_transactions_paid_count;
?>

<div class="better-payment">
    <div class="template__wrapper background__grey better-payment-admin-transactions-page">
        <header class="pb30">
            <div class="bp-container">
                <div class="bp-row">
                    <div class="bp-col">
                        <div class="logo">
                            <a href="javascript:void(0)"><img src="<?php echo esc_url(BETTER_PAYMENT_ADMIN_ASSET_URL . 'img/logo.svg'); ?>" alt=""></a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <div class="bp-container">
            <div class="bp-row">
                <div class="bp-col-lg-4 bp-col-sm-6 bp-col">
                    <div class="statistic">
                        <div class="icon">
                            <i class="bp-icon bp-swap"></i>
                        </div>
                        <div class="statistic__body">
                            <h3><?php _e($bp_admin_settings_transactions_all_count, 'better-payment'); ?></h3>
                            <p><?php _e('Total Transactions', 'better-payment'); ?></p>
                        </div>
                    </div>
                </div>
                <div class="bp-col-lg-4 bp-col-sm-6 bp-col">
                    <div class="statistic">
                        <div class="icon">
                            <i class="bp-icon bp-list-check"></i>
                        </div>
                        <div class="statistic__body">
                            <h3><?php _e($bp_admin_settings_transactions_paid_count, 'better-payment'); ?></h3>
                            <p><?php _e('Completed Transactions', 'better-payment'); ?></p>
                        </div>
                    </div>
                </div>
                <div class="bp-col-lg-4 bp-col-sm-6 bp-col">
                    <div class="statistic">
                        <div class="icon">
                            <i class="bp-icon bp-server"></i>
                        </div>
                        <div class="statistic__body">
                            <h3><?php _e($bp_admin_settings_transactions_processing_count, 'better-payment'); ?></h3>
                            <p><?php _e('Processing Transactions', 'better-payment'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bp-row">
                <div class="bp-col">
                    <div class="transaction-filter-wrapper transactions">
                        <div class="transaction__filter">
                            <form action="#" id="better-payment-admin-settings-form">
                                <div class="mb20">
                                    <label for="#"><?php _e('Search', 'better-payment'); ?></label>
                                    <div class="form__group">
                                        <input name="search_text" type="text" class="form__control serch-text" placeholder="<?php _e('Search here', 'better-payment'); ?>" value="<?php echo isset($search_text) ? esc_attr($search_text) : '' ?>" title="<?php _e('Search by email, amount, transaction id, source', 'better-payment'); ?>" >
                                    </div>
                                </div>
                                <div class="mb20">
                                    <label for="#"> <?php _e('From Date', 'better-payment'); ?> </label>
                                    <div class="form__group">
                                        <input name="payment_date_from" type="text" class="form__control payment_date_from bp-datepicker" placeholder="<?php _e('Payment date', 'better-payment'); ?>">
                                    </div>
                                </div>
                                
                                <div class="mb20">
                                    <label for="#"> <?php _e('To Date', 'better-payment'); ?> </label>
                                    <div class="form__group">
                                        <input name="payment_date_to" type="text" class="form__control payment_date_to bp-datepicker" placeholder="<?php _e('Payment date', 'better-payment'); ?>">
                                    </div>
                                </div>
                                
                                <div class="mb20">
                                    <label for="#"><?php _e('Sort By', 'better-payment'); ?></label>
                                    <div class="bp-select">
                                        <select name="order_by" class="order-by">
                                            <option value="payment_date" <?php ( $transaction_pagination_orderby === 'payment_date' || $transaction_pagination_orderby === 'id' ) ? 'selected' : ''; ?> > <?php _e('Payment Date', 'better-payment'); ?> </option>
                                            <option value="email" <?php $transaction_pagination_orderby === 'email' ? 'selected' : ''; ?> > <?php _e('Email', 'better-payment'); ?> </option>
                                            <option value="amount" <?php $transaction_pagination_orderby === 'amount' ? 'selected' : ''; ?> > <?php _e('Amount', 'better-payment'); ?> </option>
                                            <option value="status" <?php $transaction_pagination_orderby === 'status' ? 'selected' : ''; ?> > <?php _e('Status', 'better-payment'); ?> </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb20">
                                    <label for="#"> <?php _e('Sort Order', 'better-payment'); ?> </label>
                                    <div class="bp-select">
                                        <select name="order" class="order">
                                            <option value="DESC" <?php echo $transaction_pagination_order === 'DESC' ? 'selected' : ''; ?> > <?php _e('Descending', 'better-payment'); ?> </option>
                                            <option value="ASC" <?php echo $transaction_pagination_order === 'ASC' ? 'selected' : ''; ?> > <?php _e('Ascending', 'better-payment'); ?> </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb20">
                                    <label for="#"> <?php _e('Source', 'better-payment'); ?> </label>
                                    <div class="bp-select">
                                        <select name="source" class="source">
                                            <option value=""> <?php _e('All', 'better-payment'); ?> </option>
                                            <option value="paypal" <?php echo $transaction_pagination_source === 'paypal' ? 'selected' : ''; ?> > <?php _e('PayPal', 'better-payment'); ?> </option>
                                            <option value="stripe" <?php echo $transaction_pagination_source === 'stripe' ? 'selected' : ''; ?> > <?php _e('Stripe', 'better-payment'); ?> </option>
                                        </select>
                                    </div>
                                </div>
                                <button class="button button__active better-payment-transaction-filter"> <?php _e('Filter', 'better-payment'); ?> </button>
                            </form>
                        </div>
                    </div>

		            <?php include_once BETTER_PAYMENT_ADMIN_VIEW_PATH . "template-transaction-list.php"; ?>
                    
                </div>
            </div>
        </div>
    </div>
</div>