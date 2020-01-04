<?php

namespace App\Admin\Form;

use Encore\Admin\Form;

/**
 * Class Builder.
 */
class Builder {

    use Builder;

    /**
     * Submit button of form..
     *
     * @return string
     */
    public function submitButton() {
        echo "AA"; exit;
        if ($this->mode == self::MODE_VIEW) {
            return '';
        }

        if (!$this->options['enableSubmit']) {
            return '';
        }

        if ($this->mode == self::MODE_EDIT) {
            $text = trans('admin.save');
        } else {
            $text = trans('admin.submit');
        }

        return <<<EOT
<div class="btn-group pull-right">
    <button type="submit" class="btn btn-primary pull-right" data-loading-text="<i class='fa fa-spinner fa-spin '></i> $text">$text</button>
</div>
EOT;
    }

    /**
     * Reset button of form.
     *
     * @return string
     */
    public function resetButton() {
        if (!$this->options['enableReset']) {
            return '';
        }

        $text = trans('admin.reset');

        return <<<EOT
<div class="btn-group pull-left">
    <button type="reset" class="btn btn-warning">$text</button>
</div>
EOT;
    }

}
