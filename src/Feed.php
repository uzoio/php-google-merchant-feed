<?php

namespace Vitalybaev\GoogleMerchant;

use Sabre\Xml\Service as SabreXmlService;

class Feed
{
	const GOOGLE_MERCHANT_XML_NAMESPACE = 'http://base.google.com/ns/1.0';

	/**
	 * Feed title.
	 *
	 * @var string
	 */
	protected $title;

	/**
	 * Link to the store
	 *
	 * @var string
	 */
	protected $link;

	/**
	 * Feed description
	 *
	 * @var string
	 */
	protected $description;

	/**
	 * Feed items
	 *
	 * @var Product[]
	 */
	protected $items = [];

	/**
	 * Feed constructor.
	 *
	 * @param string $title
	 * @param string $link
	 * @param string $description
	 */
	public function __construct($title, $link, $description)
	{
		$this->title = $title;
		$this->link = $link;
		$this->description = $description;
		$this->xmlService = new SabreXmlService();
	}

	/**
	 * Adds product to feed.
	 *
	 * @param $product
	 */
	public function addProduct($product)
	{
		$this->items[] = $product;
	}

	/**
	 * Generate string representation of this feed.
	 *
	 * @return string
	 */
	public function build()
	{
		$output = $this->startDocument();
		$output .= $this->writeElements();
		$output .= $this->endDocument();

		return $output;
	}

	public function startDocument()
	{
		$this->initXMLService();
		$this->initXMLWriter();
		$namespace = $this->formatNamespace();
		$this->xmlService->namespaceMap[static::GOOGLE_MERCHANT_XML_NAMESPACE] = 'g';

		$xmlStructure = [];

		if (!empty($this->title)) {
			$xmlStructure[] = [
				'title' => $this->title,
			];
		}

		if (!empty($this->link)) {
			$xmlStructure[] = [
				'link' => $this->link,
			];
		}

		if (!empty($this->description)) {
			$xmlStructure[] = [
				'description' => $this->description,
			];
		}

		$this->xmlWriter->openMemory();
		$this->xmlWriter->setIndent(true);
		$this->xmlWriter->startDocument();
		$this->xmlWriter->startElement('rss');
		$this->xmlWriter->startElement('channel');
		$this->xmlWriter->write($xmlStructure);

		return $this->xmlWriter->outputMemory(true);
	}

	public function writeElements()
	{
		$xmlStructure = [];

		$namespace = $this->formatNamespace();

		foreach ($this->items as $item) {
			$this->xmlWriter->writeElement('item', $item->getPropertiesXmlStructure($namespace));
		}

		$this->items = [];

		return $this->xmlWriter->outputMemory(true);
	}

	public function endDocument()
	{
		$this->xmlWriter->endElement();
		$this->xmlWriter->endElement();

		return $this->xmlWriter->outputMemory(true);
	}

	public function initXMLService(): void
	{
		$this->xmlService = new SabreXmlService();
	}

	public function initXMLWriter(): void
	{
		$this->xmlWriter = $this->xmlService->getWriter();
	}

	public function formatNamespace(): string
	{
		return '{' . static::GOOGLE_MERCHANT_XML_NAMESPACE . '}';
	}
}
