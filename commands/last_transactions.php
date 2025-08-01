<?php

if ($text == '📊 تراکنش‌ های من') {
    $responseText = "لطفا یکی از گزینه های زیر را انتخاب کنید: ";
    sendMessage($from_id, $responseText, $userTransactionKeyboard);
    die;
}

if ($text == '🛒 سفارشات ممبر') {
    $query = "SELECT * FROM `orders` WHERE `chat_id` = ? ORDER BY `created_at` DESC LIMIT 5";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$from_id]);
    $orders = $stmt->fetchAll();

    if (count($orders) > 0) {
        $responseText = "📦 *آخرین سفارشات شما:*\n\n";

        foreach ($orders as $order) {
            $persianDate = jdate('Y/m/d', strtotime($order->created_at));

            $status = $order->status == 1 ? "✅ تکمیل شده" : "🕒 در صف انجام";
            $responseText .= "🔘 *کد سفارش:* {$order->track_id}\n";
            $responseText .= "🔗 *شناسه کانال:* {$order->channel_username}\n";
            $responseText .= "👥 *تعداد:* {$order->count}\n";
            $responseText .= "📅 *تاریخ سفارش:* {$persianDate}\n";
            $responseText .= "🟢 *وضعیت:* $status\n\n";
        }

        sendMessage($from_id, $responseText);
    } else {
        sendMessage($from_id, "❌ هیچ سفارشی پیدا نشد.");
    }
    die;
}

if ($text == '💳 شارژ حساب') {
    $query = "SELECT * FROM `transactions` WHERE `chat_id` = ? AND `is_paid` = 1 ORDER BY `created_at` DESC LIMIT 5";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$from_id]);
    $transactions = $stmt->fetchAll();

    if (count($transactions) > 0) {
        $responseText = "💳 *تاریخچه شارژ حساب:*\n\n";

        foreach ($transactions as $transaction) {
            $persianDate = jdate('Y/m/d', strtotime($transaction->created_at));

            $status = $transaction->is_paid == 1 ? "✅ پرداخت شده" : "❌ پرداخت نشده";
            $responseText .= "🔘 *کد تراکنش:* {$transaction->track_id}\n";
            $responseText .= "💰 *مبلغ:* " . number_format($transaction->amount) . " تومان\n";
            $responseText .= "📅 *تاریخ:* {$persianDate}\n";
            $responseText .= "🟢 *وضعیت:* $status\n\n";
        }

        sendMessage($from_id, $responseText);
    } else {
        sendMessage($from_id, "❌ هیچ تراکنشی پیدا نشد.");
    }
    die;
}

if ($text == '🔍 جستجوی سفارش') {
    sendMessage($from_id, "🔎 لطفا *کد سفارش* خود را وارد کنید:", $backToMainMenu);
    setStep($from_id, "search_order");
    die;
}

if ($currentUser->step == "search_order") {
    $track_id = trim($text);

    $query = "SELECT * FROM `orders` WHERE `track_id` = ? AND `chat_id` = ? LIMIT 1";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$track_id, $from_id]);
    $order = $stmt->fetch(PDO::FETCH_OBJ);

    if ($order) {
        $persianDate = jdate('Y/m/d', strtotime($order->created_at));

        $status = $order->status == 1 ? "✅ تکمیل شده" : "🕒 در صف انجام";
        $responseText = "📦 *جزئیات سفارش:*\n\n";
        $responseText .= "🔘 *کد سفارش:* {$order->track_id}\n";
        $responseText .= "📱 *نام کانال:* {$order->channel_name}\n";
        $responseText .= "🔗 *نام کاربری کانال:* {$order->channel_username}\n";
        $responseText .= "👥 *تعداد:* {$order->count}\n";
        $responseText .= "💰 *مبلغ کل:* " . number_format($order->total_cost) . " تومان\n";
        $responseText .= "💵 *مبلغ واحد:* " . number_format($order->unit_cost) . " تومان\n";
        $responseText .= "📅 *تاریخ سفارش:* {$persianDate}\n";
        $responseText .= "🟢 *وضعیت:* $status\n\n";

        sendMessage($from_id, $responseText, $userTransactionKeyboard);
    } else {
        sendMessage($from_id, "❌ سفارشی با این کد پیدا نشد!", $userTransactionKeyboard);
    }
    setStep($from_id, "home");
    die;
}

if ($text == '🔰 برداشت از حساب') {
    $query = "SELECT * FROM `withdrawals` WHERE `chat_id` = ? ORDER BY `created_at` DESC LIMIT 5";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$from_id]);
    $withdrawals = $stmt->fetchAll();

    if (count($withdrawals) > 0) {
        $responseText = "🔰 *تاریخچه برداشت از حساب:*\n\n";

        foreach ($withdrawals as $withdrawal) {
            $persianDate = jdate('Y/m/d', strtotime($withdrawal->created_at));
            $type = $withdrawal->key_;
            $destination = $withdrawal->value_;
            $amount = number_format($withdrawal->amount);
            $trackId = $withdrawal->track_id;

            switch ($withdrawal->status) {
                case 'pending':
                    $status = "⏳ در حال بررسی";
                    break;
                case 'completed':
                    $status = "✅ پرداخت شده";
                    break;
                case 'rejected':
                    $status = "❌ رد شده";
                    break;
                default:
                    $status = "❓ نامشخص";
            }

            $responseText .= "🎟 *کد پیگیری:* $trackId\n";
            $responseText .= "💰 *مبلغ:* {$amount} تومان\n";
            $responseText .= "📤 *نوع برداشت:* $type\n";
            $responseText .= "🎯 *مقصد:* $destination\n";
            $responseText .= "📅 *تاریخ:* $persianDate\n";
            $responseText .= "🟢 *وضعیت:* $status\n\n";
        }

        sendMessage($from_id, $responseText);
    } else {
        sendMessage($from_id, "❌ هیچ سابقه‌ای از برداشت پیدا نشد.");
    }
    die;
}
