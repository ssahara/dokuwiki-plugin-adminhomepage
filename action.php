<?php
/**
 * Plugin for a nicer Admin main page with some layout
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Håkan Sandell <hakan.sandell@home.se>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

class action_plugin_adminhomepage extends DokuWiki_Action_Plugin {

    /**
     * register the eventhandlers
     */
    function register(Doku_Event_Handler $controller) {
        $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this, 'handle_act_preprocess');
        $controller->register_hook('TPL_ACT_UNKNOWN', 'BEFORE', $this, 'handle_act_unknown');
    }

    /**
     * Looks for admin action, if found the name is changed so TPL_ACT_UNKNOWN is raised
     */
    function handle_act_preprocess(Doku_Event $event, $param) {
        if (($event->data == 'admin') && empty($_REQUEST['page']) && (act_permcheck($event->data) == 'admin')) {
            $event->data = 'adminhomepage';
            $event->stopPropagation();
            $event->preventDefault();
        }
    }

    /**
     * Catches the "unknown" event "adminhomepage" and outputs the alternative admin main page
     */
    function handle_act_unknown(Doku_Event $event, $param) {
        if ($event->data == 'adminhomepage') {
            $this->_html_admin();
            $event->stopPropagation();
            $event->preventDefault();
        }
    }

    function _html_admin(){
        global $ID, $INFO, $lang, $conf, $auth;

        // build menu of admin functions from the plugins that handle them
        $pluginlist = plugin_list('admin');
        $menu = array();
        foreach ($pluginlist as $p) {
            if(($obj = plugin_load('admin',$p)) === NULL) continue;

            // check permissions
            if($obj->forAdminOnly() && !$INFO['isadmin']) continue;

            $menu[$p] = array('plugin' => $p,
                                'prompt' => $obj->getMenuText($conf['lang']),
                                'sort' => $obj->getMenuSort()
                            );
        }

        // data security check
        // simple check if the 'savedir' is relative and accessible when appended to DOKU_URL
        // it verifies either:
        //   'savedir' has been moved elsewhere, or
        //   has protection to prevent the webserver serving files from it
        if (substr($conf['savedir'],0,2) == './'){
            echo '<a style="border:none; float:right;"
                href="https://www.dokuwiki.org/security#web_access_security">
                <img src="'.DOKU_URL.$conf['savedir'].'/security.png" alt="Your data directory seems to be protected properly."
                onerror="this.parentNode.style.display=\'none\'" /></a>';
        }

        print p_locale_xhtml('admin');

        if ($INFO['isadmin']){
            ptln('<ul class="admin_tasks">');

            if($menu['usermanager'] && $auth && $auth->canDo('getUsers')){
                ptln('  <li class="admin_usermanager"><div class="li">'.
                    '<a href="'.wl($ID, array('do' => 'admin','page' => 'usermanager')).'">'.
                    $menu['usermanager']['prompt'].'</a></div></li>');
            }
            unset($menu['usermanager']);

            if($menu['acl']){
                ptln('  <li class="admin_acl"><div class="li">'.
                    '<a href="'.wl($ID, array('do' => 'admin','page' => 'acl')).'">'.
                    $menu['acl']['prompt'].'</a></div></li>');
            }
            unset($menu['acl']);

            if ($menu['extension2']){
                ptln('  <li class="admin_plugin"><div class="li">'.
                        '<a href="'.wl($ID, array('do' => 'admin','page' => 'extension2')).'">'.
                        $menu['extension2']['prompt'].'</a></div></li>');
            }
            unset($menu['extension2']);

            if ($menu['config']){
                ptln('  <li class="admin_config"><div class="li">'.
                        '<a href="'.wl($ID, array('do' => 'admin','page' => 'config')).'">'.
                        $menu['config']['prompt'].'</a></div></li>');
            }
            unset($menu['config']);

            // 設定ファイルの更新（内容変更なし）
            if ($menu['toucher']){
                ptln('  <li class="admin_config"><div class="li">'.
                        '<a href="'.wl($ID, array('do' => 'admin','page' => 'toucher')).'">'.
                        $menu['toucher']['prompt'].'</a></div></li>');
            }
            unset($menu['toucher']);
        }
        ptln('</ul>');


        // Manager Tasks
        ptln('<ul class="admin_tasks">');

        if($menu['revert']){
            ptln('  <li class="admin_revert"><div class="li">'.
                '<a href="'.wl($ID, array('do' => 'admin','page' => 'revert')).'">'.
                $menu['revert']['prompt'].'</a></div></li>');
        }
        unset($menu['revert']);

        if($menu['popularity']){
            ptln('  <li class="admin_popularity"><div class="li">'.
                '<a href="'.wl($ID, array('do' => 'admin','page' => 'popularity')).'">'.
                $menu['popularity']['prompt'].'</a></div></li>');
        }
        unset($menu['popularity']);

        // リダイレクトマネージャー
        if ($menu['redirect2']){
                ptln('  <li class="admin_config"><div class="li">'.
                        '<a href="'.wl($ID, array('do' => 'admin','page' => 'redirect2')).'">'.
                        $menu['redirect2']['prompt'].'</a></div></li>');
        }
        unset($menu['redirect2']);

        ptln('</ul>');


        // print DokuWiki version:
        echo '<div id="admin__version">';
        echo getVersion();
        ptln('    <div><b>'.$this->getLang('php_version').'</b> '.phpversion().'</div>');
        echo '</div>';
        ptln('<div class="clearer"></div>');


        // print the rest as sorted list
        if(count($menu)){
            usort($menu, 'p_sort_modes');
            // output the menu
            print p_locale_xhtml('adminplugins');
            ptln('<ul>');
            foreach ($menu as $item) {
                if (!$item['prompt']) continue;
                ptln('  <li><div class="li"><a href="'.wl($ID, 'do=admin&amp;page='.$item['plugin']).'">'.$item['prompt'].'</a></div></li>');
            }
            ptln('</ul>');
        }
    }

}
