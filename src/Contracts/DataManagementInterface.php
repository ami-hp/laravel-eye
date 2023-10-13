<?php

namespace Ami\Eye\Contracts;

use Illuminate\Database\Eloquent\Model;

interface DataManagementInterface
{
    public function once();
    public function record(?Model $visitable = null , ?Model $visitor = null, bool $once = false);
    public function get();
}