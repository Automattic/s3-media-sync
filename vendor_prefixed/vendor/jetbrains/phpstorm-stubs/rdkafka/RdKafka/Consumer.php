<?php

namespace WPCOM_VIP\RdKafka;

class Consumer extends \RdKafka
{
    /**
     * @param null|Conf $conf
     */
    public function __construct(?\RdKafka\Conf $conf = null)
    {
    }
    /**
     * @param string    $topic_name
     * @param null|TopicConf $topic_conf
     *
     * @return ConsumerTopic
     */
    public function newTopic($topic_name, ?\RdKafka\TopicConf $topic_conf = null)
    {
    }
    /**
     * @return Queue
     */
    public function newQueue()
    {
    }
}
