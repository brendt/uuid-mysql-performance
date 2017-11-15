<?php

namespace Spatie\Benchmark;

use Ramsey\Uuid\Uuid;

class BinaryUuid extends AbstractBenchmark
{
    public function name(): string
    {
        return 'Binary UUID';
    }

    public function table()
    {
        $this->connection->exec(<<<SQL
DROP TABLE IF EXISTS `normal_uuid`;

CREATE TABLE `normal_uuid` (
    `uuid` BINARY(16) NOT NULL,
    `uuid_text` varchar(36) generated always as
        (insert(
            insert(
                insert(
                    insert(hex(uuid),9,0,'-'),
                14,0,'-'),
            19,0,'-'),
            24,0,'-')
        ) virtual,
    `text` TEXT NOT NULL,

    PRIMARY KEY (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQL
        );
    }

    public function seed()
    {
        $queries = [];

        for ($i = 0; $i < $this->recordsInTable; $i++) {
            $uuid = Uuid::uuid1()->toString();

            $text = $this->randomTexts[array_rand($this->randomTexts)];

            $queries[] = <<<SQL
INSERT INTO `normal_uuid` (`uuid`, `text`) VALUES (UNHEX(REPLACE("$uuid", "-","")), '$text');
SQL;

            if (count($queries) > $this->flushAmount) {
                $this->connection->exec(implode('', $queries));
                $queries = [];
            }
        }

        if (count($queries)) {
            $this->connection->exec(implode('', $queries));
        }
    }

    public function run(): BenchmarkResult
    {
        $queries = [];
        $uuids = $this->connection->fetchAll('SELECT `uuid_text` FROM `normal_uuid`');

        for ($i = 0; $i < $this->benchmarkRounds; $i++) {
            $uuid = $uuids[array_rand($uuids)]['uuid_text'];

            $queries[] = "SELECT * FROM `normal_uuid` WHERE `uuid` = UNHEX(REPLACE('$uuid', '-', ''));";
        }

        return $this->runQueryBenchmark($queries);
    }
}
