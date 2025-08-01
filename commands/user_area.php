<?php

if ($text == '👤 حساب کاربری') {

    $joinAt = jdate("Y/m/d", strtotime($currentUser->joined_at));
    $balance = $currentUser->balance;

    $query = "SELECT COUNT(*) FROM `orders` WHERE `chat_id` = ? AND `status` = 1";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$from_id]);
    $orderCount = $stmt->fetchColumn();

    $responseText = "👤 *نام کاربری:* $first_name
🔰 *شناسه عددی:* $from_id
🤝 *تاریخ عضویت:* $joinAt

💳 *موجودی:* $balance تومان
📦 *تعداد سفارش‌های موفق:* $orderCount";

    sendMessage($from_id, $responseText, json_encode([
        'inline_keyboard' => [
            [['text' => 'کپی کردن شناسه عددی', 'copy_text' => ['text' => (string) $from_id]]]
        ]
    ]));
    die;
}
