<?php

namespace glasswalllab\dearconnector;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Glasswalllab\WiiseConnector\Skeleton\SkeletonClass
 */
class DearConnectorFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'dearonnector';
    }
}
