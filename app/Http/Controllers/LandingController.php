<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;

class LandingController extends Controller
{
	public function landing() {
	    if (session_status() == PHP_SESSION_NONE) {
	    	session_start();
	    }

	    $tokenCache = new \App\TokenStore\TokenCache;


		$graph = new Graph();
		$graph->setAccessToken($tokenCache->getAccessToken());

		$user = $graph->createRequest('GET', '/me')
			->setReturnType(Model\User::class)
			->execute();


		return view('landing', array(
			'username' => $user->getDisplayName(),
			'givenname' => $user->getGivenName(),
			'jobTitle' => $user->getJobTitle(),
			'mail' => $user->getMail(),
			'surname' => $user->getSurname(),
			'id' => $user->getID(),
			'officeLocation' => $user->getOfficeLocation()
		));
		/*
		echo 'User: '.$user->getDisplayName() . "<br><br>";    
		echo 'Token: '.$tokenCache->getAccessToken();
	  	*/
	}
}