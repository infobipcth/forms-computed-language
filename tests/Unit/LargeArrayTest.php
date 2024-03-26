<?php

test('Test that looping through large dataset works', function ($arrayData) {
    $code = <<<'CODE'
    if($region == 'Europe' && $numberOfMessages > 1000) {
        $result = 'test';
    }
   
    foreach($SS_USD as $key => $value) {
        if($key == 'Price' && $value < 0.3 && $numberOfSessions > 200) {
            $arrayOut = 'array checking works!';
        } else {
            $arrayOut = 'array checking works! But with a different result';
        }
    }
CODE;
    $this->languageRunner->setCode($code);
    $this->languageRunner->setVars($arrayData);
    $this->languageRunner->evaluate();

    expect($this->languageRunner->getVars())->toBe([]);
})->with('largeArray');
