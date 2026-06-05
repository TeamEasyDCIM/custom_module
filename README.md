# EasyDCIM CustomModule

`CustomModule` is the official sample addon for EasyDCIM. It is intended as a reference implementation for developers building their own EasyDCIM extensions.

This version targets **EasyDCIM 1.25.1+** and demonstrates current Laravel/EasyDCIM patterns:

- module service provider bootstrap,
- event subscriber via `CustomModuleEventHandler`,
- Eloquent observer via `DeviceObserver`,
- API v3 routes with Sanctum authentication,
- backend settings page built with EasyDCIM `FormBuilder`,
- module database migration and demo data seeding,
- dashboard and device widgets,
- custom grid column insertion,
- Artisan commands and scheduler examples,
- email notification example using `GeneralMail` and `app_email()`.

> API v2 examples were removed because API v2 is no longer supported. New integrations should use API v3.

## Installation

Install the module into the EasyDCIM addon directory:

```bash
cd /opt/easydcim
git submodule add https://repository.easydcim.com/easydcim/custom_module.git modules/addons/CustomModule
# or, in an existing checkout:
git submodule update --init modules/addons/CustomModule
```

Then activate **Custom Module** in **Modules → My Modules**.

During activation, the module runs its migrations and seeds demo rows for the custom model table example.

## Main Files

| Path | Purpose |
| --- | --- |
| `CustomModule.php` | Module metadata and activation/deactivation hooks |
| `CustomModuleProvider.php` | Registers translations, commands, scheduler, and observers |
| `CustomModuleEventHandler.php` | EasyDCIM event subscriber examples |
| `Controller/Api/v3/ExampleController.php` | API v3 example endpoints |
| `Controller/SettingsController.php` | Backend settings page using `FormBuilder` |
| `Observers/DeviceObserver.php` | Eloquent model event example |
| `database/migrations/*` | Module-owned database schema |
| `Support/Installers/*` | Demo data installer |
| `Widgets/*` | Dashboard and device widget examples |
| `templates/*` | Twig views |

## API v3 Examples

The module registers sample admin API v3 endpoints under:

```text
/api/v3/admin/custom-module
```

Example:

```bash
curl -H "Authorization: Bearer <token>" \
     -H "Accept: application/json" \
     https://example.com/api/v3/admin/custom-module/status
```

## Documentation

Full documentation is available here:

https://www.easydcim.com/documentation/extensions-integrations/extensions-development/sample-extension/
