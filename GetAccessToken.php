<?php
/**
 * Class for obtaining an access token from SurveyMonkey API v2
 * @package default
 */
class GetAccessToken {

    /**
     * Your api key.
     * @var string
     */
    protected $apiKey;

    /**
     * This must match the redirect uri set on your SurveyMonkey account
     * @var string
     */
    protected $redirectUri;

    /**
     * Your clientId is your SurveyMonkey developer username
     * @var string
     */
    protected $clientId;

    /**
     * @var string
     */
    protected $clientSecret;

    /**
     * SurveyMonkey's authorization API base URL
     * @var string
     */
    protected $baseUrl = 'https://www.surveymonkey.com/user/oauth/authorize?';

    public function __construct($apiKey, $redirectUri, $clientId, $clientSecret)
    {
        $this->apiKey = $apiKey;
        $this->redirectUri = $redirectUri;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    /**
     * This will generate a URL that will redirect the user (survey owner) to log in and authorize access to
     * their account.
     * @return string
     */
    public function getLoginUriWithParams()
    {
        $params = array(
            'response_type' => 'code',
            'redirect_uri' => $this->redirectUri,
            'client_id' => $this->clientId,
            'api_key' => $this->apiKey,
        );

        $uriWithParams = $this->baseUrl . http_build_query($params);

        return $uriWithParams;
    }

    /**
     * After the user has logged in, an $authCode is generated. Pass that into this function to get an access token.
     * The access token will be used to sign all the requests.
     * @param $authCode
     * @return string
     */
    public function requestAccessToken($authCode)
    {
        $baseUrl = 'https://api.surveymonkey.net';
        $endpoint = '/oauth/token';

        $params = array(
            'client_secret' => $this->clientSecret,
            'code' => $authCode,
            'redirect_uri' => $this->redirectUri,
            'client_id' => $this->clientId,
            'grant_type' => 'authorization_code'
        );

        $accessTokenUri = $baseUrl . $endpoint . '?api_key=' . $this->apiKey;

        $ch  = curl_init();
        curl_setopt($ch, CURLOPT_URL, $accessTokenUri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

        $result = curl_exec($ch);

        curl_close($ch);

        $response = (array) json_decode($result);

        return array_key_exists('access_token', $response) ? $response['access_token'] : 'Could not get access token';
    }

}