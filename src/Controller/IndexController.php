<?php
namespace App\Controller;
use App\Entity\Category;
use App\Entity\Produits;
use App\Form\CategoryType;
use App\Form\ProduitsType;
use App\Entity\PriceSearch;
use App\Form\PriceSearchType;
use App\Entity\CategorySearch;
use App\Entity\PropertySearch;
use App\Form\CategorySearchType;
use App\Form\PropertySearchType;
use App\Entity\Form;
use App\Form\FormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class IndexController extends AbstractController
{
/**
* @Route("/Tailleur")
*/
public function home()
{
    $article = ['Produits', 'Clients','Tissus','Commande'];
    
    return $this->render('articles/Acceuil.html.twig',['articles' => $article]);
}
/**
* @Route("/",name="article_list")
*/
public function produits(Request $request)
{
    $propertySearch = new PropertySearch();
    $form = $this->createForm(PropertySearchType::class,$propertySearch);
    $form->handleRequest($request);
//initialement le tableau des articles est vide,
//c.a.d on affiche les articles que lorsque l'utilisateur
//clique sur le bouton rechercher
    $articles= [];
    if($form->isSubmitted() && $form->isValid()) {
//on récupère le nom d'article tapé dans le formulaire
    $Nom = $propertySearch->getNom();
    if ($Nom!="")
//si on a fourni un nom d'article on affiche tous les articles ayant ce nom
    $articles= $this->getDoctrine()->getRepository(Produits::class)->findBy(['Nom' => $Nom] );
    else
//si si aucun nom n'est fourni on affiche tous les articles
    $articles= $this->getDoctrine()->getRepository(Produits::class)->findAll();
}
    return $this->render('articles/Produits.html.twig',[ 'form' =>$form->createView(), 'articles' => $articles]);
    $articles= $this->getDoctrine()->getRepository(Produits::class)->findAll();
    return $this->render('articles/Produits.html.twig',['articles'=> $articles]);
    
}


/**
* @Route("/Ajouter_article", name="new_article")
* Method({"GET", "POST"})
*/
public function new(Request $request) {
    $article = new Produits();
    $form = $this->createForm(ProduitsType::class,$article);
    $form->handleRequest($request);
    if($form->isSubmitted() && $form->isValid()) {
    $article = $form->getData();
    $entityManager = $this->getDoctrine()->getManager();
    $entityManager->persist($article);
    $entityManager->flush();
    return $this->redirectToRoute('article_list');
    }
    return $this->render('articles/new.html.twig',['form' => $form->createView()]);
    }

/**
* @Route("/produits/{id}", name="article_show")
*/
public function show($id) {
    $article = $this->getDoctrine()->getRepository(Produits::class)->find($id);
    return $this->render('articles/show.html.twig',['article' => $article]);
    }

    /**
* @Route("/produits/edit/{id}", name="edit_article")
* Method({"GET", "POST"})
*/
public function edit(Request $request, $id) {
$article = new Produits();
$article = $this->getDoctrine()->getRepository(Produits::class)->find($id);
$form = $this->createForm(ProduitsType::class,$article);
$form->handleRequest($request);
if($form->isSubmitted() && $form->isValid()) {
$entityManager = $this->getDoctrine()->getManager();
$entityManager->flush();
return $this->redirectToRoute('article_list');
}
return $this->render('articles/edit.html.twig', ['form' =>$form->createView()]);
    }

    /**
* @Route("/produits/delete/{id}",name="delete_article")
* @Method({"DELETE"})
*/
public function delete(Request $request, $id) {
    $article = $this->getDoctrine()->getRepository(Produits::class)->find($id);
    $entityManager = $this->getDoctrine()->getManager();
    $entityManager->remove($article);
    $entityManager->flush();
    $response = new Response();
    $response->send();
    return $this->redirectToRoute('article_list');
    }

    /**
* @Route("/category/newCat", name="new_category")
* Method({"GET", "POST"})
*/
public function newCategory(Request $request) {
    $category = new Category();
    $form = $this->createForm(CategoryType::class,$category);
    $form->handleRequest($request);
    if($form->isSubmitted() && $form->isValid()) {
    $article = $form->getData();
    $entityManager = $this->getDoctrine()->getManager();
    $entityManager->persist($category);
    $entityManager->flush();
    }
    return $this->render('articles/newCategory.html.twig',['form'=>$form->createView()]);
    }

    /**
* @Route("/art_cat/", name="article_par_cat")
* Method({"GET", "POST"})
*/
public function articlesParCategorie(Request $request) {
    $categorySearch = new CategorySearch();
    $form = $this->createForm(CategorySearchType::class,$categorySearch);
    $form->handleRequest($request);
    $articles= [];
    if($form->isSubmitted() && $form->isValid()) {
        $category = $categorySearch->getCategory();
        if ($category!="")
        $articles= $category->getProduits();
        else
        $articles= $this->getDoctrine()->getRepository(Produits::class)->findAll();
        }
        return $this->render('articles/articlesParCategorie.html.twig',['form' => $form->createView(),'articles' => $articles]);
        }

/**
* @Route("/art_prix/", name="article_par_prix")
* Method({"GET","POST"})
*/
public function articlesParPrix(Request $request)
{
$priceSearch = new PriceSearch();
$form = $this->createForm(PriceSearchType::class,$priceSearch);
$form->handleRequest($request);
$articles= [];
if($form->isSubmitted() && $form->isValid()) {
$minPrice = $priceSearch->getMinPrice();
$maxPrice = $priceSearch->getMaxPrice();
$articles= $this->getDoctrine()-> getRepository(Produits::class)->findByPriceRange($minPrice,$maxPrice);
}
return $this->render('articles/articlesParPrix.html.twig',[ 'form' =>$form->createView(), 'articles' => $articles]);
}

/**
* @Route("/Djezner", name="commande")
* Method({"GET","POST"})
*/
public function command(Request $request)
{
//$form = new Form();
//$form = $this->createForm(FormType::class,$form);
$article = new Form();
$form = $this->createForm(FormType::class,$article);
$form->handleRequest($request);
if($form->isSubmitted() && $form->isValid()) {
$article = $form->getData();
$entityManager = $this->getDoctrine()->getManager();
$entityManager->persist($article);
$entityManager->flush();
return $this->redirectToRoute('commande');
}
return $this->render('articles/formulaire.html.twig',['form' => $form->createView()]);
//return $this->render('articles/formulaire.html.twig',[ 'form' =>$form->createView()]);
}

/**
* @Route("/Taille_Gabonaise", name="client")
* Method({"GET","POST"})
*/
public function client(EntityManagerInterface $em)
{
//$em = $this->getDoctrine()->getManager();
//$repo=$em-> getRepository('App\Entity\Form');
//$forms=$repo-> findAll();
//return $this->render('articles/client.html.twig',[ 'forms' =>$forms]);
$forms= $this->getDoctrine()->getRepository(Form::class)->findAll();
return $this->render('articles/client.html.twig',['forms'=> $forms]);
}

}
