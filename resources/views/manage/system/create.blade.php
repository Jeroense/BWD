@extends('layouts.manage')

@section('content')
<div class="container testleft column is-6 is-offset-4 ">
    <div class="columns m-t-5">
        <div class="column">
            <h1 class="title">Create SystemInfo</h1>
        </div>
    </div>
    <hr class="m-t-0">

    <div class="columns">
        <div class="column">
            <form action="{{route('system.store')}}" method="POST">
                {{csrf_field()}}

                <div class="field">
                    <label for="organizationName" class="formlabel">Naam Organisatie</label>
                    <p class="control">
                        <input type="text" class="input" name="organizationName" id="organizationName">
                    </p>
                </div>

                <div class="field">
                    <label for="street" class="formlabel">Straat</label>
                    <p class="control">
                        <input type="text" class="input" name="street" id="street">
                    </p>
                </div>

                <div class="field">
                    <label for="houseNr" class="formlabel">Huisnr.</label>
                    <p class="control">
                        <input type="text" class="input" name="houseNr" id="houseNr">
                    </p>
                </div>

                <div class="field">
                    <label for="postalCode" class="formlabel">Postcode</label>
                    <p class="control">
                        <input type="text" class="input" name="postalCode" id="postalCode">
                    </p>
                </div>

                <div class="field">
                    <label for="city" class="formlabel">Plaats</label>
                    <p class="control">
                        <input type="text" class="input" name="city" id="city">
                    </p>
                </div>

                <div class="field">
                    <label for="email" class="formlabel">Email</label>
                    <p class="control">
                        <input type="text" class="input" name="email" id="email">
                    </p>
                </div>

                <div class="field">
                    <label for="cocNr" class="formlabel">KvK</label>
                    <p class="control">
                        <input type="text" class="input" name="cocNr" id="cocNr">
                    </p>
                </div>

                <div class="field">
                    <label for="vatNr" class="formlabel">Btw nummer</label>
                    <p class="control">
                        <input type="text" class="input" name="vatNr" id="vatNr">
                    </p>
                </div>

                <div class="field">
                    <label for="appSerNr" class="formlabel">Serienummer Applicatie</label>
                    <p class="control">
                        <input type="text" class="input" name="appSerNr" id="appSerNr">
                    </p>
                </div>

                <div class="field">
                    <label for="apiKeyBol" class="formlabel">Api key Bol.com</label>
                    <p class="control">
                        <input type="text" class="input" name="apiKeyBol" id="apiKeyBol">
                    </p>
                </div>

                <div class="field">
                    <label for="apiKeySmake" class="formlabel">Api key Smake</label>
                    <p class="control">
                        <input type="text" class="input" name="apiKeySmake" id="apiKeySmake">
                    </p>
                </div>

                <button class="button is-success">Opslaan</button>
            </form>
        </div>
    </div>
</div>
@endsection

