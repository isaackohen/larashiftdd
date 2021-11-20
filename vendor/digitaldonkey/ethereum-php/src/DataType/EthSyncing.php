<?php
/**
 * @file
 * This is a file generated by scripts/generate-complex-datatypes.php.
 *
 * DO NOT EDIT THIS FILE.
 *
 * @ingroup generated
 * @ingroup dataTypesComplex
 */
namespace Ethereum\DataType;

/**
 * Ethereum data type EthSyncing.
 * 
 * Generated by scripts/generate-complex-datatypes.php based on resources/ethjs-schema.json.
 */
class EthSyncing extends EthDataType {

	/**
	 * Value for 'startingBlock'.
	 */
	public $startingBlock;

	/**
	 * Value for 'currentBlock'.
	 */
	public $currentBlock;

	/**
	 * Value for 'highestBlock'.
	 */
	public $highestBlock;

	/**
	 * @param EthQ $startingBlock
	 * @param EthQ $currentBlock
	 * @param EthQ $highestBlock
	 */
	public function __construct(EthQ $startingBlock = null, EthQ $currentBlock = null, EthQ $highestBlock = null) {
		$this->startingBlock = $startingBlock;  
		$this->currentBlock = $currentBlock;  
		$this->highestBlock = $highestBlock;
	}

	/**
	 * Returns a name => type array.
	 */
	public static function getTypeArray() {
		return array( 
			'startingBlock' => 'EthQ',
			'currentBlock' => 'EthQ',
			'highestBlock' => 'EthQ',
		);
	}

	/**
	 * Returns array with values.
	 */
	public function toArray() {
		$return = [];
		(!is_null($this->startingBlock)) ? $return['startingBlock'] = $this->startingBlock->hexVal() : NULL; 
		(!is_null($this->currentBlock)) ? $return['currentBlock'] = $this->currentBlock->hexVal() : NULL; 
		(!is_null($this->highestBlock)) ? $return['highestBlock'] = $this->highestBlock->hexVal() : NULL; 
		return $return;
	}
}