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

    public function whereCollection($macroThis , string $name)
    {
        return $macroThis->where('collection' , $name);
    }

    public function whereVisitor($macroThis , Model $user)
    {
        return $macroThis->where('visitor_id' , $user->id)->where('visitor_type' , get_class($user));
    }

    public function whereVisitable($macroThis , Model $post)
    {
        return $macroThis->where('visitable_id' , $post->id)->where('visitable_type' , get_class($post));
    }

    public function whereVisitHappened($macroThis , Model $visit)
    {

        $cacheQuery = $macroThis->where('unique_id' , $visit->unique_id);

        if($visit->visitable_type !== null && $visit->visitable_id !== null)
        {
            $cacheQuery =
                $cacheQuery
                    ->where('visitable_type' , $visit->visitable_type)
                    ->where('visitable_id'   , $visit->visitable_id);
        }
        else{
            $cacheQuery = $cacheQuery->where('url' , $visit->url);
        }

        return $cacheQuery;
    }
}