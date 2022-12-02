<?php

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
$question = ["Bún bò kho", "Hehehe", "Hahaha"];
$answer = [];

// echo RemoteWebDriver::getSessionID();
// if($driver->getSessionID()){
// }
startBrowser();

gotoAndWaitElementPresent('https://chat.zalo.me', '#contact-search-input');

$now = gmdate("Y-m-d", time());
$now = "2022-12-03";

$nextDate = (DateTime::createFromFormat("Y-m-d", $now)->modify("+1 day"));
gotoGroup('Hải châu');
getPoll();
generateTicket(8);

sleep(20);

gotoGroup('Hải châu');
createPoll($nextDate->format("d/m") . ": Chọn cơm trưa", $question);
// createPoll($nextDate, $question);

// GotoGroup('Hải âu');
// createPoll($nextDate, $question);

var_dump($answer);
// sleep(20);
// getPoll();
// generateTicket(8);

function generateTicket($maximum = 0)
{
    global $answer, $question;

    $sum = 0;
    if (count($answer) > 0) {
        $sum = array_reduce($answer, function ($a, $b) {
            return $a + (int)$b;
        });
    }

    $redundant = $maximum - $sum;

    if ($redundant > 0) {
        for ($i = 0; $i < $redundant; $i++) {
            if (!isset($answer[$i%count($question)]))
                $answer[$i%count($question)] = 0;
            $answer[$i%count($question)]++;
        }
    }
}

echo $lastPollName;

$driver->quit();
