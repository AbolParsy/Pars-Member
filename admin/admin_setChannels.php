<?php

if ($text == '📡 تنظیم کانال‌ ها' && $currentUser->is_admin) {
    sendMessage($from_id, "🔧 شما اکنون در بخش تنظیمات کانال‌ها هستید. لطفاً یکی از گزینه‌های زیر را انتخاب کنید تا تنظیمات مربوطه را مدیریت کنید:", $setChannelKeyboard);
    die;
}

if ($text == '🧾 لاگ کاربران' && $currentUser->is_admin) {
    sendMessage($from_id, "🔢 لطفاً *شناسه عددی* کانال لاگ کاربران را ارسال کنید:", $backToAdminKeyboard);
    setStep($from_id, 'set-log-channel');
    die;
}

if ($currentUser->step == 'set-log-channel' && $currentUser->is_admin) {
    if (!is_numeric($text)) {
        sendMessage($from_id, "❌ شناسه وارد شده معتبر نیست.\nلطفاً فقط *عدد* وارد کنید:", $backToAdminKeyboard);
        die;
    }

    $query = "SELECT * FROM `settings` WHERE `_key` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['userLogChannelId']);
    $exists = $stmt->fetch();

    if ($exists) {
        $query = "UPDATE `settings` SET `_value` = ? WHERE `_key` = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$text, 'userLogChannelId']);
    } else {
        $query = "INSERT INTO `settings` (`_key`, `_value`) VALUES (?, ?)";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['userLogChannelId', $text]);
    }

    sendMessage($from_id, "✅ شناسه کانال لاگ کاربران با موفقیت ذخیره شد.", $setChannelKeyboard);
    setStep($from_id, 'panel');
    die;
}

if ($text == '💳 پرداخت ها' && $currentUser->is_admin) {
    sendMessage($from_id, "🔢 لطفاً *شناسه عددی* کانال گزارشات خرید را ارسال کنید:", $backToAdminKeyboard);
    setStep($from_id, 'set-purchase-channel');
    die;
}

if ($currentUser->step == 'set-purchase-channel' && $currentUser->is_admin) {
    if (!is_numeric($text)) {
        sendMessage($from_id, "❌ شناسه وارد شده معتبر نیست.\nلطفاً فقط *عدد* وارد کنید:", $backToAdminKeyboard);
        die;
    }

    $query = "SELECT * FROM `settings` WHERE `_key` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['purchaseReportChannelId']);
    $exists = $stmt->fetch();

    if ($exists) {
        $query = "UPDATE `settings` SET `_value` = ? WHERE `_key` = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$text, 'purchaseReportChannelId']);
    } else {
        $query = "INSERT INTO `settings` (`_key`, `_value`) VALUES (?, ?)";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['purchaseReportChannelId', $text]);
    }

    sendMessage($from_id, "✅ شناسه کانال گزارشات خرید با موفقیت ذخیره شد.", $setChannelKeyboard);
    setStep($from_id, 'panel');
    die;
}

if ($text == '📦 سفارشات ثبت شده' && $currentUser->is_admin) {
    sendMessage($from_id, "📥 لطفاً *شناسه عددی* کانالی که می‌خواهید سفارشات کاربران به آن ارسال شود را وارد کنید:", $backToAdminKeyboard);
    setStep($from_id, 'set-user-orders-channel');
    die;
}

if ($currentUser->step == 'set-user-orders-channel' && $currentUser->is_admin) {
    if (!is_numeric($text)) {
        sendMessage($from_id, "❌ شناسه وارد شده معتبر نیست.\nفقط *عدد* وارد کنید:", $backToAdminKeyboard);
        die;
    }

    $query = "SELECT * FROM `settings` WHERE `_key` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['userOrdersChannelId']);
    $exists = $stmt->fetch();

    if ($exists) {
        $query = "UPDATE `settings` SET `_value` = ? WHERE `_key` = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$text, 'userOrdersChannelId']);
    } else {
        $query = "INSERT INTO `settings` (`_key`, `_value`) VALUES (?, ?)";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['userOrdersChannelId', $text]);
    }

    sendMessage($from_id, "✅ شناسه کانال سفارشات کاربران با موفقیت ذخیره شد.", $setChannelKeyboard);
    setStep($from_id, 'panel');
    die;
}

if ($text == '📢 عضویت اجباری' && $currentUser->is_admin) {
    sendMessage($from_id, "🔐 لطفاً *یوزرنیم کانال* (با @) را وارد کنید که کاربران باید در آن عضو باشند:", $backToAdminKeyboard);
    setStep($from_id, 'set-force-sub-channel');
    die;
}

if ($currentUser->step == 'set-force-sub-channel' && $currentUser->is_admin) {
   
    $query = "SELECT * FROM `settings` WHERE `_key` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['forceSubChannelId']);
    $exists = $stmt->fetch();

    if ($exists) {
        $query = "UPDATE `settings` SET `_value` = ? WHERE `_key` = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$text, 'forceSubChannelId']);
    } else {
        $query = "INSERT INTO `settings` (`_key`, `_value`) VALUES (?, ?)";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['forceSubChannelId', $text]);
    }

    sendMessage($from_id, "✅ کانال عضویت اجباری با موفقیت ذخیره شد.", $setChannelKeyboard);
    setStep($from_id, 'panel');
    die;
}

if ($text == '🏧 برداشت ‌ها' && $currentUser->is_admin) {
    sendMessage($from_id, "📢 لطفاً *شناسه عددی* کانالی که می‌خواهید گزارش برداشت‌ها در آن ارسال شود را وارد کنید:", $backToAdminKeyboard);
    setStep($from_id, 'set-withdraw-report-channel');
    die;
}

if ($currentUser->step == 'set-withdraw-report-channel' && $currentUser->is_admin) {
    if (!is_numeric($text)) {
        sendMessage($from_id, "❌ شناسه وارد شده معتبر نیست.\nفقط *عدد* وارد کنید:", $backToAdminKeyboard);
        die;
    }

    $query = "SELECT * FROM settings WHERE _key = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['withdrawReportChannelId']);
    $exists = $stmt->fetch();

    if ($exists) {
        $query = "UPDATE settings SET _value = ? WHERE _key = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$text, 'withdrawReportChannelId']);
    } else {
        $query = "INSERT INTO settings (_key, _value) VALUES (?, ?)";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['withdrawReportChannelId', $text]);
    }

    sendMessage($from_id, "✅ شناسه کانال گزارش برداشت‌ها با موفقیت ذخیره شد.", $setChannelKeyboard);
    setStep($from_id, 'panel');
    die;
}
