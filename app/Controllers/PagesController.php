<?php

/**
 * Static pages controller.
 * @package App
 * @subpackage Controller 
 */
class PagesController extends Controller {
    
    protected $load = false;

    public function view() {
        echo Display::header(ucfirst($this->params['page']));
        ?>
<div class="span-24 last">
    <h2>Hey there!</h2>
    <p>
        You have asked to reach the <?php echo $this->params['page']; ?> page.       
    </p>
    <p>
        If you can read this, it probably means that you have installed Magister 
        correctly. Now it's time to build your App!
    </p>
</div>
        <?php
        echo Display::footer();
    }

}
