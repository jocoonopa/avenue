<?php

namespace Woojin\OrderBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Woojin\OrderBundle\Entity\Orders;
use Woojin\OrderBundle\Entity\Invoice;
use Woojin\StoreBundle\Entity\Auction;

/**
 * @Route("/print")
 */
class PrintController extends Controller
{
    /** 
     * @Route("/invoice/{id}/page/{page}", requirements={"page"="\d+"}, defaults={"page"=1}, name="invoice_print")
     * @ParamConverter("invoice", class="WoojinOrderBundle:Invoice")
     * 
     * @Template("WoojinOrderBundle:Print:keyhand.html.twig")
     */
    public function printInvoiceAction(Invoice $invoice, $page)
    {
        return array(
            'invoice' => $invoice,
            'page' => $page
        );
    }

    /**
     * @Route("/invoice/keybyhand", name="orders_keyinvoicebyhand")
     * @Method("GET")
     * @Template("WoojinOrderBundle:Print:keyhand.html.twig")
     */
    public function keyInvoiceByHandAction()
    {
        return array();
    }

    /**
     * @Route("/invoice/auction/{id}", name="print_invoice_auction")
     * @ParamConverter("auction", class="WoojinStoreBundle:Auction")
     * @Method("GET")
     * @Template("WoojinOrderBundle:Print:auction.certificate.html")
     */
    public function printAuctionInvoiceAction(Auction $auction)
    {
        return array('auction' => $auction);
    }

    /**
     * @Route("/invoice/printkeybyhand", name="orders_printkeybyhand")
     * @Method("POST")
     * @Template("WoojinOrderBundle:Print:keyhand.print.html.twig")
     */
    public function printKeyInvoiceByHandAction(Request $request)
    {
        $rows = array();

        foreach ($request->request->get('sn') as $key => $sn) {
            $rows[$key]['sn'] = $sn;

            $prices = $request->request->get('price');

            $rows[$key]['price'] = $prices[$key];

            $paids = $request->request->get('paid');

            $rows[$key]['paid'] = $paids[$key];

            $names = $request->request->get('name');

            $rows[$key]['name'] = $names[$key];

            $memos = $request->request->get('memo');

            $rows[$key]['memo'] = $memos[$key];
        }

        $date = $request->request->get('date');

        $totalRequired = 0;
        $totalPaid = 0;

        foreach ($rows as $row) {
            $totalRequired += $row['price'];
            $totalPaid += $row['paid'];
        }

        return array(
            'rows' => $rows, 
            'date' => new \DateTime($date),
            'totalRequired' => $totalRequired,
            'totalPaid' => $totalPaid,
            'invoiceId' => str_pad($request->request->get('invoice_id'), 6, '0', STR_PAD_LEFT),
            'storeSn' => $request->request->get('store_sn'),
            'currentPage' => $request->request->get('current_page')
        );
    }

    /**
     * @Route("/invoice/now/{key}", name="get_invoice_now", options={"expose"=true})
     * @Template("WoojinOrderBundle:Invoice:now.html.twig")
     * @Method("GET")
     */
    public function getInvoiceNowAction($key)
    {
        $em = $this->getDoctrine()->getManager();

        $qb = $em->createQueryBuilder();

        $qb
            ->select('o')
            ->from('WoojinOrderBundle:Orders', 'o')
            ->leftJoin('o.invoice', 'i')
            ->where($qb->expr()->eq('i.sn', $qb->expr()->literal($key)))
        ;

        $orderses = $qb->getQuery()->getResult();

        return array('orderses' => $orderses);
    }
}