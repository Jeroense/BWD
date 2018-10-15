@if(count($errors))
<div class="field">
    <article class="message is-danger">
        <div class="message-header">
            <p>Invoer Fouten gevonden!</p>
        </div>
        <div class="message-body body-border">
            <ul>
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </article>
</div>
@endif
