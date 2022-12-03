<?php

require_once(__DIR__ . "/vendor/autoload.php");

use Facebook\WebDriver\Firefox\FirefoxOptions;
use Facebook\WebDriver\Interactions\WebDriverActions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverKeys;
use Facebook\WebDriver\WebDriverWait;

function startBrowser()
{
    global $driver;

    $serverUrl = 'http://localhost:4444';

    // Firefox
    $desiredCapabilities = DesiredCapabilities::firefox();
    // Disable accepting SSL certificates
    $desiredCapabilities->setCapability('acceptSslCerts', false);
    // Add arguments via FirefoxOptions to start headless firefox
    $firefoxOptions = new FirefoxOptions();
    $firefoxOptions->addArguments(['--profile', __DIR__ . '/profile']);
    $firefoxOptions->addArguments(['--start-maximized']);
    $desiredCapabilities->setCapability(FirefoxOptions::CAPABILITY, $firefoxOptions);
    $driver = RemoteWebDriver::create($serverUrl, $desiredCapabilities);
    $driver->manage()->window()->maximize();
}

function gotoAndWaitElementPresent($url, $cssSelector)
{
    global $driver;
    $driver->get('https://chat.zalo.me', $cssSelector);

    waitElementPresent($cssSelector);
}

function gotoChat($chatName)
{
    global $driver;
    try {
        $elementContactSearchInput = $driver->findElements(WebDriverBy::cssSelector('#contact-search-input'));

        if (count($elementContactSearchInput)) {
            $elementContactSearchInput[0]->clear();
            $elementContactSearchInput[0]->sendKeys($chatName);
        }

        // (new WebDriverActions($driver))->doubleClick($elementContactSearchInput[0])->perform();

        waitElementPresent('.ReactVirtualized__Grid__innerScrollContainer>div div-16 span.truncate');

        $elementChats = $driver->findElements(WebDriverBy::cssSelector('.ReactVirtualized__Grid__innerScrollContainer>div div-16 span.truncate'));

        for ($i = 0; $i < count($elementChats); $i++) {
            if ($elementChats[$i]->getText() == $chatName) {
                $elementChats[$i]->click();
                $driver->findElements(WebDriverBy::cssSelector("[data-id=\"div_Main_TabMsg\"]"))[0]->click();
                break;
            }
        }
    } catch (Exception $e) {
        gotoChat($chatName);
    }
}

function waitElementPresent($cssSelector, $timeout = 15)
{
    global $driver;

    $waitElementPresent = new WebDriverWait($driver, $timeout);
    $waitElementPresent->until(
        function () use ($driver, $cssSelector) {
            $elements = $driver->findElements(WebDriverBy::cssSelector($cssSelector));
            return count($elements) > 0;
        },
        'Error locating more than one elements'
    );
}



function createPoll($title, array $question)
{
    global $driver, $lastPollName;
    try {
        gotoChat("Hải châu");
        waitElementPresent('[data-id="div_More_Menu"]');
        $driver->findElements(WebDriverBy::cssSelector('[data-id="div_More_Menu"]'))[0]->click();
        waitElementPresent('[data-id="div_MoreMenu_Poll"]');
        $driver->findElements(WebDriverBy::cssSelector('[data-id="div_MoreMenu_Poll"]'))[0]->click();
        waitElementPresent('.zl-modal');
        $driver->findElements(WebDriverBy::cssSelector('[data-id="div_CreatePoll_AddOpt"]'))[0]->click();
        $inputTopic = $driver->findElements(WebDriverBy::cssSelector('[data-id="txt_CreatePoll_Question"]'))[0];
        $inputTopic->click();
        $inputTopic->sendKeys($title);
        $lastPollName = $title;

        $countQuest = count($question);

        if ($countQuest > 2) {
            for ($i = 1; $i < $countQuest - 2; $i++) {
                $driver->findElements(WebDriverBy::cssSelector('[data-id="div_CreatePoll_AddOpt"]'))[0]->click();
            }
        }

        $listQuestionInput = $driver->findElements(WebDriverBy::cssSelector(".group-poll-create-content-item"));

        for ($i = 0; $i < count($listQuestionInput); $i++) {
            $input = $listQuestionInput[$i]->findElements(WebDriverBy::cssSelector("input"))[0];
            $input->click();
            $input->sendKeys($question[$i]);
        }

        $driver->findElements(WebDriverBy::cssSelector("[data-id='btn_CreatePoll_Create']"))[0]->click();
    } catch (Exception $e) {
        createPoll($title, $question);
    }
}

function sendMessage($message)
{
    global $driver;
    gotoChat("Hải âu");
    $builderMessage = "";
    $textSplit = explode("\n", $message);
    for ($i = 0; $i < count($textSplit); $i++) {
        $builderMessage .= '<div id="input_line_' . $i . '">' . $textSplit[$i] . '</div>';
    }
    $builderScript = ";(function(){document.querySelector('#richInput').innerHTML = `" . $builderMessage . "`;event = document.createEvent('Event');event.initEvent('input', true, true);document.querySelector('#richInput').dispatchEvent(event);})();";
    $driver->executeScript($builderScript);
    waitElementPresent(".send-btn-chatbar.input-btn");
    $driver->findElements(WebDriverBy::cssSelector(".send-btn-chatbar.input-btn"))[0]->click();
}

function getPoll()
{
    global $driver, $lastPollName, $question, $answer;
    try {
        gotoChat("Hải châu");
        $rightBar = $driver->findElements(WebDriverBy::cssSelector('.fa-outline-right-bar'));
        if (count($rightBar)) {
            $rightBar[0]->click();
        } else {
            $driver->findElements(WebDriverBy::cssSelector('.fa-outline-right-bar-active'))[0]->click();
            $driver->findElements(WebDriverBy::cssSelector('.fa-outline-right-bar'))[0]->click();
        }

        $driver->findElements(WebDriverBy::cssSelector("#add-setting-box>div"))[1]->click();

        $listPoll = $driver->findElements(WebDriverBy::cssSelector('#sideBodyScrollBox .ReactVirtualized__Grid__innerScrollContainer>div'));
        for ($i = 0; $i < count($listPoll); $i++) {
            $pollName = $listPoll[$i]->findElements(WebDriverBy::cssSelector('.truncate-2'));
            if(count($pollName)){
                $pollName = $pollName[0]->getText();
            }else{
                getPoll();
                return;
            }
            if ($lastPollName == "" || $lastPollName == $pollName) {
                $listPoll[$i]->click();
                break;
            }
        }

        waitElementPresent(".zl-modal__dialog");

        $groupPollVoteDescription = $driver->findElements(WebDriverBy::cssSelector(".group-poll-vote-description"));
        if (count($groupPollVoteDescription)) {
            $groupPollVoteDescription[0]->click();
            waitElementPresent(".group-poll-detail-section");

            $listAnserPoll = $driver->findElements(WebDriverBy::cssSelector(".group-poll-detail-section"));

            for ($i = 0; $i < count($listAnserPoll); $i++) {
                $text = $listAnserPoll[$i]->findElements(WebDriverBy::cssSelector(".group-poll-detail-section-title"))[0]->getText();
                $count = explode(")", explode("(", $text)[1])[0];
                $title = explode(" (", $text)[0];
                echo $title . ": " . $count . "\n";
                if (($index = array_search($title, $question)) !== false) {
                    echo $index. "\n";
                    $answer[$index] = $count;
                }
                var_dump($answer);
            }
        }

        $driver->findElements(WebDriverBy::cssSelector('[icon="icon-outline-setting"]'))[0]->click();

        waitElementPresent(".popover-v3");
        $divDetailPollMoreBlock = $driver->findElements(WebDriverBy::cssSelector('.popover-v3 [data-id="div_DetailPollMore_Block"]'));
        if (count($divDetailPollMoreBlock)) {
            $divDetailPollMoreBlock[0]->click();

            waitElementPresent('.zl-modal__footer__button.z--btn--fill--secondary-red');
            $driver->findElements(WebDriverBy::cssSelector('.zl-modal__footer__button.z--btn--fill--secondary-red'))[0]->click();
        } else {
            $driver->findElements(WebDriverBy::cssSelector('[icon="close f16"]'))[0]->click();
        }

        $driver->findElements(WebDriverBy::cssSelector('.fa-outline-right-bar-active'))[0]->click();
    } catch (Exception $e) {
        getPoll();
    }
}

// random số lượng chưa bình chọn cho đủ chỉ tiêu, thứ 4 và 6 all in phiếu dư vào mặn 2, thứ 7 vào chay
function generateTicket($maximum = 0)
{
    global $answer, $question;
    $dayOfWeek = date("w", time());

    $sum = 0;
    if (count($answer) > 0) {
        $sum = array_reduce($answer, function ($a, $b) {
            return $a + (int)$b;
        });
    }

    $redundant = $maximum - $sum;

    if ($redundant > 0) {
        if (in_array($dayOfWeek, [3, 5, 6])) {
            for ($i = 0; $i < count($question); $i++) {
                if (!isset($answer[$i % count($question)]))
                    $answer[$i % count($question)] = 0;
            }

            if ($dayOfWeek == 6)
                $answer[2] += $redundant;
            else
                $answer[1] += $redundant;
        } else {
            for ($i = 0; $i < $redundant; $i++) {
                if (!isset($answer[$i % count($question)]))
                    $answer[$i % count($question)] = 0;
                $answer[$i % count($question)]++;
            }
        }
    }
}
