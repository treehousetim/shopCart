<?php namespace treehousetim\shopCart;

class cartStorageSession implements cartStorageInterface
{
	protected function checkSess()
	{
		if( session_status() != PHP_SESSION_ACTIVE )
		{
			throw new Exception( 'No Active Session', Exception::noActiveSessionErrorCode );
		}
	}
	//------------------------------------------------------------------------
	public function emptyCart( cart $cart ) : cartStorageInterface
	{
		$this->checkSess();

		$_SESSION['cart_items'] = [];
		$_SESSION['cart_data'] = [];

		return $this;
	}
	//------------------------------------------------------------------------
	public function saveItems( array $items )
	{
		$this->checkSess();

		$_SESSION['cart_items'] = [];

		foreach( $items as $item )
		{
			$_SESSION['cart_items'][] = ['id' => $item->getProduct()->getId(), 'qty' => $item->getQty()];
		}

		return $this;
	}
	//------------------------------------------------------------------------
	public function saveData( array $data )
	{
		$this->checkSess();

		$_SESSION['cart_data'] = [];

		foreach( $data as $type => $_data )
		{
			$_SESSION['cart_data'][$type] = $data->getForStorage();
		}

		return $this;
	}
	//------------------------------------------------------------------------
	public function finalize( cart $cart ) : cartStorageInterface
	{
		// do nothing

		return $this;
	}
	//------------------------------------------------------------------------
	public function loadCart( cart $cart ) : cartStorageInterface
	{
		$this->checkSess();

		$sess = $_SESSION['cart_items'] ?? array();

		foreach( $sess as $item )
		{
			if( $cart->getCatalog()->hasProductId( $item['id'] ) )
			{
				$product = $cart->getCatalog()->getProductById( $item['id'] );
				$cartItem = new cartItem( $product );
				$cartItem->setQty( $item['qty'] );
				$cart->addItem( $cartItem );
			}
		}

		$data = $_SESSION['cart_data'] ?? array();

		foreach( $data as $type => $cartData )
		{
			$cartData->setType( $type );
			$cart->addData( $cartData );
		}

		return $this;
	}
}