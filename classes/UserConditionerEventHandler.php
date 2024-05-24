<?php

namespace Sixgweb\ConditionsUsers\Classes;

use Auth;
use Event;
use Sixgweb\Conditions\Classes\ConditionersManager;
use Sixgweb\Conditions\Classes\AbstractConditionerEventHandler;

class UserConditionerEventHandler extends AbstractConditionerEventHandler
{
    protected function getModelClass(): string
    {
        return \RainLab\User\Models\User::class;
    }

    protected function getControllerClass(): ?string
    {
        //All backend controllers
        return \Backend\Classes\Controller::class;
    }

    protected function getFieldConfig(): array
    {
        return [
            'label' => 'User',
            'type' => 'recordfinder',
            'list' => '~/plugins/rainlab/user/models/user/columns.yaml',
            'recordsPerPage' => 10,
            'title' => 'Find User',
            'prompt' => 'Click the Find button to find a user',
            'keyFrom' => 'id',
            'nameFrom' => 'name',
            'descriptionFrom' => 'email',
            'searchMode' => 'all',
            'useRelation' => false,
            'modelClass' => 'RainLab\User\Models\User',
        ];
    }

    protected function getGroupName(): string
    {
        return 'Users';
    }

    protected function getGroupIcon(): string
    {
        return 'bi-person-fill';
    }

    protected function getModelOptions(): array
    {
        return [
            '1' => 'Logged In',
            'null' => 'Not Logged In',
        ];
    }

    protected function getConditionerCallback(): ?callable
    {
        return function () {
            Event::listen('cms.page.beforeDisplay', function () {
                $user = Auth::user() ?? new \RainLab\User\Models\User;
                $conditionersManager = ConditionersManager::instance();
                $conditionersManager->replaceConditioner($user);
            });
        };
    }
}
