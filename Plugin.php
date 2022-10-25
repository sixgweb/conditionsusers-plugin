<?php

namespace Sixgweb\ConditionsUsers;

use Event;
use Schema;
use System\Classes\PluginBase;
use RainLab\User\Models\User;
use RainLab\User\Models\UserGroup;
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
        //If RainLab.User isn't installed (migrated), the 
        //event handler will throw a table not found error
        if (!Schema::hasTable('users')) {
            return;
        }
        Event::subscribe(UserConditionerEventHandler::class);
        Event::subscribe(UserGroupConditionerEventHandler::class);
        $this->addUserGroupConditionerToRelatedModels();
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
