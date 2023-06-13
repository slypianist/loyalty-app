@component('mail::message')
# New Transaction

Loyalty Points Have been awarded to:

@component('mail::panel')
<p>Customer Name: {{$customer}}</p>
<p>Phone Num: {{$tel}}</p>
<p>Amount Purchased:  ₦{{$amount}}</p>
<p>Center: {{$center}}</p>
<p>Point Awarded: {{$points}}</p>
<p>Total Accrued: {{$totalPoints}}</p>
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
