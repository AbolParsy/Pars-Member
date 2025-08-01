<?php

// کسر شارژ از کاربر
if ($text == '➖ کسر شارژ' && $currentUser->is_admin) {
    sendMessage($from_id, "🔻 لطفاً شناسه کاربری که می‌خواهید از حساب او مبلغی کسر شود را وارد کنید:", $backToAdminKeyboard);
    setStep($from_id, 'deduct_amount_user');
    die;
}

if ($currentUser->step == 'deduct_amount_user') {
    $query = "SELECT * FROM `users` WHERE `chat_id` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$text]);
    $userExists = $stmt->fetch();

    if (!$userExists) {
        sendMessage($from_id, "❌ کاربری با این شناسه یافت نشد!\n\n🔁 لطفاً یک شناسه معتبر وارد کنید:");
        die;
    }

    sendMessage($from_id, "💰 مبلغی که می‌خواهید از کاربر *{$text}* کسر شود را به تومان وارد کنید:");
    setStep($from_id, 'deduct_amount_value-' . $text);
    die;
}

if (strpos($currentUser->step, 'deduct_amount_value-') === 0) {
    $targetChatId = explode('-', $currentUser->step)[1];

    if (!is_numeric($text) || $text <= 0) {
        sendMessage($from_id, "⚠️ لطفاً یک مقدار عددی *معتبر* برای مبلغ وارد کنید:");
        die;
    }

    $amountToDeduct = (int)$text;

    $query = "UPDATE `users` SET `balance` = `balance` - ? WHERE `chat_id` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$amountToDeduct, $targetChatId]);

    sendMessage($from_id, "✅ مبلغ *{$amountToDeduct} تومان* با موفقیت از کاربر *$targetChatId* کسر شد.", $adminKeyboard);
    sendMessage($targetChatId, "⚠️ مبلغ *{$amountToDeduct} تومان* از حساب شما توسط مدیریت کسر شد.");

    setStep($from_id, 'panel');
    die;
}

// افزودن شارژ به کاربر
if ($text == '➕ شارژ حساب' && $currentUser->is_admin) {
    sendMessage($from_id, "➕ لطفاً شناسه کاربری که می‌خواهید به حساب او مبلغی اضافه کنید را وارد نمایید:", $backToAdminKeyboard);
    setStep($from_id, 'add_amount_user');
    die;
}

if ($currentUser->step == 'add_amount_user') {
    if (!ctype_digit($text)) {
        sendMessage($from_id, "⚠️ لطفاً فقط یک شناسه عددی معتبر وارد نمایید:");
        die;
    }

    $query = "SELECT * FROM `users` WHERE `chat_id` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$text]);
    $userExists = $stmt->fetch();

    if (!$userExists) {
        sendMessage($from_id, "❌ کاربری با این شناسه یافت نشد!\n\n🔁 لطفاً یک شناسه معتبر وارد کنید:");
        die;
    }

    sendMessage($from_id, "🎁 مبلغی که می‌خواهید به حساب کاربر *{$text}* اضافه شود را به تومان وارد نمایید:");
    setStep($from_id, 'add_amount_value-' . $text);
    die;
}

if (strpos($currentUser->step, 'add_amount_value-') === 0) {
    $targetChatId = explode('-', $currentUser->step)[1];

    if (!is_numeric($text) || $text <= 0) {
        sendMessage($from_id, "⚠️ لطفاً یک مقدار عددی *معتبر* برای مبلغ وارد کنید:");
        die;
    }

    $amountToAdd = (int)$text;

    $query = "UPDATE `users` SET `balance` = `balance` + ? WHERE `chat_id` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$amountToAdd, $targetChatId]);

    sendMessage($from_id, "✅ مبلغ *{$amountToAdd} تومان* با موفقیت به کاربر *$targetChatId* اضافه شد.", $adminKeyboard);
    sendMessage($targetChatId, "🎁 مبلغ *{$amountToAdd} تومان* توسط مدیریت به حساب شما افزوده شد.");

    setStep($from_id, 'panel');
    die;
}
