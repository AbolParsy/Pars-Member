<?php

if ($text == '💳 تنظیم پرداخت‌ ها' && $currentUser->is_admin) {
    sendMessage($from_id, "💳 لطفاً یکی از گزینه‌های زیر را برای تنظیم انتخاب کنید:", $paymentSettingsKeyboard);
    die;
}

if ($text == '💼 تنظیم کیف پول' && $currentUser->is_admin) {
    sendMessage($from_id, "🔢 لطفاً *آدرس کیف پول بله* را وارد کنید:", $backToAdminKeyboard);
    setStep($from_id, 'set-wallet-address');
    die;
}

if ($currentUser->step == 'set-wallet-address' && $currentUser->is_admin) {
    $query = "SELECT * FROM `settings` WHERE `_key` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['walletAddress']);
    $exists = $stmt->fetch();

    if ($exists) {
        $query = "UPDATE `settings` SET `_value` = ? WHERE `_key` = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$text, 'walletAddress']);
    } else {
        $query = "INSERT INTO `settings` (`_key`, `_value`) VALUES (?, ?)";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['walletAddress', $text]);
    }

    sendMessage($from_id, "✅ آدرس کیف پول بله ذخیره شد.", $paymentSettingsKeyboard);
    setStep($from_id, 'panel');
    die;
}

if ($text == '💳 تنظیم شماره کارت' && $currentUser->is_admin) {
    sendMessage($from_id, "🔢 لطفاً *شماره کارت بانکی* را وارد کنید (16 رقمی):", $backToAdminKeyboard);
    setStep($from_id, 'set-bank-card');
    die;
}

if ($currentUser->step == 'set-bank-card' && $currentUser->is_admin) {
    if (!preg_match('/^\d{16}$/', $text)) {
        sendMessage($from_id, "❌ شماره کارت نامعتبر است. لطفاً یک عدد 16 رقمی وارد کنید:", $backToAdminKeyboard);
        die;
    }

    $query = "SELECT * FROM `settings` WHERE `_key` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['bankCardNumber']);
    $exists = $stmt->fetch();

    if ($exists) {
        $query = "UPDATE `settings` SET `_value` = ? WHERE `_key` = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$text, 'bankCardNumber']);
    } else {
        $query = "INSERT INTO `settings` (`_key`, `_value`) VALUES (?, ?)";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['bankCardNumber', $text]);
    }

    sendMessage($from_id, "✅ شماره کارت بانکی ذخیره شد.", $paymentSettingsKeyboard);
    setStep($from_id, 'panel');
    die;
}
