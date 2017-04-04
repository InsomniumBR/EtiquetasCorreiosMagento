<?php
class FabioSantos_EtiquetaEndereco_Block_Printetiqueta extends Mage_Core_Block_Template
{
	public function getLabelsData()
	{
		$labelsData = array();
		
		foreach ($this->order_ids as $item) {
			$order = $this->getOrder($item);
			$shippingAddress = $this->getShippingAddress($order->getShippingAddressId());
			$shipmentCollection = Mage::getResourceModel('sales/order_shipment_collection')
				->setOrderFilter($order)
				->load();
				
			$tracking = "";
			$trackingBarcode = "";
			
			foreach ($shipmentCollection as $shipment){
				// This will give me the shipment IncrementId, but not the actual tracking information.
				foreach($shipment->getAllTracks() as $tracknum)
				{
					$tracking .= $tracknum->getNumber();
					break;
				}
			}
		
			$shipping = $order->getShippingDescription();
			
			if($shipping)
			{
				$shipping = strtoupper($shipping);
				if(strpos($shipping, 'PAC') !== false) $shipping = 'PAC'; 
				else if(strpos($shipping, 'SEDEX') !== false) $shipping = 'SEDEX'; 
				else if(strpos($shipping, 'PROMOCIONAL') !== false) $shipping = 'PROMOCIONAL'; 
				else $shipping = substr($shipping,0,10); 
			}
			
			if($tracking)
				$trackingBarcode = $this->getBarcode($tracking);
			
			$labelsData[] = array(
										'name1' => $shippingAddress->getName(),
										'name2' => '',
										'fone' => '',//$shippingAddress->getTelephone()
										'street' => $shippingAddress->getStreet(1),
										'num' => $shippingAddress->getStreet(2),
										'compl' => $shippingAddress->getStreet(3),
										'bairro' => $shippingAddress->getStreet(4),
										'city' => $shippingAddress->getCity(),
										'uf' => $shippingAddress->getRegion(),
										'zipcode' => $shippingAddress->getPostcode(),
										'zipbarcode' => $this->getBarcode($shippingAddress->getPostcode()),
										'order' => $order->getIncrementId(),
										'orderbarcode' => $this->getBarcode($order->getIncrementId()),
										'tracking' => $tracking,
										'shipping' => $shipping,
										'trackingbarcode' => $trackingBarcode
									);
		} 
		return $labelsData;
	}

	public function getLabelsRem()
	{
		$data = Mage::getStoreConfig('fabiosantos_etiquetaendereco/remetente');

		$labelsRem = array(
								'name1' => $data['name1'],
								'name2' => $data['name2'],
								'fone' => $data['fone'],
								'address' => $data['address'],
								'compl' => $data['endcomp'],
								'bairro' => $data['bairro'],
								'zipcode' => $data['cep'],
								'city' => $data['city'],
		);
		
		return $labelsRem;
	}
	
	public function getDeclContAR()
	{
		return Mage::getStoreConfig('fabiosantos_etiquetaendereco/aviso_receb/conteudo');
	}
	
	public function getOrder($orderId)
	{
		return Mage::getModel('sales/order')->load($orderId);
	}
	
	public function getShippingAddress($shippingAddressId)
	{
		return Mage::getModel('sales/order_address')->load($shippingAddressId);
	}
	
	public function getBarcode($postcode)
	{
		$postcode = str_replace('-','',$postcode);
		$barcode = Mage::getModel('etiquetaendereco/phpcode128')->phpCode128($postcode);
		
		return $barcode;
	}
		
	public function getPagesCount()
	{
		return ceil($this->getLabelsCount() / $this->getLabelsPage());
	}
	
	public function getARPagesCount()
	{
		return ceil($this->getLabelsCount() / 2);
	}
	
	public function getLabelsCount()
	{
		return count($this->order_ids);
	}
}
