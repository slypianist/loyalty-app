@component('mail::message')
# New Transaction- Claims

@component('mail::panel')
<p>Customer Name: {{$customer}}</p>
<p>Phone Num: {{$tel}}</p>
<p>Amount Purchased:  ₦{{$amount}}
<p>Amount claimed: {{$claim}}
<p>Point Awarded: {{$points}}
<p>Balance Points: {{$balance}}
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
