<?php

namespace DetIt\Admin;

class Dashboard
{

    public function register_menu()
    {

        add_menu_page(
            'DetIt',
            'DetIt',
            'manage_options',
            'detit-dashboard',
            [$this, 'render']
        );
    }

    public function render()
    {
        echo "<h1>DetIt Dashboard</h1>";
    }
}
