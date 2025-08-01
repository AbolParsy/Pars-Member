<?php

if ($text == "🛒 سفارش ممبر") {
    $responseText = "🤖 *جهت سفارش ممبر، ابتدا باید بازو را به عنوان ادمین کانال مورد نظر اضافه کنید.*\n\n📌 سپس آیدی کانال را ارسال نمایید.\n\n👈 *نمونه:*@iranozv";
    sendMessage($from_id, $responseText, $backToMainMenu);
    setStep($from_id, 'get_channel_username');
    die;
}

if (strpos($currentUser->step, 'get_channel_username') === 0) {
    $channelUsername = $text;

    if (substr($channelUsername, 0, 1) !== '@') {
        sendMessage($from_id, "❌ *شناسه کاربری کانال حتما باید با علامت @ شروع شود.*\n\n📌 لطفاً شناسه کاربری کانال را به درستی وارد نمایید.", $backToMainMenu);
        die;
    }

    $getChannelStatus = getChatMember($channelUsername, BOT_ID);
    if (!isset($getChannelStatus->result->status) || $getChannelStatus->result->status != 'administrator') {
        sendMessage($from_id, "❌ *بازو @Iranozv_bot ادمین کانال $channelUsername نیست.*\n\n📌 لطفاً ابتدا بازو را به عنوان ادمین کانال اضافه کرده و مجدداً آیدی کانال را ارسال نمایید.", $backToMainMenu);
        die;
    }

    $query = "SELECT * FROM `orders` WHERE `channel_username` = ? AND `status` = 0";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$text]);
    $orderExists = $stmt->fetch();

    if ($orderExists) {
        sendMessage($from_id, "⏳ *یک سفارش باز برای این کانال وجود دارد.*\n\n📌 لطفاً تا تکمیل شدن این سفارش صبر کنید و سپس سفارش بعدی را ثبت نمایید.", $backToMainMenu);
        die;
    }

    $minOrderCount = settings('minOrderCount');
    $maxOrderCount = settings('maxOrderCount');

    sendMessage($from_id, "📦 *سفارش ممبر*\n\nلطفاً تعداد ممبر مورد نظر خود را وارد کنید:\n\n🔢 مقدار وارد شده باید بین *$minOrderCount تا $maxOrderCount* ممبر باشد", $backToMainMenu);
    setStep($from_id, "enter_order_count-$channelUsername");
    die;
}

if (strpos($currentUser->step, "enter_order_count-") === 0) {
    $channelUsername = explode('-', $currentUser->step)[1];
    $countOfOrder = $text;

    $minOrderCount = settings('minOrderCount');
    $maxOrderCount = settings('maxOrderCount');

    if (!is_numeric($countOfOrder)) {
        sendMessage($from_id, "❌ مقدار وارد شده معتبر نیست!\n\nلطفا یک عدد بین $minOrderCount تا $maxOrderCount وارد کنید:", $backToMainMenu);
        die;
    }

    if ($countOfOrder < $minOrderCount || $countOfOrder > $maxOrderCount) {
        sendMessage($from_id, "❗تعداد باید بین *$minOrderCount تا $maxOrderCount* ممبر باشد.", $backToMainMenu);
        die;
    }

    $responseText = "💵 حالا لطفاً مبلغی که برای هر ممبر در نظر دارید را وارد کنید

🔢 مبلغ به ازای هر ممبر باید یکی از دو مقدار زیر باشد

1️⃣ *100 تک تومنی* (ممبر ساده / عضو ساده)

2️⃣ *200 تک تومنی* (ممبر Vip / عضو Vip)

لطفاً یکی از این دو مبلغ را وارد کنید:
 *100* یا *200* تک تومنی";

    sendMessage($from_id, $responseText, $backToMainMenu);
    setStep($from_id, "enter_price_per_member-$channelUsername-$countOfOrder");
    die;
}

if (strpos($currentUser->step, "enter_price_per_member-") === 0) {
    $parts = explode('-', $currentUser->step);
    $channelUsername = $parts[1];
    $countOfOrder = $parts[2];
    $priceOfMember = $text;

    if (!is_numeric($priceOfMember)) {
        sendMessage($from_id, "❌ لطفاً فقط عدد 100 یا 200 را وارد نمایید:", $backToMainMenu);
        die;
    }

    if ($priceOfMember != 100 && $priceOfMember != 200) {
        sendMessage($from_id, "❗ مبلغ وارد شده باید یکی از دو مقدار *100* یا *200* تک تومنی باشد\n\nلطفا یک مقدار بین این بازه وارد کنید:", $backToMainMenu);
        die;
    }

    $totalPrice = $countOfOrder * $priceOfMember;
    $commission = $totalPrice * settings('transactionFee') ?? 0.02;
    $finalPrice = $totalPrice + $commission;

    $responseText = "📋 *اطلاعات سفارش به شرح زیر می‌باشد:*\n\n";
    $responseText .= "📣 *آیدی کانال:* $channelUsername\n";
    $responseText .= "🔹 *تعداد:* $countOfOrder ممبر\n";
    $responseText .= "💰 *هزینه سفارش:* $totalPrice تومان\n";
    $responseText .= "🔺 *کارمزد ربات (20%):* $commission تومان\n";
    $responseText .= "🛒 *قیمت نهایی:* $finalPrice تومان\n\n";
    $responseText .= "📌 *آیا از درخواست خود اطمینان دارید؟*";

    sendMessage($from_id, $responseText, json_encode([
        'keyboard' => [
            [['text' => '✅ تایید و پرداخت']],
            [['text' => 'بازگشت به منو اصلی']]
        ]
    ]));

    setStep($from_id, "confirm_order-$channelUsername-$countOfOrder-$priceOfMember-$totalPrice-$commission-$finalPrice");
    die;
}

if (strpos($currentUser->step, "confirm_order-") === 0 && $text == "✅ تایید و پرداخت") {
    $parts = explode('-', $currentUser->step);
    $channelUsername = $parts[1];
    $countOfOrder = $parts[2];
    $priceOfMember = $parts[3];
    $totalPrice = $parts[4];
    $commission = $parts[5];
    $finalPrice = $parts[6];

    if ($currentUser->balance < $finalPrice) {
        $responseText = "❌ *موجودی حساب شما کافی نیست!*\n\n";
        $responseText .= "💰 مبلغ مورد نیاز: $finalPrice تومان\n";
        $responseText .= "💼 موجودی فعلی شما: {$currentUser->balance} تومان\n\n";
        $responseText .= "📌 لطفاً ابتدا حساب خود را شارژ نمایید.";
        sendMessage($from_id, $responseText, $mainUserKeyboard);
        setStep($from_id, "home");
        die;
    }

    $query = "UPDATE `users` SET `balance` = `balance` - ? WHERE `chat_id` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$finalPrice, $from_id]);

    $channelInfo = getChat($channelUsername);
    $channelName = $channelInfo->result->title ?? "نامشخص";

    do {
        $random_id = rand(100000000, 999999999);
        $transactionId = $random_id;

        $query = "SELECT COUNT(*) FROM `orders` WHERE `track_id` = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$transactionId]);
        $rowCount = $stmt->fetchColumn();
    } while ($rowCount > 0);

    $query = "INSERT INTO `orders` (`chat_id`, `channel_name`, `channel_username`, `count`, `total_cost`, `unit_cost`, `track_id`) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        $from_id,
        $channelName,
        $channelUsername,
        $countOfOrder,
        $totalPrice,
        $priceOfMember,
        $transactionId
    ]);

    $responseText = "✅ *سفارش شما با موفقیت ثبت شد!*\n\n";
    $responseText .= "📣 نام کانال: $channelName\n";
    $responseText .= "🆔 آیدی کانال: $channelUsername\n";
    $responseText .= "🔹 تعداد: $countOfOrder ممبر\n";
    $responseText .= "💰 مبلغ: $totalPrice تومان\n";
    $responseText .= "🔺 کارمزد: $commission تومان\n";
    $responseText .= "🧾 مبلغ نهایی پرداخت: $finalPrice تومان\n";
    $responseText .= "🎟 کد پیگیری: $transactionId\n\n";
    $responseText .= "⏳ سفارش در حال پردازش است، لطفاً تا زمان تکمیل صبور باشید.\n\n";
    $responseText .= "⚠️ *توجه:* برای جلوگیری از ریزش اعضا، تا *۲۴ ساعت پس از تکمیل سفارش*، بازو را از ادمینی کانال خارج نکنید.\n\n";
    $responseText .= "❗️ در صورت حذف بازو از ادمینی پیش از تکمیل سفارش، *سفارش لغو خواهد شد* و *هزینه پرداخت‌ شده برگشت داده نخواهد شد.*";

    sendMessage($from_id, $responseText, $mainUserKeyboard);

    $date = jdate("Y/m/d");
    $time = jdate("H:i:s");

    $logText = "📝 *گزارش ثبت سفارش جدید*\n\n"
        . "👤 کاربر: $from_id\n"
        . "📣 کانال: $channelName\n"
        . "🆔 آیدی کانال: $channelUsername\n"
        . "🔢 تعداد: $countOfOrder ممبر\n"
        . "💰 مبلغ کل: $totalPrice تومان\n"
        . "💵 کارمزد: $commission تومان\n"
        . "💳 پرداختی نهایی: $finalPrice تومان\n"
        . "🎟 کد پیگیری: `$transactionId`\n"
        . "📅 تاریخ: $date\n"
        . "⏰ ساعت: $time";

    $logChannel = settings('userOrdersChannelId');
    if ($logChannel) {
        sendMessage($logChannel, $logText);
    }

    setStep($from_id, "home");
    die;
}
