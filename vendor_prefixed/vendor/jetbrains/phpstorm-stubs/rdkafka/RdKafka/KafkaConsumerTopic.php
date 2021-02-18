<?php

namespace WPCOM_VIP\RdKafka;

class KafkaConsumerTopic extends \RdKafka\Topic
{
    /**
     * @param int $partition
     * @param int $offset
     *
     * @return void
     */
    public function offsetStore($partition, $offset)
    {
    }
}
