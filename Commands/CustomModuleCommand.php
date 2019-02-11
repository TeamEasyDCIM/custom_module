<?php

namespace Modules\Addons\CustomModule\Commands;

use Components\Core\Commands\BaseCommand;
use Indatus\Dispatcher\Scheduling\Schedulable;
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

    public function fire()
    {
        $this->info('[Custom Module] - Start');

        /**
         * Logic
         */

        $this->comment('Command Logic');

        $this->info('[Custom Module] - End');
    }

    /**
     * @param Schedulable $scheduler
     * @return Schedulable
     */
    public function schedule(Schedulable $scheduler)
    {
        $scheduler->everyMinutes(5);

        return $scheduler;
    }
    
}