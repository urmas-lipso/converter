<?php

namespace App\Workers;

use Illuminate\Support\Facades\Http;

class DirectoApiWorker
{
    protected $resource = "";
    protected $id = null;
    protected $data = [];

    public function __construct($data = null, $id = null, $resource = null)
    {
        if (!is_null($data)) {
            $this->data = $data;
        }
        if (!is_null($resource)) {
            $this->resource = $resource;
        }
        if (!is_null($id)) {
            $this->id = $id;
        }
    }

    public function getApiKey() {
        return env('DIRECTO_KEY');
    }
    public function getApiUrl() {
        return env('DIRECTO_URL');
    }

    public function data($data) {
        $this->data = $data;
        return $this;
    }

    public function id($id) {
        $this->id = $id;
        return $this;
    }

    public function compileBody() {
        $body = [];
        return $body;
    }

    public function processResponse($response) {
        return $response->body();
    }
    public function apiPost() {
        $url = $this->getApiUrl();
        $body = $this->compileBody();
        $response = Http::asForm()->post($url, $body);
        return $this->processResponse($response);
    }
}
