<?php

namespace Woojin\Utility\Avenue;

use Woojin\OrderBundle\Entity\Custom;
use Woojin\OrderBundle\Entity\Orders;
use Woojin\OrderBundle\Entity\Invoice;
use Woojin\GoodsBundle\Entity\Behalf;
use Woojin\StoreBundle\Entity\Store;

class Notifier
{
    protected $em;

    protected $mailer;

    protected $templating;

    protected $mailerUser;

    public function __construct(\Doctrine\ORM\EntityManager $em, \Swift_Mailer $mailer, \Symfony\Bundle\TwigBundle\TwigEngine $templating, $mailerUser)
    {
        $this->em = $em;
        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->mailerUser = $mailerUser;
    }

    public function behalfForCustom(Behalf $behalf)
    {
        $message = $this->genMessage(
            array($behalf->getCustom()->getEmail()), 
            '香榭國際代購通知信', 
            $this->templating->render(':Email:behalf.html.twig', array(
                'behalf' => $behalf
            ))
        );

        $this->mailer->send($message);

        return $this;
    }

    public function behalfForAvenue(Behalf $behalf)
    {
        $message = $this->genMessage(
            array($behalf->getCustom()->getEmail()), 
            '官網代購通知', 
            $this->templating->render(':Email:behalfEm.html.twig', array(
                'behalf' => $behalf
            ))
        );

        $this->mailer->send($message);

        return $this;
    }
    
    /**
     * 出貨
     *
     * @param \Woojin\OrderBundle\Entity\Invoice $invoice
     */
    public function ship(Invoice $invoice)
    {
        $message = $this->genMessage(
            $this->getMailsFromInvoice($invoice), 
            '官網訂單: ' . $invoice->getSn() . ' 出貨通知', 
            $this->templating->render('::Email/shipNotify.html.twig', array(
                'invoice' => $invoice
            ))
        );

        $this->mailer->send($message);

        $message = $this->genMessage(
            array($invoice->getCustom()->getEmail()), 
            '香榭國際精品訂單成立通知信', 
            $this->templating->render(':Email/Custom:ship.html.twig', array(
                'invoice' => $invoice
            ))
        );

        $this->mailer->send($message);

        return $this;
    }

    /**
     * 退款
     *
     * @param \Woojin\OrderBundle\Entity\Invoice $invoice
     */
    public function back(Invoice $invoice)
    {
        $message = $this->genMessage(
            $this->getMailsFromInvoice($invoice), 
            '官網訂單: ' . $invoice->getSn() . ' 退款通知', 
            $this->templating->render('::Email/chargeBack.html.twig', array(
                'invoice' => $invoice
            ))
        );

        $this->mailer->send($message);

        return $this;
    }

    /**
     * 重設密碼
     * 
     * @param \Woojin\OrderBundle\Entity\Custom $custom
     */
    public function forgot(Custom $custom)
    {
        $message = $this->genMessage(
            array($custom->getEmail()), 
            '香榭國際精品重設密碼通知信', 
            $this->templating->render('::Email/forgot.html.twig', array('custom' => $custom))
        );

        $this->mailer->send($message);

        return $this;
    }

    /**
     * 註冊通知信
     * 
     * @param \Woojin\OrderBundle\Entity\Custom $custom
     */
    public function register(Custom $custom)
    {
        $message = $this->genMessage(
            array($custom->getEmail()),
            '香榭國際精品註冊成功通知',
            $this->templating->render('::Email/register.html.twig', array('custom' => $custom))
        );

        $this->mailer->send($message);

        return $this;
    }

    /**
     * 開通帳號
     * 
     * @param \Woojin\OrderBundle\Entity\Custom $custom
     */
    public function active(Custom $custom)
    {
        $message = $this->genMessage(
            array($custom->getEmail()), 
            '香榭國際精品帳號開通', 
            $this->templating->render('::Email/active.html.twig', array('custom' => $custom))
        );

        $this->mailer->send($message);

        return $this;
    }

    /**
     * 發送操作通知
     * 
     * @param array {\Woojin\UserBundle\Entity\AvenueClue}
     * @param array {\Woojin\UserBundle\Entity\User}
     * @param \Woojin\StoreBundle\Entity\Store
     */
    public function clue(array $clues, array $users, Store $store)
    {
        $mails = array();

        $yesterday = new \DateTime();
        //$yesterday->modify('-1 DAY');

        foreach ($users as $user) {
            $mails[] = $user->getEmail();
        }

        $message = $this->genMessage(
            $mails, 
            $store->getName() . '操作記錄' . $yesterday->format('Y-m-d'), 
            $this->templating->render('::Email/clue.html.twig', array('clues' => $clues))
        );

        $this->mailer->send($message);

        return $this;
    }

    /**
     * 從發票取得負責人電子信箱清單
     * 
     * @param  \Woojin\OrderBundle\Entity\Invoice $invoice 
     * @return array           
     */
    protected function getMailsFromInvoice(Invoice $invoice)
    {
        $mails = array(
            $this->em->find('WoojinUserBundle:User', Avenue::USER_ENG),
            $this->em->find('WoojinUserBundle:User', Avenue::USER_BOSS)
        );

        $orders = $invoice->getOrders();

        $iterator = $orders->getIterator();

        while ($iterator->valid()) {
            $mails[] = $this->getMailFromOrder($iterator->current());

            $iterator->next();
        }

        return $mails;
    }

    /**
     * 從訂單取得負責人的mail
     *
     * @param \Woojin\OrderBundle\Entity\Orders $order
     */
    protected function getMailFromOrder(Orders $order)
    {
        $product = $order->getGoodsPassport();

        $store = $this->em->getRepository('WoojinStoreBundle:Store')->findOneBy(array('sn' => substr($product->getSn(), 0, 1)));

        return $store->getMail();
    }

    /**
     * 產生信件訊息
     * 
     * @param  array  $mails   收件人信箱清單
     * @param  string $subject 標題
     * @param  html/text $body    主要內容
     * @return object          
     */
    protected function genMessage(array $mails, $subject, $body)
    {
        return $this->mailer->createMessage()
                ->setSubject($subject)
                ->setFrom($this->mailerUser)
                ->setTo($mails)
                ->setBody($body, 'text/html')
            ;
    }
}