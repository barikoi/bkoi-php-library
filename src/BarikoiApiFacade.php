<?php

namespace Barikoi\BkoiPhpLibrary;

use Illuminate\Support\Facades\Facade;

class BarikoiApiFacade extends Facade {

    protected static function getFacadeAccessor() {
        return 'barikoiapi';
    }

}