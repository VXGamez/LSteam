<?php

namespace SallePW\SlimApp\Controller;

use DateTime;
use GuzzleHttp\Exception\RequestException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use GuzzleHttp\Client;
use SallePW\SlimApp\Model\Search;
use SallePW\SlimApp\Model\User;

final class FormController
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function showRegisterForm(Request $request, Response $response): Response
    {

        return $this->container->get('view')->render(
            $response,
            'register.twig',
            []
        );
    }

    public function showLanding(Request $request, Response $response): Response
    {

        return $this->container->get('view')->render(
            $response,
            'landing.twig',
            [
                'notLogin' => "pilotes"
            ]
        );
    }


    public function showSearchResults(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        if(!isset($_SESSION['email'])){
            $erroreh['db'] = 'No hi ha session iniciada';
            return $this->container->get('view')->render(
                $response,
                'home.twig',
                [
                    'errors' => $erroreh
                ]
            );
        }

        $needsDatabase=true;
        if(isset($data['pageNumber'])){
            $apiUrl = "https://www.googleapis.com/youtube/v3/search?part=snippet&pageToken=".$data['pageNumber']."&videoEmbeddable=true&order=viewCount&q=".$data['cerca']."&maxResults=15&type=video&videoDefinition=high&key=".$_ENV['API_KEY'];
            $needsDatabase=false;
        }else{
            $apiUrl = "https://www.googleapis.com/youtube/v3/search?part=snippet&videoEmbeddable=true&order=viewCount&q=".$data['cerca']."&maxResults=15&type=video&videoDefinition=high&key=".$_ENV['API_KEY'];
        }


        $client = new Client(['base_uri' => 'https://www.googleapis.com/youtube/v3/','timeout'  => 5.0,]);
        $r = $client->get($apiUrl);
        $v = json_decode($r->getBody(), true);

        if($needsDatabase){
            $search = new Search(
                $data['cerca'],
                new DateTime()
            );

            $ok = $this->container->get('repository')->saveSearch($search);

        }else{
            $ok = true;
        }

        if($ok){
            $videos = $v['items'];
            $next = [];
            if(isset($v['nextPageToken'])){
                $next = $v['nextPageToken'];
            }
            $prev = [];
            if(isset($v['prevPageToken'])){
                $prev = $v['prevPageToken'];
            }
            return $this->container->get('view')->render(
                $response,
                'home.twig',
                [
                    'videos'=> $videos,
                    'urlFinal'=> $apiUrl,
                    'nextPage' => $next,
                    'prevPage' => $prev,
                    'cerca' => $data['cerca']
                ]
            );
        }else{
            $erroreh['db'] = 'Hi ha algun error amb la base de dades';
            return $this->container->get('view')->render(
                $response,
                'home.twig',
                [
                    'errors' => $erroreh
                ]
            );
        }



    }

    public function doLogout(Request $request, Response $response): Response{

        unset($_SESSION['email']);
        return $response->withHeader('Location', '/login')->withStatus(302);
    }

    public function showHome(Request $request, Response $response): Response
    {

        return $this->container->get('view')->render(
            $response,
            'home.twig',
            []
        );
    }

    public function showLogin(Request $request, Response $response): Response
    {

        return $this->container->get('view')->render(
            $response,
            'login.twig',
            []
        );
    }

    public function showBlank(Request $request, Response $response): Response
    {
        //return $response->withHeader('Location', '/login')->withStatus(302);
        return $this->container->get('view')->render(
            $response,
            'blank.twig',
            [
            ]
        );
    }








}