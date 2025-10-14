<?php

namespace App\Enums;

enum Permission: string
{
    // Users
    case UsersView = 'users.view';
    case UsersCreate = 'users.create';
    case UsersUpdate = 'users.update';
    case UsersDelete = 'users.delete';

    // Locations
    case LocationsView = 'locations.view';
    case LocationsCreate = 'locations.create';
    case LocationsUpdate = 'locations.update';
    case LocationsDelete = 'locations.delete';

    // Applications
    case ApplicationsView = 'applications.view';
    case ApplicationsCreate = 'applications.create';
    case ApplicationsUpdate = 'applications.update';
    case ApplicationsDelete = 'applications.delete';
}
