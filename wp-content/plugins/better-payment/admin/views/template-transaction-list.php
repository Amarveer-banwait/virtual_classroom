<?php
/*
 * Transactions parial content
 *  All undefined vars comes from 'render_better_payment_admin_pages' method
 *  $bp_admin_all_transactions : contains all values
 */
?>

<?php
$transaction_filter_paged = !empty($args['paged']) ? $args['paged'] : 1;
$transaction_filter_per_page = !empty($args['per_page']) ? $args['per_page'] : 20;
?>

<div class="transaction-table-wrapper">
    <div class="transactions">
        <!-- Hidden Fields Start -->
        <div class="hidden-fields">
            <input type="hidden" name="paged" class="paged" value="<?php echo esc_attr($transaction_filter_paged); ?>">
            <input type="hidden" name="per_page" class="per-page" value="<?php echo esc_attr($transaction_filter_per_page); ?>">
            <input type="hidden" name="total_entry" class="total-entry" value="<?php echo esc_attr($bp_admin_all_transactions_count); ?>">
        </div>
        <!-- Hidden Fields End  -->

        <div class="transaction__table mb30">
            <div class="table__row row__head">
                <div class="table__col col__head col__name">
                    <p><?php _e('Name', 'better-payment'); ?></p>
                </div>
                <div class="table__col col__head col__email">
                    <p><?php _e('Email Address', 'better-payment'); ?></p>
                </div>
                <div class="table__col col__head col__amount">
                    <p><?php _e('Amount', 'better-payment'); ?></p>
                </div>
                <div class="table__col col__head col__trans">
                    <p><?php _e('Transaction ID', 'better-payment'); ?></p>
                </div>
                <div class="table__col col__head col__source">
                    <p><?php _e('Source', 'better-payment'); ?></p>
                </div>
                <div class="table__col col__head col__status">
                    <p><?php _e('Status', 'better-payment'); ?></p>
                </div>
                <div class="table__col col__head col__amount">
                    <p><?php _e('Date', 'better-payment'); ?></p>
                </div>
                <div class="table__col col__head col__action">
                    <p><?php _e('Action', 'better-payment'); ?></p>
                </div>
            </div>

            <?php $bp_txn_counter = 0; ?>
            
            <?php foreach ($bp_admin_all_transactions as $bp_transaction) : ?>
                <?php $bp_txn_counter++; ?>
                <?php $bp_customer_info = maybe_unserialize($bp_transaction->customer_info); //obj 
                ?>
                <?php $bp_form_fields_info = maybe_unserialize($bp_transaction->form_fields_info); //array 
                ?>

                <?php
                $bp_transaction_customer_name = isset($bp_form_fields_info['first_name']) ? sanitize_text_field($bp_form_fields_info['first_name']) : '';
                $bp_transaction_customer_name .= ' ';
                $bp_transaction_customer_name .= isset($bp_form_fields_info['last_name']) ? sanitize_text_field($bp_form_fields_info['last_name']) : '';
                ?>

                <div class="table__row">
                    <div class="table__col col__name">
                        <p> <?php echo esc_html($bp_transaction_customer_name); ?> </p>
                    </div>
                    <div class="table__col col__email">
                        <p> <?php echo esc_html($bp_transaction->email); ?> </p>
                    </div>
                    <div class="table__col col__amount">
                        <p> <?php echo esc_html($bp_transaction->currency) . ' ' . esc_html($bp_transaction->amount); ?> </p>
                    </div>
                    <div class="table__col col__trans">
                        <?php $bp_transaction_id = sanitize_text_field($bp_transaction->transaction_id);  ?>
                        
                        <?php if( !empty($bp_transaction_id) ) : ?>
                            <p> <span id="bp_copy_clipboard_input_<?php echo esc_html($bp_txn_counter); ?>"><?php echo esc_html($bp_transaction_id); ?></span> <span id="bp_copy_clipboard_<?php echo esc_attr($bp_txn_counter); ?>" class="bp-icon bp-copy-square bp-copy-clipboard" title="<?php _e('Copy', 'better-payment'); ?>" data-bp_txn_counter="<?php echo esc_attr($bp_txn_counter); ?>" ></span> </p>
                        <?php endif; ?>
                    </div>
                    <div class="table__col col__source">
                        <p> <?php echo esc_html($bp_transaction->source); ?> </p>
                    </div>
                    <div class="table__col col__status">
                        <?php
                        $bp_transaction_status = $bp_transaction->status ? sanitize_text_field($bp_transaction->status) : __('N/A', 'better-payment');
                        $bp_transaction_status_color = $bp_transaction_status == 'paid' ? 'color__primary ' : 'color__danger';
                        $bp_transaction_status_color = $bp_transaction_status == 'N/A' ? 'color__warning' : $bp_transaction_status_color;
                        ?>
                        <p class="<?php echo esc_attr($bp_transaction_status_color); ?>" data-id="<?php echo esc_attr($bp_transaction->id) ?>"> <?php echo esc_html(ucfirst($bp_transaction_status)); ?> </p>
                    </div>
                    <div class="table__col col__amount">
                        <?php $bp_payment_date = sanitize_text_field($bp_transaction->payment_date); ?>
                        <?php $bp_payment_date = wp_date(get_option('date_format').' '.get_option('time_format'), strtotime($bp_payment_date)); ?>
                        <p> <?php echo esc_html($bp_payment_date); ?> </p>
                    </div>
                    <div class="table__col col__action action-buttons">
                        <a href='<?php echo esc_url(admin_url("admin.php?page=better-payment-transactions&action=view&id={$bp_transaction->id}")); ?>' class="button button--sm view-button" data-id="<?php echo esc_attr($bp_transaction->id) ?>"><?php echo __('View', 'better-payment'); ?></a>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (count($bp_admin_all_transactions) == 0) : ?>
                <div class="table__row">
                    <div class="table__col col__name">
                        <p class="text-center"> <?php echo __('No records found!', 'better-payment'); ?> </p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Pagination Starts  -->
    <div class="transaction-pagination pagination pt30">
        <div class="bp-row">
            <div class="bp-col-3">
                <p class="showing-entities-html"><?php echo wp_kses( $paginations_showing_entities_html, wp_kses_allowed_html( 'post' ) ); ?></p>
            </div>

            <div class="bp-col-9">
                <?php echo wp_kses( $bp_admin_all_transactions_paginations, wp_kses_allowed_html( 'post' ) ); ?>
            </div>
        </div>
    </div>
    <!-- Pagination Ends  -->
</div>