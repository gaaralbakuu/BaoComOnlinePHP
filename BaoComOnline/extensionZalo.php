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

function gotoGroup($groupName)
{
    global $driver;
    $elementContactSearchInput = $driver->findElements(WebDriverBy::cssSelector('#contact-search-input'));

    if (count($elementContactSearchInput)) {
        $elementContactSearchInput[0]->sendKeys($groupName);
    }

    // (new WebDriverActions($driver))->doubleClick($elementContactSearchInput[0])->perform();

    waitElementPresent('.ReactVirtualized__Grid__innerScrollContainer>div div-16 span.truncate');

    $elementGroups = $driver->findElements(WebDriverBy::cssSelector('.ReactVirtualized__Grid__innerScrollContainer>div div-16 span.truncate'));

    for ($i = 0; $i < count($elementGroups); $i++) {
        if ($elementGroups[$i]->getText() == $groupName) {
            $elementGroups[$i]->click();
            $driver->findElements(WebDriverBy::cssSelector("[data-id=\"div_Main_TabMsg\"]"))[0]->click();
            break;
        }
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
        'Error locating more than five elements'
    );
}



function createPoll($title, array $question)
{
    global $driver, $lastPollName;
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
}

function sendMessage($message)
{
    global $driver;
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
    global $driver, $lastPollName, $question;
    $rightBar = $driver->findElements(WebDriverBy::cssSelector('.fa-outline-right-bar'));
    if (count($rightBar)) {
        $rightBar[0]->click();
    }

    $driver->findElements(WebDriverBy::cssSelector("#add-setting-box>div"))[1]->click();

    $listPoll = $driver->findElements(WebDriverBy::cssSelector('#sideBodyScrollBox .ReactVirtualized__Grid__innerScrollContainer>div'));
    for ($i = 0; $i < count($listPoll); $i++) {
        $pollName = $listPoll[$i]->findElements(WebDriverBy::cssSelector('.truncate-2'))[0]->getText();
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
            if (($index = array_search($title, $question)) !== false) {
                $answer[$index] = $count;
            }
        }
    }

    $driver->findElements(WebDriverBy::cssSelector('[icon="icon-outline-setting"]'))[0]->click();

    waitElementPresent(".popover-v3");
    $divDetailPollMoreBlock = $driver->findElements(WebDriverBy::cssSelector('.popover-v3 [data-id="div_DetailPollMore_Block"]'));
    if (count($divDetailPollMoreBlock)) {
        $divDetailPollMoreBlock[0]->click();

        waitElementPresent('.zl-modal__footer__button.z--btn--fill--secondary-red');
        $driver->findElements(WebDriverBy::cssSelector('.zl-modal__footer__button.z--btn--fill--secondary-red'))[0]->click();
    }else{
        $driver->findElements(WebDriverBy::cssSelector('[icon="close f16"]'))[0]->click();
    }

    $driver->findElements(WebDriverBy::cssSelector('.fa-outline-right-bar-active'))[0]->click();
}
