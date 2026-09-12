<?php

arch('el dominio Inventory nunca depende de Orders')
    ->expect('App\Domain\Inventory')
    ->not->toUse('App\Domain\Orders');

arch('el dominio Orders nunca depende de Billing (Billing escucha a Orders, no al revés)')
    ->expect('App\Domain\Orders')
    ->not->toUse('App\Domain\Billing');

arch('las Actions no deben ser llamadas directamente desde el dominio, solo desde Application')
    ->expect('App\Domain')
    ->not->toUse('App\Application');

arch('los modelos de dominio no deben usar facades de HTTP/Request directamente')
    ->expect('App\Domain')
    ->classes()
    ->not->toUse(['Illuminate\Http\Request']);

arch('no se debe usar dd() ni dump() en el código de producción')
    ->expect('App')
    ->not->toUse(['dd', 'dump', 'ray']);

arch('las Actions de Orders deben tener un método execute')
    ->expect('App\Domain\Orders\Actions')
    ->toHaveMethod('execute');

arch('las Actions de Inventory deben tener un método execute')
    ->expect('App\Domain\Inventory\Actions')
    ->toHaveMethod('execute');

arch('las excepciones de dominio de Orders son Throwable')
    ->expect('App\Domain\Orders\Exceptions')
    ->toExtend('Exception');

arch('las excepciones de dominio de Inventory son Throwable')
    ->expect('App\Domain\Inventory\Exceptions')
    ->toExtend('Exception');