<?php

namespace App\Http\Controllers;

use App\Customer;
use App\PostAddress;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $customers = Customer::all();
        return view('customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('customers.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'firstName' => 'required|max:255',
            'lastName' => 'required|max:255',
            'street' => 'required|max:255',
            'houseNr' => 'required|max:255',
            'postalCode' => 'required|max:255',
            'city' => 'required|max:255',
            'provinceCode' => 'required|max:255',
            'phone' => 'required|max:255',
            'email' => 'required|email'
        ]);

        $customer = new Customer();
        $customer->firstName = ucwords($request->firstName);
        $customer->lnPrefix = $request->lnPrefix;
        $customer->lastName = ucwords($request->lastName);
        $customer->street = $request->street;
        $customer->houseNr = $request->houseNr;
        $customer->houseNrPostfix = $request->houseNrPostfix;
        $customer->postalCode = strtoupper(substr_replace(str_replace(" ", "",$request->postalCode), " ", 4, 0));
        $customer->city = $request->city;
        $customer->provinceCode = $request->provinceCode;
        $customer->countryCode = env('COUNTRYCODE', '');
        $customer->phone = $request->phone;
        $customer->email = $request->email;
        $customer->hasDeliveryAddress = $request->deliveryAddressSelected == "true" ? 1 : 0;
        $customer->hasBillingAddress = $request->hasBillingAddress;

        if ($customer->save()) {
            if ($request->deliveryAddressSelected == true) {
                $this->validate($request, [
                    'post_firstName' => 'required|max:255',
                    'post_lastName' => 'required|max:255',
                    'post_street' => 'required|max:255',
                    'post_houseNr' => 'required|max:255',
                    'post_postalCode' => 'required|max:255',
                    'post_city' => 'required|max:255',
                    'post_provinceCode' => 'required|max:255',
                    'post_phone' => 'required|max:255',
                    'post_email' => 'required|email'
                ]);

                $postAddress = new PostAddress();
                $postAddress->customerId = $customer->id;
                $postAddress->firstName = ucwords($request->post_firstName);
                $postAddress->lnPrefix = $request->post_lnPrefix;
                $postAddress->lastName = ucwords($request->post_lastName);
                $postAddress->street = $request->post_street;
                $postAddress->houseNr = $request->post_houseNr;
                $postAddress->houseNrPostfix = $request->post_houseNrPostfix;
                $postAddress->postalCode = strtoupper(substr_replace(str_replace(" ", "",$request->post_postalCode), " ", 4, 0));
                $postAddress->city = $request->post_city;
                $postAddress->provinceCode = $request->post_provinceCode;
                $postAddress->countryCode = env('COUNTRYCODE', '');
                $postAddress->phone = $request->post_phone;
                $postAddress->email = $request->post_email;

                if ($postAddress->save()) {
                    return redirect()->route('customers.index', $customer->id);
                } else {
                    Session::flash('danger', 'Sorry, er is iets mis gegaan bij het aanmaken van deze klant.');
                    return redirect()->route('customers.index');
                }
            }
            return redirect()->route('customers.index', $customer->id);
        } else {
            Session::flash('danger', 'Sorry, er is iets mis gegaan bij het aanmaken van deze klant.');
            return redirect()->route('customers.index');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function show(Customer $customer)
    {
        return view('customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    // public function edit(Customer $customer)
    public function edit($id)
    {
        $customer = Customer::with('PostAddress')->find($id);
        $customer->hasDeliveryAddress == 1 ? $customer->hasDeliveryAddress = "true" : $customer->hasDeliveryAddress = "false";
        return view('customers.edit', compact('customer'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // dd($request);
        $this->validate($request, [
            'firstName' => 'required|max:255',
            'lastName' => 'required|max:255',
            'street' => 'required|max:255',
            'houseNr' => 'required|max:255',
            'postalCode' => 'required|max:255',
            'city' => 'required|max:255',
            'provinceCode' => 'required|max:255',
            'phone' => 'required|max:255',
            'email' => 'required|email'
        ]);

        $customer = Customer::findOrFail($id);
        $customer->firstName = ucwords($request->firstName);
        $customer->lnPrefix = $request->lnPrefix;
        $customer->lastName = ucwords($request->lastName);
        $customer->street = $request->street;
        $customer->houseNr = $request->houseNr;
        $customer->houseNrPostfix = $request->houseNrPostfix;
        $customer->postalCode = strtoupper(substr_replace(str_replace(" ", "",$request->postalCode), " ", 4, 0));
        $customer->city = $request->city;
        $customer->provinceCode = $request->provinceCode;
        $customer->countryCode = env('COUNTRYCODE', '');
        $customer->phone = $request->phone;
        $customer->email = $request->email;
        $customer->hasDeliveryAddress = $request->deliveryAddressSelected == "true" ? 1 : 0;
        $customer->hasBillingAddress = $request->hasBillingAddress;

        if ($customer->save()) {
            if ($request->deliveryAddressSelected == true) {
                $this->validate($request, [
                    'post_firstName' => 'required|max:255',
                    'post_lastName' => 'required|max:255',
                    'post_street' => 'required|max:255',
                    'post_houseNr' => 'required|max:255',
                    'post_postalCode' => 'required|max:255',
                    'post_city' => 'required|max:255',
                    'post_provinceCode' => 'required|max:255',
                    'post_phone' => 'required|max:255',
                    'post_email' => 'required|email'
                ]);

                $postAddress = PostAddress::firstOrNew(['customerId' => $id ]);
                // dd($posAddress);
                $postAddress->customerId = $customer->id;
                $postAddress->firstName = ucwords($request->post_firstName);
                $postAddress->lnPrefix = $request->post_lnPrefix;
                $postAddress->lastName = ucwords($request->post_lastName);
                $postAddress->street = $request->post_street;
                $postAddress->houseNr = $request->post_houseNr;
                $postAddress->houseNrPostfix = $request->post_houseNrPostfix;
                $postAddress->postalCode = strtoupper(substr_replace(str_replace(" ", "",$request->post_postalCode), " ", 4, 0));
                $postAddress->city = $request->post_city;
                $postAddress->provinceCode = $request->post_provinceCode;
                $postAddress->countryCode = env('COUNTRYCODE', '');
                $postAddress->phone = $request->post_phone;
                $postAddress->email = $request->post_email;

                if ($postAddress->save()) {
                    return redirect()->route('customers.index', $customer->id);
                } else {
                    Session::flash('danger', 'Sorry, er is iets mis gegaan bij het wijzigen van deze klant.');
                    return redirect()->route('customers.index');
                }
            }
            return redirect()->route('customers.index', $customer->id);
        } else {
            Session::flash('danger', 'Sorry, er is iets mis gegaan bij het wijzigen van deze klant.');
            return redirect()->route('customers.index');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function destroy(Customer $customer)
    {
        return 'klant verwijderen';
    }
}
