<?php

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
        echo "here ... ";
        exit;

        if ($request->route()->getName() === 'short-url.invoke'
            && config('short-url.disable_default_route')) {
            abort(404);
        }

        $shortURL = ShortURL::where('url_key', $shortURLKey)->firstOrFail();
        //Senegal Changes: Get agency url and add in destination url
        $agencyUrl = $this->getAgencyUrl($shortURL->agency_id);
        $destination_url = str_replace('{agency_url}', $agencyUrl, $shortURL->destination_url);

        $resolver->handleVisit(request(), $shortURL);

        return redirect($destination_url, $shortURL->redirect_status_code);

    }


    public function getAgencyUrl($agency_id = '')
    {
        $agency_id = isset($agency_id) && !empty($agency_id) ? $agency_id : \UserManager::GetCurrentUserAgencyID();
        $hostname = "";
        $agencyUrl = \AgencyManager::GetAgencyURL($agency_id);

        if (isset($_SERVER["HTTP_HOST"]) && !empty($_SERVER["HTTP_HOST"])) {
            switch ($_SERVER["HTTP_HOST"]) {
                case "localhost":
                case "localhost:8080":
                    $hostname = \Config::get('app.url');
                    break;
                default:
                    $hostname = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$agencyUrl";
                    break;
            }
        }
        return $hostname;
    }
}
