@component('mail::message')
# New Transaction- Claims

@component('mail::panel')
<p>Customer Name: {{$customer}}</p>
<p>Phone Num: {{$tel}}</p>
<p>Amount Purchased:  ₦{{$amount}}
<p>Center: {{$claim}}
<p>Point Awarded: {{$points}}
<p>Balance Points: {{$balance}}
@endcomponent

@component('mail::table')
| Name          | Tel      | Amount | Claims   |Points|Total Pts|
| :-----------: |:--------:|:------:|:-------:|:------:|:--------:|:--------:|
| {{$customer}} | {{$tel}} |₦{{$amount}}|{{$claim}}|{{$points}}|{{$balance}}|
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
