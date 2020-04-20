<?php

namespace App\Controller;

use App\Entity\UserDemand;
use App\Form\UserDemandType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\Entity\UserDemand as userDemandHelper;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserDemandController extends AbstractController
{

    private $userDemandHelper;

    public function __construct(UserDemandHelper $userDemandHelper)
    {
        $this->userDemandHelper = $userDemandHelper;
    }

    /**
     * @Route("/user/demand/list", name="user_demand_list")
     */
    public function index()
    {
        if (!$this->isGranted('ROLE_ADMIN'))
        {
            $this->addFlash('error','Il faut être administrateur pour voir les demandes de comptes');
            return $this->redirectToRoute('home');
        }

        $usersDemands = $this->getDoctrine()
            ->getRepository(UserDemand::class)
            ->findAll();

        return $this->render('user_demand/list.html.twig', [
            'userDemands' => $usersDemands
        ]);
    }

    /**
     * @Route("/demand-access", name="user_demand_access")
     */
    public function userDemand(Request $request)
    {
        $userDemand = new UserDemand();
        $form = $this->createForm(UserDemandType::class, $userDemand);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $result = $this->userDemandHelper->createUserDemand($userDemand);
            if (!isset($result['error_message'])) {
                $this->addFlash('success','Nous avons pris en compte votre demande. Si elle est acceptée, vous recevrez un email de confirmation');
                return $this->redirectToRoute('security_login');
            } else {
                if (isset($result['error_forms'])) {
                    foreach($result['error_forms'] as $key => $value) {
                        $form->get($key)->addError(new FormError($value));
                    }
                }
                $this->addFlash('error',$result['error_message']);
            }
        }

        return $this->render('user_demand/index.html.twig',[
            'formDemand' => $form->createView()
        ]);
    }

    /* AJAX PART */
    /**
     * @Route("/user/demand/valid", name="ajax_user_demand_valid")
     * @Route("/user/demand/decline", name="ajax_user_demand_decline")
     * @Method({"POST"})
     */
    public function transform(Request $request, UserDemand $userDemand = null, $id = null)
    {
        if (!$this->isGranted('ROLE_ADMIN'))
        {
            return new JsonResponse(array('error_message' => 'Il faut être administrateur pour valider/invalider les demandes de comptes'));
        }

        $id = $request->request->get('id');
        $routeName = $request->get('_route');
        if ($routeName == 'ajax_user_demand_valid') {
            $result = $this->userDemandHelper->transformDemandToAccount(array($id));
            if (isset($result['error_message'])) {
                return new JsonResponse($result);
            } else {
                return new JsonResponse(array('message' => 'La demande a été acceptée et le compte a bien été crée', 'id' => $id, 'action' => 'valider'));
            }
        } else {
            try {
                $userDemand = $this->getDoctrine()
                    ->getRepository(UserDemand::class)
                    ->find($id);

                if (empty($userDemand)) {
                    return new JsonResponse(array('error_message' => 'Impossible de trouver la demande d\'accés dans la base de données'));
                }

                $this->getDoctrine()
                    ->getManager()
                    ->remove($userDemand);

                $this->getDoctrine()
                    ->getManager()
                    ->flush();

            return new JsonResponse(array('message' => 'La demande a bien été supprimée', 'id' => $id, 'action' => 'refuser'));
            } catch (Exception $e) {
                return new JsonResponse(array('error_message' => 'Erreur lors de la suppression de la demande d\'accés'));
            }
        }
    }
}
