<?php

namespace glasswalllab\dearconnector;

//use glasswalllab\keypayconnector\TokenStore\TokenCache;
//use Illuminate\Http\Request;

class DearConnector
{
/*     public function CallAPI($endpoint,$method,$body)
    {  
        //Could move the below to job - but was having issues with the return
        $tokenCache = new TokenCache();
        $accessToken = $tokenCache->getAccessToken(config('keypayConnector.provider'));

        $url = config('keypayConnector.baseUrl').$endpoint;

        $options['headers']['Content-Type'] = 'application/json';
        //$options['headers']['If-Match'] = '*';

        $options['body'] = $body; //json encoded value

        $this->oauthClient = new \League\OAuth2\Client\Provider\GenericProvider([
            'clientSecret'            => config('keypayConnector.appSecret'),
            'clientId'                => config('keypayConnector.appId'),
            'redirectUri'             => config('keypayConnector.redirectUri'),
            'urlAuthorize'            => config('keypayConnector.authority').config('keypayConnector.authoriseEndpoint'),
            'urlAccessToken'          => config('keypayConnector.authority').config('keypayConnector.tokenEndpoint'),
            'urlResourceOwnerDetails' => config('keypayConnector.resource'),
          ]);

        try
        {
            $request = $this->oauthClient->getAuthenticatedRequest(
                $method,
                $url,
                $accessToken,
                $options,
            );

            $response = $this->oauthClient->getResponse($request);
            return $response->getBody()->getContents();

        } catch (Exception $ex) {
            return($ex);
        }
    } */

/*     public function CallAPIQueue($endpoint,$method,$body)
    {  
        $call = CallAPI::dispatch($endpoint,$method,$body);
    } */
}
