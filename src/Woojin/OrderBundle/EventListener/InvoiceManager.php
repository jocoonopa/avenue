<?php

// namespace Woojin\OrderBundle\EventListener;

// use Doctrine\ORM\Event\LifecycleEventArgs;
// use Woojin\OrderBundle\Entity\Invoice;
// use Symfony\Component\Serializer\Exception\Exception;
// use Symfony\Component\DependencyInjection\ContainerInterface;
// use Symfony\Component\Security\Core\SecurityContext;
// use Woojin\OrderBundle\Entity\Orders;

// class InvoiceManager
// {
//   const OUT_STORE = 2;
//   const COMPLETE = 2; 

//   protected $container;

//   public function __construct(ContainerInterface $container)
//   {
//     $this->container = $container;
//   }

//   public function postPersist(LifecycleEventArgs $args)
//   {
//     $order = $args->getEntity();

//     // 若不是 Orders 得實體則不動作
//     if (!($order instanceof Orders)) {   
//     	return;
//     }

//     // 若不是販售類型訂單或已經屬於某發票
//     if (
//       $order->getOrdersKind()->getType() !== self::OUT_STORE ||
//       $order->getInvoice() instanceof Invoice
//     ) {
//       return;
//     }
    
//     $em = $args->getEntityManager();
    

//     $invoice = new Invoice;
//     $invoice
//       ->setCustom( $order->getCustom() )
//       ->setStore( $this->container->get('security.token_storage')->getToken()->getUser()->getStore() )
//       ->setHasPrint(false)
//     ;

//     $em->persist($invoice);
    

//     // 綁定定單所屬發票
//     $order->setInvoice($invoice);

//     $em->persist($order);
//     $em->flush();
//   }
// }