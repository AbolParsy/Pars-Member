<?php

// چک IP غیرفعال شده
$ok = true;

// ادامه کدها
include 'configs/configs.php';
include 'configs/keyboards.php';
include 'libs/jdf.php';
include 'libs/functions.php';
include 'configs/updates.php';

if (isset($update->message->left_chat_member)) {

    $userId = $update->message->left_chat_member->id;
    $channelUsername = '@' . $update->message->chat->username;

    $query = "SELECT * FROM `memberships` 
              WHERE `chat_id` = ? AND `channel_username` = ? AND `status` = 1 
              ORDER BY `created_at` DESC LIMIT 1";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$userId, $channelUsername]);
    $membership = $stmt->fetch();

    if ($membership) {

        $createdAt = strtotime($membership->created_at);
        $now = time();
        $unitCost = $membership->unit_cost;

        $requiredSeconds = $unitCost == 100 ? 1296000 : 2592000;
        $channelExitPenalty = settings("channelExitPenalty") ?? $unitCost;

        if (($now - $createdAt) < $requiredSeconds) {

            $query = "UPDATE `users` SET `balance` = `balance` - ? WHERE `chat_id` = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$channelExitPenalty, $userId]);

            $query = "SELECT * FROM `orders` 
                      WHERE `channel_username` = ? 
                      ORDER BY `created_at` DESC LIMIT 1";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$channelUsername]);
            $order = $stmt->fetch();

            if ($order) {
                $ownerId = $order->chat_id;

                $query = "UPDATE `users` SET `balance` = `balance` + ? WHERE `chat_id` = ?";
                $stmt = $pdo->prepare($query);
                $stmt->execute([$unitCost, $ownerId]);

                sendMessage($ownerId, "✅ به دلیل خروج یکی از کاربران از کانال $channelUsername مبلغ $unitCost تومان به حساب شما برگشت داده شد.");
            }

            $query = "UPDATE `memberships` SET `status` = 0 WHERE `id` = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$membership->id]);

            sendMessage($userId, "⚠️ کاربر گرامی، به دلیل خروج زودهنگام شما از کانال $channelUsername مبلغ {$channelExitPenalty} تومان از حساب شما کسر گردید.");
        } else {
            $query = "UPDATE `memberships` SET `status` = 0 WHERE `id` = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$membership->id]);
        }
    }
}

if ($text && $chat_type == "group") {
    die;
}

include "commands/order_member.php";
include "commands/join_channels.php";
include "commands/charge_account.php";
include "commands/user_area.php";
include "commands/last_transactions.php";
include "commands/withdrawal.php";
include "commands/other_command.php";
include "commands/invite_users.php";

include "admin/amdin_login.php";
include "admin/admin_setChannels.php";
include "admin/admin_setLimits.php";
include "admin/admin_setText.php";
include "admin/set_payment.php";
include "admin/admin_sendMessage.php";
include "admin/admin_changeBalance.php";
