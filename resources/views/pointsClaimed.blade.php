@component('mail::message')
# New Transaction

Loyalty Points Have been awarded to:

@component('mail::panel')
<p>Customer Name: {{$customer}}</p>
<p>Phone Num: {{$tel}}</p>
<p>Amount Purchased:  ₦{{$amount}}
<p>Point Claimed: {{$points}}
<p>Total Accrued: {{$totalPoints}}
@endcomponent

@component('mail::table')
| Name          | Tel      | Amount | Center   |Points|Total Pts|
| :-----------: |:--------:|:------:|:-------:|:------:|:--------:|:--------:|
| {{$customer}} | {{$tel}} |₦{{$amount}}|{{$center}}|{{$points}}|{{$totalPoints}}|
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
