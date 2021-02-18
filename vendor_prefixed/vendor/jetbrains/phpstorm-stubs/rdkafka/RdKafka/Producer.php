<?php

namespace WPCOM_VIP\RdKafka;

class Producer extends \RdKafka
{
    /**
     * @param null|Conf $conf
     */
    public function __construct(\RdKafka\Conf $conf = null)
    {
    }
    /**
     * @param string    $topic_name
     * @param null|TopicConf $topic_conf
     *
     * @return ProducerTopic
     */
    public function newTopic($topic_name, ?\RdKafka\TopicConf $topic_conf = null)
    {
    }
    /**
     * @param int $timeoutMs
     *
     * @return void
     */
    public function initTransactions(int $timeoutMs)
    {
    }
    /**
     * @return void
     */
    public function beginTransaction()
    {
    }
    /**
     * @param int $timeoutMs
     *
     * @return void
     */
    public function commitTransaction(int $timeoutMs)
    {
    }
    /**
     * @param int $timeoutMs
     *
     * @return void
     */
    public function abortTransaction(int $timeoutMs)
    {
    }
}
