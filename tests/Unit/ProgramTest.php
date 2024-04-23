<?php

namespace Tests\Unit;

test('evaluating complex program works with continue statement', function () {
    $code = <<<'CODE'
    $cost = 5000;
    $volume = 1000000;
    $country = 'Korea, the Republic of';

    $prices = [
        [
            "CountryName" => "Korea, the Republic of",
            "MinBlendedInt" => "0.0032",
            "AvailableChannels" => "SMS, WhatsApp, Kakao",
            "SMSMinRecStandardInternational" => "0.0101",
            "SavingsInternational" => "68%"
        ],
        [
            "CountryName" => "Kuwait",
            "MinBlendedInt" => "0.0301",
            "AvailableChannels" => "SMS, WhatsApp, Viber",
            "SMSMinRecStandardInternational" => "0.1470",
            "SavingsInternational" => "80%"
        ]
    ];

    $ppm = $cost / $volume;

    foreach ($prices as $priceData) {
        if ($priceData['CountryName'] !== $country) {
            continue;
        }

        $ppmInfobip = $priceData['SMSMinRecStandardInternational'];
        $availableChannels = $priceData['AvailableChannels'];
        $potentialSavings = $priceData['SavingsInternational'];
        $authenticateInternationalPrice = $priceData['MinBlendedInt'];
    }

    if ($ppm == 0) {
        $savings = $ppmInfobip;
    } else {
        $savings = $ppm;
    }

    if ($authenticateInternationalPrice/$savings - 1 > 0) {
        $savingsPercentage = 0;
    } else {
        $savingsPercentage = $authenticateInternationalPrice/$savings - 1;
    }
    CODE;
	$this->languageRunner->setCode("$code");
	$this->languageRunner->setVars([]);
	$this->languageRunner->evaluate();
	expect($this->languageRunner->getVars())->toBe([
		'cost' => 5000,
		'volume' => 1000000,
		'country' => 'Korea, the Republic of',
		'prices' => [
			['CountryName' => 'Korea, the Republic of', 'MinBlendedInt' => '0.0032', 'AvailableChannels' => 'SMS, WhatsApp, Kakao', 'SMSMinRecStandardInternational' => '0.0101', 'SavingsInternational' => '68%'], ['CountryName' => 'Kuwait', 'MinBlendedInt' => '0.0301', 'AvailableChannels' => 'SMS, WhatsApp, Viber', 'SMSMinRecStandardInternational' => '0.1470', 'SavingsInternational' => '80%']
		],
		'ppm' => 0.005,
		'ppmInfobip' => '0.0101',
		'availableChannels' => 'SMS, WhatsApp, Kakao',
		'potentialSavings' => '68%',
		'authenticateInternationalPrice' => '0.0032',
		'savings' => 0.005,
		'savingsPercentage' => -0.36
	]);
});

test('evaluating complex program works with break statement', function () {
    $code = <<<'CODE'
    $cost = 5000;
    $volume = 1000000;
    $country = 'Korea, the Republic of';

    $prices = [
        [
            "CountryName" => "Korea, the Republic of",
            "MinBlendedInt" => "0.0032",
            "AvailableChannels" => "SMS, WhatsApp, Kakao",
            "SMSMinRecStandardInternational" => "0.0101",
            "SavingsInternational" => "68%"
        ],
        [
            "CountryName" => "Kuwait",
            "MinBlendedInt" => "0.0301",
            "AvailableChannels" => "SMS, WhatsApp, Viber",
            "SMSMinRecStandardInternational" => "0.1470",
            "SavingsInternational" => "80%"
        ]
    ];

    $ppm = $cost / $volume;

    foreach ($prices as $priceData) {
        if ($priceData['CountryName'] == $country) {
            $ppmInfobip = $priceData['SMSMinRecStandardInternational'];
            $availableChannels = $priceData['AvailableChannels'];
            $potentialSavings = $priceData['SavingsInternational'];
            $authenticateInternationalPrice = $priceData['MinBlendedInt'];
            break;
        }
    }

    if ($ppm == 0) {
        $savings = $ppmInfobip;
    } else {
        $savings = $ppm;
    }

    if ($authenticateInternationalPrice/$savings - 1 > 0) {
        $savingsPercentage = 0;
    } else {
        $savingsPercentage = $authenticateInternationalPrice/$savings - 1;
    }
    CODE;
	$this->languageRunner->setCode("$code");
	$this->languageRunner->setVars([]);
	$this->languageRunner->evaluate();
	expect($this->languageRunner->getVars())->toBe([
		'cost' => 5000,
		'volume' => 1000000,
		'country' => 'Korea, the Republic of',
		'prices' => [
			['CountryName' => 'Korea, the Republic of', 'MinBlendedInt' => '0.0032', 'AvailableChannels' => 'SMS, WhatsApp, Kakao', 'SMSMinRecStandardInternational' => '0.0101', 'SavingsInternational' => '68%'], ['CountryName' => 'Kuwait', 'MinBlendedInt' => '0.0301', 'AvailableChannels' => 'SMS, WhatsApp, Viber', 'SMSMinRecStandardInternational' => '0.1470', 'SavingsInternational' => '80%']
		],
		'ppm' => 0.005,
		'ppmInfobip' => '0.0101',
		'availableChannels' => 'SMS, WhatsApp, Kakao',
		'potentialSavings' => '68%',
		'authenticateInternationalPrice' => '0.0032',
		'savings' => 0.005,
		'savingsPercentage' => -0.36
	]);
});

test('check that foreach with assignment inside the loop works with continue statement', function(){
    $code = <<<'CODE'
    $a = ['first' => 1, 'second' => 2];

    foreach ($a as $index => $value) {
        if ($index != 'first') {
            continue;
        }
        $b = $value + 3;
    }
    CODE;
    $this->languageRunner->setCode("$code");
    $this->languageRunner->setVars([]);
    $this->languageRunner->evaluate();
    expect($this->languageRunner->getVars())->toBe(['a' => ['first' => 1, 'second' => 2], 'b' => 4]);
});
