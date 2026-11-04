<?php

namespace App\Events;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

class DocumentPreviewed
{
    use Dispatchable;

    /**
     * $document đã load sẵn unit.package để listener lấy tên gói mà không lazy load.
     */
    public function __construct(public Document $document, public User $user, public ?string $ip) {}
}
