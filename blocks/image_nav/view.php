<?php defined('C5_EXECUTE') or die("Access Denied.");
$c = Page::getCurrentPage();
?>

<div class="ccm-image-nav-container" >
    <div class="ccm-image-nav-inner">
        <?php if(count($rows) > 0) { ?>
        <ul class="image-nav" id="ccm-image-nav-<?php echo $bID ?>">
            <?php foreach($rows as $row) { ?>
                <li>
                <?php if($row['linkURL']) { ?>
                    <a href="<?php echo $row['linkURL'] ?>">
                <?php } ?>
                <?php
                $f = File::getByID($row['fID'])
                ?>
                <?php if(is_object($f)) {
                    $tag = Core::make('html/image', array($f, false))->getTag();
                    if($row['title']) {
                    	$tag->alt($row['title']);
                    }
                    print $tag; ?>
                <?php } ?>
                <?php if($row['linkURL']) { ?>
                    </a>
                <?php } ?>
                </li>
            <?php } ?>
        </ul>
        <?php } else { ?>
        <div class="ccm-image-nav-placeholder">
            <p><?php echo t('No Image Nav Entered.'); ?></p>
        </div>
        <?php } ?>
    </div>
</div>