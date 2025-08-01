<?php

$mainUserKeyboard = json_encode([
    "keyboard" => [
        [['text' => '📢 عضویت + کسب درآمد']],
        [['text' => '🛒 سفارش ممبر'], ['text' => '💰 شارژ حساب']],
        [['text' => '👤 حساب کاربری']],
        [['text' => '🏧 برداشت از حساب'], ['text' => '📊 تراکنش‌ های من']],
        [['text' => '🌐 زیرمجموعه‌گیری']],
        [['text' => '⚖️ قوانین'], ['text' => '🧑🏻‍💻 پشتیبانی'], ['text' => '📖 راهنما']],
    ]
]);

$userTransactionKeyboard = json_encode([
    'keyboard' => [
        [['text' => '🛒 سفارشات ممبر'], ['text' => '🔍 جستجوی سفارش']],
        [['text' => '🔰 برداشت از حساب'], ['text' => '💳 شارژ حساب']],
        [['text' => 'بازگشت به منو اصلی']]
    ]
]);

$withdrawOptionsKeyboard = json_encode([
    'keyboard' => [
        [['text' => '💳 برداشت به کارت'], ['text' => '📱 شارژ تلفن همراه']],
        [['text' => '🎁 برداشت پاکت هدیه']],
        [['text' => 'بازگشت به منو اصلی']]
    ]
]);

$adminKeyboard = json_encode([
    'keyboard' => [
        [['text' => '📊 آمار ربات']],
        [['text' => '🔒 تنظیم محدودیت‌ ها'], ['text' => '📡 تنظیم کانال‌ ها']],
        [['text' => '📝 تنظیم متن‌ ها'], ['text' => '💳 تنظیم پرداخت‌ ها']],
        [['text' => '✏️ ارسال همگانی'], ['text' => '📬 فروارد همگانی']],
        [['text' => '➕ شارژ حساب'], ['text' => '➖ کسر شارژ']],
        [['text' => 'بازگشت به منو اصلی']]
    ]
]);


$setChannelKeyboard = json_encode([
    'keyboard' => [
        [['text' => '💳 پرداخت ها'], ['text' => '🏧 برداشت ‌ها']],
        [['text' => '🧾 لاگ کاربران'], ['text' => '📢 عضویت اجباری']],
        [['text' => '📦 سفارشات ثبت شده']],
        [['text' => 'بازگشت به مدیریت']]
    ]
]);

$limitsKeyboard = json_encode([
    'keyboard' => [
        [['text' => '💰 حداقل مبلغ شارژ'], ['text' => '💸 حداکثر مبلغ شارژ']],
        [['text' => '🏦 حداقل مبلغ برداشت'], ['text' => '💳 حداکثر مبلغ برداشت']],
        [['text' => '🧾 حداقل تعداد سفارش'], ['text' => '📈 حداکثر تعداد سفارش']],
        [['text' => '💱 کارمزد تراکنش‌ها'], ['text' => '🚫 جریمه خروج از کانال']],
        [['text' => 'بازگشت به مدیریت']]
    ]
]);

$settingsTextKeyboard = json_encode([
    'keyboard' => [
        [['text' => '📜 متن شروع'], ['text' => '🛠️ متن پشتیبانی']],
        [['text' => '❓ متن راهنما'], ['text' => '🛑 متن قوانین']],
        [['text' => 'بازگشت به مدیریت']]
    ]
]);

$paymentSettingsKeyboard = json_encode([
    'keyboard' => [
        [['text' => '💼 تنظیم کیف پول'], ['text' => '💳 تنظیم شماره کارت']],
        [['text' => 'بازگشت به مدیریت']]
    ],
    'resize_keyboard' => true
]);

$backToMainMenu = json_encode([
    'keyboard' => [
        [['text' => 'بازگشت به منو اصلی']]
    ]
]);


$backToAdminKeyboard =  json_encode([
    'keyboard' => [
        [['text' => 'بازگشت به مدیریت']]
    ]
]);
