@extends('layouts.app')
@section('pageTitle', 'Nieuwe Klant Aanmaken')
@section('content')
<div class="container column is-6 pull-left">
    <div class="columns m-t-5">
        <div class="column">
            <form id="customerForm" action="{{route('customers.store')}}" method="POST">
                {{csrf_field()}}
                <br>
                <p class="is-size-5 title has-text-weight-bold">Postadres</p>
                <input type="hidden" value="false" name="deliveryAddressSelected" id="deliveryAddressSelected">
                <div class="field">
                    <label for="firstName" class="label m-b-0">Voornaam</label>
                    <p class="control">
                        <input type="text" data-pristine="true" class="input formInput address" name="firstName" id="firstName">
                    </p>
                </div>
                <div class="field">
                    <label for="lnPrefix" class="label m-b-0">Tussenvoegsel</label>
                    <p class="control">
                        <input type="text" data-pristine="true" class="input formInput address" name="lnPrefix" id="lnPrefix">
                    </p>
                </div>
                <div class="field">
                    <label for="lastName" class="label m-b-0">Achternaam</label>
                    <p class="control">
                        <input type="text" data-pristine="true" class="input formInput address" name="lastName" id="lastName">
                    </p>
                </div>
                <div class="field">
                    <label for="street" class="label m-b-0">Straat</label>
                    <p class="control">
                        <input type="text" data-pristine="true" class="input formInput address" name="street" id="street">
                    </p>
                </div>
                <div class="field">
                    <label for="houseNr" class="label m-b-0">Huisnr.</label>
                    <p class="control">
                        <input type="text" data-pristine="true" class="input formInput address" name="houseNr" id="houseNr">
                    </p>
                </div>
                <div class="field">
                    <label for="postalCode" class="label m-b-0">Postcode</label>
                    <p class="control">
                        <input type="text" data-pristine="true" class="input formInput address" name="postalCode" id="postalCode">
                    </p>
                </div>
                <div class="field">
                    <label for="city" class="label m-b-0">Plaats</label>
                    <p class="control">
                        <input type="text" data-pristine="true" class="input formInput address" name="city" id="city">
                    </p>
                </div>
                <div class="field">
                    <label for="provinceCode" class="label m-b-0">Provincie</label>
                    <p class="control">
                        <select class="input formInput address" data-pristine="true" name="provinceCode" id="provinceCode">
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
                    <label for="phone" class="label m-b-0">Telefoon</label>
                    <p class="control">
                        <input type="text" data-pristine="true" class="input formInput address" name="phone" id="phone">
                    </p>
                </div>

                <div class="field">
                    <label for="email" class="label m-b-0">Email</label>
                    <p class="control">
                        <input type="text" data-pristine="true" class="input formInput address" name="email" id="email">
                    </p>
                </div>
                <div class="field">
                    <p class="control">
                        <label>
                        <input type="checkbox" class="checkbox" value="false" name="hasDeliveryAddress" id="hasDeliveryAddress">
                        &nbsp;Afleveradres is afwijkend</label>
                    </p>
                </div>
                <div id="deliveryAddress" class="hideDetail">
                    <br>
                    <p class="is-size-5 title has-text-weight-bold is-danger">Afleveradres</p>
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
                            <input type="text" data-pristine="true" class="input formInput delivery" name="post_city" id="post_city">
                        </p>
                    </div>
                    <div class="field">
                        <label for="post_provinceCode" class="label m-b-0">Provincie</label>
                        <p class="control">
                            <select class="input formInput select delivery" data-pristine="true" name="post_provinceCode" id="post_provinceCode">
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
                <button class="button is-danger m-t-10" id="save" disabled>Opslaan</button>
            </form>
        </div>
    </div>

</div>
@endsection
@section('scripts')
    <script src="{{ asset('js/createCustomerValidation.js') }}"></script>
@endsection
