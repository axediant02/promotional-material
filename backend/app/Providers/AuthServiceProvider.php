<?php

namespace App\Providers;

use App\Models\AssignmentChatThread;
use App\Models\ClientRequest;
use App\Models\Folder;
use App\Models\MediaFile;
use App\Models\User;
use App\Policies\AssignmentChatThreadPolicy;
use App\Policies\ClientRequestPolicy;
use App\Policies\FilePolicy;
use App\Policies\FolderPolicy;
use App\Policies\UserAccessPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        User::class => UserAccessPolicy::class,
        MediaFile::class => FilePolicy::class,
        Folder::class => FolderPolicy::class,
        ClientRequest::class => ClientRequestPolicy::class,
        AssignmentChatThread::class => AssignmentChatThreadPolicy::class,
    ];
}
