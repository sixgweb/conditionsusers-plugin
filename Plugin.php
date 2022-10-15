<?php

namespace Sixgweb\ConditionsUsers;

use App;
use Auth;
use Event;
use System\Classes\PluginBase;
use RainLab\User\Models\User;
use RainLab\User\Models\UserGroup;
use Sixgweb\Conditions\Behaviors\Conditionable;
use Sixgweb\Conditions\Classes\ConditionersManager;
use Sixgweb\ConditionsUsers\Classes\UserConditionerEventHandler;
use Sixgweb\ConditionsUsers\Classes\UserGroupConditionerEventHandler;

/**
 * Plugin Information File
 */
class Plugin extends PluginBase
{
    public $require = [
        'Sixgweb.Conditions',
        'RainLab.User',
    ];

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'ConditionsUsers',
            'description' => 'No description provided yet...',
            'author'      => 'Sixgweb',
            'icon'        => 'icon-leaf'
        ];
    }

    /**
     * Register method, called when the plugin is first registered.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Boot method, called right before the request route.
     *
     * @return void
     */
    public function boot()
    {
        Event::subscribe(UserConditionerEventHandler::class);
        Event::subscribe(UserGroupConditionerEventHandler::class);
        $this->addUserGroupConditionerToRelatedModels();
    }

    /**
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents()
    {
        return []; // Remove this line to activate

        return [
            'Sixgweb\ConditionsUsers\Components\MyComponent' => 'myComponent',
        ];
    }

    /**
     * Registers any backend permissions used by this plugin.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return []; // Remove this line to activate

        return [
            'sixgweb.conditionsusers.some_permission' => [
                'tab' => 'ConditionsUsers',
                'label' => 'Some permission'
            ],
        ];
    }

    public function registerConditionGroups()
    {
        return [];
    }

    protected function addUserGroupConditionerToRelatedModels()
    {
        Event::listen('backend.form.extendFields', function ($widget) {
            $relations = \Sixgweb\Conditions\Classes\Helper::getModelRelationsByClass(
                $widget->model,
                User::class,
                ['belongsTo']
            );

            foreach ($relations as $relation) {
                $postKey = $widget->arrayName ? $widget->arrayName . '.' . $relation : $relation;
                $value = post($postKey, $widget->model->getRelationValue($relation));
                if ($value) {
                    $groups = UserGroup::whereHas('users', function ($q) use ($value) {
                        $q->where('user_id', $value);
                    })->get();
                    if ($groups) {
                        ConditionersManager::instance()->addConditioner($groups);
                    } else {
                        ConditionersManager::instance()->addConditioner(new UserGroup);
                    }
                }
            }
        });
    }
}
