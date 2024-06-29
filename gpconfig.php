<?php
session_start();

// Include Google Client Library
include_once './google-client/Google_Client.php';
include_once './google-client/contrib/Google_Oauth2Service.php';

$client_id = '1040161205180-a2tkb1uen3vsrnf10bbh9l3034pp71pl.apps.googleusercontent.com'; // Google client ID
$client_secret = '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'; // Google Client Secret
$redirect_url = 'https://cerberustoreofficial.my.id/google.php'; // Callback URL

// Call Google API
$gclient = new Google_Client();
$gclient->setApplicationName('Google Login'); // Set with your application name
$gclient->setClientId($client_id); // Set with your Client ID
$gclient->setClientSecret($client_secret); // Set with your Client Secret
$gclient->setRedirectUri($redirect_url); // Set URL for Redirect after successful login

$google_oauthv2 = new Google_Oauth2Service($gclient);
?>
