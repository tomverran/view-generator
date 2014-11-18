<?php
namespace tomverran\Viewgen\Test\View;

/**
 * Generated on 18 Nov 2014
 */
class Template
{

    /**
     * Seen on line(s): 8
     */
    private $something = null;

    /**
     * Construct this Template
     */
    public function __construct($something)
    {
        $this->something = $something;
    }

    /**
     * Render this script
     */
    public function __toString()
    {
        ob_start();
        require __DIR__ . "/../Script/Template.phtml";
        return ob_get_clean();
    }


}
