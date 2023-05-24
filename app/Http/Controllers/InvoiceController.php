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

        $invoice = Invoice::where('invoiceCode', $id)->first();
        if($invoice === NULL){
            return $this->sendResponse(200, 'successful');
        }
            return $this->sendError('Sorry, invoice already exist.');


    }
}
