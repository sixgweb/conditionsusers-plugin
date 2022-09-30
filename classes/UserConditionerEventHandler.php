<?php

namespace Sixgweb\ConditionsUsers\Classes;

use Auth;
use Sixgweb\Conditions\Classes\ConditionsManager;
use Sixgweb\Conditions\Classes\AbstractConditionerEventHandler;

class UserConditionerEventHandler extends AbstractConditionerEventHandler
{
    protected $set = false;

    protected function getModelClass(): string
    {
        return \RainLab\User\Models\User::class;
    }

    protected function getModelFields(): string
    {
        return base_path() . '/plugins/sixgweb/conditionsusers/conditioners/user.yaml';
    }

    protected function getModelOptions(): array
    {
        return [
            '1' => 'Logged In',
            'null' => 'Not Logged In',
        ];
        return $this->getModelClass()::lists('name', 'id');
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
        return 'Users';
    }

    protected function componentCallback($component, $conditionerModel): void
    {
        if ($this->set === true) {
            return;
        }

        $user = Auth::getUser() ?? new \RainLab\User\Models\User;
        $conditionsManager = ConditionsManager::instance();
        $conditionsManager->replaceConditioner($user);
        $this->set = true;
    }
}
