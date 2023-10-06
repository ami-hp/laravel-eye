<?php

declare(strict_types=1);

namespace Ami\Eye\Exceptions;

use Exception;

class ViewRecordException extends Exception
{
    public static function cannotRecordViewForViewableType()
    {
        return new static('Cannot record a view for a viewable type.');
    }
}
