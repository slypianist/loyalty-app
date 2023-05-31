<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;

class InvoiceController extends BaseController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function checkInvoice(Request $request){
        $id = $request->invoiceId;
        $compId = $request->companyId;

        $invoice = Invoice::where('invoiceCode', $id)
                            ->where('companyId', $compId)
                            ->first();
        if($invoice === NULL){
            return $this->sendResponse(200, 'successful');
        }
            return $this->sendError('Sorry, invoice already exist.');


    }

    public function getCenterInvoice(){
        $id = request('invoiceId');
        $compID = request('companyId');

        $invoice = Invoice::where('companyId', $compID)->get();

        return $this->sendResponse($invoice, 'successful');

    }
}
