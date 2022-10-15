<?php

namespace Sixgweb\ConditionsUsers\Classes;

use Auth;
use Event;
use Sixgweb\Conditions\Classes\ConditionersManager;
use Sixgweb\Conditions\Classes\AbstractConditionerEventHandler;

class UserGroupConditionerEventHandler extends AbstractConditionerEventHandler
{
    protected function getModelClass(): string
    {
        return \RainLab\User\Models\UserGroup::class;
    }

    protected function getControllerClass(): ?string
    {
        //All backend controllers
        return \Backend\Classes\Controller::class;
    }

    protected function getFieldConfig(): array
    {
        return [
            'label' => 'User Group',
            'type' => 'checkboxlist',
            'options' => $this->getModelOptions(),
        ];
    }

    protected function getGroupName(): string
    {
        return 'User Group';
    }

    protected function getModelOptions(): array
    {
        return $this->getModelClass()::lists('name', 'id');
    }

    protected function getConditionerCallback(): ?callable
    {
        return function () {
            Event::listen('cms.page.beforeDisplay', function () {
                if ($user = Auth::getUser()) {
                    $groups = $user->groups->count() ? $user->groups : new \RainLab\User\Models\UserGroup;
                } else {
                    $groups = new \RainLab\User\Models\UserGroup;
                }

                ConditionersManager::instance()->replaceConditioner($groups);
            });
        };
    }
}
