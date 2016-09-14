<?php

namespace Woojin\StoreBundle;

final class AuctionEvents
{
    const CREATE    = 'auction.create';
    const SOLD      = 'auction.sold';
    const PAY       = 'auction.pay';
    const ASSIGN    = 'auction.assign';
    const BACK      = 'auction.back';
    const CANCEL    = 'auction.cancel';
}