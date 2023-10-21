<?php

declare(strict_types=1);

namespace Ami\Eye\Observers;

use Exception;
use Illuminate\Database\Eloquent\Model;

class VisitableObserver
{
    /**
     * Handle the deleted event for the viewable model.
     *
     * @param Model $visitable
     * @return void
     * @throws Exception
     */
    public function deleted(Model $visitable)
    {
        if ($visitable->isForceDeleting())
            eye($visitable)->delete();
    }

}
