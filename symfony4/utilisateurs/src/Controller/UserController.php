<?php

namespace App\Controller;

use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use App\Service\Entity\User as UserHelper;
use App\Entity\User;
use App\Form\UserType;
use Exception;
use Knp\Component\Pager\PaginatorInterface;

class UserController extends AbstractController
{
    /* ADMIN PART */

    /**
     * @Route("/user/list/{page}", name="admin_user_list")
     */
    public function list($page = null, PaginatorInterface $paginatorInterface, UserHelper $userHelper)
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error','Il faut être administrateur pour pouvoir accéder à cette page');
            return $this->redirectToRoute('home');
        }

        if (empty($page)) {
            $page = 1;
        }

        $fields = $userHelper->getArrayFields('user');

        $users = $this->getDoctrine()
            ->getRepository(User::class)
            ->findAll();

        $usersPagination = $paginatorInterface->paginate(
            $users,
            $page,
            10
        );

        return $this->render('user/list.html.twig',[
            'fields' => $fields,
            'users' => $usersPagination
        ]);
    }

    /**
     * @Route("/user/create", name="admin_user_create")
     * @Route("/user/edit/{id}", name="admin_user_edit")
     */
    public function edit(Request $request, UserHelper $userHelper, User $user = null, $id = null)
    {
        // Si jamais le parameter converter n'a rien donné
        if (empty($user)) {
            // Et si jamais on a pas d'id
            if (empty($id)) {
                // C'est qu'on essaie de créer un compte. Si on est pas admin, on met un message d'erreur
                if (!$this->isGranted('ROLE_ADMIN')) {
                    $this->addFlash('error','Il faut être administrateur pour pouvoir créer un compte');
                    return $this->redirectToRoute('home');
                // Si on est admin, on instancie notre objet User
                } else {
                    $user = new User();
                    $new = true;
                }
                // Si jamais on a le paramètre id mais qu'on a pas de User, c'est que le comtpe avec l'id en question n'existe pas
            } else {
                // On notifie l'utilisateur et on le renvoie vers la home page
                $this->addFlash('error','Le compte que vous essayez de modifier n\'existe pas');
                return $this->redirectToRoute('home');
            }
        // Si l'auto wiring a bien fonctionné, c'est qu'on veut modifier un utilisateur
        } else {
            $new = false;
            // On récupére l'user courant
            $currentUser = $this->getUser();
            // Si les deux ids sont différents
            if ($currentUser->getId() != $id) {
                // On regarde si l'utilisateur courant est admin
                if (!$this->isGranted('ROLE_ADMIN')) {
                    // Si c'est pas le cas, il n'a pas le droit de modifier le compte d'un autre donc on le redirige vers la homepage
                    $this->addFlash('error','Il faut être admin pour pouvoir modifier le compte d\'un autre utilisateur');
                    return $this->redirectToRoute('home');
                }
            }
        }

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($form->get('password')->getData()) {
                $result = $userHelper->updatePassword($user,$form->get('password')->getData());
            } else {
                $result = $userHelper->updateUser($user);
            }

            if (!isset($result['error_message'])) {
                if ($new) {
                    $this->addFlash('success','Le compte a été crée avec succés');
                } else {
                    $this->addFlash('success','Le compte a été modifié avec succés');
                }
                //return $this->redirectToRoute('home');
            } else {
                if (isset($result['error_forms'])) {
                    foreach($result['error_forms'] as $key => $value) {
                        $form->get($key)->addError(new FormError($value));
                    }
                }
                $this->addFlash('error',$result['error_message']);
            }
        }

        return $this->render('user/edit.html.twig',[
            'formUser' => $form->createView(),
            'user' => $user
        ]);
    }

    /**
     * @Route("/user/delete/{id}", name="admin_user_delete")
     */
    public function delete(User $user, $id = null)
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error','Vous devez être administrateur pour supprimer un utilisateur');
            return $this->redirectToRoute('admin_user_list');
        }

        if (empty($id) || empty($user)) {
            $this->addFlash('error','Impossible de trouver l\'utilisateur que vous souhaitez supprimer');
            return $this->redirectToRoute('admin_user_list');
        }

        try {
            $this->getDoctrine()
                ->getManager()
                ->remove($user);

            $this->getDoctrine()
                ->getManager()
                ->flush();

            $this->addFlash('success','L\'utilisateur a bien été supprimé');
            return $this->redirectToRoute('admin_user_list');

        } catch(Exception $e) {
            $this->addFlash('error','Une erreur est survenue lors de la suppression de l\'utilisateur en base de données. Veuillez réessayer plus tard ou contacter un administrateur');
            return $this->redirectToRoute('admin_user_list');
        }
    }

    /* AJAX PART */

    /**
     * @Route("/ajax/user/isFieldTaken", name="ajax_user_field_taken")
     * @Method({"POST"})
     */
    public function isUsernameTake(Request $request, UserHelper $userHelper)
    {
        $field = $request->request->get('field');
        $value = $request->request->get('value');
        $demand = $request->request->get('demand');

        if (empty($field) || empty($value) || empty($demand)) {
            return new JsonResponse(false);
        }

        return new JsonResponse($userHelper->isFieldTaken($field,$value,$demand));
    }
}
