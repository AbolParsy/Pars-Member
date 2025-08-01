<?php

if ($text == '✏️ ارسال همگانی' && $currentUser->is_admin) {
    $responseText = 'لطفا پیامی که می‌خواهید *ارسال همگانی* کنید را ارسال کنید: ';
    sendMessage($from_id, $responseText, $backToAdminKeyboard);
    setStep($from_id, 'send_all-DB');
    die;
}

if (strpos($currentUser->step, 'send_all-DB') === 0 && $currentUser->is_admin) {
    $query = "DELETE FROM `send_all` WHERE 1";
    $stmt = $pdo->prepare($query);
    $stmt->execute();

    $query = "SELECT `chat_id` FROM `users`";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $usersSendAll = $stmt->fetchAll();

    $query = "INSERT INTO `send_all` (`user_id`, `text`) VALUES (?, ?)";
    $stmt = $pdo->prepare($query);

    $counter = 0;
    foreach ($usersSendAll as $user) {
        $userId = $user->chat_id;
        $stmt->execute([$userId, $text]);
        $counter++;
    }

    $responseText = "✅ پیام شما برای *ارسال همگانی* تنظیم شد!\n\n📊 تعداد کاربران: $counter نفر";
    sendMessage($from_id, $responseText, $sendMessageToUser);
    setStep($from_id, 'panel');
    die;
}

if ($text == '📬 فروارد همگانی' && $currentUser->is_admin) {
    $responseText = 'لطفا پیامی که می‌خواهید *فروارد همگانی* کنید را فوروارد نمایید: ';
    sendMessage($from_id, $responseText, $backToAdminKeyboard);
    setStep($from_id, 'forward_all-DB');
    die;
}

if (strpos($currentUser->step, 'forward_all-DB') === 0 && $currentUser->is_admin) {
    $query = "DELETE FROM `forward_all` WHERE 1";
    $stmt = $pdo->prepare($query);
    $stmt->execute();

    $query = "SELECT `chat_id` FROM `users`";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $usersForwardAll = $stmt->fetchAll();

    $query = "INSERT INTO `forward_all` (`from_id`, `message_id`, `user_id`) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($query);

    $counter = 0;
    foreach ($usersForwardAll as $user) {
        $userId = $user->chat_id;
        $stmt->execute([$from_id, $message_id, $userId]);
        $counter++;
    }

    $responseText = "✅ پیام شما برای *فروارد همگانی* تنظیم شد!\n\n📊 تعداد کاربران: $counter نفر";
    sendMessage($from_id, $responseText, $sendMessageToUser);
    setStep($from_id, 'panel');
    die;
}
