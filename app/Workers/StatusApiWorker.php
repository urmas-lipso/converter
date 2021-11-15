<?php

namespace App\Workers;

use Illuminate\Support\Facades\Http;

class StatusApiWorker extends GenericApiWorker
{
    protected $resource = "order";
}
