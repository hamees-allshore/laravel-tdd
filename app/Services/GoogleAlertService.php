<?php

namespace App\Services;

use DOMDocument;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class GoogleAlertService
{
    public function __construct()
    {
        if (config('app.env') == 'testing') {
            Http::preventStrayRequests(); // Throw exception if the request is not faked
            Http::fake([
                'https://www.google.com/alerts/preview?*' => Http::Response(Storage::get('test/Service/GoogleAlert/Response.html'), 200),
            ]);
        }
    }

    public function getAllFeeds($keyword)
    {
        $params = urlencode('[null,[null,null,null,[null,"' . $keyword . '","com",[null,"en","US"],null,null,null,0,1],null,3,[[null,1,"user@example.com",[null,null,11],2,"en-US",null,null,null,null,null,"0",null,null,"AB2Xq4hcilCERh73EFWJVHXx-io2lhh1EhC8UD8"]]],0]');
        $response = Http::get("https://www.google.com/alerts/preview", [
            'params' => $params,
        ]);

        if ($response->failed()) {
            return [];
        }

        $doc = new DOMDocument;
        $doc->loadHTML($response->body());
        $doc->normalize();
        $lis = $doc->getElementsByTagName('li');

        $parsedAlerts = [];
        foreach ($lis as $li) {
            if ($li->getAttribute('class') == 'result') {
                $alert = [];
                foreach ($li->childNodes as $cN) {
                    if ($cN->nodeType != 3) {
                        switch ($cN->getAttribute('class')) {
                            case 'result_image_container':
                                $alert['image'] = $cN->childNodes->item(1)->childNodes->item(1)->childNodes->item(1)->getAttribute('src');
                                break;
                            case 'result_title':
                                $alert['title'] = $cN->nodeValue;
                                if ($cN->childNodes->item(1)->nodeName == 'a') {
                                    $url = $cN->childNodes->item(1)->getAttribute('href');
                                    // https://www.google.com/url?rct=j&sa=t&url=
                                    $alert['url'] = urldecode(substr($url, 42, strpos($url, '&ct=ga') - 42));
                                }
                                break;
                            default:
                                if ($cN->nodeName == 'div') {
                                    $alert['description'] = $cN->nodeValue;
                                }
                                break;
                        }
                    }
                }

                $parsedAlerts[$li->parentNode->parentNode->childNodes->item(1)->nodeValue][] = $alert;
            }
        }

        return $parsedAlerts;
    }

}
