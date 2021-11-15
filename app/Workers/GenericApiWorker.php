<?php

namespace App\Workers;

use Illuminate\Support\Facades\Http;

class GenericApiWorker
{
    protected $resource = "";
    protected $id = null;
    protected $body = [];

    public function __construct($body = null, $id = null, $resource = null)
    {
        if (!is_null($body)) {
            $this->body = $body;
        }
        if (!is_null($resource)) {
            $this->resource = $resource;
        }
        if (!is_null($id)) {
            $this->id = $id;
        }
    }

    public function getApiKey() {
        return env('PRESTASHOP_KEY');
    }
    public function getApiUrl() {
        return env('PRESTASHOP_URL');
    }

    public function getBody() {
        return json_encode($this->body);
    }

    public function body($body) {
        $this->body = $body;
        return $this;
    }

    public function id($id) {
        $this->id = $id;
        return $this;
    }

    public function compileUrl() {
        $resource = $this->resource;
        $id = $this->id;
        $apiKey = $this->getApiKey();
        $apiUrl = $this->getApiUrl();
        if ($id) {
            return "https://$apiKey@$apiUrl/$resource/$id";
        } else {
            return "https://$apiKey@$apiUrl/$resource";
        }
    }

    public function apiGet() {
        $url = $this->compileUrl();
        $result = Http::withOptions(['output_format' => 'JSON'])->get($url);

        return $result->json();
    }

    public function apiPost() {
        $url = $this->compileUrl();
        $body = $this->getBody();
        $result = Http::withOptions(['output_format' => 'JSON'])->withBody($body, 'application/json')->post($url);

        $resultJson = $result->json();

        if (isset($resultJson["data"])) {
            return $resultJson["data"];
        } else {
            return $resultJson;
        }
    }
}
