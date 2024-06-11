<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;

class ListaDeseadosController extends AbstractController
{
    #[Route('/lista/getAll')]
    public function getAllListas(connection $connection, Request $request): Response{
        if ($request->isMethod('get')) {
            $users = $connection->fetchAllAssociative('SELECT * FROM lista_deseados');
        } else {
            throw new MethodNotAllowedException(["Method Not Allowed"]);
        }

        $response = new Response(json_encode($users));

        return $response;
    }

    #[Route('/lista/getById/{id}')]
    public function getJugadorById(connection $connection, Request $request, $id): Response{
        if ($request->isMethod('get')) {
            $users = $connection->fetchAllAssociative("SELECT * FROM lista_deseados where Jugadores_idJugadores=$id");
        } else {
            throw new MethodNotAllowedException(["Method Not Allowed"]);
        }

        $response = new Response(json_encode($users));

        return $response;
    }

    #[Route('/lista/createLista')]
    public function createWishList(connection $connection, Request $request): Response {
        if ($request->isMethod('post')){
            $data = json_decode($request->getContent());
            $array = get_object_vars($data);

            $idJugador = intval($array['Jugadores_idjugadores']);

            if($idJugador <= 0){
                throw new BadRequestException("BadRequest");
            }

            $sql = "INSERT INTO `lista_deseados`(`Jugadores_idJugadores`) VALUES (:idJugador)";
            $addGame = $connection->executeStatement($sql, ['idJugador' => $idJugador]);

            if ($addGame == 1) {
                return new Response(json_encode(["operation" => true, "message" => "Lista creada con exito"]));
            } else {
                return new Response(json_encode(["operation" => false, "message" => "No se ha creado la lista"]));
            }
        } else {
            throw new MethodNotAllowedException(["Method Not Allowed"]);
        }
    }

    #[Route('/lista/deleteLista')]
    public function deleteWishList(connection $connection, Request $request): Response {
        if ($request->isMethod('delete')){
            $data = json_decode($request->getContent());
            $array = get_object_vars($data);

            $idListaDeseados = intval($array['idListaDeseados']);

            if($idListaDeseados <= 0){
                throw new BadRequestException("BadRequest");
            }

            $sql = "DELETE FROM lista_deseados WHERE idLista_Deseados=:idListaDeseados";
            $addGame = $connection->executeStatement($sql, ['idListaDeseados' => $idListaDeseados]);

            if ($addGame == 1) {
                return new Response(json_encode(["operation" => true, "message" => "Lista eliminada con exito"]));
            } else {
                return new Response(json_encode(["operation" => false, "message" => "No se ha eliminado la lista"]));
            }
        } else {
            throw new MethodNotAllowedException(["Method Not Allowed"]);
        }
    }

    #[Route('/lista/addGameToWishList')]
    public function addGameToWishList(connection $connection, Request $request): Response {
        if ($request->isMethod('post')){
            $data = json_decode($request->getContent());
            $array = get_object_vars($data);

            $idListaDeseados = get_object_vars($array['idListaDeseados'])["idLista_Deseados"];
            $idJuego = $array['idJuego'];

            if($idListaDeseados <= 0 || $idJuego <= 0){
                throw new BadRequestException("BadRequest");
            }

            $sql = "INSERT INTO lista_deseados_has_juego (Lista_Deseados_idLista_Deseados, Juego_idJuego) VALUES (:idListaDeseados, :idJuego)";
            $addGame = $connection->executeStatement($sql, ['idListaDeseados' => $idListaDeseados, 'idJuego' => $idJuego]);

            if ($addGame == 1) {
                return new Response(json_encode(["operation" => true, "message" => "Juego añadido a la lista con exito"]));
            } else {
                return new Response(json_encode(["operation" => true, "message" => "No se ha añadido el juego a la lista con exito"]));
            }
        } else {
            throw new MethodNotAllowedException(["Method Not Allowed"]);
        }
    }
}
