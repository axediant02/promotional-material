<?php

namespace App\Providers;

use App\Models\MediaFile;
use App\Policies\FilePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        MediaFile::class => FilePolicy::class,
    ];
}
