<?php

namespace SallePW\SlimApp\Controller;


use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use GuzzleHttp\Client;

final class FriendsController
{
    private ContainerInterface $container;

    public function __construct(
        ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function showMyFriends(Request $request, Response $response): Response
    {
        return $this->container->get('view')->render($response,'friends.twig',[]);
    }

    public function showMyRequests(Request $request, Response $response): Response {
        return $response->withHeader('Location', '/user/friends#myRequests')->withStatus(302);

    }

    public function showAddFriend(Request $request, Response $response): Response {
        return $response->withHeader('Location', '/user/friends#addFriend')->withStatus(302);

    }



}