<?php

namespace App\Http\Controllers;

use App\Workers\StocklevelFlowWorker;
use Illuminate\Http\Request;

class WorkerController extends Controller
{
    public function stockLevel() {
        $worker = new StocklevelFlowWorker();
        $result = $worker->execute();

        return response()->json([
            'name' => 'stocklevel',
            'result' => $result
        ]);

    }

}
