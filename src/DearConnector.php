<?php

namespace glasswalllab\dearconnector;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;
use glasswalllab\dearconnector\Models\ApiLog;

class DearConnector
{
    const CONTENT_TYPE = 'application/json';
    const LIMIT = 100;

    private $page = 2;

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
            //POST or PUT request - contains parameter data, no pagination required - return array
            if ($method == 'POST' || $method == 'PUT') {
                $requestParams['body'] = json_encode($parameters);
                
                $log = ApiLog::create([
                    'resource' => $url,
                    'request' => $requestParams['body'],
                ]);

                $responses = Http::withHeaders($this->getHeaders())->retry(3, 500)->acceptJson()->get($url,$requestParams)->body();
                
                $log->response = $responses;
                $log->save();

            //Any other request (GET) - no parameter, check for pagination - return array    
            } else {
                $url_first_call = $url.'?page=1&limit='.self::LIMIT;
                
                $log_first_call = ApiLog::create([
                    'resource' => $url_first_call,
                    'request' => '',
                ]);

                $response = Http::withHeaders($this->getHeaders())->retry(3, 500)->acceptJson()->get($url_first_call)->body();

                $log_first_call->response = $response;
                $log_first_call->save();

                //Check total items, returned with first call
                $total = json_decode($response)->Total;

                if(isset($total)){
                    $responses[] = $response;
                    if($total > self::LIMIT)
                    {   
                        //Start for loop at 2, as page 1 has already been retrieved - ceil = rounds up to nearest whole number             
                        for($i=$this->page;$i<(ceil($total/self::LIMIT)); $i++)
                        {
                            $url_additional = $url.'?page='.$i.'&limit='.self::LIMIT;
                            $this->page = $i;
                            
                            $log_additional_call = ApiLog::create([
                                'resource' => $url_additional,
                                'request' => '',
                            ]);

                            $response = Http::withHeaders($this->getHeaders())->retry(3, 500)->acceptJson()->get($url_additional)->body();
                            $responses[] = $response;

                            $log_additional_call->response = $response;
                            $log_additional_call->save();
                        }
                    }
                }
            }
            
            //return results as an array
            return $responses;

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