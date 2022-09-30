<?php

namespace Sixgweb\ConditionsUsers\Classes;

use Auth;
use Sixgweb\Conditions\Classes\ConditionsManager;
use Sixgweb\Conditions\Classes\AbstractConditionerEventHandler;

class UserGroupConditionerEventHandler extends AbstractConditionerEventHandler
{
    protected $set = false;

    protected function getModelClass(): string
    {
        return \RainLab\User\Models\UserGroup::class;
    }

    protected function getModelFields(): string
    {
        return base_path() . '/plugins/sixgweb/conditionsusers/conditioners/usergroup.yaml';
    }

    protected function getModelOptions(): array
    {
        return [null => 'NULL'] + $this->getModelClass()::lists('name', 'id');
    }

    public static function getUserGroupIdOptions()
    {
        return \RainLab\User\Models\UserGroup::lists('name', 'id');
    }

    protected function getComponentClasses(): ?array
    {
        return [
            \Cms\Classes\ComponentBase::class
        ];
    }

    protected function getControllerClass(): ?string
    {
        return \Backend\Classes\Controller::class;
    }

    protected function getConditionGroupName(): string
    {
        return 'User Group';
    }

    protected function componentCallback($component, $conditionerModel): void
    {
        if ($this->set === true) {
            return;
        }

        if ($user = Auth::getUser()) {
            $groups = $user->groups->count() ? $user->groups : new \RainLab\User\Models\UserGroup;
        } else {
            $user = new \RainLab\User\Models\User;
            $groups = new \RainLab\User\Models\UserGroup;
        }

        $conditionsManager = ConditionsManager::instance();
        $conditionsManager->replaceConditioner($user);
        $conditionsManager->replaceConditioner($groups);
        $this->set = true;
    }
}
