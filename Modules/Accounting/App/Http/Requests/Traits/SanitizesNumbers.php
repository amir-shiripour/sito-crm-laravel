<?php

namespace Modules\Accounting\App\Http\Requests\Traits;

trait SanitizesNumbers
{
    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->sanitize();
    }

    /**
     * Sanitize the input data by converting Persian/Arabic numbers to English.
     */
    public function sanitize()
    {
        $input = $this->all();
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        array_walk_recursive($input, function (&$value) use ($persian, $arabic, $english) {
            if (is_string($value)) {
                // First, convert Arabic numerals, then Persian numerals
                $value = str_replace($arabic, $english, $value);
                $value = str_replace($persian, $english, $value);
            }
        });

        $this->replace($input);
    }
}
