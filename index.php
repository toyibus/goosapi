<?php

use CredetialProviders\ClientCredentialProvider;
use CredetialProviders\NoAuthCredentialProvider;
use CredetialProviders\TokenCredentialProvider;
use Goosapi\goosapi;


$loader = require './vendor/autoload.php';

$api = new goosapi();
$api->allowCORs(86400, "POST", "GET");


/* Any Novel API  */
$clientCredential = new ClientCredentialProvider();
$tokenCredential  = new TokenCredentialProvider(); 
$noAuthCredential = new NoAuthCredentialProvider();

$api->group("/oauth", function($api) use ($clientCredential) {
    $api->route("/", "\Api\Auth\Token", [
        "token/" => "*"
    ], $clientCredential);
});

$api->group("/just_read", function($api) use ($tokenCredential) {
    $api->route("/novel","\Api\JustRead\Novel", [
        "list/"         => "GET|POST",
        "read/read/:id" => "GET|POST",
    ], $tokenCredential);

    $api->route("/novel/favorite","\Api\JustRead\FavoriteNovel", [
        "list/"             => "GET|POST",
        "add/add/:id"       => "GET|POST",
        "remove/remove/:id" => "GET|POST",
    ], $tokenCredential);
                
});

$api->group("/just_write", function ($api) use ($tokenCredential) {
    $api->route("/novels", "\Api\JustWrite\Novels", [
        "getList"    => "GET",
        "getOne/:id" => "GET",
        "create"     => "POST",
        "update/:id" => "PUT",
        "delete/:id" => "DELETE",
    ], $tokenCredential);

    $api->route("/novels", "\Api\JustWrite\NovelPermissions", [
        "getList/:id/permissions"           => "GET",
        "create/:id/permissions/:user_id"   => "POST",
        "delete/:id/permissions/:user_id"   => "DELETE",
    ], $tokenCredential);

    $api->route("/novels/:novel_id/episodes", "\Api\JustWrite\Episodes", [
        "getList"       => "GET",
        "getOne/:id"    => "GET",
    ], $tokenCredential);

    $api->route("/novels/:novel_id/episodes", "\Api\JustWrite\EpisodePermissions", [
        "getList/:id/permissions"           => "GET",
        "create/:id/permissions/:user_id"   => "POST",
        "delete/:id/permissions/:user_id"   => "DELETE",
    ], $tokenCredential);

    $api->route("/novels/:novel_id/episodes/:episode_id/chapters", "\Api\JustWrite\Chapters", [
        "getOne/:id"    => "GET",
        "create"        => "POST",
        "update/:id"    => "PUT",
        "delete/:id"    => "DELETE",
    ], $tokenCredential);
});

$api->call("/routing/dump", function() use ($api) {
    $result = [];
    foreach ($api->router()->dump() as $route)
    {
        $result[$route["METHOD"] . ": " . $route["PATH"]] = $route;
    }
    dd($result);
});



