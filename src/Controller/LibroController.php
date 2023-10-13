<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Libro;

class LibroController extends AbstractController
{  
    #[Route('/libro/insertar/{nombre}/{autor}/{año}', name:'insertar_libro')]
    public function insertar(ManagerRegistry $doctrine, $nombre, $autor, $año) {
        $entityManager = $doctrine->getManager();
        $libro = new Libro();
        $libro->setNombre($nombre);
        $libro->setAutor($autor);
        $libro->setAño($año);
        $entityManager->persist($libro);

        try {
            $entityManager->flush();
            return new Response("Libro insertado");
        } catch (\Exception $e) {
            return new Response("Error insertando objetos");
        }
    }

    #[Route('/libro/update/{id}/{nombre}', name: 'modificar_libro')]
    public function update(ManagerRegistry $doctrine, $id, $nombre): Response {
        $entityManager = $doctrine->getManager();
        $repositorio = $doctrine->getRepository(Libro::class);
        $libro = $repositorio->find($id);
        if ($libro) {
            $libro->setNombre($nombre);
            try {
                $entityManager->flush();
                return $this->render('ficha_libro.html.twig', [
                    'libro' => $libro
                ]);
            } catch (\Exception $e) {
                return new Response("Error insertando objetos");
            }
        } else {
            return $this->render('ficha_libro.html.twig', [
                'libro' => null
            ]);
        }
    }

    #[Route('/libro/delete/{id}', name: 'borrar_libro')]
    public function delete(ManagerRegistry $doctrine, $id): Response{
        $entityManager = $doctrine->getManager();
        $repositorio = $doctrine->getRepository(Libro::class);
        $libro = $repositorio->find($id);

        if ($libro) {
            try {
                $entityManager->remove($libro);
                $entityManager->flush();
                return new Response("Libro eliminado");
            } catch (\Exception $e) {
                return new Response("Error eliminando objeto");
            }
        } else {
            return $this->render('ficha_libro.html.twig', [
                'libro' => null
            ]);
        }
    }

    #[Route('/libro/{codigo}', name: 'ficha_libro')]
    public function index(ManagerRegistry $doctrine, $codigo): Response
    {
        $repositorio = $doctrine->getRepository(Libro::class);
        $libro = $repositorio->find($codigo);

        return $this->render('ficha_libro.html.twig', [
        'libro' => $libro
        ]);
    }

    #[Route('/libro/buscar/{texto}', name: 'buscar_libro')]
    public function buscar(ManagerRegistry $doctrine, $texto):Response {
        $repositorio = $doctrine->getRepository(Libro::class);
        $libros = $repositorio->findByName($texto);

        return $this->render('lista_libros.html.twig', [
            'libros' => $libros
        ]);
    }
}
