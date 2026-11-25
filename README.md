# EasyDCIM Custom Module

Custom Module is the official starter module for EasyDCIM addons. Read it, copy it and change it.

## Requirements

**Custom Module 2.x requires EasyDCIM 2.0.** Its panel parts use classes that do not exist in EasyDCIM 1.x.
For EasyDCIM 1.x use a 1.x version of this module.

On EasyDCIM 1.x this version registers none of its panel parts: it checks the core classes it needs, writes one line to
the application log and keeps its commands, schedule, events, observers and API running.

The EasyDCIM 2.0 panel is not released yet. **The extension API is experimental until the EasyDCIM 2.0 release**:
class names, parameters, keys and payloads may change without notice.

## Installation

Place the module in `/opt/easydcim/modules/addons/CustomModule`, then activate **Custom Module** in **System > Extensions**
and give the `core.modules.addons.custom-module` permission to the groups that may use it.

The module is inactive by default. Activation runs its migration and adds sample rows. Deactivation removes its menu
entries, pages, columns, actions, filters and Summary parts; its endpoints answer 404. The table, its rows and the stored
options stay.

While active, the sample writes a log line for every saved or deleted device, sets the operating system installation
status text and runs one scheduled command, `custom-module:command`, which only prints text. The settings are only
stored. `custom-module:ipmi-bmc-reset` **resets the BMC of every IPMI device**; it runs only when you start it yourself.

## What is available

Everything here is PHP only. No front-end build is needed.

| What | Where in this module |
| --- | --- |
| Menu entries | `BackendUiNavigationServiceProvider.php` |
| Language group of the panel | `BackendUi/CustomModuleBackendUiProvider.php` |
| Overview page (counters, buttons, cards) | `BackendUi/Http/Controllers/OverviewController.php` |
| List page (search, filters, saved views, row and bulk actions) | `BackendUi/Tables/CustomItemsTable.php` |
| Form page | `BackendUi/Http/Controllers/SettingsController.php` |
| Column, row action, bulk action and filter of the Servers list | `BackendUi/Extensions/ServerTableExtensionListener.php` |
| Tab, card and Actions menu entry of the device Summary | `BackendUi/Extensions/DeviceSummaryExtensionRegistrar.php` |
| Pages, endpoints and their checks | `routes/backend-ui/custom-module.php` |
| API v3 endpoints (Sanctum) | `routes.php`, `Controller/Api/v3/ExampleController.php` |
| Domain events | `CustomModuleEventHandler.php` |
| Model observer, Artisan commands, schedule, mail, migration | `Observers`, `Commands`, `CustomModuleProvider.php`, `Mail`, `database` |

## What is not available

- Theme colours, branding and custom CSS.
- Dashboard widgets and Summary widgets.
- Dialogs with fields, and forms inside a Summary tab. An action may only ask for a confirmation.
- Actions and filters of core lists other than Servers. Columns work on every list that sends `BuildingTableColumns`.
- Parts of other Summary pages (client, company, order).
- HTML, JavaScript or styles of a module. All text is plain text.

## Start your own module

1. Copy the directory and rename the namespace and the classes that start with `CustomModule`.
2. Change `PROPER_NAME`, `SLUG` and `KEY` in `BackendUi/Services/CustomModuleAccess.php`, and `TABLE` and `OPTIONS` next to them.
   Every key of the panel parts derives from them. Two modules with the same key do not work together.
3. Change the names written out elsewhere: the slug in `CustomModule.php`, the translation namespace and the scheduled
   command in `CustomModuleProvider.php`, the command names in `Commands`, the addresses, names and permission in `routes.php`,
   the option prefix and metadata labels in `Controller/Api/v3/ExampleController.php`, and the table name in the migration,
   the model and `Support/Installers`.

## Rules to keep

**Check access in the endpoint.** The permissions of an action, a tab or a card only hide it. The routes of this module
also require the permission of the core page they stand on:

```php
Route::middleware([...$middleware, BackendUiAccess::class.':core.devices.base.summary'])->group(/* ... */);
```

The same holds for API v3: an endpoint that reads or changes core data requires the permission of the core endpoint as well,
accepts only a fixed list of fields and never answers credentials.

**Never let a route group throw.** Laravel keeps a group open when its routes throw, and every later route of EasyDCIM would
join it. Check the core classes before `loadRoutesFrom()`, evaluate anything that can fail outside the groups, and catch
inside them, as `routes/backend-ui/custom-module.php` does.

**Never let a filter match everything by accident.** "Select all matching" and the export use the same query as the list,
so a filter that skips its condition could send a mass action to every server. EasyDCIM treats a filter that fails, or that
adds no condition, as one that matches nothing. Do the same in your own branches:

```php
default => $query->whereRaw('1 = 0'),
```

**Answer pages and tabs through `ExtensionContent`.** It keeps plain text and checked actions only:

```php
return BackendUiResponse::success(ExtensionContent::overview([...]));
```

**Give translation keys, not texts, to anything you register at boot.** The locale of the administrator is not known yet:

```php
new TableActionDefinition(
    key: 'my_module_sync',
    label: 'my-module::backend.sync',
    url: '/backend/api/v1/my-module/servers/{server}/sync', // below /backend/api/v1/, POST, PUT, PATCH or DELETE
    permissions: ['core.modules.addons.my-module'],         // never empty
    confirm: ['title' => 'my-module::backend.sync_title', 'body' => 'my-module::backend.sync_body'],
);
```

**Register guarded.** The core registries of pages, menu entries, tables and language groups refuse a duplicate with an exception.
An exception at boot stops the whole application, so every step of `CustomModuleBackendUiProvider` runs in a `try` block.
A page on an invalid or taken address, a menu entry without its page and a column, action, filter or Summary part with a taken
key are left out and logged.

**Keep column projectors and Summary cards fast.** They run for every row and on every Summary read. Read local data only.

## Changes from 1.x

- The 1.x panel pages, templates, widgets and panel hooks are gone. `backend/modules/custom-module` opens the Overview page.
- The `custom_module_value` column of the Servers list is optional now. Administrators add it in the table settings; stored
  column settings and saved views that name it keep working. While the module is off they open without this column.
- The API v3 endpoints of devices and the statistics also require `api_v3.admin.devices.read`, the metadata endpoint
  `api_v3.admin.devices.metadata_assign`. Metadata is limited to `Custom Module Note` and `Custom Module Enabled`.
- The daily `custom-module:ipmi-bmc-reset` schedule is gone; the command itself stays.
- Options (`custom_module.*`), the database table and the events are unchanged.

## API v3

```bash
curl -H "Authorization: Bearer <token>" \
     -H "Accept: application/json" \
     https://example.com/api/v3/admin/custom-module/status
```

Every endpoint requires `core.modules.addons.custom-module`.

| Endpoint | Also requires |
| --- | --- |
| `GET /status`, `GET /settings`, `PUT /settings` | - |
| `GET /stats`, `GET /devices`, `GET /devices/{id}` | `api_v3.admin.devices.read` |
| `PATCH /devices/{id}/metadata` | `api_v3.admin.devices.metadata_assign` |

## Documentation

https://www.easydcim.com/documentation/extensions-integrations/extensions-development/sample-extension/
