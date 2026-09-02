<?php

namespace App\Menu\Filters;

use JeroenNoten\LaravelAdminLte\Menu\Filters\FilterInterface;

class PrivilegedUserMenuFilter implements FilterInterface
{
    /**
     * Allow the designated users to see every AdminLTE menu item.
     */
    public function transform($item)
    {
        $user = auth()->user();

        if ($user && in_array((int) $user->id, [1, 2, 3, 4], true)) {
            unset($item['can'], $item['restricted']);
        }

        return $item;
    }
}
