<?php

namespace Modules\Addons\CustomModule\Commands;

use App\Repositories\DevicesRepository;
use Components\Core\Commands\BaseCommand;
use Indatus\Dispatcher\Scheduling\Schedulable;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class IpmiBmcResetCommand
 * @package Modules\Addons\CustomModule\Commands
 */
class IpmiBmcResetCommand extends BaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'custom-module:ipmi-bmc-reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '[Custom Module] IPMI BMC Reset Cold for all servers';

    /**
     * Execute the console command.
     *
     * @return mixed
     */

    public function fire()
    {
        /**
         * @var DevicesRepository $devicesRepository
         */
        $devicesRepository = app(DevicesRepository::class);

        $filters = [
            'metadata_key' => [19],
            'metadata_value' => 1
        ];

        $ipmiDevices = $devicesRepository->fetchFilteredDevices($filters, ['user'])->get();

        $this->comment(vsprintf('Found %s IPMI devices', [$ipmiDevices->count()]));

        /**
         * @var Device $device
         */
        foreach($ipmiDevices as $device) {
            $this->comment(vsprintf('Running command for device %s', [$device->getLabeledNameAttribute()]));

            $result = $device->ipmiCommand('bmc reset cold');

            if(array_get($result, 'status') == 'error') {
                $this->error(array_get($result, 'message'));
            } else {
                $this->info(json_encode(array_get($result, 'response')));
            }
        }
    }
    
}