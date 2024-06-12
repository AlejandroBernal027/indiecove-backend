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

class JuegoController extends AbstractController
{
    #[Route('/games/getAll')]
    public function getAllJuegos(connection $connection, Request $request): Response{
        if ($request->isMethod('get')) {
            $users = $connection->fetchAllAssociative('SELECT * FROM juego');
        } else {
            throw new MethodNotAllowedException(["Method Not Allowed"]);
        }

        $response = new Response(json_encode($users));

        return $response;
    }

    #[Route('/games/getAll/OrderByFecha')]
    public function getAllJuegosByFecha(connection $connection, Request $request): Response{
        if ($request->isMethod('get')) {
            $users = $connection->fetchAllAssociative('SELECT * FROM juego ORDER BY Fecha_publicacion');
        } else {
            throw new MethodNotAllowedException(["Method Not Allowed"]);
        }

        $response = new Response(json_encode($users));

        return $response;
    }

    #[Route('/games/getById/{id}')]
    public function getGameById(connection $connection, Request $request, $id): Response{
        if ($request->isMethod('get')) {
            $users = $connection->fetchAllAssociative("SELECT * FROM juego where idJuego=$id");
        } else {
            throw new MethodNotAllowedException(["Method Not Allowed"]);
        }

        $response = new Response(json_encode($users));

        return $response;
    }

    #[Route('/games/getForSearch')]
    public function getGameForSearch(connection $connection, Request $request): Response{
        if ($request->isMethod('get')) {
            $users = $connection->fetchAllAssociative("SELECT idJuego, Nombre, Img_principal FROM juego");
        } else {
            throw new MethodNotAllowedException(["Method Not Allowed"]);
        }

        $response = new Response(json_encode($users));

        return $response;
    }

    #[Route('/games/registrarJuego')]
    public function registrarJuego(Connection $connection, Request $request)
    {
        if ($request->isMethod('post')) {
            $data = json_decode($request->getContent());
            $array = get_object_vars($data);

            $nombre = $array["Nombre"];
            $etiquetas = $array["Etiquetas"];
            $fechaPublicacion = $array["Fecha_publicacion"]; // YYYY-MM-DD
            $nombreDesarrollador = $array["Nombre_Desarrollador"];
            $precio = floatval($array["Precio"]);
            $rebaja = intval($array["Rebaja"]);
            $imgPrin = $array["Img_principal"];
            $imgSec1 = $array["Img_sec1"];
            $imgSec2 = $array["Img_sec2"];
            $imgSec3 = $array["Img_sec3"];
            $imgSec4 = $array["Img_sec4"];
            $sinopsis = $array["Sinopsis"];
            $idDesarrollador = intval($array["Desarrolladores_idDesarrolladores"]);


            if($nombre == "" || $etiquetas == "" || $fechaPublicacion == "" || $nombreDesarrollador == "" || $rebaja < 0 || $precio < 0 || $imgPrin == "" || $imgSec1 == "" || $imgSec2 == "" || $imgSec3 == "" || $imgSec4 == "" || $sinopsis == "" || $idDesarrollador == 0){
                throw new BadRequestException("BadRequest");
            }

            $sql = "INSERT INTO juego (Nombre, Etiquetas, Fecha_publicacion, Nombre_Desarrollador, Precio, Rebaja, Img_principal, Img_sec1, Img_sec2, Img_sec3, Img_sec4, Sinopsis, Desarrolladores_idDesarrolladores) values (:nombre, :etiquetas, :fechaPublicacion, :nombreDesarrollador, :precio, :rebaja, :imgPrin, :imgSec1, :imgSec2, :imgSec3, :imgSec4, :sinopsis, :idDesarrollador)";
            $crearJuego = $connection->executeStatement($sql, ['nombre' => $nombre, 'etiquetas' => $etiquetas, 'fechaPublicacion' => $fechaPublicacion, 'nombreDesarrollador' => $nombreDesarrollador, 'precio' => $precio, 'rebaja' => $rebaja, 'imgPrin' => $imgPrin, 'imgSec1' => $imgSec1, 'imgSec2' => $imgSec2, 'imgSec3' => $imgSec3, 'imgSec4' => $imgSec4, 'sinopsis' => $sinopsis, 'idDesarrollador' => $idDesarrollador]);
            if ($crearJuego == 1){
                $response = new Response(json_encode(["operation" => true, "message" => "Juego registrado con exito"]));
            } else {
                $response = new Response(json_encode(["operation" => false, "message" => "No se ha registrado el juego"]));
            }
            
            return $response;
        } else {
            throw new MethodNotAllowedException(["Method Not Allowed"]);
        }

    }

    #[Route('games/eliminarJuego')]
    public function eliminarJuego(Connection $connection, Request $request)
    {
        if ($request->isMethod('delete')){
            $data = json_decode($request->getContent());
            $array = get_object_vars($data);
            $idJuego = intval($array["id"]);

            if($idJuego == 0){
                throw new BadRequestException("BadRequest");
            }

            $sql = "DELETE FROM juego WHERE idJuego=:id";
            $reservaEliminada = $connection->executeStatement($sql, ['id' => $idJuego]);
            if ($reservaEliminada == 1){
                $response = new Response(json_encode(["operation" => true, "message" => "Juego eliminado con exito"]));
            } else {
                $response = new Response(json_encode(["operation" => false, "message" => "No se ha eliminado el juego"]));
            }

            return $response;
        } else {
            throw new MethodNotAllowedException(["Method Not Allowed"]);
        }
    }

    #[Route('games/modificarJuego')]
    public function modificarJuego(Connection $connection, Request $request)
    {
        if ($request->isMethod('put')){
            $data = json_decode($request->getContent());
            $array = get_object_vars($data);

            $idJuego = intval($array["id"]);
            $nombre = $array["Nombre"];
            $etiquetas = $array["Etiquetas"];
            $fechaPublicacion = $array["Fecha_publicacion"]; // YYYY-MM-DD
            $nombreDesarrollador = $array["Nombre_Desarrollador"];
            $precio = floatval($array["Precio"]);
            $rebaja = intval($array["Rebaja"]);
            $imgPrin = $array["Img_principal"];
            $imgSec1 = $array["Img_sec1"];
            $imgSec2 = $array["Img_sec2"];
            $imgSec3 = $array["Img_sec3"];
            $imgSec4 = $array["Img_sec4"];
            $sinopsis = $array["Sinopsis"];

            if($nombre == "" || $etiquetas == "" || $fechaPublicacion == "" || $nombreDesarrollador == "" || $rebaja < 0 || $precio < 0 || $imgPrin == "" || $imgSec1 == "" || $imgSec2 == "" || $imgSec3 == "" || $imgSec4 == "" || $sinopsis == "" || $idJuego == 0){
                throw new BadRequestException("BadRequest");
            }
            $sql = "UPDATE juego SET Nombre = :nombre, Etiquetas = :etiquetas, Fecha_publicacion = :fechaPublicacion, Nombre_Desarrollador = :nombreDesarrollador, Precio = :precio, Rebaja = :rebaja, Img_principal = :imgPrin, Img_sec1 = :imgSec1, Img_sec2 = :imgSec2, Img_sec3 = :imgSec3, Img_sec4 = :imgSec4, Sinopsis = :sinopsis WHERE idJuego=:id";
            $reservaModificada = $connection->executeStatement($sql, ['id' => $idJuego, 'nombre' => $nombre, 'etiquetas' => $etiquetas, 'fechaPublicacion' => $fechaPublicacion, 'nombreDesarrollador' => $nombreDesarrollador, 'precio' => $precio, 'rebaja' => $rebaja, 'imgPrin' => $imgPrin, 'imgSec1' => $imgSec1, 'imgSec2' => $imgSec2, 'imgSec3' => $imgSec3, 'imgSec4' => $imgSec4, 'sinopsis' => $sinopsis]);

            if ($reservaModificada == 1){
                $response = new Response(json_encode(["operation" => true, "message" => "Juego modificado con exito"]));
            } else {
                $response = new Response(json_encode(["operation" => false, "message" => "No se ha modificado el juego"]));
            }

            return $response;
        } else {
            throw new MethodNotAllowedException(["Method Not Allowed"]);
        }
    }

    #[Route('games/modificarImgsJuego')]
    public function modificarImgs(Connection $connection, Request $request)
    {
        if ($request->isMethod('put')){
            $data = json_decode($request->getContent());
            $array = get_object_vars($data);

            $idJuego = intval($array["id"]);
            $imgPrin = $array["imgPrin"];
            $imgSec1 = $array["imgSec1"];
            $imgSec2 = $array["imgSec2"];
            $imgSec3 = $array["imgSec3"];
            $imgSec4 = $array["imgSec4"];

            if($imgPrin == "" || $imgSec1 == "" || $imgSec2 == "" || $imgSec3 == "" || $imgSec4 == "" || $idJuego == 0){
                throw new BadRequestException("BadRequest");
            }
            $sql = "UPDATE juego SET Img_principal = :imgPrin, Img_sec1 = :imgSec1, Img_sec2 = :imgSec2, Img_sec3 = :imgSec3, Img_sec4 = :imgSec4 WHERE idJuego=:id";
            $reservaModificada = $connection->executeStatement($sql, ['id' => $idJuego, 'imgPrin' => $imgPrin, 'imgSec1' => $imgSec1, 'imgSec2' => $imgSec2, 'imgSec3' => $imgSec3, 'imgSec4' => $imgSec4]);

            if ($reservaModificada == 1){
                $response = new Response(json_encode(["operation" => true, "message" => "Imagenes del juego modificadas con exito"]));
            } else {
                $response = new Response(json_encode(["operation" => false, "message" => "No se ha modificado el juego"]));
            }

            return $response;
        } else {
            throw new MethodNotAllowedException(["Method Not Allowed"]);
        }
    }
}