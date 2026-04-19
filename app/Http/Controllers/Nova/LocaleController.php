<?php


namespace App\Http\Controllers\Nova;

use App\Http\Controllers\Controller;

class LocaleController extends Controller
{
    /**
     * @param string $locale
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function handle($locale)
    {
        $avail_locales = config('nova.locales');
        if (isset($avail_locales[$locale])) {
            session(['locale' => $locale]);
            app()->setLocale($locale);
//            if (auth()->check()) {
//                auth()->user()->locale = $locale;
//                auth()->user()->save();
//            }
        } else {
            return abort(404);
        }
        return redirect('home');
    }
}
