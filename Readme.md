This is ENTIRELY UNTESTED
But it was a fun evening!

From a file called Page.phtml containing

```php
<div class="page">
    <h1><?=$this->heading ?></h1>
    <p>This is an example template</p>
    <?=$this->copyrightDate; ?>
</div>
```

This will (attempt to) (maybe) generate something like

```php
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
```

So in your MVC code instead of something awful like

```php
$view->render('Page.phtml', ['heading' => 'how do you know this is needed', 'copyrightDate' => 'dull']);
```

You can produce something a bit better like
(I can't exactly show code completion, just imagine it)

```php
$view->add( new Page( 'Wow, I know what variables this script needs!', 'Gosh I'm so happy' ) );
```