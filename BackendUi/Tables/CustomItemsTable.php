<?php

declare(strict_types=1);

namespace Modules\Addons\CustomModule\BackendUi\Tables;

use App\BackendUi\Services\Tables\Actions\TableActionDefinition;
use App\BackendUi\Services\Tables\Addons\AddonTable;
use App\BackendUi\Services\Tables\Filters\TableFilterDefinition;
use App\Models\Device;
use App\Models\Item;
use App\Models\User;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Modules\Addons\CustomModule\BackendUi\Services\CustomModuleAccess;
use Modules\Addons\CustomModule\Model\CustomModel;

/**
 * The Custom Items list: search, filters, saved views, sorting, paging, a row action and a bulk action.
 *
 * The shared table code does the work; this class names the data, the columns and the actions.
 */
final class CustomItemsTable extends AddonTable
{
    public const IDENTITY = CustomModuleAccess::SLUG.'.items';

    public function identity(): string
    {
        return self::IDENTITY;
    }

    /** Checked by the shared table code for the list, its settings and its saved views. */
    public function permission(): string
    {
        return CustomModuleAccess::PERMISSION;
    }

    public function available(): bool
    {
        $access = app(CustomModuleAccess::class);

        return $access->available() && $access->tableReady();
    }

    public function routeName(): string
    {
        return CustomModuleAccess::ROUTE.'.items';
    }

    /** The rows with the relations their cells read, so one page costs a fixed number of queries. */
    public function rows(): Builder
    {
        return CustomModel::query()->with(['device.itemModel:id,name', 'device.metadata.field', 'item']);
    }

    public function searchColumns(): array
    {
        return ['name', 'type', 'description'];
    }

    /** Column key => [label key, sortable], in display order. */
    public function columnMap(): array
    {
        return [
            'id' => ['backend/global.id', true],
            'name' => ['backend/global.name', true],
            'type' => ['backend/global.type', true],
            'item_id' => [CustomModuleAccess::LANG.'related_device', false],
            'description' => ['backend/global.description', true],
            'created_at' => ['backend/global.created_at', true],
            self::ACTIONS => ['backend/global.actions', false],
        ];
    }

    public function filterDefinitions(): array
    {
        $filters = [];
        foreach (['name' => 'backend/global.name', 'type' => 'backend/global.type', 'description' => 'backend/global.description'] as $column => $label) {
            $filters[$column] = ['source' => 'custom', 'type' => 'text', 'label' => trans($label), 'query_callback' => self::textFilter($column)];
        }

        return $filters;
    }

    public function filterOrder(): array
    {
        return ['name', 'type', 'description'];
    }

    public function defaultOrdering(): array
    {
        return [['id', 'desc']];
    }

    /** Actions of a row; the list runs them as it runs the actions of any addon. */
    public function rowActions(User $admin): array
    {
        return $this->describeActions($admin, [
            new TableActionDefinition(
                key: 'sync',
                label: CustomModuleAccess::LANG.'action_sync',
                url: CustomModuleAccess::API.'/items/{id}/sync',
                permissions: [CustomModuleAccess::PERMISSION],
                icon: 'refresh-cw',
            ),
            new TableActionDefinition(
                key: 'delete',
                label: 'backend/global.delete',
                url: CustomModuleAccess::API.'/items/{id}',
                permissions: [CustomModuleAccess::PERMISSION],
                method: 'DELETE',
                icon: 'trash-2',
                confirm: [
                    'title' => CustomModuleAccess::LANG.'delete_item_title',
                    'body' => CustomModuleAccess::LANG.'delete_item_body',
                    'confirm_label' => 'backend/global.delete',
                    'tone' => 'danger',
                ],
            ),
        ]);
    }

    /** Actions of the selected rows. */
    public function massActions(User $admin): array
    {
        return $this->describeActions($admin, [
            new TableActionDefinition(
                key: 'mass_delete',
                label: 'backend/global.delete',
                url: CustomModuleAccess::API.'/items/mass-delete',
                permissions: [CustomModuleAccess::PERMISSION],
                scope: 'bulk',
                icon: 'trash-2',
                confirm: [
                    'title' => CustomModuleAccess::LANG.'mass_delete_title',
                    'body' => CustomModuleAccess::LANG.'mass_delete_body',
                    'confirm_label' => 'backend/global.delete',
                    'tone' => 'danger',
                ],
            ),
        ]);
    }

    /** The cells of one row: plain data only, the panel draws them. */
    protected function rowCells(Model $row): array
    {
        return [
            'id' => $this->cells->number($row->getKey()),
            'name' => $this->cells->text($row->getRawOriginal('name')),
            'type' => $this->cells->text($row->getRawOriginal('type')),
            'item_id' => $this->itemCell($row),
            'description' => $this->cells->text($row->getRawOriginal('description')),
            'created_at' => $this->cells->date($row->getRawOriginal('created_at')),
            self::ACTIONS => ['kind' => 'actions'],
        ];
    }

    /** A device as on the Servers list, with its Summary page when the administrator may open it; any other item by its name. */
    private function itemCell(Model $row): array
    {
        $device = $row->getRelationValue('device');
        if ($device instanceof Device) {
            return $this->cells->named((int) $device->getKey(), $device, $this->summaryHref($device));
        }
        $item = $row->getRelationValue('item');

        return $item instanceof Item ? $this->relationCell((int) $item->getKey(), (string) $item->getRawOriginal('label')) : $this->cells->text('');
    }

    /**
     * @param  list<TableActionDefinition>  $actions
     * @return list<array<string, mixed>>
     */
    private function describeActions(User $admin, array $actions): array
    {
        return array_values(array_map(static fn (TableActionDefinition $action): array => $action->descriptor(),
            array_filter($actions, static fn (TableActionDefinition $action): bool => $action->allowed($admin))));
    }

    /**
     * A text filter on one column; wildcards typed by the administrator are plain characters.
     *
     * @return Closure(Builder, string, mixed): void
     */
    private static function textFilter(string $column): Closure
    {
        return static function (Builder $query, string $operator, mixed $value) use ($column): void {
            $qualified = $query->getModel()->qualifyColumn($column);
            $text = is_scalar($value) ? (string) $value : '';
            $pattern = TableFilterDefinition::escapeLike($text);
            // A negated filter keeps the rows without a value.
            $orEmpty = static fn (string $comparison, string $other): Closure => static fn (Builder $rows) => $rows->whereNull($qualified)->orWhere($qualified, $comparison, $other);

            match ($operator) {
                'contains' => $query->where($qualified, 'like', '%'.$pattern.'%'),
                'not_contains' => $query->where($orEmpty('not like', '%'.$pattern.'%')),
                'equals' => $query->where($qualified, '=', $text),
                'not_equals' => $query->where($orEmpty('!=', $text)),
                'starts_with' => $query->where($qualified, 'like', $pattern.'%'),
                'is_empty' => $query->where($orEmpty('=', '')),
                'is_not_empty' => $query->whereNotNull($qualified)->where($qualified, '!=', ''),
                // Never widen the list on an operator this filter does not know.
                default => $query->whereRaw('1 = 0'),
            };
        };
    }
}
