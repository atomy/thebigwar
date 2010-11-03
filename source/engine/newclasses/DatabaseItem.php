<?php

/**
 *
 * $Id$

 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author firstname and lastname of author, <author@example.org>
 */

/**
 * Short description of class DatabaseItem
 *
 * @access public
 * @author firstname and lastname of author, <author@example.org>
 */
abstract class IDatabaseItem
{
    /**
     * Short description of attribute pDatabase
     *
     * @access public
     * @var pointer
     */
    private $pDatabase = null;

    /**
     * Short description of method SaveToDatabase
     *
     * @abstract
     * @access private
     * @author firstname and lastname of author, <author@example.org>
     * @return Boolean
     */
    public abstract function SaveToDatabase();

    /**
     * Short description of method LoadFromDatabase
     *
     * @abstract
     * @access private
     * @author firstname and lastname of author, <author@example.org>
     * @return Boolean
     */
    public abstract function LoadFromDatabase();

} /* end of class DatabaseItem */