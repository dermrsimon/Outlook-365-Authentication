<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class AuthController extends Controller
{
   public function signin()
   {
      if (session_status() == PHP_SESSION_NONE) {
         session_start();
      }

      // Initialize OAuth
      $oauthClient = new \League\OAuth2\Client\Provider\GenericProvider([
         'clientId'                => env('OAUTH_APP_ID'),
         'clientSecret'            => env('OAUTH_APP_PASSWORD'),
         'redirectUri'             => env('OAUTH_REDIRECT_URI'),
         'urlAuthorize'            => env('OAUTH_AUTHORITY').env('OAUTH_AUTHORIZE_ENDPOINT'),
         'urlAccessToken'          => env('OAUTH_AUTHORITY').env('OAUTH_TOKEN_ENDPOINT'),
         'urlResourceOwnerDetails' => '',
         'scopes'                  => env('OAUTH_SCOPES')
      ]);

      $authorizationUrl = $oauthClient->getAuthorizationUrl();

      $_SESSION['oauth_state'] = $oauthClient->getState();

      header('Location: ' . $authorizationUrl);
      exit();
   }

   public function gettoken()
   {
      if (session_status() == PHP_SESSION_NONE) {
         session_start();
      }

      if (isset($_GET['code'])) {
      // Check that state matches
         if (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth_state'])) {
            exit('State provided in redirect does not match expected value.');
         }

         unset($_SESSION['oauth_state']);

         // Initialize OAuth
         $oauthClient = new \League\OAuth2\Client\Provider\GenericProvider([
            'clientId'                => env('OAUTH_APP_ID'),
            'clientSecret'            => env('OAUTH_APP_PASSWORD'),
            'redirectUri'             => env('OAUTH_REDIRECT_URI'),
            'urlAuthorize'            => env('OAUTH_AUTHORITY').env('OAUTH_AUTHORIZE_ENDPOINT'),
            'urlAccessToken'          => env('OAUTH_AUTHORITY').env('OAUTH_TOKEN_ENDPOINT'),
            'urlResourceOwnerDetails' => '',
            'scopes'                  => env('OAUTH_SCOPES')
         ]);

         try {
            $accessToken = $oauthClient->getAccessToken('authorization_code', [
               'code' => $_GET['code']
            ]);
            
            $tokenCache = new \App\TokenStore\TokenCache;
            $tokenCache->storeTokens($accessToken->getToken(), $accessToken->getRefreshToken(), $accessToken->getExpires());
            // todo: store the refresh token secured in a database, not in a session
            
            return redirect()->route('landing');
         } catch (League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
            exit('Error getting tokens: '.$e->getMessage());
         }

         exit();
      } elseif (isset($_GET['error'])) {
         exit('Error: ' . $_GET['error'] . ' - ' . $_GET['error_description']);
      }
   }
}