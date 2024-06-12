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

class ValoracionesController extends AbstractController
{
    #[Route('/valoraciones/getAll')]
    public function getAllJuegos(connection $connection, Request $request): Response{
        if ($request->isMethod('get')) {
            $users = $connection->fetchAllAssociative('SELECT * FROM valoracion');
        } else {
            throw new MethodNotAllowedException("Method Not Allowed");
        }

        $response = new Response(json_encode($users));

        return $response;
    }

    #[Route('/valoraciones/getByGameId/{id}')]
    public function getJugadorById(connection $connection, Request $request, $id): Response{
        if ($request->isMethod('get')) {
            $users = $connection->fetchAllAssociative("SELECT * FROM valoracion where Juego_idJuego=$id");
        } else {
            throw new MethodNotAllowedException("Method Not Allowed");
        }

        $response = new Response(json_encode($users));

        return $response;
    }

    #[Route('/valoraciones/registrarValoracion')]
    public function registrarJuego(Connection $connection, Request $request)
    {
        if ($request->isMethod('post')) {
            $data = json_decode($request->getContent());
            $array = get_object_vars($data);

            $nombre = $array["Nombre_usuario"];
            $puntuacion = $array["Puntuacion"];
            $comentario = $array["Comentario"];
            $idJuego = intval($array["Juego_idJuego"]);
            $idJugador = intval($array["Jugadores_idJugadores"]);


            if($nombre == "" || $puntuacion == "" || $comentario == "" || $idJuego < 0 || $idJugador < 0){
                throw new BadRequestException("BadRequest");
            }
            $sql = "INSERT INTO valoracion (Nombre_usuario, Puntuacion, Comentario, Juego_idJuego, Jugadores_idJugadores) values (:nombreUsuario, :puntuacion, :comentario, :idJuego, :idJugador)";
            $crearJuego = $connection->executeStatement($sql, ['nombreUsuario' => $nombre, 'puntuacion' => $puntuacion, 'comentario' => $comentario, 'idJugador' => $idJugador, 'idJuego' => $idJuego]);
            if ($crearJuego == 1){
                $response = new Response(json_encode(["operation" => true, "message" => "Valoracion registrada con exito"]));
            } else {
                $response = new Response(json_encode(["operation" => false, "message" => "No se ha registrado la valoracion"]));
            }
            
            return $response;
        } else {
            throw new MethodNotAllowedException("Method Not Allowed");
        }

    }

    #[Route('valoraciones/eliminarValoracion')]
    public function eliminarJuego(Connection $connection, Request $request)
    {
        if ($request->isMethod('delete')){
            $data = json_decode($request->getContent());
            $array = get_object_vars($data);
            $idValoracion = intval($array["id"]);

            if($idValoracion == 0){
                throw new BadRequestException("BadRequest");
            }

            $sql = "DELETE FROM valoracion WHERE idValoracion=:id";
            $reservaEliminada = $connection->executeStatement($sql, ['id' => $idValoracion]);
            if ($reservaEliminada == 1){
                $response = new Response(json_encode(["operation" => true, "message" => "Valoracion eliminada con exito"]));
            } else {
                $response = new Response(json_encode(["operation" => false, "message" => "No se ha podido eliminar la valoracion"]));
            }

            return $response;
        } else {
            throw new MethodNotAllowedException("Method Not Allowed");
        }
    }

    #[Route('valoraciones/modificarValoracion')]
    public function modificarJuego(Connection $connection, Request $request)
    {
        if ($request->isMethod('put')){
            $data = json_decode($request->getContent());
            $array = get_object_vars($data);

            $idValoracion = intval($array["id"]);
            $nombre = $array["nombreUsuario"];
            $puntuacion = $array["puntuacion"];
            $comentario = $array["comentario"];
            $idJuego = intval($array["idJuego"]);
            $idJugador = intval($array["idJugador"]);

            if($nombre == "" || $puntuacion == "" || $comentario == "" || $idValoracion < 0 || $idJugador < 0 || $idJuego < 0){
                throw new BadRequestException("BadRequest");
            }
            $sql = "UPDATE valoracion SET Nombre_usuario = :nombre, Puntuacion = :puntuacion, Comentario = :comnetario, Juego_idJuego = :idJuego, Jugadores_idJugadores = :idJugador WHERE idValoracion = :id";
            $reservaModificada = $connection->executeStatement($sql, ['id' => $idValoracion, 'nombre' => $nombre, 'puntuacion' => $puntuacion, 'comnetario' => $comentario, 'idJuego' => $idJuego, 'idJugador' => $idJugador]);

            if ($reservaModificada == 1){
                $response = new Response(json_encode(["operation" => true, "message" => "Valoracion modificada con exito"]));
            } else {
                $response = new Response(json_encode(["operation" => false, "message" => "No se ha podido modificar la valoracion"]));
            }

            return $response;
        } else {
            throw new MethodNotAllowedException("Method Not Allowed");
        }
    }
}
