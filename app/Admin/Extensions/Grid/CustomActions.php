<?php

namespace App\Admin\Extensions\Grid;

class CustomActions extends \Encore\Admin\Grid\Displayers\Actions
{
    protected function editAction()
    {
        return <<<EOT
<a href="{$this->getResource()}/{$this->getKey()}/edit" class="btn btn-xs btn-primary mr5">
    <i class="fa fa-edit"></i>
</a>
EOT;
    }

    protected function deleteAction()
    {
        parent::deleteAction();

        return <<<EOT
<a href="javascript:void(0);" data-id="{$this->getKey()}" class="grid-row-delete btn btn-xs btn-danger">
    <i class="fa fa-trash"></i>
</a>
EOT;
    }
}

?>