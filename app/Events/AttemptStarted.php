<?php

namespace App\Events;

use App\Models\Attempt;
use Illuminate\Foundation\Events\Dispatchable;

class AttemptStarted
{
    use Dispatchable;

    public function __construct(public Attempt $attempt) {}
}
