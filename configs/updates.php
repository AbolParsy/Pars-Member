<?php

$update = json_decode(file_get_contents("php://input"));

if (isset($update->message)) {
    $update_id    = $update->update_id ?? 'none';
    $message      = $update->message ?? 'none';
    $text         = $message->text ?? '';
    $from_id      = $message->from->id ?? 'none';
    $chat_id      = $message->chat->id ?? 'none';
    $chat_type    = $message->chat->type ?? 'private';
    $user_name    = $message->from->username ?? 'ندارد';
    $first_name   = htmlspecialchars($message->from->first_name ?? '', ENT_QUOTES, 'UTF-8');
    $message_id   = $message->message_id ?? 'none';
    $photo = $update->message->photo[0]->file_id;
}

if (isset($update->callback_query)) {
    $from_id     = $update->callback_query->from->id ?? 'none';
    $chat_id     = $update->callback_query->message->chat->id ?? 'none';
    $data        = $update->callback_query->data ?? '';
    $query_id    = $update->callback_query->id ?? 'none';
    $type        = $update->callback_query->message->chat->type ?? '';
    $first_name  = htmlspecialchars($update->callback_query->from->first_name ?? '', ENT_QUOTES, 'UTF-8');
    $user_name   = $update->callback_query->from->username ?? 'ندارد';
    $message_id  = $update->callback_query->message->message_id ?? 'none';
}

if ($update->pre_checkout_query) {
    $checkoutId = $update->pre_checkout_query->id;
    answerToPay($checkoutId);
    die;
}

if (isset($message->successful_payment)) {
    $invoice = $message->successful_payment;
    $transactionId = $invoice->invoice_payload;
    $current_time = date('Y-m-d H:i:s');

    $date = jdate("Y/m/d");
    $time = jdate("H:i:s");

    $query = "SELECT * FROM `transactions` WHERE `track_id` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$transactionId]);
    $transaction = $stmt->fetch();

    if (!$transaction) {
        die;
    }

    $user = $transaction->chat_id;
    $amount = $transaction->amount;

    $query = "UPDATE `transactions` SET `is_paid` = 1, `updated_at` = ? WHERE `track_id` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$current_time, $transactionId]);

    $query = "UPDATE `users` SET `balance` = `balance` + ? WHERE `chat_id` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$amount, $user]);

    sendMessage($user, "*🎉 شارژ حساب با موفقیت انجام شد*\n\n✅ مبلغ: $amount تومان\n🔘 کد رهگیری: $transactionId\n\n📅 تاریخ: $date\n⏰ ساعت: $time");

    $responseText = "🧾 *گزارش شارژ حساب کاربر*\n\n"
        . "👤 کاربر: $user\n"
        . "💳 مبلغ: $amount تومان\n"
        . "🔗 کد رهگیری: $transactionId\n"
        . "📅 تاریخ: $date\n"
        . "⏰ ساعت: $time";

    $logChannel = settings('purchaseReportChannelId');
    if ($logChannel) {
        sendMessage($logChannel, $responseText, null, 'Markdown');
    }
}

if (preg_match('/^\/start (\d+)$/', $text, $matches)) {
    $inviterId = $matches[1];

    if ($inviterId != $from_id) {
        $query = "SELECT * FROM `referrals` WHERE `invited_chat_id` = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$from_id]);

        if ($stmt->rowCount() == 0) {

            $query = "SELECT * FROM `users` WHERE `chat_id` = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$inviterId]);
            $userExists = $stmt->fetch();

            $query = "SELECT * FROM `users` WHERE `chat_id` = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$from_id]);
            $userNotInBot = $stmt->fetch();

            if ($userExists && !$userNotInBot) {
                $inviteReward = 500;

                $query = "INSERT INTO `referrals` (`inviter_chat_id`, `invited_chat_id`) VALUES (?, ?)";
                $stmt = $pdo->prepare($query);
                $stmt->execute([$inviterId, $from_id]);

                $query = "UPDATE `users` SET `balance` = `balance` + ? WHERE `chat_id` = ?";
                $stmt = $pdo->prepare($query);
                $stmt->execute([$inviteReward, $inviterId]);
                sendMessage($inviterId, "🎉 تبریک! یک نفر با لینک دعوت شما وارد ربات شد.\n👤 آیدی کاربر: $from_id\n\n💸 مبلغ *500 تومان* به موجودی شما اضافه شد.", null, 'Markdown');
            } else {
                $query = "INSERT INTO `referrals` (`inviter_chat_id`, `invited_chat_id`) VALUES (?, ?)";
                $stmt = $pdo->prepare($query);
                $stmt->execute([0, $from_id]);
            }
        }
    }
}

$currentUser = getUser($from_id);
if (!$currentUser && $from_id != 0) {
    $query = "INSERT INTO `users` (`chat_id`) VALUES (?)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$from_id]);
}

if ($text && $chat_type == "private") {
    $step = $currentUser->step;
    $userNameDisplay = $user_name ? "@$user_name" : "ندارد";

    $responseText = "✅ وضعیت: *#فعال*  

▫️ *مشخصات کاربر:*  
▫️ *آیدی عددی:* {$from_id} 
▫️ *نام:* {$first_name}  
▫️ *یوزرنیم:* {$userNameDisplay}  
▫️ *وضعیت:* {$step}  

▫️ *متن پیام:*  
{$text}";

    $logChannel = settings('userLogChannelId');
    sendMessage($logChannel, $responseText);
}

if ((!empty($text) || !empty($data)) && !$currentUser->is_admin && $chat_type != "group") {
    $channelUsername1 = settings('forceSubChannelId') ?? 0;

    if ($channelUsername1 != 0) {
        $checkJoin1 = getChatMember($channelUsername1, $from_id);
        $status1 = $checkJoin1->result->status ?? null;

        if (!in_array($status1, ['member', 'administrator', 'creator'])) {
            $responseText = "*🔔 برای ادامه، ابتدا در کانال اطلاع‌رسانی زیر عضو شوید:*\n- $channelUsername1";
            sendMessage($from_id, $responseText, json_encode([
                'inline_keyboard' => [
                    [['text' => 'عضویت در کانال اصلی', 'url' => "https://ble.ir/$channelUsername1"]],
                    [['text' => 'بررسی عضویت', 'callback_data' => 'joined_all']]
                ]
            ]));
            die;
        }
    }
}

if (isset($currentUser->is_banned) && $currentUser->is_banned == 1) {
    sendMessage($from_id, "⛔️ شما از از ربات بلاک شده‌اید.\n\nدر صورت نیاز، لطفاً با پشتیبانی تماس بگیرید.");
    die;
}

if (preg_match('/^\/start/', $text) || $text == 'بازگشت به منو اصلی' || $data == 'joined_all') {
    $responseText = settings('startText') ?? 'تنظیم نشده';
    sendMessage($from_id, $responseText, $mainUserKeyboard);
    setStep($from_id, 'home');
    die;
}
