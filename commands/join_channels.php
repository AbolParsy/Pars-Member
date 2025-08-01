<?php

if ($text == '📢 عضویت + کسب درآمد' || $data == 'back-to-getCoin') {
    $responseText = "💸 *کسب درآمد با عضویت در کانال‌ها*

در این بخش می‌تونی با عضویت در کانال‌هایی که معرفی می‌شن، به ازای هر عضویت، مبلغ مشخصی پول واقعی دریافت کنی.

📌 پس از عضویت در هر کانال، فقط کافیه روی دکمه «تایید عضویت» بزنی تا درآمدت به حسابت اضافه بشه.

🔻 برای مشاهده کانال ها و شروع عضویت روی دکمه پایین کلیک کنید";

    $keyboard = json_encode([
        'inline_keyboard' => [
            [
                ['text' => '📋 مشاهده کانال‌ها و عضویت', 'callback_data' => 'show_join_channels']
            ]
        ]
    ]);

    if ($text) {
        sendMessage($from_id, $responseText, $keyboard);
    } else {
        editMessage($from_id, $message_id, $responseText, $keyboard);
    }

    setStep($from_id, "join_channels");
    die;
}

if ($data == 'show_join_channels' || $data == 'next_channel') {
    $query = "SELECT o.* 
              FROM orders o 
              WHERE o.status = 0 
              AND o.chat_id != ? 
              AND NOT EXISTS (
                  SELECT 1 
                  FROM memberships m 
                  WHERE m.chat_id = ?
                  AND m.channel_username = o.channel_username
                  AND m.track_id = o.track_id
              )
              ORDER BY RAND()
              LIMIT 1";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$from_id, $from_id]);
    $channelInfo = $stmt->fetch();

    if (!$channelInfo) {
        editMessage($from_id, $message_id, "📭 در حال حاضر *کانالی برای عضویت موجود نیست*؛ لطفاً کمی بعد دوباره تلاش کنید.", json_encode([
            'inline_keyboard' => [
                [['text' => '🔙 بازگشت', 'callback_data' => 'back-to-getCoin'], ['text' => '🔄 امتحان دوباره', 'callback_data' => 'next_channel']]
            ]
        ]));
        die;
    }

    $channelUsername = $channelInfo->channel_username;
    $channelName = $channelInfo->channel_name;
    $countOfMember = $channelInfo->count;
    $transactionId = $channelInfo->track_id;
    $unitCost = $channelInfo->unit_cost;

    $responseText = "*▫️ نام کانال:* $channelName\n"
        . "*▪️ شناسه کانال:* $channelUsername\n\n"
        . "*🔸 تعداد ممبرهای درخواست شده:* $countOfMember\n"
        . "*💵 هدیه عضویت:* " . number_format($unitCost) . " تومان\n\n"
        . "پس از عضویت در کانال، لطفاً روی دکمه «💰 دریافت هدیه» کلیک کنید.";

    editMessage($from_id, $message_id, $responseText, json_encode([
        'inline_keyboard' => [
            [['text' => '📌 عضویت در کانال', 'url' => 'https://ble.ir/' . $channelUsername], ['text' => '💰 دریافت هدیه', 'callback_data' => 'check_order_coin-' . $transactionId]],
            [['text' => '➡️ بعدی', 'callback_data' => 'next_channel'], ['text' => '🔙 بازگشت', 'callback_data' => 'back-to-getCoin']]
        ]
    ]));
    die;
}

if (strpos($data, 'check_order_coin-') === 0) {
    $transactionId = explode('-', $data)[1];

    $query = "SELECT * FROM `orders` WHERE `track_id` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$transactionId]);
    $channelInfo = $stmt->fetch();

    $channelUsername = $channelInfo->channel_username;
    $channelName = $channelInfo->channel_name;
    $channelOwner = $channelInfo->chat_id;
    $countOfMember = $channelInfo->count;
    $unitOfMember = $channelInfo->unit_cost;
    $statusOforder = $channelInfo->status;

    $query = "SELECT * FROM `memberships` WHERE `chat_id` = ? AND `track_id` = ? AND `status` = 1";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$from_id, $transactionId]);
    $alreadyJoined = $stmt->fetch();

    if ($alreadyJoined) {
        editMessage($from_id, $message_id, "❗️شما قبلاً در این کانال عضو شده‌اید.", json_encode([
            'inline_keyboard' => [
                [['text' => '➡️ بعدی', 'callback_data' => 'next_channel']]
            ]
        ]));
        die;
    }

    $getChannelStatus = getChatMember($channelUsername, BOT_ID);
    if (!isset($getChannelStatus->ok) || !$getChannelStatus->ok || !isset($getChannelStatus->result->status) || $getChannelStatus->result->status != 'administrator') {
        editMessage($from_id, $message_id, "⚠️ *خطایی در پردازش عضویت پیش آمده است!*\n\n📌 لطفاً کمی بعد دوباره تلاش کنید یا از دکمه‌های زیر استفاده نمایید.", json_encode([
            'inline_keyboard' => [
                [['text' => '➡️ بعدی', 'callback_data' => 'next_channel'], ['text' => '🔄 امتحان دوباره', 'callback_data' => 'check_order_coin-' . $transactionId]]
            ]
        ]));

        if ($statusOforder == 0) {

            $query = "UPDATE `orders` SET `status` = 1 WHERE `track_id` = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$transactionId]);

            $query = "SELECT COUNT(*) AS joined_count FROM `memberships` WHERE `track_id` = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$transactionId]);
            $joinedCount = $stmt->fetch()->joined_count;

            $remaining = $countOfMember - $joinedCount;
            if ($remaining < 0) $remaining = 0;

            $compensation = $remaining * $unitOfMember;

            if ($compensation > 0) {

                $query = "UPDATE `users` SET `balance` = `balance` + ? WHERE `chat_id` = ?";
                $stmt = $pdo->prepare($query);
                $stmt->execute([$compensation, $channelOwner]);

                sendMessage($channelOwner, "⚠️ سفارش شما مربوط به کانال $channelUsername با شماره سفارش *$transactionId* به دلیل خطا در دسترسی به اطلاعات کانال لغو شد.\n\n📌 به عنوان جبران، مبلغ *{$compensation} مبلغ* بابت اعضای تکمیل نشده برای شما واریز گردید.");
            } else {
                sendMessage($channelOwner, "⚠️ سفارش شما مربوط به کانال $channelUsername با شماره سفارش *$transactionId* به دلیل خطا در بررسی وضعیت ربات لغو شد.\n\n📌 چون تمامی اعضا جذب شده بودند، مبلغی بابت جبران واریز نشد.");
            }
        }
        die;
    }

    $checkJoin = getChatMember($channelUsername, $from_id);
    $status = $checkJoin->error_code == 404 ? 0 : 1;

    if ($status == 0) {
        editMessage($from_id, $message_id, "*❗️خطا: شما هنوز در کانال مورد نظر عضو نشده‌اید*\n\n📌 لطفاً ابتدا از طریق دکمه‌ی زیر در کانال عضو شوید، سپس بر روی «🔄 امتحان دوباره» کلیک کنید.", json_encode([
            'inline_keyboard' => [
                [['text' => '📢 عضویت در کانال', 'url' => 'https://ble.ir/' . $channelUsername]],
                [['text' => '➡️ بعدی', 'callback_data' => 'next_channel'], ['text' => '🔄 امتحان دوباره', 'callback_data' => 'check_order_coin-' . $transactionId]]
            ]
        ]));
        die;
    }

    $query = "SELECT * FROM `memberships` WHERE `chat_id` = ? AND `track_id` = ? AND `channel_username` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$from_id, $transactionId, $channelUsername]);
    $existingMembership = $stmt->fetch();

    if ($existingMembership) {
        if ($existingMembership->status == 0) {
            $query = "UPDATE `memberships` SET `status` = 1 WHERE `id` = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$existingMembership->id]);
        }
    } else {
        $query = "INSERT INTO `memberships` (`chat_id`, `track_id`, `channel_username`, `unit_cost`, `status`) VALUES (?, ?, ?, ?, 1)";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$from_id, $transactionId, $channelUsername, $unitOfMember]);
    }

    $query = "UPDATE `users` SET `balance` = `balance` + ? WHERE `chat_id` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$unitOfMember, $from_id]);

    $timeOfLeft = [
        "100" => 15,
        "200" => 30
    ];
    $channelExitPenalty = settings("channelExitPenalty") ?? $unitOfMember;

    editMessage($from_id, $message_id, "🎉 تبریک! مبلغ *$unitOfMember تومان* به حساب شما اضافه شد.\n\n⚠️ *توجه:* اگر تا {$timeOfLeft[$unitOfMember]} روز آینده از کانال خارج شوید، *{$channelExitPenalty} تومان هدیه* از موجودی شما کسر خواهد شد.", json_encode([
        'inline_keyboard' => [
            [['text' => '➡️ بعدی', 'callback_data' => 'next_channel']]
        ]
    ]));

    $query = "SELECT COUNT(*) AS member_joined FROM `memberships` WHERE `track_id` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$transactionId]);
    $joinedCount = $stmt->fetch()->member_joined;

    if ($joinedCount == $countOfMember) {
        $query = "UPDATE `orders` SET `status` = 1 WHERE `track_id` = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$transactionId]);

        $responseText = "🎉 تبریک! سفارش شما با شناسه *$transactionId* با موفقیت تکمیل شد.\n\n✏️ *شناسه کانال:* $channelUsername\n🔐 *تعداد ممبر درخواستی:* $countOfMember";
        sendMessage($channelOwner, $responseText);
    }
    die;
}
