<?php namespace treehousetim\shopCart;

interface cartStorageInterface
{
	public function init( cart $cart ) : cartStorageInterface;
	public function saveCartItem( cartItem $item ) : cartStorageInterface;
	public function saveCartData( cartData $data ) : cartStorageInterface;
	public function loadCart( cart $cart ) : cartStorageInterface;
}