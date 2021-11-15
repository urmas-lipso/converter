<?php

namespace App\Workers;

use Illuminate\Support\Facades\Http;

class InvoiceApiWorker extends DirectoApiWorker
{
    protected $resource = "invoice";

    public function compileBody() {
        $body = [
            'what' => $this->resource,
            'get' => 'put',
            'key' => $this->getApiKey(),
            'xmldata' => XmlProcessWorker::processInvoice($this->data)
        ];
        return $body;
    }

}
