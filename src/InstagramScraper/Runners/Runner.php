<?php


namespace InstagramScraper\Runners;


use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use InstagramScraper\Cache\MemcacheDriver;
use InstagramScraper\Instagram;
use InstagramScraper\Model\Media;
use League\Csv\Writer;

/**
 * Class Runner
 * @package InstagramScraper\Runners
 */
class Runner
{
    /**
     * @var Writer
     */
    private $writter;

    /**
     * @var Instagram
     */
    private $instagram;

    /**
     * @var string
     */
    private $mediaId;

    /**
     * @var MemcacheDriver
     */
    private $cache;

    /**
     * Runner constructor.
     * @param Writer $writter
     * @param Instagram $instagram
     * @param string $mediaId
     */
    public function __construct(Writer $writter, Instagram $instagram, string $mediaId)
    {
        $this->writter = $writter;
        $this->instagram = $instagram;
        $this->mediaId = $mediaId;
        $this->cache = MemcacheDriver::instance();
    }

    /**
     * @param string $message
     */
    public static function sendNotification(string $message)
    {
        $client = new Client();
        $request = new Request('GET', 'https://api.telegram.org/bot1289841030:AAEZtdgLqhwGICIKHBXrq7cILL2SGmxxbN0/sendMessage?chat_id=-487558137&text='.$message.'&disable_notification=true');
        $client->send($request);
    }

    /**
     * @throws \InstagramScraper\Exception\InstagramException
     * @throws \InstagramScraper\Exception\InstagramNotFoundException
     * @throws \League\Csv\CannotInsertRecord
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function loop()
    {
        /** @var Media $media */
        $media = $this->instagram->getMediaByCode($this->mediaId);
        $total = $media->getCommentsCount();
        $cursor = 0;

        $notification = 'Starting processes, there are '. $total . ' comments for media '. $this->mediaId;
        self::sendNotification($notification);

        /**
         * The maximum number of items (comments) per tick
         *
         * @var int
         */
        $tick = 10;

        $lastId = null;
        while ($cursor <= $total) {

            /** @var \InstagramScraper\Model\Comment[] $comments */
            $comments = $this->instagram->getMediaCommentsByCode($this->mediaId, $tick, $lastId);
            $count = count($comments);

            /** @var \InstagramScraper\Model\Comment $comment */
            foreach ($comments as $comment) {
                $this->writter->insertOne([
                    $comment->getId(),
                    $comment->getOwner()->getUsername(),
                    $comment->getText(),
                    $comment->getChildCommentsCount(),
                    $comment->getCreatedAt()
                ]);

                $cursor++;
                $lastId = $comment->getId();
            }

            if (($total - $cursor) <= $tick) {
                $tick = ($total - $cursor);
                if ($tick == 0) {
                    self::sendNotification('Process done '. $total . ' comments fetched');
                    exit(0);
                }
            }

            echo "Total comments {$total} cursor at {$cursor} last id {$lastId} comments this tick {$count} \n";
//            if ($count == 0) {
//                exit(0);
//            }
        }
    }
}