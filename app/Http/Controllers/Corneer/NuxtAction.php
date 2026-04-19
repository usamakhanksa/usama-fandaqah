<?php
/**
 *  * Created by PhpStorm.
 * User: Mohamed Yasser ( yasoesr@gmail.com )
 * Date: 9/17/19
 * Time: 12:24 PM
 */

namespace App\Http\Controllers\Corneer;

use App\Http\Controllers\Controller;

class NuxtAction extends Controller
{
    public function __invoke()
    {
        return file_get_contents(public_path('_nuxt/index.html'));
    }
}
