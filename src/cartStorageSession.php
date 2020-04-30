<?php namespace treehousetim\shopCart;

class cartStorageSession implements cartStorageInterface
{
	public function init( cart $cart ) : cartStorageInterface
	{
		$_SESSION['cart_items'] = [];
		$_SESSION['cart_data'] = [];
		return $this;
	}
	//------------------------------------------------------------------------
	public function saveCartItem( cartItem $item ) : cartStorageInterface
	{
		$_SESSION['cart_items'][] = ['id' => $item->getProduct()->getId(), 'qty' => $item->getQty()];

		return $this;
	}
	//------------------------------------------------------------------------
	public function saveCartData( cartData $data ) : cartStorageInterface
	{
		$_SESSION['cart_data'][] = $data;
		return $this;
	}
	//------------------------------------------------------------------------
	public function resetCartData()  : cartStorageInterface
	{
		$_SESSION['cart_data'] = array();
		return $this;
	}
	//------------------------------------------------------------------------
	public function loadCart( cart $cart ) : cartStorageInterface
	{
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

		foreach( $data as $cartData )
		{
			$cart->addData( $cartData );
		}

		return $this;
	}
}