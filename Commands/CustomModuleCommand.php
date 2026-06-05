<?php

namespace Modules\Addons\CustomModule\Commands;

use Components\Core\Commands\BaseCommand;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class CustomModuleCommand
 * @package Modules\Addons\CustomModule\Commands
 */
class CustomModuleCommand extends BaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'custom-module:command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '[Custom Module] Sample Command';

    /**
     * Execute the console command.
     *
     * @return mixed
     */

    public function handle()
    {
        $this->info('[Custom Module] - Start');

        /**
         * Logic
         */

        $this->comment('Command Logic');

        $this->info('[Custom Module] - End');
    }
    
}