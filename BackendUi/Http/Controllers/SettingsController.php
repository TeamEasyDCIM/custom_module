<?php

declare(strict_types=1);

namespace Modules\Addons\CustomModule\BackendUi\Http\Controllers;

use App\BackendUi\Http\Controllers\V1\BackendUiController;
use App\BackendUi\Http\Responses\BackendUiResponse;
use App\BackendUi\Services\Extensions\ExtensionContent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\Addons\CustomModule\BackendUi\Services\CustomModuleAccess;
use Modules\Addons\CustomModule\BackendUi\Services\CustomModuleSettings;

/** The Settings page: one form on the `addons-declarative-form` template. */
final class SettingsController extends BackendUiController
{
    public function __construct(private readonly CustomModuleSettings $settings) {}

    /**
     * Read the Custom Module settings form
     *
     * Returns the sections, fields and current values of the form. Requires the active web-admin session, the Custom Module permission and an active addon.
     *
     * @group Backend UI Endpoints
     *
     * @subgroup Custom Module
     *
     * @authenticated
     *
     * @return JsonResponse Backend UI response envelope.
     *
     * @response 200 scenario="Synthetic success excerpt" {"success":true,"status":"success","message":"Success","data":{"description":"","submit_label":"Save Changes","sections":[],"values":{"log_level":"info"}}}
     */
    public function show(): JsonResponse
    {
        $lang = CustomModuleAccess::LANG;

        // ExtensionContent keeps plain text only, so always answer through it.
        return BackendUiResponse::success(ExtensionContent::form([
            'description' => trans($lang.'settings_description'),
            'submit_label' => trans('backend/global.save_changes'),
            'sections' => [[
                'key' => 'general',
                'title' => trans($lang.'settings_general_title'),
                'description' => trans($lang.'settings_general_desc'),
                'fields' => [
                    ['key' => 'notifications_enabled', 'type' => 'boolean', 'label' => trans($lang.'field_notifications_enabled'),
                        'helper' => trans($lang.'field_notifications_enabled_help')],
                    ['key' => 'notification_email', 'type' => 'email', 'label' => trans($lang.'field_notification_email'),
                        'placeholder' => 'admin@example.com', 'helper' => trans($lang.'field_notification_email_help')],
                    ['key' => 'log_level', 'type' => 'select', 'label' => trans($lang.'field_log_level'), 'required' => true,
                        'helper' => trans($lang.'field_log_level_help'),
                        'options' => array_map(static fn (string $level): array => ['value' => $level, 'label' => trans($lang.'log_level_'.$level)], CustomModuleSettings::LOG_LEVELS)],
                ],
            ]],
            'values' => $this->settings->values(),
        ]));
    }

    /**
     * Save the Custom Module settings
     *
     * Stores the three settings of the form. Requires the active web-admin session, the Custom Module permission and an active addon; unsafe methods require web CSRF.
     *
     * @group Backend UI Endpoints
     *
     * @subgroup Custom Module
     *
     * @authenticated
     *
     * @bodyParam values object required The values of the form. No-example.
     * @bodyParam values.notifications_enabled boolean required Device deletion notifications. No-example.
     * @bodyParam values.notification_email string Recipient address. No-example.
     * @bodyParam values.log_level string required One of debug, info, warning, error. No-example.
     *
     * @param  Request  $request  Current request.
     * @return JsonResponse Backend UI response envelope.
     *
     * @response 200 scenario="Synthetic success" {"success":true,"status":"success","message":"Settings have been saved.","data":{"values":{"notifications_enabled":true,"notification_email":"","log_level":"info"}}}
     */
    public function update(Request $request): JsonResponse
    {
        // Only these keys are stored, whatever else the request holds.
        $values = $request->validate([
            'values' => ['required', 'array'],
            'values.notifications_enabled' => ['required', 'boolean'],
            'values.notification_email' => ['nullable', 'string', 'email', 'max:255'],
            'values.log_level' => ['required', 'string', Rule::in(CustomModuleSettings::LOG_LEVELS)],
        ])['values'];
        $this->settings->save($values);

        return BackendUiResponse::success(['values' => $this->settings->values()], trans(CustomModuleAccess::LANG.'settings_saved'));
    }
}
