<?php

declare(strict_types=1);

namespace Modules\Addons\CustomModule\BackendUi\Extensions;

use App\BackendUi\Services\Summary\SummaryActionDefinition;
use App\BackendUi\Services\Summary\SummaryCardDefinition;
use App\BackendUi\Services\Summary\SummaryExtensionRegistry;
use App\BackendUi\Services\Summary\SummaryTabDefinition;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Modules\Addons\CustomModule\BackendUi\Services\CustomModuleAccess;
use Modules\Addons\CustomModule\Model\CustomModel;

/** Adds a tab, a card and an Actions menu entry to the device Summary. */
final class DeviceSummaryExtensionRegistrar
{
    private const TARGET = 'device';

    /** @param  Closure(): bool  $enabled  Whether the module is active; asked on every Summary read. */
    public function register(SummaryExtensionRegistry $registry, Closure $enabled): void
    {
        $permissions = [CustomModuleAccess::PERMISSION, CustomModuleAccess::SUMMARY_PERMISSION];

        // The tab reads its cards from the endpoint when it opens.
        $registry->registerTab(self::TARGET, new SummaryTabDefinition(
            key: CustomModuleAccess::KEY,
            label: CustomModuleAccess::LANG.'device_tab_title',
            renderer: 'cards',
            dataUrl: CustomModuleAccess::API.'/devices/{device}/tab',
            permissions: $permissions,
        ), $enabled);

        // The card is a part of the Summary itself, so its rows read only local data.
        $registry->registerCard(self::TARGET, new SummaryCardDefinition(
            key: CustomModuleAccess::KEY.'_overview',
            title: CustomModuleAccess::LANG.'device_card_title',
            rows: self::cardRows(...),
            permissions: $permissions,
            description: CustomModuleAccess::LANG.'device_card_desc',
        ), $enabled);

        $registry->registerAction(self::TARGET, new SummaryActionDefinition(
            key: CustomModuleAccess::KEY.'_device_sync',
            label: CustomModuleAccess::LANG.'action_sync_device',
            url: CustomModuleAccess::API.'/devices/{device}/sync',
            permissions: $permissions,
            icon: 'refresh-cw',
            // Keys, not texts: registration runs at boot, before the locale of any administrator is known.
            confirm: [
                'title' => CustomModuleAccess::LANG.'sync_device_title',
                'body' => CustomModuleAccess::LANG.'sync_device_body',
                'confirm_label' => CustomModuleAccess::LANG.'action_sync',
            ],
        ), $enabled);
    }

    /** @return list<array<string, mixed>> */
    private static function cardRows(Model $device): array
    {
        $linked = app(CustomModuleAccess::class)->tableReady() ? CustomModel::query()->where('item_id', (int) $device->getKey())->count() : 0;

        return [
            ['key' => 'device', 'label' => trans(CustomModuleAccess::LANG.'custom_value'), 'value' => 'CustomModule #'.$device->getKey()],
            ['key' => 'linked_items', 'label' => trans(CustomModuleAccess::LANG.'device_linked_items'), 'value' => $linked],
            ['key' => 'state', 'label' => trans('backend/global.status'), 'badge' => ['label' => trans(CustomModuleAccess::LANG.'status_ready'), 'tone' => 'success']],
        ];
    }
}
