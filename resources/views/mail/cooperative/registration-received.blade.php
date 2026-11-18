<x-mail::message>
# Registration received

Hi {{ $cooperative->leader_first_name }},

Thank you for registering **{{ $cooperative->name }}**. Your mobile number has been verified and your registration is now **pending approval**.

<x-mail::panel>
**Cooperative:** {{ $cooperative->name }}<br>
**CDA Registration No.:** {{ $cooperative->cda_registration_number }}<br>
**Admin login email:** {{ $cooperative->leader_email }}<br>
**Status:** Pending approval
</x-mail::panel>

Please wait for another email once our team has reviewed your documents. You will be able to log in to the web portal after your cooperative is approved.

If you did not make this registration, you can ignore this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
