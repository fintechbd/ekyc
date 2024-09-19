<?php

namespace Fintech\Ekyc\Commands;

use Exception;
use Fintech\Core\Traits\HasCoreSetting;
use Illuminate\Console\Command;

class InstallCommand extends Command
{
    use HasCoreSetting;

    public $signature = 'ekyc:install';

    public $description = 'Configure the system for the `fintech/ekyc` module';

    private array $settings = [
        [
            'package' => 'ekyc',
            'label' => 'KYC Reference Token Count',
            'description' => 'The last token count value is assigned and the next will be increment by 1.',
            'key' => 'reference_count',
            'type' => 'integer',
            'value' => '1',
        ],
    ];

    public function handle(): int
    {
        $this->infoMessage('Module Installation', 'RUNNING');

        try {

            $this->addOverwriteSetting();

            return self::SUCCESS;

        } catch (Exception $e) {

            $this->components->twoColumnDetail($e->getMessage(), '<fg=red;options=bold>ERROR</>');

            return self::FAILURE;
        }
    }
}
