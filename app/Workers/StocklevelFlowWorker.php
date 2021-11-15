<?php

namespace App\Workers;

class StocklevelFlowWorker
{
    public function __construct()
    {
    }

    public function execute() {
        $directoApiWorker = new StocklevelApiWorker();
        $prestaApiWorker = new StockApiWorker();
        $logApiWorker = new LogApiWorker();

        $stockLevelData = $directoApiWorker->apiPost();

        $result = $prestaApiWorker->body($stockLevelData)->apiPost();
        $logResult = $logApiWorker->body(['log' => 'stock levels update'])->apiPost();

        return $result;
    }
}
