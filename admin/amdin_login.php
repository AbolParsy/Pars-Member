<?php

if (($text == 'پنل' || $text == 'بازگشت به مدیریت') && $currentUser->is_admin) {
    sendMessage($from_id, "سلام مدیر گرامی\n*به پنل مدیریت خوش آمدید*", $adminKeyboard);
    setStep($from_id, 'panel');
    die;
}

if ($text == '📊 آمار ربات' && $currentUser->is_admin) {
    $query = "SELECT COUNT(*) as total FROM `orders`";
    $stmt = $pdo->query($query);
    $totalOrders = $stmt->fetch()->total;

    $query = "SELECT COUNT(*) as done FROM `orders` WHERE `status` = 1";
    $stmt = $pdo->query($query);
    $doneOrders = $stmt->fetch()->done;

    $query = "SELECT COUNT(*) as pending FROM `orders` WHERE `status` = 0";
    $stmt = $pdo->query($query);
    $pendingOrders = $stmt->fetch()->pending;

    $query = "SELECT COUNT(*) as total_users FROM `users`";
    $stmt = $pdo->query($query);
    $totalUsers = $stmt->fetch()->total_users;

    $query = "SELECT COUNT(*) as banned_today FROM `users` WHERE `is_banned` = 1";
    $stmt = $pdo->query($query);
    $bannedToday = $stmt->fetch()->banned_today;

    $query = "SELECT COUNT(*) as today_total FROM `orders` WHERE DATE(`created_at`) = CURDATE()";
    $stmt = $pdo->query($query);
    $todayOrders = $stmt->fetch()->today_total;

    $query = "SELECT COUNT(*) as today_done FROM `orders` WHERE `status` = 1 AND DATE(`created_at`) = CURDATE()";
    $stmt = $pdo->query($query);
    $todayDone = $stmt->fetch()->today_done;

    $query = "SELECT COUNT(*) as today_pending FROM `orders` WHERE `status` = 0 AND DATE(`created_at`) = CURDATE()";
    $stmt = $pdo->query($query);
    $todayPending = $stmt->fetch()->today_pending;

    $responseText  = "📊 *آمار کلی ربات:*\n\n";
    $responseText .= "👥 تعداد کاربران: *$totalUsers*\n";
    $responseText .= "🚫 کاربران بلاک‌شده: *$bannedToday*\n\n";
    $responseText .= "📦 کل سفارشات ثبت‌شده: *$totalOrders*\n";
    $responseText .= "✅ سفارشات تکمیل‌شده: *$doneOrders*\n";
    $responseText .= "⏳ سفارشات در انتظار: *$pendingOrders*\n\n";
    $responseText .= "📆 *آمار امروز:*\n";
    $responseText .= "📥 سفارشات امروز: *$todayOrders*\n";
    $responseText .= "✅ امروز تکمیل‌ شده: *$todayDone*\n";
    $responseText .= "⏳ امروز در انتظار: *$todayPending*\n";

    sendMessage($from_id, $responseText);
    die;
}
