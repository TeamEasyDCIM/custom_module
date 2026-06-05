<?php

namespace Modules\Addons\CustomModule\Mail;

use App\Mail\GeneralMail;
use App\Models\Device;
use Illuminate\Support\Facades\Mail;

/**
 * Class DeviceDeletedMail
 *
 * Example E: send a notification email when a device is deleted.
 *
 * EasyDCIM ships with a generic GeneralMail mailable that accepts an array with a subject
 * and message body, so you don't need to create a full Mailable
 * subclass for simple notifications.
 *
 * Usage (from a listener or event hook):
 *
 *   DeviceDeletedMail::send($device);
 *
 * Configuration:
 *   Mail is sent to app_email(), the same helper used by core EasyDCIM code.
 *   It reads backend.settings.emailAddress and falls back to the first admin email.
 *
 * @package Modules\Addons\CustomModule\Mail
 */
class DeviceDeletedMail
{
    /**
     * Send a "device deleted" notification to the system administrator.
     *
     * @param Device $device  The device that was just deleted.
     * @return void
     */
    public static function send(Device $device): void
    {
        // Use the same helper as core EasyDCIM code.
        // app_email() reads backend.settings.emailAddress and falls back to the first admin email.
        $adminEmail = app_email();

        if (empty($adminEmail)) {
            // No admin email configured \u2014 skip silently.
            return;
        }

        $subject = sprintf('[EasyDCIM] Device "%s" has been deleted', $device->label);

        // GeneralMail is EasyDCIM\u2019s built-in generic mailable.
        // Pass a subject and the body as HTML in the data array.
        $body = sprintf(
            "The device <strong>%s</strong> (ID: %d) was removed from EasyDCIM.\n\n"
            . "This notification was sent by the CustomModule example.",
            htmlspecialchars($device->label),
            $device->getKey()
        );

        Mail::to($adminEmail)->send(new GeneralMail([
            'subject' => $subject,
            'emailBody' => $body,
        ]));
    }
}
