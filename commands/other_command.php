<?php

if ($text == '📖 راهنما') {
    $responseText = settings('helpText') ?? 'تنظیم نشده';
    sendMessage($from_id, $responseText);
    die;
}

if ($text == '🧑🏻‍💻 پشتیبانی') {
    $responseText = settings('supportText') ?? 'تنظیم نشده';
    sendMessage($from_id, $responseText);
    die;
}

if ($text == '⚖️ قوانین') {
     $responseText = settings('rulleText') ?? 'تنظیم نشده';
    sendMessage($from_id, $responseText);
    die;
}