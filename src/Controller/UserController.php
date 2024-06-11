<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;

class UserController extends AbstractController
{

    #[Route('/user/jugadores')]
    public function getAllJugadores(Connection $connection, Request $request): Response{
        if ($request->isMethod('get')) {
            $users = $connection->fetchAllAssociative('SELECT * FROM jugadores');
        } else {
            throw new MethodNotAllowedException(["Method Not Allowed"]);
        }

        $response = new Response(json_encode($users));

        return $response;
    }

    #[Route('/user/jugador/{id}')]
    public function getJugadorById(connection $connection, Request $request, $id): Response{
        if ($request->isMethod('get')) {
            $users = $connection->fetchAllAssociative("SELECT * FROM jugadores where idJugadores=$id");
        } else {
            throw new MethodNotAllowedException(["Method Not Allowed"]);
        }

        $response = new Response(json_encode($users));

        return $response;
    }

    #[Route('/user/registrarJugador')]
    public function registrarUsuario(Connection $connection, Request $request)
    {
        if ($request->isMethod('post')) {
            $data = json_decode($request->getContent());
            $array = get_object_vars($data);

            $email = $array["Email"];
            $nombre = $array["Nombre"];
            $apellidos = $array["Apellidos"];
            $nombreUsuario = $array["Nombre_usuario"];
            $password = $array["Contrasena"];
            $fotoPerfil = $array["Foto_perfil"];

            if($email == "" || $nombre == "" || $apellidos == "" || $nombreUsuario == "" || $password == ""){
                throw new BadRequestException("BadRequest");
            }

            $usersEmail = $connection->fetchAllAssociative("SELECT idJugadores FROM jugadores where Email='$email'");
            $usersUser = $connection->fetchAllAssociative("SELECT idJugadores FROM jugadores where Nombre_usuario='$nombreUsuario'");
            
            if ($usersEmail) {
                $response = new Response(json_encode(["operation" => false, "message" => "Email ya registrado"]));
                return $response;
            }

            if ($usersUser) {
                $response = new Response(json_encode(["operation" => false, "message" => "Usuario ya registrado"]));
                return $response;
            }

            $sql = "INSERT INTO jugadores (Email, Nombre, Apellidos, Nombre_usuario, Contrasena, Foto_perfil) values (:email , :nombre, :apellidos, :nombreUsuario, :contrasena, :fotoPerfil)";
            $crearUsuario = $connection->executeStatement($sql, ['email' => $email, 'nombre' => $nombre, 'apellidos' => $apellidos, 'nombreUsuario' => $nombreUsuario, 'contrasena' => $password, 'fotoPerfil' => $fotoPerfil]);
        
            if ($crearUsuario == 1){
                $response = new Response(json_encode(["operation" => true, "message" => "Se ha podido registrar al usuario con exito"]));
            } else {
                $response = new Response(json_encode(["operation" => false, "message" => "No se ha podido registrar al usuario"]));
            }
            
            return $response;
        } else {
            throw new MethodNotAllowedException(["Method Not Allowed"]);
        }

    }

    #[Route('user/eliminarJugador')]
    public function eliminarJugador(Connection $connection, Request $request)
    {
        if ($request->isMethod('delete')){
            $data = json_decode($request->getContent());
            $array = get_object_vars($data);
            $idJugador = intval($array["id"]);

            if($idJugador == 0){
                throw new BadRequestException("BadRequest");
            }

            $sql = "DELETE FROM jugadores WHERE idJugadores=:id";
            $reservaEliminada = $connection->executeStatement($sql, ['id' => $idJugador]);
            if ($reservaEliminada == 1){
                $response = new Response(json_encode(["operation" => true, "message" => "Se ha podido eliminar al usuario con exito"]));
            } else {
                $response = new Response(json_encode(["operation" => false, "message" => "No se ha podido eliminar al usuario"]));
            }

            return $response;
        } else {
            throw new MethodNotAllowedException(["Method Not Allowed"]);
        }
    }

    #[Route('user/modificarJugador')]
    public function modificarJugador(Connection $connection, Request $request)
    {
        if ($request->isMethod('put')){
            $data = json_decode($request->getContent());
            $array = get_object_vars($data);

            $idJugador = intval($array["idJugadores"]);
            $nombre = $array["Nombre"];
            $apellidos = $array["Apellidos"];
            $nombreUsuario = $array["Nombre_usuario"];
            $fotoPerfil = $array["Foto_perfil"];

            if($nombre == "" || $apellidos == "" || $nombreUsuario == "" || $idJugador == 0){
                throw new BadRequestException("BadRequest");
            }

            $sql = "UPDATE jugadores SET Nombre=:nombre, Apellidos=:apellidos, Nombre_usuario=:nombreUsuario, Foto_perfil=:fotoPerfil WHERE idJugadores=:id";
            $reservaModificada = $connection->executeStatement($sql, ['id' => $idJugador, 'nombre' => $nombre, 'apellidos' => $apellidos, 'nombreUsuario' => $nombreUsuario, 'fotoPerfil' => $fotoPerfil]);

            if ($reservaModificada == 1){
                $response = new Response(json_encode(["operation" => true, "message" => "Se ha podido modificar al usuario con exito"]));
            } else {
                $response = new Response(json_encode(["operation" => false, "message" => "No se ha podido modificar al usuario con exito"]));
            }

            return $response;
        } else {
            throw new MethodNotAllowedException(["Method Not Allowed"]);
        }
    }

    #[Route('/user/getGamesOwnedBy/{id}')]
    public function getGamesOwnedById(connection $connection, Request $request, $id): Response {
        if ($request->isMethod('get')) {
            $sql = $connection->fetchAllAssociative("SELECT Juego_idJuego FROM jugadores_has_juego WHERE Jugadores_idJugadores=$id");
            
            $gamesOwned = [];
            for ($i=0; $i < count($sql); $i++) {
                $idJuego = $sql[$i]["Juego_idJuego"];
                $sql2 = $connection->fetchAllAssociative("SELECT * FROM juego WHERE idJuego=$idJuego");
                array_push($gamesOwned, $sql2[0]);
            }
            
            return new Response(json_encode($gamesOwned));
            
        } else {
            throw new MethodNotAllowedException(["Method Not Allowed"]);
        }
    }

    #[Route('/user/addGame')]
    public function addGame(connection $connection, Request $request): Response {
        if ($request->isMethod('post')){
            $data = json_decode($request->getContent());
            $array = get_object_vars($data);

            $idJugador = intval($array['idJugador']);
            $idJuego = intval($array['idJuego']);

            if($idJugador <= 0 || $idJuego <= 0){
                throw new BadRequestException("BadRequest");
            }

            $sql = "INSERT INTO jugadores_has_juego (Jugadores_idJugadores, Juego_idJuego) VALUES (:idJugador, :idJuego)";           
            $addGame = $connection->executeStatement($sql, ['idJugador' => $idJugador, 'idJuego' => $idJuego]);

            if ($addGame == 1) {
                return new Response(json_encode(["operation" => true, "message" => "Juego añadido con exito"]));
            } else {
                return new Response(json_encode(["operation" => false, "message" => "No se ha añadido el juego"]));
            }
        } else {
            throw new MethodNotAllowedException(["Method Not Allowed"]);
        }
    }

    #[Route('/user/getGamesWishListedBy/{id}')]
    public function getGamesWishListedById(connection $connection, Request $request, $id): Response {
        if ($request->isMethod('get')) {
            $idListaDeseados = $id;
            $sql2 = $connection->fetchAllAssociative("SELECT Juego_idJuego FROM lista_deseados_has_juego WHERE Lista_Deseados_idLista_Deseados=$idListaDeseados");
            $gamesWishListed = [];
            for ($i=0; $i < count($sql2); $i++) {
                $idJuego = $sql2[$i]["Juego_idJuego"];
                $sql3 = $connection->fetchAllAssociative("SELECT * FROM juego WHERE idJuego=$idJuego");
                array_push($gamesWishListed, $sql3[0]);
            }
            return new Response(json_encode($gamesWishListed));
            
        } else {
            throw new MethodNotAllowedException(["Method Not Allowed"]);
        }
    }



    #[Route('/user/getCompraMadeBy/{id}')]
    public function getCompraMadeById(connection $connection, Request $request, $id): Response {
        if ($request->isMethod('get')) {
            $sql = $connection->fetchAllAssociative("SELECT idCompras FROM compras WHERE Jugadores_idJugadores=$id");
            $idCompra = $id[0];
            $sql2 = $connection->fetchAllAssociative("SELECT Juego_idJuego FROM compras_has_juego WHERE Compras_idCompras=$idCompra");
            $gamesPurchased = [];
            for ($i=0; $i < count($sql2); $i++) {
                $idJuego = $sql2[$i]["Juego_idJuego"];
                $sql3 = $connection->fetchAllAssociative("SELECT * FROM juego WHERE idJuego=$idJuego");
                array_push($gamesPurchased, $sql3[0]);
            }
            
            return new Response(json_encode($gamesPurchased));
            
        } else {
            throw new MethodNotAllowedException(["Method Not Allowed"]);
        }
    }

    #[Route('/user/desarrolladores')]
    public function getAllDesarrolladores(connection $connection, Request $request): Response{
        if ($request->isMethod('get')) {
            $users = $connection->fetchAllAssociative('SELECT * FROM desarrolladores');
        } else {
            throw new MethodNotAllowedException(["Method Not Allowed"]);
        }

        $response = new Response(json_encode($users));

        return $response;
    }

    #[Route('/user/desarrollador/{id}')]
    public function getDesarrolladorById(connection $connection, Request $request, $id): Response{
        if ($request->isMethod('get')) {
            $users = $connection->fetchAllAssociative("SELECT * FROM desarrolladores where idDesarrolladores=$id");
        } else {
            throw new MethodNotAllowedException(["Method Not Allowed"]);
        }

        $response = new Response(json_encode($users));

        return $response;
    }

    #[Route('/user/registrarDesarrollador')]
    public function registrarDesarrollador(Connection $connection, Request $request)
    {
        if ($request->isMethod('post')) {
            $data = json_decode($request->getContent());
            $array = get_object_vars($data);

            $email = $array["Email"];
            $nombre = $array["Nombre"];
            $apellidos = $array["Apellidos"];
            $nombreUsuario = $array["Nombre_usuario"];
            $password = $array["Contrasena"];
            $fotoPerfil = $array["Foto_perfil"];
            $nombreEditor = $array["Nombre_Editor"];


            if($email == "" || $nombre == "" || $apellidos == "" || $nombreUsuario == "" || $password == "" || $nombreEditor == ""){
                throw new BadRequestException("BadRequest");
            }

            $usersEmail = $connection->fetchAllAssociative("SELECT idDesarrolladores FROM desarrolladores where Email='$email'");
            $usersUser = $connection->fetchAllAssociative("SELECT idDesarrolladores FROM desarrolladores where Nombre_usuario='$nombreUsuario'");
            
            if ($usersEmail) {
                $response = new Response(json_encode(["operation" => false, "message" => "Email ya registrado"]));
                return $response;
            }

            if ($usersUser) {
                $response = new Response(json_encode(["operation" => false, "message" => "Usuario ya registrado"]));
                return $response;
            }

            $sql = "INSERT INTO desarrolladores (Email, Nombre, Apellidos, Nombre_usuario, Contrasena, Nombre_Editor, Foto_perfil) values (:email , :nombre, :apellidos, :nombreUsuario, :contrasena, :nombreEditor, :fotoPerfil)";
            $crearUsuario = $connection->executeStatement($sql, ['email' => $email, 'nombre' => $nombre, 'apellidos' => $apellidos, 'nombreUsuario' => $nombreUsuario, 'contrasena' => $password, 'nombreEditor' => $nombreEditor ,'fotoPerfil' => $fotoPerfil]);
            if ($crearUsuario == 1){
                $response = new Response(json_encode(["operation" => true, "message" => "Desarrollador registrado con exito"]));
            } else {
                $response = new Response(json_encode(["operation" => false, "message" => "No se ha podido registrar al desarrollador"]));
            }
            
            return $response;
        } else {
            throw new MethodNotAllowedException(["Method Not Allowed"]);
        }

    }

    #[Route('user/eliminarDesarrollador')]
    public function eliminarDesarrollador(Connection $connection, Request $request)
    {
        if ($request->isMethod('delete')){
            $data = json_decode($request->getContent());
            $array = get_object_vars($data);
            $idDesarrollador = intval($array["id"]);

            if($idDesarrollador == 0){
                throw new BadRequestException("BadRequest");
            }

            $sql = "DELETE FROM desarrolladores WHERE idDesarrolladores=:id";
            $reservaEliminada = $connection->executeStatement($sql, ['id' => $idDesarrollador]);
            if ($reservaEliminada == 1){
                $response = new Response(json_encode(["operation" => true, "message" => "Desarrollador eliminado con exito"]));
            } else {
                $response = new Response(json_encode(["operation" => false, "message" => "No se ha podido eliminar al desarrollador"]));
            }

            return $response;
        } else {
            throw new MethodNotAllowedException(["Method Not Allowed"]);
        }
    }

    #[Route('user/modificarDesarrollador')]
    public function modificarDesarrollador(Connection $connection, Request $request)
    {
        if ($request->isMethod('put')){
            $data = json_decode($request->getContent());
            $array = get_object_vars($data);

            $idDesarrolladores = intval($array["idDesarrolladores"]);
            $nombre = $array["Nombre"];
            $apellidos = $array["Apellidos"];
            $nombreUsuario = $array["Nombre_usuario"];
            $fotoPerfil = $array["Foto_perfil"];

            if($nombre == "" || $apellidos == "" || $nombreUsuario == "" || $idDesarrolladores == 0){
                throw new BadRequestException("BadRequest");
            }

            $sql = "UPDATE desarrolladores SET Nombre=:nombre, Apellidos=:apellidos, Nombre_usuario=:nombreUsuario ,Foto_perfil=:fotoPerfil WHERE idDesarrolladores=:id";
            $reservaModificada = $connection->executeStatement($sql, ['id' => $idDesarrolladores, 'nombre' => $nombre, 'apellidos' => $apellidos, 'nombreUsuario' => $nombreUsuario, 'fotoPerfil' => $fotoPerfil]);

            if ($reservaModificada == 1){
                $response = new Response(json_encode(["operation" => true, "message" => "Datos del desarrollador modificados con exito"]));
            } else {
                $response = new Response(json_encode(["operation" => false, "message" => "No se ha podido modificar los datos del desarrollador"]));
            }

            return $response;
        } else {
            throw new MethodNotAllowedException(["Method Not Allowed"]);
        }
    }

    #[Route('/user/getGamesMadeBy/{id}')]
    public function getGamesMadeById(connection $connection, Request $request, $id): Response{
        if ($request->isMethod('get')) {
            $users = $connection->fetchAllAssociative("SELECT * FROM juego where Desarrolladores_idDesarrolladores=$id");
        } else {
            throw new MethodNotAllowedException(["Method Not Allowed"]);
        }

        $response = new Response(json_encode($users));

        return $response;
    }

    #[Route('/user/administradores')]
    public function getAllAdministradores(connection $connection, Request $request): Response{
        if ($request->isMethod('get')) {
            $users = $connection->fetchAllAssociative('SELECT * FROM administradores');
        } else {
            throw new MethodNotAllowedException(["Method Not Allowed"]);
        }

        $response = new Response(json_encode($users));

        return $response;
    }

    #[Route('/user/administrador/{id}')]
    public function getAdministradorById(connection $connection, Request $request, $id): Response{
        if ($request->isMethod('get')) {
            $users = $connection->fetchAllAssociative("SELECT * FROM administradores where idAdministradores=$id");
        } else {
            throw new MethodNotAllowedException(["Method Not Allowed"]);
        }

        $response = new Response(json_encode($users));

        return $response;
    }

    #[Route('/user/registrarAdministrador')]
    public function registrarAdministrador(Connection $connection, Request $request)
    {
        if ($request->isMethod('post')) {
            $data = json_decode($request->getContent());
            $array = get_object_vars($data);

            $email = $array["Email"];
            $nombre = $array["Nombre"];
            $apellidos = $array["Apellidos"];
            $nombreUsuario = $array["Nombre_usuario"];
            $password = $array["Contrasena"];
            $fotoPerfil = $array["Foto_perfil"];

            if($email == "" || $nombre == "" || $apellidos == "" || $nombreUsuario == "" || $password == ""){
                throw new BadRequestException("BadRequest");
            }

            $usersEmail = $connection->fetchAllAssociative("SELECT idAdministradores FROM administradores where Email='$email'");
            $usersUser = $connection->fetchAllAssociative("SELECT idAdministradores FROM administradores where Nombre_usuario='$nombreUsuario'");
            
            if ($usersEmail) {
                $response = new Response(json_encode(["operation" => false, "message" => "Email ya registrado"]));
                return $response;
            }

            if ($usersUser) {
                $response = new Response(json_encode(["operation" => false, "message" => "Usuario ya registrado"]));
                return $response;
            }

            $sql = "INSERT INTO administradores (Email, Nombre, Apellidos, Nombre_usuario, Contrasena, Foto_perfil) values (:email , :nombre, :apellidos, :nombreUsuario, :contrasena, :fotoPerfil)";
            $crearUsuario = $connection->executeStatement($sql, ['email' => $email, 'nombre' => $nombre, 'apellidos' => $apellidos, 'nombreUsuario' => $nombreUsuario, 'contrasena' => $password, 'fotoPerfil' => $fotoPerfil]);
            if ($crearUsuario == 1){
                $response = new Response(json_encode(["operation" => true, "message" => "Administrador registrado con exito"]));
            } else {
                $response = new Response(json_encode(["operation" => false, "message" => "No se ha podido registrar al administrador"]));
            }
            
            return $response;
        } else {
            throw new MethodNotAllowedException(["Method Not Allowed"]);
        }

    }

    #[Route('user/eliminarAdministrador')]
    public function eliminarAdministrador(Connection $connection, Request $request)
    {
        if ($request->isMethod('delete')){
            $data = json_decode($request->getContent());
            $array = get_object_vars($data);
            $idAdministrador = intval($array["id"]);

            if($idAdministrador == 0){
                throw new BadRequestException("BadRequest");
            }

            $sql = "DELETE FROM administradores WHERE idAdministradores=:id";
            $reservaEliminada = $connection->executeStatement($sql, ['id' => $idAdministrador]);
            if ($reservaEliminada == 1){
                $response = new Response(json_encode(["operation" => true, "message" => "Desarrollador eliminado con exito"]));
            } else {
                $response = new Response(json_encode(["operation" => false, "message" => "No se ha podido eliminar al administrador"]));
            }

            return $response;
        } else {
            throw new MethodNotAllowedException(["Method Not Allowed"]);
        }
    }

    #[Route('user/modificarAdministrador')]
    public function modificarAdministrador(Connection $connection, Request $request)
    {
        if ($request->isMethod('put')){
            $data = json_decode($request->getContent());
            $array = get_object_vars($data);

            $idAdministrador = intval($array["idAdministradores"]);
            $nombre = $array["Nombre"];
            $apellidos = $array["Apellidos"];
            $nombreUsuario = $array["Nombre_usuario"];
            $fotoPerfil = $array["Foto_perfil"];

            if($nombre == "" || $apellidos == "" || $nombreUsuario == "" || $idAdministrador == 0){
                throw new BadRequestException("BadRequest");
            }

            $sql = "UPDATE administradores SET Nombre=:nombre, Apellidos=:apellidos, Nombre_usuario=:nombreUsuario, Foto_perfil=:fotoPerfil WHERE idAdministradores=:id";
            $reservaModificada = $connection->executeStatement($sql, ['id' => $idAdministrador, 'nombre' => $nombre, 'apellidos' => $apellidos, 'nombreUsuario' => $nombreUsuario,  'fotoPerfil' => $fotoPerfil]);

            if ($reservaModificada == 1){
                $response = new Response(json_encode(["operation" => true, "message" => "Administrador modificado con exito"]));
            } else {
                $response = new Response(json_encode(["operation" => false, "message" => "No se ha podido modificar al Administrador"]));
            }

            return $response;
        } else {
            throw new MethodNotAllowedException(["Method Not Allowed"]);
        }
    }

    #[Route('user/loginJugador/{email}')]
    public function loginJugador(Connection $connection, Request $request, $email){
        if ($request->isMethod('get')) {
            $users = $connection->fetchAllAssociative("SELECT idJugadores FROM jugadores WHERE Email='$email'");
        } else {
            throw new MethodNotAllowedException(["Method Not Allowed"]);
        }

        $response = new Response(json_encode($users));

        return $response;
    }

    #[Route('user/loginDesarrollador/{email}')]
    public function loginDesarrollador(Connection $connection, Request $request, $email){
        if ($request->isMethod('get')) {
            $users = $connection->fetchAllAssociative("SELECT idDesarrolladores FROM desarrolladores WHERE Email='$email'");
        } else {
            throw new MethodNotAllowedException(["Method Not Allowed"]);
        }

        $response = new Response(json_encode($users));

        return $response;
    }

    #[Route('user/loginAdministrador/{email}')]
    public function loginAdministrador(Connection $connection, Request $request, $email){
        if ($request->isMethod('get')) {
            $users = $connection->fetchAllAssociative("SELECT idAdministradores FROM administradores WHERE Email='$email'");
        } else {
            throw new MethodNotAllowedException(["Method Not Allowed"]);
        }

        $response = new Response(json_encode($users));

        return $response;
    }
}
