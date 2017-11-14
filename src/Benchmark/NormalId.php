<?php

namespace Spatie\Benchmark;

use Doctrine\DBAL\Logging\DebugStack;

class NormalId extends AbstractBenchmark
{
    public function name(): string
    {
        return 'Normal ID';
    }

    public function table()
    {
        $this->connection->exec(<<<SQL
DROP TABLE IF EXISTS `normal_id`;

CREATE TABLE `normal_id` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `text` TEXT NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQL
        );
    }

    public function seed()
    {
        $queries = [];

        for ($i = 0; $i < $this->seederAmount; $i++) {
            $text = $this->randomTexts[array_rand($this->randomTexts)];

            $queries[] = <<<SQL
INSERT INTO `normal_id` (`text`) VALUES ('$text');
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

    public function run(): float
    {
        $queries = [];
        $ids = $this->connection->fetchAll('SELECT `id` FROM `normal_id`');

        for ($i = 1; $i < $this->benchmarkRounds; $i++) {
            $id = $ids[array_rand($ids)]['id'];

            $queries[] = "SELECT * FROM `normal_id` WHERE `id` = {$id};";
        }

        return $this->runQueryBenchmark($queries);
    }
}
