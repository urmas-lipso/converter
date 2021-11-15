<?php

namespace App\Workers;

use Illuminate\Support\Facades\Http;

class XmlProcessWorker
{
    public function __construct()
    {
    }

    public static function processInvoice($invoiceData) {
        $xml = new \DOMDocument();
        $invoice = $xml->createElement('invoice');
        $invoices = $xml->createElement('invoices');
        foreach($invoiceData["invoice"] as $fieldName => $fieldValue) {
            $invoice->setAttribute($fieldName, print_r($fieldValue, true));
        }
        $rows = $xml->createElement('rows');
        foreach($invoiceData["products"] as $product) {
            $row = $xml->createElement('row');
            foreach($product as $fieldName => $fieldValue) {
                $row->setAttribute($fieldName, print_r($fieldValue, true));
            }
            $rows->appendChild($row);
        }
        $invoice->appendChild($rows);
        $invoices->appendChild($invoice);
        $xml->appendChild($invoices);

        return $xml->saveXML();
    }

    public static function processStock($stockXml) {
        $result = ['stocklevels' => []];
        $xml = new \DOMDocument();
        $xml->loadXML($stockXml);
        $stockLevels = $xml->getElementsByTagName('stocklevel');
        foreach ($stockLevels as $stockLevel) {
            $result['stocklevels'][] = [
                'code' => $stockLevel->getAttribute('code'),
                'freequantity' => $stockLevel->getAttribute('freequantity')
            ];
        }
        return $result;
    }
}
