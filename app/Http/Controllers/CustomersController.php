<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomersController extends Controller
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

    public function index(){
        $customer = Customer::orderBy('id', 'DESC')->withCount()->paginate(5);

    }

    public function store(Request $request){
        $this->validate($request,[
            'firstName' => 'required',
            'lastName' => 'required',
            'phoneNum' => 'required|string',
            'address'   => 'required',
            'gender' => 'required'
        ]);

        $input =    $request->all();

        if($request->hasFile('image')){
            $fileNameExtension = $request('image')->getClientOriginalName();
            $fileExtension = $request('image')->getClientExtension();
            $fileName = pathinfo($fileNameExtension, PATHINFO_FILENAME);
            $fileNameToStore = $fileName.'.'.time().'.'.$fileExtension;
            $path = $request->file('image')->move(public_path('profile'), $fileName);
            $input['image'] = $fileNameToStore;
        } else{
            $input['image'] = 'default.jpg';
        }

    $customer = Customer::create($input);

    return response()->json(['message'=>'Customer created successfully'],200);

    }

    public function update(Request $request,Customer $customer){

        $this->validate($request,[
            'firstName' => 'required',
            'lastName' => 'required',
            'phoneNum' => 'required|string',
            'address'   => 'required',
            'gender' => 'required'
        ]);

        $input = $request->all();

        if(!empty($request->image)){
            if($request->hasFile('image')){
            $fileNameExtension = $request('image')->getClientOriginalName();
            $fileExtension = $request('image')->getClientExtension();
            $fileName = pathinfo($fileNameExtension, PATHINFO_FILENAME);
            $fileNameToStore = $fileName.'.'.time().'.'.$fileExtension;
            $path = $request->file('image')->move(public_path('profile'), $fileName);
            $input['image'] = $fileNameToStore;
            }
        }
            $customer->update($input);
            return response()->json(['message'=> 'Update successful'],200);
    }


    public function destroy(Customer $customer){

    }

}
