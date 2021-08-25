<?php

namespace glasswalllab\dearconnector;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;

class DearConnector
{
    const CONTENT_TYPE = 'application/json';
    const LIMIT = 100;

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

            if ($method == 'POST' || $method == 'PUT') {
                $requestParams['body'] = json_encode($parameters);
                $responses = Http::withHeaders($this->getHeaders())->retry(3, 500)->acceptJson()->get($url,$requestParams)->body();
            } else {
                $url_first = $url.'?page=1&limit='.self::LIMIT;
                $response = Http::withHeaders($this->getHeaders())->retry(3, 500)->acceptJson()->get($url_first)->body();

                $total = json_decode($response)->Total;
                if(isset($total)){
                    $responses[] = $response;
                    if($total > self::LIMIT)
                    {                
                        for($i=2;$i<(ceil($total/self::LIMIT)); $i++)
                        {
                            $url_additional = $url.'?page='.$i.'&limit='.self::LIMIT;
                            $response = Http::withHeaders($this->getHeaders())->retry(3, 500)->acceptJson()->get($url_additional)->body();
                            $responses[] = $response;
                        }
                    }
                }
            }
            
            return $responses;

            //add response to DB
        } catch (RequestException $e) {
            if ($e->getCode() === 503) {
                // API limit exceeded
                sleep(5);
                return $this->CallDEAR($endpoint,$method,$body);
            }
            return $e;
        }
    }
}