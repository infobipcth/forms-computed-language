<?php

namespace Tests\Unit;

test('Test that looping through large dataset works', function ($arrayData) {
    $code = <<<'CODE'
    if($region == 'Europe' && $numberOfMessages > 1000) {
        $result = 'test';
    }
   
    foreach($SS_USD as $key => $value) {
        if($value['Price'] < 0.3 && $numberOfSessions > 200) {
            $arrayOut = 'array checking works!';
        } else {
            $arrayOut = 'array checking works! But with a different result';
        }
    }
CODE;
    $this->languageRunner->setCode($code);
    $this->languageRunner->setVars($arrayData);
    $this->languageRunner->evaluate();

    expect($this->languageRunner->getVars())->not->toBeEmpty();
    expect($this->languageRunner->getVars())->toHaveKeys(['region', 'country', 'Number of messages', 'Traffic time', 'Number of sessions', 'es-form-skipped', 'SS_USD', 'SS_EUR', 'RawPricesSMSPrices']);
})->with('largeArray');
