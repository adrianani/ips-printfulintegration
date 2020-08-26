<?php
/**
 * @brief		File Storage Extension: ProductImage
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @subpackage	Printful Commerce Integration
 * @since		20 Aug 2020
 */

namespace IPS\printfulintegration\extensions\core\FileStorage;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * File Storage Extension: ProductImage
 */
class _ProductImage
{
	protected static $databaseTable = 'printfulintegration_product_images';
	/**
	 * Count stored files
	 *
	 * @return	int
	 */
	public function count()
	{
		return \IPS\Db::i()->select( 'COUNT(*)', static::$databaseTable )->first();
	}
	
	/**
	 * Move stored files
	 *
	 * @param	int			$offset					This will be sent starting with 0, increasing to get all files stored by this extension
	 * @param	int			$storageConfiguration	New storage configuration ID
	 * @param	int|NULL	$oldConfiguration		Old storage configuration ID
	 * @throws	\UnderflowException					When file record doesn't exist. Indicating there are no more files to move
	 * @return	void|int							An offset integer to use on the next cycle, or nothing
	 */
	public function move( $offset, $storageConfiguration, $oldConfiguration=NULL )
	{
		$data = \IPS\Db::i()->select('*', 'printfulintegration_product_images', array(), 'id', array($offset, 1))->first();

		try
		{
			$file = \IPS\File::get( $oldConfiguration ?: 'printfulintegration_ProductImage', $data['image_location'] )->move( $storageConfiguration );
			
			if ( (string) $file != $data['image_location'] )
			{
				\IPS\Db::i()->update( static::$databaseTable, array( 'image_location' => (string) $file ), array( 'id=?', $data['id'] ) );
			}
		}
		catch( \Exception $e )
		{
		}
	}

	/**
	 * Check if a file is valid
	 *
	 * @param	string	$file		The file path to check
	 * @return	bool
	 */
	public function isValidFile( $file )
	{
		try
		{
			\IPS\Db::i()->select( '*', static::$databaseTable, array( 'image_location=?', (string) $file ) )->first();
			return TRUE;
		}
		catch ( \UnderflowException $e )
		{
			return FALSE;
		}
	}

	/**
	 * Delete all stored files
	 *
	 * @return	void
	 */
	public function delete()
	{
		foreach( \IPS\Db::i()->select( '*', static::$databaseTable, "image_location IS NOT NULL" ) as $product )
		{
			try
			{
				\IPS\File::get( 'printfulintegration_ProductImage', $product['image_location'] )->delete();
			}
			catch( \Exception $e ){}
		}
	}    
	
	public function fixUrls( $offset )
    {
        $cache = \IPS\Db::i()->select( '*', static::$databaseTable, array(), 'id ASC', array( $offset, 1 ) )->first();
        try
        {
            if ( $new = \IPS\File::repairUrl( $cache['image_location'] ) )
            {
                \IPS\Db::i()->update( static::$databaseTable, array( 'image_location' => (string) $new ), array( 'id=?', $cache['image_location'] ) );
            }
        }
        catch( \Exception $e )
        {
            /* Any issues are logged and the \IPS\Db::i()->update not run as the exception is thrown */
        }
    }
}