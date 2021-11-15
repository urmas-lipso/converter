<?php

namespace App\Workers;

use Illuminate\Support\Facades\Http;

class LogApiWorker extends GenericApiWorker
{
    protected $resource = "log";
}
