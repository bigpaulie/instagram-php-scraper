<?php

set_time_limit(0);

require_once '../vendor/autoload.php';

/** @var \InstagramScraper\Cache\MemcacheDriver $cache */
$cache = \InstagramScraper\Cache\MemcacheDriver::instance();

/** @var \League\Csv\AbstractCsv|\League\Csv\Writer $writter */
$writter = \League\Csv\Writer::createFromPath('./comments.csv');

/** @var string $mediaId */
$mediaId = '';

try {
    /** @var \InstagramScraper\Instagram $instagram */
    $instagram = \InstagramScraper\Instagram::withCredentials('username', 'password', $cache);
    $logged = $instagram->login();

    $runner = new \InstagramScraper\Runners\Runner($writter, $instagram, $mediaId);
    $runner->loop();
} catch (\Throwable $e) {
    echo get_class($e) . ' caught: '. $e->getMessage() . "\n";
}