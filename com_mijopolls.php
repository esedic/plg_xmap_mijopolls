<?php

// Parse error: syntax error, unexpected ')' in /var/www/html/mklwww3/plugins/xmap/com_mijopolls/com_mijopolls.php on line 33
// Parse error: syntax error, unexpected '$uri' (T_VARIABLE), expecting ';' or '{' in /var/www/html/mklwww3/plugins/xmap/com_mijopolls/com_mijopolls.php on line 43

/**
* @version		1.0.0
* @package		MijoPolls
* @subpackage	Xmap plugin
* @copyright	2009-2011 Mijosoft LLC, www.mijosoft.com
* @license		GNU/GPL http://www.gnu.org/copyleft/gpl.html
* @license		GNU/GPL based on AcePolls www.joomace.net
*/

defined('_JEXEC') or die('Restricted access');

class xmap_com_mijopolls {

    /**
     * @var array
     */
    private static $view = 'polls';

    /**
     * @var bool
     */
    private static $enabled = false;

    public function __construct()
    {
        self::$enabled = JComponentHelper::isEnabled('com_mijopolls');
    }

    /**
     * @param XmapDisplayerInterface $xmap
     * @param stdClass $parent
     * @param array $params
     *
     * @throws Exception
     */

	public static function getTree($xmap, stdClass $parent, array &$params) {

		$uri = new JUri($parent->link);

      if (!self::$enabled || !in_array($uri->getVar('view'), self::$views))
      {
          return;
      }

    $params['groups'] = implode(',', JFactory::getUser()->getAuthorisedViewLevels());

		$params['show_unauth'] = JArrayHelper::getValue($params, 'show_unauth', 0);
    $params['show_unauth'] = ($params['show_unauth'] == 1 || ($params['show_unauth'] == 2 && $xmap->view == 'xml') || ($params['show_unauth'] == 3 && $xmap->view == 'html'));

    $params['ddPolls_priority'] = JArrayHelper::getValue($params, 'ddPolls_priority', $parent->priority);
    $params['ddPolls_changefreq'] = JArrayHelper::getValue($params, 'ddPolls_changefreq', $parent->changefreq);

    if ($params['ddPolls_priority'] == -1)
    {
        $params['ddPolls_priority'] = $parent->priority;
    }

    if ($params['ddPolls_changefreq'] == -1)
    {
        $params['ddPolls_changefreq'] = $parent->changefreq;
    }

    if($uri->getVar('view') == 'polls')
    {
      	self::getListsTree($xmap, $parent, $params);
    }
		 
	}

	    /**
     * @param XmapDisplayerInterface $xmap
     * @param stdClass $parent
     * @param array $params
     */
    private static function getListsTree($xmap, stdClass $parent, array &$params)
    {
        $db = JFactory::getDbo();

        $query = $db->getQuery(true)
            ->select(array('id', 'title', 'alias'))
            ->from('#_mijopolls_polls AS l')
            ->where('l.published = 1')
            ->order('l.ordering');

        if (!$params['show_unauth'])
        {
            $query->where('l.visible = 1');
        }

        $db->setQuery($query);
        $rows = $db->loadObjectList();

        if (empty($rows))
        {
            return;
        }

        $xmap->changeLevel(1);

        foreach ($rows as $row)
        {
            $node = new stdclass;
            $node->id = $parent->id;
            $node->name = $row->name;
            $node->uid = $parent->uid . '_lid_' . $row->listid;
            $node->browserNav = $parent->browserNav;
						$node->priority = $params['ddPolls_priority'];
						$node->changefreq = $params['ddPolls_changefreq'];
						$node->link = 'index.php?option=com_mijopolls&amp;view=poll&amp;id='.$row->id.':'.$row->alias;   

        }

        $xmap->changeLevel(-1);
    }
	
}