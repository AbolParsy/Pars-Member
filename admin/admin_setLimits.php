<?php

if ($text == '🔒 تنظیم محدودیت‌ ها' && $currentUser->is_admin) {
    sendMessage($from_id, "⚙️ لطفاً یکی از گزینه‌های زیر را برای تنظیم محدودیت انتخاب کنید:", $limitsKeyboard);
    die;
}

if ($text == '💰 حداقل مبلغ شارژ' && $currentUser->is_admin) {
    sendMessage($from_id, "🔢 لطفاً *حداقل مبلغ شارژ* را به تومان وارد کنید:", $backToAdminKeyboard);
    setStep($from_id, 'set-min-deposit');
    die;
}

if ($currentUser->step == 'set-min-deposit' && $currentUser->is_admin) {
    if (!is_numeric($text)) {
        sendMessage($from_id, "❌ لطفاً فقط *عدد* وارد کنید:", $backToAdminKeyboard);
        die;
    }

    $query = "SELECT * FROM `settings` WHERE `_key` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['minDepositAmount']);
    $exists = $stmt->fetch();

    if ($exists) {
        $query = "UPDATE `settings` SET `_value` = ? WHERE `_key` = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$text, 'minDepositAmount']);
    } else {
        $query = "INSERT INTO `settings` (`_key`, `_value`) VALUES (?, ?)";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['minDepositAmount', $text]);
    }

    sendMessage($from_id, "✅ حداقل مبلغ شارژ ذخیره شد.", $limitsKeyboard);
    setStep($from_id, 'panel');
    die;
}

if ($text == '💸 حداکثر مبلغ شارژ' && $currentUser->is_admin) {
    sendMessage($from_id, "🔢 لطفاً *حداکثر مبلغ شارژ* را به تومان وارد کنید:", $backToAdminKeyboard);
    setStep($from_id, 'set-max-deposit');
    die;
}

if ($currentUser->step == 'set-max-deposit' && $currentUser->is_admin) {
    if (!is_numeric($text)) {
        sendMessage($from_id, "❌ لطفاً فقط *عدد* وارد کنید:", $backToAdminKeyboard);
        die;
    }

    $query = "SELECT * FROM `settings` WHERE `_key` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['maxDepositAmount']);
    $exists = $stmt->fetch();

    if ($exists) {
        $query = "UPDATE `settings` SET `_value` = ? WHERE `_key` = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$text, 'maxDepositAmount']);
    } else {
        $query = "INSERT INTO `settings` (`_key`, `_value`) VALUES (?, ?)";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['maxDepositAmount', $text]);
    }

    sendMessage($from_id, "✅ حداکثر مبلغ شارژ ذخیره شد.", $limitsKeyboard);
    setStep($from_id, 'panel');
    die;
}

if ($text == '🏦 حداقل مبلغ برداشت' && $currentUser->is_admin) {
    sendMessage($from_id, "🔢 لطفاً *حداقل مبلغ برداشت* را وارد کنید:", $backToAdminKeyboard);
    setStep($from_id, 'set-min-withdraw');
    die;
}

if ($currentUser->step == 'set-min-withdraw' && $currentUser->is_admin) {
    if (!is_numeric($text)) {
        sendMessage($from_id, "❌ لطفاً فقط *عدد* وارد کنید:", $backToAdminKeyboard);
        die;
    }

    $query = "SELECT * FROM `settings` WHERE `_key` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['minWithdrawAmount']);
    $exists = $stmt->fetch();

    if ($exists) {
        $query = "UPDATE `settings` SET `_value` = ? WHERE `_key` = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$text, 'minWithdrawAmount']);
    } else {
        $query = "INSERT INTO `settings` (`_key`, `_value`) VALUES (?, ?)";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['minWithdrawAmount', $text]);
    }

    sendMessage($from_id, "✅ حداقل مبلغ برداشت ذخیره شد.", $limitsKeyboard);
    setStep($from_id, 'panel');
    die;
}

if ($text == '💳 حداکثر مبلغ برداشت' && $currentUser->is_admin) {
    sendMessage($from_id, "🔢 لطفاً *حداکثر مبلغ برداشت* را وارد کنید:", $backToAdminKeyboard);
    setStep($from_id, 'set-max-withdraw');
    die;
}

if ($currentUser->step == 'set-max-withdraw' && $currentUser->is_admin) {
    if (!is_numeric($text)) {
        sendMessage($from_id, "❌ لطفاً فقط *عدد* وارد کنید:", $backToAdminKeyboard);
        die;
    }

    $query = "SELECT * FROM `settings` WHERE `_key` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['maxWithdrawAmount']);
    $exists = $stmt->fetch();

    if ($exists) {
        $query = "UPDATE `settings` SET `_value` = ? WHERE `_key` = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$text, 'maxWithdrawAmount']);
    } else {
        $query = "INSERT INTO `settings` (`_key`, `_value`) VALUES (?, ?)";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['maxWithdrawAmount', $text]);
    }

    sendMessage($from_id, "✅ حداکثر مبلغ برداشت ذخیره شد.", $limitsKeyboard);
    setStep($from_id, 'panel');
    die;
}

if ($text == '🧾 حداقل تعداد سفارش' && $currentUser->is_admin) {
    sendMessage($from_id, "🔢 لطفاً *حداقل تعداد سفارش* را وارد کنید:", $backToAdminKeyboard);
    setStep($from_id, 'set-min-orders');
    die;
}

if ($currentUser->step == 'set-min-orders' && $currentUser->is_admin) {
    if (!is_numeric($text)) {
        sendMessage($from_id, "❌ فقط *عدد* وارد کنید:", $backToAdminKeyboard);
        die;
    }

    $query = "SELECT * FROM `settings` WHERE `_key` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['minOrderCount']);
    $exists = $stmt->fetch();

    if ($exists) {
        $query = "UPDATE `settings` SET `_value` = ? WHERE `_key` = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$text, 'minOrderCount']);
    } else {
        $query = "INSERT INTO `settings` (`_key`, `_value`) VALUES (?, ?)";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['minOrderCount', $text]);
    }

    sendMessage($from_id, "✅ حداقل تعداد سفارش ذخیره شد.", $limitsKeyboard);
    setStep($from_id, 'panel');
    die;
}

if ($text == '📈 حداکثر تعداد سفارش' && $currentUser->is_admin) {
    sendMessage($from_id, "🔢 لطفاً *حداکثر تعداد سفارش* را وارد کنید:", $backToAdminKeyboard);
    setStep($from_id, 'set-max-orders');
    die;
}

if ($currentUser->step == 'set-max-orders' && $currentUser->is_admin) {
    if (!is_numeric($text)) {
        sendMessage($from_id, "❌ فقط *عدد* وارد کنید:", $backToAdminKeyboard);
        die;
    }

    $query = "SELECT * FROM `settings` WHERE `_key` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['maxOrderCount']);
    $exists = $stmt->fetch();

    if ($exists) {
        $query = "UPDATE `settings` SET `_value` = ? WHERE `_key` = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$text, 'maxOrderCount']);
    } else {
        $query = "INSERT INTO `settings` (`_key`, `_value`) VALUES (?, ?)";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['maxOrderCount', $text]);
    }

    sendMessage($from_id, "✅ حداکثر تعداد سفارش ذخیره شد.", $limitsKeyboard);
    setStep($from_id, 'panel');
    die;
}

if ($text == '💱 کارمزد تراکنش‌ها' && $currentUser->is_admin) {
    sendMessage($from_id, "🔢 لطفاً *کارمزد تراکنش‌ها* را به درصد وارد کنید (مثلاً 2 برای 2%):", $backToAdminKeyboard);
    setStep($from_id, 'set-transaction-fee');
    die;
}

if ($currentUser->step == 'set-transaction-fee' && $currentUser->is_admin) {
    if (!is_numeric($text) || $text < 0) {
        sendMessage($from_id, "❌ لطفاً فقط یک عدد مثبت وارد کنید:", $backToAdminKeyboard);
        die;
    }

    $transactionFee = $text / 100;

    $query = "SELECT * FROM `settings` WHERE `_key` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['transactionFee']);
    $exists = $stmt->fetch();

    if ($exists) {
        $query = "UPDATE `settings` SET `_value` = ? WHERE `_key` = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$transactionFee, 'transactionFee']);
    } else {
        $query = "INSERT INTO `settings` (`_key`, `_value`) VALUES (?, ?)";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['transactionFee', $transactionFee]);
    }

    sendMessage($from_id, "✅ کارمزد تراکنش‌ ها ذخیره شد.", $limitsKeyboard);
    setStep($from_id, 'panel');
    die;
}

if ($text == '🚫 جریمه خروج از کانال' && $currentUser->is_admin) {
    sendMessage($from_id, "🔢 لطفاً *جریمه خروج از کانال* را به تومان وارد کنید:", $backToAdminKeyboard);
    setStep($from_id, 'set-channel-exit-penalty');
    die;
}

if ($currentUser->step == 'set-channel-exit-penalty' && $currentUser->is_admin) {
    if (!is_numeric($text) || $text < 0) {
        sendMessage($from_id, "❌ لطفاً فقط یک عدد مثبت وارد کنید:", $backToAdminKeyboard);
        die;
    }

    $query = "SELECT * FROM `settings` WHERE `_key` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['channelExitPenalty']);
    $exists = $stmt->fetch();

    if ($exists) {
        $query = "UPDATE `settings` SET `_value` = ? WHERE `_key` = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$text, 'channelExitPenalty']);
    } else {
        $query = "INSERT INTO `settings` (`_key`, `_value`) VALUES (?, ?)";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['channelExitPenalty', $text]);
    }

    sendMessage($from_id, "✅ جریمه خروج از کانال ذخیره شد.", $limitsKeyboard);
    setStep($from_id, 'panel');
    die;
}
