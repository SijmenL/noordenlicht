<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class FileManager extends Component
{
    /**
     * The files.
     *
     * @var \Illuminate\Support\Collection
     */
    public $files;

    /**
     * The breadcrumbs.
     *
     * @var array
     */
    public $breadcrumbs;

    /**
     * The folder ID.
     *
     * @var int|null
     */
    public $folderId;

    /**
     * If the user has admin capabilities.
     *
     * @var bool
     */
    public $hasAdminViewers;

    /**
     * If the user has admin capabilities.
     *
     * @var bool
     */
    public $isAdmin;

    /**
     * The storage URL.
     *
     * @var string
     */
    public $storageUrl;

    /**
     * The name for users with admin power
     *
     * @var string
     */
    public $adminName;

    /**
     * The name for users that arent the admin
     *
     * @var string
     */
    public $nonAdminName;

    /**
     * The location of the file viewer, e.g. Lessen
     *
     * @var string
     */
    public $location;

    /**
     * The locations id if relevant
     *
     * @var string
     */
    public $locationId;

    public function __construct(
        $files,
        $breadcrumbs,
        $hasAdminViewers,
        $storageUrl,
        $location,
        $folderId = null,
        $isAdmin = false,
        $adminName = "Administratie",
        $nonAdminName = "Administratie",
        $locationId = null
    ) {
        $this->files = $files;
        $this->breadcrumbs = $breadcrumbs;
        $this->folderId = $folderId;
        $this->hasAdminViewers = $hasAdminViewers;
        $this->isAdmin = $isAdmin;
        $this->adminName = $adminName;
        $this->nonAdminName = $nonAdminName;
        $this->storageUrl = $storageUrl;
        $this->location = $location;
        $this->locationId = $locationId;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.file_manager');
    }
}
