<?php

namespace App\Workers;

use Illuminate\Support\Facades\Http;

class StockApiWorker extends GenericApiWorker
{
    protected $resource = "stock";

    public function buildBodySingle($code, $stock) {
        $body = ["stocklevels" => [["code" => $code, "freequantity" => $stock]]];
        return $body;
    }

    public function buildBodyList($list) {
        $body = ["stocklevels" => []];
        foreach ($list as $item) {
            $body["stocklevels"][] = [
                "code" => $item["code"],
                "freequantity" => $item["stock"]
            ];
        }
        return $body;
    }
}
