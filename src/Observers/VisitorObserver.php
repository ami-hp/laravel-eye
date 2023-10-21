<?php

declare(strict_types=1);

namespace Ami\Eye\Observers;

use Exception;
use Illuminate\Database\Eloquent\Model;

class VisitorObserver
{
    /**
     * Handle the deleted event for the visitor model.
     *
     * @param Model $visitor
     * @return void
     * @throws Exception
     */
    public function deleted(Model $visitor)
    {
        if ($visitor->isForceDeleting())
            eye()->visitor($visitor)->visitable(false)->delete();
    }

}
