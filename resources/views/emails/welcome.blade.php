@component('mail::message')

# Welcome to {{ config('app.name') }}, {{ $name }}! 🎉

We’re thrilled to have you on board. Our platform is designed to help you get the most out of your experience.
If you have any questions, feel free to reach out—we’re always happy to assist!

@component('mail::button', ['url' => '/'])
Explore Now
@endcomponent

Looking forward to your journey with us!

Best Regards,
**The {{ config('app.name') }} Team**

@endcomponent
