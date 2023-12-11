@component('mail::message')
    <ul style="list-style:none">
        <li><strong>Name : </strong>{{ $data->user->name}}</li>
        <li><strong>Email : </strong>{{ $data->user->email}}</li>
        <li><strong>Password : </strong>123456</li>
        <li><strong>Merchant Name : </strong>{{$data->merchant->display_name}}</li>
        <li><strong>Commission Rate : </strong>{{$data->commission_rate}}</li>
        <li>Please contact with our administrative team if you have query! Thanks</li>
    </ul>

<!-- Thanks,<br>
{{ config('app.name') }} -->
@endcomponent
