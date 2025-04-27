<?php

declare(strict_types=1);

namespace Infrastructure\Log\Messaging\Kafka;

use RdKafka\KafkaConsumer;
use RdKafka\Producer;

class KafkaClientFactory
{
    public static function createProducer(string $brokers): Producer
    {
        $conf = new \RdKafka\Conf();
        $conf->set('metadata.broker.list', $brokers);
        return new Producer($conf);
    }

    public static function createConsumer(string $brokers): KafkaConsumer
    {
        $conf = new \RdKafka\Conf();
        $conf->set('metadata.broker.list', $brokers);
        $conf->set('group.id', 'log-consumer-group');
        $conf->set('auto.offset.reset', 'earliest');

        $conf->set('socket.timeout.ms', '30000');                // Socket timeout (30 seconds)
        $conf->set('session.timeout.ms', '6000');                // Consumer group session timeout
        $conf->set('max.poll.interval.ms', '300000');            // Max interval between polls (5 minutes)
        $conf->set('metadata.request.timeout.ms', '30000');      // Timeout for fetching metadata


        return new KafkaConsumer($conf);
    }
}
