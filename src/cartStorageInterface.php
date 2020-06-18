<?php namespace treehousetim\shopCart;

interface cartStorageInterface
{
	public function loadCart( cart $cart ) : cartStorageInterface;
	public function emptyCart( cart $cart ) : cartStorageInterface;

	public function saveItems( array $items ) : cartStorageInterface;
	public function saveData( array $data ) : cartStorageInterface;

	// used to clean up after all storage is complete
	public function finalize( cart $cart ) : cartStorageInterface;
}