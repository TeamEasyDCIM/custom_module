<?php

namespace Modules\Addons\CustomModule\Controller;

use Components\Libs\Form\Builder as FormBuilder;
use Illuminate\Http\Request;

/**
 * Class SettingsController
 *
 * Example H: A settings page that saves configuration values to the EasyDCIM
 * options table via OptionRepository.
 *
 * OptionRepository is the standard way to store and retrieve per-module settings.
 * Values are persisted in the `options` database table and cached automatically.
 *
 * Routes are registered in routes.php:
 *   GET  /backend/custom-module/settings        → showSettings()
 *   POST /backend/custom-module/settings/save   → saveSettings()
 *
 * @package Modules\Addons\CustomModule\Controller
 */
class SettingsController extends OutputController
{
    /** Prefix used for all option keys owned by this module */
    private const KEY_PREFIX = 'custom_module.';

    /**
     * Display the settings form.
     *
     * The form is built with Components\Libs\Form\Builder, which is the same
     * backend form builder used by core EasyDCIM controllers.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function showSettings()
    {
        $options = app('OptionRepository');
        $form = new FormBuilder('backend.custom.module.settings', null, 'settings', [
            'data-edc-form' => 1,
            'data-scope' => 'settings',
            'data-ajaxRedir' => 0,
            'url' => route('backend.custom.module.settings.save'),
        ]);

        $form->addField('legend_general', [
            'type' => 'Legend',
            'value' => trans('custom-module::backend.settings'),
            'columns' => 6,
        ]);

        $form->addField('notifications_enabled', [
            'type' => 'Select',
            'label' => 'Enable device deletion notifications',
            'value' => (int) (bool) $options->get(self::KEY_PREFIX . 'notifications_enabled'),
            'options' => [
                0 => trans('backend/global.no'),
                1 => trans('backend/global.yes'),
            ],
            'attributes' => ['data-edc-select' => 1],
            'columns' => 6,
            'helper' => 'Example setting stored as custom_module.notifications_enabled.',
        ]);

        $form->addField('notification_email', [
            'type' => 'Text',
            'label' => 'Notification email',
            'value' => $options->get(self::KEY_PREFIX . 'notification_email') ?: '',
            'columns' => 6,
            'placeholder' => 'admin@example.com',
            'helper' => 'Optional recipient used by the sample notification code.',
        ]);

        $form->addField('log_level', [
            'type' => 'Select',
            'label' => 'Log level',
            'value' => $options->get(self::KEY_PREFIX . 'log_level') ?: 'info',
            'options' => [
                'debug' => 'Debug',
                'info' => 'Info',
                'warning' => 'Warning',
                'error' => 'Error',
            ],
            'attributes' => ['data-edc-select' => 1],
            'columns' => 6,
            'helper' => 'Example select field rendered by FormBuilder.',
        ]);

        return view('CustomModule::settings.form', [
            'form' => $form->render(),
        ]);
    }

    /**
     * Persist the submitted settings.
     *
     * @param Request          $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function saveSettings(Request $request)
    {
        $options = app('OptionRepository');
        $validated = $request->validate([
            'settings.notifications_enabled' => ['required', 'boolean'],
            'settings.notification_email' => ['nullable', 'email', 'max:255'],
            'settings.log_level' => ['required', 'in:debug,info,warning,error'],
        ]);

        $settings = array_get($validated, 'settings', []);

        foreach ($settings as $key => $value) {
            $options->add(self::KEY_PREFIX . $key, $value);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response_json([
                'success' => true,
                'status' => 'success',
                'message' => trans('custom-module::backend.settings_saved'),
                'redirect' => route('backend.custom.module.settings'),
            ]);
        }

        return redirect()
            ->route('backend.custom.module.settings')
            ->with('success', trans('custom-module::backend.settings_saved'));
    }
}
