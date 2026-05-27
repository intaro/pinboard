<?php

declare(strict_types=1);

namespace App\Command;

readonly class AggregateConfig
{
    /**
     * @param array<string, float> $longRequestTime    keyed by server_name or 'global'
     * @param array<string, float> $heavyRequest       keyed by server_name or 'global'
     * @param array<string, float> $heavyCpuRequest    keyed by server_name or 'global'
     * @param list<string>         $notificationIgnore
     * @param list<array{hosts: string, email: string}> $notificationList
     * @param array<string, float> $reqTimeBorder      keyed by server_name or 'global'
     */
    public function __construct(
        public string $recordsLifetime,
        public string $aggregationPeriod,
        public array $longRequestTime,
        public array $heavyRequest,
        public array $heavyCpuRequest,
        public bool $notificationEnable,
        public string $notificationSender,
        public string $notificationGlobalEmail,
        public array $notificationIgnore,
        public array $notificationList,
        public array $reqTimeBorder,
        public int $minErrorCode,
    ) {
    }
}
