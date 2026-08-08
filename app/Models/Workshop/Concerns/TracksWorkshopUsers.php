<?php

namespace App\Models\Workshop\Concerns;

trait TracksWorkshopUsers
{
    protected static function bootTracksWorkshopUsers(): void
    {
        static::creating(function ($model) {
            if (! $model->created_by) {
                $model->created_by = self::currentWorkshopUserId();
            }

            if ($model->updated_by === null) {
                $model->updated_by = 0;
            }
        });

        static::updating(function ($model) {
            $model->updated_by = self::currentWorkshopUserId();
        });
    }

    private static function currentWorkshopUserId(): int
    {
        return (int) (session('user_id') ?: auth()->id() ?: 1);
    }
}
