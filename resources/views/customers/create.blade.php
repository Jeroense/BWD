@extends('layouts.app')
@section('pageTitle', 'Nieuwe Klant Aanmaken')
@section('content')
<div class="container column is-6 pull-left">
    <div class="columns m-t-5">
        <div class="column">
            <form id="customerForm" action="{{route('customers.store')}}" method="POST">
                {{csrf_field()}}
                <div class="field">
                    <label for="firstName" class="label m-b-0">Voornaam</label>
                    <p class="control">
                        <input type="text" data-pristine="true" class="input formInput" name="firstName" id="firstName">
                    </p>
                </div>
                <div class="field">
                    <label for="lnPrefix" class="label m-b-0">Tussenvoegsel</label>
                    <p class="control">
                        <input type="text" data-pristine="true" class="input formInput" name="lnPrefix" id="lnPrefix">
                    </p>
                </div>
                <div class="field">
                    <label for="lastName" class="label m-b-0">Achternaam</label>
                    <p class="control">
                        <input type="text" data-pristine="true" class="input formInput" name="lastName" id="lastName">
                    </p>
                </div>
                <div class="field">
                    <label for="street" class="label m-b-0">Straat</label>
                    <p class="control">
                        <input type="text" data-pristine="true" class="input formInput" name="street" id="street">
                    </p>
                </div>
                <div class="field">
                    <label for="houseNr" class="label m-b-0">Huisnr.</label>
                    <p class="control">
                        <input type="text" data-pristine="true" class="input formInput" name="houseNr" id="houseNr">
                    </p>
                </div>
                <div class="field">
                    <label for="postalCode" class="label m-b-0">Postcode</label>
                    <p class="control">
                        <input type="text" data-pristine="true" class="input formInput" name="postalCode" id="postalCode">
                    </p>
                </div>
                <div class="field">
                    <label for="city" class="label m-b-0">Plaats</label>
                    <p class="control">
                        <input type="text" data-pristine="true" class="input formInput" name="city" id="city">
                    </p>
                </div>
                <div class="field">
                    <label for="provinceCode" class="label m-b-0">Provincie</label>
                    <p class="control">
                        <select class="input formInput select" data-pristine="true" name="provinceCode" id="provinceCode">
                            <option value=""></option>
                            <option value="GD">Gelderland</option>
                            <option value="ZD">Zeeland</option>
                        </select>
                    </p>
                </div>
                <div class="field">
                    {{-- <label for="countryCode" class="label m-b-0">Land</label>
                    <p class="control">
                        <input type="text" class="input formInput" name="countryCode" id="countryCode">
                    </p>
                </div> --}}
                <div class="field">
                    <label for="phone" class="label m-b-0">Telefoon</label>
                    <p class="control">
                        <input type="text" data-pristine="true" class="input formInput" name="phone" id="phone">
                    </p>
                </div>

                <div class="field">
                    <label for="email" class="label m-b-0">Email</label>
                    <p class="control">
                        <input type="text" data-pristine="true" class="input formInput" name="email" id="email">
                    </p>
                </div>
                <button class="button is-danger" id="save" disabled>Opslaan</button>
            </form>
        </div>
    </div>

</div>
@endsection
@section('scripts')
    <script src="{{ asset('js/createCustomerValidation.js') }}"></script>
@endsection
