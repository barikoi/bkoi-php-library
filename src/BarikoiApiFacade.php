<?php

namespace Barikoi\BarikoiApis;

use Illuminate\Support\Facades\Facade;

class BarikoiApiFacade extends Facade {

    protected static function getFacadeAccessor() {
        return 'barikoiapi';
    }

}