<?php

require_once 'vendor/autoload.php';

$cache = \InstagramScraper\Cache\MemcacheDriver::instance();

/** @var \InstagramScraper\Instagram $instagram */
$instagram = \InstagramScraper\Instagram::withCredentials('username', 'password', $cache);
$instagram->login();

/** @var \InstagramScraper\Model\Comment[] $comments */
$comments = $instagram->getMediaCommentsByCode('BG3Iz-No1IZ', 8000);

$writter = \League\Csv\Writer::createFromPath('./comments.csv');

/** @var \InstagramScraper\Model\Comment $comment */
foreach ($comments as $comment) {
    $writter->insertOne([
        $comment->getId(),
        $comment->getOwner(),
        $comment->getText(),
        $comment->getChildCommentsCount()
    ]);
}