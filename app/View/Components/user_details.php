<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class user_details extends Component
{
    /**
     * The details that should be visible
     * @array
     */
    public $hide;

    /**
     * The user's information retrived by the backend of the view
     * @array
     */
    public $user;



    /**
     * Create a new component instance.
     */
    public function __construct($hide, $user)
    {
        $this->hide = $hide;
        $this->user = $user;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.user_details');
    }
}
