<?php

declare(strict_types=1);

namespace On1kel\OAS\Profile31\Validation;

use On1kel\OAS\Core\Contract\Profile\SpecProfile;
use On1kel\OAS\Core\Contract\Validation\Validator;
use On1kel\OAS\Core\Validation\CompositeValidator;
use On1kel\OAS\Core\Validation\DefaultRules;

final class Profile31ValidatorFactory
{
    public function create(SpecProfile $profile): Validator
    {
        $rules = array_merge(
            DefaultRules::common(),
            $profile->extraValidators() // важное место подключения профильных правил
        );

        return new CompositeValidator($rules);
    }
}
