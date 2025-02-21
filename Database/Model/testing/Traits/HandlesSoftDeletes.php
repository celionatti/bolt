<?php

declare(strict_types=1);

/**
 * ====================================
 * Bolt - HandlesSoftDeletes ==========
 * ====================================
 */

namespace celionatti\Bolt\Database\Model\Traits;

trait HandlesSoftDeletes
{
    protected string $deletedAt = 'deleted_at';
    protected bool $softDelete = false;

    public function softDelete(): bool
    {
        $this->{$this->deletedAt} = new DateTime();
        return $this->save();
    }

    public function restore(): bool
    {
        $this->{$this->deletedAt} = null;
        return $this->save();
    }

    public function forceDelete(): bool
    {
        $this->softDelete = false;
        return $this->delete();
    }

    public function trashed(): bool
    {
        return (bool) $this->{$this->deletedAt};
    }
}