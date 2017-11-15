<?php

namespace Spatie\Benchmark;

class TextualUuid extends AbstractBenchmark
{
    private $benchmarkRoundsTextualUuid;

    public function name(): string
    {
        return 'Textual UUID';
    }

    public function table()
    {
        return;
    }

    public function seed()
    {
        return;
    }

    public function withBenchmarkRoundsTextualUuid($benchmarkRoundsTextualUuid): TextualUuid
    {
        $this->benchmarkRoundsTextualUuid = $benchmarkRoundsTextualUuid;

        return $this;
    }

    public function run(): BenchmarkResult
    {
        $queries = [];
        $uuids = $this->connection->fetchAll('SELECT `normal_uuid_text` FROM `optimised_uuid`');

        for ($i = 0; $i < $this->benchmarkRoundsTextualUuid; $i++) {
            $uuid = $uuids[array_rand($uuids)]['normal_uuid_text'];

            $queries[] = "SELECT * FROM `optimised_uuid` WHERE `normal_uuid_text` = '$uuid';";
        }

        return $this->runQueryBenchmark($queries);
    }
}
