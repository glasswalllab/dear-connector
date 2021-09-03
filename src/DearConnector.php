<?php

namespace glasswalllab\dearconnector;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;
use glasswalllab\dearconnector\Models\ApiLog;

class DearConnector
{
    const CONTENT_TYPE = 'application/json';
    const LIMIT = 100;

    private $page = 2; //Set to 2 as the first call is already completed

    protected function getHeaders()
    {
        return [
            'Content-Type' => self::CONTENT_TYPE,
            'api-auth-accountid' => config('DearConnector.accountid'),
            'api-auth-applicationkey' => config('DearConnector.applicationkey')
        ];
    }

    public function CallDEAR($endpoint,$method, array $parameters)
    {  
        $url = config('DearConnector.baseUrl').$endpoint;
        
        $responses = [];
        $method = strtoupper($method);
        
        try
        {
            //POST or PUT request - contains parameter data, no pagination required - return array
            if ($method === 'POST' || $method === 'PUT' || $method === 'PATCH' || $method === 'DELETE') {

                $log = ApiLog::create([
                    'service' => 'dear',
                    'resource' => $url,
                    'method' => $method,
                    'request' => json_encode($parameters),
                ]);

                $baseCall = Http::withHeaders($this->getHeaders())->retry(3, 500)->acceptJson();

                switch($method) {
                    case 'POST':
                        $call = $baseCall->post($url,$parameters);
                    break;

                    case 'PUT':
                        $call = $baseCall->PUT($url,$parameters);
                    break;

                    case 'PATCH':
                        $call = $baseCall->PATCH($url,$parameters);
                    break;

                    case 'DELETE':
                        $call = $baseCall->DELETE($url,$parameters);
                    break;
                }
                $responses = $call->body();
                $log->code = $call->status();
                $log->response = $responses;
                $log->save();

            //GET request - check for pagination - return array    
            } elseif($method === 'GET') {
                
                $pageLimitParams = array('page' => 1,'limit' => self::LIMIT);
                $requestParams = array_merge($parameters,$pageLimitParams);
                
                $log_first_call = ApiLog::create([
                    'service' => 'dear',
                    'resource' => $url,
                    'method' => $method,
                    'request' => json_encode($requestParams),
                ]);

                $call = Http::withHeaders($this->getHeaders())->retry(3, 500)->acceptJson()->get($url,$requestParams);
                $response = $call->body();
                $json = json_decode($response);
                $log_first_call->code = $call->status();
                $log_first_call->response = $response;
                $log_first_call->save();

                $json = json_decode($response);

                //Check total items, returned with first call
                if(!is_null($json)) {
                    $total = $json->Total;

                    if(isset($total)) {
                        $responses[] = $response;
                        if($total > self::LIMIT)
                        {   
                            //Start for loop at 2, as page 1 has already been retrieved - ceil = rounds up to nearest whole number             
                            for($i=$this->page;$i<=(ceil($total/self::LIMIT)); $i++)
                            {
                                $pageLimitParams = array('page' => $i,'limit' => self::LIMIT);
                                $requestParams = array_merge($parameters,$pageLimitParams);

                                $this->page = $i;
                                
                                $log_additional_call = ApiLog::create([
                                    'service' => 'dear',
                                    'resource' => $url,
                                    'method' => $method,
                                    'request' => json_encode($requestParams),
                                ]);

                                $call = Http::withHeaders($this->getHeaders())->retry(3, 500)->acceptJson()->get($url,$requestParams);
                                $response = $call->body();
                                $responses[] = $response;
                                $log_additional_call->code = $call->status();
                                $log_additional_call->response = $response;
                                $log_additional_call->save();
                            }
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