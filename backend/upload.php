<?php
/**
* e107 Tracker
*
* @author Lee Howarth <mostvotedplayer@gmail.com>
*/

/**
* Upload
*/
class Upload
{
    /**
    * Name
    *
    * @var string
    */
    protected $name;

    /**
    * Size
    *
    * @var integer
    */
    protected $size;

    /**
    * Mime type
    *
    * @var string
    */
    protected $type;

    /**
    * Path
    *
    * @var string
    */
    protected $tmp_name;

    /**
    * Error
    *
    * @var integer
    */
    protected $error;

    /**
    * Init.
    *
    * @return void
    */
    public function __construct( $name )
    {
        if ( is_string( $name ) )
        {
             if ( array_key_exists( $name, $_FILES ) && is_uploaded_file( $_FILES[$name]['tmp_name'] ) )
             {
                  array_walk( $_FILES[$name], function( $value, $index )
                  {
                     $this->$index = $value;
                  });
                  return;
             }
        }

        $this->error = 4;
    }

    /**
    * Get name.
    *
    * @return string
    */
    public function getName()
    {
        return mb_ereg_replace( '[\\\\/:*?"<>|]', null, $this->name );
    }

    /**
    * Get size.
    *
    * @return integer
    */
    public function getSize()
    { 
        return $this->size;
    }
 
    /**
    * Get mime type.
    * 
    * @return string
    */
    public function getType()
    {
        if ( $mime = shell_exec( 'file -b --mime-type ' . escapeshellarg( $this->tmp_name ) ) )
        {
             return trim( $mime );
        }
        return null;
    }

    /**
    * Get path.
    *
    * @return string
    */
    public function getPath()
    {
        return $this->tmp_name;
    }

    /**
    * Get error.
    *
    * @return integer
    */
    public function getError()
    {
        return $this->error;
    }

    /**
    * Get data.
    * 
    * @return string|null
    */
    public function getData()
    {
        try
        {
            $file = new SPLFileObject( $this->tmp_name, 'r' );
            $data = '';
            foreach ( $file as $line )
            {
                $data .= $line;
            }
            return $data;
        }
        catch ( Exception $e )
        {

        }
        return null;
    }
}
