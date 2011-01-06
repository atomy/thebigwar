<?php

class Item
{

    protected $item_info = false;

    protected $items_instance = false;

    function __construct( $id )
    {
        $this->items_instance = Classes::Items();
        $this->item = $id;
        
        $this->item_info = $this->items_instance->getItemInfo( $this->item );
    }

    function getInfo( $field = false )
    {
        if ( $this->item_info === false )
            return false;
        
        if ( $field === false )
            return $this->item_info;
        elseif ( isset( $this->item_info[$field] ) )
            return $this->item_info[$field];
        return false;
    }

    function checkDependencies( $user, $run_eventhandler = true )
    {
        if ( ! $user->getStatus() )
            return false;
        
        $deps = $this->getDependencies();
        foreach ( $deps as $id => $min_level ) {
            if ( $user->getItemLevel( $id, false, $run_eventhandler ) < $min_level )
                return false;
        }
        return true;
    }

    function getDependencies( )
    {
        $deps = $this->getInfo( 'deps' );
        $deps_assoc = array();
        foreach ( $deps as $dep ) {
            $dep = explode( '-', $dep, 2 );
            if ( count( $dep ) != 2 )
                continue;
            $deps_assoc[$dep[0]] = $dep[1];
        }
        return $deps_assoc;
    }

    function getType( )
    {
        return $this->items_instance->getItemType( $this->item );
    }
}

class Items
{

    private $elements = array();

    private $instance = false;

    function __construct( )
    {
        $refresh = false;
        if ( ! file_exists( global_setting( "DB_ITEM_DB" ) ) )
            $refresh = true;
        else {
            $mtimes = array();
            if ( is_file( global_setting( "DB_ITEMS" ) . '/gebaeude' ) )
                $mtimes[] = filemtime( global_setting( "DB_ITEMS" ) . '/gebaeude' );
            if ( is_file( global_setting( "DB_ITEMS" ) . '/forschung' ) )
                $mtimes[] = filemtime( global_setting( "DB_ITEMS" ) . '/forschung' );
            if ( is_file( global_setting( "DB_ITEMS" ) . '/roboter' ) )
                $mtimes[] = filemtime( global_setting( "DB_ITEMS" ) . '/roboter' );
            if ( is_file( global_setting( "DB_ITEMS" ) . '/schiffe' ) )
                $mtimes[] = filemtime( global_setting( "DB_ITEMS" ) . '/schiffe' );
            if ( is_file( global_setting( "DB_ITEMS" ) . '/verteidigung' ) )
                $mtimes[] = filemtime( global_setting( "DB_ITEMS" ) . '/verteidigung' );
            if ( count( $mtimes ) > 0 && max( $mtimes ) > filemtime( global_setting( "DB_ITEM_DB" ) ) )
                $refresh = true;
        }
        
        if ( $refresh )
            $this->refreshItemDatabase();
        
        $this->elements = unserialize( file_get_contents( global_setting( "DB_ITEM_DB" ) ) );
        $this->elements['ids'] = array();
        foreach ( $this->elements as $type => $elements ) {
            foreach ( $elements as $id => $info )
                $this->elements['ids'][$id] = & $this->elements[$type][$id];
        }
    }

    function getItemsList( $type = false )
    {
        echo "getItemsList for type: '".$type."'";
        print_r($this->elements);
        if ( $type === false )
            $type = 'ids';
        
        if ( ! isset( $this->elements[$type] ) )
            return false;
        return array_keys( $this->elements[$type] );
    }

    function getItemInfo( $id, $type = false )
    {
        if ( $type === false )
            $type = 'ids';
        
        if ( ! isset( $this->elements[$type] ) || ! isset( $this->elements[$type][$id] ) )
            return false;
        return $this->elements[$type][$id];
    }

    function getItemType( $id )
    {
        foreach ( $this->elements as $type => $elements ) {
            if ( $type == 'ids' )
                continue;
            if ( isset( $elements[$id] ) )
                return $type;
        }
        return false;
    }

    function getName( )
    { # Needed for instances
        return 'items';
    }

    function readonly( )
    { # Needed for instances
        return false;
    }

    function refreshItemDatabase( )
    {
        $items = array( 'gebaeude' => array(), 'forschung' => array(), 'roboter' => array(), 'schiffe' => array(), 'verteidigung' => array(), 'ids' => array() );
        if ( is_file( global_setting( "DB_ITEMS" ) . '/gebaeude' ) && is_readable( global_setting( "DB_ITEMS" ) . '/gebaeude' ) ) {
            $fh = fopen( global_setting( "DB_ITEMS" ) . '/gebaeude', 'r' );
            fancy_flock( $fh, LOCK_SH );
            while ( $item = preg_replace( "/^(.*)(\r\n|\r|\n)$/", "$1", fgets( $fh, 65536 ) ) ) {
                $item = explode( "\t", $item );
                if ( count( $item ) < 8 )
                    continue;
                $items['gebaeude'][$item[0]] = array( 'name' => $item[1], 'ress' => explode( '.', $item[2] ), 'time' => $item[3], 'deps' => explode( ' ', $item[4] ), 'prod' => explode( '.', $item[5] ), 'fields' => $item[6], 'caption' => parseItemDescription( $item[7] ) );
                if ( trim( $item[4] ) == '' )
                    $items['gebaeude'][$item[0]]['deps'] = array();
            }
            flock( $fh, LOCK_UN );
            fclose( $fh );
        }
        
        if ( is_file( global_setting( "DB_ITEMS" ) . '/forschung' ) && is_readable( global_setting( "DB_ITEMS" ) . '/forschung' ) ) {
            $fh = fopen( global_setting( "DB_ITEMS" ) . '/forschung', 'r' );
            fancy_flock( $fh, LOCK_SH );
            while ( $item = preg_replace( "/^(.*)(\r\n|\r|\n)$/", "$1", fgets( $fh, 65536 ) ) ) {
                $item = explode( "\t", $item );
                if ( count( $item ) < 6 )
                    continue;
                $items['forschung'][$item[0]] = array( 'name' => $item[1], 'ress' => explode( '.', $item[2] ), 'time' => $item[3], 'deps' => explode( ' ', trim( $item[4] ) ), 'caption' => parseItemDescription( $item[5] ) );
                if ( trim( $item[4] ) == '' )
                    $items['forschung'][$item[0]]['deps'] = array();
            }
            flock( $fh, LOCK_UN );
            fclose( $fh );
        }
        
        if ( is_file( global_setting( "DB_ITEMS" ) . '/roboter' ) && is_readable( global_setting( "DB_ITEMS" ) . '/roboter' ) ) {
            $fh = fopen( global_setting( "DB_ITEMS" ) . '/roboter', 'r' );
            fancy_flock( $fh, LOCK_SH );
            while ( $item = preg_replace( "/^(.*)(\r\n|\r|\n)$/", "$1", fgets( $fh, 65536 ) ) ) {
                $item = explode( "\t", $item );
                if ( count( $item ) < 6 )
                    continue;
                $items['roboter'][$item[0]] = array( 'name' => $item[1], 'ress' => explode( '.', $item[2] ), 'time' => $item[3], 'deps' => explode( ' ', trim( $item[4] ) ), 'caption' => parseItemDescription( $item[5] ) );
                if ( trim( $item[4] ) == '' )
                    $items['roboter'][$item[0]]['deps'] = array();
            }
            flock( $fh, LOCK_UN );
            fclose( $fh );
        }
        
        if ( is_file( global_setting( "DB_ITEMS" ) . '/schiffe' ) && is_readable( global_setting( "DB_ITEMS" ) . '/schiffe' ) ) {
            $fh = fopen( global_setting( "DB_ITEMS" ) . '/schiffe', 'r' );
            fancy_flock( $fh, LOCK_SH );
            while ( $item = preg_replace( "/^(.*)(\r\n|\r|\n)$/", "$1", fgets( $fh, 65536 ) ) ) {
                $item = explode( "\t", $item );
                if ( count( $item ) < 12 )
                    continue;
                $items['schiffe'][$item[0]] = array( 'name' => $item[1], 'ress' => explode( '.', $item[2] ), 'time' => $item[3], 'deps' => explode( ' ', $item[4] ), 'trans' => explode( '.', $item[5] ), 'att' => $item[6], 'def' => $item[7], 'speed' => $item[8], 'types' => explode( ' ', $item[9] ), 'tri' => $item[10], 'caption' => parseItemDescription( $item[11] ) );
                $items['schiffe'][$item[0]]['mass'] = $items['schiffe'][$item[0]]['tri'];
                if ( trim( $item[4] ) == '' )
                    $items['schiffe'][$item[0]]['deps'] = array();
            }
        }
        
        if ( is_file( global_setting( "DB_ITEMS" ) . '/verteidigung' ) && is_readable( global_setting( "DB_ITEMS" ) . '/verteidigung' ) ) {
            $fh = fopen( global_setting( "DB_ITEMS" ) . '/verteidigung', 'r' );
            fancy_flock( $fh, LOCK_SH );
            while ( $item = preg_replace( "/^(.*)(\r\n|\r|\n)$/", "$1", fgets( $fh, 65536 ) ) ) {
                $item = explode( "\t", $item );
                if ( count( $item ) < 8 )
                    continue;
                $items['verteidigung'][$item[0]] = array( 'name' => $item[1], 'ress' => explode( '.', $item[2] ), 'time' => $item[3], 'deps' => explode( ' ', $item[4] ), 'att' => $item[5], 'def' => $item[6], 'caption' => parseItemDescription( $item[7] ) );
                if ( trim( $item[4] ) == '' )
                    $items['verteidigung'][$item[0]]['deps'] = array();
            }
            flock( $fh, LOCK_UN );
            fclose( $fh );
        }
        
        $fh = fopen( global_setting( "DB_ITEM_DB" ), 'a+' );
        if ( ! $fh )
            return false;
        if ( ! fancy_flock( $fh, LOCK_EX ) )
            return false;
        
        fseek( $fh, 0, SEEK_SET );
        ftruncate( $fh, 0 );
        fwrite( $fh, serialize( $items ) );
        
        flock( $fh, LOCK_UN );
        fclose( $fh );
    }
}

function parseItemDescription( $description )
{
    $description = preg_replace( "/\\\\[\\\\n]/e", "parseItemDescriptionChar('$0')", $description );
    return $description;
}

function parseItemDescriptionChar( $char )
{
    if ( $char == '\\n' )
        return "\n";
    if ( $char == '\\\\' )
        return '\\';
    return $char;
}

function makeItemsString( $items, $html = true )
{
    $array = array();
    foreach ( $items as $id => $count ) {
        if ( $count <= 0 )
            continue;
        $item_obj = Classes::Item( $id );
        if ( $html )
            $array[] = utf8_htmlentities( $item_obj->getInfo( 'name' ) ) . ': ' . ths( $count );
        else
            $array[] = $item_obj->getInfo( 'name' ) . ': ' . ths( $count, true );
    }
    return implode( ', ', $array );
}
?>
