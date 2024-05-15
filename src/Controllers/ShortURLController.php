<?php

declare(strict_types=1);

namespace AshAllenDesign\ShortURL\Controllers;

use AshAllenDesign\ShortURL\Classes\Resolver;
use AshAllenDesign\ShortURL\Models\ShortURL;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ShortURLController
{
    /**
     * Redirect the user to the intended destination
     * URL. If the default route has been disabled
     * in the config but the controller has been
     * reached using that route, return HTTP
     * 404.
     *
     * @param Request $request
     * @param Resolver $resolver
     * @param string $shortURLKey
     * @return RedirectResponse
     */
    public function __invoke(Request $request, Resolver $resolver, string $shortURLKey): RedirectResponse
    {

        if ($request->route()->getName() === 'short-url.invoke'
            && config('short-url.disable_default_route')) {
            abort(404);
        }

        $shortURL = ShortURL::where('url_key', $shortURLKey)->firstOrFail();

        if (isset($shortURL->agency_id) && !empty($shortURL->agency_id)) {
            switch ($shortURL->agency_id) {
                case "-1":
                    $destination_url = isset($shortURL->destination_url) && !empty($shortURL->destination_url) ? $shortURL->destination_url : '';
                    break;

                default:
                    //Senegal Changes: Get agency url and add in destination url
                    $agencyUrl = $this->getAgencyUrl();
                    $destination_url = str_replace('{agency_url}', $agencyUrl, $shortURL->destination_url);
                    break;
            }
        } else {
            abort(404);
        }

        $resolver->handleVisit(request(), $shortURL);

        return redirect($destination_url, $shortURL->redirect_status_code);

    }


    public function getAgencyUrl()
    {
        $hostname = "";

        if (isset($_SERVER["HTTP_HOST"]) && !empty($_SERVER["HTTP_HOST"])) {
            switch ($_SERVER["HTTP_HOST"]) {
                case "localhost":
                case "localhost:8080":
                    $hostname = \Config::get('app.url');
                    break;
                default:
                    $hostname = request()->getSchemeAndHttpHost();
                    break;
            }
        }
        return $hostname;
    }
}
