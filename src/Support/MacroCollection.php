<?php

namespace Ami\Eye\Support;


use Illuminate\Database\Eloquent\Model;

class MacroCollection
{

    public function period($macroThis , Period $period)
    {
        $startDateTime = $period->getStartDateTime();
        $endDateTime   = $period->getEndDateTime();
        $collection = $macroThis;

        if ($startDateTime !== null && $endDateTime === null) {
            $collection = $macroThis->where('viewed_at', '>=', $startDateTime);
        } elseif ($startDateTime === null && $endDateTime !== null) {
            $collection = $macroThis->where('viewed_at', '<=', $endDateTime);
        } elseif ($startDateTime !== null && $endDateTime !== null) {
            $collection = $macroThis->whereBetween('viewed_at', [$startDateTime, $endDateTime]);
        }

        return $collection;
    }

    public function whereVisitor($macroThis , Model $user)
    {
        return $macroThis->where('visitor_id' , $user->id)->where('visitor_type' , get_class($user));
    }

    public function whereVisitable($macroThis , Model $post)
    {
        return $macroThis->where('visitor_id' , $post->id)->where('visitor_type' , get_class($post));
    }
}