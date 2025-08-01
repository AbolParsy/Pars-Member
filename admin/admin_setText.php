<?php

if ($text == '📝 تنظیم متن‌ ها' && $currentUser->is_admin) {
    sendMessage($from_id, "📑 لطفاً یکی از گزینه‌های زیر را برای تنظیم متن‌ها انتخاب کنید:", $settingsTextKeyboard);
    die;
}

if ($text == '📜 متن شروع' && $currentUser->is_admin) {
    sendMessage($from_id, "📝 لطفاً *متن شروع* ربات را ارسال کنید:", $backToAdminKeyboard);
    setStep($from_id, 'set-start-text');
    die;
}

if ($currentUser->step == 'set-start-text' && $currentUser->is_admin) {
    $query = "SELECT * FROM `settings` WHERE `_key` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['startText']);
    $exists = $stmt->fetch();

    if ($exists) {
        $query = "UPDATE `settings` SET `_value` = ? WHERE `_key` = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$text, 'startText']);
    } else {
        $query = "INSERT INTO `settings` (`_key`, `_value`) VALUES (?, ?)";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['startText', $text]);
    }

    sendMessage($from_id, "✅ *متن شروع* ذخیره شد.", $settingsTextKeyboard);
    setStep($from_id, 'panel');
    die;
}

if ($text == '🛠️ متن پشتیبانی' && $currentUser->is_admin) {
    sendMessage($from_id, "📝 لطفاً *متن پشتیبانی* را ارسال کنید:", $backToAdminKeyboard);
    setStep($from_id, 'set-support-text');
    die;
}

if ($currentUser->step == 'set-support-text' && $currentUser->is_admin) {
    $query = "SELECT * FROM `settings` WHERE `_key` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['supportText']);
    $exists = $stmt->fetch();

    if ($exists) {
        $query = "UPDATE `settings` SET `_value` = ? WHERE `_key` = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$text, 'supportText']);
    } else {
        $query = "INSERT INTO `settings` (`_key`, `_value`) VALUES (?, ?)";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['supportText', $text]);
    }

    sendMessage($from_id, "✅ *متن پشتیبانی* ذخیره شد.", $settingsTextKeyboard);
    setStep($from_id, 'panel');
    die;
}
if ($text == '❓ متن راهنما' && $currentUser->is_admin) {
    sendMessage($from_id, "📝 لطفاً *متن راهنما* را ارسال کنید:", $backToAdminKeyboard);
    setStep($from_id, 'set-help-text');
    die;
}

if ($currentUser->step == 'set-help-text' && $currentUser->is_admin) {
    $query = "SELECT * FROM `settings` WHERE `_key` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['helpText']);
    $exists = $stmt->fetch();

    if ($exists) {
        $query = "UPDATE `settings` SET `_value` = ? WHERE `_key` = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$text, 'helpText']);
    } else {
        $query = "INSERT INTO `settings` (`_key`, `_value`) VALUES (?, ?)";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['helpText', $text]);
    }

    sendMessage($from_id, "✅ *متن راهنما* ذخیره شد.", $settingsTextKeyboard);
    setStep($from_id, 'panel');
    die;
}

if ($text == '🛑 متن قوانین' && $currentUser->is_admin) {
    sendMessage($from_id, "📝 لطفاً *متن قوانین* را وارد کنید:", $backToAdminKeyboard);
    setStep($from_id, 'set-alt-rulle-text');
    die;
}

if ($currentUser->step == 'set-alt-rulle-text' && $currentUser->is_admin) {
    $query = "SELECT * FROM `settings` WHERE `_key` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['rulleText']);
    $exists = $stmt->fetch();

    if ($exists) {
        $query = "UPDATE `settings` SET `_value` = ? WHERE `_key` = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$text, 'rulleText']);
    } else {
        $query = "INSERT INTO `settings` (`_key`, `_value`) VALUES (?, ?)";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['rulleText', $text]);
    }

    sendMessage($from_id, "✅ *متن قوانین* ذخیره شد.", $settingsTextKeyboard);
    setStep($from_id, 'panel');
    die;
}
