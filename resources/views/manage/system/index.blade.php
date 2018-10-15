@extends('layouts.app')
@section('pageTitle', 'Systeem Informatie')
@section('content')
    <div class="container column is-6 pull-left">
        <div class="card">
            <div class="card-content">
                <table class="table is-narrow">
                    <tbody>
                        <tr>
                            <td>Naam Organisatie:</td>
                            <td>{{ $sysinfo->organizationName }}</td>
                        </tr>
                        <tr>
                            <td>Straat:</td>
                            <td>{{ $sysinfo->street }}</td>
                        </tr>
                        <tr>
                            <td>Huisnr.:</td>
                            <td>{{ $sysinfo->houseNr }}</td>
                        </tr>
                        <tr>
                            <td>Postcode:</td>
                            <td>{{ $sysinfo->postalCode }}</td>
                        </tr>
                        <tr>
                            <td>Plaats:</td>
                            <td>{{ $sysinfo->city }}</td>
                        </tr>
                        <tr>
                            <td>Email:</td>
                            <td>{{ $sysinfo->email }}</td>
                        </tr>
                        <tr>
                            <td>Telefoon:</td>
                            <td>{{ $sysinfo->phone }}</td>
                        </tr>
                        <tr>
                            <td>KvK:</td>
                            <td>{{ $sysinfo->cocNr }}</td>
                        </tr>
                        <tr>
                            <td>Btw nummer:</td>
                            <td>{{ $sysinfo->vatNr }}</td>
                        </tr>
                        <tr>
                            <td>Serienummer Applicatie:</td>
                            <td>{{ $sysinfo->appSerNr }}</td>
                        </tr>
                        <tr>
                            <td>Api key Bol.com:</td>
                            <td>{{ $sysinfo->apiKeyBol }}</td>
                        </tr>
                        <tr>
                            <td>Api key Smake:</td>
                            <td>{{ $sysinfo->apiKeySmake }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    <div class="columns m-t-5">
        <div class="column">
            <a href="{{ route('system.edit', $sysinfo->id) }}" class="button is-danger is-pulled-right"><i class="far fa-edit m-r-10"></i>Systeem Informatie Wijzigen</a>
        </div>
    </div>
@endsection
