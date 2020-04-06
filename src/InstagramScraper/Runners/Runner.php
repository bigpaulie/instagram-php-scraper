<?php


namespace InstagramScraper\Runners;


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
     * @return string
     * @throws \InstagramScraper\Exception\InstagramException
     * @throws \InstagramScraper\Exception\InstagramNotFoundException
     */
    private function getMediaCommentsCount():string
    {
        /** @var Media $media */
        $media = $this->instagram->getMediaByCode($this->mediaId);
        return $media->getCommentsCount();
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