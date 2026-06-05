<?php

namespace Modules\Addons\CustomModule\Observers;

use App\Models\Device;
use Illuminate\Support\Facades\Log;

/**
 * Example A: Eloquent model observer for Device.
 *
 * Observers keep model event code outside the service provider. Use them for
 * short synchronous reactions to model changes. For heavy work, dispatch a Job.
 */
class DeviceObserver
{
    /**
     * Called after a Device record is created or updated.
     */
    public function saved(Device $device): void
    {
        Log::info('[CustomModule] Device saved', [
            'id' => $device->getKey(),
            'label' => $device->label,
            'created' => $device->wasRecentlyCreated,
        ]);

        // Example: tag newly created devices with a default metadata value.
        // Uncomment when you want this sample behavior enabled.
        // if ($device->wasRecentlyCreated) {
        //     $device->forceAssignMetadata(['Custom Module Tag' => 'auto-tagged']);
        // }
    }

    /**
     * Called after a Device record is removed from the database.
     */
    public function deleted(Device $device): void
    {
        Log::info('[CustomModule] Device deleted', [
            'id' => $device->getKey(),
            'label' => $device->label,
        ]);

        // Example: send a notification email when a device is removed.
        // \Modules\Addons\CustomModule\Mail\DeviceDeletedMail::send($device);
    }
}
