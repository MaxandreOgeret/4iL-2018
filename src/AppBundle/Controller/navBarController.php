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

class navBarController extends Controller
{
    /**
     * @Route("/notmapped", name="notmapped")
     */
    public function notmappedAction() {
        return $this->render('error/notmapped.twig');
    }

}