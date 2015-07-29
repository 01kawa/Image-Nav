<?php  defined('C5_EXECUTE') or die("Access Denied.");

$fp = FilePermissions::getGlobal();
$tp = new TaskPermission();
?>
<script>
    var CCM_EDITOR_SECURITY_TOKEN = "<?php echo Loader::helper('validation/token')->generate('editor')?>";
    $(document).ready(function(){
        var ccmReceivingEntry = '';
        var imagenavEntriesContainer = $('.ccm-image-nav-entries');
        var _templateSlide = _.template($('#imageTemplate').html());
        var attachDelete = function($obj) {
            $obj.click(function(){
                var deleteIt = confirm('<?php echo t('Are you sure?') ?>');
                if(deleteIt == true) {
                    $(this).closest('.ccm-image-nav-entry').remove();
                    doSortCount();
                }
            });
        }

        var attachSortDesc = function($obj) {
            $obj.click(function(){
               var myContainer = $(this).closest($('.ccm-image-nav-entry'));
               myContainer.insertAfter(myContainer.next('.ccm-image-nav-entry'));
               doSortCount();
            });
        }

        var attachSortAsc = function($obj) {
            $obj.click(function(){
                var myContainer = $(this).closest($('.ccm-image-nav-entry'));
                myContainer.insertBefore(myContainer.prev('.ccm-image-nav-entry'));
                doSortCount();
            });
        }

        var attachFileManagerLaunch = function($obj) {
            $obj.click(function(){
                var oldLauncher = $(this);
                ConcreteFileManager.launchDialog(function (data) {
                    ConcreteFileManager.getFileDetails(data.fID, function(r) {
                        jQuery.fn.dialog.hideLoader();
                        var file = r.files[0];
                        oldLauncher.html(file.resultsThumbnailImg);
                        oldLauncher.next('.image-fID').val(file.fID)
                    });
                });
            });
        }

        var doSortCount = function(){
            $('.ccm-image-nav-entry').each(function(index) {
                $(this).find('.ccm-image-nav-entry-sort').val(index);
            });
        };

        imagenavEntriesContainer.on('change', 'select[data-field=entry-link-select]', function() {
            var container = $(this).closest('.ccm-image-nav-entry');
            switch(parseInt($(this).val())) {
                case 2:
                    container.find('div[data-field=entry-link-page-selector]').hide();
                    container.find('div[data-field=entry-link-url]').show();
                    break;
                case 1:
                    container.find('div[data-field=entry-link-url]').hide();
                    container.find('div[data-field=entry-link-page-selector]').show();
                    break;
                default:
                    container.find('div[data-field=entry-link-page-selector]').hide();
                    container.find('div[data-field=entry-link-url]').hide();
                    break;
            }
        });

       <?php if($rows) {
           foreach ($rows as $row) {
            $linkType = 0;
            if ($row['linkURL']) {
                $linkType = 2;
            } else if ($row['internalLinkCID']) {
                $linkType = 1;
           } ?>
           imagenavEntriesContainer.append(_templateSlide({
                fID: '<?php echo $row['fID'] ?>',
                <?php if(File::getByID($row['fID'])) { ?>
                image_url: '<?php echo File::getByID($row['fID'])->getThumbnailURL('file_manager_listing');?>',
                <?php } else { ?>
                image_url: '',
               <?php } ?>
                link_url: '<?php echo $row['linkURL'] ?>',
                link_type: '<?php echo $linkType?>',
                title: '<?php echo addslashes(h($row['title'])) ?>',
                sort_order: '<?php echo $row['sortOrder'] ?>'
            }));
            imagenavEntriesContainer.find('.ccm-image-nav-entry:last-child div[data-field=entry-link-page-selector]').concretePageSelector({
                'inputName': 'internalLinkCID[]', 'cID': <?php if ($linkType == 1) { ?><?php echo intval($row['internalLinkCID'])?><?php } else { ?>false<?php } ?>
            });
        <?php }
        }?>

        doSortCount();
        imagenavEntriesContainer.find('select[data-field=entry-link-select]').trigger('change');

        $('.ccm-add-image-nav-entry').click(function(){
           var thisModal = $(this).closest('.ui-dialog-content');
            imagenavEntriesContainer.append(_templateSlide({
                fID: '',
                title: '',
                link_url: '',
                cID: '',
                link_type: 0,
                sort_order: '',
                image_url: ''
            }));
            var newSlide = $('.ccm-image-nav-entry').last();
            thisModal.scrollTop(newSlide.offset().top);
            newSlide.find('.redactor-content').redactor({
                minHeight: '200',
                'concrete5': {
                    filemanager: <?php echo $fp->canAccessFileManager()?>,
                    sitemap: <?php echo $tp->canAccessSitemap()?>,
                    lightbox: true
                }
            });
            attachDelete(newSlide.find('.ccm-delete-image-nav-entry'));
            attachFileManagerLaunch(newSlide.find('.ccm-pick-slide-image'));
            newSlide.find('div[data-field=entry-link-page-selector-select]').concretePageSelector({
                'inputName': 'internalLinkCID[]'
            });
            attachSortDesc(newSlide.find('i.fa-sort-desc'));
            attachSortAsc(newSlide.find('i.fa-sort-asc'));
            doSortCount();
        });
        attachDelete($('.ccm-delete-image-nav-entry'));
        attachSortAsc($('i.fa-sort-asc'));
        attachSortDesc($('i.fa-sort-desc'));
        attachFileManagerLaunch($('.ccm-pick-slide-image'));
        $(function() {  // activate redactors
            $('.redactor-content').redactor({
                minHeight: '200',
                'concrete5': {
                    filemanager: <?php echo $fp->canAccessFileManager()?>,
                    sitemap: <?php echo $tp->canAccessSitemap()?>,
                    lightbox: true
                }
            });
        });
    });
</script>
<style>

    .ccm-image-nav-block-container .redactor_editor {
        padding: 20px;
    }
    .ccm-image-nav-block-container input[type="text"],
    .ccm-image-nav-block-container textarea {
        display: block;
        width: 100%;
    }
    .ccm-image-nav-block-container .btn-success {
        margin-bottom: 20px;
    }

    .ccm-image-nav-entries {
        padding-bottom: 30px;
    }

    .ccm-pick-slide-image {
        padding: 15px;
        cursor: pointer;
        background: #dedede;
        border: 1px solid #cdcdcd;
        text-align: center;
        vertical-align: center;
    }

    .ccm-pick-slide-image img {
        max-width: 100%;
    }

    .ccm-image-nav-entry {
        position: relative;
    }



    .ccm-image-nav-block-container i.fa-sort-asc {
        position: absolute;
        top: 10px;
        right: 10px;
        cursor: pointer;
    }

    .ccm-image-nav-block-container i:hover {
        color: #5cb85c;
    }

    .ccm-image-nav-block-container i.fa-sort-desc {
        position: absolute;
        top: 15px;
        cursor: pointer;
        right: 10px;
    }
</style>
<div class="ccm-image-nav-block-container">

    <span class="btn btn-success ccm-add-image-nav-entry"><?php echo t('Add Entry') ?></span>
    <div class="ccm-image-nav-entries">

    </div>
</div>
<script type="text/template" id="imageTemplate">
    <div class="ccm-image-nav-entry well">
        <i class="fa fa-sort-desc"></i>
        <i class="fa fa-sort-asc"></i>
        <div class="form-group">
            <label><?php echo t('Image') ?></label>
            <div class="ccm-pick-slide-image">
                <% if (image_url.length > 0) { %>
                    <img src="<%= image_url %>" />
                <% } else { %>
                    <i class="fa fa-picture-o"></i>
                <% } %>
            </div>
            <input type="hidden" name="<?php echo $view->field('fID')?>[]" class="image-fID" value="<%=fID%>" />
        </div>
        <div class="form-group">
            <label><?php echo t('Title') ?></label>
            <input type="text" name="<?php echo $view->field('title')?>[]" value="<%=title%>" />
        </div>
        <div class="form-group">
           <label><?php echo t('Link') ?></label>
            <select data-field="entry-link-select" name="linkType[]" class="form-control" style="width: 60%;">
                <option value="0" <% if (!link_type) { %>selected<% } %>><?php echo t('None')?></option>
                <option value="1" <% if (link_type == 1) { %>selected<% } %>><?php echo t('Another Page')?></option>
                <option value="2" <% if (link_type == 2) { %>selected<% } %>><?php echo t('External URL')?></option>
            </select>
        </div>

        <div style="display: none;" data-field="entry-link-url" class="form-group">
           <label><?php echo t('URL:') ?></label>
            <textarea name="linkURL[]"><%=link_url%></textarea>
        </div>

        <div style="display: none;" data-field="entry-link-page-selector" class="form-group">
           <label><?php echo t('Choose Page:') ?></label>
            <div data-field="entry-link-page-selector-select"></div>
        </div>

        <input class="ccm-image-nav-entry-sort" type="hidden" name="<?php echo $view->field('sortOrder')?>[]" value="<%=sort_order%>"/>
        <div class="form-group">
            <span class="btn btn-danger ccm-delete-image-nav-entry"><?php echo t('Delete Entry'); ?></span>
        </div>
    </div>
</script>
