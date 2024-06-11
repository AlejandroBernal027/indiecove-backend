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

class ComprasController extends AbstractController
{
    #[Route('/compras/getAll')]
    public function getAllCompras(connection $connection, Request $request): Response{
        if ($request->isMethod('get')) {
            $users = $connection->fetchAllAssociative('SELECT * FROM compras');
        } else {
            throw new MethodNotAllowedException(["Method Not Allowed"]);
        }

        $response = new Response(json_encode($users));

        return $response;
    }

    #[Route('/compras/getById/{id}')]
    public function getCompraById(connection $connection, Request $request, $id): Response{
        if ($request->isMethod('get')) {
            $users = $connection->fetchAllAssociative("SELECT * FROM compras where idCompras=$id");
        } else {
            throw new MethodNotAllowedException(["Method Not Allowed"]);
        }

        $response = new Response(json_encode($users));

        return $response;
    }

    #[Route('/compras/registrarCompra')]
    public function registrarCompra(Connection $connection, Request $request)
    {
        if ($request->isMethod('post')) {
            $data = json_decode($request->getContent());
            $array = get_object_vars($data);

            $precio = $array["Precio"];
            $idJugador = intval($array["Jugadores_idJugadores"]);


            if($precio == "" || $idJugador <= 0){
                throw new BadRequestException("BadRequest");
            }
            $sql = "INSERT INTO compras (Precio, Jugadores_idJugadores) values (:precio, :idJugador)";
            $crearJuego = $connection->executeStatement($sql, ['precio' => $precio, 'idJugador' => $idJugador]);
            if ($crearJuego == 1){
                $response = new Response(json_encode(["operation" => true, "message" => "Compra registrada con exito"]));
            } else {
                $response = new Response(json_encode(["operation" => true, "message" => "No se ha podido registrar la compra"]));
            }
            
            return $response;
        } else {
            throw new MethodNotAllowedException(["Method Not Allowed"]);
        }

    }

    #[Route('compras/eliminarCompra')]
    public function eliminarCompra(Connection $connection, Request $request)
    {
        if ($request->isMethod('delete')){
            $data = json_decode($request->getContent());
            $array = get_object_vars($data);
            $idCompras = intval($array["id"]);

            if($idCompras == 0){
                throw new BadRequestException("BadRequest");
            }

            $sql = "DELETE FROM compras WHERE idCompras=:id";
            $reservaEliminada = $connection->executeStatement($sql, ['id' => $idCompras]);
            if ($reservaEliminada == 1){
                $response = new Response(json_encode(["operation" => true, "message" => "Compra eliminada con exito"]));
            } else {
                $response = new Response(json_encode(["operation" => false, "message" => "No se ha podido eliminar la compra"]));
            }

            return $response;
        } else {
            throw new MethodNotAllowedException(["Method Not Allowed"]);
        }
    }

    #[Route('compras/modificarCompra')]
    public function modificarCompra(Connection $connection, Request $request)
    {
        if ($request->isMethod('put')){
            $data = json_decode($request->getContent());
            $array = get_object_vars($data);

            $idCompra = intval($array["id"]);
            $precio = $array["precio"];
            $idJugador = intval($array["idJugador"]);

            if($precio == "" || $idJugador < 0 || $idJugador < 0){
                throw new BadRequestException("BadRequest");
            }
            $sql = "UPDATE compras SET Precio = :precio, Jugadores_idJugadores = :idJugador WHERE idCompras = :id";
            $reservaModificada = $connection->executeStatement($sql, ['id' => $idCompra, 'precio' => $precio, 'idJugador' => $idJugador]);

            if ($reservaModificada == 1){
                $response = new Response(json_encode(["operation" => true, "message" => "Compra modificada con exito"]));
            } else {
                $response = new Response(json_encode(["operation" => false, "message" => "No se ha podido modificar la compra"]));
            }

            return $response;
        } else {
            throw new MethodNotAllowedException(["Method Not Allowed"]);
        }
    }

    #[Route('/compras/makeJugadorHasJuego')]
    public function makeCompraHasJuego(connection $connection, Request $request) {
        if ($request->isMethod('post')){
            $data = json_decode($request->getContent());
            $array = get_object_vars($data);
            $idJugador = $array['idJugador'];
            $idJuego = $array['idJuego'];

            $sql = "INSERT INTO jugadores_has_juego (Jugadores_idJugadores, Juego_idJuego) VALUES (:idJugador, :idJuego)";
            $makeCompraHasJuego = $connection->executeStatement($sql, ['idJugador' => $idJugador, 'idJuego' => $idJuego]);

            if ($makeCompraHasJuego == 1) {
                return new Response(json_encode(["operation" => true, "message" => "Juego comprado con exito"])); // Array con operation: true/false y mensaje: contenido del mensaje
            } else {
                return new Response(json_encode(["operation" => false, "message" => "No se ha podido comprar el juego"]));
            }
            
        } else {
            throw new MethodNotAllowedException(["Method Not Allowed"]);
        }
    }
}

