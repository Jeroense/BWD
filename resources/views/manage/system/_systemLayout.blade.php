<div class="container column is-6 pull-left">
        @include('layouts.errors')
        <div class="columns m-t-20">
            <div class="column">

                    {{-- <form action="{{ route('roles.update', $role->id) }}" method="POST">
                            {{ csrf_field() }}
                            {{ method_field('PUT') }} --}}

                @if( Nav::isRoute('system.edit'))
                    <form action="{{ route('system.update', $sysInfo->id)}}" method="POST">
                    {{csrf_field()}}
                    {{method_field('PUT')}}
                @else
                    <form action="{{route('system.store')}}" method="POST">
                    {{csrf_field()}}
                @endif
                    <div class="field">
                        <label for="organizationName" class="formlabel">Naam Organisatie</label>
                        <p class="control">
                        <input type="text" class="input" name="organizationName" id="organizationName" value='{{ $sysInfo->organizationName }}'>
                        </p>
                    </div>

                    <div class="field">
                        <label for="street" class="formlabel">Straat</label>
                        <p class="control">
                            <input type="text" class="input" name="street" id="street" value='{{ $sysInfo->street }}'>
                        </p>
                    </div>

                    <div class="field">
                        <label for="houseNr" class="formlabel">Huisnr.</label>
                        <p class="control">
                            <input type="text" class="input" name="houseNr" id="houseNr" value='{{ $sysInfo->houseNr }}'>
                        </p>
                    </div>

                    <div class="field">
                        <label for="postalCode" class="formlabel">Postcode</label>
                        <p class="control">
                            <input type="text" class="input" name="postalCode" id="postalCode" value='{{ $sysInfo->postalCode }}'>
                        </p>
                    </div>

                    <div class="field">
                        <label for="city" class="formlabel">Plaats</label>
                        <p class="control">
                            <input type="text" class="input" name="city" id="city" value='{{ $sysInfo->city }}'>
                        </p>
                    </div>

                    <div class="field">
                        <label for="email" class="formlabel">Email</label>
                        <p class="control">
                            <input type="text" class="input" name="email" id="email" value='{{ $sysInfo->email }}'>
                        </p>
                    </div>

                    <div class="field">
                        <label for="phone" class="formlabel">Telefoon</label>
                        <p class="control">
                            <input type="text" class="input" name="phone" id="phone" value='{{ $sysInfo->phone }}'>
                        </p>
                    </div>

                    <div class="field">
                        <label for="cocNr" class="formlabel">KvK</label>
                        <p class="control">
                            <input type="text" class="input" name="cocNr" id="cocNr" value='{{ $sysInfo->cocNr }}'>
                        </p>
                    </div>

                    <div class="field">
                        <label for="vatNr" class="formlabel">Btw nummer</label>
                        <p class="control">
                            <input type="text" class="input" name="vatNr" id="vatNr" value='{{ $sysInfo->vatNr }}'>
                        </p>
                    </div>

                    <div class="field">
                        <label for="appSerNr" class="formlabel">Serienummer Applicatie</label>
                        <p class="control">
                            <input type="text" class="input" name="appSerNr" id="appSerNr" value='{{ $sysInfo->appSerNr }}'>
                        </p>
                    </div>

                    <div class="field">
                        <label for="apiKeyBol" class="formlabel">Api key Bol.com</label>
                        <p class="control">
                            <textarea class="input textarea" name="apiKeyBol" id="apiKeyBol">{{ $sysInfo->apiKeyBol }}</textarea>
                        </p>
                    </div>

                    <div class="field">
                        <label for="apiKeySmake" class="formlabel">Api key Smake</label>
                        <p class="control">
                            <textarea class="input textarea" name="apiKeySmake" id="apiKeySmake">{{ $sysInfo->apiKeySmake }}</textarea>
                        </p>
                    </div>
                    <div>
                        <button class="button is-danger is-pulled-right"><i class="far fa-save p-r-10"></i>Opslaan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
