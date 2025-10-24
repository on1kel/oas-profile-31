<?php

declare(strict_types=1);

namespace On1kel\OAS\Profile31\Bootstrap;

use On1kel\OAS\Core\Version\ProfileRegistry;
use On1kel\OAS\Profile31\Profile\OAS31Profile;

final class ProfileRegistryFactory
{
    public static function makeDefault31(): ProfileRegistry
    {
        $registry = new ProfileRegistry(new OAS31Profile());
        $registry->setDefault('3.1');

        return $registry;
    }
}
