<?php

namespace glasswalllab\dearconnector;

use Illuminate\Support\Facades\Http;

class DearConnector
{
    public function CallDEAR($endpoint,$method,$body)
    {  
        $url = config('DearConnector.baseUrl').$endpoint;
        
        try
        {
            $response = Http::withHeaders([
                'api-auth-accountid' => config('DearConnector.accountid'),
                'api-auth-applicationkey' => config('DearConnector.applicationkey')
            ])->retry(3, 500)->acceptJson()->get($url, $body);

            return $response->getBody()->getContents();

        } catch (Exception $ex) {
            return($ex);
        }
    }
}
