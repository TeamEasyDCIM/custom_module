<?php

declare(strict_types=1);

namespace Modules\Addons\CustomModule\BackendUi\Services;

/** The settings of the module, stored as options under the keys of 1.x. */
final class CustomModuleSettings
{
    public const LOG_LEVELS = ['debug', 'info', 'warning', 'error'];

    /** @return array{notifications_enabled: bool, notification_email: string, log_level: string} The stored values, with their defaults. */
    public function values(): array
    {
        $options = app('OptionRepository');
        $level = (string) $options->get(CustomModuleAccess::OPTIONS.'log_level');

        return [
            'notifications_enabled' => (bool) $options->get(CustomModuleAccess::OPTIONS.'notifications_enabled'),
            'notification_email' => (string) $options->get(CustomModuleAccess::OPTIONS.'notification_email'),
            'log_level' => in_array($level, self::LOG_LEVELS, true) ? $level : 'info',
        ];
    }

    /** @param  array{notifications_enabled: mixed, notification_email?: mixed, log_level: mixed}  $values  Validated values; no other key is stored. */
    public function save(array $values): void
    {
        $options = app('OptionRepository');
        $options->add(CustomModuleAccess::OPTIONS.'notifications_enabled', (bool) $values['notifications_enabled']);
        $options->add(CustomModuleAccess::OPTIONS.'notification_email', (string) ($values['notification_email'] ?? ''));
        $options->add(CustomModuleAccess::OPTIONS.'log_level', (string) $values['log_level']);
    }
}
