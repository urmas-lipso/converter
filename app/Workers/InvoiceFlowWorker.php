<?php

namespace App\Workers;

class InvoiceFlowWorker
{
    public $info = [];
    public function info($what) {
        $this->info[] = $what;
    }
    public function __construct()
    {
    }

    public function execute() {
        $directoApiWorker = new InvoiceApiWorker();
        $prestaOrderApiWorker = new OrderApiWorker();
        $prestaListApiWorker = new OrderlistApiWorker();
        $prestaStatusApiWorker = new StatusApiWorker();
        $logApiWorker = new LogApiWorker();

        $logResult = $logApiWorker->body(['log' => 'starting to send invoices'])->apiPost();
        $orderListResult = $prestaListApiWorker->apiGet();
        $orderList = $orderListResult["data"];
        $invoiceNumber = $orderListResult["lastNumber"] > 10000 ? $orderListResult["lastNumber"] + 1 : 100001;
        foreach ($orderList as $order) {
            $this->info('processing order: '.$order["id_order"].' '.$order["status"]);
            $orderDataResult = $prestaOrderApiWorker->id($order["id_order"])->apiGet();
            $orderData = $orderDataResult["data"];
            $orderData["invoice"]["ext_unique_id"] = $invoiceNumber;
            $orderData["invoice"]["number"] = $invoiceNumber;
            $sendResult = $directoApiWorker->data($orderData)->apiPost();
            $prestaStatusApiWorker->id($order["id_order"])->body(['status' => 'sent', 'number' => $invoiceNumber])->apiPost();
            $this->info('order sent with number: '.$invoiceNumber);
            $invoiceNumber++;
            $logResult = $logApiWorker->body(['log' => 'sent order: '.$order["id_order"].' with number '.$invoiceNumber])->apiPost();
        }
        $logResult = $logApiWorker->body(['log' => 'done sending invoices'])->apiPost();

        return $this->info;
    }
}
