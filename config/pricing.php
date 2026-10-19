<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pricing Plans
    |--------------------------------------------------------------------------
    |
    | Edit prices, bullets, and CTAs here without touching the pricing view.
    |
    */

    'plans' => [
        [
            'slug' => 'free',
            'label' => 'Free user',
            'name' => 'Vastu Bigyan',
            'price' => 0,
            'original_price' => null,
            'price_note' => null,
            'badge' => null,
            'featured' => false,
            'cta_label' => 'Get the free app',
            'cta_url' => '#',
            'features' => [
                'Vastu Bigyan mobile Compass on Play Store',
                '16 Zone Vastu Compass only display',
                'Vastu analysis for rooms and objects by using Vastu Bigyan Compass',
                'Lo-Shu Grid',
                'Select and pin on all zones activity like Toilet, Kitchen, Bedroom etc. and all objects like AC, Fridge and Washing Machine, and get report',
                'Residential approach: Vedic, Scientific and Advance Vastu',
                'Language: English and Nepali',
                'Save your working hours',
                'Useful for Vastu learners and professionals',
                'Software will run online; internet will be required at the time of activation and surrender of license',
                'Get Vastu calculator for room Vastu and object Vastu',
            ],
        ],
        [
            'slug' => 'premium-app',
            'label' => 'One time',
            'name' => 'Premium App',
            'price' => 1100,
            'original_price' => 2500,
            'price_note' => 'One time, 1 report for first 40 users',
            'badge' => null,
            'featured' => false,
            'cta_label' => 'Buy now',
            'cta_url' => '#',
            'features' => [
                '4/8/16/32 Zone Vastu Compass',
                'Vastu analysis for rooms and objects by using Vastu Bigyan Compass',
                'Lo-Shu Grid',
                '9 Planet and 12 Rashi Vastu Compass',
                'Import your map in software (JPG/PNG file)',
                'Color Vastu Compass for color selection',
                "Pin your map's zone activities like Toilet, Kitchen, Bedroom etc. and all objects like AC, Fridge and Washing Machine, and get report",
                'Residential approach: Vedic, Scientific and Advance Vastu',
                'Save your working hours',
                'Useful for Vastu learners and professionals',
                'Software will run online; internet will be required at the time of analysis',
                'Get detailed Vastu report for one time',
                'Application will run only 1 day for Special Feature on one system, then you can upgrade to a higher package',
            ],
        ],
        [
            'slug' => 'web-app-premium',
            'label' => 'Per year',
            'name' => 'Web + App Premium',
            'price' => 29400,
            'original_price' => 49000,
            'price_note' => 'Per year, 25 reports for first few users',
            'badge' => 'BEST OFFER',
            'featured' => true,
            'cta_label' => 'Buy now',
            'cta_url' => '#',
            'features' => [
                '4/8/16/32 Zone Vastu Compass',
                'Room and Object Vastu analysis by using Vastu Bigyan Compass, i.e. Bedroom, Kitchen, Toilet, TV, Tank etc.',
                '9 Planet and 12 Rashi Vastu Compass',
                'Color Vastu Compass',
                'Lo-Shu Grid',
                'Ayadi Vastu Analysis',
                'Bar Chart Analysis',
                'Rahu (Naag) Analysis or Shilanyas Point',
                '45 Devatas (Energy Field) Details',
                'Advanced Remedies of 45 Devatas',
                '45 Energy Field Attributes for Business/Factory Vastu',
                "Pin your map's zone activities like Toilet, Kitchen, Bedroom etc. and all objects like AC, Fridge, Washing Machine etc.",
                'Import your map in software (JPG/PNG file)',
                'Residential approach: Vedic, Scientific and Advance Vastu',
                'Save your working hours',
                'Useful for Vastu basic learners to advanced Vastu professionals (Vastu Guru Level)',
                'Software will run online; internet will be required',
                'Get advance Vastu report in one click',
            ],
        ],
    ],

];
