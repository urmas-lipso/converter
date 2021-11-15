<?php

namespace App\Http\Controllers;

use App\Workers\Json2CsvWorker;
use App\Workers\Csv2JsonWorker;
use Illuminate\Http\Request;

class WorkerController extends Controller
{
    public function json2csv(Request $request) {
        $worker = new Json2CsvWorker(['show_null_as_text' => true]);
        $worker->setData($request->post());
        $worker->processJsonData();

        return response($worker->getCsv(), 200)->header('Content-Type', 'text/plain');
    }

    public function csv2json(Request $request) {
        $worker = new Csv2JsonWorker([
            'set_null_from_text' => true,
            'set_null_to_empty' => true
        ]);

        $worker->setData($request->getContent());
        $worker->processCsvData();

        return response()->json($worker->getJsonData());
    }

}
