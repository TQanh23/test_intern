<?php

namespace App\Events;

use App\Models\Attempt;
use Illuminate\Foundation\Events\Dispatchable;

class AttemptSubmitted
{
    use Dispatchable;

    public function __construct(public Attempt $attempt) {}
}
