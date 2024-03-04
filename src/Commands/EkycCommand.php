<?php

namespace Fintech\Ekyc\Commands;

use Illuminate\Console\Command;

class EkycCommand extends Command
{
    public $signature = 'ekyc';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
