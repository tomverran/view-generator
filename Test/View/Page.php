<?php
namespace tomverran\Viewgen\Test\View;

/**
 * Generated on 17 Nov 2014
 */
class Page
{

    /**
     * Seen on line(s): 2
     */
    private $heading = null;

    /**
     * Seen on line(s): 4
     */
    private $copyrightDate = null;

    /**
     * Construct this Page
     */
    public function __construct($heading, $copyrightDate)
    {
        $this->heading = $heading;
        $this->copyrightDate = $copyrightDate;
    }

    /**
     * Render this script
     */
    public function __toString()
    {
        ob_start();
        require __DIR__ . "/../Script/Page.phtml";
        return ob_get_clean();
    }


}
