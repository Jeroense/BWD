@extends('layouts.app')
@section('pageTitle', 'Klant Wijzigen')
@section('content')
<div class="container column is-6 pull-left">
    <div class="columns m-t-5">
        <div class="column">
            <form id="customerForm" action="{{ route('customers.update', $customer->id) }}" method="POST">
                {{method_field('PUT')}}
                {{csrf_field()}}
                <p class="is-size-5 title has-text-weight-bold">Postadres</p>
                <input type="hidden" value="{{ $customer->hasDeliveryAddress }}" name="deliveryAddressSelected" id="deliveryAddressSelected">
                <div class="field">
                    <label for="firstName" class="label m-b-0">Voornaam</label>
                    <p class="control">
                        <input type="text" data-pristine="true" class="input formInput address" name="firstName" id="firstName"
                        value="{{ $customer->firstName }}">
                    </p>
                </div>
                <div class="field">
                    <label for="lnPrefix" class="label m-b-0">Tussenvoegsel</label>
                    <p class="control">
                        <input type="text" data-pristine="true" class="input formInput address" name="lnPrefix" id="lnPrefix"
                        value="{{ $customer->lnPrefix }}">
                    </p>
                </div>
                <div class="field">
                    <label for="lastName" class="label m-b-0">Achternaam</label>
                    <p class="control">
                        <input type="text" data-pristine="true" class="input formInput address" name="lastName" id="lastName"
                        value="{{ $customer->lastName }}">
                    </p>
                </div>
                <div class="field">
                    <label for="street" class="label m-b-0">Straat</label>
                    <p class="control">
                        <input type="text" data-pristine="true" class="input formInput address" name="street" id="street"
                        value="{{ $customer->street }}">
                    </p>
                </div>
                <div class="field">
                    <label for="houseNr" class="label m-b-0">Huisnr.</label>
                    <p class="control">
                        <input type="text" data-pristine="true" class="input formInput address" name="houseNr" id="houseNr"
                        value="{{ $customer->houseNr }}">
                    </p>
                </div>
                <div class="field">
                    <label for="postalCode" class="label m-b-0">Postcode</label>
                    <p class="control">
                        <input type="text" data-pristine="true" class="input formInput address" name="postalCode" id="postalCode"
                        value="{{ $customer->postalCode }}">
                    </p>
                </div>
                <div class="field">
                    <label for="city" class="label m-b-0">Plaats</label>
                    <p class="control">
                        <input type="text" data-pristine="true" class="input formInput address" name="city" id="city"
                        value="{{ $customer->city }}">
                    </p>
                </div>
                <div class="field">
                    <label for="provinceCode" class="label m-b-0">Provincie</label>
                    <p class="control">
                        <select class="input formInput select address" data-pristine="false" name="provinceCode" id="provinceCode"
                        data-province="{{ $customer->provinceCode }}">
                            <option value="DR">Drenthe</option>
                            <option value="FL">Flevoland</option>
                            <option value="FR">Friesland</option>
                            <option value="GD">Gelderland</option>
                            <option value="GR">Groningen</option>
                            <option value="LB">Limburg</option>
                            <option value="NB">Noord-Brabant</option>
                            <option value="NH">Noord-Holland</option>
                            <option value="OV">Overijssel</option>
                            <option value="UT">Utrecht</option>
                            <option value="ZH">Zuid-Holland</option>
                            <option value="ZL">Zeeland</option>
                        </select>
                    </p>
                </div>
                <div class="field">
                    <label for="phone" class="label m-b-0">Telefoon</label>
                    <p class="control">
                        <input type="text" data-pristine="true" class="input formInput address" name="phone" id="phone"
                        value="{{ $customer->phone }}">
                    </p>
                </div>

                <div class="field">
                    <label for="email" class="label m-b-0">Email</label>
                    <p class="control">
                        <input type="text" data-pristine="true" class="input formInput address" name="email" id="email"
                        value="{{ $customer->email }}">
                    </p>
                </div>
                @if(!empty($customer->PostAddress))
                    <div id="deliveryAddress">
                        <br>
                        <p class="is-size-5 title has-text-weight-bold">Afleveradres</p>
                        <input type="checkbox"  class="checkbox hideDetail" value="false" name="hasDeliveryAddress" id="hasDeliveryAddress">
                        <div class="field">
                            <label for="post_firstName" class="label m-b-0">Voornaam</label>
                            <p class="control">
                                <input type="text" data-pristine="false" class="input formInput delivery" name="post_firstName" id="post_firstName"
                                value="{{ $customer->PostAddress->firstName }}">
                            </p>
                        </div>
                        <div class="field">
                            <label for="post_lnPrefix" class="label m-b-0">Tussenvoegsel</label>
                            <p class="control">
                                <input type="text" data-pristine="true" class="input formInput" name="post_lnPrefix" id="post_lnPrefix"
                                value="{{ $customer->PostAddress->lnPrefix }}">
                            </p>
                        </div>
                        <div class="field">
                            <label for="post_lastName" class="label m-b-0">Achternaam</label>
                            <p class="control">
                                <input type="text" data-pristine="true" class="input formInput delivery" name="post_lastName" id="post_lastName"
                                value="{{ $customer->PostAddress->lastName }}">
                            </p>
                        </div>
                        <div class="field">
                            <label for="post_street" class="label m-b-0">Straat</label>
                            <p class="control">
                                <input type="text" data-pristine="true" class="input formInput delivery" name="post_street" id="post_street"
                                value="{{ $customer->PostAddress->street }}">
                            </p>
                        </div>
                        <div class="field">
                            <label for="post_houseNr" class="label m-b-0">Huisnr.</label>
                            <p class="control">
                                <input type="text" data-pristine="true" class="input formInput delivery" name="post_houseNr" id="post_houseNr"
                                value="{{ $customer->PostAddress->houseNr }}">
                            </p>
                        </div>
                        <div class="field">
                            <label for="post_postalCode" class="label m-b-0">Postcode</label>
                            <p class="control">
                                <input type="text" data-pristine="true" class="input formInput delivery" name="post_postalCode" id="post_postalCode"
                                value="{{ $customer->PostAddress->postalCode }}">
                            </p>
                        </div>
                        <div class="field">
                            <label for="post_city" class="label m-b-0">Plaats</label>
                            <p class="control">
                                <input type="text" data-pristine="true" class="input formInput delivery" name="post_city" id="post_city"
                                value="{{ $customer->PostAddress->firstName }}">
                            </p>
                        </div>
                        <div class="field">
                            <label for="post_provinceCode" class="label m-b-0">Provincie</label>
                            <p class="control">
                                <select class="input formInput select delivery" data-pristine="false" name="post_provinceCode" id="post_provinceCode"
                                data-province="{{ $customer->PostAddress->provinceCode }}">
                                    <option value=""></option>
                                    <option value="DR">Drenthe</option>
                                    <option value="FL">Flevoland</option>
                                    <option value="FR">Friesland</option>
                                    <option value="GD">Gelderland</option>
                                    <option value="GR">Groningen</option>
                                    <option value="LB">Limburg</option>
                                    <option value="NB">Noord-Brabant</option>
                                    <option value="NH">Noord-Holland</option>
                                    <option value="OV">Overijssel</option>
                                    <option value="UT">Utrecht</option>
                                    <option value="ZH">Zuid-Holland</option>
                                    <option value="ZL">Zeeland</option>
                                </select>
                            </p>
                        </div>
                        <div class="field">
                            <label for="post_phone" class="label m-b-0">Telefoon</label>
                            <p class="control">
                                <input type="text" data-pristine="true" class="input formInput delivery" name="post_phone" id="post_phone"
                                value="{{ $customer->PostAddress->phone }}">
                            </p>
                        </div>
                        <div class="field">
                            <label for="post_email" class="label m-b-0">Email</label>
                            <p class="control">
                                <input type="text" data-pristine="true" class="input formInput delivery" name="post_email" id="post_email"
                                value="{{ $customer->PostAddress->email }}">
                            </p>
                        </div>
                    </div>
                @else
                    <div class="field">
                    <p class="control">
                        <label>
                        <input type="checkbox" class="checkbox" value="false" name="hasDeliveryAddress" id="hasDeliveryAddress">
                        &nbsp;Afleveradres Toevoegen</label>
                    </p>
                </div>
                <div id="deliveryAddress" class="hideDetail">
                    <br>
                    <p class="is-size-5 title has-text-weight-bold">Afleveradres</p>
                    <div class="field">
                        <label for="post_firstName" class="label m-b-0">Voornaam</label>
                        <p class="control">
                            <input type="text" data-pristine="true" class="input formInput delivery" name="post_firstName" id="post_firstName">
                        </p>
                    </div>
                    <div class="field">
                        <label for="post_lnPrefix" class="label m-b-0">Tussenvoegsel</label>
                        <p class="control">
                            <input type="text" data-pristine="true" class="input formInput" name="post_lnPrefix" id="post_lnPrefix">
                        </p>
                    </div>
                    <div class="field">
                        <label for="post_lastName" class="label m-b-0">Achternaam</label>
                        <p class="control">
                            <input type="text" data-pristine="true" class="input formInput delivery" name="post_lastName" id="post_lastName">
                        </p>
                    </div>
                    <div class="field">
                        <label for="post_street" class="label m-b-0">Straat</label>
                        <p class="control">
                            <input type="text" data-pristine="true" class="input formInput delivery" name="post_street" id="post_street">
                        </p>
                    </div>
                    <div class="field">
                        <label for="post_houseNr" class="label m-b-0">Huisnr.</label>
                        <p class="control">
                            <input type="text" data-pristine="true" class="input formInput delivery" name="post_houseNr" id="post_houseNr">
                        </p>
                    </div>
                    <div class="field">
                        <label for="post_postalCode" class="label m-b-0">Postcode</label>
                        <p class="control">
                            <input type="text" data-pristine="true" class="input formInput delivery" name="post_postalCode" id="post_postalCode">
                        </p>
                    </div>
                    <div class="field">
                        <label for="post_city" class="label m-b-0">Plaats</label>
                        <p class="control">
                            <input type="text" data-pristine="false" class="input formInput delivery" name="post_city" id="post_city">
                        </p>
                    </div>
                    <div class="field">
                        <label for="post_provinceCode" class="label m-b-0">Provincie</label>
                        <p class="control">
                            <select class="input formInput select delivery" data-pristine="false" name="post_provinceCode" id="post_provinceCode">
                                <option value=""></option>
                                <option value="DR">Drenthe</option>
                                <option value="FL">Flevoland</option>
                                <option value="FR">Friesland</option>
                                <option value="GD">Gelderland</option>
                                <option value="GR">Groningen</option>
                                <option value="LB">Limburg</option>
                                <option value="NB">Noord-Brabant</option>
                                <option value="NH">Noord-Holland</option>
                                <option value="OV">Overijssel</option>
                                <option value="UT">Utrecht</option>
                                <option value="ZH">Zuid-Holland</option>
                                <option value="ZL">Zeeland</option>
                            </select>
                        </p>
                    </div>
                    <div class="field">
                        <label for="post_phone" class="label m-b-0">Telefoon</label>
                        <p class="control">
                            <input type="text" data-pristine="true" class="input formInput delivery" name="post_phone" id="post_phone">
                        </p>
                    </div>
                    <div class="field">
                        <label for="post_email" class="label m-b-0">Email</label>
                        <p class="control">
                            <input type="text" data-pristine="true" class="input formInput delivery" name="post_email" id="post_email">
                        </p>
                    </div>
                </div>
                @endif
                <button class="button is-danger m-t-10" id="save" disabled>Opslaan</button>
            </form>
        </div>
    </div>

</div>
@endsection
@section('scripts')

    <script src="{{ asset('js/createCustomerValidation.js') }}"></script>
    <script src="{{ asset('js/setSelectFieldOption.js') }}"></script>
@endsection


