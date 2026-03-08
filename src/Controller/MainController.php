<?php

namespace App\Controller;

use App\Entity\IpmReportByServerName;
use App\Repository\IpmReportByServerNameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Utils\Utils;
use Algo26\IdnaConvert\ToUnicode;

class MainController extends AbstractController
{
    //    ------------Временно------------------------
    private EntityManagerInterface $entityManager;
    function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
//    --------------------------------------------

    #[Route('/', methods: ['GET'])]
    public function indexAction(IpmReportByServerNameRepository $ipmReportByServerNameRepository): Response
    {
        $result = [];

        $result['servers'] = $ipmReportByServerNameRepository->findAllServers();

        $idn = new ToUnicode();

        $total = [
            'req_count' => 0,
            'error_count' => 0
        ];

        foreach ($result['servers'] as &$item) {
            if (stripos($item['server_name'], 'xn--') !== false) {
                $item['server_name'] = $idn->convertUrl($item['server_name']);
            }

            $item['req_per_sec'] = number_format($item['req_per_sec'], 3, ',', '');

            $total['req_count'] += $item['req_count'];
            $total['error_count'] += $item['error_count'];
        }

        $result['total'] = $total;
        $result['base_url'] = '/';
        // Временно нижняя строка, тест
        $result['menu'] = (new BeforeController($this->entityManager))->actionBefore();

        return $this->render('index.html.twig', $result);
    }
}