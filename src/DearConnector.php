<?php

namespace glasswalllab\dearconnector;

use Illuminate\Support\Facades\Http;

class DearConnector
{
    const CONTENT_TYPE = 'application/json';
    const LIMIT = 100;
    const PAGE = 1;

    protected function getHeaders()
    {
        return [
            'Content-Type' => self::CONTENT_TYPE,
            'api-auth-accountid' => config('DearConnector.accountid'),
            'api-auth-applicationkey' => config('DearConnector.applicationkey')
        ];
    }

    public function CallDEAR($endpoint,$method,$parameters)
    {  
        $url = config('DearConnector.baseUrl').$endpoint;
        
        try
        {

        //add request to DB
        //pagination?
        //API call limit of 60 per minute


            dd($this->getHeaders());

            if ($method == 'POST' || $method == 'PUT') {
                $requestParams['body'] = json_encode($parameters);
            } else {
                $requestParams['query'] = $parameters;
            }

            $response = Http::withHeaders($this->getHeaders())->retry(3, 500)->acceptJson()->get($url, $requestParams);

            //add response to DB

            //Handle errors
            return $response->getBody()->getContents();

        } catch (ClientException $clientException) {
            return $clientException;
        }
        
        catch (ServerException $serverException) {

            if ($ex->getResponse()->getStatusCode() === 503) {
                // API limit exceeded
                sleep(5);
                return $this->CallDEAR($endpoint,$method,$body);
            }
            return $serverException;
        }
    }
}
