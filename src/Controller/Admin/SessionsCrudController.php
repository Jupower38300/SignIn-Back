<?php
namespace App\Controller\Admin;

use App\Entity\Sessions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class SessionsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Sessions::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        // On surchargera le template d'index pour inclure la modal
        return $crud
            ->overrideTemplates([
                'crud/index' => 'admin/sessions/index.html.twig',
            ]);
    }

    public function configureActions(Actions $actions): Actions
    {
        // Définition de l'action personnalisée « QR Code »
        $qrCodeAction = Action::new('qrCode', 'QR Code', 'fa fa-qrcode')
        ->linkToUrl('#')
        ->addCssClass('btn btn-info');

        return $actions
            ->add(Crud::PAGE_INDEX, $qrCodeAction);
    }

    /**
     * @Route("/admin/sessions/{id}/qr-code", name="admin_session_qrcode", methods={"GET"})
     */
    public function qrCodeAjax(Sessions $session): JsonResponse
    {
        // Génère l'URL vers la route de génération du QR (ici 'app_qr_code_generator')
        $qrCodeUrl = $this->generateUrl('app_qr_code_generator', [
            'data' => $session->getId()
        ]);
        return $this->json(['url' => $qrCodeUrl]);
    }
}
