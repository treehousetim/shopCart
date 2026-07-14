<?php namespace treehousetim\shopCart\test;

use treehousetim\shopCart\cart;
use treehousetim\shopCart\cartStorageInterface;

// a no-op storage handler so cart methods that delegate to storage
// (e.g. emptyCart) can be exercised without a real session
class fixtureStorage implements cartStorageInterface
{
	public function loadCart( cart $cart ) : cartStorageInterface
	{
		return $this;
	}
	//------------------------------------------------------------------------
	public function emptyCart( cart $cart ) : cartStorageInterface
	{
		return $this;
	}
	//------------------------------------------------------------------------
	public function saveItems( array $items ) : cartStorageInterface
	{
		return $this;
	}
	//------------------------------------------------------------------------
	public function saveData( array $data ) : cartStorageInterface
	{
		return $this;
	}
	//------------------------------------------------------------------------
	public function finalize( cart $cart ) : cartStorageInterface
	{
		return $this;
	}
}
