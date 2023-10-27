<?php

declare(strict_types=1);

/**
 * ================================================
 * ================             ===================
 * URL Validation
 * ================             ===================
 * ================================================
 */

namespace celionatti\Bolt\BoltValidation;

class UrlValidation extends BoltValidator
{
    public function runValidation()
    {
        $url = $this->_obj->{$this->field};
        $pass = true;
        if (!empty($url)) {
            $pass = filter_var($url, FILTER_VALIDATE_URL);
        }
        return $pass;
    }
}
