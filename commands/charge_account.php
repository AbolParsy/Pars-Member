<?php

if ($text == '💰 شارژ حساب') {
    $minDepositAmount = settings("minDepositAmount") ?? 5000;
    $maxDepositAmount = settings("maxDepositAmount") ?? 500000;

    $minFormatted = number_format($minDepositAmount);
    $maxFormatted = number_format($maxDepositAmount);

    $responseText = "💵 لطفاً مبلغ مورد نظر برای شارژ حساب را به تومان وارد کنید.\n\n(حداقل {$minFormatted} تومان و حداکثر {$maxFormatted} تومان)\n\nمثال: 25000";
    sendMessage($from_id, $responseText, $backToMainMenu);
    setStep($from_id, "charge_account");
    die;
}

if ($currentUser->step == "charge_account") {

    if (!is_numeric($text)) {
        sendMessage($from_id, "❌ لطفاً فقط عدد وارد نمایید:", $backToMainMenu);
        die;
    }

    $minDepositAmount = settings("minDepositAmount") ?? 5000;
    $maxDepositAmount = settings("maxDepositAmount") ?? 500000;

    $minFormatted = number_format($minDepositAmount);
    $maxFormatted = number_format($maxDepositAmount);

    if (!is_numeric($text) || $text < $minDepositAmount || $text > $maxDepositAmount) {
        $responseText = "❗️مبلغ وارد شده نامعتبر است. لطفاً عددی بین {$minFormatted} و {$maxFormatted} تومان وارد کنید.";
        sendMessage($from_id, $responseText, $backToMainMenu);
        die;
    }


    do {
        $random_id = rand(1000000, 9999999);
        $transactionId = $random_id;

        $query = "SELECT COUNT(*) FROM `transactions` WHERE `track_id` = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$transactionId]);
        $rowCount = $stmt->fetchColumn();
    } while ($rowCount > 0);

    $query = "INSERT INTO `transactions` (`chat_id`, `track_id`, `amount`) VALUES (?,?,?)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$from_id, $transactionId, $text]);

    sendMessage($from_id, "لطفا روش پرداخت را انتخاب کنید:\n\n💳 *درگاه پرداخت*: مستقیم با کارت بانکی پرداخت کنید.\n\n💼 *کیف پول بله*: از موجودی کیف پول بله استفاده کنید.", json_encode([
        'keyboard' => [
            ['💳 درگاه پرداخت', '💼 کیف پول بله'],
            ['بازگشت به منو اصلی']
        ]
    ]));
    //     sendMessage($from_id, "لطفا روش پرداخت را انتخاب کنید:\n\n💳 *درگاه پرداخت*: مستقیم با کارت بانکی پرداخت کنید.", json_encode([
    //     'keyboard' => [
    //         ['💳 درگاه پرداخت'],
    //         ['بازگشت به منو اصلی']
    //     ]
    // ]));

    setStep($from_id, "invoice_pay-$transactionId-$text");
    die;
}

if (strpos($currentUser->step, "invoice_pay-") === 0) {

    $parts = explode('-', $currentUser->step);
    $transactionId = $parts[1] ?? null;
    $amount = $parts[2] ?? null;

    if (!$transactionId || !$amount) {
        sendMessage($from_id, "❌ اطلاعات پرداخت معتبر نیست. لطفاً دوباره تلاش کنید.");
        setStep($from_id, null);
        die;
    }

    switch ($text) {
        case "💳 درگاه پرداخت":
            $cardNumber = settings("bankCardNumber") ?? "0";

            sendPayment(
                $from_id,
                "افزایش اعتبار | کد رهگیری: $transactionId",
                "لطفاً مبلغ $amount تومان را از طریق درگاه بانکی پرداخت نمایید.",
                $transactionId,
                $cardNumber,
                [['label' => "افزایش اعتبار", 'amount' => $amount * 10]]
            );
            sendMessage($from_id, 'به منو اصلی بازگشتید', $mainUserKeyboard);
            setStep($from_id, "home");
            die;

        case "💼 کیف پول بله":
            $walletAddress =  settings("walletAddress") ?? "WALLET-TEST-1111111111111111";

            sendPayment(
                $from_id,
                "افزایش اعتبار | کد رهگیری: $transactionId",
                "لطفاً مبلغ $amount تومان را از طریق کیف پول بله پرداخت نمایید.",
                $transactionId,
                $walletAddress,
                [['label' => "افزایش اعتبار", 'amount' => $amount * 10]]
            );

            sendMessage($from_id, 'به منو اصلی بازگشتید', $mainUserKeyboard);
            setStep($from_id, "home");
            die;
    }
}
