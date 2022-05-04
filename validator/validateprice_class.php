<?php

    class ValidatePrice extends Validator
    {
        protected function validate()
        {
            $data = $this->data;
            if (!is_numeric($data))
            {
                $this->setError(self::CODE_UNKNOWN);
            }
        }
    }