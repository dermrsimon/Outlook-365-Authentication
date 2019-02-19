<?php

namespace App\TokenStore;

class TokenCache {
  public function storeTokens($access_token, $refresh_token, $expires) {
    $_SESSION['access_token'] = $access_token;
    $_SESSION['refresh_token'] = $refresh_token;
    $_SESSION['token_expires'] = $expires;
  }

  public function clearTokens() {
    unset($_SESSION['access_token']);
    unset($_SESSION['refresh_token']);
    unset($_SESSION['token_expires']);
  }

  public function getAccessToken() {
    // exists token?
    if (empty($_SESSION['access_token']) ||
        empty($_SESSION['refresh_token']) ||
        empty($_SESSION['token_expires'])) {
      return '';
    }

    // + 5 minuten zeitdifferenz
    $now = time() + 300;
    if ($_SESSION['token_expires'] <= $now) {
      // Token expired (bzw fast)

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
        $newToken = $oauthClient->getAccessToken('refresh_token', [
          'refresh_token' => $_SESSION['refresh_token']
        ]);

        $this->   storeTokens($newToken->getToken(), $newToken->getRefreshToken(),
          $newToken->getExpires());

        return $newToken->getToken();
      }
      catch (League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
        return '';
      }
    }
    else {
      // just reutrn, dont refresh token
      return $_SESSION['access_token'];
    }
  }
}