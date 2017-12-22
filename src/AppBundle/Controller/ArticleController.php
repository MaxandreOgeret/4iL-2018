<?php
/**
 * Created by PhpStorm.
 * User: Mackou
 * Date: 23/09/2017
 * Time: 18:07
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Article;
use AppBundle\Form\ArticleType;
use AppBundle\Manager\ArticleManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Filesystem\Filesystem;

class ArticleController extends Controller
{
    public $am;

    public function __construct(ArticleManager $am)
    {
        $this->am = $am;
    }

    public function identityChecker()
    {
        $securityContext = $this->container->get('security.authorization_checker');
        if (!$securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedException();
        }
    }

    /**
     * @Route("getarticles", name="getarticles")
     */
    public function getAllAction() {
        $em = $this->get('doctrine.orm.entity_manager');
        $articles = $em->getRepository('AppBundle:Article')->getArticles();
        $toRender = "";

        $soutiens = $this->render(':elements:help.html.twig')->getContent();

        foreach ($articles as $article) {
            /** @var DateTime $date */
            $date = $article['date'];
            $render = $this->render(
                ':elements:card.html.twig',
                [
                    'text' => $article['text'],
                    'title' => $article['title'],
                    'id' => $article['id'],
                    'image' => $article['image'],
                    'date' => $date->format('d/m/Y'),
                ]
            );
            $toRender = $toRender."\n".$render->getContent();
        }

        return new JsonResponse($soutiens.$toRender);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("add", name="add")
     */
    public function addAction()
    {
        $this->identityChecker();

        $article = new Article();

        $form = $this->createForm
        (
            ArticleType::class,
            $article,
            [
                'action' => $this->generateUrl('handlearticleform'),
            ]
        )
            ->createView();
        return $this->render(
            'add/addpage.html.twig',
            [
                'form' => $form,
                'action' => 'Création',
            ]
        );
    }

    /**
     * @param Request $request
     * @param Article|null $article
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route("handle-article-form/{article}", name="handlearticleform", )
     */
    public function handleFormAction(Request $request, Article $article = null)
    {
        $this->identityChecker();

        if (is_null($article)) {
            $article = new Article();
        }

        $form = $this->createForm
        (
            ArticleType::class,
            $article,
            [
                'action' => $this->generateUrl('handlearticleform'),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $time = new \DateTime('now');

            /**
             * filename = the file filename if the file exists. If not $filename = null;
             */
            $fileName = $this->am->handleBase64Image($form->get('base64')->getData());

            if (!is_null($fileName)) {
                $article->setImage($fileName);
                } else {
                    $article->setImage(null);
                    $article->setImagePath(null);
                }

            $article->setDate($time);
            $article->setText($article->getText());

            $em = $this->getDoctrine()->getManager();
            $em->persist($article);
            $em->flush();

            return $this->redirect($this->generateUrl('homepage'));
        } else {
            throw new Exception('Form is not valid or not submitted');
        }
    }

    /**
     * @param $article Article
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("edit/{id}", name="editarticle")
     */
    public function editArticleAcion(Article $article)
    {
        $this->identityChecker();

        $form = $this->createForm
        (
            ArticleType::class,
            $article,
            [
                'action' => $this->generateUrl('handlearticleform', ['article'=>$article->getId()]),
            ]
        );

        return $this->render(
            'add/addpage.html.twig',
            [
                'form' => $form->createView(),
                'action' => 'Edition'
            ]
        );
    }

    /**
     * @param Article $article
     * @return JsonResponse
     * @Route("/del/{id}", name="delarticle", defaults={"id"=null}, condition="request.isXmlHttpRequest()")
     */
    public function delArticleAction(Article $article=null)
    {
        $this->identityChecker();

        $fs = new Filesystem();
        $fs->remove([$this->getParameter('image_directory').$article->getImage()]);
        $em = $this->getDoctrine()->getManager();
        $em->remove($article);
        $em->flush();
        return new JsonResponse('ok');
    }

    /**
     * @param Article $article
     * @return JsonResponse
     * @Route("/delimg/{id}", name="delimg", condition="request.isXmlHttpRequest()")
     */
    public function delImageAction(Article $article)
    {
        $this->identityChecker();

        $image = $article->getImage();
        $fs = new Filesystem();
        $fs->remove([$this->getParameter('image_directory').$image]);

        $article->setImagePath(null);
        $article->setImage(null);

        $em = $this->getDoctrine()->getManager();
        $em->persist($article);
        $em->flush();

        return new JsonResponse('ok');
    }
}