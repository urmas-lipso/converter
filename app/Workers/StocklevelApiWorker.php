<?php

namespace App\Workers;

use Illuminate\Support\Facades\Http;

class StocklevelApiWorker extends DirectoApiWorker
{
    protected $resource = "stocklevel";

    public function compileBody() {
        $body = [
            'what' => $this->resource,
            'get' => '1',
            'key' => $this->getApiKey()
        ];
        return $body;
    }

    public function processResponse($response)
    {
        return XmlProcessWorker::processStock($response);
    }
}
