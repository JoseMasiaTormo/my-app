<?php

namespace App\Controller;

use App\Entity\Editorial;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Libro;
use App\Form\EditorialFormType;
use App\Form\LibroFormType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class LibroController extends AbstractController
{
    #[Route('/libro/nuevo', name: 'nuevo_libro')]
    public function nuevo(ManagerRegistry $doctrine, Request $request) {
        $libro = new Libro();

        $formulario = $this->createForm(LibroFormType::class, $libro);
        $formulario->handleRequest($request);

            if ($formulario->isSubmitted() && $formulario->isValid()) {
                $libro = $formulario->getData();
                $entityManager = $doctrine->getManager();
                $entityManager->persist($libro);
                $entityManager->flush();
                return $this->redirectToRoute('ficha_libro', ["codigo" => $libro->getId()]);
            }
        return $this->render('nuevo.html.twig', array(
            'formulario' => $formulario->createView()
        ));
    }

    #[Route('/libro/editar/{codigo}', name: 'editar_libro')]
    public function editar(ManagerRegistry $doctrine, Request $request, $codigo) {
        $repositorio = $doctrine->getRepository(Libro::class);
        $libro = $repositorio->find($codigo);

        if ($libro) {
            $formulario = $this->createForm(LibroFormType::class, $libro);
            $formulario->handleRequest($request);

            if ($formulario->isSubmitted() && $formulario->isValid()) {
                $libro = $formulario->getData();
                $entityManager = $doctrine->getManager();
                $entityManager->persist($libro);
                $entityManager->flush();
                return $this->redirectToRoute('ficha_libro', ["codigo" => $libro->getId()]);
            }
            return $this->render('editar.html.twig', array(
                'formulario' => $formulario->createView()
            ));
        } else {
            return $this->render('ficha_libro.html.twig', [
                'libro' => NULL
            ]);
        }
    }

    #[Route('/libro/insertarConEd/{nombre}/{autor}/{anyo}/{editorial}', name:'insertar_libro_conEd')]
    public function insertarConEditorial(ManagerRegistry $doctrine, $nombre, $autor, $anyo, $editorial) {
        $entityManager = $doctrine->getManager();
        $ed = new Editorial();
        $ed->setNombre($editorial);

        $libro = new Libro();
        $libro->setNombre($nombre);
        $libro->setAutor($autor);
        $libro->setAnyo($anyo);
        $libro->setEditorial($ed);

        $entityManager->persist($ed);
        $entityManager->persist($libro);

        try {
            $entityManager->flush();
            return new Response("Libro insertado");
        } catch (\Exception $e) {
            return new Response("Error insertando objetos");
        }
    }

    #[Route('/libro/nueva_ed', name:'nueva_ed')]
    public function nueva_ed(ManagerRegistry $doctrine, Request $request) {
        $ed = new Editorial();

        $formulario = $this->createForm(EditorialFormType::class, $ed);
        $formulario->handleRequest($request);

            if ($formulario->isSubmitted() && $formulario->isValid()) {
                $ed = $formulario->getData();
                $entityManager = $doctrine->getManager();
                $entityManager->persist($ed);
                $entityManager->flush();
            }
            return $this->render('nueva_ed.html.twig', array(
                'formulario' => $formulario->createView()
        ));
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

    #[Route('/libro', name: 'ficha_todos_libros')]
    public function allLibros(ManagerRegistry $doctrine): Response {
        $repositorio = $doctrine->getRepository(Libro::class);
        $libros = $repositorio->findAll();
        return $this->render('lista_libros.html.twig', [
            'libros' => $libros
        ]);
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
