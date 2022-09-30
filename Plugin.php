<?php

namespace Sixgweb\ConditionsUsers;

use App;
use Auth;
use Event;
use System\Classes\PluginBase;
use RainLab\User\Models\User;
use RainLab\User\Models\UserGroup;
use Sixgweb\Conditions\Behaviors\Conditionable;
use Sixgweb\Conditions\Classes\ConditionsManager;
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
        //Event::subscribe(ConditionableEventHandler::class);
        Event::subscribe(UserConditionerEventHandler::class);
        Event::subscribe(UserGroupConditionerEventHandler::class);
        $this->addUserGroupConditionerToRelatedModels();
        //$this->extendUserModels();
        //$this->extendFilterWidget();
        //$this->addUserConditioners();
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
                        ConditionsManager::instance()->addConditioner($groups);
                    } else {
                        ConditionsManager::instance()->addConditioner(new UserGroup);
                    }
                }
            }
        });
    }

    protected function extendUserModels()
    {
        exit('hi');
        \RainLab\User\Models\User::extend(function ($model) {
            $model->implement[] = 'Sixgweb.Conditions.Behaviors.Conditioner';
            $model->addDynamicProperty('conditionerFields', base_path() . '/plugins/sixgweb/conditionsusers/conditioners/user.yaml');
            $model->addPurgeable('conditionerFields');
        });

        \RainLab\User\Models\UserGroup::extend(function ($model) {
            $model->implement[] = 'Sixgweb.Conditions.Behaviors.Conditioner';
            $model->implement[] = 'Sixgweb.Database.Behaviors.Purgeable';
            $model->addDynamicProperty('conditionerFields', base_path() . '/plugins/sixgweb/conditionsusers/conditioners/usergroup.yaml');
            $model->addDynamicProperty('purgeable', ['conditionerFields']);
        });
    }

    protected function extendFilterWidget()
    {
        Event::listen('backend.filter.extendScopes', function ($widget) {

            if (!$widget->model->isClassExtendedWith(Conditionable::class)) {
                return;
            }

            $widget->addScopes([
                'RainLab_User_Models_UserGroup' => [
                    'label' => 'Forms',
                    'options' => UserGroup::get()->pluck('name', 'id')->toArray(),
                    'type' => 'dropdown',
                    'emptyOption' => 'All User Groups',
                    'modelScope' => 'meetsConditions'
                ]
            ]);
        });
    }


    protected function addUserConditioners()
    {
        $conditionsManager = ConditionsManager::instance();
        $conditionsManager->addConditionerGroup('User', 'RainLab\User\Models\User');
        $conditionsManager->addConditionerGroup('User Group', 'RainLab\User\Models\UserGroup');

        if (!App::runningInBackend()) {
            if ($user = Auth::getUser()) {
                $groups = $user->groups->count() ? $user->groups : new \RainLab\User\Models\UserGroup;
            } else {
                $user = new \RainLab\User\Models\User;
                $groups = new \RainLab\User\Models\UserGroup;
            }
            $conditionsManager->addConditioner($user);
            $conditionsManager->addConditioner($groups);
        }
    }
}
