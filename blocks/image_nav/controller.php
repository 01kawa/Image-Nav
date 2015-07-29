<?php
namespace Application\Block\ImageNav;

use Concrete\Core\Block\BlockController;
use Loader;
use Page;

class Controller extends BlockController
{
    protected $btTable = 'btImageNav';
    protected $btExportTables = array('btImageNav', 'btImageNavEntries');
    protected $btInterfaceWidth = "600";
    protected $btWrapperClass = 'ccm-ui';
    protected $btInterfaceHeight = "465";
    protected $btCacheBlockRecord = true;
    protected $btExportFileColumns = array('fID');
    protected $btCacheBlockOutput = true;
    protected $btCacheBlockOutputOnPost = true;
    protected $btCacheBlockOutputForRegisteredUsers = false;
    protected $btIgnorePageThemeGridFrameworkContainer = true;

    public function getBlockTypeDescription()
    {
        return t("create your image navigation");
    }

    public function getBlockTypeName()
    {
        return t("Image Nav");
    }

    public function getSearchableContent()
    {
        $content = '';
        $db = Loader::db();
        $v = array($this->bID);
        $q = 'select * from btImageNavEntries where bID = ?';
        $r = $db->query($q, $v);
        foreach($r as $row) {
           $content.= $row['title'].' ';
        }
        return $content;
    }

    public function add()
    {
        $this->requireAsset('core/file-manager');
        $this->requireAsset('core/sitemap');
        $this->requireAsset('redactor');
    }

    public function edit()
    {
        $this->requireAsset('core/file-manager');
        $this->requireAsset('core/sitemap');
        $this->requireAsset('redactor');
        $db = Loader::db();
        $query = $db->GetAll('SELECT * from btImageNavEntries WHERE bID = ? ORDER BY sortOrder', array($this->bID));
        $this->set('rows', $query);
    }

    public function composer()
    {
        $this->edit();
    }

    public function registerViewAssets()
    {
        $this->requireAsset('javascript', 'jquery');
    }

    public function getEntries()
    {
        $db = Loader::db();
        $r = $db->GetAll('SELECT * from btImageNavEntries WHERE bID = ? ORDER BY sortOrder', array($this->bID));
        // in view mode, linkURL takes us to where we need to go whether it's on our site or elsewhere
        $rows = array();
        foreach($r as $q) {
            if (!$q['linkURL'] && $q['internalLinkCID']) {
                $c = Page::getByID($q['internalLinkCID'], 'ACTIVE');
                $q['linkURL'] = $c->getCollectionLink();
                $q['linkPage'] = $c;
            }
            $rows[] = $q;
        }
        return $rows;
    }

    public function view()
    {
        $this->set('rows', $this->getEntries());
    }

    public function duplicate($newBID) {
        parent::duplicate($newBID);
        $db = Loader::db();
        $v = array($this->bID);
        $q = 'select * from btImageNavEntries where bID = ?';
        $r = $db->query($q, $v);
        while ($row = $r->FetchRow()) {
            $db->execute('INSERT INTO btImageNavEntries (bID, fID, linkURL, title, sortOrder) values(?,?,?,?,?)',
                array(
                    $newBID,
                    $row['fID'],
                    $row['linkURL'],
                    $row['title'],
                    $row['sortOrder']
                )
            );
        }
    }

    public function delete()
    {
        $db = Loader::db();
        $db->delete('btImageNavEntries', array('bID' => $this->bID));
        parent::delete();
    }

    public function save($args)
    {
        $db = Loader::db();
        $db->execute('DELETE from btImageNavEntries WHERE bID = ?', array($this->bID));
        $count = count($args['sortOrder']);
        $i = 0;
        parent::save($args);

        while ($i < $count) {
            $linkURL = $args['linkURL'][$i];
            $internalLinkCID = $args['internalLinkCID'][$i];
            switch (intval($args['linkType'][$i])) {
                case 1:
                    $linkURL = '';
                    break;
                case 2:
                    $internalLinkCID = 0;
                    break;
                default:
                    $linkURL = '';
                    $internalLinkCID = 0;
                    break;
            }

            $db->execute('INSERT INTO btImageNavEntries (bID, fID, title, sortOrder, linkURL, internalLinkCID) values(?, ?, ?, ?,?,?)',
                array(
                    $this->bID,
                    intval($args['fID'][$i]),
                    $args['title'][$i],
                    $args['sortOrder'][$i],
                    $linkURL,
                    $internalLinkCID
                )
            );
            $i++;
        }
    }

}