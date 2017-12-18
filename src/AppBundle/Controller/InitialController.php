<?php
/**
 * Created by PhpStorm.
 * User: Mackou
 * Date: 23/09/2017
 * Time: 18:07
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Article;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class InitialController extends Controller
{


    /**
     * @Route("/", name="homepage")
     */
    public function initialAction() {
        return $this->render(':initial:index.html.twig');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("ajaxerror", name="ajax-error")
     */
    public function ajaxErrorAction()
    {
        return $this->render("error/ajax.html.twig");
    }

}