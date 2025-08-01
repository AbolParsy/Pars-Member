<?php
chdir(__DIR__);

require "../configs/configs.php";
require "../libs/functions.php";

$limit = 100;
$send_all = $pdo->prepare("SELECT * FROM `send_all` WHERE `status` = 0 LIMIT :limit");
$send_all->bindValue(':limit', $limit, PDO::PARAM_INT);
$send_all->execute();

if ($send_all->rowCount() == 0) {
    die("هیچ پیامی برای ارسال وجود ندارد.");
}

$delete_stmt = $pdo->prepare("DELETE FROM `send_all` WHERE `id` = :id");

$success_count = 0;
$fail_count = 0;

while ($row = $send_all->fetch(PDO::FETCH_ASSOC)) {
    $user_id = $row['user_id'];
    $text = $row['text'];
    $id = $row['id'];

    $response = sendMessage($user_id, $text);

    if (isset($response->ok) && $response->ok) {
        $success_count++;
    } else {
        $fail_count++;
    }

    $delete_stmt->execute(['id' => $id]);
}

echo "پیام ها با موفقیت ارسال شدند!\n";
echo "✅ موفق: $success_count\n";
echo "❌ ناموفق: $fail_count\n";

// ارسال گزارش به کانال لاگ
$query = "SELECT `chat_id` FROM `channels` WHERE `flag` = 'log_channel'";
$stmt = $pdo->prepare($query);
$stmt->execute();
$channel = $stmt->fetch(PDO::FETCH_OBJ);

if ($channel) {
    $chat_id = $channel->chat_id;

    $msg = "📊 آمار پیام همگانی 📊

✅ موفق: $success_count
❌ ناموفق: $fail_count

→→→→→→→→→→→→
🔍 *نمونه متن پیام:*
" . substr($text, 0, 100) . "..."; // نمایش 100 کاراکتر اول متن برای جلوگیری از طولانی شدن

    sendMessage($chat_id, $msg);
}

die;
