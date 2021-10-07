<?php namespace treehousetim\shopCart;

class cart implements totalInterface
{
	protected $items = [];
	protected $data = [];

	public $productsByMetal = [];
	protected $totals = [];
	protected $metalsCollection;

	public $totalTypes = [];
	protected $totalTypeLoader;

	protected $catalog;
	protected $formatter;
	protected $storageHandler;

	public function __construct( catalog $catalog )
	{
		$this->catalog = $catalog;
	}
	//------------------------------------------------------------------------
	public function getCatalog() : catalog
	{
		return $this->catalog;
	}
	//------------------------------------------------------------------------
	public function setStorageHandler( cartStorageInterface $storageHandler ) : self
	{
		$this->storageHandler = $storageHandler;
		return $this;
	}
	//------------------------------------------------------------------------
	public function setFormatter( productAmountFormatter $formatter ) : self
	{
		$this->formatter = $formatter;
		return $this;
	}
	//------------------------------------------------------------------------
	public function setTotalTypeLoader( catalogTotalTypeLoaderInterface $loader ) : self
	{
		$this->typeLoader = $loader;
		$this->populateTotalTypes();
		return $this;
	}
	//------------------------------------------------------------------------
	public function getDistinctItemQty() : string
	{
		return count( $this->items );
	}
	//------------------------------------------------------------------------
	public function getTotalQty() : string
	{
		$total = 0;
		foreach( $this->items as $item )
		{
			$total += $item->getQty();
		}

		return $total;
	}
	//------------------------------------------------------------------------
	public function addItem( cartItem $item ) : self
	{
		$this->items[ $item->getProduct()->getId() ] = $item;

		return $this;
	}
	//------------------------------------------------------------------------
	public function removeItem( cartItem $item ) : self
	{
		unset($this->items[ $item->getProduct()->getId() ]);

		return $this;
	}
	//------------------------------------------------------------------------
	public function addProduct( product $product, $qty ) : self
	{
		if( $this->hasItemForProductId( $product->getId() ) )
		{
			$cartItem = $this->getItemByProductId( $product->getId() );
			$cartItem->addQty( $qty );
		}
		else
		{
			$cartItem = new cartItem( $product );
			$cartItem->addQty( $qty );
			$this->addItem( $cartItem );
		}

		return $this;
	}
	//------------------------------------------------------------------------
	public function emptyCart() : self
	{
		$this->data = [];
		$this->items = [];
		$this->storageHandler->emptyCart( $this );

		return $this;
	}
	//------------------------------------------------------------------------
	public function populateTotalTypes() : self
	{
		$this->typeLoader->resetType();
		$this->totalTypes = [];
		do
		{
			$type = $this->typeLoader->getType();
			$this->totalTypes[] = $type;
		} while( $this->typeLoader->nextType() );

		return $this;
	}
	//------------------------------------------------------------------------
	public function getTotalTypes() : array
	{
		$this->populateTotalTypes();
		return $this->totalTypes;
	}
	//------------------------------------------------------------------------
	public function getTotal( catalogTotalType $type ) : string
	{
		$total = 0;

		foreach( $this->items as $item )
		{
			$total = bcadd( $item->getTotalTypeAmount( $type ), $total );
		}

		return $total;
	}
	//------------------------------------------------------------------------
	public function getAmountOrdered( catalogTotalType $type ) : string 
	{
		$amountOrdered = 0;
		foreach ($this->items as $item) 
		{
			$amountOrdered = $item->getTotalAmount( $type );
		}
		return $amountOrdered;
	}
	//------------------------------------------------------------------------
	public function getAmountTotal( catalogTotalType $type ) : string
	{
		$totalAmount = 0;
		foreach ($this->items as $item) 
		{
			$totalAmount = bcadd($item->getTotalAmount( $type ), $totalAmount );
		}
		return $totalAmount;
	}
	//------------------------------------------------------------------------
	public function getAmountTotalFormatted( catalogTotalType $type )  : string
	{
		return $this->formatter->formatCartTotal( $type, $this->getAmountTotal( $type ) );	
	}
	//------------------------------------------------------------------------
	public function getTotalFormatted( catalogTotalType $type )  : string
	{
		return $this->formatter->formatCartTotal( $type, $this->getTotal( $type ) );
	}
	//------------------------------------------------------------------------
	public function formatTotalType( string $value, catalogTotalType $type )
	{
		//not used now
	}
	//------------------------------------------------------------------------
	public function requireProductId( $id )
	{
		if( ! $this->hasItemForProductId( $id ) )
		{
			throw new Exception( 'No Item', Exception::notItemErrorCode );
		}
	}
	//------------------------------------------------------------------------
	public function getItemByProductId( $id )
	{
		$this->requireProductId( $id );

		return $this->items[$id];
	}
	//------------------------------------------------------------------------
	public function hasItemForProductId( $id ) : bool
	{
		if( array_key_exists( $id, $this->items ) )
		{
			return true;
		}

		return false;
	}
	//------------------------------------------------------------------------
	public function updateItemQty( $id, $qty )
	{
		$this->requireProductId( $id );
		$this->getItemByProductId( $id )->updateQty( $qty );
	}
	//------------------------------------------------------------------------
	public function populate()
	{
		$model = new productModel();
		$rows = $model->fetchAll();

		foreach( $rows as $product )
		{
			$this->addItem( new cartItem( $product ) );
		}
	}
	//------------------------------------------------------------------------
	public function load() : self
	{
		$this->storageHandler->loadCart( $this );
		return $this;
	}
	//------------------------------------------------------------------------
	public function save()
	{
		$this->storageHandler->saveItems( $this->items );
		$this->storageHandler->saveData( $this->data );

		$this->storageHandler->finalize( $this );
	}
	//------------------------------------------------------------------------
	public function addData( iCartData $data ) : self
	{
		$this->data[$data->getType()]  = $data;	
		
		return $this;
	}
	//------------------------------------------------------------------------
	public function getCartData() : array
	{
		return $this->data;
	}
	//------------------------------------------------------------------------
	public function hasCartDataType( string $type ) : bool
	{
		return array_key_exists( $type, $this->data );
	}
	//------------------------------------------------------------------------
	public function getDataByType( string $type ) : iCartData
	{
		if( ! $this->hasCartDataType( $type ) )
		{
			throw new Exception( 'Invalid cart data type: ' . $type, Exception::invalidDataTypeErrorCode  );
		}

		return $this->data[$type];
	}
	//------------------------------------------------------------------------
	public function getCartItems() : array
	{
		return $this->items;
	}
}