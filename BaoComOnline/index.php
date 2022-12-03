<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
require_once(__DIR__ . "/extensionZalo.php");

use Facebook\WebDriver\Interactions\WebDriverActions;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverKeys;
use Facebook\WebDriver\WebDriverWait;


// Init variable
$driver;
$lastPollName = "";
$question = [
    "1️⃣ Bánh canh Cuu",
    "2️⃣ Cạch chiên",
    "🥬 Luồn Luộc"
];
$answer = [];

$nextRun = 0;
$atDate = gmdate("Y-m-d", time()) . " 07:00:00";
$now = date('U', time());
$nextDate = (DateTime::createFromFormat("Y-m-d H:i:s", $atDate));
$nextRun = time();

// callback function called when signal is received
function ctrl_handler(int $event)
{
    global $driver;
    switch ($event) {
        case PHP_WINDOWS_EVENT_CTRL_C:
            echo "You have pressed CTRL+C\n";
            $driver->quit();
            exit();
            break;
    }
}

sapi_windows_set_ctrl_handler('ctrl_handler');

startBrowser();
gotoAndWaitElementPresent('https://chat.zalo.me', '#contact-search-input');

while (true) {
    if ($now < $nextRun && $nextRun != 0) {
        echo "thực hiện hành động: báo cơm\n";
        getPoll();
        createPoll($nextDate->format("d/m") . ": 👨‍🍳 Bình chọn cơm", $question);
        var_dump($answer);
        generateTicket(8);
        var_dump($answer);
        sendMessage("👨‍🍳 Phòng dự án SAP:\n1️⃣ [Mặn 1]: " . $answer[0] . "\n2️⃣ [Mặn 2]: " . $answer[1] . "\n🥬 [Chay]: " . $answer[2]);
    }

    if ($nextRun == 0 or $nextRun != 0 and $now < $nextRun) {
        $nextRun = $nextDate->format("U");
        while ($now < $nextRun) {
            $now = date('U', time());
            echo "Còn " . ($nextRun - $now) . " giây nữa đến lần chạy tiếp theo\n";
            if ($nextRun - $now > 60)
                sleep(60);
            else if ($nextRun - $now >= 5)
                sleep(5);
            else if ($nextRun - $now > 0) {
                sleep($nextRun - $now);
            }
        }
    }
    $nextDate = $nextDate->modify("+1 day");
    $nextRun = $nextDate->format("U");
}
while (true);
