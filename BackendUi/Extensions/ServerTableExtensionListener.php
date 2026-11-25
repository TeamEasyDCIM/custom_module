<?php

declare(strict_types=1);

namespace Modules\Addons\CustomModule\BackendUi\Extensions;

use App\BackendUi\Events\Tables\BuildingTableActions;
use App\BackendUi\Events\Tables\BuildingTableColumns;
use App\BackendUi\Events\Tables\BuildingTableFilters;
use App\BackendUi\Services\Tables\Actions\TableActionDefinition;
use App\BackendUi\Services\Tables\Columns\ColumnDefinition;
use App\BackendUi\Services\Tables\Filters\TableFilterDefinition;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\Log;
use Modules\Addons\CustomModule\BackendUi\Services\CustomModuleAccess;
use Throwable;

/** Adds a column, a row action, a bulk action and a filter to the Servers list. */
final class ServerTableExtensionListener
{
    private const TABLE = 'devices.servers';

    /** The key of the column in 1.x, so stored column settings and saved views keep working. */
    public const COLUMN = CustomModuleAccess::KEY.'_value';

    public const ROW_ACTION = CustomModuleAccess::KEY.'_sync';

    public const BULK_ACTION = CustomModuleAccess::KEY.'_mass_sync';

    public const FILTER = CustomModuleAccess::KEY.'_item_name';

    public function __construct(private readonly CustomModuleAccess $access) {}

    /** An optional text column; its value is computed in PHP, so it neither sorts nor exports. */
    public function columns(BuildingTableColumns $event): void
    {
        if ($event->table !== self::TABLE) {
            return;
        }
        // EasyDCIM guards listeners as well; a starter module shows how to keep its own failure to itself.
        try {
            if (! $this->access->available()) {
                return;
            }
            $event->columns->add(new ColumnDefinition(
                key: self::COLUMN,
                label: CustomModuleAccess::LANG.'custom_value',
                permissions: [CustomModuleAccess::PERMISSION],
                // Runs once per row, also while the column is hidden: read only what the row holds, never query here.
                projector: static function (Model|array $row): array {
                    $value = 'CustomModule #'.($row instanceof Model ? $row->getKey() : ($row['id'] ?? ''));

                    return ['kind' => 'text', 'value' => $value, 'display' => $value, 'empty' => false];
                },
            ));
        } catch (Throwable $exception) {
            Log::warning('[CustomModule] The Servers column was left out.', ['message' => $exception->getMessage()]);
        }
    }

    /** A row action and a bulk action, both asking before they send their request. */
    public function actions(BuildingTableActions $event): void
    {
        if ($event->table !== self::TABLE || ! $this->access->available()) {
            return;
        }
        $permissions = [CustomModuleAccess::PERMISSION, CustomModuleAccess::SERVERS_PERMISSION];
        $event->actions->add(new TableActionDefinition(
            key: self::ROW_ACTION,
            label: CustomModuleAccess::LANG.'action_sync_server',
            url: CustomModuleAccess::API.'/servers/{server}/sync',
            permissions: $permissions,
            icon: 'refresh-cw',
            // Keys, not texts: the window is translated for the administrator of each request.
            confirm: [
                'title' => CustomModuleAccess::LANG.'sync_server_title',
                'body' => CustomModuleAccess::LANG.'sync_server_body',
                'confirm_label' => CustomModuleAccess::LANG.'action_sync',
            ],
        ));
        // A bulk action gets the selected IDs as {"ids": [...]}.
        $event->actions->add(new TableActionDefinition(
            key: self::BULK_ACTION,
            label: CustomModuleAccess::LANG.'action_sync_servers',
            url: CustomModuleAccess::API.'/servers/sync',
            permissions: $permissions,
            scope: 'bulk',
            icon: 'refresh-cw',
            confirm: [
                'title' => CustomModuleAccess::LANG.'sync_servers_title',
                'body' => CustomModuleAccess::LANG.'sync_servers_body',
                'confirm_label' => CustomModuleAccess::LANG.'action_sync',
            ],
        ));
    }

    /** A text filter on the custom items of a server. */
    public function filters(BuildingTableFilters $event): void
    {
        if ($event->table !== self::TABLE || ! $this->access->available()) {
            return;
        }
        $event->filters->add(new TableFilterDefinition(
            key: self::FILTER,
            label: CustomModuleAccess::LANG.'filter_item_name',
            type: 'text',
            queryCallback: $this->itemNameFilter(...),
            permissions: [CustomModuleAccess::PERMISSION],
        ));
    }

    /**
     * Adds the condition in SQL, never through a list of IDs.
     *
     * When the filter cannot be applied it matches nothing. A filter that silently matches everything would put every
     * server behind "select all matching" and so behind mass actions.
     */
    private function itemNameFilter(Builder $query, string $operator, mixed $value): void
    {
        $text = is_scalar($value) ? (string) $value : '';
        $pattern = TableFilterDefinition::escapeLike($text);
        $named = static fn (string $comparison, string $name): Closure => static fn (QueryBuilder $items) => $items->where(CustomModuleAccess::TABLE.'.name', $comparison, $name);
        $items = static fn (?Closure $condition = null): Closure => static function (QueryBuilder $items) use ($query, $condition): void {
            $items->selectRaw('1')->from(CustomModuleAccess::TABLE)->whereColumn(CustomModuleAccess::TABLE.'.item_id', $query->getModel()->qualifyColumn('id'));
            if ($condition !== null) {
                $condition($items);
            }
        };
        if (! $this->access->tableReady()) {
            $query->whereRaw('1 = 0');

            return;
        }
        match ($operator) {
            'contains' => $query->whereExists($items($named('like', '%'.$pattern.'%'))),
            'not_contains' => $query->whereNotExists($items($named('like', '%'.$pattern.'%'))),
            'equals' => $query->whereExists($items($named('=', $text))),
            'not_equals' => $query->whereNotExists($items($named('=', $text))),
            'starts_with' => $query->whereExists($items($named('like', $pattern.'%'))),
            'is_empty' => $query->whereNotExists($items()),
            'is_not_empty' => $query->whereExists($items()),
            default => $query->whereRaw('1 = 0'),
        };
    }
}
