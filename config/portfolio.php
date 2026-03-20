<?php

return [
    'contact_recipient_name' => env('PORTFOLIO_CONTACT_RECIPIENT_NAME', env('MAIL_FROM_NAME')),
    'contact_recipient_address' => env('PORTFOLIO_CONTACT_RECIPIENT_ADDRESS', env('MAIL_FROM_ADDRESS')),
];
