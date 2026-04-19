<?php

namespace DetIt;

class Plugin
{

    public function __construct()
    {
        // Loader handles all module loading, hook registration, and execution.
    }

    public function run()
    {

        Loader::init();
    }
}
