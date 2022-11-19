<?php

function buy_microloan(int $microloan_id, int $user_id, int $auto_pledge = 0, bool $buyback = false, int $buy_order = 0, int $sell_order = 0) : string
{
    global $wpdb;

    if (function_exists('log_microloan_trading_event')) {
        log_microloan_trading_event($microloan_id, 'purchase_attempt', $auto_pledge, $buy_order, $sell_order);
    }

    $microloan = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}loan_offers 
                                 WHERE id = {$microloan_id}
                                 AND status IN(3,4)
                                 AND for_sale > 0
                                 AND user_id != {$user_id}");

    if (!$microloan->id) return 'microloan_not_found';

    $user_balance = (float)get_user_balance($user_id);
    $microloan_price = (float)get_microloan_price($microloan_id);

    $for_sale_flag = $microloan->for_sale == 3 ? 3 : 0;

    //check if buyback enabled
    if ($buyback) {
        $buyback_amount = (float)$wpdb->get_var("SELECT SUM(amount) FROM {$wpdb->prefix}loan_offers WHERE user_id = {$user_id} AND status = 20");
        $user_balance += $buyback_amount;
        $microloan_price = $microloan->capital_outstanding;
        $microloan->discount_premium = 0;
    }

    if ($user_balance && $microloan_price && $user_balance >= $microloan_price) {
        $wpdb->query('START TRANSACTION');

        if ($wpdb->get_col("SELECT id FROM {$wpdb->prefix}loan_offers WHERE from_bid = {$microloan_id}")) {
            $wpdb->query('ROLLBACK');
            return 'sale_duplication';
        }

        if ($wpdb->get_var("SELECT id FROM {$wpdb->prefix}loan_offers WHERE id = {$microloan_id} AND status = 8")) {
            $wpdb->query('ROLLBACK');
            return 'sale_duplication';
        }

        $update_sold_ml = $wpdb->query("UPDATE {$wpdb->prefix}loan_offers 
                                        SET status = 8, for_sale = {$for_sale_flag} 
                                        WHERE id = {$microloan_id} AND status != 8");

        //create new microloan for buyer
        $wpdb->insert($wpdb->prefix . 'loan_offers',
            [
                'user_id'             => $user_id,
                'application_id'      => $microloan->application_id,
                'status'              => 3,
                'rate'                => $microloan->rate,
                'amount'              => $microloan->amount,
                'date'                => date("Y-m-d H:i:s"),
                'auto_pledge'         => $auto_pledge,
                'capital_outstanding' => $microloan->capital_outstanding,
                'from_bid'            => $microloan_id,
                'pmt'                 => $microloan->pmt
            ]
        );

        $insert_ml_id = $wpdb->insert_id;

        //insert seller funds transaction
        $balance_before = (float)$wpdb->get_var("SELECT SUM(amount) FROM {$wpdb->prefix}ameuri_funds 
                                                 WHERE user = {$microloan->user_id} AND confirmed = 1");
        $balance_after = $balance_before + $microloan_price;

        $seller_funds_id = $wpdb->insert($wpdb->prefix . 'ameuri_funds',
            [
                'user'             => $microloan->user_id,
                'funddate'         => date("Y-m-d H:i:s"),
                'currency'         => 'GBP',
                'amount'           => $microloan_price,
                'x_rate'           => 1,
                'gbp_amount'       => $microloan_price,
                'balance_before'   => $balance_before,
                'balance_after'    => $balance_after,
                'confirmed'        => 1,
                'transaction_type' => 3,
                'application_id'   => $microloan->application_id
            ]
        );

        //insert buyer funds transaction
        $balance_before = $wpdb->get_var("SELECT SUM(amount) FROM {$wpdb->prefix}ameuri_funds 
                                          WHERE user = {$user_id} AND confirmed = 1");
        $balance_after = $balance_before - $microloan_price;

        $buyer_funds_id = $wpdb->insert($wpdb->prefix . 'ameuri_funds',
            [
                'user'             => $user_id,
                'funddate'         => date("Y-m-d H:i:s"),
                'currency'         => 'GBP',
                'amount'           => -$microloan_price,
                'x_rate'           => 1,
                'gbp_amount'       => -$microloan_price,
                'balance_before'   => $balance_before,
                'balance_after'    => $balance_after,
                'confirmed'        => 1,
                'transaction_type' => 4,
                'application_id'   => $microloan->application_id
            ]
        );

        //create fee to seller
        $microloan_fee_percentage = (float)get_option('microloan_transaction_fee_percentage');

        if ($microloan_fee_percentage > 0) {
            $fee_amount = round($microloan_price * $microloan_fee_percentage,2);

            $wpdb->insert($wpdb->prefix . 'rebs_fees',
                [
                    'date'           => date("Y-m-d H:i:s"),
                    'user'           => $microloan->user_id,
                    'amount'         => $fee_amount,
                    'status'         => 3,
                    'microloan_id'   => $microloan->id,
                    'application_id' => $microloan->application_id
                ]
            );

            $fee_id = $wpdb->insert_id;

            //owned debit
            $balance_before = $wpdb->get_var("SELECT SUM(amount) FROM {$wpdb->prefix}ameuri_funds 
                                              WHERE user = {$microloan->user_id} AND confirmed = 1");
            $balance_after = $balance_before - $fee_amount;

            $wpdb->insert($wpdb->prefix . 'ameuri_funds',
                [
                    'user'             => $microloan->user_id,
                    'funddate'         => date("Y-m-d H:i:s"),
                    'currency'         => 'GBP',
                    'amount'           => -$fee_amount,
                    'x_rate'           => 1,
                    'gbp_amount'       => -$fee_amount,
                    'balance_before'   => $balance_before,
                    'balance_after'    => $balance_after,
                    'confirmed'        => 1,
                    'transaction_type' => 30,
                    'application_id'   => $microloan->application_id,
                    'rebs_fees_id'     => $fee_id
                ]
            );
        } else {
            $fee_amount = 0;
        }

        if ($update_sold_ml && $insert_ml_id && $seller_funds_id && $buyer_funds_id) {
            $wpdb->query('COMMIT');

            do_action('ameuri_after_buy_microloan', $microloan, $insert_ml_id, $user_id);

            $notification_status = apply_filters('ameuri_microloan_notification_status', true);
            if ($notification_status) {
                AmeuriEmail::buy_microloan_notification($microloan, $fee_amount, $buyback);
            }

            if (function_exists('log_microloan_trading_event')) {
                log_microloan_trading_event($microloan->id, 'purchased', $auto_pledge, $buy_order, $sell_order);
            }

            return 'ok';
        } else {
            $wpdb->query('ROLLBACK');

            if (function_exists('log_microloan_trading_event')) {
                log_microloan_trading_event($microloan->id, 'purchase_error', $auto_pledge, $buy_order, $sell_order);
            }

            return 'purchase_error';
        }
    }

    return 'not_enough_funds';
}