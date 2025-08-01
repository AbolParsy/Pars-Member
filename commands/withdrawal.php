<?php

if ($text == "🏧 برداشت از حساب") {
    $responseText = "🏧 لطفاً یکی از روش‌های برداشت را انتخاب کنید:\n\n💳 *برداشت به کارت:* مبلغ مورد نظر به صورت کارت‌به‌کارت برای شما واریز می‌شود.\n\n📱 *شارژ تلفن همراه:* می‌توانید برای همه اپراتورها شارژ مستقیم یا کد شارژ دریافت کنید.\n\n🎁 *برداشت پاکت هدیه:* یک پاکت هدیه برای شما ساخته و به صورت خصوصی در چت ارسال می‌شود.";
    sendMessage($from_id, $responseText, $withdrawOptionsKeyboard);
    die;
}

if ($text == '💳 برداشت به کارت') {

    $minWithdrawAmount = settings("minWithdrawAmount") ?? 5000;
    $minFormatted = number_format($minWithdrawAmount);

    if ($currentUser->balance < $minWithdrawAmount) {
        $responseText = "❗️حداقل مبلغ برای برداشت به کارت {$minFormatted} تومان می‌باشد.\nموجودی شما کافی نیست.";
        sendMessage($from_id, $responseText);
        die;
    }

    $responseText = "💳 لطفاً شماره کارت مقصد را وارد کنید:\n\n(فقط اعداد، بدون فاصله یا خط تیره)";
    sendMessage($chat_id, $responseText, $backToMainMenu);
    setStep($from_id, "withdraw_card-to-card");
    die;
}

if ($currentUser->step == "withdraw_card-to-card") {
    $cardNumber = preg_replace('/\D/', '', $text);

    if (!ctype_digit($text)) {
        $responseText = "❗️لطفاً فقط عدد وارد کنید. شماره کارت نباید حاوی حروف یا نمادهای خاص باشد:";
        sendMessage($from_id, $responseText);
        die;
    }
    if (strlen($cardNumber) != 16) {
        $responseText = "❗️شماره کارت باید دقیقاً 16 رقم باشد:";
        sendMessage($from_id, $responseText);
        die;
    }

    $responseText = "✅ شماره کارت با موفقیت ثبت شد.\n\n💰 لطفاً مبلغ مورد نظر برای برداشت را به تومان وارد کنید:";
    sendMessage($from_id, $responseText);
    setStep($from_id, "card_set-$cardNumber");
    die;
}

if (strpos($currentUser->step, 'card_set-') === 0) {
    $cardNumber = explode('-', $currentUser->step)[1];
    $amount = $text;

    if (!is_numeric($amount)) {
        $responseText = "❗️لطفاً مبلغ را به صورت عددی وارد کنید:";
        sendMessage($from_id, $responseText);
        die;
    }

    if ($amount > $currentUser->balance) {
        $responseText = "❗️موجودی شما کافی نیست. مبلغ وارد شده بیشتر از موجودی فعلی شماست:";
        sendMessage($from_id, $responseText);
        die;
    }

    $minWithdrawAmount = settings("minWithdrawAmount") ?? 5000;
    $minFormatted = number_format($minWithdrawAmount);

    if ($amount < $minWithdrawAmount) {
        $responseText = "❗️حداقل مبلغ برای برداشت {$minFormatted} تومان می‌باشد.";
        sendMessage($from_id, $responseText);
        die;
    }

    $maxWithdrawAmount = settings("maxWithdrawAmount") ?? 5000;
    $minFormatted = number_format($maxWithdrawAmount);

    if ($amount > $maxWithdrawAmount) {
        $responseText = "❗️حداکثر مبلغ برای برداشت {$minFormatted} تومان می‌باشد.";
        sendMessage($from_id, $responseText);
        die;
    }

    $fee = 1000;
    $finalAmount = $amount - $fee;

    $responseText = "📋 *اطلاعات تراکنش به شرح زیر می‌باشد:*\n\n";
    $responseText .= "💳 *شماره کارت مقصد:* $cardNumber\n";
    $responseText .= "💰 *مبلغ برداشت:* " . number_format($amount) . " تومان\n";
    $responseText .= "💸 *کارمزد ربات:* " . number_format($fee) . " تومان\n";
    $responseText .= "✅ *مبلغ نهایی واریزی:* " . number_format($finalAmount) . " تومان\n\n";
    $responseText .= "📌 *آیا از درخواست خود اطمینان دارید؟*";

    sendMessage($from_id, $responseText, json_encode([
        'inline_keyboard' => [
            [['text' => 'تایید و برداشت', 'callback_data' => 'confirme_get_card']]
        ]
    ]));

    setStep($from_id, "final_confirme_withdraw-$amount-$cardNumber");
    die;
}

if (strpos($currentUser->step, 'final_confirme_withdraw-') === 0 && $data == 'confirme_get_card') {
    [$prefix, $amount, $cardNumber] = explode('-', $currentUser->step);

    $fee = 1000;
    $finalAmount = $amount - $fee;

    $userBalance = $currentUser->balance;

    if ($amount > $userBalance) {
        $responseText = "❗️ موجودی حساب شما برای برداشت کافی نیست.";
        sendMessage($from_id, $responseText);
        setStep($from_id, "home");
        die;
    }

    $stmt = $pdo->prepare("UPDATE `users` SET `balance` = `balance` - ? WHERE `chat_id` = ?");
    $stmt->execute([$amount, $from_id]);

    do {
        $transactionId = rand(100000000, 999999999);
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM `withdrawals` WHERE `track_id` = ?");
        $stmt->execute([$transactionId]);
        $rowCount = $stmt->fetchColumn();
    } while ($rowCount > 0);

    $stmt = $pdo->prepare("INSERT INTO `withdrawals` (`chat_id`, `track_id`, `amount`, `key_`, `value_`) VALUES (?,?,?,?,?)");
    $stmt->execute([$from_id, $transactionId, $amount, 'کارت به کارت', $cardNumber]);

    $responseText = "✅ *درخواست برداشت شما با موفقیت ثبت شد!*\n\n";
    $responseText .= "💳 *شماره کارت:* $cardNumber\n";
    $responseText .= "💰 *مبلغ برداشت:* " . number_format($amount) . " تومان\n";
    $responseText .= "💸 *کارمزد:* 1000 تومان\n";
    $responseText .= "✅ *مبلغ واریزی:* " . number_format($finalAmount) . " تومان\n";
    $responseText .= "🎟 *کد پیگیری:* $transactionId\n\n";
    $responseText .= "⏳ در حال پردازش...\n";

    editMessage($from_id, $message_id, $responseText, $mainUserKeyboard);

    $logChannel = settings("withdrawReportChannelId");
    $date = jdate("Y/m/d");
    $time = jdate("H:i:s");

    $adminText = "📤 *گزارش برداشت جدید*\n\n";
    $adminText .= "👤 *کاربر:* $from_id\n";
    $adminText .= "💰 *مبلغ:* " . number_format($finalAmount) . " تومان\n";
    $adminText .= "🎟 *کد پیگیری:* $transactionId\n";
    $adminText .= "🔑 *نوع:* کارت به کارت\n";
    $adminText .= "📌 *شماره کارت:* $cardNumber\n";
    $adminText .= "⏰ $date $time";

    sendMessage($logChannel, $adminText, json_encode([
        'inline_keyboard' => [
            [
                ['text' => 'تایید برداشت', 'callback_data' => 'confirme_withdraw-' . $transactionId],
                ['text' => 'رد کردن برداشت', 'callback_data' => 'reject_withdraw-' . $transactionId]
            ]
        ]
    ]));

    setStep($from_id, "home");
    die;
}

if ($text == '📱 شارژ تلفن همراه') {
    $minWithdrawAmount = settings("minWithdrawAmount") ?? 5000;
    $minFormatted = number_format($minWithdrawAmount);

    if ($currentUser->balance < $minWithdrawAmount) {
        $responseText = "❗️حداقل موجودی برای ثبت درخواست {$minFormatted} تومان می‌باشد.\nموجودی شما کافی نیست.";
        sendMessage($from_id, $responseText);
        die;
    }


    $responseText = "📱 لطفاً شماره موبایل خود را وارد کنید:\n\n(مثال: 09123456789)";
    sendMessage($chat_id, $responseText, $backToMainMenu);
    setStep($from_id, "withdraw_charge-phone");
    die;
}

if ($currentUser->step == "withdraw_charge-phone") {
    $phone = preg_replace('/\D/', '', $text);

    if (!ctype_digit($phone) || strlen($phone) != 11 || substr($phone, 0, 2) != "09") {
        $responseText = "❗️شماره تلفن وارد شده نامعتبر است. لطفاً شماره‌ای معتبر و 11 رقمی وارد کنید (مثال: 09123456789):";
        sendMessage($from_id, $responseText);
        die;
    }

    $responseText = "✅ شماره تلفن با موفقیت ثبت شد.\n\n💰 لطفاً مبلغ مورد نظر برای شارژ را به تومان وارد کنید:";
    sendMessage($from_id, $responseText);
    setStep($from_id, "phone_set-$phone");
    die;
}

if (strpos($currentUser->step, 'phone_set-') === 0) {
    $phoneNumber = explode('-', $currentUser->step)[1];
    $amount = $text;

    if (!is_numeric($amount)) {
        sendMessage($from_id, "❗️لطفاً مبلغ را به صورت عددی وارد کنید:");
        die;
    }

    $amount = intval($amount);
    $minWithdrawAmount = settings("minWithdrawAmount") ?? 5000;
    $minFormatted = number_format($minWithdrawAmount);

    if ($amount < $minWithdrawAmount) {
        $responseText = "❗️حداقل مبلغ برای شارژ {$minFormatted} تومان می‌باشد.";
        sendMessage($from_id, $responseText);
        die;
    }

    $maxWithdrawAmount = settings("maxWithdrawAmount") ?? 5000;
    $minFormatted = number_format($maxWithdrawAmount);

    if ($amount > $maxWithdrawAmount) {
        $responseText = "❗️حداکثر مبلغ برای برداشت {$minFormatted} تومان می‌باشد.";
        sendMessage($from_id, $responseText);
        die;
    }

    $chargeFee = 1000;
    $totalAmount = $amount + $chargeFee;

    if ($totalAmount > $currentUser->balance) {
        sendMessage($from_id, "❗️موجودی شما کافی نیست. مبلغ شارژ $amount تومان + کارمزد 1000 تومان = $totalAmount تومان، بیشتر از موجودی شماست.");
        die;
    }

    $responseText = "📋 *اطلاعات تراکنش به شرح زیر می‌باشد:*\n\n";
    $responseText .= "📱 *شماره موبایل:* $phoneNumber\n";
    $responseText .= "💰 *مبلغ شارژ:* $amount تومان\n";
    $responseText .= "💸 *کارمزد ربات:* 1000 تومان\n";
    $responseText .= "💳 *مبلغ کسر شده از حساب:* " . number_format($totalAmount) . " تومان\n";
    $responseText .= "📌 *آیا از درخواست خود اطمینان دارید؟*";

    sendMessage($from_id, $responseText, json_encode([
        'inline_keyboard' => [
            [['text' => 'تایید و برداشت', 'callback_data' => 'confirme_get_charge']]
        ]
    ]));

    setStep($from_id, "final_confirme_charge-$amount-$phoneNumber");
    die;
}

if (strpos($currentUser->step, 'final_confirme_charge-') === 0 && $data == 'confirme_get_charge') {
    $exploded = explode('-', $currentUser->step);
    $amount = intval($exploded[1]);
    $phoneNumber = $exploded[2];
    $chargeFee = 1000;
    $totalAmount = $amount + $chargeFee;

    $userBalance = $currentUser->balance;

    if ($totalAmount > $userBalance) {
        $responseText = "❗️ موجودی حساب شما برای برداشت کافی نیست.";
        sendMessage($from_id, $responseText);
        setStep($from_id, "home");
        die;
    }

    $query = "UPDATE `users` SET `balance` = `balance` - ? WHERE `chat_id` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$totalAmount, $from_id]);

    do {
        $transactionId = rand(100000000, 999999999);
        $query = "SELECT COUNT(*) FROM `withdrawals` WHERE `track_id` = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$transactionId]);
        $rowCount = $stmt->fetchColumn();
    } while ($rowCount > 0);

    $query = "INSERT INTO `withdrawals` (`chat_id`, `track_id`, `amount`, `key_`, `value_`) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$from_id, $transactionId, $amount, 'شارژ تلفن', $phoneNumber]);

    $responseText = "✅ *درخواست شارژ شما با موفقیت ثبت شد!*\n\n";
    $responseText .= "📱 *شماره موبایل:* $phoneNumber\n";
    $responseText .= "💰 *مبلغ شارژ:* $amount تومان\n";
    $responseText .= "💸 *کارمزد ربات:* 1000 تومان\n";
    $responseText .= "💳 *مبلغ کسر شده از حساب:* " . number_format($totalAmount) . " تومان\n";
    $responseText .= "🎟 *کد پیگیری:* $transactionId\n\n";
    $responseText .= "⏳ شارژ شما در حال پردازش است. لطفاً تا زمان تکمیل، صبور باشید.";

    editMessage($from_id, $message_id, $responseText, $mainUserKeyboard);

    $logChannel = settings("withdrawReportChannelId");
    $date = jdate("Y/m/d");
    $time = jdate("H:i:s");

    $report = "📤 *گزارش برداشت جدید*\n\n";
    $report .= "👤 *کاربر:* $from_id\n";
    $report .= "💰 *مبلغ:* $amount تومان\n";
    $report .= "💸 *کارمزد:* $chargeFee تومان\n";
    $report .= "🎟 *کد پیگیری:* $transactionId\n";
    $report .= "🔑 *نوع برداشت:* کارت شارژ\n";
    $report .= "📱 *شماره موبایل:* $phoneNumber\n";
    $report .= "⏰ $date $time\n";

    sendMessage($logChannel, $report, json_encode([
        'inline_keyboard' => [
            [
                ['text' => 'تایید برداشت', 'callback_data' => 'confirme_withdraw-' . $transactionId],
                ['text' => 'رد کردن برداشت', 'callback_data' => 'reject_withdraw-' . $transactionId]
            ]
        ]
    ]));

    setStep($from_id, "home");
    die;
}

if ($text == '🎁 برداشت پاکت هدیه') {
    $minWithdrawAmount = settings("minWithdrawAmount") ?? 5000;
    $minFormatted = number_format($minWithdrawAmount);

    if ($currentUser->balance < $minWithdrawAmount) {
        $responseText = "❗️حداقل مبلغ برای ارسال پاکت هدیه {$minFormatted} تومان می‌باشد.\nموجودی شما کافی نیست.";
        sendMessage($from_id, $responseText);
        die;
    }


    $responseText = "🎁 لطفاً یوزرنیم (آیدی) فرد دریافت‌کننده پاکت هدیه را وارد کنید:\n\n(مثال: @username)";
    sendMessage($chat_id, $responseText, $backToMainMenu);
    setStep($from_id, "withdraw_gift-username");
    die;
}

if ($currentUser->step == "withdraw_gift-username") {
    $username = trim($text);

    if (strpos($username, '@') !== 0 || strlen($username) < 5) {
        $responseText = "❗️یوزرنیم وارد شده معتبر نیست. لطفاً با فرمت صحیح وارد کنید (مثال: @username):";
        sendMessage($from_id, $responseText);
        die;
    }

    $responseText = "✅ یوزرنیم دریافت‌کننده با موفقیت ثبت شد.\n\n💰 لطفاً مبلغ مورد نظر برای ارسال پاکت هدیه را به تومان وارد کنید:";
    sendMessage($from_id, $responseText);
    setStep($from_id, "gift_set-$username");
    die;
}


if (strpos($currentUser->step, 'gift_set-') === 0) {
    $username = explode('-', $currentUser->step)[1];
    $amount = intval($text);
    $giftFee = 1000;

    if (!is_numeric($text)) {
        $responseText = "❗️لطفاً مبلغ را به صورت عددی وارد کنید:";
        sendMessage($from_id, $responseText);
        die;
    }

    $minWithdrawAmount = settings("minWithdrawAmount") ?? 5000;
    $minFormatted = number_format($minWithdrawAmount);

    if ($amount < $minWithdrawAmount) {
        $responseText = "❗️حداقل مبلغ برای ارسال پاکت هدیه {$minFormatted} تومان می‌باشد:";
        sendMessage($from_id, $responseText);
        die;
    }

    $maxWithdrawAmount = settings("maxWithdrawAmount") ?? 5000;
    $minFormatted = number_format($maxWithdrawAmount);

    if ($amount > $maxWithdrawAmount) {
        $responseText = "❗️حداکثر مبلغ برای برداشت {$minFormatted} تومان می‌باشد.";
        sendMessage($from_id, $responseText);
        die;
    }

    $totalAmount = $amount - $giftFee;
    if ($totalAmount > $currentUser->balance) {
        $responseText = "❗️موجودی شما کافی نیست. مبلغ هدیه $amount تومان و پس از کسر کارمزد 1000 تومان، مبلغ نهایی $totalAmount تومان است که بیشتر از موجودی شماست.";
        sendMessage($from_id, $responseText);
        die;
    }

    $responseText = "📋 *اطلاعات تراکنش به شرح زیر می‌باشد:*\n\n";
    $responseText .= "🎁 *دریافت‌کننده:* $username\n";
    $responseText .= "💰 *مبلغ هدیه:* $amount تومان\n";
    $responseText .= "💸 *کارمزد:* 1000 تومان\n";
    $responseText .= "💳 *مبلغ کسر شده از حساب:* " . number_format($amount) . " تومان\n";
    $responseText .= "📌 *آیا از درخواست خود اطمینان دارید؟*";

    sendMessage($from_id, $responseText, json_encode([
        'inline_keyboard' => [
            [['text' => 'تایید و برداشت', 'callback_data' => 'confirme_get_packat']]
        ]
    ]));

    setStep($from_id, "final_confirme_gift-$amount-$username");
    die;
}

if (strpos($currentUser->step, 'final_confirme_gift-') === 0 && $data == 'confirme_get_packat') {
    $exploded = explode('-', $currentUser->step);
    $amount = intval($exploded[1]);
    $username = $exploded[2];
    $giftFee = 1000;
    $totalAmount = $amount - $giftFee;

    $userBalance = $currentUser->balance;

    if ($amount > $userBalance) {
        $responseText = "❗️ موجودی حساب شما برای برداشت کافی نیست.";
        sendMessage($from_id, $responseText);
        setStep($from_id, "home");
        die;
    }

    $query = "UPDATE `users` SET `balance` = `balance` - ? WHERE `chat_id` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$amount, $from_id]);

    do {
        $transactionId = rand(100000000, 999999999);
        $query = "SELECT COUNT(*) FROM `withdrawals` WHERE `track_id` = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$transactionId]);
        $rowCount = $stmt->fetchColumn();
    } while ($rowCount > 0);

    $query = "INSERT INTO `withdrawals` (`chat_id`, `track_id`, `amount`, `key_`, `value_`) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$from_id, $transactionId, $amount, 'پاکت هدیه', $username]);

    $responseText = "✅ *درخواست ارسال پاکت هدیه با موفقیت ثبت شد!*\n\n";
    $responseText .= "🎁 *دریافت‌کننده:* $username\n";
    $responseText .= "💰 *مبلغ هدیه:* $totalAmount تومان\n";
    $responseText .= "💸 *کارمزد:* 1000 تومان\n";
    $responseText .= "💳 *مبلغ کسر شده از حساب:* " . number_format($amount) . " تومان\n";
    $responseText .= "🎟 *کد پیگیری:* $transactionId\n\n";
    $responseText .= "⏳ پاکت هدیه شما در صف پردازش قرار گرفت. لطفاً شکیبا باشید.";

    editMessage($from_id, $message_id, $responseText, $mainUserKeyboard);

    $logChannel = settings("withdrawReportChannelId");
    $date = jdate("Y/m/d");
    $time = jdate("H:i:s");

    $responseText = "📤 *گزارش برداشت جدید*\n\n";
    $responseText .= "👤 *کاربر:* $from_id\n";
    $responseText .= "💰 *مبلغ:* " . $totalAmount . " تومان\n";
    $responseText .= "🎟 *کد پیگیری:* $transactionId\n";
    $responseText .= "🔑 *نوع برداشت:* پاکت هدیه\n";
    $responseText .= "📌 *نام کاربری:* $username\n";
    $responseText .= "⏰ " . $date . " " . $time . "\n";

    sendMessage($logChannel, $responseText, json_encode([
        'inline_keyboard' => [
            [
                ['text' => 'تایید برداشت', 'callback_data' => 'confirme_withdraw-' . $transactionId],
                ['text' => 'رد کردن برداشت', 'callback_data' => 'reject_withdraw-' . $transactionId]
            ]
        ]
    ]));
    setStep($from_id, "home");
    die;
}

if (strpos($data, 'confirme_withdraw-') === 0 || strpos($data, 'reject_withdraw-') === 0) {
    $transactionId = explode('-', $data)[1];

    $query = "SELECT * FROM `withdrawals` WHERE `track_id` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$transactionId]);
    $withdrawal = $stmt->fetch();

    $chatId = $withdrawal->chat_id;
    $amount = $withdrawal->amount;
    $username = $withdrawal->value_;
    $status = $withdrawal->status;
    $keyOf  = $withdrawal->key_;

    if (strpos($data, 'confirme_withdraw-') === 0) {
        $query = "UPDATE `withdrawals` SET `status` = 1 WHERE `track_id` = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$transactionId]);

        $responseText = "✅ درخواست برای برداشت با موفقیت تایید شد.\n\n";
        $responseText .= "🎁 *دریافت‌کننده:* $username\n";
        $responseText .= "💰 *مبلغ:* $amount تومان\n";
        $responseText .= "🎟 *کد پیگیری:* $transactionId\n";
        $responseText .= "📌 درخواست شما انجام شد.";

        sendMessage($chatId, $responseText);
        editMessage($chat_id, $message_id, $responseText, json_encode([
            'inline_keyboard' => [
                [
                    ['text' => '✅ انجام شد', 'callback_data' => '0']
                ]
            ]
        ]));
    } elseif (strpos($data, 'reject_withdraw-') === 0) {
        $query = "UPDATE `withdrawals` SET `status` = 0 WHERE `track_id` = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$transactionId]);

        $responseText = "❌ درخواست برای برداشت رد شد.\n\n";
        $responseText .= "🎁 *دریافت‌کننده:* $username\n";
        $responseText .= "💰 *مبلغ:* $amount تومان\n";
        $responseText .= "🎟 *کد پیگیری:* $transactionId\n";
        $responseText .= "📌 درخواست شما رد شد.";

        sendMessage($chatId, $responseText);
        $refundAmount = $amount;

        $query = "UPDATE `users` SET `balance` = `balance` + ? WHERE `chat_id` = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$refundAmount, $chatId]);

        editMessage($chat_id, $message_id, $responseText, json_encode([
            'inline_keyboard' => [
                [
                    ['text' => '❌ رد شد', 'callback_data' => '0']
                ]
            ]
        ]));
    }
    die;
}
